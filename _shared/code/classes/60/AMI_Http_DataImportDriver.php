<?php
/**
 * Base functionality for http import.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   DataImportDriver
 * @version   $Id: AMI_Http_DataImportDriver.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Base HTTP data import driver.
 *
 * @package     DataImportDriver
 * @since       x.x.x
 * @amidev      Temporary
 */
abstract class AMI_Http_DataImportDriver extends AMI_DataImportDriver{
    /**
     * Connection method
     *
     * @var int
     */
    protected $connectionMethod = AMI_HTTPRequest::METHOD_GET;

    /**
     * Connection type
     *
     * @var string
     */
    protected $connectionType = 'http_curl';

    /**
     * Available connection types
     *
     * @var array
     */
    protected $aConnectionTypes = array('http_curl', 'http_fgc', 'http_fsock');

    /**
     * Connection pipe
     *
     * @var string
     */
    protected $oPipe;

    /**
     * Request settings
     *
     * Available options:
     * - dataMaxSize        The maximum size of the data that will be readed from the stream
     * - dataPartialSize    The maximum size of the data that will be readed from the socket in one iteration
     * - keepSession        Keep cUrl session alive
     * - connectionMethod   Set up POST or GET connection method
     *
     * @var array
     */
    protected $aRequestSettings = array();

    /**
     * Request data
     *
     * @var array
     */
    protected $aRequestData = array();

    /**
     * Data url for import
     *
     * @var string
     */
    protected $requestUrl;

    /**
     * Is connection initialized
     *
     * @var bool
     */
    protected $bIsInitialized = false;

    /**
     * Set http connection url.
     *
     * @param string $resourceName  Name of a resource
     * @return $this
     */
    public function setResourceName($resourceName){
        $this->requestUrl = $resourceName;

        return $this;
    }

    /**
     * Set up request data.
     *
     * @param array $aRequestSettings  HTTP request settings
     * @return $this
     */
    public function setRequestSettings(array $aRequestSettings = array()){
        // set up request data if exists
        if(isset($aRequestSettings['aRequestData'])){
            $this->aRequestData = $aRequestSettings['aRequestData'];
            unset($aRequestSettings['aRequestData']);
        }
        // set up connection settings
        $this->aRequestSettings = $aRequestSettings;

        // set up connection method
        $this->connectionMethod =
            (isset($aRequestSettings['connectionMethod']) && $aRequestSettings['connectionMethod'] == AMI_HTTPRequest::METHOD_GET)
                ? AMI_HTTPRequest::METHOD_GET
                : AMI_HTTPRequest::METHOD_POST;

        return $this;
    }

    /**
     * Initialize http connection.
     *
     * @return $this
     */
    public function initConnection(){

        // add event before initializing connection
        $aEvent = array('aRequestSettings' => $this->aRequestSettings, 'aRequestData' => $this->aRequestData);
        AMI_Event::fire('ami_data_httpimport_driver_before_init', $aEvent, $this->modId);
        $this->aRequestSettings = $aEvent['aRequestSettings'];
        $this->aRequestData = $aEvent['aRequestData'];

        if(in_array($this->connectionType, $this->aConnectionTypes)){
            if(empty($this->requestUrl)){
                trigger_error('Connection url is not specified', E_USER_WARNING);
            }else{
                switch($this->connectionType){
                    // call to curl_init
                    default:
                        $this->oPipe = new AMI_HTTPRequest();
                        break;

                    // open the socket
                    case 'http_fsock':
                        $this->oPipe = fsockopen($this->requestUrl, 80, $errno, $errstr, 10);

                        if(!is_resource($this->oPipe)){
                            trigger_error('Unable to open socket [err: ' . $errno .', ' . $errstr . ']', E_USER_WARNING);
                        }else{
                            // make socket headers
                            $tmpQueryString = http_build_query($this->aRequestData);
                            $connectionString =
                                $this->connectionType == AMI_HTTPRequest::METHOD_POST
                                    ? 'POST ' . $this->requestUrl
                                    : 'GET ' . $this->requestUrl . '?' . $tmpQueryString;
                            $connectionString .= "HTTP/1.1\r\nHost: " . AMI_Registry::get('ROOT') . "\r\nUser-Agent: PHP";
                            $connectionString .=
                                $this->connectionType == AMI_HTTPRequest::METHOD_POST
                                    ? "\r\nContent-Type: application/x-www-form-urlencoded\r\nContent-Length: ".strlen($tmpQueryString)
                                    : "";
                            $connectionString .= "\r\nConnection: Close\r\n\r\n" . ($this->connectionType == AMI_HTTPRequest::METHOD_POST ? $tmpQueryString : "");

                            // .. and write it to connection
                            fwrite($this->opipe, $connectionString);
                        }
                        break;

                    // fake
                    case 'http_fgc':
                        if(ini_get('allow_furl_open') == 1){
                            $this->oPipe = new stdClass();
                        }else{
                            trigger_error('Unable to establish connection: the "allow_furl_open" directive is disabled.', E_USER_WARNING);
                        }
                        break;
                }

                // chech for established connection and set up initialized flag
                if(is_resource($this->oPipe) || is_object($this->oPipe)){
                    $this->bIsInitialized = true;
                    return $this;
                }else{
                    trigger_error('Connection is not established [type: ' . $this->connectionType . ']', E_USER_WARNING);
                }
            }
        }else{
            trigger_error('Connection type is not supported', E_USER_WARNING);
        }

        return $this;
    }

    /**
     * Read the data from established connection.
     *
     * @return $this
     */
    public function readData(){
        if(false == $this->bIsInitialized){
            trigger_error('Unable to read data because connection is not initialized', E_USER_WARNING);
        }

        switch($this->connectionType){
            case 'http_fgc':
                $this->rawData = file_get_contents(
                    $this->requestUrl,
                    false,
                    null,
                    0,
                    isset($this->aRequestSettings['dataMaxSize']) ? (int)$this->aRequestSettings['dataMaxSize'] : 0
                );
                break;

            case 'http_fsock':
                $tmpPartialSize = isset($this->aRequestSettings['dataPartialSize']) ? $this->aRequestSettings['dataPartialSize'] : 4096;
                while(false == feof($this->oPipe)){
                    if(isset($this->aRequestSettings['dataMaxSize'])
                        && $this->aRequestSettings['dataMaxSize'] >= mb_strlen($this->rawData)
                    ){
                        break;
                    }
                    $this->rawData .= fgets($this->oPipe, $tmpPartialSize);
                }
                break;

            default:
                $this->rawData = $this->oPipe->send(
                    $this->requestUrl,
                    $this->aRequestData,
                    $this->connectionMethod
                );
                break;
        }

        return $this;
    }

    /**
     * Close the connection.
     *
     * @return $this
     */
    public function closeConnection(){
        if($this->bIsInitialized
            && (is_object($this->oPipe) || is_resource($this->oPipe))
        ){
            switch($this->connectionType){
                // nothing to do
                case 'http_fgc':
                    break;

                // close the socket
                case 'http_fsock':
                    fclose($this->oPipe);
                    break;

                // call to curl_close
                default:
                    $this->oPipe->close();
                    break;
            }

            return $this;
        }else{
            trigger_error('Unable to close connection because connection is not initialized', E_USER_WARNING);
        }
    }

}