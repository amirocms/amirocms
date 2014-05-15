<?php
/**
 * AmiEshopTax/Zones configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiEshopTax_Zones
 * @version   $Id: AmiEshopTax_Zones_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiEshopTax/Zones configuration admin action controller.
 *
 * @package    Config_AmiEshopTax_Zones
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopTax_Zones_Adm extends Hyper_AmiEshopTax_Adm{
    /**
     * Returns default tax classes id's.
     *
     * @return array
     */
    public function getDefaultTaxZones(){
        $aDefaultIDs = array();
        $oQuery = new DB_Query('cms_es_tax_zones');
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
 * AmiEshopTax/Zones configuration model.
 *
 * @package    Config_AmiEshopTax_Zones
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopTax_Zones_State extends Hyper_AmiEshopTax_State{
}

/**
 * AmiEshopTax/Zones configuration admin filter component action controller.
 *
 * @package    Config_AmiEshopTax_Zones
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopTax_Zones_FilterAdm extends Hyper_AmiEshopTax_FilterAdm{
}

/**
 * AmiEshopTax/Zones configuration item list component filter model.
 *
 * @package    Config_AmiEshopTax_Zones
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopTax_Zones_FilterModelAdm extends Hyper_AmiEshopTax_FilterModelAdm{
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
                'name'          => 'zip',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'zip',
            )
        );
    }
}

/**
 * AmiEshopTax/Zones configuration admin filter component view.
 *
 * @package    Config_AmiEshopTax_Zones
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopTax_Zones_FilterViewAdm extends Hyper_AmiEshopTax_FilterViewAdm{
}

/**
 * AmiEshopTax/Zones configuration admin form component action controller.
 *
 * @package    Config_AmiEshopTax_Zones
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopTax_Zones_FormAdm extends Hyper_AmiEshopTax_FormAdm{
}

/**
 * AmiEshopTax/Zones configuration form component view.
 *
 * @package    Config_AmiEshopTax_Zones
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopTax_Zones_FormViewAdm extends Hyper_AmiEshopTax_FormViewAdm{
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

        $this->addField(array('name' => 'id', 'type' => 'hidden'));
        $this->addField(array('name' => 'mod_id', 'value' => $this->getModId(), 'type' => 'hidden'));
        $this->addField(array('name' => 'mod_action', 'value' => 'form_save', 'type' => 'hidden'));
        $this->addField(array('name' => 'ami_full', 'value' => 1, 'type' => 'hidden'));
        $this->addField(array('name' => 'header'));

        $aCountriesOptions = array();
        $aCountriesOptions[] = array(
            'name'  => $this->aLocale['all_countries'],
            'value' => ''
        );
        $oTpl = AMI::getSingleton('env/template_sys');
        $aCountries = $oTpl->parseLocale(AMI_Registry::get('LOCAL_FILES_REL_PATH')."_admin/templates/lang/country.lng");
        foreach($aCountries as $code => $country){
            $aCountriesOptions[] = array(
                'name'  => $country,
                'value' => $code
            );
        }
        $this->addField(
            array(
                'name' => 'country',
                'type' => 'select',
                'data' => $aCountriesOptions
            )
        );

        $aStateOptions = array();
        $aStates = $oTpl->parseLocale(AMI_Registry::get('LOCAL_FILES_REL_PATH')."_admin/templates/lang/us_states.lng");
        foreach($aStates as $code => $state){
            $aStateOptions[] = array(
                'name'  => $state,
                'value' => $code
            );
        }
        $this->addField(
            array(
                'name' => 'state',
                'type' => 'select',
                'data' => $aStateOptions
            )
        );
        $lngAllStates = AMI_Lib_String::jParse($this->aLocale['all_states']);
        $this->addScriptCode($this->getTemplate()->parse($this->tplBlockName . ':set_states_js', $aData = array('all_states' => $lngAllStates)));
        foreach($aStates as $code => $state){
            if($code == "all"){
                continue;
            }
            $jsCode .= 'usStates[\''.AMI_Lib_String::jParse($code).'\'] = \''.AMI_Lib_String::jParse($state).'\';'."\n";
        }
        $this->addScriptCode($jsCode);
        $this->addScriptCode($this->getTemplate()->parse($this->tplBlockName . ':javascript', $aData = array('all_states' => $lngAllStates)));

        $this->addField(array('name' => 'zip'));

        if($Core->getModOption('eshop_tax_classes', 'tax_system') == 'us'){
            $this->putPlaceholder('tax_rate_fields', 'zip.begin', true);
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
        }

        return $this;
    }
}

/**
 * AmiEshopTax/Zones configuration admin list component action controller.
 *
 * @package    Config_AmiEshopTax_Zones
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopTax_Zones_ListAdm extends Hyper_AmiEshopTax_ListAdm{
    /**
     * List actions controller resource id
     *
     * @var string
     */
    protected $listActionsResId = 'eshop_tax_zones/list_actions/controller/adm';

    /**
     * List group actions controller resource id
     *
     * @var string
     */
    protected $listGrpActionsResId = 'eshop_tax_zones/list_group_actions/controller/adm';

    /**
     * Initialization.
     *
     * @return AmiSubscribe_Topic_ListAdm
     */
    public function init(){
        parent::init();

        $this->dropActions(AMI_ModListAdm::ACTION_COLUMN, array('public'));
        $this->dropActions(self::ACTION_GROUP, array('public', 'unpublic', 'gen_sublink', 'gen_html_meta', 'gen_html_meta_force', 'seo_section'));

        return $this;
    }
}

/**
 * AmiEshopTax/Zones configuration admin list component view.
 *
 * @package    Config_AmiEshopTax_Zones
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopTax_Zones_ListViewAdm extends Hyper_AmiEshopTax_ListViewAdm{
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
            '#columns', 'is_default', 'header', 'country', 'state', 'zip', 'tax_rate', 'columns',
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
        global $Core;

        parent::init();

        // Init columns
        $this
            ->removeColumn('announce')
            ->removeColumn('position')
            ->removeColumn('public')
            ->removeColumn('date_created')
            ->addColumn('is_default')
            ->setColumnWidth('is_default', 'extra-narrow')
            ->addColumn('country')
            ->addColumnType('country', 'text')
            ->formatColumn('country', array($this, 'fmtCountry'))
            ->addColumn('state')
            ->formatColumn('state', array($this, 'fmtState'))
            ->addColumn('zip')
            ->setColumnTensility('header', false)
            ->setColumnWidth('header', 'wide')
            ->setColumnWidth('country', 'extra-wide')
            ->setColumnTensility('country')
            ->setColumnWidth('state', 'wide')
            ->setColumnWidth('zip', 'wide')
            ->addSortColumns(array('is_default', 'header', 'country', 'state', 'zip'));

        if($Core->getModOption('eshop_tax_classes', 'tax_system') == 'us'){
            $this
                ->addColumn('tax_rate', 'int')
                ->addColumnType('tax_type', 'hidden')
                ->formatColumn('tax_rate', array($this, 'fmtTaxRate'))
                ->addSortColumns(array('tax_rate'));
        }

        $this->setColumnLayout('is_default', array('align' => 'center'));
        $this->formatColumn('is_default', array($this, 'fmtColIcon'), array('class' => 'checked'));

        $this->addScriptCode($this->getTemplate()->parse($this->tplBlockName . ':javascript'));

        return $this;
    }

    /**
     * Country formatter.
     *
     * @param  string $value  Value to format
     * @param  array $aArgs   Arguments
     * @return string
     */
    protected function fmtCountry($value, array $aArgs){
        $oTpl = AMI::getSingleton('env/template_sys');
        $aCountries = $oTpl->parseLocale(AMI_Registry::get('LOCAL_FILES_REL_PATH')."_admin/templates/lang/country.lng");
        $value = !empty($aCountries[$value]) ? $aCountries[$value] : $value;
        return $value;
    }

    /**
     * State formatter.
     *
     * @param  string $value  Value to format
     * @param  array $aArgs   Arguments
     * @return string
     */
    protected function fmtState($value, array $aArgs){
        $oTpl = AMI::getSingleton('env/template_sys');
        $aStates = $oTpl->parseLocale(AMI_Registry::get('LOCAL_FILES_REL_PATH')."_admin/templates/lang/us_states.lng");
        $value = !empty($aStates[$value]) ? $aStates[$value] : $value;
        return $value;
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
}

/**
 * AmiEshopTax/Zones configuration module admin list actions controller.
 *
 * @package    Config_AmiEshopTax_Zones
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopTax_Zones_ListActionsAdm extends Hyper_AmiEshopTax_ListActionsAdm{
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
        $aDefaultIDs = $aEvent['oController']->getDefaultTaxZones();

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
 * AmiEshopTax/Zones configuration module admin list group actions controller.
 *
 * @package    Config_AmiEshopTax_Zones
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopTax_Zones_ListGroupActionsAdm extends Hyper_AmiEshopTax_ListGroupActionsAdm{
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

        $aDefaultIDs = $aEvent['oController']->getDefaultTaxZones();
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
