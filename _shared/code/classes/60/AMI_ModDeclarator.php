<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Module
 * @version   $Id: AMI_ModDeclarator.php 48934 2014-03-21 06:53:19Z Medvedev Konstantin $
 * @since     5.14.4
 */

/**
 * Module declarator.
 *
 * @package Module
 * @since   5.14.4
 */
final class AMI_ModDeclarator{
    /**#@+
     * @amidev
     */

    const SORT_FORBID_ASC  = 0x01;
    const SORT_FORBID_DESC = 0x02;
    // const SORT_FORBID_BOTH = 0x03;

    const IS_SYS = 0x10;

    /**#@-*/

    /**#@+
     * @see AMI_ModDeclarator::register()
     */
    const INTERFACE_NONE  = 0x00;
    const INTERFACE_ADMIN = 0x01;
    const INTERFACE_FRONT = 0x02;

    /**
     * @deprecated since 5.14.8
     * @amidev
     */
    const INTERFACE_BOTH  = 0x03;

    /**
     * Flag specifying module has uncanonical table name
     */
    const HAS_UNCANONICAL_TABLE_NAME = 0x04;

    /**
     * @amidev
     */
    const HAS_ASSOC_MODELS           = 0x08;

    /**#@-*/

    /**
     * Instance
     *
     * @var AMI_ModDeclarator
     */
    private static $oInstance;

    /**
     * Core object
     *
     * @var CMS_Core|AMI_Core
     */
    private $oCore;

    /**
     * Flag specifying core is full (CMS_Core)
     *
     * @var bool
     */
    private $isFullCore;

    /**
     * Array containing keys as module ids and array(hyper, config) as values
     *
     * @var array
     */
    private $aMods = array();

    /**
     * Array containing keys as module ids and sections as values
     *
     * @var array
     */
    private $aSections = array();

    /**
     * Array containing keys as module ids and tab orders as values
     *
     * @var array
     */
    private $aTabOrders = array();

    /**
     * Array containing keys as module ids and parent module id as values
     *
     * @var array
     */
    private $aParents = array();

    /**
     * Last section (owner)
     *
     * @var string
     * @see AMI_ModDeclarator::startConfig()
     */
    private $lastSection;

    /**
     * Tab order
     *
     * @var string
     * @see AMI_ModDeclarator::startConfig()
     */
    private $tabOrder;

    /**
     * Array of AMI_Module
     *
     * @var array
     */
    private $aModules = array();

    /**
     * Array of allowed modules to set options
     *
     * @var array
     */
    private $aAllowedOptions = array();

    /**
     * Contains module ids as keys and datasource module ids as values
     *
     * @var array
     */
    private $aModDataSources = array();

    /**
     * Section filter
     *
     * @var string
     * @see AMI_ModDeclarator::getRegistered()
     */
    private $sectionFilter;

    /**
     * Hypermodule filter
     *
     * @var string
     * @see AMI_ModDeclarator::getRegistered()
     */
    private $hypermodFilter;

    /**
     * Config filter
     *
     * @var string
     * @see AMI_ModDeclarator::getRegistered()
     */
    private $configFilter;

    /**
     * Flag specifying to use shared (system) configurations
     *
     * @var bool
     */
    private $useSharedConfigsOnly;

    /**
     * EOL style for conent modifiers
     *
     * @var string
     */
    private $eol = "\r\n";

    /**
     * Declared modules attributes
     *
     * @var array
     */
    private $aAttributes = array();

    /**
     * Attribute handler
     *
     * @var array
     */
    private $aAttributeHandlers = array();

    /**
     * Attribute handler result
     *
     * @var mixed
     */
    private $attrHandlerResult;

    /**
     * Local mode flag
     *
     * @var bool
     */
    private $isLocalMode = FALSE;

    /**
     * Contains registring module ids
     *
     * @var array
     */
    private $aCollected = array();

    /**
     * Collect flag
     *
     * @var bool
     */
    private $collect = FALSE;

    /**
     * Fake registration mode flag
     *
     * @var bool
     */
    private $fakeRegistration = FALSE;

    /**
     * Array having keys as install Ids and values as arrays of modules having same 'id_install' attribute
     *
     * @var array
     * @see AMI_ModDeclarator::getModIdsByInstallId()
     */
    private $aInstallIdXModId = array();

    /**
     * Returns an instance of AMI_ModDeclarator.
     *
     * @param  array $aArgs  Constructor arguments
     * @return AMI_ModDeclarator
     * @amidev
     */
    public static function getInstance(array $aArgs = array()){
        if(is_null(self::$oInstance)){
            self::$oInstance = new AMI_ModDeclarator();
        }
        return self::$oInstance;
    }

    /**
     * Starts each config declaration.
     *
     * @param  string $section  Section name
     * @param  string $tabOrder  Module tab order
     * @return void
     */
    public function startConfig($section, $tabOrder = ''){
        $this->lastSection = $section;
        $this->tabOrder = (int)$tabOrder;
    }

    /**
     * Starts/stops collection registring module ids.
     *
     * @param  bool $collect           Collect flag
     * @param  bool $fakeRegistration  Fake registtration flag
     * @return void
     * @amidev Temporary?
     */
    public function collectRegistration($collect, $fakeRegistration = FALSE){
        $this->aCollected = array();
        $this->collect = (bool)$collect;
        $this->fakeRegistration = (bool)$fakeRegistration;
    }

    /**
     * Returns collected module ids.
     *
     * @return array
     * @amidev Temporary?
     */
    public function getCollected(){
        return $this->aCollected;
    }

    /**
     * Registers module in Amiro.CMS.
     *
     * @param  string $hypermod     Hypermodule
     * @param  string $config       Configuration
     * @param  string $modId        Module id
     * @param  string $parentModId  Parent module id
     * @param  int    $flags        Flags:
     *                              self::INTERFACE_ADMIN  - flag specifying that module has admin interface,
     *                              self::INTERFACE_FRONT  - flag specifying that module has front interface,
     *                              self::SET_TABLE_NAME   - flag specifying to use non-canonical table name,
     *                              self::HAS_ASSOC_MODELS - flag specifying that module has associated models.
     * @return void
     */
    public function register($hypermod, $config, $modId, $parentModId = '', $flags = self::INTERFACE_ADMIN){
        // *self::INTERFACE_BOTH   - flag specifying that module has admin/front interface,
        global $HOST_PATH;

        if($this->collect){
            $this->aCollected[] = $modId;
        }
        if($this->fakeRegistration){
            return;
        }
        if(!AMI::validateModId($hypermod)){
            trigger_error("Invalid hypermodule format '{$hypermod}'", E_USER_ERROR);
        }
        if(!AMI::validateModId($config)){
            trigger_error("Invalid configuration format '{$config}'", E_USER_ERROR);
        }
        if(
            $this->useSharedConfigsOnly &&
            !($flags & self::IS_SYS) &&
            'ami_multifeeds5' !== $hypermod &&
            !in_array($modId, array('news_archive', 'blog_archive', 'photoalbum_data_exchange'))
        ){
            return;
        }
        $oMod = $this->oCore->declareModule($this->lastSection, $hypermod, $config, $modId, $parentModId, (bool)($flags & self::INTERFACE_FRONT));
        $this->aMods[$modId] = array($hypermod, $config);
        $this->aSections[$modId] = $this->lastSection;
        $this->aTabOrders[$modId] = $this->tabOrder;
        if($parentModId !== ''){
            $this->aParents[$modId] = $parentModId;
        }
        /*
        if(AMI_Registry::get('ami/isFastMode', FALSE)){
            return;
        }
        */
        $key = 'AMI/HyperConfig/DataSource/' . $hypermod . '/' . $config;
        if(!AMI_Registry::exists($key)){
            AMI_Registry::set($key, $modId);
        }
        if($this->isFullCore){
            if($flags & self::INTERFACE_FRONT){
                $oMod->SetTableSortAdvFields(array('position' => self::SORT_FORBID_DESC));
            }
            if(!($flags & self::INTERFACE_ADMIN)){
                $oMod->SetAdminAllowed(FALSE);
            }
            /*
            if($flags & self::HAS_UNCANONICAL_TABLE_NAME){
                $tableName = AMI::getResourceModel($modId . '/table')->getTableName();
                if(mb_strpos($tableName, 'cms_', 0, 'utf-8') === 0){
                    $tableName = mb_substr($tableName, 4);
                }
                $oMod->SetTableName($tableName);
            }
            if($flags & self::HAS_ASSOC_MODELS){
                // Add associated models
                foreach(AMI::getResourceModel($modId . '/table')->getAssociatedTableNames() as $tableName){
                    $this->oCore->AddChildTable($oMod, $tableName);
                }
            }
            */
        }else{
            $aTables = array();
            // collect tables for admin fast async entry point rights support
            if($flags & self::HAS_UNCANONICAL_TABLE_NAME){
                // $tableName = AMI::getResourceModel($modId . '/table')->getTableName();
                // $aTables[] = $tableName;
            }else{
                $aTables[] = 'cms_' . $modId;
            }
            /*
            if($flags & self::HAS_ASSOC_MODELS){
                foreach(AMI::getResourceModel($modId . '/table')->getAssociatedTableNames() as $tableName){
                    $aTables[] = $tableName;
                }
            }
            */
            $this->oCore->setModTables($modId, $aTables);
        }

        if($flags & self::IS_SYS){
            $this->setAttr($modId, 'id_pkg', 'amiro.system');
            $this->setAttr($modId, 'id_install', 0);
        }

        $this->setAttr($modId, 'flags', $flags);

        /*
        $metaClassName = AMI::getClassPrefix($hypermod) . '_' . AMI::getClassPrefix($config) . '_Meta';

        foreach(
            array(
                "{$HOST_PATH}_shared/code/hyper_modules/code/",
                $GLOBALS['ROOT_PATH'] . '_local/modules/code/'
            ) as $path
        ){
            $file = "{$path}{$metaClassName}.php";
            if(file_exists($file)){
                require_once $file;
                $oMeta = new $metaClassName; // @var AMI_HyperConfig_Meta
                if($oMeta->hasCommonDataSource()){
                    $key = 'AMI/DataSource/' . $hypermod . '/' . $config;
                    if(!AMI_Registry::exists($key)){
                        AMI_Registry::set($key, $modId);
                    }
                }
                return;
            }
        }
        */
    }

    /**
     * Sets module interface visibility.
     *
     * @param  string $modId  Module id
     * @param  int    $flags  Flags: AMI_ModDeclarator::INTERFACE_*
     * @return void
     */
/*
    public function setInterface($modId, $flags){
        $this->check($modId);
        if($this->isFullCore){
            $oMod = &$this->oCore->getModule($modId);
            $oMod->SetAdminAllowed((bool)($flags & AMI_ModDeclarator::INTERFACE_ADMIN));
            $oMod->SetFrontAllowed((bool)($flags & AMI_ModDeclarator::INTERFACE_FRONT));
        }
    }
*/

    /**
     * Returns registered modules ids.
     *
     * @param  string $hypermod  Hypermodule
     * @param  string $config    Config
     * @param  string $section   Section
     * @return array
     */
    public function getRegistered($hypermod = '', $config = '', $section = ''){
        $aModIds = array_keys($this->aMods);
        if($section !== ''){
            $this->sectionFilter = $section;
            $aModIds = array_filter($aModIds, array($this, 'cbFilterSection'));
        }
        $noHypermod = $hypermod === '';
        $noConfig = $hypermod === '';
        if($noHypermod && $noConfig){
            // All registered modules will be returned
            return $aModIds;
        }elseif($noConfig){
            // Filter registered modules by hypermodule
            $this->hypermodFilter = $hypermod;
            $this->configFilter = '';
            return array_filter($aModIds, array($this, 'cbFilterRegistred'));
        }elseif($noHypermod){
            trigger_error('Registred modules cannot be filtered by configuration only', E_USER_ERROR);
        }
        $this->hypermodFilter = $hypermod;
        $this->configFilter = $config;
        return array_filter($aModIds, array($this, 'cbFilterRegistred'));
    }

    /**
     * Returns registered sections/hypermodules/configurations.
     *
     * @param  string $type      Type
     * @param  string $hypermod  Hypermod
     * @return array
     * @see    ModManager_FilterModelAdm::__construct()
     * @amidev
     */
    public function getRegisteredEnv($type = '', $hypermod = ''){
        $aResult = array();
        switch($type){
            case 'sections':
                $aResult = $this->aSections;
                break;
            case 'hypermodules':
                $index = 0;
            case 'configurations':
                if(!isset($index)){
                    $index = 1;
                }
                foreach($this->aMods as $aHypConf){
                    if($index ? $aHypConf[0] == $hypermod : TRUE){
                        $aResult[] = $aHypConf[$index];
                    }
                }
                break;
        }
        $aResult = array_unique($aResult);
        return $aResult;
    }

    /**
     * Returns hypermodule and config by module id.
     *
     * @param  string $modId  Module id
     * @return array  Array ($hypermod, $config)
     */
    public function getHyperData($modId){
        if(isset($this->aMods[$modId])){
            return $this->aMods[$modId];
        }
        trigger_error("Module '{$modId}' is not declared", E_USER_ERROR);
    }

    /**
     * Returns section by module id.
     *
     * @param  string $modId  Module id
     * @return string
     */
    public function getSection($modId){
        if(AMI_Registry::exists('AMI/override/forceModSection')){
            return AMI_Registry::get('AMI/override/forceModSection');
        }
        if(isset($this->aSections[$modId])){
            return $this->aSections[$modId];
        }
        trigger_error("Module '{$modId}' is not declared", E_USER_ERROR);
    }

    /**
     * Returns sections.
     *
     * @return array
     * @since  6.0.4
     */
    public function getSections(){
        return array_values($this->aSections);
    }

    /**
     * Returns tab order by module id.
     *
     * @param  string $modId  Module id
     * @return string
     */
    public function getTabOrder($modId){
        if(isset($this->aTabOrders[$modId])){
            return $this->aTabOrders[$modId];
        }
        trigger_error("Module '{$modId}' is not declared", E_USER_ERROR);
    }

    /**
     * Returns parent module id by module id.
     *
     * @param  string $modId  Module id
     * @return mixed  Parent module id or null
     */
    public function getParent($modId){
        return isset($this->aParents[$modId]) ? $this->aParents[$modId] : null;
    }

    /**
     * Return children module Ids for passed module.
     *
     * @param  string $modId  Module Id
     * @return array
     * @since  6.0.2
     */
    public function getChildren($modId){
        $aParents = $this->aParents;
        if(is_null($this->getParent($modId))){
            $aChildren = array();
            do{
                $childModId = array_search($modId, $aParents);
                $res = $childModId !== FALSE;
                if($res){
                    $aChildren[$childModId] = $childModId;
                    unset($aParents[$childModId]);
                }
            }while($res);
        }

        return $aChildren;
    }

    /**
     * Get all submodule Ids of specified module.
     *
     * @param  string $parentModId  Parent modId
     * @return array
     */
    public function getSubmodules($parentModId){
        $aResult = array();
        if(!$this->isRegistered($parentModId)){
            return $aResult;
        }
        $aModules = $this->getRegistered();
        foreach($aModules as $modId){
            if($this->getParent($modId) == $parentModId){
                $aResult[] = $modId;
            }
        }
        return $aResult;
    }

    /**
     * Returns TRUE if module is registered.
     *
     * @param  string $modId  Module id
     * @return bool
     */
    public function isRegistered($modId){
        return isset($this->aMods[$modId]);
    }

    /**
     * Sets registered module attribute.
     *
     * @param  string $modId  Module id
     * @param  string $name   Attribute name
     * @param  mixed  $value  Attribute value
     * @return mixed
     */
    public function setAttr($modId, $name, $value){
        // $this->check($modId);
        if(!$this->isRegistered($modId)){
            return NULL;
        }
        $return = null;
        if($this->handleAttr('set', $modId, $name, $value, $return)){
            return $return;
        }
        if(empty($this->aAttributes[$modId])){
            $this->aAttributes[$modId] = array();
        }
        $this->aAttributes[$modId][$name] = $value;
        return $return;
    }

    /**
     * Returns TRUE if registered module attribute is present.
     *
     * @param  string $modId  Module id
     * @param  string $name   Attribute name
     * @return bool
     */
    public function issetAttr($modId, $name){
        $this->check($modId);
        $value = null;
        $return = null;
        if($this->handleAttr('isset', $modId, $name, $value, $return)){
            return $return;
        }
        return isset($this->aAttributes[$modId]) && array_key_exists($name, $this->aAttributes[$modId]);
    }

    /**
     * Returns registered module attribute.
     *
     * @param  string $modId         Module id
     * @param  string $name          Attribute name
     * @param  mixed  $defaultValue  Default value to return
     * @return mixed
     */
    public function getAttr($modId, $name, $defaultValue = null){
        $this->check($modId);
        $return = null;
        if($this->handleAttr('get', $modId, $name, $defaultValue, $return)){
            return $return;
        }
        return
            isset($this->aAttributes[$modId]) && array_key_exists($name, $this->aAttributes[$modId])
                ? $this->aAttributes[$modId][$name]
                : $defaultValue;
    }

    /**
     * Drops registered module attribute.
     *
     * @param  string $modId  Module id
     * @param  string $name   Attribute name
     * @return bool
     */
    public function dropAttr($modId, $name){
        $this->check($modId);
        $value = null;
        $return = true;
        if($this->handleAttr('drop', $modId, $name, $value, $return)){
            return $return;
        }
        if(!empty($this->aAttributes[$modId])){
            unset($this->aAttributes[$modId][$name]);
            if(!sizeof($this->aAttributes[$modId])){
                unset($this->aAttributes[$modId]);
            }
        }
        return $return;
    }

    /**
     * Returns module Id by admin file name or null if no admin_link attribute was set.
     *
     * @param  string $modLink  Admin script file name
     * @return string
     */
    public function getModIdByLink($modLink){
        $return = null;
        foreach($this->aAttributes as $modId => $aModAttributes){
            if(isset($aModAttributes['admin_link']) && ($aModAttributes['admin_link'] == $modLink)){
                $return = $modId;
                break;
            }
        }
        return $return;
    }

    /**
     * Returns AMI_Module.
     *
     * @param  string $modId  Module id
     * @return AMI_Module
     */
    public function getModule($modId){
        if(!$this->isFullCore){
            trigger_error("Full environment is required", E_USER_ERROR);
        }
        if(!isset($this->aModules[$modId])){
            $this->check($modId);
            $oMod50 = &$this->oCore->getModule($modId);
            $this->setupAsyncInterface($oMod50, FALSE);
            $this->aModules[$modId] = new AMI_Module($oMod50);
        }
        $oMod = $this->aModules[$modId];
        $oMod->allowToSetOptions(is_null($this->aAllowedOptions) || in_array($modId, $this->aAllowedOptions));
        // temporary hack
        /*
        if($this->isLocalMode){
            $oMod->setProperty('dont_show_in_pm', TRUE);
        }
        */
        return $oMod;
    }

    /**
     * Sets shared/local mode.
     *
     * Temporary hack to disable local modules in Site Manager.
     *
     * @param  bool $isLocal  Local mode flag
     * @return void
     * @amidev
     */
    public function setMode($isLocal){
        $this->isLocalMode = (bool)$isLocal;
    }

    /**
     * Executes modules declaration having same passed section.
     *
     * @param  string $section  Section
     * @param  array  $aScope   PHP variables scope
     * @return void
     * @amidev
     */
    public function execSection($section, array $aScope = null){
        if(!is_array($aScope)){
            $aScope = array();
        }
        $aScope += array('oDeclarator' => $this);
        $path = AMI_Registry::get('path/hyper_local') . 'declaration/declares.php';
        $content = '';
        foreach($this->aSections as $modId => $modSection){
            if($section !== $modSection){
                continue;
            }
            if($content === ''){
                $content = file_get_contents($path);
                if($content === FALSE){
                    break;
                }
            }
            $opener = "// [{$modId}] {{$this->eol}";
            $closer = "// } [{$modId}]{$this->eol}";
            $start = mb_strpos($content, $opener);
            $end = mb_strpos($content, $closer);
            if(
                ($start !== FALSE && $end === FALSE) ||
                ($start === FALSE && $end !== FALSE)
            ){
                triogger_error("Cannot parse local declaration at '{$path}'", E_USER_ERROR);
            }
            if($start !== FALSE && $end !== FALSE){
                $code = mb_substr($content, $start, $end + mb_strlen($closer) - $start);
                $executor = new AMI_PHPExecutor($code);
                $executor->run($aScope);
            }
        }
    }

    /**
     * Sets up async interface possibility for base and other instances.
     *
     * @param  CMS_Module &$oMod       Module
     * @param  bool       $isProperty  Flag specifying to setup property or option
     * @return void
     * @amidev
     */
    public function setupAsyncInterface(CMS_Module &$oMod, $isProperty = TRUE){
        $isCoreV5 = $this->getAttr($oMod->GetName(), 'core_v5');
        if($isProperty){
            // setting 'admin_request_types' property
            $aTypes = array('ajax');
            if($isCoreV5){
                $aTypes[] = 'plain';
            }
            $oMod->SetProperty('admin_request_types', $aTypes);
        }else{
            // setting 'engine_version' option
            $oMod->SetOption('engine_version', $isCoreV5 ? '0303' : '0600');
        }
    }

    /**
     * Sets allowed modules for setting options.
     *
     * @param  array $aAllowed  Allowed modules to set options
     * @return void
     * @amidev
     */
    public function setAllowedOptions(array $aAllowed = null){
        $this->aAllowedOptions = $aAllowed;
    }

    /**
     * Replaces core/declarator iustance for distributive cleaner.
     *
     * @param  AMI_ModDeclarator $oInstance  Previous declarator
     * @return AMI_ModDeclarator|null
     * @amidev
     */
    public static function replaceInstance(AMI_ModDeclarator $oInstance = null){
        $oCurrentInstance = clone self::$oInstance;
        self::$oInstance = new AMI_ModDeclarator();
        if(is_null($oInstance)){
            return $oCurrentInstance;
        }else{
            self::$oInstance = $oInstance;
        }
    }

    /**
     * Validates declaration.
     *
     * @return void
     * @amidev
     */
    public function validate(){
        $aInvalidModIds = array();
        foreach(array_keys($this->aMods) as $modId){
            if(
                !isset($this->aParents[$modId]) && (
                    is_null($this->getAttr($modId, 'id_pkg')) ||
                    is_null($this->getAttr($modId, 'id_install'))
                )
            ){
                $aInvalidModIds[] = $modId;
            }
        }
        if(sizeof($aInvalidModIds)){
            if(AMI::isSingletonInitialized('core')){
                $oCore = AMI::getSingleton('core');
            }
            foreach($aInvalidModIds as $modId){
                $aChildren = array();###
                do{
                    $childModId = array_search($modId, $this->aParents);
                    $res = $childModId !== FALSE;
                    if($res){
                        $aChildren[$childModId] = $childModId;
                        unset($this->aParents[$childModId]);
                    }
                }while($res);
                if(isset($oCore)){
                    $oCore->setInstalled($modId, FALSE);
                }
                $aChildren[] = $modId;
                foreach($aChildren as $modId){
                    unset(
                        $this->aMods[$modId],
                        $this->aSections[$modId],
                        $this->aTabOrders[$modId],
                        $this->aAttributes[$modId]
                    );
                }
            }
            AMI_Registry::push('disable_error_mail', TRUE);
            trigger_error(
                "Missing obligatory attributes id_pkg/id_install for modules '" . implode("', '", $aInvalidModIds) . "'",
                E_USER_WARNING
            );
            AMI_Registry::pop('disable_error_mail');
        }
    }

    /**
     * Returns module Ids by install Id.
     *
     * @param  int $installId  Install Id
     * @return array
     * @amidev Temporary
     */
    public function getModIdsByInstallId($installId){
        $installId = (int)$installId;
        return
            isset($this->aInstallIdXModId[$installId])
            ? $this->aInstallIdXModId[$installId]
            : array();
    }

    /**
     * Checks if module is registered or triggers fatal error.
     *
     * @param  string $modId  Module id
     * @return void
     */
    private function check($modId){
        if(!$this->isRegistered($modId) && !$this->fakeRegistration){
            d::trace();
            trigger_error("Unregistered module '" . $modId . "'", E_USER_ERROR);
        }
    }

    /**
     * Constructor.
     */
    private function __construct(){
        global $Core, $sys;

        $this->oCore = $Core;
        $this->isFullCore = $Core instanceof CMS_Core;
        $this->useSharedConfigsOnly = !empty($sys['disable_user_scripts']);

        $this->setAttrHandler('admin_link', array($this, 'handleAttrAdminLink'));
        $this->setAttrHandler('data_source', array($this, 'handleAttrDataSource'));
        $this->setAttrHandler('assoc_db_tables', array($this, 'handleAttrAssocDBTables'));
        $this->setAttrHandler('db_table', array($this, 'handleAttrDBTable'));
        $this->setAttrHandler('id_install', array($this, 'handleAttrInstallId'));
    }

    /**
     * Cloning is forbidden.
     */
    private function __clone(){
    }

    /**
     * Sets/drops attribute handler.
     *
     * @param  string   $name      Attribute name
     * @param  callback $callback  Callback
     * @return void
     */
    private function setAttrHandler($name, $callback){
        if(is_null($callback)){
            unset($this->aAttributeHandlers[$name]);
        }else{
            $this->aAttributeHandlers[$name] = $callback;
        }
    }

    /**
     * Handles attribute manipulations.
     *
     * @param  string $action   Attribute action: 'set'|'isset'|'get'|'drop'
     * @param  string $modId    Module id
     * @param  string $name     Attribute name
     * @param  mixed  &$value   Attribute value
     * @param  mixed  &$return  Return value that will be used to return from attribute manipulating method
     * @return bool   TRUE if attribute related method could be interrupted, FALSE otherwise
     */
    private function handleAttr($action, $modId, $name, &$value, &$return){
        $res = FALSE;
        if(isset($this->aAttributeHandlers[$name])){
            $oArgs = new stdClass;
            $oArgs->action = $action;
            $oArgs->modId  = $modId;
            $oArgs->name   = $name;
            $oArgs->value  = &$value;
            $oArgs->return = &$return;
            $res = call_user_func($this->aAttributeHandlers[$name], $oArgs);
        }
        return $res;
    }

    /**#@+
     * Attribute handler.
     *
     * @see    AMI_ModDeclarator::setAttrHandler()
     * @see    AMI_ModDeclarator::handleAttr()
     */

    /**
     * Handles 'admin_link' attribute manipulations.
     *
     * @param  stdClass $oArgs  Parameters
     * @return bool
     * @todo   Getter
     */
    private function handleAttrAdminLink(stdClass $oArgs){
        if($oArgs->action === 'set'){
            $oMod = &$this->oCore->getModule($oArgs->modId);
            $oMod->setAdminLink($oArgs->value);
        }elseif($oArgs->action === 'get'){
            $oMod = &$this->oCore->getModule($oArgs->modId);
            $this->return = $oMod->getAdminLink($oArgs->value);
        }
        return TRUE;
    }

    /**
     * Handles 'data_source' attribute manipulations.
     *
     * @param  stdClass $oArgs  Parameters
     * @return bool
     */
    private function handleAttrDataSource(stdClass $oArgs){
        switch($oArgs->action){
            case 'set':
                if(!$this->isRegistered($oArgs->value)){
                    $oArgs->return = FALSE;
                    return TRUE; // break common attribute manipulator
                }
                if(isset($this->aModDataSources[$oArgs->value])){
                    $oArgs->value = $this->aModDataSources[$oArgs->value];
                }
                if(
                    $oArgs->modId === $oArgs->value ||
                    (isset($this->aModDataSources[$oArgs->modId]) && $this->aModDataSources[$oArgs->modId] === $oArgs->value)
                ){
                    $oArgs->return = TRUE;
                    return TRUE; // break common attribute manipulator
                }
                if(in_array($oArgs->modId, $this->aModDataSources)){
                    trigger_error("Data source cannot be set for '" . $oArgs->modId . "' because other modules have it as data source", E_USER_WARNING);
                    $oArgs->return = FALSE;
                    return TRUE; // break common attribute manipulator
                }
                $this->aModDataSources[$oArgs->modId] = $oArgs->value;
                if($this->isFullCore){
                    $oMod = &$this->oCore->getModule($oArgs->value);
                    $tableName = $oMod->GetTableName();
                    if(mb_strpos($tableName, $oMod->TablePrefix) === 0){
                        $tableName = mb_substr($tableName, mb_strlen($oMod->TablePrefix));
                    }
                    $oMod = &$this->oCore->GetModule($oArgs->modId);
                    $oMod->TableInit($tableName);
                }
                break;
            case 'isset':
                $oArgs->return = isset($this->aModDataSources[$oArgs->modId]);
                break;
            case 'get':
                $oArgs->return = isset($this->aModDataSources[$oArgs->modId]) ? $this->aModDataSources[$oArgs->modId] : $oArgs->modId;
                break;
            case 'drop':
                trigger_error("Data source cannot be dropped for '" . $oArgs->modId . "'", E_USER_WARNING);
                break;
        }
        return TRUE;
    }

    /**
     * Handles 'assoc_db_tables' attribute manipulations.
     *
     * @param  stdClass $oArgs  Parameters
     * @return bool
     */
    private function handleAttrAssocDBTables(stdClass $oArgs){
        if($oArgs->action === 'set'){
            if($this->isFullCore){
                foreach($oArgs->value as $tableName){
                    $oMod = &$this->oCore->getModule($oArgs->modId);
                    $this->oCore->AddChildTable($oMod, $tableName);
                }
            }else{
                $this->oCore->setModTables($oArgs->modId, $oArgs->value);
            }
        }
        return FALSE;
    }

    /**
     * Handles 'db_table' attribute manipulations.
     *
     * @param  stdClass $oArgs  Parameters
     * @return bool
     */
    private function handleAttrDBTable(stdClass $oArgs){
        if($oArgs->action === 'set'){
            if($this->isFullCore){
                $oMod = &$this->oCore->getModule($oArgs->modId);
                $tableName = $oArgs->value;
                if(mb_strpos($tableName, 'cms_', 0, 'utf-8') === 0){
                    $tableName = mb_substr($tableName, 4);
                }
                $oMod->SetTableName($tableName);
                $this->oCore->AddTableToList($oMod);
            }else{
                $this->oCore->setModTables($oArgs->modId, array($oArgs->value));
            }
        }
        return FALSE;
    }

    /**
     * Handles 'id_install' attribute manipulations.
     *
     * @param  stdClass $oArgs  Parameters
     * @return bool
     */
    private function handleAttrInstallId(stdClass $oArgs){
        if($oArgs->action === 'set'){
            $installId = (int)$oArgs->value;
            if($installId){
                if(empty($this->aInstallIdXModId[$installId])){
                    $this->aInstallIdXModId[$installId] = array();
                }
                $this->aInstallIdXModId[$installId][] = $oArgs->modId;
            }
        }
        return FALSE;
    }

    /**#@-*/

    /**
     * Registred modules filterring callback.
     *
     * @param  string $modId  Module id
     * @return bool
     * @see    AMI_ModDeclarator::getRegistered()
     */
    private function cbFilterSection($modId){
        $allowed = $this->sectionFilter === $this->aSections[$modId];
        return $allowed;
    }

    /**
     * Registred modules filterring callback.
     *
     * @param  string $modId  Module id
     * @return bool
     * @see    AMI_ModDeclarator::getRegistered()
     */
    private function cbFilterRegistred($modId){
        $allowed = $this->aMods[$modId][0] === $this->hypermodFilter;
        if($allowed && $this->configFilter !== ''){
            $allowed = $this->aMods[$modId][1] === $this->configFilter;
        }
        return $allowed;
    }
}

/**
 * Module class used to describe options, properies and interface to change options (rules).
 *
 * @package Module
 * @since   5.14.4
 */
final class AMI_Module{
    /**#@+
     * Rule type
     *
     * @var int
     */

    /**
     * Boolean
     */
    const RLT_BOOL              = 10;

    /**
     * Unsigned int
     */
    const RLT_UINT              = 20;

    /**
     * Signed int
     */
    const RLT_SINT              = 25;

    /**
     * Float
     */
    const RLT_FLOAT             = 40;

    /**
     * String
     */
    const RLT_STRING            = 30;

    /**
     * Text (multiline string)
     */
    const RLT_TEXT              = 45;

    /**
     * E-mail
     */
    const RLT_EMAIL             = 50;

    /**
     * Enumerated list
     */
    const RLT_ENUM              = 60;

    /**
     * Multiselectable enumerated list
     */
    const RLT_ENUM_MULTI        = 70;

    /**
     * Date interval
     */
    const RLT_DATE_INTERVAL     = 130;

    /**
     * Positive date interval
     */
    const RLT_DATE_INTERVAL_POS = 140;

    /**
     * Negative date interval
     */
    const RLT_DATE_INTERVAL_NEG = 150;

    /**
     * Splitter
     */
    const RLT_SPLITTER          = 20010;

    /**#@-*/

    /**#@+
     * Rule data
     *
     * @var int
     */

    /**
     * No rule data
     */
    const RLC_NONE   = -1;

    /**
     * Empty value for enumerated list
     */
    const RLC_EMPTY  = -222222;

    /**#@-*/

    /**
     * Old core module
     *
     * @var CMS_Module
     */
    private $oMod;

    /**
     * Rule module
     *
     * @var CMS_ModuleRules
     */
    private $oRuleMod;

    /**
     * Flag specifying settin options is allowed
     *
     * @var bool
     */
    private $allowToSetOptions = FALSE;

    /**
     * Supported rule types
     *
     * @var array
     */
    private $aRuleTypes = array(
        self::RLT_BOOL, self::RLT_UINT, self::RLT_SINT, self::RLT_FLOAT, self::RLT_STRING, self::RLT_TEXT,
        self::RLT_EMAIL, self::RLT_ENUM, self::RLT_ENUM_MULTI,
        self::RLT_DATE_INTERVAL, self::RLT_DATE_INTERVAL_POS, self::RLT_DATE_INTERVAL_NEG,
        self::RLT_SPLITTER
    );

    /**
     * Sets module property.
     *
     * @param  string $name   Property name
     * @param  mixed  $value  Property value
     * @return AMI_Module
     */
    public function setProperty($name, $value){
        $this->oMod->SetProperty($name, $value);
        return $this;
    }

    /**
     * Sets module option.
     *
     * @param  string $name   Option name
     * @param  mixed  $value  Option value
     * @return AMI_Module
     */
    public function setOption($name, $value){
        if($this->allowToSetOptions){
            $this->oMod->SetOption($name, $value);
        }
        return $this;
    }

    /**
     * Adds module rule to manage oprions.
     *
     * @param  string $name          Option name
     * @param  int    $type          Rule type<br />
     *                               Possible values:<br />
     *                               AMI_Module::RLT_BOOL, AMI_Module::RLT_UINT,<br />
     *                               AMI_Module::RLT_SINT, AMI_Module::RLT_FLOAT,<br />
     *                               AMI_Module::RLT_STRING, AMI_Module::RLT_TEXT,<br />
     *                               AMI_Module::RLT_EMAIL, AMI_Module::RLT_ENUM,<br />
     *                               AMI_Module::RLT_ENUM_MULTI_ARRAY,<br />
     *                               AMI_Module::RLT_DATE_PERIOD,<br />
     *                               AMI_Module::RLT_DATE_PERIOD_POSITIVE,<br />
     *                               AMI_Module::RLT_DATE_PERIOD_NEGATIVE,<br />
     *                               AMI_Module::RLT_SPLITTER
     * @param  mixed  $aOptions      Module rule options
     * @param  mixed  $defaultValue  Module default rule value
     * @param  mixed  $aGroups       Module rule groups
     * @return AMI_Module
     * @todo   Specblock rules support
     * @todo   Complex rules (callbacks, etc)
     */
    public function addRule($name, $type, $aOptions = AMI_Module::RLC_NONE, $defaultValue = NULL, $aGroups = array()){
        if(in_array($type, $this->aRuleTypes)){
            if(!$this->oRuleMod && $this->oMod->IsInstalled()){
                $modId = $this->oMod->Name;
                $oCoreRules = &AMI_Registry::get('oAMICoreRules', FALSE);
                if(!is_object($oCoreRules)){
                    return $this;
                }
                $oCoreRules->setCurrentOwner($this->oMod->GetOwnerName());
                if($oCoreRules->issetModule($modId)){
                    $this->oRuleMod = &$oCoreRules->getModule($modId);
                }else{
                    $this->oRuleMod = &$oCoreRules->addModule($modId);
                    $this->oRuleMod->removeRules();
                }
                $this->oRuleMod->addCaptions("_local/_admin/templates/lang/options/{$modId}_rules_captions.lng");
                $this->oRuleMod->addCaptions("_local/_admin/templates/lang/options/{$modId}_rules_values.lng", TRUE);
            }

            /**
             * Fires before adding a module rule
             *
             * @event      on_before_add_{$ruleName}_rule $modId
             * @eventparam string           name          Rule name
             * @eventparam string           type          Rule type
             * @eventparam mixed            aOptions      Rule options
             * @eventparam mixed            defaultValue  Default rule value
             * @eventparam AMI_Module       oMod          Module object
             * @eventparam CMS_ModuleRules  oRuleMod      Module rules object
             */
            $aEvent = array(
                'name'          => $name,
                'type'          => $type,
                'aOptions'      => &$aOptions,
                'defaultValue'  => &$defaultValue,
                'oMod'          => $this,
                'oRuleMod'      => $this->oRuleMod
            );
            AMI_Event::fire('on_before_add_{' . $name . '}_rule', $aEvent, $this->oMod->Name);

            if(is_array($aOptions)){
                if(!isset($aOptions['style_class'])){
                    $aOptions['style_class'] = '';
                }

                if(!isset($aOptions['custom_style_class'])){
                    $aOptions['custom_style_class'] = '';
                }

                if($aOptions['style_class']){
                    if(isset($this->oRuleMod->Captions[$name . '_' . $aOptions['style_class']])){
                        $this->oRuleMod->Captions[$name] = $this->oRuleMod->Captions[$name . '_' . $aOptions['style_class']];
                    }
                }
            }

            $this->oRuleMod->addRule(CMS_CoreRules::RLR_ANY, CMS_CoreRules::VIEW_MODE_NOVICE, $name, $type, $aOptions, $defaultValue, TRUE, $aGroups);
        }else{
            trigger_error("Unsupported module rule type {$type}", E_USER_WARNING);
        }
        return $this;
    }

    /**
     * Finalizes rules description.
     *
     * @return void
     */
    public function finalize(){
        if(is_object($this->oRuleMod)){
            $this->oRuleMod->finishModule();
        }
    }

    /**
     * Sets allowing setting options.
     *
     * @param  bool $allow  Flag
     * @return void
     * @amidev
     */
    public function allowToSetOptions($allow){
        $this->allowToSetOptions = (bool)$allow;
    }

    /**
     * Constructor.
     *
     * @param CMS_Module &$oMod  Module
     * @amidev
     */
    public function __construct(CMS_Module &$oMod){
        $this->oMod = &$oMod;
    }

    /**
     * Sets rule module.
     *
     * @param  CMS_ModuleRules &$oRuleMod  Rule module
     * @return void
     * @amidev
     */
    public function setRuleMod(CMS_ModuleRules &$oRuleMod){
        $this->oRuleMod = &$oRuleMod;
    }
}

/**
 * PHP code executor.
 *
 * Used for isolate PHP code from object scopes.
 *
 * @package Service
 * @amidev
 */
final class AMI_PHPExecutor{
    /**
     * PHP code
     *
     * @var string
     */
    private $code;

    /**
     * Constructor.
     *
     * @param string $code  PHP code
     */
    public function __construct($code){
        $this->code = $code;
    }

    /**
     * Executes PHP code.
     *
     * @param  array &$aScope  Vars
     * @return mixed
     */
    public function run(array &$aScope){
        extract($aScope);
        return eval($this->code);
    }
}
