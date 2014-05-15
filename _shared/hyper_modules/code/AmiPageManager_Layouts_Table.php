<?php
/**
 * AmiPageManager/Layouts configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiPageManager_Layouts
 * @version   $Id: AmiPageManager_Layouts_Table.php 42102 2013-10-07 11:53:16Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiPageManager/Layouts configuration table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Config_AmiPageManager_Layouts
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiPageManager_Layouts_Table extends Hyper_AmiPageManager_Table{
    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     */
    public function __construct(array $aAttributes = array()){
        $this->setDependence('pages', 'p', 'i.id=p.lay_id', 'LEFT JOIN');
        $this->setActiveDependence('p');
        $this->tableName = 'cms_' . $this->getModId();
        // $this->setDependence($this->getModId() . '_cat', 'cat', 'cat.id=i.id_cat');

        parent::__construct($aAttributes);

        $aRemap = array(
            'id' => 'id',
            'id_layout' => 'id',
            'header' => 'name'
        );
        $this->addFieldsRemap($aRemap);
    }
}

/**
 * AmiPageManager/Layouts configuration table item model.
 *
 * @package    Config_AmiPageManager_Layouts
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiPageManager_Layouts_TableItem extends Hyper_AmiPageManager_TableItem{
}

/**
 * AmiPageManager/Layouts configuration table list model.
 *
 * @package    Config_AmiPageManager_Layouts
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiPageManager_Layouts_TableList extends Hyper_AmiPageManager_TableList{
    /**
     * Initializing table list data.
     *
     * @param AMI_ModTable $oTable  Table model
     * @param DB_Query     $oQuery  DB_Query object, required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery){
        parent::__construct($oTable, $oQuery);
		$this->addExpressionColumn('script_link', 'p.script_link', 'p');
    }
}
