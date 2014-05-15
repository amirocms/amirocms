<?php
/**
 * AmiDiscussion/Guestbook configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiDiscussion_Guestbook
 * @version   $Id: AmiDiscussion_Guestbook_Table.php 42102 2013-10-07 11:53:16Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiDiscussion/Guestbook configuration table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Config_AmiDiscussion_Guestbook
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Guestbook_Table extends Hyper_AmiDiscussion_Table{
    /**
     * Database table name, must be declared in child classes
     *
     * @var string
     */
    protected $tableName = 'cms_guestbook';

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     */
    public function __construct(array $aAttributes = array()){
        // $this->tableName = 'cms_' . $this->getModId();

        parent::__construct($aAttributes);

        $aRemap = array(
            'id'                    => 'id',
            'id_parent'             => 'id_parent',
            'count_children'        => 'count_children',
            'count_public_children' => 'count_public_children',
            'subject'               => 'subject',
            'ip'                    => 'ip',
            'id_member'             => 'id_member',
            'author'                => 'author',
            'source_app_id'         => 'source_app_id',
            'message'               => 'message',
            'date_created'          => 'date',
            'public'                => 'public',
            'lang'                  => 'lang',
            'date_modified'         => 'modified_date'
        );
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
    }
}

/**
 * AmiDiscussion/Guestbook configuration table item model.
 *
 * @package    Config_AmiDiscussion_Guestbook
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Guestbook_TableItem extends Hyper_AmiDiscussion_TableItem{
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
 * AmiDiscussion/Guestbook configuration table list model.
 *
 * @package    Config_AmiDiscussion_Guestbook
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Guestbook_TableList extends Hyper_AmiDiscussion_TableList{
}
