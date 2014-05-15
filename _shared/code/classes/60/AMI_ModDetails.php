<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModDetails.php 49503 2014-04-08 05:09:05Z Leontiev Anton $
 * @since     5.14.8
 */

/**
 * Module front details body type action controller.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.14.8
 */
abstract class AMI_ModDetails extends AMI_ModComponent{
    /**
     * Item primary key
     *
     * @var mixed
     */
    protected $id;

    /**
     * Returns component type.
     *
     * @return string
     */
    public function getType(){
        return 'details';
    }

    /**
     * Initialization.
     *
     * @return AMI_ModDetails
     */
    public function init(){
        AMI_Event::addHandler('dispatch_mod_action_details_view', array($this, 'dispatchDetails'), $this->getModId());
        return $this;
    }

    /**
     * Event handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function dispatchDetails($name, array $aEvent, $handlerModId, $srcModId){
        $this->_details($aEvent);
        $this->displayView();
        return $aEvent;
    }

    /**
     * Common front module details action dispatcher.
     *
     * @param  array &$aEvent  Event data
     * @return void
     */
    protected function _details(array &$aEvent){
        $this->id = AMI::getSingleton('env/request')->get('id', 0);
    }

    /**
     * Item details.
     *
     * @return AMI_ModTableItem
     */
    protected function initModel(){
        return parent::initModel()->find($this->id);
    }
}

/**
 * Module front details body type view.
 *
 * @package    ModuleComponent
 * @subpackage View
 * @since      5.14.8
 */
abstract class AMI_ModDetailsView extends AMI_View{
    /**
     * Template engine object
     *
     * @var AMI_Template
     */
    protected $oTpl;

    /**
     * Body type view
     *
     * @var string
     */
    protected $bodyType = 'details';

    /**
     * Array of simple set fields
     *
     * @var array
     */
    protected $aSimpleSetFields = array('header', 'announce', 'body', 'fdate', 'ftime');

    /**
     * Module common front view
     *
     * @var AMI_ModCommonViewFrn
     */
    protected $oCommonView;

    /**
     * Supported "response_type" GET parameters
     *
     * @var array
     */
    protected $aSupportedResponseTypes = array('json', 'item_details');

    /**
     * Constructor.
     */
    public function  __construct(){
        parent::__construct();

        $this->oTpl = $this->getTemplate();

        $modId = $this->getModId();
        $this->oCommonView = AMI::getResource('module/common/view/frn');
        $this->oCommonView->setModId($modId);
        $this->oCommonView->initByBodyType($this->bodyType, $this->aSimpleSetFields);
    }

    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        $modId = $this->getModId();
        $aScope = $this->getScope('details') + $this->oCommonView->getFrontScope();
        $aPage = AMI_Registry::get('page', array());

        $aTplData = $this->oCommonView->getTplData();

        $colsOptionName = AMI_Registry::get('AMI/Module/Environment/Filter/active') ? 'body_filtered_cols' : 'cols';

        if(AMI::issetOption($modId, $colsOptionName)){
            $aScope['_cols'] = AMI::getOption($modId, $colsOptionName);
        }

        if(isset($aPage['itemId']) && $aPage['itemId'] > 0){
            $oItem = AMI::getResourceModel($modId . '/table')->find($aPage['itemId']);

            $aItem = $oItem->getData();
            $aItem += $this->oCommonView->getFrontScope();

            AMI_Event::fire('on_details_view', $aItem, $modId);

            $aEvent = array(
                'aScope' => &$aScope,
                'aData'  => &$aItem,
                'oItem'  => $oItem,
                'block'  => $this->tplBlockName
            );
            /**
             * Allows to modify item details data.
             *
             * @event      on_item_details  $modId
             * @eventparam array            &aScope  Scope
             * @eventparam array            &aData   Item data
             * @eventparam AMI_ModTableItem oItem    Table item model
             * @eventparam string           block    Template block name
             */
            AMI_Event::fire('on_item_details', $aEvent, $this->getModId());

            // Fill empty description for item
            $this->oCommonView->fillEmptyDescription($aItem, FALSE);
            // Fill empty description for category
            $this->oCommonView->fillEmptyDescription($aItem, TRUE);

            $idPage = isset($aItem['id_page']) ? $aItem['id_page'] : 0;
            $aItem['front_link'] = AMI_PageManager::getModLink($this->getModId(), AMI_Registry::get('lang_data'), $idPage, FALSE, TRUE);

            $aItem['fdate'] = AMI_Lib_Date::formatDateTime($aItem['date_created'], AMI_Lib_Date::FMT_DATE);
            $aItem['ftime'] = AMI_Lib_Date::formatDateTime($aItem['date_created'], AMI_Lib_Date::FMT_TIME);

            $aMetaData = $oItem->getMetaData();
            $GLOBALS['ModuleHtml']['headers'] = AMI_PageManager::getPageMetaData($aMetaData);
            $aItem['sm_data'] = json_encode($aMetaData['aOrigData']['html_meta']);

            $this->oCommonView->processFields($aItem, $aScope);
            $aScope += $aItem;
            $this->applyVars($aItem, $aScope);

            if(isset($aItem['details_noindex']) && $aItem['details_noindex'] == '1'){
                AMI_Event::addHandler('on_before_seo_process', array($this, 'handleSEOProcess'), $modId);
            }

            $aScope[$aTplData['set']['details']] = $this->oCommonView->parseTpl('details', $aScope);
        }else{
            $aScope['cat_nav_data'] = '?';
            AMI_Event::fire('on_details_view', $aScope, $modId);
            $aScope[$aTplData['set']['details']] = $this->oCommonView->parseTpl('empty_details', $aScope);
            if(AMI::issetAndTrueOption($modId, 'set_404_header')){
                AMI::getSingleton('response')->HTTP->setHeader404();
            }
        }

        // Response type output
        $outType = strtolower(AMI::getSingleton('env/request')->get('response_type', ''));
        if($outType && in_array($outType, $this->aSupportedResponseTypes)){
            $this->oCommonView->outType($aScope, $outType);
        }

        if(isset($aScope['header'])){
            $header = $aScope['header'];
            $description = isset($aScope['announce']) ? $aScope['announce'] : '';
            $image = '';
            if(isset($aScope['img_src']) && ($aScope['img_src'] != "")){
                $image = $aScope['img_src'];
            }
            if($oItem->og_image != ''){
                $image = $oItem->og_image;
            }
            $this->addOpenGraphTags($header, $description, $image);
        }

        return $aScope;
    }

    /**
     * Disable page indexing.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTableItem::setData()
     */
    public function handleSEOProcess($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['oController']->disablePageIndexing();
        return $aEvent;
    }

    /**
     * Fill the field callbacks.
     *
     * @return void
     */
    protected function prepareFieldCallbacks(){
        $this->oCommonView->setFieldCallback(
            'sublink',
            array(
                'object' => $this->oCommonView,
                'method' => 'getNavDataCB'
            )
        );

        if(sizeof($this->aSimpleSetFields) > 0){
            $this->oCommonView->setFieldCallback(
                'simple_sets',
                array(
                    'object' => $this->oCommonView,
                    'method' => 'applySimpleSetsCB'
                )
            );
        }
    }

    /**
     * Allows data customization during front page building.
     *
     * @param  array &$aItemData  Item data
     * @param  array &$aData  Page data
     * @return void
     * @amidev Temporary
     */
    protected function applyVars(array &$aItemData, array &$aData){
    }
}
