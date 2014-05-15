<?php
/**
 * AmiUsers/AccessRights configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiUsers_AccessRights
 * @version   $Id: AmiUsers_AccessRights_Table.php 42102 2013-10-07 11:53:16Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiUsers/AccessRights configuration table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Config_AmiUsers_AccessRights
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_AccessRights_Table extends AmiUsers_Users_Table{
    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     */
    public function __construct(array $aAttributes = array()){
        $this->setDependence('ami_sys_users', 'g', "i.id = g.id_member", 'LEFT JOIN');

        parent::__construct($aAttributes);
    }
}

/**
 * AmiUsers/AccessRights configuration table item model.
 *
 * @package    Config_AmiUsers_AccessRights
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_AccessRights_TableItem extends AmiUsers_Users_TableItem{
}

/**
 * AmiUsers/AccessRights configuration table list model.
 *
 * @package    Config_AmiUsers_AccessRights
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_AccessRights_TableList extends AmiUsers_Users_TableList{
    /**
     * Initializing table list data.
     *
     * @param AMI_ModTable $oTable  Table model
     * @param DB_Query     $oQuery  DB_Query object, required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery){
        parent::__construct($oTable, $oQuery);

        $this->addExpressionColumn(
            'full_name',
            DB_Query::getSnippet("CONCAT(`i`.`lastname`, %s, `i`.`firstname`)")->q(' ')
        );
        if(in_array('g', $this->oTable->getActiveDependenceAliases())){
            $this->addExpressionColumn('groups', "COUNT(`g`.`id`)", 'g');
        }
    }
}
