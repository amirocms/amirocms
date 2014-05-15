<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Service
 * @version   $Id: AMI_Service.php 49858 2014-04-16 04:38:15Z Leontiev Anton $
 * @since     5.10.0
 */

/**
 * AMI service class.
 *
 * Provides autoload and class mapping functionality.
 *
 * @package Service
 * @static
 * @since   5.10.0
 */
final class AMI_Service{
    /**
     * @amidev
     */
    const DEFAULT_MAX_LOG_SIZE = 2000000;

    const LOG_NOTE = 1;
    const LOG_WARN = 2;
    const LOG_ERR  = 3;

    /**
     * Autoload path list
     *
     * @var array
     * @see AMI_Service::addAutoloadPath()
     * @see AMI_Service::autoload()
     */
    private static $aAutoloadPath = array();

    /**
     * Class names mapping
     *
     * @var array
     * @see AMI_Service::addClassMapping()
     * @see AMI_Service::autoload()
     */
    private static $aClassMapping = array(
        'd'                     => 'AMI_Debug',
        'DB_si'                 => 'DBConnection',
        'DB_iRecordset'         => 'AMI_DB',
        'DB_Recordset'          => 'AMI_DB',
        'DB_Recordset_Column'   => 'AMI_DB',
        'AMI_iDB'               => 'AMI_DB',
        'DB_Query'              => 'AMI_DB',
        'AMI_iTemplate'         => 'AMI_Template',
        'AMI_TemplateSystem'    => 'AMI_Template',
        'CMS_CacheSimpleL1'     => 'CacheSimpleL1',
        'AMI_iModTableItemMeta' => 'AMI_ModTableItemMeta',
        'General_I18n'          => 'AMI_I18n',
        'EN_I18n'               => 'AMI_I18n',
        'RU_I18n'               => 'AMI_I18n',
        'AMI_iCaptchaImage'     => 'AMI_CaptchaImage',
        'AMI_iCaptcha'          => 'AMI_Captcha',
        'CMS_ModulesProperties' => 'CMS_ModulesSettings',
        'CMS_ModulesOptions'    => 'CMS_ModulesSettings',
        'CMS_Core'              => 'Core',
        'CMS_Cache'             => 'Cache',

        'AMI_iExtView'          => 'AMI_ExtView',

        'AMI_RequestHTTP' => 'AMI_Request',

        'AMI_ModState' => 'AMI_Mod',

        'AMI_iModServant' => 'AMI_ModServant',

        'AMI_iModComponent'         => 'AMI_ModComponent',
        'AMI_ModComponentStub'      => 'AMI_ModComponent',

        'AMI_iModTable'        => 'AMI_ModTable',
        'AMI_iModTableItem'    => 'AMI_ModTableItem',
        'AMI_iModTableList'    => 'AMI_ModTableList',
        'AMI_iModFormView'     => 'AMI_ModFormView',
        'AMI_ViewEmpty'        => 'AMI_View',
        'AMI_ModFilterAdm'     => 'AMI_ModFilter',
        'AMI_ModFilterViewAdm' => 'AMI_ModFilterView',
        'AMI_ModFormAdm'       => 'AMI_ModForm',

        'AMI_CatModule_FilterModelAdm' => 'AMI_CatModule_FilterAdm',

        'AMI_Module_TableItemModifier'      => 'AMI_ModTableItemModifier',
        'AMI_CatModule_TableItemModifier'   => 'AMI_ModTableItemModifier',
        'AMI_CatModule_Adm'                 => 'AMI_Module_Adm',
        'AMI_Module_FilterViewAdm'          => 'AMI_ModFilterView',
        'AMI_CatModule_FilterViewAdm'       => 'AMI_ModFilterView',
        'AMI_CatModule_FormAdm'             => 'AMI_Module_FormAdm',
        'AMI_CatModule_FormViewAdm'         => 'AMI_Module_FormViewAdm',
        'AMI_CatModule_ListAdm'             => 'AMI_Module_ListAdm',
        'AMI_CatModule_ListActionsAdm'      => 'AMI_Module_ListAdm',
        'AMI_CatModule_ListGroupActionsAdm' => 'AMI_Module_ListAdm',

        'AMI_iFile'                      => 'AMI_File',
        'AMI_iFileValidator'             => 'AMI_File',
        'AMI_File_LocalValidatePresence' => 'AMI_File',
        'AMI_File_Local'                 => 'AMI_File',
        'AMI_FileFactory'                => 'AMI_File',

        'AMI_ModListGroupActions' => 'AMI_ModListActions',

        // Data exchange classes
        'AMI_ExchangeDriver'  => 'AMI_DataExchange',
        'PhotoExchangeDriver' => 'AMI_DataExchange',

        'AMI_iStorage'         => 'AMI_Storage',
        'AMI_Storage_FS'       => 'AMI_Storage',
        'AMI_Storage_Template' => 'AMI_Storage',

        // Hypermodules {

        'AMI_HyperConfig_Meta' => 'AMI_Hyper_Meta',

        // } Hypermodules
        // Transactions {

        #/*
        'AMI_Tx_Exception' => 'AMI_Tx',
        'AMI_Tx_Cmd_Args'  => 'AMI_Tx',
        'AMI_iTx_Cmd'      => 'AMI_Tx',
        'AMI_Tx_Cmd'       => 'AMI_Tx',
        'AMI_iTx'          => 'AMI_Tx',
        'AMI_Tx_DB'        => 'AMI_Tx',
        'AMI_Tx_Storage'   => 'AMI_Tx',

        'AMI_Tx_Cmd_Storage_ContentModifier' => 'AMI_Tx_Cmd_Storage',

        'AMI_Package_Manipulator'     => 'AMI_Package',
        'AMI_Package_Exception'       => 'AMI_Package',
        'AMI_Package_Install'         => 'AMI_Package',
        'AMI_Package_Uninstall'       => 'AMI_Package',
        'AMI_PseudoPackage_Install'   => 'AMI_Package',
        'AMI_PseudoPackage_Uninstall' => 'AMI_Package',
        'AMI_Tx_Package_Storage'      => 'AMI_Package',
        'AMI_Package_Common'          => 'AMI_Package',
        #*/

        /*
        '' => 'AMI_Tx',
        '' => 'AMI_Tx',
        '' => 'AMI_Tx',
        '' => 'AMI_Tx',
        '' => 'AMI_Tx',
        '' => 'AMI_Package',
        '' => 'AMI_Package',
        '' => 'AMI_Package',
        '' => '',
        '' => '',
        '' => '',
        */

        /*
        'AMI_Tx_Logger'       => 'AMI_Tx',
        'AMI_Tx_CmdException' => 'AMI_Tx',
        'AMI_iTx_Cmd'         => 'AMI_Tx',
        'AMI_Tx_Cmd'          => 'AMI_Tx',
        'AMI_Tx_Cmd_Args'     => 'AMI_Tx',

        'AMI_Tx_Cmd_DB_CreateTable' => 'AMI_Tx_Cmd_DB',
        'AMI_Tx_Cmd_DB_DropTable'   => 'AMI_Tx_Cmd_DB',

        'AMI_Tx_Cmd_Storage_ContentModifier' => 'AMI_Tx_Cmd_Storage',
        'AMI_Tx_Cmd_Storage_ContentSetter'   => 'AMI_Tx_Cmd_Storage',
        'AMI_Tx_Cmd_Storage_Copier'          => 'AMI_Tx_Cmd_Storage',

        'AMI_Tx_ModException'                => 'AMI_Tx_ModManager',
        'AMI_Tx_ModInstall'                  => 'AMI_Tx_ModManager',
        'AMI_Tx_ModUninstall'                => 'AMI_Tx_ModManager',
        'AMI_Tx_ModInstallInstanceLocalCode' => 'AMI_Tx_ModManager',
        'AMI_Tx_ModUpdateCaptions'           => 'AMI_Tx_ModManager',
        'AMI_Tx_Watchdog'                    => 'AMI_Tx_ModManager',
        'AMI_Tx_ModInstallPackageLocalCode'  => 'AMI_Tx_ModManager',
        'AMI_Tx_PackageInstall'              => 'AMI_Tx_ModManager',
        'AMI_Tx_PackageUninstall'            => 'AMI_Tx_ModManager',
        'AMI_Tx_PseudoInstall'               => 'AMI_Tx_ModManager',
        'AMI_Tx_PseudoUninstall'             => 'AMI_Tx_ModManager',
        */

        // } Transactions

        'AMI_ModDetailsView' => 'AMI_ModDetails',
        'AMI_ModSpecblockListView' => 'AMI_ModSpecblockList',

        // Module table models {

        'AMI_Module_TableItem' => 'AMI_ModTableItem',

        'Hyper_AmiExt' => 'AMI_Ext',

        /*
        'Articles_TableItem' => 'Articles_Table',
        'Articles_TableList' => 'Articles_Table',

        'ArticlesCat_TableItem' => 'ArticlesCat_Table',
        'ArticlesCat_TableList' => 'ArticlesCat_Table',

        'Blog_TableItem' => 'Blog_Table',
        'Blog_TableList' => 'Blog_Table',

        'EshopCat_TableItem' => 'EshopCat_Table',
        'EshopCat_TableList' => 'EshopCat_Table',

        'EshopItem_TableItem' => 'EshopItem_Table',
        'EshopItem_TableList' => 'EshopItem_Table',

        'Files_TableItem' => 'Files_Table',
        'Files_TableList' => 'Files_Table',

        'FilesCat_TableItem' => 'FilesCat_Table',
        'FilesCat_TableList' => 'FilesCat_Table',

        'KbCat_TableItem' => 'KbCat_Table',
        'KbCat_TableList' => 'KbCat_Table',

        'KbItem_TableItem' => 'KbItem_Table',
        'KbItem_TableList' => 'KbItem_Table',

        'News_TableItem' => 'News_Table',
        'News_TableList' => 'News_Table',

        'Photoalbum_TableItem' => 'Photoalbum_Table',
        'Photoalbum_TableList' => 'Photoalbum_Table',

        'PhotoalbumCat_TableItem' => 'PhotoalbumCat_Table',
        'PhotoalbumCat_TableList' => 'PhotoalbumCat_Table',

        'PortfolioCat_TableItem' => 'PortfolioCat_Table',
        'PortfolioCat_TableList' => 'PortfolioCat_Table',

        'PortfolioItem_TableItem' => 'PortfolioItem_Table',
        'PortfolioItem_TableList' => 'PortfolioItem_Table',

        'Search_TableItem' => 'Search_Table',
        'Search_TableList' => 'Search_Table',

        'SearchHistory_TableItem' => 'SearchHistory_Table',
        'SearchHistory_TableList' => 'SearchHistory_Table',

        'Stickers_TableItem' => 'Stickers_Table',
        'Stickers_TableList' => 'Stickers_Table',

        'StickersCat_TableItem' => 'StickersCat_Table',
        'StickersCat_TableList' => 'StickersCat_Table',

        'Users_TableItem' => 'Users_Table',
        'Users_TableList' => 'Users_Table'
        */

        // } Module table models

        'PrivateMessageBodies_Table'               => 'AmiAsync_PrivateMessages_Table',
        'PrivateMessageBodies_TableItem'           => 'AmiAsync_PrivateMessages_Table',
        'PrivateMessageBodies_TableList'           => 'AmiAsync_PrivateMessages_Table',
        'PrivateMessageContacts_Table'             => 'AmiAsync_PrivateMessages_Table',
        'PrivateMessageContacts_TableItem'         => 'AmiAsync_PrivateMessages_Table',
        'PrivateMessageContacts_TableList'         => 'AmiAsync_PrivateMessages_Table',
        'PrivateMessageBodies_TableItemModifier'   => 'AmiAsync_PrivateMessages_TableItemModifier',
        'PrivateMessageContacts_TableItemModifier' => 'AmiAsync_PrivateMessages_TableItemModifier',

        'PrivateMessages_iUserHandler' => 'PrivateMessages_UserHandler',

        'AMI_SMS_Twilio' => 'AMI_SMS',
        'AMI_SMS_SMSRU'  => 'AMI_SMS'
    );

    /**
     * Warn flag for missing classes on autoload
     *
     * @var bool
     */
    private static $autoloadWarning = true;

    /**
     * Flag specifying to require class on autoloading
     *
     * @var bool
     */
    private static $autoloadFile = TRUE;

    /**
     * Debug visibility flag
     *
     * @var bool
     */
    private static $isDebugVisible = true;

    /**
     * Debug buffering flag
     *
     * @var bool
     */
    private static $isDebugBuffered = true;

    /**
     * Array of autoloaded classes and local/shared class flag.
     *
     * @var array
     * @see http://jira.cmspanel.net/browse/CMS-10991
     */
    private static $aAutoloadedClasses = array();

    /**
     * Autoload missing classes internal cache
     *
     * @var array
     */
    private static $aAutoloadMissingClasses = array();

    /**
     * Modules resources info.
     *
     * @var array
     */
    private static $aModResourceInfo = array();

    /**
     * Modules resources URLs.
     *
     * @var array
     */
    private static $aResourceURLs = array(
        'mod_icon' => array(
            'oldenv' => 'skins/{$skin}/images/{$modId}_icon.gif',
            'shared' => 'skins/{$skin}/images/hyper/{$hyper}_{$cfg}_icon.gif',
            'local'  => '_local/_admin/images/icons/{$modId}.gif'
        ),
        'mod_specblock' => array(
            'oldenv' => 'skins/{$skin}/images/specblocks/{$locale}/spec_{$block}.gif',
            'shared' => 'skins/{$skin}/images/hyper/{$hyper}_{$cfg}{$entity}_{$locale}.gif',
            'local'  => '_local/_admin/images/icons/{$block}_specblock_{$locale}.gif'
        )
        /*
        ,
        'list_action' => array(
            'oldenv' => '',
            'shared' => '',
            'local'  => '_local/_admin/images/{$modId}/{$entity}.png'
        )
        */
    );

    /**
     * Modules resources URLs cache.
     *
     * @var array
     */
    private static $aResourceURLsCache = array();

    /**
     * Scope for parsing module resource URLs
     *
     * @var array
     */
    private static $aScope;

    /**
     * User defined handlers.
     *
     * @var array
     * @see https://jira.cmspanel.net:8443/browse/CMS-11622
     */
    // private static $aUserHandlers = array();

    /**
     * Adds path to PHP class autoload.
     *
     * Let we have plugin folder "_local/plugins_distr/<i>my_plugin</i>/code/" containing files with class descriptions:<br />
     *
     * Example:
     * <code>
     * // "_local/plugins_distr/my_plugin/code/Foo1.php"
     * class Foo1{
     *     // ...
     * }
     *
     * // "_local/plugins_distr/my_plugin/code/Foo2.php"
     * class Foo2{
     *     // ...
     * }
     *
     * // "_local/plugins_distr/my_plugin/code/file.php"
     *
     * // After
     * AMI_Service::addAutoloadPath('_local/plugins_distr/my_plugin/code/');
     * // we can create objects
     * $oObject1 = new Foo1;
     * $oObject2 = new Foo2;
     * // instead of
     * require_once 'Foo1.php'
     * $oObject1 = new Foo1;
     * require_once 'Foo2.php'
     * $oObject2 = new Foo2;
     * </code>
     *
     * @param  string $path  Path
     * @return void
     */
    public static function addAutoloadPath($path){
        self::$aAutoloadPath[] = str_replace('\\', '/', realpath((string)$path)) . '/';
        // @todo: avoid hack
        if(empty(self::$aClassMapping['gui'])){
            self::$aClassMapping['gui'] = $GLOBALS['_SIDE'] === 'front' ? 'guiFront' : 'guiAdmin';
        }
    }

    /**
     * Adds collation between class name and its file name for class autoloading.
     *
     * Example:
     * <code>
     * // Let our file "_local/plugins_distr/my_plugin/code/Foo.php" contains three classes:
     * class Foo1{
     *     // ...
     * }
     *
     * class Foo2{
     *     // ...
     * }
     *
     * class Foo3{
     *     // ...
     * }
     *
     * // and we need to autoload these classes from other file "_local/plugins_distr/my_plugin/code/file.php".
     * // Than we should write next code:
     * AMI_Service::addAutoloadPath('_local/plugins_distr/my_plugin/code/');
     * AMI_Service::addClassMapping(array(
     *     'Foo1' => 'Foo',
     *     'Foo2' => 'Foo',
     *     'Foo3' => 'Foo'
     * ));
     * // After setting class mapping we can write
     * $oObject1 = new Foo1();
     * $oObject1 = new Foo2();
     * $oObject1 = new Foo3();
     * // instead of
     * require_once 'Foo.php'
     * $oObject1 = new Foo1;
     * require_once 'Foo.php'
     * $oObject2 = new Foo2;
     * require_once 'Foo.php'
     * $oObject3 = new Foo3;
     * </code>
     *
     * @param  array $aMapping  Mapping
     * @return void
     */
    public static function addClassMapping(array $aMapping){
        self::$aClassMapping = array_merge(self::$aClassMapping, $aMapping);
    }

    /**
     * Resets autoload class state.
     *
     * @param  string $class  Class name
     * @return void
     * @amidev Temporary?
     */
    public static function resetAutoloadState($class){
        unset(self::$aAutoloadMissingClasses[$class]);
    }

    /**
     * Set flag specifying to require class on autoloading.
     *
     * @param  bool $autoload  Flag specifying to require class on autoloading
     * @return bool  Previous state
     * @amidev Temporary?
     */
    public static function setAutoloadFile($autoload){
        $state = self::$autoloadFile;
        self::$autoloadFile = (bool)$autoload;

        return $state;
    }

    /**
     * Autoload handler.
     *
     * @param  string $class  Class name
     * @return bool  TRUE if class exists
     * @amidev Temporary?
     */
    public static function autoload($class){
        $aAutoloadPath = self::$aAutoloadPath;
        if(isset(self::$aAutoloadMissingClasses[$class])){
            if(self::$aAutoloadMissingClasses[$class] === sizeof($aAutoloadPath)){
                if(self::$autoloadWarning){
                    // Class not found and autoload path array was not changed
                   trigger_error('Class ' . $class . ' not found', E_USER_WARNING);
                }

                return FALSE;
            }
            $aAutoloadPath = array_slice($aAutoloadPath, self::$aAutoloadMissingClasses[$class]);
        }

        $file = (empty(self::$aClassMapping[$class]) ? $class : self::$aClassMapping[$class]) . '.php';
        foreach($aAutoloadPath as $path){
            if(is_file($path . $file)){
                self::$aAutoloadedClasses[$class] = array(
                    mb_strpos($path, $GLOBALS['ROOT_PATH'] . '_local/') !== 0,
                    $file
                );
                // Class found
                unset(self::$aAutoloadMissingClasses[$class]);
                if(self::$autoloadFile){
                    require_once $path . $file;
                }

                return TRUE;
            }
        }
        if(empty($GLOBALS['sys']['disable_user_scripts'])){
            // Check plugins directory for specified modId
            $modId = AMI::getModId($class);
            if($modId){
                $path = AMI::getPluginPath($modId, FALSE);
                if(!is_null($path)){
                    $path .= 'code/' . $file;
                    if(is_file($path)){
                        // Class found
                        self::$aAutoloadedClasses[$class] = array(FALSE, $path);
                        unset(self::$aAutoloadMissingClasses[$class]);
                        if(self::$autoloadFile){
                            require_once $path;
                        }

                        return TRUE;
                    }
                }
            }
        }
        if(self::$autoloadWarning){
            trigger_error('Class ' . $class . ' not found', E_USER_WARNING);
            // $e = new Exception;self::logMessage($e->getTraceAsString());###
        }
        self::$aAutoloadMissingClasses[$class] = sizeof(self::$aAutoloadPath);

        return FALSE;
    }

    /*
    // autoloadDebug
    public static function autoload($class){
        static $seekQtyTotal = 0, $includedFilesTotal = 0;

        // Flag specifying to output entry point, FALSE by default
        $debugOnEntryPoint = FALSE;
        // Flag specifying to output seek path, FALSE by default
        $printSeekPath = FALSE;

        if($debugOnEntryPoint && class_exists('AMI_Debug', FALSE)){
             d::w('--- AMI_Service::autoloadDebug(' . $class . ") ---<br />\n");
         }

        $time = microtime(TRUE);
        $seekTime = 0;
        $loadTime = 0;

        $aAutoloadPath = self::$aAutoloadPath;
        if(isset(self::$aAutoloadMissingClasses[$class])){
            if(self::$aAutoloadMissingClasses[$class] === sizeof($aAutoloadPath)){
                if(self::$autoloadWarning){
                    trigger_error('Class ' . $class . ' not found', E_USER_WARNING);
                }

                $time = microtime(TRUE) - $time;
                self::autoloadDebugOutput($time, $seekTime, $seekQtyTotal, $includedFilesTotal, $loadTime, $class);
                return;
            }
            $aAutoloadPath = array_slice($aAutoloadPath, self::$aAutoloadMissingClasses[$class]);
        }
        $file = (empty(self::$aClassMapping[$class]) ? $class : self::$aClassMapping[$class]) . '.php';
        foreach($aAutoloadPath as $path){
            $seekQtyTotal++;
            $seekTime = microtime(TRUE);
            $e = is_file($path . $file);
            # $e = @include_once($path . $file);
            # if($e){
            #     var_dump($path . $file);var_dump($e);die;###
            # }
            $seekTime = microtime(TRUE) - $seekTime;
            if($printSeekPath && class_exists('AMI_Debug', FALSE)){
                d::w(($e ? '[+]' : '[-]') . " seek in '" . $path . $file . "'<br />\n");
            }

            if($e){
                self::$aAutoloadedClasses[$class] = array(
                    mb_strpos($path, $GLOBALS['ROOT_PATH'] . '_local/') !== 0,
                    $file
                );

                $loadTime = microtime(TRUE);

                require_once $path . $file;

                $loadTime = microtime(TRUE) - $loadTime;
                $includedFilesTotal++;

                unset(self::$aAutoloadMissingClasses[$class]);

                $time = microtime(TRUE) - $time;
                self::autoloadDebugOutput($time, $seekTime, $seekQtyTotal, $includedFilesTotal, $loadTime, $class);

                return;
            }
        }

        if(empty($GLOBALS['sys']['disable_user_scripts'])){
            // Check plugins directory for specified modId
            $modId = AMI::getModId($class);
            if($modId){

                $seekQtyTotal++;
                $seekTime = microtime(TRUE);

                $path = AMI::getPluginPath($modId, FALSE);

                $seekTime = microtime(TRUE) - $seekTime;

                d::w((is_null($path) ? '[-]' : '[+]') . " seek in '" . $path . $file . "'<br />\n");
                if(!is_null($path)){
                    $path .= ('code/' . $file);

                    $seekQtyTotal++;
                    $st = microtime(TRUE);
                    $e = is_file($path);
                    $seekTime = microtime(TRUE) - $st;

                    if($e){
                        self::$aAutoloadedClasses[$class] = array(FALSE, $path);

                        $loadTime = microtime(TRUE);

                        require_once $path;

                        $loadTime = microtime(TRUE) - $loadTime;

                        unset(self::$aAutoloadMissingClasses[$class]);

                        $time = microtime(TRUE) - $time;
                        $includedFilesTotal++;
                        self::autoloadDebugOutput($time, $seekTime, $seekQtyTotal, $includedFilesTotal, $loadTime, $class);

                        return;
                    }
                }
            }
        }
        if(self::$autoloadWarning){
            trigger_error('Class ' . $class . ' not found', E_USER_WARNING);
        }
        self::$aAutoloadMissingClasses[$class] = sizeof(self::$aAutoloadPath);

        $time = microtime(TRUE) - $time;
        self::autoloadDebugOutput($time, $seekTime, $seekQtyTotal, $includedFilesTotal, $loadTime, $class);
    }

    public static function autoloadDebugOutput($time, $seekTime, $seekQtyTotal, $includedFilesTotal, $loadTime, $class){
        static $iteration = 0, $timeTotal = 0, $seekTimeTotal = 0, $lastSeekQtyTotal = 0, $lastIncludedFilesTotal = 0, $loadTimeTotal = 0, $lastFoldersToSeekQty = 0;

        // Flag specifying to output only total values, FALSE by default
        $isTotalMode = FALSE;

        $iteration++;
        $timeTotal += $time;
        $seekTimeTotal += $seekTime;
        $loadTimeTotal += $loadTime;
        $folders = sizeof(self::$aAutoloadPath);

        if(class_exists('AMI_Debug', FALSE)){
            $aFMT = array(
                'timeTotal'     => $timeTotal,
                'time'          => $time,
                'seekTimeTotal' => $seekTimeTotal,
                'seekTime'      => $seekTime,
                'loadTimeTotal' => $loadTimeTotal,
                'loadTime'      => $loadTime
            );
            foreach(array_keys($aFMT) as $k){
                $aFMT[$k] = number_format($aFMT[$k], 6, '.', '');
            }
            d::w(
                '# ' . sprintf('%03d', $iteration) . ' ::: ' .
                'folders: ' . $folders . ($isTotalMode ? '' : ' (+' . ($folders - $lastFoldersToSeekQty) . ')') . ', ' .
                'time: ' . $aFMT['timeTotal'] . ($isTotalMode ? '' : ' (+' . $aFMT['time'] . ')') . ', ' .
                'total seek qty: ' . sprintf('%03d', $seekQtyTotal + $includedFilesTotal) . ($isTotalMode ? '' : ' (+' . sprintf('%03d', $includedFilesTotal + $seekQtyTotal - $lastSeekQtyTotal - $lastIncludedFilesTotal) . ')') . ', ' .
                'seek time: ' . $aFMT['seekTimeTotal'] . ($isTotalMode ? '' : ' (+' . $aFMT['seekTime'] . ')') . ', ' .
                'seek qty: ' . sprintf('%03d', $seekQtyTotal) . ($isTotalMode ? '' : ' (+' . sprintf('%03d', $seekQtyTotal - $lastSeekQtyTotal) . ')') . ', ' .
                'load qty: ' . sprintf('%03d', $includedFilesTotal) . ($isTotalMode ? '' : ' (+' . sprintf('%03d', $includedFilesTotal - $lastIncludedFilesTotal) . ')') . ', ' .
                'load time: ' . $aFMT['loadTimeTotal'] . ($isTotalMode ? '' : ' (+' . $aFMT['loadTime'] . '), ' . 'class: ' . $class) . "<br />\n"
            );
        }
        $lastFoldersToSeekQty = $folders;
        $lastSeekQtyTotal = $seekQtyTotal;
        $lastIncludedFilesTotal = $includedFilesTotal;
    }

    */

    /**
     * Sets warn flag for missing classes on autoload.
     *
     * @param  bool $doWarn  Warn missing classes on autoload
     * @return void
     * @amidev
     */
    public static function setAutoloadWarning($doWarn){
        self::$autoloadWarning = (bool)$doWarn;
    }

    /**
     * Returns shared/local flag and file path for autoloaded class.
     *
     * @param  string $class  Autoloaded class
     * @return array|null
     * @amidev
     */
    public static function getClassInfo($class){
        return
            isset(self::$aAutoloadedClasses[$class])
                ? self::$aAutoloadedClasses[$class]
                : null;
    }

    /**
     * Logs message.
     *
     * @param  string $message     Message
     * @param  string $path        Path to log file
     * @param  int    $maxLogSize  Max log file size, used for rotation
     * @return void
     */
    public static function log($message, $path = '', $maxLogSize = self::DEFAULT_MAX_LOG_SIZE){
        $message = '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
        clearstatcache();
        if(
            $maxLogSize > 0 && @file_exists($path) &&
            @filesize($path) >= $maxLogSize
        ){
            $backup = $path . '.bak';
            @unlink($backup);
            @rename($path, $backup);
        }
        @file_put_contents($path, $message, FILE_APPEND);
        @chmod($path, 0666);
    }

    /**
     * Returns CMS version.
     *
     * @return array
     * @since  6.0.2
     */
    public static function getVersion(){
        require_once AMI_Registry::get('path/host') . '_shared/code/const/init_simple.php';

        return $GLOBALS['VERSIONS'];
    }

    /**
     * Error handler.
     *
     * Facade software engineering design pattern behaviour.
     *
     * @param  int    $code      Code
     * @param  string $message   Message
     * @param  string $file      File
     * @param  int    $line      Line
     * @param  array  $aContext  Context
     * @param  array  $aTrace    Force trace, used in AMI_Service::handleException
     * @return void
     * @amidev
     */
    public static function handleError($code, $message, $file = '', $line = 0, array $aContext = array(), array $aTrace = null){
        if(error_reporting() & $code){
            AMI_ErrorHandler::getInstance()->handleError($code, $message, $file, $line, $aTrace);
        }
    }

    /**
     * Handles PHP fatal errors.
     *
     * @return void
     * @amidev
     */
    public static function handleFatalError(){
        $aError = error_get_last();
        if($aError['type'] === E_ERROR){
            // ob_clean();
            self::handleError(E_ERROR, $aError['message'], $aError['file'], $aError['line']);
        }
    }

    /**
     * Exception handler.
     *
     * @param  Exception $e  Exception
     * @return void
     * @amidev
     */
    public static function handleException(Exception $e){
        if(version_compare(PHP_VERSION, '5.3.0', '>=')){
            do{
                if(
                    method_exists($e, 'getPrevious') &&
                    $e->getPrevious()
                ){
                    d::w(
                        '<br /><b style="color: red;">Uncaught exception:</b> ' .
                        "['" . $e->getMessage() . "'] [File: '" . $e->getFile() . "'] [Line: " . $e->getLine() . ']'
                    );
                    d::trace($e->getTrace());
                    $e = $e->getPrevious();
                }else{
                    break;
                }
            }while(TRUE);
        }
        self::handleError(E_USER_ERROR, 'Uncaught exception: ' . $e->getMessage(), $e->getFile(), $e->getLine(), array(), $e->getTrace());
    }

    /**
     * Hides debug output.
     *
     * @return void
     * @amidev
     */
    public static function hideDebug(){
        self::$isDebugVisible = false;
    }

    /**
     * Sets debug output.
     *
     * @param  bool $debug  Debug output flag
     * @return bool Previous debug output flag
     * @amidev
     */
    public static function setDebug($debug){
        $previous = self::$isDebugVisible;
        self::$isDebugVisible = (bool)$debug;
        return $previous;
    }

    /**
     * Manages debug output by GET parameter.
     *
     * Useful when you have shared IP address and need to hide debug from site visitors having the same IP address.<br />
     * You can add example code to the file "_local/common_functions.php" replacing 'debug' by yor own GET parameter.<br /><br />
     *
     * Example:
     * <code>
     * AMI_Service::debugByRequest('debug');
     * </code>
     *
     * <pre>
     * http://cms.my/?debug=on - turn on bebug output
     * http://cms.my/?debug=off - turn off bebug output
     * </pre>
     *
     * @param  string $requestName  GET parameter name
     * @return void
     */
    public static function debugByRequest($requestName){
        if(isset($_GET[$requestName])){
            if($_GET[$requestName] === 'on'){
                setCookie($requestName, 1, time() + 3600 * 24 * 365);
                $_COOKIE[$requestName] = 1;
            }else{
                setCookie($requestName, 0, time() - 3600);
                unset($_COOKIE[$requestName]);
            }
        }
        if(empty($_COOKIE[$requestName])){
            self::hideDebug();
        }
    }

    /**
     * Sets debug buffering flag.
     *
     * @param  bool $isBuffered  Debug buffering flag
     * @return void
     * @amidev
     */
    public static function setDebugBuffering($isBuffered){
        self::$isDebugBuffered = (bool)$isBuffered;
    }

    /**
     * Returns debug visibility flag.
     *
     * @return bool
     * @amidev
     */
    public static function isDebugVisible(){
        return self::$isDebugVisible;
    }

    /**
     * Returns debug buffering flag.
     *
     * @return bool
     * @amidev
     */
    public static function isDebugBuffered(){
        return self::$isDebugBuffered;
    }

    /**
     * Returns debug output availability.
     *
     * @return bool
     * @amidev
     */
    public static function isDebugSkipped(){
        return !self::$isDebugVisible || empty($GLOBALS['sys']['err']['extdeb']);
    }

    /**
     * Returns environment info.
     *
     * @param  string $separator  Environment variables separator
     * @return string
     * @todo   Find out right place for this method
     * @amidev
     */
    public static function getEnvInfo($separator = ' '){
        $res = "PHP_SELF=('" . $_SERVER['PHP_SELF'] . "') . " . $separator;
        foreach(array(
            'REMOTE_ADDR', 'HTTP_USER_AGENT', 'PATH_TRANSLATED', 'REQUEST_METHOD', 'REQUEST_URI', 'HTTP_REFERER'
        ) as $name){
            $res .= $name . "=('" . getenv($name) . "')" . $separator;
        }
        return $res;
    }

    /**
     * Returns old style microtimes difference.
     *
     * @param  mixed $a  Microtime 1
     * @param  mixed $b  Microtime 2
     * @return float
     * @amidev
     */
    public static function microtimeDiff($a, $b){
        global $_current_timestamp;
        list($aMicro, $aInt) = explode(' ', $a);
        list($bMicro, $bInt) = explode(' ', $b);
        $a = (float)($aMicro) + (float)($aInt - $_current_timestamp);
        $b = (float)($bMicro) + (float)($bInt - $_current_timestamp);
        return abs($b - $a);
    }

    /**
     * Returns simple bench info string.
     *
     * @param  array $aBench     Bench array
     * @param  bool  $bTextView  Text/HTML view flag
     * @param  float $sleepTime  Sleep time, sec
     * @return string
     * @todo   Implement regular benching
     * @amidev
     */
    public static function getSimpleBenchInfo(array $aBench, $bTextView = false, $sleepTime = 0){
        global $_total_qtime, $_total_queries, $_total_ftime, $_total_fqueries;

        $timeTotal = number_format(self::microtimeDiff($aBench['_bstart'], microtime()) - $sleepTime, 6, '.', '');
        $timeDB = number_format($_total_qtime, 6, '.', '');
        $timeDBFetch = number_format($_total_ftime, 6, '.', '');
        $timePHP = number_format($timeTotal - $timeDB, 6, '.', '');
        $timeDBQueries = $timeDB - $timeDBFetch;
        $res =
            "\n<br>\n<b><font color=blue size=2>Script total = ". $timeTotal .
            " sec. </font> [MySQL time: Total = " . $timeDB .
            " sec.</b> Queries = " . $timeDBQueries . " sec. [" .
            $_total_queries . " times]  Fetch = ". $timeDBFetch . " sec [" .
            $_total_fqueries . " times]<b>] [PHP time total = " . $timePHP . " sec.]</b>";
        if($sleepTime){
            $res .= ' sleep: ' . number_format($sleepTime, 6, '.', '') . ' sec.';
        }
        if(function_exists('memory_get_peak_usage')){
            $res .= ' peak: ' . round(memory_get_peak_usage() / 1048576, 2);
        }
        $res .= ' files: ' . sizeof(get_included_files());
        if($bTextView){
            $res = str_replace("\n", '', strip_tags($res));
        }
        return $res;
    }

    /**
     * Merges '_local/config.ini.php' and db stored config.
     *
     * Loads cache config on front.
     *
     * @param  array $aConfigRow  Row read from DB.
     * @return void
     * @amidev
     */
    public static function mergeConfig(array $aConfigRow = null){
        global $CONNECT_OPTIONS, $sys;
        $aDefaults = array (
            'admin_compression_level' => 6,
            'front_compression_level' => 4,
            'compression_method'      => 'handler',
            'cache_frontside'         => true,
            'disable_cache_warn'      => false,
            'cache_storage_size'      => 200, // #CMS-11718
            'cache_expire_period'     => '+6 month',
            'time_zone'   => 0,
            'source'     => 0,
            'messages'   => 0,
            'store'      => 1,
            'env'        => 1,
            'extdeb'     => 0,
            'read_templates_from_disk' => false,
            'cachedeb'   => 0,
            'email'      => 'reports.dev@locmail.amiro.ru'
        );

        $aMapping = array();

        self::mapConfig(
            array(
                'admin_compression_level', 'front_compression_level', 'compression_method',
                'cache_frontside', 'disable_cache_warn', 'cache_storage_size', 'cache_expire_period'
            ),
            $CONNECT_OPTIONS,
            $aMapping
        );
        self::mapConfig(
            array(
                'source', 'messages', 'store', 'env', 'extdeb', 'read_templates_from_disk',
                'cachedeb', 'email', 'debug_ips'
            ),
            $sys['err'],
            $aMapping
        );

        $aMapping['time_zone'] = &$sys['time_zone'];
        $aMapping['session_no_ip_bind'] = &$sys['session_no_ip_bind'];
        $aMapping['session_adm_no_ip_bind'] = &$sys['session_adm_no_ip_bind'];
        $aDump = $aDefaults;
        if(!empty($aConfigRow)){
            if(mb_strlen($aConfigRow['big_value']) == $aConfigRow['value']){
                $aDump = unserialize($aConfigRow['big_value']);
                $aDump = $aDump['Options'] + $aDefaults;
                if(isset($sys['err']['debug_ips']) && isset($aDump['debug_ips'])){
                    $sys['err']['debug_ips'] += $aDump['debug_ips'];
                }
            }
        }
        foreach(array_keys($aDump) as $option){
            if(array_key_exists($option, $aMapping) && !isset($aMapping[$option]) && !is_null($aDump[$option])){
                $aMapping[$option] = $aDump[$option];
            }
        }
        if($sys['time_zone']){
            date_default_timezone_set('Etc/GMT' . ($aMapping['time_zone'] > 0 ? '-' : '+') . abs($aMapping['time_zone']));
        }
        if(isset($sys['err']['debug_ips'][$_SERVER['REMOTE_ADDR']])){
            $debugOptions = $sys['err']['debug_ips'][$_SERVER['REMOTE_ADDR']];
            if(mb_strpos($debugOptions, 'show_bench') !== false){
                $sys['err']['show_bench'] = 1;
            }
            if(mb_strpos($debugOptions, 'cachedeb') !== false){
                $sys['err']['cachedeb'] = 1;
            }
            if(mb_strpos($debugOptions, 'disable_cache_frontside') !== false && $CONNECT_OPTIONS['cache_frontside']){
                $CONNECT_OPTIONS['cache_disabled_forced'] = true;
                $CONNECT_OPTIONS['cache_frontside'] = false;
            }
            if(preg_match('/extdeb_(\d+)/', $debugOptions, $aMatches)){
                $sys['err']['extdeb'] = (int)$aMatches[1];
            }
        }else{
            $sys['err']['messages'] = FALSE;
        }
        $CONNECT_OPTIONS['cache_frontside'] =
            $CONNECT_OPTIONS['cache_frontside'] && !empty($GLOBALS['enable_cache'])
            ? 'ON' : 'OFF';

        self::$isDebugVisible = self::$isDebugVisible && ($sys['err']['extdeb'] > 0);
    }

    /**
     * Returns module resources info.
     *
     * @param  string $modId  Module Id
     * @return array
     * @amidev Temporary?
     */
    public static function getModResourceInfo($modId, $block = ''){
        $hash = $modId . '.' . $block;
        if(!isset(self::$aModResourceInfo[$hash])){
            $aInfo = array();
            $isPlugin = 0 === mb_strpos($modId, 'plugin_');
            if($isPlugin){
                $modId = AMI::getOption($modId, 'plugin_id');
            }
            $oDeclarator = AMI_ModDeclarator::getInstance();
            $isRegistered = $oDeclarator->isRegistered($modId);
            if($isRegistered && !is_null($oDeclarator->getParent($modId))){
                return $aInfo;
            }
            $resId = $modId . '/module/controller/';
            if(
                $block &&
                $isRegistered &&
                '0' !== (string)$oDeclarator->getAttr($modId, 'id_install', FALSE)
            ){
                $resId .= 'frn';
            }else{
                $resId .= 'adm';
            }
            unset($oDeclarator);
            if($isPlugin || AMI::isResource($resId, FALSE)){
                $aInfo['resMode'] = $isPlugin ? 'local' : 'shared';
                if($isPlugin){
                    $aInfo['force'] = 1;
                }else{
                    // Loockup resource: front specblock controller for specblock,
                    // admin module controller for module icon
                    $class = AMI::getResourceClass($resId);
                    $aClassInfo = self::getClassInfo($class);
                    if(is_array($aClassInfo) && !$aClassInfo[0]){
                        $aInfo['resMode'] = 'local';
                    }else{
                        list(
                            $aInfo['hyper'],
                            $aInfo['cfg']
                        ) = AMI_ModDeclarator::getInstance()->getHyperData($modId);
                    }
                    if('shared' === $aInfo['resMode']){
                        self::$aScope = array(
                            'modId'  => $modId,
                            'block'  => '' === $block ? $modId : $block,
                            'locale' => AMI_Registry::get('lang'),
                            'skin'   => $GLOBALS['CURRENT_SKIN']
                        );
                        $url =
                            preg_replace_callback(
                                '/\{\$(.*?)\}/',
                                array('AMI_Service', '_getScopeVariable'),
                                self::$aResourceURLs[$block ? 'mod_specblock' : 'mod_icon']['oldenv']
                            );
                        $path = $GLOBALS['DEFAULT_INCLUDES_PATH'] . '../' . $url;
                        self::$aScope = NULL;
                        // d::vd(file_exists($path), "{$modId}/{$block}: {$path}");###
                        if(file_exists($path)){
                            $aInfo['force'] = 1;
                        }
                    }
                }
            }
            self::$aModResourceInfo[$hash] = $aInfo;
        }

        return self::$aModResourceInfo[$hash];
    }

    /**
     * Resets modules resources cache.
     *
     * @return void
     * @amidev Temporary?
     */
    public static function resetModResourceInfoCache(){
        self::$aModResourceInfo = array();
    }

    /**
     * Returns module resource URL.
     *
     * @param  string $type
     * @param  array  $aScope
     * @return string
     * @amidev Temporary?
     */
    public static function getModResourceURL($type, array $aScope){
        $hash = $type . md5(implode('|', $aScope));

        if(!isset(self::$aResourceURLsCache[$hash])){
            $modId = $aScope['modId'];
            $isPlugin = 0 === mb_strpos($modId, 'plugin_');
            if($isPlugin){
                $modId = AMI::getOption($modId, 'plugin_id');
                $aScope['block'] = 'small_plugin';
            }
            $aModInfo = self::getModResourceInfo(
                $aScope['modId'],
                isset($aScope['block']) ? $aScope['block'] : ''
            );
            $isPseudo = 0 === mb_strpos($aScope['modId'], 'pseudo_');
            if($isPseudo){
                $resMode = 'local';
            }else{
                $resMode = isset($aModInfo['resMode']) ? $aModInfo['resMode'] : 'oldenv';
            }

            self::$aScope = array('skin' => $GLOBALS['CURRENT_SKIN']) + $aScope + $aModInfo;
            if(!empty($aModInfo['force'])){
                $resMode = 'oldenv';
            }
            if(!sizeof($aModInfo) && 'mod_icon' === $type){
                if(!$isPseudo){
                    $resMode = 'oldenv';
                }
                $path =
                    (
                        $isPseudo
                            ? AMI_Registry::get('path/root')
                            : $GLOBALS['DEFAULT_INCLUDES_PATH'] . '../'
                    ) .
                    preg_replace_callback(
                        '/\{\$(.*?)\}/',
                        array('AMI_Service', '_getScopeVariable'),
                        self::$aResourceURLs[$type][$resMode]
                    );
                if(!file_exists($path)){
                    self::$aScope['modId'] = '-';
                    $resMode = 'oldenv';
                }
            }
            $url =
                ('local' !== $resMode ? $GLOBALS['ADMIN_PATH_WWW'] : AMI_Registry::get('path/www_root')) .
                preg_replace_callback(
                    '/\{\$(.*?)\}/',
                    array('AMI_Service', '_getScopeVariable'),
                    self::$aResourceURLs[$type][$resMode]
                );
            self::$aScope = NULL;
            self::$aResourceURLsCache[$hash] = $url;
        }

        return self::$aResourceURLsCache[$hash];
    }

    /**
     * Loads user defined handlers.
     *
     * @return void
     * @see    https://jira.cmspanel.net:8443/browse/CMS-11622
     * @amidev
     */
    /*
    public static function loadUserHandlers(){
        $path = AMI_Registry::get('path/root') . '_local/modules/declaration/handlers.php';
        if(empty($GLOBALS['sys']['disable_user_scripts']) && file_exists($path)){
            self::$aUserHandlers = require $path;
        }
    }
    */

    /**
     * Initializes user defined handlers.
     *
     * @param  string $type  Handlers type, 'common' | 'admin' | 'front'
     * @return void
     * @see    https://jira.cmspanel.net:8443/browse/CMS-11622
     * @amidev
     */
    /*
    public static function initUserHandlers($type){
        if(!empty(self::$aUserHandlers[$type])){
            foreach(self::$aUserHandlers[$type] as $aHandler){
                $resId = $aHandler['handler'][0];
                $class = AMI::getResourceClass($resId, FALSE);
                if($class){
                    $aHandler['handler'][0] = $class;
                    $aHandler += array(
                        'handlerModId' => AMI_Event::MOD_ANY,
                        'priority'     => AMI_Event::PRIORITY_DEFAULT
                    );
                    AMI_Event::addHandler(
                        $aHandler['name'],
                        $aHandler['handler'],
                        $aHandler['handlerModId'],
                        $aHandler['priority']
                    );
                }
            }
        }
    }
    */

    /**
     * Returns valriable value from runtime scope.
     *
     * @param  array $aMatches  Matches for regular expression of variable parsing
     * @return string
     */
    private static function _getScopeVariable(array $aMatches){
        return
            isset(self::$aScope[$aMatches[1]])
            ? self::$aScope[$aMatches[1]]
            : '';
    }

    /**
     * Config mapping.
     *
     * @param  array $aKeys      Keys to map
     * @param  array &$aSource   Source array
     * @param  array &$aMapping  Mapping array
     * @return void
     */
    private static function mapConfig(array $aKeys, array &$aSource, array &$aMapping){
        foreach($aKeys as $key){
            $aMapping[$key] = &$aSource[$key];
        }
    }
}

spl_autoload_register(array('AMI_Service', 'autoload'));
@ini_set('track_errors', 1); // recommended but not required
set_error_handler(array('AMI_Service', 'handleError'), CMS_ERROR_REPORTING);
set_exception_handler(array('AMI_Service', 'handleException'));
register_shutdown_function(array('AMI_Service', 'handleFatalError'));
