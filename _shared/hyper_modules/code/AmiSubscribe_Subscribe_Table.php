<?php
/**
 * AmiSubscribe/Subscribe configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiSubscribe_Subscribe
 * @version   $Id: AmiSubscribe_Subscribe_Table.php 42102 2013-10-07 11:53:16Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiSubscribe/Subscribe configuration table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Config_AmiSubscribe_Subscribe
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Subscribe_Table extends Hyper_AmiSubscribe_Table{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_subs_members';

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     */
    public function __construct(array $aAttributes = array()){
        $this->setDependence('users', 'u', 'u.id=i.id_member', 'INNER JOIN');
        $this->setActiveDependence('u');

        parent::__construct($aAttributes);
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
 * AmiSubscribe/Subscribe configuration table item model.
 *
 * @package    Config_AmiSubscribe_Subscribe
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Subscribe_TableItem extends Hyper_AmiSubscribe_TableItem{
}

/**
 * AmiSubscribe/Subscribe configuration table list model.
 *
 * @package    Config_AmiSubscribe_Subscribe
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Subscribe_TableList extends Hyper_AmiSubscribe_TableList{
    /**
     * Initializing table list data.
     *
     * @param AMI_ModTable $oTable  Table model
     * @param DB_Query     $oQuery  DB_Query object, required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery){
        parent::__construct($oTable, $oQuery);

        $this->addExpressionColumn('date', 'u.date', 'u');
		$this->addExpressionColumn('email', 'u.email', 'u');
    }
}
