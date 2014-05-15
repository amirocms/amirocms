<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Environment
 * @version   $Id: AMI_ErrorHandler.php 49358 2014-04-03 07:16:20Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary?
 */

// PHP < 5.3.0 compatibility
if(!defined('E_STRICT')){
    /**
     * @amidev
     */
    define('E_STRICT', 2048);
}
if(!defined('E_DEPRECATED')){
    /**
     * @amidev
     */
    define('E_DEPRECATED', 8192);
}
if(!defined('E_USER_DEPRECATED')){
    /**
     * @amidev
     */
    define('E_USER_DEPRECATED', 16384);
}

/**
 * Error handler.
 *
 * @package Environment
 * @since   x.x.x
 * @amidev  Temporary?
 */
final class AMI_ErrorHandler{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    const MESSAGE_HTML = "</td></tr></table>\n<br><font color=\"red\" size=2><b>[#_E_TYPE#] [code = [#_E_CODE#]]:</b></font>&nbsp;<font color=\"black\">[#_E_BODY#]</font>\n";

    // const MESSAGE_TEXT = "[[#_E_DATE#]] ['[#_E_TYPE#]'] [code=[#_E_CODE#]]: [#_E_BODY#]";
    const MESSAGE_TEXT = "['[#_E_TYPE#]'] [code=[#_E_CODE#]]: [#_E_BODY#]";

    /*
    const TERMINATE_MSG =
        "</td></tr></table>\n<br><font color=\"red\" size=2><b>You are not allowed to view this page.</b></font><br>\n";
    */

    // mail collector {

    const SAME_ERRORS_QTY = 10;
    const SAME_ERRORS_PERIOD = 1; // in minutes

    // } mail collector

    /**
     * Instance
     *
     * @var AMI_ErrorHandler
     */
    private static $oInstance;

    /**
     * Same reports quantity string
     *
     * @var string
     * @see AMI_ErrorHandler::mailCollector()
     */
    private $reportQty = '';

    /**
     * Same reports period string
     *
     * @var string
     * @see AMI_ErrorHandler::mailCollector()
     */
    private $reportPeriod = '';

    /**
     * Path to log file
     *
     * @var string
     */
    private $logPath;

    /**
     * Plugin id
     *
     * @var string
     */
    private $pluginId;

    /**
     * Error codes string representation
     *
     * @var array
     */
    private $aErrors = array(
        E_ERROR             => 'PHP: FATAL ERROR',
        E_WARNING           => 'PHP: WARNING',
        E_PARSE             => 'PHP: PARSE ERROR',
        E_NOTICE            => 'PHP: NOTICE',
        E_CORE_ERROR        => 'PHP: CORE ERROR',
        E_CORE_WARNING      => 'PHP: CORE WARNING',
        E_COMPILE_ERROR     => 'PHP: COMPILE ERROR',
        E_COMPILE_WARNING   => 'PHP: COMPILE WARNING',
        E_USER_ERROR        => 'USER: ERROR',
        E_USER_WARNING      => 'USER: WARNING',
        E_USER_NOTICE       => 'USER: NOTICE',
        E_RECOVERABLE_ERROR => 'PHP: RECOVERABLE ERROR',
        E_STRICT            => 'PHP: E_STRICT',
        E_DEPRECATED        => 'PHP: DEPRECATED',
        E_USER_DEPRECATED   => 'USER: DEPRECATED'
    );

    /**
     * Returns AMI_ErrorHandler instance.
     *
     * @return AMI_ErrorHandler
     */
    public static function getInstance(){
        if(is_null(self::$oInstance)){
            self::$oInstance = new AMI_ErrorHandler();
        }
        return self::$oInstance;
    }

    /**
     * Logs error.
     *
     * @param  string $message  Message
     * @param  int    $code     Code
     * @return void
     * @amidev
     */
    public function log($message, $code = E_USER_NOTICE){
        if($GLOBALS['sys']['err']['store']){
            $details = '';
            if($GLOBALS['sys']['err']['env']){
                $details = '] [Details: ' . AMI_Service::getEnvInfo();
            }
            $message = " ['" . $message . "'" . $details . "] ";
            $message = str_replace('[#_E_BODY#]', $message, $this->getMessage($code));
            AMI_Service::log($message, $this->logPath);
        }
    }

    /**
     * Sets plugin Id.
     *
     * @param  string $pluginId  Plugin Id
     * @return void
     * @amidev
     */
    public function setPluginId($pluginId){
        $this->pluginId = $pluginId;
    }

    /**
     * Error handler.
     *
     * @param  int    $code     Code
     * @param  string $message  Message
     * @param  string $file     File
     * @param  int    $line     Line
     * @param  array  $aTrace   Force trace
     * @return void
     */
    public function handleError($code, $message, $file, $line, array $aTrace = null){
        global $sys, $modId;

        $pluginMod = '';
        $pluginErrEmailAddr = '';
        $pluginErrEmailSubj = '';
        if($this->pluginId){
            // Old plugins support
            $pluginMod = $this->pluginId;
        }elseif(!empty($modId) && AMI::isModInstalled($modId) && AMI_Registry::exists('_source_mod_id')){
            $pluginMod = $modId;
        }

        if($pluginMod){
            // Try t get plugin developer e-mail
            $aPluginConfig = @parse_ini_file(AMI::getPluginPath($pluginMod) . 'config.php');
            if(
                !empty($aPluginConfig['errors_notification_email']) &&
                AMI_Lib_String::validateEmail($aPluginConfig['errors_notification_email'])
            ){
                $pluginErrEmailAddr = $aPluginConfig['errors_notification_email'];
            }
            $pluginErrEmailSubj = 'PLUGIN ' . $pluginMod . ' ';
        }

        $details = '';
        if(!empty($sys['err']['env'])){
            $details = ' [Details: ' . AMI_Service::getEnvInfo() . ' ]';
        }
        $body = " ['" . $message . "'] [File: '" . $file . "'] [Line: " . $line . ']';
        $btrace = "\n" . $this->getTraceAsString(true, $aTrace) . "\n";
        $text = str_replace("[#_E_BODY#]", $body . $details, $this->getMessage($code));
        if($code != E_NOTICE && !empty($sys['err']['store'])){
            if(!empty($sys['err']['store_trace']) && ($code & $sys['err']['store_trace'])){
                $text .= "\r\n" . $this->getTraceAsString(true, $aTrace);
            }
            AMI_Service::log($text, $this->logPath);
        }
        $details = '';
        if(AMI_Service::isDebugVisible() && !empty($sys['err']['messages']) && !empty($sys['err']['extdeb'])){
            $details =
                str_replace('[#_E_BODY#]', $body, $this->getMessage($code, true)) .
                (E_USER_NOTICE !== $code ? $this->getTraceAsString(false, $aTrace) : '') . "\n";
            d::write($details);
        }

        // mail notification {

        $forceDisableEmail = AMI_Registry::get('disable_error_mail', false) || (
            $GLOBALS['_SIDE'] == 'admin' && isset($_GET['step']) && $_GET['step'] == 1 &&
            isset($_GET['action']) && $_GET['action'] == 'refresh'
        );
        if(
            !$forceDisableEmail && defined('AMIRO_HOST') &&
            (!empty($sys['err']['email']) || $pluginErrEmailAddr) &&
            !in_array($code, array(E_NOTICE, E_USER_NOTICE)) &&
            $this->mailCollector($message)
        ){
            // email messages
            $vstr = '';
            if(isset($GLOBALS['Core'])){
                $vers = $GLOBALS['Core']->getVersion('cms');
                require $GLOBALS['HOST_PATH'] . "_shared/code/lib/lo.php";
                $vstr .= "\nVersions: cms=" . $vers['code'] . '  req_db='.$vers['db'] . '  act_db=' . $vers['act_db'] . "\n";
            }
            $oRequest = AMI::isResource('env/request') ? AMI::getSingleton('env/request') : new AMI_RequestHTTP;
            $message = mb_substr($message, 0, 3 * 1024);
            $mailBody =
                "\nclient_ip = " . $oRequest->getEnv('REMOTE_ADDR') .
                "\nhost_ip = " . $oRequest->getEnv('SERVER_ADDR') . ',' .
                @getenv('LOCAL_ADDR') . "\nwww_path = " . $GLOBALS['ROOT_PATH_WWW'] . "\n\n" .
                "Msg:\t" . $message . "\n" .
                "Date:\t" . date(self::DATE_FORMAT) . "\n" .
                "Code:\t" . $code . ' ' . (isset($this->aErrors[$code]) ? $this->aErrors[$code] : 'UNKNOWN') . "\n" .
                "Src:\t" . $file . ':' . $line . "\n" .
                "URL:\t" . $GLOBALS['ROOT_PATH_WWW'] . ltrim(@$oRequest->getURL('uri'), '/') . "\n";
            if(AMI_Registry::get('eh_enable_post_data', false) && !empty($_POST)){
                $mailBody .= "POST:\n";
                foreach($_POST as $postKey => $postValue){
                    $mailBody .= "\t[" . $postKey . "] => " . (is_array($postValue) ? 'Array(' . sizeof($postValue) . ')' : mb_substr($postValue, 0, 200) . (mb_strlen($postValue) > 200 ? '...' : '')) . "\n";
                }
            }
            $mailBody .= "Env:\n\t" . AMI_Service::getEnvInfo("\n\t") .
            "\nPHP:\t" . phpversion() . "\nOS:\t" . php_uname() . "\n";
            if($this->reportPeriod){
                $mailBody .= "\nSame errors period:\n\t" . $this->reportPeriod . "\n";
            }
            if(sizeof($_COOKIE)){
                $aCookies = $_COOKIE;
            }elseif(isset($GLOBALS['cms']) && is_object($GLOBALS['cms']) && sizeof($GLOBALS['cms']->VarsCookie)){
                $aCookies = $GLOBALS['cms']->VarsCookie;
            }else{
                $aCookies = array();
            }
            if(sizeof($aCookies)){
                $cookies = array();
                foreach($aCookies as $name => $value){
                    $cookies[] = $name . ' = ' . $value;
                }
                $mailBody .= "\nCookies:\n\t" . implode("\n\t", $cookies) . "\n";
            }
            $mailBody .= $vstr;
            if($pluginErrEmailAddr){
                $pluginErrEmailBody = $mailBody;
            }

            if(!empty($sys['err']['email'])){
                if(
                    mb_substr($sys['err']['email'], -8) == 'amiro.ru' ||
                    mb_substr($sys['err']['email'], -17) == '@websitemaster.ru'
                ){
                    $mailBody .= $btrace;
                }
                $mailBody = mb_substr($mailBody, 0, 10000); // 5120

                // Don't send 'deprecated' UZ error to us. Hack.
                if(AMI_Registry::get('_deprecated_error', false) && mb_strpos($mailBody, '/uz_') !== FALSE){
                	AMI_Registry::delete('_deprecated_error');
                    // }elseif($pluginErrEmailSubj !== '' && mb_strpos($pluginMod, 'uz_') === 0){
                    // UZ plugin report
                }else{
                    $sum = 0;
                    for($i = 0, $q = mb_strlen($message, 'ASCII'); $i < $q; $i++){
                        $sum += ord($message[$i]);
                    }
                    $subject = $pluginErrEmailSubj . 'ERROR REPORT: ' . sprintf('%04X', $sum) . ' SITE ' . $GLOBALS['ROOT_PATH_WWW'] . $this->reportQty;
                	@mail($sys['err']['email'], $subject, $mailBody);
                }
            }

            // Send error notification to plugin owner
            if($pluginErrEmailAddr){
                @mail($pluginErrEmailAddr, $pluginErrEmailSubj . 'ERROR REPORT: SITE ' . $GLOBALS['ROOT_PATH_WWW'] . $this->reportQty, mb_substr($pluginErrEmailBody, 0, 10000));
            }
        }

        // } mail notification

        $isFatal = $code === E_USER_ERROR;
        if(class_exists('AMI_Response', false) && !defined('AMI_60')){
            /**
             * @var AMI_Response
             */
            $oResponse = AMI::isResource('response') ? AMI::getSingleton('response') : AMI_Response::getInstance();
            if($isFatal){
                // Exitpoint
                /*
                $text =
                    !empty($sys['err']['messages']) && !empty($sys['err']['extdeb'])
                    ? str_replace("[#_E_BODY#]", $body, $this->getMessage($code, true)) . $this->getTraceAsString(false, $aTrace) . "\n";
                    : self::TERMINATE_MSG;
                */
                if(empty($sys['err']['messages']) || empty($sys['err']['extdeb'])){
                    $text = require(dirname(__FILE__) . '/AMI_ErrorHandler_Message.php');
                }else{
                    $text =
                        $oResponse->getType() === 'HTML' && !empty($sys['err']['messages'])
                        ? AMI_Debug::getBuffer()
                        : str_replace("[#_E_BODY#]", $body, $this->getMessage($code, true)) . $this->getTraceAsString(false, $aTrace) . "\n";
                }
                $oResponse->setMessage($text, $code);
                $oResponse->HTTP->setServiceUnavailable(3600);
                if($oResponse->getType() === 'HTML'){
                    if(!$oResponse->isStarted()){
                        $oResponse->start();
                    }
                    $oResponse->write($text);
                }
                $oResponse->send();
            }
        }elseif($isFatal){
            // Exitpoint
            if(!defined('AMI_60') && function_exists('display503Header') && AMI_Response::getInstance()->getType() !== 'JSON'){
                display503Header(3600);
            }
            if($details !== ''){
                $buffer = AMI_Debug::getBuffer();
                if($buffer !== ''){
                    $details = $buffer;
                }
            }
            if($details === ''){
                $details = require(dirname(__FILE__) . '/AMI_ErrorHandler_Message.php');
            }
            // die($details === '' ? self::TERMINATE_MSG : $details);
            die($details);
        }
    }

    /**
     * Constructor.
     */
    private function __construct(){
        $this->logPath = $GLOBALS['ROOT_PATH'] . '_admin/_logs/err.log';
    }

    /**
     * Returns error message by internal template.
     *
     * @param  int  $code   Code
     * @param  bool $bHTML  Return as HTML
     * @return string
     */
    private function getMessage($code, $bHTML = false){
        if($bHTML){
            $res = str_replace("[#_E_CODE#]", $code, self::MESSAGE_HTML);
        }else{
            $res = str_replace("[#_E_DATE#]", date(self::DATE_FORMAT), self::MESSAGE_TEXT);
            $res = str_replace("[#_E_CODE#]", $code, $res);
        }
        $type = isset($this->aErrors[$code]) ? $this->aErrors[$code] : 'UNKNOWN';
        $res = str_replace("[#_E_TYPE#]", $type, $res);
        return $res;
    }

    /**
     * Returns trace as string.
     *
     * @param  bool  $bPrevFormat  Use previous format
     * @param  array $aTrace       Trace
     * @return string
     */
    private function getTraceAsString($bPrevFormat = true, array $aTrace = null){
        if(is_null($aTrace)){
            $aTrace = debug_backtrace();
            // Skip calls to error handling functions
            for($i=0; $i < ($bPrevFormat ? 4 : 3); $i++){
                array_shift($aTrace);
            }
        }

        if(!$bPrevFormat){
            return AMI_Debug::getTraceAsString($aTrace);
        }

        // Iterate backtrace
        $calls = array();
        foreach($aTrace as $i => $call){
            $location = isset($call['file']) ? $call['file'] . ':' . $call['line'] : '';
            $function = (isset($call['class'])) ?
                $call['class'] . '.' . $call['function'] :
                $call['function'];
            $params = '';
            if(isset($call['args'])){
                $aParams = array();
                foreach($call['args'] as $arg){
                    if(is_array($arg)){
                        $entity = 'Array(' . sizeof($arg);
                    }elseif(is_object($arg)){
                        $entity = 'Object';
                    }else{
                        $entity = (string)$arg;
                    }
                    $aParams[] = $entity;
                }
                $params = implode(', ', $aParams);
            }
            $calls[] = sprintf(
                "#%d  %s(%s) \n\t called at [%s]",
                $i, $function, $params, $location
            );
        }
        return implode("\n", $calls);
    }

    /**
     * Returns true if email should be sent.
     *
     * @param  string $message  Error message
     * @return bool
     */
    private function mailCollector($message){
        static $dbh, $table;

        $this->reportQty = '';
        $this->reportPeriod = '';
        if(!isset($dbh)){
            if($GLOBALS['_h']['mode'] === 'shared'){
                if(isset($GLOBALS['CONNECT_OPTIONS']['ERROR_User'])){
                    $dbh = mysql_connect(
                        $GLOBALS['CONNECT_OPTIONS']['ERROR_Host'],
                        $GLOBALS['CONNECT_OPTIONS']['ERROR_User'],
                        $GLOBALS['CONNECT_OPTIONS']['ERROR_Password'],
                        FALSE,
                        isset($GLOBALS['CONNECT_OPTIONS']['ERROR_ClientFlags'])
                            ? (int)$GLOBALS['CONNECT_OPTIONS']['ERROR_ClientFlags']
                            : 0
                    );
                    if(!is_resource($dbh)){
                        return true;
                    }
                }
                $table = '`' . $GLOBALS['CONNECT_OPTIONS']['ERROR_Database'] . '`.`cms_errors`';
            }else{
                if(empty($GLOBALS['db']) || !is_object($GLOBALS['db'])){
                    return true;
                }
                $dbh = $GLOBALS['db']->_dbLink;
                $table = '`cms_errors`';
            }
        }
        $label = debug_backtrace();
        unset($label[0], $label[1]);
        foreach(array_keys($label) as $index){
            unset($label[$index]['args'], $label[$index]['object']);
        }
        $label = crc32($message . serialize($label));
        $sql = "SELECT *, NOW() `now` FROM " . $table . " WHERE `label` = " . $label;
        if(!is_resource($result = @mysql_query($sql, $dbh))){
            return true;
        }
        $bSend = false;
        if(mysql_num_rows($result)){
            $aRecord = mysql_fetch_assoc($result);
            $sameErrorsPeriod = self::SAME_ERRORS_PERIOD * pow(2, $aRecord['period'] << 1);
            if(((strtotime($aRecord['now']) - strtotime($aRecord['rdate'])) / 60) < $sameErrorsPeriod){
                $sql = "UPDATE " . $table . " SET `counter` = `counter` + 1 WHERE `label` = " . $label;
            }else{
                $sql = "UPDATE " . $table . " SET `rdate` = NOW(), `counter` = 1, `rqty` = `rqty` + 1";
                $sameErrorsQuantity = self::SAME_ERRORS_QTY * pow(2, $aRecord['period']);
                if($aRecord['counter'] >= $sameErrorsQuantity){
                    $sql .= ", `period` = `period` + 1";
                }else{
                    $period = ceil(log($aRecord['counter'] / self::SAME_ERRORS_QTY, 2));
                    $sql .= ", `period` = " . ($period < 0 ? 0 : $period);
                }
                $sql .= " WHERE `label` = " . $label;
                $this->reportPeriod = $sameErrorsPeriod . ' minutes';
                $this->reportQty = ' (' . $aRecord['counter'] . ' occurrences)';
                $bSend = true; // send email
            }
        }else{
            $sql = "INSERT INTO " . $table . " VALUES (" . $label . ", NOW(), NOW(), 1, 0, 1)";
            $this->reportQty = ' (first occurrence)';
            $bSend = true; // send email
        }
        if(@mysql_query($sql, $dbh) === false){
            return true;
        }
        return $bSend;
    }
}
