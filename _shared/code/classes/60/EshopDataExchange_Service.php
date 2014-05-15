<?php
/**
 * EshopDataExchange module service.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module_EshopDataExchange
 * @version   $Id: EshopDataExchange_Service.php 44161 2013-11-20 13:35:57Z Kolesnikov Artem $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * EshopDataExchange module service functions.
 *
 * @package    Module_EshopDataExchange
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class EshopDataExchange_Service extends AMI_Module_Service{
    /**
     * Adds web-service handlers.
     *
     * @param  array $aEvent  'on_webservice_start' event data
     * @return void
     */
    public function addWebserviceHandlers(array &$aEvent){
        parent::addWebserviceHandlers($aEvent);

        $action = $aEvent['action'];
        $aActionXHandler = array(
            'get_yml_url'  => 'handleGetYMLURLAction'
        );
        if(isset($aActionXHandler[$action])){
            $this->oRequest = AMI::getSingleton('env/request');
            $this->section = 'eshop';
            // $this->oWebService->setAuthRequired($action);

            if($aActionXHandler[$action]){
                AMI_Event::addHandler(
                    'on_webservice_{' . $action . '}_action',
                    array($this, $aActionXHandler[$action]),
                    AMI_Event::MOD_ANY,
                    AMI_Event::PRIORITY_HIGH
                );
            }
        }
    }

    /**
     * Handles 'get_yml_url' action.
     *
     * @param  string $name     Event name
     * @param  array  &$aEvent  Event data
     * @return array
     */
    public function handleGetYMLURLAction($name, array &$aEvent){
        $lastYMLFile = AMI_ModSettings::getOptions($this->section . '_data_exchange', 'last_yml_file', TRUE, TRUE);
        if(!$lastYMLFile){
            $this->oWebService->error(
                AmiClean_Webservice_Service::ERR_FAIL,
                "Start export first"
            );
        }
        $tail = '_mod_files/_upload/tmp/' . $lastYMLFile;
        $path = AMI_Registry::get('path/root') . $tail;
        if(file_exists($path) && is_readable($path)){
            $aEvent['url'] = AMI_Registry::get('path/www_root') . $tail;
            $this->oWebService->ok($aEvent);
        }else{
            $this->oWebService->error(
                AmiClean_Webservice_Service::ERR_FAIL,
                "File '" . $lastYMLFile . "' not found"
            );
        }

        return $aEvent;
    }
}
