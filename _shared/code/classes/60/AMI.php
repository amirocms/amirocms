<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Service
 * @version   $Id: AMI.php 50563 2014-05-13 06:48:49Z Leontiev Anton $
 * @since     5.10.0
 */

/**
 * Fires before user logout.
 *
 * @event      on_before_user_logout AMI_Event::MOD_ANY
 * @eventparam AmiUsers_Users_TableItem oUser  User table item model
 * @since      5.12.0
 */

/**
 * Fires before user login.
 *
 * Allows to modify login/password/password hased flag before login.<br />
 * Set $aEvent['_discard'] to TRUE to discard action.
 *
 * @event      on_before_user_login AMI_Event::MOD_ANY
 * @eventparam string &login             Login
 * @eventparam string &password          Password (optional)
 * @eventparam bool   &isPasswordHashed  Password hashed flag (optional)
 * @eventparam bool   _discard
 * @since      5.12.0
 */

/**
 * Fires after user login.
 *
 * @event      on_after_user_login AMI_Event::MOD_ANY
 * @eventparam AmiUsers_Users_TableItem oUser  User table item model
 * @eventparam string password          Password (optional)
 * @eventparam bool   isPasswordHashed  Password hashed flag (optional)
 * @since      5.12.0
 */

/**
 * Fires before user create.
 *
 * Set $aEvent['aError']['code'] to non zero value to discard action.
 *
 * @event      on_before_user_create AMI_Event::MOD_ANY
 * @eventparam AmiUsers_Users_TableItem oUser    User table item model
 * @eventparam array                    &aError  array('code' => 0, 'message' => '')
 * @since      5.12.0
 */

/**
 * Fires after user create.
 *
 * @event      on_after_user_create AMI_Event::MOD_ANY
 * @eventparam AmiUsers_Users_TableItem oUser  User table item model
 * @since      5.12.0
 */

/**
 * Fires before user create.
 *
 * Set $aEvent['aError']['code'] to non zero value to discard action.
 *
 * @event      on_before_user_update AMI_Event::MOD_ANY
 * @eventparam AmiUsers_Users_TableItem oUser        User table item model
 * @eventparam array                    aSourceData  Source user data (optionsl)
 * @eventparam array                    &aData       New user data (optionsl)
 * @eventparam array                    &aError      array('code' => 0, 'message' => '')
 * @since      5.12.0
 */

/**
 * Allows to collect information about ratings in Rating extension.
 *
 * @event      on_rate ext_rating
 * @eventparam string modId   Module id
 * @eventparam int    itemId  Module item id
 * @eventparam string rating  Passed rating
 * @eventparam int    userId  Authorized user id or 0 if not authorized
 * @eventparam string vid     Site visitor id
 * @since      5.14.8
 */

/**
 * Allows to modify item data on export, forbid to export item setting $aEvent['_break'], interrupt export setting $aEvent['_result'] as FALSE.
 *
 * @event      on_data_exchange_export_item AMI_Event::MOD_ANY
 * @eventparam string driver  Driver name
 * @eventparam string modId   Module id
 * @eventparam int    itemId  Module item id
 * @eventparam array  &aItem  Module item data
 * @since      5.14.8
 */

/**
 * EventInitBefore() function replacement ("_local/admin_functions.php", "_local/front_functions.php").
 *
 * @event      v5_on_before_init AMI_Event::MOD_ANY
 * @eventparam CMS_Base oObject  Core v5 CMS object (was $vObject argument)
 * @since      6.0.2
 */

/**
 * CustomApplyVars() function replacement ("_local/admin_functions.php", "_local/front_functions.php").<br />
 * Set up '_skip' event array key to "return FALSE" from function.
 *
 * @event      v5_on_apply_data AMI_Event::MOD_ANY
 * @eventparam string type     Data type (was $cThread argument)
 * @eventparam mixed  oObject  Object (was $vObject argument)
 * @eventparam mixed  &aData   Data (was $aVars argument)
 * @eventparam int    pageId   Page Id (was $pageId argument)
 * @since      6.0.2
 */

/**
 * CacheSavePageBefore() function replacement ("_local/common_functions.php").<br />
 * Set up '_skip' event array key to "return FALSE" from function.
 *
 * @event      v5_on_before_save_cached_page AMI_Event::MOD_ANY
 * @eventparam CMS_Cache oObject   Cache object (was $vObject argument)
 * @eventparam string    &pageUIN  Page UIN (was $Pageuin argument)
 * @eventparam int       &pageId   Found page Id (was $foundPageId argument)
 * @since      6.0.2
 */

/**
 * Allows to modify E-shop order status or discard action.
 *
 * Set $aEvent['_discard'] to TRUE to discard action.
 *
 * @event      on_order_before_status_change AMI_Event::MOD_ANY
 * @eventparam string                        &status   New status of order
 * @eventparam AmiClean_EshopOrder_TableItem oItem     Order table item model
 * @eventparam array                         &aParams  Order parameters
 * @eventparam bool                          _discard  Set to TRUE to discard action
 * @since      6.0.4
 */

/**
 * Allows to modify E-shop some order data before order creation.
 *
 * @event      on_order_before_create AMI_Event::MOD_ANY
 * @eventparam string &status     New status of order
 * @eventparam string &header     Order header
 * @eventparam string &firstname  Buyer firs tname
 * @eventparam string &lastname   Buyer last name
 * @eventparam string &company    Buyer company name
 * @eventparam string &comments   Order comments
 * @eventparam float  &shipping   Shipping cost
 * @eventparam float  &total      Total order cost
 * @since      6.0.6
 */

/**
 * Allows to do some action afetr E-shop order status changed.
 *
 * @event      on_order_after_status_change AMI_Event::MOD_ANY
 * @eventparam string                        status   New status of order
 * @eventparam AmiClean_EshopOrder_TableItem oItem    Order table item model
 * @eventparam array                         aParams  Order parameters
 * @since      6.0.4
 */

/**
 * Fires when file is downloading using "ftpgetfile.php" script.
 *
 * Setting "errorCode" will return appropriate error and deny downloading.<br />
 * Possible values / HTTP code:
 * * wrong_request - "Wrong request" (400);
 * * not_logged_in - "You are not registered" (401);
 * * access_denied - "Access denied" (403);
 * * no_attempts - "The quantity of loadings is exhausted" (403);
 * * order_expired - "The time for loadings has expired" (403);
 * * not_found - "File not found" (404);
 * * unknown_file - "Unknown file" (404);
 * * not_installed - "This functionality is inaccessible" (404).
 *
 * @event      on_file_download AMI_Event::MOD_ANY
 * @eventparam string modId      Module id
 * @eventparam int    itemId     Module item id
 * @eventparam string errorCode  Error code, empty string by default
 *
 * @since      6.0.4
 */

/**
 * Allows to customize SQL query to export products.
 *
 * function cstHandleDataExchangeExportSQL($name, array $aEvent, $handlerModId, $srcModId){<br />
 * &nbsp;&nbsp;&nbsp;&nbsp;$pfx = $aEvent['aSQL]['default_prefix'];<br />
 * &nbsp;&nbsp;&nbsp;&nbsp;// select extra field `id_owner`<br />
 * &nbsp;&nbsp;&nbsp;&nbsp;$aEvent['aSQL]['select'] .= ', ' . $pfx . 'id_owner';<br />
 * &nbsp;&nbsp;&nbsp;&nbsp;// select products having `price` > 1000 only<br />
 * &nbsp;&nbsp;&nbsp;&nbsp;$aEvent['aSQL]['where'] .= ' AND ' . $pfx . 'price > 1000';<br />
 * &nbsp;&nbsp;&nbsp;&nbsp;// limit selection to 100 products<br />
 * &nbsp;&nbsp;&nbsp;&nbsp;$aEvent['aSQL]['limit'] = 'LIMIT 100';<br />
 * &nbsp;&nbsp;&nbsp;&nbsp;// JOIN query part<br />
 * &nbsp;&nbsp;&nbsp;&nbsp;// $aEvent['aSQL]['join']<br />
 *<br />
 * &nbsp;&nbsp;&nbsp;&nbsp;return $aEvent;<br />
 * }<br />
 *<br />
 * AMI_Event::addHandler('on_dataexchange_export_sql', 'cstHandleDataExchangeExportSQL', AMI_Event::MOD_ANY);
 *
 * @event      on_dataexchange_export_sql AMI_Event::MOD_ANY
 * @eventparam string modId  Module id
 * @eventparam &array aSQL   Array of SQL query parts
 * @since      6.0.6
 */

/**
 * Allows to change name of template set for next exchange drivers: Yandex.Market, Rambler, IRR.
 *
 * function cstHandleDataExchangeGetTplSetName($name, array $aEvent, $handlerModId, $srcModId){<br />
 * &nbsp;&nbsp;&nbsp;&nbsp;if(<br />
 * &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;in_array($aEvent['driverName'], array('YandexEshopDriver', 'RapidYandexEshopDriver') AND<br />
 * &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'export' === $aEvent['exchangeType'] AND<br />
 * &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'eshop_item' === $aEvent['modId'] AND<br />
 * &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'product' === $aEvent['type']<br />
 * &nbsp;&nbsp;&nbsp;&nbsp;){<br />
 * &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$aEvent['setName'] = 'item_row_my_custom';<br />
 * &nbsp;&nbsp;&nbsp;&nbsp;}<br />
 *<br />
 * &nbsp;&nbsp;&nbsp;&nbsp;return $aEvent;<br />
 * }<br />
 *<br />
 * AMI_Event::addHandler('on_dataexchange_get_tpl_set_name', 'cstHandleDataExchangeGetTplSetName', AMI_Event::MOD_ANY);
 *
 * @event      on_dataexchange_get_tpl_set_name AMI_Event::MOD_ANY
 * @eventparam string  driverName    Driver name
 * @eventparam string  exchangeType  Exchange type
 * @eventparam string  modId         Module id
 * @eventparam string  type          Type of template set
 * @eventparam &string setName       Name of template set
 * @since      6.0.6
 */

/**
 * Fires on before prepare item data for saving.
 *
 * @event      v5_on_item_prepare AMI_Event::MOD_ANY
 * @eventparam string modId  Module Id
 * @eventparam int    itemId Item Id
 * @eventparam array  aData  Array of item data
 * @since      6.0.6
 */

/**
 * Fires on before front list page size control show.
 *
 * @event      v5_on_show_page_size AMI_Event::MOD_ANY
 * @eventparam bool   enabled     Is page
 * @eventparam array  aPageSizes  Array of page sizes
 * @since      6.0.6
 */

/**
 * Fires on before front list page size set.
 *
 * @event      v5_on_set_page_size AMI_Event::MOD_ANY
 * @eventparam string modId     Module Id
 * @eventparam int    pageSize  Number of items on page
 * @since      6.0.6
 */


/**
 * AMI hub class.
 *
 * @package Service
 * @static
 */
final class AMI{
    /**
     * Resource ids to class names mapping
     *
     * Contains resource ids as keys and class names as values.
     *
     * @var array
     */
    private static $aResMapping = array();

    /**
     * Resource singleton objects
     *
     * Contains resource ids as keys and objects as values.
     *
     * @var array
     */
    private static $aResSingletons = array();

    /**
     * Class names to module ids mapping
     *
     * Contains class names as keys and module ids as values.
     *
     * @var array
     */
    private static $aClassToModId = array();

    /**
     * Module ids initiated by hypermodule config class
     *
     * @var array
     */
    private static $aHyperConfigInstances = array();

    /**
     * Default locale dependend date formats
     *
     * @var array
     */
    private static $aDateFormats = array(
        'ru' => array(
            'DB'       => '%d.%m.%Y %H:%i:%s',
            'DB_DATE'  => '%d.%m.%Y',
            'DB_TIME'  => '%H:%i:%s',
            'PHP'      => 'd.m.Y H:i:s',
            'PHP_ZONE' => 'd.m.Y H:i:s e',
            'PHP_DATE' => 'd.m.Y',
            'PHP_TIME' => 'H:i:s'
        ),
        'en' => array(
            'DB'       => '%m/%d/%Y %H:%i:%s',
            'DB_DATE'  => '%m/%d/%Y',
            'DB_TIME'  => '%H:%i:%s',
            'PHP'      => 'm/d/Y H:i:s',
            'PHP_ZONE' => 'm/d/Y H:i:s e',
            'PHP_DATE' => 'm/d/Y',
            'PHP_TIME' => 'H:i:s'
        )
    );

    /**
     * Fast environment options defaults
     *
     * @var array
     * @see AMI::getFastEnvDefaults()
     */
    /*
    private static $aFastEnvDefaults = array(
        '/^core$/' => array(
            'allow_multi_lang'   => false,
            'multi_page_allowed' => false,
            'multi_site'         => false,
            'dateformat_front'   => array(
                'en' => 'DD.MM.YYYY hh:mm:ss',
                'ru' => 'DD.MM.YYYY hh:mm:ss',
                'gr' => 'MM/DD/YY hh:mm:ss',
                'cn' => 'DD.MM.YYYY hh:mm:ss',
                'ge' => 'DD.MM.YYYY hh:mm:ss',
                'sv' => 'DD.MM.YYYY hh:mm:ss',
                'tr' => 'DD.MM.YYYY hh:mm:ss',
                'es' => 'DD.MM.YYYY hh:mm:ss'
            )
        ),
        '/_item$/' => array(
            'multi_page'     => false,
            'use_categories' => true,
            'stop_arg_names' => array(),
            'extensions'     => array()
        ),
        '/^news|blog$/' => array(
            'multi_page'     => true,
            'use_categories' => false,
            'stop_arg_names' => array(),
            'extensions'     => array()
        ),
        '/^search/' => array(
            'multi_page'     => false,
            'use_categories' => false,
            'stop_arg_names' => array(),
            'extensions'     => array()
        ),
        '/_cat$/' => array(
            'multi_page'     => false,
            'use_categories' => false,
            'stop_arg_names' => array(),
            'extensions'     => array()
        ),
        '/^common_settings$/' => array(
            'display_nickname_as' => 'nickname'
        ),
        '/.* /' => array(
            'multi_page'     => true,
            'use_categories' => true,
            'stop_arg_names' => array(),
            'extensions'     => array()
        )
    );
    */

    /**
     * Fast environment options
     *
     * @var        array
     * @see        AMI::setFastEnvOptions()
     * @deprecated since 5.14.6
     */
    private static $aFastEnvOptions = array();

    /**
     * Array containing unsupported extensions configs for current module
     *
     * @var array
     * @see self::cbFilterExt()
     */
    private static $aUnsupExt;

    private static $skipInitModIds = array();

    /**
     * Returns path to plugin distributive by its id.
     *
     * @param  string $pluginId     Plugin id
     * @param  bool   $exitOnError  Flag specifying exit on error, since 5.12.4
     * @return string|null
     * @see    ami_resp.php
     */
    public static function getPluginPath($pluginId, $exitOnError = true){
        $path = null;
        if(self::validateModId($pluginId)){
            $path = $GLOBALS['ROOT_PATH'] . '_local/plugins_distr/' . $pluginId . '/';
            if(!is_dir($path)){
                $path = null;
            }
        }
        if(is_null($path) && $exitOnError){
            /**
             * @exitpoint
             */
            while(ob_get_level() > 0){
                ob_end_clean();
            }
            header(self::getSingleton('response')->HTTP->getProtocol() . ' 404 Not Found', true, 404);
            trigger_error("Invalid plugin id '" . $pluginId . "'", E_USER_WARNING);
            die;
        }
        return $path;
    }

    /**
     * Returns path to module distributive by its id.
     *
     * @param  string $moduleId     Module id
     * @param  bool   $exitOnError  Flag specifying exit on error, since 5.12.4
     * @param  string $localPath    Path to the local modules directory
     * @return string|null
     * @since  5.12.4
     * @deprecated  5.14.4
     */
    public static function getModulePath($moduleId, $exitOnError = true, $localPath = 'modules/code'){
        AMI_Registry::set('_deprecated_error', TRUE);
        trigger_error('AMI::getModulePath() is deprecated', E_USER_WARNING);
        return null;
    }

    /**
     * Returns path to installed plugin directory (i. e. '_local/plugins/sample/').
     *
     * @param  string $pluginId  Plugin id
     * @return string
     * @see    ami_resp.php
     */
    public static function getPluginDataPath($pluginId){
        $path = false;
        if(self::validateModId($pluginId)){
            $path = $GLOBALS['ROOT_PATH'] . '_local/plugins/' . $pluginId . '/';
            if(!is_dir($path)){
                $path = false;
            }
        }
        if(!$path){
            // @exitpoint
            while(ob_get_level() > 0){
                ob_end_clean();
            }
            header(self::getSingleton('response')->HTTP->getProtocol() . ' 404 Not Found', true, 404);
            trigger_error("Invalid plugin id '" . $pluginId . "'", E_USER_WARNING);
            die;
        }
        return $path;
    }

    /**
     * Validates module id.
     *
     * @param  string $modId  Module id
     * @return bool
     * @amidev temporary
     */
    public static function validateModId($modId){
        return (bool)(preg_match('/^[a-z](?:[a-z\d]|_[a-z])+$/', $modId) || preg_match('/^plugin\_(\d+)$/', $modId));
    }

    /**
     * Returns module id by its class name.
     *
     * @param  string $class  Class name
     * @return string
     */
    public static function getModId($class){
        if(empty(self::$aClassToModId[$class])){
            $classPfx = explode('_', $class);
            $classPfx = $classPfx[0];
            if(empty(self::$aClassToModId[$classPfx])){
                preg_match_all('/([A-Z][^A-Z]+)/', $classPfx, $aMatches);
                $modId = implode('_', array_map('mb_strtolower', $aMatches[1]));
                self::$aClassToModId[$classPfx] = $modId;
                self::$aClassToModId[$class] = $modId;
            }else{
                self::$aClassToModId[$class] = self::$aClassToModId[$classPfx];
            }
        }
        return self::$aClassToModId[$class];
    }

    /**
     * Returns module property value.
     *
     * Could be called in {@link ami_env.php full environment context} only.
     *
     * @param  string $modId  Module id
     * @param  string $name  Module property name
     * @return mixed
     * @since  6.0.2
     */
    public static function getProperty($modId, $name = ''){
        return
            $modId !== 'core' // || ($GLOBALS['Core'] instanceof AMI_iCore)
            ? $GLOBALS['Core']->getModProperty($modId, $name)
            : $GLOBALS['Core']->getProperty($name);
    }

    /**
     * Returns module option value.
     *
     * @param  string $modId  Module id
     * @param  string $name  Module option name
     * @return mixed
     */
    public static function getOption($modId, $name = ''){
        if(isset($GLOBALS['Core'])){
            return
                $modId !== 'core' // || ($GLOBALS['Core'] instanceof AMI_iCore)
                ? $GLOBALS['Core']->getModOption($modId, $name)
                : $GLOBALS['Core']->getOption($name);
        }
        /*
        if(isset(self::$aFastEnvOptions[$modId])){
            return $name !== '' ? self::$aFastEnvOptions[$modId][$name] : self::$aFastEnvOptions[$modId];
        }
        foreach(self::$aFastEnvDefaults as $mask => $aDefaults){
            if(preg_match($mask, $modId)){
                return $name !== '' ? $aDefaults[$name] : $aDefaults;
            }
        }
        */
        trigger_error("Unknown option '" . $name . "' in module '" . $modId . "'", E_USER_WARNING);
    }

    /**
     * Returns true if module option is set.
     *
     * @param  string $modId  Module id
     * @param  string $name  Module option name
     * @return bool
     */
    public static function issetOption($modId, $name){
        if(isset($GLOBALS['Core'])){
            return
                $modId !== 'core' || ($GLOBALS['Core'] instanceof AMI_iCore)
                ? $GLOBALS['Core']->issetModOption($modId, $name)
                : $GLOBALS['Core']->issetOption($name);
        }
        /*
        if(isset(self::$aFastEnvOptions[$modId])){
            return array_key_exists($name, self::$aFastEnvOptions[$modId]);
        }else{
            foreach(self::$aFastEnvDefaults as $mask => $aDefaults){
                if(preg_match($mask, $modId)){
                    return isset($aDefaults[$name]);
                }
            }
        }
        */
        return false;
    }

    /**
     * Returns true if module propery is set.
     *
     * @param  string $modId  Module id
     * @param  string $name   Module property name
     * @return bool
     * @since  6.0.2
     */
    public static function issetProperty($modId, $name){
        if(isset($GLOBALS['Core'])){
            return
                $modId !== 'core' || ($GLOBALS['Core'] instanceof AMI_iCore)
                ? $GLOBALS['Core']->issetModProperty($modId, $name)
                : $GLOBALS['Core']->issetProperty($name);
        }
        return false;
    }

    /**
     * Returns true if module option is set and is true.
     *
     * @param  string $modId  Module id
     * @param  string $name  Module option name
     * @return bool
     */
    public static function issetAndTrueOption($modId, $name){
        return self::issetOption($modId, $name) && self::getOption($modId, $name);
    }

    /**
     * Returns true if module propert is set and is true.
     *
     * @param  string $modId  Module id
     * @param  string $name   Module property name
     * @return bool
     * @since  6.0.2
     */
    public static function issetAndTrueProperty($modId, $name){
        return self::issetProperty($modId, $name) && self::getProperty($modId, $name);
    }

    /**
     * Sets module option value.
     *
     * @param  string $modId  Module id
     * @param  string $name  Module option name
     * @param  mixed $value  Module option value, null to unset
     * @return void
     * @since  5.12.0
     */
    public function setOption($modId, $name, $value){
        if(isset($GLOBALS['Core'])){
            if($modId != 'core'){
                $GLOBALS['Core']->SetModOption($modId, $name, $value);
            }else{
                $GLOBALS['Core']->SetOption($name, $value);
            }
        }else{
            if(empty(self::$aFastEnvOptions[$modId])){
                self::$aFastEnvOptions[$modId] = array();
            }
            if(is_null($value)){
                unset(self::$aFastEnvOptions[$modId][$name]);
            }else{
                self::$aFastEnvOptions[$modId][$name] = $value;
            }
        }
    }

    /**
     * Sets new options values, returns array of replaced options with original values.
     *
     * @param  string $modId   Module id
     * @param array $aOptions  Array of option names and new values
     * @return array
     * @since  6.0.2
     */
    public function replaceOptions($modId, array $aOptions){
        $aSavedOptions = array();
        foreach($aOptions as $option => $value){
            $aSavedOptions[$option] = AMI::getOption($modId, $option);
            AMI::setOption($modId, $option, $value);
        }
        return $aSavedOptions;
    }

    /**
     * Sets module property value.
     *
     * @param  string $modId  Module id
     * @param  string $name   Module property name
     * @param  mixed  $value  Module property value, null to unset
     * @return void
     * @amidev temporary
     */
    public function setProperty($modId, $name, $value){
        if(isset($GLOBALS['Core'])){
            if($modId != 'core'){
                $GLOBALS['Core']->SetModProperty($modId, $name, $value);
            }else{
                $GLOBALS['Core']->SetProperty($name, $value);
            }
        }
    }

    /**
     * Saves module options.
     *
     * @param  string $modId  Module id
     * @return bool  TRUE if saved successfully
     * @since  6.0.2
     */
    public function saveOptions($modId){
        global $Core;

        if(in_array($modId, array('', 'core', 'all'))){
            trigger_error("AMI::saveOptions(): Forbidden module Id '" . $modId . "'", E_USER_WARNING);
            return FALSE;
        }
        if(isset($Core) && ($Core instanceof CMS_Core)){
            trigger_error("AMI::saveOptions('" . $modId . "') is called", E_USER_WARNING);
            return $Core->saveOptions($modId, FALSE);
        }
        trigger_error("AMI::saveOptions(): Full environment required", E_USER_WARNING);
        return FALSE;
    }

    /**
     * Returns true if module is installed.
     *
     * @param  string $modId  Module id
     * @return bool
     * @todo   Implement
     * @amidev temporary
     */
    public static function isModInstalled($modId){
        return
            isset($GLOBALS['Core'])
                ? $GLOBALS['Core']->IsInstalled($modId)
                : AMI_ModDeclarator::getInstance()->isRegistered($modId);
    }

    /**
     * Returns plugin option by its id.
     *
     * Example:
     * <code>
     * $flag = AMI::getPluginOption('sample', 'option_bool');
     * </code>
     *
     * @param  string $pluginId  Plugin id
     * @param  string $name      Option name
     * @param  string $secret    Secret key to avoid unauthorized access to plugin options
     *                           if set from {@link options.php} using $api->setOption('secret', 'Secret key')
     * @return mixed
     * @see    AMI_PluginState::setOption()
     * @see    options.php
     */
    public function getPluginOption($pluginId, $name, $secret = ''){
        static $aPluginIdToModId = array();

        /**
         * @var CMS_Core|AMI_Core|null
         */
        global $Core;

        if(empty($Core)){
            return null;
        }

        if($Core->isInstalled($pluginId) && AMI_Registry::exists('_source_mod_id')){
            // 6.0 admin entry point
            $aPluginIdToModId[$pluginId] = $pluginId;
        }else{
            if(!isset($aPluginIdToModId[$pluginId])){
                $aPluginIdToModId[$pluginId] = '';
                // Search 'plugin_id' option in plugins to get real module id
                for($i = 1; $i <= $GLOBALS['PLUGINS_COUNT']; $i++){
                    $modId = CMS_InstallablePlugin::GetPluginName($i);
                    if(
                        $Core->IsInstalled($modId) &&
                        $Core->GetModOption($modId, 'plugin_id') === $pluginId
                    ){
                        $aPluginIdToModId[$pluginId] = $modId;
                        break;
                    }
                }
            }
        }
        $modId = $aPluginIdToModId[$pluginId];
        if($modId === ''){
            // Plugin not found
            return null;
        }
        if($Core->issetModOption($modId, $name)){
            // Check secret
            if(
                $Core->issetModOption($modId, 'secret')
                ? $Core->GetModOption($modId, 'secret') === $secret
                : true
            ){
                $res = $Core->GetModOption($modId, $name);
            }
        }else{
            $res = null;
        }
        return $res;
    }

    /**
     * Returns CMS versions.
     *
     * AMI::getVersion($product) returns all versions for a specific product<br />
     * AMI::getVersion() returns all versions in an array
     *
     * @param  mixed $product  Product
     * @param  mixed $type     Type
     * @return mixed
     */
    public function getVersion($product = false, $type = false){
        global $Core;

        return
            isset($Core)
                ? $Core->getVersion($product, $type)
                : AMI::getSingleton('core')->getVersion($product, $type);
    }

    /**
     * Converts module component controller action to its handler name.
     *
     * @param  string $action  Action
     * @return string
     * @amidev Temporary
     */
    public static function actionToHandler($action){
        return 'dispatch' . implode(array_map('ucfirst', explode('_', $action)));
    }

    /**
     * Adds resource mapping.
     *
     * All CMS entities should be described as resources.
     * See {@link ami_server.php} for usage example.
     *
     * @param  array $aMapping  Array having resource ids as keys and class names as values
     * @return void
     */
    public static function addResourceMapping(array $aMapping){
        self::$aResMapping += $aMapping;
    }

    /**
     * Adds/changes mapping for resource.
     *
     * @param  string $resId     Resource id
     * @param  string $resClass  Resource class mapping
     * @return void
     */
    public static function addResource($resId, $resClass){
        self::$aResMapping[$resId] = $resClass;
    }

    /**
     * Adds module resource mapping.
     *
     * Example:
     * <code>
     * // ami_sample_mapping.php {
     *
     * // Table models
     * AMI::addModResources($modId, 'table'));
     * // replaces:
     * // AMI::addResourceMapping(
     * //     array(
     * //         $modId . '/table/model'               => 'AmiSample_Table',
     * //         $modId . '/table/model/list'          => 'AmiSample_TableList',
     * //         $modId . '/table/model/item'          => 'AmiSample_TableItem'
     * //         $modId . '/table/model/item/modifier' => 'AmiSample_TableItemModifier'
     * //     )
     * // );
     * // AMI_Service::addClassMapping(
     * //     array(
     * //         'AmiSample_TableItem'  => 'AmiSample_Table',
     * //         'AmiSample_TableList'  => 'AmiSample_Table'
     * //     )
     * // );
     *
     * // Module admin controller and module model
     * AMI::addModResources($modId, 'module', array('model', 'controller/adm'));
     * // replaces:
     * // AMI::addResourceMapping(
     * //     array(
     * //         $modId . '/module/model'          => 'AmiSample_State',
     * //         $modId . '/module/controller/adm' => 'AmiSample_Adm',
     * //     )
     * // );
     * // AMI_Service::addClassMapping(
     * //     array(
     * //         'AmiSample_State'  => 'AmiSample_Adm'
     * //     )
     * // );
     *
     * // Module admin filter controller, model and view
     * AMI::addModResources($modId, 'filter/adm');
     * // replaces:
     * // AMI::addResourceMapping(
     * //     array(
     * //         $modId . '/filter/controller/adm' => 'AmiSample_FilterAdm',
     * //         $modId . '/filter/model/adm'      => 'AmiSample_FilterModelAdm',
     * //         $modId . '/filter/view/adm'       => 'AmiSample_FilterViewAdm'
     * //     )
     * // );
     * // AMI_Service::addClassMapping(
     * //     array(
     * //         'AmiSample_FilterModelAdm' => 'AmiSample_FilterAdm',
     * //         'AmiSample_FilterViewAdm'  => 'AmiSample_FilterAdm'
     * //     )
     * // );
     *
     * // Module admin list controller and view
     * AMI::addModResources($modId, 'list/adm');
     * // replaces:
     * // AMI::addResourceMapping(
     * //     array(
     * //         $modId . '/list/controller/adm' => 'AmiSample_ListAdm',
     * //         $modId . '/list/view/adm'       => 'AmiSample_ListViewAdm'
     * //     )
     * // );
     * // AMI_Service::addClassMapping(
     * //     array(
     * //         'AmiSample_ListViewAdm' => 'AmiSample_ListAdm'
     * //     )
     * // );
     *
     * // Module admin form controller and view
     * AMI::addModResources($modId, 'form/adm');
     * // replaces:
     * // AMI::addResourceMapping(
     * //     array(
     * //         $modId . '/form/controller/adm' => 'AmiSample_FormAdm',
     * //         $modId . '/form/view/adm'       => 'AmiSample_FormViewAdm'
     * //     )
     * // );
     * // AMI_Service::addClassMapping(
     * //     array(
     * //         'AmiSample_FormViewAdm' => 'AmiSample_FormAdm'
     * //     )
     * // );
     *
     * // } ami_sample_mapping.php
     * // ami_sample_specblock.php {
     *
     * AMI::addModResources($modId, 'module', array('model', 'controller/frn'));
     * // replaces:
     * // AMI::addResourceMapping(
     * //     array(
     * //         $modId . '/module/controller/frn' => 'AmiSample_Frn'
     * //     )
     * // );
     *
     * // } ami_sample_specblock.php
     * </code>
     *
     * @param  string     $modId      Module id
     * @param  string     $type       Resource type
     * @param  array|null $aParts     Parts, resource list depending on type:
     *         - <b>table</b>: array('model', 'model/item', 'model/item/modifier', 'model/list'), array('model', 'model/item', 'model/list') by default;
     *         - <b>module</b>: array('model', 'controller/adm', 'controller/frn'), no defaults;
     *         - <b>list/adm</b>: array('controller/adm', 'list_actions/controller/adm', 'list_group_actions/controller/adm', 'view/adm'), array('controller/adm', 'view/adm') by default;
     *         - <b>form/adm</b>: array('controller/adm', 'view/adm'), the same list by default;
     *
     *         - <b>filter/adm</b> or other resource type in format <b>{$type/adm}</b>: array('controller/adm', 'model/adm', 'view/adm'), the same list by default.
     * @param  bool       $doCombine  Add class mapping to the first class
     * @return void
     * @see    ami_sample_mapping.php
     * @see    ami_sample_specblock.php
     * @since  5.12.0
     */
    public static function addModResources($modId, $type, array $aParts = null, $doCombine = true){
        if(mb_strpos($type, '/') !== false){
            list($type, $side) = explode('/', $type, 2);
        }else{
            $side = '';
        }
        $classPrefix = self::getClassPrefix($modId);
        $aResources = array();
        $aPartsToResources = array();
        $classPart = ucfirst($type);
        // Add module resources according to selected type
        switch($type){
            case 'table':
                if(!is_array($aParts)){
                    $aParts = array('model', 'model/item', 'model/list');
                }
                $aPartsToResources = array(
                    'model'               => array($modId . '/table/model',               $classPrefix . '_Table'),
                    'model/item'          => array($modId . '/table/model/item',          $classPrefix . '_TableItem'),
                    'model/item/modifier' => array($modId . '/table/model/item/modifier', $classPrefix . '_TableItemModifier'),
                    'model/list'          => array($modId . '/table/model/list',          $classPrefix . '_TableList')
                );
                break;
            case 'module':
                $aPartsToResources = array(
                    'controller/adm' => array($modId . '/module/controller/adm', $classPrefix . '_Adm'),
                    'controller/frn' => array($modId . '/module/controller/frn', $classPrefix . '_Frn'),
                    'model'          => array($modId . '/module/model',          $classPrefix . '_State')
                );
                break;
            case 'list':
            case 'form':
                if(!is_array($aParts)){
                    $aParts = $side === 'adm' ? array('controller/adm', 'view/adm') : array();
                }
                $aPartsToResources = array(
                    'controller/adm'  => array($modId . '/' . $type . '/controller/adm', $classPrefix . '_' . $classPart . 'Adm'),
                    'view/adm'        => array($modId . '/' . $type . '/view/adm',       $classPrefix . '_' . $classPart . 'ViewAdm')
                );
                if($type === 'list'){
                    $aPartsToResources['list_actions/controller/adm'] = array($modId . '/list_actions/controller/adm', $classPrefix . '_ListActionsAdm');
                    $aPartsToResources['list_group_actions/controller/adm'] = array($modId . '/list_group_actions/controller/adm', $classPrefix . '_ListGroupActionsAdm');
                }
                break;
            default:
                if(!is_array($aParts)){
                    $aParts = array('controller/' . $side, 'model/' . $side, 'view/' . $side);
                }
                $aPartsToResources = array(
                    'controller/adm'  => array($modId . '/' . $type . '/controller/adm', $classPrefix . '_' . $classPart . 'Adm'),
                    'model/adm'       => array($modId . '/' . $type . '/model/adm',      $classPrefix . '_' . $classPart . 'ModelAdm'),
                    'view/adm'        => array($modId . '/' . $type . '/view/adm',       $classPrefix . '_' . $classPart . 'ViewAdm'),
                    'controller/frn'  => array($modId . '/' . $type . '/controller/frn', $classPrefix . '_' . $classPart . 'Frn'),
                    'view/frn'        => array($modId . '/' . $type . '/view/frn',       $classPrefix . '_' . $classPart . 'ViewFrn')
                );
        }
        foreach($aPartsToResources as $part => $aMapping){
            if(in_array($part, $aParts)){
                $aResources[$aMapping[0]] = $aMapping[1];
            }
        }
        // d::vd($aResources, "resources: {$modId}, {$type}");
        self::addResourceMapping($aResources);
        if($doCombine){
            $firstClass = array_shift($aResources);
            // d::vd($firstClass);
            $aClassMapping = array();
            foreach($aResources as $class){
                $aClassMapping[$class] = $firstClass;
            }
            // d::vd($aClassMapping, "class mapping: {$modId}, {$type}");
            AMI_Service::addClassMapping($aClassMapping);
        }
    }

    /**
     * Returns true if passed parameter is resource.
     *
     * @param  string $resId     Resource id
     * @param  bool   $autoload  Flag specifying to autoload resource class
     * @return bool
     * @amidev Temporary
     */
    public static function isResource($resId, $autoload = TRUE){
        if(!isset(self::$aResMapping[$resId])){
            // Try to add resource
            $prev = AMI_Service::setAutoloadFile($autoload);
            self::_addResourceById($resId, $autoload);
            AMI_Service::setAutoloadFile($prev);
        }

        return isset(self::$aResMapping[$resId]);
    }

    /**
     * Returns raw resource assotiation string by resource id.
     *
     * @param  string $resId  Resource id
     * @return bool
     * @amidev Temporary
     */
    public static function getRawResource($resId){
        return self::$aResMapping[$resId];
    }

    /**
     * Return associative array of resources ids => class name, where id begins with given mask.
     *
     * @param  string $stringMask  String mask to check
     * @return array  Array of resources id
     * @amidev
     */
    public static function getResourcesByMask($stringMask = ''){
    	$aResKeys = array_keys(self::$aResMapping);
    	$aList = array();
        foreach($aResKeys as $key){
            if(mb_strpos($key, $stringMask) === 0){
                $aList[$key] = self::$aResMapping[$key];
            }
        }
    	return $aList;
    }

    /**
     * Returns resource object by resource id.
     *
     * @param  string $resId  Resource id
     * @param  array $aArgs   Arguments passed to constructor
     * @param  bool  $isLazy  Specifies to create object after first call
     * @return object
     */
    public static function getResource($resId, array $aArgs = array(), $isLazy = false){
        if(empty(self::$aResMapping[$resId])){
            if(!self::_addResourceById($resId)){
                throw new AMI_Exception(
                    "Unknown resource '" . $resId . "'",
                    E_USER_ERROR
                );
            }
        }
        if($isLazy){
            // Retutn lazy facade object for later real onject creation
            return new AMI_LazyFacade($resId, $aArgs);
        }
        if(empty(self::$aResMapping[$resId])){
            throw new AMI_Exception(
                "Unknown resource '" . $resId . "'",
                E_USER_ERROR
            );
        }
        $class = self::$aResMapping[$resId];
        try{
            $oReflection = new ReflectionClass($class);
        }catch(ReflectionException $oException){
            throw new AMI_Exception(
                "Cannot reflect class '" . $class . "'",
                E_USER_ERROR,
                $oException
            );
        }

        // Create resource object
        $oResource = $oReflection->getConstructor() ? $oReflection->newInstanceArgs($aArgs) : new $class;

        $aParts = explode('/', $resId, 2);
        $modId = array_shift($aParts);
        if(in_array($modId, AMI::$aHyperConfigInstances)){
            if(method_exists($oResource, 'setExtId')){
                // Setting extension module id
                $oResource->setExtId($modId);
            }elseif(method_exists($oResource, 'setModId')){
                // Setting module id
                $oResource->setModId($modId);
                if($oResource instanceof AMI_ModTable){
                    // Setting table name according to module id
                    $oResource->setTableName('cms_' . AMI_ModDeclarator::getInstance()->getAttr($modId, 'data_source'));
                    // Workaround for categories
                    $aExclusions = array('eshop_item', 'kb_item', 'portfolio_item');
                    if($oResource->getDependenceResId('cat') && !in_array($modId, $aExclusions)){
                        $oResource->changeDependenentModId('cat', $modId . '_cat');
                    }
                }
            }
        }

        return $oResource;
    }

    /**
     * Returns class name by resource id.
     *
     * @param  string $resId     Resource id
     * @param  bool   $fatality  Generate fatal error if no resource found
     * @return string | NULL
     * @amidev Temporary?
     */
    public static function getResourceClass($resId, $fatality = TRUE){
        if(empty(self::$aResMapping[$resId])){
            if(!self::_addResourceById($resId)){
                trigger_error("Unknown resource '" . $resId . "'", $fatality ? E_USER_ERROR : E_USER_WARNING);
                return;
            }
        }
        if(empty(self::$aResMapping[$resId])){
            trigger_error("Unknown resource '" . $resId . "'", $fatality ? E_USER_ERROR : E_USER_WARNING);
            return;
        }

        return self::$aResMapping[$resId];
    }

    /**
     * Returns resource Id bt class name.
     *
     * @param  string $class  Class name
     * @return string|FALSE
     * @amidev Temporary?
     */
    public static function getResourceByClass($class){
        $resId = array_search($class, self::$aResMapping);
        return $resId;
    }

    /**
     * Returns resource model by resource id.
     *
     * Appends '/model' tail to passed resource id.
     * Can return resource model as instance or singleton.
     *
     * @param  string $resId  Resource id
     * @param  array $aArgs  Arguments passed to constructor
     * @param  bool $bAsInstance  Return resource model as instance
     * @return object
     */
    public static function getResourceModel($resId, array $aArgs = array(), $bAsInstance = true){
        if(!self::isResource($resId) && preg_match('~^([a-z][a-z\d_]+[a-z\d])/table/model/(item|list)$~', $resId, $aMatches)){
            $newResId = $aMatches[1] . '/table/' . $aMatches[2] . '/model';
            if(self::isResource($newResId)){
            	AMI_Registry::set("_deprecated_error", true);
                trigger_error("Resource '" . $newResId . "' is deprecated, describe as '" . $resId . "' or use AMI::addModResources(\$modId, 'table'))", E_USER_WARNING);
                $resId = $newResId;
            }
        }elseif(mb_strpos($resId, '/model') === false){
            $resId .= '/model';
        }
        return $bAsInstance ? self::getResource($resId, $aArgs) : self::getSingleton($resId, $aArgs);
    }

    /**
     * Returns true if resource singleton is initialized.
     *
     * @param  string $resId  Resource id
     * @return bool
     * @since  5.14.4
     */
    public static function isSingletonInitialized($resId){
        return isset(self::$aResSingletons[$resId]);
    }

    /**
     * Returns resource singleton by resource id.
     *
     * @param  string $resId  Resource id
     * @param  array  $aArgs  Arguments passed to getInstance() method or to the constructor
     * @return object
     */
    public static function getSingleton($resId, array $aArgs = array()){
        if(empty(self::$aResSingletons[$resId])){
            self::initSingleton($resId, $aArgs);
        }
        return self::$aResSingletons[$resId];
    }

    /**
     * Reinit resource singleton by resource id.
     *
     * @param  string $resId  Resource id
     * @param  array $aArgs   Arguments passed to getInstance() method or to the constructor
     * @return object
     * @amidev
     */
    public static function initSingleton($resId, array $aArgs = array()){
        if(empty(self::$aResMapping[$resId])){
            if(!self::_addResourceById($resId)){
                trigger_error("Unknown resource '" . $resId . "'", E_USER_ERROR);
            }
        }
        $class = self::$aResMapping[$resId];
        $oReflection = new ReflectionClass($class);
        if($oReflection->hasMethod('getInstance')){
            // Create object using getInstance() method
            $oReflection = new ReflectionMethod($class, 'getInstance');
            self::$aResSingletons[$resId] = $oReflection->invoke(null, $aArgs);
        }else{
            // Create object using constructor
            self::$aResSingletons[$resId] = self::getResource($resId, $aArgs);
        }
        return self::$aResSingletons[$resId];
    }


    /**
     * Initialize module extensions.
     *
     * @param  string  $modId           Module Id
     * @param  string  $optSrcId        Reserved, will be describe later
     * @param  AMI_Mod $oModController  Module controller (since 6.0.2)
     * @return void
     */
    public static function initModExtensions($modId, $optSrcId = '', $oModController = NULL){
        $oDeclarator = AMI_ModDeclarator::getInstance();
        if(isset(self::$skipInitModIds[$modId]) || !$oDeclarator->isRegistered($modId)){
            return;
        }
        $aAPIExt = array();

        if(self::issetAndTrueOption($modId, 'use_categories')){
            if(preg_match('/^(eshop|kb|portfolio)_(item|cat)$/', $modId)){
                $catExtId = 'ext_eshop_category';
            }else{
                $catExtId = 'ext_category';
            }
            $aAPIExt[] = $catExtId;
        }

        if(self::issetOption($modId, 'extensions')){
            $aExt = self::getOption($modId, 'extensions');
            if(!is_array($aExt)){
                $aExt = $aExt ? array($aExt) : array();
            }

            $index = array_search('ext_images', $aExt);
            if($index !== FALSE){
                $aAPIExt[] = 'ext_image';
                unset($aExt[$index]);
            }elseif(
                preg_match('/^(eshop|kb|portfolio)_cat$/', $modId, $aMatches) &&
                in_array('ext_images', self::getOption($aMatches[1] . '_item', 'extensions'))
            ){
                // *_item/*_cat hack
                $aAPIExt[] = 'ext_image';
            }
            $aAPIExt = array_merge($aAPIExt, $aExt);
        }

        if($aAPIExt){
            // Prepare extensions list for 6.0 environment

            // ext_image hack for multifeeds: turn on if root module has ext_image
            if(!in_array('ext_image', $aAPIExt) && self::isCategoryModule($modId)){
                list($hyper, ) = AMI_ModDeclarator::getInstance()->getHyperData($modId);
                if($hyper == 'ami_multifeeds'){
                    $rootModId = str_replace('_cat', '', $modId);
                    $aRootExt = self::getOption($rootModId, 'extensions');
                    if(in_array('ext_images', $aRootExt) || in_array('ext_image', $aRootExt)){
                        $aAPIExt[] = 'ext_image';
                    }
                }
            }

            // Advertisement extension hack
            if(
                AMI_Registry::get('side') == 'adm' && self::issetOption('adv_places', 'adv_modules') &&
                is_array(self::getOption('adv_places', 'adv_modules')) &&
                in_array($modId, self::getOption('adv_places', 'adv_modules'))
            ){
                $aAPIExt[] = 'ext_adv';
            }

            // Unsupported extensions
            self::$aUnsupExt =
                self::issetProperty($modId, 'unsupported_extensions')
                    ? self::getProperty($modId, 'unsupported_extensions')
                    : array();

            // Filter extensions
            $aAPIExt = array_filter($aAPIExt, array('AMI', 'cbFilterExt'));
            self::$aUnsupExt = NULL;
        }

        // d::vd($aAPIExt);###
        $key = 'AMI/Environment/Model/IstalledExtensions/' . $modId;
        if(AMI_Registry::exists($key)){
            $aCurrentExt =
                array_values(
                    array_filter(
                        array_keys(AMI_Registry::get($key)),
                        array('AMI', 'cbFilterExtViews')
                    )
                );

            if(array_values($aAPIExt) != $aCurrentExt){
                trigger_error(
                    "Other set of extensions ('" . implode("', '", $aCurrentExt) .
                    "') is already initialized for module '" . $modId . "' instead of '" .
                    implode("', '", $aAPIExt) . "'",
                    E_USER_WARNING
                );
            }
            // else {} // Same extensions set is already installed
            return;
        }

        // d::vd($aAPIExt);return;###
        // ob_end_clean();###
        self::$skipInitModIds[$modId] = TRUE;

        // d::vd($aAPIExt, "'{$modId}' extensions");###
        foreach($aAPIExt as $extModId){
            $extResId = $extModId;
            if($oDeclarator->isRegistered($extModId)){
                $extResId = $extModId . '/module/controller/' . AMI_Registry::get('side', 'adm');
            }
            // $e = new Exception;###
            // echo '<pre>', $e->getTraceAsString();###
            // var_dump("{$extModId}: $extResId");echo str_repeat(' ', 65000);echo '</pre>';flush();###

            AMI_Registry::set(
                $key . '/' . $extModId,
                self::getResource($extResId, array($modId, $optSrcId, $oModController))
            );
        }

        $aEvent = array(
            'modId'        => $modId,
            'tableModelId' => $modId . '/table' // module table model resource id
        );

        /**
         * Called before initialization module component.
         *
         * @event      on_mod_pre_init $modId
         * @eventparam string modId  Module Id
         * @eventparam AMI_Mod|null oController  Module controller object
         * @eventparam string tableModelId  Table model resource id
         */
        AMI_Event::fire('on_mod_pre_init', $aEvent, $modId);
        unset(self::$skipInitModIds[$modId]);
    }

    /**
     * Cleans up module extensions.
     *
     * @param  string $modId  Module Id
     * @return void
     */
    public static function cleanupModExtensions($modId){
        if(isset(self::$skipInitModIds[$modId])){
            return;
        }
        $key = 'AMI/Environment/Model/IstalledExtensions/' . $modId;
        if(AMI_Registry::exists($key)){
            $aExt = AMI_Registry::get($key);
            $aExtModIds = array_keys($aExt);
            AMI_Registry::delete($key);
            foreach($aExtModIds as $extModId){
                $aExt[$extModId]->__destruct();
                unset($aExt[$extModId]);
            }
        }
    }

    private static function cbFilterExt($extModId){
        if(
            $extModId === '' ||
            in_array($extModId, self::$aUnsupExt) ||
            in_array('*', self::$aUnsupExt) ||
            (
                !self::isModInstalled($extModId) &&
                !in_array($extModId, array('ext_category', 'ext_image', 'ext_adv'))
            )
        ){
            return FALSE;
        }

        $extResId = $extModId;
        if(AMI_ModDeclarator::getInstance()->isRegistered($extModId)){
            list(, $extConfig) = AMI_ModDeclarator::getInstance()->getHyperData($extModId);
            if(in_array($extConfig, self::$aUnsupExt)){
                return FALSE;
            }
            $extResId = $extModId . '/module/controller/' . AMI_Registry::get('side', 'adm');
        }

        return AMI::isResource($extResId);
    }

    /**
     * Callback filterring extesion views from extensions array.
     *
     * @param  type $extModId  Extension module Id
     * @return bool
     */
    public static function cbFilterExtViews($extModId){
        return !preg_match('/\.view$/', $extModId);
    }

    /**
     * Returns date format by locale id.
     *
     * The method returns php or database date/time format according to given locale.
     *
     * @param  string $locale  Locale id
     * @param  string $type  Format type: 'DB'|'DB_DATE'|'DB_TIME'|'PHP'|'PHP_DATE'|'PHP_TIME'
     * @return string
     * @see    PlgAJAXResp::initModel()
     */
    public static function getDateFormat($locale = 'en', $type = 'DB'){
        static $isFirst = true;

        $retLocale = $locale;

        if($isFirst){
            $isFirst = false;
            $optionName = 'dateformat_' . (AMI_Registry::get('side') === 'frn' ? 'front' : 'admin');
            if(AMI::issetOption('core', $optionName)){
                $aFormat = AMI::getOption('core', $optionName);
                if(isset($aFormat[$locale])){
                    $format = $aFormat[$locale];
                    self::$aDateFormats[$locale]['PHP'] = DateTools::conf2phpFormat($format);
                    self::$aDateFormats[$locale]['PHP_DATE'] = DateTools::conf2phpFormat(DateTools::getJustDateFormat($format));
                    self::$aDateFormats[$locale]['PHP_TIME'] = DateTools::conf2phpFormat($format, true);
                    /*
                    // from CMS_Base
                    $this->DFMT["conf"] = DateTools::getJustDateFormat($dfmt);
                    $this->DFMT["conf_dtime"] = $dfmt;
                    $this->DFMT["php"] = DateTools::conf2phpFormat($this->DFMT["conf"]);
                    $this->DFMT["db"] = DateTools::php2mysqlFormat($this->DFMT["php"]);
                    $this->DFMT["php_dtime"] = DateTools::conf2phpFormat($this->DFMT["conf_dtime"]);
                    $this->DFMT["db_dtime"] = DateTools::php2mysqlFormat($this->DFMT["php_dtime"]);
                    $this->DFMT["php_time"] = DateTools::conf2phpFormat($this->DFMT["conf_dtime"], true);
                    $this->DFMT["db_time"] = DateTools::php2mysqlFormat($this->DFMT["php_dtime"], true);
                    */
                }
            }
        }

        if(!isset(self::$aDateFormats[$locale])){
            $retLocale = 'en';
        }
        if(isset(self::$aDateFormats[$retLocale][$type])){
            $aEvent = array(
                'locale'      => $locale,
                'ret_locale'  => &$retLocale,
                'date_format' => &self::$aDateFormats
            );

            /**
             * Called before the return date / time format.
             *
             * @event      on_get_date_format AMI_Event::MOD_ANY
             * @eventparam string locale  Current locale
             * @eventparam string ret_locale  Target locale
             * @eventparam array date_format  Array of date formats for all possible locales
             */
            AMI_Event::fire('on_get_date_format', $aEvent, AMI_Event::MOD_ANY);
            return self::$aDateFormats[$retLocale][$type];
        }else{
            trigger_error("Invalid date format type: ".$type, E_USER_WARNING);
        }
    }

    /**
     * Saves global scope, can be restored via {@link AMI::restoreGlobalScope()}.
     *
     * Example:
     * <code>
     *
     * $AMI_ENV_SETTINGS = array('external_call' => true);
     * require 'ami_env.php';
     *
     * $db = new MyDBClass();
     *
     * // get_class($db) === 'MyDBClass'
     *
     * AMI::saveGlobalScope('Dummy');
     * AMI::restoreGlobalScope('Amiro');
     *
     * // get_class($db) !== 'MyDBClass'
     *
     * AMI::restoreGlobalScope('Dummy');
     *
     * // get_class($db) === 'MyDBClass'
     *
     * </code>
     *
     * @param string $name  Scope name, empty by default
     * @param array $aScope  Array to store as a global scope
     * @see    AMI::restoreGlobalScope()
     * @see    ami_env.php
     * @since  5.12.4
     * @return void
     */
    public static function saveGlobalScope($name = '', array $aScope = null){
        if(is_null($aScope)){
            $aGlobalKeys = array_diff(
                array_keys($GLOBALS),
                array(
                    'GLOBALS', '_ENV', 'HTTP_ENV_VARS', '_POST', 'HTTP_POST_VARS', '_GET', 'HTTP_GET_VARS', '_COOKIE', 'HTTP_COOKIE_VARS',
                    '_SERVER', 'HTTP_SERVER_VARS', '_FILES', 'HTTP_POST_FILES', '_REQUEST',
                    'AMI_ENV_SETTINGS', 'AMI_MICROTIME_STARTED'
                )
            );
            $aScope = array();
            foreach($aGlobalKeys as $key){
                $aScope[$key] = $GLOBALS[$key];
            }
        }
        AMI_Registry::set('aAMIGlobalScope_' . $name, $aScope);
    }

    /**
     * Restores global scope modified in the environment setting up entry point. See {@link AMI::saveGlobalScope()} for usage example.
     *
     * @param string $name  Scope name
     * @return void
     * @see    AMI::saveGlobalScope()
     * @see    ami_env.php
     * @since  5.12.4
     */
    public static function restoreGlobalScope($name = ''){
        $aScope = AMI_Registry::get('aAMIGlobalScope_' . $name, false);
        if($aScope){
            foreach(array_keys($aScope) as $key){
                $GLOBALS[$key] = $aScope[$key];
            }
        }else{
            trigger_error('Nothing to restore', E_USER_WARNING);
        }
    }

    /**
     * Returns fast environment module options defaults.
     *
     * @return array
     * @see    Core::updateFastEnvOptions()
     * @amidev
     */
    /*
    public static function getFastEnvDefaults(){
        return self::$aFastEnvDefaults;
    }
    */

    /**
     * Sets fast environment module options.
     *
     * @param  array $aOptions  Options array
     * @return void
     * @amidev
     */
    /*
    public static function setFastEnvOptions(array $aOptions){
        foreach($aOptions as $modId => $aModOptions){
            foreach(self::$aFastEnvDefaults as $mask => $aDefaults){
                if(preg_match($mask, $modId)){
                    self::$aFastEnvOptions[$modId] = array_merge($aDefaults, $aModOptions);
                    break;
                }
            }
        }
    }
    */

    /**
     * Returns class name prefix by module id.
     *
     * @param  string $modId  Module id
     * @return string
     * @since  5.12.0
     */
    public static function getClassPrefix($modId){
        return implode('', array_map('ucfirst', explode('_', $modId)));
    }

    /**
     * Returns module config class name.
     *
     * @param  string $modId  Module id
     * @return string
     * @amidev Temporary
     */
    public static function getModuleConfigClassName($modId){
        list($hyper, $config) = AMI_ModDeclarator::getInstance()->getHyperData($modId);
        $configName = AMI::getClassPrefix($hyper) . '_' . AMI::getClassPrefix($config);
        // Workaround for categories, needed because config name is common for item module and cat module
        if(AMI::isCategoryModule($modId)){
            $configName .= 'Cat';
        }
        return $configName;
    }

    /**
     * Checks whether module is a category module.
     *
     * @param  string $modId  Module id
     * @return bool
     */
    public static function isCategoryModule($modId){
        return strpos(substr($modId, -4), '_cat') !== false;
    }

    /**
     * Return current environment mode ('fast', 'full').
     *
     * @return string
     * @amidev
     */
    public static function getEnvMode(){
        return isset($GLOBALS['Core']) && is_object($GLOBALS['Core']) && (get_class($GLOBALS['Core']) == 'CMS_Core') ? 'full' : 'fast';
    }

    /**
     * Returns CMS edition.
     *
     * Full environment required.
     *
     * @return string  'business' | 'minimarket' | 'vitrina' | 'corporate' | 'visitka' | 'free' | 'unknown'
     * @since  6.0.2
     */
    public static function getEdition(){
        return AMI_ModSettings::getOptions('cms', 'edition', TRUE);
    }

    /**
     * Adds resource by reconstructing its classname from given resource id.
     *
     * @param  string $resId     Resource Id
     * @param  bool   $autoload  Flag specifying to autoload resource class
     * @return bool
     */
    private static function _addResourceById($resId, $autoload = TRUE){
        $return = false;
        $className = self::_getExpectedClassNameByResourceId($resId, $autoload);
        AMI_Service::setAutoloadWarning(false);
        if(
            !is_null($className) &&
            ($autoload ? class_exists($className) : AMI_Service::autoload($className))
        ){
            self::addResource($resId, $className);
            $return = true;
        }
        AMI_Service::setAutoloadWarning(true);

        return $return;
    }

    /**
     * Reconstructs class name by resource id.
     *
     * @param  string $resId     Resource Id
     * @param  bool   $autoload  Flag specifying to autoload resource class
     * @return string
     */
    private static function _getExpectedClassNameByResourceId($resId, $autoload = TRUE){
        $aResId = explode('/', $resId);
        $modId      = $aResId[0];
        $type       = isset($aResId[1]) ? $aResId[1] : null;
        $subtype    = isset($aResId[2]) ? $aResId[2] : null;
        $last       = $aResId[count($aResId) - 1];

        if(is_null($type) || (in_array($type, array('module', 'table')) && !isset($subtype))){
            return null;
        }

        $classPrefix = self::getClassPrefix($modId);

        // For hypermodule instances only
        if(AMI_ModDeclarator::getInstance()->isRegistered($modId)){
            if(!in_array($modId, AMI::$aHyperConfigInstances)){
                AMI::$aHyperConfigInstances[] = $modId;
            }
            AMI_Service::setAutoloadWarning(false);
            $disableUserScripts = isset($GLOBALS["sys"]["disable_user_scripts"]) ? $GLOBALS["sys"]["disable_user_scripts"] : false;

            $classPostfix = AMI::_getDefaultResourceFilePostfix($modId, $type, $subtype, $last);
            $localClassName = $classPrefix . '_' . $classPostfix;
            $hasLocalClassName =
                $autoload ? class_exists($localClassName) : AMI_Service::autoload($localClassName);
            if($disableUserScripts || !$hasLocalClassName){
                if(!$hasLocalClassName){
                    // d::vd($classPrefix . '_' . $classPostfix);####
                    // Use hypermodule configuration class if no local sources present
                    $classPrefix = AMI::getModuleConfigClassName($modId);
                    // d::vd($classPrefix . '_' . $classPostfix);###
                    if(
                        !(
                            $autoload
                                ? class_exists($classPrefix . '_' . $classPostfix)
                                : AMI_Service::autoload($classPrefix . '_' . $classPostfix)
                        )
                    ){

                        return null;
                    }
                }
            }
            AMI_Service::setAutoloadWarning(true);
        }

        $aDefaultResourceMappingSchema = array(
            // models
            $modId . '/table/model'               => $classPrefix . '_Table',
            $modId . '/table/model/item'          => $classPrefix . '_TableItem',
            $modId . '/table/model/item/modifier' => $classPrefix . '_TableItemModifier',
            $modId . '/table/model/list'          => $classPrefix . '_TableList',

            // module
            $modId . '/module/controller/adm' => $classPrefix . '_Adm',
            $modId . '/module/controller/frn' => $classPrefix . '_Frn',
            $modId . '/module/model'          => $classPrefix . '_State',
            $modId . '/module/rules'          => $classPrefix . '_Rules',

            // list
            $modId . '/list/controller/adm'               => $classPrefix . '_ListAdm',
            $modId . '/list/controller/frn'               => $classPrefix . '_ListFrn',
            $modId . '/list/view/adm'                     => $classPrefix . '_ListViewAdm',
            $modId . '/list/view/frn'                     => $classPrefix . '_ListViewFrn',
            $modId . '/list_actions/controller/adm'       => $classPrefix . '_ListActionsAdm',
            $modId . '/list_group_actions/controller/adm' => $classPrefix . '_ListGroupActionsAdm',
            $modId . '/list_actions/controller/frn'       => $classPrefix . '_ListActionsFrn',
            $modId . '/list_group_actions/controller/frn' => $classPrefix . '_ListGroupActionsFrn',

            // filter
            $modId . '/filter/controller/adm' => $classPrefix . '_FilterAdm',
            $modId . '/filter/view/adm'       => $classPrefix . '_FilterViewAdm',
            $modId . '/filter/controller/frn' => $classPrefix . '_FilterFrn',
            $modId . '/filter/view/frn'       => $classPrefix . '_FilterViewFrn',

            // form
            $modId . '/form/controller/adm' => $classPrefix . '_FormAdm',
            $modId . '/form/view/adm'       => $classPrefix . '_FormViewAdm',
            $modId . '/form/controller/frn' => $classPrefix . '_FormFrn',
            $modId . '/form/view/frn'       => $classPrefix . '_FormViewFrn',

            // front specblocks
            $modId . '/specblock/controller/frn' => $classPrefix . '_Specblock',
            $modId . '/specblock/view/frn'       => $classPrefix . '_SpecblockView',

            // common view
            $modId . '/view/adm' => $classPrefix . '_ViewAdm',
            $modId . '/view/frn' => $classPrefix . '_ViewFrn',

            // service
            $modId . '/service' => $classPrefix . '_Service',

            // mail view
            $modId . '/mail/view' => $classPrefix . '_EmailView',
        );

        if(!in_array($type, array('table', 'module', 'list', 'form'))){
            $aClassParts = explode('_', $type);
            $classPart = '';
            foreach($aClassParts as $part){
                $classPart .= ucfirst($part);
            }
            $aDefaultResourceMappingSchema += array(
               $modId . '/' . $type . '/controller/adm' => $classPrefix . '_' . $classPart . 'Adm',
               $modId . '/' . $type . '/model/adm'      => $classPrefix . '_' . $classPart . 'ModelAdm',
               $modId . '/' . $type . '/view/adm'       => $classPrefix . '_' . $classPart . 'ViewAdm',
               $modId . '/' . $type . '/controller/frn' => $classPrefix . '_' . $classPart . 'Frn',
               $modId . '/' . $type . '/model/frn'      => $classPrefix . '_' . $classPart . 'ModelFrn',
               $modId . '/' . $type . '/view/frn'       => $classPrefix . '_' . $classPart . 'ViewFrn'
            );
        }

        if(AMI_Registry::get('side', '') === 'frn'){
            $aFrontComponents = array('items', 'subitems', 'sticky_items', 'details', 'cat_details', 'empty', '404', 'cats', 'sticky_cats', 'specblock');
            if(in_array($type, $aFrontComponents)){
                $aClassParts = explode('_', $type);
                $classPart = '';
                foreach($aClassParts as $part){
                    $classPart .= ucfirst($part);
                }
                $aDefaultResourceMappingSchema[$modId . '/' . $type . '/controller/frn'] = $classPrefix . '_' . $classPart . 'Frn';
                $aDefaultResourceMappingSchema[$modId . '/' . $type . '/view/frn'] = $classPrefix . '_' . $classPart . 'ViewFrn';
            }
        }

        if(isset($aDefaultResourceMappingSchema[$resId])){
            self::$aClassToModId[$aDefaultResourceMappingSchema[$resId]] = $modId;
        }

        return isset($aDefaultResourceMappingSchema[$resId]) ? $aDefaultResourceMappingSchema[$resId] : null;
    }

    /**
     * Returns default file postfix for the file that contains resource class declaration.
     *
     * @param  string $modId    Module id
     * @param  string $type     Resource type (table, module, service)
     * @param  string $subtype  Resource subtype
     * @param  string $last     Last resource token (adm, frn)
     * @return string
     */
    private static function _getDefaultResourceFilePostfix($modId, $type, $subtype, $last){
        if($last == 'frn'){
            return 'Frn';
        }elseif($type == 'table'){
            return 'Table';
        }elseif($subtype == 'rules'){
            return 'Rules';
        }elseif($type == 'service'){
            return 'Service';
        }
        return 'Adm';
    }

    /**
     * Forbidden constructor.
     */
    private function __construct(){
    }

    /**
     * Forbidden cloning.
     */
    private function __clone(){
    }
}

/**
 * Lazy object initialization class.
 *
 * <code>
 * // returns AMI_LazyFacade object
 * AMI::getResource('...', array(), true);
 * </code>
 *
 * @package Service
 * @since   5.12.4
 * @see     AMI::getResource()
 * @amidev
 */
class AMI_LazyFacade{
    /**
     * Object resource id
     *
     * @var string
     */
    protected $resId;

    /**
     * Object constructor arguments
     *
     * @var array
     */
    protected $aArgs;

    /**
     * Object instance
     *
     * @var object
     */
    protected $oInstace;

    /**
     * Constructor.
     *
     * @param string $resId  Object resource id
     * @param array  $aArgs  Object constructor arguments
     */
    public function __construct($resId, array $aArgs){
        $this->resId = (string)$resId;
        $this->aArgs = $aArgs;
    }

    /**
     * Magic facade caller.
     *
     * @param  string $method  Object method
     * @param  array $aArgs    Object method arguments
     * @return mixed
     */
    public function __call($method, array $aArgs){
        if(is_null($this->oInstace)){
            $this->oInstace = AMI::getResource($this->resId, $this->aArgs);
            unset($this->aArgs);
        }
        return call_user_func_array(array($this->oInstace, $method), $aArgs);
    }
}

/**
 * Benches
 *
 * @var array
 * @amidev
 */
$GLOBALS['aAMIBench'] = array(
    'DB' => array(
        'queryCount' => 0,
        'queryTime'  => 0,
        'fetchCount' => 0,
        'fetchTime'  => 0,
        'queries'    => ''
    ),
    'benches' => array()
);
