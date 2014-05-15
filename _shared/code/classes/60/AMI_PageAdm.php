<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Page
 * @version   $Id: AMI_PageAdm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Admin page controller.
 *
 * @package    Page
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
final class AMI_PageAdm{
    /**
     * Request object.
     *
     * @var AMI_Request
     */
    private $oRequest;

    /**
     * Response object.
     *
     * @var AMI_Response
     */
    private $oResponse;

    /**
     * Default module name.
     *
     * @var string
     */
    private $defaultModule = 'news';

    /**
     * Module ID.
     *
     * @var string
     */
    private $modId;

    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request object
     * @param AMI_Response $oResponse  Response object
     * @exitpoint
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        $this->oRequest = $oRequest;
        $this->oResponse = $oResponse;
        $this->modId = $this->oRequest->get('mod_id', $this->defaultModule);
        AMI_Event::addHandler('on_before_view_mod_page', array($this, 'handleModPageView'), 'page_adm');
        $oView = new AMI_PageViewAdm;
        $oResponse->write($oView->get());
    }

    /**
     * Event handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $targetModId   Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function handleModPageView($name, array $aEvent, $handlerModId, $targetModId){
        /**
         * Module action controller
         *
         * @var AMI_Mod
         */
        $oController = AMI::getResource($this->modId . '/module/controller/adm', array($this->oRequest, $this->oResponse));
        $oController->initComponents();
/*
        $modController = $this->oRequest->get('controller_res_id', $this->modId);
        $oController = AMI::getResource($modController . '/module/controller/adm', array($this->oRequest, $this->oResponse));
        $this->oRequest->set('controller_res_id', '');
*/
        $aEvent['aScope']['hash'] = mt_rand(); ### admin url hash for ajax
        $aEvent['aScope']['mod_id'] = $this->modId;
        $aEvent['aScope']['mod_components'] = $oController->getComponentList();
        array_walk(
            $aEvent['aScope']['mod_components'],
            array($this, 'walkModComponents')
        );
        $aEvent['aScope']['mod_components'] = implode(', ', $aEvent['aScope']['mod_components']);

        // Parse client locales {

        /**
         * @var AMI_TemplateSystem
         */
        $oTpl = AMI::getSingleton('env/template_sys');
        $aLocale = $oTpl->parseLocale(AMI_iTemplate::LNG_MOD_PATH . '/_client.lng');
        $clientLocalePath = $oController->getClientLocalePath();
        if($clientLocalePath){
            $aLocale = array_merge($aLocale, $oTpl->parseLocale($clientLocalePath));
        }
        $aJSLocale = array();
        foreach($aLocale as $key => $value){
            $aJSLocale[] = "'" . AMI_Lib_String::jParse($key) . "': '" . AMI_Lib_String::jParse($value) . "'";
        }
        if(isset($aLocale['list_add_new_button'])){
            $aEvent['aScope']['list_add_new_button'] = $aLocale['list_add_new_button'];
        }
        $aEvent['aScope']['js_locales'] = implode(', ', $aJSLocale);

        // } Parse client locales

        return $aEvent;
    }

    /**
     * Array walk callback.
     *
     * @param  array  &$aData  Component data
     * @param  string $key     Component key
     * @return void
     * @see    AMI_PageAdm::handleModPageView()
     */
    private function walkModComponents(array &$aData, $key){
        if(is_array($aData['related'])){
            foreach($aData['related'] as $index => $serialId){
                $aData['related'][$index] = "'" . $serialId . "'";
            }
        }
        $result = $key . ": {type: '" . $aData['type'] . "', fullEnv: " . ($aData['fullEnv'] ? 'true' : 'false') . ", related: [" . implode(", ", $aData['related']) . "]";
        if(isset($aData['primary'])){
            $result .= (', primary: ' . ($aData['primary'] ? 'true' : 'false'));
        }
        $result .= "}";
        $aData = $result;
    }
}
