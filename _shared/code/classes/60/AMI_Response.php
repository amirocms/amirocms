<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Service
 * @version   $Id: AMI_Response.php 50253 2014-04-24 06:28:54Z Leontiev Anton $
 * @since     5.10.0
 */

/**
 * CMS response.
 *
 * <b>Attention! Do not use response functionality in plugin CMS context code!</b><br />
 * Plugin CMS context is always buffered and system is expecting plugin response in $resultHtml variable.<br /><br />
 *
 * Example:
 * <code>
 * require 'ami_env.php';
 * // @var AMI_Response
 * $oResponse = AMI::getSingleton('response');
 * $oResponse->HTTP->setContentType('text/plain');
 * $oResponse->HTTP->addHeader('Pragma: no-cache');
 * $oResponse->HTML->addStyle('someStyles.css');
 * $oResponse->HTML->addScript('someScript.js');
 * $oResponse->start();
 * $oResponse->write('Hello world');
 * $oResponse->send();
 * </code>
 *
 * @package       Service
 * @resource      response <code>AMI::getSingleton('response')</code>
 * @property-read AMI_HTTPResponse $HTTP  HTTP response part
 * @property-read AMI_HTMLResponse $HTML  HTML response part
 */
class AMI_Response{
    /**
     * Logs warning if script total time exceeds this constant in fast environment mode
     *
     * @amidev
     */
    const FAST_ENV_MAX_EXECUTION_TIME = 0.4;

    /**
     * Default status message code
     */
    const STATUS_MESSAGE = 'none';

    /**
     * 'Warning' status message code
     */
    const STATUS_MESSAGE_WARNING = 'warn';

    /**
     * 'Error' status message code
     */
    const STATUS_MESSAGE_ERROR = 'error';

    /**
     * Instance
     *
     * @var AMI_Resoponse
     */
    private static $oInstance;

    /**
     * Sttart time (result of microtime(true))
     *
     * @var float
     */
    private $startTime = 0;

    /**
     * Response type ('HTML'|'JSON')
     *
     * @var string
     */
    private $type = 'HTML';

    /**
     * Is response buffered flag
     *
     * @var bool
     */
    private $isBuffered = true;

    /**
     * Is response started flag
     *
     * @var bool
     */
    private $isStarted = false;

    /**
     * Getter access objects
     *
     * @var array
     */
    private $aReadOnlyObjects = array();

    /**
     * JSON data
     *
     * @var mixed  null|array
     * @see AMI_Response::write()
     */
    private $aJSONData;

    /**
     * Error message
     *
     * @var string
     * @see AMI_Response::setMessage()
     */
    private $message = '';

    /**
     * Error code
     *
     * @var int
     * @see AMI_Response::setMessage()
     */
    private $code;

    /**
     * JSON response redirection URL
     *
     * @var string
     */
    private $jsonRedirect = '';

    /**
     * Specifies that page should be reloaded after the response was send
     *
     * @var bool
     */
    private $needPageReload = false;

    /**
     * @var array
     * @see AMI_Response::addErrorMessage()
     */
     // private $aErrorMessages = array();

    /**
     * Display benches flag
     *
     * @var bool
     * @see AMI_Response::displayBench()
     */
    private $bBench = false;

    /**
     * Total sleep time
     *
     * @var float
     * @see AMI_Response::sleep()
     */
    private $sleepTime = 0;

    /**
     * Page modules, used by cache to drop full environment pages
     *
     * @var array
     */
    /*
    private $aPageModules = array();
    */
    /**
     * Page module, used by cache to drop full environment pages
     *
     * @var  string
     * @link https://pm.cmspanel.net/show_bug.cgi?id=4569#c1
     */
    private $pageModId = '';

    /**
     * Status messages locale
     *
     * @var array
     */
    private $aStatusMessagesLocale = array();

    /**
     * Status messages
     *
     * @var array
     * @see AMI_Response::addStatusMessage()
     */
    private $aStatusMessages = array();

    /**
     * Bench type
     *
     * @var string
     */
    private $benchType = '';

    /**
     * Returns an instance of an AMI_Response.
     *
     * @param  array $aArgs  Constructor arguments
     * @return AMI_Resoponse
     * @amidev
     */
    public static function getInstance(array $aArgs = array()){
        if(is_null(self::$oInstance)){
            self::$oInstance =
                isset($aArgs[1])
                ? new AMI_Response($aArgs[0], $aArgs[1])
                : new AMI_Response(isset($aArgs[0]) ? $aArgs[0] : 0);
        }
        return self::$oInstance;
    }

    /**
     * Getter for AMI_Response::$aReadOnlyObjects.
     *
     * @param  string $name  R/O object name
     * @return AMI_HTTPResponse|AMI_HTMLResponse|void
     * @amidev
     */
    public function __get($name){
        if(isset($this->aReadOnlyObjects[$name])){
            return $this->aReadOnlyObjects[$name];
        }else{
            trigger_error('Undefined property: AMI_Response::$' . $name, E_USER_NOTICE);
        }
    }

    /**
     * Setter for AMI_Response::$aReadOnlyObjects.
     *
     * @param  string $name  Property name
     * @param  mixed $value  Property value
     * @return void
     * @amidev
     */
    public function __set($name, $value){
        if(isset($this->aReadOnlyObjects[$name])){
            trigger_error('Readonly property: AMI_Response::$' . $name, E_USER_NOTICE);
        }else{
            $this->$name = $value;
        }
    }

    /**
     * Sets response type.
     *
     * @param  string $type  Type ('HTML'|'JSON')
     * @return AMI_Resoponse
     * @amidev
     */
    public function setType($type){
        switch($type){
            case 'HTML':
                $this->aReadOnlyObjects['HTTP']->setContentType('text/html');
                break;
            case 'JSON':
                $this->aReadOnlyObjects['HTTP']->setContentType('text/plain');
                break;
            /*
            case 'raw':
            case 'none':
            */
                break;
            default:
                trigger_error("Unknown response type '" . $type. "'", E_USER_ERROR);
        }
        $this->type = $type;
        return $this;
    }

    /**
     * Specifies buffered / non buffered response usage.
     *
     * @param  bool $isBuffered  TRUE if buffered output needed
     * @param  bool $force       TRUE to avoid fatal error on response start already
     * @return this
     * @amidev
     */
    public function setBuffering($isBuffered, $force = FALSE){
        if($this->isStarted){
            trigger_error('Response is already started', $force ? E_USER_NOTICE : E_USER_ERROR);
        }
        if(!$isBuffered && $this->isBuffered !== (bool)$isBuffered){
            while(ob_get_level() > 0){
                ob_end_clean();
            }
        }
        $this->isBuffered = (bool)$isBuffered;

        return $this;
    }

    /**
     * Delay execution.
     *
     * Metod is used for delay taking into benches.
     *
     * @param  float $sleep  Delay in microseconds
     * @return AMI_Response
     */
    public function sleep($sleep){
        $sleep = (float)$sleep;
        usleep($sleep);
        $this->sleepTime += $sleep;
        return $this;
    }

    /**
     * Returns true if response is already started.
     *
     * @return bool
     * @since  5.14.4
     */
    public function isStarted(){
        return $this->isStarted;
    }

    /**
     * Starts reponse.
     *
     * @return AMI_Resoponse
     */
    public function start(){
        if($this->isStarted){
            trigger_error('Response is already started', E_USER_ERROR);
        }
        $this->isStarted = TRUE;
        if($this->isBuffered){
            ob_start();
        }else{
            $aEvent = array(
                'type'         => $this->type,
                'isBuffered'   => FALSE,
                'aHTTPHeaders' => $this->aReadOnlyObjects['HTTP']->getHeaders()
            );
            /**
             * Allows receive information and modify headers of answer.
             *
             * @event      on_response_start AMI_Event::MOD_ANY
             * @eventparam string type        Type
             * @eventparam bool   isBuffered  Buffering
             * @eventparam array aHTTPHeaders  Array of HTTP headers
             */
            AMI_Event::fire('on_response_start', $aEvent, AMI_Event::MOD_ANY);
            $this->sendHeaders($aEvent['aHTTPHeaders']);
        }
        return $this;
    }

    /**
     * Writes data to the response.
     *
     * @param  mixed $data  String for HTML response type or any data for JSON response type.
     * @return AMI_Resoponse
     */
    public function write($data){
        if(!$this->isStarted){
            trigger_error("Response isn't started yet", E_USER_ERROR);
        }
        /*
        $aEvent = array(
            'type'       => $this->type,
            'isBuffered' => $this->isBuffered,
            'data'       => &$data
        );
        AMI_Event::fire('on_response_write', $aEvent, AMI_Event::MOD_ANY);
        */
        if($this->isBuffered && $this->type === 'JSON'){
            if(!is_null($data)){
                if(!is_null($this->aJSONData)){
                    trigger_error('JSON data can be written only once', E_USER_ERROR);
                }
                // Store data for sending JSON-response
                $this->aJSONData = $data;
            }
        }else{
            echo $data;
            if(!$this->isBuffered){
                flush();
            }
        }
        return $this;
    }

    /**
     * Sends response and exits.
     *
     * @return void
     * @exitpoint
     */
    public function send(){
        global $conn;

        $bBufferedJSON = $this->isBuffered && $this->type === 'JSON';
        if($bBufferedJSON){
            ob_end_clean();
            $content = $this->getJSONResponse($this->aJSONData);
            $this->aJSONData = null;
        }else{
            $content = ob_get_clean();
        }
        $aEvent = array(
            'type'       => $this->type,
            'isBuffered' => $this->isBuffered,
            'content'    => &$content
        );
        if($this->isBuffered){
            $aEvent['aHTTPHeaders'] = $this->aReadOnlyObjects['HTTP']->getHeaders();
        }
        /**
         * Allows receive information and modify headers buffered response, change response content.
         *
         * @event      on_response_send AMI_Event::MOD_ANY
         * @eventparam string type           Response type
         * @eventparam bool   isBuffered     TRUE if response is buffered
         * @eventparam string &content       Content
         * @eventparam array  aHTTPHeaders   Array of HTTP headers
         */
        AMI_Event::fire('on_response_send', $aEvent, AMI_Event::MOD_ANY);
        $isFullEnv = isset($GLOBALS[AMI_Registry::get('side', 'frn')]);
        $isConnectorPresent = isset($GLOBALS['conn']);
        if($this->isBuffered){
            if((AMI_Registry::get('side', 'frn') == 'frn') && $isFullEnv){
                global $frn, $Core;
                if($this->code === E_USER_ERROR){
                    if($isConnectorPresent){
                        $conn->Cache->Disable();
                    }
                    die($bBufferedJSON ? $content : $this->message);
                }
                if($isConnectorPresent){
                    foreach($aEvent['aHTTPHeaders'] as $header => $aHeader){
                        $conn->AddHeader($header, $aHeader[0], $aHeader[1]);
                    }
                }else{
                    $this->sendHeaders($aEvent['aHTTPHeaders']);
                }
                echo $content;
                unset($content);
                if($isConnectorPresent){
                    $conn->Cache->pageIsComplitedForSave = true;
                    $Core->Cache->SetOption('DFMT_php', $frn->DFMT['php']);
                    $pageModId = $this->pageModId === '' ? 'pages' : $this->pageModId;
                    $conn->Cache->SetPageModule($pageModId);
                }
                /*
                if(sizeof($this->aPageModules)){
                    // hack
                    $conn->Cache->SetPageModule('pages');
                    $specBlockName = 'spec_small_relations_0';
                    $hash = implode('_', $this->aPageModules);
                    $conn->Cache->SetSpecBlockNames(array($specBlockName), true);
                    $conn->Cache->AddModuleHashData('hash', $hash);
                    $conn->Cache->PrepareCachedBlocks();
                    // $conn->Cache->AddDynamicSpecData($specBlockName . '_hash', $hash);
                    // $conn->Cache->UseSpecBlocks = true;
                }
                */
                if($isConnectorPresent){
                    if($this->type === 'JSON'){
                        $conn->forbidContentModification();
                    }
                    $conn->DisableSign = true;
                    $conn->Out(); // exitpoint
                }else{
                    if(isset($GLOBALS['oCache'])){
                        $GLOBALS['oCache']->End();
                    }
                }
                die;
            }else{
                $this->sendHeaders($aEvent['aHTTPHeaders']);
            }
        }
        // full env is always buffered
        // TODO: Need to understand why '>0' doesn't work
        // Artem found out that Connector calls ob_start twice for gzipped content
        while(ob_get_level() > 1){
            ob_end_clean();
        }
        // Send debug info.
        if($this->type === 'HTML'){
            if(AMI_Service::isDebugVisible() && !empty($GLOBALS['sys']['err']['extdeb'])){
                $this->aReadOnlyObjects['HTML']->writeDebug(
                    (empty($GLOBALS['sys']['err']['show_bench']) ? '' : AMI_Service::getSimpleBenchInfo($GLOBALS['bench_time'], FALSE, $this->sleepTime / 1000000) . '<br><br>') .
                    (empty($GLOBALS['sys']['err']['messages']) ? '' : $GLOBALS['aAMIBench']['DB']['queries'] . AMI_Debug::getBuffer())
                );
                $this->aReadOnlyObjects['HTML']->sendDebug($content);
            }
        }
        echo $content;
        unset($content);
        if(isset($GLOBALS['oCache'])){
            $GLOBALS['oCache']->End();
        }
        if(!defined('AMI_60')){
            // Check execution time
            $executionsTime = microtime(true) - $this->startTime;
            $maxExecutionTime = self::FAST_ENV_MAX_EXECUTION_TIME + $this->sleepTime / 1000000;
            if($executionsTime > $maxExecutionTime){
                AMI_Service::log(
                    'Fast env execution time exceeds maximum (' . number_format($executionsTime, 4, '.', '') .
                    ' > ' . number_format($maxExecutionTime, 4, '.', '') . '): ' .
                    AMI::getSingleton('env/request')->getURL('uri'), $GLOBALS['ROOT_PATH'] . '_admin/_logs/err.log'
                );
            }
        }
        if(!empty($GLOBALS['BENCH_LOG_FILE'])){
            // Write bench log info
            $type = strip_tags($this->benchType);
            if(!$type){
                $type = 'XXX';
                if(isset($conn) && $conn->benchType){
                    $type = $conn->benchType;
                }
                ###$type = (AMI_Registry::get('side', 'frn') !== 'adm' ? '' : 'ADMIN-') . 'ENV-' . ($isFullEnv ? 'FULL' : 'FAST');
            }
            $message =
                "['USER: NOTICE'] [code=1024]:  ['" . $GLOBALS['ROOT_PATH_WWW'] . ': '.
                $type . ': ' . AMI_Service::getSimpleBenchInfo($GLOBALS['bench_time'], TRUE, $this->sleepTime / 1000000) .
                "'] [Details: " . AMI_Service::getEnvInfo() . "]";
            AMI_Service::log(
                $message,
                $GLOBALS['BENCH_LOG_FILE'],
                $GLOBALS['BENCH_LOG_FILE_SIZE']
            );
        }
        // $this->backgroundProcess();
        die;
    }

    /**
     * Returns response type.
     *
     * @return string  'HTML'|'JSON'
     * @amidev
     */
    public function getType(){
        return $this->type;
    }

    /**
     * Sets error message and code.
     *
     * @param  string $message  Message
     * @param  int    $code     Code
     * @return AMI_Response
     * @amidev
     */
    public function setMessage($message, $code = 0){
        $this->message = (string)$message;
        $this->code = (int)$code;
        return $this;
    }

    /**
     * @param  string $message
     * @param  int    $code
     * @return AMI_Response
     * @amidev
     */
    /*
    public function addErrorMessage($message, $code = 0){
        // if($this->type === 'HTML'){
            AMI_Debug::write($message);
        // }
        // $this->aErrorMessages[] = array('message' => (string)$message, 'code' => (int)$code);
        return $this;
    }
    */

    /**
     * Writes content and finishes script execution.
     *
     * @param  string $content  Content
     * @return void
     * @amidev Temporary
     */
    public function directOutput($content){
        echo $content;
        die;
    }

    /**
     * Adds page module, used by cache to drop full environment pages.
     *
     * @param  string $modId  Module id
     * @return AMI_Response
     * @todo   Find out right "specblock" names
     * @amidev
     */
    public function addPageModule($modId){
        if(isset($GLOBALS['frn']) && $this->pageModId === ''){
            // $this->aPageModules[$modId] = $modId;
            $this->pageModId = $modId;
        }
        return $this;
    }

    /**
     * Enables bench info in response.
     *
     * @return AMI_Response
     */
    public function displayBench(){
        $this->bBench = TRUE;
        return $this;
    }

    /**
     * Loads status message loacales.
     *
     * Example:
     * <code>
     * // AmiSample_Adm.php::__construct()
     * parent::__construct($oRequest, $oResponse);
     * $oResponse->loadStatusMessages('_local/plugins_distr/' . $this->getModId() . '/templates/messages.lng');
     * </code>
     *
     * @param  string $path   Locales path
     * @param  string $modId  Module id
     * @return AMI_Response
     * @since  5.12.4
     * @see    AMI_Response::addStatusMessage()
     */
    public function loadStatusMessages($path, $modId = ''){
        $aLocale = AMI::getResource('env/template_sys')->parseLocale($path);
        if($aLocale && is_array($aLocale)){
            $this->mergeStatusMessages($aLocale, $modId);
        }
        return $this;
    }

    /**
     * Merges status messages locales.
     *
     * @param  array $aLocale  Locales
     * @param  string $modId   Module Id
     * @return void
     * @amidev Temporary?
     */
    public function mergeStatusMessages(array $aLocale, $modId = ''){
        if(empty($this->aStatusMessagesLocale[$modId])){
            $this->aStatusMessagesLocale[$modId] = array();
        }
        $this->aStatusMessagesLocale[$modId] =
            $aLocale + $this->aStatusMessagesLocale[$modId];
    }

    /**
     * Add status message.
     *
     * Example:
     * <code>
     * // AmiSample_ListActionsAdm.php {
     *
     * $aEvent['oResponse']->addStatusMessage(
     *     'status_copied',
     *     array(
     *         'source'      => $nickname,
     *         'destination' => $newNickname
     *     )
     * );
     *
     * // } AmiSample_ListActionsAdm.php
     * </code>
     *
     * @param  string $key     Status message key
     * @param  array $aParams  Message parameters
     * @param  string $type    Message type (STATUS_MESSAGE | STATUS_MESSAGE_WARNING | STATUS_MESSAGE_ERROR)
     * @param  string $modId   Module id
     * @return AMI_Response
     * @see    AMI_Response::loadStatusMessages()
     * @see    AMI_Response::resetStatusMessages()
     * @see    AmiSample_ListActionsAdm.php
     * @since  5.12.4
     */
    public function addStatusMessage($key, array $aParams = array(), $type = self::STATUS_MESSAGE, $modId = ''){
        $aEvent = array(
            'modId'         => $modId,
            'key'           => &$key,
            'type'          => &$type,
            'aParams'       => &$aParams
        );
        /**
         * Called when status message added.
         *
         * @event      on_add_status_message AMI_Event::MOD_ANY
         * @eventparam string modId    Module id
         * @eventparam string key      Status message key from locales
         * @eventparam string type     Status message type, AMI_Response constants: STATUS_MESSAGE | STATUS_MESSAGE_WARNING | STATUS_MESSAGE_ERROR
         * @eventparam array  aParams  Status message scope
         */
        AMI_Event::fire('on_add_status_message', $aEvent, AMI_Event::MOD_ANY);
        if(empty($aEvent['_break'])){
            $aMessage = array(
                'modId'         => $modId,
                'key'           => $key,
                'message'       => $key,
                'aParams'       => $aParams,
                'type'          => $type
            );
            if(!in_array($aMessage, $this->aStatusMessages)){
                $this->aStatusMessages[] = $aMessage;
            }
        }
        return $this;
    }

    /**
     * Delete all status messages.
     *
     * @return AMI_Response
     * @see    AMI_Response::addStatusMessage()
     * @since  5.12.4
     */
    public function resetStatusMessages(){
        $this->aStatusMessages = array();
        return $this;
    }

    /**
     * Returns bench info as HTML or text string.
     *
     * @param  bool $bTextView  Returns as HTML by default
     * @return string
     * @amidev
     */
    public function getBenchString($bTextView = false){
        if(isset($GLOBALS['aAMIBench'])){
            $b = $GLOBALS['aAMIBench'];
            $timeTotal = microtime(true) - $this->startTime - ($this->sleepTime / 1000000);
            $timeDB = $b['DB']['queryTime'] + $b['DB']['fetchTime'];
            $timePHP = $timeTotal - $timeDB;
            $res =
                "<div style=\"font-size: 9px; font-family: Verdana,Geneva,Arial,Helvetica,sans-serif; padding-bottom: 10px;\">" .
                "<b>{$this->benchType}<span style=\"color: #00f;\">Script total: " .
                number_format($timeTotal, 6, '.', '') .
                " sec </span> [DB total: " .
                number_format($timeDB, 6, '.', '') .
                " sec</b> / queries: " . number_format($b['DB']['queryTime'], 6, '.', '') . " sec (" .
                $b['DB']['queryCount'] . " times) / fetch: " .
                number_format($b['DB']['fetchTime'], 6, '.', '') . " sec (" .
                $b['DB']['fetchCount'] . " times)<b>] [PHP total: " .
                number_format($timePHP, 6, '.', '') . " sec</b>";
            if($this->sleepTime > 0){
                $res .= ' / <span style="color: #f00;">sleep: ' . number_format($this->sleepTime / 1000000, 6, '.', '') . ' sec</span>';
            }
            if(function_exists('memory_get_peak_usage')){
                $res .= ' / peak mem usage: ' . number_format(memory_get_peak_usage(), 0, '.', ' ') . ' bytes';
            }
            $res .= ' / files: ' . sizeof(get_included_files()) . '<b>]</b>';
            if(sizeof($b['benches'])){
                $prevStartTime = $this->startTime;
                $prevMemotyUsage = 0;
                $res .= "<br>\n";
                foreach($b['benches'] as $label => $aBench){
                    $label = preg_replace('/_end$/', '', $label);
                    $diff = $aBench['t'] - $prevStartTime;
                    $total = $aBench['t'] - $this->startTime;
                    $res .=
                        "<b>[" . $label . "]</b>: " . AMI_Debug::getBenchTimeHTML($diff) .
                        " sec (<b>total</b>: " . AMI_Debug::getBenchTimeHTML($total, 'total') . ') ' .
                        (isset($aBench['m']) ? ', mem diff: ' . number_format($aBench['m'] - $prevMemotyUsage, 0, '.', ' ') . ' bytes' : '') .
                        (isset($aBench['p']) ? ' (<b>peak</b>: ' . number_format($aBench['p'], 0, '.', ' ') . ' bytes)' : '') .
                        ', <i>' . $aBench['c'] . "</i><br>\n";
                    $prevStartTime = $aBench['t'];
                    if(isset($aBench['m'])){
                        $prevMemotyUsage = $aBench['m'];
                    }
                }
            }
            $res .= "</div>\n";
            if($bTextView){
                $res = str_replace("\n", '', strip_tags($res));
            }
        }else{
            $res = '';
        }
        return $res;
    }

    /**
     * Returns true if benches output is enabled.
     *
     * @return bool
     * @amidev
     */
    public function isBenchEnabled(){
        return $this->bBench;
    }

    /**
     * Sets bench type.
     *
     * @return this
     * @amidev
     */
    public function setBenchType($type){
        global $conn;

        $this->benchType = $type;
        if(isset($conn) && is_object($conn) && ($conn instanceof Connector)){
            $conn->SetBenchType(strip_tags($type));
        }

        return $this;
    }

    /**
     * Constructor.
     *
     * @param float  $startTime  Start microtime
     * @param string $envMode    Environment mode
     * @todo  Implement const_advanced.php - _setDebugs() here?
     * @todo  Avoid damn config hacks
     * @amidev
     */
    private function __construct($startTime, $envMode = 'full'){
        global $AMI_ENV_SETTINGS;

        $this->startTime = (float)$startTime;

        /*
        // Prepare debug info
        $this->benchType = '';
        $fullOpener = '<span style="color: #f00; background: #fff;">';
        $fullCloser = '</span>';
        if(isset($AMI_ENV_SETTINGS) && isset($AMI_ENV_SETTINGS['mode'])){
            if($AMI_ENV_SETTINGS['mode'] !== 'full'){
                $fullOpener = '';
                $fullCloser = '';
            }
            $this->benchType = 'ENV-' . $fullOpener . mb_strtoupper($AMI_ENV_SETTINGS['mode']) . $fullCloser . ' ';
        }elseif(defined('AMI_60')){
            $this->benchType = 'ADMIN-' . (empty($_REQUEST['ami_full']) ? 'FAST' : $fullOpener . 'FULL' . $fullCloser) . ' ';
        }
        */

        $this->aReadOnlyObjects = array(
            'HTTP' => new AMI_HTTPResponse,
            'HTML' => new AMI_HTMLResponse($this->benchType)
        );

        if(AMI::isResource('db') && AMI::isSingletonInitialized('db')){
            if(!empty($GLOBALS['AMI_ENV_SETTINGS']['raw_mode'])){
                return;
            }
            // Read options
            $template =
                "SELECT `module_name`, `value`, `big_value` FROM `cms_options` " .
                "WHERE (`name` = %s AND `module_name` = %s)";
            /*
            if($envMode === 'fast'){
                $template .= " OR (`name` = %s)";
            }
            if(AMI_Registry::get('side') != 'adm'){
                $sql .= " OR (`name` = 'params' AND `module_name` = 'cache')";
            }
            */
            $oQuery =
                DB_Query::getSnippet($template)
                    ->q('options_dump')
                    ->q('common_settings');
            /*
            if($envMode === 'fast'){
                $oQuery->q('env_fast');
            }
            */
            $oRS = AMI::getSingleton('db')->select($oQuery);
        }else{
            $oRS = array();
        }
        $aOptions = array(
            'admin_compression_level'  => 6,
            'front_compression_level'  => 4,
            'compression_method'       => 'handler',
            'cache_frontside'          => true,
            'disable_cache_warn'       => false,
            'cache_storage_size'       => 200, // #CMS-11718
            'cache_expire_period'      => '+6 month',
            'time_zone'                => 0,
            'messages'                 => 0,
            'store'                    => 1,
            'env'                      => 1,
            'extdeb'                   => 0,
            'read_templates_from_disk' => false,
            'cachedeb'                 => 0,
            'email'                    => 'reports.dev@locmail.amiro.ru',
            'session_no_ip_bind'       => false,
            'session_adm_no_ip_bind'   => false,
            'www_prefix_type'          => false,
            'debug_ips'                => array(),
            'buffered'                 => true,
            'show_bench'               => false
        );
        // $aCacheOptions = array();
        foreach($oRS as $aRecord){
            if($aRecord['module_name'] === 'common_settings'){
                if(
                    !is_array($aRecord = unserialize($aRecord['big_value'])) ||
                    empty($aRecord['Options'])
                ){
                    trigger_error('System options data corruption detected', E_USER_ERROR);
                }
                $aRecord = $aRecord['Options'];
                // parse config {
                foreach(array_keys($aOptions) as $key){
                    if(isset($GLOBALS['CONNECT_OPTIONS'][$key])){
                        $aOptions[$key] = $GLOBALS['CONNECT_OPTIONS'][$key];
                    }elseif(isset($GLOBALS['sys'][$key])){
                        $aOptions[$key] = $GLOBALS['sys'][$key];
                    }elseif(isset($GLOBALS['sys']['err'][$key])){
                        $aOptions[$key] = $GLOBALS['sys']['err'][$key];
                    }elseif(isset($aRecord[$key])){
                        $aOptions[$key] = $aRecord[$key];
                    }
                }
                if(!empty($aRecord['debug_ips'])){
                    $aOptions['debug_ips'] += $aRecord['debug_ips'];
                }
                $oRequest = AMI::getSingleton('env/request');
                if(isset($aOptions['debug_ips'][$oRequest->getEnv('REMOTE_ADDR')])){
                    $aOptions['buffered'] =
                        mb_strpos($aOptions['debug_ips'][$oRequest->getEnv('REMOTE_ADDR')], 'to_body') !== FALSE;
                    $aOptions['show_bench'] =
                        mb_strpos($aOptions['debug_ips'][$oRequest->getEnv('REMOTE_ADDR')], 'show_bench') !== FALSE;
                    $aOptions['cachedeb'] = $aOptions['cachedeb'] ||
                        mb_strpos($aOptions['debug_ips'][$oRequest->getEnv('REMOTE_ADDR')], 'cachedeb') !== FALSE;
                    if(
                        preg_match('/extdeb_(\d+)/', $aOptions['debug_ips'][$oRequest->getEnv('REMOTE_ADDR')], $aMatches)
                    ){
                        $aOptions['extdeb'] = (int)$aMatches[1];
                    }
                    if(
                        mb_strpos($aOptions['debug_ips'][$oRequest->getEnv('REMOTE_ADDR')], 'disable_cache_frontside') !== FALSE &&
                        $aOptions['cache_frontside']
                    ){
                        $aOptions['cache_disabled_forced'] = TRUE;
                        $aOptions['cache_frontside'] = FALSE;
                    }
                }
                $aOptions['cache_frontside'] =
                    (is_bool($aOptions['cache_frontside']) ? $aOptions['cache_frontside'] : $aOptions['cache_frontside'] == 'ON') &&
                    !empty($GLOBALS['enable_cache'])
                        ? 'ON'
                        : 'OFF';
                // } parse config
            }else{
                /*
                $aRecord = unserialize($aRecord['big_value']);
                if(!is_array($aRecord)){
                    trigger_error('System options data corruption detected', E_USER_ERROR);
                }
                AMI::setFastEnvOptions($aRecord);
                */
            }
        }

        $aConfig = array(
            'response' => array(
                'compress_adm' => &$GLOBALS['CONNECT_OPTIONS']['admin_compression_level'],
                'compress_frn' => &$GLOBALS['CONNECT_OPTIONS']['front_compression_level'],
            ),
            'cache' => array(
                'front'      => &$GLOBALS['CONNECT_OPTIONS']['cache_frontside'],
                'hide_warn'  => &$GLOBALS['CONNECT_OPTIONS']['disable_cache_warn'],
                'size'       => &$GLOBALS['CONNECT_OPTIONS']['cache_storage_size'],
                'expiration' => &$GLOBALS['CONNECT_OPTIONS']['cache_expire_period']
                // 'options'    => $aCacheOptions
            ),
            'debug' => array(
                'env'         => &$GLOBALS['sys']['err']['env'],
                'cache'       => &$GLOBALS['sys']['err']['cachedeb'],
                'bench'       => &$GLOBALS['sys']['err']['show_bench'],
                'details'     => &$GLOBALS['sys']['err']['messages'],
                'tpl_from_fs' => &$GLOBALS['sys']['err']['read_templates_from_disk'],
                'level'       => &$GLOBALS['sys']['err']['extdeb'],
                'log'         => &$GLOBALS['sys']['err']['store'],
                'email'       => &$GLOBALS['sys']['err']['email'],
                'buffered'    => $aOptions['buffered']
            ),
            'session' => array(
                'ip_bind_frn' => &$GLOBALS['sys']['session_no_ip_bind'],
                'ip_bind_adm' => &$GLOBALS['sys']['session_adm_no_ip_bind'],
            ),
            'time_zone' => &$GLOBALS['sys']['time_zone'],
            'host_info' => &$GLOBALS['_h']
        );
        $aConfig['response']['compress_adm'] = $aOptions['admin_compression_level'];
        $aConfig['response']['compress_frn'] = $aOptions['front_compression_level'];
        $aConfig['cache']['front'] = $aOptions['cache_frontside'];
        $aConfig['cache']['hide_warn'] = $aOptions['disable_cache_warn'] || !empty($_REQUEST['ami_full']);
        $aConfig['cache']['size'] = $aOptions['cache_storage_size'];
        $aConfig['cache']['expiration'] = $aOptions['cache_expire_period'];
        $aConfig['debug']['log'] = $aOptions['store'];
        $aConfig['debug']['email'] = $aOptions['email'];
        $aConfig['debug']['env'] = $aOptions['env'];
        $aConfig['debug']['cache'] = $aOptions['cachedeb'];
        $aConfig['debug']['bench'] = $aOptions['show_bench'];
        $aConfig['debug']['details'] = $aOptions['messages'];
        $aConfig['debug']['tpl_from_fs'] = $aOptions['read_templates_from_disk'];
        $aConfig['debug']['level'] = $aOptions['extdeb'];
        $aConfig['session']['ip_bind_frn'] = $aOptions['session_no_ip_bind'];
        $aConfig['session']['ip_bind_adm'] = $aOptions['session_adm_no_ip_bind'];
        $aConfig['time_zone'] = $aOptions['time_zone'];
        // AMI_Registry::set('config', $aConfig);
        AMI_Service::setDebugBuffering($aOptions['buffered']);
        // set_error_handler(array('AMI_Service', 'handleError'), CMS_ERROR_REPORTING);
    }

    /**
     * Cloning.
     */
    private function __clone(){
    }

    /**
     * Sends response HTTP headers.
     *
     * @param  array $aHeaders  Headers
     * @return void
     * @see    AMI_HTTPResponse::getHeaders()
     */
    private function sendHeaders(array $aHeaders){
        foreach($aHeaders as $string => $aData){
            if(is_null($aData[1])){
                header($string, $aData[0]);
            }else{
                header($string, $aData[0], $aData[1]);
            }
        }
    }

    /**
     * Sets page reload flag.
     *
     * @param bool $needPageReload  Flag value
     * @return AMI_HTTPResponse
     * @since 5.14.4
     */
    public function setPageReload($needPageReload = TRUE){
        $this->needPageReload = $needPageReload;
        return $this;
    }

    /**
     * Returns JSON response.
     *
     * @param  mixed $content  Data to convert to JSON
     * @return string
     */
    private function getJSONResponse($content){
        $oCookies = AMI::getSingleton('env/cookie');
        $cookiePath = '';
        $side = AMI_Registry::get('side');
        $modId = AMI_Registry::get('modId', FALSE);
        if($side == 'adm' && $modId == 'mod_manager' && !AMI::getSingleton('env/request')->get('update', FALSE)){
            $cookiePath = '/60/ami_market/';
        }
        if($oCookies->get('ami_delayed_status_messages', FALSE)){
            $this->aStatusMessages += unserialize($oCookies->get('ami_delayed_status_messages'));
            $oCookies->set('ami_delayed_status_messages', '', time() - 60, $cookiePath);
            $oCookies->save();
        }
        $aResponse = array();
        if($this->message !== ''){
            $aResponse['message'] = $this->message;
        }
        if($this->code){
            $aResponse['code'] = $this->code;
        }
        if($this->jsonRedirect !== ''){
            $aResponse['redirect'] = $this->jsonRedirect;
        }
        if($this->needPageReload){
            $aResponse['reload'] = TRUE;
            $oCookies->set('ami_delayed_status_messages', serialize($this->aStatusMessages), time() + 60, $cookiePath);
            $oCookies->save();
        }
        $aEvent = array(
            'aStatusMessages' => &$this->aStatusMessages
        );
        /**
         * Called when received an array of status messages.
         *
         * @event      on_get_status_messages AMI_Event::MOD_ANY
         * @eventparam array aStatusMessages  Array of status messages
         */
        AMI_Event::fire('on_get_status_messages', $aEvent, AMI_Event::MOD_ANY);
        if(sizeof($this->aStatusMessages)){
            $aResponse['status_msgs'] = array_map(array($this, 'cbStatusMessage'), $this->aStatusMessages);
        }
        /*
        if(sizeof($this->aErrorMessages)){
            $aResponse['errors'] = $this->aErrorMessages;
        }
        */
        if(!is_null($content)){
            $aResponse['data'] = $content;
        }
        if(AMI_Service::isDebugVisible() && !empty($GLOBALS['sys']['err']['extdeb']) && !empty($GLOBALS['sys']['err']['messages'])){
            $debug = '';
            if($this->bBench){
                $debug .= $this->getBenchString();
            }
            $debug .= $GLOBALS['aAMIBench']['DB']['queries'] . AMI_Debug::getBuffer();
            if($debug !== ''){
                $aResponse['debug'] = $debug;
            }
        }
        return json_encode($aResponse);
    }

    /**
     * HTML entities encoding callback.
     *
     * @param  array $aMessage  Status message struct
     * @return array
     * @see    AMI_Response::getJSONResponse()
     * @amidev Temporary
     */
    public function cbStatusMessage(array $aMessage){
        $modId = $aMessage['modId'];
        if(
            !(
                isset($this->aStatusMessagesLocale[$modId]) || isset($this->aStatusMessagesLocale[$modId][$aMessage['key']])
            ) &&
            $modId !== ''
        ){
            $modId = '';
        }
        if(isset($this->aStatusMessagesLocale[$modId]) && isset($this->aStatusMessagesLocale[$modId][$aMessage['key']])){
            $message = $this->aStatusMessagesLocale[$modId][$aMessage['key']];
            foreach($aMessage['aParams'] as $key => $value){
                $message = mb_ereg_replace('_' . $key . '_', AMI_Lib_String::htmlChars($value), $message);
            }
            $aMessage['message'] = $message;
        }

        return $aMessage;
    }
}

/**
 * HTTP response.
 *
 * @package Service
 */
final class AMI_HTTPResponse{
    /**
     * HTTP protocol version
     *
     * @var string
     */
    private $protocol;

    /**
     * HTTP content type
     *
     * @var string
     */
    private $contentType = 'text/html';

    /**
     * HTTP headers
     *
     * @var array
     */
    private $aHeaders = array();

    /**
     * Constructor.
     *
     * @amidev
     */
    public function __construct(){
        $this->protocol = getenv('SERVER_PROTOCOL');### $_SERVER['SERVER_PROTOCOL']???
        if($this->protocol == ''){
            $this->protocol = 'HTTP/1.1';
        }
    }

    /**
     * Sets HTTP content type.
     *
     * Example:
     * <code>
     * AMI::getSingleton('response')->HTTP->setContentType('text/plain');
     * </code>
     *
     * @param  string $type  HTTP contetnt type
     * @return AMI_HTTPResponse
     */
    public function setContentType($type){
        $this->contentType = $type;
        return $this;
    }

    /**
     * Adds/replaces HTTP header.
     *
     * Example:
     * <code>
     * AMI::getSingleton('response')->HTTP->addHeader('Pragma: no-cache');
     * </code>
     *
     * @param  string $string  HTTP header
     * @param  bool $bReplace  Indicates whether the header should replace a previous similar header,
     *                         or add a second header of the same type
     * @param  int $code       Forces the HTTP response code to the specified value
     * @return AMI_HTTPResponse
     * @link   http://php.net/manual/en/function.header.php php:header()
     */
    public function addHeader($string, $bReplace = true, $code = null){
        $this->aHeaders[$string] = array($bReplace, $code);
        return $this;
    }

    /**
     * Adds "200 Ok" HTTP header.
     *
     * @return AMI_HTTPResponse
     * @amidev
     */
    public function addHeader200(){
        return $this
            ->addHeader('Status: 200 OK')
            ->addHeader($this->protocol . ' 200 OK');
    }

    /**
     * Adds "404 Not Found" HTTP header.
     *
     * @return AMI_HTTPResponse
     * @amidev
     */
    function setHeader404(){
        global $conn;

        $aPage = AMI_Registry::get('page');
        $aPage['isAvailable'] = false;
        AMI_Registry::set('page', $aPage);
        if(is_callable(array($conn, 'DelHeader'))){
            $conn->DelHeader('Status: 200 OK');
            $conn->DelHeader($this->protocol . ' 200 OK');
        }
        $conn->AddHeader($this->protocol . ' 404 Not Found', TRUE);
        $conn->AddHeader('Status: 404 Not Found', TRUE);
        $conn->SetSkip200Status(TRUE);

        return $this;
    }

    /**
     * Sends 503 header for HTML response mode.
     *
     * @param int $timeout  Timeout in seconds
     * @return AMI_Resoponse
     * @amidev
     */
    public function setServiceUnavailable($timeout){
        if(AMI::getSingleton('response')->getType() === 'HTML'){
            global $conn;
            if(is_object($conn)){
                $conn->Headers = array();
                if(is_callable(array($conn, 'DelHeader'))){
                    $conn->DelHeader('HTTP/1.x 404 Not Found');
                }
                $conn->AddHeader($this->protocol . ' 503 Service Temporarily Unavailable', TRUE);
                $conn->AddHeader('Status: 503 Service Temporarily Unavailable', TRUE);
                $conn->AddHeader('Retry-After: ' . $timeout, TRUE);
                $conn->SetSkip200Status(TRUE);
                $conn->OutHeaders();
            }else{
                header($this->protocol . ' 503 Service Temporarily Unavailable');
                header('Status: 503 Service Temporarily Unavailable');
                header('Retry-After: ' . $timeout);
            }
        }
        return $this;
    }

    /**
     * Sends redirect header for HTML response mode.
     *
     * @param string $location  Redirect location
     * @param int $code  Redirect code
     * @return this
     */
    public function setRedirect($location, $code = 302){
        $this->jsonRedirect = $location;
        if(AMI::getSingleton('response')->getType() === 'HTML'){
            switch($code){
                case 301:
                    $codeText = 'Moved Permanently';
                    break;
                case 302:
                    $codeText = 'Found';
                    break;
                case 303:
                    $codeText = 'See Other';
                    break;
                case 307:
                    $codeText = 'Temporary Redirect';
                    break;
                default:
                    $codeText = 'Found';
            };

            global $conn;
            if(is_object($conn)){
                $conn->Headers = array();
                if(is_callable(array($conn, 'DelHeader'))){
                    $conn->DelHeader('HTTP/1.x 404 Not Found');
                }
                $conn->AddHeader($this->protocol . ' '. $code. ' '. $codeText, TRUE);
                $conn->AddHeader('Location: ' . $location, TRUE);
                $conn->SetSkip200Status(TRUE);
                $conn->OutHeaders();
            }else{
                header($this->protocol . ' ' . $code . ' ' . $codeText);
                header('Location: ' . $location);
            }
        }
        return $this;
    }

    /**
     * Sets a cookie.
     *
     * @param string $name    Cookie name
     * @param string $value   Cookie value
     * @param string $expire  Cookie expiration date (Unix timestamp)
     * @param string $path    Cookie save path
     * @return AMI_HTTPResponse
     * @since 5.12.4
     */
    public function setCookie($name, $value, $expire = FALSE, $path = FALSE){
        global $cms;
        if(!is_null($cms)){
            // Full entry point
            SetLocalCookie($name, $value, $expire, $path);
        }else{
            // Fast entry point
            $urlData = parse_url($path);
            $cookiePath = $urlData['path'];
            $domain =
                empty($urlData['host']) || mb_strpos($urlData['host'], '.') === FALSE
                ? ''
                : $urlData['host'];
            setcookie($name, $value, $expire, $cookiePath, $domain);
        }
        return $this;
    }

    /**
     * Returns array of headers.
     *
     * @return array
     * @amidev
     */
    public function getHeaders(){
        return $this->contentType != ''
            ? array('Content-Type: ' . $this->contentType . '; charset=UTF-8' => array(true, null)) + $this->aHeaders
            : $this->aHeaders;
    }

    /**
     * Returns server protocol.
     *
     * @return string
     */
    public function getProtocol(){
        return $this->protocol;
    }
}

/**
 * HTML response.
 *
 * @package Service
 */
final class AMI_HTMLResponse{
    /**
     * Template
     *
     * @var GUI_template
     */
    private $oTpl;

    /**
     * Debug output HTML
     *
     * @var string
     */
    private $debugHTML;

    /**
     * Debug started flag
     *
     * @var bool
     */
    private $isDebugStarted = false;

    /**
     * Internal debug output flag
     *
     * @var bool
     */
    private $hasHeader = true;

    /**
     * Constructor.
     *
     * @param  string $benchType  Bench type
     * @amidev
     */
    public function __construct($benchType){
        $this->debugHTML =
<<< EOT
<script type="text/javascript">
DEBUG_BY_IP=1;
AMI_Debug = {
    setCookie: function(name, value){
        var oDate = new Date();
        oDate.setDate(oDate.getDate() + 30);
        this.deleteCookie(name);
        document.cookie = name + '=' + encodeURIComponent(value) + '; path=/; expires=' + oDate.toGMTString();
    },
    getCookie: function(name){
        var aCookie = document.cookie.split('; ');
        var value = '';
        for (var i = 0; i < aCookie.length; i++){
            var aPair = aCookie[i].split('=');
            if (name == aPair[0]){
                return typeof(aPair[1]) != 'undefined' ? aPair[1] : null;
            }
        }
        return null;
    },
    deleteCookie: function(name){
        if(this.getCookie(name) != null){
            document.cookie = name + '=; path=/; expires=Thu, 01-Jan-1970 00:00:01 GMT';
        }
    },
    clear: function(oAnchor, id){
        if(typeof(AMI) == 'undefined'){
            return;
        }
        if(typeof(id) == 'undefined'){
            var aDebugs = AMI.find('.componentDebug');
            for(var i = 0, q = aDebugs.length; i < q; i++){
                aDebugs[i].innerHTML = '';
            }
        }else{
            AMI.find('#' + id).innerHTML = '';
        }
        oAnchor.blur();
        return false;
    },
    clearOnMouseOver: function(oAnchor){
        if(typeof(AMI) == 'undefined'){
            return;
        }
        if(typeof(top.amiDebugBuilt) == 'undefined'){
            top.amiDebugBuilt = true;
            var aDebugs = AMI.find('.componentDebug'), html = '';
            for(var i = 0, q = aDebugs.length; i < q; i++){
                var srcComponent = aDebugs[i], component = aDebugs[i];
                while(component.parentNode){
                    component = component.parentNode;
                    if(component.className && component.className.match(/(^| )modPageComponent( |$)/)){
                        html += '<a style="color: #fff; font-weight: bold;" title="' + component.id + '" href="#" onclick="return AMI_Debug.clear(this, \'' + srcComponent.id + '\');">' + component.id.replace(/^\D+/, '') + '</a> | ';
                        break;
                    }
                }
            }
            oAnchor.parentNode.innerHTML = html + oAnchor.parentNode.innerHTML;
        }
    }
}
</script>
<span style="display: block; text-align: left; color: #fff; background: #f00; font-family: tahoma; font-weight:bold; font-size: 11px; padding:0 4px 2px 4px; margin-bottom: 5px;">{$benchType}&raquo;&raquo; Warning: debug mode is enabled,%s %s<i style="color: #f88;"> GMT: %s</i> &nbsp;%s <a style="color: #fff; text-decoration: none; font-family: tahoma; font-weight: bold; font-size: 11px;" href="%s" title="%s" target="_blank">%s</a></span>
<div style="position: fixed; top: 0; right: 0; border: none; margin: 0; padding: 0 5px 2px 5px; background: #f00; z-index: 19770404; color: #fff; font-weight: bold;"><a style="color: #fff; text-decoration: none; font-family: tahoma; font-weight: bold; font-size: 11px;" href="#" onclick="return AMI_Debug.clear(this);" onmouseover="AMI_Debug.clearOnMouseOver(this);" style="color: #f88;">Clear</a></div>
<div class="componentDebug" id="id
EOT;
        $this->debugHTML .= md5(mt_rand()) . '">';
    }

    /**
     * Adds CSS inclusion to HTML output.
     *
     * Example:
     * <code>
     * AMI::getSingleton('response')->HTML->addStyle('someStyles.css');
     * </code>
     *
     * @param  string $path  Path to the CSS file
     * @return void
     */
    public function addStyle($path){
        $this->checkTpl();
        $this->oTpl->addStyle($path);
    }

    /**
     * Adds JavsScript inclusion to HTML output.
     *
     * Example:
     * <code>
     * AMI::getSingleton('response')->HTML->addScript('someScript.js');
     * </code>
     *
     * @param  string $path  Path to the JavsScript file
     * @return void
     */
    public function addScript($path){
        $this->checkTpl();
        $this->oTpl->addScript($path);
    }

    /**
     * Accumulates debug output.
     *
     * @param  string $html       HTML string
     * @param  bool   $bBefore    Prepend/append flag
     * @param  bool   $bNoHeader  Reset header flag
     * @return void
     * @amidev
     */
    public function writeDebug($html, $bBefore = FALSE, $bNoHeader = FALSE){
        if(!$this->isDebugStarted){
            if($bNoHeader){
                $this->debugHTML = '';
                $this->hasHeader = FALSE;
            }else{
                /**
                 * @var AMI_Response
                 */
                $oResponse = AMI::getSingleton('response');
                $uri = $_SERVER['REQUEST_URI'];
                $aURL = @parse_url($uri);
                if(is_array($aURL) && isset($aURL['query'])){
                    $aArgs = array();
                    parse_str($aURL['query'], $aArgs);
                    if(sizeof($aArgs) > 2){
                        $aArgs = array_slice($aArgs, 0, 2, TRUE);
                        $uri = $aURL['path'] . '?' . http_build_query($aArgs);
                    }
                }
                $this->debugHTML =
                    sprintf(
                        $this->debugHTML,
                        empty($GLOBALS['sys']['disable_user_scripts']) ? '' : ' user scripts are disabled,',
                        date('Y-m-d H:i:s'),
                        gmdate('Y-m-d H:i'),
                        $_SERVER['REQUEST_METHOD'],
                        $_SERVER['REQUEST_URI'],
                        htmlentities($_SERVER['REQUEST_URI']),
                        htmlentities($uri)
                    );
                if($oResponse->isBenchEnabled()){
                    $this->debugHTML .= $oResponse->getBenchString();
                }
            }
            $this->isDebugStarted = true;
        }
        if($bBefore){
            $this->debugHTML = $html . $this->debugHTML;
        }else{
            $this->debugHTML .= $html;
        }
    }

    /**
     * Encapsulates debug output to the page content.
     *
     * @param  string &$content  Page content.
     * @return void
     * @amidev
     */
    public function sendDebug(&$content){
        if($this->isDebugStarted){
            if($this->hasHeader){
                $this->debugHTML .= '</div>'; // debug div
            }
            if(preg_match('/<body/i', $content)){
                $content = preg_replace('/<body.*?' . '>/si', "\\0\n" . $this->debugHTML, $content, 1);
            }else{
                $content = $this->debugHTML . $content;
            }
        }
        $this->debugHTML = '';
        $this->isDebugStarted = false;
    }

    /**
     * Creates GUI object if needed.
     *
     * We don't use GUI initialization in constructor to avoid creating useless entities.
     *
     * @return void
     */
    private function checkTpl(){
        if(is_null($this->oTpl)){
            if(AMI_Registry::exists('oGUI')){
                $this->oTpl = AMI_Registry::get('oGUI');
            }else{
                $this->oTpl = new gui;
                AMI_Registry::set('oGUI', $this->oTpl);
            }
        }
    }
}
