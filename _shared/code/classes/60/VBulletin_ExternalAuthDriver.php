<?php
/**
 * VBulletin integration driver.
 *
 * @copyright  Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category   AMI
 * @package    Service
 * @version    $Id: VBulletin_ExternalAuthDriver.php 48191 2014-02-25 13:37:31Z Leontiev Anton $
 * @since      5.12.4
 * @filesource
 */

/**
 * VBulletin common auth driver class.
 *
 * @package Service
 * @since   5.12.4
 */
class VBulletin_ExternalAuthDriver extends AMI_ExternalAuthDriver{
    /**
     * An associative array with driver specific options
     *
     * Keys:
     * - 'forum_url' - vBulletin URL;
     * - 'db_host' - Database: host;
     * - 'db_username' - Database: username;
     * - 'db_password' - Database: password;
     * - 'db_flags' - Database: client flags (since 6.0.6)
     * - 'db_database' - Database: name;
     * - 'db_prefix' - Database: tables prefix;
     * - 'cookie_ttl' - Cookies: time to live (seconds);
     * - 'cookie_path' - Cookies: path;
     * - 'secret_key' - Secret hash used for vBulletin authorization through social networks.
     *
     * @var array
     */
    protected $aSettings = array(
        'forum_url'   => '',
        'db_host'     => '',
        'db_username' => '',
        'db_flags'    => 0,
        'db_password' => '',
        'db_database' => '',
        'db_prefix'   => '',
        'cookie_ttl'  => 3600,
        'cookie_path' => '/',
        'secret_key'  => 'Default secret key',
    );

    /**
     * Link to an external database connection
     *
     * @var resource
     */
    private $db = null;

    /**
     * Temporary storage for a password
     *
     * @var string
     */
    private $password;

    /**
     * Returns an instance of VBulletin_ExternalAuthDriver.
     *
     * @return VBulletin_ExternalAuthDriver
     */
    public static function getInstance(){
        if(is_null(self::$oInstance)){
            self::$oInstance = new VBulletin_ExternalAuthDriver();
        }
        return self::$oInstance;
    }

    /**
     * Initializes external auth driver.
     *
     * @param  array $aSettings  Settings array
     * @return bool
     */
    public function init(array $aSettings=array()){
        parent::init($aSettings);

        // Sets a database connection
        if(!$this->_connectToExternalDatabase()){
            return FALSE;
        }

        // Add event handlers
        AMI_Event::addHandler('on_after_user_create', array($this, 'afterCreate'), AMI_Event::MOD_ANY);
        AMI_Event::addHandler('on_after_user_update', array($this, 'afterUpdate'), AMI_Event::MOD_ANY);
        AMI_Event::addHandler('on_before_user_login', array($this, 'beforeLogin'), AMI_Event::MOD_ANY);
        AMI_Event::addHandler('on_after_user_login', array($this, 'afterLogin'), AMI_Event::MOD_ANY);
        AMI_Event::addHandler('on_before_user_logout', array($this, 'beforeLogout'), AMI_Event::MOD_ANY);

        return TRUE;
    }

    /**
     * Custom handler for on_after_user_create event.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function afterCreate($name, array $aEvent, $handlerModId, $srcModId){
        $oUser = isset($aEvent['oUser']) ? $aEvent['oUser'] : null;

        // check $oUser object
        if(is_null($oUser) || !($oUser instanceof Users_TableItem)){
            $aEvent['error'] = array(
                'code'    => AMI_ExternalAuthDriver::ERR_WRONG_OBJECT,
                'message' => AMI_ExternalAuthDriver::MSG_WRONG_OBJECT);
            return $aEvent;
        }

        $oUser->login = strtolower($oUser->login);

        $aDebug = array(
            $oUser->login,
            $oUser->password
        );

        // Check if user do exist in vBulletin
        $query =
            "SELECT 1 " .
            "FROM `" . $this->aSettings['db_prefix'] . "user` " .
            "WHERE `username` = '" . $oUser->login . "'";
        $result = mysql_query($query, $this->db);
        $userExists = mysql_num_rows($result);
        if(0 === $userExists){ // If user do not exist in vBulletin, create
            $this->_vbCreateUser($oUser->login, $oUser->password, $oUser->email);
        }else{ // Else, update the password and email to correspond new ones
            $this->_vbUpdateUser($oUser->login, $oUser->password, $oUser->email);
        }
        return $aEvent;
    }

    /**
     * Custom handler for on_after_user_update event.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function afterUpdate($name, array $aEvent, $handlerModId, $srcModId){
        // Protect from the recursive actions
        if(AMI_Registry::get('vb_acton', false)){
            AMI_Registry::delete('vb_acton');
            return $aEvent;
        }

        $oUser = isset($aEvent['oUser']) ? $aEvent['oUser'] : null;

        // check $oUser object
        if(is_null($oUser) || !($oUser instanceof Users_TableItem)){
            $aEvent['error'] = array(
                'code' => AMI_ExternalAuthDriver::ERR_WRONG_OBJECT,
                'message' => AMI_ExternalAuthDriver::MSG_WRONG_OBJECT);
            return $aEvent;
        }

        // Password is not avaiable from the $oUser object, we'll take it from the Request
        $oRequest = AMI::getSingleton('env/request');
        $aRequest = $oRequest->getScope();
        $password = isset($aRequest['password']) ? $aRequest['password'] : '';
        $this->_vbUpdateUser($oUser->login, $password, $oUser->email);

        return $aEvent;
    }

    /**
     * Custom handler for on_before_user_login event.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function beforeLogin($name, array $aEvent, $handlerModId, $srcModId){
        // We need to save the password, it will be used in "afterLogin" event handler
        $this->password = isset($aEvent['password']) ? $aEvent['password'] : '';
        return $aEvent;
    }

    /**
     * Custom handler for on_after_user_login event.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function afterLogin($name, array $aEvent, $handlerModId, $srcModId){
        // Protect from the recursive actions
        if(AMI_Registry::get('vb_acton', false)){
            AMI_Registry::delete('vb_acton');
            return $aEvent;
        }

        $oUser = isset($aEvent['oUser']) ? $aEvent['oUser'] : null;

        // check $oUser object
        if(is_null($oUser) || !($oUser instanceof Users_TableItem)){
            $aEvent['error'] = array(
                'code'    => AMI_ExternalAuthDriver::ERR_WRONG_OBJECT,
                'message' => AMI_ExternalAuthDriver::MSG_WRONG_OBJECT);
            return $aEvent;
        }

        // We do not have a password property in the $oUser object
        // but we can get it from beforeLogin event handler
        $password = $this->password;
        $secretKey = false;

        // If no password, it must be social network login, we use secret hash for this case
        if(!$password && $this->aSettings['secret_key']){
             $secretKey = $this->_genSecretKey($oUser->login);
        }

        $passwordHash = md5($password);

        $query =
            "SELECT `userid`, `salt`, `password` " .
            "FROM `" . $this->aSettings['db_prefix'] . "user` " .
            "WHERE `username` = '" . $oUser->login . "'";
        $result = mysql_query($query, $this->db);
        $aUserRow = mysql_fetch_assoc($result);

        if(!$secretKey){
            // If user do not exist in vBulletin, create one
            if(false === $aUserRow){
                $userID = $this->_vbCreateUser($oUser->login, $password, $oUser->email);
                $aUserRow = array('userid' => $userID, 'salt' => '', 'password' => md5($passwordHash));
            }

            // Check if the password is correct, update if not
            $vbPassword = md5($passwordHash . $aUserRow['salt']);
            if($aUserRow['password'] != $vbPassword){
                $this->_vbUpdateUser($oUser->login, $password);
            }
        }

        // Prepare URL
        $url = rtrim($this->aSettings['forum_url'], '/') . '/login.php';

        // Prepare POST data
        $aPostFields = array(
            'vb_login_username'        => $oUser->login,
            'vb_login_password'        => '',
            's'                        => '',
            'do'                       => 'login',
            'vb_login_md5password'     => $passwordHash,
            'vb_login_md5password_utf' => $passwordHash
        );

        // Send secret key if it was set
        if($secretKey){
            $aPostFields['amiro_key'] = $secretKey;
        }

        // Request settings
        $aSettings = array(
            'returnHeaders' => TRUE,
            'userAgent'     => $_SERVER['HTTP_USER_AGENT']
        );

        // Execute a POST request
        $oHTTPRequest = new AMI_HTTPRequest($aSettings);
        $headers = $oHTTPRequest->send($url, $aPostFields, AMI_HTTPRequest::METHOD_POST);

        // Set cookies from headers
        $this->_setCookies($headers);

        // Update user session with real data
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $idHash = md5($_SERVER['HTTP_USER_AGENT'] . $ipAddress);
        $query =
            "UPDATE `" . $this->aSettings['db_prefix'] . "session` " .
            "SET `host` = '" . $ipAddress . "', `idhash` = '" . $idHash . "' " .
            "WHERE `userid` = '" . $aUserRow['userid'] . "'";
        mysql_query($query, $this->db);

        return $aEvent;
    }

    /**
     * Custom handler for on_before_user_logout event.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function beforeLogout($name, array $aEvent, $handlerModId, $srcModId){
        // Clear vBulletin cookie "bbsessionhash"
        AMI::getSingleton('response')->HTTP->setCookie('bbsessionhash', '', 1, $this->aSettings['cookie_path']);

        return $aEvent;
    }

    /**
     * Checks if specified secret key is valid for current username.
     *
     * @param  string $username   User login
     * @param  string $remoteKey  Secret key to check
     * @return bool
     */
    public function checkSecretKey($username, $remoteKey){
        return $this->_genSecretKey($username) == $remoteKey;
    }

    /**
     * Generates a secret key for specified username and current website.
     *
     * @param  string $username  User login
     * @return string
     */
    private function _genSecretKey($username){
        return md5($this->aSettings['secret_key'] . $this->aSettings['db_username'] . $username);
    }

    /**
     * Establishes a connection with an external database.
     *
     * @access private
     * @return bool
     */
    private function _connectToExternalDatabase(){
        if(is_null($this->db)){
            $this->db = mysql_connect(
                $this->aSettings['db_host'],
                $this->aSettings['db_username'],
                $this->aSettings['db_password'],
                TRUE,
                $this->aSettings['db_flags']
            );
            if($this->db === FALSE){
                return FALSE;
            }
            return mysql_select_db($this->aSettings['db_database'], $this->db);
        }
        return TRUE;
    }

    /**
     * Creates a new user in the vBulletin database.
     *
     * @param  string $username  New username
     * @param  string $password  Password
     * @param  string $email     Email address
     * @return mixed
     */
    private function _vbCreateUser($username, $password, $email){
        // Create a user with the same login and password in vBulletin DB
        $query = "INSERT INTO `" . $this->aSettings['db_prefix'] . "user` SET
            `usergroupid`    = 2,
            `membergroupids` = '',
            `displaygroupid` = 0,
            `username`       = '" . $username . "',
            `password`       = '" . md5(md5($password)) . "',
            `passworddate`   = NOW(),
            `joindate`       = '" . time() . "',
            `email`          = '" . $email . "',
            `usertitle`      = 'Junior Member',
            `options`        = '45108295'";

        mysql_query($query, $this->db);

        // Neccessary to add empty records to these tables
        $userID = mysql_insert_id();
        if($userID){
            $query = "INSERT INTO `" . $this->aSettings['db_prefix'] . "userfield` SET `userid` = '" . $userID . "'";
            mysql_query($query, $this->db);
            $query = "INSERT INTO `" . $this->aSettings['db_prefix'] . "usertextfield` SET `userid` = '" . $userID . "'";
            mysql_query($query, $this->db);

            return $userID;
        }

        return FALSE;
    }

    /**
     * Update vBulletin user.
     *
     * @param  string $username  New username
     * @param  string $password  Password
     * @param  string $email     Email address
     * @return bool
     */
    private function _vbUpdateUser($username, $password = FALSE, $email = FALSE){
        // Get userdata from vBulletin database
        $query =
            "SELECT `userid`, `salt` " .
            "FROM `" . $this->aSettings['db_prefix'] . "user` " .
            "WHERE `username` = '" . $username . "'";
        $result = @mysql_query($query, $this->db);
        $userRow = @mysql_fetch_assoc($result);
        if(FALSE === $userRow){
            return FALSE;
        }

        $userID = $userRow['userid'];
        $userSalt = $userRow['salt'];

        // Change password
        if($password){
            $query =
                "UPDATE `" . $this->aSettings['db_prefix'] . "user` " .
                "SET `password` = '" . md5(md5($password) . $userSalt) . "' " .
                "WHERE `userid` = '" . $userID . "'";
            $result = @mysql_query($query, $this->db);
        }

        // Update email
        if($email){
            $query =
                "UPDATE `" . $this->aSettings['db_prefix'] . "user` " .
                "SET `email` = '" . $email . "' " .
                "WHERE `userid` = '" . $userID . "'";
            $result = @mysql_query($query, $this->db);
        }

        return TRUE;
    }

    /**
     * Grab vBulletin cookies from raw headers and set them according to the setings.
     *
     * @param  string $headers  Returned headers
     * @return void
     */
    private function _setCookies($headers){
        if(preg_match_all("/Set-Cookie: (bb[^=]+)=([^;]+)/s", $headers, $matches)){
            $oHTTPResponse = AMI::getSingleton('response')->HTTP;
            foreach($matches[1] as $index => $cookieName){
                $oHTTPResponse->setCookie(
                    $cookieName,
                    $matches[2][$index],
                    time() + $this->aSettings['cookie_ttl'],
                    $this->aSettings['cookie_path']
                );
            }
        }
    }
}
