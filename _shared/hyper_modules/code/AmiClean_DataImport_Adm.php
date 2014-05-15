<?php
/**
 * AmiClean/DataImport configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiClean_DataImport
 * @version   $Id: AmiClean_DataImport_Adm.php 48056 2014-02-19 07:43:55Z Kolesnikov Artem $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiClean/DataImport module admin action controller.
 *
 * @package    Config_AmiClean_DataImport
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_DataImport_Adm extends Hyper_AmiClean_Adm{
    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request
     * @param AMI_Response $oResponse  Response
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        parent::__construct($oRequest, $oResponse);
        $this->addComponents(array('filter', 'list', 'form'));
    }
}

/**
 * AmiClean/DataImport module model.
 *
 * @package    Config_AmiClean_DataImport
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_DataImport_State extends Hyper_AmiClean_State{
}

/**
 * AmiClean/DataImport module admin filter component action controller.
 *
 * @package    Config_AmiClean_DataImport
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_DataImport_FilterAdm extends Hyper_AmiClean_FilterAdm{
}

/**
 * AmiClean/DataImport  module item list component filter model.
 *
 * @package    Config_AmiClean_DataImport
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_DataImport_FilterModelAdm extends Hyper_AmiClean_FilterModelAdm{
    /**
     * Common fields for the filter
     *
     * @var array
     */
    protected $aCommonFields = array('datefrom', 'dateto');

    /**
     * Driver array
     *
     * @var array
     */
    protected $aDrivers = array(
        0 => array('caption' => 'ami_no_driver_name', 'value' => ''),
    );

    /**
     * Constructor.
     */
    public function __construct(){

        // get drivers list
        if(false == file_exists(AMI_Registry::get('path/root') . '_local/data_import_drivers.ini.php')){
            trigger_error('Configuration file for data import drivers is not found', E_USER_ERROR);
        }

        $this->aConfig = parse_ini_file(AMI_Registry::get('path/root') . '_local/data_import_drivers.ini.php', TRUE);

        if(is_array($this->aConfig) && !empty($this->aConfig)){
            // add enabled drivers to select list
            foreach($this->aConfig as $driverName => $aDriverConfig){
                if(isset($aDriverConfig['enabled']) && $aDriverConfig['enabled'] == 1){
                    $this->aDriversConfig[$driverName] = $aDriverConfig;
                    $this->aDrivers[] =
                        array(
                            'caption'   => $driverName . '_driver_name',
                            'value'     => $driverName,
                        );
                }
            }
        }

        // add filter fields
        $this->addViewField(
            array(
                'name'          => 'import_type',
                'type'          => 'select',
                'flt_type'      => 'text',
                'data'          => $this->aDrivers,
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'driver_name'
            )
        );

        $this->addViewField(
            array(
                'name'          => 'frequency',
                'type'          => 'checkbox',
                'flt_type'      => 'text',
                'flt_default'   => '0',
                'flt_condition' => '=',
                'flt_column'    => 'frequency',
            )
        );

        $this->addViewField(
            array(
                'name'          => 'last_success',
                'type'          => 'checkbox',
                'flt_type'      => 'text',
                'flt_default'   => '0',
                'flt_condition' => '=',
                'flt_column'    => 'last_success',
            )
        );

        $this->addViewField(
            array(
                'name'          => 'datefrom',
                'type'          => 'datefrom',
                'flt_type'      => 'date',
                'flt_default'   => AMI_Lib_Date::formatUnixTime(AMI_Lib_Date::UTIME_MIN),
                'flt_condition' => '>=',
                'flt_column'    => 'date_nextimport',
            )
        );

        $this->addViewField(
            array(
                'name'          => 'dateto',
                'type'          => 'dateto',
                'flt_type'      => 'date',
                'flt_default'   => AMI_Lib_Date::formatUnixTime(AMI_Lib_Date::UTIME_MAX),
                'flt_condition' => '<',
                'flt_column'    => 'date_nextimport',
            )
        );
    }

    /**
     * Preprocess fields before making the query.
     *
     * @param string $field  Field name
     * @param array  $aData  Field data
     * @return array|void
     */
    public function processFieldData($field, array $aData){
        switch($field){
            // setting up filter by only successed tasks
            case 'last_success':
                if(!isset($aData['value']) || $aData['value'] == 0){
                    $aData['skip'] = true;
                }
                break;

            // setting up filter by manual start
            case 'frequency':
                if(isset($aData['value']) && $aData['value'] == 1){
                    $aData['forceSQL'] = ' AND (i.`frequency` = 0) ';
                }else{
                    $aData['skip'] = true;
                }
                break;
        }

        return $aData;
    }
}

/**
 * AmiClean/DataImport  module admin filter component view.
 *
 * @package    Config_AmiClean_DataImport
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_DataImport_FilterViewAdm extends Hyper_AmiClean_FilterViewAdm{
}

/**
 * AmiClean/DataImport module admin form component action controller.
 *
 * @package    Config_AmiClean_DataImport
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_DataImport_FormAdm extends Hyper_AmiClean_FormAdm{
    /**
     * Returns true if component needs to be started always in full environment.
     *
     * @return bool
     */
    public function isFullEnv(){
        return true;
    }

    /**
     * Initiaize additional dispather.
     *
     * @return $this|AMI_ModForm
     */
    public function init(){
        parent::init();
        AMI_Event::addHandler('on_after_save_model_item', array($this, 'dispatchAfterSave'), $this->getModId());
        return $this;
    }

    /**
     * Show status message if data is not saved.
     *
     * @param array &$aEvent  Event array
     * @return array|void
     */
    protected function onSaveException(array &$aEvent){
        $aError = $aEvent['oException']->getData();

        if(isset($aError[0]['message'])){
            AMI::getSingleton('response')->addStatusMessage(
                $aError[0]['message'],
                array(),
                AMI_Response::STATUS_MESSAGE_ERROR
            );
        }

        return $aEvent;
    }

    /**
     * Dispatch after save event.
     *
     * @param string $name         Event name
     * @param array $aEvent        Event array
     * @param mixed $handlerModId  Handler mod id
     * @param mixed $srcModId      Source mod id
     * @return array
     */
    public function dispatchAfterSave($name, array $aEvent, $handlerModId, $srcModId){

        // data was saved succesfully
        if($aEvent['success']){
            // task data
            $aTaskData = array(
                'handler'           => DB_Query::getSnippet('%s')->q($aEvent['oItem']->driver_name),
                'module'            => DB_Query::getSnippet('%s')->q($this->getModId()),
                'type'              => 1,
                'is_sheduled'       => 1,
                'update_start'      => DB_Query::getSnippet('%s')->q($aEvent['oItem']->update_start),
                'next_execution'    => DB_Query::getSnippet('%s')->q($aEvent['oItem']->date_nextimport),
                'import_task_id'    => $aEvent['oItem']->id,
            );

            // is it new task?
            if(true == $aEvent['onCreate']){
                $taskQuery = DB_Query::getInsertQuery('cms_processes', $aTaskData);
            }else{
                // checks that task really exist
                $oSnippet =
                    DB_Query::getSnippet("SELECT module FROM cms_processes WHERE import_task_id = %s")
                        ->plain($aEvent['oItem']->id);
                $oResult = AMI::getSingleton('db')->select($oSnippet);
                // task not exists? (row delete from DB manually etc.)
                if($oResult->count() == 0){
                    $taskQuery = DB_Query::getInsertQuery('cms_processes', $aTaskData);
                }else{
                    $taskQuery = DB_Query::getUpdateQuery('cms_processes', $aTaskData, (' WHERE import_task_id = ' . $aEvent['oItem']->id));
                }
            }
            $result = AMI::getSingleton('db')->query($taskQuery);
            if(isset($GLOBALS["oCache"])){
                $GLOBALS["oCache"]->SetOption('background_process', true);
                $GLOBALS["oCache"]->saveOptions();
            }
        }

        return $aEvent;
    }
}

/**
 * AmiClean/DataImport module form component view.
 *
 * @package    Config_AmiClean_DataImport
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_DataImport_FormViewAdm extends Hyper_AmiClean_FormViewAdm{
    /**
     * Form view placeholders
     *
     * @var array
     */
    protected $aPlaceholders = array('#form', '#first', 'first', '#tabset', 'tabset', 'form');

    /**
     * Frequency
     *
     * @var array
     */
    protected $aFrequency = array(
        array(
            'caption'  => 'frequency_0',
            'value'    => 0,
            'selected' => TRUE
        ),
        array(
            'caption'  => 'frequency_12h',
            'value'    => 720
        ),
        array(
            'caption'  => 'frequency_24h',
            'value'    => 1440
        ),
        array(
            'caption'  => 'frequency_1w',
            'value'    => 10080
        ),
        array(
            'caption'  => 'frequency_1m',
            'value'    => 43200
        ),
    );


    /**
     * Available drivers
     *
     * @var array
     */
    protected $aDrivers = array();

    /**
     * The constructor.
     */
    public function __construct(){
        parent::__construct();

        if(false == file_exists(AMI_Registry::get('path/root') . '_local/data_import_drivers.ini.php')){
            trigger_error('Configuration file for data import drivers is not found', E_USER_ERROR);
        }

        $this->aConfig = parse_ini_file(AMI_Registry::get('path/root') . '_local/data_import_drivers.ini.php', TRUE);

        if(is_array($this->aConfig) && !empty($this->aConfig)){
            // add enabled drivers to select list
            foreach($this->aConfig as $driverName => $aDriverConfig){
                if(isset($aDriverConfig['enabled']) && $aDriverConfig['enabled'] == 1){
                    $this->aDriversConfig[$driverName] = $aDriverConfig;
                    $this->aDrivers[] =
                        array(
                            'caption'   => $driverName . '_driver_name',
                            'value'     => $driverName,
                        );
                }
            }
        }
    }

    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        // add handlers
        AMI_Event::addHandler('on_before_save_model_item', array($this, 'handleBeforeSave'), $this->getModId());
        AMI_Event::addHandler('on_before_view_form', array($this, 'handleBeforeFormShow'), $this->getModId());

        // get id
        $oRequest = AMI::getSingleton('env/request');
        $itemId = $oRequest->get('id', null);

        $this
            ->addField(array('name' => 'id', 'type' => 'hidden'))
            ->addField(array('name' => 'public', 'type' => 'checkbox', 'default_checked' => true, 'position' => 'first.after'))
            ->addField(array('name' => 'header', 'validate' => array('required', 'filled', 'stop_on_error')))
            ->addField(array('name' => 'driver_data', 'type' => 'hidden'))
            ->addField(array('name' => 'driver_name', 'type' => 'select',  'data' => $this->aDrivers, 'validate' => array('custom')));


        // make tabs
        $this->addTabContainer('tabset', 'header.after');
        $this->addTab('data_task', 'tabset', self::TAB_STATE_ACTIVE, 'tabset.end');
        $this->addTab('data_driver', 'tabset', self::TAB_STATE_COMMON, 'tabset.end');

        // add fields on data tab
        if(empty($itemId)){
            $aUpdateStart = array('name' => 'update_start', 'type' => 'time_period', 'position' => 'data_task.end', 'validate' => array('custom'), 'value' => '23:00-07:00');
        }else{
            $aUpdateStart = array('name' => 'update_start', 'type' => 'time_period', 'position' => 'data_task.end', 'validate' => array('custom'));
        }

        $this
            ->addField(array('name' => 'id', 'type' => 'hidden'))
            ->addField(array('name' => 'driver_data', 'type' => 'hidden'))
            ->addField(array('name' => 'header', 'validate' => array('required', 'filled', 'stop_on_error'), 'position' => 'data_task.end'))
            ->addField(array('name' => 'public', 'type' => 'checkbox', 'position' => 'data_task.end', 'default_checked' => true))
            ->addField(array('name' => 'driver_name', 'type' => 'select', 'position' => 'data_task.end', 'data' => $this->aDrivers, 'validate' => array('custom')))
            ->addField(array('name' => 'frequency', 'type' => 'select', 'position' => 'data_task.end', 'data' => $this->aFrequency))
            ->addField(array('name' => 'allow_duplicate', 'type' => 'checkbox', 'position' => 'tabset.after'))
            ->addField($aUpdateStart);

        // add action input
        $this->addField(
            array(
            'name'  => 'mod_action',
            'value' => 'form_save',
            'type'  => 'hidden'
            )
        );


        // add all drivers settings to form pane
        foreach($this->aDrivers as $key => $aDriverData){
            $oDriver = AMI::getResource('import_driver/' . $aDriverData['value'])
                ->setModId($this->getModId())
                ->addToForm($this);
        }

        // add js validators
        // all validators and listeners has been moved into data_import.js
        // $this->addScriptFile('_local/_admin/_js/' . $this->getModId() .  '/form.adm.js');

        // add shared js code - all validators and listeners now placed here
        $this->addScriptFile('_admin/skins/vanilla/_js/data_import.js');

        return parent::init();
    }

    /**
     * Handle task data before save.
     *
     * @param string $name    Event name
     * @param array  $aEvent  Event data
     * @return array
     */
    public function handleBeforeSave($name, array $aEvent){
        // calculate date for next import - for new tasks
        if(isset($aEvent['aData']['action']) && ($aEvent['aData']['action'] == 'apply' || $aEvent['aData']['action'] == 'add')){
            $oService = AMI::getResource($this->getModId() . '/service');
            $tmpNextImport = $oService->makeNextImportDate(
                $aEvent['aData']['frequency'],
                $aEvent['aData']['update_start']
            );
            $aEvent['aData']['date_nextimport'] = date('Y-m-d H:i:s', $tmpNextImport);
        }

        // add default settings
        $aEvent['aData']['driver_data'] = array(
            'mod_id'        => $this->getModId(),
            'table_name'    => $aEvent['aData']['table_name'],
            'table_fields'  => $aEvent['aData']['table_fields'],
            'import_fields' => $aEvent['aData']['import_fields'],
            'id_cat'        => $aEvent['aData']['id_cat']
        );

        // add user-defined settings
        if(!empty($aEvent['aData']['driver_settings'])){
            foreach(explode(',', $aEvent['aData']['driver_settings']) as $fieldName){
                $aEvent['aData']['driver_data'][$fieldName] = $aEvent['aData'][$fieldName];
            }
            $aEvent['aData']['driver_data']['driver_settings'] = $aEvent['aData']['driver_settings'];
        }

        $aEvent['aData']['driver_data'] = serialize($aEvent['aData']['driver_data']);

        /*
        // merge import period time
        $aEvent['aData']['update_start'] = $aEvent['aData']['update_start'] . '-' . $aEvent['aData']['update_start2'];
        $aEvent['oItem']->update_start = $aEvent['aData']['update_start'];
        $aEvent['oItem']->update_start2 = $aEvent['aData']['update_start2'];
        */

        return $aEvent;
    }

    /**
     * Add values from driver_data to default driver fields.
     *
     * @param string $name    Event name
     * @param array  $aEvent  Event data
     * @return array
     */
    public function handleBeforeFormShow($name, array $aEvent){

        $aDriverData =
            !empty($aEvent['oView']->oItem->driver_data)
                ? unserialize($aEvent['oView']->oItem->driver_data)
                : array(
                    'table_name'    => NULL,
                    'table_fields'  => NULL,
                    'import_fields' => NULL,
                    'id_cat'        => NULL
                );

        // add default values
        $aEvent['oView']->oItem->table_name = $aDriverData['table_name'];
        $aEvent['oView']->oItem->table_fields = $aDriverData['table_fields'];
        $aEvent['oView']->oItem->import_fields = $aDriverData['import_fields'];
        $aEvent['oView']->oItem->id_cat = $aDriverData['id_cat'];

        // add values for user-defined fields
        if(isset($aDriverData['driver_settings']) && !empty($aDriverData['driver_settings'])){
            foreach(explode(',', $aDriverData['driver_settings']) as $fieldName){
                $aEvent['oView']->oItem->$fieldName = $aDriverData[$fieldName];
            }
        }

        // format import periods
        /*
        if(!empty($aEvent['oView']->oItem->update_start)){
            $aUpdateStart = explode('-', $aEvent['oView']->oItem->update_start);
            $aEvent['oView']->oItem->update_start = $aUpdateStart[0];
            $aEvent['oView']->oItem->update_start2 = isset($aUpdateStart[1]) ? $aUpdateStart[1] : '';
        }
        */

        return $aEvent;
    }
}

/**
 * AmiClean/DataImport module admin list component action controller.
 *
 * @package    Config_AmiClean_DataImport
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_DataImport_ListAdm extends Hyper_AmiClean_ListAdm{
    /**
     * Initialization.
     *
     * @return $this
     */
    public function init(){

        $this
            ->addActions(
                array(
                    self::REQUIRE_FULL_ENV . 'edit',
                    self::REQUIRE_FULL_ENV . 'delete',
                    self::REQUIRE_FULL_ENV . 'execute'
                )
            )
            ->addColActions(array(self::REQUIRE_FULL_ENV . 'public'), true)
            ->dropActions(self::ACTION_GROUP, array('seo_section', 'common_section', 'meta_section'));


        $this->listActionsResId = $this->getModId() . '/list_actions/controller/adm';

        parent::init();
        return $this;
    }
}

/**
 * AmiClean/DataImport module admin list component view.
 *
 * @package    Config_AmiClean_DataImport
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_DataImport_ListViewAdm extends Hyper_AmiClean_ListViewAdm{
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
        '#columns', 'id', 'public', 'driver_name', 'header', 'frequency', 'date_lastimport', 'date_nextimport', 'errors_num',
        '#actions', 'actions',
        'list_header'
    );

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return DataImport_ListViewAdm
     */
    public function init(){
        parent::init();

        $this
            ->addColumn('id')
            ->addColumnType('id', 'hidden')
            ->addColumn('public')
            ->addColumn('driver_name')
            ->formatColumn('driver_name', array($this, 'fmtDriverName'))
            ->addColumn('header')
            ->addColumn('frequency')
            ->formatColumn('frequency', array($this, 'fmtFrequency'))
            ->addColumnType('date_lastimport', 'datetime')
            ->formatColumn('date_lastimport', array($this, 'fmtHumanDateTime'))
            ->addColumnType('date_nextimport', 'datetime')
            ->formatColumn('date_nextimport', array($this, 'fmtDateNextImport'))
            ->addColumnType('errors_num', 'int')
            ->addSortColumns(array('id', 'public', 'driver_name', 'header', 'active', 'errors_num', 'date_nextimport', 'date_lastimport'));

        return $this;
    }

    /**
     * Format frequency column.
     *
     * @param mixed $value  Column value
     * @param array $aArgs  Arguments
     * @return mixed
     */
    public function fmtFrequency($value, array $aArgs){
        switch($value){
            case 0:
            case 10:
            case 30:
            case 60:
            case 120:
                $value = $this->aLocale['frequency_' . $value];
                break;

            case 720:
                $value = $this->aLocale['frequency_12h'];
                break;

            case 1440:
                $value = $this->aLocale['frequency_24h'];
                break;
        }

        return $value;
    }

    /**
     * Format driver name.
     *
     * @param mixed $value  Column value
     * @param array $aArgs  Arguments
     * @return mixed
     */
    public function fmtDriverName($value, array $aArgs){
        if(isset($this->aLocale[$value . '_driver_name'])){
            $value = $this->aLocale[$value . '_driver_name'];
        }

        return $value;
    }

    /**
     * Format next import date column.
     *
     * @param mixed $value  Column value
     * @param array $aArgs  Arguments
     * @return mixed
     */
    public function fmtDateNextImport($value, array $aArgs){
        $value =
            $aArgs['aScope']['frequency'] > 0
                ? $this->fmtHumanDateTime($value, $aArgs)
                : '&#8212;';

        return $value;
    }
}

/**
 * AmiClean/DataImport module admin list actions executer.
 *
 * @package    Config_AmiClean_DataImport
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_DataImport_ListActionsAdm extends Hyper_AmiClean_ListActionsAdm{
    /**
     * Dispatches 'execute' action.
     *
     * Execute the task (load driver, read data, save data).
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @todo move all logic about call to the driver to _service_ class (is this necessary?)
     */
    public function dispatchExecute($name, array $aEvent, $handlerModId, $srcModId){
        $this->refreshView();

        $oRequest = AMI::getSingleton('env/request');
        $taskId = (int)$oRequest->get('ami_applied_id');

        // get the data of the task - with driver settings
        $oModel = AMI::getResourceModel($aEvent['modId'] . '/table');
        $oTask = $oModel->find($taskId);

        if(empty($oTask)){
            trigger_error('Unable to execute task that not exists', E_USER_ERROR);
        }

        // unable to get driver data ?
        if(empty($oTask->driver_data)){
            AMI::getSingleton('response')->addStatusMessage(
                'status_driver_data_empty',
                array(),
                AMI_Response::STATUS_MESSAGE_ERROR
            );

            $aEvent['message'] = 'Configuration array is empty. Unable to execute operation';
            return $aEvent;
        }

        $aDriverData = unserialize($oTask->driver_data);
        $aDriverData['table_fields'] = explode(',', $aDriverData['table_fields']);
        $aDriverData['import_fields'] = explode(',', $aDriverData['import_fields']);

        // get the driver
        $oDriver = AMI::getResource('import_driver/' . $oTask->driver_name);

        // set up base data
        $resourceName = $oTask->driver_name == 'ami_csv' ? $aDriverData['file_name'] : $aDriverData['source_url'];
        $oDriver
            ->setModId($aDriverData['mod_id'])
            ->setContentEncoding('utf-8');

        // add import fields and mapping to the destination table
        for($i = 0; $i < count($aDriverData['table_fields']); $i++){
            $aField = array(
                'name'  => $aDriverData['import_fields'][$i],
                'mapTo' => $aDriverData['table_fields'][$i],
            );

            $oDriver->addImportField($aField);
        }

        // add settings
        $aSettings = array();
        if(!empty($aDriverData['driver_settings'])){
            foreach(explode(',', $aDriverData['driver_settings']) as $fieldName){
                if(isset($aDriverData[$fieldName])){
                    $aSettings[$fieldName] = $aDriverData[$fieldName];
                }
            }
        }

        if(isset($aDriverData['id_cat'])){
            $aSettings['id_cat'] = $aDriverData['id_cat'];
            AMI::cleanupModExtensions($aDriverData['table_name']);
            AMI::initModExtensions($aDriverData['table_name']);
            AMI_Registry::set(
                'AMI/Environment/Model/DefaultAttributes',
                array(
                    'extModeOnConstruct' => 'none',
                    'extModeOnDestruct'  => 'none'
                )
            );
        }

        // try to establish the connection and import the data
        try{
            $oDriver
                ->setResourceId($aDriverData['table_name'])
                ->setResourceName($resourceName)
                ->setRequestSettings($aSettings)
                ->initConnection();

            // read the data from the source
            if($oDriver->isImportResourceAvailable()){
                $oDriver
                    ->readData()
                    ->closeConnection();

                // if data succesfully imported save it into db
                if($oDriver->doImport() && $oDriver->isImported()){
                    $oDriver->save($oTask->id, $oTask->allow_duplicate);
                    $errorsCount = $oDriver->getErrorsCount();
                    $savedCount = $oDriver->getSavedRowsCount();
                    $importedCount = $oDriver->getImportedRowsCount();
                    $msgStatus = AMI_Response::STATUS_MESSAGE;
                    $aExceptionData = $oDriver->getExceptionData();

                    // something was added with errors
                    if($errorsCount > 0){
                        $msgCode = 'status_driver_data_saved_with_errors';
                        $aPlaceholders = array(
                            'taskName'      => $oTask->header,
                            'taskType'      => $oTask->driver_name,
                            'savedCount'    => $savedCount,
                            'allCount'      => ($errorsCount + $savedCount),
                        );
                        if($aExceptionData){
                            $aPlaceholders += $aExceptionData;
                            $msgCode .= '_details';
                        }
                    }elseif($importedCount == 0){
                        // nothing was added but import was completed succesfully
                        $msgCode = 'status_driver_data_saved_nothing_added';
                        $aPlaceholders = array(
                            'taskName'      => $oTask->header,
                            'taskType'      => $oTask->driver_name,
                        );
                    }else{
                        // a lot of new rows have been added successfully
                        $msgCode = 'status_driver_data_saved';
                        $aPlaceholders = array(
                            'taskName'      => $oTask->header,
                            'taskType'      => $oTask->driver_name,
                            'savedCount'    => $savedCount,
                        );
                    }

                }else{
                    $msgStatus= AMI_Response::STATUS_MESSAGE_ERROR;
                    $msgCode = 'status_driver_data_not_imported';
                }
            }else{
                $msgStatus = AMI_Response::STATUS_MESSAGE_ERROR;
                $msgCode = 'status_driver_not_connected';
            }

        }catch(AMI_DataImportException $oImportExeption){
            // get locale code and params that will be changed in the localized string
            $msgCode = $oImportExeption->getLocaleCode();
            $aPlaceholders = $oImportExeption->getData();

            $msgStatus = AMI_Response::STATUS_MESSAGE_ERROR;
        }

        // update task data
        if($msgStatus == AMI_Response::STATUS_MESSAGE_ERROR){
            $oTask->last_success = 0;
            $oTask->errors_num++;
        }else{
            $oTask->last_success = 1;
        }
        $oTask->executed++;
        $oTask->date_lastimport = date('Y-m-d H:i:s', time());
        $oTask->save();

        // add message
        AMI::getSingleton('response')->addStatusMessage(
            $msgCode,
            isset($aPlaceholders) ? $aPlaceholders : array(),
            $msgStatus
        );

        return $aEvent;
    }
}
