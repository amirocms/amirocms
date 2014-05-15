<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   TxService
 * @version   $Id: AMI_Tx.php 50450 2014-05-05 07:40:35Z Leontiev Anton $
 * @since     6.0.2
 */

/**
 * Transaction exception.
 *
 * @package TxService
 * @since   6.0.2
 */
class AMI_Tx_Exception extends AMI_Exception{
    const TX_ALREADY_STARTED      = 10;
    const TX_NOT_STARTED          = 20;
    const TX_MISSING_CMD_RES      = 30;
    const TX_INVALID_CMD          = 40;
    const TX_PRECOMMIT_FAILED     = 50;
    const TX_COMMIT_FAILED        = 60;
    const TX_ROLLBACK_FAILED      = 70;
    const TX_INVALD_BACKUP_ENTITY = 80;

    const CMD_MISSING_OBLIGATORY_ARG = 1000;
    const CMD_INVALID_ARG            = 1010;

    const CMD_DB_ON_CREATE_TABLE = 2000;

    const CMD_EXISTING_FILE    = 3000;
    const CMD_ON_BACKUP_FILE   = 3010;
    const CMD_ON_CREATE_FILE   = 3020;
    const CMD_ON_ROLLBACK_FILE = 3030;
    const CMD_BROKEN_CONTENT_MARKER = 3040;
    const CMD_DUPLICATE_CONTENT_MARKER = 3050;

    const CMD_MISSING_TPL_CONTENT = 3060;
    const CMD_INVALID_TPL_CONTENT = 3070;
}

/**
 * Transaction command arguments.
 *
 * Kind of registry design pattern.
 *
 * @property-read int    $mode
 * @property-read string $source  Source content or file name
 * @property-read string $target  Target place (db table name, file name or etc)
 * @package       TxCommand
 * @subpackage    Model
 * @since         6.0.2
 */
class AMI_Tx_Cmd_Args{
    /**
     * Initial data
     *
     * @var array
     */
    protected $aData;

    /**
     * Constructor.
     *
     * @param array $aData  Object properties
     */
    public function __construct(array $aData){
        $this->aData = $aData;
    }

    /**
     * Magic __get().
     *
     * @param  string $name  Property name
     * @return mixed
     */
    public function __get($name){
        return $this->aData[$name];
    }

    /**
     * Magic __isset().
     *
     * @param  string $name  Property name
     * @return bool
     */
    public function __isset($name){
        return isset($this->aData[$name]);
    }

    /**
     * Magic __set().
     *
     * Setting is forbidden.
     *
     * @param  string $name   Property name
     * @param  mixed  $value  Property value
     * @return void
     */
    public function __set($name, $value){
        trigger_error("Readonly property '{$name}' cannot be overwtitten", E_USER_ERROR);
    }

    /**
     * Magic __unset().
     *
     * Deleting is forbidden.
     *
     * @param  string $name  Property name
     * @return void
     */
    public function __unset($name){
    }

    /**
     * Returns all properties.
     *
     * @return array
     */
    public function getAll(){
        return $this->aData;
    }

    /**
     * Overwrites property.
     *
     * @param  string $name  Property name
     * @param  mixed $value  Property value
     * @return void
     */
    public function overwrite($name, $value){
        $valueToLog =
            $name === 'mode'
                ? '0x' . dechex($value)
                : var_export($value, TRUE);
        AMI_Tx::log(get_class($this) . '::$' . $name . ' is overwritten by ' . $valueToLog, AMI_Service::LOG_WARN);
        $this->aData[$name] = $value;
    }
}

/**
 * Transaction command interface.
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      6.0.2
 */
interface AMI_iTx_Cmd{
    /**#@+
     * Command mode.
     *
     * @var int
     */

    /**
     * If target command entity exists exception will be thrown
     */
    const MODE_COMMON                  = 0x00;

    /**
     * Skip existing target command entity
     */
    const MODE_APPEND                  = 0x01;

    /**
     * Overwrite existing target command entity
     */
    const MODE_OVERWRITE               = 0x02;

    /**
     * Uninstallation mode allows to keep data and local code
     */
    const MODE_SOFT                    = 0x04;

    /**
     * Uninstallation mode deleting data and local code
     */
    const MODE_PURGE                   = 0x08;

    /**
     * Ignore target existence
     */
    const MODE_IGNORE_TARGET_EXISTENCE = 0x10;

    /**
     * Ignore data existence
     */
    const MODE_IGNORE_DATA_EXISTENCE   = 0x20;

    /**
     * Skip rollback manipulations
     */
    // const MODE_SKIP_ROLLBACK           = 0x40;

    /**
     * Delete backup on commit
     */
    const MODE_DELETE_BACKUP_ON_COMMIT = 0x80;

    /**#@-*/

    /**
     * Commits command.
     *
     * Called after all commands finished successfully.
     *
     * @return void
     */
    public function commit();

    /**
     * Rollbacks command.
     *
     * Called on transaction exception cought.
     *
     * @return void
     */
    public function rollback();

    /**
     * Returns string containing argements for logging.
     *
     * @return string
     */
    public function getLoggingArgs();
}

/**
 * Transaction command abstraction.
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      6.0.2
 */
abstract class AMI_Tx_Cmd implements AMI_iTx_Cmd{
    /**
     * Current transaction object
     *
     * @var AMI_iTx
     */
    protected $oTx;

    /**
     * Command arguments
     *
     * @var AMI_Tx_Cmd_Args
     */
    protected $oArgs;

    /**
     * Backup name
     *
     * @var string
     */
    protected $backupName;

    /**
     * Flag specifying to replace original file on commit
     *
     * @var bool
     */
    protected $replaceOnCommit = TRUE;

    /**
     * Constructor.
     *
     * @param AMI_iTx  $oTx           Current transaction
     * @param AMI_Tx_Cmd_Args $oArgs  Command arguments
     */
    public function __construct(AMI_iTx $oTx, AMI_Tx_Cmd_Args $oArgs){
        DB_si::globalAttr('log_tx', TRUE);
        $cmd = get_class($this);
        $aTrace = debug_backtrace();
        if(isset($aTrace[1]['object'])){
            $callback = array($aTrace[1]['object'], $aTrace[1]['function']);
        }elseif(isset($aTrace[1]['class'])){
            $callback = array($aTrace[1]['class'], $aTrace[1]['function']);
        }elseif(isset($aTrace[1]['function'])){
            $callback = $aTrace[1]['function'];
        }else{
            $callback = null;
        }
        unset($aTrace);
        $aEvent = array(
            'caller'  => $callback,
            'oTx_Cmd' => $this,
            'oArgs'   => $oArgs
        );
        /**
         * Allows to modify or discard transacton command.
         *
         * Set $aEvent['_discard'] to TRUE to discard command.
         *
         * @event      on_tx_command AMI_Event::MOD_ANY
         * @eventparam mixed           caller   Callback
         * @eventparam AMI_Tx_Cmd      oTx_Cmd  Transaction command object
         * @eventparam AMI_Tx_Cmd_Args oArgs    Transaction command arguments
         */
        AMI_Event::fire('on_tx_command', $aEvent, AMI_Event::MOD_ANY);
        if(!empty($aEvent['_discard'])){
            AMI_Tx::log(get_class($this) . ' DISCARDED', AMI_Service::LOG_WARN);
            DB_si::globalAttr('log_tx', FALSE);
            return;
        }
        $this->oTx = $oTx;
        $this->oArgs = $oArgs;
        $this->validateArgs();
        $this->init();
        $this->run();
        DB_si::globalAttr('log_tx', FALSE);
    }

    /**
     * Returns string containing argements for logging.
     *
     * Override method to log only useful argements using AMI_Tx_Cmd::argsToString().
     *
     * @return string
     */
    public function getLoggingArgs(){
        return $this->argsToString();
    }

    /**
     * Validates passed arguments.
     *
     * @return void
     * @see    AMI_Tx_Cmd::__construct()
     */
    abstract protected function validateArgs();

    /**
     * Initializes command.
     *
     * @return void
     * @see    AMI_Tx_Cmd::__construct()
     */
    abstract protected function init();

    /**
     * Runs command.
     *
     * @return void
     * @see    AMI_Tx_Cmd::__construct()
     */
    abstract protected function run();

    /**
     * Validates obligatory arguments.
     *
     * @param  array $aArgs  Arguments to validate
     * @return void
     * @throws AMI_Tx_Exception  In case of missing obligatory argument.
     */
    protected function validateObligatoryArgs(array $aArgs){
        foreach($aArgs as $arg){
            if(!isset($this->oArgs->$arg)){
                throw new AMI_Tx_Exception(
                    "Missing obligatory argument '" . $arg . "'",
                    AMI_Tx_Exception::CMD_MISSING_OBLIGATORY_ARG
                );
            }
        }
    }

    /**
     * Returns string containing specifying arguments and values.
     *
     * @param  array $aArgs  Argument names
     * @return string
     * @since  6.0.6
     */
    protected function argsToString(array $aArgs = array()){
        if(!sizeof($aArgs)){
            $aArgs = array_keys($this->oArgs->getAll());
        }
        $result = '';
        foreach($aArgs as $arg){
            $value = $this->oArgs->{$arg};
            if(is_object($value)){
                $class = get_class($value);
                $resId = AMI::getResourceByClass($class);
                $value =
                    FALSE !== $resId
                        ? "Object having resource '" . $resId. "' of class " . $class
                        : "Object of class " . $class;
            }elseif(is_array($value)){
                $value = 'Array(' . sizeof($value) . ')';
            }
            $result .= $arg . " = [" . $value . "], ";
        }
        if('' !== $result){
            $result = mb_substr($result, 0, -2);
        }

        return $result;
    }
}

/**
 * Transaction interface.
 *
 * @package    TxService
 * @subpackage Controller
 * @since      6.0.2
 */
interface AMI_iTx{
    const LOG_NOTE = 1;
    const LOG_WARN = 2;
    const LOG_ERR  = 3;

    /**
     * Logs message to "_admin/_logs/tx.log".
     *
     * @param  string $message  Message
     * @param  int    $type     Type: self::LOG_NOTE / self::LOG_WARN / self::LOG_ERR
     * @return void
     */
    public static function log($message, $type = self::LOG_NOTE);

    /**
     * Start transaction.
     *
     * @param  strung $name  Transaction name
     * @return void
     */
    public function start($name);

    /**
     * Returns TRUE if transaction is started but not commited.
     *
     * @return bool
     */
    public function isStarted();

    /**
     * Adds command resources.
     *
     * @param array $aResources  Resources
     * @see   AMI_Tx::$aCmdResources
     */
    public function addCommandResources(array $aResources);

    /**
    /**
     * Add command executed on commit.
     *
     * @param  string $cmd   Command
     * @param  mixed  $data  Command data
     * @return void
     */
    public function addCommand($cmd, $data = null);

    /**
     * Commit tracnsaction.
     *
     * @return void
     */
    public function commit();

    /**
     * Returns backup entity name or null.
     *
     * @param  string       $entity    Entity (file name, db table name, etc.)
     * @param  AMI_iStorage $oStorage  Storage object
     * @return string|null
     */
    public function getBackupName($entity, AMI_iStorage $oStorage = null);

    /**
     * Set backup entity name.
     *
     * @param  string       $entity    Entity (file name, db table name, etc.)
     * @param  string       $name      Backup entity name
     * @param  AMI_iStorage $oStorage  Storage object
     * @return void
     */
    public function setBackupName($entity, $name, AMI_iStorage $oStorage = null);

    /**
     * Set backup entity name.
     *
     * @param  string       $entity    Entity (file name, db table name, etc.)
     * @param  bool         $exists    Flag specifying existence
     * @param  AMI_iStorage $oStorage  Storage object
     * @return void
     */
    public function setExistence($entity, $exists, AMI_iStorage $oStorage = null);

    /**
     * Rollback tracnsaction.
     *
     * @return void
     */
    public function rollback();

    /**
     * Revert (offline rollback) last tracnsaction.
     *
     * @param  array $aRollbackData  Rollback data
     * @param  bool  $output         Do output details
     * @return void
     */
    public function revert(array $aRollbackData, $output = TRUE);
}

/**
 * Transaction abstraction.
 *
 * @package    TxService
 * @subpackage Controller
 * @since      6.0.2
 */
abstract class AMI_Tx implements AMI_iTx{
    /**
     * Variable containing current transactions state, using for offline rollback.
     *
     * @var array
     */
    protected static $aState;

    /**
     * Variable containing global transactions state, using for offline rollback.
     *
     * @var int
     */
    protected static $stateIndex = 0;

    /**
     * Variable containing current transactions state, using for offline rollback.
     *
     * @var int
     */
    protected $localStateIndex;

    /**
     * State file path
     *
     * @var string
     */
    protected $statePath;

    /**
     * Array of supported commands, must be filled in children
     *
     * Example:
     * <code>
     * protected $aCmdResources = array(
     *     // command        // resource
     *     'storage/copy' => 'tx/cmd/storage/copy'
     * );
     * </code>
     *
     * @var array
     */
    protected $aCmdResources = array();

    /**
     * Flag specifying is transaction started
     *
     * @var bool
     */
    protected $started = FALSE;

    /**
     * Array of commands
     *
     * @var array
     * @see AMI_Tx::addCommand
     */
    protected $aCmds = array();

    /**
     * Array of executing commands
     *
     * Used for adding command during execution
     *
     * @var array
     * @see AMI_Tx::commit()
     * @see AMI_Tx::addCommand()
     */
    protected $aExecutingCmds = array();

    /**
     * Precommited commands count
     *
     * @var int
     */
    protected $cmdsCount = 0;

    /**
     * Array of backup data needed for revert
     *
     * @var array
     */
    protected $aBackup = array();

    /**
     * Array of entities existence flags
     *
     * @var array
     */
    protected $aExistence = array();

    /**
     * Array of precommited commands
     *
     * @var array
     * @see AMI_Tx::commit()
     */
    protected $aPrecommitedCmds = array();

    /**
     * Failed commit command index in AMI_Tx::$aPrecommitedCmds
     *
     * @var int
     */
    protected $index;

    /**
     * Contains exception fired on commit
     *
     * @var AMI_Tx_Exception
     */
    protected $oCommitException;

    /**
     * Logs message to "_admin/_logs/tx.log".
     *
     * @param  string $message  Message
     * @param  int    $type     Type: self::LOG_NOTE / self::LOG_WARN / self::LOG_ERR
     * @return void
     */
    public static function log($message, $type = self::LOG_NOTE){
        $aTypes = array(
            self::LOG_NOTE => 'NOTE',
            self::LOG_WARN => 'WARN',
            self::LOG_ERR  => 'ERR ',
        );
        $type = $aTypes[$type];
        $message = '[' . $type . '] ' . $message;
        AMI_Service::log($message, $GLOBALS['ROOT_PATH'] . '_admin/_logs/tx.log');
        // d::w('<code>[' . date('Y-m-d H:i:s') . "] {$message}</code><br />\n");
    }

    /**
     * Start transaction.
     *
     * @param  strung $name  Transaction name
     * @return void
     */
    public function start($name){
        self::log(get_class($this) . ' >>>');
        self::log(get_class($this) . '::start(' . $name . ') {');
        if($this->started){
            $message = 'Already started';
            self::log(get_class($this) . ' ' . $message, AMI_Service::LOG_ERR);
            throw new AMI_Tx_Exception(
                $message,
                AMI_Tx_Exception::TX_ALREADY_STARTED
            );
        }
        if(!$this->aCmdResources){
            $message = 'No command resource declared';
            self::log(get_class($this) . ' ' . $message, AMI_Service::LOG_ERR);
            throw new AMI_Tx_Exception(
                $message,
                AMI_Tx_Exception::TX_MISSING_CMD_RES
            );
        }
        $this->localStateIndex = self::$stateIndex++;
        $class = get_class($this);
        // $e = new Exception; ###

        $aVersion = AMI_Service::getVersion();
        self::$aState[$this->localStateIndex] = array(
            'name'       => $name,
            // 'trace'      => "\r\n" . $e->getTraceAsString() . "\r\n", ###
            'cmsVersion' => $aVersion['cms']['code'],
            'started'    => date('Y-m-d H:i:s'),
            'finished'   => FALSE,
            'class'      => $class,
            'backup'     => array(),
            'existence'  => array()
        );

        if(!$this->statePath){
            $this->statePath = AMI_Registry::get('path/root') . '_admin/_logs/tx.php';
        }
        $this->updateState();

        $this->started = TRUE;
        self::log('} ' . get_class($this) . '::start()');
    }

    /**
     * Returns TRUE if transaction is started but not commited.
     *
     * @return bool
     */
    public function isStarted(){
        return $this->started;
    }

    /**
     * Adds command resources.
     *
     * @param array $aResources  Resources
     * @see   AMI_Tx::$aCmdResources
     */
    public function addCommandResources(array $aResources){
        $this->aCmdResources += $aResources;
    }

    /**
     * Add command executed on commit.
     *
     * @param  string $cmd    ommand
     * @param  mixed  $data  Command data
     * @return void
     */
    public function addCommand($cmd, $data = null){
        self::log(get_class($this) . " addCommand('" . $cmd . "')");
        if(!isset($this->aCmdResources[$cmd])){
            $message = "Invalid '" . $cmd . "' command";
            self::log(get_class($this) . ' ' . $message, AMI_Service::LOG_ERR);
            throw new AMI_Tx_Exception(
                $message,
                AMI_Tx_Exception::TX_INVALID_CMD
            );
        }
        $this->aCmds[] = array($cmd, $data);
    }

    /**
     * Commit tracnsaction.
     *
     * @return void
     */
    public function commit(){
        self::log(get_class($this) . '::commit() {');
        if(!$this->started){
            // die(d::getTraceAsString());###
            $message = 'Transaction not started';
            self::log(get_class($this) . ' ' . $message, AMI_Service::LOG_ERR);
            throw new AMI_Tx_Exception(
                $message,
                AMI_Tx_Exception::TX_NOT_STARTED
            );
        }

        try{
            // Create and store all tracnsaction commands
            $this->index = 0;
            $this->cmdsCount = 0;
            $this->precommit();
            $this->cmdsCount = sizeof($this->aCmds);

            // Commit commands
            $aExecutingCmds = $this->aPrecommitedCmds;
            $qty = sizeof($aExecutingCmds);
            for($this->index = 0; $this->index < $qty; ++$this->index){
                $oCmd = $this->aPrecommitedCmds[$this->index];
                $class = get_class($oCmd);
                $resId = AMI::getResourceByClass($class);
                $args = $oCmd->getLoggingArgs();
                if('' !== $args){
                    $args = ' ' . $args;
                }
                self::log(get_class($this) . " Commiting '" . $resId . " | " . $class . "'" . $args);
                $oCmd->commit();
                if(sizeof($this->aCmds) != $this->cmdsCount){
                    $this->precommit();
                    $this->cmdsCount = sizeof($this->aCmds);
                    $qty = sizeof($this->aPrecommitedCmds);
                }
            }
        }catch(AMI_Tx_Exception $oException){
            $this->oCommitException = $oException;
            $message = 'Commit failed: ' . $oException->getMessage();
            self::log(get_class($this) . ' ' . $message, AMI_Service::LOG_ERR);
            $this->rollback();
            throw new AMI_Tx_Exception(
                $message,
                AMI_Tx_Exception::TX_COMMIT_FAILED,
                $oException
            );
        }
        self::log('} ' . get_class($this) . '::commit()');
        self::$aState[$this->localStateIndex]['finished'] = date('Y-m-d H:i:s');
        $this->updateState();
        self::log(get_class($this) . ' <<<');

        $this->started = FALSE;
    }

    /**
     * Returns backup entity name or null.
     *
     * @param  string       $entity    Entity (file name, db table name, etc.)
     * @param  AMI_iStorage $oStorage  Storage object
     * @return string|null
     */
    public function getBackupName($entity, AMI_iStorage $oStorage = null){
        $result = NULL;
        $storage = is_null($oStorage) ? '-' : get_class($oStorage);
        if(
            isset($this->aBackup[$storage]) &&
            isset($this->aBackup[$storage][$entity])
        ){
            $result = $this->aBackup[$storage][$entity];
        }

        return $result;
    }

    /**
     * Set backup entity name.
     *
     * @param  string       $entity    Entity (file name, db table name, etc.)
     * @param  string       $name      Backup entity name
     * @param  AMI_iStorage $oStorage  Storage object
     * @return void
     */
    public function setBackupName($entity, $name, AMI_iStorage $oStorage = null){
        $storage = is_null($oStorage) ? '-' : get_class($oStorage);
        self::log(get_class($this) . " Backup entity '" . $storage . '/' . $entity . "' to '" . $name . "' for '" . $storage . "'");
        if($entity === $name){
            $message = "Backup entity '" . $entity . "' cannot have same name";
            self::log(get_class($this) . ' ' . $message, AMI_Service::LOG_ERR);
            throw new AMI_Tx_Exception(
                $message,
                AMI_Tx_Exception::TX_INVALD_BACKUP_ENTITY
            );
        }
        if(
            isset($this->aBackup[$storage]) &&
            isset($this->aBackup[$storage][$entity])
        ){
            $message = "Backup entity '" . $storage . '/' . $entity . "' already exists";
            self::log(get_class($this) . ' ' . $message, AMI_Service::LOG_ERR);
            throw new AMI_Tx_Exception(
                $message,
                AMI_Tx_Exception::TX_INVALD_BACKUP_ENTITY
            );
        }
        $this->aBackup[$storage][$entity] = $name;
        $this->updateState();
    }

    /**
     * Set backup entity name.
     *
     * @param  string       $entity    Entity (file name, db table name, etc.)
     * @param  bool         $exists    Flag specifying existence
     * @param  AMI_iStorage $oStorage  Storage object
     * @return void
     */
    public function setExistence($entity, $exists, AMI_iStorage $oStorage = null){
        $storage = is_null($oStorage) ? '-' : get_class($oStorage);
        if(!isset($this->aExistence[$storage])){
            $this->aExistence[$storage] = array();
        }
        $this->aExistence[$storage][$entity] = $exists;
        $this->updateState();
    }

    /**
     * Rollback tracnsaction.
     *
     * @return void
     */
    public function rollback(){
        if(!$this->started){
            $message = 'Transaction not started';
            self::log(get_class($this) . ' ' . $message, AMI_Service::LOG_ERR);
            throw new AMI_Tx_Exception(
                $message,
                AMI_Tx_Exception::TX_NOT_STARTED
            );
        }
        $this->started = FALSE;

        if(is_null($this->index)){
            $message = 'Rollback called before commit, nothing to do';
            self::log(get_class($this) . ' ' . $message, AMI_Service::LOG_WARN);
            return;
        }

        try{
            for($i = $this->index; $i >= 0; $i--){
                if(isset($this->aPrecommitedCmds[$i])){
                    $oCmd = $this->aPrecommitedCmds[$i];
                    $oCmd->rollback();
                }
            }
        }catch(AMI_Tx_Exception $oException){
            $message = 'Rollback failed';
            self::log(get_class($this) . ' ' . $message, AMI_Service::LOG_ERR);
            throw new AMI_Tx_Exception(
                $message,
                AMI_Tx_Exception::TX_ROLLBACK_FAILED,
                $this->oCommitException
            );
        }
    }

    /**
     * Update file containing state fr possible offline rollback.
     *
     * @return void
     */
    protected function updateState(){
        $class = get_class($this);
        self::$aState[$this->localStateIndex]['backup'] = $this->aBackup;
        self::$aState[$this->localStateIndex]['existence'] = $this->aExistence;
        file_put_contents($this->statePath, '<' . '?php return ' . var_export(self::$aState, TRUE) . ';');
    }

    /**
     * Create and store all tracnsaction commands.
     *
     * @return void
     */
    protected function precommit(){
        self::log(get_class($this) . '::precommit(' . $this->cmdsCount . ') {');
        try{
            $this->aExecutingCmds = $this->aCmds;
            $startIndex = sizeof($this->aPrecommitedCmds);
            $qty = sizeof($this->aExecutingCmds);
            for($index = $startIndex; $index < $qty; ++$index){
                list($cmd, $aArgs) = $this->aCmds[$index];
                $resId = $this->aCmdResources[$cmd];
                $oCmd = AMI::getResource($this->aCmdResources[$cmd], array($this, $aArgs));
                $this->aPrecommitedCmds[] = $oCmd;
                $args = $oCmd->getLoggingArgs();
                if('' !== $args){
                    $args = ' ' . $args;
                }
                self::log(get_class($this) . " Precommiting '" . $cmd . " | " . $resId . " | " . AMI::getResourceClass($resId) . "'" . $args);
                /**
                 * @var AMI_Tx_iCmd
                 */
                if(sizeof($this->aCmds) != $qty){
                    // Some commandes were added, insert it after current command
                    $this->aExecutingCmds =
                        array_merge(
                            array_slice($this->aExecutingCmds, 0, $index + 1),
                            array_diff($this->aCmds, $this->aExecutingCmds),
                            array_slice($this->aExecutingCmds, $index + 1)
                        );
                    $qty = sizeof($this->aExecutingCmds);
                }
            }
        }catch(AMI_Tx_Exception $oException){
            $this->oCommitException = $oException;
            $message = 'Precommit failed: ' . $oException->getMessage();
            self::log(get_class($this) . ' ' . $message, AMI_Service::LOG_ERR);
            throw new AMI_Tx_Exception(
                $message,
                AMI_Tx_Exception::TX_PRECOMMIT_FAILED,
                $oException
            );
        }
        self::log('} ' . get_class($this) . '::precommit()');
    }

    /**
     * Output message.
     *
     * @param  string $message  Message
     * @param  bool   $output   Do output
     * @param  int    $type     Type: self::LOG_NOTE / self::LOG_WARN / self::LOG_ERR
     * @return void
     */
    protected function output($message, $output = TRUE, $type = self::LOG_NOTE){
        if(!$output){
            return;
        }

        $aColor = array(
            self::LOG_NOTE => 'green',
            self::LOG_WARN => 'yellow',
            self::LOG_ERR  => 'red'
        );
        AMI::getSingleton('response')
            ->write('<span style="font-size: 11px; font-family: tahoma;">[ ' . date('Y-m-d H:i:s') . '] <span style="color: ' . $aColor[$type] . '">' . $message . "<br /></span></span>\n");
    }
}

/**
 * Database transaction.
 *
 * @package    TxService
 * @subpackage Controller
 * @since      6.0.2
 */
class AMI_Tx_DB extends AMI_Tx{
    /**
     * Array of supported commands
     *
     * @var array
     */
    protected $aCmdResources = array(
        'table/create' => 'tx/cmd/db/table/create',
        'table/drop'   => 'tx/cmd/db/table/drop',
        'query'        => 'tx/cmd/db/query'
    );

    /**
     * Constructor.
     */
    public function __construct(){
        AMI_Tx_Cmd_DB::addResources();
    }

    /**
     * Revert (offline rollback) last tracnsaction.
     *
     * @param  array $aRollbackData  Rollback data
     * @param  bool  $output         Do output details
     * @return void
     */
    public function revert(array $aRollbackData, $output = TRUE){
        $oDB = AMI::getSingleton('db');

        $message = '>>> Rollback DB {';
        self::log($message);
        self::output($message, $output);

        // Restore from backup
        foreach($aRollbackData['existence'] as $storage => $aData){
            foreach($aData as $target => $exists){
                if($exists){
                    $backupName = $aRollbackData['backup'][$storage][$target];
                    $oQuery =
                        DB_Query::getSnippet("SHOW TABLES LIKE %s")
                        ->q($backupName);
                    if($oDB->fetchRow($oQuery)){
                        $oQuery =
                            DB_Query::getSnippet("DROP TABLE IF EXISTS `%s`")
                            ->plain($target);
                        $message = $oQuery->get();
                        self::log($message);
                        self::output($message, $output);
                        $oDB->query($oQuery, AMI_DB::QUERY_TRUSTED);
                        $oQuery =
                            DB_Query::getSnippet("RENAME TABLE `%s` TO `%s`")
                            ->plain($backupName)
                            ->plain($target);
                        $message = $oQuery->get();
                        self::log($message);
                        self::output($message, $output);
                        $oDB->query($oQuery, AMI_DB::QUERY_TRUSTED);
                    }
                }
            }
        }

        // Delete having no backup entities
        foreach($aRollbackData['backup'] as $storage => $aData){
            foreach(array_keys($aData) as $target){
                if(
                    !isset($aRollbackData['existence'][$storage]) ||
                    !isset($aRollbackData['existence'][$storage][$target])
                ){
                    $oQuery =
                        DB_Query::getSnippet("DROP TABLE IF EXISTS `%s`")
                        ->plain($target);
                    $message = $oQuery->get();
                    self::log($message);
                    self::output($message, $output);
                    $oDB->query($oQuery, AMI_DB::QUERY_TRUSTED);
                }
            }
        }

        $message = '<<< } Rollback DB';
        self::log($message);
        self::output($message, $output);
    }
}

/**
 * Storage transaction.
 *
 * @package    TxService
 * @subpackage Controller
 * @since      6.0.2
 */
class AMI_Tx_Storage extends AMI_Tx{
    /**
     * Array of supported commands
     *
     * @var array
     */
    protected $aCmdResources = array(
        'storage/copy'  => 'tx/cmd/storage/copy',
        'storage/set'   => 'tx/cmd/storage/set',
        'storage/clean' => 'tx/cmd/storage/clean',
        'php/install'   => 'tx/cmd/php/install',
        'php/uninstall' => 'tx/cmd/php/uninstall',
        'tpl/install'   => 'tx/cmd/tpl/install',
        'tpl/uninstall' => 'tx/cmd/tpl/uninstall',
        'ini/install'   => 'tx/cmd/ini/install',
        'ini/uninstall' => 'tx/cmd/ini/uninstall'
    );

    /**
     * Constructor.
     */
    public function __construct(){
        AMI_Tx_Cmd_Storage::addResources();
    }

    /**
     * Revert (offline rollback) last tracnsaction.
     *
     * @param  array $aRollbackData  Rollback data
     * @param  bool  $output         Do output details
     * @return void
     */
    public function revert(array $aRollbackData, $output = TRUE){
        $aStorage = array();
        $aNames = $aRollbackData['backup'];
        $aExistence = $aRollbackData['existence'];

        $message = '>>> Rollback FS/Template {';
        self::log($message);
        self::output($message, $output);

        foreach($aNames as $storageClassName => $aData){
            if($storageClassName === '-'){
                continue;
            }
            if(!isset($aStorage[$storageClassName])){
                $aStorage[$storageClassName] = new $storageClassName;
            }

            $message = '>>> ' . $storageClassName . ' {';
            self::log($message);
            self::output($message, $output);

            $oStorage = $aStorage[$storageClassName];
            foreach($aData as $source => $backup){
                $message = ">>> Restoring '" . $source . "' from '" . $backup . "' {";
                self::log($message);
                self::output($message, $output);
                if($oStorage->exists($backup)){
                    if($oStorage->exists($source)){
                        $message = "Deleting '" . $source . "'";
                        self::log($message);
                        self::output($message, $output);
                        $success = $oStorage->delete($source);
                        if(!$success){
                            $message = 'Failed';
                            self::log($message, self::LOG_ERR);
                            self::output($message, $output, self::LOG_ERR);
                        }
                    }
                    if($aExistence[$storageClassName][$source]){
                        $message = "Copying '" . $backup . "' to '" . $source . "'";
                        self::log($message);
                        self::output($message, $output);
                        $success = $oStorage->copy($backup, $source);
                        if(!$success){
                            $message = 'Failed';
                            self::log($message, self::LOG_ERR);
                            self::output($message, $output, self::LOG_ERR);
                        }
                    }
                }else{
                    if($oStorage->exists($source)){
                        $message = "Deleting '" . $source . "'";
                        self::log($message);
                        self::output($message, $output);
                        $success = $oStorage->delete($source);
                        if(!$success){
                            $message = 'Failed';
                            self::log($message, self::LOG_ERR);
                            self::output($message, $output, self::LOG_ERR);
                        }
                    }
                }
                $message = "<<< } Restoring '" . $source . "' from '" . $backup . "'";
                self::log($message);
                self::output($message, $output);
            }

            $message = '<<< } ' . $storageClassName;
            self::log($message);
            self::output($message, $output);
        }

        $message = '>>> } Rollback FS/Template';
        self::log($message);
        self::output($message, $output);
    }
}
