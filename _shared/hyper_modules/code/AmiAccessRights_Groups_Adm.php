<?php
/**
 * AmiAccessRights configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiAccessRights_Groups
 * @version   $Id: AmiAccessRights_Groups_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiAccessRights configuration admin action controller.
 *
 * @package    Config_AmiAccessRights_Groups
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAccessRights_Groups_Adm extends Hyper_AmiAccessRights_Adm{
}

/**
 * AmiAccessRights configuration model.
 *
 * @package    Config_AmiAccessRights_Groups
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAccessRights_Groups_State extends Hyper_AmiAccessRights_State{
}

/**
 * AmiAccessRights configuration admin filter component action controller.
 *
 * @package    Config_AmiAccessRights_Groups
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAccessRights_Groups_FilterAdm extends Hyper_AmiAccessRights_FilterAdm{
}

/**
 * AmiAccessRights configuration item list component filter model.
 *
 * @package    Config_AmiAccessRights_Groups
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAccessRights_Groups_FilterModelAdm extends Hyper_AmiAccessRights_FilterModelAdm{
    /**
     * Constructor.
     */
    public function __construct(){
        $this->addViewField(
            array(
                'name'          => 'name',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'name'
            )
        );
    }
}

/**
 * AmiAccessRights configuration admin filter component view.
 *
 * @package    Config_AmiAccessRights_Groups
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAccessRights_Groups_FilterViewAdm extends Hyper_AmiAccessRights_FilterViewAdm{
}

/**
 * AmiAccessRights configuration admin form component action controller.
 *
 * @package    Config_AmiAccessRights_Groups
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAccessRights_Groups_FormAdm extends Hyper_AmiAccessRights_FormAdm{
}

/**
 * AmiAccessRights configuration form component view.
 *
 * @package    Config_AmiAccessRights_Groups
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAccessRights_Groups_FormViewAdm extends Hyper_AmiAccessRights_FormViewAdm{
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
 * AmiAccessRights configuration admin list component action controller.
 *
 * @package    Config_AmiAccessRights_Groups
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAccessRights_Groups_ListAdm extends Hyper_AmiAccessRights_ListAdm{
    /**
     * Initialization.
     *
     * @return AmiAccessRights_Groups_ListAdm
     */
    public function init(){
        AMI_Registry::set('AMI/models/ami_sys_groups/join/ami_sys_users', TRUE);
        AMI_Event::addHandler('on_query_add_table', array($this, 'handleAddTable'), $this->getModId());
        $this->getModel()->setActiveDependence('u');
        $this->addJoinedColumns(array('users'), 'u');
        $this->addActions(array('edit', self::REQUIRE_FULL_ENV . 'delete'));
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
        $aEvent['oQuery']->addGrouping('u.id_group');
        return $aEvent;
    }
}

/**
 * AmiAccessRights configuration admin list component view.
 *
 * @package    Config_AmiAccessRights_Groups
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAccessRights_Groups_ListViewAdm extends Hyper_AmiAccessRights_ListViewAdm{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'name';

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
     * @return AmiAccessRights_Groups_ListViewAdm
     */
    public function init(){
        $this
            ->addColumnType('id', 'hidden')
            ->addColumnType('is_default', 'none')
            ->addColumn('is_guest')
            ->setColumnWidth('is_guest', 'extra-narrow')
            ->addColumn('name')
            ->setColumnTensility('name')
            ->addColumn('u_users')
            ->setColumnAlign('u_users', 'center')
            ->setColumnWidth('u_users', 'extra-narrow')
            ->addColumn('modules')
            ->setColumnAlign('modules', 'center')
            ->setColumnWidth('modules', 'extra-narrow')
            ->addColumn('is_moderator')
            ->setColumnWidth('is_moderator', 'extra-narrow')
            ->addColumn('is_admin')
            ->setColumnWidth('is_admin', 'extra-narrow')
            ->addSortColumns(array('is_guest', 'name', 'is_moderator', 'is_admin'))

            ->addColumnType('is_default', 'none')
            ->addColumnType('mask', 'none');

        $this->formatColumn('modules', array($this, 'fmtModules'));
        foreach(array('is_guest', 'is_moderator', 'is_admin') as $column){
            $this->setColumnLayout($column, array('align' => 'center'));
            $this->formatColumn($column, array($this, 'fmtColIcon'), array('class' => 'checked'));
        }

        parent::init();
        return $this;
    }

    /**
     * Event handler.
     *
     * Handling action cell to disallow deleting of guest groups.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleActionCell($name, array $aEvent, $handlerModId, $srcModId){
        if(!empty($aEvent['aScope']['is_default']) || !empty($aEvent['aScope']['is_guest'])){
            unset($aEvent['aScope']['_action_col']['delete']);
        }
        $aEvent = parent::handleActionCell($name, $aEvent, $handlerModId, $srcModId);
        return $aEvent;
    }

    /**
     * Modules column formatter.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtModules($value, array $aArgs){
        if(is_null($value)){
            $value = '-';
        }
        return $value;
    }
}

/**
 * AmiAccessRights configuration module admin list actions controller.
 *
 * @package    Config_AmiAccessRights_Groups
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAccessRights_Groups_ListActionsAdm extends Hyper_AmiAccessRights_ListActionsAdm{
}

/**
 * AmiAccessRights configuration module admin list group actions controller.
 *
 * @package    Config_AmiAccessRights_Groups
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAccessRights_Groups_ListGroupActionsAdm extends Hyper_AmiAccessRights_ListGroupActionsAdm{
}
