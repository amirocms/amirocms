<?php
/**
 * AmiEshopDiscounts/Discounts configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiEshopDiscounts_Discounts
 * @version   $Id: AmiEshopDiscounts_Discounts_Adm.php 42473 2013-10-22 00:21:46Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiEshopDiscounts/Discounts configuration admin action controller.
 *
 * @package    Config_AmiEshopDiscounts_Discounts
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopDiscounts_Discounts_Adm extends Hyper_AmiEshopDiscounts_Adm{
    /**
     * Change publish status for child discounts.
     *
     * @param int $idParent  Parent discount ID
     * @param int $status  Parent discount status
     *
     * @return void
     */
    public function changeChildDiscounts($idParent = 0, $status = 0){
        AMI::getSingleton('db')->query(
            DB_Query::getUpdateQuery(
                'cms_es_discounts',
                array('public'  => intval($status)),
                DB_Query::getSnippet('WHERE id_parent = %s')->q($idParent)
            )
        );
    }
}

/**
 * AmiEshopDiscounts/Discounts configuration model.
 *
 * @package    Config_AmiEshopDiscounts_Discounts
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopDiscounts_Discounts_State extends Hyper_AmiEshopDiscounts_State{
}

/**
 * AmiEshopDiscounts/Discounts configuration admin filter component action controller.
 *
 * @package    Config_AmiEshopDiscounts_Discounts
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopDiscounts_Discounts_FilterAdm extends Hyper_AmiEshopDiscounts_FilterAdm{
}

/**
 * AmiEshopDiscounts/Discounts configuration item list component filter model.
 *
 * @package    Config_AmiEshopDiscounts_Discounts
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopDiscounts_Discounts_FilterModelAdm extends Hyper_AmiEshopDiscounts_FilterModelAdm{
    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();

        $oRequest = AMI::getSingleton('env/request');

        // Popup for creating/editing discounts from eshop category module
        $isPopup = $oRequest->get('popup', FALSE);

        // Popup for selecting discount from eshop category module
        $isCategoryPopup = $oRequest->get('category_id', FALSE);

        if($isPopup){
            $this->addViewField(
                array(
                    'name'          => 'popup',
                    'type'          => 'hidden',
                    'flt_type'      => 'hidden',
                    'flt_default'   => '1',
                    'flt_condition' => '=',
                )
            );
        }
        $this->addViewField(
            array(
                'name'          => 'id_parent',
                'type'          => 'hidden',
                'flt_type'      => 'hidden',
                'flt_default'   => '0',
                'flt_condition' => '=',
                'flt_column'    => 'id_parent'
            )
        );
        $this->addViewField(
            array(
                'name'          => 'public',
                'type'          => 'checkbox',
                'flt_default'   => '0',
                'flt_condition' => '>=',
                'act_as_int'    => TRUE,
                'disable_empty' => TRUE
            )
        );
        $this->addViewField(
            array(
                'name'          => 'kind',
                'type'          => 'select',
                'flt_type'      => 'select',
                'flt_default'   => '',
                'flt_condition' => '=',
                'data'          => array(
                    array('caption' => 'kind_single', 'value' => 'single'),
                    array('caption' => 'kind_coupon', 'value' => 'coupon'),
                    array('caption' => 'kind_accumulative', 'value' => 'accumulative'),
                    array('caption' => 'kind_mixed', 'value' => 'mixed')
                ),
                'not_selected'  => array('id' => '', 'caption' => 'all')
            )
        );
        $this->addViewField(
            array(
                'name'          => 'type',
                'type'          => 'select',
                'flt_type'      => 'select',
                'flt_default'   => '',
                'flt_condition' => '=',
                'data'          => array(
                    array('caption' => 'type_abs', 'value' => 'abs'),
                    array('caption' => 'type_percent', 'value' => 'percent')
                ),
                'not_selected'  => array('id' => '', 'caption' => 'all')
            )
        );

        $aConditions = array();
        if($isPopup && !$isCategoryPopup){
            $aConditions[] = array('caption' => 'all', 'value' => 'all');
        }elseif(!$isCategoryPopup){
            $aConditions[] = array('caption' => 'condition_global', 'value' => 'global');
        }
        $aConditions[] = array('caption' => 'condition_category', 'value' => 'category', 'selected' => ($isPopup && !$isCategoryPopup && !$oRequest->get('condition', FALSE)) ? 'selected' : '');
        $aConditions[] = array('caption' => 'condition_total', 'value' => 'total');
        $aConditions[] = array('caption' => 'condition_items_count', 'value' => 'items_count');
        $aConditionField = array(
            'name'          => 'condition',
            'type'          => 'select',
            'flt_type'      => 'select',
            'flt_default'   => $isPopup ? 'category' : '',
            'flt_condition' => '=',
            'data'          => $aConditions,
        );
        if($isCategoryPopup){
            $aConditionField['flt_default'] = '';
        }
        if(!$isPopup || $isCategoryPopup){
            $aConditionField['not_selected'] = array('id' => '', 'caption' => 'all');
        }
        $this->addViewField($aConditionField);
    }

    /**
     * Patches filter conditions.
     *
     * @param string $field  Field name
     * @param array $aData  Filter data
     * @return array
     * @amidev Temporary
     */
    protected function processFieldData($field, array $aData){
        switch($field){
            case 'popup':
                $aData['forceSQL'] = " AND i.condition != 'global'";
                break;
            case 'condition':
                $oRequest = AMI::getSingleton('env/request');
                if($oRequest->get('popup', FALSE)){
                    if($oRequest->get('condition', FALSE) == 'all'){
                        $aData['forceSQL'] = '';
                    }elseif($oRequest->get('condition', FALSE)){
                        $aData['forceSQL'] = " AND i.condition = '".$this->prepareSqlField('condition', $oRequest->get('condition', FALSE), 'text')."'";
                    }
                }
                break;
        }
        return $aData;
    }
}

/**
 * AmiEshopDiscounts/Discounts configuration admin filter component view.
 *
 * @package    Config_AmiEshopDiscounts_Discounts
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopDiscounts_Discounts_FilterViewAdm extends Hyper_AmiEshopDiscounts_FilterViewAdm{
    /**
     * Filter default elemnts template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#filter',
            'datefrom', 'dateto', 'public', 'kind', 'type', 'header', 'condition',
        'filter'
    );
}

/**
 * AmiEshopDiscounts/Discounts configuration admin form component action controller.
 *
 * @package    Config_AmiEshopDiscounts_Discounts
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopDiscounts_Discounts_FormAdm extends Hyper_AmiEshopDiscounts_FormAdm{
}

/**
 * AmiEshopDiscounts/Discounts configuration form component view.
 *
 * @package    Config_AmiEshopDiscounts_Discounts
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopDiscounts_Discounts_FormViewAdm extends Hyper_AmiEshopDiscounts_FormViewAdm{
    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        return parent::init();
    }
}

/**
 * AmiEshopDiscounts/Discounts configuration admin list component action controller.
 *
 * @package    Config_AmiEshopDiscounts_Discounts
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopDiscounts_Discounts_ListAdm extends Hyper_AmiEshopDiscounts_ListAdm{
    /**
     * List actions controller resource id
     *
     * @var string
     */
    protected $listActionsResId = 'eshop_discounts/list_actions/controller/adm';

    /**
     * List group actions controller resource id
     *
     * @var string
     */
    protected $listGrpActionsResId = 'eshop_discounts/list_group_actions/controller/adm';

    /**
     * Initialization.
     *
     * @return AmiSubscribe_Topic_ListAdm
     */
    public function init(){
        parent::init();

        $oRequest = AMI::getSingleton('env/request');
        $isCategoryPopup = $oRequest->get('category_id', FALSE);

        AMI_Event::addHandler('on_query_add_table', array($this, 'handleAddTable'), $this->getModId());
        $this->getModel()->setActiveDependence('c');
        $this->addJoinedColumns(array('categories_count'), 'c');

        if($isCategoryPopup){
            $this->dropActions(AMI_ModListAdm::ACTION_COLUMN, array('public'));
            $this->dropActions(self::ACTION_GROUP);
            $this->dropActions(AMI_ModListAdm::ACTION_COMMON);
            $this->addActions(array(self::REQUIRE_FULL_ENV . 'active'));
        }else{
            $this->dropActions(self::ACTION_GROUP, array('delete', 'gen_sublink', 'gen_html_meta', 'gen_html_meta_force', 'seo_section'));
            $this->dropActions(AMI_ModListAdm::ACTION_COMMON, array('edit', 'delete'));
            $this->addActions(array(self::REQUIRE_FULL_ENV . 'edit', self::REQUIRE_FULL_ENV . 'copy', self::REQUIRE_FULL_ENV . 'delete'));
            $this->addGroupActions(array(array(self::REQUIRE_FULL_ENV . 'copy', 'delete_section'), array(self::REQUIRE_FULL_ENV . 'delete', 'delete_section')));
        }

        return $this;
    }

    /**
     * Adds grouping to list model.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTable::getQueryBase()
     */
    public function handleAddTable($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['oQuery']->addGrouping('i.id');
        return $aEvent;
    }
}

/**
 * AmiEshopDiscounts/Discounts configuration admin list component view.
 *
 * @package    Config_AmiEshopDiscounts_Discounts
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopDiscounts_Discounts_ListViewAdm extends Hyper_AmiEshopDiscounts_ListViewAdm{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'header';

    /**
     * Order column direction
     *
     * @var bool
     */
    protected $orderDirection = 'asc';

    /**
     * List default elemnts template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#list_header',
            '#columns', 'public', 'header', 'kind', 'cond', 'datefrom', 'dateto', 'amount', 'categories_count', 'columns',
            '#actions', 'edit', 'copy', 'delete', 'actions',
        'list_header'
    );

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiEshopDiscounts_Discounts_ListViewAdm
     */
    public function init(){
        parent::init();

        // Init columns
        $this
            ->removeColumn('announce')
            ->removeColumn('position')
            ->removeColumn('date_created')
            ->addColumn('public')
            ->setColumnWidth('public', 'extra-narrow')
            ->setColumnAlign('public', 'center')
            ->addColumn('kind')
            ->formatColumn('kind', array($this, 'fmtLocalizeColumn'))
            ->addColumnType('cond_orig', 'hidden')
            ->addColumn('cond')
            ->formatColumn('cond', array($this, 'fmtLocalizeColumn'))
            ->addColumn('datefrom')
            ->addColumn('dateto')
            ->addColumnType('type', 'hidden')
            ->addColumn('amount')
            ->formatColumn('amount', array($this, 'fmtAmount'))
            ->addColumnType('amount', 'int')
            ->addColumnType('c_categories_count', 'int')
            ->formatColumn('c_categories_count', array($this, 'fmtCategoriesCount'))
            ->addSortColumns(array('header', 'kind', 'cond', 'datefrom', 'dateto', 'amount', 'c_categories_count'));

        $this->formatColumn(
            'public',
            array($this, 'fmtColIcon'),
            array(
                'class'             => 'public',
                'has_inactive'      => true,
                'caption'           => $this->aLocale['list_public'],
                'caption_inactive'  => $this->aLocale['list_public_inactive']
            )
        );

        $this->formatColumn(
            'datefrom',
            array($this, 'fmtDateTime'),
            array(
                'format' => AMI_Lib_Date::FMT_DATE
            )
        );
        $this->formatColumn(
            'dateto',
            array($this, 'fmtDateTime'),
            array(
                'format' => AMI_Lib_Date::FMT_DATE
            )
        );

        $this->getTemplate()->setBlockLocale($this->tplBlockName, $this->aLocale, true);
        $this->addScriptCode($this->getTemplate()->parse($this->tplBlockName . ':javascript'));

        return $this;
    }

    /**
     * Localize formatter.
     *
     * @param  string $value  Value to format
     * @param  array $aArgs   Arguments
     * @return string
     */
    protected function fmtLocalizeColumn($value, array $aArgs){
        return $this->aLocale[$value];
    }

    /**
     * Amount column formatter.
     *
     * @param  string $value  Value to format
     * @param  array $aArgs   Arguments
     * @return string
     */
    protected function fmtAmount($value, array $aArgs){
        if($aArgs['aScope']['amount'] == 0){
            return $this->aLocale['combined'];
        }else{
            if($aArgs['aScope']['type'] == 'abs'){
                $oEshop = AMI::getSingleton('eshop');
                $value = $oEshop->getCurrencyString($value, $oEshop->getBaseCurrency());
            }else{
                $value .= ' %';
            }
        }

        return $value;
    }

    /**
     * Categories column formatter.
     *
     * @param  string $value  Value to format
     * @param  array $aArgs   Arguments
     * @return string
     */
    protected function fmtCategoriesCount($value, array $aArgs){
        if($aArgs['aScope']['cond'] == 'global'){
            $value = '';
        }
        return $value;
    }
}

/**
 * AmiEshopDiscounts/Discounts configuration module admin list actions controller.
 *
 * @package    Config_AmiEshopDiscounts_Discounts
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopDiscounts_Discounts_ListActionsAdm extends Hyper_AmiEshopDiscounts_ListActionsAdm{
    /**
     * Dispatches 'public' action.
     *
     * Publishes item.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchPublic($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['oController']->changeChildDiscounts($this->getRequestId(), 1);
        return parent::dispatchPublic($name, $aEvent, $handlerModId, $srcModId);
    }

    /**
     * Dispatches 'unpublic' action.
     *
     * Unpublishes item.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchUnPublic($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['oController']->changeChildDiscounts($this->getRequestId(), 0);
        return parent::dispatchUnPublic($name, $aEvent, $handlerModId, $srcModId);
    }
}

/**
 * AmiEshopDiscounts/Discounts configuration module admin list group actions controller.
 *
 * @package    Config_AmiEshopDiscounts_Discounts
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopDiscounts_Discounts_ListGroupActionsAdm extends Hyper_AmiEshopDiscounts_ListGroupActionsAdm{
    /**
     * Event handler.
     *
     * Dispatches group 'public' action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function dispatchGrpPublic($name, array $aEvent, $handlerModId, $srcModId){
        $aRequestIds = $this->getRequestIds($aEvent);
        foreach($aRequestIds as $id){
            $aEvent['oController']->changeChildDiscounts($id, 1);
        }

        return parent::dispatchGrpPublic($name, $aEvent, $handlerModId, $srcModId);
    }

    /**
     * Event handler.
     *
     * Dispatches group 'unpublic' action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function dispatchGrpUnPublic($name, array $aEvent, $handlerModId, $srcModId){
        $aRequestIds = $this->getRequestIds($aEvent);
        foreach($aRequestIds as $id){
            $aEvent['oController']->changeChildDiscounts($id, 0);
        }

        return parent::dispatchGrpUnPublic($name, $aEvent, $handlerModId, $srcModId);
    }
}
