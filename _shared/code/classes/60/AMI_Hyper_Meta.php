<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module
 * @version   $Id: AMI_Hyper_Meta.php 45064 2013-12-05 09:27:10Z Leontiev Anton $
 * @since     6.0.2
 */

/**
 * Hypermodule metadata.
 *
 * @package Module
 * @since   6.0.2
 */
abstract class AMI_Hyper_Meta{
    /**
     * Version
     *
     * @var string
     */
    protected $version = '';

    /**
     * Flag specifying that hypermodule configs can have only one instance per config
     *
     * @var bool
     */
    protected $isSingleInstance = FALSE;

    /**
     * Flag specifies to display configuraration in Module Manager
     *
     * @var bool
     * @amidev
     */
    protected $isVisible = TRUE;

    /**
     * Array of hypermodule meta data
     *
     * @var array
     */
    protected $aData;

    /**
     * Array having locales as keys and titles as values
     *
     * @var array
     */
    protected $aTitle;

    /**
     * Array having locales as keys and meta data as values
     *
     * @var array
     */
    protected $aInfo;

    /**
     * Returns hypermodule version.
     *
     * @return string
     */
    public function getVersion(){
        return $this->version;
    }

    /**
     * Returns hypermodule / config title.
     *
     * @param  string $locale  Locale
     * @return string
     */
    public function getTitle($locale){
        return $this->aTitle[$locale];
    }

    /**
     * Returns hypermodule info.
     *
     * @param  string $locale  Locale
     * @return string
     */
    public function getInfo($locale){
        return $this->aInfo[$locale];
    }

    /**
     * Returns hypermodule meta data.
     *
     * @param  string $key  Key name
     * @return string
     */
    public function getData($key = null){
        if(empty($key)){
            return $this->aData;
        }
        return isset($this->aData[$key]) ? $this->aData[$key] : null;
    }

    /**
     * Set hypermodule meta data.
     *
     * @param  string $key    Key name
     * @param  mixed  $value  Value
     * @return void
     */
    public function setData($key, $value){
        $this->aData[$key] = $value;
    }

    /**
     * Returns hypermodule instantiate flag value.
     *
     * @return bool
     */
    public function isSingleInstance(){
        return $this->isSingleInstance;
    }

    /**
     * Returns hypermodule instantiate flag value.
     *
     * @return bool
     * @amidev
     */
    public function isVisible(){
        return $this->isVisible;
    }

    /**
     * Retrurns allowed installation/uninstallation modes.
     *
     * Example:
     * <code>
     * // AMI_Hyper_Meta::getAllowedModes():
     *
     * public function getAllowedModes($type = ''){
     *     $aModes = array(
     *         'install' => array(
     *             'common'    => AMI_iTx_Cmd::MODE_COMMON,
     *             'append'    => AMI_iTx_Cmd::MODE_APPEND,
     *             'overwrite' => AMI_iTx_Cmd::MODE_OVERWRITE
     *         ),
     *         'uninstall' => array(
     *             'soft'  => AMI_iTx_Cmd::MODE_SOFT,
     *             'purge' => AMI_iTx_Cmd::MODE_PURGE
     *         )
     *     );
     *     return
     *         $type === '' ? $aModes : $aModes[$type];
     * }
     *
     *
     * // AMI_Hyper_Meta child context:
     *
     * public function getAllowedModes($type = ''){
     *     $aModes = parent::getAllowedModes();
     *     // I. e. common payment driver doesn't support soft uninstallation.
     *     unset($aModes['uninstall']['soft']);
     *     return
     *         $type === '' ? $aModes : $aModes[$type];
     * }
     * </code>
     *
     * @param  string $type  Possible values: 'install' / 'uninstall' / '' (all)
     * @return array
     * @since  6.0.2
     */
    public function getAllowedModes($type = ''){
        $aModes = array(
            'install' => array(
                'common'    => AMI_iTx_Cmd::MODE_COMMON,
                'append'    => AMI_iTx_Cmd::MODE_APPEND,
                'overwrite' => AMI_iTx_Cmd::MODE_OVERWRITE
            ),
            'uninstall' => array(
                'soft'  => AMI_iTx_Cmd::MODE_SOFT,
                'purge' => AMI_iTx_Cmd::MODE_PURGE
            )
        );
        return
            $type === '' ? $aModes : $aModes[$type];
    }
}

/**
 * Hypermodule config metadata.
 *
 * @package Module
 * @since   6.0.2
 */
abstract class AMI_HyperConfig_Meta extends AMI_Hyper_Meta{
    /**#@+
     * Caption flag.
     */

    /**
     * @amidev
     */
    const CAPTION_OPTIONAL    = 0;
    /**
     * @amidev
     */
    const CAPTION_OBLIGATORY  = 1;

    const CAPTION_TYPE_STRING = 0;
    const CAPTION_TYPE_TEXT   = 1;

    /**#@-*/

    /**
     * Flag specifying that hypermodule configs has one common data source
     *
     * @var bool
     */
    protected $hasCommonDataSource = FALSE;

    /**
     * Flag specifies possibility of local PHP-code generation
     *
     * @var   bool
     * @since 6.0.2
     */
    protected $canGenCode = TRUE;

    /**
     * Array containing captions struct
     *
     * @var array
     */
    protected $aCaptions;

    /**
     * Exact configuration instance modId
     *
     * @var string
     */
    protected $instanceId = null;

    /**
     * Flag speficies impossibility of instance deinstallation
     *
     * @var bool
     * @amidev
     */
    protected $permanent = FALSE;

    /**
     * Flag specifies to display configuraration in Module Manager
     *
     * @var bool
     * @amidev
     */
    protected $displayInModManager = TRUE;

    /**
     * Flag specifies possibility of instace editing
     *
     * @var    bool
     * @amidev Temporary
     */
    protected $editable = TRUE;

    /**
     * Import instructions
     *
     * @var array
     * @amidev
     */
    protected $aImport = array();

    /**
     * Old captions format
     *
     * @var array
     */
    private $aOldCaptions;

    /**
     * Returns hypermodule config default instance captions.
     *
     * @param  string $locale     Locale
     * @param  string $oldFormat  Flag specifying to return data in old format
     * @return array
     * @amidev Temporary?
     * @todo   Rewrite code using method to use new format?
     */
    public function getCaptions($locale = '', $oldFormat = TRUE){
        // return $locale === '' ? $this->aCaptions : $this->aCaptions[$locale];###

        if($oldFormat && !$this->aOldCaptions){
            // Convert captions to old format
            $this->aOldCaptions = array();
            if(is_array($this->aCaptions)){
                foreach($this->aCaptions as $modTail => $aCaptionsData){
                    foreach($aCaptionsData as $caption => $aData){
                        if(empty($aData['locales'])){
                            // skip old meta format
                            continue;
                        }
                        foreach($aData['locales'] as $loc => $aLocaleData){
                            if(empty($this->aOldCaptions[$loc])){
                                $this->aOldCaptions[$loc] = array();
                            }
                            if(empty($this->aOldCaptions[$loc][$modTail])){
                                $this->aOldCaptions[$loc][$modTail] = array();
                            }
                            if(empty($this->aOldCaptions[$loc][$modTail][$caption])){
                                $this->aOldCaptions[$loc][$modTail][$caption] = array(
                                    $aLocaleData['name'],
                                    $aLocaleData['caption'],
                                    (int)$aData['obligatory'],
                                    (int)$aData['type']
                                );
                            }
                        }
                    }
                }
            }
            /*
            else{
                $this->aOldCaptions = array('en' => array(), 'ru' => array());
            */
        }

        if($oldFormat){
            return $locale === '' ? $this->aOldCaptions : $this->aOldCaptions[$locale];
        }else{
            return $this->aCaptions;
        }
    }

    /**
     * Returns fixed module Id for configuration instance if set.
     *
     * @return string
     */
    public function getInstanceId(){
        return $this->instanceId;
    }

    /**
     * Returns TRUE if configuration has one common data source.
     *
     * @return bool
     */
    public function hasCommonDataSource(){
        return $this->hasCommonDataSource;
    }

    /**
     * Returns true if instance cannot be uninstalled.
     *
     * @return bool
     * @amidev
     */
    public function isPermanent(){
        return $this->permanent;
    }

    /**
     * Returns true if instance can be edited in modules manager.
     *
     * @return bool
     */
    public function isEditable(){
        return $this->editable;
    }

    /**
     * Returns true if local PHP-code can be generated for.
     *
     * @return bool
     * @since  6.0.2
     */
    public function canGenCode(){
        return $this->canGenCode;
    }

    /**
     * Checks if import is allowed.
     *
     * @return bool
     * @amidev
     */
    public function isImportAllowed(){
        return is_array($this->aImport) && isset($this->aImport['sourceModIds']);
    }

    /**
     * Retruns import source module id.
     *
     * @return array
     * @amidev
     */
    public function getImportSourceModIds(){
        return isset($this->aImport['sourceModIds']) ? $this->aImport['sourceModIds'] : array();
    }

    /**
     * Get allowed import types.
     *
     * @return array
     * @amidev
     */
    public function getImportAllowedTypes(){
        return isset($this->aImport['allowedTypes']) ? $this->aImport['allowedTypes'] : array();
    }
}
