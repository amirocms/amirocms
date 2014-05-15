<?php
/**
 * AmiMultifeeds hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiMultifeeds
 * @version   $Id: Hyper_AmiMultifeeds_Frn.php 45064 2013-12-05 09:27:10Z Leontiev Anton $
 * @since     6.0.2
 */

/**
 * Multifeeds hypermodule front action controller.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
abstract class Hyper_AmiMultifeeds_Frn extends AMI_Module_Frn{
    /**
     * Initializes module.
     *
     * @return Hyper_AmiMultifeeds_Frn
     */
    public function init(){
        AMI_Registry::set('ami_global_limit_counter', 0);
        AMI_Registry::set('ami_global_limit_stopper', false);
        AMI_Registry::set('ami_global_limit_enable', false);

        parent::init();

        $frontLink = AMI_PageManager::getModLink(
            $this->getModId(),
            AMI_Registry::get('lang'),
            0,    // pageId
            TRUE, // suppress errors
            TRUE  // prepend lang
        );
        AMI_Registry::get('oGUI')->AddGlobalVars(array('front_link' => $frontLink));
        
        $this->initCache();

        return $this;
    }

    /**
     * Cache initialization.
     *
     * @return Hyper_AmiMultifeeds_Frn
     */
    protected function initCache(){
        $expireTime = '';
        $modId = $this->getModId();

        if(AMI::issetOption($modId, 'cache_expire_force')){
            $expireTime = AMI::getOption($modId, 'cache_expire_force');
            if($expireTime != ''){
                $expireTime = strtotime($expireTime);
            }
        }

        foreach(array_keys($this->aComponents) as $index){
            $componentExpireTime = null;
            if(is_callable(array($this->aComponents[$index], 'getCacheExpireTime'))){
                $componentExpireTime = $this->aComponents[$index]->getCacheExpireTime();
                if(is_object($GLOBALS['oCache'])){
                    if(!empty($expireTime) && !empty($componentExpireTime)){
                        $GLOBALS['oCache']->SetForceExpireTime($modId, min($expireTime, $componentExpireTime));
                    }elseif(!empty($expireTime)){
                        $GLOBALS['oCache']->SetForceExpireTime($modId, $expireTime);
                    }elseif(!empty($componentExpireTime)){
                        $GLOBALS['oCache']->SetForceExpireTime($modId, $componentExpireTime);
                    }
                }
            }
        }

        return $this;
    }
}

/**
 * Multifeeds hypermodule front items component.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
class Hyper_AmiMultifeeds_ItemsFrn extends AMI_ModItemsFrn{
}

/**
 * Multifeeds hypermodule front items component view.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
class Hyper_AmiMultifeeds_ItemsViewFrn extends AMI_ModItemsView{
    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        $aData = parent::get();

        AMI_Event::fire('on_list_view', $aData, $this->getModId());

        $setName = 'body';
        if(AMI_Registry::get('AMI/Module/Environment/Filter/active')){
            $setName = 'body_filtered';
            $oFilter = AMI_Registry::get('AMI/Module/Environment/Filter/Controller');
            $aFields = $oFilter->getFieldsAsArray();
            // Fill template data
            foreach($aFields as $field => $value){
                $aData['flt_' . $field] = $value;
            }
        }
        $aData['body'] = $this->oCommonView->parseTpl($setName, $aData);
        return $this->oTpl->parse($this->getModId(), $aData);
    }

    /**
     * Set filter.
     *
     * @return Hyper_AmiMultifeeds_StickyItemsViewFrn
     * @since      6.0.2
     */
    protected function setFilter(){
        parent::setFilter();

        $aBrowser = $this->oCommonView->getBrowserData();
        if($aBrowser['useSpecView']){
            $this->oList->addWhereDef(DB_Query::getSnippet('AND `i`.`' . $this->oModel->getFieldName('hide_in_list') . '` = 0'));
        }

        return $this;
    }
}

/**
 * Multifeeds hypermodule front details component.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
class Hyper_AmiMultifeeds_DetailsFrn extends AMI_ModComponent{
    /**
     * Initialization.
     *
     * @return AMI_ModComponentStub
     */
    public function init(){
        $this->bDispayView = true;

        $modId = $this->getModId();
        if(AMI::issetAndTrueOption($modId, 'show_body_browse')){
            $this->addSubComponent(AMI::getResource($modId.'/browse_items/controller/frn'));
        }

        return $this;
    }

    /**
     * Returns component type.
     *
     * @return string
     */
    public function getType(){
        return 'details';
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
 * Multifeeds hypermodule front details component view.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
class Hyper_AmiMultifeeds_DetailsViewFrn extends AMI_ModDetailsView{
    /**
     * Constructor.
     */
    public function __construct(){
        $modId = $this->getModId();
        $tplFileName = $modId;
        if(!AMI_Registry::get('ami_specblock_mode', false) && AMI_Registry::exists('page/tplAddon')){
            $tplFileName .= AMI_Registry::get('page/tplAddon');
        }
        $this->tplFileName = AMI_iTemplate::TPL_MOD_PATH . '/' . $tplFileName . '.tpl';
        $this->tplBlockName = $modId . '_details';
        $this->localeFileName = AMI_iTemplate::LNG_MOD_PATH . '/' . $modId . '.lng';

        parent::__construct();
    }

    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        $this->prepareFieldCallbacks();

        $aData = array();
        $aData = parent::get();
        $aData['body'] = $this->oTpl->parse($this->tplBlockName.':body_itemD', $aData);
        $aData['body'] = $this->oTpl->parse($this->tplBlockName, $aData);

        return $aData['body'];
    }

    /**
     * Allows data customization during front page building.
     *
     * @param  array &$aItemData  Item data
     * @param  array &$aData  Page data
     * @return void
     */
    protected function applyVars(array &$aItemData, array &$aData){
        $catId = (int)AMI_Registry::get('page/catId', 0);
        $aData['body'] = str_replace('##body_page_break##', '', $aData['body']);
        $aData['item_link'] =
            $this->oTpl->parse(
                $this->tplBlockName . ':' . ($catId > 0 ? 'itemD_cat_item_link' : 'itemD_item_link'),
                $aData
            );
        $aData['top_link'] = $this->parseSet('itemD', 'top_link', $aData);
        // $aData['date'] = $this->parseSet('itemD', 'date', $aItemData['date']);
        if(!empty($aItemData['author'])){
            $aData['author'] = $this->parseSet('itemD', 'author', $aItemData);
        }
        if(!empty($aItemData['source'])){
            $aData['source'] = $this->parseSet('itemD', 'source', $aItemData['source']);
        }
    }
}

/**
 * Multifeeds hypermodule front sticky items component.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
class Hyper_AmiMultifeeds_StickyItemsFrn extends Hyper_AmiMultifeeds_ItemsFrn{
    /**
     * Initialization.
     *
     * @return AMI_ModComponentStub
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
        return 'sticky_items';
    }
}

/**
 * Multifeeds hypermodule front sticky items component view.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage View
 * @since      6.0.2
 */
class Hyper_AmiMultifeeds_StickyItemsViewFrn extends AMI_ModItemsView{
    /**
     * Body type view
     *
     * @var string
     */
    protected $bodyType = 'sticky_items';

    /**
     * Constructor.
     */
    public function __construct(){
        AMI_Event::addHandler('on_list_view', array($this, AMI::actionToHandler('view')), $this->getModId());

        parent::__construct();
    }

    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        $aData = array();

        $aBrowser = $this->oCommonView->getBrowserData();
        if($aBrowser['useSpecView']){
            $aShowAt = AMI::getOption($this->getModId(), 'show_urgent_elements');
            if(!is_array($aShowAt)){
                $aShowAt = array($aShowAt);
            }
            /**
             * List browser data
             */
            if($this->oList->getActivePage($aBrowser['pageSize'], $aBrowser['listStart'])){
                // show at next pages
                if(!in_array('at_next_pages', $aShowAt)){
                    return $aData;
                }
            }else{
                // show at first page
                if(!in_array('at_first_page', $aShowAt)){
                    return $aData;
                }
            }

            $this->oCommonView->setBrowserData(
                array(
                    'pageSize'  => 0,
                    'listStart' => 0,
                    'listLimit' => 0
                )
            );
            $aData = parent::get();
        }

        return $aData;
    }

    /**
     * Dispatches view.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchView($name, array $aEvent, $handlerModId, $srcModId){
        $aData = $this->get();

        // TODO: Don't forget about old forum hack in CMS_ActModule.php.
        /*
        // hack to use urgent item list inside item list view
        if (isset($vData['urgent_item_list'])){
            $this->cms->Gui->addGlobalVars(array ('URGENT_ITEM_LIST_' . mb_strtoupper($this->moduleName) => $vData['urgent_item_list']));
        }
        */

        $aEvent['sticky_item_list'] = !empty($aData['sticky_item_list']) ? $aData['sticky_item_list'] : '';
        if(
            $aEvent['sticky_item_list'] !== '' &&
            AMI_Registry::get('AMI/Module/Environment/' . $this->getModId() . '/body_items/count', FALSE) === 0
        ){
            // Hide 'no items' message if sticky items are present
            $aEvent['item_list'] = '';
        }
        return $aEvent;
    }

    /**
     * Set filter.
     *
     * @return Hyper_AmiMultifeeds_StickyItemsViewFrn
     */
    protected function setFilter(){
        parent::setFilter();
        $this->oList->addWhereDef(DB_Query::getSnippet('AND `i`.`' . $this->oModel->getFieldName('sticky') . '` = 1'));
        return $this;
    }
}

/**
 * AmiMultifeeds hypermodule front filter component view.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage View
 * @since      6.0.2
 */
class Hyper_AmiMultifeeds_FilterFrn extends AMI_ModComponent{
    /**
     * Initialization.
     *
     * @return Hyper_AmiMultifeeds_FilterFrn
     */
    public function init(){
        $this->bDispayView = false;

        AMI::getSingleton('db')->displayQueries();

        $this->oModelItem = AMI::getResource($this->getModId() . '/filter/model/frn');
        $this->oModelItem->setModId($this->getModId());

        $aEvent = array(
           'oFilter' => $this->oModelItem
        );
        AMI_Event::fire('on_filter_init', $aEvent, $this->getModId());
        AMI_Event::addHandler('on_list_recordset', array($this, 'handleListRecordset'), $this->getModId());
        AMI_Registry::set('AMI/Module/Environment/Filter/Controller', $this);

        return $this;
    }

    /**
     * Returns component type.
     *
     * @return string
     */
    public function getType(){
        return 'filter';
    }

    /**
     * Returns component view.
     *
     * @return AMI_ViewEmpty
     */
    public function getView(){
        $this->oModelItem->setData(
            AMI::getSingleton('env/request')->getScope()
        );
        return new AMI_ViewEmpty;
    }

    /**
     * List recordset handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListRecordset($name, array $aEvent, $handlerModId, $srcModId){
        $this->oModelItem->applyFilter($aEvent);
        return $aEvent;
    }

    /**
     * Returns filter fields as array.
     *
     * @return string
     */
    public function getFieldsAsArray(){
        $res = array();
        $aFields = $this->oModelItem->getViewFields();
        foreach($aFields as $aField){
            $res[$aField['name']] = $this->oModelItem->getFieldValue($aField['name']);
        }
        return $res;
    }

    /**
     * Returns filter fields as part of url query string.
     *
     * @return string
     */
    public function getFieldsAsUrlParams(){
        $res = '';
        $aFields = $this->getFieldsAsArray();
        foreach($aFields as $field => $value){
            $res .= ('&' . $field . '=' . urlencode($value));
        }
        return $res;
    }
}

/**
 * Multifeeds hypermodule front filter component model.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Model
 * @since      6.0.2
 */
class Hyper_AmiMultifeeds_FilterModelFrn extends AMI_Filter{
    /**
     * Constructor.
     */
    public function __construct(){
        $this->addViewField(
            array(
                'name'          => 'date_from',
                'type'          => 'datefrom',
                'flt_type'      => 'date',
                'flt_default'   => AMI_Lib_Date::formatUnixTime(AMI_Lib_Date::UTIME_MIN),
                'flt_condition' => '>=',
                'flt_column'    => 'date_created',
                'validate'      => array('date','date_limits'),
                'session_field' => true
            )
        );

        $this->addViewField(
            array(
                'name'          => 'date_to',
                'type'          => 'dateto',
                'flt_type'      => 'date',
                'flt_default'   => AMI_Lib_Date::formatUnixTime(AMI_Lib_Date::UTIME_MAX),
                'flt_condition' => '<=',
                'flt_column'    => 'date_created',
                'validate'      => array('date','date_limits'),
                'session_field' => true
            )
        );
    }

    /**
     * Handle id_page filter custom logic.
     *
     * @param  string $field  Field name
     * @param  array  $aData  Filter data
     * @return array
     */
    protected function processFieldData($field, array $aData){
        return $aData;
    }
}

/**
 * Multifeeds hypermodule front specblock component.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
class Hyper_AmiMultifeeds_SpecblockFrn extends AMI_ModList{
    /**
     * Initialization.
     *
     * @return AMI_ModComponentStub
     */
    public function init(){
        AMI_Registry::set('AMI/Module/Environment/Filter/active', false);
        AMI_Registry::push('AMI/Module/Environment/Filter/skipIdPage', TRUE);
        AMI_Registry::push('AMI/Module/Environment/Extension/ext_category/enableCatFilter', FALSE);

        parent::init();

        $this->bDispayView = true;
        $modId = $this->getModId();

        return $this;
    }

    /**
     * Returns component type.
     *
     * @return string
     */
    public function getType(){
        return 'specblock';
    }
}

/**
 * Multifeeds hypermodule front specblock component view.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage View
 * @since      6.0.2
 */
class Hyper_AmiMultifeeds_SpecblockViewFrn extends AMI_ModItemsView{
    /**
     * Body type view
     *
     * @var string
     */
    protected $bodyType = 'small';

    /**
     * Specblock mode
     *
     * @var string
     */
    protected $specblockMode = 'no_cats';

    /**
     * Array of specblock data
     *
     * @var array
     */
    protected $aSpecblockData = array();

    /**
     * Array of categories IDs
     *
     * @var array
     */
    protected $aCatIds = array();

    /**
     * Current category id
     *
     * @var int
     */
    protected $curCatId = 0;

    /**
     * Array of simple set fields
     *
     * @var array
     */
    protected $aSimpleSetFields = array('fdate', 'ftime', 'announce', 'header');

    /**
     * Flag specifying to restore original merged template block
     *
     * @var bool
     */
    protected $restoreTplBlock = FALSE;

    /**
     * Specblock announce truncate args
     *
     * @var array
     */
    protected $aTruncateArgs;

    /**
     * Add CALC_FOUND_ROWS to select query
     *
     * @var bool
     */
    protected $bCalcFoundRows = false;

    /**
     * Constructor.
     */
    public function __construct(){
        $modId = $this->getModId();

        parent::__construct();

        if(AMI::issetOption($modId, 'spec_block_template')){
            $specBlockTemplate = AMI::getOption($modId, 'spec_block_template');
            if($this->oTpl->isValidFile($specBlockTemplate)){
                $this->restoreTplBlock = TRUE;
                AMI_Registry::get('oGUI')->copyBlock($modId, $modId . '_backup');
                $this->oTpl->mergeBlock($modId, $specBlockTemplate);
            }
        }
    }

    /**
     * Initialize.
     *
     * @see    AMI_View::init()
     * @return AMI_ModItemsView
     */
    public function init(){
        parent::init();

        $this->addColumn('id_page');

        $modId = $this->getModId();
        if(
            !AMI::issetAndTrueOption($modId, 'announce_mode_full') &&
            AMI::issetOption($modId, 'announce_small_length')
        ){
            $this->aTruncateArgs = array(
                AMI::getOption($modId, 'announce_small_length'),
                FALSE,
                AMI::getOption('core', 'strip_strings_by_words')
            );
        }

        return $this;
    }

    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        AMI_Registry::set('ami_global_limit_enable', true);
        $modId = $this->getModId();
        $catModId = $modId . '_cat';
        $useCats = AMI::isModInstalled($catModId) && AMI::issetAndTrueOption($modId, 'use_categories');
        $this->aSpecblockData['prevCatId'] = -1;

        $this->specblockMode =
            $useCats && AMI::isModInstalled($catModId) && AMI::getOption($modId, 'small_grp_by_cat')
                ? 'cats_num'
                : 'no_cats';

        $aBrowserData = $this->oCommonView->getBrowserData();
        if(AMI::issetOption($modId, 'small_items_sort_col')){
            $aBrowserData['orderColumn'] = AMI::getOption($modId, 'small_items_sort_col');
        }elseif(AMI::issetOption($modId, 'page_sort_col_small')){
            $aBrowserData['orderColumn'] = AMI::getOption($modId, 'page_sort_col_small');
        }
        if(AMI::issetOption($modId, 'small_items_sort_dim')){
            $aBrowserData['orderDirection'] = AMI::getOption($modId, 'small_items_sort_dim');
        }elseif(AMI::issetOption($modId, 'page_sort_dim_small')){
            $aBrowserData['orderDirection'] = AMI::getOption($modId, 'page_sort_dim_small');
        }

        // set specblock limit param for 'no_cats' mode
        if($this->specblockMode == 'no_cats'){
            AMI_Registry::push('AMI/Module/Environment/items/spec_no_cat_mode', TRUE);
            if(AMI::issetOption($modId, 'small_number_items')){
                $aBrowserData['listLimit'] = AMI::getOption($modId, 'small_number_items');
            }elseif(AMI::issetOption($modId, 'page_size_small')){
                $aBrowserData['listLimit'] = AMI::getOption($modId, 'page_size_small');
            }
            if($aBrowserData['listLimit'] > AMI::getOption($modId, 'spec_total_items_limit')){
                $aBrowserData['listLimit'] = AMI::getOption($modId, 'spec_total_items_limit');
                $sbId = AMI_Registry::get('ami_specblock_id', FALSE);
                AMI_Registry::push('disable_error_mail', TRUE);
                trigger_error(
                    'Subitems count limit excedeed (spec_total_items_limit) in specblock ' . $sbId,
                    E_USER_WARNING
                );
                AMI_Registry::pop('disable_error_mail');
            }
        }
        $this->oCommonView->setBrowserData($aBrowserData);

        // get specblock pages id's
        $option = $useCats ? 'spec_cat_id_pages' : 'spec_id_pages';
        $pageIdPfx = $useCats ? 'cat.' : 'i.';
        $aSpecblockPagesId = array();
        if(AMI::issetAndTrueOption('core', 'multi_page_allowed') && AMI::issetAndTrueOption($modId, 'multi_page') && AMI::issetOption($modId, $option)){
            $aSpecIdPages = AMI::getOption($modId, $option);
            if(!is_array($aSpecIdPages)){
                $aSpecIdPages = array($aSpecIdPages);
            }
            if(!in_array(-1, $aSpecIdPages)){
                $aSpecblockPagesId = $aSpecIdPages;
            }
        }

        // get specblock categories data
        $aShowAt = AMI::getOption($catModId, 'show_urgent_elements');
        if(!is_array($aShowAt)){
            $aShowAt = array($aShowAt);
        }
        $isShowUrgentCats = in_array('at_spec_block', $aShowAt);
        if($this->specblockMode == 'cats_num'){
            $oCatsModel = AMI::getResourceModel($catModId . '/table');

            $filter = '';
            if(sizeof($aSpecblockPagesId)){
                $filter .= ' AND (i.id_page IN (' . implode(',', $aSpecblockPagesId) . '))';
            }
            if(AMI::issetAndTrueProperty($modId, 'use_special_list_view')){
                $filter .= " AND " . (!$isShowUrgentCats ? $oCatsModel->getFieldName('hide_in_list') . ' = 0' : '(' . $oCatsModel->getFieldName('hide_in_list') . ' = 0 OR ' . ' ' . $oCatsModel->getFieldName('sticky'). ' = 1)');
            }
            $oCatsList = $oCatsModel->getList();
            $oCatsList->addColumns(array('id', 'sublink', 'header', 'announce', 'sticky'));
            if($isShowUrgentCats){
                $oCatsList->addOrder($oCatsModel->getFieldName('sticky'), 'DESC');
            }
            $oCatsList->addOrder(AMI::getOption($modId, 'small_categories_sort_col'), AMI::getOption($modId, 'small_categories_sort_dim'));
            if(!empty($filter)){
                $oCatsList->addWhereDef(DB_Query::getSnippet($filter));
            }
            $oCatsList->load();
            if($oCatsList->count() > 0){
                foreach($oCatsList as $oCatItem){
                    $aCatItem = $oCatItem->getData();
                    $aCatItemData = array(
                        'cat_id' => $aCatItem['id'],
                        'cat_sublink' => $aCatItem['sublink'],
                        'cat_name' => $aCatItem['header'],
                        'cat_announce' => $aCatItem['announce'],
                        'sticky' => $aCatItem['sticky']
                    );
                    $this->aCatIds[] = $aCatItemData['cat_id'];
                    $this->aSpecblockData['aCats'][$aCatItemData['cat_id']] = $aCatItemData;
                }
            }
        }

        $aShowAt = AMI::getOption($modId, 'show_urgent_elements');
        if(!is_array($aShowAt)){
            $aShowAt = array($aShowAt);
        }
        $isShowUrgentItems = in_array('at_spec_block', $aShowAt);

        // setup specblock list object
        switch($this->specblockMode){
            case 'no_cats':
                $snippet = '';
                if(sizeof($aSpecblockPagesId)){
                    $snippet .= ' AND (' . $pageIdPfx . '`id_page` IN (' . implode(',', $aSpecblockPagesId) . '))';
                }
                if($aBrowserData['useSpecView']){
                    $snippet .= ' AND ' . (!$isShowUrgentItems ? '`i`.`' . $this->oModel->getFieldName('hide_in_list') . '` = 0 ' : '(`i`.`' . $this->oModel->getFieldName('hide_in_list'). '` = 0 OR `i`.`' . $this->oModel->getFieldName('sticky') . '` = 1)');
                    if($useCats){
                        $oCatsModel = AMI::getResourceModel($catModId . '/table');
                        $snippet .= " AND " . (!$isShowUrgentCats ? '`cat`.' . $oCatsModel->getFieldName('hide_in_list') . ' = 0' : '(' . '`cat`.' . $oCatsModel->getFieldName('hide_in_list') . ' = 0 OR ' . ' `cat`.' . $oCatsModel->getFieldName('sticky'). ' = 1)');
                    }
                }
                if($isShowUrgentItems){
                    $this->oList->addOrder($this->oModel->getFieldName('sticky'), 'DESC');
                }
                $this->oList->addWhereDef(DB_Query::getSnippet($snippet));
                break;
            case 'cats_num':
                $aItems = array();
                $this->isPaginationEnabled = false;
                if(sizeof($this->aCatIds) > 0){
                    $oItemsList = $this->oModel->getList();
                    $aItemsColumns = $this->getColumns();
                    $oItemsList->addColumns($aItemsColumns);
                    if($isShowUrgentItems){
                        $oItemsList->addOrder($this->oModel->getFieldName('sticky'), 'DESC');
                    }

                    $oItemsList
                        ->addOrder($aBrowserData['orderColumn'], $aBrowserData['orderDirection'])
                        ->setLimitParameters(0, AMI::getOption($modId, 'small_number_items'));

                    $commonFilter = ' AND `i`.`lang` = %s AND `i`.`public` = 1 AND `i`.`' . $this->oModel->getFieldName('date_created') . '` <= NOW()';
                    if(AMI::issetAndTrueProperty($modId, 'use_special_list_view')){
                        $commonFilter .= " AND " . (!$isShowUrgentItems ? '`i`.`' . $this->oModel->getFieldName('hide_in_list') . '` = 0' : '(' . '`i`.`' . $this->oModel->getFieldName('hide_in_list') . '` = 0 OR ' . ' `i`.`' . $this->oModel->getFieldName('sticky') . '` = 1)');
                    }
                    // get specblock categories items
                    foreach($this->aCatIds as $catId){
                        if(AMI_Registry::get('ami_global_limit_enable', false)){
                            if(AMI_Registry::get('ami_global_limit_stopper', false)){
                                break;
                            }
                        }

                        $this->curCatId = $catId;
                        $filter = $commonFilter . ' AND `i`.`' . 'id_cat' . '` = ' . $catId;
                        $oItemsList->setWhereDef(DB_Query::getSnippet($filter)->q(AMI_Registry::get('lang_data')));
                        // AMI_Registry::set('AMI/Module/Environment/Extension/ext_category/enableCatFilter', FALSE);
                        $oItemsList->load();
                        // AMI_Registry::delete('AMI/Module/Environment/Extension/ext_category/enableCatFilter');

                        if($oItemsList->count() > 0){
                            foreach($oItemsList as $oItem){
                                if(AMI_Registry::get('ami_global_limit_enable', false)){
                                    $counter = (int)AMI_Registry::get('ami_global_limit_counter', 0) + 1;
                                    AMI_Registry::set('ami_global_limit_counter', $counter);
                                    if($counter > AMI::getOption($modId, 'spec_total_items_limit')){
                                        AMI_Registry::set('ami_global_limit_stopper', true);
                                        $sbId = AMI_Registry::get('ami_specblock_id', FALSE);
                                        AMI_Registry::push('disable_error_mail', TRUE);
                                        trigger_error(
                                            'Subitems count limit excedeed (spec_total_items_limit) in ' .
                                            ($sbId ? 'specblock ' . $sbId : 'module ' . $modId),
                                            E_USER_WARNING
                                        );
                                        AMI_Registry::pop('disable_error_mail');
                                    }
                                    if(AMI_Registry::get('ami_global_limit_stopper', false)){
                                        break;
                                    }
                                }

                                $aItem = $oItem->getData();
                                $aItem[$this->oModel->getFieldName('date_created')] = strtotime($aItem['date_created']);
                                $aItems[] = $aItem + $this->aSpecblockData['aCats'][$aItem['cat_id']];
                            }
                        }
                    }
                }
                AMI_Registry::set('ami_global_limit_enable', false);

                // create iterator object for loading specblock items

                $specblockItemsIterator = AMI::getResourceModel('specblock_array_iterator/table', array($aItems));
                $specblockItemsList = AMI::getResourceModel('specblock_array_iterator/table/model/list', array($specblockItemsIterator));
                $aBrowserData['listLimit'] = sizeof($aItems);
                $this->oCommonView->setBrowserData($aBrowserData);

                $this->oList = $specblockItemsIterator->getList();

                break;
        }

        // get specblock data
        $aBrowser = $this->oCommonView->getBrowserData();
        $aBrowser['listStart'] = 0;
        $this->oCommonView->setBrowserData($aBrowser);

        $aData = parent::get();

        AMI_Registry::pop('AMI/Module/Environment/Filter/skipIdPage');
        AMI_Registry::pop('AMI/Module/Environment/Extension/ext_category/enableCatFilter');

        $setName = 'body';
        $result = $this->oCommonView->parseTpl($setName, $aData);
        if($this->restoreTplBlock){
            AMI_Registry::get('oGUI')->copyBlock($modId . '_backup', $modId);
        }
        AMI_Registry::pop('AMI/Module/Environment/items/spec_no_cat_mode');
        return $result;
    }

    /**
     * Loads list model.
     *
     * @param  array $aColumns  Columns
     * @return void
     */
    protected function loadModel(array $aColumns){
        if($this->specblockMode == 'no_cats'){
            parent::loadModel($aColumns);
        }else{
            $aBrowser = $this->oCommonView->getBrowserData();
            $this->oList
                ->addColumns($aColumns)
                // ->addOrder($aBrowser['orderColumn'], $aBrowser['orderDirection'])
                ->setLimitParameters(0, $aBrowser['listLimit']);

            $this->setFilter();

            // AMI_Registry::set('AMI/Module/Environment/Extension/ext_category/enableCatFilter', FALSE);
            $this->oList->load();
            // AMI_Registry::delete('AMI/Module/Environment/Extension/ext_category/enableCatFilter');
        }
    }

    /**
     * Set filter.
     *
     * @return Hyper_AmiMultifeeds_StickyItemsViewFrn
     * @since      6.0.2
     */
    protected function setFilter(){
        parent::setFilter();

        return $this;
    }

    /**
     * Fill the field callbacks.
     *
     * @return void
     */
    protected function prepareFieldCallbacks(){
        parent::prepareFieldCallbacks();

        if($this->specblockMode != 'no_cats'){
            $this->oCommonView->setFieldCallback(
                'cat_details',
                array(
                    'object' => $this,
                    'method' => 'getCatDetailsCB'
                )
            );
        }

        $this->oCommonView->setFieldCallback(
            'details',
            array(
                'object' => $this,
                'method' => 'getDetailsCB'
            )
        );
    }

    /**
     * Fill the category details.
     *
     * @param  array &$aItem  Item data
     * @param  array &$aData  List data
     * @return void
     */
    public function getCatDetailsCB(array &$aItem, array &$aData){
        $aData['front_link'] = AMI_PageManager::getModLink($this->getModId(), AMI_Registry::get('lang_data'), $aItem['id_page'], FALSE, TRUE);
        if($this->aSpecblockData['prevCatId'] != $aItem['cat_id']){
            $this->aSpecblockData['prevCatId'] = $aItem['cat_id'];
            $aItem['front_link'] = $aData['front_link'];
            $aNavData = array(
                'modId' => $this->getModId()
            ) + $this->oCommonView->getFrontScope();
            $aNavData['catid'] = $aItem['catid'];
            $aNavData['catid_sublink'] = isset($aItem['catid_sublink']) ? $aItem['catid_sublink'] : null;
            $aCatNavData = AMI_PageManager::applyNavData($aNavData);
            $aItem['cat_nav_data'] = $aCatNavData['nav_data'];
            $aItem['cat_detail'] = $this->parseSet('small', 'cat_detail', $aItem);
        }else{
            $aItem['cat_detail'] = '';
        }
    }

    /**
     * Fill the item details.
     *
     * @param  array &$aItem  Item data
     * @param  array &$aData  List data
     * @return void
     */
    public function getDetailsCB(array &$aItem, array &$aData){
        $modId = $this->getModId();

        if(AMI::issetOption($modId, 'header_small_length')){
            $aItem['header'] =
                AMI_Lib_String::truncate(
                    $aItem['header'],
                    AMI::getOption($modId, 'header_small_length'),
                    FALSE,
                    AMI::getOption('core', 'strip_strings_by_words')
                );
        }

        $aData['front_link'] = AMI_PageManager::getModLink($modId, AMI_Registry::get('lang_data'), $aItem['id_page'], FALSE, TRUE);
        if(AMI::issetAndTrueOption($modId, 'announce_mode_full')){
            $aItem['announce'] = AMI_registry::get('oGUI')->MYnl2br($aItem['announce']);
        }
        if(AMI::issetOption($modId, 'announce_mode_full')){
            $isAnnounceModeFull = AMI::getOption($modId, 'announce_mode_full');
            if(!$isAnnounceModeFull || ($isAnnounceModeFull && !empty($aItem['body']))){
                $aItem['details_link'] = '1';
            }
        }
        if($this->aTruncateArgs){
            $aItem['aTruncateModOptions'] = $this->aTruncateArgs;
        }
        $aItem['date'] = $aItem['date_created'];
        $aItem['date'] = $this->parseSet('small', 'date', $aItem);
        $aItem['front_link'] = $aData['front_link'];
        $aItem['header'] = $this->parseSet('small', 'header', $aItem);
        $aItem['more'] = $this->parseSet('small', 'more', $aItem);
    }
}

/**
 * Multifeeds hypermodule front browse items component.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
class Hyper_AmiMultifeeds_BrowseItemsFrn extends Hyper_AmiMultifeeds_ItemsFrn{
    /**
     * Initialization.
     *
     * @return AMI_ModComponentStub
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
        return 'browse_items';
    }
}

/**
 * Multifeeds hypermodule front browse items component view.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage View
 * @since      6.0.2
 */
class Hyper_AmiMultifeeds_BrowseItemsViewFrn extends AMI_ModItemsView{
    /**
     * Body type view
     *
     * @var string
     */
    protected $bodyType = 'browse_items';

    /**
     * Constructor.
     */
    public function __construct(){
        AMI_Event::addHandler('on_item_details', array($this, AMI::actionToHandler('view')), $this->getModId());

        parent::__construct();
    }

    /**
     * Fill the field callbacks.
     *
     * @return void
     */
    protected function prepareFieldCallbacks(){
        parent::prepareFieldCallbacks();

        $this->oCommonView->setFieldCallback(
            'prev_next_links',
            array(
                'object' => $this->oCommonView,
                'method' => 'getPrevNextLinksCB'
            )
        );
    }

    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        $aData = array();
        $modId = $this->getModId();
        $tapePosition = AMI::issetOption($modId, 'browse_active_item_position') ? (int)AMI::getOption($modId, 'browse_active_item_position') : 0;

        $aBrowser = $this->oCommonView->getBrowserData();
        $this->oCommonView->setBrowserData(
            array(
                'mode'         => 'tape',
                'tapePosition' => $tapePosition,
                'pageSize'     => AMI::getOption($modId, 'body_browse_page_size'),
                'listStart'    => 0,
                'listLimit'    => 0
            )
        );
        $aData = parent::get();

        return $aData;
    }

    /**
     * Dispatches view.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchView($name, array $aEvent, $handlerModId, $srcModId){
        $aData = $this->get();

        $aPrevNextData = array(
            'prev_nav_data', 'next_nav_data', 'current_id', 'previos_link', 'next_link',
            'prev_id', 'next_id', 'prev_sublink', 'next_sublink', 'prev_next_ready'
        );
        foreach($aPrevNextData as $param){
            $aEvent['aData'][$param] = empty($aData[$param]) ? '' : $aData[$param];
        }

        $aEvent['aData']['browse_item_list'] = !empty($aData['browse_item_list']) ? $aData['browse_item_list'] : '';

        return $aEvent;
    }
}

/**
 * Multifeeds hypermodule front category details component.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
class Hyper_AmiMultifeeds_CatDetailsFrn extends AMI_ModComponent{
    /**
     * Initialization.
     *
     * @return this
     */
    public function init(){
        $this->bDispayView = true;

        /*
        $modId = $this->getModId();
        if(AMI::issetAndTrueOption($modId, 'show_body_browse')){
            $this->addSubComponent(AMI::getResource($modId.'/browse_items/controller/frn'));
        }
        */

        return $this;
    }

    /**
     * Returns component type.
     *
     * @return string
     */
    public function getType(){
        return 'cat_details';
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
 * Multifeeds hypermodule front category details component view.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
class Hyper_AmiMultifeeds_CatDetailsViewFrn extends AMI_ModDetailsView{
    /**
     * Body type view
     *
     * @var string
     */
    protected $bodyType = 'cat_details';

    /**
     * Constructor.
     */
    public function __construct(){
        $modId = $this->getModId();
        $this->tplFileName = AMI_iTemplate::TPL_MOD_PATH . '/' . $modId . '.tpl';
        $this->tplBlockName = $modId . '_details';
        $this->localeFileName = AMI_iTemplate::LNG_MOD_PATH . '/' . $modId . '.lng';

        parent::__construct();
    }

    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        $modId = $this->getModId();
        $aScope = $this->getScope('cat_details') + $this->oCommonView->getFrontScope();
        $oItem = AMI::getResourceModel($modId . '_cat/table')->find(AMI_Registry::get('page/catId'));
        $aItem = $oItem->getData();
        $aItem += $this->oCommonView->getFrontScope() + array('modId' => $modId);
        $this->oCommonView->processFields($aItem, $aScope);
        $aScope += $aItem;
        $aScope['body'] = $this->oTpl->parse($this->tplBlockName . ':body_catD', $aScope);
        $aScope['body'] = $this->oTpl->parse($this->tplBlockName, $aScope);

        return $aScope['body'];
    }
}
