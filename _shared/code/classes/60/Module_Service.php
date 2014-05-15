<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module
 * @version   $Id: Module_Service.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Module service functions.
 *
 * @package    Module
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class Module_Service extends AMI_Module_Service{
    /**
     * Dispatches front module action.
     *
     * @param  AMI_Request  $oRequest   Request
     * @param  AMI_Response $oResponse  Response
     * @return void
     */
    public function dispatchAction(AMI_Request $oRequest, AMI_Response $oResponse){
        global $AMI_ENV_SETTINGS, $CLASSES_PATH;

        $modId = $ActiveModule = $oRequest->get('mod_id');
        $modAction = $oRequest->get('mod_action');

        $ActiveModule = $modId;

        define('AMI_60', TRUE);
        AMI::addResourceMapping(require($CLASSES_PATH . '60/resourceMapping.php'));
        AMI::addResourceMapping(require($CLASSES_PATH . '60/resourceMapping.60.php'));

        if($AMI_ENV_SETTINGS['mode'] == 'fast' && !$oRequest->get('ami_full', FALSE)){
            AMI::getSingleton('core')->init();
        }

        // Language

        $lang = $oRequest->get('ami_locale', 'en');
        AMI::getSingleton('env/session', AMI::getOption('core', 'allow_multi_lang') ? array('locale' => $lang) : array());
        AMI_Registry::set('lang_data', $lang);
        AMI_Registry::set('lang', $lang);
        AMI::getResource('env/template_sys')->setLocale($lang);
        $aDateFormats = AMI::getOption('core', 'dateformat_front');
        AMI_Registry::get('oGUI')->addGlobalVars(
            array(
                'active_lang' => $lang,
                'date_format' => preg_replace('/ .*$/', '', isset($aDateFormats[$lang]) ? $aDateFormats[$lang] : $aDateFormats['en'])
            )
        );
        AMI_Registry::set('ami_allow_model_save', TRUE);
        AMI_GlobalFilters::setGlobalFilter('side');

        if(in_array($modAction, array('list_view', 'form_save'))){
            $oResponse->setType('JSON');
        }
        AMI_Registry::set('ami_request_type', 'ajax');

        AMI::setOption($modId, 'extensions', array());###
        $oController = AMI::getResource($modId . '/module/controller/frn', array($oRequest, $oResponse));
        $oController->init();

        $aEvent = array();
        /**
         * Request processing controller module.
         *
         * @event      dispatch_request $modId
         */
        AMI_Event::fire('dispatch_request', $aEvent, $modId);
        $aViews = $oController->getViews();
        foreach($aViews as $oView){
            $oResponse->write($oView->get());
        }
    }
}
