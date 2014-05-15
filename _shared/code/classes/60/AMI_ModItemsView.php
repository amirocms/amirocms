<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModItemsView.php 46781 2014-01-20 12:49:00Z Maximov Alexey $
 * @since     5.14.8
 */

/**
 * Module front items body type view.
 *
 * @package    ModuleComponent
 * @subpackage View
 * @since      5.14.8
 */
abstract class AMI_ModItemsView extends AMI_ModListView{
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
     * Body type view
     *
     * @var string
     */
    protected $bodyType = 'items';

    /**
     * Array of simple set fields
     *
     * @var array
     */
    protected $aSimpleSetFields = array('announce', 'header', 'body', 'fdate', 'ftime');

    /**
     * Module common front view
     *
     * @var AMI_ModCommonViewFrn
     */
    protected $oCommonView;

    /**
     * Flag specifies that pagination is enabled
     *
     * @var bool
     */
    protected $isPaginationEnabled = TRUE;

    /**
     * Add CALC_FOUND_ROWS to select query
     *
     * @var bool
     */
    protected $bCalcFoundRows = TRUE;

    /**
     * Active sort field
     *
     * @var string
     */
    protected $sortField;

    /**
     * Active sort direction
     *
     * @var string
     */
    protected $sortDir;

    /**
     * Is default sort used
     *
     * @var bool
     */
    protected $sortDefault = TRUE;

    /**
     * Supported "response_type" GET parameters
     *
     * @var array
     */
    protected $aSupportedResponseTypes = array('json', 'item_list');

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
        $this->tplBlockName = $modId;
        $this->localeFileName = AMI_iTemplate::LNG_MOD_PATH . '/' . $modId . '.lng';

        parent::__construct();
        $this->oTpl = $this->getTemplate();

        if(AMI_Registry::get('AMI/Module/Environment/items/no_cat_mode', FALSE)){
            // Override common options by subitems options
            foreach(
                array(
                    'front_subitem_sort_col' => 'front_page_sort_col',
                    'front_subitem_sort_dim' => 'front_page_sort_dim',
                    'subitems_cols'          => 'cols'
                ) as $src => $dest
            ){
                AMI::setOption($modId, $dest, AMI::getOption($modId, $src));
            }
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
     * @return AMI_ModItemsView
     */
    public function init(){
        parent::init();

        $this->initListModel();

        $modId = $this->getModId();

        // Init columns
        $this
            ->addColumn('id')
            ->addColumn('public')
            ->addColumnType('date_created', 'datetime')
            ->addColumnType('fdate', 'date')
            ->addColumnType('ftime', 'time')
            ->addColumn('sublink')
            ->addColumn('header')
            ->addColumn('author')
            ->addColumn('source')
            ->addColumn('announce')
            ->addColumn('body')
            ->addColumn('body_notempty')
            ->addColumn('date_modified')
            ->addSortColumns(array('header', 'date_created', 'ext_rate_count', 'ext_rate_rate'));

        if(AMI::issetOption($modId, 'mod_id_pages')){
            $this->addColumnType('id_page', 'hidden');
        }

        AMI_Event::addHandler('on_list_body_row', array($this, 'handleListBodyRow'), $modId);

        return $this;
    }

    /**
     * Fills fdate/ftime list columns.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListBodyRow($name, array $aEvent, $handlerModId, $srcModId){
        if(!isset($aEvent['aData']['date_created'])){
            return $aEvent;
        }
        $pageId = isset($aEvent['aData']['id_page']) ? $aEvent['aData']['id_page'] : 0;
        $aEvent['aData']['front_link'] = AMI_PageManager::getModLink($this->getModId(), AMI_Registry::get('lang_data'), $pageId, TRUE, TRUE);
        $aEvent['aData']['fdate'] =
            AMI_Lib_Date::formatDateTime(
                $aEvent['aData']['date_created'],
                AMI_Lib_Date::FMT_DATE
            );
        $aEvent['aData']['ftime'] =
            AMI_Lib_Date::formatDateTime(
                $aEvent['aData']['date_created'],
                AMI_Lib_Date::FMT_TIME
            );
        return $aEvent;
    }

    /**
     * Initialize list model.
     *
     * @return void
     */
    protected function initListModel(){
        $this->oList = $this->oModel->getList();
    }

    /**
     * Sets list model order by condition.
     *
     * @return void
     */
    protected function setOrder(){
        $defaultSort = true;
        $oRequest = AMI::getSingleton('env/request');
        $sortField = $oRequest->get('sort', false);
        $sortDir = $oRequest->get('sort_dir', 'asc');
        if($sortField){
            if($this->oModel->hasField($sortField) && in_array($sortDir, array('asc', 'desc'))){
                $this->oList->addOrder($sortField, $sortDir);
                $defaultSort = false;
                $this->sortField = $sortField;
                $this->sortDir = $sortDir;
                $this->sortDefault = false;
            }
        }
        if($defaultSort){
            $aBrowser = $this->oCommonView->getBrowserData();
            $this->oList->addOrder($aBrowser['orderColumn'], $aBrowser['orderDirection']);
            $this->sortField = $aBrowser['orderColumn'];
            $this->sortDir = $aBrowser['orderDirection'];
        }
    }

    /**
     * Returns sort controls for the list.
     *
     * @return string
     */
    protected function getSortControls(){
        $sort = '';
        $navData = $this->getNavData();
        $aBrowser = $this->oCommonView->getBrowserData();
        if(isset($aBrowser['listStart']) && $aBrowser['listStart']){
            $navData .= ('&offset=' . $aBrowser['listStart']);
        }
        $aSortData = array(
            'nav_data'      => $navData
        );
        if(sizeof($this->aSortColumns)){
            foreach($this->aSortColumns as $idx => $sortColumn){
                $aSortFieldData = array(
                    'name'          => $sortColumn,
                    'is_active'     => $this->sortField == $sortColumn,
                    'dir'           => $this->sortDir,
                    'nav_data'      => $navData,
                    'is_first'      => $idx == 0,
                    'is_last'       => $idx == (count($this->aSortColumns) - 1),
                    'is_sortable'   => true,
                    'is_header'     => false,
                    'caption'       => isset($this->aLocale['sort_field_' . $sortColumn]) ? $this->aLocale['sort_field_' . $sortColumn] : $sortColumn,
                    'sort_controls' => ''
                );
                foreach(array('asc', 'desc') as $dir){
                    $dirCaption = $dir;
                    if(isset($this->aLocale['sort_dir_' . $dir])){
                        $dirCaption = $this->aLocale['sort_dir_' . $dir];
                    }
                    if(isset($this->aLocale['sort_dir_' . $dir . '_' . $sortColumn])){
                        $dirCaption = $this->aLocale['sort_dir_' . $dir . '_' . $sortColumn];
                    }
                    $aSortCtrlData = array(
                        'name'          => $sortColumn,
                        'field_caption' => $aSortFieldData['caption'],
                        'dir_caption'   => $dirCaption,
                        'is_active'     => $aSortFieldData['is_active'] && ($this->sortDir == $dir),
                        'nav_data'      => $navData,
                        'dir'           => $dir
                    );
                    $aSortFieldData['sort_controls'] .= $this->parse('sort_control', $aSortCtrlData);
                }
                $field = $this->parse('sort_field', $aSortFieldData);
                $aSortData['field_' . $sortColumn] = $field;
                $sort .= $field;
            }
            if($sort){
                $sortDirs = '';
                foreach(array('asc', 'desc') as $dir){
                    $dirCaption = $dir;
                    if(isset($this->aLocale['sort_dir_' . $dir])){
                        $dirCaption = $this->aLocale['sort_dir_' . $dir];
                    }
                    $aSortCtrlData = array(
                        'name'          => '',
                        'field_caption' => '',
                        'is_active'     => $this->sortDir == $dir,
                        'nav_data'      => $navData,
                        'dir'           => $dir,
                        'dir_caption'   => $dirCaption
                    );
                    $sortDirs .= $this->parse('sort_control', $aSortCtrlData);
                }
                $aSortData += array(
                    'sort_fields'   => $this->parse('sort_fields_all', array('sort_fields' => $sort)),
                    'sort_dirs'     => $this->parse('sort_dirs_all', array('sort_dirs' => $sortDirs)),
                    'sort'          => $sort
                );
                $sort = $this->parse('sort', $aSortData);
            }
        }

        return $sort;
    }

    /**
     * Returns header with sort ability for the list.
     *
     * @return string
     */
    protected function getListHeader(){
        $header = '';
        $navData = $this->getNavData();
        $aBrowser = $this->oCommonView->getBrowserData();
        if(isset($aBrowser['listStart']) && $aBrowser['listStart']){
            $navData .= ('&offset=' . $aBrowser['listStart']);
        }
        $aHeaderData = array(
            'nav_data'  => $navData,
            'sort'      => ''
        );
        $aHeaderColumns = array();
        foreach($this->aColumns as $idx => $column){
            if(!$column){
                continue;
            }
            $aHeaderColumns[] = $column;
        }
        foreach($aHeaderColumns as $idx => $column){
            $aSortFieldData = array(
                'name'          => $column,
                'is_active'     => $this->sortField == $column,
                'dir'           => $this->sortDir,
                'nav_data'      => $navData,
                'is_first'      => $idx == 0,
                'is_last'       => $idx == (count($this->aColumns) - 1),
                'is_sortable'   => in_array($column, $this->aSortColumns),
                'is_header'     => true,
                'caption'       => isset($this->aLocale['header_field_' . $column]) ? $this->aLocale['header_field_' . $column] : $column,
                'sort_controls' => ''
            );
            if($aSortFieldData['is_sortable']){
                foreach(array('asc', 'desc') as $dir){
                    $dirCaption = $dir;
                    if(isset($this->aLocale['sort_dir_' . $dir])){
                        $dirCaption = $this->aLocale['sort_dir_' . $dir];
                    }
                    if(isset($this->aLocale['sort_dir_' . $dir . '_' . $column])){
                        $dirCaption = $this->aLocale['sort_dir_' . $dir . '_' . $column];
                    }
                    $aSortCtrlData = array(
                        'name'          => $column,
                        'field_caption' => $aSortFieldData['caption'],
                        'dir_caption'   => $dirCaption,
                        'is_active'     => $aSortFieldData['is_active'] && ($this->sortDir == $dir),
                        'nav_data'      => $navData,
                        'dir'           => $dir
                    );
                    $aSortFieldData['sort_controls'] .= $this->parse('sort_control', $aSortCtrlData);
                }
            }
            $field = $this->parse('sort_field', $aSortFieldData);
            $aHeaderData['field_' . $column] = $field;
            $header .= $field;
        }
        if($header){
            $aHeaderData['fields'] = $header;
            $header = $this->parse('sort_list_header', $aHeaderData);
        }
        return $header;
    }

    /**
     * Returns navigation string to element or category.
     *
     * @return string
     */
    protected function getNavData(){
        $aPage = AMI_Registry::get('page');
        $aBrowser = $this->oCommonView->getBrowserData();
        $itemId = (int)AMI_Registry::get('page/itemId', 0);
        $catId = (int)AMI_Registry::get('page/catId', 0);

        $navData = $aPage['scriptLink'] . $aBrowser['navData'] . 'action=rsrtme';
        if($this->bodyType == 'browse_items'){
            $navData .= '&mode=browse';
        }
        if($itemId && ($this->bodyType != 'browse_items')){
            $navData .= '&id=' . $itemId;
        }
        if($aBrowser['catoffset']){
            $navData .= '&' . $aBrowser['catoffsetVar'] . '=' . $aBrowser['catoffset'];
        }
        if($catId){
            $navData .= '&catid=' . $catId;
        }
        return $navData;
    }

    /**
     * Loads list model.
     *
     * @param  array $aColumns  Columns
     * @return void
     */
    protected function loadModel(array $aColumns){
        $aBrowser = $this->oCommonView->getBrowserData();

        $this->setOrder();

        // adjust list position
        $bCorrectPosition = true;
        if($aBrowser['mode'] == "tape"){
            $backupPageSize = $aBrowser['pageSize'];
            $aBrowser['pageSize'] = 1;
            $position = $this->oList->arrangePosition((int)AMI_Registry::get('page/itemId', 0), $this->sortField, $this->sortDir);
            if($aBrowser['tapePosition'] > 0){
                $numLeftTapeItems = $aBrowser['tapePosition'] - 1;
                $numRightTapeItems = $backupPageSize - $aBrowser['tapePosition'];
                $this->oList->setItemPosition(max(0, $position - $numRightTapeItems - $numLeftTapeItems));
                $aBrowser['pageSize'] = $backupPageSize + $numRightTapeItems;
                $bCorrectPosition = false;
            }else{
                $this->oList->setItemPosition($position);
                $aBrowser['pageSize'] = $backupPageSize;
            }
        }

        $this->oList->adjustPosition($aBrowser['pageSize'], $bCorrectPosition);

        if($aBrowser['mode'] == "tape" && $aBrowser['tapePosition'] == 0){
            $itemPosition = $this->oList->getItemPosition();
            if($itemPosition > 0){
                $this->oList->setItemPosition($itemPosition - 1);
                $aBrowser['pageSize'] += 2;
            }else{
                $aBrowser['pageSize']++;
            }
        }

        $this->oList->addColumns($aColumns);

        // set limit parameters
        if($aBrowser['mode'] == "tape"){
            $itemPosition = $this->oList->getItemPosition();
            if($aBrowser['pageSize'] > 0){
                if($itemPosition > 0){
                    $this->oList->setLimitParameters($itemPosition, $aBrowser['pageSize']);
                }else{
                    $this->oList->setLimitParameters(0, $aBrowser['pageSize']);
                }
            }else{
                if($itemPosition > 0){
                    $this->oList->setLimitParameters(0, $itemPosition);
                }
            }
            $aBrowser['pageSize'] = $backupPageSize;
            $this->oList->setItemPosition($position);
        }else{
            $this->oList->setLimitParameters($aBrowser['listStart'], $aBrowser['listLimit']);
        }

        $this->oList->addCalcFoundRows($this->bCalcFoundRows);
        $this->setFilter();

        // AMI::getSingleton('db')->displayQueries(TRUE);
        $this->oList->load();
        // AMI::getSingleton('db')->displayQueries(FALSE);
    }

    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        /**
         * List browser data
         */
        $aBrowser = $this->oCommonView->getBrowserData();
        $aScope = $this->getScope($this->bodyType);
        if(
            AMI_Registry::get('AMI/Module/Environment/items/no_cat_mode', FALSE) ||
            AMI_Registry::get('AMI/Module/Environment/items/spec_no_cat_mode', FALSE)
        ){
            $aScope['no_cat_mode'] = TRUE;
        }
        $aData = array();
        $itemId = (int)AMI_Registry::get('page/itemId', 0);
        $catId = (int)AMI_Registry::get('page/catId', 0);
        $this->prepareFieldCallbacks();

        $modId = $this->getModId();
        // $aScope = $aData + $this->getScope($this->bodyType);
        $aPage = AMI_Registry::get('page');

        $aColumns = $this->getColumns();

        $aTplData = $this->oCommonView->getTplData();

        // Set list view params
        $aData +=
            array(
                'page_item_type'   => 'body_items',
                'active_item_type' => 'body_' . $this->bodyType,
                'simple_prefix'    => $aTplData['prefix']['simpleField'],
                'splitter_prefix'  => $aTplData['prefix']['splitter'],
                'stub_prefix'      => $aTplData['prefix']['stub'],
                'cat_id'           => $catId,
                'list'             => ''
            ) +
            $aScope +
            $this->oCommonView->getFrontScope();

        $aEvent = array(
            'oTable'   => $this->oModel,
            'oList'    => $this->oList,
            'aColumns' => $aColumns
        );

        $this->loadModel($aColumns);

        $aData['sort'] = $this->getSortControls();
        $aData['list_header'] = $this->getListHeader();

        $aData['_num_rows'] = 0;
        $aData['_total_rows'] = $this->oList->getNumberOfFoundRows();
        $aData['_page_is_last'] =
            $this->oList->getPagesCount($aBrowser['pageSize']) ==
            ($this->oList->getActivePage($aBrowser['pageSize'], $aBrowser['listStart']) + 1);

        AMI_Registry::set('AMI/Module/Environment/' . $this->getModId() . '/' . $this->bodyType . '/count', $this->oList->count());

        // Process list items
        if($this->oList->count() > 0){
            $numRows = 0;

            $numSkippedRows = 0;
            $aSkipRowNums = array();
            if($aBrowser['mode'] == "tape"){
                $position = $this->oList->getItemPosition();
                $maxNumRows = $this->oList->count();
                if($aBrowser['tapePosition'] > 0){
                    $numLeftTapeItems = $aBrowser['tapePosition'] - 1;
                    $numRightTapeItems = $aBrowser['pageSize'] - $aBrowser['tapePosition'];
                    $numSkippedRows = min($numRightTapeItems, $aData['_total_rows'] - $position - 1, $position - $numLeftTapeItems, $aData['_total_rows'] - $aBrowser['pageSize']);
                    $numSkippedRows = max(0, $numSkippedRows);
                    $maxNumRows = $aBrowser['pageSize'] + $numSkippedRows;
                }else{
                    if($position > ($aBrowser['pageSize'] - 1) && $position != ($aData['_total_rows'])){
                        $aSkipRowNums[] = 0;
                    }
                    if($maxNumRows > $aBrowser['pageSize']){
                        $aSkipRowNums[] = $aBrowser['pageSize'];
                    }
                }
            }
            if(sizeof($aSkipRowNums) > 0){
                reset($aSkipRowNums);
                $skipRowNum = current($aSkipRowNums);
            }else{
                $skipRowNum = -1;
            }
            $rowIndex = -1;
            foreach($this->oList as $oItem){
                $rowIndex += 1;
                $aItem = $oItem->getData();
                if($aBrowser['mode'] == "tape"){
                    if(!(($rowIndex >= $numSkippedRows) && ($rowIndex < $maxNumRows))){
                        continue;
                    }
                }
                if(!$this->checkItem($oItem, $numRows)){
                    continue;
                }
                // check global limits
                if(AMI_Registry::get('ami_global_limit_enable', false)){
                    $counter = (int)AMI_Registry::get('ami_global_limit_counter', 0) + 1;
                    AMI_Registry::set('ami_global_limit_counter', $counter);
                    $modLimitOption = 'subitems_total_items_limit';
                    if(AMI_Registry::get('ami_specblock_mode', false)){
                        $modLimitOption = 'spec_total_items_limit';
                    }
                    if($counter > (int)AMI::getOption($modId, $modLimitOption)){
                        AMI_Registry::set('ami_global_limit_stopper', true);
                        $sbId = AMI_Registry::get('ami_specblock_id', FALSE);
                        AMI_Registry::push('disable_error_mail', TRUE);
                        trigger_error(
                            'Subitems count limit excedeed (' . $modLimitOption .') in ' .
                            ($sbId ? 'specblock ' . $sbId : 'module ' . $modId),
                            E_USER_WARNING
                        );
                        AMI_Registry::pop('disable_error_mail');
                    }
                    if(AMI_Registry::get('ami_global_limit_stopper', false)){
                        break;
                    }
                }
                // get item data
                $aItem = $oItem->getData();
                $aItem += $this->oCommonView->getFrontScope();
                if(isset($aScope['no_cat_mode'])){
                    $aItem['no_cat_mode'] = $aScope['no_cat_mode'];
                }

                $aEvent = array(
                    'aScope' => &$aScope,
                    'aData'  => &$aItem,
                    'oItem'  => $oItem,
                    'block'  => $modId,
                    'type'   => $this->bodyType
                );

                /**
                 * Allows to modify async list row.
                 *
                 * @event      on_list_body_row $modId
                 * @eventparam array            aScope  Row scope
                 * @eventparam array            aData   Item data
                 * @eventparam AMI_ModTableItem oItem   Table item model
                 * @eventparam string           block   Template block name
                 * @eventparam string           type    current bodyType
                 */
                AMI_Event::fire('on_list_body_row', $aEvent, $this->getModId());

                foreach($aItem as $columnName => $columnValue){
                    $aEvent['aScope']['list_col_name'] = $columnName;
                    $aEvent['aScope']['list_col_value'] = $columnValue;
                    AMI_Event::fire('on_list_body_{' . $columnName . '}', $aEvent, $modId);
                    $aItem[$columnName] = $aEvent['aScope']['list_col_value'];
                }

                $aItem['row_index'] = $numRows;
                $aItem['style'] = $aItem['row_index'] & 1 ? 'row2' : 'row1';
                $aItem['SELECTED_ITEM'] = $aItem['id'] == $itemId ? 1 : 0;
                $aItem['abs_row_index'] = $aBrowser['listStart'] + $numRows;
                $aItem['details_link'] = isset($aItem['body']) && $aItem['body'] !== '' ? '1' : '0';
                if($aItem['id'] == $itemId){
                    $currentItemOffset = $numRows;
                }

                // process item fields
                $this->oCommonView->processFields($aItem, $aData);

                if($numRows == $skipRowNum){
                    if(next($aSkipRowNums)){
                        $skipRowNum = current($aSkipRowNums);
                    }else{
                        $skipRowNum = -1;
                    }
                }else{
                    $numRows++;
                    $aData['browser_row'] = $this->oCommonView->parseTpl('row', $aItem);
                    $aData['list'] .= $aData['browser_row'];
                }
            }

            // set pagination and nav. data
            $paginationNavData = $this->getNavData();
            if(!$this->sortDefault){
                $paginationNavData .= '&sort=' . $this->sortField;
                $paginationNavData .= '&sort_dir=' . $this->sortDir;
            }

            // Process filter data
            if(AMI_Registry::get('AMI/Module/Environment/Filter/active')){
                // Add filter params to pager url
                $oFilter = AMI_Registry::get('AMI/Module/Environment/Filter/Controller');
                $paginationNavData .= '&body_filtered=1' . $oFilter->getFieldsAsUrlParams();
            }
            $aData['offset_link'] = $paginationNavData . "&" . $aBrowser['offsetVar'] . "=[START]";
            $aData['_num_rows'] = $numRows;
            $aData['pager'] = '';
            if($this->isPaginationEnabled && $numRows > 0){
                $aPagerParams = array(
                    'mode' => $aBrowser['mode'],
                    'pageSize' => $aBrowser['pageSize'],
                    'position' => $aBrowser['listStart'],
                    'tapePosition' => $aBrowser['tapePosition'],
                    'calcPages' => AMI::issetAndTrueOption($modId, 'pager_page_number_as_bound'),
                    'offsetLink' => $aData['offset_link'],
                    'currentItemOffset' => isset($currentItemOffset) ? $currentItemOffset : $aBrowser['currentItemOffset']
                );
                $aData['pager'] = $this->oCommonView->getPagination($aPagerParams, $this->oList);
            }
            $aData[$aTplData['set']['list']] = $this->oCommonView->parseTpl('list', $aData);
        }else{
            $aData[$aTplData['set']['list']] = $this->oCommonView->parseTpl('empty_list', $aData);
                // $this->oModelList->getNumberOfFoundRows() >= $aBrowser['offset']
                    // ? $this->parse($this->tplListEmptySet, $aData)
                    // : '';
        }
        unset($aData['list']);

        if(isset($aData['cat_header'])){
            $header = $aData['cat_header'];
            $description = isset($aData['cat_announce']) ? $aData['cat_announce'] : '';
            $image = '';
            if(isset($aData['img_src']) && ($aData['img_src'] != "")){
                $image = $aData['img_src'];
            }
            $this->addOpenGraphTags($header, $description, $image);
        }      
        /*
        if($oItem->og_image != ''){
            $image = $oItem->og_image;
        }
         */
        // Response type output
        $outType = strtolower(AMI::getSingleton('env/request')->get('response_type', ''));
        if($outType && in_array($outType, $this->aSupportedResponseTypes)){
            $this->oCommonView->outType($aData, $outType);
        }

        return $aData;
    }

    /**
     * Fill the item fields.
     *
     * @param  array &$aItem  Item data
     * @param  array &$aData  List data
     * @return void
     */
    public function getItemCB(array &$aItem, array &$aData){
        $this->oCommonView->fillEmptyDescription($aItem, TRUE);
        $this->oCommonView->drawItemCB($aItem, $aData);
        if(!empty($aItem['author'])){
            $aItem['author'] = $this->parseSet('item', 'author', $aItem);
        }
        if(!empty($aItem['source'])){
            $aItem['source'] = $this->parseSet('item', 'source', $aItem);
        }
    }

    /**
     * Checks to display item.
     *
     * @param  AMI_iModTableItem $oItem  Item model
     * @param  int               $index  Item index
     * @return bool
     */
    protected function checkItem(AMI_iModTableItem $oItem, $index){
        return TRUE;
    }

    /**
     * Set filter.
     *
     * @return AMI_ModItemsView
     */
    protected function setFilter(){
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
            'details',
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
