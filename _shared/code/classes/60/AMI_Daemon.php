<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Service
 * @version   $Id: AMI_Daemon.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary?
 */

/**
 * Daemon common class.
 *
 * PM Task #5146.
 *
 * @package Service
 * @see     daemon.privateMessages.broadcast.php
 * @since   x.x.x
 * @amidev  Temporary?
 */
abstract class AMI_Daemon{
    /**
     * Config
     *
     * @var array
     */
    protected $aConfig;

    /**
     * State
     *
     * @var array
     */
    protected $aState;

    /**
     * DB defaults
     *
     * @var array
     */
    protected $aDBDefaults = array(
        'host' => '127.0.0.1',
        'user' => 'root',
        'pass' => '',
        'path' => ''
    );

    /**
     * Human readable error codes
     *
     * @var array
     */
    protected $aErrors = array(
        E_ERROR             => 'E_ERROR',
        E_WARNING           => 'E_WARNING',
        E_PARSE             => 'E_PARSE',
        E_NOTICE            => 'E_NOTICE',
        E_CORE_ERROR        => 'E_CORE_ERROR',
        E_CORE_WARNING      => 'E_CORE_WARNING',
        E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
        E_USER_ERROR        => 'E_USER_ERROR',
        E_USER_WARNING      => 'E_USER_WARNING',
        E_USER_NOTICE       => 'E_USER_NOTICE',
        E_STRICT            => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR'
        // E_DEPRECATED        => 'E_DEPRECATED',
        // E_USER_DEPRECATED   => 'E_USER_DEPRECATED'
    );

    /**
     * Constructor.
     *
     * @param array $aConfig  Config
     * @param array $aState   State
     */
    public function __construct(array $aConfig, array $aState){
        $this->aConfig = $aConfig;
        $this->aState  = $aState;

        set_error_handler(array($this, 'handleError'), E_ALL);

        $this->checkLockOnStart();

        if(isset($aConfig['cms']) && isset($aConfig['cms']['hostConfigFile'])){
            // compose host db from php-file
            require $aConfig['cms']['hostConfigFile'];
            $this->aConfig['db']['host'] = 'mysql://' . ${'sDB_User'} . ':' . ${'sDB_Password'} . '@' . ${'sDB_Host'} . '/' . ${'sDB_Database'};
        }
    }

    /**
     * Handles error.
     *
     * @param  int    $code      Code
     * @param  string $message   Message
     * @param  string $file      File
     * @param  int    $line      Line
     * @param  array  $aContext  Context
     * @param  array  $aTrace    Force trace, used in AMI_Service::handleException
     * @return void
     */
    public function handleError($code, $message, $file = '', $line = 0, array $aContext = array(), array $aTrace = null){
        if(empty($this->aConfig['log'])){
            echo '[ ', date('Y-m-d H:i:s'), ' ] ';
            if($code !== E_USER_NOTICE){
                echo '[' . $this->aErrors[$code] . '] at line ' . $line . " in '" . $file . "'\n" . $message . "\n";
                $e = new Exception;
                echo $e->getTraceAsString();
            }else{
                echo $message;
            }
            echo "\n";
        }elseif($this->aConfig['log']['mask'] & $code){
            // $this->aConfig['lock'] has four keys: 'mask', 'file', 'trace' and 'maxSize'
            extract($this->aConfig['log'] + array('maxSize' => 0));
            $message = '[' . date('Y-m-d H:i:s') . '] [' . $this->aState['processId'] . '] [' . $this->aErrors[$code] . '] ' . $message . "\n";
            clearstatcache();
            if(
                $maxSize > 0 && @file_exists($file) &&
                @filesize($file) >= $maxSize
            ){
                $backup = $file . '.bak';
                @unlink($backup);
                @rename($file, $backup);
            }
            if(!empty($trace) && $code != E_USER_NOTICE){
                $e = new Exception;
                $message .= $e->getTraceAsString() . "\n";
            }
            @file_put_contents($file, $message, FILE_APPEND);
            @chmod($file, 0666);
        }

        if($code & E_USER_ERROR){
            die;
        }
    }

    /**
     * Runs application.
     *
     * @return mixed
     */
    public abstract function run();

    /**
     * Checks lock file on start, breaks lock if necessary.
     *
     * @return void
     * @exitpoint  If lock is correct
     */
    protected function checkLockOnStart(){
        if(!isset($this->aConfig['lock'])){
            return;
        }
        // $this->aConfig['lock'] has two keys: file, period
        extract($this->aConfig['lock']);
        if(file_exists($file)){
            if(
                empty($period) ||
                filemtime($file) >= ($this->aState['started']['time'] - $period)
            ){
                // correct lock
                if($this->checkReporting('lock')){
                    trigger_error('Correct lock is found', E_USER_NOTOCE);
                }
                exit(0);
            }else{
                trigger_error("Lock '" . file_get_contents($file) . " is broken!", E_USER_WARNING);
                if(!file_put_contents($file, $this->aState['processId'])){
                    trigger_error("Lock file '" . $file . " cannot be created!", E_USER_ERROR);
                }
            }
        }else{
            if(!file_put_contents($file, $this->aState['processId'])){
                trigger_error("Lock file '" . $file . " cannot be created!", E_USER_ERROR);
            }
        }
    }

    /**
     * Updates lock file modification time.
     *
     * @return void
     * @exitpoint If lock file is absent or contains invalid process id
     */
    protected function updateLock(){
        if(!isset($this->aConfig['lock'])){
            return;
        }
        // $this->aConfig['lock'] has two keys: file, period
        extract($this->aConfig['lock']);
        if(file_exists($file)){
            $processId = file_get_contents($file);
            if($processId !== $this->aState['processId']){
                trigger_error("Lock is rewritten by another process '" . $processId . "', daemon is interrupted!", E_USER_ERROR);
            }
            if(!touch($file)){
                trigger_error("Lock file '" . $file . "' cannot be touched!", E_USER_ERROR);
            }
        }else{
            trigger_error("Lock file '" . $file . "' is missing, daemon is interrupted!", E_USER_ERROR);
        }
    }

    /**
     * Releases lock file.
     *
     * @return void
     * @exitpoint If lock file is absent or contains invalid process id
     */
    protected function releaseLock(){
        if(!isset($this->aConfig['lock'])){
            return;
        }
        // $this->aConfig['lock'] has two keys: file, period
        extract($this->aConfig['lock']);
        if(file_exists($file)){
            $processId = file_get_contents($file);
            if($processId !== $this->aState['processId']){
                trigger_error("Lock is rewritten by another process '" . $processId . "', daemon is interrupted!", E_USER_ERROR);
            }
            unlink($file);
        }else{
            trigger_error("Lock file '" . $file . "' is missing, daemon is interrupted!", E_USER_ERROR);
        }
    }

    /**
     * Returns DB connection.
     *
     * @param  string $path        Resource path
     * @param  string $name        Connection name
     * @param  int    $errorLevel  Error level on connection fail
     * @return CMS_simpleDb
     */
    protected function getDBConnection($path, $name, $errorLevel = E_USER_ERROR){
        $aParams = parse_url($path) + $this->aDBDefaults;
        $aParams['path'] = trim($aParams['path'], '/');
        $path = $aParams['user'] . '@' . $aParams['host'] . '/' . $aParams['path'];
        if(empty($aParams['path'])){
            trigger_error("{$name}: Database name is missing in '{$path}'", E_USER_ERROR);
        }
        require_once $this->aConfig['cms']['classesPath'] . 'CMS_simpleDb.php';
        $oDB = new CMS_simpleDb;
        if(!$oDB->connect($aParams['host'], $aParams['user'], $aParams['pass'], $aParams['path'], true)){
            trigger_error("{$name}: Cannot connect to the database '{$path}': [" . mysql_errno() . '] ' . mysql_error(), $errorLevel);
            @mysql_close($oDB->_dbLink);
            $oDB = null;
        }
        return $oDB;
    }

    /**
     * Returns db resource path parsing CMS config file.
     *
     * @param  string $domain      Domain name
     * @param  string $path        Path to config file
     * @param  int    $errorLevel  Error level
     * @return string|false
     */
    protected function getDBAccessFromCMSConfig($domain, $path, $errorLevel = E_USER_WARNING){
        if(!is_file($path)){
            trigger_error($domain . ", missing config file '" . $path . '"', $errorLevel);
            return false;
        }

        $aConfig = @parse_ini_file($path, true);
        if(
            !is_array($aConfig) || empty($aConfig['dbaccess']) ||
            !isset($aConfig['dbaccess']['DB_Host']) ||
            !isset($aConfig['dbaccess']['DB_User']) ||
            !isset($aConfig['dbaccess']['DB_Password']) ||
            !isset($aConfig['dbaccess']['DB_Database'])
        ){
            trigger_error($domain . ", cannot read complete [dbaccess] section from config file '" . $path . '"', $errorLevel);
            return false;
        }
        return
            'mysql://' .
            $aConfig['dbaccess']['DB_User'] . ':' .
            $aConfig['dbaccess']['DB_Password'] . '@' .
            $aConfig['dbaccess']['DB_Host'] . '/' .
            $aConfig['dbaccess']['DB_Database'];
    }

    /**
     * Return true if 'report' config section key is present.
     *
     * @param  string $key  Config section 'report' key
     * @return bool
     */
    protected function checkReporting($key){
        return isset($this->aConfig['report']) && in_array($key, $this->aConfig['report']);
    }

    /**
     * Returns true / false by probability.
     *
     * @param  float $probability  Probability
     * @return bool
     */
    protected function probability($probability){
        return $probability != 1 ? (mt_rand(0, mt_getrandmax()) < $probability * mt_getrandmax()) : true;
    }
}
