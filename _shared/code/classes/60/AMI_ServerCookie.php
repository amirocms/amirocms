<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Environment
 * @version   $Id: AMI_ServerCookie.php 49503 2014-04-08 05:09:05Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Server-side Cookies.
 *
 * @package  Environment
 * @resource env/cookie <code>AMI::getSingleton('env/cookie')</code>
 * @since    x.x.x
 * @amidev   Temporary
 */
class AMI_ServerCookie{

    /**
     * One hour in seconds
     */
    const LIFETIME_HOUR = 3600;

    /**
     * One day in seconds
     */
    const LIFETIME_DAY = 86400;

    /**
     * One year in seconds
     */
    const LIFETIME_YEAR = 31536000;

    /**
     * Member's Id
     *
     * @var int
     */
    private $userId = 0;

    /**
     * Server cookies
     *
     * @var array
     */
    private $aCookieData = array();

    /**
     * Vars Cookie array
     *
     * @var array
     */
    private $aVarsCookie;

    /**
     * Set to true if cookieData was changed, specifies to save data
     *
     * @var bool
     */
    private $isChanged = false;

    /**
     * Constructor.
     *
     * @amidev
     */
    public function __construct(){
        $this->userId = isset($GLOBALS['_h']['uid']) ? (int)$GLOBALS['_h']['uid'] : 0;
        if(!$this->userId){
            // Front side, only user having admin access can use server cookies for now
            return;
        }

        /**
         * @var AMI_iDB
         */
        $oDB = AMI::getSingleton('db');

        // Clear expired cookies.
        // Same as CheckProbability(0.03) defined in const.php, but it is not included in fast entry point.
        if(mt_rand(0, mt_getrandmax()) < 0.03 * mt_getrandmax()){
            $oDB->query("DELETE FROM cms_cookies WHERE expired < NOW()");
            if(mt_rand(0, mt_getrandmax()) < 0.05 * mt_getrandmax()){
                $oDB->query("OPTIMIZE TABLE cms_cookies");
            }
        }

        // Get cookies from db
        $sql = "SELECT data FROM cms_cookies WHERE id_member = %s";
        $aRow =
            $oDB->fetchRow(
                DB_Query::getSnippet($sql)
                ->plain($this->userId)
            );

        $this->aCookieData = array('values' => array(), 'valuesbr' => array(), 'expire' => -1);
        if($aRow && !AMI::getSingleton('env/request')->get('drop_user_cookie_data', false)){
            $data = @unserialize($aRow['data']);
            if(is_array($data)){
                $this->aCookieData = $data;
            }else{
                trigger_error("AMI_ServerCookie: Invalid entry in cookies table for user " . $this->userId . " (overflow possible)", E_USER_WARNING);
            }
        }

        $this->aCookieData['valuesbr'] = array();
    }

    /**
     * Gets cookie data.
     *
     * @return array
     * @amidev
     */
    public function getCookieData(){
        return $this->aCookieData;
    }

    /**
     * Get VarsCookie array.
     *
     * @param  bool $force  Request cookieData rebuild
     * @return array
     * @todo   Front side support
     * @amidev
     */
    public function getVarsCookie($force = false){
        $side = AMI_Registry::get('side');
        if(is_null($this->aVarsCookie) || $force){
            $tm = time();
            $aCookies = $_COOKIE;
            if($side == 'adm'){
                $getPath = '/' . $this->getModType() . '/' . $this->getModId() . '/';
            }else{
                // todo: front
                $getPath = '/';
            }
            foreach($this->aCookieData as $secName => $aVal){
                if(isset($aVal[$side])){
                    foreach($aVal[$side] as $path => $aVals){
                        if($path != "" && (($path == '/') || ($getPath == $path)) && is_array($aVals['data'])){
                            foreach($aVals['data'] as $name => $val){
                                if(($aVals['expire'][$name] == 0 || $aVals['expire'][$name] > $tm) && !isset($aCookies[$name])){
                                    $aCookies[$name] = $val;
                                }
                            }
                        }
                    }
                }
            }
            $this->aVarsCookie = $aCookies;
        }
        return $this->aVarsCookie;
    }

    /**
     * Get actual server cookies.
     *
     * @return array
     * @amidev
     */
    public function getData(){
        $cookieData = $this->aCookieData;

        $aCookieDataVarNames = array();
        if(isset($cookieData['values'])){
            foreach($cookieData['values'] as $aDomainData){
                foreach($aDomainData as $aPathData){
                    $aCookieDataVarNames = array_merge($aCookieDataVarNames, array_flip(array_keys($aPathData['data'])));
                }
            }
        }

        $aCookies = array();
        if(is_array($this->aVarsCookie)){
            foreach($this->aVarsCookie as $cookieVarName => $cookieVarValue){
                if(isset($aCookieDataVarNames[$cookieVarName])){
                    $aCookies[$cookieVarName] = $cookieVarValue;
                }
            }
        }
        return $aCookies;
    }

    /**
     * Returns server-side cookie value.
     *
     * Example:
     * <code>
     * $lang = AMI::getSingleton('env/cookie')->get('lang', 'ru');
     * </code>
     *
     * @param  string $name          Cookie name
     * @param  string $defaultValue  Will be retured if no cookie found (default null)
     * @return mixed
     * @see    AMI_ServerCookie::set()
     */
    public function get($name, $defaultValue = null){
        $aData = $this->getData();
        return isset($aData[$name]) ? $aData[$name] : $defaultValue;
    }

    /**
     * Sets a server-side cookie for module.
     *
     * Example:
     * <code>
     * // Set global scope cookie
     * AMI::getSingleton('env/cookie')->set('myCookie', 'myValue', time() + ..., '/');
     * // Set current module cookie
     * AMI::getSingleton('env/cookie')->set('myCookie', 'myValue', time() + ...);
     * </code>
     *
     * @param  string $name    Cookie name (you SHOULD use your own module specific prefixes!)
     * @param  string $value   Cookie value
     * @param  int    $expire  0 or timestamp
     * @param  string $path    Cookie path (live blank to be set automaticaly)
     * @return AMI_ServerCookie
     * @see    AMI_ServerCookie::get()
     */
    public function set($name, $value, $expire, $path = ''){
        $side = AMI_Registry::get('side');
        $subFolder = AMI_Registry::get('subfolder', '');
        if(($side == 'adm')){
            // Workaround for admin root path that comes from JS
            if($path && (mb_strpos($path, '/_admin/') !== false)){
                $path = (mb_strpos($path, '.php') === false) ? '/' : '';
            }
            if($this->getModId() && !$path){
                $path = '/' . $this->getModType() . '/' . $this->getModId() . '/';
            }
        }else{
            // todo: Front
            if(!$path){
                $path = '/';
            }
        }
        if($expire == 0 || $expire > time()){
            $aValues = &$this->aCookieData[($expire > 0) ? 'values' : 'valuesbr'];
            $needUpdate = false;
            $skipSave = false;
            if(!isset($aValues[$side][$path]['data'][$name]) || (isset($aValues[$side][$path]['data'][$name]) && $aValues[$side][$path]['data'][$name] != $value)){
                if(!isset($aValues[$side][$path]['data'][$name]) && !$expire){
                    $skipSave = true;
                }
                $needUpdate = true;
            }elseif(isset($aValues[$side][$path]['expire'][$name]) && $aValues[$side][$path]['expire'][$name] < (time() + 3000)){
                $needUpdate = true;
            }
            if($needUpdate){
                if(!$skipSave){
                    $this->isChanged = true;
                }
                $aValues[$side][$path]['data'][$name] = $value;
                $aValues[$side][$path]['expire'][$name] = $expire;
                $this->aCookieData['expire'] = min(max($this->aCookieData['expire'], $expire), time() + AMI_ServerCookie::LIFETIME_YEAR);
            }
        }else{
            // Unset expired cookie
            if(isset($this->aCookieData['values'][$side][$path]['data'][$name])){
                unset($this->aCookieData['values'][$side][$path]['data'][$name]);
            }
            $this->isChanged = true;
        }
        $this->aVarsCookie[$name] = $value;
        return $this;
    }

    /**
     * Deletes cookie by name.
     *
     * @param  string $name  Name
     * @param  string $side  Side
     * @return void
     */
    public function deleteByName($name, $side = ''){
        $side = $side === '' ? AMI_Registry::get('side') : $side;

        $aPathes = array_keys($this->aCookieData['values'][$side]);
        foreach($aPathes as $path){
            if(isset($this->aCookieData['values'][$side][$path]['data'][$name])){
                unset($this->aCookieData['values'][$side][$path]['data'][$name]);
                unset($this->aCookieData['values'][$side][$path]['expire'][$name]);
                $this->isChanged = TRUE;
            }
        }
    }

    /**
     * Saves server cookies.
     *
     * @return AMI_ServerCookie
     * @amidev
     */
    public function save(){
        if(!$this->isChanged || !$this->userId){
            return $this;
        }

        $side = AMI_Registry::get('side');
        $oDB = AMI::getSingleton('db');
        $cookieData = $this->aCookieData;

        $expire = intval($cookieData['expire']);
        if($expire == 0){
            $expire = time() + 12 * AMI_ServerCookie::LIFETIME_DAY; // 12 days
        }

        // Sort date to full cookie by path emulation
        // @todo: detect is_array usage necessity
        if(isset($cookieData['values'][$side]) && is_array($cookieData['values'][$side])){
            ksort($cookieData['values'][$side], SORT_STRING);
        }
        /*
         * Useless. maybe.
        if(isset($cookieData['valuesbr'][$side]) && is_array($cookieData['valuesbr'][$side])){
            ksort($cookieData['valuesbr'][$side], SORT_STRING);
        }
        */
        if($this->userId){
            $sql = "REPLACE cms_cookies (id_member, expired, ip, data) VALUES (%s, %s, %s, %s)";
            $oDB->query(
                DB_Query::getSnippet($sql)
                ->q($this->userId)
                ->q(DateTools::toMysqlDate($expire))
                ->q(getenv("REMOTE_ADDR"))
                ->q(serialize($cookieData))
            );
        }
        return $this;
    }

    /**
     * Get serialized JS string for server cookies.
     *
     * @return string
     * @amidev
     */
    public function getDataAsJS(){
        return json_encode($this->getData());
    }

    /**
     * Do required actions when CMS_Base::PrepareVars() completed.
     *
     * Saves server-side cookies if action get parameter equals to saveServerCookie.
     *
     * @return void
     * @amidev
     */
    public function onPrepareVars(){
        $oRequest = AMI::getSingleton('env/request');
        if($oRequest->get('action') == 'saveServerCookie'){
            if(is_array($oRequest->get('key'))){
                $value = $oRequest->get('value');
                $aExpire = $oRequest->get('expire');
                $aPath = $oRequest->get('path');
                foreach($oRequest->get('key') as $index => $key){
                    if(isset($value[$index]) && isset($value[$index])){
                        $this->set($key, $value[$index], time() + $aExpire[$index], $aPath[$index]);
                    }
                }
            }
            $this->save();
            // AJAX response
            AMI_Service::hideDebug();
            $oResponse = AMI::getSingleton('response');
            $oResponse->HTTP->addHeader('Pragma: no-cache');
            if($oRequest->get('response', false) == 'img'){
                $oResponse->HTTP->setContentType('image/gif');
                $out = chr(71).chr(73).chr(70).chr(56).chr(57).chr(97).chr(1).chr(0).chr(1).chr(0).chr(128).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0).chr(33).chr(249).chr(4).chr(1).chr(0).chr(0).chr(0).chr(0).chr(44).chr(0).chr(0).chr(0).chr(0).chr(1).chr(0).chr(1).chr(0).chr(0).chr(2).chr(2).chr(68).chr(1).chr(0).chr(59);
            }else{
                $oResponse->HTTP->setContentType('text/html');
                $out = 'OK';
            }
            $oResponse->start();
            $oResponse->write($out);
            $oResponse->send();
            die;
        }
        /*
        else{
            AMI_Event::addHandler('custom_on_request_init', array($this, 'handleRequest'), AMI_Event::MOD_ANY);
        }
        */
    }

    /**
     * Cleans unused data.
     *
     * @amidev
     * @return AMI_ServerCookie
     */
    public function cleanup(){
        $oDeclarator = AMI_ModDeclarator::getInstance();
        foreach($this->aCookieData['values']['adm'] as $path => $aPathData){
            $aPath = explode('/', $path);
            if(isset($aPath[2]) && (strpos($aPath[2], 'inst_') === 0)){
                if(!$oDeclarator->isRegistered($aPath[2])){
                    unset($this->aCookieData['values']['adm'][$path]);
                    $this->isChanged = true;
                }
            }
        }
        $this->save();
        return $this;
    }

    /**
     * Event hander.
     *
     * Prepends server cookie data ro  request / cookie vars.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @amidev
     */
    /*
    public function handleRequest($name, array $aEvent, $handlerModId, $srcModId){
        $this->getVarsCookie();
        $aData = $this->getData();
        $aEvent['aRequest'] += $aData;
        $aEvent['aCookie'] += $aData;
        return $aEvent;
    }
    */

    /**
     * Get current module id (60 or 50).
     *
     * @return string|null
     */
    protected function getModId(){
        $scriptName = get_script_name();
        $modId = null;
        if(($scriptName == 'engine') || ($scriptName == '60')){
            $modId = AMI_Registry::get('modId', FALSE);
            if($modId === FALSE){
                $modId = null;
            }
        }else{
            $modId = AMI_ModDeclarator::getInstance()->getModIdByLink($scriptName . '.php');
            if(is_null($modId)){
                $modId = $scriptName;
            }
        }
        return $modId;
    }

    /**
     * Get current module type (50, 60 or PA).
     *
     * @return string
     */
    protected function getModType(){
        $type = '50';
        if((defined('AMI_60') && AMI_60) || AMI_Registry::get('modEngine', '') == '60'){
            $type = '60';
        }
        if(AMI::getSingleton('env/request')->get('partial_async', false) || AMI_Registry::get('injected60', false)){
            $type = 'PA';
        }
        return $type;
    }
}
