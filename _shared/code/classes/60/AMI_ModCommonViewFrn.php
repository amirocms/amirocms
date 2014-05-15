<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModCommonViewFrn.php 43082 2013-11-05 09:26:04Z Maximov Alexey $
 * @since     5.14.8
 */

/**
 * Module common front view.
 *
 * Supports simple sets, pagination.
 *
 * @package    ModuleComponent
 * @subpackage View
 * @since      5.14.8
 * @resource   'module/common/view/frn' <code>AMI::getSingleton('module/common/view/frn')</code>
 */
class AMI_ModCommonViewFrn extends AMI_View{
    /**
     * Template engine object
     *
     * @var AMI_Template
     */
    protected $oTpl;

    /**
     * Module id
     *
     * @var string
     */
    protected $modId;

    /**
     * Structure containing template set prefixes
     *
     * @var array
     */
    protected $aTplData;

    /**
     * Pagination block name
     *
     * @var string
     */
    protected $paginationBlockName = '_pager';

    /**
     * Pagination template name
     *
     * @var string
     */
    protected $paginationTemplate = 'templates/pager.tpl';

    /**
     * Browser related data
     *
     * @var array
     */
    protected $aBrowser = array(
        'mode'              => 'page',      // Browsing type: page, tape
        'pageSize'          => 10,          // Items per page
        'offset'            => 0,           // Item list offset
        'catoffset'         => 0,           // Category list offset
        'offsetVar'         => 'offset',    // Item list offset variable name
        'catoffsetVar'      => 'catoffset', // Category list offset variable name
        'currentItemOffset' => 0,           // Current item offset, used in the 'tape' mode
        'listStart'         => 0,           // List start position
        'listLimit'         => 0,           // List limit
        'orderColumn'       => 'id',        // List order column
        'orderDirection'    => 'asc',       // List order direction
        'navData'           => '?',         // Pagination navigation data
        'useSpecView'       => FALSE,       // Spicifies if special list view
                                           // (public direct link/sticky) is used
        'cols'              => 1,           // List colimns number
        'forceViewEndLink'  => FALSE,       // Used by forum
        'tapePosition'      => 0            // Tape position, used in the 'tape' mode
    );

    /**
     * Array of links data
     *
     * @var array
     */
    protected $aFrontScope = array();

    /**
     * Array of simple set fields
     *
     * @var array
     */
    protected $aSimpleSetFields = array();

    /**
     * Array of fields data
     *
     * @var array
     * @see AMI_ModCommonViewFrn::setFieldCallback()
     * @see AMI_ModCommonViewFrn::processFields()
     */
    protected $aFields = array();

    /**
     * Body type
     *
     * @var string
     */
    protected $bodyType;

    /**
     * Constructor.
     *
     * @todo SEO check
     */
    public function __construct(){
        $this->oTpl = $this->getTemplate();

        $oRequest = AMI::getSingleton('env/request');
        foreach(
            array($this->aBrowser['catoffsetVar'], $this->aBrowser['offsetVar'])
            as $varName
        ){
            $value = $oRequest->get($varName, FALSE);
            if($value !== FALSE){
                // $this->checkIntArg($value); ## TODO: SEO
                $this->aBrowser[$varName] = (int)$value;
            }
        }
    }

    /**
     * Set module id.
     *
     * @param  string $modId        Module id
     * @param  string $tplFileName  Template file name
     * @return void
     */
    public function setModId($modId, $tplFileName = ''){
        $this->modId = $modId;
        $tplName = $modId;
        if(!AMI_Registry::get('ami_specblock_mode', false) && AMI_Registry::exists('page/tplAddon')){
            $tplName .= AMI_Registry::get('page/tplAddon');
        }
        $this->tplFileName = !empty($tplFileName) ? $tplFileName : AMI_iTemplate::TPL_MOD_PATH . '/' . $tplName . '.tpl';
        $this->tplBlockName = $modId;
        $this->localeFileName = AMI_iTemplate::LNG_MOD_PATH . '/' . $modId . '.lng';

        // Hack
        parent::__construct();
    }

    /**
     * Initialize view by body type.
     *
     * @param  string $type              Body type
     * @param  array  $aSimpleSetFields  Simple set field list
     * @return void
     */
    public function initByBodyType($type, array $aSimpleSetFields){
        $this->bodyType = $type;
        switch($type){
            case 'items':
                $this->setupTplData('item_');
                $this->setupList();
                break;
            case 'sticky_items':
                $this->setupTplData('sticky_item_');
                $this->setupList();
                break;
            case 'browse_items':
                $this->setupTplData('browse_item_');
                $this->setupList();
                break;
            case 'subitems':
                $this->setupTplData('subitem_');
                $this->setupList();
                break;
            case 'cats':
                $this->setupTplData('cat_');
                $this->setupList();
                break;
            case 'sticky_cats':
                $this->setupTplData('sticky_cat_');
                $this->setupList();
                break;
            case 'details':
                $this->setupTplData('itemD_');
                break;
            case 'cat_details':
                $this->setupTplData('catD_');
                break;
            case 'small':
                $this->setupTplData('small_');
                $this->setupList();
                break;
            case 'filtered':
                break;
            default:
                trigger_error("Unsupported body type '" . $type . "'", E_USER_WARNING);
        }

        $this->aSimpleSetFields = $aSimpleSetFields;

        // Create links offset
        $itemOffsetUrl = '';
        $catOffsetUrl = '';
        $this->aFrontScope = array();
        if($this->aBrowser['offset'] > 0){
            $this->oTpl->addGlobalVars(array($this->aBrowser['offsetVar'] => $this->aBrowser['offset']));
            $itemOffsetUrl = '&' . $this->aBrowser['offsetVar'] . '=' . $this->aBrowser['offset'];
        }
        if($this->aBrowser['catoffset'] > 0){
            $this->oTpl->addGlobalVars(array($this->aBrowser['catoffsetVar'] => $this->aBrowser['catoffset']));
            $catOffsetUrl = '&' . $this->aBrowser['catoffsetVar'] . '=' . $this->aBrowser['catoffset'];
        }

        $this->aFrontScope['front_cats_link'] = $catOffsetUrl;
        $this->aFrontScope['front_items_link'] = $catOffsetUrl . $itemOffsetUrl;
    }

    /**
     * Stub.
     *
     * @return void
     */
    public function get(){
    }

    /**
     * Sets global body type.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleAfterInitComponents($name, array $aEvent, $handlerModId, $srcModId){
        $type = AMI_Registry::get('AMI/Module/Environment/bodyType');
        if($type !== 'specblock'){
            if($type === 'items' && AMI_Registry::get('AMI/Module/Environment/Filter/active')){
                $type = 'filtered';
            }elseif($type === 'details'){
                $type = 'itemD';
            }
            $type = 'body_' . $type;
            $this->oTpl->addGlobalVars(array('BODY_TYPE' => $type));
            $this->oTpl->addScriptCode("var _cms_body_type = '" . $type . "';");
        }
        return $aEvent;
    }

    /**
     * Returns browsing related data.
     *
     * @return array
     */
    public function getBrowserData(){
        return $this->aBrowser;
    }

    /**
     * Sets browsing related data.
     *
     * @param  array $aData  Data
     * @return void
     */
    public function setBrowserData(array $aData){
        $this->aBrowser = $aData + $this->aBrowser;
    }

    /**
     * Returns part of front scope.
     *
     * @return array
     */
    public function getFrontScope(){
        return $this->aFrontScope;
    }

    /**
     * Returns template set prefixes/names.
     *
     * @return array
     */
    public function getTplData(){
        return $this->aTplData;
    }

    /**
     * Returns list pagination.
     *
     * @param  array            $aParams  Pagination parameters
     * @param  AMI_ModTableList $oList    List object
     * @return string
     */
    public function getPagination(array $aParams, AMI_ModTableList $oList){
        $aPagination = $oList->getPager($aParams);
        $this->oTpl->addBlock($this->paginationBlockName, $this->paginationTemplate);

        $pagination = '';
        $activePageLink = '';
        $aScope = array();
        $isAfterActive = false;
        $cLinkHtml = $aParams['offsetLink'];

        foreach($aPagination as $aItem){
            if($aItem['type'] == 'active'){
                $activePageLink = str_replace('[START]', $aItem['_start'], $cLinkHtml);
                $isAfterActive = true;
            }
            $page =
                isset($aItem['page_start'])
                ? $this->oTpl->parse($this->paginationBlockName . ':page_tpl', $aItem)
                : $aItem['pagenum'];
            $cLinkHtmlReady = str_replace('[START]', $aItem['start'], $cLinkHtml);
            $aLocalScope = array('page' => $page, 'link' => $cLinkHtmlReady, 'start' => $aItem['start']);
            $sitem = $this->oTpl->parse($this->paginationBlockName . ':'.$aItem['type'], $aLocalScope);
            $spacer = $this->oTpl->parse($this->paginationBlockName . ':spacer', $aLocalScope);

            if(!empty($pagination)){
                $pagination .= $spacer;
            }
            $pagination .= $sitem;

            if($aItem['type'] == 'page'){
                $varName = $isAfterActive ? 'page_after_active' : 'page_before_active';
            }else{
                $varName = $aItem['type'];
            }

            if(isset($aScope[$varName])){
                $aScope[$varName] .= $spacer.$sitem;
            }else{
                $aScope[$varName] = $sitem;
            }
        }

        $aScope['body'] = $pagination;
        $aScope['page_size'] = '';
        $postfix =
            $this->oTpl->issetSet($this->paginationBlockName . ':body') && $oList->getPagesCount($aParams['pageSize']) > 1
                ? ':body'
                : '';
        $pagination = $this->oTpl->parse($this->paginationBlockName . $postfix, $aScope);

        return $pagination;
    }

    /**
     * Parses set from template data and returns the result as a string.
     *
     * @param  string $setName  Set name
     * @param  array  $aScope   Scope
     * @return string
     */
    public function parseTpl($setName, array $aScope = array()){
        return $this->getTemplate()->parse($this->tplBlockName . ':' . $this->aTplData['set'][$setName], $aScope);
    }

    /**
     * Sets field callback.
     *
     * @param  string   $field      Field name
     * @param  callback $aCallback  Callback
     * @return void
     */
    public function setFieldCallback($field, $aCallback = null){
        if(is_null($aCallback)){
            unset($this->aFields[$field]);
        }else{
            $this->aFields[$field] = $aCallback;
        }
    }

    /**
     * Process fields by callbacks.
     *
     * @param  array &$aItem   Item data
     * @param  array &$aScope  Scope
     * @return void
     */
    public function processFields(array &$aItem, array &$aScope){
        foreach($this->aFields as $name => $aRule){
            if(!is_array($aRule)){
                continue;
            }
            if(!isset($aItem[$name])){
                $aItem[$name] = null;
            }
            $aRule['object']->{$aRule['method']}($aItem, $aScope);
        }
        $modId =
            $this->getModId() .
            (in_array($this->bodyType, array('cats', 'sticky_cats', 'cat_details')) ? '_cat' : '');
        $aEvent = array(
            'bodyType' => $this->bodyType,
            'aItem'    => &$aItem,
            'aScope'   => &$aScope
        );
        /**
         * Called after front data processing.
         *
         * @event      on_post_process_fields $modId
         * @eventparam string bodyType  Body type
         * @eventparam array  &aItem    Item data
         * @eventparam array  &aScope   Scope
         */
        AMI_Event::fire('on_post_process_fields', $aEvent, $modId);
    }

    /**
     * Generates navigation data.
     *
     * @param  array &$aItem  Item data
     * @param  array &$aData  List data
     * @return void
     */
    public function getNavDataCB(array &$aItem, array &$aData){
        $aNavData = array(
            'modId' => $this->getModId()
        ) + $this->aFrontScope;

        if(in_array($this->bodyType, array('cats', 'sticky_cats'))){
            $aNavData['id']            = null;
            $aNavData['id_sublink']    = null;
            $aNavData['catid']         = $aItem['id'];
            $aNavData['catid_sublink'] = isset($aItem['sublink']) ? $aItem['sublink'] : null;
        }else{
            $aNavData['catid'] = isset($aItem['cat_id']) ? $aItem['cat_id'] : null;
            $aNavData['catid_sublink'] = isset($aItem['cat_sublink']) ? $aItem['cat_sublink'] : null;
            if($this->bodyType == 'browse_items'){
                if(isset($aData['prev_id'])){
                    $aNavData['id'] = $aData['prev_id'];
                    $aNavData['id_sublink'] = $aData['prev_sublink'];
                    $aNavData = AMI_PageManager::applyNavData($aNavData);
                    $aData['prev_nav_data'] = $aNavData['nav_data'];
                }
                if(isset($aData['next_id'])){
                    $aNavData['id'] = $aData['next_id'];
                    $aNavData['id_sublink'] = $aData['next_sublink'];
                    $aNavData = AMI_PageManager::applyNavData($aNavData);
                    $aData['next_nav_data'] = $aNavData['nav_data'];
                }
            }
            // if($this->bodyType === 'details'){
                $aItem += AMI_PageManager::applyNavData($aNavData);
                $aNavData['cat_nav_data'] = $aItem['nav_data'];
                unset($aItem['nav_data']);
            // }
            $aNavData['id'] = isset($aItem['id']) ? $aItem['id'] : null;
            $aNavData['id_sublink'] = isset($aItem['sublink']) ? $aItem['sublink'] : null;
        }

        $aItem += AMI_PageManager::applyNavData($aNavData);
    }

    /**
     * Generates previous/next links.
     *
     * @param  array &$aItem  Item data
     * @param  array &$aData  List data
     * @return void
     */
    public function getPrevNextLinksCB(array &$aItem, array &$aData){
        static $header = '';

        if(empty($aData['prev_next_ready'])){
            if($aItem['id'] == (int)AMI_Registry::get('page/itemId', 0)){
                $aData['current_id'] = $aItem['id'];
                $this->getNavDataCB($aItem, $aData);
                if(isset($aData['prev_nav_data'])){
                    $aData['previos_link'] = $this->parseTpl('previous_link_set', $aData + array('header' => $header));
                }else{
                    $aData['previos_link'] = $this->parseTpl('previous_link_set_na', $aData);
                }
            }else{
                if(isset($aData['current_id'])){
                    $aData['next_id'] = $aItem['id'];
                    $aData['next_sublink'] = $aItem['sublink'];
                    $aData['prev_next_ready'] = true;
                    $this->getNavDataCB($aItem, $aData);
                    if(isset($aData['next_nav_data'])){
                        $header = isset($aItem['_header']) ? $aItem['_header'] : '';
                        $aData['next_link'] = $this->parseTpl('next_link_set', $aData + array('header' => $header));
                    }else{
                        $aData['next_link'] = $this->parseTpl('next_link_set_na', $aData);
                    }
                }else{
                    $aData['prev_id'] = $aItem['id'];
                    $aData['prev_sublink'] = $aItem['sublink'];
                    $aData['next_link'] = $this->parseTpl('next_link_set_na', $aData);
                }
            }
        }

        $header = isset($aItem['_header']) ? $aItem['_header'] : '';
    }

    /**
     * Appends splitter to list.
     *
     * @param  array &$aItem  Item data
     * @param  array &$aData  List data
     * @return void
     */
    public function getSplitterCB(array &$aItem, array &$aData){
        $row = isset($aItem['row_index']) ? $aItem['row_index'] : 0;
        $aItem['_cols'] = $this->aBrowser['cols'];
        if($row > 0){
            $splitterType = (($row % $this->aBrowser['cols']) == 0 ? 'Vsplitter' : 'Hsplitter');
            $setName = $this->tplBlockName . ':' . $this->aTplData['prefix']['splitter'] . $splitterType;
            if($this->bodyType == 'small' && $this->oTpl->issetSet($this->tplBlockName . ':' . 'small_' . $this->aTplData['prefix']['splitter'] . $splitterType)){
                $setName = $this->tplBlockName . ':' . 'small_' . $this->aTplData['prefix']['splitter'] . $splitterType;
            }
            $aData['list'] .= $this->oTpl->parse($setName, $aItem);
        }
    }

    /**
     * Fills empty description.
     *
     * @param  array &$aItem     Item data
     * @param  bool  $isCat      Specifies to use field 'cat_' prefix / module '_cat' postfix
     * @param  bool  $usePrefix  Specifies to use field 'cat_' prefix
     * @return void
     */
    public function fillEmptyDescription(array &$aItem, $isCat = FALSE, $usePrefix = TRUE){
        $fieldPrefix = $usePrefix && $isCat ? 'cat_' : '';
        $modIdPostfix = $isCat ? '_cat' : '';
        if(
            isset($aItem[$fieldPrefix . 'announce']) &&
            $aItem[$fieldPrefix . 'announce'] !== '' &&
            AMI::issetAndTrueOption($this->getModId() . $modIdPostfix, 'fill_empty_description') &&
            mb_strlen(trim(strip_tags($aItem[$fieldPrefix . 'announce']))) &&
            !mb_strlen(trim(strip_tags($aItem[$fieldPrefix . 'body'], '<img><span>')))
        ){
            $aItem[$fieldPrefix . 'body'] = $aItem[$fieldPrefix . 'announce'];
        }
    }

    /**
     * Applies simple sets to specified simple items on frontend.
     *
     * @param  array &$aItem  Item data
     * @param  array &$aData  List data
     * @return void
     */
    public function applySimpleSetsCB(array &$aItem, array &$aData){
        foreach($this->aSimpleSetFields as $field){
            $aItem['_' . $field] = isset($aItem[$field]) ? $aItem[$field] : null;
            if(isset($aItem[$field]) && $aItem[$field] !== ''){
                $aItem[$field] = $this->oTpl->parse(
                    $this->tplBlockName . ':' . $this->aTplData['prefix']['simpleField'] . $field,
                    $aItem
                );
                // AMI_Registry::set('AMI/Module/Environment/Template/Scope/simpleset_' . $field, $aItem[$field]);
            }
        }
    }

    /**
     * Fill the item fields.
     *
     * @param  array &$aItem  Item data
     * @param  array &$aData  List data
     * @return void
     */
    public function drawItemCB(array &$aItem, array &$aData){
        // $aItem['date'] = isset($aItem['date_created']) ? $aItem['date_created'] : '';
        // $aItem['date'] = $this->parseSet('item', 'date', $aItem);
        if(!empty($aItem['body_notempty'])){
            $aItem['more'] = $this->parseSet('item', 'more', $aItem);
        }
    }

    /**
     * Returns module id.
     *
     * @return string
     */
    protected function getModId(){
        return $this->modId;
    }

    /**
     * Sets up template data.
     *
     * @param  string $prefix    Sets prefix
     * @param  array  $aTplData  Temlate data overriding defaults
     * @return void
     */
    protected function setupTplData($prefix, array $aTplData = array()){
        $this->aTplData =
            $aTplData +
            array(
                'prefix' => array(
                    'simpleField' => $prefix,
                    'splitter'    => $prefix,
                    'stub'        => $prefix
                ),
                'set' => array(
                    'body'           => 'body_' . $this->bodyType,
                    'body_filtered'  => 'body_filtered'
                ),
            );
        switch($this->bodyType){
            case 'items':
            case 'browse_items':
            case 'sticky_items':
            case 'subitems':
            case 'cats':
            case 'sticky_cats':
            case 'small':
                $this->aTplData['set'] += array(
                    'row'            => $prefix . 'row',
                    'list'           => $prefix . 'list',
                    'empty_list'     => $prefix . 'list_empty',
                    'list_not_found' => $prefix . 'list_not_found',
                    'cat_link'       => $prefix . 'cat_link'
                );
                break;
            case 'details':
                $this->aTplData['set'] += array(
                    'details'       => 'item_details',
                    'empty_details' => 'body_empty',
                    'cat_link'      => $prefix . 'cat_link'
                );
                break;
        }
        if($this->bodyType == 'browse_items'){
            $this->aTplData['set'] += array(
                'previous_link_set'    => $prefix . 'previous_link',
                'previous_link_set_na' => $prefix . 'previous_link_na',
                'next_link_set'        => $prefix . 'next_link',
                'next_link_set_na'     => $prefix . 'next_link_na'
            );
        }
    }

    /**
     * Sets up browsing according to options/state.
     *
     * @return void
     */
    protected function setupList(){
        $modId = $this->getModId();

        $this->aBrowser['useSpecView'] = AMI::issetAndTrueProperty($modId, 'use_special_list_view');
        $colsOption = in_array($this->bodyType, array('cats', 'sticky_cats')) ? 'body_cats_cols' : 'cols';
        if($this->bodyType == 'small'){
            $colsOption = 'body_small_cols';
        }elseif($this->bodyType == 'browse_items'){
            $colsOption = 'body_browse_cols';
        }
        // Init columns number value
        if(AMI::issetOption($modId, $colsOption)){
            $this->aBrowser['cols'] = AMI::getOption($modId, $colsOption);
            if($this->aBrowser['cols'] < 1){
                $this->aBrowser['cols'] = 1;
            }
        }else{
            $this->aBrowser['cols'] = 1;
        }
        // Init browser options
        $isCatsMode = in_array($this->bodyType, array('cats', 'sticky_cats'));
        if($isCatsMode){
            $modId .= '_cat';
        }
        $this->aBrowser['orderColumn'] = AMI::getOption($modId, 'front_page_sort_col');
        $this->aBrowser['orderDirection'] = AMI::getOption($modId, 'front_page_sort_dim');
        if($this->bodyType == 'browse_items'){
            $this->aBrowser['pageSize'] = AMI::getOption($modId, 'body_browse_page_size');
        }else{
            $this->aBrowser['pageSize'] = AMI::getOption($modId, 'page_size');
        }
        if(
            !in_array($this->bodyType, array('cats', 'sticky_cats')) &&
            AMI_Registry::get('AMI/Module/Environment/Filter/active')
        ){
            $this->aBrowser['pageSize'] = AMI::getOption($modId, 'body_filtered_page_size');
        }
        // Init limit parameters
        $offsetPrefix = $isCatsMode ? 'cat' : '';
        if($this->aBrowser[$offsetPrefix . 'offset'] > 0){
            $this->aBrowser['listStart'] = $this->aBrowser[$offsetPrefix . 'offset'];
            if($this->aBrowser['pageSize'] > 0){
                $this->aBrowser['listLimit'] = $this->aBrowser['pageSize'];
            }
        }elseif($this->aBrowser['pageSize'] > 0){
            $this->aBrowser['listStart'] = 0;
            $this->aBrowser['listLimit'] = $this->aBrowser['pageSize'];
        }
    }

    /**
     * Sets body type.
     *
     * @param  string $bodyType  Page body type
     * @return AMI_ModCommonViewFrn
     */
    public function setBodyType($bodyType){
        $this->bodyType = $bodyType;
        return $this;
    }

    /**
     * Outputs data using specified response type.
     *
     * @param array $aData  Module data
     * @param string $type  Response type
     * @return void
     */
    public function outType(array $aData, $type = 'json'){
        $oResponse = AMI::getSingleton('response');

        $aEvent = array(
            'aData' => &$aData,
            'type'  => $type,
            'skip'  => true
        );
        /**
         * Allows to alternate specified response type data before module output.
         *
         * @event      on_module_{$type}_response_type $modId
         * @eventparam array aData  Module data
         * @eventparam string type  Response type
         * @eventparam bool skip    Skip this response type (default: true)
         */
        AMI_Event::fire("on_module_{$type}_response_type", $aEvent, $this->getModId());
        if(isset($aEvent['skip']) && $aEvent['skip']){
            return;
        }

        switch($type){
            case 'json':
                unset($aData['captcha_row'], $aData['captcha_script']);
                $oResponse->setType('JSON');
                if(!$oResponse->isStarted()){
                    $oResponse->start();
                }
                $oResponse->write($aData);
                break;
            case 'item_list':
            case 'item_details':
                $oResponse->setType('HTML');
                if(!$oResponse->isStarted()){
                    $oResponse->start();
                }
                $oResponse->write(isset($aData[$type]) ? $aData[$type] : '');
                break;
            default:
                return;
        }
        $oResponse->send();
        die;
    }
}
