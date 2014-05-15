<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Plugin
 * @version   $Id: AMI_PluginState.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.10.0
 */

/**
 * Deprecated PluginTemplate class.
 *
 * @package    Plugin
 * @deprecated
 * @amidev
 */
class PluginTemplate extends template{
    /**
     * System GUI object
     *
     * @var template
     * @amidev
     */
    protected $sysGUI;

    /**
     * Constructor.
     *
     * @param  template $sysGUI  CMS template object
     * @amidev
     */
    public function __construct(template $sysGUI){
        $this->sysGUI = $sysGUI;
        parent::template();
    }

    /**
     * Adds CSS inclusion to result HTML.
     *
     * @param  string $path  CSS file path
     * @return void
     */
    public function addStyle($path){
        $this->sysGUI->addStyle($path);
    }

    /**
     * Adds JS inclusion to result HTML.
     *
     * @param  string $path  JavaScript file path
     * @return void
     */
    public function addScript($path){
        $this->sysGUI->addScript($path);
    }

    /**
     * Parses template / template set.
     *
     * @param  string       $name   Block name passed to PluginTemplate::addBlock() or "{$blockName}:{$setName}"
     * @param  string|array $aVars  Variables
     * @return string
     */
    public function get($name, $aVars = array()){
        return parent::get($name, $aVars);
    }

    /**
     * Hack.
     *
     * @param  string $file  File path to check
     * @return bool
     * @amidev
     */
    function _isFileLocal($file){
        return (strncmp($file, '_local/_admin/templates/', 24) == 0) || (strncmp($file, '_local/plugins', 14) == 0);
    }

    /**
     * Hack.
     *
     * @param  string $file  File path to check
     * @return bool
     * @amidev
     */
    function _isFileShared($file){
        return strncmp($file, '_shared/', 8) == 0;
    }
}

/**
 * Plugin options/rules API running in full CMS environment.
 *
 * @package Plugin
 * @since   5.10.0
 */
class AMI_PluginState{
    /**
     * AMI_PluginState object mode
     *
     * @var string
     */
    private $mode;

    /**
     * Interface language
     *
     * @var string
     */
    private $interfaceLanguage;

    /**
     * Data language
     *
     * @var string
     */
    private $dataLanguage;

    /**
     * CMS_Module object
     *
     * @var CMS_Module
     */
    private $oModule;

    /**
     * CMS_Session object
     *
     * @var CMS_Session
     */
    private $oSession;

    /**
     * CMS_Member object
     *
     * @var CMS_Member
     */
    private $oMember;

    /**
     * CMS_Base object
     *
     * @var Admin|Front
     */
    private $oCMS;

    /**
     * CMS_Cache object
     *
     * @var CMS_Cache
     */
    private $oCache;

    /**
     * CMS_ModuleRules object
     *
     * @var CMS_ModuleRules|null
     */
    private $oModuleRules;

    /**
     * CMSDBConnection object
     *
     * @var CMSDBConnection
     */
    private $oDB;

    /**
     * GUI_template object
     *
     * @var GUI_template
     */
    private $oTpl;

    /**
     * Session variable names prefix
     *
     * @var string
     */
    private $sessionNamePrefix;

    /**
     * Error flag
     *
     * @var bool
     */
    private $err;

    /**
     * Constructor.
     *
     * @param  string               $mode           Mode: 'setOption' | 'deleteOption' | 'addRule' | anything else
     * @param  CMS_Module           &$oModule       CMS_Module object
     * @param  Admin|Front          &$oCMS          CMS_Base object
     * @param  CMS_ModuleRules|null &$oModuleRules  CMS_ModuleRules object
     * @amidev
     */
    public function __construct($mode, CMS_Module &$oModule, CMS_Base &$oCMS, CMS_ModuleRules &$oModuleRules = null){
        global $oSession;

        $this->mode = $mode;
        $this->interfaceLanguage = $oCMS->lang;
        $this->dataLanguage = $oCMS->lang_data;
        $this->oModule = &$oModule;
        if($oCMS->Side == 'front' && isset($oSession) && is_object($oSession)){
            $this->oSession = &$oSession;
        }
        if(!is_object($this->oSession)){
            $this->oSession = new CMS_Session($oCMS, $oCMS->Core->GetOption('allow_multi_lang') ? $this->dataLanguage : '');
        }
        $this->sessionNamePrefix = '_' . $oModule->Name . '_';
        $this->oMember = new CMS_Member($this->oSession);
        $this->oCMS = &$oCMS;
        $this->oCache = &$oCMS->Core->Cache;
        $this->oModuleRules = &$oModuleRules;
        $this->oDB = new DB_si;
        $this->oGUI = new PluginTemplate($oCMS->Gui);
        $this->oGUI->globalVars = $oCMS->Gui->getGlobalVars();
        $this->oGUI->setForceReadFromDisk(true);
        $this->oGUI->setLang($this->interfaceLanguage);
    }

    /**
     * Returns db object.
     *
     * Example:
     * <code>
     * $db = &$api->getDB();
     * </code>
     *
     * @return CMSDBConnection
     * @example _local/plugins_distr/sample/code/my_class.php See PlgSampleClass::PlgSampleClass()
     * @amidev
     */
    public function &getDB(){
        return $this->oDB;
    }

    /**
     * Returns gui object.
     *
     * Example:
     * <code>
     * $gui = &$api->getGUI();
     * </code>
     *
     * @return template
     * @deprecated  Use {@link AMI_Template} or {@link AMI_TemplateSystem} instead of this method
     * @example    _local/plugins_distr/sample/code/my_class.php See PlgSampleClass::__construct()
     * @amidev
     */
    public function &getGUI(){
        return $this->oGUI;
    }

    /**
     * Returns intreface language.
     *
     * Intarface language may be different from data language on admin side, intreface and data languages are equal on front side.<br /><br />
     *
     * Example:
     * <code>
     * $resultHtml .= "Interface language is " . $api->getInterfaceLanguage();
     * </code>
     *
     * @return string
     * @amidev
     */
    public function getInterfaceLanguage(){
        return $this->interfaceLanguage;
    }

    /**
     * Returns data language.
     *
     * Intarface language may be different from data language on admin side, intreface and data languages are equal on front side.<br /><br />
     *
     * Example:
     * <code>
     * $resultHtml .= "Data language is " . $api->getDataLanguage();
     * </code>
     *
     * @return string
     * @amidev
     */
    public function getDataLanguage(){
        return $this->dataLanguage;
    }

    /**
     * Returns plugin module option set by AMI_PluginState::setOption() during plugin installation.
     *
     * @param  string $name  Option name
     * @return mixed
     * @example _local/plugins_distr/sample/code/my_class.php See PlgSampleClass::getUserDefinedOptionsHTML()
     * @amidev
     */
    public function getOption($name){
        return $this->oModule->GetOption($name);
    }

    /**
     * Sets plugin module option set during plugin installation.
     *
     * See {@link AMI::getPluginOption()} to learn how to read plugin options.
     *
     * @param  string $name   Optioon name
     * @param  mixed  $value  Optioon value
     * @return bool
     * @example _local/plugins_distr/sample/options/options.php Options definition
     */
    public function setOption($name, $value){
        AMI_Registry::push('disable_error_mail', true);
        $res = in_array($this->mode, array ('setOption', 'deleteOption'));
        if($res){
            if($this->mode == 'setOption'){
                $this->oModule->SetOption($name, $value);
            }else{
                // uninstall plugin
                $this->oModule->SetOption($name);
            }
        }else{
            trigger_error('CMS_PluginsAPI::setOption(): invalid call for mode ' . $this->mode, E_USER_WARNING);
        }
        AMI_Registry::pop('disable_error_mail');
        return $res;
    }

    /**
     * Adds plugin module option rule during plugin module rule displaying.
     *
     * @param  string $name          Plugin module option name
     * @param  int    $type          Plugin module rule type<br />
     *                               Possible values:<br />
     *                               RLT_BOOL, RLT_UINT, RLT_SINT, RLT_FLOAT, RLT_CHAR,<br />
     *                               RLT_TEXT, RLT_EMAIL, RLT_ENUM, RLT_ENUM_MULTI_ARRAY,<br />
     *                               RLT_DATE_PERIOD, RLT_DATE_PERIOD_POSITIVE, RLT_DATE_PERIOD_NEGATIVE,<br />
     *                               RLT_SPLITTER
     * @param  mixed  $aOptions      Plugin module rule options
     * @param  mixed  $defaultValue  Plugin module default rule value
     * @param  array  $aCaptions     Custom rule values captions
     * @return bool
     * @example _local/plugins_distr/sample/options/options.php Options definition
     * @example _local/plugins_distr/sample/options/rules.php Rules definition
     */
    public function addRule($name, $type, $aOptions, $defaultValue, array $aCaptions = array()){
        AMI_Registry::push('disable_error_mail', true);
        $res = $this->mode == 'addRule' && mb_strtolower(get_class($this->oModuleRules)) == 'cms_modulerules';
        if($res){
            // rules content
            static $aAllowedTypes = array(
                RLT_BOOL, RLT_UINT, RLT_SINT, RLT_FLOAT, RLT_CHAR, RLT_TEXT, RLT_EMAIL, RLT_ENUM, RLT_ENUM_MULTI_ARRAY,
                RLT_DATE_PERIOD, RLT_DATE_PERIOD_POSITIVE, RLT_DATE_PERIOD_NEGATIVE, RLT_SPLITTER
            );
            if(in_array($type, $aAllowedTypes)){
                $this->oModuleRules->SpecialCaptions = array_merge($this->oModuleRules->SpecialCaptions, $aCaptions);
                $res = $this->oModuleRules->addRule(CMS_CoreRules::RLR_ANY, CMS_CoreRules::VIEW_MODE_NOVICE, $name, $type, $aOptions, $defaultValue, true, array ('spec_small_' . $this->oModule->Name), '', null);
            }else{
                trigger_error('Unsupported plugin rule type ' . $type, E_USER_WARNING);
            }
        }else{
            trigger_error('CMS_PluginsAPI::addRule(): invalid call for mode ' . $this->mode, E_USER_WARNING);
        }
        AMI_Registry::pop('disable_error_mail');
        return $res;
    }

    /**
     * Returns if session is started.
     *
     * Example:
     * <code>
     * $resultHtml .= 'Session is ' . ($api->sessionIsStarted() ? '' : 'not ') . 'started';
     * </code>
     *
     * @return bool
     * @amidev
     */
    public function sessionIsStarted(){
        return $this->oSession->IsSessionStarted();
    }

    /**
     * Returns session id.
     *
     * Example:
     * <code>
     * if($api->sessionIsStarted()){
     *     $resultHtml .= 'Session id is ' . $api->sessionGetId();
     * }
     * </code>
     *
     * @return string|false
     * @amidev
     */
    public function sessionGetId(){
        return $this->oSession->sid;
    }

    /**
     * Starts session.
     *
     * Example:
     * <code>
     * if(!$api->sessionIsStarted()){
     *     $api->sessionStart(); // Start session until browser window well be closed
     * }
     * </code>
     *
     * @param  int $expirationTime  Expiration time in minutes
     * @return void
     * @amidev
     */
    public function sessionStart($expirationTime = 0){
        $this->oSession->Start((int)$expirationTime, true);
    }

    /**
     * Stops session.
     *
     * Example:
     * <code>
     * $pluginAction = empty($pluginParams['http_vars_get']['plugin_action'])
     *     ? ''
     *     : $pluginParams['http_vars_get']['plugin_action'];
     *
     * if($api->sessionIsStarted() && $pluginAction == 'session_stop')){
     *     // session is started and GET-parameter 'plugin_action' equals to 'session_stop'
     *     $api->sessionStop(); // Stop session
     * }
     * </code>
     *
     * @return void
     * @amidev
     */
    public function sessionStop(){
        $this->oSession->Stop();
    }

    /**
     * Sets session data.
     *
     * Example:
     * <code>
     * if(!$api->sessionIsStarted()){
     *     $api->sessionStart();
     *     // Store session data
     *     $api->sessionSetData('session_variable_name', 'session_variable_value');
     * }
     * </code>
     *
     * @param  string $name   Variable name
     * @param  mixed  $value  Variable value
     * @return void
     * @amidev
     */
    public function sessionSetData($name, $value){
        $this->oSession->SetVar($this->sessionNamePrefix . $name, $value);
    }

    /**
     * Returns session data by name.
     *
     * Example:
     * <code>
     * if($api->sessionIsStarted()){
     *     $resultHtml .=
     *         'Session variable value is &nbsp;' .
     *         $api->sessionGetData('session_variable_name') . '&nbsp;';
     * }
     * </code>
     *
     * @param  string $name  Variable name
     * @return mixed
     * @amidev
     */
    public function sessionGetData($name){
        return $this->oSession->GetVar($this->sessionNamePrefix . $name);
    }

    /**
     * Returns is session data is set.
     *
     * Example:
     * <code>
     * if($api->sessionIsStarted()){
     *     $resultHtml .=
     *         'Session variable &nbsp;session_variable_name&nbsp; is ' .
     *         ($api->sessionIssetData('session_variable_name') ? '' : 'not ') . 'set';
     * }
     * </code>
     *
     * @param  string $name  Variable name
     * @return bool
     * @amidev
     */
    public function sessionIssetData($name){
        return $this->oSession->IssetVar($this->sessionNamePrefix . $name);
    }

    /**
     * Unsets session data by name.
     *
     * Example:
     * <code>
     * if($api->sessionIsStarted()){
     *     $api->sessionUnsetData('session_variable_name');
     * }
     * </code>
     *
     * @param  string $name  Variable name
     * @return void
     * @amidev
     */
    public function sessionUnsetData($name){
        $this->oSession->UnsetVar($this->sessionNamePrefix . $name);
    }

    /**
     * Returns authorized member data.
     *
     * Example:
     * <code>
     * if($api->sessionIsStarted()){
     *     $user = $api->sessionGetMemberData();
     *     if(is_array($user)){
     *         $resultHtml .=
     *             'Authorized user login name is &nbsp;' .
     *             $user['username'] . '&nbsp;';
     *     }
     * }
     * </code>
     *
     * @return array|null|false  array - authorized member data
     *                           null  - visitor is not authorized
     *                           false - returns on admin side, no authorized admin member data is available
     * @amidev
     */
    public function sessionGetMemberData(){
        $res = false;
        if($this->oCMS->Side == 'front'){
            $res = $this->oSession->GetVar('user');
            if(is_array($res)){
                $keys = array_keys($res);
                foreach($keys as $key){
                    if(is_numeric($key)){
                        unset($res[$key]);
                    }
                }
            }
        }
        return $res;
    }

    /**
     * Sets/resets member used fields.
     *
     * This method should be called before AMI_PluginState::memberCreate().
     *
     * @param  array $aFields  Fields
     * @param  bool  $set      Do set
     * @param  bool  $reset    Do reset
     * @return void
     * @see    AMI_PluginState::memberCreate()
     * @amidev
     */
    public function memberSetUsedFields(array $aFields, $set = false, $reset = false){
        $this->oMember->setUsed($aFields, $set, $reset);
    }

    /**
     * Sets/resets member obligatory fields.
     *
     * This method should be called before AMI_PluginState::memberCreate().
     *
     * @param  array $aFields  Fields
     * @param  bool  $set      Do set
     * @param  bool  $reset    Do reset
     * @return void
     * @see    AMI_PluginState::memberCreate()
     * @amidev
     */
    public function memberSetObligatoryFields(array $aFields, $set = false, $reset = false){
        $this->oMember->setObligatory($aFields, $set, $reset);
    }

    /**
     * Creates member in CMS.
     *
     * Example:
     * <code>
     * $aMemberToCreate = array (
     *     'username' => 'test',
     *     'password' => 'testpassword',
     *     'email'    => 'test@test.com'
     * );
     *
     * if(!is_array($api->memberGetInfo('username', $aMemberToCreate['username']))){
     *     // member is not exist
     *
     *     // set used fields
     *     $api->memberSetUsedFields(array_keys($aMemberToCreate), true);
     *     // set obligatory fields to none
     *     $api->memberSetObligatoryFields(array (), false, true);
     *     // create member
     *     $api->memberCreate($aMemberToCreate);
     * }
     * </code>
     *
     * @param  array  $aData         Data
     * @param  bool   $sendUserMail  Send email flag
     * @param  string $autoGenerate  Autogenerate field list
     * @return bool
     * @amidev
     */
    public function memberCreate(array $aData, $sendUserMail = false, $autoGenerate = ''){
        return $this->oMember->createMember($this->oCMS, $this->oDB, $aData, $sendUserMail, $autoGenerate);
    }

    /**
     * Updates CMS member data.
     *
     * Example:
     * <code>
     * $aMember = $api->memberGetInfo('username', 'test');
     * if(is_array($aMember)){
     *     // set used fields
     *     $api->memberSetUsedFields(array ('email'), true);
     *     // set obligatory fields to none
     *     $api->memberSetObligatoryFields(array (), false, true);
     *     // update member
     *     $api->memberUpdate($aMember['id'], array ('email' => 'newemail@test.com'));
     * }
     * </code>
     *
     * @param  int   $id                   Member id
     * @param  array $aData                Fields to update
     * @param  bool  $updateSessionMember  Do update current session member<br />
     *                                     If there is authorized visitor data stored is session its will be updated
     * @return bool
     * @amidev
     */
    public function memberUpdate($id, array $aData, $updateSessionMember = false){
        if($this->oCMS->Side != 'front'){
            $updateSessionMember = false;
        }
        return $this->oMember->updateMember($this->oCMS, $this->oDB, (int)$id, $aData, !$updateSessionMember);
    }

    /**
     * Gets member info by identity column/value.
     *
     * @param  string $identityColumn  Col name
     * @param  string $identityValue   Col value
     * @return bool|array              False in case of member is not exists, array containing member data otherwise (MySQL cms_members table record)
     * @see    AMI_PluginState::memberCreate()
     * @amidev
     */
    public function memberGetInfo($identityColumn, $identityValue){
        $res = $this->oMember->getData($this->oDB, $identityColumn, $identityValue);
        return $res;
    }

    /**
     * Tries to authorize member.
     *
     * After successful authorization page need to be refreshed to correct displaying auth-related data.<br /><br />
     *
     * Example:
     * <code>
     * if($api->memberAuthorize('test', 'testpassword')){
     *     $resultHtml .= 'User &nbsp;test&nbsp; is authorized successfully';
     * }else{
     *     $resultHtml .= 'Unable to authorize user &nbsp;test&nbsp;';
     * }
     * </code>
     *
     * @param string $username  Login
     * @param string $password  Password
     * @return bool
     * @amidev
     */
    public function memberAuthorize($username, $password){
        $res = false;
        if($this->oCMS->Side == 'front'){
            $res = is_array($this->oCMS->Member->verifyLogin($this->oCMS, $username, $password, 'record'));
            if($res){
                $this->oCMS->Member->updateCacheStatus();
            }
        }
        return $res;
    }

    /**
     * Redirects front side visitor to authorization page if needed.
     *
     * Example:
     * <code>
     * $api->memberRequireLogin($pluginParams['root_path_www'] . $pluginParams['active_script']);
     * </code>
     *
     * @param  string $backURL  URL to return after authorization
     * @return void
     * @amidev
     */
    function memberRequireLogin($backURL = ''){
        if($this->oCMS->Side == 'front' && !$this->oCMS->Member->isLoggedIn()){
            $oModule = &$this->oCMS->Core->GetModule('members');
            if($oModule->IsPresentInPMandPublic()){
                $url = $oModule->GetFrontLink();
                if($backURL != ''){
                    $url .= (mb_strpos($url, '?') === false ? '?' : '&') . 'wantsurl=' . rawurlencode($backURL);
                }
                $this->redirect($GLOBALS['ROOT_PATH_WWW'] . $url);
            }
        }
    }

    /**
     * Sets plugin specblock cache expiration.
     *
     * Example:
     * <code>
     * $api->cacheSetExpiration(); // disable cache for this plugin
     * </code>
     *
     * @param  string $expirationPeriod  Expiration period in strtotime() PHP function format
     * @return void
     * @amidev
     */
    public function cacheSetExpiration($expirationPeriod = ''){
        $time = strtotime($expirationPeriod);
        if($time === false){
            $time = strtotime('-1 hour');
        }
        $this->oCache->SetForceExpireTime($this->oModule->Name, $time);
    }

    /**#@-*/

    /**
     * Redirects visitor to URL.
     *
     * Example:
     * <code>
     * $api->redirect('http://www.amiro.ru');
     * </code>
     *
     * @param  string $url  URL
     * @return void
     * @exitpoint
     * @amidev
     */
    public function redirect($url){
        $this->oSession->Location($url);
    }

    /**
     * Returns if system module is installed.
     *
     * Example:
     * <code>
     * if($api->moduleIsInstalled('news')){
     *     $resultHtml .= 'News module is installed';
     * }
     * </code>
     *
     * @param  string $moduleName  System module name
     * @return bool
     * @amidev
     */
    public function moduleIsInstalled($moduleName){
        $res = $this->oCMS->Core->IsInstalled($moduleName);
        return $res;
    }

    /**
     * Set/get plugin params.
     *
     * @param  array|null $pluginParams  Plugin params
     * @return void|array
     * @amidev Permanently
     */
    public function params($pluginParams = null){
        static $params = null;

        if(is_null($params)){
            $params = $pluginParams;
        }else{
            return $params;
        }
    }
}
