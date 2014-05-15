<?php
/**
 * AmiClean/DataImport configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiClean_DataImport
 * @version   $Id: AmiClean_DataImport_Table.php 48793 2014-03-18 10:56:40Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiClean/DataImport configuration table model.
 *
 * @package    Config_AmiClean_DataImport
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_DataImport_Table extends Hyper_AmiClean_Table{
    /**
     * Set up table to cms_es_custom_types
     *
     * @var string
     */
    protected $tableName = 'cms_data_import';

    /**
     * Overload table name setter.
     *
     * @param string $tableName  Table name
     * @return void
     */
    public function setTableName($tableName){
    }
}

/**
 * AmiExt/EshopCustomFields configuration table list model.
 *
 * @package    Config_AmiExt_EshopCustomFields
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_DataImport_TableList extends Hyper_AmiClean_TableList{
}

/**
 * AmiExt/EshopCustomFields configuration table item model.
 *
 * @package    Config_AmiExt_EshopCustomFields
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_DataImport_TableItem extends Hyper_AmiClean_TableItem{
    /**
     * The constructor.
     *
     * @param AMI_ModTable $oTable  Table object
     * @param DB_Query $oQuery      Query object
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery = null){
        parent::__construct($oTable, $oQuery);

        $this->oTable->addValidators(
            array(
                'header'            => array('filled', 'stop_on_error'),
                'driver_name'       => array('filled', 'stop_on_error'),
                'file_name'         => array('file_existed'),
                'table_name'        => array('table_name'),
                'table_fields'      => array('table_fields'),
                'import_fields'     => array('import_fields'),
            )
        );
        $this->setFieldCallback('header', array($this, 'fcbHTMLEntities'));

        // Virtual fields
        $this->setFieldCallback('module', array($this, 'fcbModuleField'));
        $this->setFieldCallback('import_task_id', array($this, 'fcbTaskIdField'));

        AMI_Event::addHandler('on_save_validate_{file_existed}', array($this, 'validateFileExisted'), $this->getModId());
        AMI_Event::addHandler('on_save_validate_{table_name}', array($this, 'validateTableName'), $this->getModId());
        AMI_Event::addHandler('on_save_validate_{import_fields}', array($this, 'validateImportFields'), $this->getModId());
        AMI_Event::addHandler('on_save_validate_{table_fields}', array($this, 'validateTableFields'), $this->getModId());
    }

    /**
     * Callback for virtuial field 'import_task_id'.
     *
     * @param  array $aData  Callback data
     * @return array
     */
    public function fcbTaskIdField(array $aData){
        $action = $aData['action'];

        switch($action){
            case 'get':
                $aData['value'] = $this->id;
                break;
            case 'set':
                $aData['_skip'] = TRUE;
                break;
         }

         return $aData;
     }

    /**
     * Callback for virtuial field 'module'.
     *
     * @param array $aData  Callback data
     * @return array
     */
    public function fcbModuleField(array $aData){
        $action = $aData['action'];

        switch($action){
            case 'get':
                $aTaskData = isset($this->aData['driver_data']) && !empty($this->aData['driver_data']) ? unserialize($this->aData['driver_data']) : array();
                $aData['value'] = isset($aTaskData['mod_id']) ? $aTaskData['mod_id'] : null;
                break;
            case 'set':
                $aData['_skip'] = TRUE;
        }

        return $aData;
    }

    /**
     * Validate for existed csv file.
     *
     * @param string $name         Event name
     * @param array $aEvent        Event array
     * @param mixed $handlerModId  Handler mod id
     * @param mixed $srcModId      Source mod id
     * @return array
     */
    public function validateFileExisted($name, array $aEvent, $handlerModId, $srcModId){
        $fileName = AMI_Registry::get('path/root') . '/_mod_files/_upload/' . $aEvent['oItem']->file_name;

        // csv file is not exist?
        if($aEvent['oItem']->driver_name == 'ami_csv' && !file_exists($fileName)){
            $aEvent['message'] = 'data_import_error_csv_not_exists';
        }

        return $aEvent;
    }

    /**
     * Validate table name.
     *
     * @param string $name         Event name
     * @param array $aEvent        Event array
     * @param mixed $handlerModId  Handler mod id
     * @param mixed $srcModId      Source mod id
     * @return array
     */
    public function validateTableName($name, array $aEvent, $handlerModId, $srcModId){
        $tableName = $aEvent['oItem']->table_name;

        if(empty($tableName)){
            $aEvent['message'] = 'data_import_error_table_not_specified.';
            return $aEvent;
        }

        $oSnippet =
            DB_Query::getSnippet("SHOW TABLES LIKE %s")
                ->q('cms_' . $tableName);
        $oResult = AMI::getSingleton('db')->select($oSnippet);

        if($oResult === false || $oResult->count() == 0){
            $aEvent['message'] = 'data_import_error_table_not_exist';
        }

        return $aEvent;
    }

    /**
     * Validate import fields count.
     *
     * @param string $name         Event name
     * @param array $aEvent        Event array
     * @param mixed $handlerModId  Handler mod id
     * @param mixed $srcModId      Source mod id
     * @return array
     */
    public function validateTableFields($name, array $aEvent, $handlerModId, $srcModId){
        if(empty($aEvent['oItem']->table_fields)
            || count(explode(',', $aEvent['oItem']->table_fields)) != count(explode(',', $aEvent['oItem']->import_fields))
        ){
            $aEvent['message'] = 'data_import_error_import_fields_mismatch';
            return $aEvent;
        }

        // check for existed fields
        $oModel = AMI::getResourceModel($aEvent['oItem']->table_name . '/table/model');
        foreach(explode(',', $aEvent['oItem']->table_fields) as $fieldName){
            // if field is not a function field()
            if(false === strpos($fieldName, '()') && false == $oModel->hasField(trim($fieldName))){
                $aEvent['message'] = 'status_table_field_not_exists';
                break;
            }
        }

        return $aEvent;
    }

    /**
     * Validate import fields count.
     *
     * @param string $name         Event name
     * @param array $aEvent        Event array
     * @param mixed $handlerModId  Handler mod id
     * @param mixed $srcModId      Source mod id
     * @return array
     */
    public function validateImportFields($name, array $aEvent, $handlerModId, $srcModId){
        // check for empty fields list
        if(empty($aEvent['oItem']->import_fields)
            || count(explode(',', $aEvent['oItem']->import_fields)) != count(explode(',', $aEvent['oItem']->table_fields))
        ){
            $aEvent['message'] = 'data_import_error_import_fields_mismatch';
            return $aEvent;
        }

        return $aEvent;
    }
}
