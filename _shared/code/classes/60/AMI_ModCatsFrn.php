<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModCatsFrn.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.14.8
 */

/**
 * Module front cats body type action controller.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.14.8
 */
abstract class AMI_ModCatsFrn extends AMI_ModList{
    /**
     * Calculated cache expiration time
     *
     * @var int
     */
    protected static $cacheExpireTime;

    /**
     * Category ids to collect subitems
     *
     * @var array
     */
    protected $aCatIds = array();

    /**
     * Subitems list model
     *
     * @var AMI_ModTableList
     */
    protected $oSubitems;

    /**
     * Initialization.
     *
     * @return AMI_ModCatsFrn
     */
    public function init(){
        parent::init();

        $this->addSubComponent(AMI::getResource('list/pagination'));
        $this->bDispayView = TRUE;
        $modId = $this->getModId();
        // Check for sticky categories
        if(AMI::issetAndTrueProperty($modId . '_cat', 'use_special_list_view')){
            $aShowAt = AMI::getOption($modId . '_cat', 'show_urgent_elements');
            if(is_array($aShowAt) && (in_array('at_first_page', $aShowAt) || in_array('at_next_pages', $aShowAt))){
                $this->addSubComponent(AMI::getResource($modId . '/sticky_cats/controller/frn'));
            }
        }
        // Check for grouping subitems by categories
        if(
            (int)AMI::getOption($modId, 'show_subitems') >= 0 &&
            (
                AMI::issetOption($modId, 'subitems_grp_by_cat')
                    ? AMI::getOption($modId, 'subitems_grp_by_cat')
                    : TRUE
            )
        ){
            AMI_Event::addHandler('on_list_recordset_loaded', array($this, 'handleListRecordsetLoaded'), $modId . '_cat');
            AMI_Event::addHandler('on_list_body_{id}', array($this, 'handleSubitems'), $modId);
        }

        return $this;
    }

    /**
     * Returns component type.
     *
     * @return string
     */
    public function getType(){
        return 'cats';
    }

    /**
     * Handles list recordset.
     *
     * Collects all items ids.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListRecordsetLoaded($name, array $aEvent, $handlerModId, $srcModId){
        foreach($aEvent['oList'] as $oItem){
            if($oItem->num_public_items){
                $this->aCatIds[] = $oItem->id;
            }
        }
        $aEvent['oList']->rewind();
        return $aEvent;
    }

    /**
     * Prepare subitems.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::get()
     */
    public function handleSubitems($name, array $aEvent, $handlerModId, $srcModId){
        // If global limit is reached no subitems will be printed
        if(AMI_Registry::get('ami_global_limit_stopper', FALSE)){
            return $aEvent;
        }
        if(sizeof($this->aCatIds)){
            $modId = $this->getModId();
            $catId = $aEvent['aScope']['list_col_value'];
            $subitems = AMI::getOption($modId, 'show_subitems');
            // Backup items sticky related option
            if(AMI::issetAndTrueProperty($modId, 'use_special_list_view')){
                $aShowAt = AMI::getOption($modId, 'show_urgent_elements');
                AMI::setOption($modId, 'show_urgent_elements', array());
            }
            if($subitems == 0){
                $this->displayAllSubitems($aEvent);
            }elseif(in_array($catId, $this->aCatIds)){
                $this->displayLimitedSubitems($aEvent, $subitems);
            }
            // Restore items sticky related option
            if(isset($aShowAt)){
                AMI::setOption($modId, 'show_urgent_elements', $aShowAt);
            }
        }

        return $aEvent;
    }

    /**
     * Displays all subitems (subitems per each category).
     *
     * @param  array &$aEvent  Event
     * @return void
     */
    protected function displayAllSubitems(array &$aEvent){
        $modId = $this->getModId();
        $catId = $aEvent['aScope']['list_col_value'];

        // Initialize subitems model if not initialized yet
        if(empty($this->oSubitems)){
            // Disable ext_category handler thet added id_cat condition
            AMI_Registry::set('AMI/Module/Environment/Extension/ext_category/enableCatFilter', FALSE);
            AMI_Registry::set('AMI/Module/Environment/Filter/skipIdPage', TRUE);
            /**
             * @var AMI_ModTable
             */
            $oTable = AMI::getResourceModel($modId . '/table');
            /**
             * @var AMI_ModTableList
             */
            $this->oSubitems = $oTable->getList();
            // Filter by hidden/sticky items
            $useSpecListView =  AMI::issetAndTrueProperty($modId, 'use_special_list_view');
            if($useSpecListView){
                $this->oSubitems->addWhereDef(
                    'AND (i.`' . $oTable->getFieldName('hide_in_list') . '` = 0 ' .
                    'OR i.`' . $oTable->getFieldName('sticky') . '` = 1)'
                );
            }
            // Filter by categories
            $this->oSubitems->addWhereDef(
                DB_Query::getSnippet("AND i.`id_cat` IN (%s)")
                ->implode($this->aCatIds)
            );
            // Order list
            $this->oSubitems->addOrder('cat.id', 'ASC');
            // Order list by sticky elements
            if($useSpecListView){
                $this->oSubitems->addOrder($oTable->getFieldName('sticky'), 'DESC');
            }
            // Order list by module option
            $this->oSubitems->addOrder(
                'i.' . AMI::getOption($modId, 'front_subitem_sort_col'),
                AMI::getOption($modId, 'front_subitem_sort_dim')
            );

            // AMI::getSingleton('db')->displayQueries(TRUE);
            // Load list
            $oView = AMI::getResource($modId . '/subitems/view/frn');
            $oView->init();
            $this->oSubitems->addColumns($oView->getColumns());
            $this->oSubitems->addCalcFoundRows();
            $this->oSubitems->load();
            // Enable back ext_category handler thet added id_cat condition
            AMI_Registry::delete('AMI/Module/Environment/Extension/ext_category/enableCatFilter');
            AMI_Registry::set('AMI/Module/Environment/Filter/skipIdPage', FALSE);
            // AMI::getSingleton('db')->displayQueries(FALSE);
        }

        AMI_Event::disableHandler('on_list_body_{id}');
        // Backup number of columns
        $cols = AMI::issetOption($modId, 'cols') ? AMI::getOption($modId, 'cols') : NULL;
        AMI::setOption($modId, 'cols', AMI::getOption($modId, 'subitems_cols'));

        // Get list content
        $oView = AMI::getResource($modId . '/subitems/view/frn');
        $this->oSubitems->rewind();
        $oView->setModel($this->oSubitems);
        $oView->setParameters(
            array(
                'catId'          => $catId,
                'splitterPeriod' => AMI::getOption($modId, 'subitems_splitter_period'),
                'limit'          => AMI::getOption($modId, 'show_subitems')
            )
        );
        $oView->init();
        $aContent = $oView->get();
        $aEvent['aData']['item_list'] = $aContent['subitem_list'];
        AMI_Event::enableHandler('on_list_body_{id}');

        // Restore number of columns
        if(!is_null($cols)){
            AMI::setOption($modId, 'cols', $cols);
        }
    }

    /**
     * Displays limited subitems per each category.
     *
     * @param  array &$aEvent   Event
     * @param  int   $subitems  Subitems limit
     * @return void
     */
    protected function displayLimitedSubitems(array &$aEvent, $subitems){
        $modId = $this->getModId();
        $catId = $aEvent['aScope']['list_col_value'];
        // Disable ext_category handler thet added id_cat condition
        AMI_Registry::set('AMI/Module/Environment/Filter/skipIdPage', TRUE);
        AMI_Registry::set('AMI/Module/Environment/Extension/ext_category/enableCatFilter', FALSE);
        /**
         * @var AMI_ModTable
         */
        $oTable = AMI::getResourceModel($modId . '/table');
        /**
         * @var AMI_ModTableList
         */
        $oList = $oTable->getList();
        // Filter by hidden/sticky items
        $useSpecListView =  AMI::issetAndTrueProperty($modId, 'use_special_list_view');
        if($useSpecListView){
            $oList->addWhereDef(
                'AND (i.`' . $oTable->getFieldName('hide_in_list') . '` = 0 ' .
                'OR i.`' . $oTable->getFieldName('sticky') . '` = 1)'
            );
        }
        // Filter by categories
        $oList->addWhereDef("AND i.`id_cat` = " . $catId);
        // Order list by sticky elements
        if($useSpecListView){
            $oList->addOrder($oTable->getFieldName('sticky'), 'DESC');
        }
        // Order list by module option
        $oList->addOrder(
            'i.' . AMI::getOption($modId, 'front_subitem_sort_col'),
            AMI::getOption($modId, 'front_subitem_sort_dim')
        );
        // Limit list by max count
        $oList->setLimitParameters(0, $subitems);
        // Backup number of columns
        $cols = AMI::issetOption($modId, 'cols') ? AMI::getOption($modId, 'cols') : NULL;
        AMI::setOption($modId, 'cols', AMI::getOption($modId, 'subitems_cols'));

        // AMI::getSingleton('db')->displayQueries(TRUE);
        // Load list
        $oView = AMI::getResource($modId . '/subitems/view/frn');
        $oView->setParameters(
            array(
                'catId'          => $catId,
                'splitterPeriod' => AMI::getOption($modId, 'subitems_splitter_period'),
                'limit'          => AMI::getOption($modId, 'show_subitems')
            )
        );
        $oView->init();
        $oList->addColumns($oView->getColumns());
        $oList->addCalcFoundRows();
        $oList->load();
        // Enable back ext_category handler thet added id_cat condition
        AMI_Registry::delete('AMI/Module/Environment/Extension/ext_category/enableCatFilter');
        AMI_Registry::set('AMI/Module/Environment/Filter/skipIdPage', FALSE);
        // AMI::getSingleton('db')->displayQueries(FALSE);
        AMI_Event::disableHandler('on_list_body_{id}');
        // Get list content
        $oView->setModel($oList);
        $oView->init();
        $aContent = $oView->get();
        $aEvent['aData']['item_list'] = $aContent['subitem_list'];
        AMI_Event::enableHandler('on_list_body_{id}');

        // Restore number of columns
        if(!is_null($cols)){
            AMI::setOption($modId, 'cols', $cols);
        }
    }

    /**
     * Initializes model.
     *
     * @return AMI_ModTable
     */
    protected function initModel(){
        // we need to override model
        return AMI::getResourceModel($this->getModId() . '_cat/table');
    }

    /**
     * Returns component expiration time.
     *
     * @return int
     */
    public function getCacheExpireTime(){
        if(is_null(self::$cacheExpireTime)){
            $modId = $this->getModId();
            // Backup and set some options for items module
            $aOptions = array(
                'extensions'     => array(),
                'use_categories' => FALSE,
            );
            $aOptionsBackup = array();
            foreach($aOptions as $name => $value){
                if(AMI::issetOption($modId, $name)){
                    $aOptionsBackup[$name] = AMI::getOption($modId, $name);
                    AMI::setOption($modId, $name, $value);
                }
            }
            // Load  time from items module
            $oItemsController = AMI::getResource($modId . '/items/controller/frn');
            self::$cacheExpireTime = $oItemsController->getCacheExpireTime();
            // Restore options for items module
            foreach($aOptionsBackup as $name => $value){
                AMI::setOption($modId, $name, $value);
            }
        }
        return self::$cacheExpireTime;
    }
}

/**
 * Module front cats body type view.
 *
 * @package    ModuleComponent
 * @subpackage View
 * @since      5.14.8
 */
abstract class AMI_ModCatsView extends AMI_ModListView{
    /**
     * Body type
     *
     * @var string
     */
    protected $bodyType = 'cats';

    /**
     * Module table list model
     *
     * @var AMI_ModTableList
     */
    protected $oList;

    /**
     * Template engine object
     *
     * @var AMI_Template
     */
    protected $oTpl = null;

    /**
     * Array of simple set fields
     *
     * @var array
     */
    protected $aSimpleSetFields = array('announce', 'body', 'num_public_items');

    /**
     * Special list view (public direct link/sticky) is used
     *
     * @var bool
     */
    protected $useSpecialListView = FALSE;

    /**
     * The 'on_cat_list_view' event will be fired
     *
     * @var bool
     */
    protected $fireOnCatListView = TRUE;

    /**
     * Module common front view
     *
     * @var AMI_ModCommonViewFrn
     */
    protected $oCommonView;

    /**
     * Constructor.
     */
    public function  __construct(){
        $modId = $this->getModId();

        $tplFileName = $modId;
        // Append template addon if set
        if(
            !AMI_Registry::get('ami_specblock_mode', false) &&
            AMI_Registry::exists('page/tplAddon')
        ){
            $tplFileName .= AMI_Registry::get('page/tplAddon');
        }
        $this->tplFileName    = AMI_iTemplate::TPL_MOD_PATH . '/' . $tplFileName . '.tpl';
        $this->tplBlockName   = $modId;
        $this->localeFileName = AMI_iTemplate::LNG_MOD_PATH . '/' . $modId . '.lng';

        // Insert cats body type to items body type
        if(AMI_Registry::get('AMI/Module/Environment/useCatsAsSubcomponent', FALSE)){
            $aHandler = array($this, AMI::actionToHandler('view'));
            AMI_Event::addHandler('on_list_view', $aHandler, $this->getModId());
            AMI_Event::addHandler('on_details_view', $aHandler, $this->getModId());
        }

        parent::__construct();

        // Prepare template global vars appropriate to body type
        $this->oTpl = $this->getTemplate();
        $catId = (int)AMI_Registry::get('page/catId', 0);
        if($catId > 0){
            $itemId = (int)AMI_Registry::get('page/itemId', 0);
            $prefix = $itemId > 0 ? 'itemD_' : 'item_';
            /**
             * @var AMI_Reuqest
             */
            $oRequest = AMI::getSingleton('env/request');
            $catOffset = (int)$oRequest->get('catoffset', 0);
            $aScope = array(
                'front_cats_link' => $catOffset ? 'catoffset=' . $catOffset : ''
            );
            $this->getTemplate()->addGlobalVars(
                array('cat_link' => $this->parse($prefix . 'cat_link', $aScope))
            );
        }
        // Initialize common view
        $this->oCommonView = AMI::getResource('module/common/view/frn');
        $this->oCommonView->setModId($modId);
        $this->oCommonView->initByBodyType($this->bodyType, $this->aSimpleSetFields);
    }

    /**
     * Initialize.
     *
     * @see    AMI_View::init()
     * @return AMI_ModCatsViewFrn
     */
    public function init(){
        parent::init();

        $this->oList = $this->oModel->getList();

        // Init columns
        $this
            ->addColumn('id')
            ->addColumn('sublink')
            ->addColumn('header')
            ->addColumn('announce')
            ->addColumn('body')
            ->addColumn('num_public_items');

        if(AMI::issetOption($this->getModId(), 'mod_id_pages')){
            $this->addColumnType('id_page', 'hidden');
        }
        AMI_Event::addHandler('on_list_body_row', array($this, 'handleListFrontLink'), $this->getModId());

        if(!AMI_Event::hasHandlers('on_list_body_{header}')){
            $this->formatColumn('header', array($this, 'fmtHTMLEncode'));
        }

        return $this;
    }

    /**
     * Fills front link list column.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListFrontLink($name, array $aEvent, $handlerModId, $srcModId){
        // Fill front_link
        $pageId = isset($aEvent['aData']['id_page']) ? $aEvent['aData']['id_page'] : 0;
        $aEvent['aData']['front_link'] =
            AMI_PageManager::getModLink($this->getModId(), AMI_Registry::get('lang_data'), $pageId, TRUE, TRUE);
        return $aEvent;
    }

    /**
     * Fill the item fields.
     *
     * @param  array &$aItem  Item data
     * @param  array &$aData  List data
     * @return void
     */
    public function getItemCB(array &$aItem, array &$aData){
        $this->oCommonView->fillEmptyDescription($aItem);
        if(!empty($aItem['body_notempty'])){
            $aItem['more'] = $this->parseSet('cat', 'more', $aItem);
        }
        if($aItem['_num_public_items'] > 0){
            $aItem['header'] = $this->parseSet('cat', 'lheader', $aItem);
        }else{
            $aItem['header'] = $this->parseSet('cat', 'header', $aItem);
        }
    }

    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        // Initialize field callbacks
        $this->prepareFieldCallbacks();
        // Initialize scope
        $modId = $this->getModId();
        $aScope = $this->getScope($this->bodyType);
        $aPage = AMI_Registry::get('page');
        $aColumns = $this->getColumns();
        $aData = array('offset_link' => "javascript:go_page('[START]', 'offset');", 'list' => '');
        $aFrontScope = $this->oCommonView->getFrontScope();
        $aData += $aFrontScope;
        if($this->fireOnCatListView){
            AMI_Event::fire('on_cat_list_view', $aData, $modId);
        }
        /*
        $aEvent = array(
            'oTable'   => $this->oModel,
            'oList'    => $this->oList,
            'aColumns' => $aColumns
        );
        */
        // Filter categories having no items
        if(!AMI::getOption($modId . '_cat', 'show_empty_cats')){
            $this->oList->addWhereDef("AND `num_public_items` > 0");
        }

        // Load list
        $aBrowser = $this->oCommonView->getBrowserData();
        $aTplData = $this->oCommonView->getTplData();
        $this->oList
            ->addColumns($aColumns)
            ->addOrder($aBrowser['orderColumn'], $aBrowser['orderDirection'])
            ->setLimitParameters($aBrowser['listStart'], $aBrowser['listLimit'])
            ->addCalcFoundRows();
        $this->setFilter();
        $this->oList->load();

        $aData['active_item_type'] = 'body_' . $this->bodyType;
        $aData['page_item_type'] = 'body_' . AMI_Registry::get('AMI/Module/Environment/bodyType');
        $aData['_num_rows'] = $this->oList->count();
        $aData['_total_rows'] = $this->oList->getNumberOfFoundRows();
        $aData['_page_is_last'] =
            (string)(($aBrowser['listStart'] + $aBrowser['listLimit']) >= $aData['_total_rows']);
        $aData['stub_prefix'] = $aTplData['prefix']['stub'];
        $aData['simple_prefix'] = $aTplData['prefix']['simpleField'];
        $aData['splitter_prefix'] = $aTplData['prefix']['splitter'];

        $this->getTemplate()->addGlobalVars(array('BODY_TYPE' => $aData['page_item_type']));
        AMI_Registry::set('ami_global_limit_enable', true);

        // Render list
        if($this->oList->count() > 0){
            $catId = (int)AMI_Registry::get('page/catId', 0);
            $i = 0;
            foreach($this->oList as $oItem){
                $aItem = $oItem->getData() + $aFrontScope;

                $aEvent = array(
                    'aScope' => &$aScope,
                    'aData'  => &$aItem,
                    'oItem'  => $oItem
                );

                /**
                 * Allows to modify async list row.
                 *
                 * @event      on_list_body_row $modId
                 * @eventparam array            aScope  Row scope
                 * @eventparam AMI_ModTableItem oItem   Table item model
                 */
                AMI_Event::fire('on_list_body_row', $aEvent, $this->getModId());

                foreach($aItem as $columnName => $columnValue){
                    $aEvent['aScope']['list_col_name'] = $columnName;
                    $aEvent['aScope']['list_col_value'] = $columnValue;
                    AMI_Event::fire('on_list_body_{' . $columnName . '}', $aEvent, $modId);
                    $aItem[$columnName] = $aEvent['aScope']['list_col_value'];
                }

                $aItem['abs_row_index'] = $aBrowser['listStart'] + $i;
                $aItem['row_index'] = $i;

                $aItem['style'] = $aItem['row_index'] & 1 ? 'row2' : 'row1';
                $aItem['SELECTED_ITEM'] = '0'; // $catId != $aItemData['id'] ? '0' : '1';
                $this->oCommonView->processFields($aItem, $aData);

                $i++;

                $aData['browser_row'] = $this->oCommonView->parseTpl('row', $aItem);

                $aData['list'] .= $aData['browser_row'];
            }
            $paginationNavData = $aPage['scriptLink'] . $aBrowser['navData'] . 'action=rsrtme';
            $itemId = (int)AMI_Registry::get('page/itemId', 0);
            $catId = (int)AMI_Registry::get('page/catId', 0);
            if($itemId > 0){
                $paginationNavData .= '&id=' . $itemId;
            }
            if($aBrowser['offset']){
                $paginationNavData .= '&' . $aBrowser['offsetVar'] . '=' . $aBrowser['offset'];
            }
            if($catId){
                $paginationNavData .= '&catid=' . $catId;
            }
            $aData['offset_link'] = $paginationNavData . "&" . $aBrowser['catoffsetVar'] . "=[START]";
            $aData['pager'] = '';
            if($i > 0){
                $aPagerParams = array(
                    'pageSize'   => $aBrowser['pageSize'],
                    'position'   => $aBrowser['listStart'],
                    'calcPages'  => AMI::issetAndTrueOption($modId, 'pager_page_number_as_bound'),
                    'offsetLink' => $aData['offset_link']
                );
                $aData['pager'] = $this->oCommonView->getPagination($aPagerParams, $this->oList);
            }
            $aData[$aTplData['set']['list']] = $this->oCommonView->parseTpl('list', $aData + $aScope);
        }else{
            $aData[$aTplData['set']['list']] = $this->oCommonView->parseTpl('empty_list', $aData);
        }
        unset($aData['list']);
        AMI_Registry::set('ami_global_limit_enable', false);
        return $aData;
    }

    /**
     * Set filter.
     *
     * @return AMI_ModItemsView
     */
    protected function setFilter(){
        $aBrowser = $this->oCommonView->getBrowserData();
        if($aBrowser['useSpecView']){
            $this->oList->addWhereDef(
                DB_Query::getSnippet("AND `i`.`" . $this->oModel->getFieldName('hide_in_list') . "` = 0")
            );
        }
        return $this;
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

        $this->oCommonView->setFieldCallback(
            'header',
            array(
                'object' => $this,
                'method' => 'getItemCB'
            )
        );
        $this->oCommonView->setFieldCallback(
            'splitter',
            array(
                'object' => $this->oCommonView,
                'method' => 'getSplitterCB'
            )
        );
    }
}
