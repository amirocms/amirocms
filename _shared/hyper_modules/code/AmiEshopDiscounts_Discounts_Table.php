<?php
/**
 * AmiEshopDiscounts/Discounts configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiEshopDiscounts_Discounts
 * @version   $Id: AmiEshopDiscounts_Discounts_Table.php 42102 2013-10-07 11:53:16Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiEshopDiscounts/Discounts configuration table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Config_AmiEshopDiscounts_Discounts
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopDiscounts_Discounts_Table extends Hyper_AmiEshopDiscounts_Table{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_es_discounts';

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     */
    public function __construct(array $aAttributes = array()){
        if(!AMI_Registry::get('AMI/models/eshop_discounts/nojoin/eshop_cat', FALSE)){
            $this->setDependence('eshop_cat', 'c', 'c.id_discount = i.id', 'LEFT OUTER JOIN');
        }

        parent::__construct($aAttributes);

        $aRemap = array(
            'header' => 'name',
            'date_created' => 'date_start',
            'date_modified' => 'modified_date',
            'datefrom' => 'date_start',
            'dateto' => 'date_end',
            'cond' => 'condition',
            'cond_orig' => 'condition'
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
        $this->tableName = $this->tableName;
    }
}

/**
 * AmiEshopDiscounts/Discounts configuration table item model.
 *
 * @package    Config_AmiEshopDiscounts_Discounts
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopDiscounts_Discounts_TableItem extends Hyper_AmiEshopDiscounts_TableItem{
    /**
     * Initializing table item data.
     *
     * @param AMI_ModTable $oTable  Module table model
     * @param DB_Query     $oQuery  Required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery = null){
        parent::__construct($oTable, $oQuery);
    }
}

/**
 * AmiEshopDiscounts/Discounts configuration table list model.
 *
 * @package    Config_AmiEshopDiscounts_Discounts
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopDiscounts_Discounts_TableList extends Hyper_AmiEshopDiscounts_TableList{
    /**
     * Initializing table list data.
     *
     * @param AMI_ModTable $oTable  Table model
     * @param DB_Query     $oQuery  DB_Query object, required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery){
        parent::__construct($oTable, $oQuery);
        if(!AMI_Registry::get('AMI/models/eshop_discounts/nojoin/eshop_cat', FALSE)){
            $this->addExpressionColumn('categories_count', 'COUNT(`c`.`id`)', 'c');
        }
    }
}
