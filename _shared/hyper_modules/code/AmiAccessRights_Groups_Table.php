<?php
/**
 * AmiAccessRights configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiAccessRights_Groups
 * @version   $Id: AmiAccessRights_Groups_Table.php 42102 2013-10-07 11:53:16Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiAccessRights configuration table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Config_AmiAccessRights_Groups
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAccessRights_Groups_Table extends Hyper_AmiAccessRights_Table{
    /**
     * Database table name, must be declared in child classes
     *
     * @var string
     */
    protected $tableName = 'cms_sys_groups';

    /**
     * Flag specifying to join users model
     *
     * @var bool
     * @amidev
     */
    protected $doJoin;

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     */
    public function __construct(array $aAttributes = array()){
        $this->doJoin = AMI_Registry::get('AMI/models/ami_sys_groups/join/ami_sys_users', FALSE);
        if($this->doJoin){
            $this->setDependence('ami_sys_users', 'u', "i.id = u.id_group", 'LEFT JOIN');
        }
        $aRemap = array(
            'id'           => 'id',
            'name'         => 'name',
            'is_default'   => 'is_default',
            'is_guest'     => 'guest',
            'is_admin'     => 'login',
            'is_moderator' => 'moderator',
            'mask'         => 'group_mask'
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

    /**
     * Returns flag.
     *
     * @return bool
     * @amidev
     */
    public function doJoin(){
        return $this->doJoin;
    }
}

/**
 * AmiAccessRights configuration table item model.
 *
 * @package    Config_AmiAccessRights_Groups
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAccessRights_Groups_TableItem extends Hyper_AmiAccessRights_TableItem{
}

/**
 * AmiAccessRights configuration table list model.
 *
 * @package    Config_AmiAccessRights_Groups
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAccessRights_Groups_TableList extends Hyper_AmiAccessRights_TableList{
    /**
     * Initializing table list data.
     *
     * @param AMI_ModTable $oTable  Table model
     * @param DB_Query     $oQuery  DB_Query object, required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery){
        parent::__construct($oTable, $oQuery);
        if($oTable->doJoin()){
            $this->addExpressionColumn('users', 'COUNT(`u`.`id`)', 'u');
        }
    }
}
