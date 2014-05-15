<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModListAdm.php 44398 2013-11-26 09:02:25Z Kolesnikov Artem $
 * @since     5.12.0
 */

/**
 * Module admin list component action controller.
 *
 * Example:
 * <code>
 * class AmiSample_ListAdm extends AMI_ModListAdm{
 *     public function __construct(){
 *         $this->addActions(array('edit', 'delete'));
 *     }
 * }
 * </code>
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.12.0
 */
abstract class AMI_ModListAdm extends AMI_ModList{
    /**
     * Action prefix specifying full environment requirement for action processing
     *
     * @see AMI_ModListAdm::addActions()
     * @see AMI_ModListAdm::addColActions()
     * @see AMI_ModListAdm::addGroupActions()
     */
    const REQUIRE_FULL_ENV = '!';

    /**#@+
     * Action type.
     *
     * @see    AMI_ModListAdm::dropActions()
     * @since  5.14.0
     */

    const ACTION_COMMON   = 0x01;

    const ACTION_COLUMN   = 0x02;

    const ACTION_POSITION = 0x04;

    const ACTION_GROUP    = 0x08;

    const ACTION_ALL      = 0x0F;

    /**#@-*/

    /**
     * Array of actions for "actions" column
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aActions = array();

    /**
     * Array of actions having separate columns
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aColActions = array();

    /**
     * Value dependend separate column actions
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aValueDependendColActions = array();

    /**
     * Inner column actions to columns
     *
     * @var    array
     * @amidev Temporary?
     */
    protected $aActionsXCols = array();

    /**
     * Array of group actions
     *
     * @var array
     * @amidev Temporary
     */
    protected $aGroupActions = array();

    /**
     * View for list group actions
     *
     * @var AMI_ModListGroupActionsView
     */
    protected $oGroupActionsView = null;

    /**
     * Array of position actions
     *
     * @var array
     * @amidev Temporary
     */
    protected $aPositionActions = array();

    /**
     * List actions controller resource id
     *
     * @var string
     */
    protected $listActionsResId = 'list/actions';

    /**
     * List group actions controller resource id
     *
     * @var string
     */
    protected $listGrpActionsResId = 'list/actions/group';

    /**
     * List columns from dependent tables
     *
     * @var array
     */
    protected $aJoinedColumns = array();

    /**
     * Array containing full environment actions
     *
     * @var array
     */
    private $aFullEnvActions = array();

    /**
     * Initialization.
     *
     * @return AMI_ModListAdm
     */
    public function init(){
        parent::init();
        $this->addSubComponent(AMI::getResource('list/pagination'));

        // Add corresponding action handlers

        // Common actions
        $aActions = array_merge($this->getActions(), array_keys($this->getActions(true)), $this->getColActions());
        foreach(array_keys($this->aValueDependendColActions) as $action){
            $aActions[] = 'un_' . $action;
        }
        foreach($aActions as $action){
            $this->addActionCallback('common', $action);
        }
        // Group actions
        foreach($this->getGroupActions() as $action){
            $this->addActionCallback('group', $action);
        }
        // Position actions
        foreach($this->getPositionActions() as $action){
            $this->addActionCallback('position', $action);
        }
        // Add action collumn
        AMI_Event::addHandler('on_list_columns', array($this, 'handleActionsColumnHeader'), $this->getModId());
        AMI_Event::addHandler('on_list_body_row', array($this, 'handleActionsColumn'), $this->getModId());
        AMI_Event::addHandler('on_before_view_list', array($this, 'handleSortColumn'), $this->getModId());

        return $this;
    }

    /**
     * Returns component view.
     *
     * @return AMI_ModListView
     * @see    AMI_ModListAdm::init()
     */
    public function getView(){
        $hasActionColumn = sizeof($this->aActions) > 0;
        if($hasActionColumn || sizeof($this->getGroupActions())){
            AMI_Event::addHandler('on_before_view_list', array($this, 'handleBeforeViewList'), $this->getModId());
        }

        $oView = parent::getView();
        if($oView instanceof AMI_ModListView_JSON){
            foreach($this->getColActions() as $action){
                $oView->addColumnType($action, 'action');
            }
            if($hasActionColumn){
                $oView->addActionColumns();
            }
        }
        return $oView;
    }

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     * @amidev Temporary
     */

    /**
     * Creates "Actions" column.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleActionsColumnHeader($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['columns'][] = 'actions';
        return $aEvent;
    }

    /**
     * Adds joined columns to available fields.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @amidev
     */
    public function handleCommonGetAvailableFields($name, array $aEvent, $handlerModId, $srcModId){
        foreach($this->aJoinedColumns as $alias => $aColumns){
            foreach($aColumns as $column){
                $aEvent['aFields'][] = $alias . '_' . $column;
            }
        }
        return $aEvent;
    }

    /**
     * Adds joined columns to the list.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleCommonQueryAddJoinedColumns($name, array $aEvent, $handlerModId, $srcModId){
        if(isset($this->aJoinedColumns[$aEvent['alias']])){
            $aEvent['oList']->addColumns($this->aJoinedColumns[$aEvent['alias']]/*, $aEvent['alias']*/);
        }
        return $aEvent;
    }

    /**
     * Creates "Actions" columns data.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleActionsColumn($name, array $aEvent, $handlerModId, $srcModId){
       return $aEvent;
    }

    /**
     * Prepares action column.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @since  5.12.4
     * @see    AMI_View::getScope()
     */
    public function handleBeforeViewList($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['aScope']['_action_col'] = array();
        foreach($this->getActions() as $action){
            $aEvent['aScope']['_action_col'][$action] = array(
                'format' => 'action',
                'value'  => $action
            );
        }
        $aEvent['aScope']['_actions'] = array_merge($this->getActions(), $this->getColActions());
        $aEvent['aScope']['_group_actions'] = $this->getGroupActions();
        $aEvent['aScope']['_value_dependend_actions'] = $this->aValueDependendColActions;
        $aEvent['aScope']['_inner_actions'] = $this->getActions(true);
        $aEvent['aScope']['_full_env_actions'] = array_keys($this->aFullEnvActions);
        return $aEvent;
    }

    /**
     * Prepares order column.
     *
     * @param  string $name          Event name
     * @param  array $aEvent         Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @since  5.12.4
     * @see    AMI_View::getScope()
     */
    public function handleSortColumn($name, array $aEvent, $handlerModId, $srcModId){
        // Load default order from module options
        $prefix = AMI_Registry::get('side') == 'adm' ? '' : 'front_';
        foreach(array(
            $prefix . 'page_sort_col' => 'col',
            $prefix . 'page_sort_dim' => 'dir',
        ) as $option => $key){
            if(AMI::issetOption($this->getModid(), $option)){
                $this->aDefaultOrder[$key] = AMI::getOption($this->getModid(), $option);
            }
        }
        $oRequest = AMI::getSingleton('env/request');
        $oCookies = AMI::getSingleton('env/cookie');
        $sortColumn = $oRequest->get('sort_column', $oCookies->get($this->getSerialId() . '_order_column', null));
        $sortDirection = $oRequest->get('sort_dir', $oCookies->get($this->getSerialId() . '_order_direction', null));

        if(is_null($sortColumn)){
            $sortColumn = $aEvent['oView']->getOrder()?$aEvent['oView']->getOrder():$this->aDefaultOrder['col'];
            $sortDirection = $aEvent['oView']->getOrderDirection()?$aEvent['oView']->getOrderDirection():$this->aDefaultOrder['dir'];
            $realSortColumn = $sortColumn;
        }else{
            $realSortColumn = $sortColumn;
            if(sizeof($this->aJoinedColumns)){
                foreach($this->aJoinedColumns as $alias => $aJoinedColumns){
                    foreach($aJoinedColumns as $column){
                        $joinedColumn = $alias . '_' . $column;
                        if($joinedColumn === $sortColumn){
                            $realSortColumn = $alias . '.' . $column;
                            break;
                        }
                    }
                }
            }
            $oTable = $this->getModel();
            if(is_string($realSortColumn)){
                $aParts = explode((strpos($realSortColumn, '.') !== false) ? '.' : '_', $realSortColumn);
                $alias = $aParts[0];
                // model fields support & list model expression fields support
                if((!$oTable->hasField($realSortColumn) && !$oTable->getList()->hasColumn($realSortColumn)) && !in_array($alias, $oTable->getActiveDependenceAliases())){
                    $realSortColumn = $this->aDefaultOrder['col'];
                    $sortDirection = $this->aDefaultOrder['dir'];
                }
            }
        }
        $aEvent['aScope']['order_column'] = $realSortColumn;
        $aEvent['aScope']['order_direction'] = $sortDirection;
        $oCookies->set($this->getSerialId() . '_order_column', $sortColumn, time() + 3600);
        $oCookies->set($this->getSerialId() . '_order_direction', $sortDirection, time() + 3600);
        return $aEvent;
    }

    /**#@-*/

    /**
     * Adds joined columns to the list.
     *
     * Example:
     * <code>
     * class MyModule_ListAdm extends AMI_ModListAdm{
     *     public function init(){
     *         // ...
     *         $this->addJoinedColumns(array('joined_field'), 'alias');
     *         // ...
     *     }
     * }
     *
     * class MyModule_ListViewAdm extends AMI_ModListView_JSON{
     *     public function init(){
     *         // ...
     *         $this->addColumn('alias_joined_field');
     *         // ...
     *     }
     * }
     *
     * class MyModule_TableList extends AMI_ModTableList{
     *     public function __construct(AMI_ModTable $oTable, DB_Query $oQuery){
     *         parent::__construct($oTable, $oQuery);
     *         // ...
     *         $this->addExpressionColumn('joined_field', '...', 'alias');
     *         // ...
     *     }
     * }
     * </code>
     *
     * @param array $aColumns  List of joined columns
     * @param string $alias    Dependence alias
     * @return AMI_ModListAdm
     * @since 5.14.0
     */
    protected function addJoinedColumns(array $aColumns, $alias){
        if(in_array($alias, $this->getModel()->getActiveDependenceAliases())){
            $modId = $this->getModel()->getDependenceResId($alias);
            if($modId){
                $this->aJoinedColumns[$alias] = $aColumns;
                AMI_Event::addHandler('on_query_add_joined_columns', array($this, 'handleCommonQueryAddJoinedColumns'), $modId);
                AMI_Event::addHandler('on_get_available_fields', array($this, 'handleCommonGetAvailableFields'), $modId);
            }
        }
        return $this;
    }

    /**
     * Adds actions to the item list.
     *
     * The are two actions supported by default: edit, delete.<br /><br />
     *
     * Example:
     * <code>
     * // AmiSample_ListAdm::__construct()
     * public function __construct(){
     *     $this->addActions(array(self::REQUIRE_FULL_ENV . 'edit', self::REQUIRE_FULL_ENV . 'delete'));
     * }
     * </code>
     *
     * @param  array  $aActions  Array of actions, use prefix self::REQUIRE_FULL_ENV for data modifying actions
     * @param  string $column    Adds inner action to the column if specified
     * @return AMI_ModListAdm
     * @see    AmiSample_ListActionsAdm::dispatchDelete()
     * @see    AmiSample_ListActionsAdm::dispatchInner()
     */
    protected function addActions(array $aActions, $column = ''){
        foreach($aActions as $action){
            $this->fullEnvAction($action);
            $this->aActions[] = $action;
            if($column !== ''){
                $this->aActionsXCols[$action] = $column;
            }
        }
        return $this;
    }

    /**
     * Adds hidden action and registers a callback.
     *
     * @param string $action  Action name
     * @return AMI_ModListAdm
     * @since 5.14.0
     */
    protected function addHiddenAction($action){
        $this->fullEnvAction($action);
        $this->addActionCallback('common', $action);
        return $this;
    }

    /**
     * Adds separate column actions to the item list.
     *
     * Example:
     * <code>
     * // AmiSample_ListAdm::__construct()
     * public function __construct(){
     *     // ...
     *     // Add separate column action
     *     $this->addColActions(array(self::REQUIRE_FULL_ENV . 'public'), true);
     *
     *     // Add separate column action
     *     $this->addColActions(array(self::REQUIRE_FULL_ENV . 'rename'));
     *     // ...
     * }
     * </code>
     *
     * @param  array $aActions        Array of actions, use prefix self::REQUIRE_FULL_ENV for data modifying actions
     * @param  bool  $valueDependend  Flag specifying image dependence on field value
     * @return AMI_ModListAdm
     * @since  5.12.4
     */
    protected function addColActions(array $aActions, $valueDependend = false){
        foreach($aActions as $action){
            $this->fullEnvAction($action);
            $this->aColActions[] = $action;
            if($valueDependend){
                $this->aValueDependendColActions[$action] = true;
            }
        }
        return $this;
    }

    /**
     * Add model supported group actions.
     *
     * Example:
     * <code>
     * // AmiSample_ListAdm::__construct()
     * public function __construct(){
     *     // ...
     *     // Add group actions with splitters
     *     $this->addGroupActions(
     *         array(
     *             array(self::REQUIRE_FULL_ENV . 'public', 'publish_section'),
     *             array(self::REQUIRE_FULL_ENV . 'unpublic', 'publish_section'),
     *             array(self::REQUIRE_FULL_ENV . 'rename', 'rename_section'),
     *             array(self::REQUIRE_FULL_ENV . 'delete', 'rename.before'),
     *         )
     *     );
     *     // ...
     * }
     * </code>
     *
     * @param  array $aActions  Array of actions, use prefix self::REQUIRE_FULL_ENV for data modifying actions
     * @return bool
     * @see    AMI_ModListAdm::init()
     */
    protected function addGroupActions(array $aActions){
        foreach($aActions as $action){
            if(is_array($action)){

                if(is_null($this->oGroupActionsView)){
                    $this->oGroupActionsView = new AMI_ModListGroupActionsView();
                }

                $name       = $action[0];
                $position   = isset($action[1]) ? $action[1] : '';
                $section    = '';

                $this->fullEnvAction($name, true);
                if(mb_strpos($name, 'grp_') === 0){
                    $placeholderName = mb_substr($name, 4);
                }

                if(mb_strlen($position)){
                    if(!mb_strpos($position, '.')){
                        $section = $position;
                        $position .= '.end';
                        if(!$this->oGroupActionsView->isPlaceholderExists($section)){
                            $this->oGroupActionsView->putPlaceholder($section, '', true);
                        }elseif(!$this->oGroupActionsView->isSection($section)){
                            trigger_error("Can not add section '" . $section . "' because placeholder with the same name already exists", E_USER_ERROR);
                        }
                    }
                }
                $this->oGroupActionsView->putPlaceholder($placeholderName, $position);
            }else{
                trigger_error("Group action '" . $action . "' expected to be an array", E_USER_ERROR);
            }
        }
        return true;
    }

    /**
     * Deletes group actions.
     *
     * Example:
     * // AmiSample_ListAdm::__construct()
     * public function __construct(){
     *     // ...
     *     // Delete 'public' and 'unpublic' group actions
     *     $this->deleteGroupActions(
     *         array(
     *             'public', 'unpublic'
     *         )
     *     );
     *     // ...
     * }
     * </code>
     *
     * @param  array $aActions  Array of action names to delete
     * @return void
     * @since  5.12.4
     * @see    AMI_ModListAdm::addGroupActions()
     * @amidev
     * @deprecated  Since 5.14.0
     */
    public function deleteGroupActions(array $aActions){
        AMI_Registry::set('_deprecated_error', TRUE);
        trigger_error('AMI_ModListAdm::deleteGroupActions() is deprecated, please refer to AMI_ModListAdm::dropActions() to get more details', E_USER_WARNING);
        if(!is_null($this->oGroupActionsView)){
            $this->oGroupActionsView->dropPlaceholders($aActions);
        }
    }

    /**
     * Returns groups actions view object.
     *
     * @return AMI_ModListGroupActionsView
     * @amidev Temporary
     */
    protected function getGroupActionsView(){
        return $this->oGroupActionsView;
    }

    /**
     * Add item list supported position actions.
     *
     * @param  array $aActions  Array of actions
     * @return AMI_ModListAdm
     * @see    AMI_ModListAdm::init()
     * @amidev Temporary
     */
    protected function addPositionActions(array $aActions){
        foreach($aActions as $action){
            $this->fullEnvAction($action);
            $this->aPositionActions[] = $action;
        }
        return $this;
    }

    /**
     * Discards previously added actions.
     *
     * Exaple:
     * <code>
     * class MySample_ListAdm extends AmiSample_ListAdm{
     *     public function init(){
     *         parent::init();
     *         // Discard edit action
     *         $this->dropActions(self::ACTION_COMMON, array('edit'));
     *         return $this;
     *     }
     * }
     * </code>
     *
     * @param  int   $type         Action type:
     *                             - AMI_ModListAdm::ACTION_COMMON - inner actions or actions placed at last column
     *                             - AMI_ModListAdm::ACTION_COLUMN - separate column actions
     *                             - AMI_ModListAdm::ACTION_POSITION - position actions
     *                             - AMI_ModListAdm::ACTION_GROUP - group actions
     *                             - AMI_ModListAdm::ACTION_ALL
     * @param  array $aActions     List of actions, all actions having specified type will be discarder if this parameter is not passed
     * @param  bool  $dropHandler  Flag specifying to drop action handler (since 5.14.8)
     * @return AMI_ModListAdm
     * @since  5.14.0
     * @todo   Full environment flags cleanup (aFullEnvActions)?
     */
    protected function dropActions($type, array $aActions = null, $dropHandler = TRUE){
        if($type & self::ACTION_COMMON){
            if(is_null($aActions)){
                if($dropHandler){
                    foreach($this->aActions as $action){
                        $this->dropActionHandler($action);
                    }
                }
                $this->aActions = array();
                $this->aActionsXCols = array();
            }else{
                foreach($aActions as $action){
                    $index = array_search($action, $this->aActions);
                    if($index !== FALSE){
                        if($dropHandler){
                            $this->dropActionHandler($action);
                        }
                        unset($this->aActions[$index]);
                    }
                    unset($this->aActionsXCols[$action]);
                }
            }
        }
        if($type & self::ACTION_COLUMN){
            if(is_null($aActions)){
                foreach($this->aColActions as $action){
                    if($dropHandler){
                        $this->dropActionHandler($action);
                    }
                    unset($this->aValueDependendColActions[$action]);
                }
                $this->aColActions = array();
            }else{
                foreach($aActions as $action){
                    $index = array_search($action, $this->aColActions);
                    if($index !== FALSE){
                        if($dropHandler){
                            $this->dropActionHandler($action);
                        }
                        unset($this->aColActions[$index]);
                    }
                    unset($this->aValueDependendColActions[$action]);
                }
            }
        }
        if($type & self::ACTION_POSITION){
            if(is_null($aActions)){
                foreach($this->aPositionActions as $action){
                    if($dropHandler){
                        $this->dropActionHandler($action);
                    }
                }
                $this->aPositionActions = array();
            }else{
                foreach($aActions as $action){
                    $index = array_search($action, $this->aPositionActions);
                    if($index !== FALSE){
                        if($dropHandler){
                            $this->dropActionHandler($action);
                        }
                        unset($this->aPositionActions[$index]);
                    }
                }
            }
        }
        if(($type & self::ACTION_GROUP) && !is_null($this->oGroupActionsView)){
            if(is_null($aActions)){
                $aActions = $this->oGroupActionsView->getPlaceholders();
            }
            $this->oGroupActionsView->dropPlaceholders($aActions);
            $this->aGroupActions = $this->oGroupActionsView->get();
        }
        return $this;
    }

    /**
     * Returns "actions" column actions or inner column actions.
     *
     * Used to override previously added actions.<br /><br />
     *
     * Example:
     * <code>
     * protected function getActions(){
     *     return array('edit', 'delete');
     * }
     * </code>
     *
     * @param  bool $inner  Returns inner column actions
     * @return array
     * @see    AMI_ModListAdm::addActions()
     * @amidev
     */
    protected function getActions($inner = false){
        return $inner ? $this->aActionsXCols : array_diff($this->aActions, array_keys($this->aActionsXCols));
    }

    /**
     * Returns actions having separate columns.
     *
     * Used to override previously added actions.<br /><br />
     *
     * Example:
     * <code>
     * // Discard any separate column actions
     * protected function getColActions(){
     *     return array();
     * }
     * </code>
     *
     * @return array
     * @see    AMI_ModListAdm::addActions()
     * @since  5.12.4
     * @amidev
     */
    protected function getColActions(){
        return $this->aColActions;
    }

    /**
     * Returns model supported group actions.
     *
     * @return array
     * @see    AMI_ModListAdm::init()
     * @amidev Temporary
     */
    protected function getGroupActions(){
        $aGroupActions = (is_null($this->oGroupActionsView)) ? $this->aGroupActions : $this->oGroupActionsView->get();
        $aEvent = array(
            'oView'         => $this,
            'aGroupActions' => &$aGroupActions
        );
        /**
         * Allows to modify group actions list.
         *
         * @event      on_list_group_actions $modId
         * @eventparam AMI_ModListAdm oView           View object
         * @eventparam array          &aGroupActions  Group actions
         */
        AMI_Event::fire('on_list_group_actions', $aEvent, $this->getModId());
        $size = sizeof($aGroupActions);
        if($size && $aGroupActions[$size - 1] === 'grp_|'){
            unset($aGroupActions[$size - 1]);
        }
        return $aGroupActions;
    }

    /**
     * Returns item list supported position actions.
     *
     * @return bool
     * @see    AMI_ModListAdm::init()
     * @amidev Temporary
     */
    protected function getPositionActions(){
        return $this->aPositionActions;
    }

    /**
     * Sets action callback for specified parameters.
     *
     * @param  string $type    Type of 'group' | 'position' | 'common'
     * @param  string $action  Action
     * @return void
     * @see    AMI_ModListAdm::init()
     * @amidev
     */
    protected function addActionCallback($type, $action = ''){
        switch($type){
            case 'common':
                $resId = $this->listActionsResId;
                break;
            case 'group':
                $resId = $this->listGrpActionsResId;
                break;
            case 'position':
                $resId = 'list/actions/position';
                break;
            default:
                trigger_error("Unknown action type '" . $type . "'", E_USER_ERROR);
        }
        $oHandler = AMI::getResource($resId, array(), true);
        AMI_Event::addHandler(
            'dispatch_mod_action_list_' . $action,
            array($oHandler, 'setActionData'),
            $this->getModId(),
            AMI_Event::PRIORITY_HIGH
        );
        AMI_Event::addHandler(
            'dispatch_mod_action_list_' . $action,
            array($oHandler, AMI::actionToHandler($action)),
            $this->getModId()
        );
    }

    /**
     * Parses self::REQUIRE_FULL_ENV action prefix, prepends 'grp_' prefix by condition.
     *
     * @param  string &$action  Action
     * @param  bool   $isGroup  Flag specifying to prepend 'grp_' prefix
     * @return void
     */
    private function fullEnvAction(&$action, $isGroup = false){
        if(mb_strpos($action, self::REQUIRE_FULL_ENV) === 0){
            $action = mb_substr($action, mb_strlen(self::REQUIRE_FULL_ENV));
            if($isGroup){
                $action = 'grp_' . $action;
            }
            $this->aFullEnvActions[$action] = true;
        }else{
            if($isGroup){
                $action = 'grp_' . $action;
            }
            unset($this->aFullEnvActions[$action]);
        }
    }

    /**
     * Drops action handler.
     *
     * @param  string $action  Action
     * @return void
     */
    private function dropActionHandler($action){
        $oHandler = AMI::getResource($this->listActionsResId, array(), true);
        AMI_Event::dropHandler(
            'dispatch_mod_action_list_' . $action,
            array($oHandler, 'setActionData'),
            $this->getModId()
        );
        AMI_Event::dropHandler(
            'dispatch_mod_action_list_' . $action,
            array($oHandler, AMI::actionToHandler($action)),
            $this->getModId()
        );
    }
}
