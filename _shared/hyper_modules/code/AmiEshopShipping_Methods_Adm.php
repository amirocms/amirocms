<?php
/**
 * AmiEshopShipping/Methods configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiEshopShipping_Methods
 * @version   $Id: AmiEshopShipping_Methods_Adm.php 48660 2014-03-13 07:51:41Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiEshopShipping/Methods configuration admin action controller.
 *
 * @package    Config_AmiEshopShipping_Methods
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Methods_Adm extends Hyper_AmiEshopShipping_Adm{
}

/**
 * AmiEshopShipping/Methods configuration model.
 *
 * @package    Config_AmiEshopShipping_Methods
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Methods_State extends Hyper_AmiEshopShipping_State{
}

/**
 * AmiEshopShipping/Methods configuration admin filter component action controller.
 *
 * @package    Config_AmiEshopShipping_Methods
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Methods_FilterAdm extends Hyper_AmiEshopShipping_FilterAdm{
}

/**
 * AmiEshopShipping/Methods configuration item list component filter model.
 *
 * @package    Config_AmiEshopShipping_Methods
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Methods_FilterModelAdm extends Hyper_AmiEshopShipping_FilterModelAdm{
    /**
     * Common filter fields
     *
     * @var   array
     */
    protected $aCommonFields = array('header');

    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();

        $this->addViewField(
            array(
                'name'          => 'amount',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => '=',
                'flt_column'    => 'amount',
            )
        );

        $aData = array();
        $aConditions = array('none', 'total', 'weight', 'value');
        foreach($aConditions as $condition){
            $aData[] = array(
                'name'    => $condition,
                'value'   => $condition,
                'caption' => $condition
            );
        }
        $this->addViewField(
            array(
                'name'          => 'custom_conditions',
                'type'          => 'select',
                'flt_condition' => '=',
                'not_selected'  => array('id' => '', 'caption' => 'flt_all'),
                'data'          => $aData
            )
        );

        $oShippingFieldsList = AMI::getResourceModel('eshop_shipping_fields/table')->getList();
        $oShippingFieldsList
            ->addColumns(array('id', 'header', 'postfix'))
            ->addWhereDef('AND public = 1')
            ->addWhereDef(DB_Query::getSnippet('AND lang = %s')->q(AMI_Registry::get('lang')))
            ->addOrder('id', ' asc')
            ->load();
        $aData = array();
        foreach($oShippingFieldsList as $oShippingFieldModelItem){
            $aData[] = array(
                'name'    => $oShippingFieldModelItem->header,
                'value'   => $oShippingFieldModelItem->postfix
            );
        }
        $this->addViewField(
            array(
                'name'          => 'custom_fields',
                'type'          => 'select',
                'flt_condition' => '=',
                'not_selected'  => array('id' => '', 'caption' => 'flt_all'),
                'data'          => $aData
            )
        );
    }

    /**
     * Adds current user id as id_owner.
     *
     * @param string $field  Field name
     * @param array $aData  Filter data
     * @return array
     * @amidev Temporary
     */
    protected function processFieldData($field, array $aData){
        if($field == 'custom_fields'){
            if($aData['value'] == ''){
                $aData['skip'] = true;
            }else{
                $aData['forceSQL'] = " AND custom_fields LIKE '%|".mysql_real_escape_string($aData['value'])."|%'";
            }
        }
        return $aData;
    }
}

/**
 * AmiEshopShipping/Methods configuration admin filter component view.
 *
 * @package    Config_AmiEshopShipping_Methods
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Methods_FilterViewAdm extends Hyper_AmiEshopShipping_FilterViewAdm{
}

/**
 * AmiEshopShipping/Methods configuration admin form component action controller.
 *
 * @package    Config_AmiEshopShipping_Methods
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Methods_FormAdm extends Hyper_AmiEshopShipping_FormAdm{
}

/**
 * AmiEshopShipping/Methods configuration form component view.
 *
 * @package    Config_AmiEshopShipping_Methods
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Methods_FormViewAdm extends Hyper_AmiEshopShipping_FormViewAdm{
}

/**
 * AmiEshopShipping/Methods configuration admin list component action controller.
 *
 * @package    Config_AmiEshopShipping_Methods
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Methods_ListAdm extends Hyper_AmiEshopShipping_ListAdm{
    /**
     * Initialization.
     *
     * @return AmiSubscribe_Topic_ListAdm
     */
    public function init(){
        AMI_Event::addHandler('on_query_add_table', array($this, 'handleAddTable'), $this->getModId());

        parent::init();

        $this->dropActions(AMI_ModListAdm::ACTION_COLUMN, array('public'));
        $this->dropActions(AMI_ModListAdm::ACTION_ALL, array('edit', 'delete'));
        $this->addActions(array(self::REQUIRE_FULL_ENV . 'edit', self::REQUIRE_FULL_ENV . 'copy', self::REQUIRE_FULL_ENV . 'delete'));
        $this->dropActions(self::ACTION_GROUP, array('public', 'unpublic', 'gen_sublink', 'gen_html_meta', 'gen_html_meta_force', 'seo_section'));
        $this->addGroupActions(array(array(self::REQUIRE_FULL_ENV . 'delete', 'delete_section')));

        return $this;
    }

    /**
     * Excluding non-parent and hidden shipping methods.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTable::getQueryBase()
     */
    public function handleAddTable($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['oQuery']->addWhereDef(
            DB_Query::getSnippet('AND i.`id_parent` = 0 AND i.`hidden` = 0')
        );
        return $aEvent;
    }
}

/**
 * AmiEshopShipping/Methods configuration admin list component view.
 *
 * @package    Config_AmiEshopShipping_Methods
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Methods_ListViewAdm extends Hyper_AmiEshopShipping_ListViewAdm{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'position';

    /**
     * Order column direction
     *
     * @var bool
     */
    protected $orderDirection = 'asc';

    /**
     * Array of shipping fields headers
     *
     * @var array
     */
    protected $aShippingFieldsHeaders = array();

    /**
     * Array of fieldsets IDs
     *
     * @var array
     */
    protected $aFieldsetsIds = array();

    /**
     * List default elemnts template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#list_header',
            '#columns', 'position', 'header', 'amount', 'delivery_time', 'custom_conditions', 'fields', 'columns',
            '#actions', 'edit', 'copy', 'delete', 'actions',
        'list_header'
    );

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiEshopShipping_Methods_ListViewAdm
     */
    public function init(){
        parent::init();

        // Init columns
        $this
            ->removeColumn('public')
            ->removeColumn('date_created')
            ->removeColumn('announce')
            ->addColumnType('type', 'hidden')
            ->addColumnType('groups', 'hidden')
            ->addColumnType('amount', 'int')
            ->formatColumn('amount', array($this, 'fmtAmount'))
            ->addColumn('delivery_time')
            ->setColumnWidth('delivery_time', 'wide')
            ->addColumn('custom_conditions')
            ->formatColumn('custom_conditions', array($this, 'fmtCustomConditions'))
            ->addColumn('fields')
            ->formatColumn('fields', array($this, 'fmtCustomFields'))
            ->setColumnWidth('fields', 'extra-wide')
            ->addSortColumns(array('amount', 'delivery_time', 'custom_conditions'));

        $oShippingFieldsList = AMI::getResourceModel('eshop_shipping_fields/table')->getList();
        $oShippingFieldsList
            ->addColumns(array('id', 'header', 'postfix'))
            ->addWhereDef('AND public = 1')
            ->addWhereDef(DB_Query::getSnippet('AND lang = %s')->q(AMI_Registry::get('lang')))
            ->addOrder('id', ' asc')
            ->load();
        $fieldsets = array();
        foreach($oShippingFieldsList as $oShippingFieldModelItem){
            $fieldsets[] = 'custom_shipping_' . $oShippingFieldModelItem->postfix;
            $this->aShippingFieldsHeaders[$oShippingFieldModelItem->postfix] = $oShippingFieldModelItem->header;
        }
        foreach($fieldsets as $fieldset){
            $this->aFieldsetsIds[] = preg_replace('/^custom\_shipping\_/', '', $fieldset);
        }

        return $this;
    }

    /**
     * Amount column formatter.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return string
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtAmount($value, array $aArgs){
        if($aArgs['aScope']['custom_conditions'] != 'value'){
            if($value >= 0){
                if($aArgs['aScope']['type'] == "abs"){
                    $oEshop = AMI::getSingleton('eshop');
                    $value = $oEshop->getCurrencyString($value, $oEshop->getBaseCurrency());
                }else{
                    $value .= "%";
                }
            }else{
                $value = $this->aLocale['combined'];
            }
        }else{
            $value = '---';
        }
        return $value;
    }

    /**
     * Custom conditions column formatter.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return string
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtCustomConditions($value, array $aArgs){
        return $this->aLocale[$value];
    }

    /**
     * Custom fields column formatter.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return string
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtCustomFields($value, array $aArgs){
        $fldGroupOptionsIds = $aArgs['aScope']['groups'];
        if(!is_array($fldGroupOptionsIds)){
            $fldGroupOptionsIds = array();
        }

        if(sizeof($value) > 0){
            $fieldsets = $value;
            $customFields = "";
            foreach($fieldsets as $fieldset){
                if(in_array($fieldset, $this->aFieldsetsIds)){
                    $customFields .= $this->aShippingFieldsHeaders[$fieldset] . "<br />";
                }elseif(in_array($fieldset, $fldGroupOptionsIds)){
                    $customFields .= $this->aShippingFieldsHeaders[array_search($fieldset, $fldGroupOptionsIds)] . "<br />";
                }
            }
            $value = $customFields . '<br />';
        }

        return $value;
    }
}

/**
 * AmiEshopShipping/Methods configuration module admin list actions controller.
 *
 * @package    Config_AmiEshopShipping_Methods
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Methods_ListActionsAdm extends Hyper_AmiEshopShipping_ListActionsAdm{
}

/**
 * AmiEshopShipping/Methods configuration module admin list group actions controller.
 *
 * @package    Config_AmiEshopShipping_Methods
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Methods_ListGroupActionsAdm extends Hyper_AmiEshopShipping_ListGroupActionsAdm{
}
