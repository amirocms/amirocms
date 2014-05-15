<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Module_AdvGroups
 * @version   $Id: AdvGroups_Table.php 42102 2013-10-07 11:53:16Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Advertising groups module table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Module_AdvGroups
 * @subpackage Model
 * @resource   adv_groups/table/model <code>AMI::getResourceModel('adv_groups/table')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AdvGroups_Table extends AMI_ModTable{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_adv_groups';

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     */
    public function __construct(array $aAttributes = array()){
        parent::__construct($aAttributes);

        $aRemap = array(
            'id'               => 'id',
            'lang'             => 'lang',
            'header'           => 'name',
            'adv_places'       => 'adv_places',
            'date_modified'    => 'modified_date',
        );
        $this->addFieldsRemap($aRemap);
    }
}

/**
 * Advertising groups module table item model.
 *
 * @package    Module_AdvGroups
 * @subpackage Model
 * @resource   adv_groups/table/model/item <code>AMI::getResourceModel('adv_groups/table')->getItem()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AdvGroups_TableItem extends AMI_ModTableItem{
}

/**
 * Advertising groups module table list model.
 *
 * @package    Module_AdvGroups
 * @subpackage Model
 * @resource   adv_groups/table/model/list <code>AMI::getResourceModel('adv_groups/table')->getList()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AdvGroups_TableList extends AMI_ModTableList{
}
