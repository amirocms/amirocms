<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Service
 * @version   $Id: AMI_HTTPRequest.php 49377 2014-04-03 10:01:24Z Kolesnikov Artem $
 * @since     5.12.4
 */

/**
 * HTTP request class.
 *
 * Allows to execute GET or POST HTTP requests.<br /><br />
 *
 * Example:
 * <code>
 * require_once 'ami_env.php';
 * $oHTTPRequest = new AMI_HTTPRequest();
 * $oHTTPRequest->send('http://anysite', array('postVar' => 'value'), AMI_HTTPRequest::METHOD_POST);
 * </code>
 *
 * @package Service
 * @since   5.12.4
 */
class AMI_HTTPRequest{
    /**
     * HTTP GET request method
     */
    const METHOD_GET  = 1;

    /**
     * HTTP POST request method
     */
    const METHOD_POST = 2;

    /**
     * HTTP Request settings
     *
     * @var array
     */
    protected $aSettings = array();

    /**
     * Old HTTP request object
     *
     * @var CMS_HTTPRequest
     * @amidev
     */
    protected $oHTTPRequest;

    /**
     * Constructor.
     *
     * Example:
     * <code>
     * $oHTTPRequest = new AMI_HTTPRequest(
     *     array(
     *         'returnHeaders'  => FALSE,  // Specifies to return headers if TRUE, bool, FALSE by default
     *         'followLocation' => TRUE,   // Follow the redirects if TRUE, bool, TRUE by default
     *         'useCookies'     => FALSE,  // Allow to receive cookies, bool, FALSE by default
     *         'userAgent'      => '',     // User Agent, string, 'AMI_HTTPRequest' by default
     *         'cookieFile'     => '',     // Name of the cookie file, string, empty string by default
     *         'verifySSL'      => FALSE   // Verify SSL certificate (since 6.0.4)
     *     )
     * );
     * </code>
     *
     * @param array $aSettings  Settings array
     */
    public function __construct(array $aSettings = array()){
        require_once $GLOBALS['HOST_PATH'] . '_shared/code/const/init_simple.php';

        $this->aSettings = $aSettings += array(
            'returnBody'     => TRUE,
            'returnHeaders'  => FALSE,
            'followLocation' => TRUE,
            'useCookies'     => FALSE,
            'userAgent'      => 'AMI_HTTPRequest ' . $GLOBALS['VERSIONS']['cms']['code'],
            'cookieFile'     => '',
            'keepSession'    => FALSE,
            'verifySSL'      => FALSE
        );
    }

    /**
     * Set new settings.
     *
     * @param  array $aSettings  Settings array
     * @return void
     */
    public function alterSettings(array $aSettings = array()){
        $this->aSettings = array_merge($this->aSettings, $aSettings);
    }

    /**
     * Send HTTP request and returns the result.
     *
     * @param  string $url     Request URL
     * @param  array  $aData   Associative array containing request data
     * @param  int    $method  HTTP requset method: AMI_HTTPRequest::METHOD_GET, AMI_HTTPRequest::METHOD_POST
     * @return string
     */
    public function send($url, array $aData = array(), $method = self::METHOD_GET){
        $post = null;
        $method = (int)$method;
        switch($method){
            case self::METHOD_GET:
                if(sizeof($aData)){
                    $url .= (mb_strpos($url, '?') === false ? '?' : '&') . http_build_query($aData);
                }
                break;
            case self::METHOD_POST:
                if(sizeof($aData)){
                    $post = http_build_query($aData);
                }
                break;
            default:
                trigger_error('Invalid request method', E_USER_WARNING);
                return null;
        }

        $cookieFile = (string)$this->aSettings['cookieFile'];

        if($this->aSettings['keepSession'] && isset($this->oHTTPRequest)){
            $oHTTPRequest = $this->oHTTPRequest;
        }else{
            $oHTTPRequest = new CMS_HTTPRequest();
        }
        $oHTTPRequest->_returntransfer  = TRUE;
        $oHTTPRequest->_nobody          = !(bool)$this->aSettings['returnBody'];
        $oHTTPRequest->_header          = (bool)$this->aSettings['returnHeaders'];
        $oHTTPRequest->_followlocation  = (bool)$this->aSettings['followLocation'];
        $oHTTPRequest->_usecookies      = (bool)$this->aSettings['useCookies'];
        $oHTTPRequest->_useragent       = (string)$this->aSettings['userAgent'];
        $oHTTPRequest->_cookiepath      = dirname($cookieFile);
        $oHTTPRequest->_cookiefile      = basename($cookieFile);
        $oHTTPRequest->_verfySSL        = (bool)$this->aSettings['verifySSL'];
        if($this->aSettings['keepSession']){
            $this->oHTTPRequest = $oHTTPRequest;
        }
        return $oHTTPRequest->httpRequest($url, (bool)$this->aSettings['keepSession'], $this->aSettings['useCookies'], $post);
    }

    /**
     * Returns CURL error.
     *
     * @return array  Array of error number, error message
     * @since  6.0.2
     */
    function getError(){
        return
            is_object($this->oHTTPRequest)
                ? $this->oHTTPRequest->getError()
                : array(-1, 'No HTTP request object initialized');
    }

    /**
     * Returns CURL info.
     *
     * @return array
     * @see    curl_getinfo()
     * @since  6.0.2
     */
    function getInfo(){
        return $this->oHTTPRequest->getInfo();
    }
}
