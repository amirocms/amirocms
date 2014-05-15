<?php
/**
 * AmiCatalog/Items configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiCatalog_Items
 * @version   $Id: AmiCatalog_Items_Adm.php 45478 2013-12-17 11:52:15Z Kolesnikov Artem $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiCatalog/Items configuration admin action controller.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_Items_Adm extends Hyper_AmiCatalog_Adm{
    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request object
     * @param AMI_Response $oResponse  Response object
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){

        AMI_Registry::set('oldenv_inside_newenv', TRUE);

        // Replace category extension by eshop category extension
        AMI::addResource('ext_category/module/controller/adm', 'AmiExt_EshopCategory_Adm');

        AMI_Event::addHandler('v5_on_adm_form_requested', array($this, 'handleOldEnvFormRequest'), AMI_Event::MOD_ANY);

        // Initialize registry by supported product types (adm fast env only)
        $oRequest = AMI::getSingleton('env/request');
        $aProductTypes = array_filter(
            AMI::getOption($this->getModId(), 'available_item_types'),
            array($oRequest->get('ami_full', FALSE) ? $GLOBALS['Core'] : AMI::getSingleton('core'), 'isInstalled')
        );

        // Use filter selected category in list if not set
        $filterCatId = AMI_Filter::getFieldValue('category', 0);
        if(($oRequest->get('mod_action', FALSE) == 'list_view') && ($oRequest->get('category', FALSE) === FALSE) && $filterCatId){
            $oRequest->set('category', $filterCatId);
        }
        // Same for the form
        if(($oRequest->get('mod_action', FALSE) == 'form_edit') && ($oRequest->get('cat_id', FALSE) === FALSE) && $filterCatId){
            $oRequest->set('cat_id', $filterCatId);
        }

        AMI_Registry::set('AMI/Runtime/' . $this->getModId() . '/product_types', $aProductTypes);

        parent::__construct($oRequest, $oResponse);
        if($oRequest->get('mode', '') == 'popup'){
            $this->removeComponents(array('form'));
        }
    }

    /**
     * Handles request to old environment.
     *
     * @param string $name          Event name
     * @param array $aEvent         Event data
     * @param string $handlerModId  Handler module id
     * @param string $srcModId      Source module id
     * @return array
     */
    public function handleOldEnvFormRequest($name, array $aEvent, $handlerModId, $srcModId){
        $aRequest = $aEvent['aRequest'];
        if(isset($aRequest['mod_action']) && ($aRequest['mod_action'] == 'form_edit')){
            if((!isset($aRequest['action']) || (isset($aRequest['action']) && ($aRequest['action'] == 'none'))) && isset($aRequest['id']) && ($aRequest['id'])){
                $aEvent['aRequest']['action'] = 'edit';
                $aEvent['aGet']['action'] = 'edit';
            }
        }
        // Big ugly workaround
        $oRequest = AMI::getSingleton('env/request');
        $aPostData = $_POST;
        if(is_array($aPostData) && count($aPostData)){
            $aEvent['aPost'] = $aPostData;
        }
        if($oRequest->get('mod_action', FALSE) === 'list_oldenv'){

            $aGetData = $_GET;
            if(is_array($aGetData) && count($aGetData)){
                $aEvent['aRequest'] = $GLOBALS['adm']->_PrepareVars($aGetData);
                $aEvent['aGet'] = $aGetData;
                if($oRequest->get('action', FALSE) === 'ss_apply'){
                    $aEvent['aPost'] = $aGetData;
                }
            }
            // Small ugly workaround
            if(isset($aEvent['aGet']['mod_action_id'])){
                $aEvent['aRequest']['id'] = $aEvent['aGet']['mod_action_id'];
                $aEvent['aGet']['id'] = $aEvent['aGet']['mod_action_id'];
            }
        }
        return $aEvent;
    }
}

/**
 * AmiCatalog/Items configuration model.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_Items_State extends Hyper_AmiCatalog_State{
}

/**
 * AmiCatalog/Items configuration admin filter component action controller.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_Items_FilterAdm extends Hyper_AmiCatalog_FilterAdm{
    /**
     * Adds id_source field.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleFilterInit($name, array $aEvent, $handlerModId, $srcModId){
        $section = AMI_ModDeclarator::getInstance()->getSection($srcModId);
        if($section == 'eshop'){
            $aEvent['oFilter']->addViewField(
                array(
                    'name'          => 'id_source',
                    'type'          => 'checkbox',
                    'flt_default'   => '0',
                    'flt_condition' => '>=',
                    'act_as_int'    => TRUE,
                    'disable_empty' => TRUE
                )
            );
        }
        return parent::handleFilterInit($name, $aEvent, $handlerModId, $srcModId);
	}
}

/**
 * AmiCatalog/Items configuration item list component filter model.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_Items_FilterModelAdm extends Hyper_AmiCatalog_FilterModelAdm{
    /**
     * Common filter fields
     *
     * @var array
     */
    protected $aCommonFields = array('header');

    /**
     * Constructor.
     */
    public function __construct(){
        // Add product types selectbox if > 1
        $aProductTypes = AMI_Registry::get('AMI/Runtime/' . AMI_Registry::get('modId') . '/product_types');
        if(sizeof($aProductTypes) > 1){
            $aData = array();
            foreach($aProductTypes as $type){
                $aData[] = array(
                    'caption' => 'product_type_' . $type,
                    'value'   => $type
                );
            }
            $this->addViewField(
                array(
                    'name'          => 'product_type',
                    'type'          => 'select',
                    'flt_type'      => 'select',
                    'flt_condition' => '=',
                    'flt_column'    => 'item_type',
                    'flt_default'   => '',
                    'data'          => $aData,
                    'not_selected'  => array('id' => '', 'caption' => 'all')
                )
            );
        }

        parent::__construct();

        $this->addViewField(
            array(
                'name'          => 'sku',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like'
            )
        );
        $this->addViewField(
            array(
                'name'          => 'id_external',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like'
            )
        );
    }
}

/**
 * AmiCatalog/Items configuration admin filter component view.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_Items_FilterViewAdm extends Hyper_AmiCatalog_FilterViewAdm{
    /**
     * Filter default elemnts template (placeholders)
     *
     * @var array
     */
    protected $aPlaceholders = array(
        '#filter',
            'product_type', 'category', 'datefrom', 'dateto', 'header', 'sku', 'id_external', 'id_source', 'sticky',
        'filter'
    );

    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        $aScope = array(
            'cat_id'          => (int)AMI_Filter::getFieldValue('category', 0),
            'path_strip_text' => AMI::getOption($this->getModId(), 'path_settings_strip_text')
        );
        list(
            $aScope['path_max_length'],
            $aScope['cat_max_length'],
            $aScope['cat_start_qty'],
            $aScope['cat_end_qty']
        ) = AMI::getOption($this->getModId(), 'path_settings');
        $aScope += $this->aScope;
        $this->aScope['path'] = $this->parse('path_all', $aScope);
        $this->addScriptCode($this->parse('javascript', $aScope));

        return parent::init();
    }

    /**
     * Sets path scope variable displaying under filter form.
     *
     * @return void
     */
    protected function setPath(){
        $this->aScope['path'] = $this->parse('path_all');
    }
}

/**
 * AmiCatalog/Items configuration admin form component action controller.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_Items_FormAdm extends AMI_ModFormOldEnvAdm{
    /**
     * Save action dispatcher.
     *
     * @param  array &$aEvent  Event data
     * @return void
     */
    protected function _save(array &$aEvent){
        $id = AMI::getSingleton('env/request')->get('id');
        $oRequest = AMI::getSingleton('env/request');
        $returnType = $oRequest->get('return_type', 'current');
        if($id){
            $oRequest->set('applied_id', $id);
        }
        /**
         * Processing controller actions of the AMI_Mod module.
         *
         * @event      dispatch_mod_action_form_edit $modId
         * @eventparam string modId  Module id  ---
         * @eventparam AMI_Mod|null oController  Module controller object
         * @eventparam string tableModelId  Table model resource id
         * @eventparam AMI_Request oRequest  Request object
         * @eventparam AMI_Response oResponse  Response object
         */
        AMI_Event::fire('dispatch_mod_action_form_edit', $aEvent, $this->getModId());
    }    
}

/**
 * AmiCatalog/Items configuration form component view.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_Items_FormViewAdm extends AMI_ModFormOldEnvViewAdm{
    /**
     * Returns view data.
     *
     * @return array|string
     */
    public function get(){
        $aModParts = explode('_', $this->getModId());
        $owner = $aModParts[0];
        $this->formModId = $owner . '_item';
        $this->addScriptFile('_admin/skins/vanilla/_js/eshop_item_form.js');
        $this->aScope = $this->getScope($this->viewType);
        $this->aScope['scripts'] = $this->getScripts($this->aScope);
        $this->aScope['form_html'] = parent::get();
        
        $this->oTpl->setBlockLocale($this->tplBlockName, $this->aLocale);
        $html = $this->oTpl->parse($this->tplBlockName . ':section_form', $this->aScope);
        $oResponse = AMI::getSingleton('response');
        if($oResponse->getType() == 'JSON'){
            $oRequest = AMI::getSingleton('env/request');
            return array(
                'id'        => is_object($this->oItem) ? $this->oItem->getId() : '',
                'appliedId' => $oRequest->get('applied_id'),
                'htmlCode'  => $html
            );
        }else{
            return $html;
        }
    }

    /**
     * Returns prepared view scope.
     *
     * @param  string $type   View type
     * @param  array $aScope  Scope
     * @return array
     */
    protected function getScope($type, array $aScope = array()){
        $this->owner = preg_replace('/_.*$/', '', $this->getModId());

        $aScope['flags'] = AMI::getOption($this->owner . '_home', 'num_extra_special_flags');
        $this->addScriptCode('var flagsCount = ' . (int)$aScope['flags']);
        return parent::getScope($type, $aScope);
    }
}

/**
 * AmiCatalog/Items configuration admin list component action controller.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_Items_ListAdm extends Hyper_AmiCatalog_ListAdm{
    /**
     * Owner
     *
     * @var  string
     */
    protected $owner;

    /**
     * Supported product type filter
     *
     * @var DB_Snippet
     */
    protected $oProductTypeFilter;

    /**
     * Initialization.
     *
     * @return AmiCatalog_Items_ListAdm
     */
    public function init(){
        $this->owner = preg_replace('/_.*$/', '', $this->getModId());
        $this->listActionsResId = $this->getModId() . '/list_actions/controller/adm';
        $this->prepareProductTypeFilter();

        $filterCatId = AMI_Filter::getFieldValue('category', 0);
        if(!$filterCatId && $this->owner == 'eshop'){
            /**
             * @var AMI_Eshop
             */
            $oEshop = AMI::getSingleton('eshop');
            $aColumns = $oEshop->getOtherPricesColumns('', null);
            if(sizeof($aColumns)){
                $this->getModel()->setActiveDependence('cat');
                $this->addJoinedColumns($aColumns, 'cat');
            }
        }

        parent::init();

        $this->addColActions(array(self::REQUIRE_FULL_ENV . 'flags'), TRUE);

        $oView = $this->getGroupActionsView();
        $oView->putPlaceholder('flags_section', 'common_section.end', TRUE);

        $aGrpActions = array();
        $aGrpActions[] = array(self::REQUIRE_FULL_ENV . 'flags', 'flags_section');
        if(!AMI::getOption($this->owner . '_home', 'num_extra_special_flags')){
            $aGrpActions[] = array(self::REQUIRE_FULL_ENV . 'unflags', 'flags_section');
        }
        $aGrpActions[] = array(self::REQUIRE_FULL_ENV . 'id_cat', 'id_cat_section');
        $this->addGroupActions($aGrpActions);
        $this->addActionCallback('group', 'grp_id_cat');
        $this->addActionCallback('common', 'oldenv');

        return $this;
    }

    /**
     * Event handler.
     *
     * Adds filter by product type.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function handleQueryAddTable($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['oQuery']->addWhereDef($this->oProductTypeFilter);
        return $aEvent;
    }

    /**
     * Prepares product type filetr.
     *
     * @return void
     */
    protected function prepareProductTypeFilter(){
        $aProductTypes = AMI_Registry::get('AMI/Runtime/' . $this->getModId() . '/product_types');

        if(!sizeof($aProductTypes)){
            $aProductTypes = array($this->owner . '_fake_type');
        }

        $sql = "AND `item_type` IN (";
        foreach($aProductTypes as $type){
            $sql .= "%s,";
        }
        $sql = mb_substr($sql, 0, -1) . ')';
        $this->oProductTypeFilter = DB_Query::getSnippet($sql);
        foreach($aProductTypes as $type){
            $this->oProductTypeFilter->q($type);
        }

        AMI_Event::addHandler('on_query_add_table', array($this, 'handleQueryAddTable'), $this->getModId());
    }
}

/**
 * AmiCatalog/Items configuration admin list component view.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_Items_ListViewAdm extends Hyper_AmiCatalog_ListViewAdm{
    /**
     * Flag specifying that class is servant
     *
     * @var bool
     */
    protected $isServant = FALSE;

    /**
     * Category extra prices data
     *
     * @var array
     */
    protected $aExtraPriceData;

    /**
     * Owner
     *
     * @var  string
     */
    protected $owner;

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return EshopItem_ListViewAdm
     */
    public function init(){
        $this->owner = preg_replace('/_.*$/', '', $this->getModId());

        /**
         * @var AMI_RequestHTTP
         */
        $oRequest = AMI::getSingleton('env/request');

        /**
         * @var AMI_Eshop
         */
        $oEshop = AMI::getSingleton('eshop');

        $this->addColumnType('data', 'none');

        parent::init();

        $this
            ->addColumnType('date_created', 'date')
            ->setColumnClass('date_created', 'td_small_text');
        $section = AMI_ModDeclarator::getInstance()->getSection($this->getModId());
        if($section == 'eshop'){
            $this
                ->addColumn('price', 'announce.after')
                ->addColumnType('price', 'float')
                ->addColumnType('id_source', 'hidden')
                ->addSortColumns(array('price'));
        }
        $skuColumnPresent = AMI::issetAndTrueOption($this->getModId(), 'show_sku_column');
        if($skuColumnPresent){
            $this
                ->addColumn('sku', 'header.after')
                ->setColumnWidth('sku', 'extra-narrow')
                ->addSortColumns(array('sku'));
        }
        $this->addSortColumns(array('flags'));

        // Load category extra price data
        $catId = (int)AMI_Filter::getFieldValue('category', 0);

        if($this->owner == 'eshop'){
            foreach($oEshop->getOtherPricesColumns('', null) as $column){
                $this->addColumnType($column, $this->isServant ? 'none' : 'hidden');
            }
            if(!$catId){
                foreach($oEshop->getOtherPricesColumns(null) as $column){
                    $this->addColumnType($column, 'none');
                }
            }

            if(in_array($this->owner . '_digitals', AMI_Registry::get('AMI/Runtime/' . $this->getModId() . '/product_types'))){
                $this->addColumnType('num_files', 'int')->setColumnWidth('num_files', 'extra-narrow');
                // Format 'date_created' column in local date format
                $this->formatColumn(
                    'num_files',
                    array($this, 'fmtNumFiles')
                );
            }
        }

        // Format 'date_created' column in local date format
        $this->formatColumn(
            'date_created',
            array($this, 'fmtDateTime'),
            array(
                'format' => AMI_Lib_Date::FMT_DATE
            )
        );
        // Truncate 'header' column by 255 symbols
        $this->formatColumn(
            'header',
            array($this, 'fmtTruncate'),
            array(
                'length'       => 255,
                'doStripTags'  => TRUE,
                'doHTMLEncode' => FALSE
            )
        );
        // Truncate 'announce' column by 50 symbols
        $this->formatColumn(
            'announce',
            array($this, 'fmtTruncate'),
            array(
                'length'       => 80,
                'doStripTags'  => TRUE,
                'doHTMLEncode' => FALSE
            )
        );

        if($this->owner == 'eshop'){

            // Format 'price' column
            $this->formatColumn(
                'price',
                array($this, 'fmtPrice')
            );

            if($oEshop->areOtherPricesEnabled()){
                $this
                    ->addColumn('other_prices', 'price.after')
                    ->addColumnType('other_prices', 'float');
                // Format 'other_prices' column
                $this->formatColumn(
                    'other_prices',
                    array($this, 'fmtOtherPrices')
                );
            }

            $this->aExtraPriceData = array();

            if(empty($this->isServant) && $catId > 0){

                $this->addColumnType('price0', 'hidden');
                AMI_Event::addHandler('on_list_body_{price0}', array($this, 'handlePriceUnformatted'), $this->getModId());

                $this->addColumnType('header_unformatted', 'hidden');
                AMI_Event::addHandler('on_list_body_{header_unformatted}', array($this, 'handleHeaderUnformatted'), $this->getModId());

                if(!$skuColumnPresent){
                    $this->addColumnType('sku', 'hidden');
                }
                $this->addColumnType('rest', 'hidden');
                $this->addColumnType('price_mask', 'hidden');

                /**
                 * Category submodule resource id
                 *
                 * @var string
                 */
                $catResId = $this->oModel->getDependenceResId('cat');

                /**
                 * @var AMI_ModTable
                 */
                $oTable = AMI::getResourceModel($catResId . '/table');
                $aPrices = $oEshop->getOtherPricesColumns(null, '');
                $aColumns = $aPrices;
                $aColumns[] = 'id';

                foreach($oEshop->getOtherPrices() as $num){
                    $aColumns[] = 'price_caption' . $num;
                }

                $oItem = $oTable->find($catId, $aColumns);
                if($oItem->id){
                    $aData = $oItem->getData();
                    foreach($oEshop->getOtherPrices() as $num){
                        $listColumn = 'price' . $num;
                        $this->aExtraPriceData[$listColumn] = '=' . $aData[$listColumn];
                        $this->aLocale['list_col_' . $listColumn] = $aData['price_caption' . $num];
                    }
                    if(sizeof($this->aExtraPriceData)){
                        AMI_Event::addHandler('on_list_view', array($this, 'handleListView'), $this->getModId());
                    }
                }
            }
            $currencyJS = '';
            $aCurrencies = $oEshop->getCurrencies();
            foreach($aCurrencies as $code => $aData){
                $currencyJS .= strlen($currencyJS) ? ', ' : '';
                $currencyJS .= $code . ':' . (float)$aData['exchange'];
            }
            $aJSVars = array(
                'currencyJS'    => $currencyJS,
                'baseCurrency'  => $oEshop->getBaseCurrency(),
                'maxRows'       => AMI::getOption($this->getModId(), 'excel_list_rows'),
                'isPopup'       => AMI::getSingleton('env/request')->get('mode', false) == 'popup'
            );
            $aJSVars += $this->aScope;
            $this->addScriptCode($this->parse('javascript', $aJSVars));
        }

        // } Load category extra price data

        return $this;
    }

    /**
     * Event handler.
     *
     * Adds category extra prices data to the response.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function handleListView($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['aResponse']['extra'] = $this->aExtraPriceData;
        // $aEvent['aResponse']['flagsQty'] = AMI::getOption($this->owner . '_home', 'num_extra_special_flags');
        return $aEvent;
    }

    /**#@+
     * Column formatter.
     *
     * @see AMI_ModListView::handleFormatCell()
     */

    /**
     * Prepare unformatted price field (price0).
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::get()
     */
    public function handlePriceUnformatted($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['aScope']['list_col_value'] = $aEvent['aScope']['price'];
        return $aEvent;
    }

    /**
     * Prepare unformatted header field (header_unformatted).
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::get()
     */
    public function handleHeaderUnformatted($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['aScope']['list_col_value'] = $aEvent['aScope']['header'];
        return $aEvent;
    }

    /**
     * Formats num_files column value.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return int | string
     */
    protected function fmtNumFiles($value, array $aArgs){
        return
            $value
            ? $value
            : '';
    }

    /**
     * Formats price column value.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     */
    protected function fmtPrice($value, array $aArgs){
        $oTpl = $this->getTemplate();
        if(is_null($value)){
            $value = $oTpl->parse($this->tplBlockName . ':price_empty');
        }else{
            $value = AMI::getSingleton('eshop')->formatNumber($value, FALSE);
        }
        $value = $oTpl->parse($this->tplBlockName . ':price', array('price' => $value));
        return $value;
    }

    /**
     * Formats other prices column value.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     */
    protected function fmtOtherPrices($value, array $aArgs){
        /**
         * @var AMI_Eshop
         */
        $oEshop = AMI::getSingleton('eshop');
        /**
         * @var AMI_ModTableItem
         */
        $oItem = $aArgs['oItem'];
        $oTpl = $this->getTemplate();
        $value = '';
        $aPrices = $oEshop->getOtherPrices();
        $useExtraPriceData = sizeof($this->aExtraPriceData) > 0;
        foreach($aPrices as $num){
            $price = $oItem->getValue('price' . $num);
            if($price != ''){
                $currency = '';
                $storageCurrency = ''; // db_currency
                $catPrice = $useExtraPriceData ? $this->aExtraPriceData['price' . $num] : $oItem->getValue('cat_price' . $num);
                if($catPrice != ''){
                    foreach(array(
                        '#' => 'currency',
                        ':' => 'storageCurrency'
                    ) as $marker => $varName){
                        $pos = mb_strpos($catPrice, $marker);
                        if($pos !== FALSE){
                            $$varName = mb_substr($catPrice, $pos + 1, 3);
                        }
                    }
                }
                if($storageCurrency != '' && $oEshop->issetCurrency($storageCurrency)){
                    $currency = $storageCurrency;
                }else{
                    $price = $oEshop->convertCurrency($price, '-', $currency);
                }

                $this->onExtraPrice($oItem, $num, $price, $currency);

                $price =
                    $currency ?
                    $oEshop->formatMoney($price, $currency, TRUE, FALSE)
                    : $oTpl->parse($this->tplBlockName . ':price_empty');
            }else{
                $price = $oTpl->parse($this->tplBlockName . ':price_empty');
            }
            $value .= $price . $oTpl->parse($this->tplBlockName . ':extra_price_separator');
        }

        return $value;
    }

    /**#@-*/

    /**
     * Other price callback to improve functionality.
     *
     * @param  AMI_ModTableItem $oItem     Product
     * @param  int              $num       Price number
     * @param  float            $price     Price value
     * @param  string           $currency  Currency code
     * @return void
     */
    protected function onExtraPrice(AMI_ModTableItem $oItem, $num, $price, $currency){
    }
}

/**
 * AmiCatalog/Items configuration module admin list actions controller.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_Items_ListActionsAdm extends Hyper_AmiCatalog_ListActionsAdm{
    /**
     * Dispatches old environment action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchOldenv($name, array $aEvent, $handlerModId, $srcModId){
        // Execute old environment action
        AMI_OldEnv::processAction($handlerModId);
        $this->refreshView();
        return $aEvent;
    }
}

/**
 * AmiCatalog/Items configuration module admin list group actions controller.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_Items_ListGroupActionsAdm extends Hyper_AmiCatalog_ListGroupActionsAdm{
}
