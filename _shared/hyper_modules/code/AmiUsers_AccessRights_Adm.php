<?php
/**
 * AmiUsers/AccessRights configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiUsers_AccessRights
 * @version   $Id: AmiUsers_AccessRights_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiUsers/AccessRights configuration admin action controller.
 *
 * @package    Config_AmiUsers_AccessRights
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_AccessRights_Adm extends Hyper_AmiUsers_Adm{
}

/**
 * AmiUsers/AccessRights configuration model.
 *
 * @package    Config_AmiUsers_AccessRights
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_AccessRights_State extends Hyper_AmiUsers_State{
}

/**
 * AmiUsers/AccessRights configuration admin filter component action controller.
 *
 * @package    Config_AmiUsers_AccessRights
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_AccessRights_FilterAdm extends Hyper_AmiUsers_FilterAdm{
}

/**
 * AmiUsers/AccessRights configuration item list component filter model.
 *
 * @package    Config_AmiUsers_AccessRights
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_AccessRights_FilterModelAdm extends Hyper_AmiUsers_FilterModelAdm{
    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();
        $this->addViewField(
            array(
                'name'          => 'login',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like'
            )
        );
    }
}

/**
 * AmiUsers/AccessRights configuration admin filter component view.
 *
 * @package    Config_AmiUsers_AccessRights
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_AccessRights_FilterViewAdm extends Hyper_AmiUsers_FilterViewAdm{
}

/**
 * AmiUsers/AccessRights configuration admin form component action controller.
 *
 * @package    Config_AmiUsers_AccessRights
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_AccessRights_FormAdm extends Hyper_AmiUsers_FormAdm{
}

/**
 * AmiUsers/AccessRights configuration form component view.
 *
 * @package    Config_AmiUsers_AccessRights
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_AccessRights_FormViewAdm extends Hyper_AmiUsers_FormViewAdm{
}

/**
 * AmiUsers/AccessRights configuration admin list component action controller.
 *
 * @package    Config_AmiUsers_AccessRights
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_AccessRights_ListAdm extends Hyper_AmiUsers_ListAdm{
    /**
     * Initialization.
     *
     * @return AmiUsers_AccessRights_ListAdm
     */
    public function init(){
        $modId = $this->getModId();
        AMI_Event::addHandler('on_query_add_table', array($this, 'handleAddTable'), $modId);
        AMI_Event::addHandler('on_list_init', array($this, 'handleListInit'), $modId);
        $this->getModel()->setActiveDependence('g');
        $this->addJoinedColumns(array('groups'), 'g');
        $this->addActions(array('edit'));
        $this->addColActions(array(self::REQUIRE_FULL_ENV . 'active'), TRUE);
        parent::init();
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
        $aEvent['oQuery']->addGrouping('g.id_member');
        return $aEvent;
    }

    /**
     * Adds late data binding for admin field.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListInit($name, array $aEvent, $handlerModId, $srcModId){
        if($handlerModId === $this->getModId()){
            AMI_Event::disableHandler('on_list_init');
            $idColumn = AMI::getResourceModel($aEvent['modId'] . '/table')->getItem()->getPrimaryKeyField();
            $aEvent['oList']->setLateDataBinding(
                $idColumn, 'is_admin', 'ami_sys_users_joined_groups', array('g' => 'admin'),
                null,
                'id_member', 0
            );
            AMI_Event::enableHandler('on_list_init');
        }
        return $aEvent;
    }
}

/**
 * AmiUsers/AccessRights configuration admin list component view.
 *
 * @package    Config_AmiUsers_AccessRights
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_AccessRights_ListViewAdm extends Hyper_AmiUsers_ListViewAdm{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'login';

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
     * @return AmiUsers_AccessRights_ListViewAdm
     */
    public function init(){
        parent::init();

        // Init columns
        $this
            ->addColumnType('id', 'hidden')
            ->addColumn('active')
            ->setColumnWidth('active', 'extra-narrow')
            ->addColumn('login')
            ->addColumn('full_name')
            ->addColumn('email')
            ->addColumn('g_groups')
            ->setColumnAlign('g_groups', 'center')
            ->setColumnWidth('g_groups', 'extra-narrow')
            ->addColumn('is_admin')
            ->setColumnWidth('is_admin', 'extra-narrow')
            ->setColumnTensility('full_name')
            ->addSortColumns(array('active', 'login', 'full_name', 'email'));

        foreach(array('active', 'is_admin') as $column){
            $this->setColumnLayout($column, array('align' => 'center'));
            if($column == 'is_admin'){
                $this->formatColumn($column, array($this, 'fmtColIcon'), array('class' => 'checked'));
            }
        }

        return $this;
    }
}
