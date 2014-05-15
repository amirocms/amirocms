<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Environment
 * @version   $Id: AMI_Session.php 41842 2013-09-29 10:25:46Z Leontiev Anton $
 * @since     5.10.0
 */

/**
 * Session class.
 *
 * Example:
 * <code>
 * $oSession = AMI::getSingleton('env/session');
 * if(!$oSession->isStarted()){
 *     // start session if it is not started
 *     $oSession->start();
 *     // set session variable
 *     $oSession->myCounter = 0;
 * }
 * $oSession->myCounter++;
 * if($oSession->myCounter > 100){
 *     $oSession->stop();
 * }
 * </code>
 *
 * @package  Environment
 * @resource env/session <code>AMI::getSingleton('env/session')</code>
 * @since    5.10.0
 */
class AMI_Session{
    /**
     * Instance
     *
     * @var AMI_Session
     */
    private static $oInstance = null;

    /**
     * Internal session object
     *
     * @var CMS_Session
     */
    private $oSession = null;

    /**
     * Internal CMS object
     *
     * @var CMS
     */
    private $oCMS = null;

    /**
     * Internal session object
     *
     * @var CMS_Session
     */
    private $oMember = null;

    /**
     * Locale
     *
     * @var string
     */
    private $locale = '';

    /**
     * Session variables prefix
     *
     * @var string
     * @see AMI_Session::setPrefix()
     */
    private $prefix = '';

    /**
     * Admin authorized user data
     *
     * @var array
     * @amidev
     */
    private $aAdminUserData = array();

    /**
     * Class constructor.
     *
     * @param  array $aOptions  Reserved options array
     * @amidev
     */
    private function __construct(array $aOptions = array()){
        if(!empty($aOptions['locale'])){
            $this->locale = $aOptions['locale'];
        }else{
            $this->locale = getLangFromURL(AMI::getSingleton('env/request')->getURL('url'), $GLOBALS['ROOT_PATH_WWW']);
        }
        $this->oSession = isset($GLOBALS['oSession']) && is_object($GLOBALS['oSession']) ? $GLOBALS['oSession'] : new CMS_Session($GLOBALS['cms'], $this->locale);
        $this->oCMS = $GLOBALS['cms'];
    }

    /**
     * Returns an instance of an AMI_Session.
     *
     * @param  array $aOptions  Reserved options array
     * @return AMI_Session
     * @amidev
     */
    public static function getInstance(array $aOptions = array()){
        if(is_null(self::$oInstance)){
            self::$oInstance = new AMI_Session($aOptions);
        }
        return self::$oInstance;
    }

    /**
     * Returns is session was started or not.
     *
     * @return bool
     */
    public function isStarted(){
        return $this->oSession->IsSessionStarted();
    }

    /**
     * Starts session.
     *
     * Return hash where key is cookie session id name and value is cookie session id value.<br />
     * See {@link AMI_Session::login()} for usage example.
     *
     * @param  int  $time      Expiration time (in minutes)
     * @param  bool $doCreate  Allow to create new session (since 5.12.8)
     * @return array  Session cookie name => session id
     */
    public function start($time = 0, $doCreate = false){
        $this->oSession->Start((int)$time, (bool)$doCreate);
        return array($this->oSession->CookieName => $this->oSession->sid);
    }

    /**
     * Stops session.
     *
     * @return void
     */
    public function stop(){
        $this->oSession->Stop();
    }

    /**
     * Returns current session id.
     *
     * @return string|false
     */
    public function getId(){
        return $this->oSession->sid;
    }

    /**
     * Sets session variables prefix to isolate data between different modules.
     *
     * Example:
     * <code>
     * $oSession = AMI::getSingleton('env/session');
     * $oSession->test = 1;
     * $oSession->setPrefix('namespace2');
     * $oSession->test = 2;
     * $oSession->store();
     * // ...
     * d::vd($oSession->test)); // 2
     * $oSession->setPrefix('');
     * d::vd($oSession->test)); // 1
     * </code>
     *
     * @param  string $prefix  Session variables prefix
     * @return AMI_Session
     */
    public function setPrefix($prefix){
        $this->prefix = (string)$prefix;
        return $this;
    }

    /**
     * Return corresponding CMS_Member object.
     *
     * @return CMS_Member
     * @amidev
     */
    public function getMember() {
        global ${'sess_user_name'};
        if(empty($this->oMember)){
            $sessionName = ${'sess_user_name'} ? ${'sess_user_name'} : 'user';
            $this->oMember = new CMS_Member($this->oSession, $sessionName);
        }
        return $this->oMember;
    }

    /**
     * Login current user using login and password.
     *
     * Example:
     * <code>
     * $oSession = AMI::getSingleton('env/session');
     * if(!$oSession->isStarted()){
     *     $oSession->start();
     * }
     * $oSession->login($login, $password);
     * // ...
     * $oSession->logout();
     * </code>
     *
     * @param  string $userName  User name
     * @param  string $password  User password
     * @since  5.12.0
     * @return bool
     */
    public function login($userName, $password){
        $result = $this->getMember()->verifyLogin($this->oCMS, $userName, $password, true, $checkLang, $isPasswordHashed, $isLongSession);
        if(($result !== false) && is_array($result) && $result['active'] == 1){
            $oUser = $this->getUserData();
            $this->oSession->addJsSessionCookie('nickname_cookie', $oUser->nickname);
            $this->oSession->addJsSessionCookie('username_cookie', $oUser->username ? $oUser->username : $oUser->login);
            $this->oSession->addJsSessionCookie('firstname_cookie', $oUser->firstname);
            $this->oSession->addJsSessionCookie('lastname_cookie', $oUser->lastname);
            $this->oSession->addJsSessionCookie('id_cookie', $oUser->id);
            $this->oSession->addJsSessionCookie('source_app_id', $oUser->source_app_id);
            if(is_object($this->oCMS->Eshop)){
                $userBalance = $this->oCMS->Eshop->formatMoney(doubleval($oUser->balance), $this->oCMS->Eshop->baseCurrency, TRUE, FALSE);
                $this->oSession->addJsSessionCookie("balance_cookie", $userBalance);
            }
            $this->oSession->storeJsSessionCookies();
            $this->getMember()->updateCacheStatus();
            return TRUE;
        }else{
            return FALSE;
        }
    }

    /**
     * Login user depends validate only by login.
     *
     * Usefull for remote application integration. When password is unknown, but user valid and remote user id present.<br /><br />
     *
     * Example:
     * <code>
     * $oUserTable = AMI::getResourceModel('users/table');
     * $oUser = $oUserTable->getItem();
     *
     * $mySession = AMI::getSingleton('env/session');
     * $mySession->start();
     *
     * $source_app_id = 2;
     * $source_app_user_id = $_REQUEST['valid_remote_login'];
     *
     * if($uid = $oUserTable->checkRemoteUserExists($source_app_id, $source_app_user_id)){
     *
     *         $mySession->loginAsUser($oUserTable->find($uid)->login);
     *
     * }else{
     *         $new_user_login = $source_app_user_id;
     *         $uid_i = 2;
     *
     *         while($oUserTable->checkUserExists($new_user_login)){
     *                 $new_user_login = $source_app_user_id.($uid_i++);
     *         }
     *
     *         $oUser->username = $new_user_login;
     *         $oUser->password = $oUser->generatePassword();
     *         $oUser->firstname = '';
     *         $oUser->lastname = '';
     *         $oUser->email  = $new_user_login.'@unknown.mail';
     *         $oUser->source_app_id = $source_app_id;
     *         $oUser->source_app_user_id = $source_app_user_id;
     *
     *         if($oUser->save()){
     *                 if($mySession->loginAsUser($new_user_login)){
     *                     header('Location: /members');
     *                 }
     *         }
     * }
     * </code>
     *
     * @param  string $userName  User name
     * @return bool
     * @since  5.12.0
     */
    public function loginAsUser($userName){
        $result = $this->getMember()->loginAsUser($this->oCMS, $userName, true, $checkLang, $isPasswordHashed, $isLongSession);
        if(($result !== false) && is_array($result) && $result['active'] == 1){
            $oUser = $this->getUserData();
            $this->oSession->addJsSessionCookie('nickname_cookie', $oUser->nickname);
            $this->oSession->addJsSessionCookie('username_cookie', $oUser->username?$oUser->username:$oUser->login);
            $this->oSession->addJsSessionCookie('firstname_cookie', $oUser->firstname);
            $this->oSession->addJsSessionCookie('lastname_cookie', $oUser->lastname);
            $this->oSession->addJsSessionCookie('id_cookie', $oUser->id);
            $this->oSession->addJsSessionCookie('source_app_id', $oUser->source_app_id);
            if(is_object($this->oCMS->Eshop)){
                $userBalance = $this->oCMS->Eshop->formatMoney(doubleval($oUser->balance), $this->oCMS->Eshop->baseCurrency, TRUE, FALSE);
                $this->oSession->addJsSessionCookie("balance_cookie", $userBalance);
            }
            $this->oSession->storeJsSessionCookies();
            $this->getMember()->updateCacheStatus();
            return TRUE;
        }else{
            return FALSE;
        }
    }

    /**
     * Return flag logged in current user in this session or not.
     *
     * @return bool
     * @since  5.12.0
     */
    public function isLoggedIn(){
        // return $this->getMember()->isLoggedIn();
        if(!$this->isStarted()){
            $this->start();
        }
        return $this->oSession->IssetVar('user');
    }

    /**
     * Logout current user.
     *
     * See {@link AMI_Session::login()} for usage example.
     *
     * @return void
     * @since  5.12.0
     */
    public function logout(){
        $this->getMember()->logout($this->oCMS);
    }

    /**
     * Returns authorized user object or null if visitor is not authorized.
     *
     * @return AmiUsers_Users_TableItem|null
     */
    public function getUserData(){
        if(!$this->isStarted()){
            $this->start();
        }
        $aData = $this->oSession->GetVar('user');

        // Hack for getting admin user id
        $side = AMI_Registry::get('side');
        if($side == 'adm'){
            $aData['id'] = $GLOBALS['_h']['uid'];
        }
        // $aData = $this->getMember()->getUserInfo();
        if(is_array($aData)){
            $keys = array_keys($aData);
            foreach($keys as $key){
                // Leave only text keys
                if(is_numeric($key) || !$key){
                    unset($aData[$key]);
                }
            }
            /**
             * @var AMI_ModTableItem
             */
            if($side == 'adm'){
                if(!sizeof($this->aAdminUserData)){
                    $oItemModel = AMI::getResourceModel('users/table')->find($aData['id']);
                    $this->aAdminUserData = $oItemModel->getData();
                }else{
                    $oItemModel = AMI::getResourceModel('users/table')->getItem();
                }
                $aData = $this->aAdminUserData;
            }else{
                $oItemModel = AMI::getResourceModel('users/table')->getItem();
            }
            return $oItemModel->setDataAndRemap($aData, FALSE, TRUE);
        }else{
            return null;
        }
    }

    /**
     * Requires authorized user.
     *
     * @param  string $url             URL to redirect for authorization
     * @param  string $backURL         URL to return after successful authorization
     * @param  string $shortSessionId  Reserved, will be described later
     * @return void
     * @since  5.12.8
     * @todo   Describe $shortSessionId
     */
    public function requireAuthorizedUser($url = '', $backURL = '', $shortSessionId = ''){
        if(!$this->getUserData()){
            if(AMI_Registry::get('ami_request_type', 'plain') === 'ajax'){
                ob_clean();
                header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                header('Cache-Control: post-check=0, pre-check=0', false);
                header('Pragma: no-cache'); // HTTP/1.0
                header('HTTP/1.0 401 Unauthorized');
                if(is_object($GLOBALS['oCache'])){
                    $GLOBALS['oCache']->markPageIsSkipped();
                }
                die;
            }elseif(!empty($GLOBALS['Core'])){
                $this->getMember()->requireLogin($url, $backURL, $shortSessionId);
            }
            $backURL = (string)$backURL;
            if($backURL != ''){
                $this->oSession->SetVar('wantsurl', $backURL);
            }
            $this->oSession->Location((string)$url);
        }
    }

    /**
     * Store sessions data.
     *
     * Example:
     * <code>
     * $oSession = AMI::getSingleton('env/session');
     * $oSession->start();
     * d::vd($oSession->test); // Previous random value after page reload.
     * $oSession->test = rand(1,10);
     * $oSession->store();
     * </code>
     *
     * @return void
     * @since  5.12.0
     */
    public function store(){
        $this->oSession->store();
    }

    /**#@+
     * Property access implementation.
     */

    /**
     * Property setter.
     *
     * @param  string $name   Variable name
     * @param  mixed  $value  Variable value
     * @return void
     */
    public function __set($name, $value){
        $this->oSession->SetVar($this->prefix . $name, $value);
    }

    /**
     * Property getter.
     *
     * @param  string $name  Variable name
     * @return mixed
     */
    public function __get($name){
        return $this->oSession->GetVar($this->prefix . $name);
    }

    /**
     * Property getter.
     *
     * @param  string $name  Variable name
     * @return bool
     */
    public function __isset($name){
        return $this->oSession->IssetVar($this->prefix . $name);
    }

    /**
     * Property setter.
     *
     * @param  string $name  Variable name
     * @return void
     */
    public function __unset($name){
        $this->oSession->UnsetVar($this->prefix . $name);
    }

    /**#@-*/
}
