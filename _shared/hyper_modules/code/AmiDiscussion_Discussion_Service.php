<?php
/**
 * AmiDiscussion/Discussion configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiExt_Discussion
 * @version   $Id: AmiDiscussion_Discussion_Service.php 50069 2014-04-18 10:29:27Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Discussion extension service class.
 *
 * @package    Config_AmiExt_Discussion
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Discussion_Service{
    /**
     * Instance
     *
     * @var ExtCustomFields_Service
     */
    private static $oInstance;

    /**
     * Applicable module list, also contains modules captions after initialization
     *
     * @var  array
     * @todo Cleanup list when module is
     */
    private $aAllowedModules = array(
        'blog'            => '',
        'eshop_item'      => '',
        'kb_item'         => '',
        'portfolio_item'  => ''
    );

    /**
     * Applicable installed modules list
     *
     * @var array
     */
    private $aIntsalledModules = array();

    /**
     * Returns AmiExt_CustomFields_Service instance.
     *
     * @return AmiExt_CustomFields_Service
     */
    public static function getInstance(){
        if(self::$oInstance == null){
            self::$oInstance = new self;
        }
        return self::$oInstance;
    }

    /**
     * Destroys instance.
     *
     * @return void
     * @todo   Use when all business logic processed or detect usage necessity
     */
    public static function destroyInstance(){
        self::$oInstance = null;
    }

    /**
     * Returns root id from discussion table.
     *
     * @param  string $modId         Modul;e id
     * @param  string $extModId      External module id
     * @param  int    $extModItemId  External module item id
     * @return int
     */
    public function getMainParentId($modId, $extModId, $extModItemId){
        $oItem =
            AMI::getResourceModel($modId . '/table')
                ->findByFields(
                    array(
                        'id_parent'     => 0,
                        'ext_module'    => $extModId,
                        'id_ext_module' => $extModItemId
                    ),
                    array('id')
                );
        return (int)$oItem->id;
    }

    /**
     * Returns allowed modules list for Custom Fields extension.
     *
     * @return array  Array ('news' => 'localized news module cpation', ...)
     */
    public function getAllowedModules(){
        return $this->aAllowedModules;
    }

    /**
     * Returns module caption.
     *
     * @param  string $modId  Module id
     * @return string|null
     */
    public function getModCaption($modId){
        return isset($this->aAllowedModules[$modId]) ? $this->aAllowedModules[$modId] : null;
    }

    /**
     * Returns allowed and installed modules list for Custom Fields extension.
     *
     * @return array  Array ('news', 'blog', ...)
     */
    public function getInstalledModules(){
        return $this->aIntsalledModules;
    }

    /**
     * Initialize modules captions from dictionary.
     *
     * @return void
     * @todo   Avoid owners/section hardcode
     */
    protected function initModulesCaptions(){
        $oDeclarator = AMI_ModDeclarator::getInstance();
        $aSections = array_unique($oDeclarator->getSections());
        $index = array_search('modules', $aSections);
        if(FALSE !== $index){
            unset($aSections[$index]);
        }
        $this->aAllowedModules = AMI_Service_Adm::getModulesCaptions(array_keys($this->aAllowedModules), TRUE, $aSections, TRUE, TRUE);
        foreach(array('eshop', 'kb', 'portfolio') as $section){
            unset($this->aAllowedModules[$section . '_account']);
        }

        /*
        $oTpl = AMI::getSingleton('env/template_sys');
        $aLocale = $oTpl->parseLocale('templates/lang/_menu_all.lng');
        $aSectionsLocale = $oTpl->parseLocale('templates/lang/_menu_owners.lng');
        $aSpecialModIds = array(
            'eshop_item'     => 'eshop',
            'kb_item'        => 'kb',
            'portfolio_item' => 'portfolio'
        );
        $oDeclarator = AMI_ModDeclarator::getInstance();
        foreach(array_keys($this->aAllowedModules) as $modId){
            if(isset($aLocale[$modId])){
                $caption = $aLocale[$modId];
                $parentModId = $oDeclarator->getParent($modId);
                $prefix = '';
                if(isset($aSpecialModIds[$modId])){
                    $prefix = $aSpecialModIds[$modId];
                }
                if(!is_null($parentModId)){
                    $parentCaption = isset($aLocale[$parentModId]) ? $aLocale[$parentModId] : $aLocale['unknown'];
                    $caption = $parentCaption . ' : ' . $caption;
                    if(isset($aSpecialModIds[$parentModId])){
                        $prefix = $aSpecialModIds[$parentModId];
                    }
                }
                if($prefix !== ''){
                    $caption = $aSectionsLocale[$prefix] . ' : ' . $caption;
                }
            }else{
                $caption = '{' . $modId . '}';
            }
            $this->aAllowedModules[$modId] = $caption;
        }
        */

        asort($this->aAllowedModules, SORT_LOCALE_STRING);
    }

    /**
     * Singleton counstructor.
     */
    private function __construct(){
        /**
         * @todo Avoid hardcoded ext_discussion
         */
        $this->aAllowedModules += AMI_Ext::getSupportedModules('ext_discussion');
        if(is_array($this->aAllowedModules) && sizeof($this->aAllowedModules)){
            foreach(array_keys($this->aAllowedModules) as $modId){
                if(AMI::isModInstalled($modId)){
                    $this->aIntsalledModules[] = $modId;
                }
            }
        }
        $this->initModulesCaptions();
    }

    /**
     * Singleton cloning.
     */
    private function __clone(){
    }
}
