<?php
/**
 * AmiSearch/Search configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiSearch_Search
 * @version   $Id: AmiSearch_Search_Table.php 42102 2013-10-07 11:53:16Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiSearch/Search configuration table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Config_AmiSearch_Search
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSearch_Search_Table extends Hyper_AmiSearch_Table{
    /**
     * Database table name, must be declared in child classes
     *
     * @var string
     */
    protected $tableName = 'cms_search_history';

    /**
     * Associated table names
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aAssociatedTables = array('cms_site_search_index');

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     * @todo Avoid hack!
     */
    public function __construct(array $aAttributes = array()){
        // Damn hack, see AmiSearch_Search_Adm::__construct()
        if(AMI_Registry::get('AMI/HyperConfig/Model/ami_search/search/switched', FALSE)){
            // admin interface model

            parent::__construct($aAttributes);

            $aRemap = array(
                'id'            => 'id',
                'date_created'  => 'create_date',
                'date_updated'  => 'update_date',
            );
        }else{
            // <= 5.14.2 compatibility for AMI::getResourceModel('search/table')
            $this->tableName = 'cms_site_search_index';
            $this->aAssociatedTables = array();

            parent::__construct($aAttributes);

            $aRemap = array(
                'sublink'          => AMI_ModTable::FIELD_DOESNT_EXIST,
                'id_page'          => 'id_page',
                'lang'             => 'lang',
                'header'           => 'name',
                'id'               => 'id',
                'public'           => 'public',
                'hide_in_list'     => AMI_ModTable::FIELD_DOESNT_EXIST
            );
        }
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
        // do nothing
    }
}

/**
 * AmiSearch/Search configuration table item model.
 *
 * @package    Config_AmiSearch_Search
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSearch_Search_TableItem extends Hyper_AmiSearch_TableItem{
}

/**
 * AmiSearch/Search configuration table list model.
 *
 * @package    Config_AmiSearch_Search
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSearch_Search_TableList extends Hyper_AmiSearch_TableList{
}
