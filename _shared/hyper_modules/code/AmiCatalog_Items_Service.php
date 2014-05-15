<?php
/**
 * AmiCatalog configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiCatalog_Items
 * @version   $Id: AmiCatalog_Items_Rules.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiCatalog/Items service functions.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage Service
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_Items_Service extends AMI_Module_Service{
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
            'sys.item_add'  => FALSE,
            'sys.item_list' => 'handleItemListAction',
            'sys.item_get'  => FALSE
        );
        if(isset($aActionXHandler[$action])){
            $this->oRequest = AMI::getSingleton('env/request');
            $this->section = AMI_ModDeclarator::getInstance()->getSection($this->oRequest->get('modId'));
            $lang = $this->oRequest->get('ami_locale', 'en');
            AMI_Registry::set('lang_data', $lang);
            AMI_Registry::set('lang', $lang);

            //$this->oWebService->setAuthRequired($action);

            if($aActionXHandler[$action]){
                if($action == 'sys.item_list'){
                    AMI_Event::dropHandler('on_webservice_{' . $action . '}_action');
                }
                AMI_Event::addHandler(
                    'on_webservice_{' . $action . '}_action',
                    array($this, $aActionXHandler[$action]),
                    AMI_Event::MOD_ANY,
                    AMI_Event::PRIORITY_HIGH
                );
            }
        }
        return $aEvent;
    }

    /**
     * Handles sys.item_list action
     *
     * @param  array $aEvent  'on_webservice_{sys.item_list}_action' event data
     * @return array
     */
    public function handleItemListAction($name, array &$aEvent){
        $aEvent['_break_event'] = true;

        if($this->oRequest->get('oldEnv', FALSE)){
            $aEvent['oWebService'] = $this->oWebService;
            $ModuleEshopService = new ModuleEshopService();
            $ModuleEshopService->getItemList($aEvent);
        }else{
            AMI_Registry::push('AMI/override/forceModSection', $this->section);
            $this->oWebService->handleItemListAction($name, $aEvent);
            AMI_Registry::pop('AMI/override/forceModSection');
        }

        return $aEvent;
    }
}
