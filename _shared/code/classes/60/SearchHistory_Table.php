<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Module_SearchHistory
 * @version   $Id: SearchHistory_Table.php 42102 2013-10-07 11:53:16Z Leontiev Anton $
 * @since     5.10.0
 */

/**
 * SearchHistory module table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Module_SearchHistory
 * @subpackage Model
 * @resource   search_history/table/model <code>AMI::getResourceModel('search_history/table')</code>
 * @since      5.10.0
 */
class SearchHistory_Table extends AMI_ModTable{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_search_history';

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     */
    public function __construct(array $aAttributes = array()){
        parent::__construct($aAttributes);

        $aRemap = array(
            'sublink'          => AMI_ModTable::FIELD_DOESNT_EXIST,
            'id_page'          => AMI_ModTable::FIELD_DOESNT_EXIST,
            'lang'             => 'lang',
            'id'               => 'id',
            'public'           => AMI_ModTable::FIELD_DOESNT_EXIST,
            'hide_in_list'     => AMI_ModTable::FIELD_DOESNT_EXIST,
            'quantity'         => 'quantity'
        );
        $this->addFieldsRemap($aRemap);
    }

    /**
     * Sets new table name for a model.
     *
     * Restrict to change table name.
     *
     * @param  string $tableName  New table name
     * @return void
     * @amidev Temporary
     */
    public function setTableName($tableName){
    }
}

/**
 * SearchHistory module table item model.
 *
 * @package    Module_SearchHistory
 * @subpackage Model
 * @resource   search_history/table/model/item <code>AMI::getResourceModel('search_history/table')->getItem()</code>
 * @since      5.10.0
 */
class SearchHistory_TableItem extends AMI_ModTableItem{
    /**
     * Initializing table item data.
     *
     * @param AMI_ModTable $oTable  Module table model
     * @param DB_Query     $oQuery  Required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery = null){
        parent::__construct($oTable, $oQuery);

        $this->setFieldCallback('oQuery', array($this, 'fcbHTMLEntities'));
    }
}

/**
 * SearchHistory module table list model.
 *
 * @package    Module_SearchHistory
 * @subpackage Model
 * @resource   search_history/table/model/list <code>AMI::getResourceModel('search_history/table')->getList()</code>
 * @since      5.10.0
 */
class SearchHistory_TableList extends AMI_ModTableList{
}
