<?php
/**
 * AmiRelations/Relations configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiRelations_Relations
 * @version   $Id: AmiRelations_Relations_Adm.php 45082 2013-12-05 13:16:06Z Kolesnikov Artem $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiRelations/Relations configuration admin action controller.
 *
 * @package    Config_AmiRelations_Relations
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiRelations_Relations_Adm extends Hyper_AmiRelations_Adm{
    /**
     * Array of default components
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aDefaultComponents = array(
        // Selected items list filter
        array(
            'type'          => 'filter',
            'id'            => 'selected_filter',
            'related_to'    => array('selected_items_list'),
            'primary'       => false
        ),
        // Selected items
        array(
            'type'          => 'list',
            'id'            => 'selected_items_list',
            'resource'      => 'relations/list/controller/adm',
            'primary'       => false
        ),
        // Models items list filter
        array(
            'type'          => 'filter',
            'related_to'    => array('module_items_list'),
            'primary'       => false
        ),
        // Models items list
        array(
            'type'          => 'list',
            'id'            => 'module_items_list',
            'resource'      => 'relations/modules_list/controller/adm',
            'primary'       => false
        )
    );

    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request object
     * @param AMI_Response $oResponse  Response object
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        parent::__construct($oRequest, $oResponse);
        $this->addComponents($this->aDefaultComponents);
        AMI::addResource('relations/modules_list/controller/adm', 'AmiRelations_Relations_ModulesListAdm');
        AMI::addResource('relations/modules_list/view/adm', 'AmiRelations_Relations_ModulesListViewAdm');

        AMI::addResource('relations_array_iterator/table/model', 'AmiRelations_Relations_ArrayIterator');
        AMI::addResource('relations_array_iterator/table/model/item', 'AmiRelations_Relations_ArrayIteratorItem');
        AMI::addResource('relations_array_iterator/table/model/list', 'AmiRelations_Relations_ArrayIteratorList');

        if(!$oRequest->get('moduleId', false) || !$oRequest->get('itemId', false)){
            trigger_error('Unsupported relations module working mode: moduleId or itemId not set', E_USER_ERROR);
        }
    }
}

/**
 * AmiRelations/Relations configuration model.
 *
 * @package    Config_AmiRelations_Relations
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiRelations_Relations_State extends Hyper_AmiRelations_State{
}

/**
 * AmiRelations/Relations configuration admin filter component action controller.
 *
 * @package    Config_AmiRelations_Relations
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiRelations_Relations_FilterAdm extends Hyper_AmiRelations_FilterAdm{

    /**
     * Initialization.
     *
     * @return $this
     */
    public function init(){
        parent::init();
        $aAllModules = AmiRelations_Relations_Service::getSupportedModules();
        if($this->getSerialId() != 'selected_filter'){
            $modname = AMI::getSingleton('env/request')->get('modname', AMI::getSingleton('env/cookie')->get('filter_selected_module', $aAllModules[0]));
            AMI_Event::addHandler('on_list_recordset', array($this, 'handleListRecordset'), $modname);
        }else{
            $modname = 'relations_array_iterator';
            AMI_Event::dropHandler('on_list_recordset', array($this, 'handleListRecordset'), $this->getModId());
            AMI_Event::addHandler('on_list_recordset', array($this, 'handleListRecordsetAI'), $modname);
        }
        return $this;
    }

    /**
     * List recordset handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListRecordsetAI($name, array $aEvent, $handlerModId, $srcModId){
        $aSearchCondition = array();
        $header = $this->oItem->getValue('header');
        $module = $this->oItem->getValue('module');
        if($module){
            $aSearchCondition['module'] = $module;
        }
        if($header){
            $aSearchCondition['header'] = $header;
        }
        if(count($aSearchCondition)){
            $aEvent['oList']->addSearchCondition($aSearchCondition);
        }
        return $aEvent;
    }

    /**
     * Returns true if component needs to be started always in full environment.
     *
     * @return bool
     */
    public function isFullEnv(){
        return true;
    }
}

/**
 * AmiRelations/Relations configuration item list component filter model.
 *
 * @package    Config_AmiRelations_Relations
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiRelations_Relations_FilterModelAdm extends Hyper_AmiRelations_FilterModelAdm{
    /**
     * Constructor.
     */
    public function __construct(){
        $aAllModules = $this->getModulesList();
        if(AMI::getSingleton('env/request')->get('componentId', false) != 'selected_filter'){
            $this->addViewField(
                array(
                    'name'          => 'modname',
                    'type'          => 'select',
                    'flt_condition' => '=',
                    'data'          => $aAllModules,
                    'flt_default'   => $aAllModules[0]['id'],
                    'session_field' => true,
                    'flt_column'    => 'id'
                )
            );
        }else{
            $this->addViewField(
                array(
                    'name'          => 'module',
                    'type'          => 'select',
                    'flt_condition' => '=',
                    'data'          => $this->getModulesList(true),
                    'flt_default'   => '',
                    'session_field' => true,
                    'flt_column'    => 'id'
                )
            );
        }

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
    }

    /**
     * Returns array of modules for filter field.
     *
     * @param bool $addAll  Add "all" option
     * @return array
     */
    protected function getModulesList($addAll = false){
        $aModules = $this->getPossibleModulesCaptions();
        $aResult = $addAll ? array(array('id' => '', 'caption' => 'all')) : array();
        foreach($aModules as $modId => $caption){
            $aResult[] = array('id' => $modId, 'name' => $caption);
        }
        return $aResult;
    }

    /**
     * Returns associative array of modules ids and their captions.
     *
     * @return array
     */
    protected function getPossibleModulesCaptions(){
        $possibleModules = AMI::getProperty('relations', 'possible_modules_list');
        $supportedModules = array_keys(AMI_Ext::getSupportedModules('ext_relations'));
        $modules = array_merge($possibleModules, $supportedModules);
        $oDeclarator = AMI_ModDeclarator::getInstance();
        foreach($modules as $modId){
            if(!AMI::isResource($modId . '/table/model')){
                continue;
            }
            $section = $oDeclarator->getSection($modId);
            $aModCaptions = AMI_Service_Adm::getModulesCaptions(array($modId), true, array($section), true);
            $aModules[$modId] = isset($aModCaptions[$modId]) ? $aModCaptions[$modId] : $modId;
        }

        return $aModules;
    }

    /**
     * Handle id_page filter custom logic.
     *
     * @param  string $field  Field name
     * @param  array  $aData  Filter data
     * @return array
     */
    protected function processFieldData($field, array $aData){
        if($field == 'modname'){
            AMI::getSingleton('env/cookie')->set('filter_selected_module', $aData['value'], time() + AMI_ServerCookie::LIFETIME_YEAR);
            $oRequest = AMI::getSingleton('env/request');
            $moduleId = $oRequest->get('moduleId', false);
            $itemId = $oRequest->get('itemId', false);
            $oModel = AMI::getResourceModel('relations/table');
            $oItem =
                $oModel->findByFields(
                    array(
                        'module' => $moduleId,
                        'id_object' => $itemId,
                        'related_module' => $aData['value']
                    ),
                    array(
                        'id',
                        'related_objects'
                    )
                );
            if($oItem->getId()){
                $objects = trim($oItem->related_objects, ',');
                if($objects){
                    $aData['forceSQL'] = ' AND i.id NOT IN(' . $objects . ') ';
                }
            }else{
                $aData['skip'] = true;
            }
        }
        return $aData;
    }
}

/**
 * AmiRelations/Relations configuration admin filter component view.
 *
 * @package    Config_AmiRelations_Relations
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiRelations_Relations_FilterViewAdm extends Hyper_AmiRelations_FilterViewAdm{
    /**
     * Filter default elemnts template (placeholders)
     *
     * @var array
     */
    protected $aPlaceholders = array('#filter', 'modname', 'module', 'header', 'filter');
}

/**
 * AmiRelations/Relations configuration admin form component action controller.
 *
 * @package    Config_AmiRelations_Relations
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiRelations_Relations_FormAdm extends Hyper_AmiRelations_FormAdm{
}

/**
 * AmiRelations/Relations configuration form component view.
 *
 * @package    Config_AmiRelations_Relations
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiRelations_Relations_FormViewAdm extends Hyper_AmiRelations_FormViewAdm{
}

/**
 * AmiRelations/Relations configuration admin list component action controller.
 *
 * @package    Config_AmiRelations_Relations
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiRelations_Relations_ListAdm extends Hyper_AmiRelations_ListAdm{
    /**
     * Initialization.
     *
     * @return AMI_ModList
     */
    public function init(){
        $this->listActionsResId = $this->getModId() . '/list_actions/controller/adm';
        $this->listGrpActionsResId = $this->getModId() . '/list_group_actions/controller/adm';
        parent::init();
        $this->addActions(array(self::REQUIRE_FULL_ENV . 'remove'));
        $this->addGroupActions(array(array(self::REQUIRE_FULL_ENV . 'remove')));
        $this->addActionCallback('common', 'remove');
        $this->addActionCallback('group', 'grp_remove');
        return $this;
    }

    /**
     * Returns prepared module model.
     *
     * @return AmiRelations_Relations_Table
     */
    protected function initModel(){
        $aData = array();
        $oRequest = AMI::getSingleton('env/request');
        $oDB = AMI::getSingleton('db');
        $oResult =
            $oDB->select(
                DB_Query::getSnippet('SELECT id, related_module, related_objects FROM cms_relations WHERE module = %s AND id_object = %s')
                ->q($oRequest->get('moduleId'))
                ->q($oRequest->get('itemId'))
            );
        foreach($oResult as $aRow){
            $aObjects = explode(',', $aRow['related_objects']);
            foreach($aObjects as $objectId){
                if((int)$objectId){
                    $oItem = AMI::getResourceModel($aRow['related_module'] . '/table')->find($objectId, array('id', 'public', 'header', 'announce'));
                    if($oItem && $oItem->getId()){
                        $aData[] = array(
                            'module'        => $aRow['related_module'],
                            'id'            => $aRow['id'] . '|' . $objectId,
                            'public'        => $oItem->public,
                            'header'        => $oItem->header,
                            'announce'      => $oItem->announce
                        );
                    }
                }
            }
        }
        $oModel = AMI::getResourceModel('relations_array_iterator/table/model', array($aData));
        return $oModel;
    }

    /**
     * Returns true if component needs to be started always in full environment.
     *
     * @return bool
     */
    public function isFullEnv(){
        return true;
    }
}

/**
 * AmiRelations/Relations configuration admin list component view.
 *
 * @package    Config_AmiRelations_Relations
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiRelations_Relations_ListViewAdm extends Hyper_AmiRelations_ListViewAdm{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'module';

    /**
     * Order column direction
     *
     * @var bool
     */
    protected $orderDirection = 'asc';

    /**
     * Cached modules names
     *
     * @var array
     */
    protected $aModulesNames = array();

    /**
     * List default elemnts template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#list_header',
            '#flags', 'flags',
            '#common', 'common',
            '#columns', 'module', 'public', 'header', 'announce', 'columns',
            '#actions', 'actions',
        'list_header'
    );

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiRelations_Relations_ListViewAdm
     */
    public function init(){
        parent::init();
         $this
            ->addColumnType('id', 'hidden')
            ->addColumnType('module', 'mediumtext')
            ->addColumn('public')
            ->setColumnWidth('public', 'extra-narrow')
            ->setColumnAlign('public', 'center')
            ->addColumnType('header', 'mediumtext')
            ->addColumnType('announce', 'longtext')
            ->setColumnTensility('module')
            ->setColumnTensility('header')
            ->addSortColumns(
                array(
                    'module',
                    'public',
                    'header',
                    'announce'
                )
            );

        $this->formatColumn(
            'module',
            array($this, 'fmtModuleName'),
            array()
        );

        $this->formatColumn(
            'public',
            array($this, 'fmtColIcon'),
            array(
                'class'             => 'public',
                'has_inactive'      => true,
                'caption'           => $this->aLocale['list_public'],
                'caption_inactive'  => $this->aLocale['list_public_inactive']
            )
        );

        // Truncate 'header' column by 30 symbols
        $this->formatColumn(
            'header',
            array($this, 'fmtTruncate'),
            array(
                'length'       => 45
            )
        );

        // Truncate 'announce' column by 4096 symbols
        $this->formatColumn(
            'announce',
            array($this, 'fmtTruncate'),
            array(
                'length'       => 4096,
                'doStripTags'  => true,
                'doHTMLEncode' => false
            )
        );
        $this->addScriptCode($this->parse('javascript'));

        AMI_Event::addHandler('on_list_view', array($this, 'handleTotal'), $this->getModId());

        return $this;
    }

    /**
     * List recordset handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleTotal($name, array $aEvent, $handlerModId, $srcModId){
        $oRequest = AMI::getSingleton('env/request');
        $moduleId = $oRequest->get('moduleId', false);
        $itemId = $oRequest->get('itemId', false);
        $oModel = AMI::getResourceModel($this->getModId() . '/table');
        $oList = $oModel->getList()->addColumns(array('related_objects'))->addWhereDef(DB_Query::getSnippet(' AND module = %s AND id_object = %s')->q($moduleId)->q($itemId))->load();
        // array('module' => $moduleId, 'id_object' => $itemId)
        $total = 0;
        if($oList){
            foreach($oList as $oItem){
                $aObjects = explode(',', $oItem->related_objects);
                if(count($aObjects) > 2){
                    $total += (count($aObjects) - 2);
                }
            }
        }
        $aEvent['aResponse']['totalItems'] = $total;
        return $aEvent;
    }

    /**
     * Module name formatter.
     *
     * @param  string $value  Value to format
     * @param  array $aArgs   Arguments
     * @return string
     */
    protected function fmtModuleName($value, array $aArgs){
        $modId = $value;
        if(!isset($this->aModulesNames[$modId])){
            $aModuleNames = AMI::getSingleton('env/template_sys')->parseLocale('templates/lang/_menu_all.lng');
            $aOwnersNames = AMI::getSingleton('env/template_sys')->parseLocale('templates/lang/_menu_owners.lng');
            $aCaption = array();
            $oDeclarator = AMI_ModDeclarator::getInstance();
            if($oDeclarator->isRegistered($modId)){
                // 6.0 Instance
                $aCaption[] = $aModuleNames[$oDeclarator->getSection($modId)];
                $parentModId = $oDeclarator->getParent($modId);
                if(!is_null($parentModId)){
                    $aCaption[] = $aModuleNames[$parentModId];
                }
            }else{
                // 5.0 Module
                if(isset($GLOBALS['Core']) && ($GLOBALS['Core'] instanceof CMS_Core)){
                    $oModule = $GLOBALS['Core']->GetModule($modId);
                    $aCaption = array ($aOwnersNames[$oModule->GetOwnerName()]);
                    if($oModule->HaveParent()){
                        $aCaption[] = $aModuleNames[$oModule->GetParentName()];
                    }
                }
            }
            $aCaption[] = isset($aModuleNames[$modId]) ? $aModuleNames[$modId] : $modId;
            $this->aModulesNames[$modId] = implode(' : ', $aCaption);
        }
        return $this->aModulesNames[$modId];
    }
}

/**
 * AmiRelations/Relations configuration admin list component action controller.
 *
 * @package    Config_AmiRelations_Relations
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiRelations_Relations_ModulesListAdm extends Hyper_AmiRelations_ListAdm{
    /**
     * Initialization.
     *
     * @return AMI_ModList
     */
    public function init(){
        // AMI::getSingleton('db')->displayQueries();
        $this->listActionsResId = $this->getModId() . '/list_actions/controller/adm';
        $this->listGrpActionsResId = $this->getModId() . '/list_group_actions/controller/adm';
        parent::init();
        $this->addActions(array(self::REQUIRE_FULL_ENV . 'add_subitem'));
        $this->addGroupActions(array(array(self::REQUIRE_FULL_ENV . 'add')));
        $this->addActionCallback('common', 'add_subitem');
        $this->addActionCallback('group', 'grp_add');
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

        $oView = $this->_getView('/modules_list/view/adm');
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

    /**
     * Initializes model.
     *
     * @return AMI_ModTable
     * @amidev Temporary
     */
    protected function initModel(){
        $aAllMods = AmiRelations_Relations_Service::getSupportedModules();
        $modId = AMI::getSingleton('env/request')->get('modname', AMI::getSingleton('env/cookie')->get('filter_selected_module', $aAllMods[0]));
        return AMI::getResourceModel($modId . '/table');
    }

    /**
     * Returns module Id.
     *
     * @return string
     */
    public function getModId(){
        return 'relations';
    }
}

/**
 * AmiRelations/Relations configuration admin list component view.
 *
 * @package    Config_AmiRelations_Relations
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiRelations_Relations_ModulesListViewAdm extends Hyper_AmiRelations_ListViewAdm{
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
     * List default elemnts template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#list_header',
            '#flags', 'flags',
            '#common', 'common',
            '#columns', 'public', 'header', 'announce', 'columns',
            '#actions', 'actions',
        'list_header'
    );

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiRelations_Relations_ListViewAdm
     */
    public function init(){
        parent::init();
        $this
            ->addColumnType('id', 'hidden')
            ->addColumn('public')
            ->setColumnWidth('public', 'extra-narrow')
            ->setColumnAlign('public', 'center')
            ->addColumnType('header', 'mediumtext')
            ->addColumnType('announce', 'longtext')
            ->setColumnTensility('header')
            ->addSortColumns(
                array(
                    'public',
                    'header',
                    'announce',
                    'date_created',
                )
            );

        $this->formatColumn(
            'public',
            array($this, 'fmtColIcon'),
            array(
                'class'             => 'public',
                'has_inactive'      => true,
                'caption'           => $this->aLocale['list_public'],
                'caption_inactive'  => $this->aLocale['list_public_inactive']
            )
        );

        // Truncate 'header' column by 30 symbols
        $this->formatColumn(
            'header',
            array($this, 'fmtTruncate'),
            array(
                'length'       => 45
            )
        );

        // Truncate 'announce' column by 4096 symbols
        $this->formatColumn(
            'announce',
            array($this, 'fmtTruncate'),
            array(
                'length'       => 4096,
                'doStripTags'  => true,
                'doHTMLEncode' => false
            )
        );
        $this->addScriptCode($this->parse('javascript'));
        return $this;
    }

    /**
     * Returns module Id.
     *
     * @return string
     */
    public function getModId(){
        return 'relations';
    }
}

/**
 * AmiRelations/Relations configuration module admin list actions controller.
 *
 * @package    Config_AmiRelations_Relations
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiRelations_Relations_ListActionsAdm extends Hyper_AmiRelations_ListActionsAdm{
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
    public function dispatchRemove($name, array $aEvent, $handlerModId, $srcModId){
        $oDB = AMI::getSingleton('db');
        $oRequest = AMI::getSingleton('env/request');
        $moduleId = $oRequest->get('moduleId', false);
        $itemId = $oRequest->get('itemId', false);
        $targetIds = $oRequest->get('mod_action_id', false);
        list($id, $targetId) = explode('|', $targetIds);
        $deleted = false;
        if($moduleId && $itemId && $id && $targetId){
            $oModel = AMI::getResourceModel($aEvent['tableModelId']);
            $oItem = $oModel->find($id);
            if($oItem->getId()){
                $aObjects = explode(',', $oItem->related_objects);
                $aRealObjects = array();
                foreach($aObjects as $objectId){
                    if($objectId != $targetId){
                        $aRealObjects[] = $objectId;
                    }
                }
                if(count($aRealObjects) > 2){
                    $oItem->related_objects = implode(',', $aRealObjects);
                    $oItem->save();
                }else{
                    $oItem->delete();
                }
                $deleted = true;
            }
        }
        if(!$deleted){
            $statusMsg = 'status_del_fail';
            $aEvent['failed'] = true;
        }else{
            $statusMsg = 'status_del';
        }
        $aEvent['oResponse']->addStatusMessage($statusMsg);
        $this->refreshView();
        return $aEvent;
    }

    /**
     * Dispatches 'add_subitem' action.
     *
     * Deletes item.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchAddSubitem($name, array $aEvent, $handlerModId, $srcModId){
        $oDB = AMI::getSingleton('db');
        $oRequest = AMI::getSingleton('env/request');
        $moduleId = $oRequest->get('moduleId', false);
        $itemId = $oRequest->get('itemId', false);
        $aAllMods = AmiRelations_Relations_Service::getSupportedModules();
        $targetModuleId = $oRequest->get('modname', AMI::getSingleton('env/cookie')->get('filter_selected_module', $aAllMods[0]));
        $targetId = (int)$oRequest->get('mod_action_id', false);
        $added = false;
        if($moduleId && $itemId && $targetId){
            $oModel = AMI::getResourceModel($aEvent['tableModelId']);
            $oItem =
                $oModel->findByFields(
                    array(
                        'module' => $moduleId,
                        'id_object' => $itemId,
                        'related_module' => $targetModuleId
                    ),
                    array(
                        'id',
                        'related_objects'
                    )
                );

            if($oItem->getId()){
                $aObjects = explode(',', $oItem->related_objects);
                $aRealObjects = array();
                foreach($aObjects as $id){
                    if($id){
                        $aRealObjects[] = $id;
                    }
                }
                if(!in_array($targetId, $aRealObjects)){
                    $aRealObjects[] = $targetId;
                }
                $aObjects = array_merge(array(""), $aRealObjects);
                $aObjects[] = "";
                $oItem->related_objects = implode(',', $aObjects);
                $oItem->save();
                $added = true;
            }else{
                $oItem->module = $moduleId;
                $oItem->id_object = $itemId;
                $oItem->related_module = $targetModuleId;
                $oItem->related_objects = implode(',', array("", $targetId, ""));
                $oItem->save();
                $added = true;
            }
        }
        $aEvent['oResponse']->addStatusMessage($added ? 'status_added' : 'status_not_added', array(), $added ? AMI_Response::STATUS_MESSAGE : AMI_Response::STATUS_MESSAGE_ERROR);
        if(!$added){
            $aEvent['failed'] = true;
        }
        $this->refreshView();
        return $aEvent;
    }
}

/**
 * AmiRelations/Relations configuration module admin list group actions controller.
 *
 * @package    Config_AmiRelations_Relations
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiRelations_Relations_ListGroupActionsAdm extends Hyper_AmiRelations_ListGroupActionsAdm{
    /**
     * Dispatches 'grp_add' group action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchGrpAdd($name, array $aEvent, $handlerModId, $srcModId){
        $oDB = AMI::getSingleton('db');
        $oRequest = AMI::getSingleton('env/request');
        $moduleId = $oRequest->get('moduleId', false);
        $itemId = $oRequest->get('itemId', false);
        $aAllMods = AmiRelations_Relations_Service::getSupportedModules();
        $targetModuleId = $oRequest->get('modname', AMI::getSingleton('env/cookie')->get('filter_selected_module', $aAllMods[0]));
        $aRequestIds = $this->getRequestIds($aEvent);
        $added = 0;
        if($moduleId && $itemId && count($aRequestIds)){
            $oModel = AMI::getResourceModel($aEvent['tableModelId']);
            $oItem =
                $oModel->findByFields(
                    array(
                        'module' => $moduleId,
                        'id_object' => $itemId,
                        'related_module' => $targetModuleId
                    ),
                    array(
                        'id',
                        'related_objects'
                    )
                );

            if($oItem->getId()){
                $aObjects = explode(',', $oItem->related_objects);
                $aRealObjects = array();
                foreach($aObjects as $id){
                    if($id){
                        $aRealObjects[] = $id;
                    }
                }
                $oldSize = count($aRealObjects);
                $aRealObjects = array_unique(array_merge($aRealObjects, $aRequestIds));
                $newSize = count($aRealObjects);
                $aObjects = array_merge(array(""), $aRealObjects);
                $aObjects[] = "";
                $oItem->related_objects = str_replace(" ", "", implode(',', $aObjects));
                $oItem->save();
                $added = $newSize - $oldSize;
            }else{
                $oItem->module = $moduleId;
                $oItem->id_object = $itemId;
                $oItem->related_module = $targetModuleId;
                $oItem->related_objects = str_replace(" ", "", implode(',', array("", implode(",", $aRequestIds), "")));
                $oItem->save();
                $added = count($aRequestIds);
            }
        }
        $aEvent['oResponse']->addStatusMessage('status_grp_added', array('num_items' => $added));
        $this->refreshView();
        return $aEvent;
    }

    /**
     * Dispatches 'grp_add' group action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchGrpRemove($name, array $aEvent, $handlerModId, $srcModId){
        $oDB = AMI::getSingleton('db');
        $oRequest = AMI::getSingleton('env/request');
        $moduleId = $oRequest->get('moduleId', false);
        $itemId = $oRequest->get('itemId', false);
        $aRequestIds = $this->getRequestIds($aEvent);
        $deleted = 0;
        $aDeleteInstructions = array();

        foreach($aRequestIds as $ids){
            list($itemId, $objectId) = explode('|', $ids);
            if(!isset($aDeleteInstructions['item_' . $itemId])){
                $aDeleteInstructions['item_' . $itemId] = array(
                    'itemId' => $itemId,
                    'objects' => array()
                );
            }
            $aDeleteInstructions['item_' . $itemId]['objects'][] = $objectId;
            $aDeleteInstructions['item_' . $itemId]['objects'] = array_unique($aDeleteInstructions['item_' . $itemId]['objects']);
        }

        foreach($aDeleteInstructions as $aItem){
            $oModel = AMI::getResourceModel($aEvent['tableModelId']);
            $oItem = $oModel->find($aItem['itemId'], array('id', 'related_objects'));
            if($oItem->getId()){
                $aNewObjects = array();
                $aObjects = explode(',', $oItem->related_objects);
                foreach($aObjects as $objectId){
                    if(!in_array($objectId, $aItem['objects'])){
                        $aNewObjects[] = $objectId;
                    }else{
                        $deleted++;
                    }
                }
                if(count($aNewObjects) > 2){
                    $oItem->related_objects = implode(',', $aNewObjects);
                    $oItem->save();
                }else{
                    $oItem->delete();
                }
            }
        }

        $aEvent['oResponse']->addStatusMessage('status_grp_del', array('num_items' => $deleted));
        $this->refreshView();
        return $aEvent;
    }
}

/**
 * AmiRelations/Relations configuration table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Config_AmiRelations_Relations
 * @subpackage Model
 * @resource   {$modId}_array_iterator/table/model <code>AMI::getResourceModel('{$modId}_array_iterator/table')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiRelations_Relations_ArrayIterator extends AMI_ArrayIterator{
    /**
     * Returns module Id.
     *
     * @return string
     */
    public function getModId(){
        return 'relations_array_iterator';
    }
}

/**
 * AmiRelations/Relations configuration table item model.
 *
 * @package    Config_AmiRelations_Relations
 * @subpackage Model
 * @resource   {$modId}_array_iterator/table/model/item <code>AMI::getResourceModel('{$modId}_array_iterator/table')->getItem()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiRelations_Relations_ArrayIteratorItem extends AMI_ArrayIteratorItem{
    /**
     * Returns module Id.
     *
     * @return string
     */
    public function getModId(){
        return 'relations_array_iterator';
    }
}

/**
 * AmiRelations/Relations configuration table list model.
 *
 * @package    Config_AmiRelations_Relations
 * @subpackage Model
 * @resource   {$modId}_array_iterator/table/model/list <code>AMI::getResourceModel('{$modId}_array_iterator/table')->getList()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiRelations_Relations_ArrayIteratorList extends AMI_ArrayIteratorList{
    /**
     * Returns module Id.
     *
     * @return string
     */
    public function getModId(){
        return 'relations_array_iterator';
    }
}

/**
 * AmiRelations/Relations service class.
 *
 * @package    Config_AmiRelations_Relations
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiRelations_Relations_Service {
    /**
     * Returns array of supported modules ids.
     *
     * @return array
     */
    public static function getSupportedModules(){
        $possibleModules = AMI::getProperty('relations', 'possible_modules_list');
        $supportedModules = array_keys(AMI_Ext::getSupportedModules('ext_relations'));
        $modules = array_merge($possibleModules, $supportedModules);
        $aAllModules = array();
        foreach($modules as $modId){
            if(!AMI::isResource($modId . '/table/model')){
                continue;
            }
            $aAllModules[] = $modId;
        }
        return $aAllModules;
    }
}