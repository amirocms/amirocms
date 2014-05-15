<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   TxCommand
 * @version   $Id: AMI_Tx_Cmd_DB.php 44504 2013-11-27 10:21:28Z Leontiev Anton $
 * @since     6.0.2
 */

/**
 * Abstract database transaction command.
 *
 * Expects in $oArgs constructor arguments:
 * - mode   - command execustion mode (AMI_iTx_Cmd::MODE_* constants);
 * - source - "create table" query,
 * - target - table name.
 *
 * @package TxCommand
 * @since   6.0.2
 */
abstract class AMI_Tx_Cmd_DB extends AMI_Tx_Cmd{
    /**
     * DB object
     *
     * @var AMI_iDB
     */
    protected $oDB;

    /**
     * Array containing queries to rollback
     *
     * @var array
     */
    protected $aRollback = array();

    /**
     * Flag specifying to replace original file on commit
     *
     * @var bool
     */
    protected $replaceOnCommit;


    /**
     * Adds resources of available commands.
     *
     * @return void
     */
    public static function addResources(){
        AMI::addResourceMapping(
            array(
                'tx/cmd/db/table/create' => 'AMI_Tx_Cmd_DB_CreateTable',
                'tx/cmd/db/table/drop'   => 'AMI_Tx_Cmd_DB_DropTable',
                'tx/cmd/db/query'        => 'AMI_Tx_Cmd_DB_Query'
            )
        );
    }

    /**
     * Rollbacks command.
     *
     * @return void
     */
    public function rollback(){
        foreach($this->aRollback as $sql){
            $this->oDB->allowUnsafeQueryOnce();
            $this->oDB->query($sql);
        }
    }

    /**
     * Validates passed arguments.
     *
     * @return void
     * @see    AMI_Tx_Cmd::__construct()
     */
    protected function validateArgs(){
        $this->validateObligatoryArgs(array('mode', 'target'));
    }

    /**
     * Initializes command.
     *
     * @return void
     */
    protected function init(){
        $this->oDB = AMI::getSingleton('db');
    }

    /**
     * Executes db query.
     *
     * @param  string $sql    DB query
     * @param  int    $type   Query type for throwing exception
     * @param  int    $flags  Flags
     * @return void
     * @throws AMI_Tx_Exception  In case of db table creation failed.
     */
    protected function execSQL($sql, $type, $flags = 0){
        if(!$this->oDB->query($sql, AMI_DB::QUERY_NO_HALT | $flags)){
            $message = "SQL: " . '[' . $this->oDB->getErrorNumber() . '] ' . $this->oDB->getErrorMessage();
            AMI_Service::log($message, AMI_Service::LOG_ERR);
            throw new AMI_Tx_Exception(
                $message,
                AMI_Tx_Exception::CMD_DB_ON_CREATE_TABLE,
                NULL,
                array(
                    'table' => $this->oArgs->target,
                    'type'  => $type
                )
            );
        }
    }
}

/**
 * Database "CREATE TABLE" transaction command.
 *
 * Expects in $oArgs constructor arguments:
 * - mode   - command execustion mode (AMI_iTx_Cmd::MODE_* constants);
 * - source - "CREATE TABLE" query,
 * - target - table name.
 *
 * Example:
 * <code>
 * $this->aTx['db']->addCommand(
 *     'table/create',
 *     new AMI_Tx_Cmd_Args(
 *         array(
 *             'mode'   => $this->oArgs->mode,
 *             'source' => $sql,
 *             'target' => $table
 *         )
 *     )
 * );
 * </code>
 *
 * @package  TxCommand
 * @since    6.0.2
 * @resource tx/cmd/db/table/create <code>AMI::getResource('tx/cmd/db/table/create')</code>
 */
class AMI_Tx_Cmd_DB_CreateTable extends AMI_Tx_Cmd_DB{
    /**
     * Flag specifies if previous db table was backuped
     *
     * @var bool
     */
    protected $backuped = FALSE;

    /**
     * Commits command.
     *
     * @return void
     */
    public function commit(){
        if($this->backuped){
            $this->execSQL(
                "DROP TABLE IF EXISTS `" . $this->oArgs->target . "_` ",
                'drop',
                AMI_DB::QUERY_TRUSTED
            );
            $this->execSQL(
                "RENAME TABLE `" . $this->oArgs->target . "` TO `" . $this->oArgs->target . "_`",
                'rename',
                AMI_DB::QUERY_TRUSTED
            );
        }
        if($this->replaceOnCommit){
            $this->execSQL(
                "DROP TABLE IF EXISTS `" . $this->oArgs->target . "` ",
                'drop',
                AMI_DB::QUERY_TRUSTED
            );
            $this->execSQL(
                "RENAME TABLE `" . $this->backupName . "` TO `" . $this->oArgs->target . "`",
                'rename',
                AMI_DB::QUERY_TRUSTED
            );
        }
    }

    /**
     * Validates passed arguments.
     *
     * @return void
     * @see    AMI_Tx_Cmd::__construct()
     */
    protected function validateArgs(){
        parent::validateArgs();
        $this->validateObligatoryArgs(array('source'));
    }

    /**
     * Runs command.
     *
     * @return void
     * @throws AMI_Tx_Exception  In case of problems.
     */
    protected function run(){
        $oQuery =
            DB_Query::getSnippet("SHOW TABLES LIKE %s")
            ->q($this->oArgs->target);
        if($this->oDB->fetchRow($oQuery)){
            // table already exists
            switch($this->oArgs->mode){
                case self::MODE_COMMON:
                    $message = "DB table `" . $this->oArgs->target . "` already exists'";
                    AMI_Tx::log($message, AMI_Service::LOG_ERR);
                    throw new AMI_Tx_Exception(
                        $message,
                        AMI_Tx_Exception::CMD_DB_ON_CREATE_TABLE,
                        null,
                        array(
                            'table' => $this->oArgs->target,
                            'type'  => 'exists'
                        )
                    );
                case self::MODE_OVERWRITE:
                    $this->backupName = $this->oTx->getBackupName($this->oArgs->target);
                    $this->replaceOnCommit = is_null($this->backupName);
                    if($this->replaceOnCommit){
                        $this->backupName = $this->oArgs->target . '__';
                        $this->oTx->setBackupName($this->oArgs->target, $this->backupName);
                    }
                    $this->oTx->setExistence($this->oArgs->target, TRUE);
                    // AMI_Tx::log('@ ' . var_export($this->replaceOnCommit, TRUE));###
                    // if($this->replaceOnCommit){
                        $this->execSQL(
                            "DROP TABLE IF EXISTS `" . $this->backupName . "`",
                            'backup',
                            AMI_DB::QUERY_TRUSTED
                        );
                        $this->aRollback[] = "DROP TABLE `" . $this->backupName . "`";
                    // }
                    $this->oDB->allowQuotesInQueryOnce();
                    $sql = preg_replace('/cms_[^\s`]+/s', $this->backupName, $this->oArgs->source);
                    $this->execSQL($sql, 'create', AMI_DB::QUERY_TRUSTED);
                    $this->aRollback[] =
                        "DROP TABLE IF EXISTS `" . $this->oArgs->target . "`";
                    /*
                    array_unshift(
                        $this->aRollback,
                        "DROP TABLE IF EXISTS `" . $this->oArgs->target . "`"
                    );
                    */
                    $this->backuped = $this->replaceOnCommit;
                    break;
            }
        }else{
            $this->backupName = $this->oTx->getBackupName($this->oArgs->target);
            $this->replaceOnCommit = is_null($this->backupName);
            if($this->replaceOnCommit){
                $this->backupName = $this->oArgs->target . '__';
                $this->oTx->setBackupName($this->oArgs->target, $this->backupName);
                $this->execSQL(
                    'DROP TABLE IF EXISTS ' . $this->backupName,
                    'drop',
                    AMI_DB::QUERY_TRUSTED
                );
                $this->oDB->allowQuotesInQueryOnce();
                $sql = preg_replace('/cms_[^\s`]+/s', $this->backupName, $this->oArgs->source);
                $this->execSQL($sql, 'create', AMI_DB::QUERY_TRUSTED);
                $this->aRollback[] =
                    "DROP TABLE IF EXISTS `" . $this->oArgs->target . "`";
            }
        }
    }
}

/**
 * Database "DROP TABLE" transaction command.
 *
 * Expects in $oArgs constructor arguments:
 * - mode   - command execustion mode (AMI_iTx_Cmd::MODE_* constants);
 * - target - table name.
 *
 * Example:
 * <code>
 * $this->aTx['db']->addCommand(
 *     'table/drop',
 *     new AMI_Tx_Cmd_Args(
 *         array(
 *             'mode'   => $this->oArgs->mode,
 *             'modId'  => $this->oArgs->modId,
 *             'target' => $table
 *         )
 *     )
 * );
 * </code>
 *
 * @package  TxCommand
 * @since    6.0.2
 * @resource tx/cmd/db/table/drop <code>AMI::getResource('tx/cmd/db/table/drop')</code>
 */
class AMI_Tx_Cmd_DB_DropTable extends AMI_Tx_Cmd_DB{
    /**
     * Commits command.
     *
     * @return void
     */
    public function commit(){
        if($this->oArgs->mode & AMI_iTx_Cmd::MODE_SOFT){
            return;
        }

        $oQuery =
            DB_Query::getSnippet("SHOW TABLES LIKE %s")
            ->q($this->oArgs->target);
        if($this->oDB->fetchRow($oQuery)){
            // table exists
            $this->backupName = $this->oTx->getBackupName($this->oArgs->target);
            if(is_null($this->backupName)){
                $this->backupName = $this->oArgs->target . '_';
                $this->oTx->setBackupName($this->oArgs->target, $this->backupName);
                $this->oTx->setExistence($this->oArgs->target, TRUE);
                $this->execSQL(
                    "DROP TABLE IF EXISTS `" . $this->backupName . "`",
                    'backup',
                    AMI_DB::QUERY_TRUSTED
                );
                $this->execSQL(
                    "RENAME TABLE `" . $this->oArgs->target . "` TO `" . $this->backupName . "`",
                    'rename',
                    AMI_DB::QUERY_TRUSTED
                );
            }
        }
        /*
        if(!($this->oArgs->mode & AMI_iTx_Cmd::MODE_SKIP_ROLLBACK)){
            $this->execSQL(
                "DROP TABLE IF EXISTS `" . $this->oArgs->target . "`",
                'drop',
                AMI_DB::QUERY_TRUSTED
            );
        }
        */
    }

    /**
     * Runs command.
     *
     * @return void
     */
    protected function run(){
        return;
        /*
        if($this->oArgs->mode & AMI_iTx_Cmd::MODE_SOFT){
            return;
        }

        $oQuery =
            DB_Query::getSnippet("SHOW TABLES LIKE %s")
            ->q($this->oArgs->target);
        $exists = FALSE;
        if($this->oDB->fetchRow($oQuery)){
            // table exists
            $this->backupName = $this->oTx->getBackupName($this->oArgs->target);
            if(is_null($this->backupName)){
                $this->backupName = $this->oArgs->target . '__';
                $this->oTx->setBackupName($this->oArgs->target, $this->backupName);
                $this->oTx->setExistence($this->oArgs->target, TRUE);
                $exists = TRUE;
            }
        }

        if($this->oArgs->mode & AMI_iTx_Cmd::MODE_SKIP_ROLLBACK){
            $this->execSQL(
                "DROP TABLE IF EXISTS `" . $this->oArgs->target . "`",
                'drop',
                AMI_DB::QUERY_TRUSTED
            );
        }else{
            $this->backupName = $this->oTx->getBackupName($this->oArgs->target);
            if(is_null($this->backupName)){
                $this->backupName = $this->oArgs->target . '__';
                $this->oTx->setBackupName($this->oArgs->target, $this->backupName);
                $this->oTx->setExistence($this->oArgs->target, $exists);
            }
            $this->execSQL(
                "DROP TABLE IF EXISTS `" . $this->backupName . "`",
                'backup',
                AMI_DB::QUERY_TRUSTED
            );
        }
        */
    }
}

/**
 * Database query transaction command (no ability rollback, just commit).
 *
 * Expects in $oArgs constructor arguments:
 * - query - array of queries.
 *
 * Example:
 * <code>
 * $this->aTx['db']->addCommand(
 *     'query',
 *     new AMI_Tx_Cmd_Args(
 *         array(
 *             'aQueries' => array(...)
 *         )
 *     )
 * );
 * </code>
 *
 * @package  TxCommand
 * @since    6.0.2
 * @resource tx/cmd/db/query <code>AMI::getResource('tx/cmd/db/query')</code>
 */
class AMI_Tx_Cmd_DB_Query extends AMI_Tx_Cmd_DB{
    /**
     * Commits command.
     *
     * @return void
     */
    public function commit(){
        foreach($this->oArgs->aQueries as $query){
            echo is_object($query) ? $query->get() : $query, "\n<br />";###
            $this->execSQL(is_object($query) ? $query->get() : $query, AMI_DB::QUERY_TRUSTED);
        }
    }

    /**
     * Validates passed arguments.
     *
     * @return void
     * @see    AMI_Tx_Cmd::__construct()
     */
    protected function validateArgs(){
        $this->validateObligatoryArgs(array('aQueries'));
    }

    /**
     * Runs command.
     *
     * @return void
     */
    protected function run(){
        $this->oArgs->overwrite('target', '');
    }
}
