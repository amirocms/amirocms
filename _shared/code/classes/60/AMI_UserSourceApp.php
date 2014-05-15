<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Service
 * @version   $Id: AMI_UserSourceApp.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.12.0
 */

/**
 * User Source Application service interface.
 *
 * The main purpose of class description below is to provide functionality to
 * login using third-party services (like Twitter, Facebook, etc.)
 *
 * @package Service
 * @since   5.12.0
 */
interface AMI_iUserSourceAppService{
    /**
     * Return HTML list of drivers's buttons.
     *
     * @return string
     */
    function getButtons();

    /**
     * Return specified driver icon as HTML.
     *
     * @param  int   $driverId  Driver id
     * @param  array $aScope    Template scope
     * @return string
     */
    function getDriverIcon($driverId, array $aScope = array());
}

/**
 *  User Source Application service class. Provide drivers management functionality.
 *
 *  Remote config file name stored as $iniFileName ('_local/user_source_app.ini.php' by default).
 *
 * @package Service
 * @since   5.12.0
 */
class AMI_UserSourceApp implements AMI_iUserSourceAppService{
    /**
     * Template file name
     *
     * @var string
     */
    protected $tplFileName = 'templates/user_source_app.tpl';

    /**
     * Template block name.
     *
     * @var string
     */
    public $tplBlockName = 'users_source_app';

    /**
     * Buttons list set name
     *
     * @var string
     */
    protected $buttonsListSet = 'buttons_list';

    /**
     * Button item set name
     *
     * @var string
     */
    protected $buttonItemSet = 'button_item';

    /**
     * Inactive user set
     *
     * @var string
     */
    public $inactiveUserSet = 'inactive_user_set';

    /**
     * Incorrect user set
     *
     * @var string
     */
    public $incorrectUserSet = 'incorrect_user_set';

    /**
     * User specblock icons list set
     *
     * @var string
     */
    public $userSpecblockIconsListSet = 'user_specblock_icons_list_set';

    /**
     * Locales file name
     *
     * @var string
     */
    protected $localeFileName = 'templates/lang/user_source_app.lng';

    /**
     * INI Config file name
     *
     * @var string
     */
    protected $iniFileName = '_local/user_source_app.ini.php';

    /**
     * Locales array
     *
     * @var array
     */
    protected $aLocale = array();

    /**
     * INI Config array
     *
     * @var array
     */
    protected $aConfig = array();

    /**
     * Enabled/disabled flag
     *
     * @var bool
     */
    protected $bEnabled = true;

    /**
     * Array of drivers names
     *
     * @var array
     */
    protected $aDriversList = array();

    /**
     * Associative array of driverId => driverName pair
     *
     * @var array
     */
    protected $aDriversIdList = array();

    /**
     * Cached driver icons
     *
     * @var array
     */
    protected static $aDriversIcons = array();

    /**
     * Constructor.
     *
     * Reading all drivers resources from: 'user_source_app/drivers/',and initialize its.
     */
    public function __construct(){
        // Check corresponding option
        if(!AMI::getOption('members', 'user_source_app')){
            $this->bEnabled = FALSE;
            return FALSE;
        }

        // Reading config file
        $this->aConfig = @parse_ini_file($this->iniFileName, TRUE);

        // Automatically adds drivers
        $aDrivers = AMI::getResourcesByMask('user_source_app/drivers/');

        foreach(array_keys($aDrivers) as $driver){
            $driverName = str_replace('user_source_app/drivers/', '', $driver);
            if(isset($this->aConfig[$driverName]['enabled']) && $this->aConfig[$driverName]['enabled']){
            	$this->addDriver($driverName);
            }
            $this->aDriversIdList[$this->getDriver($driverName)->driverId] = $driverName;
        }

        // Templates init
        $this->getTemplate()->addBlock($this->tplBlockName, $this->tplFileName);
        $this->aLocale = $this->getTemplate()->parseLocale($this->localeFileName);

    }

    /**
     * Add driver into service.
     *
     * @param  string $driverName  Driver name
     * @return void
     */
    public function addDriver($driverName){
        $this->aDriversList[] = $driverName;
    }

    /**
     * Create and return driver object.
     *
     * @param  string $driverName  Driver name
     * @return AMI_iUserSourceAppDriver
     */
    public function getDriver($driverName){
        $this->aConfig[$driverName]['inactive_user_redirect_page'] = '/';
        if(isset($this->aConfig['inactive_user_redirect_page'])){
            $this->aConfig[$driverName]['inactive_user_redirect_page'] = $this->aConfig['inactive_user_redirect_page'];
        }
    	return AMI::getSingleton(
            'user_source_app/drivers/'.$driverName,
            array($this, $this->aConfig[$driverName])
        );
    }

    /**
     * Getting all drivers buttons as HTML.
     *
     * @return string
     */
    public function getButtons(){
        // Do nothing when disabled
        if(!$this->bEnabled){
            return '';
        }

        $mySession = AMI::getSingleton('env/session');
        $mySession->start();
        if($mySession->getMember()->isLoggedIn()){
            // No auth is needed
            return '';
        }

        $oTpl = $this->getTemplate();
        $aScope = array('buttons' => '');
        foreach($this->aDriversList as $driverName){
            $aDriverScope = $aScope;
            $aDriverScope['button'] = $this->getDriver($driverName)->getButton();
            $aScope['buttons'] .= $oTpl->parse($this->tplBlockName . ':' . $this->buttonItemSet, $aDriverScope);
        }
        return $oTpl->parse($this->tplBlockName . ':' . $this->buttonsListSet, $aScope);
    }

    /**
     * Return driver name by driver Id.
     *
     * @param  int $id  Driver Id
     * @return string
     */
    function getDriverNameById($id){
    	return $this->aDriversIdList[$id];
    }

    /**
     * Return specified driver icon as HTML.
     *
     * @param  int   $driverName  Driver name
     * @param  array $aScope      Template scope
     * @return string
     */
    function getDriverIcon($driverName, array $aScope = array()){
        $userId = isset($aScope['user_id']) ? $aScope['user_id'] : '';
        if(!isset(self::$aDriversIcons[$driverName][$userId])){
            self::$aDriversIcons[$driverName][$userId] = $this->getDriver($driverName)->getIcon($aScope);
        }
        return self::$aDriversIcons[$driverName][$userId];
    }

    /**
     * Dispatch custom driver action.
     *
     * @param  string $driverName  Driver name
     * @param  string $actionName  Action name
     * @return mixed
     */
    public function dispatchDriverAction($driverName, $actionName){
        // Do nothing when disabled
        if(!$this->bEnabled){
            return null;
        }
        $oDriver = $this->getDriver($driverName);
        $methodName = 'dispatch' . implode(array_map('ucfirst', explode('_', $actionName)));
        if(is_callable(array($oDriver,$methodName))){
            return $oDriver->$methodName();
        }else{
            trigger_error("Unknown action '" . $actionName . "' in driver '". $driverName ."'", E_USER_ERROR);
        }
    }

    /**
     * Returns global template object.
     *
     * @return AMI_iTemplate
     */
    public function getTemplate(){
        return AMI::getSingleton('env/template_sys');
    }

    /**
     * Special function to correct show icon in cached users specblock.
     *
     * @return string
     */
    public function getUserSpecblockDriverIcon(){
        $oTpl = $this->getTemplate();
        $aScope = array('icons' => '');
        foreach($this->aDriversIdList as $driverId => $driverName){
            $oDriver = $this->getDriver($driverName);
            $aScope['icons'] .= $driverId . " : '" . $oDriver->getIcon() . "',\n";
        }
        $aScope['icons'] = trim($aScope['icons'], ", \n");
        return $oTpl->parse($this->tplBlockName . ':' . $this->userSpecblockIconsListSet, $aScope);
    }

    /**
     * Check for valid user E-mail.
     *
     * @param  string $email  E-mail
     * @return bool   Returns false if user dint't change e-mail after autocreating
     */
    public function checkValidMail($email){
        return !empty($email); // (empty($email) || mb_strpos($email, '@unknown.mail')!==false)?false:true;
    }
}

/**
 * User source apllication driver interface.
 *
 * @package Service
 */
interface AMI_iUserSourceAppDriver{
    /**
     * Constructor.
     *
     * @param AMI_iUserSourceAppService $oService  Service object
     * @param array                     $aConfig   Config array.
     */
    public function __construct(AMI_iUserSourceAppService $oService,  array $aConfig = array());

    /**
     * Returns HTML string to place in buttons list.
     *
     * @return string
     */
    public function getButton();

    /**
     * Returns driver icon.
     *
     * @param  array $aScope  Template scope
     * @return string
     */
    public function getIcon(array $aScope = array());
}

/**
 * User source apllication driver middle layer.
 *
 * Contains common drivers functionality.<br />
 * * To configure current driver:
 * - Edit "/_local/users_source_app.ini.php";
 * - Fill up all required fields;
 * - Enable it.<br />
 *
 * To create a new one:
 * - Create new class extends AMI_UserSourceAppDriver;
 * - Generate new unique driverId value (more than 10000);
 * - Generate new unique driverName;
 * - Add you new driver into resource mapping in '/user_source_app/drivers/';
 * - Create new options in '/_local/users_source_app.ini.php' (optional);
 * - Create method getButton witch returns HTML driver icon;
 * - Modify verifyLogin method according to your requirements.
 *
 * @package Service
 * @since   5.12.0
 */
abstract class AMI_UserSourceAppDriver implements AMI_iUserSourceAppDriver{
    /**
     * Driver id numeric (unique).
     *
     * ID less than 10000 restricted.
     *
     * @var void
     */
    public $driverId;

    /**
     * Driver name.
     *
     * @var void
     */
    public $driverName;

    /**
     * User Source application object.
     *
     * @var array
     * @see AMI_UserSourceApp
     */
    protected $oService;

    /**
     * Driver's settings array.
     *
     * @var array
     */
    protected $aConfig;

    /**
     * Constructior.
     *
     * @param AMI_iUserSourceAppService $oService  User Source application object
     * @param array                     $aConfig   Driver's settings array
     */
    public function __construct(AMI_iUserSourceAppService $oService, array $aConfig = array()){
        $this->oService = $oService;
        $this->aConfig = $aConfig;
    }

    /**
     * Entry point URL.
     *
     * @var void
     */
    var $entryPoint = 'ami_service.php';

    /**
     * Returns current driver ID.
     *
     * @return string
     */
    public final function getDriverId(){
        if(
            !in_array(
                $this->getDriverName(),
                array('ami_twitter', 'ami_vkontakte', 'ami_facebook', 'ami_loginza')
            ) && $this->driverId < 10000
        ){
            trigger_error('User Source Driver ID less than 10000 reserved', E_USER_ERROR);
        }
        return $this->driverId;
    }

    /**
     * Return driver name.
     *
     * @return string
     */
    public final function getDriverName(){
        return $this->driverName;
    }

    /**
     * Return entry point script name.
     *
     * @return string
     */
    public function getEntryPoint(){
        return $this->entryPoint;
    }

    /**
     * Login or create and login user.
     *
     * @param  array $aData  Array of incoming data
     * @return void
     * @exitpoint
     */
    private function loginOrCreateUser(array $aData){
        /**
         * @var Users_Table
         */
        $usersModel = AMI::getResourceModel('users/table');
        $usersItem = $usersModel->getItem();

        $mySession = AMI::getSingleton('env/session');
        $mySession->start();

        $sourceAppUserID =
            isset($aData['source_app_user_id'])
            ? $aData['source_app_user_id']
            : AMI_Lib_String::transliterate($aData['login'], 'ru');

        $uid = $usersModel->checkRemoteUserExists($this->getDriverId(), $sourceAppUserID);

        if($uid){
            // Existing user
            $oUser = $usersModel->find($uid);
            if($mySession->loginAsUser($oUser->login ? $oUser->login : $oUser->username)){
                $oResponse = AMI::getSingleton('response');
                $oResponse->HTTP->setRedirect($aData['return_url']);
                die;
            }
            if(!$oUser->active){
                $sublink =
                    isset($this->aConfig['inactive_user_redirect_page'])
                    ? $this->aConfig['inactive_user_redirect_page'] :
                    FALSE;
                if($sublink && AMI::getResourceModel('pages/table')->getItem()->addFields('id')->addSearchCondition(array('sublink' => $sublink))->load()){
                    $oResponse = AMI::getSingleton('response');
                    $oResponse->HTTP->setRedirect('/' . $this->aConfig['inactive_user_redirect_page']);
                    die;
                }
                $oResponse = AMI::getSingleton('response');
                $oTpl = $this->oService->getTemplate();
                $aScope = array('return_url' => $aData['return_url']);
                $oResponse->write(
                    $oTpl->parse($this->oService->tplBlockName . ':' . $this->oService->inactiveUserSet, $aScope)
                );
                die;
            }
        }else{
            // New user
            $newUserLoginBase = $newUserLogin = preg_replace('/[^\_\w\d]/', '', AMI_Lib_String::transliterate($aData['login'], 'ru'));
            $newUserLogin = preg_replace('/[^\_\w\d]/', '', $newUserLogin);
            $uIDi = 2;
            while($usersModel->checkUserExists($newUserLogin)){
                if($uIDi > 100*50){
                    trigger_error('Can not generate new user name. Last was :' . $newUserLogin, E_USER_WARNING);
                    $oResponse = AMI::getSingleton('response');
                    $oTpl = $this->oService->getTemplate();
                    $aScope = array('return_url' => $aData['return_url']);
                    $oResponse->write(
                        $oTpl->parse($this->oService->tplBlockName . ':' . $this->oService->incorrectUserSet, $aScope)
                    );
                    die;
                }
                $uIDi += mt_rand(1, 100);
                $newUserLogin = $newUserLoginBase.($uIDi++);
            }

            $usersItem->username = $newUserLogin;
            $usersItem->password = $usersItem->generatePassword(7);
            $usersItem->firstname = !empty($aData['firstname'])?$aData['firstname']:'';
            $usersItem->lastname = !empty($aData['lastname'])?$aData['lastname']:'';
            $usersItem->email  = empty($aData['email']) ? '' : $aData['email']; // !empty($aData['email'])?$aData['email']:$newUserLogin.'@unknown.mail';
            $usersItem->source_app_id = $this->getDriverId();
            $usersItem->source_app_user_id = $sourceAppUserID;
            $aOldObligatoryFields = $usersItem->getObligatoryFields();
            $usersItem->setObligatoryFields(array('username', 'password'));
            if($usersItem->save()){
            	$usersItem->setActiveState(TRUE);
                if($mySession->loginAsUser($usersItem->username)){
                    $oResponse = AMI::getSingleton('response');
                    $oResponse->HTTP->setRedirect($aData['return_url']);
                    die;
                }
            }
            $usersItem->setObligatoryFields($aOldObligatoryFields);
        }

        trigger_error('Can not verify user', E_USER_WARNING);

        $oResponse = AMI::getSingleton('response');
        $oTpl = $this->oService->getTemplate();
        $aScope = array('return_url' => $aData['return_url']);
        $oResponse->write(
            $oTpl->parse($this->oService->tplBlockName . ':' . $this->oService->incorrectUserSet, $aScope)
        );
        die;
    }

    /**
     * Dispatch verify login action.
     *
     * @param  array $aData  Array. Required keys: 'login'.
     * @return bool
     */
    function dispatchVerify(array $aData = array()){
        $oRequest = AMI::getSingleton('env/request');
        foreach(array('login','firstname','lastname','email') as $key){
            if(!isset($aData[$key])){
                $aData[$key] = html_entity_decode($oRequest->get($key, null), ENT_QUOTES);
            }
        }
        if(!isset($aData['return_url']) && $oRequest->get('return_url')){
            $aData['return_url'] = urldecode(html_entity_decode($oRequest->get('return_url', null)));
            if(preg_match('/^http:\/\/.+?(\/.+)$/', $aData['return_url'], $aMatch)){
                $aData['return_url'] = $aMatch[1];
            }
        }
        if(empty($aData['return_url']) || empty($aData['login'])){
            trigger_error('Not all required paramters given to login user', E_USER_ERROR);
        }
        return $this->loginOrCreateUser($aData);
    }

    /**
     * Return HTML button.
     *
     * @param  array $aScope  Template scope array.
     * @return string
     * @see    AMI_iUserSourceAppDriver::getButton()
     */
    public function getButton(array $aScope = array()){
        $aScope['entry_point'] = $this->getEntryPoint();
        $aScope['rand'] = mt_rand(100, 1000);
        $aScope['driver'] = $this->getDriverName();
        $aScope['lang'] = AMI_Registry::get('lang_data');
        return $this->oService->getTemplate()->parse($this->oService->tplBlockName . ':' . $this->loginButtonSet, $aScope);
    }

    /**
     * Returns driver icon.
     *
     * @param  array $aScope  Template scope
     * @return string
     */
    public function getIcon(array $aScope = array()){
        $aScope['driver'] = $this->getDriverName();
        return $this->oService->getTemplate()->parse($this->oService->tplBlockName . ':' . $this->iconSet, $aScope);
    }

    /**
     * Returns short string hash.
     *
     * @param  string $string  Source string
     * @return string
     */
    public function getShortHash($string){
        return $this->genShortHash(abs(crc32($string)));
    }

    /**
     * Generate short hash.
     *
     * @param  int    $number  String checksum
     * @param  string $res     Result string
     * @return string
     * @amidev
     */
    private function genShortHash($number, $res = ''){
        $base = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $size = strlen($base);
        $division = $number / $size;
        $resInt = floor($number / $size);
        $remnant = $number % $size;
        $res = $base[$remnant] . $res;
        if($resInt > $size){
            return $this->genShortHash($resInt, $res);
        }
        return $base[$resInt] . $res;
    }
}

/**
 * Twitter driver.
 *
 * Config parameters:
 *
 * [ami_twitter]
 *
 * enabled = yes;
 * app_id = '...';
 * consumer_secret = '...';
 *
 * @package Service
 * @since   5.12.0
 */
class Twitter_UserSourceAppDriver extends AMI_UserSourceAppDriver{
    /**
     * Login button set.
     *
     * @var string
     */
    protected $loginButtonSet = 'twitter_login_button';

    /**
     * Icon set.
     *
     * @var string
     */
    protected $iconSet = 'twitter_icon';

    /**
     * Driver id numeric (unique).
     *
     * @var void
     */
    public $driverId = 1;

    /**
     * Driver name.
     *
     * @var void
     */
    public $driverName = 'ami_twitter';

    /**
     * Returns driver button HTML.
     *
     * @return string
     * @see    AMI_iUserSourceAppDriver::getButton()
     */
    public function getButton(){
        return
            isset($this->aConfig['app_id'])
            ? parent::getButton(array('appId' => $this->aConfig['app_id']))
            : '';
    }

    /**
     * Process Verify action.
     *
     * @param  array $aData  Array. Required keys: 'login'.
     * @return bool
     */
    function dispatchVerify(array $aData = array()){

        require_once $GLOBALS['CLASSES_PATH'] . "../lib/oauth.php";

        $oRequest = AMI::getSingleton('env/request');
        $oSession = AMI::getSingleton('env/session');

        if(!$oSession->isStarted()){
            trigger_error('Session is not started', E_USER_ERROR);
        }

        $authToken = $oRequest->get('oauth_token', false);
        $authVerifier = $oRequest->get('oauth_verifier', false);

        $wwwRoot = AMI_Registry::get('path/www_root');
        $aData['return_url'] = $wwwRoot . 'ami_service.php?service=source_app&driver=' . $this->getDriverName() . '&driver_action=redirect&oauth_token=' . $authToken . '&oauth_verifier=' . $authVerifier . '&ami_locale=' . AMI_Registry::get('lang_data', 'en');

        $sessToken = $oSession->twitterOauthToken;

        $ready = $authToken && ($authToken == $sessToken) && $authVerifier;
        if($ready){
            require_once $GLOBALS['CLASSES_PATH'] . "../lib/oauth.php";
            $oTwAuth = new TwitterOAuth($this->aConfig['app_id'], $this->aConfig['consumer_secret'], $oSession->twitterOauthToken, $oSession->twitterOauthTokenSecret);
            $accessToken = $oTwAuth->getAccessToken($authVerifier);
            $oUserInfo = $oTwAuth->get('account/verify_credentials');
            if($oUserInfo->errors){
                $ready = false;
            }
        }
        if($ready){
            $aData['source_app_user_id'] = $oUserInfo->screen_name;
            $aData['login'] = $oUserInfo->screen_name;
            $aData['firstname'] = $oUserInfo->name;
        }else{
            // Old case by Gu
            trigger_error('Can not verify '.$this->getDriverName().' user ID', E_USER_WARNING);
            $oResponse = AMI::getSingleton('response');
            $oTpl = $this->oService->getTemplate();
            $aScope = array('return_url' => $aData['return_url']);
            $oResponse->write(
                $oTpl->parse($this->oService->tplBlockName . ':' . $this->oService->inactiveUserSet, $aScope)
            );
            return null;
        }
        return parent::dispatchVerify($aData);
    }

    /**
     * Process Request action.
     *
     * @param  array $aData  Data array
     * @return void
     */
    function dispatchRequest(array $aData = array()){
        require_once $GLOBALS['CLASSES_PATH'] . "../lib/oauth.php";
        $oTwAuth = new TwitterOAuth($this->aConfig['app_id'], $this->aConfig['consumer_secret']);
        $wwwRoot = AMI_Registry::get('path/www_root');
        $aRequestToken = $oTwAuth->getRequestToken($wwwRoot . 'ami_service.php?service=source_app&driver=' . $this->getDriverName() . '&driver_action=verify&ami_locale=' . AMI_Registry::get('lang_data', 'en'));

        if(($oTwAuth->http_code==200) && $aRequestToken['oauth_callback_confirmed']){
            $url = $oTwAuth->getAuthorizeURL($aRequestToken['oauth_token']);
            $oSession = AMI::getSingleton('env/session');
            if(!$oSession->isStarted()){
                $oSession->start();
            }
            $oSession->twitterOauthToken = $aRequestToken['oauth_token'];
            $oSession->twitterOauthTokenSecret = $aRequestToken['oauth_token_secret'];

            $oResponse = AMI::getSingleton('response');
            $oResponse->HTTP->setRedirect($url);
        }else{
            trigger_error('Twitter authorization failed, site: ' . $wwwRoot, E_USER_ERROR);
        }
    }

    /**
     * Process Redirect action.
     *
     * @param  array $aData  Data array
     * @return void
     */
    function dispatchRedirect(array $aData = array()){
        $oResponse = AMI::getSingleton('response');
        $oResponse->write('<script>window.opener.location.reload(); window.close();</script>');
        $oResponse->send();
        die();
    }
}

/**
 * VKontakte driver.
 *
 * Config paramters:
 *
 * [ami_vkontakte]
 *
 * enabled = yes;
 * api_id = '...';
 * secret =  '...';
 *
 * @package Service
 * @since   5.12.0
 */
class VKontakte_UserSourceAppDriver extends AMI_UserSourceAppDriver{
    /**
     * Driver id numeric (unique).
     *
     * @var void
     */
    public $driverId = 2;

    /**
     * Driver name.
     *
     * @var void
     */
    public $driverName = 'ami_vkontakte';

    /**
     * Login button tempalte set.
     *
     * @var string
     */
    protected $loginButtonSet = 'vkontakte_login_button';

    /**
     * Icon set.
     *
     * @var string
     */
    protected $iconSet = 'vkontakte_icon';

    /**
     * Process verify action.
     *
     * @param  array $aData  Array. Required keys: 'login'.
     * @return bool
     */
    function dispatchVerify(array $aData = array()){
        $oRequest = AMI::getSingleton('env/request');
        // Verify user ID ( VKontakte example code )
        $aSession = array();
        $member = false;
        $aValidKeys = array('expire', 'mid', 'secret', 'sid', 'sig');
        $appCookie = html_entity_decode($oRequest->get('vk_app_' . $this->aConfig ['api_id'], null, 'c'));
        if($appCookie){
            $aSessionData = explode('&', $appCookie, 10);
            foreach($aSessionData as $pair){
                list($key, $value) = explode('=', $pair, 2);
                if(empty($key) || empty($value) || ! in_array($key, $aValidKeys)){
                    continue;
                }
                $aSession[$key] = $value;
            }
            foreach($aValidKeys as $key){
                if(!isset($aSession[$key]))
                    return $member;
            }
            ksort($aSession);
            $sign = '';
            foreach($aSession as $key => $value){
                if($key != 'sig'){
                    $sign .= ($key . '=' . $value);
                }
            }
            $sign .= $this->aConfig['secret'];
            $sign = md5($sign);
            if($aSession['sig'] == $sign &&	$aSession['expire'] > time()){
                $member = array('id' => intval($aSession['mid']), 'secret' => $aSession['secret'], 'sid' => $aSession['sid']);
            }
        }

        if(!$member){
            trigger_error('Can not verify '.$this->getDriverName().' user ID', E_USER_WARNING);
            $oResponse = AMI::getSingleton('response');
            $oTpl = $this->oService->getTemplate();
            $aScope = array('return_url' => $aData['return_url']);
            $oResponse->write(
                $oTpl->parse($this->oService->tplBlockName . ':' . $this->oService->inactiveUserSet, $aScope)
            );
            return null;
        }

        $aData['source_app_user_id'] = 'id'.$member['id'];
        return parent::dispatchVerify($aData);
    }

    /**
     * Getting button HTML.
     *
     * @see    AMI_iUserSourceAppDriver::getButton()
     * @return string
     */
    public function getButton(){
        return
            isset($this->aConfig['api_id'])
            ? parent::getButton(array('apiId' => $this->aConfig['api_id']))
            : '';
    }
}

/**
 * Facebook driver.
 *
 * Config: [ami_facebook]
 * enabled = yes;
 *
 * app_id = '...';
 * secret_id = '...';
 *
 * @package Service
 * @since   5.12.0
 */
class Facebook_UserSourceAppDriver extends AMI_UserSourceAppDriver{
    /**
     * Driver id numeric (unique).
     *
     * @var void
     */
    public $driverId = 3;

    /**
     * Driver name.
     *
     * @var void
     */
    public $driverName = 'ami_facebook';

    /**
     * Enter description here ...
     *
     * @var void
     */
    protected $loginButtonSet = 'facebook_login_button';

    /**
     * Icon set.
     *
     * @var string
     */
    protected $iconSet = 'facebook_icon';

    /**
     * Returns driver button HTML.
     *
     * @see    AMI_iUserSourceAppDriver::getButton()
     * @return string
     */
    public function getButton(){
        return
            isset($this->aConfig['app_id'])
            ? parent::getButton(array('appId' => $this->aConfig['app_id']))
            : '';
    }

    /**
     * Process verify action.
     *
     * @param  array $aData  Array. Required keys: 'login'.
     * @return bool
     */
    function dispatchVerify(array $aData = array()){
        $oRequest = AMI::getSingleton('env/request');
        // Verify user ID ( facebook example code )
        $psr = $this->fb_parse_signed_request(trim(html_entity_decode($oRequest->get('fbsr_'.$this->aConfig['app_id'], null, 'c')), '\\"'), $this->aConfig['secret_id']);
        if(is_null($psr) || !is_array($psr)){
            trigger_error('Can not verify '.$this->getDriverName().' user ID', E_USER_WARNING);
            $oResponse = AMI::getSingleton('response');
            $oTpl = $this->oService->getTemplate();
            $aScope = array('return_url' => $aData['return_url']);
            $oResponse->write(
                $oTpl->parse($this->oService->tplBlockName . ':' . $this->oService->inactiveUserSet, $aScope)
            );
            return null;
        }
        $aData['source_app_user_id'] = 'id' . $psr['user_id'];
        return parent::dispatchVerify($aData);
    }

    /**
     * Facebook parsing singed request function..
     *
     * @param  string $signedRequest  String
     * @param  string $secret         String
     * @return bool
     */
    function fb_parse_signed_request($signedRequest, $secret){
        list($encodedSig, $payload) = explode('.', $signedRequest, 2);
        // decode the data
        $sig = $this->fb_base64_url_decode($encodedSig);
        $data = json_decode($this->fb_base64_url_decode($payload), true);
        if(strtoupper($data['algorithm']) !== 'HMAC-SHA256'){
            trigger_error('Can not verify '.$this->getDriverName().' algorithm', E_USER_WARNING);
            return null;
        }
        // check sig
        $expectedSig = hash_hmac('sha256', $payload, $secret, $raw = true);
        if($sig !== $expectedSig){
             trigger_error('Can not verify '.$this->getDriverName().' JSON signature', E_USER_WARNING);
             return null;
        }
        return $data;
    }

    /**
     * Facebook base64_url_decode function.
     *
     * @param  string $input  String
     * @return string
     */
    function fb_base64_url_decode($input){
        return base64_decode(strtr($input, '-_', '+/'));
    }
}

/**
 * Amiro server driver.
 *
 * @package Service
 * @since   5.12.0
 */
class Amiro_UserSourceAppDriver extends AMI_UserSourceAppDriver{
    /**
     * Driver id numeric (unique).
     *
     * @var void
     */
    public $driverId = 4;

    /**
     * Driver name.
     *
     * @var void
     */
    public $driverName = 'ami_amiro';
}

/**
 * Loginza driver.
 *
 * Config parameters:
 *
 * [ami_loginza]
 *
 * enabled = yes
 * app_id = ...
 * secret_id = ...
 *
 * @package Service
 * @since   5.12.0
 */
class Loginza_UserSourceAppDriver extends AMI_UserSourceAppDriver{
    /**
     * Driver id numeric (unique).
     *
     * @var void
     */
    public $driverId = 5;

    /**
     * Driver name.
     *
     * @var void
     */
    public $driverName = 'ami_loginza';

    /**
     * Name of login button set
     *
     * @var string
     */
    protected $loginButtonSet = 'loginza_login_button';

    /**
     * Icon set.
     *
     * @var string
     */
    protected $iconSet = 'loginza_icon';

    /**
     * Getting button HTML.
     *
     * @see    AMI_iUserSourceAppDriver::getButton()
     * @return string
     */
    public function getButton(){
    	if(!isset($this->aConfig['app_id']) || !isset($this->aConfig['secret_id'])){
            return '';
    	}
        return parent::getButton(array('appId' => $this->aConfig['app_id']));
    }

    /**
     * Process verify action.
     *
     * @param  array $aData  Array.
     * @return bool
     */
    function dispatchVerify(array $aData = array()){
        $oRequest = AMI::getSingleton('env/request');
        $token = $oRequest->get('token');
        if(!isset($token) || !isset($this->aConfig['app_id']) || !isset($this->aConfig['secret_id'])){
            trigger_error('Not all required paramters given.', E_USER_ERROR);
        }
        $aSettings = array('userAgent' => $_SERVER['HTTP_USER_AGENT']);
        $oHTTPRequest = new AMI_HTTPRequest($aSettings);
        $loginzaUserInfo = $oHTTPRequest->send('http://loginza.ru/api/authinfo?token=' . $token . '&id=' . $this->aConfig['app_id'] . '&sig=' . md5($token.$this->aConfig['secret_id']));
        $loginzaUserInfo = json_decode($loginzaUserInfo);
        if(!isset($loginzaUserInfo->identity)){
            trigger_error('Can not verify '.$this->getDriverName().' user ID', E_USER_WARNING);
            $oResponse = AMI::getSingleton('response');
            $oTpl = $this->oService->getTemplate();
            $aScope = array('return_url' => $aData['return_url']);
            $oResponse->write(
                $oTpl->parse($this->oService->tplBlockName . ':' . $this->oService->inactiveUserSet, $aScope)
            );
            return null;
        }
        if(!empty($loginzaUserInfo->nickname)){
            $aData['login'] = $loginzaUserInfo->nickname;
        }elseif(!empty($loginzaUserInfo->name->first_name)){
            $aData['login'] = $loginzaUserInfo->name->first_name;
        }elseif(!empty($loginzaUserInfo->name->last_name)){
            $aData['login'] = $loginzaUserInfo->name->last_name;
        }else{
            $aData['login'] = 'loginza' . $this->getShortHash($loginzaUserInfo->identity);
        }
        if(!empty($loginzaUserInfo->email)){
            $aData['email'] = $loginzaUserInfo->email;
        }
        if(!empty($loginzaUserInfo->name->first_name)){
            $aData['firstname'] = $loginzaUserInfo->name->first_name;
        }
        if(!empty($loginzaUserInfo->name->last_name)){
            $aData['lastname'] = $loginzaUserInfo->name->last_name;
        }
        return parent::dispatchVerify($aData);
    }
}
