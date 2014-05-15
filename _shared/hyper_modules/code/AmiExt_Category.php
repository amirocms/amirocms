<?php
/**
 * AmiExt/Category extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Config_AmiExt_Category
 * @version   $Id: AmiExt_Category.php 46631 2014-01-15 19:08:53Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/Category extension configuration controller.
 *
 * Common admin/front code.
 *
 * @package    Config_AmiExt_Category
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class AmiExt_Category extends Hyper_AmiExt{

    /**
     * Category table resource name
     *
     * @var string
     */
    protected $resName = '';

    /**
     * Category table prefix
     *
     * @var string
     */
    protected $prefix = 'cat';

    /**
     * Category alias prefix
     *
     * @var string
     */
    protected $aliasPrefix = 'cat_';

    /**
     * Array of categories
     *
     * @var array
     */
    protected $aCat = null;

    /**
     * Current category id
     *
     * @var string
     */
    protected $catId = 0;

    /**
     * Category item data
     *
     * @var array
     */
    protected $aItem;

    /**
     * Joining fields
     *
     * @var array
     * @see AmiExt_Category::handleGetAvailableFields()
     */
    protected $aJoinedFields = array('id', 'header');

    /**
     * Contains difference for element counters
     *
     * @var array
     * @see AmiExt_Category::handleSaveModelItem()
     * @see AmiExt_Category::handleDeleteModelItem()
     */
    protected $aRollback;

    /**
     * Flag specifying to add 'cats' subcomponent to the 'items' component
     *
     * @var    bool
     * @amidev Temporary
     */
    protected $addSubComponent = FALSE;

    /**
     * Constructor.
     *
     * @param string  $modId        Module id
     * @param string  $optSrcId     Options source module id
     * @param AMI_Mod $oController  Module controller
     */
    public function __construct($modId, $optSrcId = '', AMI_Mod $oController = null){

        $resId = $modId . '/table/model';
        if(!AMI::isResource($resId)){
            return;
        }

        $oTable = AMI::getResourceModel($modId . '/table', array(array('extModeOnConstruct' => 'none')));
        $this->resName = $oTable->getDependenceResId($this->prefix);
        if(is_null($this->resName)){
            return;
        }

        parent::__construct($modId, $optSrcId, $oController);

        // Add available navigation fields to select
        $this->aJoinedFields = array_merge(
            $this->aJoinedFields,
            AMI::getResourceModel($this->resName . '/table')->getNavFields()
        );
        $this->setInstalled(!is_null($this->resName));
    }

    /**
     * Returns categories list.
     *
     * @param  string $force  Force getting category list
     * @return array
     */
    public function getCatList($force = false){
        if(is_null($this->aCat) || $force){
            $this->aCat = array();
            $oList = $this->getCatListModel();
            $hasIdPage = false;
            if(
                AMI::issetAndTrueOption('core', 'multi_page_allowed') &&
                AMI::issetAndTrueOption($this->getModId(), 'multi_page')
            ){
                $hasIdPage = true;
            }
            foreach($oList as $oItem){
                $name = $oItem->header;
                if($hasIdPage){
                    $pageName = '';
                    if($oItem->id_page){
                        $pageName = AMI_PageManager::getModPageName($oItem->id_page, $this->getModId(), AMI_Registry::get('lang_data'));
                        $name = '[' . $pageName . '] ' . $name;
                    }else{
                    }
                }
                $this->aCat[] = array(
                    'id'   => $oItem->id,
                    'name' => $name
                );
            }
        }
        return $this->aCat;
    }

    /**
     * Returns alias prefix i. e. 'cat_'.
     *
     * @return string
     */
    public function getAliasPrefix(){
        return $this->aliasPrefix;
    }

    /**
     * Category id field callback.
     *
     * @param  array $aData  Field data
     * @return array
     * @see    AmiExt_Category::handleTableGetItem()
     */
    public function fcbCatId(array $aData){
        if($aData['action'] === 'get' && isset($aData['oItem']->id_cat)){
            $aData['value'] = $aData['oItem']->id_cat;
        }
        return $aData;
    }

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Extension pre-initialization.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handlePreInit($name, array $aEvent, $handlerModId, $srcModId){
        $modId = $aEvent['modId'];
        $side = AMI_Registry::get('side');

        // AMI_Event::addHandler('on_before_view_list', array($this, 'handleBeforeViewList'), $modId);
        AMI_Event::addHandler('on_table_get_list', array($this, 'handleTableGetList'), $modId);
        AMI_Event::addHandler('on_table_get_item', array($this, 'handleTableGetList'), $modId);
        AMI_Event::addHandler('on_table_get_item_post', array($this, 'handleTableGetItem'), $modId);
        AMI_Event::addHandler('on_get_available_fields', array($this, 'handleGetAvailableFields'), $modId);
        AMI_Event::addHandler('on_query_add_joined_columns', array($this, 'handleQueryAddJoinedColumns'), $this->resName);
        AMI_Event::addHandler('on_get_nav_data', array($this, 'handleGetNavData'), $modId);
        AMI_Event::addHandler('on_get_nav_data', array($this, 'handleGetCatNavData'), $this->resName);
        AMI_Event::addHandler('on_get_id_page', array($this, 'handleGetIdPage'), $this->resName);
        AMI_Event::addHandler('on_get_validators', array($this, 'handleGetValidators'), $modId);
        AMI_Event::addHandler('on_before_save_model_item', array($this, 'handleSaveModelItem'), $modId);
        AMI_Event::addHandler('on_after_delete_model_item', array($this, 'handleDeleteModelItem'), $modId);
        AMI_Event::addHandler('on_generate_html_meta_title', array($this, 'handleGenerateHTMLMetaTitle'), $modId);
        AMI_Event::addHandler('dispatch_mod_action_form_save', array($this, 'handleDispatchModActionFormSave'), $modId);
        ### AMI_Event::addHandler('on_rollback_save_model_item', array($this, 'handleRollbackModelItem'), $modId);

        if($side == 'adm'){
            AMI_Event::addHandler('on_before_view_list', array($this, 'handleBeforeViewList'), $modId);
            AMI_Event::addHandler('on_list_body_row', array($this, 'handleListBodyRow'), $modId, AMI_Event::PRIORITY_HIGH);
            AMI_Event::addHandler('on_filter_init', array($this, 'handleFilterInit'), $modId);
            $oView = $this->getView($side);
            if($oView){
                $oView->setExt($this);
                AMI_Event::addHandler('on_list_columns', array($oView, 'handleListColumns'), $modId);
                AMI_Event::addHandler('on_list_sort_columns', array($oView, 'handleListSortColumns'), $modId);
                AMI_Event::addHandler('on_list_group_actions', array($oView, 'handleListGroupActions'), $modId);
                AMI_Event::addHandler('on_form_fields_form', array($oView, 'handleFormFields'), $modId);
                AMI_Event::addHandler('on_form_fields_form_filter', array($oView, 'handleFilterFormFileds'), $modId);
            }
            /*
            foreach(array('add', 'save') as $action){
                AMI_Event::addHandler('dispatch_mod_action_form_' . $action, array($this, AMI::actionToHandler($action)), $modId);
            }
            */
        }elseif($side == 'frn'){
            if(AMI_Registry::exists('page/catId')){
                // sync
                $this->catId = (int)AMI_Registry::get('page/catId');
                AMI_Event::addHandler('on_before_init_componets', array($this, 'handleBeforeInitComponents'), $modId);
                AMI_Event::addHandler('on_after_init_componets', array($this, 'handleAfterInitComponents'), $modId);
                if($this->catId > 0){
                    AMI_Event::addHandler('on_before_view_items', array($this, 'handleBeforeViewFrn'), $modId);
                    AMI_Event::addHandler('on_before_view_details', array($this, 'handleBeforeViewFrn'), $modId);
                }
            }else{
                // async ?
                AMI_Event::addHandler('dispatch_mod_action_list_view', array($this, AMI::actionToHandler('list_view')), $modId);
            }
            AMI_Event::addHandler('on_list_recordset', array($this, 'handleListRecordset'), $modId);
        }
        return $aEvent;
    }

    /**
     * Handles items list getting.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTable::getList()
     */
    public function handleTableGetList($name, array $aEvent, $handlerModId, $srcModId){
        /**
         * @var AMI_ModTable
         */
        $oTable = $aEvent['oTable'];
        if(is_null($oTable->setActiveDependence($this->prefix))){
            trigger_error('Categories table not found', E_USER_ERROR);
        }
        return $aEvent;
    }

    /**
     * Handles item getting.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTable::getItem()
     */
    public function handleTableGetItem($name, array $aEvent, $handlerModId, $srcModId){
        /**
         * @var AMI_ModTableItem
         */
        $oTableItem = $aEvent['oItem'];
        $oTableItem->setFieldCallback('cat_id', array($this, 'fcbCatId'));
        return $aEvent;
    }

    /**
     * Prepares scope for list.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_View::getScope()
     */
    public function handleBeforeViewList($name, array $aEvent, $handlerModId, $srcModId){
        // Set prefixes for category column
        $aEvent['aScope'] += array(
            'cat_id_alias'     => $this->aliasPrefix . 'id',
            'cat_header_alias' => $this->aliasPrefix . 'header'
        );
        return $aEvent;
    }

    /**
     * Adds category columns to list model.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::getContent()
     */
    public function handleQueryAddJoinedColumns($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['oList']->addColumns($this->aJoinedFields);
        return $aEvent;
    }

    /**
     * Appends category fields to available fields.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTable::getAvailableFields()
     */
    public function handleGetAvailableFields($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['aFields']['cat'] = $this->aJoinedFields;
        return $aEvent;
    }

    /**
     * Appends navigation data navigation structure.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTable::getUrl()
     */
    public function handleGetNavData($name, array $aEvent, $handlerModId, $srcModId){
    	$aEvent['aNavModNames'] = array('catid' => $this->resName) + $aEvent['aNavModNames'];
        $aEvent['aNavData']['catid_sublink'] = $aEvent['oItem']->{$this->aliasPrefix . 'sublink'};
        return $aEvent;
    }

    /**
     * Appends category specified navigation data.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTable::getUrl()
     */
    public function handleGetCatNavData($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['aNavModNames'] = array('id' => $srcModId) + $aEvent['aNavModNames'];
    	return $aEvent;
    }

    /**
     * Appends navigation data navigation structure.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTable::getUrl()
     */
    public function handleGetIdPage($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['pageId'] = $aEvent['oItem']->{$this->aliasPrefix . 'id_page'};
        $aEvent['_break_event'] = true;
        return $aEvent;
    }

    /**
     * Handling save action and create new category if corresponding form fild filled.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleDispatchModActionFormSave($name, array $aEvent, $handlerModId, $srcModId){
        if($aEvent['oRequest']->get('catname')){
            /**
             * Create new category if corresponding form field filled
             */
            $oCatItem = AMI::getResourceModel($this->getCatSubmodId($handlerModId) . '/table')->getItem();
            $oCatItem->header = $aEvent['oRequest']->get('catname');
            $oCatItem->id_page = $aEvent['oRequest']->get('catname_id_page', 0);
            $oCatItem->announce = '';
            $oCatItem->body = '';
            $oCatItem->public = 1;

            if($oCatItem->save()){
            	$aEvent['oRequest']->set('id_cat', $oCatItem->id);
            	$aEvent['oResponse']->addStatusMessage('status_category_add');
            }else{
                trigger_error('Unable to create category: '.$aEvent['oRequest']->get('catname'));
            }
    	}
    	return $aEvent;
    }

    /**
     * Adds filter field.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::getContent()
     */
    public function handleFilterInit($name, array $aEvent, $handlerModId, $srcModId){
        $aField = $this->getFilterField();
        $aEvent['oFilter']->addViewField($aField);

        return $aEvent;
    }

    /**
     * Fills item list category column.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListBodyRow($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['oItem']->id_cat = $aEvent['oItem']->{$aEvent['aScope']['cat_header_alias']};
        return $aEvent;
    }

    /**
     * Dispatchs front list view.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchListView($name, array $aEvent, $handlerModId, $srcModId){
        $this->catId = (int)AMI::getSingleton('env/request')->get('catid', 0);
        return $aEvent;
    }

    /**
     * Applies filter by category if any.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListRecordset($name, array $aEvent, $handlerModId, $srcModId){
        if(
            $this->catId > 0 &&
            AMI_Registry::get('AMI/Module/Environment/Extension/ext_category/enableCatFilter', FALSE)
        ){
            $aEvent['oQuery']->addWhereDef(' AND ' . $this->prefix . '.id = ' . $this->catId);
        }
        return $aEvent;
    }

    /**
     * Adds fields validation.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTable::getValidators()
     */
    public function handleGetValidators($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['oTable']->addValidators(array('id_cat' => array('filled')));
        return $aEvent;
    }

    /**
     * Save model item handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTableItemModifier::save()
     */
    public function handleSaveModelItem($name, array $aEvent, $handlerModId, $srcModId){
        $this->aRollback = array('num_items' => 0, 'num_public_items' => 0);
        if(isset($aEvent['aData']['id_cat'])){
            $catId = $aEvent['aData']['id_cat'];
        }elseif(isset($aEvent['aData']['cat_id'])){
            $catId = $aEvent['aData']['cat_id'];
        }else{
            $catId = 0;
        }
        if(AMI::issetAndTrueOption($handlerModId, 'use_categories') && $catId){
            /**
             * @var AMI_ModTableItem
             */
            $oItem = $aEvent['oItem'];
            /**
             * @var AMI_ModTableItem
             */
            $oTable = AMI::getResourceModel($this->getCatSubmodId($handlerModId) . '/table');
            $aFields = array('id', 'num_items', 'num_public_items', 'sublink');
            if($oTable->hasField('id_page')){
                $aFields[] = 'id_page';
            }
            $oCatItem = $oTable->find($catId, $aFields);
            if($oTable->hasField('id_page')){
                $aEvent['aData']['id_page'] = $oCatItem->id_page;
                $oItem->id_page = $oCatItem->id_page;
            }
            if($aEvent['onCreate']){
                // new item
                $this->aRollback['num_items'] = 1;
                $this->aRollback['num_public_items'] = (int)!empty($oItem->public);
                $oCatItem->num_items = $oCatItem->num_items + $this->aRollback['num_items'];
                $oCatItem->num_public_items = $oCatItem->num_public_items + $this->aRollback['num_public_items'];
                AMI_Event::disableHandler('on_before_save_model_item');
                $oCatItem->save();
                AMI_Event::enableHandler('on_before_save_model_item');
            }elseif(!empty($aEvent['aOrigData']['aData'])){
                $hasPublicFlag = isset($aEvent['aOrigData']['aData']['public']);
                if(
                    isset($aEvent['aOrigData']['aData']['cat_id']) &&
                    $aEvent['aOrigData']['aData']['cat_id'] != $catId
                ){
                    // category is changed
                    $oTable = AMI::getResourceModel($this->getCatSubmodId($handlerModId) . '/table');
                    $aFields = array('id', 'num_items', 'num_public_items');
                    if($oTable->hasField('id_page')){
                        $aFields[] = 'id_page';
                    }
                    $oSourceCatItem = $oTable->find($aEvent['aOrigData']['aData']['cat_id'], $aFields);
                    $oSourceCatItem->num_items = $oSourceCatItem->num_items - 1;
                    $oCatItem->num_items = $oCatItem->num_items + 1;
                    if($hasPublicFlag && $aEvent['aOrigData']['aData']['public']){
                        $oSourceCatItem->num_public_items = $oSourceCatItem->num_public_items - 1;
                    }
                    $oCatItem->num_public_items = $oCatItem->num_public_items + (int)(bool)$oItem->public;
                    AMI_Event::disableHandler('on_before_save_model_item');
                    $oCatItem->save();
                    $oSourceCatItem->save();
                    AMI_Event::enableHandler('on_before_save_model_item');
                }elseif($hasPublicFlag && $aEvent['aOrigData']['aData']['public'] != $oItem->public){
                    // public status is changed
                    $this->aRollback['num_public_items'] = (int)((bool)$oItem->public - (bool)$aEvent['aOrigData']['aData']['public']);
                    $oCatItem->num_public_items = $oCatItem->num_public_items + $this->aRollback['num_public_items'];
                    AMI_Event::disableHandler('on_before_save_model_item');
                    $oCatItem->save();
                    AMI_Event::enableHandler('on_before_save_model_item');
                }
            }
        }
        return $aEvent;
    }

    /**
     * Delete model item handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTableItemModifier::delete()
     */
    public function handleDeleteModelItem($name, array $aEvent, $handlerModId, $srcModId){
        /**
         * @var AMI_ModTableItem
         */
        $oCatItem =
            AMI::getResourceModel($this->getCatSubmodId($handlerModId) . '/table')
            ->find(
                $aEvent['oItem']->cat_id,
                array('id', 'num_items', 'num_public_items')
            );
        $this->aRollback['num_items'] = -1;
        $this->aRollback['num_public_items'] = -(int)!empty($aEvent['oItem']->public);
        $oCatItem->num_items = $oCatItem->num_items + $this->aRollback['num_items'];
        $oCatItem->num_public_items = $oCatItem->num_public_items + $this->aRollback['num_public_items'];
        AMI_Event::disableHandler('on_before_save_model_item');
        $oCatItem->save();
        AMI_Event::enableHandler('on_before_save_model_item');
        return $aEvent;
    }

    /**
     * Handles HTML-meta title generation.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTableItemModifier::delete()
     */
    public function handleGenerateHTMLMetaTitle($name, array $aEvent, $handlerModId, $srcModId){
        /**
         * @var AMI_ModTableItem
         */
        $oCatItem =
            AMI::getResourceModel($this->getCatSubmodId($handlerModId) . '/table')
            ->find(
                $aEvent['oItem']->cat_id,
                array('id', 'header')
            );
        $aEvent['aScope']['cat_name'] = $oCatItem->header;
        return $aEvent;
    }

    /**
     * Manipulates with front components.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleBeforeInitComponents($name, array $aEvent, $handlerModId, $srcModId){
        if(AMI_Registry::get('AMI/Module/Environment/Filter/active')){
            return $aEvent;
        }

        $modId = $this->getModId();
        $itemId = (int)AMI_Registry::get('page/itemId', 0);

        /**
         * @var AMI_Mod
         */
        $oController = $aEvent['oController'];

        $oController->removeComponents(array('items', 'details'));
        $aComponents = array();
        $flag = TRUE;
        $isItemDetails = FALSE;
        $bodyType = '';

        if($this->catId != -1){
            if($itemId != 0){
                // Item details
                $flag = FALSE;
                $isItemDetails = TRUE;
                $type = 'details';
                $aComponents[$type] = array(
                    'type'    => $type,
                    'options' => AMI_Mod::INIT_ON_START
                );
                $bodyType = $type;
            }else{
                if($this->catId === 0){
                    // Cat list

                    if(
                        AMI::issetOption($modId, 'subitems_grp_by_cat') &&
                        !AMI::getOption($modId, 'subitems_grp_by_cat')
                    ){
                        // Display items from categories from pages from 'mod_cat_page_ids' option
                        $type = 'items';
                        AMI_Registry::set('AMI/Module/Environment/items/no_cat_mode', TRUE);
                    }else{
                        $type = 'cats';
                    }
                    $aComponents[$type] = array(
                        'type'    => $type,
                        'options' => AMI_Mod::INIT_ON_START
                    );
                    $bodyType = $type;
                    $flag = FALSE;
                }else{
                    if($itemId != -1){
                        // Item list
                        // 5.0:
                        // $this->SetBodyType("body_items");
                        // $this->SetBodyType('body_urgent_items');
                        $type = 'items';
                        $aComponents[$type] = array(
                            'type'    => $type,
                            'options' => AMI_Mod::INIT_ON_START
                        );
                        $bodyType = $type;
                    }else{
                        // Item details
                        // 5.0:
                        // $this->SetBodyType("body_itemD");

                        $type = 'details';
                        $aComponents[$type] = array(
                            'type'    => $type,
                            'options' => AMI_Mod::INIT_ON_START
                        );
                        $bodyType = $type;
                        $flag = FALSE;
                        $isItemDetails = TRUE;
                    }
                }
            }
            if($this->catId !== FALSE){
                if(
                    ($flag && AMI::getOption($modId, 'multicat')) ||
                    ($isItemDetails && AMI::getOption($modId, 'multicat_in_body_details'))
                ){
                    // Cat list
                    $this->addSubComponent = TRUE;
                }
            }
        }else{
            // 404 for categories
            $type = 'empty';
            $aComponents[$type] = array(
                'type'    => $type,
                'options' => AMI_Mod::INIT_ON_START
            );
            $bodyType = $type;
        }

        if($this->catId > 0){
            /**
             * AMI_ModTableItem
             */
            $aItemData = AMI_PageManager::getPageItemData();
            if(!empty($aItemData['catPublic'])){
                $header = htmlentities($aItemData['catHeader'], ENT_COMPAT, 'UTF-8');
                AMI_Registry::get('oGUI')->addGlobalVars(
                    array(
                        'cat_header'  => $header,
                        '_cat_header' => $header
                    )
                );
                $oItem =
                    AMI::getResourceModel($this->getCatSubmodId($modId) . '/table')
                    ->find($this->catId, array('*'));
                $aEvent1 = array(
                    'oItem' => $oItem
                );
                AMI_Event::fire('on_get_category_details', $aEvent1, $modId);
                unset($aEvent1);
                if(!$isItemDetails){
                    $GLOBALS["ModuleHtml"]["headers"] = AMI_PageManager::getPageMetaData($oItem->getMetaData());
                    AMI_Registry::get('oGUI')->addGlobalVars(
                        array(
                            'cat_body'  => $oItem->body
                        )
                    );
                }
            }else{
                // Category isn't published
                // 404 for categories
                $aComponents = array();
                $type = 'empty';
                $aComponents[$type] = array(
                    'type'    => $type,
                    'options' => AMI_Mod::INIT_ON_START
                );
                $bodyType = $type;
                if(is_object($this->oModController)){
                    $this->oModController->forcePageIndexing(FALSE);
                }
            }
        }

        if($bodyType){
            AMI_Registry::set('AMI/Module/Environment/bodyType', $bodyType);
        }

        if(sizeof($aComponents)){
            $oController->addComponents($aComponents);
        }

        return $aEvent;
    }

    /**
     * Adds 'cat' subcomponent to 'items' component if needed.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleAfterInitComponents($name, array $aEvent, $handlerModId, $srcModId){
        if(!$this->addSubComponent){
            return $aEvent;
        }

        // Find 'items' or 'details' component serial id
        $noComponent = TRUE;
        foreach($aEvent['aOptions'] as $serialId => $aOptions){
            if(in_array($aOptions['type'], array('items', 'details'))){
                $noComponent = FALSE;
                break;
            }
        }
        if($noComponent){
            return $aEvent;
        }

        AMI_Registry::set('AMI/Module/Environment/useCatsAsSubcomponent', TRUE);
        $aEvent['aComponents'][$serialId]->addSubComponent(AMI::getResource($this->getModId() . '/cats/controller/frn'));

        return $aEvent;
    }

    /**
     * Adds category related scope to item list or to details page.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleBeforeViewFrn($name, array $aEvent, $handlerModId, $srcModId){
        if(is_null($this->aItem)){
            $modId = $aEvent['modId'];
            AMI_Registry::push('AMI/Module/Environment/Filter/skipIdPage', TRUE);
            $this->aItem =
                AMI::getResourceModel($this->getCatSubmodId($modId) . '/table')
                ->find($this->catId, array('*'))->getData();
            AMI_Registry::pop('AMI/Module/Environment/Filter/skipIdPage');

            $oCommonView = AMI::getResource('module/common/view/frn');
            $aBrowser = $oCommonView->getBrowserData();
            $oCommonView->setModId($modId);
            $oCommonView->initByBodyType($aEvent['type'], array('cat_header', 'cat_announce', 'cat_body', 'cat_num_public_items'));
            foreach(array('header', 'announce', 'body', 'num_public_items') as $field){
                $this->aItem['cat_' . $field] = $this->aItem[$field];
                unset($this->aItem[$field]);
            }
            $oCommonView->fillEmptyDescription($this->aItem, TRUE);
            $aTmp = array();
            $oCommonView->applySimpleSetsCB($this->aItem, $aTmp);
            $offset = $aBrowser['offset'];
            $catOffset = $aBrowser['catoffset'];
            $this->aItem['front_cats_link'] = $catOffset ? 'catoffset=' . $catOffset : '';
            $this->aItem['front_items_link'] = $offset ? '&offset=' . $offset : '';
            $this->aItem['cat_link'] = $oCommonView->parseTpl('cat_link', $this->aItem);
        }
        foreach(
            array(
                'id'           => 'cat_id',
                '_cat_header'   => '_cat_header',
                '_cat_announce' => '_cat_announce',
                '_cat_body'     => '_cat_body',
                'cat_header'   => 'cat_header',
                'cat_announce' => 'cat_announce',
                'cat_body'     => 'cat_body',

                'cat_link'  => 'cat_link',
                'sublink'   => 'cat_sublink',

                'front_cats_link'  => 'front_cats_link',
                'front_items_link' => 'front_items_link',
/*
                'ext_img'       => 'ext_img',
                'ext_img_small' => 'ext_img_small',
                'ext_img_popup' => 'ext_img_popup'
*/
            ) as $src => $dest
        ){
            if(isset($this->aItem[$src])){
                $aEvent['aScope'][$dest] = $this->aItem[$src];
            }
        }

        return $aEvent;
    }

    /**
     * Rollback model item handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTableItemModifier::save()
     */
/*
    public function handleRollbackModelItem($name, array $aEvent, $handlerModId, $srcModId){
        if($this->aRollback['num_items'] || $this->aRollback['num_public_items']){
            /**
             * @var AMI_ModTableItem
             */ /*
            $oCatItem = AMI::getResourceModel($handlerModId . '_cat/table')->find($aEvent['aData']['cat_id']);
            $oCatItem->num_items = $oCatItem->num_items - $this->aRollback['num_items'];
            $oCatItem->num_public_items = $oCatItem->num_public_items - $this->aRollback['num_public_items'];
            AMI_Event::disableHandler('on_rollback_save_model_item');
            $oCatItem->save();
            AMI_Event::enableHandler('on_rollback_save_model_item');
        }
        return $aEvent;
    }
*/

    /**
     * Dispatches item adding.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @todo   Implement
     */
/*
    public function dispatchAdd($name, array $aEvent, $handlerModId, $srcModId){
        return $aEvent;
    }
*/

    /**
     * Dispatches item saving.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @todo   Implement
     */
/*
    public function dispatchSave($name, array $aEvent, $handlerModId, $srcModId){
        return $aEvent;
    }
*/

    /**#@-*/

    /**
     * Returns categories table list model.
     *
     * @return AMI_iModTableList
     */
    protected function getCatListModel(){
        $aColumns = array('id', 'header');
        if(
            AMI::issetAndTrueOption('core', 'multi_page_allowed') &&
            AMI::issetAndTrueOption($this->getModId(), 'multi_page')
        ){
            $aColumns[] = 'id_page';
        }
        return
            AMI::getResourceModel($this->resName . '/table')
            ->getList()
            ->addColumns($aColumns)
            ->addOrder('header', 'asc')
            ->load();
    }

    /**
     * Returns filter field structure.
     *
     * @return array
     */
    protected function getFilterField(){
        return
            array(
                'name'          => 'category',
                'type'          => 'select',
                'flt_default'   => '',
                'flt_condition' => '=',
                'flt_column'    => 'id_cat',
                'data'          => array(),
                'not_selected'  => $this->getNotSelectedRow(),
                'session_field' => TRUE,
                'act_as_int'    => TRUE
            );
    }

    /**
     * Returns not selected row data for drpdown select boxes.
     *
     * @return array
     */
    protected function getNotSelectedRow(){
        return array('id' => '', 'caption' => 'flt_all_categories');
    }

    /**
     * Returns categories submodule Id.
     *
     * @param  string $modId  Module Id
     * @return string
     */
    protected function getCatSubmodId($modId){
        return $modId . '_cat';
    }
}
