<?php
/**
 * AmiEshopShipping/Methods configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiEshopShipping_Methods
 * @version   $Id: AmiEshopShipping_Methods_Table.php 50198 2014-04-23 08:05:51Z Kolesnikov Artem $
 * @since     6.0.6
 */

/**
 * AmiEshopShipping/Methods configuration table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.<br /><br />
 * Fields description:
 * - <b>id_parent</b> - parent id for complex methods (int);
 * - <b>hidden</b> - 1 for system methods, always use 0 (int);
 * - <b>amount</b> - shipping value for simple method / children of complex method, -1 complex method (double);
 * - <b>type</b> type of amount: 'abs', 'percent', '' (enum);
 * - <b>delivery_time</b> - delivery time (string);
 * - <b>custom_conditions</b> - children of complex methods custom conditions: 'none', 'total', 'weight', 'value' (enum);
 * - <b>max_total</b> - maximum products quantity (double);
 * - <b>max_weight</b> - maximum products weight(double);
 * - <b>max_value</b> - maximum products price (double);
 * - <b>fields</b> - array of fields postfixes (array);
 * - <b>groups</b> - array of field groups ids (array);
 * - <b>position</b> - position for methods during creating order (int);
 * - <b>comments</b> - comments (string);
 * - <b>tax_class_type</b> - tax class type: ('taxable', 'taxable_excl', 'taxable_class', 'non_taxable' (enum);
 * - <b>id_tax_class</b> - tax class id (int);
 * - <b>types</b> - array of associated types ids (array).
 *
 * @package    Config_AmiEshopShipping_Methods
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      6.0.6
 */
class AmiEshopShipping_Methods_Table extends Hyper_AmiEshopShipping_Table{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_es_shipping_methods';

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model
     */
    public function __construct(array $aAttributes = array()){
        parent::__construct($aAttributes);

        $aRemap = array(
            'header'        => 'name',
            'date_modified' => 'modified_date',
            'fields'        => 'custom_fields',
            'groups'        => 'custom_field_groups'
        );
        $this->addFieldsRemap($aRemap);
    }

    /**
     * Sets new table name for a model.
     *
     * @param string $tableName  New table name
     * @return void
     * @amidev Temporary
     */
    public function setTableName($tableName){
    }
}

/**
 * AmiEshopShipping/Methods configuration table item model.
 *
 * @package    Config_AmiEshopShipping_Methods
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      6.0.6
 */
class AmiEshopShipping_Methods_TableItem extends Hyper_AmiEshopShipping_TableItem{
    /**
     * Types to methods model field name
     *
     * @var string
     */
    protected $typesToMethodsModelField = 'types';

    /**
     * Types to methods column field name
     *
     * @var string
     */
    protected $typesToMethodsColumnField = 'id_type';

    /**
     * Types to methods column field name
     *
     * @var string
     */
    protected $typesToMethodsConditionField = 'id_method';

    /**
     * Constructor.
     *
     * Initializes table item data.
     *
     * @param AMI_ModTable $oTable  Module table model
     * @param DB_Query     $oQuery  Required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery = null){
        parent::__construct($oTable, $oQuery);

        $this->setFieldCallback('fields', array($this, 'fcbCustomFields'));
        $this->setFieldCallback('groups', array($this, 'fcbCustomFieldGroups'));
        $this->setFieldCallback(
            $this->typesToMethodsModelField,
            array($this, 'fcbTypesToMethodsField')
        );
    }

    /**
     * Custom fields list field callback.
     *
     * @param  array $aData  Formatter data
     * @return array
     */
    protected function fcbCustomFields(array $aData){
        $action = $aData['action'];
        $name = $aData['name'];

        switch($action){
            case 'get':
                $aData['value'] = NULL;
                if(isset($this->aData[$name])){
                    if(is_string($this->aData[$name])){
                        $fields = trim($this->aData[$name], '|');
                        $this->aData[$name] = $fields ? explode('|', $fields) : array();
                    }
                    $aData['value'] = $this->aData[$name];
                }                break; // case 'get'
            case 'save':
                $aData['value'] = NULL;
                if(isset($this->aData[$name])){
                    $this->aData[$name] = '|' . implode('|', $this->aData[$name]) . '|';
                    $aData['value'] = $this->aData[$name];
                }
                break; // case 'save'
        }
    }

    /**
     * Custom field groups field callback.
     *
     * @param  array $aData  Formatter data
     * @return array
     */
    protected function fcbCustomFieldGroups(array $aData){
        $action = $aData['action'];
        $name = $aData['name'];

        switch($action){
            case 'get':
                $aData = $this->fcbSerialized($aData);
                $this->aData[$name] = is_array($this->aData[$name]) ? array_keys($this->aData[$name]) : array();
                $aData['value'] = $this->aData[$name];
                break; // case 'get'
            case 'save':
                if(isset($this->aData[$name]) && is_array($this->aData[$name])){
                    $aGroups = array_unique($this->aData[$name]);
                    $this->aData[$name] = array_combine($aGroups, $aGroups);
                }else{
                    $this->aData[$name] = array();
                }
                $aData = $this->fcbSerialized($aData);

                break; // case 'save'
        }
    }
}

/**
 * AmiEshopShipping/Methods configuration table list model.
 *
 * @package    Config_AmiEshopShipping_Methods
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      6.0.6
 */
class AmiEshopShipping_Methods_TableList extends Hyper_AmiEshopShipping_TableList{
}
