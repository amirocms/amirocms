<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   TxCommand
 * @since     6.0.2
 * @version   $Id: AMI_Tx_Cmd_Storage.php 48663 2014-03-13 12:45:13Z Leontiev Anton $
 */

/**
 * Abstract storage transaction command.
 *
 * Expects in $oArgs constructor arguments:
 * - mode    - command execustion mode (AMI_iTx_Cmd::MODE_* constants);
 * - source  - source file path;
 * - target  - target file path;
 * - content - (optional) content to set by AMI_Tx_Cmd_FS::set().
 *
 * @package    TxCommand
 * @subpackage Controller
 * @see        AMI_iStorage
 * @since      6.0.2
 */
abstract class AMI_Tx_Cmd_Storage extends AMI_Tx_Cmd{
    /**
     * Flag specifies if previous file was backuped
     *
     * @var bool
     */
    protected $backuped = FALSE;

    /**
     * Path for backup file
     *
     * @var string
     */
    protected $backupPath;

    /**
     * Storage driver
     *
     * @var AMI_iStorage
     */
    protected $oStorage;

    /**
     * EOL style for conent modifiers
     *
     * @var string
     */
    protected $eol = "\r\n";

    /**
     * Adds resources of available commands.
     *
     * @return void
     */
    public static function addResources(){
        AMI::addResourceMapping(
            array(
                'tx/cmd/storage/copy'  => 'AMI_Tx_Cmd_Storage_Copier',
                'tx/cmd/storage/set'   => 'AMI_Tx_Cmd_Storage_ContentSetter',
                'tx/cmd/storage/clean' => 'AMI_Tx_Cmd_Storage_Cleaner',
                'tx/cmd/php/install'   => 'AMI_Tx_Cmd_PHP_ContentIntsall',
                'tx/cmd/php/uninstall' => 'AMI_Tx_Cmd_PHP_ContentUninstall',
                'tx/cmd/tpl/install'   => 'AMI_Tx_Cmd_Tpl_ContentInstall',
                'tx/cmd/tpl/uninstall' => 'AMI_Tx_Cmd_Tpl_ContentUninstall',
                'tx/cmd/ini/install'   => 'AMI_Tx_Cmd_INI_ContentInstall',
                'tx/cmd/ini/uninstall' => 'AMI_Tx_Cmd_INI_ContentUninstall'
            )
        );
    }

    /**
     * Rollbacks command.
     *
     * Called on transaction exception cought.
     *
     * @return void
     * @throws AMI_Tx_Exception  In case of problems.
     */
    public function rollback(){
        if($this->backuped){
            if(!$this->oStorage->rename($this->backupPath, $this->oArgs->target)){
                $this->oStorage->delete($this->backupPath);
                throw new AMI_Tx_Exception(
                    "Cannot rollback data from backup '" . $this->backupPath . "'",
                    AMI_Tx_Exception::CMD_ON_ROLLBACK_FILE
                );
            }
        }
        /*else{
            if(!$this->oStorage->delete($this->oArgs->target)){
                throw new AMI_Tx_Exception(
                    "Cannot delete '" . $this->oArgs->target . "'",
                    AMI_Tx_Exception::CMD_ON_ROLLBACK_FILE
                );
            }
        }
         */
    }

    /**
     * Commits command.
     *
     * Called after all commands finished successfully.
     *
     * @return void
     */
    public function commit(){
        // d::vd($this->backuped, $this->backupPath);###
        if($this->replaceOnCommit){
            $target = $this->oArgs->target;
            if($this->backuped){
                $realBackup =
                    dirname($this->backupPath) . '/_' . basename($this->backupPath);
                if($this->oStorage->exists($realBackup)){
                    $this->oStorage->delete($realBackup);
                }
                $this->oStorage->rename($target, $realBackup);
                $this->oStorage->rename($this->backupPath, $target);
                $this->oStorage->rename($realBackup, $this->backupPath);
            }elseif($this->oStorage->exists($this->backupPath)){
                if($this->oStorage->exists($target)){
                    $resId = AMI::getResourceByClass(get_class($this));
                    if(
                        $this->oArgs->mode & AMI_iTx_Cmd::MODE_APPEND
                            ? !empty($this->isContentModifier)
                            : TRUE
                    ){
                        $this->oStorage->rename($this->backupPath, $target);
                    }
                }else{
                    $this->oStorage->rename($this->backupPath, $target);
                }
            }
            if(
                ($this->oArgs->mode & self::MODE_DELETE_BACKUP_ON_COMMIT) &&
                $this->oStorage->exists($this->backupPath)
            ){
                $this->oStorage->delete($this->backupPath);
            }
        }
    }

    /**
     * Validates passed arguments.
     *
     * @return void
     * @see    AMI_Tx_Cmd::__construct()
     * @throws AMI_Tx_Exception  In case of problems.
     */
    protected function validateArgs(){
        $this->validateObligatoryArgs(array('mode', 'target', 'oStorage'));
        if(!is_object($this->oArgs->oStorage) || !($this->oArgs->oStorage instanceof AMI_iStorage)){
            throw new AMI_Tx_Exception(
                "'oStorage' argument must be an instance of AMI_iStorage",
                AMI_Tx_Exception::CMD_INVALID_ARG
            );
        }
    }

    /**
     * Initializes command.
     *
     * @return void
     * @throws AMI_Tx_Exception  In case of problems.
     */
    protected function init(){
        $this->oStorage = $this->oArgs->oStorage;
        $this->backupPath = $this->oTx->getBackupName($this->oArgs->target, $this->oArgs->oStorage);
        $this->replaceOnCommit = is_null($this->backupPath);
        if($this->replaceOnCommit){
            $this->backupPath =
                dirname($this->oArgs->target) . '/_' . basename($this->oArgs->target);
            $this->oTx->setBackupName(
                $this->oArgs->target,
                $this->backupPath,
                $this->oArgs->oStorage
            );
            $this->oTx->setExistence(
                $this->oArgs->target,
                $this->oArgs->oStorage->exists($this->oArgs->target),
                $this->oArgs->oStorage
            );
        }
        if($this->oStorage->exists($this->oArgs->target)){
            if(
                !($this->oArgs->mode & AMI_iTx_Cmd::MODE_OVERWRITE) &&
                !($this->oArgs->mode & AMI_iTx_Cmd::MODE_APPEND) &&
                !($this->oArgs->mode & AMI_iTx_Cmd::MODE_IGNORE_TARGET_EXISTENCE)
            ){
                throw new AMI_Tx_Exception(
                    "Target file '" . $this->oArgs->target . "' already exists",
                    AMI_Tx_Exception::CMD_EXISTING_FILE,
                    null,
                    array('target' => $this->oArgs->target)
                );
            }
            if(!($this->oArgs->mode & AMI_iTx_Cmd::MODE_APPEND)){
                $this->backup();
            }
        }
    }

    /**
     * Backups target file.
     *
     * @return void
     * @throws AMI_Tx_Exception  In case of problems.
     */
    protected function backup(){
        if($this->oStorage->exists($this->oArgs->target) && !is_null($this->backupPath)){
            if($this->oStorage->exists($this->backupPath)){
                $this->oStorage->delete($this->backupPath);
            }
            if($this->oStorage->copy($this->oArgs->target, $this->backupPath)){
                $this->backuped = TRUE;
            }else{
                throw new AMI_Tx_Exception(
                    "Cannot backup rollback data to '" . $this->backupPath . "'",
                    AMI_Tx_Exception::CMD_ON_BACKUP_FILE,
                    null,
                    array('backup' => $this->backupPath)
                );
            }
        }
    }

    /**
     * Copies source to target file.
     *
     * @return void
     * @throws AMI_Tx_Exception  In case of problems.
     */
    protected function copy(){
        if(
            ($this->oArgs->mode & AMI_iTx_Cmd::MODE_APPEND) &&
            $this->oStorage->exists($this->oArgs->target)
        ){
            return;
        }
        if(!$this->oStorage->copy($this->oArgs->source, $this->backupPath)){
            throw new AMI_Tx_Exception(
                "Cannot copy data from '" . $this->oArgs->source . "' to '" . $this->backupPath . "'",
                AMI_Tx_Exception::CMD_ON_CREATE_FILE,
                null,
                array(
                    'type'   => 'copy',
                    'source' => $this->oArgs->source,
                    'target' => $this->backupPath
                )
            );
        }
    }

    /**
     * Sets target file content.
     *
     * @param  string $content  Content to set
     * @return void
     * @throws AMI_Tx_Exception  In case of problems.
     */
    protected function set($content = null){
        if(($this->oArgs->mode & AMI_iTx_Cmd::MODE_APPEND) && $this->backuped){
            return;
        }
        if(
            !$this->oStorage->save(
                $this->backupPath,
                is_null($content) ? $this->oArgs->content : $content,
                !empty($this->oArgs->asDefault)
            )
        ){
            throw new AMI_Tx_Exception(
                "Cannot set data to '" . $this->backupPath . "'",
                AMI_Tx_Exception::CMD_ON_CREATE_FILE,
                null,
                array(
                    'type'   => 'create',
                    'target' => $this->backupPath
                )
            );
        }
    }
}

/**
 * Storage content setter transaction command.
 *
 * Sets content to target.
 * Example:
 * <code>
 * // "install_after.php" / "install.php" context
 *
 * $srcPath = dirname(__FILE__) . '/';
 * $destPath = AMI_Registry::get('path/root') . '_local/';
 *
 * $oStorage = AMI::getResource('storage/fs');
 *
 * $file = 'ami_redirect.php';
 * // Load PHP-code template file
 * $content = $oStorage->load($srcPath . $file);
 * // Replace ##modId## by instance id
 * $content = str_replace('##modId##', $this->oArgs->modId, $content);
 * // Save file with PHP-code
 *
 * $oArgs = new AMI_Tx_Cmd_Args(
 *     array(
 *         // Installation mode
 *         'mode'     => $this->oArgs->mode,
 *         // New file content
 *         'content'  => $content,
 *         // Target file path
 *         'target'   => $destPath . $file,
 *         // Storage driver
 *         'oStorage' => $oStorage
 *     )
 * );
 * $this->aTx['storage']->addCommand('storage/set', $oArgs);
 *
 * // File 'ami_redirect.php' contains PHP code starting from opening PHP tag
 * </code>
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      6.0.2
 * @resource   tx/cmd/storage/set <code>AMI::getResource('tx/cmd/storage/set')</code>
 */
class AMI_Tx_Cmd_Storage_ContentSetter extends AMI_Tx_Cmd_Storage{
    /**
     * Runs command.
     *
     * @return void
     */
    protected function run(){
        // $this->backup();
        $this->set();
    }
}

/**
 * Storage data copier transaction command.
 *
 * Sets content to target.
 * Example:
 * <code>
 * // "install_after.php" / "install.php" context
 *
 * $srcPath = dirname(__FILE__) . '/';
 * $destPath = AMI_Registry::get('path/root') . '_local/eshop/';
 *
 * $oStorage = AMI::getResource('storage/fs');
 *
 * $file = 'AtoPaymentSystem.php';
 *
 * $oArgs = new AMI_Tx_Cmd_Args(
 *     array(
 *         // Installation mode
 *         'mode'     => AMI_iTx_Cmd::MODE_APPEND,
 *         // Source file path
 *         'source'   => $srcPath . $file,
 *         // Target file path
 *         'target'   => $destPath . $file,
 *         // Storage driver
 *         'oStorage' => $oStorage
 *     )
 * );
 * $this->aTx['storage']->addCommand('storage/set', $oArgs);
 *
 * // File 'AtoPaymentSystem.php' contains PHP code starting from opening PHP tag
 * </code>
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      6.0.2
 * @resource   tx/cmd/storage/copy <code>AMI::getResource('tx/cmd/storage/copy')</code>
 */
class AMI_Tx_Cmd_Storage_Copier extends AMI_Tx_Cmd_Storage{
    /**
     * Validates passed arguments.
     *
     * @return void
     * @see    AMI_Tx_Cmd::__construct()
     */
    protected function validateArgs(){
        $this->validateObligatoryArgs(array('source'));

        parent::validateArgs();
    }

    /**
     * Runs command.
     *
     * @return void
     */
    protected function run(){
        $this->copy();
    }
}

/**
 * Files removing transaction command.
 *
 * Expects in $oArgs constructor arguments:
 * - rmEmptyDir - (optional) TRUE if need remove empty directory.
 * Example:
 * <code>
 * // "uninstall_before.php" / "uninstall.php" context
 *
 * $oStorage = AMI::getResource('storage/fs');
 *
 * $destPath = AMI_Registry::get('path/root') . '_local/';
 *
 * $file = 'ami_redirect.php';
 *
 * $this->aTx['storage']->addCommand(
 *     'storage/clean',
 *     new AMI_Tx_Cmd_Args(
 *         array(
 *             'modId'    => $this->oArgs->modId,
 *             'mode'     => $this->oArgs->mode,
 *             'target'   => $destPath . $file,
 *             'oStorage' => $oStorage
 *         )
 *     )
 * );
 * </code>
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      6.0.2
 * @resource   tx/cmd/storage/clean <code>AMI::getResource('tx/cmd/storage/clean')</code>
 */
class AMI_Tx_Cmd_Storage_Cleaner extends AMI_Tx_Cmd_Storage{
    /**
     * Commits command.
     *
     * Called after all commands finished successfully.
     *
     * @return void
     */
    public function commit(){
        parent::commit();

        if($this->oStorage->exists($this->oArgs->target)){
            $this->oStorage->delete($this->oArgs->target);
        }
        if($this->backuped && !empty($this->oArgs->rmEmptyDir)){
            $this->oStorage->rmdir(dirname($this->backupPath), TRUE);
        }
    }

    /**
     * Initializes command.
     *
     * @return void
     */
    protected function init(){
        // To avoid throwing exception on existent content
        $this->oArgs->overwrite(
            'mode',
            $this->oArgs->mode | AMI_iTx_Cmd::MODE_IGNORE_TARGET_EXISTENCE
        );

        parent::init();
    }

    /**
     * Runs command.
     *
     * @return void
     */
    protected function run(){
    }
}

/**
 * Storage content modifier transaction command.
 *
 * Sets content to target.
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      6.0.2
 */
abstract class AMI_Tx_Cmd_Storage_ContentModifier extends AMI_Tx_Cmd_Storage{
    /**
     * @var bool
     */
    protected $isContentModifier = TRUE;

    /**
     * Content modifier.
     *
     * @param  string &$content  Content
     * @param  string $opener    Opening marker
     * @param  string $closer    Closing marker
     * @return void
     */
    protected abstract function modify(&$content, $opener, $closer);

    /**
     * Creates new if no content present.
     *
     * @return string
     */
    protected abstract function createNewContent();

    /**
     * Returns opening marker.
     *
     * @return string
     */
    protected abstract function getOpeningMarker();

    /**
     * Returns closing marker.
     *
     * @return string
     */
    protected abstract function getClosingMarker();

    /**
     * Validates passed arguments.
     *
     * @return void
     * @see    AMI_Tx_Cmd::__construct()
     */
    protected function validateArgs(){
        $this->validateObligatoryArgs(array('modId'));

        parent::validateArgs();
    }

    /**
     * Flag specified that error was handled during token_get_all()
     *
     * @var    bool
     * @see    AMI_Tx_Cmd_Storage_ContentModifier::checkPHPSyntax()
     * @see    AMI_Tx_Cmd_Storage_ContentModifier::handleError()
     * @amidev Temporary
     */
    protected $syntaxError;

    /**
     * Runs command.
     *
     * Patches target file.
     *
     * @return void
     * @throws AMI_Tx_Exception  In case of missing obligatory argument.
     */
    protected function run(){
        $file = $this->oArgs->target;
        $content = $this->oStorage->load($this->backuped ? $this->backupPath : $file, FALSE);
        if($content === FALSE){
            $content = $this->createNewContent();
        }
        $opener = $this->getOpeningMarker();
        $closer = $this->getClosingMarker();
        if($this->checkMarkers($content, $opener, $closer)){
            $this->modify($content, $opener, $closer);
            $this->set($content);
        }
    }

    /**
     * Add/replace markers.
     *
     * Add if not found, wipe content if found and have appropriate mode.
     *
     * @param  string &$content  Content
     * @param  string $opener   Opening marker
     * @param  string $closer   Closing marker
     * @return bool
     */
    protected function checkMarkers(&$content, $opener, $closer){
        $modId = $this->oArgs->modId;
        $file  = $this->oArgs->target;
        $start = mb_strpos($content, $opener);
        $end = mb_strpos($content, $closer);
        if(
            ($start !== FALSE && $end === FALSE) ||
            ($start === FALSE && $end !== FALSE)
        ){
            throw new AMI_Tx_Exception(
                "Broken content marker at file '" . $file . "'",
                AMI_Tx_Exception::CMD_BROKEN_CONTENT_MARKER
            );
        }
        $patch = TRUE;
        if($start !== FALSE && $end !== FALSE){
            if($this->oArgs->mode & (AMI_iTx_Cmd::MODE_OVERWRITE | AMI_iTx_Cmd::MODE_IGNORE_DATA_EXISTENCE)){
                // Get content to replace
                $content = mb_substr($content, 0, $start) . mb_substr($content, $end + mb_strlen($closer));
            }else{
                if($this->oArgs->mode & AMI_iTx_Cmd::MODE_APPEND){
                    // Content markers already exist, nothing to patch in append mode
                    $patch = FALSE;
                }else{
                    // Module captions already exist, but mode doesn't allow this
                    throw new AMI_Tx_Exception(
                        "Content markers for '" . $modId . "' at '" . $file . "' already exists",
                        AMI_Tx_Exception::CMD_DUPLICATE_CONTENT_MARKER
                    );
                }
            }
        }

        return $patch;
    }

    /**
     * Checks PHP syntax for parse errors.
     *
     * @param  string $code  PHP code (having no starting/closing tags)
     * @return bool
     * @amidev Temporary
     */
    protected function checkPHPSyntax($code){
        $braceLevel = 0;
        $stringLevel = 0;

        // Try to catch unclosed comments
        $this->syntaxError = FALSE;
        set_error_handler(array($this, 'errorHandler'));
        $aTockens = @token_get_all('<?php ' . $code);
        restore_error_handler();
        if($this->syntaxError){
            return FALSE;
        }

        // We need to know if braces are correctly balanced.
        // This is not trivial due to variable interpolation
        // which occurs in heredoc, backticked and double quoted strings
        foreach($aTockens as $token){
            if(is_array($token)){
                switch($token[0]){
                    case T_CURLY_OPEN:
                    case T_DOLLAR_OPEN_CURLY_BRACES:
                    case T_START_HEREDOC:
                        ++$stringLevel;
                        break;
                    case T_END_HEREDOC:
                        --$stringLevel;
                        break;
                }
            }elseif($stringLevel & 1){
                switch ($token){
                    case '`':
                    case '"':
                        --$stringLevel;
                        break;
                }
            }else{
                switch($token){
                    case '`':
                    case '"':
                        ++$stringLevel;
                        break;
                    case '{':
                        ++$braceLevel;
                        break;
                    case '}':
                        if($stringLevel){
                            --$stringLevel;
                        }else{
                            --$braceLevel;
                            if($braceLevel < 0){
                                return FALSE;
                            }

                        }
                        break;
                }
            }
        }
        if($braceLevel){
            // Unbalanced braces would break the eval below
            return FALSE;

        }else{
            // Catch potential parse error messages
            ob_start();
             // Put $code in a dead code sandbox to prevent its execution
            $code = @eval('if(0){' . $code . '}');
            ob_end_clean();

            return FALSE !== $code;
        }
    }

    /**
     * Handles errors during token_get_all().
     *
     * @return void
     * @see    AMI_Tx_Cmd_Storage_ContentModifier::checkPHPSyntax()
     * @amidev Temporary
     */
    protected function errorHandler(){
        $this->syntaxError = TRUE;
    }
}

/**
 * PHP-file content modifier.
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      6.0.2
 */
abstract class AMI_Tx_Cmd_PHP_ContentModifier extends AMI_Tx_Cmd_Storage_ContentModifier{
    /**
     * Creates new if no content present.
     *
     * @return string
     */
    protected function createNewContent(){
        // Create declaration file if not exists
        $content = '<' . "?php" . $this->eol . $this->eol;
        AMI_Registry::push('disable_error_mail', TRUE);
        trigger_error("Missing PHP file at '" . $this->oArgs->target . "', creating new one", E_USER_WARNING);
        AMI_Registry::pop('disable_error_mail');

        return $content;
    }

    /**
     * Returns opening marker.
     *
     * @return string
     */
    protected function getOpeningMarker(){
        return '// Do not delete this comment! [' . $this->oArgs->modId . '] {' . $this->eol;
    }

    /**
     * Returns closing marker.
     *
     * @return string
     */
    protected function getClosingMarker(){
        return '// } Do not delete this comment! [' . $this->oArgs->modId . ']' . $this->eol;
    }
}

/**
 * Transaction command adding PHP-file content.
 *
 * Example:
 * <code>
 * // "install_after.php" / "install.php" context
 *
 * $srcPath = dirname(__FILE__) . '/';
 * $destPath = AMI_Registry::get('path/root') . '_local/';
 *
 * $oStorage = AMI::getResource('storage/fs');
 *
 * $file = 'common_functions.php';

 * $oArgs = new AMI_Tx_Cmd_Args(
 *     array(
 *         // Instance Id
 *         'modId'    => $this->oArgs->modId,
 *         // Installation mode
 *         'mode'     => $this->oArgs->mode,
 *         // Source PHP-file path
 *         'source'   => $srcPath . $file,
 *         // Target PHP-file to patch
 *         'target'   => $destPath . $file,
 *         // Storage driver
 *         'oStorage' => $oStorage
 *     )
 * );
 * $this->aTx['storage']->addCommand('php/install', $oArgs);
 *
 * // File 'common_functions.php' contains PHP-template:
 * <?php
 * ....
 * // {{}}  <- PHP-code template start marker
 * include_once('ami_redirect.php');
 * ...
 * </code>
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      6.0.2
 * @resource   tx/cmd/php/install <code>AMI::getResource('tx/cmd/php/install')</code>
 */
class AMI_Tx_Cmd_PHP_ContentIntsall extends AMI_Tx_Cmd_PHP_ContentModifier{
    /**
     * Obligatory arguments.
     *
     * To give ability to override obligatory arguments
     *
     * @var   array
     * @since 6.0.6
     */
    protected $aObligatoryArgs = array('source');

    /**
     * Initializes command.
     *
     * @return void
     */
    protected function init(){
        // To avoid throwing exception on existent local declaration file
        $this->oArgs->overwrite(
            'mode',
            $this->oArgs->mode | AMI_iTx_Cmd::MODE_IGNORE_TARGET_EXISTENCE
        );

        parent::init();
    }

    /**
     * Validates passed arguments.
     *
     * @return void
     * @see    AMI_Tx_Cmd::__construct()
     */
    protected function validateArgs(){
        $this->validateObligatoryArgs($this->aObligatoryArgs);

        parent::validateArgs();
    }

    /**
     * Content modifier.
     *
     * Appends target file content.
     *
     * @param  string &$content  Content
     * @param  string $opener    Opening marker
     * @param  string $closer    Closing marker
     * @return void
     * @throws AMI_Tx_Exception  In case of missing content template
     */
    protected function modify(&$content, $opener, $closer){
        $code = $this->oStorage->load($this->oArgs->source);
        if($code === FALSE){
            throw new AMI_Tx_Exception(
                "Missing PHP template at '" . $this->oArgs->source . "'",
                AMI_Tx_Exception::CMD_MISSING_TPL_CONTENT
            );
        }
        $codeOpener = '// {{}}' . $this->eol;
        $aArgs = array_filter(
            $this->oArgs->getAll(),
            array($this, 'cbFilterObjects')
        );
        $code = str_replace(
            array_map(
                array($this, 'cbToTplVar'),
                array_keys($aArgs)
            ),
            array_values($aArgs),
            $code
        );
        $code = mb_substr($code, mb_strpos($code, $codeOpener) + mb_strlen($codeOpener));
        if(!$this->checkPHPSyntax($code)){
            throw new AMI_Tx_Exception(
                "Parse error found in '" . $this->oArgs->source . "', source:\n" . $code,
                AMI_Tx_Exception::CMD_INVALID_TPL_CONTENT
            );
        }
        $content .= $opener . $code . $closer;
    }

    /**
     * Callback filterring objects from arguments.
     *
     * @param  mixed $value  Value
     * @return bool
     * @see    AMI_Tx_Cmd_PHP_ContentIntsaller::modify()
     */
    protected function cbFilterObjects($value){
        return !is_object($value);
    }

    /**
     * Callback converting key to template variable name.
     *
     * @param  string $key  Name
     * @return string
     * @see    AMI_Tx_Cmd_PHP_ContentIntsaller::modify()
     */
    protected function cbToTplVar($key){
        return '##' . $key . '##';
    }
}

/**
 * Transaction command deleting part of PHP-file content.
 *
 * Example:
 * <code>
 * // "uninstall_before.php" / "uninstall_after.php" / "uninstall.php" / "uninstall_all.php" context
 * $oStorage = AMI::getResource('storage/fs');
 *
 * $destPath = AMI_Registry::get('path/root') . '_local/';
 *
 * // Patch "_local/common_functions.php"
 *
 * $file = 'common_functions.php';
 * $oArgs = new AMI_Tx_Cmd_Args(
 *     array(
 *         // Unnstallation mode
 *         'mode'      => $this->oArgs->mode,
 *         // Instance Id
 *         'modId'     => $this->oArgs->modId,
 *         // Target file path
 *         'target'    => $destPath . $file,
 *         // Storage driver
 *         'oStorage'  => $oStorage
 *     )
 * );
 * $this->aTx['storage']->addCommand('php/uninstall', $oArgs);
 * </code>
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      6.0.2
 * @resource   tx/cmd/php/uninstall <code>AMI::getResource('tx/cmd/php/uninstall')</code>
 */
class AMI_Tx_Cmd_PHP_ContentUninstall extends AMI_Tx_Cmd_PHP_ContentModifier{
    /**
     * Validates passed arguments.
     *
     * @return void
     * @see    AMI_Tx_Cmd::__construct()
     */
    protected function validateArgs(){
        $this->validateObligatoryArgs(array('target'));

        parent::validateArgs();
    }

    /**
     * Initializes command.
     *
     * @return void
     */
    protected function init(){
        // To avoid throwing exception on existent file or
        // module declaration data inside
        $this->oArgs->overwrite(
            'mode',
            $this->oArgs->mode |
            AMI_iTx_Cmd::MODE_IGNORE_TARGET_EXISTENCE |
            AMI_iTx_Cmd::MODE_IGNORE_DATA_EXISTENCE
        );

        parent::init();
    }

    /**
     * Content modifier.
     *
     * @param  string &$content  Content
     * @param  string $opener    Opening marker
     * @param  string $closer    Closing marker
     * @return void
     */
    protected function modify(&$content, $opener, $closer){
        // Nothing to do to wipe out code.
    }
}

/**
 * Template/locale file content modifier.
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      6.0.2
 */
abstract class AMI_Tx_Cmd_Tpl_ContentModifier extends AMI_Tx_Cmd_Storage_ContentModifier{
    /**
     * Validates passed arguments.
     *
     * @return void
     * @see    AMI_Tx_Cmd::__construct()
     */
    protected function validateArgs(){
        $this->validateObligatoryArgs(array('modId'));

        parent::validateArgs();
    }

    /**
     * Creates new if no content present.
     *
     * @return string
     */
    protected function createNewContent(){
        // No template/locale file found, try to create it
        trigger_error("Missing template/locale file '" . $this->oArgs->target . "'", E_USER_NOTICE);
        $content = '##--system info: module_owner="" module="" system="0"--##' . $this->eol;

        return $content;
    }

    /**
     * Returns opening marker.
     *
     * @return string
     */
    protected function getOpeningMarker(){
        return '##-- Do not delete this comment! [' . $this->oArgs->modId . '] { --##' . $this->eol;
    }

    /**
     * Returns closing marker.
     *
     * @return string
     */
    protected function getClosingMarker(){
        return '##-- } Do not delete this comment! [' . $this->oArgs->modId . '] --##' . $this->eol;
    }
}

/**
 * Template/locale data installation transaction command.
 *
 * Example:
 * <code>
 * // "install_after.php" / "install.php" context
 *
 * $oTplStorage = AMI::getResource('storage/tpl');
 *
 * $srcPath = dirname(__FILE__) . '/';
 * $destPath = AMI_Registry::get('path/root') . '_local/';
 *
 * // Patch "templates/user_source_app.tpl" template
 * // Patch "templates/;ang/user_source_app.lng" locale
 *
 * foreach(
 *     array(
 *         $srcPath . 'user_source_app.tpl' => AMI_iTemplate::TPL_PATH . '/user_source_app.tpl',
 *         $srcPath . 'user_source_app.lng' => AMI_iTemplate::LNG_PATH . '/user_source_app.lng'
 *     ) as $src => $dest
 * ){
 *     $oArgs = new AMI_Tx_Cmd_Args(
 *         array(
 *             // Installation mode
 *             'mode'      => $this->oArgs->mode,
 *             // Instance Id
 *             'modId'     => $this->oArgs->modId,
 *             // Target template/locale path
 *             'target'    => $dest,
 *             // Content to add
 *             'content'   => $oStorage->load($src),
 *             // Storage driver
 *             'oStorage'  => $oTplStorage
 *         );
 *     $this->aTx['storage']->addCommand('tpl/install', $oArgs);
 * }
 * </code>
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      6.0.2
 * @resource   tx/cmd/tpl/install <code>AMI::getResource('tx/cmd/tpl/install')</code>
 */
class AMI_Tx_Cmd_Tpl_ContentInstall extends AMI_Tx_Cmd_Tpl_ContentModifier{
    /**
     * Initializes command.
     *
     * @return void
     */
    protected function init(){
        // To avoid throwing exception on existent caption files
        $this->oArgs->overwrite(
            'mode',
            $this->oArgs->mode | AMI_iTx_Cmd::MODE_IGNORE_TARGET_EXISTENCE
        );

        parent::init();
    }

    /**
     * Validates passed arguments.
     *
     * @return void
     * @see    AMI_Tx_Cmd::__construct()
     */
    protected function validateArgs(){
        $this->validateObligatoryArgs(array('content'));

        parent::validateArgs();
    }

    /**
     * Content modifier.
     *
     * @param  string &$content  Content
     * @param  string $opener    Opening marker
     * @param  string $closer    Closing marker
     * @return void
     */
    protected function modify(&$content, $opener, $closer){
        $content .= $opener . $this->oArgs->content . $closer;
    }
}

/**
 * Template/locale data uninstallation transaction command.
 *
 * Example:
 * <code>
 * // "uninstall_before.php" / "uninstall_after.php" / "uninstall.php" / "uninstall_all.php" context
 * $oTplStorage = AMI::getResource('storage/tpl');
 *
 * $srcPath = dirname(__FILE__) . '/';
 * $destPath = AMI_Registry::get('path/root') . '_local/';
 *
 * // Patch "templates/user_source_app.tpl" template
 * // Patch "templates/;ang/user_source_app.lng" locale
 *
 * foreach(
 *     array(
 *         AMI_iTemplate::TPL_PATH . '/user_source_app.tpl',
 *         AMI_iTemplate::LNG_PATH . '/user_source_app.lng'
 *     ) as $dest
 * ){
 *     $oArgs = new AMI_Tx_Cmd_Args(
 *         array(
 *             // Uninstallation mode
 *             'mode'      => $this->oArgs->mode,
 *             // Instance Id
 *             'modId'     => $this->oArgs->modId,
 *             // Target template/locale path
 *             'target'    => $dest,
 *             // Storage driver
 *             'oStorage'  => $oTplStorage,
 *         );
 *     $this->aTx['storage']->addCommand('tpl/uninstall', $oArgs);
 * }
 * </code>
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      6.0.2
 * @resource   tx/cmd/tpl/uninstall <code>AMI::getResource('tx/cmd/tpl/uninstall')</code>
 */
class AMI_Tx_Cmd_Tpl_ContentUninstall extends AMI_Tx_Cmd_Tpl_ContentModifier{
    /**
     * Initializes command.
     *
     * @return void
     */
    protected function init(){
        // To avoid throwing exception on existent caption files or
        // captions data inside
        $this->oArgs->overwrite(
            'mode',
            $this->oArgs->mode |
            AMI_iTx_Cmd::MODE_IGNORE_TARGET_EXISTENCE |
            AMI_iTx_Cmd::MODE_IGNORE_DATA_EXISTENCE
        );

        parent::init();
    }

    /**
     * Content modifier.
     *
     * @param  string &$content  Content
     * @param  string $opener    Opening marker
     * @param  string $closer    Closing marker
     * @return void
     */
    protected function modify(&$content, $opener, $closer){
        // Module related content will be wiped out automatically
    }
}

/**
 * INI-file content modifier.
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      6.0.2
 */
abstract class AMI_Tx_Cmd_INI_ContentModifier extends AMI_Tx_Cmd_Storage_ContentModifier{
    /**
     * Validates passed arguments.
     *
     * @return void
     * @see    AMI_Tx_Cmd::__construct()
     */
    protected function validateArgs(){
        $this->validateObligatoryArgs(array('modId'));

        parent::validateArgs();
    }

    /**
     * Creates new if no content present.
     *
     * @return string
     */
    protected function createNewContent(){
        // No template/locale file found, try to create it
        $content = '; <' . '?php /*!!! DO NOT REMOVE THIS LINE !!!*/ die; ?' . '>' . $this->eol . $this->eol;
        AMI_Registry::push('disable_error_mail', TRUE);
        trigger_error("Missing INI-file '" . $file . "'", E_USER_WARNING);
        AMI_Registry::pop('disable_error_mail');
        return $content;
    }

    /**
     * Returns opening marker.
     *
     * @return string
     */
    protected function getOpeningMarker(){
        return '; Do not delete this comment! [' . $this->oArgs->modId . '] {' . $this->eol;
    }

    /**
     * Returns closing marker.
     *
     * @return string
     */
    protected function getClosingMarker(){
        return '; } Do not delete this comment! [' . $this->oArgs->modId . ']' . $this->eol;
    }
}

/**
 * INI-file data installation transaction command.
 *
 * <code>
 * // "install_after.php" / "install.php" context
 *
 * $srcPath = dirname(__FILE__) . '/';
 * $destPath = AMI_Registry::get('path/root') . '_local/';
 *
 * $oStorage = AMI::getResource('storage/fs');
 *
 * // Insetrt data to INI-file
 *
 * $file = 'user_source_app.ini.php';
 *
 * $oArgs = new AMI_Tx_Cmd_Args(
 *     array(
 *         // Installation mode
 *         'mode'      => $this->oArgs->mode,
 *         // Instance Id
 *         'modId'     => $this->oArgs->modId,
 *         // Target file path
 *         'target'    => $destPath . $file,
 *         // Content to add
 *         'content'   => $oStorage->load($srcPath . $file),
 *         // Storage driver
 *         'oStorage'  => $oStorage
 *     );
 * $this->aTx['storage']->addCommand('ini/install', $oArgs);
 * </code>
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      6.0.2
 * @resource   tx/cmd/ini/install <code>AMI::getResource('tx/cmd/ini/install')</code>
 */
class AMI_Tx_Cmd_INI_ContentInstall extends AMI_Tx_Cmd_INI_ContentModifier{
    /**
     * Initializes command.
     *
     * @return void
     */
    protected function init(){
        // To avoid throwing exception on existent caption files
        $this->oArgs->overwrite(
            'mode',
            $this->oArgs->mode | AMI_iTx_Cmd::MODE_IGNORE_TARGET_EXISTENCE
        );

        parent::init();
    }

    /**
     * Validates passed arguments.
     *
     * @return void
     * @see    AMI_Tx_Cmd::__construct()
     */
    protected function validateArgs(){
        $this->validateObligatoryArgs(array('content'));

        parent::validateArgs();
    }

    /**
     * Content modifier.
     *
     * @param  string &$content  Content
     * @param  string $opener    Opening marker
     * @param  string $closer    Closing marker
     * @return void
     */
    protected function modify(&$content, $opener, $closer){
        $content .= $opener . $this->oArgs->content . $closer;
    }
}

/**
 * INI-file data uninstallation transaction command.
 *
 * Example:
 * <code>
 * // "uninstall_before.php" / "uninstall_after.php" / "uninstall.php" / "uninstall_all.php" context
 * $oStorage = AMI::getResource('storage/fs');
 *
 * $destPath = AMI_Registry::get('path/root') . '_local/';
 *
 * // Delete data from INI-file
 *
 * $file = 'user_source_app.ini.php';
 * $oArgs = new AMI_Tx_Cmd_Args(
 *     array(
 *         // Unnstallation mode
 *         'mode'      => $this->oArgs->mode,
 *         // Instance Id
 *         'modId'     => $this->oArgs->modId,
 *         // Target file path
 *         'target'    => $destPath . $file,
 *         // Storage driver
 *         'oStorage'  => $oStorage
 *     );
 * $this->aTx['storage']->addCommand('ini/uninstall', $oArgs);
 *
 * </code>
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      6.0.2
 * @resource   tx/cmd/ini/uninstall <code>AMI::getResource('tx/cmd/ini/uninstall')</code>
 */
class AMI_Tx_Cmd_INI_ContentUninstall extends AMI_Tx_Cmd_INI_ContentModifier{
    /**
     * Initializes command.
     *
     * @return void
     */
    protected function init(){
        // To avoid throwing exception on existent caption files or
        // captions data inside
        $this->oArgs->overwrite(
            'mode',
            $this->oArgs->mode |
            AMI_iTx_Cmd::MODE_IGNORE_TARGET_EXISTENCE |
            AMI_iTx_Cmd::MODE_IGNORE_DATA_EXISTENCE
        );

        parent::init();
    }

    /**
     * Content modifier.
     *
     * @param  string &$content  Content
     * @param  string $opener    Opening marker
     * @param  string $closer    Closing marker
     * @return void
     */
    protected function modify(&$content, $opener, $closer){
        // Module related content will be wiped out automatically
    }
}
