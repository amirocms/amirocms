<?php
/**
 * AmiUsers/Users configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiUsers_Users
 * @version   $Id: AmiUsers_Users_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiUsers/Users configuration admin action controller.
 *
 * @package    Config_AmiUsers_Users
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_Users_Adm extends Hyper_AmiUsers_Adm{
}

/**
 * AmiUsers/Users configuration model.
 *
 * @package    Config_AmiUsers_Users
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_Users_State extends Hyper_AmiUsers_State{
}

/**
 * AmiUsers/Users configuration admin filter component action controller.
 *
 * @package    Config_AmiUsers_Users
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_Users_FilterAdm extends Hyper_AmiUsers_FilterAdm{
}

/**
 * AmiUsers/Users configuration item list component filter model.
 *
 * @package    Config_AmiUsers_Users
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_Users_FilterModelAdm extends Hyper_AmiUsers_FilterModelAdm{
    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();

        foreach(array('login', 'nickname', 'email', 'firstname', 'lastname') as $field){
            $this->addViewField(
                array(
                    'name'          => $field,
                    'type'          => 'input',
                    'flt_type'      => 'text',
                    'flt_default'   => '',
                    'flt_condition' => 'like'
                )
            );
        }
        $this->addViewField(
            array(
                'name'          => 'datefrom',
                'type'          => 'datefrom',
                'flt_type'      => 'date',
                'flt_default'   => AMI_Lib_Date::formatUnixTime(AMI_Lib_Date::UTIME_MIN),
                'flt_condition' => '>=',
                'flt_column'    => 'date_created',
                'validate'         => array('date','date_limits'),
                'session_field' => true
            )
        );
        $this->addViewField(
            array(
                'name'          => 'dateto',
                'type'          => 'dateto',
                'flt_type'      => 'date',
                'flt_default'   => AMI_Lib_Date::formatUnixTime(AMI_Lib_Date::UTIME_MAX),
                'flt_condition' => '<=',
                'flt_column'    => 'date_created',
                'validate'         => array('date','date_limits'),
                'session_field' => true
            )
        );
    }
}

/**
 * AmiUsers/Users configuration admin filter component view.
 *
 * @package    Config_AmiUsers_Users
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_Users_FilterViewAdm extends Hyper_AmiUsers_FilterViewAdm{
}

/**
 * AmiUsers/Users configuration admin form component action controller.
 *
 * @package    Config_AmiUsers_Users
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_Users_FormAdm extends Hyper_AmiUsers_FormAdm{
}

/**
 * AmiUsers/Users configuration form component view.
 *
 * @package    Config_AmiUsers_Users
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_Users_FormViewAdm extends Hyper_AmiUsers_FormViewAdm{
}

/**
 * AmiUsers/Users configuration admin list component action controller.
 *
 * @package    Config_AmiUsers_Users
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_Users_ListAdm extends Hyper_AmiUsers_ListAdm{
    /**
     * List actions controller resource id
     *
     * @var string
     */
    protected $listActionsResId = 'members/list_actions/controller/adm';

    /**
     * List group actions controller resource id
     *
     * @var string
     */
    protected $listGrpActionsResId = 'members/list_group_actions/controller/adm';

    /**
     * Initialization.
     *
     * @return EshopOrder_ListAdm
     */
    public function init(){
        $this->addActions(array('edit', 'reset_password', 'delete'));
        $this->addColActions(array(self::REQUIRE_FULL_ENV . 'active'), TRUE);
        $this->addGroupActions(
            array(
                array(self::REQUIRE_FULL_ENV . 'delete', 'delete_section')
            )
        );
        parent::init();
        return $this;
    }
}

/**
 * AmiUsers/Users configuration admin list component view.
 *
 * @package    Config_AmiUsers_Users
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_Users_ListViewAdm extends Hyper_AmiUsers_ListViewAdm{
    /**
     * System user id
     *
     * @var int
     */
    protected $sysUserId;

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiUsers_Users_ListViewAdm
     */
    public function init(){
        parent::init();

        $sql =
            "SELECT `id_member` " .
            "FROM `cms_host_users` " .
            "WHERE `sys_user` = 1";

        $this->sysUserId = (int)AMI::getSingleton('db')->fetchValue($sql);

        AMI_Event::dropHandler('on_query_add_table', array('AMI_GlobalFilters', 'handleFilterLangData'), AMI_Event::MOD_ANY);
        AMI_Event::addHandler(
            'on_query_add_table',
            array($this, 'handleFilterLangData'),
            AMI_Event::MOD_ANY
        );

        // Init columns
        $this
            ->addColumnType('id', 'hidden')
            ->addColumn('active')
            ->addColumn('date_created', 'active.after')
            ->addColumnType('date_created', 'datetime')
            ->addColumn('login')
            ->addColumn('nickname')
            ->addColumn('email')
            ->addColumn('firstname')
            ->addColumn('lastname')
            ->setColumnTensility('nickname')
            ->addSortColumns(array('active', 'date_created', 'login', 'nickname', 'email', 'firstname', 'lastname'));

        return $this;
    }

    /**
     * Event handler.
     *
     * Handling action cell to disallow deleting of system user.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleActionCell($name, array $aEvent, $handlerModId, $srcModId){
        if($aEvent['aScope']['id'] == $this->sysUserId){
            unset($aEvent['aScope']['_action_col']['delete']);
        }
        $aEvent = parent::handleActionCell($name, $aEvent, $handlerModId, $srcModId);
        return $aEvent;
    }

    /**
     * Event handler.
     *
     * Adds filter by lang data or system user id.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleFilterLangData($name, array $aEvent, $handlerModId, $srcModId){
        if($aEvent['modId'] === $this->getModId()){
            $alias = $aEvent['alias'];
            if($alias){
                $alias .= '.';
            }
            $aEvent['oQuery']->addWhereDef(
                DB_Query::getSnippet("AND (%s`lang` = %s OR %s`id` = %s)")
                ->plain($alias)
                ->q(AMI_Registry::get('lang_data'))
                ->plain($alias)
                ->plain($this->sysUserId)
            );
        }else{
            // Call common filter
            $aEvent = AMI_GlobalFilters::handleFilterLangData($name, $aEvent, $handlerModId, $srcModId);
        }
        return $aEvent;
    }
}

/**
 * AmiUsers/Users configuration module admin list actions controller.
 *
 * @package    Config_AmiUsers_Users
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_Users_ListActionsAdm extends Hyper_AmiUsers_ListActionsAdm{
    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     * @see    AMI_ModListAdm::addActions()
     * @see    AMI_ModListAdm::addColActions()
     */

    /**
     * Dispatches 'active' action.
     *
     * Activates user.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchActive($name, array $aEvent, $handlerModId, $srcModId){
        $id = $this->getRequestId();
        if($id != $this->sysUserId){
            global $cms, $db;

            $this->oMember->activateMember($cms, $db, $id, 1);
            $aEvent['oResponse']->addStatusMessage('status_activate');
        }else{
            $aEvent['oResponse']->addStatusMessage('status_activate_fail', array(), AMI_Response::STATUS_MESSAGE_ERROR);
        }
        $this->refreshView();
        return $aEvent;
    }

    /**
     * Dispatches 'unactive' action.
     *
     * Disactivates user.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchUnactive($name, array $aEvent, $handlerModId, $srcModId){
        $id = $this->getRequestId();
        if($id != $this->sysUserId){
            global $cms, $db;

            $this->oMember->activateMember($cms, $db, $id, 0);
            $aEvent['oResponse']->addStatusMessage('status_activate');
        }else{
            $aEvent['oResponse']->addStatusMessage('status_activate_fail', array(), AMI_Response::STATUS_MESSAGE_ERROR);
        }
        $this->refreshView();
        return $aEvent;
    }

    /**#@-*/
}

/**
 * AmiUsers/Users configuration module admin list group actions controller.
 *
 * @package    Config_AmiUsers_Users
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_Users_ListGroupActionsAdm extends Hyper_AmiUsers_ListGroupActionsAdm{
}
