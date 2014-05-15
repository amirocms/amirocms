<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Service
 * @version   $Id: SourceApp_Service.php 47911 2014-02-13 12:02:46Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * User source app service functions.
 *
 * @package    Service
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class SourceApp_Service extends AMI_Module_Service{
    /**
     * Dispatches  module action.
     *
     * @param AMI_Request  $oRequest   Request
     * @param AMI_Response $oResponse  Response
     * @return void
     */
    public function dispatchAction(AMI_Request $oRequest, AMI_Response $oResponse){
        global $cms;

        $lang = $oRequest->get('ami_locale', false);
        if($lang){
            AMI_Registry::set('lang_data', $lang);
            AMI_Registry::set('lang', $lang);
        }

        if(isset($cms) && is_object($cms)){
            $cms->Core->Cache->Disable();
        }

        if($oRequest->get('driver') && $oRequest->get('driver_action')){
            $oResponse->write(
                AMI::getResource('user_source_app')
                ->dispatchDriverAction($oRequest->get('driver'), $oRequest->get('driver_action'))
            );
        }else{
            throw new Exception('No driver or no driver action given', E_USER_ERROR);
        }
    }
}
