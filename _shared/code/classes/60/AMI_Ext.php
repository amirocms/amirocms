<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Extension
 * @version   $Id: AMI_Ext.php 43709 2013-11-15 10:35:57Z Kolesnikov Artem $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Module extension abstraction.
 *
 * @package    Extension
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class AMI_Ext{
    /**
     * Module Id
     *
     * @var string
     */
    public $modId;

    /**
     * Options source Id
     *
     * @var string
     */
    public $optScrId;

    /**
     * Extension module id
     *
     * @var    string
     * @see    AMI_Ext::getExtId()
     * @amidev
     */
    protected $extId = '';

    /**
     * Module controller
     *
     * @var AMI_Mod
     */
    protected $oModController;

    /**
     * Installed flag
     *
     * @var bool
     */
    private $bInstalled = true;

    /**
     * Related modules ids
     *
     * @var array
     */
    private $aModIds = array();

    /**
     * Internal tree of hypermodules compatible by others.
     *
     * @var array
     */
    private static $aCompatModules;

    /**
     * Constructor.
     *
     * @param string  $modId        Module id
     * @param string  $optSrcId     Options source module id
     * @param AMI_Mod $oController  Module controller
     */
    public function __construct($modId, $optSrcId = '', AMI_Mod $oController = null){
        $this->modId = $modId;
        $this->optSrcId = empty($optSrcId)? $modId: $optSrcId;
        $this->oModController = $oController;

        AMI_Event::addHandler('on_mod_pre_init', array($this, 'handlePreInit'), $modId);
        // Commented by #CMS-11370
        // AMI_Event::addHandler('on_list_body_row', array($this, 'handleOverallListBodyRow'), $modId);
    }

    /**
     * Destructor.
     */
    public function __destruct(){
        AMI_Event::dropHandler('', $this);
        unset($this->oModController);
    }

    /**
     * Callback called after module is installed (stub).
     *
     * @param  string         $modId  Installed module id
     * @param  AMI_Tx_Cmd_Args $oArgs  Transaction command arguments
     * @return void
     */
    public function onModPostInstall($modId, AMI_Tx_Cmd_Args $oArgs){
        // trigger_error($this->getExtId() . '::onModPostInstall(' . $modId . ')', E_USER_WARNING);
    }

    /**
     * Callback called after module is uninstalled (stub).
     *
     * @param  string         $modId  Unnstalled module id
     * @param  AMI_Tx_Cmd_Args $oArgs  Transaction command arguments
     * @return void
     */
    public function onModPostUninstall($modId, AMI_Tx_Cmd_Args $oArgs){
        // trigger_error($this->getExtId() . '::onModPostUninstall(' . $modId . ')', E_USER_WARNING);
    }

    /**
     * Callback called after module is uninstalled without cheking unistallation mode (stub).
     *
     * @param  string         $modId  Unnstalled module id
     * @param  AMI_Tx_Cmd_Args $oArgs  Transaction command arguments
     * @return void
     */
    public function onModPostUninstallUnmasked($modId, AMI_Tx_Cmd_Args $oArgs){
        // trigger_error($this->getExtId() . '::onModPostUninstall(' . $modId . ')', E_USER_WARNING);
    }

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     * @amidev Temporary
     */

    /**
     * Extension base initialization.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Ext::__construct()
     * @see    AMI_Mod::init()
     */
/*
    public final function handlePreInitBase($name, array $aEvent, $handlerModId, $srcModId){
        return $aEvent;
    }
*/

    /**
     * Extension initialization.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Ext::__construct()
     * @see    AMI_Mod::init()
     */
    public abstract function handlePreInit($name, array $aEvent, $handlerModId, $srcModId);

    /**
     * Adding extension presence flag to templates.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Ext::__construct()
     * @see    AMI_Mod::init()
     */
    /*
    // Moved to #CMS-11370
    public function handleOverallListBodyRow($name, array $aEvent, $handlerModId, $srcModId){
        list(, $config) = AMI_ModDeclarator::getInstance()->getHyperData($this->getExtId());
        $aEvent['aData']['ext_' . $config . '_enabled'] = $this->isInstalled();

        return $aEvent;
    }
    */

    /**#@-*/

    /**
     * Returns moule controller object.
     *
     * @return AMI_Mod
     */
    public function getModController(){
        return $this->oModController;
    }

    /**
     * Returns TRUE if module option exists.
     *
     * @param  string $name  Option name
     * @return bool
     */
    public function issetModOption($name){
        return AMI::issetOption($this->optSrcId, $name);
    }

    /**
     * Returns module option.
     *
     * @param  string $name  Option name
     * @return mixed
     */
    public function getModOption($name){
        return AMI::getOption($this->optSrcId, $name);
    }

    /**
     * Checks module option.
     *
     * @param  string $name  Option name
     * @return bool
     */
    public function checkModOption($name){
        return AMI::issetOption($this->optSrcId, $name) && AMI::getOption($this->optSrcId, $name) != -1;
    }

    /**
     * Returns module id.
     *
     * @return string
     */
    public function getModId(){
        return $this->modId;
    }

    /**
     * Returns instanceof AMI_iModFormView from event data.
     *
     * @param  array $aEvent  Event 'on_form_fields_form' data
     * @return AMI_ModFormView|null
     */
    public function getFormViewInstance(array $aEvent){
        return
            empty($aEvent['oFormView']) ||
            !is_object($aEvent['oFormView']) ||
            !($aEvent['oFormView'] instanceof AMI_iModFormView)
                ? NULL
                : $aEvent['oFormView'];
    }

    /**
     * Returns extension module id.
     *
     * @return string
     */
    public function getExtId(){
        if(empty($this->extId)){
            $class = get_class($this);
            if(mb_strpos($class, 'AmiExt_') === 0){
                $this->extId = 'ext_' . AMI::getModId(mb_substr($class, 7));
            }else{
                $this->extId = AMI::getModId(preg_replace('/^Ami/', '', $class));
            }
        }
        return $this->extId;
    }

    /**
     * Sets extension module id.
     *
     * @param  string $extId  Extension module id
     * @return void
     * @amidev Temporary
     */
    public function setExtId($extId){
        $this->extId = $extId;
    }

    /**
     * Returns TRUE if extension is installed.
     *
     * @return bool
     */
    public function isInstalled(){
        return $this->bInstalled;
    }

    /**
     * Sets extension is installation flag.
     *
     * @param  bool $bInstalled  Flag
     * @return void
     */
    protected function setInstalled($bInstalled){
        $this->bInstalled = (bool)$bInstalled;
        if(!$this->bInstalled){
            // Removing init handler
            AMI_Event::dropHandler('on_mod_pre_init', $this, $this->modId);
        }
    }

    /**
     * Adds module.
     *
     * @param  string $modId  Module id
     * @return void
     */
    protected function addModule($modId){
        $this->aModIds[] = $modId;
        if(!AMI::isModInstalled($modId)){
            $this->setInstalled(FALSE);
        }
    }

    /**
     * Returns extension property.
     *
     * @param  string $name  Property name
     * @return mixed
     */
    protected function getProperty($name){
        return AMI::getProperty($this->getExtId(), $name);
    }

    /**
     * Returns extension option.
     *
     * @param  string $name  Option name
     * @return mixed
     * @todo   Implement real getOption (were getOption)?
     */
    protected function getOption($name){
        return AMI::getOption($this->getExtId(), $name);
    }

    /**
     * Returns extension option presence.
     *
     * @param  string $name  Option name
     * @return bool
     */
    protected function issetOption($name){
        return AMI::issetOption($this->getExtId(), $name);
    }

    /**
     * Returns extension view.
     *
     * @param  string $side  Side: 'frn'|'adm'|''
     * @return AMI_ExtView|null
     */
    protected function getView($side = ''){
        $extModId = $this->getExtId();
        $resId = $extModId . '/view/' . $side;
        if(AMI::isResource($resId)){
            $oView = AMI::getResource($resId, array());
            $key = 'AMI/Module/Environment/IstalledExtensions/' . $this->getModId();
            AMI_Registry::set(
                $key . '/' . $extModId . '.view',
                $oView
            );
        }else{
            $oView = NULL;
        }
        return $oView;
    }

    /**
     * Returns hypermodule & subtypes mantains by extension.
     *
     * @param  string $extModId  Extension mod name
     * @return array|null
     */
    public static function getHypermodSubtypes($extModId){
        if($extModId === 'ext_custom_fields'){
            $extModId = 'ext_modules_custom_fields';
        }
        return
            AMI::issetProperty($extModId, 'hypermod_subtypes')
            ? AMI::getProperty($extModId, 'hypermod_subtypes')
            : null;
    }

    /**
     * Returns subtypes & table fields mantains by extension.
     *
     * @param  string $extModId  Extension mod name
     * @return array|null
     */
    /*
    public static function getAffectedModelFields($extModId){
        return
            AMI::issetProperty($extModId, 'subtypes_table_affected_fields')
            ? AMI::getProperty($extModId, 'subtypes_table_affected_fields')
            : null;
    }
    */

    /**
     * Returns dependent on module hypermodule aviable extensions.
     *
     * @param  string $modId  Module id
     * @return array
     * @amidev Temporary
     */
    public static function getAviableModExtensions($modId){
        global $Core;

        $aExtensions = array();
        $aAllExt = array();
        $oDeclarator = AMI_ModDeclarator::getInstance();

        list($hyper, ) = $oDeclarator->getHyperData($modId);

        if(is_callable(array($Core, 'GetModNames'))){
            $aModNames = $Core->GetModNames();
        }else{
            $aModNames = $oDeclarator->getRegistered();
        }
        $isCatModule = AMI::isCategoryModule($modId);

        foreach($aModNames as $name){
            // dirty hardcode for 'ce_page_break' extension, because it ain't start from 'ext_'
            if(preg_match('/^ext_/', $name) || $name == 'ce_page_break'){
                $aAllExt[] = $name;
                $aSupHyper = AMI_Ext::getHypermodSubtypes($name);
                if($aSupHyper && isset($aSupHyper[$hyper])){
                    $isCatSupported = isset($aSupHyper[$hyper]['cat']) && $aSupHyper[$hyper]['cat'];
                    $isRootSupported = isset($aSupHyper[$hyper]['root']) && $aSupHyper[$hyper]['root'];
                    if($isCatModule ? $isCatSupported : $isRootSupported){
                        $aExtensions[] = $name;
                    }
                }
            }
        }

        if(($hyper == 'ami_multifeeds5') && in_array('ext_image', $aExtensions)){
            $aExtensions[] = 'ext_images';
        }

        if(AMI::issetProperty($modId, 'supported_extensions')){
            $aSupportedExtConfigs = AMI::getProperty($modId, 'supported_extensions');
            foreach($aSupportedExtConfigs as $config){
                list($hyper, $config) = explode('/', $config, 2);
                $aIds = $oDeclarator->getRegistered($hyper, $config);
                $aExtensions = array_merge($aExtensions, $aIds);
            }
        }
        $aExtensions = array_unique($aExtensions);

        return $aExtensions;
    }

    /**
     * Get hypermodules compatible to this by extensions.
     *
     * @param  string $modId  Module id
     * @return array | null
     */
    public static function getCompatHypermods($modId){
        if(!self::$aCompatModules){
            self::$aCompatModules = array();
            $aHypers = AMI_Package::getAvailableHyper(TRUE);
            foreach($aHypers as $aHyper){
                if(isset($aHyper['meta']) && isset($aHyper['compatible_by_extensions'])){
                    $compatName = $aHyper['compatible_by_extensions'];
                    if(isset(self::$aCompatModules[$compatName])){
                        self::$aCompatModules[] = $aHyper['hypermod'];
                    }else{
                        self::$aCompatModules[$compatName] = array($aHyper['hypermod']);
                    }
                }
            }
        }
        return isset(self::$aCompatModules[$modId]) ? self::$aCompatModules[$modId] : null;
    }

    /**
     * Getting all hypermodules and subtypes (compatible too) maintains by extension.
     *
     * @param  string $extModId  Extension module id
     * @return array
     */
    public static function getCompatHypermodSubtypes($extModId){
    	$aHypermodSubtypes = AMI_Ext::getHypermodSubtypes($extModId);
        if(!is_array($aHypermodSubtypes)){
            $aHypermodSubtypes = array();
        }
    	foreach($aHypermodSubtypes as $name => $aSub){
    	    $aCompat = self::getCompatHypermods($name);
    	    if($aCompat){
    	        foreach($aCompat as $compatHyper){
                    $aHypermodSubtypes[$compatHyper] = $aSub;
    	        }
    	    }
    	}
    	return $aHypermodSubtypes;
    }

    /**
     * Returns all module ids supported by specified extension.
     *
     * @param  string $extModId   Extension module id
     * @param  bool   $idsAsKeys  Modules ids are keys of result array
     * @return array
     */
    public static function getSupportedModules($extModId, $idsAsKeys = true){
        $aModules = array();
        $aHypers = AMI_Ext::getCompatHypermodSubtypes($extModId);
        if(!is_null($aHypers)){
            foreach($aHypers as $hyper => $aSubs){
                $bAllowCat  = !empty($aSubs['cat']);
                $bAllowRoot = !empty($aSubs['root']);
                $aHyperMods = AMI_ModDeclarator::getInstance()->getRegistered($hyper);
                foreach($aHyperMods as $modId){
                    $bIsCat  = strpos(mb_substr($modId, -4), '_cat') === TRUE;
                    if(($bIsCat ? $bAllowCat : $bAllowRoot) && AMI_Ext::isExtSupportedByModule($extModId, $modId)){
                        $aModules[$modId] = '';
                    }
                }
            }
        }
        return $idsAsKeys ? $aModules : array_keys($aModules);
    }

    /**
     * Checks if module supports specified extension.
     *
     * @param  string $extModId  Extension module id
     * @param  string $modId     Module id
     * @return bool
     */
    public static function isExtSupportedByModule($extModId, $modId){
        $oDeclarator = AMI_ModDeclarator::getInstance();

        if(AMI::issetProperty($modId, 'supported_extensions')){
            if($oDeclarator->isRegistered($extModId)){
                list($hyper, $config) = $oDeclarator->getHyperData($extModId);
                if(in_array($hyper . '/' . $config, AMI::getProperty($modId, 'supported_extensions'))){
                    return TRUE;
                }
            }
        }
        if(!AMI::issetProperty($modId, 'unsupported_extensions')){
            return TRUE;
        }

        $aUnsupported = AMI::getProperty($modId, 'unsupported_extensions');
        if($oDeclarator->isRegistered($extModId)){
            list(, $config) = $oDeclarator->getHyperData($extModId);
            if(in_array($config, $aUnsupported)){
                return FALSE;
            }
        }
        if(in_array($extModId, $aUnsupported) || in_array('*', $aUnsupported)){
            return FALSE;
        }
        return TRUE;
    }
}

/**
 * Module extension abstraction.
 *
 * @package    Hyper_AmiExt
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiExt extends AMI_Ext{
}
