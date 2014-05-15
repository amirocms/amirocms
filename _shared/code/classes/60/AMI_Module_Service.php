<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module_AMI_Module
 * @version   $Id: AMI_Module_Service.php 47245 2014-01-30 14:04:48Z Kolesnikov Artem $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Module service action controller.
 *
 * @package    Module_AMI_Module
 * @subpackage Controller
 * @static
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class AMI_Module_Service{
    /**
     * Request object.
     *
     * @var AMI_Request
     */
    protected $oRequest;

    /**
     * Response object.
     *
     * @var AMI_Response
     */
    protected $oResponse;

    /**
     * Web-service event if service is run in its content
     *
     * @var AmiClean_Webservice_Service
     * @see this::addWebserviceHandlers()
     */
    protected $oWebService;

    /**
     * Dispatches module action.
     *
     * @param  AMI_Request  $oRequest   Request
     * @param  AMI_Response $oResponse  Response
     * @return void
     */
    public function dispatchAction(AMI_Request $oRequest, AMI_Response $oResponse){
        $this->oRequest  = $oRequest;
        $this->oResponse = $oResponse;
    }

    /**
     * Dispatches raw action.
     *
     * @return void
     */
    public function dispatchRawAction(){
    }

    /**
     * Adds web-service handlers, need to be overridden in children.
     *
     * @param  array $aEvent  'on_webservice_start' event data
     * @return void
     * @see    AmiClean_Webservice_Service::dispatchAction()
     */
    public function addWebserviceHandlers(array &$aEvent){
        /*
        $oReflection = new ReflectionClass($this);
        $oMethod = $oReflection->getMethod('addWebserviceHandlers');
        if($oMethod->class === get_class($this)){
            // this::addWebserciceHandlers() is overriden
            $this->oWebService = $aEvent['oWebService'];
        }
        */
        if(isset($aEvent['oWebService'])){
            $this->oWebService = $aEvent['oWebService'];
        }
    }

    /**
     * Send response.
     *
     * @param  string $response  Response
     * @param  int    $code      HTTP code
     * @return void
     * @exitpoint
     */
    protected function send($response, $code = 200){
        AMI_Service::hideDebug();
        if(AMI::isResource('response')){
            $this->sendUsingResponseObject($response, $code = 200);
        }else{
            $this->sendRawResponse($response, $code = 200);
        }
    }

    /**
     * Send response using AMI_Response object.
     *
     * @param  string $response  Response
     * @param  int    $code      HTTP code
     * @return void
     * @exitpoint
     */
    protected function sendUsingResponseObject($response, $code = 200){
        /**
         * @var AMI_Response
         */
        $oResponse = AMI::getSingleton('response');
        if(!$oResponse->isStarted()){
            $oResponse->start();
        }
        $oResponse->write($response);
        switch($code){
            case 503:
                $oResponse->HTTP->setServiceUnavailable(3600);
                break;
        }
        $oResponse->send();
    }

    /**
     * Send response using AMI_Response object.
     *
     * @param  string $response  Response
     * @param  int    $code      HTTP code
     * @return void
     * @exitpoint
     */
    protected function sendRawResponse($response, $code = 200){
        $protocol = @getenv('SERVER_PROTOCOL');
        if(!$protocol){
            $protocol = 'HTTP/1.1';
        }
        switch($code){
            case 200:
                header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
                header('Cache-Control: no-cache, no-store, must-revalidate, post-check=0, pre-check=0');
                header('Pragma: no-cache');
                header('Status: 200 OK');
                header($protocol . ' 200 OK');
                break;
            case 503:
                header($protocol . ' 503 Service Temporarily Unavailable');
                header('Status: 503 Service Temporarily Unavailable');
                header('Retry-After: 3600');
                break;
        }
        die($response);
    }
}
