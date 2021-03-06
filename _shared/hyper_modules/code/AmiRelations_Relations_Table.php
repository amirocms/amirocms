<?php
/**
 * AmiRelations/Relations configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiRelations_Relations
 * @version   $Id: AmiRelations_Relations_Table.php 42102 2013-10-07 11:53:16Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiRelations/Relations configuration table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Config_AmiRelations_Relations
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiRelations_Relations_Table extends Hyper_AmiRelations_Table{
    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     */
    public function __construct(array $aAttributes = array()){
        $this->tableName = 'cms_' . $this->getModId();
        // $this->setDependence($this->getModId() . '_cat', 'cat', 'cat.id=i.id_cat');

        parent::__construct($aAttributes);

        $aRemap = array(
            'id' => 'id'
        );
        $this->addFieldsRemap($aRemap);
    }
}

/**
 * AmiRelations/Relations configuration table item model.
 *
 * @package    Config_AmiRelations_Relations
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiRelations_Relations_TableItem extends Hyper_AmiRelations_TableItem{
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
 * AmiRelations/Relations configuration table list model.
 *
 * @package    Config_AmiRelations_Relations
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiRelations_Relations_TableList extends Hyper_AmiRelations_TableList{
}
