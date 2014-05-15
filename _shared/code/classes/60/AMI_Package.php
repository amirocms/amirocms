<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Package
 * @version   $Id: AMI_Package.php 49380 2014-04-03 11:25:12Z Leontiev Anton $
 * @since     6.0.2
 */

/**
 * Transaction CMS module manipulating command exception.
 *
 * @package Package
 * @since   6.0.2
 */
class AMI_Package_Exception extends AMI_Exception{
    /**#@+
     * Manipulating CMS module transaction command code
     */

    /**
     * Throws if template mode is set to read templates from disk.
     */
    const INVALID_TEMPLATE_MODE    = 10000;

    /**
     * Throws if invalid module Id passed.
     */
    const INVALID_MOD_ID           = 10010;

    /**
     * Throws if passed already installed module Id for installation.
     */
    const ALREADY_INSTALLED        = 10020;

    /**
     * Throws if passed reserved module Id (some configurations has same).
     */
    const RESERVED_MOD_ID          = 10030;

    /**
     * Throws if passed invalid hypermodule or configuration for installation.
     */
    const INVALID_HYPER_MOD_CONFIG = 10040;

    /**
     * Throws if passed not all obligatory captions data for installation.
     */
    const INVALID_CAPTIONS_DATA    = 10050;

    /**
     * Throws if configuration resource not found during installation.
     */
    const MISSING_CONFIG_RESOURCE  = 10060;

    /**
     * Throws if installation is not in overwrite mode and some module captions
     * are already existing during installation.
     */
    const EXISTING_CAPTIONS        = 10070;

    /**
     * Throws if captions file has current module opening/closing markers without
     * its pairs during installation.
     */
    const BROKEN_CAPTIONS_FILE     = 10080;

    /**
     * Throws if unable to save patched captions file during installation.
     */
    const ON_CREATE_CAPTIONS       = 10090;

    /**
     * Throws if unable to find declaration template i. e.
     * "_local/modules/distrib/configs/ami_clean/ami_sample/declaration/declaration.php"
     * during installation.
     */
    const MISSING_DECLARATION_TPL  = 10100;

    /**
     * Throws if unable to open local declaration file
     * "_local/modules/declaration/declares.php" during installation.
     */
    const OPEN_DECLARATION         = 10110;

    /**
     * Throws if local declaration file has current module opening/closing
     * markers without its pairs during installation.
     */
    const PARSE_DECLARATION        = 10120;

    /**
     * Throws if installation is not in overwrite mode module declaration
     * is already existing in local declaration file during installation.
     */
    const EXISTING_DECLARATION     = 10130;

    /**
     * Throws if unable to save patched local declaration file during
     * installation.
     */
    const ON_CREATE_DECLARATION    = 10140;

    /**
     * Throws if configuration has 1 instance limit during installation.
     */
    const INSTANCE_LIMIT           = 10150;

    /**
     * Throws if module was already installed/uninstalled during script
     * execution (old core limitation).
     */
    const ENTITY_PROCESSED         = 10160;

    /**
     * Throws if passed invalid package Id for installation.
     */
    const INVALID_PKG_ID           = 10170;

    /**
     * Throws if try to uninstall not installed module.
     */
    const NOT_INSTALLED            = 10180;

    /**
     * Throws if try to uninstall permanent module.
     */
    const IS_PERMANENT             = 10190;

    /**
     * Throws if import called on module with configuration that doesn't support
     * import.
     */
    const IMPORT_FORBIDDEN         = 10200;

    /**
     * Throws if try to uninstall base instance module.
     *
     * @todo Cancel this limitation in the future.
     */
    const UNINSTALL_FORBIDDEN      = 10210;

    /**
     * Throws if try to uninstall base instance module.
     *
     * @since 6.0.6
     */
    const HAS_DEPENDENCIES         = 10220;

    /**
     * Throws installing/uninstalling custom script detect a problem.
     *
     * Example:
     * <code>
     * // "install_after.php" / "install.php" /
     * // "uninstall_before.php" / "uninstall_after.php" / "uninstall.php" / "uninstall_all.php" context
     *
     * // 'key_from_error_message_locale_file' will be read from "errors.lng"
     * // located at configuration and same folder as this script.
     *
     * if(...){
     *     throw new AMI_Package_Exception(
     *         'key_from_error_message_locale_file',
     *         AMI_Package_Exception::CUSTOM_ERROR,
     *         null,
     *         // possible locale message aruments
     *         array('var1' => 'val1', ...)
     *     );
     * }
     * </code>
     */
    const CUSTOM_ERROR             = 20000;

    /**#@-*/
}

/**
 * Module captions updater transaction command.
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class AMI_Tx_Cmd_Package_Captions extends AMI_Tx_Cmd_Storage_ContentModifier{
    /**
     * Validates passed arguments.
     *
     * @return void
     * @see    AMI_Cmd::__construct()
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
        $content = '##--system info: module_owner="" module="" system="1"--##' . $this->eol;
        AMI_Registry::push('disable_error_mail', TRUE);
        trigger_error("Missing captions locales '" . $this->oArgs->target . "'", E_USER_WARNING);
        AMI_Registry::pop('disable_error_mail');
        return $content;
    }

    /**
     * Returns opening marker.
     *
     * @return string
     */
    protected function getOpeningMarker(){
        return '##-- [' . $this->oArgs->modId . '] { --##' . $this->eol;
    }

    /**
     * Returns closing marker.
     *
     * @return string
     */
    protected function getClosingMarker(){
        return '##-- } [' . $this->oArgs->modId . '] --##' . $this->eol;
    }
}

/**
 * Module captions installation transaction command.
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 * @resource   tx/cmd/pkg/captions/install <code>AMI::getResource('tx/cmd/pkg/captions/install')</code>
 */
class AMI_Tx_Cmd_Package_CaptionsInstaller extends AMI_Tx_Cmd_Package_Captions{
    /**
     * Validates passed arguments.
     *
     * @return void
     * @see    AMI_Cmd::__construct()
     */
    protected function validateArgs(){
        $this->validateObligatoryArgs(array('aCaptions', 'caption'));
        parent::validateArgs();
    }

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
     * Content modifier.
     *
     * @param  string &$content  Content
     * @param  string $opener    Opening marker
     * @param  string $closer    Closing marker
     * @return void
     */
    protected function modify(&$content, $opener, $closer){
        $content .= $opener;
        foreach($this->oArgs->aCaptions as $locale => $aLocaleData){
            foreach($aLocaleData as $captionModId => $aModData){
                $captionModId = $this->oArgs->modId . $captionModId;
                foreach($aModData as $caption => $string){
                    $string = htmlentities(strip_tags($string), ENT_COMPAT, 'UTF-8');
                    if(!preg_match($this->oArgs->caption, $caption)){
                        continue;
                    }
                    if(mb_strpos($caption, 'specblock') !== 0){
                        // Common case
                        $content .=
                            "%%{$captionModId}%{$locale}%%" . $this->eol .
                            $string . $this->eol;
                    }else{
                        // Specblock case
                        $pos = mb_strpos($caption, ':');
                        $tail = $captionModId;
                        if($pos !== FALSE){
                            $tail .= '_' . mb_substr($caption, $pos + 1);
                        }
                        if(preg_match('/_desc$/', $caption)){
                            $tail .=  '_desc';
                        }
                        $content .=
                            "%%spec_small_{$tail}%{$locale}%%" . $this->eol .
                            $string . $this->eol;
                    }
                }
            }
        }
        $content .= $closer;
    }
}

/**
 * Module captions uninstallation transaction command.
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 * @resource   tx/cmd/pkg/captions/uninstall <code>AMI::getResource('tx/cmd/pkg/captions/uninstall')</code>
 */
class AMI_Tx_Cmd_Package_CaptionsUninstaller extends AMI_Tx_Cmd_Package_Captions{
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
 * Event handlers installation transaction command.
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
 *         // Package Id
 *         'pkgId'    => $this->oArgs->pkgId,
 *         // Hypermodule
 *         'hypermod' => $this->oArgs->hypermod,
 *         // Configuration
 *         'config'   => $this->oArgs->config,
 *         // Instance Id
 *         'modId'    => $this->oArgs->modId,
 *         // Installation mode
 *         'mode'     => $this->oArgs->mode,
 *         // Source PHP-file path
 *         'handlerRegistrationSource' => $srcPath . 'commonEventHandlersRegistration.php',
 *         // Source PHP-file path
 *         'handlerDeclarationSource'  => $srcPath . 'commonEventHandlersDeclaration.php',
 *         // Target PHP-file to patch
 *         'target'   => $destPath . $file,
 *         // Storage driver
 *         'oStorage' => $oStorage
 *     )
 * );
 * $this->aTx['storage']->addCommand('pkg/handlers/install', $oArgs);
 *
 * // File 'commonEventHandlersRegistration.php' contains PHP-template:
 * <?php
 * ....
 * // {{}}  <- PHP-code template start marker
 * AMI_Event::addHandler('...', 'amiHandler', ...);
 * ...
 *
 * // File 'commonEventHandlersDeclaration.php' contains PHP-template:
 * <?php
 * ....
 * // {{}}  <- PHP-code template start marker
 * function amiHandler($name, array $aEvent, $handlerModId, $srcModId){
 *     ...
 * }
 * ...
 * </code>
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      6.0.6
 * @resource   tx/cmd/pkg/handlers/install <code>AMI::getResource('tx/cmd/pkg/handlers/install')</code>
 */
class AMI_Tx_Cmd_Package_HandlersInstaller extends AMI_Tx_Cmd_PHP_ContentIntsall{
    /**
     * Obligatory arguments.
     *
     * @var array
     * @see AMI_Tx_Cmd_PHP_ContentIntsall::validateArgs()
     */
    protected $aObligatoryArgs = array('handlerRegistrationSource', 'handlerDeclarationSource');

    /**
     * Handler registration opening marker
     *
     * @var string
     */
    protected $handlerRegistrationOpener;

    /**
     * Handler registration closing marker
     *
     * @var string
     */
    protected $handlerRegistrationCloser;

    /**
     * Handler declaration opening marker
     *
     * @var string
     */
    protected $handlerDeclarationOpener;

    /**
     * Handler declaration closing marker
     *
     * @var string
     */
    protected $handlerDeclarationCloser;

    /**
     * Validates passed arguments.
     *
     * @return void
     * @see    AMI_Cmd::__construct()
     */
    protected function validateArgs(){
        $this->validateObligatoryArgs(
            array(
                'pkgId',
                'hypermod',
                'config',
                'handlerRegistrationSource',
                'handlerDeclarationSource'
            )
        );

        parent::validateArgs();
    }

    /**
     * Initializes command.
     *
     * @return void
     */
    protected function init(){
        $this->handlerRegistrationOpener = '// DO NOT REMOVE THIS LINE! Registering handlers {' . $this->eol;
        $this->handlerRegistrationCloser = '// DO NOT REMOVE THIS LINE! } Registering handlers' . $this->eol;
        $this->handlerDeclarationOpener = '// DO NOT REMOVE THIS LINE! Declaration of handlers {' . $this->eol;
        $this->handlerDeclarationCloser = '// DO NOT REMOVE THIS LINE! } Declaration of handlers' . $this->eol;

        parent::init();
    }

    /**
     * Creates new if no content present.
     *
     * @return string
     */
    protected function createNewContent(){
        // Create declaration file if not exists
        $content =
            '<' . "?php" . $this->eol . $this->eol .
            $this->handlerRegistrationOpener . $this->eol .
            $this->handlerRegistrationCloser . $this->eol . $this->eol .
            $this->handlerDeclarationOpener . $this->eol .
            $this->handlerDeclarationCloser;

        AMI_Registry::push('disable_error_mail', TRUE);
        trigger_error("Missing PHP file at '" . $this->oArgs->target . "', creating new one", E_USER_NOTICE);
        AMI_Registry::pop('disable_error_mail');

        return $content;
    }

    /**
     * Runs command.
     *
     * Patches target file.
     *
     * @return void
     * @throws AMI_Tx_Exception  In case of missing obligatory argument.
     */
    protected function run(){
        $aPackages = AMI_PackageManager::getInstance()->getDownloadedPackages();
        if($aPackages[$this->oArgs->pkgId]['instCnt'] > 1){
            AMI_Tx::log(get_class($this) . '::run() There are other instances, handlers added already');
            return;
        }

        $file = $this->oArgs->target;
        $content = $this->oStorage->load($this->backuped ? $this->backupPath : $file, FALSE);
        if($content === FALSE){
            $content = $this->createNewContent();
        }
        $backupedModId = $this->oArgs->modId;
        $this->oArgs->overwrite('modId', $this->oArgs->hypermod . '_' . $this->oArgs->config);
        $opener = $this->getOpeningMarker();
        $closer = $this->getClosingMarker();
        $this->oArgs->overwrite('modId', $backupedModId);

        $backupedMode = $this->oArgs->mode;
        $this->oArgs->overwrite('mode', $this->oArgs->mode | AMI_iTx_Cmd::MODE_OVERWRITE);
        $handlerRegistrationContent = $content;
        if($this->checkMarkers($handlerRegistrationContent, $this->handlerDeclarationOpener, $this->handlerDeclarationCloser)){
            $start = mb_strpos(
                $handlerRegistrationContent,
                $this->handlerRegistrationOpener
            ) + mb_strlen($this->handlerRegistrationOpener);
            $end = mb_strpos($handlerRegistrationContent, $this->handlerRegistrationCloser);
            $handlerRegistrationContent =
                mb_substr($handlerRegistrationContent, $start, $end - $start);
            $newHandlerRegistrationContent = $handlerRegistrationContent;
            $this->oArgs->overwrite('mode', $backupedMode);
            if($this->checkMarkers($newHandlerRegistrationContent, $opener, $closer)){
                $this->oArgs->overwrite('source', $this->oArgs->handlerRegistrationSource);
                $this->modify($newHandlerRegistrationContent, $opener, $closer);
            }
        }

        $this->oArgs->overwrite('mode', $this->oArgs->mode | AMI_iTx_Cmd::MODE_OVERWRITE);
        $handlerDeclarationContent = $content;
        if($this->checkMarkers($handlerDeclarationContent, $this->handlerRegistrationOpener, $this->handlerRegistrationCloser)){
            $start = mb_strpos(
                $handlerDeclarationContent,
                $this->handlerDeclarationOpener
            ) + mb_strlen($this->handlerDeclarationOpener);
            $end = mb_strpos($handlerDeclarationContent, $this->handlerDeclarationCloser);
            $handlerDeclarationContent =
                mb_substr($handlerDeclarationContent, $start, $end - $start);
            $newHandlerDeclarationContent = $handlerDeclarationContent;
            $this->oArgs->overwrite('mode', $backupedMode);
            if($this->checkMarkers($newHandlerDeclarationContent, $opener, $closer)){
                $this->oArgs->overwrite('source', $this->oArgs->handlerDeclarationSource);
                $this->modify($newHandlerDeclarationContent, $opener, $closer);
            }
        }

        $this->oArgs->overwrite('mode', $backupedMode);

        if(
            $handlerRegistrationContent === $newHandlerRegistrationContent ||
            $handlerDeclarationContent === $newHandlerDeclarationContent
        ){
            throw new AMI_Tx_Exception(
                "Broken content marker at file '" . $file . "'",
                AMI_Tx_Exception::CMD_BROKEN_CONTENT_MARKER
            );
        }

        $content =
            str_replace(
                array(
                    $this->handlerRegistrationOpener . $handlerRegistrationContent . $this->handlerRegistrationCloser,
                    $this->handlerDeclarationOpener . $handlerDeclarationContent . $this->handlerDeclarationCloser
                ),
                array(
                    $this->handlerRegistrationOpener . $this->eol . trim($newHandlerRegistrationContent) . $this->eol . $this->eol . $this->handlerRegistrationCloser,
                    $this->handlerDeclarationOpener . $this->eol . trim($newHandlerDeclarationContent) . $this->eol . $this->eol . $this->handlerDeclarationCloser
                ),
                $content
            );

        $this->set($content);
    }
}

/**
 * Event handlers uninstallation transaction command.
 *
 * Example:
 * <code>
 * // "uninstall_before.php" / "uninstall_after.php" / "uninstall.php" / "uninstall_all.php" context
 *
 * $srcPath = dirname(__FILE__) . '/';
 * $targetPath = AMI_Registry::get('path/root') . '_local/';
 *
 * $oStorage = AMI::getResource('storage/fs');
 *
 * $file = 'common_functions.php';

 * $oArgs = new AMI_Tx_Cmd_Args(
 *     array(
 *         // Package Id
 *         'pkgId'    => $this->oArgs->pkgId,
 *         // Hypermodule
 *         'hypermod' => $this->oArgs->hypermod,
 *         // Configuration
 *         'config'   => $this->oArgs->config,
 *         // Instance Id
 *         'modId'    => $this->oArgs->modId,
 *         // Installation mode
 *         'mode'     => $this->oArgs->mode,
 *         // Target PHP-file to patch
 *         'target'   => $targetPath . $file,
 *         // Storage driver
 *         'oStorage' => $oStorage
 *     )
 * );
 * $this->aTx['storage']->addCommand('pkg/handlers/uninstall', $oArgs);
 * </code>
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      6.0.6
 * @resource   tx/cmd/pkg/handlers/uninstall <code>AMI::getResource('tx/cmd/pkg/handlers/uninstall')</code>
 */
class AMI_Tx_Cmd_Package_HandlersUninstaller extends AMI_Tx_Cmd_PHP_ContentUninstall{
    /**
     * Validates passed arguments.
     *
     * @return void
     * @see    AMI_Cmd::__construct()
     */
    protected function validateArgs(){
        $this->validateObligatoryArgs(
            array(
                'pkgId',
                'hypermod',
                'config'
            )
        );

        parent::validateArgs();
    }

    /**
     * Returns opening marker.
     *
     * @return string
     */
    protected function getOpeningMarker(){
        return
            '// Do not delete this comment! [' . $this->oArgs->hypermod . '_' . $this->oArgs->config . '] {' . $this->eol;
    }

    /**
     * Returns closing marker.
     *
     * @return string
     */
    protected function getClosingMarker(){
        return
            '// } Do not delete this comment! [' . $this->oArgs->hypermod . '_' . $this->oArgs->config . ']' . $this->eol;
    }

    /**
     * Runs command.
     *
     * Patches passed file.
     *
     * @return void
     * @throws AMI_Tx_Exception  In case of missing obligatory argument.
     */
    protected function run(){
        $aPackages = AMI_PackageManager::getInstance()->getDownloadedPackages();
        if($aPackages[$this->oArgs->pkgId]['instCnt'] > 1){
            AMI_Tx::log(get_class($this) . '::run() There are other instances, handlers will be deleted later');
            return;
        }

        parent::run();
    }

    /**
     * Content modifier.
     *
     * Wipes all configure/hypermodule content.
     *
     * @param  string &$content  Content
     * @param  string $opener    Opening marker
     * @param  string $closer    Closing marker
     * @return void
     */
    protected function modify(&$content, $opener, $closer){
        $content = preg_replace(
            '/.?' . preg_quote($opener, '/') . '.*?' . preg_quote($closer, '/') . '/s',
            '',
            $content
        );
    }
}

/**
 * Local declaration transaction command.
 *
 * Local declaration manipulator.
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class AMI_Tx_Cmd_Package_Declaration extends AMI_Tx_Cmd_Storage_ContentModifier{
    /**
     * Flag specifying to rebuild properties on next CMS start
     *
     * @var bool
     */
    private static $doRebuildProperties = TRUE;

    /**
     * Validates passed arguments.
     *
     * @return void
     * @see    AMI_Cmd::__construct()
     */
    protected function validateArgs(){
        $this->validateObligatoryArgs(array('modId', 'source'));
        parent::validateArgs();
    }

    /**
     * Creates new if no content present.
     *
     * @return string
     */
    protected function createNewContent(){
        // Create declaration file if not exists
        $content = '<' . "?php" . $this->eol . $this->eol;
        AMI_Registry::push('disable_error_mail', TRUE);
        trigger_error("Missing local declaration at '" . $this->oArgs->target . "'", E_USER_WARNING);
        AMI_Registry::pop('disable_error_mail');
        return $content;
    }

    /**
     * Returns opening marker.
     *
     * @return string
     */
    protected function getOpeningMarker(){
        return '// [' . $this->oArgs->modId . '] {' . $this->eol;
    }

    /**
     * Returns closing marker.
     *
     * @return string
     */
    protected function getClosingMarker(){
        return '// } [' . $this->oArgs->modId . ']' . $this->eol;
    }

    /**
     * Runs command.
     *
     * @return void
     * @throws AMI_Package_Exception  In case of imposibility to patch local declaretin file.
     * @todo   Sign code
     */
    protected function run(){
        parent::run();

        if(self::$doRebuildProperties){
            global $Core;

            // Specify to rebuild properties/create options on next script execution.
            self::$doRebuildProperties = FALSE;
            $Core->WriteOption('core', 'requre_rebuild_modules', 1);
        }
    }
}

/**
 * Local declaration creation transaction command.
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 * @resource   tx/cmd/pkg/declaration/install <code>AMI::getResource('tx/cmd/pkg/declaration/install')</code>
 */
class AMI_Tx_Cmd_Package_DeclarationInstaller extends AMI_Tx_Cmd_Package_Declaration{
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
     * @see    AMI_Cmd::__construct()
     */
    protected function validateArgs(){
        $this->validateObligatoryArgs(
            array('hypermod', 'config', 'pkgId', 'installId', 'section', 'classPrefix')
        );
        parent::validateArgs();
    }

    /**
     * Content modifier.
     *
     * Appends module declaration.
     *
     * @param  string &$content  Content
     * @param  string $opener    Opening marker
     * @param  string $closer    Closing marker
     * @return void
     * @throws AMI_Package_Exception  In case of missing obligatory argument.
     */
    protected function modify(&$content, $opener, $closer){
        $code = $this->oStorage->load($this->oArgs->source);
        if($code === FALSE){
            throw new AMI_Package_Exception(
                "Missing declaration template at '" . $this->oArgs->source . "'",
                AMI_Package_Exception::MISSING_DECLARATION_TPL
            );
        }
        $codeOpener = '// {{}}' . $this->eol;
        foreach(
            array('modId', 'section', 'hypermod', 'config', 'taborder', 'classPrefix', 'pkgId', 'installId') as
            $arg
        ){
            if(isset($this->oArgs->$arg)){
                $code = str_replace("##{$arg}##", $this->oArgs->$arg, $code);
            }
        }
        $code = mb_substr($code, mb_strpos($code, $codeOpener) + mb_strlen($codeOpener));
        $isDeclaration = preg_match('/declaration\.php$/', $this->oArgs->source);
        if($isDeclaration){
            AMI_Registry::set('AMI/Core/lastDeclaration', $code);
        }
        if(!$this->checkPHPSyntax($code)){
            throw new AMI_Tx_Exception(
                "Parse error found in '" . $this->oArgs->source . "', source:\n" . $code,
                AMI_Tx_Exception::CMD_INVALID_TPL_CONTENT
            );
        }
        $content .= $opener . $code . $closer;
        if($isDeclaration || preg_match('/properties\.php$/', $this->oArgs->source)){
            $executor = new AMI_PHPExecutor($code);
            $aScope = array(
                'oDeclarator' => AMI_ModDeclarator::getInstance()
            );
            $executor->run($aScope);
        }
    }
}

/**
 * Local declaration removing transaction command.
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 * @resource   tx/cmd/pkg/declaration/uninstall <code>AMI::getResource('tx/cmd/pkg/declaration/uninstall')</code>
 * @todo       Use code from declaration file, not from config
 */
class AMI_Tx_Cmd_Package_DeclarationUninstaller extends AMI_Tx_Cmd_Package_Declaration{
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
        // d::vd($this->oArgs->source);###
        // Module related content will be wiped out automatically
        if(preg_match('/declaration\.php$/', $this->oArgs->source)){
            // Used to call AMI_Ext::onModPostUninstall()
            $code = $this->oStorage->load($this->oArgs->source);
            if($code === FALSE){
                throw new AMI_Package_Exception(
                    "Missing declaration template at '" . $this->oArgs->source . "'",
                    AMI_Package_Exception::MISSING_DECLARATION_TPL
                );
            }
            $codeOpener = '// {{}}' . $this->eol;
            $this->oArgs->overwrite('section', 'modules');
            $this->oArgs->overwrite('taborder', 100500);
            $this->oArgs->overwrite('classPrefix', AMI::getClassPrefix($this->oArgs->modId));
            foreach(array('modId', 'hypermod', 'config', 'taborder', 'classPrefix') as $arg){
                $code = str_replace("##{$arg}##", $this->oArgs->$arg, $code);
            }
            $code = mb_substr($code, mb_strpos($code, $codeOpener) + mb_strlen($codeOpener));
            AMI_Registry::set('AMI/Core/lastDeclaration', $code);
        }
    }
}

/**
 * On post install transaction command.
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 * @resource   tx/cmd/pkg/onPostInstall <code>AMI::getResource('tx/cmd/pkg/onPostInstall')</code>
 */
class AMI_Tx_Cmd_Package_OnPostInstall extends AMI_Tx_Cmd{
    /**
     * Array of transactions
     *
     * @var array
     */
    protected $aTx;

    /**
     * Commits command.
     *
     * Called after all commands finished successfully.
     *
     * @return void
     */
    public function commit(){
        global $Core, $HOST_PATH;
        static $doUpdateRightsVersion = TRUE;

        $oDeclarator = AMI_ModDeclarator::getInstance();
        $oDeclarator->collectRegistration(TRUE, TRUE);
        $code = AMI_Registry::get('AMI/Core/lastDeclaration');
        eval($code);
        $aCollectedModIds = $oDeclarator->getCollected();

        // Mark module or hypermodule/configuration as tenant
        // of installation/uninstallation process to avoid repeated call.
        AMI_Package_InstanceManipulator::$aProcessedEntities[] = $this->processingEntity;####

        if(AMI_Registry::exists('AMI/Core/lastInstallAfter')){
            $path = AMI_Registry::get('AMI/Core/lastInstallAfter');
            if(file_exists($path)){
                // extract($this->oArgs->getAll());
                // unset($aTx);
                $this->aTx = $this->oArgs->aTx;
                require_once $path;
                unset($this->aTx);
            }
        }

        // Reset admin interface modules cache
        if($doUpdateRightsVersion && isset($Core) && is_object($Core) && ($Core instanceof CMS_Core)){
            $Core->UpdateRightsVersion();
            $doUpdateRightsVersion = FALSE;
        }

        $oDeclarator->collectRegistration(FALSE);
        $aExtensions = array();
        $oArgs = new AMI_Tx_Cmd_Args($this->oArgs->getAll());
        foreach($aCollectedModIds as $modId){
            // Setup properties
            $Core->setupHyperMod(
                $HOST_PATH . '_shared/code/hyper_modules/declaration/',
                array($oDeclarator->getSection($modId)),
                'properties',
                null,
                array($modId)
            );
            $aExtModIds = AMI_ModRules::getAvailableExts($modId, TRUE);
            foreach($aExtModIds as $extId){
                $extResId = $extId . '/module/controller/adm';
                if($oDeclarator->isRegistered($extId) && AMI::isResource($extResId)){
                    if(!isset($aExtensions[$extId])){
                        $aExtensions[$extId] = AMI::getResource($extResId, array($modId));
                    }
                    $aExtensions[$extId]->onModPostInstall($modId, $oArgs);
                }
            }
        }
    }

    /**
     * Rollbacks command.
     *
     * Called on transaction exception cought.
     *
     * @return void
     */
    public function rollback(){
    }

    /**
     * Validates passed arguments.
     *
     * @return void
     */
    protected function validateArgs(){
    }

    /**
     * Initializes command.
     *
     * @return void
     * @see    AMI_Tx_Cmd::__construct()
     */
    protected function init(){
    }

    /**
     * Runs command.
     *
     * @return void
     * @see    AMI_Tx_Cmd::__construct()
     */
    protected function run(){
    }
}

/**
 * On post install transaction command.
 *
 * @package    TxCommand
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 * @resource   tx/cmd/pkg/onPostUninstall <code>AMI::getResource('tx/cmd/pkg/onPostUninstall')</code>
 */
class AMI_Tx_Cmd_Package_OnPostUninstall extends AMI_Tx_Cmd{
    /**
     * Commits command.
     *
     * Called after all commands finished successfully.
     *
     * @return void
     */
    public function commit(){
        global $Core, $HOST_PATH;

        $oDeclarator = AMI_ModDeclarator::getInstance();
        $oDeclarator->collectRegistration(TRUE, TRUE);
        $code = AMI_Registry::get('AMI/Core/lastDeclaration');
        eval($code);
        $aCollectedModIds = $oDeclarator->getCollected();
        $oDeclarator->collectRegistration(FALSE);
        $aExtensions = array();
        $oArgs = new AMI_Tx_Cmd_Args($this->oArgs->getAll());

        foreach($aCollectedModIds as $modId){
            // Setup properties
            $Core->setupHyperMod(
                $HOST_PATH . '_shared/code/hyper_modules/declaration/',
                array($oDeclarator->getSection($modId)),
                'properties',
                null,
                array($modId)
            );
            $aExtModIds = AMI_ModRules::getAvailableExts($modId, TRUE);
            foreach($aExtModIds as $extId){
                if($extId == 'ext_category'){
                    continue;
                }
                $extResId = $extId . '/module/controller/adm';
                if($oDeclarator->isRegistered($extId) && AMI::isResource($extResId)){
                    if(!isset($aExtensions[$extId])){
                        $aExtensions[$extId] = AMI::getResource($extResId, array($modId));
                    }
                    call_user_func(array($aExtensions[$extId], $oArgs->extMethod), $modId, $oArgs);
                }
            }
        }
    }

    /**
     * Rollbacks command.
     *
     * Called on transaction exception cought.
     *
     * @return void
     */
    public function rollback(){
    }

    /**
     * Validates passed arguments.
     *
     * @return void
     */
    protected function validateArgs(){
    }

    /**
     * Initializes command.
     *
     * @return void
     * @see    AMI_Tx_Cmd::__construct()
     */
    protected function init(){
    }

    /**
     * Runs command.
     *
     * @return void
     * @see    AMI_Tx_Cmd::__construct()
     */
    protected function run(){
    }
}

/**
 * Package storage transaction.
 *
 * @package    TxService
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary?
 */
class AMI_Tx_Package_Storage extends AMI_Tx_Storage{
    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();

        AMI::addResourceMapping(
            array(
                'tx/cmd/pkg/captions/install'      => 'AMI_Tx_Cmd_Package_CaptionsInstaller',
                'tx/cmd/pkg/captions/uninstall'    => 'AMI_Tx_Cmd_Package_CaptionsUninstaller',
                'tx/cmd/pkg/handlers/install'      => 'AMI_Tx_Cmd_Package_HandlersInstaller',
                'tx/cmd/pkg/handlers/uninstall'    => 'AMI_Tx_Cmd_Package_HandlersUninstaller',
                'tx/cmd/pkg/declaration/install'   => 'AMI_Tx_Cmd_Package_DeclarationInstaller',
                'tx/cmd/pkg/declaration/uninstall' => 'AMI_Tx_Cmd_Package_DeclarationUninstaller',
                'tx/cmd/pkg/onPostInstall'         => 'AMI_Tx_Cmd_Package_OnPostInstall',
                'tx/cmd/pkg/onPostUninstall'       => 'AMI_Tx_Cmd_Package_OnPostUninstall'
            )
        );
        $this->aCmdResources += array(
            'pkg/captions/install'      => 'tx/cmd/pkg/captions/install',
            'pkg/captions/uninstall'    => 'tx/cmd/pkg/captions/uninstall',
            'pkg/handlers/install'      => 'tx/cmd/pkg/handlers/install',
            'pkg/handlers/uninstall'    => 'tx/cmd/pkg/handlers/uninstall',
            'pkg/declaration/install'   => 'tx/cmd/pkg/declaration/install',
            'pkg/declaration/uninstall' => 'tx/cmd/pkg/declaration/uninstall',
            'pkg/onPostInstall'         => 'tx/cmd/pkg/onPostInstall',
            'pkg/onPostUninstall'       => 'tx/cmd/pkg/onPostUninstall'
        );
    }
}

/**
 * Package utility.
 *
 * @package    TxService
 * @subpackage Controller
 * @static
 * @since      x.x.x
 * @amidev     Temporary?
 */
class AMI_Package{
    /**
     * Config path length
     *
     * @var string
     */
    private static $configPathLength;

    /**
     * Returns hypermodule / configuration meta object.
     *
     * @param  string $hypermod  Hypermodule
     * @param  string $config    Configuration
     * @param  bool   $reset     Flag specifying to reset autoload cache
     * @return AMI_Hyper_Meta | AMI_HyperConfig_Meta | null
     * @amidev Temporary
     */
    public static function getMeta($hypermod, $config = '', $reset = FALSE){
        $oMeta = null;
        $metaClassName =
            (
                $config !== ''
                ? AMI::getClassPrefix($hypermod) . '_' . AMI::getClassPrefix($config)
                : 'Hyper_' . AMI::getClassPrefix($hypermod)
            ) . '_Meta';
        AMI_Service::setAutoloadWarning(FALSE);
        if($reset){
            AMI_Service::resetAutoloadState($metaClassName);
        }
        if(class_exists($metaClassName)){
            $oMeta = new $metaClassName;
        }
        AMI_Service::setAutoloadWarning(TRUE);
        return $oMeta;
    }

    /**
     * Validates install/uninstall modes for hypermodule/configuration meta.
     *
     * @param  string $hypermod  Hypermodule
     * @param  string $config    Configuration
     * @param  array  &$aModes   Invalid modes in case of fail
     * @return bool
     * @todo   Complete structure validation.
     * @amidev Temporary
     */
    public static function validateMetaModes($hypermod, $config, array &$aModes){
        $oMeta = self::getMeta($hypermod, $config, TRUE);
        $result = (bool)$oMeta;
        if($result){
            $aModes = $oMeta->getAllowedModes();
            if(is_array($aModes) && $aModes){
                $result =
                    isset($aModes['install']) && is_array($aModes['install']) && $aModes['install'] &&
                    isset($aModes['uninstall']) && is_array($aModes['uninstall']) && $aModes['uninstall'];
            }else{
                $result = FALSE;
            }
        }
        return $result;
    }

    /**
     * Returns hypermodule / configuration meta object.
     *
     * @param  string $hypermod  Hypermodule
     * @param  string $config    Configuration
     * @return string | null
     */
    public static function getHyperConfigVersion($hypermod, $config = ''){
        $oMeta = self::getMeta($hypermod, $config);
        $res = null;
        if(is_object($oMeta)){
            $res = $oMeta->getVersion();
        }
        return $res;
    }

    /**
     * Compares two versions.
     *
     * @param  string $first   First version
     * @param  string $second  Second version
     * @return bool  Returns true if first version is bigger, than second
     */
    public static function compareVersions($first, $second){
        $aVersions = array(
            explode('.', (string)$first, 2),
            explode('.', (string)$second, 2)
        );
        if(!isset($aVersions[0][1])){
            $aVersions[0][1] = 0;
        }
        if(!isset($aVersions[1][1])){
            $aVersions[1][1] = 0;
        }
        $res = $aVersions[0][0] > $aVersions[1][0];
        if(!$res){
            if($aVersions[0][0] == $aVersions[1][0]){
                $res = $aVersions[0][1] > $aVersions[1][1];
            }
        }
        return $res;
    }

    /**
     * Returns array of available hypermodules.
     *
     * @param  bool $doLoadMeta  Flag specifying to load meta
     * @return array
     */
    public static function getAvailableHyper($doLoadMeta = FALSE){
        $side = AMI_Registry::get('side');
        $locale = AMI_Registry::get($side !== 'adm' ? 'lang_data' : 'lang');
        $aPaths =
            array(
                array(
                    'path'   => AMI_Registry::get('path/hyper_shared') . 'configs/',
                    'is_sys' => TRUE
                )
            );
        if(empty($GLOBALS['sys']['disable_user_scripts'])){
            // Use local hypermodules path if allowed
            $aPaths[] =
                array(
                    'path'   => AMI_Registry::get('path/hyper_local') . 'distrib/configs/',
                    'is_sys' => FALSE
                );
        }
        $aHypers = array();

        foreach($aPaths as $aPath){
            self::$configPathLength = mb_strlen($aPath['path']);

            $aHyperParts =
                array_filter(
                    array_map(
                        array('AMI_Package', 'cbCutConfigPath'),
                        AMI_Lib_FS::scan($aPath['path'], '', '*', AMI_Lib_FS::SCAN_DIRS, 1)
                    ),
                    array('AMI_Package', 'cbFilterHyper')
                );

            foreach($aHyperParts as $hypermod){
                $aHyper =
                    array(
                        'hypermod' => $hypermod,
                        'is_sys'   => $aPath['is_sys'],
                        // 'path'     => $aPath['path'].$hypermod
                    );
                if($doLoadMeta){
                    $aHyper['hypermod_caption'] = $aHyper['hypermod'];
                    $metaClassName = 'Hyper_' . AMI::getClassPrefix($hypermod) . '_Meta';
                    if(class_exists($metaClassName)){
                       /**
                        * @var AMI_Hyper_Meta
                        */
                        $oMeta = new $metaClassName;
                        $aHyper['hypermod_caption'] = $oMeta->getTitle($locale);
                        $aHyper['info'] = $oMeta->getInfo($locale);
                        $aHyper['meta'] = $oMeta->getData();
                    }
                }
                $aHypers[] = $aHyper;
            }
        }
        AMI_Lib_Array::sortMultiArray($aHypers, 'hypermod', SORT_STRING, SORT_ASC);
        return $aHypers;
    }

    /**
     * Returns array of available hypermodules configurations.
     *
     * @param  bool $doLoadMeta                    Flag specifying to load meta
     * @param  bool $skipInstalledSingleInstances  Flag specifying to skip installed single instances
     *                                             (affects if $doLoadMeta is TRUE)
     * @return array
     */
    public static function getAvailableConfigs(
        $doLoadMeta = FALSE,
        $skipInstalledSingleInstances = TRUE
    ){
        $locale = AMI_Registry::get('lang', 'en');
        $aPaths =
            array(
                array(
                    'path'   => AMI_Registry::get('path/hyper_shared') . 'configs/',
                    'is_sys' => TRUE
                )
            );
        if(empty($GLOBALS['sys']['disable_user_scripts'])){
            // Use local configurations path if allowed
            $aPaths[] =
                array(
                    'path'   => AMI_Registry::get('path/hyper_local') . 'distrib/configs/',
                    'is_sys' => FALSE
                );
        }
        $aConfigs = array();
        if($doLoadMeta){
            $oDeclarator = AMI_ModDeclarator::getInstance();
        }
        foreach($aPaths as $aPath){
            self::$configPathLength = mb_strlen($aPath['path']);
            $aHyperParts =
                array_filter(
                    array_map(
                        array('AMI_Package', 'cbCutConfigPath'),
                        AMI_Lib_FS::scan($aPath['path'], '', '*', AMI_Lib_FS::SCAN_DIRS, 2)
                    ),
                    array('AMI_Package', 'cbFilterConfigs')
                );
            foreach($aHyperParts as $part){
                list($hypermod, $config) = explode('/', $part, 2);
                $aConfig =
                    array(
                        'hypermod' => $hypermod,
                        'config'   => $config,
                        'is_sys'   => $aPath['is_sys']
                    );
                if($doLoadMeta){
                    $aConfig['hypermod_caption'] = $aConfig['hypermod'];
                    $aConfig['config_caption'] = $aConfig['config'];
                    foreach(
                        array(
                            'hypermod_caption' => 'Hyper_' . AMI::getClassPrefix($hypermod) . '_Meta',
                            'config_caption'   => AMI::getClassPrefix($hypermod) . '_' . AMI::getClassPrefix($config) . '_Meta',
                        ) as $key => $metaClassName
                    ){
                        AMI_Service::setAutoloadWarning(FALSE);
                        if(class_exists($metaClassName)){
                            /**
                             * @var AMI_Hyper_Meta
                             */
                            $oMeta = new $metaClassName;
                            if(
                                $skipInstalledSingleInstances &&
                                $oMeta->isSingleInstance() &&
                                sizeof($oDeclarator->getRegistered($hypermod, $config))
                            ){
                                // Skip already installed single instances
                                continue 2;
                            }
                            $aConfig[$key]   = $oMeta->getTitle($locale);
                            $aConfig['info'] = $oMeta->getInfo($locale);
                            $aConfig['meta'] = $oMeta->getData();
                            if($key === 'config_caption'){
                                $aConfig['captions']   = $oMeta->getCaptions();
                                $aConfig['instanceId'] = $oMeta->getInstanceId();
                            }
                        }else{
                            // No meta
                            continue 2;
                        }
                        AMI_Service::setAutoloadWarning(TRUE);
                    }
                }
                $aConfig['order'] =
                    (isset($aConfig['hypermod_caption']) ? $aConfig['hypermod_caption'] : $aConfig['hypermod']) . ' ' .
                    (isset($aConfig['config_caption']) ? $aConfig['config_caption'] : $aConfig['config']);
                $aConfigs[] = $aConfig;
            }
        }
        AMI_Lib_Array::sortMultiArray($aConfigs, 'order', SORT_STRING, SORT_ASC);

        return $aConfigs;
    }

    /**
     * Converts captions from meta to package/instance installation format.
     *
     * @param  array $aCaptions  Captions
     * @return array
     */
    public static function convertCaptions(array $aCaptions){
        $aResult = array();
        foreach($aCaptions as $locale => $aLocaleData){
            if(empty($aResult[$locale])){
                $aResult[$locale] = array();
            }
            foreach($aLocaleData as $modIdPostfix => $aData){
                if(empty($aResult[$locale][$modIdPostfix])){
                    $aResult[$locale][$modIdPostfix] = array();
                }
                foreach($aData as $caption => $aValue){
                    $aResult[$locale][$modIdPostfix][$caption] = $aValue[1];
                }
            }
        }
        return $aResult;
    }

    /**
     * Hyper list filter callback.
     *
     * @param  string $path  Dir name
     * @return bool
     * @see    AMI_Package::getResources()
     */
    private static function cbFilterHyper($path){
        return mb_strpos($path, '.svn') === FALSE; // && mb_strpos($path, '/') !== FALSE
    }

    /**
     * Configs list filter callback.
     *
     * @param  string $path  Dir name
     * @return bool
     * @see    AMI_Package::getResources()
     */
    private static function cbFilterConfigs($path){
        return mb_strpos($path, '.svn') === FALSE && mb_strpos($path, '/') !== FALSE;
    }

    /**
     * Configs paths cutter callback.
     *
     * @param  string $path  Dir name
     * @return bool
     * @see    AMI_Package::getResources()
     */
    private static function cbCutConfigPath($path){
        return mb_substr($path, self::$configPathLength);
    }
}

/**
 * Common package manipulator.
 *
 * @package    Package
 * @subpackage Controller
 * @since      6.0.2
 */
abstract class AMI_Package_Manipulator{
    /**
     * @var AMI_Package_Common
     */
    public $oPkgCommon;

    /**
     * Array containing class methods to run
     *
     * @var array
     */
    protected $aActions = array();

    /**
     * Array of transactions
     *
     * @var array
     */
    protected $aTx = array();

    /**
     * Name for transactions, must be filled in children
     *
     * @var string
     */
    protected $txName;

    /**
     * Used to store previous exception
     *
     * var AMI_Tx_Exception
     */
    protected $oException;

    /**
     * Runs transactions.
     *
     * @return void
     */
    public function run(){
        $this->initTx();
        foreach($this->aTx as $oTx){
            if(!$oTx->isStarted()){
                $oTx->start($this->txName);
            }
        }
        $this->doActions();
        $this->commit();
        AMI_Tx::log('Finished ' . $this->txName);
    }

    /**
     * Initializes transactions.
     *
     * @return void
     */
    protected function initTx(){
        $this->aTx = array(
            'db'      => new AMI_Tx_DB,
            'storage' => new AMI_Tx_Package_Storage
        );
    }

    /**
     * Validates initial data.
     *
     * @return void
     */
    protected abstract function validate();

    /**
     * Adds action.
     *
     * Adds action that will be called on AMI_Package_Manipulator::run().<br />
     *
     * Example:
     * <code>
     * class SomePackageManipulator extends AMI_Package_Manipulator{
     *     public function __construct(){
     *         $this->addAction('doSomethig');
     *     }
     *
     *     protected function doSomethig(){
     *     }
     * }
     *
     * $oPkgManipulator = new SomePackageManipulator;
     * try{
     *     $oPkgManipulator->run();
     * }catch(AMI_Tx_Exception $oException){
     *     // do something else
     * }
     * </code>
     *
     * @param  string $method  This class method name
     * @param  array  $aArgs   Method arguments
     * @return void
     * @see    AMI_Tx::run()
     */
    protected function addAction($method, array $aArgs = null){
        if(!is_callable(array($this, $method))){
            trigger_error('Missing method ' . get_class($this) . '::' . $method, E_USER_ERROR);
        }
        $this->aActions[] = array($method, $aArgs);
    }

    /**
     * Do actions.
     *
     * @return void
     */
    protected function doActions(){
        foreach($this->aActions as $aAction){
            list($method, $aArgs) = $aAction;
            $action = get_class($this) . "::{$method}()";
            AMI_Tx::log($action . ' {');
            $aEvent = array(
                'action' => $method,
                'aArgs'  => $aArgs,
                'oTx'    => $this
            );
            /**
             * Allows to modify or discard package manipulation action.
             *
             * Set $aEvent['_discard'] to TRUE to discard action.
             *
             * @event      on_pkg_action AMI_Event::MOD_ANY
             * @eventparam string action  Transaction action
             * @eventparam array  aArgs   Transaction arguments
             * @eventparam AMI_Tx oTx     Transaction object
             */
            AMI_Event::fire('on_pkg_action', $aEvent, AMI_Event::MOD_ANY);
            if(!empty($aEvent['_discard'])){
                AMI_Tx::log($action . ' DISCARDED', AMI_Service::LOG_WARN);
                continue;
            }
            try{
                if($aArgs){
                    call_user_func_array(array($this, $method), $aArgs);
                }else{
                    call_user_func(array($this, $method));
                }
            }catch(AMI_Tx_Exception $oException){
                // Store exception to throw from in case of rollback possible problems
                $this->oException = $oException;
                AMI_Tx::log($action . ' FAILED', AMI_Service::LOG_ERR);
                $this->rollback();
                throw $oException;
            }
            AMI_Tx::log($action . ' }');
        }
    }

    /**
     * Commits transactions.
     *
     * @return void
     */
    protected function commit(){
        // $index = 0;
        // try{
            foreach($this->aTx as $oTx){
                $oTx->commit();
                // $index++;
            }
        /*
        }
        catch(AMI_Tx_Exception $oException){
            $this->oException = $oException;
            $this->rollback($index);
            throw $oException;
        }
        */
    }

    /**
     * Rollbacks transactions.
     *
     * @param  int $index
     * @return void
     */
    protected function rollback($index = 0){
        $reverseIndex = sizeof($this->aTx) - 1;
        foreach(array_reverse($this->aTx) as $oTx){
            // if($index >= $reverseIndex){
                $oTx->rollback();
            /*
                $reverseIndex--;
            }else{
                // Rollbacked already
                break;
            }
            */
        }
    }

    /**
     * Converts mode to string representation for logging.
     *
     * @param  int $mode  Mode
     * @return string
     */
    protected function getModeAsString($mode){
        if($mode === AMI_iTx_Cmd::MODE_COMMON){
            return 'COMMON';
        }
        $aMode = array();
        foreach(
            array(
                AMI_iTx_Cmd::MODE_APPEND                  => 'APPEND',
                AMI_iTx_Cmd::MODE_OVERWRITE               => 'OVERWRITE',
                AMI_iTx_Cmd::MODE_SOFT                    => 'SOFT',
                AMI_iTx_Cmd::MODE_IGNORE_TARGET_EXISTENCE => 'IGNORE_TARGET_EXISTENCE',
                AMI_iTx_Cmd::MODE_IGNORE_DATA_EXISTENCE   => 'IGNORE_DATA_EXISTENCE',
                // AMI_iTx_Cmd::MODE_SKIP_ROLLBACK           => 'SKIP_ROLLBACK'
            ) as $mask => $text
        ){
            if($mode & $mask){
                $aMode[] = $text;
            }
        }
        return sizeof($aMode) ? implode(' | ', $aMode) : 'INVALID';
    }
}

/**
 * Abstract class for transaction manipulating CMS module.
 *
 * @package    Package
 * @subpackage Controller
 * @since      6.0.2
 */
class AMI_Package_InstanceManipulator extends AMI_Package_Manipulator{
    /**
     * Processed module Ids
     *
     * Stores installed/uninstalled module Ids or hypermodule/config for single instanced modules
     *
     * @var array
     */
    public static $aProcessedEntities = array();

    /**
     * Package Id
     *
     * @var string
     */
    protected $pkgId;

    /**
     * Package info
     *
     * @var array
     */
    protected $aPkgInfo;

    /**
     * Module Id
     *
     * @var string
     */
    protected $modId;

    /**
     * Module section
     *
     * @var string
     */
    protected $section;

    /**
     * Module tab order
     *
     * @var string
     */
    protected $tabOrder;

    /**
     * Flag specifying hypermodule/config is single instanced
     *
     * @var bool
     */
    protected $isSingleInstance;

    /**
     * Hypermodule/config for single instanced or module Id
     *
     * @var string
     */
    protected $processingEntity;

    /**
     * Config path length
     *
     * @var string
     */
    private static $configPathLength;

    /**
     * Initialization.
     *
     * @return void
     */
    protected function init(){
    }

    /**
     * Validates initial data.
     *
     * @return void
     */
    protected function validate(){
        if(defined('TEMPLATES_FROM_DISK')){
            throw new AMI_Package_Exception(
                'Module cannot be installed when teplates/locales are read from disk',
                AMI_Package_Exception::INVALID_TEMPLATE_MODE
            );
        }
        if(!AMI::validateModId($this->modId)){
            throw new AMI_Package_Exception(
                "Invalid module Id '" . $this->modId . "'",
                AMI_Package_Exception::INVALID_MOD_ID
            );
        }
        // Validate package Id
        if(!preg_match('/^[a-z](?:[a-z\d]|_[a-z])+(\.[a-z](?:[a-z\d]|_[a-z])+)?$/', $this->pkgId)){
            throw new AMI_Package_Exception(
                "Invalid package Id '" . $this->pkgId . "'",
                AMI_Package_Exception::INVALID_PKG_ID
            );
        }
        $oPkgManager = AMI_PackageManager::getInstance();
        $this->aPkgInfo = $oPkgManager->getManifest($this->pkgId);
        if(!$this->aPkgInfo){
            $aError = $oPkgManager->getError();
            d::vd($aError);###
            throw new AMI_Package_Exception(
                "Invalid package Id or broken package '" . $this->pkgId . "': [ " .
                $aError['errorCode'] . ' ] ' . $aError['errorMessage'],
                AMI_Package_Exception::INVALID_PKG_ID
            );
        }
    }

    /**
     * Returns resources by type.
     *
     * @param  string $type  Resource type
     * @return array
     */
    protected function getResources($type){
        return
            array_filter(
                AMI_Lib_FS::scan(
                    $this->oPkgCommon->configPath . 'configs/' . $this->hypermod . '/' . $this->config . '/' . $type,
                    '*',
                    '',
                    AMI_Lib_FS::SCAN_FILES,
                    0
                ),
                array($this, 'cbFilterResourceList')
            );
    }

    /**
     * Resource list filter callback.
     *
     * @param  string $file  Path to the file
     * @return bool
     * @see    AMI_Package::getResources()
     */
    protected function cbFilterResourceList($file){
        return !preg_match('~/\.noencode$~', $file);
    }

    /**
     * Returns config resource content.
     *
     * @param  string $resource  Path to resource
     * @return string
     */
    protected function getContentByResource($resource){
        $path = NULL;
        $content = NULL;
        if($resource !== FALSE){
            $content = file_get_contents($resource);
            if($content === FALSE){
                throw new AMI_Package_Exception(
                    "Cannot open hypermodule config resource at '" . $resource . "'",
                    AMI_Package_Exception::MISSING_CONFIG_RESOURCE
                );
            }
        }
        return $content;
    }

    /**
     * Returns TRUE if hypermodule/config specifies meta has according property.
     *
     * @return bool
     */
    protected function isSingleInstance(){
        foreach(array(
            'Hyper_' . AMI::getClassPrefix($this->hypermod),
            AMI::getClassPrefix($this->hypermod) . '_' . AMI::getClassPrefix($this->config),
        ) as $classPrefix){
            $metaClassName = $classPrefix . '_Meta';
            if(class_exists($metaClassName)){
                /**
                 * @var AMI_Hyper_Meta
                 */
                $oMeta = new $metaClassName;
                if($oMeta->isSingleInstance()){
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    /**
     * Transaction action.
     *
     * Checks processed entities.
     *
     * @return void
     * @throws AMI_Package_Exception  In case of problems.
     */
    protected function checkProcessed(){
        $this->isSingleInstance = $this->isSingleInstance();
        $this->processingEntity =
            $this->isSingleInstance
            ? $this->hypermod . '/' . $this->config : $this->modId;
        if(in_array($this->processingEntity, self::$aProcessedEntities)){
            throw new AMI_Package_Exception(
                "Entity '" . $this->processingEntity . "' is already processed",
                AMI_Package_Exception::ENTITY_PROCESSED
            );
        }
    }
}

/**
 * Transaction installing CMS module.
 *
 * @package    Package
 * @subpackage Controller
 * @since      x.x.x
 * @todo       Decide about resource
 * @amidev     Temporary?
 */
final class AMI_ModInstall extends AMI_Package_InstanceManipulator{
    /**
     * Transaction type for audit
     *
     * @var string
     */
    protected $transactionType = 'install';

    /**
     * Audit status for successfull transaction
     *
     * @var string
     */
    protected $auditSuccessStatus = 'added';

    /**
     * Package install Id
     *
     * @var int
     */
    protected $installId;

    /**
     * Module captions
     *
     * @var array
     */
    protected $aCaptions;

    /**
     * List of module postfixes
     *
     * @var array
     */
    protected $aModPostfix = array();

    /**
     * Constructor.
     *
     * @param  array  $aTx        Array of transaction
     * @param  string $section    Admin section
     * @param  int    $taborder   Module tab order
     * @param  string $hypermod   Hypermodule
     * @param  string $config     Config
     * @param  string $modId      Module Id, if not started with 'inst_' will be prefixed
     * @param  string $pkgId      Package Id
     * @param  int    $installId  Package install Id
     * @param  array  $aCaptions  Module captions
     * @param  int    $mode       Flags specifying installation mode, AMI_iTx_Cmd::MODE_*
     */
    public function __construct(
        array $aTx, $section, $taborder, $hypermod, $config, $modId, $pkgId, $installId,
        array $aCaptions, $mode = AMI_iTx_Cmd::MODE_COMMON
    ){
        $this->aTx = $aTx;
        $modId = (string)$modId;
        // Force prepend 'inst_' prefix if none
        if(mb_strpos($modId, 'inst_') !== 0){
            $modId = 'inst_' . $modId;
            /*
            foreach(array_keys($aCaptions) as $locale){
                $aModIds = array_keys($aCaptions[$locale]);
                foreach($aModIds as $mid){
                    $aCaptions[$locale]['inst_' . $mid] = $aCaptions[$locale][$mid];
                    unset($aCaptions[$locale][$mid]);
                }
                unset($aModIds, $locale, $mid);
            }
            */
        }
        $this->section   = (string)$section;
        $this->tabOrder  = (int)$taborder;
        $this->hypermod  = (string)$hypermod;
        $this->config    = (string)$config;
        $this->modId     = $modId;
        $this->pkgId     = $pkgId;
        $this->installId = (int)$installId;
        $this->aCaptions = $aCaptions;
        $this->mode      = (int)$mode;

        if($this->tabOrder <= 0){
            // Setup automatic taborder
            global $Core;

            $aModIds = $Core->GetInstalledModuleNames($this->section);
            $maxTabOrder = 0;
            foreach($aModIds as $modId){
                if($Core->issetModProperty($modId, 'taborder')){
                    $maxTabOrder = max($maxTabOrder, (int)$Core->getModProperty($modId, 'taborder'));
                }
            }
            $this->tabOrder = $maxTabOrder + 100;
        }

        $this->txName =
            "Installing instance '" . $this->modId . "' from package '" . $this->pkgId . "', " .
            "section = '" . $this->section . "', " .
            "taborder = " . $this->tabOrder . ", " .
            "hypermod = '" . $this->hypermod . "', " .
            "config = '" . $this->config . "', " .
            "mode = " . $this->getModeAsString($this->mode);
        AMI_Tx::log($this->txName);

        $this->init();

        $this->validate();
        $this->oPkgCommon = new AMI_Package_Common($this->hypermod, $this->config, $this->pkgId);
        $this->addAction('checkProcessed');
        $this->addAction('validateInstance');
        $this->addAction('onInstall');
        $this->addAction('installDB');
        $this->addAction('installLocalCode');
        $this->addAction('installTplResource');
        $this->addAction('installTplResource', array(TRUE));
        $this->addAction('installTplResource', array(FALSE, TRUE));
        $this->addAction('installTplResource', array(TRUE, TRUE));
        $this->addAction('installJS');
        $this->addAction('installIcons');
        $this->addAction('installCaptions');
        $this->addAction('installRulesCaptions');
        $this->addAction('createDeclaration');
        $this->addAction('finish');
        $this->addAction('onPostInstall');
    }

    /**
     * Returns module Id.
     *
     * @return string
     */
    public function getModId(){
        return $this->modId;
    }

    /**
     * Initializes transactions.
     *
     * @return void
     */
    protected function initTx(){
    }

    public function commit(){
    }

    /**
     * Validates initial data.
     *
     * @return void
     * @throws AMI_Package_Exception  In case of problems.
     */
    protected function validate(){
        global $Core;

        // Check for reserved module Id or
        // module Id dependent on hypermodule/configuration
        $aConfigs = AMI_Package::getAvailableConfigs(TRUE);
        foreach($aConfigs as $aConfig){
            if(empty($aConfig['info'])){
                continue;
            }
            if(!is_null($aConfig['instanceId'])){
                if($aConfig['instanceId'] === $this->modId){
                    if($aConfig['hypermod'] !== $this->hypermod || $aConfig['config'] !== $this->config){
                        throw new AMI_Package_Exception(
                            "'" . $this->modId . "' is reserved for '" . $this->hypermod . '/' . $this->config . "' configuration",
                            AMI_Package_Exception::RESERVED_MOD_ID
                        );
                    }
                }elseif($aConfig['hypermod'] === $this->hypermod && $aConfig['config'] === $this->config){
                    $this->modId = $aConfig['instanceId'];
                }
            }
        }

        parent::validate();

        if($Core->isInstalled($this->modId)){
            throw new AMI_Package_Exception(
                "Module '" . $this->modId . "' is already installed",
                AMI_Package_Exception::ALREADY_INSTALLED
            );
        }

        // Check for plugins specified plgin Id ~ module Id
        if($Core->IsOwnerInstalled('plugins')){
            for($i = 1; $i <= $GLOBALS['PLUGINS_COUNT']; $i++){
                $pluginModId = CMS_InstallablePlugin::GetPluginName($i);
                if(
                    $Core->issetModOption($pluginModId, 'installed') &&
                    $Core->issetModOption($pluginModId, 'api_version') &&
                    $Core->GetModOption($pluginModId, 'installed') &&
                    $Core->GetModOption($pluginModId, 'api_version') >= 6 &&
                    $Core->GetModOption($pluginModId, 'plugin_id') === $this->modId
                ){
                    $aPluginCaption = $Core->GetModOption($pluginModId, 'admin_menu_caption');
                    throw new AMI_Package_Exception(
                        "Module Id '" . $this->modId . "' is used by '" . $aPluginCaption[AMI_Registry::get('lang')] . "' plugin",
                        AMI_Package_Exception::ALREADY_INSTALLED
                    );
                }
            }
        }

        // Validate meta
        $error = TRUE;
        if(AMI::validateModId($this->hypermod) && AMI::validateModId($this->config)){
            $metaClassName = AMI::getClassPrefix($this->hypermod) . '_' . AMI::getClassPrefix($this->config) . '_Meta';
            AMI_Service::setAutoloadWarning(FALSE);
            if(class_exists($metaClassName)){
                $error = FALSE;
            }
            AMI_Service::setAutoloadWarning(TRUE);
        }
        if($error){
            // No way to install without configuration meta file
            throw new AMI_Package_Exception(
                'Invalid hypermodule or config',
                AMI_Package_Exception::INVALID_HYPER_MOD_CONFIG
            );
        }

        // Validate obligatory captions according to meta
        /**
         * @var AMI_HyperConfig_Meta
         */
        $oMeta = new $metaClassName;
        $aCaptions = $oMeta->getCaptions();
        foreach($aCaptions as $locale => $aLocaleData){
            if(!isset($this->aCaptions[$locale]) || !is_array($this->aCaptions[$locale])){
                throw new AMI_Package_Exception(
                    "Missing caption data for locale '" . $locale . "' at " . $metaClassName,
                    AMI_Package_Exception::INVALID_CAPTIONS_DATA
                );
            }
            foreach($aLocaleData as $modPostfix => $aModData){
                $this->aModPostfix[] = $modPostfix;
                if(!isset($this->aCaptions[$locale][$modPostfix]) || !is_array($this->aCaptions[$locale][$modPostfix])){
                    throw new AMI_Package_Exception(
                        "Missing module '" . $this->modId . $modPostfix . "' caption data for locale '" . $locale . "'",
                        AMI_Package_Exception::INVALID_CAPTIONS_DATA
                    );
                }
                foreach($aModData as $caption => $aCaptionData){
                    if(
                        $aCaptionData[2] === AMI_HyperConfig_Meta::CAPTION_OBLIGATORY &&
                        (!isset($this->aCaptions[$locale][$modPostfix][$caption]) ||
                        trim($this->aCaptions[$locale][$modPostfix][$caption]) === '')
                    ){
                        throw new AMI_Package_Exception(
                            "Missing obligatory '" . $caption . "' caption fot module '" . $this->modId . $modPostfix . "' for locale '" . $locale . "'",
                            AMI_Package_Exception::INVALID_CAPTIONS_DATA
                        );
                    }
                }
            }
            $this->aModPostfix = array_unique($this->aModPostfix);
        }
    }

    /**#@+
     * Transaction action.
     */

    /**
     * Validates multiple instances possibility.
     *
     * @return void
     */
    protected function validateInstance(){
        if(
            $this->isSingleInstance &&
            sizeof(AMI_ModDeclarator::getInstance()->getRegistered($this->hypermod, $this->config))
        ){
            throw new AMI_Package_Exception(
                "Configuration '" . $this->hypermod . '/' . $this->config . "' cannot have more than 1 instance",
                AMI_Package_Exception::INSTANCE_LIMIT
            );
        }
    }

    /**
     * Executes PHP-code before installation.
     *
     * @return void
     */
    protected function onInstall(){
        AMI_Registry::set('ami_allow_model_save', TRUE);
        // Call pre-install hook
        $path = $this->oPkgCommon->configPath . 'configs/' . $this->hypermod . '/' . $this->config . '/install_before.php';
        if(file_exists($path)){
            require $path;
        }
    }

    /**
     * Creates module db tables.
     *
     * Files from configuration folder, i. e.
     * "_local/modules/distrib/configs/ami_clean/ami_sample/db"
     * will be used.
     *
     * @return void
     */
    protected function installDB(){
        foreach($this->getResources('db') as $resource){
            $sql = $this->getContentByResource($resource);
            /**
             * @var AMI_iDB
             */
            $oDB = AMI::getSingleton('db');
            $sql = str_replace('##modId##', $this->modId, $sql);
            if(preg_match('/^\s*CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`([^`]+)`/si', $sql, $aMatches)){
                $table = $aMatches[1];
                $this->aTx['db']->addCommand(
                    'table/create',
                    new AMI_Tx_Cmd_Args(
                        array(
                            'mode'   => $this->mode,
                            'source' => $sql,
                            'target' => $table
                        )
                    )
                );
            }else{
                trigger_error(
                    "'CREATE TABLE' sentence is absent in '" . $resource . "'",
                    E_USER_WARNING
                );
            }
        }
    }

    /**
     * Creates local module code files.
     *
     * Code templates from configuration folder, i. e.
     * "_local/modules/distrib/configs/ami_clean/ami_sample/code"
     * will be used.
     *
     * @return void
     * @todo   Make public method to use from module manager
     */
    protected function installLocalCode(){
        if(!$this->oPkgCommon->isLocalConfigUsed){
            return;
        }

        $oStorage = AMI::getResource('storage/fs');
        // Copy hyper/config PHP-code from distributive to the "_local/modules/code" {

        /*
        $aCode = array_merge(
            AMI_Lib_FS::scan("{$this->oInstall->configPath}configs/{$this->hypermod}", '*.php', '', AMI_Lib_FS::SCAN_FILES, 0),
            AMI_Lib_FS::scan("{$this->oInstall->configPath}configs/{$this->hypermod}/{$this->config}", '*.php', '', AMI_Lib_FS::SCAN_FILES, 0)
        );
        foreach($aCode as $resource){
            $code = file_get_contents($resource);
            $code = str_replace('##modId##', $this->modId, $code);
            new
                AMI_Tx_Cmd_Storage_ContentSetter(
                    $this->aTx['db'],
                    new AMI_Tx_Cmd_Args(
                        array(
                            'mode'     => $this->mode,
                            'content'  => $code,
                            'target'   => "{$this->oInstall->localModPath}code/" . basename($resource),
                            'oStorage' => $oStorage
                        )
                    )
                );
        }
        */

        // } Copy hyper/config PHP-code from distributive to the "_local/modules/code"
        // Copy instance PHP-code from distributive to the "_local/modules/code" {

        foreach($this->getResources('code') as $resource){
            $code = $this->getContentByResource($resource);
            $class = AMI::getClassPrefix($this->modId);
            $name = str_replace('--modId--', $class, basename($resource));
            $code = str_replace('##modId##', $class, $code);
            $this->aTx['storage']->addCommand(
                'storage/set',
                new AMI_Tx_Cmd_Args(
                    array(
                        'mode'     => $this->mode,
                        'content'  => $code,
                        'target'   => $this->oPkgCommon->localModPath . 'code/' . $name,
                        'oStorage' => $oStorage
                    )
                )
            );
        }

        // } Copy instance PHP-code from distributive to the "_local/modules/code"
    }

    /**
     * Creates local module templates/locales.
     *
     * Files from configuration folder, i. e.
     * "_local/modules/distrib/configs/ami_clean/ami_sample/templates" (admin interface),
     * "_local/modules/distrib/configs/ami_clean/ami_sample/templates_frn" (front interface)
     * will be used.
     *
     * @param  bool $processLocales  Flag specifying to process templates or locales
     * @param  bool $processFrn      Flag specifying to process front resources
     * @return void
     */
    protected function installTplResource($processLocales = FALSE, $processFrn = FALSE){
        $oStorage = AMI::getResource('storage/tpl');
        if($processFrn){
            $type = $processLocales ? 'locales_frn' : 'templates_frn';
            $path = $processLocales ? AMI_iTemplate::LNG_MOD_PATH . '/' : AMI_iTemplate::TPL_MOD_PATH . '/';
        }else{
            $type = $processLocales ? 'locales' : 'templates';
            $path = $processLocales ? AMI_iTemplate::LOCAL_LNG_MOD_PATH . '/' : AMI_iTemplate::LOCAL_TPL_MOD_PATH . '/';
        }
        foreach($this->getResources($type) as $resource){
            $content = $this->getContentByResource($resource);
            $content = str_replace('##section##', $this->section, $content);
            $content = str_replace('##modId##', $this->modId, $content);
            $this->aTx['storage']->addCommand(
                'storage/set',
                new AMI_Tx_Cmd_Args(
                    array(
                        'mode'      => $this->mode,
                        'content'   => $content,
                        'asDefault' => TRUE,
                        'target'    => $path . str_replace('--modId--', $this->modId, basename($resource)),
                        'oStorage'  => $oStorage
                    )
                )
            );
        }
    }

    /**
     * Copy module JS.
     *
     * Files from configuration folder, i. e.
     * "_local/modules/distrib/configs/ami_clean/ami_sample/js"
     * will be used.
     *
     * @return void
     */
    protected function installJS(){
        $path = $this->oPkgCommon->localPath . '_admin/_js/' . $this->modId;
        if(!file_exists($path)){
            mkdir($path);
        }
        $oStorage = AMI::getResource('storage/fs');
        foreach($this->getResources('js') as $resource){
            $target = $this->oPkgCommon->localPath . '_admin/_js/' . $this->modId . '/' . basename($resource);
            $this->aTx['storage']->addCommand(
                'storage/copy',
                new AMI_Tx_Cmd_Args(
                    array(
                        'mode'     => $this->mode,
                        'source'   => $resource,
                        'target'   => $target,
                        'oStorage' => $oStorage
                    )
                )
            );
        }
    }


    /**
     * Creates local module icons (for start page and specblocks in site manager) and copy other icons.
     *
     * Files from configuration folder, i. e.
     * "_local/modules/distrib/configs/ami_clean/ami_sample/icons"
     * will be used.
     *
     * @return void
     */
    protected function installIcons(){
        if(!$this->oPkgCommon->isLocalConfigUsed){
            return;
        }
        $oStorage = AMI::getResource('storage/fs');
        $aIcons = $this->getResources('icons');
        $aSourceIcons = $aIcons;
        $aRegExpXName =
            array(
                '/\/' . preg_quote('--modId--.gif', '/') . '$/'              => '--modId--.gif',
                '/\/' . preg_quote('--modId--_specblock_en.gif', '/') . '$/' => '--modId--_specblock_en.gif',
                '/\/' . preg_quote('--modId--_specblock_ru.gif', '/') . '$/' => '--modId--_specblock_ru.gif'
            );
        foreach($aRegExpXName as $regExp => $defaultIcon){
            foreach($aSourceIcons as $path){
                if(preg_match($regExp, $path)){
                    continue 2;
                }
            }
            $aIcons[] = $this->oPkgCommon->hyperPath . 'default_icons/' . $defaultIcon;
        }

        $path = $this->oPkgCommon->localPath . '_admin/images/' . $this->modId;
        if(!$oStorage->exists($path)){
            $oStorage->mkdir($path);
        }
        foreach($aIcons as $resource){
            if(preg_match('/\-\-modId\-\-/', basename($resource))){
                $target =
                    $this->oPkgCommon->localPath . '_admin/images/icons/' .
                    str_replace('--modId--', $this->modId, basename($resource));
            }else{
                $target =
                    $this->oPkgCommon->localPath . '_admin/images/' .
                    $this->modId . '/' . basename($resource);
            }
            // d::vd("{$resource} -> {$target}");###
            $this->aTx['storage']->addCommand(
                'storage/copy',
                new AMI_Tx_Cmd_Args(
                    array(
                        'mode'     => $this->mode,
                        'source'   => $resource,
                        'target'   => $target,
                        'oStorage' => $oStorage
                    )
                )
            );
        }
    }

    /**
     * Installs module captions.
     *
     * @return void
     */
    protected function installCaptions(){
        $path = AMI_iTemplate::LOCAL_LNG_MOD_PATH . '/';
        $oStorage = AMI::getResource('storage/tpl');
        foreach(array('en', 'ru') as $locale){
            if(
                !isset($this->aCaptions[$locale]['']['menu_group']) ||
                trim($this->aCaptions[$locale]['']['menu_group']) === ''
            ){
                $this->aCaptions[$locale]['']['menu_group'] =
                    $this->aCaptions[$locale]['']['menu'];
            }
        }
        foreach(
            array(
                // Admin interface module page header
                '/^header$/'           => '_headers.lng',
                // Interface menu group header
                '/^menu_group$/'        => '_menu_group.lng',
                // Interface menu header
                '/^menu$/'             => '_menu.lng',
                // Admin interface start page module description
                '/^description$/'      => '_start.lng',
                // Specblocks captions
                '/^specblock(_desc)?(\:.+)?$/' => '_specblocks.lng'
            ) as $caption => $template
        ){
            $this->aTx['storage']->addCommand(
                'pkg/captions/install',
                new AMI_Tx_Cmd_Args(
                    array(
                        'mode'      => $this->mode,
                        'modId'     => $this->modId,
                        'target'    => $path . $template,
                        'oStorage'  => $oStorage,
                        'aCaptions' => $this->aCaptions,
                        'caption'   => $caption
                    )
                )
            );
        }
    }

    /**
     * Creates local rules captions.
     *
     * Files from configuration folder, i. e.
     * "_local/modules/distrib/configs/ami_clean/ami_sample/rules"
     * will be used.
     *
     * @return void
     */
    protected function installRulesCaptions(){
        $oStorage = AMI::getResource('storage/tpl');
        foreach($this->getResources('rules') as $resource){
            $content = $this->getContentByResource($resource);
            $content = str_replace(
                array('##modId##', '##section##'),
                array($this->modId, $this->section),
                $content
            );
            $this->aTx['storage']->addCommand(
                'storage/set',
                new AMI_Tx_Cmd_Args(
                    array(
                        'mode'      => $this->mode,
                        'content'   => $content,
                        'asDefault' => TRUE,
                        'target'    =>
                            '_local/_admin/templates/lang/options/' .
                            str_replace('--modId--', $this->modId, basename($resource)),
                        'oStorage'  => $oStorage
                    )
                )
            );
        }
    }

    /**
     * Create local declaration.
     *
     * Files in "_local/modules/declaration" will be patched by installing
     * module declaration, properties, options and rules.<br />
     * Code templates from configuration folder, i. e.
     * "_local/modules/distrib/configs/ami_clean/ami_sample/declaration"
     * will be used.
     *
     * @return void
     * @throws AMI_Tx_Exception  In case of problems.
     */
    protected function createDeclaration(){
        $aDeclares = $this->getResources('declaration');
        if(!sizeof($aDeclares)){
            throw new AMI_Package_Exception(
                'Missing declaration template',
                AMI_Package_Exception::MISSING_DECLARATION_TPL
            );
        }
        $aResources = $this->getResources('declaration');
        usort($aResources, array($this, 'cbSortDeclaration'));
        $oStorage = AMI::getResource('storage/fs');
        foreach($aResources as $resource){
            $localName = basename($resource);
            if($localName === 'declaration.php'){
                $localName = 'declares.php';
            }
            $this->aTx['storage']->addCommand(
                'pkg/declaration/install',
                new AMI_Tx_Cmd_Args(
                    array(
                        'mode'        => $this->mode,
                        'modId'       => $this->modId,
                        'section'     => $this->section,
                        'taborder'    => $this->tabOrder,
                        'hypermod'    => $this->hypermod,
                        'config'      => $this->config,
                        'classPrefix' => AMI::getClassPrefix($this->modId),
                        'pkgId'       => $this->pkgId,
                        'installId'   => $this->installId,
                        'source'      => $resource,
                        'target'      => $this->oPkgCommon->localModPath . "declaration/{$localName}",
                        'oStorage'    => $oStorage
                    )
                )
            );
        }
    }

    /**
     * Finishes installation.
     *
     * @return void
     */
    protected function finish(){
        if(AMI_ModDeclarator::getInstance()->isRegistered('sys_groups')){
            $this->updateSysRights();
        }

        global $Core;
        static $doUpdateRightsVersion = TRUE;

        // Mark module or hypermodule/configuration as tenant
        // of installation/uninstallation process to avoid repeated call.
        self::$aProcessedEntities[] = $this->processingEntity;

        // Call post-install hook
        AMI_Registry::set(
            'AMI/Core/lastInstallAfter',
            $this->oPkgCommon->configPath . 'configs/' . $this->hypermod . '/' . $this->config . '/' . 'install_after.php'
        );
        /*
        $path = $this->oInstall->configPath . 'configs/' . $this->hypermod . '/' . $this->config . '/' . 'install_after.php';
        if(file_exists($path)){
            require_once $path;
        }

        if(AMI_ModDeclarator::getInstance()->isRegistered('sys_groups')){
            $this->updateSysRights();
        }

        // Reset admin interface modules cache
        if($doUpdateRightsVersion && isset($Core) && is_object($Core) && ($Core instanceof CMS_Core)){
            $Core->UpdateRightsVersion();
            $doUpdateRightsVersion = FALSE;
        }
        */
    }

    /**
     * Run supported extensions on instance post-install.
     *
     * @return void
     */
    protected function onPostInstall(){
        $this->aTx['storage']->addCommand(
            'pkg/onPostInstall',
            new AMI_Tx_Cmd_Args(get_object_vars($this))
        );
    }

    /**#@-*/

    /**
     * Callback sorting local declaration resources.
     *
     * @param  string $first
     * @param  string $second
     * @return bool
     * @see    AMI_ModInstall::createDeclaration()
     */
    protected function cbSortDeclaration($first, $second){
        return preg_match('/declaration\.php$/', $second);
    }

    /**
     * Update sys rights if needed.
     *
     * @return void
     */
    protected function updateSysRights(){
        $oRequest = AMI::getSingleton('env/request');
        $oDB = AMI::getSingleton('db');
        $aActions = array('none', 'edit', 'rsrtme');
        if($oRequest->get('add_to_groups', FALSE)){
            $sql = "SELECT group_mask FROM cms_sys_groups WHERE login=1";
            $oRecordset = $oDB->select(DB_Query::getSnippet($sql));
            $mask = 0;
            if($oRecordset){
                foreach($oRecordset as $aRecord){
                    $mask = $mask | $aRecord['group_mask'];
                }
            }
            if($mask){
                foreach($this->aModPostfix as $postfix){
                    foreach($aActions as $action){
                        $delSQL = "DELETE FROM cms_sys_actions_rights WHERE module_name=%s AND action_name=%s";
                        $oDB->query(DB_Query::getSnippet($delSQL)->q($this->modId . $postfix)->q($action));
                        $insSQL = "INSERT INTO cms_sys_actions_rights SET module_name=%s, action_name=%s, group_mask=%s";
                        $oDB->query(DB_Query::getSnippet($insSQL)->q($this->modId . $postfix)->q($action)->q($mask));
                    }
                }
                $sql = "UPDATE cms_sys_groups SET modules=modules+1 WHERE login=1";
                $oDB->query(DB_Query::getSnippet($sql));
            }
        }
    }
}

/**
 * Installation/uninstallation common functionality and data.
 *
 * @package       TxService
 * @subpackage    Controller
 * @property-read string $hyperPath          Filesystem path to shared hyper resources
 * @property-read string $localPath          Filesystem path to local resources
 * @property-read string $localModPath       Filesystem path to local module resources
 * @property-read string $configPath         Filesystem path to config depending on hypermodule/config
 * @property-read bool   $isLocalConfigUsed  Flag specifying that local (custom) config is used
 * @property-read string $pkgId              Package Id
 * @property-read array  $aPkgInfo           Package info
 * @property-read bool   $installId          Package install Id
 * @property-read bool   $uninstallId        Package uninstall Id
 * @since         6.0.6
 */
final class AMI_Package_Common{
    /*
     * @see           AMI_ModInstall::__construct()
     * @see           AMI_ModUninstall::__construct()
     * @see           AMI_ModImport::__construct()
     * @see           AMI_PseudoPackage_Install::__construct()
     * @see           AMI_PseudoPackage_Uninstall::__construct()
     */
    const LOG_START   = 1;
    const LOG_FAILURE = 2;
    const LOG_FINISH  = 3;

    /**
     * Filesystem path to shared hyper resources
     *
     * @var string
     */
    protected $hyperPath;

    /**
     * Filesystem path to local resources
     *
     * @var string
     */
    protected $localPath;

    /**
     * Filesystem path to local module resources
     *
     * @var string
     */
    protected $localModPath;

    /**
     * Filesystem path to config depending on hypermodule/config
     *
     * @var string
     */
    protected $configPath;

    /**
     * Flag specifying that local (custom) config is used
     *
     * @var bool
     */
    protected $isLocalConfigUsed = FALSE;

    /**
     * Package Id
     *
     * @var string
     */
    protected $pkgId;

    /**
     * Package info
     *
     * @var array
     */
    protected $aPkgInfo;

    /**
     * Package install Id
     *
     * @var int
     */
    protected $installId;

    /**
     * Package uninstall Id
     *
     * @var int
     */
    protected $uninstallId;

    /**
     * Constructor.
     *
     * @param  string $hypermod   Hypermodule
     * @param  string $config     Configure
     * @param  string $pkgId      Package Id
     * @param  int    $installId  Install Id
     * @throws AMI_Package_Exception  In case of invalid hypermodule/configuration.
     * @amidev Temporary
     */
    public function __construct($hypermod, $config, $pkgId, $installId = 0){
        $this->hyperPath    = AMI_Registry::get('path/hyper_shared');
        $this->localModPath = AMI_Registry::get('path/hyper_local');
        $this->localPath    = $this->localModPath . '../';

        $this->hypermod  = (string)$hypermod;
        $this->config    = (string)$config;
        $this->pkgId     = (string)$pkgId;
        $this->aPkgInfo  = AMI_PackageManager::getInstance()->getManifest($this->pkgId);
        $this->installId = (int)$installId;

        if($this->hypermod === '' || $this->config === ''){
            return;###
            $this->hypermod = $this->aPkgInfo['install'][0]['hypermodule'];
            $this->config   = $this->aPkgInfo['install'][0]['configuration'];
        }

        if(AMI::validateModId($this->hypermod) && AMI::validateModId($this->config)){
            $aConfig =
                empty($GLOBALS['sys']['disable_user_scripts'])
                ? array($this->hyperPath, $this->localModPath . 'distrib/')
                : array($this->hyperPath);
            foreach($aConfig as $isLocalConfigUsed => $configPath){
                if(
                    is_dir("{$configPath}configs/{$this->hypermod}") &&
                    is_dir("{$configPath}configs/{$this->hypermod}/{$this->config}")
                ){
                    $this->configPath = $configPath;
                    $this->isLocalConfigUsed = (bool)$isLocalConfigUsed;
                    return;
                }
            }
        }
        throw new AMI_Package_Exception(
            "Unknown hypermodule or configuration '" . $this->hypermod . "/" . $this->config . "'",
            AMI_Package_Exception::INVALID_HYPER_MOD_CONFIG
        );
    }

    /**
     * Getter returning protected object properties.
     *
     * @param  string $name  Property name
     * @return mixed
     */
    public function __get($name){
        return $this->$name;
    }

    /**
     * Creates/updates record in mod manager history table.
     *
     * @param  string $type  Transaction type  'ininstall' / 'uninstall'
     * @param  int    $mode  AMI_ModCommon::LOG_START | AMI_ModCommon::LOG_FAILURE | AMI_ModCommon::LOG_FINISH
     * @return void
     * @amidev Temporary
     */
    public function log($type, $mode){
        $mode = (int)$mode;
        switch($mode){
            case self::LOG_START:
                $oItem = AMI::getResourceModel('mod_manager_history/table')->getItem();
                $header =
                    !empty($this->aPkgInfo['information']['en']) &&
                    !empty($this->aPkgInfo['information']['en']['title'])
                    ? $this->aPkgInfo['information']['en']['title']
                    : $this->pkgId;
                if($type === 'install'){
                    // Install
                    $oItem->setData(
                        array(
                            'id_pkg'   => $this->pkgId,
                            'action'   => $type,
                            'state'    => 'in progress',
                            'header'   => $header,
                            'manifest' => $this->aPkgInfo['manifest']
                        )
                    );
                    $oItem->save();
                    $this->installId = $oItem->getId();
                }else{
                    // Uninstall
                    $oItem->setData(
                        array(
                            'id_pkg'     => $this->pkgId,
                            'id_install' => $this->installId,
                            'action'     => $type,
                            'state'      => 'in progress',
                            'header'     => $header
                        )
                    );
                    $oItem->save();
                    $this->uninstallId = $oItem->getId();
                }
                break; // case self::LOG_START
            case self::LOG_FAILURE:
            case self::LOG_FINISH:
                if($this->installId){
                    $id = $type == 'install' ? $this->installId : $this->uninstallId;
                    $oItem =
                        AMI::getResourceModel('mod_manager_history/table')
                        ->find($id, array('id', 'state'));
                    $oItem->state =
                        $mode === self::LOG_FINISH ? 'success' : 'failure';
                    $oItem->save();
                }
                break;
        }
    }

    /**
     * Loads custom locale file in case of custom error.
     *
     * @param  AMI_Exception $oException  Exception
     * @return void
     * @amidev Temporary
     */
    public function loadExceptionLocale(AMI_Exception $oException){
        // var_dump($oException->getCode());die;###
        if($oException->getCode() !== AMI_Package_Exception::CUSTOM_ERROR){
            return;
        }
        $this->loadStatusMessages('errors.lng');
    }

    /**
     * Loads extra status messages locales from file stored in configuration folder.
     *
     * @param string $file
     * @return void
     */
    public function loadStatusMessages($file){
        $oTpl = AMI::getResource('env/template');
        $aLocale = $oTpl->parseLocale(
            $this->configPath . 'configs/' . $this->hypermod . '/' .
            $this->config . '/' . $file,
            AMI_Registry::get('lang')
        );
        if($aLocale && is_array($aLocale)){
            AMI::getSingleton('response')->mergeStatusMessages($aLocale);
        }
    }

    /**
     * Adds status message during installation/uninstallation.
     *
     * @return void
     * @see    AMI_Service::addStatusMessage() for parameters.
     */
    public static function addStatusMessage($message, array $aParams = array(), $type = AMI_Sesponse::STATUS_MESSAGE, $modId = ''){
        $oResponse = AMI::getSingleton('response');

        $aMessage = array(
            'modId'         => $modId,
            'key'           => $message,
            'message'       => $message,
            'type'          => $type,
            'aParams'       => $aParams
        );
        $aMessage = $oResponse->cbStatusMessage($aMessage);
        $oResponse->addStatusMessage('custom_message', array('message' => $aMessage['message']));
    }

    /**
     * Returns dependent packages information.
     *
     * @param  string $pkgId  Package Id
     * @return array
     */
    public static function getDependentPackages($pkgId){
        // #CMS-11708 {

        $locale = AMI_Registry::get('lang', 'en');
        $oPkgManager = AMI_PackageManager::getInstance();

        $aPackages = $oPkgManager->getDownloadedPackages(AMI_PackageManager::TYPE_BOTH, AMI_PackageManager::STATUS_INSTALLED);
        #d::vd($aPackages);###
        unset($aPackages[$pkgId]);
        $aUninstallingPkgInfo = $oPkgManager->getManifest($pkgId);
        $aInstalled = array(
            'hypermods' => array(),
            'configs'   => array()
        );
        foreach($aUninstallingPkgInfo['install'] as $aInstall){
            if(isset($aInstall['configuration'])){
                $aInstalled['configs'][] = $aInstall;
            }elseif(isset($aInstall['hypermodule'])){
                $aInstalled['hypermods'][] = $aInstall['hypermodule'];
            }
        }
        $aPackages = array_keys($aPackages);
        $aDependencies = array();
        foreach($aPackages as $installedPkgId){
            $aPkgInfo = $oPkgManager->getManifest($installedPkgId);
            if(isset($aPkgInfo['dependencies'])){
                $dependent = FALSE;
                foreach($aPkgInfo['dependencies'] as $aDependence){
                    if(isset($aDependence['configuration'])){
                        foreach($aInstalled['configs'] as $aInstall){
                            if(
                                $aDependence['configuration'] === $aInstall['configuration'] &&
                                $aDependence['hypermodule'] === $aInstall['hypermodule']
                            ){
                                $dependent = TRUE;
                                break 2; // foreach($aPkgInfo['dependencies'] as $aDependence){
                            }
                        }
                    }elseif(
                        isset($aDependence['hypermodule']) &&
                        in_array($aDependence['hypermodule'], $aInstalled['hypermods'])
                    ){
                        $dependent = TRUE;
                        break; // foreach($aPkgInfo['dependencies'] as $aDependence){
                    }
                }
                if($dependent){
                    $aDependencies[$installedPkgId] = "'" . $aPkgInfo['information'][$locale]['title'] . " (" . $installedPkgId . ")'";
                }
            }
        }

        return $aDependencies;
    }

    // } #CMS-11708
}

/**
 * Transaction installing CMS package.
 *
 * Example:
 * <code>
 * // Root script context
 *
 * $AMI_ENV_SETTINGS = array(
 *     'mode'          => 'full',
 *     'disable_cache' => TRUE
 * );
 * require_once 'ami_env.php';
 *
 * $oResponse = AMI::getSingleton('response');
 * $oResponse->start();
 *
 * $pkgId     = 'amiro.sample';
 * $section   = 'plugins';
 * $taborder  = 0;
 * $modId     = 'inst_ami_sample'
 * $aCaptions = array(
 *     'en' => array(
 *         '' => array(
 *             'header'           => 'SAMPLE MODULE',
 *             'menu'             => 'Sample Module',
 *             'description'      => 'Sample instance of AmiClean base hypermodule / AmieSample configuration',
 *             'specblock'        => 'First specblock',
 *             'specblock:custom' => 'Second specblock'
 *         )
 *     ),
 *     'ru' => array(
 *         '' => array(
 *             'header'           => 'SAMPLE MODULE (ru)',
 *             'menu'             => 'Sample Module (ru)',
 *             'description'      => 'Sample instance of AmiClean base hypermodule / AmieSample configuration (ru)',
 *             'specblock'        => 'First specblock (ru)',
 *             'specblock:custom' => 'Second specblock (ru)'
 *         )
 *     )
 * );
 *
 * $oModManipulator = new AMI_PackageInstall(
 *     $pkgId,
 *     $section,
 *     $taborder,
 *     $modId,
 *     $aCaptions,
 *     AMI_iTx_Cmd::MODE_COMMON
 * );
 * try{
 *     $oModManipulator->run();
 *     // Success installation
 *     // ...
 * }catch(AMI_Exception $oException){
 *     // Installation failed
 *     d::w('<span style="color: red;">' . $oException->getMessage() . '</span>');
 *     d::trace($oException->getTrace());
 * }
 *
 * $oResponse->send();
 * </code>
 *
 * @package    Package
 * @subpackage Controller
 * @since      6.0.2
 */
final class AMI_Package_Install extends AMI_Package_InstanceManipulator{
    /**
     * Transaction type for audit
     *
     * @var string
     */
    protected $transactionType = 'install';

    /**
     * Audit status for successfull transaction
     *
     * @var string
     */
    protected $auditSuccessStatus = 'added';

    /**
     * Module captions
     *
     * @var array
     */
    protected $aCaptions;

    /**
     * Package install Id
     *
     * @var int
     */
    protected $installId;

    /**
     * Constructor.
     *
     * @param  string $pkgId      Package Id
     * @param  string $section    Admin section
     * @param  int    $taborder   Module tab order
     * @param  string $modId      Module Id, if not started with 'inst_' will be prefixed
     * @param  array  $aCaptions  FIrst configuration captions
     * @param  int    $mode       Flags specifying installation mode, AMI_iTx_Cmd::MODE_*
     */
    public function __construct(
        $pkgId, $section, $taborder, $modId,
        array $aCaptions, $mode = AMI_iTx_Cmd::MODE_COMMON
    ){
        $modId = (string)$modId;
        // Force prepend 'inst_' prefix if none
        if($modId !== '' && mb_strpos($modId, 'inst_') !== 0){
            $modId = 'inst_' . $modId;
        }

        $this->pkgId     = (string)$pkgId;
        $this->section   = (string)$section;
        $this->tabOrder  = (int)$taborder;
        $this->modId     = $modId;
        $this->aCaptions = $aCaptions;
        $this->mode      = (int)$mode;

        $this->txName =
            "Installing package '" . $this->pkgId . "': " .
            "section = '" . $this->section . "', " .
            "taborder = " . $this->tabOrder . ", " .
            "mode = " . $this->getModeAsString($this->mode) . ", " .
            "modId = '" . $this->modId . "'";
        AMI_Tx::log($this->txName);

        $this->init();

        $this->validate();
        $this->oPkgCommon = new AMI_Package_Common('', '', $this->pkgId);
        $this->addAction('logStart');
        $this->addAction('validateModes');
        $this->addAction('installInstances');
        $this->addAction('logFinish');
    }

    /**
     * Returns module Id.
     *
     * @return string
     */
    public function getModId(){
        return $this->modId;
    }

    /**
     * Transaction action.
     *
     * Create record in mod manager history table.
     *
     * @return void
     */
    protected function logStart(){
        $this->oPkgCommon->log($this->transactionType, AMI_Package_Common::LOG_START);
        $this->installId = $this->oPkgCommon->installId;
    }

    /**
     * Transaction action.
     *
     * Validates install/uninstall modes from meta.
     *
     * @return void
     */
    protected function validateModes(){
        $oPkgManager = AMI_PackageManager::getInstance();
        $oPkgManager->validateMetaModes($this->pkgId);
    }

    /**
     * Transaction action.
     *
     * Install instances from package.
     *
     * @return void
     * @throws AMI_Tx_Exception  In case of problems.
     */
    protected function installInstances(){
        $next = FALSE;
        $tabOrder = $this->tabOrder;
        foreach($this->aPkgInfo['install'] as $aHyperConf){
            $modId = $this->modId;
            $aCaptions = $this->aCaptions;
            if($next){
                $modId .= $aHyperConf['postfix'];
                $oMeta = self::getMeta($aHyperConf['hypermodule'], $aHyperConf['configuration']);
                $aCaptions = self::convertCaptions($oMeta->getCaptions());
            }
            $oModManipulator = new AMI_ModInstall(
                $this->aTx,
                $this->section,
                $tabOrder,
                $aHyperConf['hypermodule'],
                $aHyperConf['configuration'],
                $modId,
                $this->pkgId,
                $this->installId,
                $aCaptions,
                $this->mode
            );
            $oModManipulator->run();
            $tabOrder += 10;
            $next = TRUE;
        }
    }

    /**
     * Transaction action.
     *
     * Update state in record of mod manager history table.
     *
     * @return void
     * @throws AMI_Tx_Exception  In case of problems.
     */
    protected function logFinish(){
        $this->oPkgCommon->log($this->transactionType, AMI_Package_Common::LOG_FINISH);
    }
}

/**
 * Transaction updating installed CMS module captions.
 *
 * @package    Package
 * @subpackage Controller
 * @since      x.x.x
 * @todo       Decide about resource
 * @amidev     Temporary?
 */
class AMI_ModUpdateCaptions extends AMI_Package_InstanceManipulator{
    /**
     * Transaction type for audit
     *
     * @var string
     */
    protected $transactionType = 'updateCaptions';

    /**
     * Audit status for successfull transaction
     *
     * @var string
     */
    protected $auditSuccessStatus = 'changed';

    /**
     * Constructor.
     *
     * @param string $modId      Module Id
     * @param array  $aCaptions  Captions
     */
    public function __construct($modId, array $aCaptions){
        $this->modId     = (string)$modId;
        $this->aCaptions = $aCaptions;
        $this->mode      = AMI_iTx_Cmd::MODE_OVERWRITE;
        $oDeclarator     = AMI_ModDeclarator::getInstance();
        $this->pkgId     = $oDeclarator->getAttr($this->modId, 'id_pkg');
        $this->installId = $oDeclarator->getAttr($this->modId, 'id_install');

        $this->init();
        $this->validate();

        $this->txName =
           "Update captions for '" . $this->modId . "': " .
            "pkgId = '" . $this->pkgId . "', " .
            "installId = '" . $this->installId . "', " .
            "mode = " . $this->getModeAsString($this->mode);
        AMI_Tx::log($this->txName);

        // Uninstall previous and install new captions
        $this->addAction('uninstallCaptions');
        $this->addAction('installCaptions');
    }

    /**
     * Uninstalls module captions.
     *
     * @return void
     */
    protected function uninstallCaptions(){
        $path = AMI_iTemplate::LOCAL_LNG_MOD_PATH . '/';
        $oStorage = AMI::getResource('storage/tpl');
        foreach(
            array(
                'header'         => '_headers.lng',
                'menu'           => '_menu.lng',
                'menu_group'     => '_menu_group.lng',
                'description'    => '_start.lng',
                'specblock'      => '_specblocks.lng',
                'specblock_desc' => '_specblocks.lng'
            ) as $template
        ){
            $this->aTx['storage']->addCommand(
                'pkg/captions/uninstall',
                new AMI_Tx_Cmd_Args(
                    array(
                        'mode'      => $this->mode,
                        'modId'     => $this->modId,
                        'target'    => $path . $template,
                        'oStorage'  => $oStorage
                    )
                )
            );
        }
    }

    /**
     * Installs module captions.
     *
     * @return void
     */
    protected function installCaptions(){
        $path = AMI_iTemplate::LOCAL_LNG_MOD_PATH . '/';
        $oStorage = AMI::getResource('storage/tpl');
        foreach(array('en', 'ru') as $locale){
            if(
                !isset($this->aCaptions[$locale]['']['menu_group']) ||
                trim($this->aCaptions[$locale]['']['menu_group']) === ''
            ){
                $this->aCaptions[$locale]['']['menu_group'] =
                    $this->aCaptions[$locale]['']['menu'];
            }
        }
        foreach(
            array(
                '/^header$/'           => '_headers.lng',
                '/^menu_group$/'       => '_menu_group.lng',
                '/^menu$/'             => '_menu.lng',
                '/^description$/'      => '_start.lng',
                '/^specblock(_desc)?(\:.+)?$/' => '_specblocks.lng'
            ) as $caption => $template
        ){
            $this->aTx['storage']->addCommand(
                'pkg/captions/install',
                new AMI_Tx_Cmd_Args(
                    array(
                        'mode'      => $this->mode,
                        'modId'     => $this->modId,
                        'target'    => $path . $template,
                        'oStorage'  => $oStorage,
                        'aCaptions' => $this->aCaptions,
                        'caption'   => $caption
                    )
                )
            );
        }
    }
}

/**
 * Transaction installing instance local code to "_local/modules/code" folder.
 *
 * Code templates from configuration folder, i. e.
 * "_local/modules/distrib/configs/ami_clean/ami_sample/code"
 * will be used.
 *
 * @package    Package
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 * @todo       Decide about resource
 */
class AMI_ModInstallInstanceLocalCode extends AMI_Package_InstanceManipulator{
    /**
     * Transaction type for audit
     *
     * @var string
     */
    protected $transactionType = 'genLocalCode';

    /**
     * Audit status for successfull transaction
     *
     * @var string
     */
    protected $auditSuccessStatus = 'changed';

    /**
     * Constructor.
     *
     * @param string $modId  Module Id
     * @param int    $mode   Flags specifying installation mode, AMI_iTx_Cmd::MODE_*
     */
    public function __construct($modId, $mode = AMI_iTx_Cmd::MODE_COMMON){
        $oDeclarator = AMI_ModDeclarator::getInstance();
        $this->modId     = (string)$modId;
        list($hypermod, $config) = $oDeclarator->getHyperData($this->modId);
        $this->hypermod  = $hypermod;
        $this->config    = $config;
        $this->mode      = (int)$mode;
        $this->pkgId     = $oDeclarator->getAttr($this->modId, 'id_pkg');
        $this->installId = $oDeclarator->getAttr($this->modId, 'id_install');

        $this->txName =
            'Installing local PHP code: ' .
            "hypermod = '" . $this->hypermod . "', " .
            "config = '" . $this->config . "', " .
            "mode = " . $this->getModeAsString($this->mode) . ", " .
            "modId = '" . $modId . "'";
        AMI_Tx::log($this->txName);

        $this->oPkgCommon = new AMI_Package_Common($this->hypermod, $this->config, $this->pkgId);
        $this->init();
        $this->validate();
        $this->addAction('installLocalCode');
        $this->addAction('installIcons');
    }

    /**
     * Transaction action.
     *
     * Creates local module code files.
     *
     * @return void
     * @todo   Make public method to use from module manager
     */
    protected function installLocalCode(){
        $oStorage = AMI::getResource('storage/fs');
        foreach($this->getResources('code') as $resource){
            $code = $this->getContentByResource($resource);
            $class = AMI::getClassPrefix($this->modId);
            $name = str_replace('--modId--', $class, basename($resource));
            $code = str_replace('##modId##', $class, $code);
            $this->aTx['storage']->addCommand(
                'storage/set',
                new AMI_Tx_Cmd_Args(
                    array(
                        'mode'     => $this->mode,
                        'content'  => $code,
                        'target'   => "{$this->oPkgCommon->localModPath}code/{$name}",
                        'oStorage' => $oStorage
                    )
                )
            );
        }
    }

    /**
     * Creates local module icons (for start page and specblocks in site manager) and copy other icons.
     *
     * Files from configuration folder, i. e.
     * "_local/modules/distrib/configs/ami_clean/ami_sample/icons"
     * will be used.
     *
     * @return void
     */
    protected function installIcons(){
        $oStorage = AMI::getResource('storage/fs');
        $aIcons = $this->getResources('icons');
        $aSourceIcons = $aIcons;
        $aRegExpXName =
            array(
                '/\/' . preg_quote('--modId--.gif', '/') . '$/'              => '--modId--.gif',
                '/\/' . preg_quote('--modId--_specblock_en.gif', '/') . '$/' => '--modId--_specblock_en.gif',
                '/\/' . preg_quote('--modId--_specblock_ru.gif', '/') . '$/' => '--modId--_specblock_ru.gif'
            );
        foreach($aRegExpXName as $regExp => $defaultIcon){
            foreach($aSourceIcons as $path){
                if(preg_match($regExp, $path)){
                    continue 2;
                }
            }
            $aIcons[] = $this->oPkgCommon->hyperPath . 'default_icons/' . $defaultIcon;
        }

        $path = $this->oPkgCommon->localPath . '_admin/images/' . $this->modId;
        if(!$oStorage->exists($path)){
            $oStorage->mkdir($path);
        }
        foreach($aIcons as $resource){
            if(preg_match('/\-\-modId\-\-/', basename($resource))){
                $target =
                    $this->oPkgCommon->localPath . '_admin/images/icons/' .
                    str_replace('--modId--', $this->modId, basename($resource));
            }else{
                $target =
                    $this->oPkgCommon->localPath . '_admin/images/' .
                    $this->modId . '/' . basename($resource);
            }
            // d::vd("{$resource} -> {$target}");###
            $this->aTx['storage']->addCommand(
                'storage/copy',
                new AMI_Tx_Cmd_Args(
                    array(
                        'mode'     => $this->mode,
                        'source'   => $resource,
                        'target'   => $target,
                        'oStorage' => $oStorage
                    )
                )
            );
        }
    }
}

/**
 * Transaction installing package local code to "_local/modules/code" folder.
 *
 * Code templates from configuration folder, i. e.
 * "_local/modules/distrib/configs/ami_clean/ami_sample/code"
 * will be used.
 *
 * @package    Package
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 * @todo       Decide about resource
 */
class AMI_ModInstallPackageLocalCode extends AMI_Package_InstanceManipulator{
    /**
     * Transaction type for audit
     *
     * @var string
     */
    protected $transactionType = 'genLocalCode';

    /**
     * Audit status for successfull transaction
     *
     * @var string
     */
    protected $auditSuccessStatus = 'changed';

    /**
     * Package install Id
     *
     * @var int
     */
    protected $installId;


    /**
     * Constructor.
     *
     * @param string $modId  Module Id
     * @param int    $mode   Flags specifying installation mode, AMI_iTx_Cmd::MODE_*
     */
    public function __construct($modId, $mode = AMI_iTx_Cmd::MODE_COMMON){
        $oDeclarator = AMI_ModDeclarator::getInstance();
        $this->modId     = (string)$modId;
        $this->pkgId     = $oDeclarator->getAttr($this->modId, 'id_pkg');
        $this->installId = (int)$oDeclarator->getAttr($this->modId, 'id_install');
        $this->mode      = (int)$mode;

        $this->txName =
            'Installing package local PHP code: ' .
            "id_pkg = '" . $this->pkgId . "', " .
            "id_install = '" . $this->installId . "', " .
            "mode = " . $this->getModeAsString($this->mode) . ", " .
            "modId = '" . $this->modId . "'";
        AMI_Tx::log($this->txName);

        $this->init();

        $this->validate();
        $this->addAction('installInstancesLocalCode');
    }

    /**
     * Install package instances local code.
     *
     * @return void
     * @throws AMI_Tx_Exception  In case of problems.
     */
    protected function installInstancesLocalCode(){
        $oDeclarator = AMI_ModDeclarator::getInstance();
        if($this->installId){
            // Common package, instances for meta has same 'id_install' attribute
            $aInstanceIds = $oDeclarator->getModIdsByInstallId($this->installId);
            $aInstalledHyperConfigs = array_reverse($this->aPkgInfo['install']);
            foreach($aInstalledHyperConfigs as $aHyperConf){
                $aModIds = $oDeclarator->getRegistered($aHyperConf['hypermodule'], $aHyperConf['configuration']);
                $aModIds = array_intersect($aInstanceIds, $aModIds);
                foreach($aModIds as $modId){
                    if((int)$oDeclarator->getAttr($modId, 'id_install') === $this->installId){
                        $oModManipulator = new AMI_ModInstallInstanceLocalCode(
                            $modId,
                            $this->mode
                        );
                        // $oModManipulator->setDebug(TRUE);###
                        $oModManipulator->run();
                    }
                }
            }
        }else{
            // Amiro base package, instances for meta are children of current module
            $aInstanceIds = $oDeclarator->getSubmodules($this->modId);
            array_unshift($aInstanceIds, $this->modId);
            $aGenerated = array();
            foreach($aInstanceIds as $modId){
                list($hypermod, $config) = $oDeclarator->getHyperData($modId);
                $label = $hypermod . '/' .  $config;
                if(in_array($label, $aGenerated)){
                    continue;
                }
                $aGenerated[] = $label;
                $oModManipulator = new AMI_ModInstallInstanceLocalCode(
                    $modId,
                    $this->mode
                );
                // $oModManipulator->setDebug(TRUE);###
                $oModManipulator->run();
            }
        }
    }
}

/**
 * Transaction uninstalling CMS module.
 *
 * @package    Package
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary?
 */
final class AMI_ModUninstall extends AMI_Package_InstanceManipulator{
    /**
     * Transaction type for audit
     *
     * @var string
     */
    protected $transactionType = 'uninstall';

    /**
     * Audit status for successfull transaction
     *
     * @var string
     */
    protected $auditSuccessStatus = 'deleted';

    /**
     * Transaction arguments
     *
     * @var AMI_Tx_Cmd_Args
     */
    protected $oArgs;

    /**
     * Constructor.
     *
     * @param array  $aTx    Transactions
     * @param string $modId  Module Id
     * @param int    $mode   Flags specifying deinstallation mode, AMI_iTx_Cmd::MODE_*
     */
    public function __construct(array $aTx, $modId, $mode = AMI_iTx_Cmd::MODE_COMMON){
        $this->aTx = $aTx;
        $oDeclarator = AMI_ModDeclarator::getInstance();
        $this->modId = (string)$modId;
        $this->mode  = (int)$mode;
        $this->pkgId = $oDeclarator->getAttr($this->modId, 'id_pkg');
        // $this->installId = $oDeclarator->getAttr($this->modId, 'id_pkg');
        $this->init();

        list($this->hypermod, $this->config) = $oDeclarator->getHyperData($this->modId);
        $this->txName =
            "Uninstalling '" . $modId . "': " .
            "package '" . $this->pkgId . "', " .
            "section = '" . $this->section . "', " .
            "hypermod = '" . $this->hypermod . "', " .
            "config = '" . $this->config . "', " .
            "mode = " . $this->getModeAsString($this->mode);
        AMI_Tx::log($this->txName);

        $this->validate();
        $this->section = $oDeclarator->getSection($this->modId);
        $this->oPkgCommon = new AMI_Package_Common($this->hypermod, $this->config, $this->pkgId);
        $this->addAction('checkProcessed');
        $this->addAction('onUninstall');
        $this->addAction('deleteDeclaration');
        $this->addAction('uninstallRulesCaptions');
        $this->addAction('uninstallCaptions');
        $this->addAction('uninstallIcons');
        $this->addAction('uninstallJS');
        $this->addAction('uninstallTplResource', array(TRUE));
        $this->addAction('uninstallTplResource');
        $this->addAction('uninstallTplResource', array(TRUE, TRUE));
        $this->addAction('uninstallTplResource', array(FALSE, TRUE));
        $this->addAction('uninstallLocalCode');
        $this->addAction('uninstallDB');
        $this->addAction('uninstallOptions');
        $this->addAction('uninstallRights');
        $this->addAction('finish');
        $this->addAction('onPostUninstall');
        $this->addAction('onPostUninstallUnmasked');
    }

    /**
     * Initializes transactions.
     *
     * @return void
     */
    protected function initTx(){
    }

    public function commit(){
    }

    /**
     * Validates initial data.
     *
     * @return void
     * @throws AMI_Package_Exception  In case of problems.
     */
    protected function validate(){
        parent::validate();

        $oDeclarator = AMI_ModDeclarator::getInstance();
        // Module installation check
        if(!$oDeclarator->isRegistered($this->modId)){
            throw new AMI_Package_Exception(
                'Module is not installed',
                AMI_Package_Exception::NOT_INSTALLED
            );
        }

        // #CMS-11173 {

        // Forbid base instances uninstallation
        if(
            $oDeclarator->getAttr($this->modId, 'core_v5') &&
            !in_array($this->modId, array('news', 'articles', 'photoalbum', 'blog'))
        ){
            throw new AMI_Package_Exception(
                'Base instance cannot be uninstalled',
                AMI_Package_Exception::UNINSTALL_FORBIDDEN
            );
        }

        // } #CMS-11173
        // #CMS-11708 {

        $aDependencies = AMI_Package_Common::getDependentPackages($this->pkgId);
        if(sizeof($aDependencies) > 0){
            throw new AMI_Package_Exception(
                'Uninstall dependent extensions first',
                AMI_Package_Exception::HAS_DEPENDENCIES,
                NULL,
                array('dependencies' => implode(', ', $aDependencies))
            );
        }

        // } #CMS-11708
    }

    /**#@+
     * Transaction action.
     */

    /**
     * Executes PHP-code before deinstallation.
     *
     * @return void
     */
    protected function onUninstall(){
        // Forbid to uninstall system permanent modules
        if(!$this->oPkgCommon->isLocalConfigUsed && AMI_Package::getMeta($this->hypermod, $this->config)->isPermanent()){
            throw new AMI_Package_Exception(
                'This module cannot be uninstalled',
                AMI_Package_Exception::IS_PERMANENT
            );
        }
        // Call pre-uninstall hook
        $this->oArgs = new AMI_Tx_Cmd_Args(get_object_vars($this));
        $path = $this->oPkgCommon->configPath . 'configs/' . $this->hypermod . '/' . $this->config . '/uninstall_before.php';
        if(file_exists($path)){
            require $path;
        }
    }

    /**
     * Deletes local declaration.
     *
     * @return void
     */
    protected function deleteDeclaration(){
        $oStorage = AMI::getResource('storage/fs');
        foreach($this->getResources('declaration') as $resource){
            $localName = basename($resource);
            if($localName === 'declaration.php'){
                $localName = 'declares.php';
            }
            $this->aTx['storage']->addCommand(
                'pkg/declaration/uninstall',
                new AMI_Tx_Cmd_Args(
                    array(
                        'mode'      => $this->mode,
                        'modId'     => $this->modId,
                        'hypermod'  => $this->hypermod,
                        'config'    => $this->config,
                        'source'    => $resource,
                        'target'    => $this->oPkgCommon->localModPath . "declaration/{$localName}",
                        'oStorage'  => $oStorage
                    )
                )
            );
        }
    }

    /**
     * Deletes local rules captions.
     *
     * @return void
     */
    protected function uninstallRulesCaptions(){
        $oStorage = AMI::getResource('storage/tpl');
        foreach($this->getResources('rules') as $resource){
            $target =
                '_local/_admin/templates/lang/options/' .
                str_replace('--modId--', $this->modId, basename($resource));
            $content = $this->getContentByResource($resource);
            if($oStorage->exists($target)){
                $this->aTx['storage']->addCommand(
                    'storage/clean',
                    new AMI_Tx_Cmd_Args(
                        array(
                            'mode'     => $this->mode,
                            'target'   => $target,
                            'oStorage' => $oStorage
                        )
                    )
                );
            }
        }
    }

    /**
     * Uninstalls module captions.
     *
     * @return void
     */
    protected function uninstallCaptions(){
        $path = AMI_iTemplate::LOCAL_LNG_MOD_PATH . '/';
        $oStorage = AMI::getResource('storage/tpl');
        foreach(
            array(
                'header'      => '_headers.lng',
                'menu'        => '_menu.lng',
                'menu_group'  => '_menu_group.lng',
                'description' => '_start.lng',
                'specblock'   => '_specblocks.lng'
            ) as $template
        ){
            $this->aTx['storage']->addCommand(
                'pkg/captions/uninstall',
                new AMI_Tx_Cmd_Args(
                    array(
                        'mode'      => $this->mode,
                        'modId'     => $this->modId,
                        'target'    => $path . $template,
                        'oStorage'  => $oStorage
                    )
                )
            );
        }
    }

    /**
     * Deletes local module icons (for start page and specblocks in site manager).
     *
     * @return void
     */
    protected function uninstallIcons(){
        $oStorage = $oStorage = AMI::getResource('storage/fs');
        $aIcons = $this->getResources('icons');
        $aSourceIcons = $aIcons;
        $aRegExpXName =
            array(
                '/\/' . preg_quote('--modId--.gif', '/') . '$/'              => '--modId--.gif',
                '/\/' . preg_quote('--modId--_specblock_en.gif', '/') . '$/' => '--modId--_specblock_en.gif',
                '/\/' . preg_quote('--modId--_specblock_ru.gif', '/') . '$/' => '--modId--_specblock_ru.gif'
            );
        foreach($aRegExpXName as $regExp => $defaultIcon){
            foreach($aSourceIcons as $path){
                if(preg_match($regExp, $path)){
                    continue 2;
                }
            }
            $aIcons[] = $this->oPkgCommon->hyperPath . 'default_icons/' . $defaultIcon;
        }

        foreach($aIcons as $resource){
            if(preg_match('/\-\-modId\-\-/', basename($resource))){
                $target =
                    $this->oPkgCommon->localPath . '_admin/images/icons/' .
                    str_replace('--modId--', $this->modId, basename($resource));
            }else{
                $target =
                    $this->oPkgCommon->localPath . '_admin/images/' .
                    $this->modId . '/' . basename($resource);
            }
            if($oStorage->exists($target)){
                $this->aTx['storage']->addCommand(
                    'storage/clean',
                    new AMI_Tx_Cmd_Args(
                        array(
                            'mode'       => $this->mode,
                            'target'     => $target,
                            'oStorage'   => $oStorage,
                            'rmEmptyDir' => TRUE
                        )
                    )
                );
            }
        }
    }

    /**
     * Deletes local module JS.
     *
     * @return void
     */
    protected function uninstallJS(){
        $oStorage = AMI::getResource('storage/fs');
        foreach($this->getResources('js') as $resource){
            $target =
                $this->oPkgCommon->localPath . '_admin/_js/' .
                $this->modId . '/' . basename($resource);
            if($oStorage->exists($target)){
                $this->aTx['storage']->addCommand(
                    'storage/clean',
                    new AMI_Tx_Cmd_Args(
                        array(
                            'mode'     => $this->mode,
                            'target'   => $target,
                            'oStorage' => $oStorage
                        )
                    )
                );
            }
        }
    }

    /**
     * Deletes local module templates/locales.
     *
     * @param  bool $processLocales  Flag specifying to process templates or locales
     * @param  bool $processFrn      Flag specifying to process front resources
     * @return void
     */
    protected function uninstallTplResource($processLocales = FALSE, $processFrn = FALSE){
        $oStorage = AMI::getResource('storage/tpl');
        if($processFrn){
            $type = $processLocales ? 'locales_frn' : 'templates_frn';
            $path = $processLocales ? AMI_iTemplate::LNG_MOD_PATH . '/' : AMI_iTemplate::TPL_MOD_PATH . '/';
        }else{
            $type = $processLocales ? 'locales' : 'templates';
            $path = $processLocales ? AMI_iTemplate::LOCAL_LNG_MOD_PATH . '/' : AMI_iTemplate::LOCAL_TPL_MOD_PATH . '/';
        }
        foreach($this->getResources($type) as $resource){
            $target = $path . str_replace('--modId--', $this->modId, basename($resource));
            if($oStorage->exists($target)){
                $this->aTx['storage']->addCommand(
                    'storage/clean',
                    new AMI_Tx_Cmd_Args(
                        array(
                            'mode'     => $this->mode,
                            'target'   => $target,
                            'oStorage' => $oStorage
                        )
                    )
                );
            }
        }
    }

    /**
     * Deletes local module code files.
     *
     * @return void
     */
    protected function uninstallLocalCode(){
        $oStorage = AMI::getResource('storage/fs');
        /*
        $aCode = AMI_Lib_FS::scan("{$this->oInstall->localModPath}code", AMI::getClassPrefix($this->modId) . '*.php', '', AMI_Lib_FS::SCAN_FILES, 0);
        foreach($aCode as $target){
            new
                AMI_Tx_Cmd_Storage_Cleaner(
                    $this->aTx['db'],
                    new AMI_Tx_Cmd_Args(
                        array(
                            'mode'     => $this->mode,
                            'target'   => $target,
                            'oStorage' => $oStorage
                        )
                    )
                );
        }
        */
        foreach($this->getResources('code') as $resource){
            $code = $this->getContentByResource($resource);
            $class = AMI::getClassPrefix($this->modId);
            $name = str_replace('--modId--', $class, basename($resource));
            $this->aTx['storage']->addCommand(
                'storage/clean',
                new AMI_Tx_Cmd_Args(
                    array(
                        'mode'     => $this->mode,
                        'target'   => "{$this->oPkgCommon->localModPath}code/{$name}",
                        'oStorage' => $oStorage
                    )
                )
            );
        }
    }

    /**
     * Deletes module db tables.
     *
     * @return void
     * @todo   Other datasource support.
     */
    protected function uninstallDB(){
        foreach($this->getResources('db') as $resource){
            $sql = $this->getContentByResource($resource);
            /**
             * @var AMI_iDB
             */
            $oDB = AMI::getSingleton('db');
            $sql = str_replace('##modId##', $this->modId, $sql);
            if(preg_match('/^\s*CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`([^`]+)`/si', $sql, $aMatches)){
                $table = $aMatches[1];
                $this->aTx['db']->addCommand(
                    'table/drop',
                    new AMI_Tx_Cmd_Args(
                        array(
                            'mode'   => $this->mode,
                            'modId'  => $this->modId,
                            'target' => $table
                        )
                    )
                );
            }else{
                trigger_error("'CREATE TABLE' sentence is absent in '{$resource}'", E_USER_WARNING);
            }
        }
    }

    /**
     * Deletes module options.
     *
     * @return void
     */
    protected function uninstallOptions(){
        /**
         * @var AMI_iDB
         */
        $oDB = AMI::getSingleton('db');

        $aChildren = AMI_ModDeclarator::getInstance()->getChildren($this->modId);
        $aChildren[] = $this->modId;
        $oQuery =
            DB_Query::getSnippet("DELETE FROM `cms_options` WHERE `module_name` IN (%s) AND `name` = %s")
            ->implode($aChildren)->q('options_dump');
        $oDB->query($oQuery, AMI_DB::QUERY_SYSTEM);
    }

    /**
     * Deletes module sys rights.
     *
     * @return void
     */
    protected function uninstallRights(){
        /**
         * @var AMI_iDB
         */
        $oDB = AMI::getSingleton('db');
        $oDeclarator = AMI_ModDeclarator::getInstance();
        $aModules = $oDeclarator->getSubmodules($this->modId);
        $aModules[] = $this->modId;
        $oQuery =
            DB_Query::getSnippet("DELETE FROM `cms_sys_actions_rights` WHERE `module_name` IN (%s)")
            ->implode($aModules);
        $oDB->query($oQuery, AMI_DB::QUERY_SYSTEM);
    }

    /**
     * Finishes uninstallation.
     *
     * @return void
     */
    protected function finish(){
        global $Core;
        static $doUpdateRightsVersion = TRUE;

        self::$aProcessedEntities[] = $this->processingEntity;
        // Clear admin interface modules cache
        if($doUpdateRightsVersion && isset($Core) && is_object($Core) && ($Core instanceof CMS_Core)){
            $Core->UpdateRightsVersion();
            $doUpdateRightsVersion = FALSE;
        }

        // Call post-uninstall hook
        $path = $this->oPkgCommon->configPath . 'configs/' . $this->hypermod . '/' . $this->config . '/uninstall_after.php';
        if(file_exists($path)){
            require $path;
        }
    }

    /**
     * Run supported extensions on instance post-install.
     *
     * @return void
     */
    protected function onPostUninstall(){
        $this->aTx['storage']->addCommand(
            'pkg/onPostUninstall',
            new AMI_Tx_Cmd_Args(
                get_object_vars($this) + array('extMethod' => 'onModPostUninstall')
            )
        );
    }

    /**
     * Run supported extensions on instance post-install (without cheking unistallation mode).
     *
     * @return void
     */
    protected function onPostUninstallUnmasked(){
        $this->aTx['storage']->addCommand(
            'pkg/onPostUninstall',
            new AMI_Tx_Cmd_Args(
                get_object_vars($this) + array('extMethod' => 'onModPostUninstallUnmasked')
            )
        );
    }

    /**#@-*/
}

/**
 * Transaction uninstalling CMS package.
 *
 * Example:
 * <code>
 * // Root script context
 *
 * $AMI_ENV_SETTINGS = array(
 *     'mode'          => 'full',
 *     'disable_cache' => TRUE
 * );
 * require_once 'ami_env.php';
 *
 * $oResponse = AMI::getSingleton('response');
 * $oResponse->start();
 *
 * $modId     = 'inst_ami_sample'
 *
 * $oModManipulator = new AMI_PackageUninstall(
 *     $pkgId
 *     AMI_iTx_Cmd::MODE_COMMON
 * );
 * try{
 *     $oModManipulator->run();
 *     // Success uninstallation
 *     // ...
 * }catch(AMI_Exception $oExeception){
 *     // Uninstallation failed
 *     d::w('<span style="color: red;">' . $oException->getMessage() . '</span>');
 *     d::trace($oException->getTrace());
 * }
 *
 * $oResponse->send();
 * </code>
 *
 * @package    Package
 * @subpackage Controller
 * @since      6.0.2
 */
final class AMI_Package_Uninstall extends AMI_Package_InstanceManipulator{
    /**
     * Transaction type for audit
     *
     * @var string
     */
    protected $transactionType = 'uninstall';

    /**
     * Audit status for successfull transaction
     *
     * @var string
     */
    protected $auditSuccessStatus = 'deleted';

    /**
     * Module captions
     *
     * @var array
     */
    protected $aCaptions;

    /**
     * Package install Id
     *
     * @var int
     */
    protected $installId;

    /**
     * Constructor.
     *
     * @param  string $modId  Module Id
     * @param  int    $mode   Flags specifying uninstallation mode, AMI_iTx_Cmd::MODE_*
     */
    public function __construct($modId, $mode = AMI_iTx_Cmd::MODE_COMMON){
        $oDeclarator = AMI_ModDeclarator::getInstance();
        $this->modId     = (string)$modId;
        $this->pkgId     = (string)$oDeclarator->getAttr($this->modId, 'id_pkg');
        $this->installId = (int)$oDeclarator->getAttr($this->modId, 'id_install');
        $this->mode      = (int)$mode;

        $this->txName =
            "Uninstalling instance '" . $this->modId . "': " .
            "package '" . $this->pkgId . "', " .
            "installId = '" . $this->installId . "', " .
            "mode = " . $this->getModeAsString($this->mode);
        AMI_Tx::log($this->txName);

        $this->init();

        $this->validate();
        $this->oPkgCommon = new AMI_Package_Common('', '', $this->pkgId);
        $this->addAction('logStart');
        $this->addAction('uninstallInstances');
        $this->addAction('logFinish');
    }

    /**
     * Returns module Id.
     *
     * @return string
     */
    public function getModId(){
        return $this->modId;
    }

    /**
     * Create record in mod manager history table.
     *
     * @return void
     */
    protected function logStart(){
        $this->oPkgCommon->log($this->transactionType, AMI_Package_Common::LOG_START);
    }

    /**
     * Uninstall instances from package.
     *
     * @return void
     * @throws AMI_Tx_Exception  In case of problems.
     */
    protected function uninstallInstances(){
        $oDeclarator = AMI_ModDeclarator::getInstance();
        if($this->installId){
            // Common package, instances for meta has same 'id_install' attribute
            $aInstanceIds = $oDeclarator->getModIdsByInstallId($this->installId);
        }else{
            // Amiro base package, instances for meta are children of current module
            // $aInstanceIds = $oDeclarator->getSubmodules($this->modId);
            // array_unshift($aInstanceIds, $this->modId);
            $aInstanceIds = array($this->modId);
        }
        $aInstalledHyperConfigs = array_reverse($this->oPkgCommon->aPkgInfo['install']);

        // Hack: check for ami_multifeeds5 hypermodule
        if(
            !$this->installId &&
            'ami_multifeeds' === $aInstalledHyperConfigs[0]['hypermodule']
        ){
            $aInstalledHyperConfigs[0]['hypermodule'] = 'ami_multifeeds5';
        }

        foreach($aInstalledHyperConfigs as $aHyperConf){
            $aModIds = $oDeclarator->getRegistered($aHyperConf['hypermodule'], $aHyperConf['configuration']);
            $aModIds = array_intersect($aInstanceIds, $aModIds);
            foreach($aModIds as $modId){
                if((int)$oDeclarator->getAttr($modId, 'id_install') === $this->installId){
                    $oModManipulator = new AMI_ModUninstall(
                        $this->aTx,
                        $modId,
                        $this->mode
                    );
                    // $oModManipulator->setDebug(TRUE);###
                    $oModManipulator->run();
                }
            }
        }

        $oDB = AMI::getSingleton('db');
        foreach(array(
            'TRUNCATE `cms_cache`',
            'TRUNCATE `cms_cache_content`',
            'TRUNCATE `cms_cache_blocks`'
        ) as $sql){
            $oDB->query($sql, AMI_DB::QUERY_TRUSTED);
        }
    }

    /**
     * Update state in record of mod manager history table.
     *
     * @return void
     * @throws AMI_Tx_Exception  In case of problems.
     */
    protected function logFinish(){
        $this->oPkgCommon->log($this->transactionType, AMI_Package_Common::LOG_FINISH);
    }
}

/**
 * Transaction import data and options for CMS module.
 *
 * @package    Package
 * @subpackage Controller
 * @since      x.x.x
 * @todo       Decide about resource
 * @amidev     Temporary
 */
class AMI_ModImport extends AMI_Package_InstanceManipulator{
    /**
     * Transaction type for audit
     *
     * @var string
     */
    protected $transactionType = 'import';

    /**
     * Audit status for successfull transaction
     *
     * @var string
     */
    protected $auditSuccessStatus = 'migration';

    /**
     * Import source module Id
     *
     * @var string
     */
    protected $sourceModId;

    /**
     * Constructor.
     *
     * @param string $modId            Module Id
     * @param bool   $sourceModId      Source module Id
     * @param int    $mode             Flags specifying deinstallation mode, AMI_iTx_Cmd::MODE_*
     * @param bool   $importData       Data import needed
     * @param bool   $importOptions    Options import needed
     * @param bool   $importTemplates  Templates import needed
     * @param bool   $importExt        Extensions templates import needed
     */
    public function __construct($modId, $sourceModId, $mode = AMI_iTx_Cmd::MODE_COMMON, $importData = false, $importOptions = false, $importTemplates = false, $importExt = false){
        $this->modId = (string)$modId;
        $this->mode  = (int)$mode;
        $oDeclarator = AMI_ModDeclarator::getInstance();
        $this->pkgId     = $oDeclarator->getAttr($this->modId, 'id_pkg');
        $this->installId = $oDeclarator->getAttr($this->modId, 'id_install');
        $mainInstanceModId = $this->modId;

        $aTypes = array();
        if($importData){
            $aTypes[] = 'DATA';
        }
        if($importOptions){
            $aTypes[] = 'OPTIONS';
        }
        if($importTemplates){
            $aTypes[] = 'TEMPLATES';
        }
        if($importExt){
            $aTypes[] = 'EXT.TEMPLATES';
        }

        $this->txName =
            'Importing: ' .
            "sourceModId = '" . $sourceModId . "', " .
            "types = " . implode(' | ', $aTypes) . ", " .
            "mode = " . $this->getModeAsString($this->mode) . ", " .
            "modId = '" . $this->modId . "'";
        AMI_Tx::log($this->txName);

        $this->init();
        $this->validate();
        $oDeclarator = AMI_ModDeclarator::getInstance();
        $this->section = $oDeclarator->getSection($this->modId);
        $marker = $oDeclarator->getAttr($modId, 'marker', false);
        $this->sourceModId = $sourceModId;
        if($marker){
            $mainInstanceModId = $oDeclarator->getParent($modId);
        }
        list($this->hypermod, $this->config) = $oDeclarator->getHyperData($mainInstanceModId);
        $this->sourceModId = $sourceModId;
        if($marker){
            $this->sourceModId .= '_' . $marker;
        }

        $skipDBImport = false;
        if($marker == 'data_exchange'){
            $skipDBImport = true;
        }

        $this->oPkgCommon = new AMI_Package_Common($this->hypermod, $this->config, $this->pkgId);
        $this->addAction('checkProcessed');
        $this->addAction('onImport');
        if(!$skipDBImport && $importData){
            $this->addAction('importDB');
            $this->addAction('importComments');
        }
        if($importOptions){
            $this->addAction('importOptions');
        }
        if($importTemplates){
            $this->addAction('importTemplates');
            $this->addAction('importLocales');
        }
        if($importExt && ($mainInstanceModId == $this->modId)){
            $this->addAction('importExtTemplates', array('ext_image'));
            $this->addAction('importExtTemplates', array('ext_rss'));
            $this->addAction('importExtTemplates', array('ext_discussion'));
            $this->addAction('importExtTemplates', array('ext_twist_prevention'));
            $this->addAction('importExtTemplates', array('ext_tags'));
            $this->addAction('importExtTemplates', array('ext_rating'));
            $this->addAction('finishExtImport');
        }
        // $this->addAction('finish');
    }

    /**
     * Validates initial data.
     *
     * @return void
     * @throws AMI_Package_Exception  In case of problems.
     */
    protected function validate(){
        parent::validate();

        // Check module installation
        if(!AMI_ModDeclarator::getInstance()->isRegistered($this->modId)){
            throw new AMI_Package_Exception(
                'Module is not installed',
                AMI_Package_Exception::NOT_INSTALLED
            );
        }
    }

    /**
     * Initializes transactions.
     *
     * @return void
     */
    protected function initTx(){
    }

    /**#@+
     * Transaction action.
     */

    /**
     * Validates arguments before import.
     *
     * @return void
     */
    protected function onImport(){
        $oMeta = AMI_Package::getMeta($this->hypermod, $this->config);
        if(!$oMeta->isImportAllowed()){
            throw new AMI_Package_Exception(
                "Import is not allowed for module '" . $this->modId . "'",
                AMI_Package_Exception::IMPORT_FORBIDDEN
            );
        }
        if($this->modId == $this->sourceModId){
            throw new AMI_Package_Exception(
                'Source and destination module Ids are the same',
                AMI_Package_Exception::IMPORT_FORBIDDEN
            );
        }
        if(!AMI_ModDeclarator::getInstance()->isRegistered($this->sourceModId)){
            global $Core;

            if(!$Core->IsInstalled($this->sourceModId)){
                throw new AMI_Package_Exception(
                    'Source module is not installed',
                    AMI_Package_Exception::NOT_INSTALLED
                );
            }
        }
    }

    /**
     * Imports db tables.
     *
     * @return void
     */
    protected function importDB(){
        $oModel = AMI::getResourceModel($this->modId . '/table');
        $table = $oModel->getTableName();
        $hasIdCat = $oModel->hasField('id_cat');
        /**
         * @var AMI_iDB
         */
        $oDB = AMI::getSingleton('db');
        /**
        $this->aTx['db']->addCommand(
            'table/drop',
            new AMI_Tx_Cmd_Args(
                array(
                    'mode'   => $this->mode,
                    'modId'  => $this->modId,
                    'target' => $table
                )
            )
        );
        new AMI_Tx_Cmd_DB_DropTable(
            $this->aTx['db'],
            new AMI_Tx_Cmd_Args(
                array(
                    'mode'   => $this->mode,
                    'modId'  => $this->modId,
                    'target' => $table
                )
            )
        );
        */
        $oSrcModel = AMI::getResourceModel($this->sourceModId . '/table');
        $sourceTable = $oSrcModel->getTableName();
        // $sql = 'CREATE TABLE `' . $table . '` LIKE `' . $sourceTable . '`';
        /*
        $this->aTx['db']->addCommand(
            'table/create',
            new AMI_Tx_Cmd_Args(
                array(
                    'mode'   => $this->mode,
                    'source' => $sql,
                    'target' => $table
                )
            )
        );
        */
        /*
        $this->aTx['db']->addCommand(
            'table/create',
            new AMI_Tx_Cmd_Args(
                array(
                    'mode'   => AMI_iTx_Cmd::MODE_OVERWRITE,
                    'source' => $sql,
                    'target' => $table
                )
            )
        );
        */

        $sql = "DROP TABLE IF EXISTS `" . $table . "`";
        $oDB->allowUnsafeQueryOnce();
        $oDB->query($sql);
        $sql = 'CREATE TABLE `' . $table . '` LIKE `' . $sourceTable . '`';
        $oDB->allowUnsafeQueryOnce();
        $oDB->query($sql);

        // Remove old custom fields
        $sql = "DELETE FROM `cms_modules_custom_fields` WHERE `module_name` = %s";
        $oDB->query(DB_Query::getSnippet($sql)->q($this->modId));

        // Remove old datasets
        $sql = "DELETE FROM `cms_modules_datasets` WHERE `module_name` = %s";
        $oDB->query(DB_Query::getSnippet($sql)->q($this->modId));

        // Remove old comments
        $sql = "DELETE FROM `cms_discussion` WHERE `ext_module` = %s";
        $oDB->query(DB_Query::getSnippet($sql)->q($this->modId));

        // Copy data
        $sql = 'INSERT INTO `' . $table . '` SELECT * FROM `' . $sourceTable . '`';
        $oDB->query($sql);

        // Add Id_cat if categories on
        if($hasIdCat && !$oSrcModel->hasField('id_cat')){
            $sql = 'ALTER TABLE  `' . $table . '` ADD  `id_cat` INT UNSIGNED DEFAULT 1 NOT NULL AFTER `id`, ADD INDEX i_id_cat (`id_cat`)';
            $oDB = AMI::getSingleton('db');
            $db = $oDB->getCoreDB();
            $db->setSafeSQLOptions('alter');
            $db->execute($sql);
            $db->clearSafeSQLOptions();
        }

        // Make all items shared
        if($oModel->hasField('id_page')){
            $sql = 'UPDATE `' . $table . '` SET `id_page` = 0';
            $oDB->query($sql);
        }

        // Make archived elements direct link accessable (depending on option value)
        if($oModel->hasField('archive') && AMI::issetOption($this->sourceModId, 'archive_type')){
            $archiveType = AMI::getOption($this->sourceModId, 'archive_type');
            $archivePeriod = AMI::getOption($this->sourceModId, 'archive_period');
            if(AMI::getOption($this->sourceModId, 'show_type') == 'active'){
                if($archiveType == 'manual'){
                    $sql = 'UPDATE `' . $table . '` SET public_direct_link = 1 WHERE archive = 1';
                }else{
                    $sql = 'UPDATE `' . $table . '` SET public_direct_link = 1 WHERE archive = 1 OR date < DATE_ADD(NOW(), INTERVAL ' . $archivePeriod . ')';
                }
            }
            if(AMI::getOption($this->sourceModId, 'show_type') == 'archive'){
                if($archiveType == 'manual'){
                    $sql = 'UPDATE `' . $table . '` SET public_direct_link = 1 WHERE archive = 0';
                }else{
                    $sql = 'UPDATE `' . $table . '` SET public_direct_link = 1 WHERE archive = 0 AND date > DATE_ADD(NOW(), INTERVAL ' . $archivePeriod . ')';
                }
            }
            $oDB->query($sql);
        }

        // Update counters if category with Id = 1 present
        if($hasIdCat && !$oSrcModel->hasField('id_cat')){
            $sql = "SELECT 1 FROM `" . $table . "_cat` WHERE Id=1";
            $hasCatOne = false;
            if(($rs = $db->select($sql)) && $rs->nextRecord()){
                $hasCatOne = true;
            }

            if($hasCatOne){
                $oAllItems =
                    $oModel
                        ->getList()
                        ->addColumn('id')
                        ->addWhereDef(DB_Query::getSnippet("AND Id_cat=1"))
                        ->load();
                $oPublicItems =
                    $oModel
                        ->getList()
                        ->addColumn('id')
                        ->addWhereDef(DB_Query::getSnippet("AND Id_cat=1 AND public=1"))
                        ->load();
                $allItems = $oAllItems->count();
                $pubItems = $oPublicItems->count();
                if($allItems){
                    $sql = "UPDATE `%s_cat` SET num_items=%s, num_public_items=%s WHERE Id=1";
                    $oDB->query(
                        DB_Query::getSnippet($sql)
                        ->plain($table)
                        ->q($allItems)
                        ->q($pubItems)
                    );
                }
            }
        }

        // #CMS-11434 {

        // Rename foelds to avoid mapping
        // @too: rename indicies?
        $aTables = array($table);
        if($hasIdCat){
            $aTables[] = $table . '_cat';
        }
        foreach($aTables as $table){
            $sql = 'SHOW CREATE TABLE `' . $table . '`';
            $oDB->allowUnsafeQueryOnce();
            $aRow = $oDB->fetchRow($sql, MYSQL_NUM);
            $fields = preg_replace(
                array(
                    '/^CREATE TABLE\s+`[^`]+`\s\(/si',
                    '/\)\sENGINE=.+$/si'
                ),
                array(
                    '',
                    ''
                ),
                $aRow[1]
            );
            foreach(array('PRIMARY KEY (`', 'UNIQUE KEY `', 'KEY `', 'FULLTEXT KEY `') as $key){
                $pos = mb_stripos($fields, $key);
                if(FALSE !== $pos){
                    $fields = mb_substr($fields, 0, $pos);
                }
            }

            $aTable = preg_split('/,\s+/s', $fields);

            $aTypes = array();
            foreach($aTable as $row){
                $row = trim($row);
                if(preg_match('/^`([^`]+)`\s(.+)$/', $row, $aMatches)){
                    $aTypes[$aMatches[1]] = $aMatches[2];
                }
            }

            $aMapping = array(
                // Common fields
                'urgent'             => 'sticky',
                'urgent_date'        => 'date_sticky_till',
                'public_direct_link' => 'hide_in_list',
                'date'               => 'date_created',
                'modified_date'      => 'date_modified',

                // Categpry field
                'name' => 'header',

                // Extensopns fields
                'ext_picture'       => 'ext_img',
                'ext_small_picture' => 'ext_img_small',
                'ext_popup_picture' => 'ext_img_popup',
                'disable_comments'  => 'ext_dsc_disable',
                'votes_rate'        => 'ext_rate_rate',
                'votes_count'       => 'ext_rate_count',
                'rate_opt'          => 'ext_rate_opt',
                'votes_weight'      => 'ext_rate_weight'
            );
            foreach($aMapping as $srcField => $destField){
                if(isset($aTypes[$srcField])){
                    $sql =
                        "ALTER TABLE `" . $table . "` " .
                        "CHANGE `" . $srcField . "` `" . $destField . "` " . $aTypes[$srcField];
                    $oDB->allowUnsafeQueryOnce();
                    $oDB->allowQuotesInQueryOnce();
                    $oDB->query($sql);
                }
            }
        }

        // } #CMS-11434

        // #CMS-11326 {

        $modIds = array(
            $this->sourceModId => $this->modId
        );
        if($hasIdCat){
            $modIds[$this->sourceModId . '_cat'] = $this->modId . '_cat';
        }
        $oTable = AMI::getResourceModel('modules_datasets/table');
        foreach($modIds as $srcModId => $modId){
            $oItem =
                $oTable
                ->findByFields(
                    array(
                        'module' => $modId,
                        'is_sys' => 1
                    )
                );
            if($oItem->getId()){
                // skip existing system dataset
                continue;
            }

            // copy system dataset
            $oItem =
                $oTable
                ->findByFields(
                    array(
                        'module' => $srcModId,
                        'is_sys' => 1
                    )
                );
            if($oItem->getId()){
                $oItem->module = $modId;
                $oItem->used_pages = '';
                $oItem->resetId();
                $oItem->save();
            }
        }

        // } #CMS-11326

        AMI::getSingleton('response')
            ->addStatusMessage('import_data_success', array('src' => $this->sourceModId, 'dst' => $this->modId));
    }

    /**
     * Imports module options.
     *
     * @return void
     */
    protected function importOptions(){
        /**
         * @var AMI_iDB
         */
        $oDB = AMI::getSingleton('db');

        // Get original module options
        $sql = "SELECT `value`, `big_value` FROM `cms_options` WHERE `module_name` = %s AND `name` = %s";
        $oQuery = DB_Query::getSnippet($sql)->q($this->modId)->q('options_dump');
        $aRecord = $oDB->fetchRow($oQuery);
        $aOriginalOptions = unserialize($aRecord['big_value']);

        // Get source module options
        $sql = "SELECT `value`, `big_value` FROM `cms_options` WHERE `module_name` = %s AND `name` = %s";
        $oQuery = DB_Query::getSnippet($sql)->q($this->sourceModId)->q('options_dump');
        $aRecord = $oDB->fetchRow($oQuery);
        $aOptions = unserialize($aRecord['big_value']);
        if(isset($aOptions['Options']['stop_arg_names'])){
            if(isset($aOptions['Options']['stop_arg_names']['catid'])){
                $aOptions['Options']['stop_arg_names']['catid'] =
                    str_replace($this->sourceModId, $this->modId, $aOptions['Options']['stop_arg_names']['catid']);
            }
            if(isset($aOptions['Options']['stop_arg_names']['id'])){
                $aOptions['Options']['stop_arg_names']['id'] =
                    str_replace($this->sourceModId, $this->modId, $aOptions['Options']['stop_arg_names']['id']);
            }
        }

        $aOptions['Options'] = array_merge($aOriginalOptions['Options'], $aOptions['Options']);
        if(!empty($aOptions['Options']['small_number_categories'])){
            $aOptions['Options']['small_grp_by_cat'] = TRUE;
        }

        // #CMS-11434 {

        // Rename options

        $aOptionsMap = array(
            'item_pictures'           => 'ext_img_fields',
            'col_picture_type'        => 'ext_img_list_col',
            'generate_pictures'       => 'ext_img_creatable',
            'prior_source_picture'    => 'ext_img_source',
            'picture_maxwidth'        => 'ext_img_maxwidth',
            'picture_maxheight'       => 'ext_img_maxheight',
            'popup_picture_maxwidth'  => 'ext_img_popup_maxwidth',
            'popup_picture_maxheight' => 'ext_img_popup_maxheight',
            'small_picture_maxwidth'  => 'ext_img_small_maxwidth',
            'small_picture_maxheight' => 'ext_img_small_maxheight',
            'generate_bigger_image'   => 'ext_img_create_bigger'
        );

        $aOptionsValuesMapping = array(
            'name'         => 'header',
            'date'         => 'date_created',
            'votes_rate'   => 'ext_rate_rate',
            'votes_count'  => 'ext_rate_count',
            'votes_weight' => 'ext_rate_weight'
        );

        foreach($aOptionsMap as $srcOpt => $destOpt){
            if(array_key_exists($srcOpt, $aOptions['Options'])){
                $aOptions['Options'][$destOpt] =
                    AmiExt_Image_Adm::convertOptionValue($destOpt, $aOptions['Options'][$srcOpt]);
                unset($aOptions['Options'][$srcOpt]);
            }
        }

        foreach(array_keys($aOptions['Options']) as $name){
            if(FALSE !== mb_strpos($name, 'sort_col')){
                $value = $aOptions['Options'][$name];
                if(isset($aOptionsValuesMapping[$value])){
                    $aOptions['Options'][$name] = $aOptionsValuesMapping[$value];
                }
            }
        }

        // } #CMS-11434

        if(
            isset($aOptions['Options']['form_template']) &&
            ('rating_like.tpl' === $aOptions['Options']['form_template']) &&
            $GLOBALS['Core']->IsInstalled('ext_rating')
        ){
            $aOptions['Options']['form_template'] = $GLOBALS['Core']->GetModOption('ext_rating', 'form_template');
        }

        $aRecord['big_value'] = serialize($aOptions);
        $aRecord['value'] = mb_strlen($aRecord['big_value']);
        $sql = "UPDATE `cms_options` SET `value` = %s, `big_value` = %s WHERE `module_name` = %s AND `name` = %s";
        $oQuery =
            DB_Query::getSnippet($sql)
            ->q($aRecord['value'])->q($aRecord['big_value'])->q($this->modId)->q('options_dump');
        $oDB->query($oQuery, AMI_DB::QUERY_SYSTEM);

        // Reset inheritance for instance
        $option = AMI::getOption('srv_options', 'inheritance');
        $option[$this->modId] = array();
        AMI::setOption('srv_options', 'inheritance', $option);
        AMI::saveOptions('srv_options');
        $GLOBALS['Core']->UpdateRightsVersion();

        AMI::getSingleton('response')
            ->addStatusMessage('import_options_success', array('src' => $this->sourceModId, 'dst' => $this->modId));
    }

    /**
     * Import comments.
     *
     * @return void
     */
    protected function importComments(){
        $this->_importComments();
    }

    /**
     * Recursive import of comments.
     *
     * @param  int $parentId  Parent Id
     * @return void
     * @todo   Use "transactions"
     */
    protected function _importComments($parentId = 0){
        static $aIdMapping = array(0 => 0);
        $sql = "SELECT * FROM `cms_discussion` WHERE `ext_module` = %s AND `id_parent` = %s";
        $oQuery = DB_Query::getSnippet($sql)->q($this->sourceModId)->plain($parentId);
        $oRecordset = AMI::getSingleton('db')->select($oQuery);
        foreach($oRecordset as $aRecord){
            $id = $aRecord['id'];
            $parentId = $aRecord['id_parent'];
            $aRecord['id_parent'] = $aIdMapping[$parentId];
            unset($aRecord['id']);
            $aRecord['ext_module'] = $this->modId;

            $fields = '';
            $aValues = array();
            foreach($aRecord as $field => $value){
                $fields .= '`' . $field . '`,';
                $aValues[] = trim($value);
            }
            $fields = mb_substr($fields, 0, -1);
            $sql = 'INSERT LOW_PRIORITY INTO `cms_discussion` (' . $fields . ') VALUES (%s)';
            $oQuery = DB_Query::getSnippet($sql)->implode($aValues);
            AMI::getSingleton('db')->query($oQuery);
            $insertId = AMI::getSingleton('db')->getInsertId();
            $doRecursion = !isset($aIdMapping[$id]);
            $aIdMapping[$id] = $insertId;
            if($doRecursion){
                $this->_importComments($id);
            }
        }
    }

    /**
     * Imports module templates.
     *
     * @return void
     * @todo   Use "transactions"
     */
    protected function importTemplates(){
        /**
         * @var AMI_iDB
         */
        $oDB = AMI::getSingleton('db');
        $sql =
            "SELECT `content` " .
            "FROM `cms_modules_templates` " .
            "WHERE `path` = %s AND `name` = %s AND `module` = %s AND `side` = %s AND allowed = 1";
        $oQuery =
            DB_Query::getSnippet($sql)
            ->q('templates/')->q($this->sourceModId . '.tpl')->q($this->sourceModId)->q('front');
        $oRecordset = $oDB->select($oQuery);
        $content = '';
        foreach($oRecordset as $aRecord){
            $content = $aRecord['content'];
            $content = preg_replace('/%%include_language (.*)%%/iU', '', $content);
            $content = preg_replace('/%%include_template (.*)%%/iU', '', $content);
            // $content = preg_replace('/##--(.*)--##/iU', '', $content);
            $content = str_replace("\n\r\n\r", "\n", $content);
            $aReplacement = array(
                // Extensions flags mapping (CMS-11471)
                'EXTENSION_TWIST_PREVENTION'      => 'ext_antispam_enabled',
                'EXT_TWIST_PREVENTION'            => 'ext_antispam_enabled',
                'EXTENSION_CE_PAGE_BREAK'         => 'ext_ce_page_break_enabled',
                'CE_PAGE_BREAK'                   => 'ext_ce_page_break_enabled',
                'EXTENSION_MODULES_CUSTOM_FIELDS' => 'ext_custom_fields_enabled',
                'EXT_MODULES_CUSTOM_FIELDS'       => 'ext_custom_fields_enabled',
                'EXTENSION_DISCUSSION'            => 'ext_discussion_enabled',
                'EXT_DISCUSSION'                  => 'ext_discussion_enabled',
                'EXTENSION_FORUM'                 => 'ext_discussion_enabled',
                'EXT_FORUM'                       => 'ext_discussion_enabled',
                'EXTENSION_IMAGES'                => 'ext_image_enabled',
                'EXT_IMAGES'                      => 'ext_image_enabled',
                'EXTENSION_RATING'                => 'ext_rating_enabled',
                'EXT_RATING'                      => 'ext_rating_enabled',
                'EXTENSION_RSS'                   => 'ext_rss_enabled',
                'EXT_RSS'                         => 'ext_rss_enabled',
                'EXTENSION_TAGS'                  => 'ext_tags_enabled',
                'EXT_TAGS'                        => 'ext_tags_enabled',
                'EXTENSION_RELATIONS'             => 'ext_relations_enabled',
                'EXT_RELATIONS'                   => 'ext_relations_enabled',
                'EXTENSION_ADV'                   => 'ext_adv_enabled',
                'EXT_ADV'                         => 'ext_adv_enabled',

                '##sort_name##'         => '##sort_header##',
                '##sort_date##'         => '##sort_date_created##',

                'small_picture'     => 'img_small',
                'popup_picture'     => 'img_popup',
                'picture'           => 'img',
                'show_img'          => 'show_picture',

                'num_items'         => 'num_public_items',
                '##name##'          => '##header##',
                '##script_link##'   => '##front_link##',
                "'##front_link##?'" => "'##script_link##?'",

                '##forum_'          => '##discussion_',
                '_extention'        => '_extension',

                'cat_name'          => 'cat_header',
                'cat_lname'         => 'cat_lheader',
                'cat_num_items'     => 'cat_num_public_items',

                'small_item_Hsplitter' => 'small_Hsplitter',
                'small_item_Vsplitter' => 'small_Vsplitter',

                'urgent'        => 'sticky',
                '##URGENT_CAT_LIST_' . mb_strtoupper($this->sourceModId) . '##' => '##STICKY_CAT_LIST_' . mb_strtoupper($this->modId) . '##'
            );
            if($this->sourceModId === 'articles'){
                $aReplacement += array(
                    'date'                => 'fdate',
                    'sort_fdate'          => 'sort_date',
                    'articles_item_fdate' => 'articles_item_date',
                    'flt_fdate'           => 'flt_date',
                );
            }
            if(in_array($this->sourceModId, array('news', 'blog'))){
                $content = preg_replace("/set var=\"small_row\"(.*)##announce##(.*)-->/siU", "set var=\"small_row\"$1##announce|striptags|truncate(aTruncateModOptions)##$2-->", $content);
            }
            foreach($aReplacement as $old => $new){
                $content = str_replace($old, $new, $content);
            }
            $content = preg_replace("/\(module=\"{$this->sourceModId}/siU", "(module=\"{$this->modId}", $content);
            $content = preg_replace("/set var=\"subitem_list\"(.*)##item_list##(.*)-->/siU", "set var=\"subitem_list\"$1##list##$2-->", $content);
            $content = preg_replace("/set var=\"([^\"]*)_link(.*)\"(.*)##front_link##(.*)-->/siU", "set var=\"$1_link$2\"$3##script_link##$4-->", $content);
            $content = preg_replace("/set var=\"small_header\"(.*)##script_link##(.*)-->/siU", "set var=\"small_header\"$1##front_link##$2-->", $content);
            $content = preg_replace("/set var=\"body_items\"(.*)##front_link##(.*)-->/siU", "set var=\"body_items\"$1##script_link##$2-->", $content);
            $aMatches = array();
            if(preg_match_all("/(<!--#set +(GS|GD)?var=\")(.+?)\"(\s+filter=\"(.*?)\")?(\s+value=\")(.*?)(\"\\s*-->)([\\r]?[\\n]?)/s", $content, $aMatches)){
                $vItemSplitter = '';
                $hItemSplitter = '';
                $vSmallSplitter = '';
                $hSmallSplitter = '';
                foreach($aMatches[0] as $set){
                    if(strpos($set, 'item_Vsplitter')){
                        $vItemSplitter = $set;
                    }
                    if(strpos($set, 'item_Hsplitter')){
                        $hItemSplitter = $set;
                    }
                    if(strpos($set, 'small_Vsplitter')){
                        $vSmallSplitter = $set;
                    }
                    if(strpos($set, 'small_Hsplitter')){
                        $hSmallSplitter = $set;
                    }
                }
                if(!strlen($vSmallSplitter) && strlen($vItemSplitter)){
                    $content .= ("\n" . str_replace('item_Vsplitter', 'small_Vsplitter', $vItemSplitter));
                }
                if(!strlen($hSmallSplitter) && strlen($hItemSplitter)){
                    $content .= ("\n" . str_replace('item_Hsplitter', 'small_Hsplitter', $hItemSplitter));
                }
            }
        }

        if(!strlen($content)){
            return;
        }

        $sql =
            "SELECT `id`, `content` " .
            "FROM `cms_modules_templates` " .
            "WHERE `path` = %s AND `module` = %s AND `name` = %s AND `side` = %s AND allowed = 1";
        $oQuery =
            DB_Query::getSnippet($sql)
            ->q('templates/modules/')->q($this->modId)->q($this->modId . '.tpl')->q('front');
        $aRow = $oDB->fetchRow($oQuery);
        $id = 0;
        if(is_array($aRow) && count($aRow) && isset($aRow['id'])){
            $id = (int)$aRow['id'];
        }
        if($id){
            list($hyper, $config) = AMI_ModDeclarator::getInstance()->getHyperData($this->modId);
            $includeHyper = 'templates/hyper/' . $hyper . '.tpl';
            $includeConfig = 'templates/hyper/' . $hyper . '_' . $config . '.tpl';
            $includeLng = 'templates/lang/modules/' . $this->modId. '.lng';
            $header = '';
            $header .= '%%include_template "' . $includeHyper . '"%%' . "\n";
            $header .= '%%include_template "' . $includeConfig . '"%%' . "\n";
            $header .= '%%include_language "' . $includeLng . '"%%' . "\n\n";
            $content = ($header . $content);
            $sql =
                "UPDATE `cms_modules_templates` " .
                "SET `content` = %s, content_type=%s, parsed=%s, modified=NOW() " .
                "WHERE `id` = %s";
            $oQuery = DB_Query::getSnippet($sql)->q($content)->plain(0)->q('')->q($id);
            $oDB->query($oQuery, AMI_DB::QUERY_SYSTEM);
            AMI::getSingleton('response')
                ->addStatusMessage('import_templates_success', array('src' => $this->sourceModId, 'dst' => $this->modId));
        }
    }

    /**
     * Imports module locales.
     *
     * @return void
     * @todo   Use "transactions"
     */
    protected function importLocales(){
        /**
         * @var AMI_iDB
         */
        $oDB = AMI::getSingleton('db');
        $sql =
            "SELECT `content` " .
            "FROM `cms_modules_templates_langs` " .
            "WHERE `path` = %s AND `module` = %s AND `side` = %s AND allowed = 1";
        $oQuery =
            DB_Query::getSnippet($sql)
            ->q('templates/lang/')->q($this->sourceModId)->q('front');
        $oRecordset = $oDB->select($oQuery);

        $content = '';
        foreach($oRecordset as $aRecord){
            $content = $aRecord['content'];
            $content = preg_replace('/%%include_language (.*)%%/iU', '', $content);
            $aReplacement = array(
                'urgent' => 'sticky'
            );
            foreach($aReplacement as $old => $new){
                $content = str_replace($old, $new, $content);
            }
        }

        if(!strlen($content)){
            return;
        }

        $sql =
            "SELECT `id`, `content` " .
            "FROM `cms_modules_templates_langs` " .
            "WHERE `path` = %s AND `module` = %s AND `name` = %s AND `side` = %s AND allowed = 1";
        $oQuery =
            DB_Query::getSnippet($sql)
            ->q('templates/lang/modules/')->q($this->modId)->q($this->modId . '.lng')->q('front');
        $aRow = $oDB->fetchRow($oQuery);
        $id = 0;
        if(is_array($aRow) && count($aRow) && isset($aRow['id'])){
            $id = (int)$aRow['id'];
        }
        if($id){
            list($hyper, $config) = AMI_ModDeclarator::getInstance()->getHyperData($this->modId);
            $includeLng = 'templates/lang/hyper/' . $hyper . '_' . $config . '.lng';
            $header = '';
            $header .= '%%include_language "' . $includeLng . '"%%' . "\n\n";
            $content = ($header . $content);
            $sql = "UPDATE `cms_modules_templates_langs` SET `content` = %s, modified = NOW() WHERE `id` = %s";
            $oQuery = DB_Query::getSnippet($sql)->q($content)->q($id);
            $oDB->query($oQuery, AMI_DB::QUERY_SYSTEM);
            AMI::getSingleton('response')->addStatusMessage('import_locales_success', array('src' => $this->sourceModId, 'dst' => $this->modId));
        }
    }

    /**
     * Imports module and extensions templates.
     *
     * @param  string $extModId  Extension module Id
     * @return void
     * @todo   Use "transactions"
     */
    protected function importExtTemplates($extModId){
        $tplSrc = '';
        $tplDst = '';
        $aSets = array();
        $aReplacement = array();

        switch($extModId){
            case 'ext_image':
                $tplSrc = 'common_modules_sets.tpl';
                $aSets = array('_picture');
                $tplDst = 'ext_image.tpl';
                $aReplacement = array(
                    'small_picture' => 'img_small',
                    'popup_picture' => 'img_popup',
                    'picture'       => 'img',
                    'show_img'      => 'show_picture'
                );
                break;
            case 'ext_rss':
                $tplSrc = 'ext_rss.tpl';
                $tplDst = 'ext_rss.tpl';
                break;
            case 'ext_discussion':
                $tplSrc = 'common_modules_sets.tpl';
                $aSets = array('forum_', '_comments');
                $tplDst = 'ext_discussion.tpl';
                $aReplacement = array(
                    'forum'                   => 'discussion',
                    'extention'               => 'extension',
                    'discussion_ext='         => 'forum_ext=',
                    'disabled_comments'       => 'discussion_disabled',
                    '%%discussion_disabled%%' => '%%disabled_comments%%',
                    'discussionForm'          => 'forumForm',
                    '#discussion"'            => '#forum"',
                    '##script_link##'         => '##front_link##'
                );
                break;
            case 'ext_twist_prevention':
                $tplSrc = 'captcha.tpl';
                $tplDst = 'ext_twist_prevention.tpl';
                break;
            case 'ext_tags':
                $tplSrc = 'common_modules_sets.tpl';
                $tplDst = 'ext_tags.tpl';
                $aSets = array('tag_', 'tags_');
                break;
            case 'ext_rating':
                $oDB = AMI::getSingleton('db');
                $sql =
                    "SELECT name " .
                    "FROM cms_modules_templates " .
                    "WHERE module=%s AND path=%s AND side=%s AND name LIKE %s AND allowed=1";
                $oRecordSet =
                    $oDB->select(
                        DB_Query::getSnippet($sql)->q('rating')->q('templates/')->q('front')->q('rating\_%')
                    );
                $tplSrc = array();
                $tplDst = array();
                foreach($oRecordSet as $aRecord){
                    $tplSrc[] = $aRecord['name'];
                    $tplDst[] = 'ext_' . $aRecord['name'];
                }
                $aReplacement = array(
                    "if(AMI.find('.rating')[0]." => "if(AMI.find('.rating').length && AMI.find('.rating')[0].",
                    '%%this_##module_name##%%='  => '%%this_##config_name##%%=',
                    "##submitter_link##"         => "##www_root##ami_service.php",
                    '<input type="hidden" name="id_item"' => '<input type="hidden" name="service" value="ext_rating" /><input type="hidden" name="id_item"'
                );
                break;
        }
        if($tplSrc && $tplDst){
            if(is_array($tplSrc)){
                // Run multiple templates import
                foreach($tplSrc as $i => $srcTpl){
                    $dstTpl = $tplDst[$i];
                    $this->importTplWithChanges($extModId, $srcTpl, $dstTpl, $aSets, $aReplacement);
                }
            }else{
                // Run single template import
                $this->importTplWithChanges($extModId, $tplSrc, $tplDst, $aSets, $aReplacement);
            }
            AMI::getSingleton('response')
                ->addStatusMessage('import_ext_templates_success', array('src' => $extModId));
        }
    }

    /**
     * Save option after extensions templates have been imported.
     *
     * @return void
     */
    protected function finishExtImport(){
        // Save extensions templates imported flag
        AMI::setOption("mod_manager", 'ext_templates_imported', TRUE);
        $GLOBALS['Core']->SaveOptions("mod_manager", FALSE);
    }

    /**
     * Import extensions templates with changes by set names.
     *
     * @param  string $modId         Module Id
     * @param  string $src           Source tpl name
     * @param  string $dst           Destination tpl name
     * @param  array  $aSets         Set parts to import
     * @param  array  $aReplacement  Replacement strings array
     * @return void
     * @todo   Use "transactions"
     */
    protected function importTplWithChanges($modId, $src, $dst, array $aSets, array $aReplacement){
        /**
         * @var AMI_iDB
         */
        $oDB = AMI::getSingleton('db');
        $sql =
            "SELECT `content` " .
            "FROM `cms_modules_templates` " .
            "WHERE `path` = %s AND `name` = %s AND `side` = %s";
        $oQuery =
            DB_Query::getSnippet($sql)
            ->q('templates/')->q($src)->q('front');
        $oRecordset = $oDB->select($oQuery);
        foreach($oRecordset as $aRecord){
            $content = $aRecord['content'];
            $content = preg_replace('/%%include_language (.*)%%/iU', '', $content);
            $content = preg_replace('/%%include_template (.*)%%/iU', '', $content);
            $content = str_replace("\n\r\n\r", "\n", $content);
            if(sizeof($aSets)){
                $newContent = '';
                $regexp = "/(<!--#set +(GS|GD)?var=\")(.+?)\"(\s+filter=\"(.*?)\")?(\s+value=\")(.*?)(\"\\s*-->)([\\r]?[\\n]?)/si";
                $aMatches = array();
                if(preg_match_all($regexp, $content, $aMatches)){
                    $aSetNames = $aMatches[3];
                    foreach($aSets as  $setPart){
                        foreach($aSetNames as $i => $setNames){
                            if(strpos($setNames, $setPart) !== FALSE){
                                $newContent .= $aMatches[0][$i];
                                $newContent .= "\n\n";
                            }
                        }
                    }
                }
                $content = $newContent;
            }
            foreach($aReplacement as $old => $new){
                $content = str_replace($old, $new, $content);
            }
        }
        $sql =
            "SELECT `id`, `content` " .
            "FROM `cms_modules_templates` " .
            "WHERE `path` = %s AND `name` = %s AND `side` = %s";
        $oQuery =
            DB_Query::getSnippet($sql)
            ->q('templates/modules/')->q($dst)->q('front');
        $aRow = $oDB->fetchRow($oQuery);
        $id = 0;
        if(is_array($aRow) && count($aRow) && isset($aRow['id'])){
            $id = (int)$aRow['id'];
        }
        if($id){
            list($hyper, $config) = AMI_ModDeclarator::getInstance()->getHyperData($modId);
            $includeFile = 'templates/hyper/' . $hyper . '_' . $config . '.tpl';
            if($modId == 'ext_rating'){
                $includeFile = 'templates/hyper/' . $hyper . '_' . $config . '_form.tpl';
            }
            $includeLng = 'templates/lang/modules/' . $modId. '.lng';
            $header = '';
            $header .= '%%include_template "' . $includeFile . '"%%' . "\n";
            $header .= '%%include_language "' . $includeLng . '"%%' . "\n\n";
            $content = ($header . $content);
            $sql = "UPDATE `cms_modules_templates` SET `content` = %s, modified = NOW() WHERE `id` = %s";
            $oQuery = DB_Query::getSnippet($sql)->q($content)->q($id);
            $oDB->query($oQuery, AMI_DB::QUERY_SYSTEM);
        }
    }

    /**
     * Finishes uninstallation.
     *
     * @return void
     */
    /*
    protected function finish(){
    }
    */

    /**#@-*/
}

/**
 * Common class for transaction installing/uninstailing pseudoinstance.
 *
 * @package    Package
 * @subpackage Controller
 * @since      6.0.2
 */
abstract class AMI_PseudoPackage extends AMI_Package_Manipulator{
    /**
     * Package Id
     *
     * @var string
     */
    protected $pkgId;

    /**
     * Installation mode
     *
     * @var int
     * @see AMI_iTx_Cmd::MODE_COMMON
     */
    protected $mode;

    /**
     * Package info
     *
     * @var array
     */
    protected $aPkgInfo;

    /**
     * Package metadata
     *
     * @var array
     */
    protected $oMeta;

    /**
     * Package install Id
     *
     * @var int
     */
    protected $installId;

    /**
     * Path to pseudo records file
     *
     * @var type @var string
     */
    protected $path;

    /**
     * Object to manipulate pseudo records file
     *
     * @var AMI_Storage_FS
     */
    protected $oStorage;

    /**
     * Pseudo records.
     *
     * @var array
     */
    protected $aRecords;

    /**
     * Transaction arguments
     *
     * @var AMI_Tx_Cmd_Args
     */
    protected $oArgs;

    /**
     * Initializes transactions.
     *
     * @return void
     */
    /*
    protected function initTx(){
    }
    */

    /**
     * Initialization.
     *
     * @return void
     */
    protected function init(){
        $this->path = AMI_Registry::get('path/hyper_local') . 'declaration/pseudo.php';
        $this->oStorage = AMI::getResource('storage/fs');
        $this->aRecords =
            $this->oStorage->exists($this->path)
            ? require($this->path)
            : array();
    }

    /**
     * Validates initial data.
     *
     * @return void
     * @throws AMI_Package_Exception  In case of problems.
     */
    protected function validate(){
        if(defined('TEMPLATES_FROM_DISK')){
            throw new AMI_Package_Exception(
                'Module cannot be installed when teplates/locales are read from disk',
                AMI_Package_Exception::INVALID_TEMPLATE_MODE
            );
        }

        // Validate package Id
        if(!preg_match('/^[a-z](?:[a-z\d]|_[a-z])+(\.[a-z](?:[a-z\d]|_[a-z])+)?$/', $this->pkgId)){
            throw new AMI_Package_Exception(
                "Invalid package Id '" . $this->pkgId . "'",
                AMI_Package_Exception::INVALID_PKG_ID
            );
        }

        // Validate package
        $oPkgManager = AMI_PackageManager::getInstance();
        $this->aPkgInfo = $oPkgManager->getManifest($this->pkgId);
        if(!$this->aPkgInfo){
            $aError = $oPkgManager->getError();
            throw new AMI_Package_Exception(
                "Invalid package Id or broken package '" . $this->pkgId . "': [ " .
                $aError['errorCode'] . ' ] ' . $aError['errorMessage'],
                AMI_Package_Exception::INVALID_PKG_ID
            );
        }

        // Validate meta
        $this->hypermod = $this->aPkgInfo['install'][0]['hypermodule'];
        $this->config   = $this->aPkgInfo['install'][0]['configuration'];
        $this->oMeta = AMI_Package::getMeta(
            $this->hypermod,
            $this->config
        );
        if(!is_object($this->oMeta)){
            throw new AMI_Package_Exception(
                "Missing metadata for package '" . $this->pkgId . "'",
                AMI_Package_Exception::INVALID_PKG_ID
            );
        }
    }

    /**
     * Returns module Id.
     *
     * @return string
     */
    public function getModId(){
        return 'pseudo_' . $this->oPkgCommon->installId;
    }

    /**
     * Returns package info.
     *
     * @return array
     */
    public function getPkgInfo(){
        return $this->oPkgCommon->aPkgInfo;
    }

    /**
     * Create record in mod manager history table.
     *
     * @return void
     */
    protected function logStart(){
        $this->oPkgCommon->log($this->transactionType, AMI_Package_Common::LOG_START);
        $this->modId = 'pseudo_' . $this->oPkgCommon->installId;
    }

    /**
     * Update state in record of mod manager history table.
     *
     * @return void
     * @throws AMI_Tx_Exception  In case of problems.
     */
    protected function logFinish(){
        $this->oPkgCommon->log($this->transactionType, AMI_Package_Common::LOG_FINISH);
    }

    /**
     * Saves pseudo records.
     *
     * @return void
     */
    protected function saveRecords(){
        $this->oStorage->save(
            $this->path,
            '<' . "?php\r\n\r\n// This is autogenerated file. The changes are undesirable and dangerous.\r\n\r\nreturn " .
            var_export($this->aRecords, TRUE) . ";\r\n"
        );
    }

    /**
     * Drops front and template cache.
     *
     * @return void
     */
    protected function dropCache(){
        global $Core;
        static $doUpdateRightsVersion = TRUE;

        // Clear tpls and lngs permissions and cache
        $oldModId = AMI_Registry::get('modId', false);
        AMI_Registry::set('modId', 'modules_templates');
        Hyper_AmiModulesTemplates_Service::setTemplatesPermissions();
        AMI_Registry::set('modId', 'modules_templates_langs');
        Hyper_AmiModulesTemplates_Service::setTemplatesPermissions();
        AMI_Registry::set('modId', $oldModId);

        if($doUpdateRightsVersion){
            if(isset($Core) && is_object($Core) && ($Core instanceof CMS_Core)){
                $Core->UpdateRightsVersion();
            }
            $oDB = AMI::getSingleton('db');
            $oQuery = DB_Query::getSnippet(
                "UPDATE `cms_modules_templates` " .
                "SET `parsed` = %s, `content_type` = 0"
            )->q('');
            $oQuery = DB_Query::getSnippet(
                "UPDATE `cms_modules_templates_langs` " .
                "SET `parsed` = %s, `content_type` = 0"
            )->q('');
            $oDB->query($oQuery);
            $oQuery = DB_Query::getSnippet(
                "UPDATE `cms_pages` " .
                "SET `id` = 0 " .
                "WHERE 0"
            );
            $oDB->query($oQuery);

            $doUpdateRightsVersion = FALSE;
        }
    }
}

/**
 * Transaction installing pseudoinstance (package having no instance).
 *
 * Example:
 * <code>
 * // Root script context
 *
 * $AMI_ENV_SETTINGS = array(
 *     'mode'          => 'full',
 *     'disable_cache' => TRUE
 * );
 * require_once 'ami_env.php';
 *
 * $oResponse = AMI::getSingleton('response');
 * $oResponse->start();
 *
 * $pkgId = 'ato.payment_driver_onpay';
 *
 * $oModManipulator = new AMI_PseudoPackage_Install(
 *     $pkgId,
 *     AMI_iTx_Cmd::MODE_COMMON
 * );
 *
 * try{
 *     $oModManipulator->run();
 *     // Success installation
 *     // ...
 * }catch(AMI_Exception $oExeception){
 *     // Installation failed
 *     d::w('<span style="color: red;">' . $oException->getMessage() . '</span>');
 *     d::trace($oException->getTrace());
 * }
 *
 * $oResponse->send();
 * </code>
 *
 * @package    Package
 * @subpackage Controller
 * @since      6.0.2
 */
final class AMI_PseudoPackage_Install extends AMI_PseudoPackage{
    /**
     * Transaction type for audit
     *
     * @var string
     */
    protected $transactionType = 'install';

    /**
     * Audit status for successfull transaction
     *
     * @var string
     */
    protected $auditSuccessStatus = 'added';

    /**
     * Constructor.
     *
     * @param string $pkgId  Package Id
     * @param int    $mode   Flags specifying installation mode, AMI_iTx_Cmd::MODE_*
     */
    public function __construct($pkgId, $mode = AMI_iTx_Cmd::MODE_COMMON){
        $this->pkgId = (string)$pkgId;
        $this->mode  = (int)$mode;

        $this->txName =
            "Installing package '" . $this->pkgId . "' pseudoinstance: " .
            "mode = " . $this->getModeAsString($this->mode);
        AMI_Tx::log($this->txName);

        $this->init();
        $this->validate();
        $this->oPkgCommon = new AMI_Package_Common($this->hypermod, $this->config, $this->pkgId);
        $this->addAction('logStart');
        $this->addAction('validateInstance');
        $this->addAction('install');
        $this->addAction('updateData');
        $this->addAction('logFinish');
        $this->addAction('dropCache');
    }

    /**
     * Validates multiple instances possibility.
     *
     * @return void
     */
    protected function validateInstance(){
        $oMeta = AMI_Package::getMeta($this->hypermod, $this->config);
        if($oMeta->isSingleInstance()){
            foreach($this->aRecords as $aRecord){
                if(
                    $aRecord['pkgInfo']['install'][0]['hypermodule'] == $this->hypermod &&
                    $aRecord['pkgInfo']['install'][0]['configuration'] == $this->config
                ){
                    throw new AMI_Package_Exception(
                        "Configuration '" . $this->hypermod . '/' . $this->config . "' cannot have more than 1 instance",
                        AMI_Package_Exception::INSTANCE_LIMIT
                    );
                }
            }
        }
    }

    /**
     * Executes PHP-code during installation.
     *
     * @return void
     */
    protected function install(){
        // Call install hook
        $path = $this->oPkgCommon->configPath . 'configs/' . $this->hypermod . '/' . $this->config . '/install.php';
        if(file_exists($path)){
            $this->oArgs = new AMI_Tx_Cmd_Args(get_object_vars($this));
            require $path;
        }
    }

    /**
     * Updates data in separate declaration file.
     *
     * @return void
     */
    protected function updateData(){
        $this->aRecords[] = array(
            'date'      => date('Y-m-d H:i:s'),
            'installId' => $this->oPkgCommon->installId,
            'pkgInfo'   => $this->aPkgInfo
        );
        $this->saveRecords();
    }
}

/**
 * Transaction uninstalling pseudoinstance (package having no instance).
 *
 * Example:
 * <code>
 * // Root script context
 *
 * $AMI_ENV_SETTINGS = array(
 *     'mode'          => 'full',
 *     'disable_cache' => TRUE
 * );
 * require_once 'ami_env.php';
 *
 * $oResponse = AMI::getSingleton('response');
 * $oResponse->start();
 *
 * // 'installId' key value from record at file "_local/modules/declaration/pseudo.php"
 * $installId = ...;
 *
 * $oModManipulator = new AMI_PseudoPackage_Uninstall(
 *     $installId,
 *     AMI_iTx_Cmd::MODE_COMMON
 * );
 * try{
 *     $oModManipulator->run();
 *     // Success uninstallation
 *     // ...
 * }catch(AMI_Exception $oExeception){
 *     // Uninstallation failed
 *     d::w('<span style="color: red;">' . $oException->getMessage() . '</span>');
 *     d::trace($oException->getTrace());
 * }
 *
 * $oResponse->send();
 * </code>
 *
 * @package    Package
 * @subpackage Controller
 * @since      6.0.2
 */
final class AMI_PseudoPackage_Uninstall extends AMI_PseudoPackage{
    /**
     * Transaction type for audit
     *
     * @var string
     */
    protected $transactionType = 'uninstall';

    /**
     * Audit status for successfull transaction
     *
     * @var string
     */
    protected $auditSuccessStatus = 'deleted';

    /**
     * Found pseudo record index
     *
     * @var int
     */
    protected $recordIndex;

    /**
     * Constructor.
     *
     * @param int $installId  Install Id
     * @param int $mode       Flags specifying deinstallation mode, AMI_iTx_Cmd::MODE_*
     */
    public function __construct($installId, $mode = AMI_iTx_Cmd::MODE_COMMON){
        $this->installId = (int)$installId;
        $this->modId = 'pseudo_' . $this->installId;
        $this->mode  = (int)$mode;
        /*
        $aRecords = $this->loadRecords();
        foreach($aRecords as $index => $aRecord){
            if($aRecord['installId'] == $this->installId){
                $this->pkgId = $aRecord['pkgInfo']['id'];
            }
        }
        */

        $this->init();

        $this->txName =
            "Uninstalling pseudoinstance: " .
            "installId =  " . $this->installId . ", " .
            "mode = " . $this->getModeAsString($this->mode);
        AMI_Tx::log($this->txName);

        $this->validate();
        $this->oPkgCommon = new AMI_Package_Common($this->hypermod, $this->config, $this->pkgId, $this->installId);
        $this->addAction('logStart');
        $this->addAction('uninstall');
        $this->addAction('uninstallAll');
        $this->addAction('updateData');
        $this->addAction('logFinish');
        $this->addAction('dropCache');
    }

    /**
     * Initialization.
     *
     * @return void
     */
    protected function init(){
        parent::init();

        $found = FALSE;
        foreach($this->aRecords as $index => $aRecord){
            if($aRecord['installId'] == $this->installId){
                $found = TRUE;
                break;
            }
        }
        if($found){
            $this->recordIndex = $index;
            $this->pkgId       = $aRecord['pkgInfo']['id'];
            $this->hypermod    = $aRecord['pkgInfo']['install'][0]['hypermodule'];
            $this->config      = $aRecord['pkgInfo']['install'][0]['configuration'];
        }else{
            throw new AMI_Package_Exception(
                'Module is not installed',
                AMI_Package_Exception::NOT_INSTALLED
            );
        }
    }

    /**
     * Validates initial data.
     *
     * @return void
     * @throws AMI_Package_Exception  In case of problems.
     */
    protected function validate(){
        parent::validate();

        // #CMS-11708 {

        $aDependencies = AMI_Package_Common::getDependentPackages($this->pkgId);
        if(sizeof($aDependencies) > 0){
            throw new AMI_Package_Exception(
                'Uninstall dependent extensions first',
                AMI_Package_Exception::HAS_DEPENDENCIES,
                NULL,
                array('dependencies' => implode(', ', $aDependencies))
            );
        }

        // } #CMS-11708
    }

    /**
     * Executes PHP-code during uninstallation.
     *
     * @return void
     */
    protected function uninstall(){
        // Call install hook
        $path = $this->oPkgCommon->configPath . 'configs/' . $this->hypermod . '/' . $this->config . '/uninstall.php';
        $this->oArgs = new AMI_Tx_Cmd_Args(get_object_vars($this));
        if(file_exists($path)){
            require $path;
        }
    }

    /**
     * Executes PHP-code during hardcore uninstallation.
     *
     * @return void
     */
    protected function uninstallAll(){
        // Call install hook
        $path = $this->oPkgCommon->configPath . 'configs/' . $this->hypermod . '/' . $this->config . '/uninstall_all.php';
        if(file_exists($path)){
            require $path;
        }
    }

    /**
     * Updates data in separate declaration file.
     *
     * @return void
     */
    protected function updateData(){
        unset($this->aRecords[$this->recordIndex]);
        $this->aRecords = array_values($this->aRecords);
        $this->saveRecords();
    }
}
