<?php
/**
 * AmiEshopCoupons/Coupons configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiEshopCoupons_Coupons
 * @version   $Id: AmiEshopCoupons_Coupons_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiEshopCoupons/Coupons configuration admin action controller.
 *
 * @package    Config_AmiEshopCoupons_Coupons
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopCoupons_Coupons_Adm extends Hyper_AmiEshopCoupons_Adm{
}

/**
 * AmiEshopCoupons/Coupons configuration model.
 *
 * @package    Config_AmiEshopCoupons_Coupons
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since     x.x.x
 * @amidev     Temporary
 */
class AmiEshopCoupons_Coupons_State extends Hyper_AmiEshopCoupons_State{
}

/**
 * AmiEshopCoupons/Coupons configuration admin filter component action controller.
 *
 * @package    Config_AmiEshopCoupons_Coupons
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since     x.x.x
 * @amidev     Temporary
 */
class AmiEshopCoupons_Coupons_FilterAdm extends Hyper_AmiEshopCoupons_FilterAdm{
}

/**
 * AmiEshopCoupons/Coupons configuration item list component filter model.
 *
 * @package    Config_AmiEshopCoupons_Coupons
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since     x.x.x
 * @amidev     Temporary
 */
class AmiEshopCoupons_Coupons_FilterModelAdm extends Hyper_AmiEshopCoupons_FilterModelAdm{
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
                'name'          => 'used',
                'type'          => 'checkbox',
                'flt_default'   => '0',
                'flt_condition' => '',
                'flt_column'    => 'activations_left',
                'disable_empty' => true
            )
        );

        $this->addViewField(
            array(
                'name'          => 'u_login',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'login',
                'flt_alias'     => 'u'
            )
        );

        return $this;
    }

    /**
     * Adds filter by activations left field.
     *
     * @param string $field  Field name
     * @param array $aData  Filter data
     * @return array
     * @amidev Temporary
     */
    protected function processFieldData($field, array $aData){
        if(($field == 'used') && $aData['value'] == 1){
            // $aData['forceSQL'] = ' AND (i.activations_left <= 0 AND i.activations_left IS NOT NULL)';
        }elseif($field == 'used'){
            $aData['forceSQL'] = ' AND (i.activations_left > 0 OR i.activations_left IS NULL)';
        }
        return $aData;
    }
}

/**
 * AmiEshopCoupons/Coupons configuration admin filter component view.
 *
 * @package    Config_AmiEshopCoupons_Coupons
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since     x.x.x
 * @amidev     Temporary
 */
class AmiEshopCoupons_Coupons_FilterViewAdm extends Hyper_AmiEshopCoupons_FilterViewAdm{
}

/**
 * AmiEshopCoupons/Coupons configuration admin form component action controller.
 *
 * @package    Config_AmiEshopCoupons_Coupons
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since     x.x.x
 * @amidev     Temporary
 */
class AmiEshopCoupons_Coupons_FormAdm extends Hyper_AmiEshopCoupons_FormAdm{
}

/**
 * AmiEshopCoupons/Coupons configuration form component view.
 *
 * @package    Config_AmiEshopCoupons_Coupons
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since     x.x.x
 * @amidev     Temporary
 */
class AmiEshopCoupons_Coupons_FormViewAdm extends Hyper_AmiEshopCoupons_FormViewAdm{
}

/**
 * AmiEshopCoupons/Coupons configuration admin list component action controller.
 *
 * @package    Config_AmiEshopCoupons_Coupons
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since     x.x.x
 * @amidev     Temporary
 */
class AmiEshopCoupons_Coupons_ListAdm extends Hyper_AmiEshopCoupons_ListAdm{
    /**
     * Initialization.
     *
     * @return AmiSubscribe_Topic_ListAdm
     */
    public function init(){
        $this->addJoinedColumns(array('login'), 'u');
        $this->addJoinedColumns(array('amount', 'type', 'discounts_count'), 'd');
        AMI_Event::addHandler('on_query_add_table', array($this, 'handleAddTable'), $this->getModId());

        parent::init();

        $this->dropActions(AMI_ModListAdm::ACTION_COLUMN, array('public'));
        $this->dropActions(self::ACTION_GROUP, array('public', 'unpublic', 'gen_sublink', 'gen_html_meta', 'gen_html_meta_force', 'seo_section'));
        $this->addGroupActions(array(array(self::REQUIRE_FULL_ENV . 'delete', 'delete_section')));

        return $this;
    }

    /**
     * Adds grouping.
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
 * AmiEshopCoupons/Coupons configuration admin list component view.
 *
 * @package    Config_AmiEshopCoupons_Coupons
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since     x.x.x
 * @amidev     Temporary
 */
class AmiEshopCoupons_Coupons_ListViewAdm extends Hyper_AmiEshopCoupons_ListViewAdm{
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
     * List default elements template (placeholders)
     *
     * @var array
     */
    protected $aPlaceholders = array(
        '#list_header',
            '#columns', 'header', 'cat_header', 'u_login', 'd_amount', 'activations_left', 'activation_count', 'columns',
            '#actions', 'actions',
        'list_header'
    );

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiEshopCoupons_Coupons_ListViewAdm
     */
    public function init(){
        parent::init();

        // Init columns
        $this
            ->removeColumn('public')
            ->removeColumn('position')
            ->removeColumn('date_created')
            ->removeColumn('announce')
            ->addColumnType('d_discounts_count', 'hidden')
            ->addColumnType('d_type', 'hidden')
            ->addColumn('u_login')
            ->addColumn('d_amount')
            ->formatColumn('d_amount', array($this, 'fmtAmount'))
            ->addColumnType('activations_left', 'int')
            ->formatColumn('activations_left', array($this, 'fmtActivationsLeft'))
            ->addColumnType('activation_count', 'int')
            ->setColumnWidth('cat_header', 'wide')
            ->addSortColumns(array('u_login', 'd_amount', 'activations_left', 'activation_count'));

        $this->addScriptCode($this->parse('javascript'));

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
        switch(intval($aArgs['aScope']['d_discounts_count'])){
            case 0:
                $value = '-';
                break;
            case 1:
                if($aArgs['aScope']['d_type'] == "abs"){
                    $oEshop = AMI::getSingleton('eshop');
                    $value = $oEshop->getCurrencyString($value, $oEshop->getBaseCurrency());
                }else{
                    $value .= '%';
                }
                break;
            default:
                $value = $this->aLocale['multiple'];
                break;
        }
        return $value;
    }

    /**
     * Activation left column formatter.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return string
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtActivationsLeft($value, array $aArgs){
        if(is_null($aArgs['aScope']['activations_left'])){
            $value = $this->aLocale['unlimited'];
        }
        return $value;
    }
}

/**
 * AmiEshopCoupons/Coupons configuration module admin list actions controller.
 *
 * @package    Config_AmiEshopCoupons_Coupons
 * @subpackage Controller
 * @since     x.x.x
 * @amidev     Temporary
 */
class AmiEshopCoupons_Coupons_ListActionsAdm extends Hyper_AmiEshopCoupons_ListActionsAdm{
}

/**
 * AmiEshopCoupons/Coupons configuration module admin list group actions controller.
 *
 * @package    Config_AmiEshopCoupons_Coupons
 * @subpackage Controller
 * @since     x.x.x
 * @amidev     Temporary
 */
class AmiEshopCoupons_Coupons_ListGroupActionsAdm extends Hyper_AmiEshopCoupons_ListGroupActionsAdm{
}
