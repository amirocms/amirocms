<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModEmptyFrn.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.14.8
 */

/**
 * Module front category empty body type action controller.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.14.8
 */
abstract class AMI_ModEmptyFrn extends AMI_ModComponent{
    /**
     * Initialization.
     *
     * @return AMI_ModEmptyFrn
     */
    public function init(){
        $this->bDispayView = true;
        return $this;
    }

    /**
     * Returns component type.
     *
     * @return string
     */
    public function getType(){
        return 'empty';
    }

    /**
     * Returns component view.
     *
     * @return AMI_ViewEmpty
     */
    public function getView(){
        $oView = $this->_getView('/' . $this->getType() . '/view/frn');
        return $oView;
    }
}

/**
 * Module front category empty body type view.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.14.8
 */
abstract class AMI_ModEmptyView extends AMI_View{
    /**
     * Template engine object
     *
     * @var AMI_Template
     */
    protected $oTpl = null;

    /**
     * Template empty item set
     *
     * @var string
     */
    protected $tplItemEmptySet = 'body_empty';

    /**
     * Constructor.
     */
    public function  __construct(){
        parent::__construct();
        $this->oTpl = $this->getTemplate();
    }

    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        $modId = $this->getModId();
        $html = $this->oTpl->parse($this->tplBlockName . ':' . $this->tplItemEmptySet);
        if(AMI::issetAndTrueOption($modId . '_cat', 'set_404_header')){
            AMI::getSingleton('response')->HTTP->setHeader404();
        }
        return $html;
    }

    /**
     * Create links offset.
     *
     * @return AMI_ModItemsView
     */
    protected function createLinksOffset(){
        $itemOffsetUrl = "";
        $catOffsetUrl = "";

        $this->aFrontLinks = array();
        if($this->offset > 0){
            $this->oTpl->addGlobalVars(array($this->offsetVar => $this->offset));
            $itemOffsetUrl = "&" . $this->offsetVar . "=" . $this->offset;
        }

        if($this->catoffset > 0){
            $this->oTpl->addGlobalVars(array($this->catoffsetVar => $this->catoffset));
            $catOffsetUrl = "&" . $this->catoffsetVar . "=" . $this->catoffset;
        }

        $this->aFrontLinks["front_cats_link"] = $catOffsetUrl;
        $this->aFrontLinks["front_items_link"] = $catOffsetUrl . $itemOffsetUrl;

        return $this;
    }

    /**
     * Fill the field callbacks.
     *
     * @return void
     */
    protected function prepareFieldCallbacks(){
        $this->aFields['sublink'] = array(
            "action" => "callback",
            "object" => &$this,
            "method" => "getNavDataCB"
        );

        if(sizeof($this->aSimpleSetFields) > 0){
            $this->aFields['simple_sets'] = array(
                "action" => "callback",
                "object" => &$this,
                "method" => "applySimpleSetsCB"
            );
        }
    }

    /**
     * Generates navigation data.
     *
     * @param  array &$aItemData  Item data
     * @param  array &$aData      List data
     * @return void
     */
    protected function getNavDataCB(array &$aItemData, array &$aData){
	$aNavData = array(
            'modId' => $this->getModId()
        );
        $aItemData += $this->aFrontLinks;

        $aNavData["catid"] = isset($aItemData['cat_id']) ? $aItemData['cat_id'] : null;
        $aNavData["catid_sublink"] = isset($aItemData['cat_sublink']) ? $aItemData['cat_sublink'] : null;
        $aItemData += AMI_PageManager::applyNavData($aNavData);
        $aNavData["cat_nav_data"] = $aItemData["nav_data"];
        unset($aItemData["nav_data"]);

        $aNavData["id"] = isset($aItemData['id']) ? $aItemData['id'] : null;
        $aNavData["id_sublink"] = isset($aItemData['sublink']) ? $aItemData['sublink'] : null;
        $aItemData += AMI_PageManager::applyNavData($aNavData);
    }

    /**
     * Applies simple sets to specified simple items on frontend.
     *
     * @param  array &$aItemData  Item data
     * @param  array &$aData      List data
     * @return void
     */
    protected function applySimpleSetsCB(array &$aItemData, array &$aData){
        foreach($this->aSimpleSetFields as $fieldName){
            $aItemData["_".$fieldName] = isset($aItemData[$fieldName]) ? $aItemData[$fieldName] : null;
            if(isset($aItemData[$fieldName]) && $aItemData[$fieldName] != ''){
                $aItemData[$fieldName] = $this->oTpl->parse($this->tplBlockName . ':' . $this->tplSimpleFieldPrefix . $fieldName, $aItemData);
                AMI_Registry::set('AMI/Module/Environment/Template/Scope/simpleset_' . $fieldName, $aItemData[$fieldName]);
            }
        }
    }

    /**
     * Process item fields.
     *
     * @param  array &$aItemData  Item data
     * @param  array &$aData      List data
     * @return void
     */
    private function processFields(array &$aItemData, array &$aData){
        foreach($this->aFields as $name => $aRule){
            if(!is_array($aRule)){
                continue;
            }
            if(!isset($aItemData[$name])){
                $aItemData[$name] = null;
            }
            switch($aRule['action']){
                case 'callback':
                    $method = $aRule['method'];
                    if(is_object($aRule['object'])){
                        if(method_exists($aRule['object'], $method)){
                            $aRule['object']->{$method}($aItemData, $aData);
                        }else{
                            trigger_error(get_class($aRule['object'])."::{$method} doesn't exist", E_USER_ERROR);
                        }
                    }else{
                        if(function_exists($method)){
                            $method($aItemData, $aData);
                        }else{
                            trigger_error("Function {$method} doesn't exist", E_USER_ERROR);
                        }
                    }
                    break;
            }
        }
    }
}
