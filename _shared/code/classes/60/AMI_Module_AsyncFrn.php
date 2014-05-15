<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_Module_AsyncFrn.php 45856 2013-12-24 09:48:43Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Module front async component action controller.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class AMI_Module_AsyncFrn extends AMI_ModComponent{
    /**
     * Flag specifying to include async scripts / css
     *
     * @var bool
     */
    private static $bInitializeUI = true;

    /**
     * Supported async component types
     *
     * @var  array
     * @todo ability to add own component types
     */
    private $aSupportedComponentTypes = array('list', 'form', 'form_filter');

    /**
     * Returns component type.
     *
     * @return string
     */
    public function getType(){
        return 'async';
    }

    /**
     * Returns view object.
     *
     * @return News_DetailsView
     */
    public function getView(){
        return $this->_getView('/' . $this->getType() . '/view/frn');
    }

    /**
     * Initialization.
     *
     * @return AMI_Module_AsyncFrn
     */
    public function init(){
        $this->displayView();

        if(self::$bInitializeUI){
            self::$bInitializeUI = false;
            /**
             * @var gui
             */
            $oTpl = AMI_Registry::get('oGUI');
            // Common scripts
            AMI_Registry::set('AMI/resources/j/60', TRUE);
            $oTpl->addStyle('base.css');
            $oTpl->addStyle('common.60.css');
            $oTpl->addHtmlScript("var amiModuleLink='" . rtrim($GLOBALS['ROOT_PATH_WWW'], '/') . "/ami_service.php', editorBaseHref='" . $GLOBALS['ROOT_PATH_WWW'] . "';");
        }
        AMI_Event::addHandler('on_before_view_mod_page', array($this, 'handleModPageView'), $this->getModId());
        return $this;
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
        // Load AJAX components {

        AMI_Registry::push('ami_request_type', 'ajax');
        /**
         * Module action controller
         *
         * @var AMI_Mod
         */
        $oController =
            AMI::getResource(
                $this->getModId() . '/module/controller/frn',
                array(AMI::getSingleton('env/request'), AMI::getSingleton('response'))
            );
        $aEvent['aScope']['hash'] = mt_rand(); ### admin url hash for ajax
        $aEvent['aScope']['mod_id'] = $this->getModId();
        $aEvent['aScope']['mod_components'] = $oController->getComponentList();
        array_walk(
            $aEvent['aScope']['mod_components'],
            array($this, 'walkModComponents')
        );
        $aEvent['aScope']['mod_components'] = implode(', ', $aEvent['aScope']['mod_components']);
        AMI_Registry::pop('ami_request_type');

        // } Load AJAX components
        // Parse client locales {

        /**
         * @var AMI_TemplateSystem
         */
        $oTpl = AMI::getSingleton('env/template_sys');
        $aLocale = array_merge(
            $oTpl->parseLocale(AMI_iTemplate::LNG_MOD_PATH . '/_client.lng'),
            $oTpl->parseLocale($oController->getClientLocalePath())
        );
        $aJSLocale = array();
        foreach($aLocale as $key => $value){
            $aJSLocale[] = "'" . AMI_Lib_String::jParse($key) . "': '" . AMI_Lib_String::jParse($value) . "'";
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
     * @see    AMI_Module_AsyncFrn::handleModPageView()
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
