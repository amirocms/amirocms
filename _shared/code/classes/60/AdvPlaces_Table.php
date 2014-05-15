<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Module_AdvPlaces
 * @version   $Id: AdvPlaces_Table.php 42102 2013-10-07 11:53:16Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Advertising places module table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Module_AdvPlaces
 * @subpackage Model
 * @resource   adv_places/table/model <code>AMI::getResourceModel('adv_places/table')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AdvPlaces_Table extends AMI_ModTable{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_adv_places';

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     */
    public function __construct(array $aAttributes = array()){
        parent::__construct($aAttributes);

        $aRemap = array(
            'id'                => 'id',
            'public'            => 'public',
            'id_page'           => 'id_page',
            'lang'              => 'lang',
            'header'           => 'name',
            'date_modified'    => 'modified_date',
        );
        $this->addFieldsRemap($aRemap);
    }
}

/**
 * Advertising places module table item model.
 *
 * @package    Module_AdvPlaces
 * @subpackage Model
 * @resource   adv_places/table/model/item <code>AMI::getResourceModel('adv_places/table')->getItem()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AdvPlaces_TableItem extends AMI_ModTableItem{
}

/**
 * Advertising places module table list model.
 *
 * @package    Module_AdvPlaces
 * @subpackage Model
 * @resource   adv_places/table/model/list <code>AMI::getResourceModel('adv_places/table')->getList()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AdvPlaces_TableList extends AMI_ModTableList{
}
