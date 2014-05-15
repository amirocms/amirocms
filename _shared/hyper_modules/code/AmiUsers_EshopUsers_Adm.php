<?php
/**
 * AmiUsers/EshopUsers configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiUsers_EshopUsers
 * @version   $Id: AmiUsers_EshopUsers_Adm.php 40352 2013-08-07 12:04:45Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiUsers/EshopUsers configuration admin action controller.
 *
 * @package    Config_AmiUsers_EshopUsers
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_EshopUsers_Adm extends Hyper_AmiUsers_Adm{
}

/**
 * AmiUsers/EshopUsers configuration model.
 *
 * @package    Config_AmiUsers_EshopUsers
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_EshopUsers_State extends Hyper_AmiUsers_State{
}

/**
 * AmiUsers/EshopUsers configuration admin filter component action controller.
 *
 * @package    Config_AmiUsers_EshopUsers
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_EshopUsers_FilterAdm extends Hyper_AmiUsers_FilterAdm{
}

/**
 * AmiUsers/EshopUsers configuration item list component filter model.
 *
 * @package    Config_AmiUsers_EshopUsers
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_EshopUsers_FilterModelAdm extends Hyper_AmiUsers_FilterModelAdm{
    /**
     * Constructor.
     */
    public function __construct(){
        foreach(array('login', 'firstname', 'lastname', 'email') as $field){
            $this->addViewField(
                array(
                    'name'          => $field,
                    'type'          => 'input',
                    'flt_type'      => 'text',
                    'flt_default'   => '',
                    'flt_condition' => 'like',
                    'flt_column'    => $field,
                    'flt_alias'     => 'u'
                )
            );
        }
    }
}

/**
 * AmiUsers/EshopUsers configuration admin filter component view.
 *
 * @package    Config_AmiUsers_EshopUsers
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_EshopUsers_FilterViewAdm extends Hyper_AmiUsers_FilterViewAdm{
}

/**
 * AmiUsers/EshopUsers configuration admin form component action controller.
 *
 * @package    Config_AmiUsers_EshopUsers
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_EshopUsers_FormAdm extends Hyper_AmiUsers_FormAdm{
}

/**
 * AmiUsers/EshopUsers configuration form component view.
 *
 * @package    Config_AmiUsers_EshopUsers
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_EshopUsers_FormViewAdm extends Hyper_AmiUsers_FormViewAdm{
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
 * AmiUsers/EshopUsers configuration admin list component action controller.
 *
 * @package    Config_AmiUsers_EshopUsers
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_EshopUsers_ListAdm extends Hyper_AmiUsers_ListAdm{
    /**
     * List actions controller resource id
     *
     * @var string
     */
    protected $listActionsResId = 'eshop_user/list_actions/controller/adm';

    /**
     * List group actions controller resource id
     *
     * @var string
     */
    protected $listGrpActionsResId = 'eshop_user/list_group_actions/controller/adm';

    /**
     * Initialization.
     *
     * @return EshopOrder_ListAdm
     */
    public function init(){
        $this->addJoinedColumns(array('firstname', 'lastname', 'email', 'login', 'balance'), 'u');
        $this->addActions(array('edit', 'reset_password', 'delete'));
        $this->addColActions(array(self::REQUIRE_FULL_ENV . 'active'), TRUE);
        $this->addGroupActions(
            array(
                array('active', 'active_section'),
                array('unactive', 'active_section'),
                array('reset_password', 'delete_section'),
                array('delete', 'delete_section')
            )
        );
        parent::init();
        return $this;
    }
}

/**
 * AmiUsers/EshopUsers configuration admin list component view.
 *
 * @package    Config_AmiUsers_EshopUsers
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_EshopUsers_ListViewAdm extends Hyper_AmiUsers_ListViewAdm{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'id_member';

    /**
     * Order column direction
     *
     * @var bool
     */
    protected $orderDirection = 'asc';

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiUsers_EshopUsers_ListViewAdm
     */
    public function init(){
        parent::init();
        // AMI::getSingleton('db')->displayQueries();
        // Init columns
        $this
            ->addColumnType('id', 'hidden')
            ->addColumnType('id_member', 'hidden')
            ->addColumn('active')
            ->addColumnType('u_login', 'mediumtext')
            ->addColumnType('u_firstname', 'mediumtext')
            ->addColumnType('u_lastname', 'mediumtext')
            ->addColumnType('u_balance', 'int')
            ->addColumnType('discount', 'int')
            ->addColumn('u_email')
            ->setColumnTensility('u_email')
            ->addSortColumns(array('active', 'u_login', 'u_email', 'u_firstname', 'u_lastname', 'u_balance', 'discount'));

        AMI_Event::addHandler('on_list_body_{id}', array($this, 'handleId'), $this->getModId());

        $this->formatColumn(
            'u_balance',
            array($this, 'fmtBalance')
        );

        $this->formatColumn(
            'discount',
            array($this, 'fmtDiscount')
        );

        return $this;
    }

    /**
     * Format discount column values.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     */
    protected function fmtDiscount($value, array $aArgs){
        return $value ? $value . ' %' : '';
    }

    /**
     * Format price.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     */
    protected function fmtBalance($value, array $aArgs){
        return AMI::getSingleton('eshop')->formatMoney($value);
    }

    /**
     * Prepare id value.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::get()
     */
    public function handleId($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['aScope']['list_col_value'] = $aEvent['aScope']['id_member'];
        return $aEvent;
    }
}

/**
 * AmiUsers/EshopUsers configuration module admin list actions controller.
 *
 * @package    Config_AmiUsers_EshopUsers
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_EshopUsers_ListActionsAdm extends Hyper_AmiUsers_ListActionsAdm{
}

/**
 * AmiUsers/EshopUsers configuration module admin list group actions controller.
 *
 * @package    Config_AmiUsers_EshopUsers
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_EshopUsers_ListGroupActionsAdm extends Hyper_AmiUsers_ListGroupActionsAdm{
}
