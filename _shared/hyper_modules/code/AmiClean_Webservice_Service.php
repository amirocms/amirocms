<?php
/**
 * AmiClean/Webservice configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiClean_AmiSeopult
 * @version   $Id: AmiClean_AmiSeopult_Service.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Seopult service class.
 *
 * @package    Config_AmiClean_AmiSeopult
 * @subpackage Controller
 * @resource   ami_seopult/service
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_Webservice_Service extends AMI_Module_Service{
    /**
     * OK result
     */
    const OK = 'OK';

    /**
     * Common fail, see details in message
     */
    const ERR_FAIL = 'ERR_FAIL';

    const ERR_MISSING_PROTOCOL_VERSION = 'ERR_MISSING_PROTOCOL_VERSION';

    const ERR_UNSUPPORTED_PROTOCOL_VERSION = 'ERR_UNSUPPORTED_PROTOCOL_VERSION';

    const ERR_INVALID_ACTION = 'ERR_INVALID_ACTION';

    const ERR_FULL_ENV_REQUIRED = 'ERR_FULL_ENV_REQUIRED';

    const ERR_PUBLIC_ACCESS_RESTRICTED = 'ERR_PUBLIC_ACCESS_RESTRICTED';

    /**
     * `cms_ami_webservice`.`id_user` appropriate to api key not found in `cms_members`
     */
    const ERR_INVALID_API_USER = 'ERR_INVALID_API_USER';

    const ERR_USER_LOGIN_FAIL = 'ERR_USER_LOGIN_FAIL';

    const ERR_MISSING_REQUIRED_ARGUMENT = 'ERR_MISSING_REQUIRED_ARGUMENT';

    const ERR_INVALID_ARGUMENT_VALUE = 'ERR_INVALID_ARGUMENT_VALUE';

    const ERR_ACCESS_DENIED = 'ERR_ACCESS_DENIED';

    const ERR_ALREADY_AUTHORIZED = 'ERR_ALREADY_AUTHORIZED';

    const ERR_DUPLICATE_USER_EMAIL = 'ERR_DUPLICATE_USER_EMAIL';

    const ERR_FAST_REGISTRATION_DISABLED = 'ERR_FAST_REGISTRATION_DISABLED';

    const ERR_FAST_RERISTRATION_FORCE_REQUIRED = 'ERR_FAST_RERISTRATION_FORCE_REQUIRED';

    const ERR_REGISTRATION_FAILED = 'ERR_REGISTRATION_FAILED';

    const ERR_ITEM_ADD_FAILED = 'ERR_ITEM_ADD_FAILED';

    const ERR_ITEM_NOT_FOUND = 'ERR_ITEM_NOT_FOUND';

    // Public token hardcode
    const PUBLIC_TOKEN = 'app_token_public';

    /**
     * Webservice action access requirements
     *
     * @var array
     */
    protected $aAccessRequirements = array(
        'sys.info' => array(
            'rwd_mask'      => FALSE,
            'auth_required' => FALSE,
            'public_access' => TRUE
        ),
        'sys.auth_check' => array(
            'rwd_mask'      => FALSE,
            'auth_required' => TRUE,
        ),
        'sys.item_add' => array(
            'rwd_mask'      => FALSE,
            'auth_required' => TRUE,
        ),
        'sys.item_list' => array(
            'public_access' => TRUE,
        ),
        'sys.item_get' => array(
            'public_access' => TRUE,
        )
    );

    /**
     * Application name
     *
     * @var string
     */
    protected $appName;

    /**
     * Allowed modules
     *
     * @var string
     */
    protected $modules = '';

    /**
     * Fields that are safe for read from webservice clientside
     *
     * @var array
     */
    protected $aSafeFields = array('id', 'date_created', 'header', 'announce', 'ext_img_small', 'ext_rate_rate', 'ext_rate_count');

    /**
     * Public token data
     *
     * @var array
     */
    protected static $aPublicTokenData = array();

    /**
     * Instance
     *
     * @var AmiClean_AmiSeopult_Service
     */
    private static $oInstance;

    /**
     * Returns AmiSeopult_Service instance.
     *
     * @return AmiSeopult_Service
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
     * Dispatches raw service action.
     *
     * @param  AMI_Request  $oRequest   Request
     * @param  AMI_Response $oResponse  Response
     * @return void
     * @amidev
     */
    public function dispatchAction(AMI_Request $oRequest, AMI_Response $oResponse){

        $oResponse->displayBench();
        AMI::getSingleton('db')->displayQueries(true);

        // Language
        $lang = $oRequest->get('ami_locale', 'en');
        AMI::getSingleton('env/session', AMI::getOption('core', 'allow_multi_lang') ? array('locale' => $lang) : array());
        AMI_Registry::set('lang_data', $lang);
        AMI_Registry::set('lang', $lang);
        AMI::getResource('env/template_sys')->setLocale($lang);
        $aDateFormats = AMI::getOption('core', 'dateformat_front');
        AMI_Registry::get('oGUI')->addGlobalVars(
            array(
                'active_lang' => $lang,
                'date_format' => preg_replace('/ .*$/', '', isset($aDateFormats[$lang]) ? $aDateFormats[$lang] : $aDateFormats['en'])
            )
        );

        // Ext image vars
        AMI_Registry::set('CUSTOM_PICTURES_HTTP_PATH', "_mod_files/ce_images/");
        AMI_Registry::set('MODULE_PICTURES_PATH', AMI_Registry::get('path/root')."_mod_files/");
        AMI_Registry::set('LOCAL_FILES_REL_PATH', "_local/");

        parent::dispatchAction($oRequest, $oResponse);

        self::createPublicTokenRecordIfNotExist();

        $GLOBALS['lang_data'] = AMI_Registry::get('lang_data');
        $oResponse->setType('JSON');

        $version = $oRequest->get('version', FALSE);
        if(FALSE === $version){
            $this->error(
                self::ERR_MISSING_PROTOCOL_VERSION,
                'Missing protocol version'
            );
        }
        if('1.1' != $version){
            $this->error(
                self::ERR_UNSUPPORTED_PROTOCOL_VERSION,
                "Unsupported protocol version " . $version . ", supported versions: 1.1"
            );
        }

        $action = $oRequest->get('action', FALSE);
        $modId = $oRequest->get('modId', FALSE);

        // Event: on_webservice_start
        $aEvent = array(
            'version'     => $version,
            'action'      => $action,
            'modId'       => $modId,
            'oWebService' => $this,
            'aSafeFields' => &$this->aSafeFields
        );

        if($action !== FALSE){
            $this->addWebserviceHandlers($aEvent);
            if($modId !== FALSE){
                try{
                    $oService = AMI::getResource($modId . '/service');
                    $oService->addWebserviceHandlers($aEvent);
                    unset($oService);
                }catch(AMI_Exception $oException){
                    // No resource, do nothing
                }
            }
        }

        /**
         * Event to set any initial data.
         *
         * @event      on_webservice_start         $modId
         * @eventparam string                      action       Action
         * @eventparam AmiClean_Webservice_Service oWebservice  Webservice service object
         */
        AMI_Event::fire('on_webservice_start', $aEvent, AMI_Event::MOD_ANY);

        // Save to registry to be used in front list view class
        AMI_Registry::set($modId . '/webservice/safeFields', $this->aSafeFields);

        // Event: on_webservice_{##action##}_action
        $aEvent = array(
            'version'      => $version,
            'errorCode'    => self::ERR_INVALID_ACTION,
            'errorMessage' => "Invalid '" . $action . "' action"
        );

        // All actions except auth require valid appToken
        if($action != 'sys.auth'){
            $oDB = AMI::getSingleton('db');
            $appToken = $oRequest->get('appToken', FALSE);
            if($appToken === FALSE){
                $this->error(
                    self::ERR_MISSING_REQUIRED_ARGUMENT,
                    "Missing required 'appToken' argumnet"
                );
            }
            if(!strlen($appToken) || ($appToken[0] != 'a')){
                $this->error(
                    self::ERR_INVALID_ARGUMENT_VALUE,
                    "Passed app token '" . $appToken . "' is invalid"
                );
            }
            if($appToken != self::PUBLIC_TOKEN){
                // Check session user by access token
                $sql =
                    "SELECT `id_member`, `data` " .
                    "FROM `cms_sessions` " .
                    "WHERE `token` = %s";
                $aRow = $oDB->fetchRow(
                    DB_Query::getSnippet($sql)
                        ->q($appToken)
                );
                if(($aRow !== FALSE) && $aRow['id_member']){
                    // TODO: check appliocation user rights for specified module if set
                }else{
                    $this->error(
                        self::ERR_INVALID_ARGUMENT_VALUE,
                        "Passed app token '" . $appToken . "' not found"
                    );
                }
                $aData = unserialize($aRow['data']);
                $this->appName = $aData['appName'];
                if($modId){
                    if(!in_array($modId, $aData['modules'])){
                        $this->error(
                            self::ERR_ACCESS_DENIED,
                            "Passed app token '" . $appToken . "' is not valid for module '" . $modId . "'"
                        );
                    }
                }
                unset($aData, $aRow['data']);
            }else{
                // Public app token
                // 1. Check module access
                if($modId){
                    if(!isset(self::$aPublicTokenData['id']) || !self::$aPublicTokenData['id']){
                        $this->error(
                            self::ERR_INVALID_ARGUMENT_VALUE,
                            "Public token not supported"
                        );
                    }
                    $aModules = !empty(self::$aPublicTokenData['modules']) ? explode(';', trim(self::$aPublicTokenData['modules'], ';')) : array();
                    if(!in_array($modId, $aModules)){
                        $this->error(
                            self::ERR_ACCESS_DENIED,
                            "Public token is not valid for module '" . $modId . "'"
                        );
                    }
                }

                // 2. Check action access
                if($this->isPublicAccessAllowed($action)){
                    $this->appName = 'GUEST APPLICATION';
                    AMI_Registry::set('webservice_public_token', true);
                }else{
                    $this->error(
                        self::ERR_PUBLIC_ACCESS_RESTRICTED,
                        "Action '" . $action . "' public access restricted."
                    );
                }
            }

            // TODO: check if user token is needed for method
            if($this->isAuthRequired($action)){
                $this->requireFullEnv();
                if(!AMI::getSingleton('env/session')->isLoggedIn()){
                    $userToken = $oRequest->get('userToken', FALSE);
                    if($userToken !== FALSE){
                        $sql = "SELECT id, id_member FROM cms_sessions WHERE token = %s";
                        $aSession = $oDB->fetchRow(
                            DB_Query::getSnippet($sql)
                                ->q($userToken)
                        );
                        if(mb_strlen($userToken) && ($userToken[0] == 'u') && is_array($aSession)){
                            $sid = $aSession['id'];
                            $uid = $aSession['id_member'];
                            // Check user existance
                            $oUser = AMI::getResourceModel('users/table')->find($uid, array('id', 'login'));
                            if(!$oUser->getId()){
                                $this->error(
                                    self::ERR_INVALID_ARGUMENT_VALUE,
                                    "Passed user token '" . $userToken . "' not found"
                                );
                            }
                            // Authorize with specified user
                            if(isset($GLOBALS['oSession'])){
                                $GLOBALS['oSession']->_isSessionStarted = false;
                                $GLOBALS['oSession']->sid = $sid;
                            }
                            $oSession = AMI::getSingleton('env/session');
                            $oSession->start();
                            $oSession->loginAsUser($oUser->login);
                        }else{
                            $this->error(
                                self::ERR_INVALID_ARGUMENT_VALUE,
                                "Passed user token '" . $userToken . "' is invalid"
                            );
                        }
                    }else{
                        $this->error(
                            self::ERR_MISSING_REQUIRED_ARGUMENT,
                            "Missing required 'userToken' argumnet"
                        );
                    }
                }
            }
        }

        /**
         * Calls webservice action handler.
         *
         * @event      on_webservice_{action}_action $modId
         * @eventparam string                 errorCode     Error code
         * @eventparam string                 errorMessage  Error message
         */
        AMI_Event::fire('on_webservice_{' . $action . '}_action', $aEvent, AMI_Event::MOD_ANY);

        $this->send($aEvent);
    }

    /**
     * Adds web-service handlers.
     *
     * @param  array $aEvent  'on_webservice_start' event data
     * @return void
     */
    public function addWebserviceHandlers(array &$aEvent){
        $aSystemHandlers = array(
            'sys.auth'              => array($this, 'handleAuthAction'),
            'sys.auth_user'         => array($this, 'handleAuthUserAction'),
            'sys.auth_check'        => array($this, 'handleAuthCheckAction'),
            'sys.info'              => array($this, 'handleInfoAction'),
            'sys.item_list'         => array($this, 'handleItemListAction'),
            'sys.item_add'          => array($this, 'handleItemAddAction'),
            'sys.item_get'          => array($this, 'handleItemGetAction'),
            'sys.fast_registration' => array($this, 'handleFastRegistrationAction')
        );
        foreach($aSystemHandlers as $action => $aHandler){
            AMI_Event::addHandler('on_webservice_{' . $action . '}_action', $aHandler, AMI_Event::MOD_ANY);
        }
    }


    /**
     * Handles 'auth' action.
     *
     * @param  string $name     Event name
     * @param  array  &$aEvent  Event data
     * @return array
     */
    public function handleAuthAction($name, array &$aEvent){

        $this->requireFullEnv();

        $oRequest = AMI::getSingleton('env/request');
        $apiKey = $oRequest->get('apiKey', FALSE);
        if($apiKey === FALSE){
            $this->error(
                self::ERR_MISSING_REQUIRED_ARGUMENT,
                "Missing required 'apiKey' argumnet"
            );
        }
        $oWSTable = AMI::getResourceModel('ami_webservice/table');
        $oWSItem = $oWSTable
            ->getItem()
            ->addFields(
                array('id', 'id_user', 'header', 'modules')
            )
            ->addSearchCondition(
                array('api_key' => $apiKey, 'public' => 1)
            )
            ->load();
        if(!$oWSItem->getId()){
            $this->error(
                self::ERR_INVALID_ARGUMENT_VALUE,
                "Passed API key '" . $apiKey . "' not found"
            );
        }
        $this->modules = $oWSItem->modules;
        $uid = $oWSItem->id_user;
        $oUserTable = AMI::getResourceModel('users/table');
        $oUser = $oUserTable->find($uid, array('id', 'login'));
        if(!$oUser->getId()){
            $this->error(
                self::ERR_INVALID_API_USER,
                "Internal error: API user " . $oWSItem->id_user . " for '" . $apiKey . "' not found"
            );
        }
        $this->appName = $oWSItem->header;
        $token = $this->setUserToken('a', $oUser->login);
        if(!$token){
           $this->error(
                self::ERR_INVALID_API_USER,
                "Internal error: API user " . $oWSItem->id_user . " / '" . $oUser->login . "' for '" . $apiKey . "' not found"
            );
        }
        $aEvent['appToken'] = $token;

        $login = $oRequest->get('login', FALSE);
        $password = $oRequest->get('password', FALSE);
        $ok = true;
        if($login !== FALSE){
            $ok = false;
            $userToken = $this->setUserToken('u', $login, $password, true);
            if($userToken){
                $ok = true;
                $aEvent['userToken'] = $userToken;
            }
        }
        if(!$ok){
            // Remove appToken due to error
            $sql = "DELETE FROM cms_sessions WHERE token = %s";
            AMI::getSingleton('db')->query(DB_Query::getSnippet($sql)->q($token));
            $this->error(
                'ERR_USER_LOGIN_FAIL',
                "User '" . $login . "' login failed"
            );
        }
        $this->ok($aEvent);
        $this->cleanupCookies();

        return $aEvent;
    }

    /**
     * Handles 'auth_user' action.
     *
     * @param  string $name     Event name
     * @param  array  &$aEvent  Event data
     * @return array
     */
    public function handleAuthUserAction($name, array &$aEvent){
        $oRequest = AMI::getSingleton('env/request');
        $login = $oRequest->get('login', FALSE);
        $password = $oRequest->get('password', FALSE);
        $ok = FALSE;
        if($login === FALSE){
            $this->error(
                self::ERR_MISSING_REQUIRED_ARGUMENT,
                "Missing required 'login' argumnet"
            );
        }
        if($password === FALSE){
            $this->error(
                self::ERR_MISSING_REQUIRED_ARGUMENT,
                "Missing required 'password' argumnet"
            );
        }
        $userToken = $this->setUserToken('u', $login, $password, true);
        if($userToken){
            $ok = TRUE;
            $aEvent['userToken'] = $userToken;
        }else{
            // Remove accessToken due to error
            $sql = "DELETE FROM `cms_sessions` WHERE `token` = %s";
            AMI::getSingleton('db')->query(DB_Query::getSnippet($sql)->q($userToken));
            $this->error(
                'ERR_USER_LOGIN_FAIL',
                "User '" . $login . "' login failed"
            );
        }
        $this->ok($aEvent);
        $this->cleanupCookies();

        return $aEvent;
    }

    /**
     * Handles 'auth_check' action.
     *
     * @param  string $name     Event name
     * @param  array  &$aEvent  Event data
     * @return array
     */
    public function handleAuthCheckAction($name, array &$aEvent){
        $this->ok($aEvent);

        return $aEvent;
    }

    /**
     * Handles 'item_list' action.
     *
     * @param  string $name     Event name
     * @param  array  &$aEvent  Event data
     * @return array
     */
    public function handleItemListAction($name, array &$aEvent){
        AMI_Registry::set('AMI/Module/Environment/bodyType', 'items');
        $this->validateOnItemList();

        $modId = $this->oRequest->get('modId');
        AMI_Registry::set('modId', $modId);
        AMI_Registry::set('modEngine', '60');
        $this->oRequest->set('mod_action', 'list_view');
        $this->oRequest->set('componentId', 'ami_webservice_1');

        // Map API request arguments to list component arguments
        foreach(
            array(
                'modId' => 'mod_id',
                'sortCol' => 'sort_column',
                'sortDir' => 'sort_dir',
                'getFoundRows' => 'calc_found_rows'
            ) as $from => $to
        ){
            $value = $this->oRequest->get($from, FALSE);
            if($value !== FALSE){
                $this->oRequest->set($to, $value);
            }
        }

        // @todo: Discard usage of hardcoded 'ami_webservice'
        $oController = AMI::getResource('ami_webservice/module/controller/frn', array($this->oRequest, $this->oResponse));
        $oController->init();

        $aEvent = array();
        AMI_Event::fire('dispatch_request', $aEvent, 'ami_webservice');

        $aViews = $oController->getViews();

        foreach($aViews as $oView){
            $componentResponse = $oView->get();
            if(is_array($componentResponse)){
                $aEvent += $componentResponse;
                break;
            }
        }

        $this->ok($aEvent);

        return $aEvent;
    }

    /**
     * Handles 'item_get' action.
     *
     * @param  string $name     Event name
     * @param  array  &$aEvent  Event data
     * @return array
     */
    public function handleItemGetAction($name, array &$aEvent){
        $this->validateOnItemGet();

        $modId = $this->oRequest->get('modId');
        $itemId = $this->oRequest->get('itemId');

        $oTable = AMI::getResourceModel($modId . '/table', array(array('extModeOnConstruct' => 'common')));
        $oItem = $oTable->find($itemId);

        if(!$oItem->getId()){
            $this->error(
                self::ERR_ITEM_NOT_FOUND,
                "Requested item '" . $itemId . "' not found for module '" . $modId . "'"
            );
        }
        $aData = array();
        foreach($oItem->getData() as $field => $value){
            if(in_array($field, $this->aSafeFields)){
                $aData[$field] = $value;
            }
        }

        $aEvent['item'] = $aData;
        $this->ok($aEvent);

        return $aEvent;
    }

    /**
     * Handles 'item_get' action.
     *
     * @param  string $name     Event name
     * @param  array  &$aEvent  Event data
     * @return array
     * @todo   Implement
     */
    public function handleItemAddAction($name, array &$aEvent){
        $this->requireFullEnv();
        $this->validateItemAccess();

        $this->error(
            self::ERR_ACCESS_DENIED,
            "Access denied for action '" . $this->oRequest->get('action', '') .
            "' in module '" . $this->oRequest->get('modId', '') . "'"
        );
    }

    /**
     * Handles 'info' action.
     *
     * @param  string $name     Event name
     * @param  array  &$aEvent  Event data
     * @return array
     * @todo   Implement
     */
    public function handleInfoAction($name, array &$aEvent){
        // TODO: use real data
        $aEvent +=
            array(
                'title'         => 'CMS Amiro',
                'description'   => 'CMS Amiro description',
                'logo'          => 'http://www.amiro.ru/_img/dez_logo.png',
                'contacts'      => array(
                    'address'       => 'Moscow',
                    'phone'         => '+7(495) 287-08-62 ',
                    'email'         => 'info@amiro.ru',
                    'website'       => 'http://www.amiro.ru'
                )
            );
        $this->ok($aEvent);

        return $aEvent;
    }


    /**
     * Handles 'fast_registration' action.
     *
     * @param  string $name     Event name
     * @param  array  &$aEvent  Event data
     * @return array
     */
    public function handleFastRegistrationAction($name, array &$aEvent){
        $this->section = 'eshop';
        $this->aOptions = array(
            'allowFastRegistration' =>
                AMI::getOption($this->section . '_order', 'fast_register'),
            'allowSameEmail' =>
                AMI::getOption($this->section . '_order', 'allow_user_email_double'),
            'notifyByEmail' =>
                AMI::getOption($this->section . '_order', 'send_registration_email')
        );

        $this->validateOnOrderUserCreate();

        global $frn, $db;

        $aUser = array(
            'ip' => getenv('REMOTE_ADDR')
        );
        foreach(array('email', 'phone', 'firstname', 'lastname') as $arg){
            $aUser[$arg] = $this->oRequest->get($arg);
        }
        
        $fields = 'email|phone|firstname|lastname';

        // CMS-11712
        // Allow to use username and password fields for fast registration
        $canGenUsername = TRUE;
        $aGenFields = array('username', 'password');
        foreach(array('username', 'password') as $arg){
            $requestParam = ($arg == 'username') ? 'login' : $arg;
            if($this->oRequest->get($requestParam, FALSE) !== FALSE){
                $aUser[$arg] = $this->oRequest->get($requestParam);
                unset($aGenFields[array_search($arg, $aGenFields)]);
                if($arg === 'username'){
                    $canGenUsername = FALSE;
                }
                $fields .= ('|' . $arg);
            }
        }
        $genString = implode('|', $aGenFields);

        $frn->Member->setUsed($fields, TRUE, TRUE, TRUE);
        $frn->Member->setObligatory($fields, TRUE, TRUE);

        if($frn->Core->IsFrontAllowed('members')){
            // Check e-mail first
            $oUser =
                AMI::getResourceModel('users/table')
                ->findByFields(
                    array('email' => $aUser['email']),
                    array('id')
                );
            $doesEmailExists = $oUser->id > 0;
            unset($oUser);
            $allowSameEmail = $this->aOptions['allowSameEmail'];
            $doForceGenUsername = $canGenUsername && $doesEmailExists && $allowSameEmail && ($frn->Member->getLoginField() == 'email');
            if($doesEmailExists && !$allowSameEmail){
                sleep(1); // Prevent autogenerating
                $this->error(
                    self::ERR_DUPLICATE_USER_EMAIL,
                    'Duplicate user e-mail during registration'
                );
            }elseif($doForceGenUsername && !$this->oRequest->get('forceCreate', FALSE)){
                sleep(1); // Prevent autogenerating
                $this->error(
                    self::ERR_FAST_RERISTRATION_FORCE_REQUIRED,
                    "'forceCreate' parameter is required"
                );
            }
            AMI::setOption('members', 'confirmation_type', 'none');
            $oUser =
                $frn->Member->createMember(
                    $frn,
                    $db,
                    $aUser,
                    $this->aOptions['notifyByEmail'],
                    $genString,
                    !empty($doForceGenUsername) && $this->oRequest->get('forceCreate', FALSE),
                    $doesEMailExists && $allowSameEMail
                );
            unset($aUser);

            if($oUser){
                $aEvent['userToken'] = $this->setUserToken('u', $oUser->login, $oUser->password, TRUE);
                if(FALSE === $aEvent['userToken']){
                    unset($aEvent['userToken']);
                    $this->cleanupCookies();
                    $this->error(
                        self::ERR_REGISTRATION_FAILED,
                        'User creation during eshop order is failed'
                    );
                }
                $frn->Member->SetDbTablePrefix($frn->Eshop->dbTablePrefix);
                $frn->Member->replaceUser($frn, $db, $oUser->id, 1);
            }else{
                $this->cleanupCookies();
                $this->error(
                    self::ERR_REGISTRATION_FAILED,
                    'User creation during eshop order is failed'
                );
            }
            $frn->Member->updateCacheStatus();
            $this->cleanupCookies();
        }

        $this->ok($aEvent);

        return $aEvent;
    }

    /**
     * Sets user authorization requirement for webservice action.
     *
     * @param  string $action    Action
     * @param  bool   $required  Reuirement of authorization
     * @return AmiClean_Webservice_Service
     */
    public function setAuthRequired($action, $required = TRUE){
        if(!isset($this->aAccessRequirements[$action])){
            $this->aAccessRequirements[$action] = array();
        }
        $this->aAccessRequirements[$action]['auth_required'] = $required;

        return $this;
    }

    /**
     * Set public access restrictions for specified action.
     *
     * @param  string $action   Action
     * @param  bool   $allowed  Is public access allowed
     * @return AmiClean_Webservice_Service
     */
    public function setPublicAccess($action, $allowed = TRUE){
        if(!isset($this->aAccessRequirements[$action])){
            $this->aAccessRequirements[$action] = array();
        }
        $this->aAccessRequirements[$action]['public_access'] = $allowed;
        return $this;
    }

    /**
     * Sets OK code.
     *
     * @param  array $aEvent  Event data
     * @return void
     */
    public function ok(array &$aEvent){
        $aEvent =
            array(
                'errorCode'    => self::OK,
                'errorMessage' => '',
            ) + $aEvent;
    }

    /**
     * Sends error.
     *
     * @param string $code     Error code
     * @param string $message  Error message
     * @return void
     */
    public function error($code, $message){
        trigger_error($message, E_USER_WARNING);
        $aOutput = array();
        $this->send(
            array(
                'errorCode'    => $code,
                'errorMessage' => $message
            ) + $aOutput
        );
    }

    /**
     * Send response.
     *
     * @param  mixed $response  Response (string or array)
     * @param  int   $code      HTTP code
     * @return void
     * @exitpoint
     */
    protected function send($response, $code = 200){
        if(AMI::isResource('response')){
            //AMI::getSingleton('response')->setType('HTML');
            $this->sendUsingResponseObject($response, $code);
        }else{
            $this->sendRawResponse($response, $code);
        }
    }

    /**
     * Used to require full environment inside action handler.
     *
     * @return AmiClean_Webservice_Service
     */
    public function requireFullEnv(){
        if(AMI::getEnvMode() != 'full'){
            $this->error(self::ERR_FULL_ENV_REQUIRED, 'Full environment required for this action');
        }
        return $this;
    }

    /**
     * Sets new safe fields list.
     *
     * @param array $aSafeFields  List of fields that are safe to read by webservice cliet application
     * @return AmiClean_Webservice_Service
     */
    public function setSafeFields(array $aSafeFields){
        $this->aSafeFields = $aSafeFields;
        return $this;
    }

    /**
     * Validates request on user creating during eshop order.
     *
     * @return void
     */
    protected function validateOnOrderUserCreate(){
        $this->requireFullEnv();

        if(!$this->aOptions['allowFastRegistration']){
            $this->error(
                self::ERR_FAST_REGISTRATION_DISABLED,
                "Fast user registration during eshop order creating is disabled"
            );
        }

        global $frn;

        /*
        $isLoggedIn = is_object($frn->Member) && $frn->Member->isLoggedIn();
        if($isLoggedIn){
            $this->error(
                self::ERR_ALREADY_AUTHORIZED,
                "User is already authorized"
            );
        }
        */

        foreach(array('email', 'phone', 'firstname', 'lastname') as $arg){
            $value = $this->oRequest->get($arg, FALSE);
            if(FALSE === $value){
                $this->error(
                    self::ERR_MISSING_REQUIRED_ARGUMENT,
                    "Missing required '" . $arg . "' argument"
                );
            }
            if('' === $value){
                $this->error(
                    self::ERR_INVALID_ARGUMENT_VALUE,
                    "Invalid argument '" . $arg . "' value"
                );
            }
        }
    }

    /**
     * Callback mapping table item model to array.
     *
     * @param  AMI_ModTableItem $oItem  Table item model
     * @return array
     * @see    this::onEshopOrderHistoryDetails()
     */
    protected function cbItemToArray($oItem){
        return $oItem->getData();
    }

    /**
     * Validates request on item list request.
     *
     * @return void
     */
    protected function validateOnItemList(){
        $this->validateItemAccess();

        foreach(array('getFoundRows', 'limit', 'offset') as $notFirst => $arg){
            $value = (int)$this->oRequest->get($arg, FALSE);
            if(FALSE !== $value){
                if(
                    ($value < 0) ||
                    (
                        $notFirst
                            ? FALSE
                            : $value != 0 && $value != 1
                    )
                ){
                    $this->error(
                        self::ERR_INVALID_ARGUMENT_VALUE,
                        "Invalid '" . $arg . "' argument value '" . $value . "'"
                    );
                }
            }
        }

        $arg = 'sortDir';
        $value = $this->oRequest->get($arg, FALSE);
        if(
            FALSE !== $value &&
            !in_array($value, array('asc', 'desc', 'rand'))
        ){
            $this->error(
                self::ERR_INVALID_ARGUMENT_VALUE,
                "Invalid '" . $arg . "' argument value '" . $value . "'"
            );
        }

        $arg = 'fields';
        $value = $this->oRequest->get($arg, FALSE);
        if(FALSE !== $value){
            $aValue = json_decode($value);
            if(!is_array($aValue) || !$aValue){
                $this->error(
                    self::ERR_INVALID_ARGUMENT_VALUE,
                    "Invalid '" . $arg . "' argument value '" . $value . "'"
                );
            }
        }
    }

    /**
     * Validates request on item details request.
     *
     * @return void
     */
    protected function validateOnItemGet(){
        $this->validateItemAccess();

        $arg = 'itemId';
        $value = $this->oRequest->get($arg, FALSE);
        if(FALSE === $value){
            $this->error(
                self::ERR_MISSING_REQUIRED_ARGUMENT,
                "Missing required 'itemId' argument"
            );
        }
        if('' === $value){
            $this->error(
                self::ERR_INVALID_ARGUMENT_VALUE,
                "Invalid 'itemId' argument value ''"
            );
        }
    }

    /**
     * Validates request on item access request.
     *
     * @return void
     */
    protected function validateItemAccess(){
        $modId = $this->oRequest->get('modId', FALSE);
        if($modId === FALSE){
            $this->error(
                self::ERR_MISSING_REQUIRED_ARGUMENT,
                "Missing required 'modId' argument"
            );
        }
        if(!AMI::validateModId($modId)){
            $this->error(
                self::ERR_INVALID_ARGUMENT_VALUE,
                "Invalid 'modId' argument value '" . $modId . "'"
            );
        }
        $oDeclarator = AMI_ModDeclarator::getInstance();
        if(!$oDeclarator->isRegistered($modId)){
            $this->error(
                self::ERR_INVALID_ARGUMENT_VALUE,
                "Module '" . $modId . "' isn't instaled"
            );
        }
        list($hypermod, ) = $oDeclarator->getHyperData($modId);
        if((!in_array($hypermod, array('ami_catalog', 'ami_clean', 'ami_multifeeds', 'ami_multifeeds5'))) && ('eshop_order' !== $modId)){
            $this->error(
                self::ERR_ACCESS_DENIED,
                "Access denied for action '" . $this->oRequest->get('action', '') . "' in module '" . $modId . "'"
            );
        }
    }

    /**
     * Check access requirements (todo).
     *
     * @return bool
     * @todo   Implement
     */
    protected function checkAccessRequirements(){
        $oRequest = AMI::getSingleton('env/request');
        $action = $oRequest->get('action');
        return true;
    }

    /**
     * Generates user token and sets it to session.
     *
     * @param char $prefix            Token prefix ('a' or 'u' for appToken and userToken)
     * @param string $login           User login
     * @param string $password        User password
     * @param bool $requiresPassword  Requires password to login user if true
     * @return boolean|string
     */
    protected function setUserToken($prefix, $login, $password = '', $requiresPassword = false){
        // Add special hack
        if(isset($GLOBALS['oSession'])){
            $GLOBALS['oSession']->_isSessionStarted = false;
            $GLOBALS['oSession']->sid = '';
        }
        $oSession = AMI::getSingleton('env/session');
        $aSessionData = $oSession->start(true);
        $sessionCookie = array_shift(array_keys($aSessionData));
        if($requiresPassword){
            if(!$oSession->login($login, $password)){
                return FALSE;
            }
        }else{
            if(!$oSession->loginAsUser($login)){
                return FALSE;
            }
            $this->appName = ''; // todo: is it needed?
        }
        $sessionId = $oSession->getId();
        $token = str_replace('.', '-', uniqid($prefix, true)) . rand(0, 9);
        $aData = array(
            'token' => $token
        );
        if(!$requiresPassword){
            $aModules = strlen($this->modules) ? explode(';', trim($this->modules, ';')) : array();
            $aData['data'] = serialize(
                array(
                    'appName' => $this->appName,
                    'modules' => $aModules
                )
            );
        }
        $oQuery =
            DB_Query::getUpdateQuery(
                'cms_sessions',
                $aData,
                DB_Query::getSnippet('WHERE id = %s')->q($sessionId)
            );
        AMI::getSingleton('db')->query($oQuery);
        unset($_COOKIE['session_']);

        return $token;
    }

    /**
     * Check if authorization required for action.
     *
     * @param string $action  Action
     * @return bool
     */
    protected function isAuthRequired($action){
        $result =
            isset($this->aAccessRequirements[$action]) &&
            isset($this->aAccessRequirements[$action]['auth_required'])
                ? $this->aAccessRequirements[$action]['auth_required']
                : FALSE;

        return $result;
    }

    /**
     * Check if authorization required for action.
     *
     * @param string $action  Action
     * @return bool
     */
    protected function isPublicAccessAllowed($action){
        $result =
            isset($this->aAccessRequirements[$action]) &&
            isset($this->aAccessRequirements[$action]['public_access'])
                ? $this->aAccessRequirements[$action]['public_access']
                : FALSE;

        return $result;
    }

    /**
     * Cleanup session cookies.
     *
     * @return AmiClean_Webservice_Service
     */
    public function cleanupCookies(){
        $this->oResponse->HTTP->setCookie('session_', '', 1);
        $this->oResponse->HTTP->setCookie('session_en', '', 1);
        $this->oResponse->HTTP->setCookie('is_logged_in', '', 1);
        $this->oResponse->HTTP->setCookie('is_cart_filled', '', 1);
        $this->oResponse->HTTP->setCookie('user_session', '', 1);

        return $this;
    }

    /**
     * Allow public access for specified module.
     *
     * @param string $modId  Module Id
     * @return AmiClean_Webservice_Service
     */
    public function allowPublicAccess($modId){
        if(!$modId){
            return;
        }
        self::createPublicTokenRecordIfNotExist();
        $oTable = AMI::getResourceModel('ami_webservice/table');
        $oItem =
            $oTable
                ->getItem()
                ->addFields(array('id', 'modules'))
                ->addSearchCondition(
                    array(
                        'is_sys'  => 1,
                        'id_user' => 0,
                        'api_key' => ''
                    )
                )
                ->load();
        $aModules = !empty($oItem->modules) ? explode(';', trim($oItem->modules, ';')) : array();
        if(!in_array($modId, $aModules)){
            $aModules[] = $modId;
            $oItem->modules = implode(';', $aModules) . ';';
            $oItem->save();
        }
    }

    /**
     * Adds field name as "safe" for public access.
     *
     * @param string $field  Module's model field name
     * @return void
     */
    public function addSafeField($field){
        if(!in_array($field, $this->aSafeFields)){
            $this->aSafeFields[] = $field;
        }
        return $this;
    }

    /**
     * Creates public token if not created yet.
     *
     * @return void
     */
    public static function createPublicTokenRecordIfNotExist(){
        // Could be run in non-module environment
        AMI_Registry::set('ami_allow_model_save', true);
        $oTable = AMI::getResourceModel('ami_webservice/table');
        $oItem =
            $oTable
                ->getItem()
                ->addFields(array('id', 'id_user', 'api_key', 'header', 'is_sys', 'modules'))
                ->addSearchCondition(
                    array(
                        'is_sys'  => 1,
                        'id_user' => 0,
                        'api_key' => ''
                    )
                )
                ->load();
        if(!$oItem->getId()){
            $oItem = $oTable->add(
                array(
                    'active'   => true,
                    'id_user'  => 0,
                    'api_key'  => '',
                    'header'   => 'App. Public Token',
                    'announce' => 'Use "' . self::PUBLIC_TOKEN . '" application token for webservice public access',
                    'is_sys'   => 1,
                    'modules'  => ''
                )
            );
            $oItem->save();
        }
        self::$aPublicTokenData = $oItem->getData();
    }

    /**
     * Creates public token if not created yet.
     *
     * @param string $modId   Module ID
     * @param string $apiKey  API Key
     * @param string $header  Key description
     *
     * @return boolean
     */
    public static function createApiKey($modId, $apiKey='', $header = '', $isSys = FALSE){
        $oUser = AMI::getSingleton('env/session')->getUserData();
        $userId = ($oUser) ? (int)$oUser->getId() : null;
        if(!$userId){
            return FALSE;
        }
        if(!$apiKey){
            $apiKey = strtoupper(substr(md5(time()), 0, 31));
            $apiKey[10] = '-';
            $apiKey[21] = '-';
        }
        $oTable = AMI::getResourceModel('ami_webservice/table');
        $oItem = $oTable->add(
            array(
                'active'   => true,
                'id_user'  => $userId,
                'api_key'  => $apiKey,
                'header'   => $header ? $header : $modId . ' API key',
                'announce' => '',
                'is_sys'   => $isSys ? 1 : 0,
                'modules'  => $modId . ';'
            )
        );
        $oItem->save();
        return TRUE;
    }

    /**
     * Singleton cloning.
     */
    private function __clone(){
    }
}
