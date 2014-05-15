<?php
/**
 * AmiEshopCoupons/CouponsCat configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiEshopCoupons_CouponsCat
 * @version   $Id: AmiEshopCoupons_CouponsCat_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiEshopCoupons/CouponsCat configuration admin action controller.
 *
 * @package    Config_AmiEshopCoupons_CouponsCat
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopCoupons_CouponsCat_Adm extends Hyper_AmiEshopCoupons_Cat_Adm{
}

/**
 * AmiEshopCoupons/CouponsCat configuration model.
 *
 * @package    Config_AmiEshopCoupons_CouponsCat
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopCoupons_CouponsCat_State extends Hyper_AmiEshopCoupons_Cat_State{
}

/**
 * AmiEshopCoupons/CouponsCat configuration admin filter component action controller.
 *
 * @package    Config_AmiEshopCoupons_CouponsCat
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopCoupons_CouponsCat_FilterAdm extends Hyper_AmiEshopCoupons_Cat_FilterAdm{
}

/**
 * AmiEshopCoupons/CouponsCat configuration item list component filter model.
 *
 * @package    Config_AmiEshopCoupons_CouponsCat
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopCoupons_CouponsCat_FilterModelAdm extends Hyper_AmiEshopCoupons_Cat_FilterModelAdm{
    /**
     * Constructor.
     */
    public function __construct(){
        $this->addViewField(
            array(
                'name'          => 'header',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'header'
            )
        );

        $aData = array();
        $aConditions = array('bind_member_0', 'bind_member_1');
        foreach($aConditions as $condition){
            $aData[] = array(
                'name'    => $condition,
                'value'   => ($condition == 'bind_member_0') ? '0' : '1',
                'caption' => $condition
            );
        }
        $this->addViewField(
            array(
                'name'          => 'bind_member',
                'type'          => 'select',
                'flt_condition' => '=',
                'not_selected'  => array('id' => '', 'caption' => 'flt_all'),
                'data'          => $aData
            )
        );
    }
}

/**
 * AmiEshopCoupons/CouponsCat configuration admin filter component view.
 *
 * @package    Config_AmiEshopCoupons_CouponsCat
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopCoupons_CouponsCat_FilterViewAdm extends Hyper_AmiEshopCoupons_Cat_FilterViewAdm{
}

/**
 * AmiEshopCoupons/CouponsCat configuration admin form component action controller.
 *
 * @package    Config_AmiEshopCoupons_CouponsCat
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopCoupons_CouponsCat_FormAdm extends Hyper_AmiEshopCoupons_Cat_FormAdm{
}

/**
 * AmiEshopCoupons/CouponsCat configuration form component view.
 *
 * @package    Config_AmiEshopCoupons_CouponsCat
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopCoupons_CouponsCat_FormViewAdm extends Hyper_AmiEshopCoupons_Cat_FormViewAdm{
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
 * AmiEshopCoupons/CouponsCat configuration admin list component action controller.
 *
 * @package    Config_AmiEshopCoupons_CouponsCat
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopCoupons_CouponsCat_ListAdm extends Hyper_AmiEshopCoupons_Cat_ListAdm{
    /**
     * Initialization.
     *
     * @return PhotoalbumCat_ListAdm
     */
    public function init(){
        AMI_Event::addHandler('on_query_add_table', array($this, 'handleAddTable'), $this->getModId());
        parent::init();
        $this->dropActions(AMI_ModListAdm::ACTION_GROUP, array('index_details', 'no_index_details'));
        $this->dropActions(self::ACTION_GROUP, array('gen_sublink', 'gen_html_meta', 'gen_html_meta_force', 'seo_section'));
        return $this;
    }

    /**
     * Adds condition.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTable::getQueryBase()
     */
    public function handleAddTable($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['oQuery']->addWhereDef(DB_Query::getSnippet("AND i.target = %s")->q('eshop'));
        return $aEvent;
    }
}

/**
 * AmiEshopCoupons/CouponsCat configuration admin list component view.
 *
 * @package    Config_AmiEshopCoupons_CouponsCat
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopCoupons_CouponsCat_ListViewAdm extends Hyper_AmiEshopCoupons_Cat_ListViewAdm{
    /**
     * List default elements template (placeholders)
     *
     * @var array
     */
    protected $aPlaceholders = array(
        '#list_header',
            '#flags', 'public', 'flags',
            '#columns', 'header', 'description', 'bind_member', 'columns',
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
            ->removeColumn('position')
            ->removeColumn('announce')
            ->removeColumn('num_items')
            ->addColumn('bind_member')
            ->formatColumn('bind_member', array($this, 'fmtBindMember'))
            ->setColumnWidth('header', 'normal')
            ->addColumn('description')
            ->setColumnTensility('description')
            ->setColumnTensility('header', false)
            ->addSortColumns(array('bind_member'));

        $this->formatColumn(
            'description',
            array($this, 'fmtTruncate'),
            array(
                'length'       => 150,
                'doStripTags'  => true,
                'doHTMLEncode' => false
            )
        );

        return $this;
    }

    /**
     * Bind member column formatter.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return string
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtBindMember($value, array $aArgs){
        return $this->aLocale['bind_member_' . intval($aArgs['aScope']['bind_member'])];
    }
}

/**
 * AmiEshopCoupons/CouponsCat configuration module admin list actions controller.
 *
 * @package    Config_AmiEshopCoupons_CouponsCat
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopCoupons_CouponsCat_ListActionsAdm extends Hyper_AmiEshopCoupons_Cat_ListActionsAdm{
}

/**
 * AmiEshopCoupons/CouponsCat configuration module admin list group actions controller.
 *
 * @package    Config_AmiEshopCoupons_CouponsCat
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopCoupons_CouponsCat_ListGroupActionsAdm extends Hyper_AmiEshopCoupons_Cat_ListGroupActionsAdm{
}
