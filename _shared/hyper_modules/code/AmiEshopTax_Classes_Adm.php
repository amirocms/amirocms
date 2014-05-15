<?php
/**
 * AmiEshopTax/Classes configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiEshopTax_Classes
 * @version   $Id: AmiEshopTax_Classes_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiEshopTax/Classes configuration admin action controller.
 *
 * @package    Config_AmiEshopTax_Classes
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopTax_Classes_Adm extends Hyper_AmiEshopTax_Adm{
    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request object
     * @param AMI_Response $oResponse  Response object
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        global $Core;

        $selectPopup = AMI::getSingleton('env/request')->get('item_id', null);
        if(isset($selectPopup)){
            $this->aDefaultComponents = array('filter', 'list');
        }elseif($Core->getModOption('eshop_tax_classes', 'tax_system') == 'us'){
            $this->aDefaultComponents = array('form');
            $oRequest->set('mod_action', 'form_show');
        }

        parent::__construct($oRequest, $oResponse);
    }

    /**
     * Returns default tax classes id's.
     *
     * @return array
     */
    public function getDefaultTaxClasses(){
        $aDefaultIDs = array();
        $oQuery = new DB_Query('cms_es_tax_classes');
        $oQuery->addFields(array('id'));
        $oQuery->addWhereDef('AND is_default = 1');
        $oTaxClassesRS = AMI::getSingleton('db')->select($oQuery);
        if($oTaxClassesRS->count()){
            foreach($oTaxClassesRS as $aRow){
                $aDefaultIDs[] = $aRow['id'];
            }
        }
        return $aDefaultIDs;
    }
}

/**
 * AmiEshopTax/Classes configuration model.
 *
 * @package    Config_AmiEshopTax_Classes
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopTax_Classes_State extends Hyper_AmiEshopTax_State{
}

/**
 * AmiEshopTax/Classes configuration admin filter component action controller.
 *
 * @package    Config_AmiEshopTax_Classes
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopTax_Classes_FilterAdm extends Hyper_AmiEshopTax_FilterAdm{
}

/**
 * AmiEshopTax/Classes configuration item list component filter model.
 *
 * @package    Config_AmiEshopTax_Classes
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopTax_Classes_FilterModelAdm extends Hyper_AmiEshopTax_FilterModelAdm{
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
                'name'          => 'tax_class_code',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'tax_class_code',
            )
        );
    }
}

/**
 * AmiEshopTax/Classes configuration admin filter component view.
 *
 * @package    Config_AmiEshopTax_Classes
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopTax_Classes_FilterViewAdm extends Hyper_AmiEshopTax_FilterViewAdm{
}

/**
 * AmiEshopTax/Classes configuration admin form component action controller.
 *
 * @package    Config_AmiEshopTax_Classes
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopTax_Classes_FormAdm extends Hyper_AmiEshopTax_FormAdm{
}

/**
 * AmiEshopTax/Classes configuration form component view.
 *
 * @package    Config_AmiEshopTax_Classes
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopTax_Classes_FormViewAdm extends Hyper_AmiEshopTax_FormViewAdm{
    /**
     * Common fields validators
     *
     * @var array
     */
    protected $aCommonFieldsValidators = array();

    /**
     * Reset form default elements template
     *
     * @var array
     */
    protected $aPlaceholders = array('#form', 'form');

    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        global $Core;

        if($Core->getModOption('eshop_tax_classes', 'tax_system') == 'us'){
            $this->addField(array('name' => 'tax_classes_us'));
            return $this;
        }

        $this->addField(array('name' => 'id', 'type' => 'hidden'));
        $this->addField(array('name' => 'mod_id', 'value' => $this->getModId(), 'type' => 'hidden'));
        $this->addField(array('name' => 'mod_action', 'value' => 'form_save', 'type' => 'hidden'));
        $this->addField(array('name' => 'ami_full', 'value' => 1, 'type' => 'hidden'));
        $this->addField(array('name' => 'header'));
        $this->addField(array('name' => 'tax_class_code', 'att' => 'tax_field'));

        $this->putPlaceholder('tax_rate_fields', 'tax_class_code.begin', true);
        $this->addTemplate($this->tplFileName, 'tax_rate_fields', $this->aLocale);
        $this->addField(
            array(
                'name' => 'tax_rate',
                'validate' => array('float', 'filled', 'stop_on_error'),
                'position' => 'tax_rate_fields.end'
            )
        );

        $oEshop = AMI::getSingleton('eshop');
        $baseCurrency = $oEshop->getBaseCurrency();
        $aTaxRateOptions = array();
        $aTaxRateOptions[] = array(
            'name'  => '%',
            'value' => 'percent'
        );
        $aTaxRateOptions[] = array(
            'name'  => $oEshop->getCurrencyPrefix($baseCurrency) . $oEshop->getCurrencyPostfix($baseCurrency),
            'value' => 'abs'
        );
        $this->addField(
            array(
                'name' => 'tax_type',
                'type' => 'select',
                'data' => $aTaxRateOptions,
                'position' => 'tax_rate_fields.end'
            )
        );

        $aTaxApplyTypeOptions = array();
        $aTaxApplyTypeOptions[] = array(
            'name'  => $this->aLocale['detach'],
            'value' => 'detach',
        );
        $aTaxApplyTypeOptions[] = array(
            'name'  => $this->aLocale['charge'],
            'value' => 'charge'
        );
        $this->addField(
            array(
                'name' => 'tax_apply_type',
                'type' => 'select',
                'data' => $aTaxApplyTypeOptions
            )
        );

        return $this;
    }
}

/**
 * AmiEshopTax/Classes configuration admin list component action controller.
 *
 * @package    Config_AmiEshopTax_Classes
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopTax_Classes_ListAdm extends Hyper_AmiEshopTax_ListAdm{
    /**
     * List actions controller resource id
     *
     * @var string
     */
    protected $listActionsResId = 'eshop_tax_classes/list_actions/controller/adm';

    /**
     * List group actions controller resource id
     *
     * @var string
     */
    protected $listGrpActionsResId = 'eshop_tax_classes/list_group_actions/controller/adm';

    /**
     * Initialization.
     *
     * @return AmiSubscribe_Topic_ListAdm
     */
    public function init(){
        parent::init();

        $this->dropActions(AMI_ModListAdm::ACTION_COLUMN, array('public'));

        $selectPopup = AMI::getSingleton('env/request')->get('item_id', null);
        if(isset($selectPopup)){
            $this->dropActions(self::ACTION_GROUP);
            $this->dropActions(AMI_ModListAdm::ACTION_COMMON);
            $this->addActions(array(self::REQUIRE_FULL_ENV . 'active'));
        }else{
            $this->dropActions(self::ACTION_GROUP, array('public', 'unpublic', 'gen_sublink', 'gen_html_meta', 'gen_html_meta_force', 'seo_section'));
        }

        return $this;
    }
}

/**
 * AmiEshopTax/Classes configuration admin list component view.
 *
 * @package    Config_AmiEshopTax_Classes
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopTax_Classes_ListViewAdm extends Hyper_AmiEshopTax_ListViewAdm{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'is_default';

    /**
     * Order column direction
     *
     * @var bool
     */
    protected $orderDirection = 'desc';

    /**
     * List default elemnts template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#list_header',
            '#columns', 'is_default', 'header', 'tax_class_code', 'tax_rate', 'tax_apply_type', 'columns',
            '#actions', 'edit', 'delete', 'actions',
        'list_header'
    );

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiEshopTax_Classes_ListViewAdm
     */
    public function init(){
        parent::init();

        // Init columns
        $this
            ->removeColumn('announce')
            ->removeColumn('position')
            ->removeColumn('public')
            ->removeColumn('date_created')
            ->addColumn('is_default')
            ->setColumnWidth('is_default', 'extra-narrow')
            ->addColumn('tax_class_code')
            ->setColumnWidth('tax_class_code', 'extra-wide')
            ->addColumnType('tax_rate', 'int')
            ->addColumnType('tax_type', 'hidden')
            ->formatColumn('tax_rate', array($this, 'fmtTaxRate'))
            ->addColumnType('tax_apply_type', 'mediumtext')
            ->formatColumn('tax_apply_type', array($this, 'fmtTaxApplyType'))
            ->addSortColumns(array('is_default', 'header', 'tax_class_code', 'tax_rate', 'tax_apply_type'));

        $this->setColumnLayout('is_default', array('align' => 'center'));
        $this->formatColumn('is_default', array($this, 'fmtColIcon'), array('class' => 'checked'));

        $this->addScriptCode($this->getTemplate()->parse($this->tplBlockName . ':javascript'));

        return $this;
    }

    /**
     * Tax rate formatter.
     *
     * @param  string $value  Value to format
     * @param  array $aArgs   Arguments
     * @return string
     */
    protected function fmtTaxRate($value, array $aArgs){
        if(!empty($value)){
            if($aArgs['aScope']['tax_type'] == 'abs'){
                $oEshop = AMI::getSingleton('eshop');
                $value = $oEshop->getCurrencyString($value, $oEshop->getBaseCurrency());
            }else{
                $value .= ' %';
            }
        }else{
            $value = '';
        }
        return $value;
    }

    /**
     * Tax apply type formatter.
     *
     * @param  string $value  Value to format
     * @param  array $aArgs   Arguments
     * @return string
     */
    protected function fmtTaxApplyType($value, array $aArgs){
        return $this->aLocale[$value];
    }
}

/**
 * AmiEshopTax/Classes configuration module admin list actions controller.
 *
 * @package    Config_AmiEshopTax_Classes
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopTax_Classes_ListActionsAdm extends Hyper_AmiEshopTax_ListActionsAdm{
    /**
     * Dispatches 'delete' action.
     *
     * Deletes item.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchDelete($name, array $aEvent, $handlerModId, $srcModId){
        $aDefaultIDs = $aEvent['oController']->getDefaultTaxClasses();

        $id = $this->getRequestId();
        if(in_array($id, $aDefaultIDs)){
            $statusMsg = 'status_del_fail';
            $aEvent['oResponse']->addStatusMessage($statusMsg);
            $this->refreshView();
            return $aEvent;
        }else{
            return parent::dispatchDelete($name, $aEvent, $handlerModId, $srcModId);
        }
    }
}

/**
 * AmiEshopTax/Classes configuration module admin list group actions controller.
 *
 * @package    Config_AmiEshopTax_Classes
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopTax_Classes_ListGroupActionsAdm extends Hyper_AmiEshopTax_ListGroupActionsAdm{
    /**
     * Event handler.
     *
     * Dispatches group delete action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function dispatchGrpDelete($name, array $aEvent, $handlerModId, $srcModId){
        $actionIDs = $aEvent['oRequest']->get('mod_action_id');
        if(!empty($actionIDs)){
            $aActionIDs = explode(',', $actionIDs);
        }else{
            return $aEvent;
        }

        $aDefaultIDs = $aEvent['oController']->getDefaultTaxClasses();
        foreach($aDefaultIDs as $defaultID){
            $resKey = array_search($defaultID, $aActionIDs);
            if($resKey !== false){
                unset($aActionIDs[$resKey]);
            }
        }

        $aEvent['oRequest']->set('mod_action_id', implode(',', $aActionIDs));

        return parent::dispatchGrpDelete($name, $aEvent, $handlerModId, $srcModId);
    }
}
