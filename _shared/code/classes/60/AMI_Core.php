<?php
/**
 * Fake core.
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Core
 * @version   $Id: AMI_Core.php 48490 2014-03-06 11:45:54Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary?
 */

/**
 * Core interface.
 *
 * @package Core
 * @since   x.x.x
 * @amidev  Temporary?
 */
interface AMI_iCore{
    /**
     * Sets core module installation flag.
     *
     * @param  string $modId         Module id
     * @param  bool   $isIntstalled  Module installation flag
     * @return AMI_iCore
     */
    public function setInstalled($modId, $isIntstalled);

    /**
     * Returns true if module is installed.
     *
     * @param  string $modId  Module id
     * @return bool
     */
    public function isInstalled($modId);

    /**
     * Returns module property value.
     *
     * @param  mixed $modId  Module id
     * @param  mixed $name  Module property name
     * @return mixed
     */
    public function getModProperty($modId, $name);

    /**
     * Sets module property value.
     *
     * @param  string $modId  Module id
     * @param  string $name  Module property name
     * @param  mixed $value  Module property value, null to unset
     * @return void
     */
    public function setModProperty($modId, $name, $value);

    /**
     * Returns true if module property is set.
     *
     * @param  string $modId  Module id
     * @param  string $name  Module property name
     * @return bool
     */
    public function issetModProperty($modId, $name);

    /**
     * Returns module option value.
     *
     * @param  mixed $modId  Module id
     * @param  mixed $name  Module option name
     * @return mixed
     */
    public function getModOption($modId, $name);

    /**
     * Returns separate module option value.
     *
     * @param  mixed $modId  Module id
     * @param  mixed $name  Module option name
     * @return mixed
     */
    public function getSingleModOption($modId, $name);

    /**
     * Sets module option value.
     *
     * @param  string $modId  Module id
     * @param  string $name  Module option name
     * @param  mixed $value  Module option value, null to unset
     * @return void
     */
    public function setModOption($modId, $name, $value);

    /**
     * Returns true if module option is set.
     *
     * @param  string $modId  Module id
     * @param  string $name  Module options name
     * @return bool
     */
    public function issetModOption($modId, $name);

    /**
     * Returns core module property value.
     *
     * @param  mixed $name  Module property name
     * @return mixed
     */
    public function getProperty($name);

    /**
     * Sets core module property value.
     *
     * @param  string $name  Module property name
     * @param  mixed $value  Module property value, null to unset
     * @return void
     */
    public function setProperty($name, $value);

    /**
     * Returns true if core module property is set.
     *
     * @param  string $name  Module property name
     * @return bool
     */
    public function issetProperty($name);

    /**
     * Returns core module option value.
     *
     * @param  mixed $name  Module option name
     * @return mixed
     */
    public function getOption($name);

    /**
     * Sets core module option value.
     *
     * @param  string $name  Module option name
     * @param  mixed $value  Module option value, null to unset
     * @return void
     */
    public function setOption($name, $value);

    /**
     * Returns true if core module option is set.
     *
     * @param  string $name  Module options name
     * @return bool
     */
    public function issetOption($name);

    /**
     * Returns CMS versions.
     *
     * AMI_iCore::getVersion($product) returns all versions for a specific product<br />
     * AMI_iCore::getVersion() returns all versions in an array
     *
     * @param  mixed $product  Product
     * @param  mixed $type     Type
     * @return mixed
     * @see    CMS_Core
     */
    public function getVersion($product = false, $type = false);

    /**
     * Check is user is system user or not.
     *
     * @param int $userId  User ID. Optional.
     * @amidev
     * @return bool
     */
    public function isSysUser($userId = false);

    /**
     * Sets core module admin link.
     *
     * @param  string $modId  Module id
     * @param  string $link   Link
     * @return AMI_iCoreModule
     */
    public function setAdminLink($modId, $link);

    /**
     * Returns core module admin link.
     *
     * @param  string $modId  Module id
     * @return string
     */
    public function getAdminLink($modId);

    /**
     * Returns module.
     *
     * @param string $modId  Module id
     * @return AMI_iCoreModule
     */
    public function &getModule($modId);
}

/**
 * Core.
 *
 * @package  Core
 * @resource response <code>AMI::getSingleton('core')</code>
 * @since    x.x.x
 * @amidev   Temporary?
 */
final class AMI_Core implements AMI_iCore{
    /**
     * @var    int
     * @see    AMI_Core::getRights()
     * @amidev Temporary
     */
    const SYS_RIGHTS_READ   = 0x04; // was SYS_R_RIGHT

    /**
     * @var    int
     * @see    AMI_Core::getRights()
     * @amidev Temporary
     */
    const SYS_RIGHTS_WRITE  = 0x02; // was SYS_W_RIGHT

    /**
     * @var    int
     * @see    AMI_Core::getRights()
     * @amidev Temporary
     */
    const SYS_RIGHTS_DELETE = 0x01; // was SYS_D_RIGHT

    /**
     * @var     int
     * @see    AMI_Core::getRights()
     * @amidev Temporary
     */
    const SYS_RIGHTS_ALL    = 0x07; // was SYS_A_RIGHT = SYS_R_RIGHT | SYS_W_RIGHT | SYS_D_RIGHT

    /**
     * Instance
     *
     * @var AMI_Core
     */
    private static $oInstance;

    /**
     * Installed modules list
     *
     * @var array
     */
    private $aInstalledMods;

    /**
     * Disabled modules list
     *
     * @var array
     */
    private $aDisabledMods;

    /**
     * Hypermodules struct
     *
     * @var array
     * @see AMI_Core::declareModule()
     */
    private $aHyperStruct = array();

    /**
     * Modules properties
     *
     * @var array
     */
    private $aProperties;

    /**
     * Modules options
     *
     * @var CMS_ModulesOptions
     */
    private $oOptions;

    /**
     * Versions
     *
     * @var array
     */
    private $aVersions;

    /**
     * Structure containg rights related data
     *
     * @var array
     */
    private $aSysData = array('tables' => array());

    /**
     * Array containing special admin links
     *
     * @var array
     */
    private $aAdminLinks = array();

    /**
     * Table names x module ids
     *
     * @var array
     */
    private $aTablesXModIds = array();

    /**
     * Returns an instance of AMI_Core.
     *
     * @param  array $aArgs  Constructor arguments
     * @return AMI_Core
     * @amidev
     */
    public static function getInstance(array $aArgs = array()){
        if(is_null(self::$oInstance)){
            self::$oInstance = new AMI_Core($aArgs[0]);
        }
        return self::$oInstance;
    }

    /**#@+
     * AMI_iCore interface implementation.
     */

    /**
     * Sets core module installation flag.
     *
     * @param  string $modId         Module id
     * @param  bool   $isIntstalled  Module installation flag
     * @return AMI_Core
     */
    public function setInstalled($modId, $isIntstalled){
        if($isIntstalled){
            unset($this->aDisabledMods[$modId]);
        }else{
            $this->aDisabledMods[$modId] = TRUE;
        }
        return $this;
    }

    /**
     * Returns true if module is installed.
     *
     * @param  string $modId  Module id
     * @return bool
     */
    public function isInstalled($modId){
        return
            empty($this->aDisabledMods[$modId]) &&
            in_array($modId, $this->aInstalledMods) &&
            isset($this->aProperties[$modId]);
    }

    /**
     * Returns module property value.
     *
     * @param  string $modId  Module id
     * @param  string $name   Module property name
     * @return mixed
     */
    public function getModProperty($modId, $name){
        return $this->aProperties[$modId]['Properties'][$name];
    }

    /**
     * Sets module property value.
     *
     * @param  string $modId  Module id
     * @param  string $name   Module property name
     * @param  mixed  $value  Module property value, null to unset
     * @return void
     */
    public function setModProperty($modId, $name, $value){
        if(is_null($value)){
            unset($this->aProperties[$modId]['Properties'][$name]);
        }else{
            $this->aProperties[$modId]['Properties'][$name] = $value;
        }
    }

    /**
     * Returns true if module property is set.
     *
     * @param  string $modId  Module id
     * @param  string $name   Module property name
     * @return bool
     */
    public function issetModProperty($modId, $name){
        return isset($this->aProperties[$modId]['Properties'][$name]);
    }

    /**
     * Returns module option value.
     *
     * @param  string $modId  Module id
     * @param  string $name   Module option name
     * @return mixed
     */
    public function getModOption($modId, $name){
        return $this->oOptions->getModParam($modId, $name);
    }

    /**
     * Returns separate module option value.
     *
     * @param  string $modId  Module id
     * @param  string $name   Module option name
     * @return mixed
     */
    public function getSingleModOption($modId, $name){
        $this->oOptions->readOption($value, $modId, $name);
        return $value;
    }

    /**
     * Save separate module option value.
     *
     * @param  string $modId           Module id
     * @param  string $name            Module option name
     * @param  string $value           Module option value
     * @param  string $serializedData  Module option serialized data
     * @return void
     */
    public function setSingleModOption($modId, $name, $value, $serializedData){
        $this->oOptions->writeOption($modId, $name, $value, $serializedData);
    }

    /**
     * Sets module option value.
     *
     * @param  string $modId  Module id
     * @param  string $name   Module option name
     * @param  mixed  $value  Module option value, null to unset
     * @return void
     */
    public function setModOption($modId, $name, $value){
        $this->oOptions->setModParam($modId, $name, $value);
    }

    /**
     * Returns true if module option is set.
     *
     * @param  string $modId  Module id
     * @param  string $name   Module options name
     * @return bool
     */
    public function issetModOption($modId, $name){
        // if(!is_object($this->oOptions))die(d::getTraceAsString());
        return $this->oOptions->issetModParam($modId, $name);
    }

    /**
     * Deletes module option.
     *
     * @param  string $modId  Module id
     * @param  string $name   Module options name
     * @return bool
     * @amidev Temporary
     */
    public function deleteModOption($modId, $name){
        return $this->oOptions->deleteOption($modId, $name);
    }

    /**
     * Returns core module property value.
     *
     * @param  mixed $name  Module property name
     * @return mixed
     */
    public function getProperty($name){
        return $this->aProperties['core']['Properties'][$name];
    }

    /**
     * Sets core module property value.
     *
     * @param  string $name   Module property name
     * @param  mixed  $value  Module property value, null to unset
     * @return void
     */
    public function setProperty($name, $value){
        if(is_null($value)){
            unset($this->aProperties['core']['Properties'][$name]);
        }else{
            $this->aProperties['core']['Properties'][$name] = $value;
        }
    }

    /**
     * Returns true if core module property is set.
     *
     * @param  string $name  Module property name
     * @return bool
     */
    public function issetProperty($name){
        return $this->aProperties->issetModParam('core', $name);
    }

    /**
     * Returns core module option value.
     *
     * @param  string $name  Module option name
     * @return mixed
     */
    public function getOption($name){
        return $this->oOptions->getModParam('core', $name);
    }

    /**
     * Sets core module option value.
     *
     * @param  string $name  Module option name
     * @param  mixed $value  Module option value, null to unset
     * @return void
     */
    public function setOption($name, $value){
        $this->oOptions->setModParam('core', $name, $value);
    }

    /**
     * Returns true if core module option is set.
     *
     * @param  string $name  Module options name
     * @return bool
     */
    public function issetOption($name){
        return $this->oOptions->issetModParam('core', $name);
    }

    /**
     * Returns versions.
     *
     * AMI_iCore::getVersion($product) returns all versions for a specific product.<br />
     * AMI_iCore::getVersion() returns all versions in an array
     *
     * @param  mixed $product  Product
     * @param  mixed $type     Type
     * @return mixed
     * @see    CMS_Core
     */
    public function getVersion($product = FALSE, $type = FALSE){
        require_once AMI_Registry::get('path/host') . '_shared/code/const/init_simple.php';

        if(!is_array($this->aVersions)){
            $this->aVersions = $GLOBALS['VERSIONS'];
        }
        if($type === FALSE && $product === FALSE){
            return $this->aVersions;
        }
        return $type === FALSE ? $this->aVersions[$product] : $this->aVersions[$product][$type];
    }

    /**
     * Check is user is system user or not.
     *
     * @param  int $userId  User id, optional
     * @return bool|null
     * @amidev
     */
    public function isSysUser($userId = FALSE){
        $userId = (int)$userId;
        if(!$userId){
            $oSess = admSession();
            if($oSess){
                $userId = (int)($oSess->Data['user']['id']);
            }else{
                return NULL;
            }
        }
        $oDB = new CMS_simpleDb;
        $oDB->_dbLink = AMI::getSingleton('db')->getCoreDB()->_dbLink;
        $sql =
            "SELECT `id` " .
            "FROM `cms_host_users` " .
            "WHERE `sys_user` = 1 AND `id_member` = " . $userId;
        $oDB->query($sql);
        return (bool)$oDB->nextRecord();
    }

    /**
     * Sets core module admin link.
     *
     * @param  string $modId  Module id
     * @param  string $link   Link
     * @return AMI_iCoreModule
     */
    public function setAdminLink($modId, $link){
        $this->aAdminLinks[$modId] = $link;
        return $this;
    }

    /**
     * Returns core module admin link.
     *
     * @param  string $modId  Module id
     * @return string
     */
    public function getAdminLink($modId){
        if(isset($this->aAdminLinks[$modId])){
            return $this->aAdminLinks[$modId] . '?';
        }
        if(
            in_array($modId, $this->aInstalledMods) &&
            (
                (
                    $this->issetModOption($modId, 'engine_version') &&
                    ($this->getModOption($modId, 'engine_version') >= '0600')
                ) ||
                (
                    $this->issetModProperty($modId, 'admin_request_types') &&
                    in_array('ajax', $this->getModProperty($modId, 'admin_request_types')) &&
                    AMI::getSingleton('env/cookie')->get('ami_engine', FALSE) == 6
                )
            )
        ){
            return 'engine.php?mod_id=' . urlencode($modId) . '&';
        }
        return $modId . '.php?';
    }

    /**#@-*/

    /**
     * Rename options module name.
     *
     * @param  string $modId     Module name
     * @param  string $newModId  New module name
     * @return bool
     * @todo   Avoid hack?
     * @amidev
     */
    public function renameModOptions($modId, $newModId){
        if(isset($this->aDisabledMods[$modId]) || !in_array($modId, $this->aInstalledMods)){
            trigger_error("Module '" . $modId . "' isn't installed", E_USER_ERROR);
        }
        $res = isset($this->oOptions->aParams[$modId]);
        if($res && $newModId){
            $this->aProperties[$newModId] = $this->aProperties[$modId];
            $this->aInstalledMods[array_search($modId, $this->aInstalledMods)] = $newModId;
            $this->oOptions->aParams[$newModId] = $this->oOptions->aParams[$modId];
            AMI_Registry::set('_source_mod_id', $modId);
            unset($this->oOptions->aParams[$modId]);
        }
        return $res;
    }

    /**
     * Fake module/submodule declaration.
     *
     * Stub module declaration method.
     *
     * @param  string $section      Section
     * @param  string $hypermod     Hypermodule
     * @param  string $config       Configuration
     * @param  string $modId        Module id
     * @param  string $parentModId  Parent module id
     * @param  bool   $useFront     Front usage flag
     * @return void
     */
    public function declareModule($section, $hypermod, $config, $modId, $parentModId = null, $useFront = FALSE){
        if(!AMI::validateModId($hypermod)){
            trigger_error("Invalid hypermodule '{$hypermod}'", E_USER_ERROR);
        }
        if(!AMI::validateModId($config)){
            trigger_error("Invalid hypermodule config '{$config}'", E_USER_ERROR);
        }
        if(!AMI::validateModId($modId)){
            trigger_error("Invalid module '{$modId}'", E_USER_ERROR);
        }
        if($parentModId && !in_array($parentModId, $this->aInstalledMods)){
            trigger_error("Parent module '{$parentModId}' not declared", E_USER_ERROR);
        }
        if(in_array($modId, $this->aInstalledMods)){
            trigger_error("Module '{$modId}' is already declared", E_USER_ERROR);
        }
        $this->aInstalledMods[] = $modId;
        $this->aHyperStruct[$modId] = array($hypermod, $config);
    }

    /**
     * Associates passed tables with passed module.
     *
     * @param  string $modId   Module id
     * @param  array $aTables  Array of table names
     * @return AMI_Core
     * @amidev Temporary
     */
    public function setModTables($modId, array $aTables){
        foreach($aTables as $table){
            $this->aTablesXModIds[$table] = $modId;
        }
        return $this;
    }

    /**
     * Returns module id by passed table name or null otherwise.
     *
     * @param  string $tableName  Table name
     * @return string|null
     */
    public function getModIdByTable($tableName){
        return isset($this->aTablesXModIds[$tableName]) ? $this->aTablesXModIds[$tableName] : null;
    }

    /**
     * Returns module.
     *
     * @param  string $modId  Module id
     * @return AMI_iCoreModule
     */
    public function &getModule($modId){
        $oMod = new AMI_CoreModule($modId);
        return $oMod;
    }

    /**
     * Returns authorized user rights for passed object.
     *
     * Returns AMI_Core::SYS_RIGHTS_ALL for system user or AMI_Core::SYS_RIGHTS_READ otherwise.
     *
     * @param  mixed  $object  Object, string containing table name is supported
     * @param  string $type    Type, 'table' is supported
     * @return int
     * @amidev Temporary
     */
    public function getRights($object, $type = 'table'){
        return $this->isSysUser() ? self::SYS_RIGHTS_ALL : self::SYS_RIGHTS_READ;
    }

    /**
     * Returns authorized user id.
     *
     * @return int
     * @amidev Temporary
     */
    public function getUserId(){
        if(!array_key_exists('userId', $this->aSysData)){
            $oSess = admSession();
            $this->aSysData['userId'] = $oSess ? (int)($oSess->Data['user']['id']) : null;
        }
        return $this->aSysData['userId'];
    }

    /**
     * Returns TRUE if module id can be specified by table name and module has property 'support_rights' having TRUE value.
     *
     * @param  string $table  Table name
     * @return bool
     * @amidev Temporary
     */
    public function hasRightsSupport($table){
        if(!isset($this->aSysData['tables'][$table])){
            $modId = $this->getModIdByTable($table);
            $this->aSysData['tables'] =
                $modId
                    ? (bool)($this->issetModProperty($modId, 'support_rights') && $this->getModProperty($modId, 'support_rights'))
                    : FALSE;
        }
        return $this->aSysData['tables'];
    }

    /**
     * Returns TRUE if user having passed id has access to all records of passed module.
     *
     * @param  string $modId   Module id
     * @param  int    $userId  User id
     * @return bool
     */
    public function doIgnoreOwner($modId, $userId){
        if($this->isSysUser()){
            return TRUE;
        }
        $aRights = $this->getUserRights($userId);
        return isset($aRights['mod_rights'][$modId]) && $aRights['mod_rights'][$modId]['moderator'];
    }

    /**
     * Returns TRUE if admin authorized user has access to the passed module.
     *
     * @param  string $modId  Module id
     * @return bool
     */
    public function hasModAccess($modId){
        if($this->isSysUser()){
            return TRUE;
        }
        $aRights = $this->getUserRights($this->getUserId());

        return isset($aRights['mod_rights'][$modId]);
    }

    /**#@+
     * CMS_Core compatibility method.
     *
     * @amidev Temporary
     */

    /**
     * Returns TRUE if module id can be specified by table name and module has property 'support_rights' having TRUE value.
     *
     * @param  string $table  Table name
     * @return bool
     * @see    AMI_Core::hasRightsSupport()
     */
    public function hasRecordRights($table){
        return $this->hasRightsSupport($table);
    }

    /**
     * Returns TRUE if user having passed id has access to all records of passed module.
     *
     * @param  string $modId   Module id
     * @param  int    $userId  User id
     * @return bool
     * @see    AMI_Core::doIgnoreOwner()
     */
    public function isModerator($modId, $userId){
        return $this->doIgnoreOwner($modId, $userId);
    }

    /**
     * Returns array containing raghts related fields (keys as field names, values as field values).
     *
     * @param  &array $aLexems  Lexems array
     * @param  string $table    Table name
     * @return array
     */
    function getRightsFields(array $aLexems, $table){
        $aFileds = array();
        if($this->hasRightsSupport($table)){
            foreach(array('id_owner') as $col){ // , 'sys_rights_r', 'sys_rights_w', 'sys_rights_d'
                $found = FALSE;
                $regCol = preg_quote($col, '/');
                foreach($aLexems as $lexem){
                    if((mb_substr($lexem, 0, 1) != "'") && preg_match("/\\b" . $regCol . "\\b/", $lexem)){
                        $found = TRUE;
                        break;
                    }
                }
                if(!$found){
                    switch($col){
                        case 'id_owner':
                            $val = (int)$this->getUserId();
                            break;
                    }
                    $aFileds[$col] = $val;
                }
            }
        }
        return $aFileds;
    }

    /**#@-*/

    /**
     * Load installed modules state.
     *
     * @param  string $localModuleName  Local module name
     * @return AMI_Core
     * @todo   Delete tables section
     * @see    http://jira.cmspanel.net/browse/CMS-10991 about $localModuleName
     * @see    http://jira.cmspanel.net/browse/CMS-10992 about $localModuleName
     * @amidev
     */
    public function init($localModuleName = ''){
        if(empty($this->aProperties)){
            $oState = new CMS_ModulesProperties();
            $aDump = $oState->read(AMI_Registry::get('side') === 'frn' ? 'front' : 'all');
            $this->aProperties = $aDump['Properties']['values'];
            if($localModuleName !== ''){
                // Installed modules clean up
                $aSysModules = array('start', 'pages', 'users', 'adv_places', 'ce', 'common_settings', 'rating');
                $aIndices = array_keys($this->aInstalledMods);
                foreach($aIndices as $index){
                    $modId = $this->aInstalledMods[$index];
                    if(
                        mb_substr($modId, 0, 4) !== 'ext_' &&
                        !in_array($modId, $aSysModules)
                    ){
                        unset($this->aInstalledMods[$index]);
                    }
                }
                $this->aInstalledMods[] = $localModuleName;
                $this->aInstalledMods[] = mb_substr($localModuleName, -4) !== '_cat' ? $localModuleName . '_cat' : mb_substr($localModuleName, 0, -4);
            }
            $this->aInstalledMods[] = 'core_fast_adm';
            $this->oOptions = new CMS_ModulesOptions($this->aInstalledMods);
            // $this->oOptions->setModDump('core', $aDump['Options']);
            $this->oOptions->aParams['core'] = $this->oOptions->aParams['core_fast_adm'];
            unset($this->oOptions->aParams['core_fast_adm']);

            if($localModuleName !== ''){
                // Read 'extensions' option and disable options of not installed extensions
                if($this->oOptions->issetModParam($localModuleName, 'extensions')){
                    $aExtensions = $this->oOptions->getModAParam($localModuleName, 'extensions');
                    if(in_array('ext_images', $aExtensions)){
                        $aExtensions[] = 'ext_image';
                    }
                    if($this->oOptions->issetAndTrueModParam($localModuleName, 'use_categories')){
                        $aExtensions[] = 'ext_category';
                        $aExtensions[] = 'ext_eshop_category';
                    }
                    foreach($this->aInstalledMods as $modId){
                        if(mb_substr($modId, 0, 4) === 'ext_' && !in_array($modId, $aExtensions)){
                            $this->oOptions->dropModData($modId);
                        }
                    }
                }
            }

            // Tables section {

            foreach($this->aInstalledMods as $modId){
                if(empty($this->aHyperStruct[$modId])){
                    $this->setModTables($modId, array('cms_' . $modId));
                }
            }

            // } Tables section
        }
        return $this;
    }

    /**
     * Returns user rights.
     *
     * @param  int  $userId         User rights
     * @param  bool $hasAdminLogin  Has user admin login
     * @return array
     * @amidev Temporary?
     */
    private function getUserRights($userId, $hasAdminLogin = FALSE){
        if(!isset($this->aSysData['user_rights'])){
            $this->aSysData['user_rights'] = array();
        }
        $aData = array(
            'groups'         => array(),
            'mod_rights'     => array(),
            'group_mask'     => '0',
            'login'          => 0,
            'sys_user'       => 0,
            'rights_version' => 0
        );
        if(!$userId){
            return $aData;
        }
        if(isset($this->aSysData['user_rights'][$userId])){
            return $this->aSysData['user_rights'][$userId];
        }
        $this->aSysData['user_rights'][$userId] = array();

        $oDB = new DB_si;
        if(empty($GLOBALS['BUILDER_VERSION']) || $GLOBALS['BUILDER_VERSION'] < 2){
            $sql = "SELECT `id_member`, `rights_version` FROM `cms_host_users` WHERE `sys_user` = 1";
        }else{
            $sql = "SELECT `id_admin` `id_member`, `rights_version` FROM `cms_hst_res_cms_inst` WHERE `is_sys` = 1";
        }
        $oDB->query($sql, DBC_SYS_QUERY);
        $oDB->next_record();
        if($oDB->Record['id_member'] == $userId){
            $aData['sys_user'] = 1;
        }
        $aData['rights_version'] = $oDB->Record['rights_version'];
        if($this->issetOption('su') && $this->getOption('su')){
            $moderatorGroups = "0";

            // Load group data
            $sql =
                "SELECT `sg`.`id`, `sg`.`group_mask`, `sg`.`login`, `sg`.`moderator` ".
                "FROM `cms_sys_users` `su` ".
                "LEFT JOIN `cms_sys_groups` `sg` ON `su`.`id_group` = `sg`.`id` ".
                "WHERE `su`.`id_member` = " . $userId . ($hasAdminLogin ? " AND `sg`.`login` = 1" : '');
            $oDB->query($sql, DBC_SYS_QUERY);
            if($oDB->num_rows() > 0){
                while($oDB->next_record()){
                    $aData['groups'][]    = $oDB->Record['id'];
                    $aData['group_mask'] .= '|' . $oDB->Record['group_mask'];
                    $aData['login']      |= (int)$oDB->Record['login'];
                    if($oDB->Record['moderator']){
                        $moderatorGroups .= '|' . $oDB->Record['group_mask'];
                    }
                }
            }else{
                // Get guest group
                $sql = "SELECT `id`, `group_mask`, `login` FROM `cms_sys_groups` WHERE `guest` = 1 LIMIT 1";
                $oDB->query($sql, DBC_SYS_QUERY);
                $oDB->next_record();
                $aData['groups'][0]   = (int)$oDB->Record['id'];
                $aData['group_mask']  = $oDB->Record['group_mask'];
                $aData['login']       = (int)$oDB->Record['login'];
                $aData['moderator']   = 0; // Prevent human error that guest can be a moderator
                if(!$aData['groups'][0]){
                    trigger_error('Guest group not found', E_USER_ERROR);
                }
            }

            // Load module access permissions
            $oDeclarator = AMI_ModDeclarator::getInstance();

            $aModRights = array();
            $sql =
                "SELECT *, (`group_mask` & (" . $moderatorGroups . ")) > 0 AS `moderator` " .
                "FROM `cms_sys_actions_rights` " .
                "WHERE (`group_mask` & (" . $aData['group_mask'] . ")) != 0";
            $oDB->query($sql, DBC_SYS_QUERY);
            while($oDB->next_record()){
                $modId = $oDB->Record['module_name'];
                // if($this->isInstalled($modId)){ // Wrong check, #CMS-10954
                    $aModRights[$modId]['moderator'] = (int)(!empty($aModRights[$modId]['moderator'])) | $oDB->Record['moderator'];
                    $aModRights[$modId]['actions'][] = $oDB->Record['action_name'];
                // }
            }
            $aData['mod_rights'] = $aModRights;
        }
        $this->aSysData['user_rights'][$userId] += $aData;
        return $aData;
    }

    /**
     * Constructor.
     *
     * @param array $aInstalledMods  Installed modules
     * @amidev
     */
    private function __construct(array $aInstalledMods){
        $this->aInstalledMods = $aInstalledMods;
    }

    /**
     * Cloning is forbidden.
     */
    private function __clone(){
    }
}

/**
 * Core module interface.
 *
 * @package Core
 * @since   x.x.x
 * @amidev  Temporary?
 */
interface AMI_iCoreModule{
    /**
     * Sets core module installation flag.
     *
     * @param  bool $isIntstalled  Module installation flag
     * @return AMI_iCoreModule
     */
    public function setInstalled($isIntstalled);

    /**
     * Returns core module installation flag.
     *
     * @return bool
     */
    public function isInstalled();

    /**
     * Sets core module admin link.
     *
     * @param  string $link  Link
     * @return AMI_iCoreModule
     */
    public function setAdminLink($link);

    /**
     * Returns core module admin link.
     *
     * @return string
     */
    public function getAdminLink();
}

/**
 * Core module.
 *
 * Backward code compatibility.
 *
 * @package  Core
 * @resource response <code>AMI::getSingleton('core')</code>
 * @since    x.x.x
 * @amidev   Temporary?
 */
final class AMI_CoreModule implements AMI_iCoreModule{
    /**
     * Module id
     *
     * @var string
     */
    private $modId;

    /**
     * Sets core module installation flag.
     *
     * @param  bool $isIntstalled  Module installation flag
     * @return AMI_CoreModule
     */
    public function setInstalled($isIntstalled){
        AMI::getSingleton('core')->setInstalled($this->modId, (bool)$isIntstalled);
        return $this;
    }

    /**
     * Returns core module installation flag.
     *
     * @return bool
     */
    public function isInstalled(){
        return AMI::getSingleton('core')->isInstalled($this->modId);
    }

    /**
     * Sets core module admin link.
     *
     * @param  string $link  Link
     * @return AMI_CoreModule
     */
    public function setAdminLink($link){
        AMI::getSingleton('core')->setAdminLink($this->modId, $link);
        return $this;
    }

    /**
     * Returns core module admin link.
     *
     * @return string
     */
    public function getAdminLink(){
        return AMI::getSingleton('core')->getAdminLink($this->modId);
    }

    /**
     * Constructor.
     *
     * @param string $modId  Module id
     */
    public function __construct($modId){
        $this->modId = (string)$modId;
    }
}
