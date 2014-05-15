<?php
/**
 * AmiPageManager/Templates configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiClean_Templates
 * @version   $Id: AmiClean_Templates_Table.php 42102 2013-10-07 11:53:16Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiPageManager/Templates configuration table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Config_AmiClean_Templates
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_Templates_Table extends Hyper_AmiClean_Table{
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
            'date_created' => 'date',
            'header' => 'name',
        );
        $this->addFieldsRemap($aRemap);
    }
}

/**
 * AmiPageManager/Templates configuration table item model.
 *
 * @package    Config_AmiClean_Templates
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_Templates_TableItem extends Hyper_AmiClean_TableItem{
}

/**
 * AmiPageManager/Templates configuration table list model.
 *
 * @package    Config_AmiClean_Templates
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_Templates_TableList extends Hyper_AmiClean_TableList{
}
