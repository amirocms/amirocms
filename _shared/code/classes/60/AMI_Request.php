<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Environment
 * @version   $Id: AMI_Request.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.10.0
 */

/**
 * Request class.
 *
 * Lets our stanalone script is named 'script.php' and localted in the site root folder.<br /><br />
 *
 * Example:
 * <code>
 * require_once 'ami_env.php';
 * $oRequest = AMI::getSingleton('env/request');
 * $oResponse = AMI::getSingleton('response');
 * $oResponse->start();
 * $oResponse->write('my_var: ' . $oRequest->get('my_var', 0) . '<br />');
 * $oResponse->write('my_get_var: ' . $oRequest->get('my_get_var', 0, 'g') . '<br />');
 * $oResponse->write('my_post_var: ' . $oRequest->get('my_post_var', 0, 'p') . '<br />');
 * $oResponse->send();
 * </code>
 *
 * <br />will output
 * - 0, 0, 0 for http://cms.my/script.php
 * - 1, 2, 0 for http://cms.my/script.php?my_var=1&my_get_var=2
 * - 1, 0, ... for http://cms.my/script.php?my_var=1 if my_post_var posted
 *
 * @package  Environment
 */
class AMI_Request{
    /**
     * Request scope
     *
     * @var array
     */
    protected $aScope = array('_default' => array());

    /**
     * Default scope source
     *
     * @var   mixed  Array or string
     * @since 5.14.8
     */
    protected $defaultSource = '_default';

    /**
     * Returns value by specified key.
     *
     * @param  string $key      Request key
     * @param  mixed  $default  Default value, will be returned if there is no key in the request
     * @param  mixed  $source   Scope source, array (several sources will be scanned) or string,
     *                          if not specified, default source is used, since 5.14.8
     * @return mixed
     */
    public function get($key, $default = null, $source = '_default'){
        $this->patchSource($source);
        if(is_array($source)){
            foreach($source as $src){
                $result = $this->get($key, null, $src);
                if(!is_null($result)){
                    return $result;
                }
            }
            return $default;
        }
        return
            isset($this->aScope[$source]) && isset($this->aScope[$source][$key])
                ? $this->aScope[$source][$key]
                : $default;
    }

    /**
     * Returns whole scope.
     *
     * If $source argument is array, the sum of scopes will be returned.
     *
     * @param  mixed $source  Scope source, if not specified, default source is used, since 5.14.8
     * @return array
     */
    public function getScope($source = '_default'){
        $this->patchSource($source);
        if(!is_array($source)){
            return $this->aScope[$source];
        }
        $aScope = array();
        foreach($source as $src){
            $aScope += $this->getScope($src);
        }
    }

    /**
     * Sets/unsets value by key.
     *
     * If $source argument is array, first source scope will be modified.
     *
     * @param  string $key     Request key
     * @param  mixed  $value   Null to unset
     * @param  string $source  Scope source, if not specified, default source is used, since 5.14.8
     * @return AMI_Request
     */
    public function set($key, $value = null, $source = '_default'){
        $this->patchSource($source, TRUE);
        if(is_null($value)){
            unset($this->aScope[$source][$key]);
        }else{
            $this->aScope[$source][$key] = $value;
        }
        return $this;
    }

    /**
     * Sets whole scope.
     *
     * @param  array  $aScope  Scope
     * @param  string $source  Scope source, since 5.14.8
     * @return AMI_Request
     */
    public function setScope(array $aScope, $source){
        $this->aScope[$source] = $aScope;
        return $this;
    }

    /**
     * Resets scope.
     *
     * @param  string $source  Scope source, if not specified, all scopes will be reset, since 5.14.8
     * @return AMI_Request
     */
    public function reset($source = null){
        if(is_null($source)){
            foreach(array_keys($this->aScope) as $source){
                $this->aScope[$source] = array();
            }
        }else{
            $this->aScope[$source] = array();
        }
        return $this;
    }

    /**
     * Sets default scope.
     *
     * @param  mixed $source  Scope source, array or string
     * @return AMI_Request
     * @since  5.14.8
     */
    public function setDefaultSource($source){
        $this->defaultSource = $source;
        return $this;
    }

    /**
     * Replace source by default value if needed.
     *
     * @param  string &$source       Scoupe source
     * @param  bool   $extractFirst  Extract first source from array
     * @return void
     * @since  5.14.8
     */
    protected function patchSource(&$source, $extractFirst = FALSE){
        if($source == '_default'){
            $source = $this->defaultSource;
        }
        if($extractFirst && is_array($source)){
            $source = $source[0];
        }
    }
}

/**
 * HTTP request class.
 *
 * Default scope set to GET, POST (array('g', 'p')).<br />
 * Available scopes:
 * - g GET (get/set)
 * - p POST (get/set)
 * - c COOKIE (get)
 * - f FILES (get)
 *
 * @package  Environment
 * @resource env/request <code>AMI::getSingleton('env/request')</code>
 */
class AMI_RequestHTTP extends AMI_Request{
    /**
     * Request scope
     *
     * @var array
     */
    protected $aScope = array('g' => array(), 'p' => array(), 'c' => array());

    /**
     * Default scope source
     *
     * @var   string
     * @since 5.14.8
     */
    protected $defaultSource = array('g', 'p');

    /**
     * Parsed url
     *
     * @var array
     */
    protected $aParsedURL = false;

    /**
     * Constructor.
     */
    public function __construct(){
        $this->initScopes();
        $this->setURL();
    }

    /**
     * Returns value by specified key.
     *
     * Usage example:
     * <code>
     * $oRequest = AMI::getSingleton('env/request');
     * // To get query, post, or cookie value is set any:
     * $oRequest->get('my_var', null, array('g', 'p', 'c'));
     * // To get post, query value is set any:
     * $oRequest->get('my_var', null, array('p', 'g'));
     * // To get uploaded files:
     * $oRequest->get('...', null, array('f'));
     * </code>
     *
     * @param  string $key      Request key
     * @param  mixed  $default  Default value, will be returned if there is no key in the request
     * @param  mixed  $source   Scope source, array (several sources will be scanned) or string,
     *                          if not specified, default source is used, since 5.14.8
     * @return mixed
     */
    public function get($key, $default = null, $source = '_default'){
        /**
         * @var CMS_Base
         */
        global $cms;

        $this->patchSource($source);
        if(is_array($source)){
            foreach($source as $src){
                $result = $this->get($key, null, $src);
                if(!is_null($result)){
                    return $result;
                }
            }
            return $default;
        }

        $result = parent::get($key, null, $source);
        if(is_null($result) && in_array($source, array('g', 'p', 'c', 'f'))){
            $cmsVar = FALSE;
            $phpVar = FALSE;
            switch($source){
                case 'g':
                    $cmsVar = 'VarsGet';
                    break;
                case 'p':
                    $cmsVar = 'VarsPost';
                    break;
                case 'c':
                    $cmsVar = 'VarsCookie';
                    $phpVar = '_COOKIE';
                    break;
                case 'f':
                    $phpVar = '_FILES';
                    break;
            }
            if(!empty($cms) && $cmsVar && isset($cms->{$cmsVar}[$key])){
                $result = $cms->{$cmsVar}[$key];
            }elseif($phpVar && isset($GLOBALS[$phpVar][$key])){
                $result = $GLOBALS[$phpVar][$key];
            }
        }
        return is_null($result) ? $default : $result;
    }

    /**
     * Sets/unsets value by key.
     *
     * If $source argument is array, first source scope will be modified.
     *
     * @param  string $key     Request key
     * @param  mixed  $value   Null to unset
     * @param  string $source  Scope source, if not specified, default source is used, since 5.14.8
     * @return AMI_RequestHTTP
     */
    public function set($key, $value = null, $source = '_default'){
        /**
         * @var CMS_Base
         */
        global $cms;

        $this->patchSource($source, TRUE);
        if($this->checkSource($source)){
            return $this;
        }

        parent::set($key, $value, $source);

        if(in_array($source, array('g', 'p'))){
            switch($source){
                case 'g':
                    $cmsVar = 'VarsGet';
                    $otherCMSVar = 'VarsPost';
                    $src = '_GET';
                    break;
                case 'p':
                    $cmsVar = 'VarsPost';
                    $otherCMSVar = 'VarsGet';
                    $src = '_POST';
                    break;
            }
            $reset = is_null($value);
            if(!empty($cms)){
                if($reset){
                    unset($cms->{$cmsVar}[$key]);
                    if(!isset($cms->{$otherCMSVar}[$key])){
                        unset($cms->Vars[$key]);
                    }
                }else{
                    $cms->{$cmsVar}[$key] = $value;
                    $cms->Vars[$key] = $value;
                }
            }
            if($reset){
                unset($this->aScope[$src][$key]);
            }
        }
        return $this;
    }

    /**
     * Returns cookie value by specified key.
     *
     * @param  string $key      Request key
     * @param  mixed  $default  Default value, will be returned if there is no key in the cookies
     * @return mixed
     */
    public function getCookie($key, $default = null){
        return $this->get($key, $default, 'c');
    }

    /**
     * Returns environment ($_SERVER / getenv()) value by specified key.
     *
     * @param  string $key      Request key
     * @param  mixed  $default  Default value, will be returned if there is no key in the environment
     * @return mixed
     */
    public function getEnv($key, $default = null){
        if(isset($_SERVER[$key])){
            return $_SERVER[$key];
        }
        $res = @getenv($key);
        return $res !== false ? $res : $default;
    }

    /**
     * Returns whole scope.
     *
     * If $source argument is array, the sum of scopes will be returned.
     *
     * @param  mixed $source  Scope source, if not specified, default source is used, since 5.14.8
     * @return array
     */
    public function getScope($source = '_default'){
        /**
         * @var CMS_Base
         */
        global $cms;

        $this->patchSource($source);
        if(is_array($source)){
            $aScope = array();
            foreach($source as $src){
                $aScope += $this->getScope($src);
            }
            return $aScope;
        }
        $aScope = parent::getScope($source);

        if(in_array($source, array('g', 'p', 'c', 'f'))){
            $cmsVar = FALSE;
            $phpVar = FALSE;
            switch($source){
                case 'g':
                    $cmsVar = 'VarsGet';
                    break;
                case 'p':
                    $cmsVar = 'VarsPost';
                    break;
                case 'c':
                    $cmsVar = 'VarsCookie';
                    $phpVar = '_COOKIE';
                    break;
                case 'f':
                    $phpVar = '_FILES';
                    break;
            }
            if(!empty($cms) && $cmsVar){
                $aScope += $this->stripSlashesRec($cms->{$cmsVar});
            }elseif($phpVar){
                $aScope = $GLOBALS[$phpVar];
            }
        }
        return $aScope;
    }

    /**
     * Returns source GET scope.
     *
     * @return array
     * @amidev
     */
    public function getSourceGet(){
        /**
         * @var CMS_Base
         */
        global $frn;

        return isset($frn->sourceGET) ? $frn->sourceGET : array();
    }

    /**
     * Resets scope.
     *
     * Resetting scope is forbidden in AMI_RequestHTTP.
     *
     * @param  string $source  Scope source
     * @return AMI_RequestHTTP
     */
    public function reset($source = null){
        trigger_error('AMI_RequestHTTP::reset() is forbidden', E_USER_WARNING);
        return $this;
    }

    /**
     * Returns URL or URL part of request.
     *
     * @param  string $urlPart  None or: host, port, script, url, uri, path
     * @return String or null
     * @since  5.12.4
     */
    public function getURL($urlPart = 'url'){
        return isset($this->aParsedURL[$urlPart]) ? $this->aParsedURL[$urlPart] : null;
    }

    /**
     * Setting up and parse URL string.
     *
     * @param  string $newURL  Url string, if none - use current.
     * @return void
     * @since  5.12.4
     */
    public function setURL($newURL = false){
        if($newURL){
            $aUrl = parse_url($newURL);
            $this->aParsedURL['url'] = $newURL;
            $this->aParsedURL['host'] = $aUrl['host'];
            $this->aParsedURL['port'] = isset($aUrl['port']) ? $aUrl['port'] : '80';
            $this->aParsedURL['uri'] = $aUrl['path'] . (isset($aUrl['query']) ? '?' . $aUrl['query'] : '');
            $this->aParsedURL['path'] = $aUrl['path'];
        }else{
            $sTmp =
                isset($_SERVER["REQUEST_URI"])
                    ? preg_replace('/^\//', '', addslashes(getenv("REQUEST_URI")))
                    : preg_replace('/^.*?https?:\/\/.+?\//', '', $_SERVER["QUERY_STRING"]);
            if(mb_strpos($sTmp, '"') !== false || mb_strpos($sTmp, "'") !== false){
                $sTmp = urlencode($sTmp);
            }
            $prefix = preg_replace('/^.*?(https?:\/\/.+?\/).*/', '$1', $GLOBALS['ROOT_PATH_WWW']);
            $sTmp = $prefix . $sTmp;
            $this->aParsedURL['url'] = $sTmp;
            $aUrl = parse_url($sTmp);
            $this->aParsedURL['host'] = $aUrl['host'];
            $this->aParsedURL['port'] = isset($aUrl['port']) ? $aUrl['port'] : '80';
            $this->aParsedURL['uri'] = $aUrl['path'] . (isset($aUrl['query']) ? '?' . $aUrl['query'] : '');
            $this->aParsedURL['path'] = $aUrl['path'];
        }
        $this->aParsedURL['script'] = $_SERVER['SCRIPT_NAME'];
    }

    /**
     * Initialize scope.
     *
     * @return void
     * @since  5.14.8
     */
    protected function initScopes(){
        foreach(array('_GET' => 'g', '_POST' => 'p') as $source => $src){
            foreach(array_keys($GLOBALS[$source]) as $key){
                $this->aScope[$src][$key] =
                    $GLOBALS['AMI_ESCAPE_REQUEST'] ? $GLOBALS[$source][$key] : $this->stripSlashesRec($GLOBALS[$source][$key]);
            }
        }
    }

    /**
     * Protectes cookies/files modification.
     *
     * @param  string $source  Scope source
     * @return bool
     * @since  5.14.8
     */
    protected function checkSource($source){
        $message = '';
        if($source === 'c'){
            $message = 'Cookies are read only in request, use AMI_Response::$HTTP->setCookie() method';
        }elseif($source === 'f'){
            $message = 'Uploaded files data is read only';
        }
        $result = $message !== '';
        if($result){
            trigger_error(
                $message,
                E_USER_WARNING
            );
        }
        return $result;
    }

    /**
     * Strips slashes recursively.
     *
     * @param  mixed $entity  Entity
     * @return mixed
     * @since  5.14.8
     */
    protected function stripSlashesRec($entity){
        if(is_array($entity)){
            $entity = array_map(array($this, 'stripSlashesRec'), $entity);
        }elseif(is_string($entity)){
            $entity = stripslashes($entity);
        }
        return $entity;
    }
}
