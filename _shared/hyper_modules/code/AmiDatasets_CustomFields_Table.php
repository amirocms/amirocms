<?php
/**
 * AmiDatasets/CustomFields configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiDatasets_CustomFields
 * @version   $Id: AmiDatasets_CustomFields_Table.php 42102 2013-10-07 11:53:16Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiDatasets/CustomFields configuration table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Config_AmiDatasets_CustomFields
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDatasets_CustomFields_Table extends Hyper_AmiDatasets_Table{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_modules_custom_fields';

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     */
    public function __construct(array $aAttributes = array()){
        $this->setDependence('modules_datasets', 'd', "POSITION(CONCAT(';', i.id, ';') IN d.`fields_map`) > 0", 'LEFT JOIN');
        $aRemap = array(
            'id'          => 'id',
            'system_name' => 'system_name',
            'module'      => 'module_name',
            'name'        => 'name',
            'public'      => 'public',
            'admin_form'  => 'admin_form'
        );
        $this->addFieldsRemap($aRemap);

        parent::__construct($aAttributes);
    }

    /**
     * Sets new table name for a model.
     *
     * @param string $tableName  New table name
     * @return void
     */
    public function setTableName($tableName){
    }
}

/**
 * AmiDatasets/CustomFields configuration table item model.
 *
 * @package    Config_AmiDatasets_CustomFields
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDatasets_CustomFields_TableItem extends Hyper_AmiDatasets_TableItem{
}

/**
 * AmiDatasets/CustomFields configuration table list model.
 *
 * @package    Config_AmiDatasets_CustomFields
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDatasets_CustomFields_TableList extends Hyper_AmiDatasets_TableList{
    /**
     * Initializing table list data.
     *
     * @param AMI_ModTable $oTable  Table model
     * @param DB_Query     $oQuery  DB_Query object, required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery){
        parent::__construct($oTable, $oQuery);
        $this->addExpressionColumn('datasets', 'COUNT(d.`id`)', 'd');
    }
}
