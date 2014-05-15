<?php
/**
 * AmiDiscussion/Discussion configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiDiscussion_Discussion
 * @version   $Id: AmiDiscussion_Discussion_Table.php 48796 2014-03-18 11:27:14Z Kolesnikov Artem $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiDiscussion/Discussion configuration table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Config_AmiDiscussion_Discussion
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Discussion_Table extends Hyper_AmiDiscussion_Table{
    /**
     * Database table name, must be declared in child classes
     *
     * @var string
     */
    protected $tableName = 'cms_discussion';

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model
     */
    public function __construct(array $aAttributes = array()){
        $this->setDependence($this->getModId(), 'dd', "`dd`.`id` = `i`.`last_msg_id`", 'LEFT OUTER JOIN');

        parent::__construct($aAttributes);

        $aRemap = array(
            /*
            'author'        => 'author',
            'body'          => '',
            'id_ext_module' => 'id',
            */
            'id'                    => 'id',
            'id_parent'             => 'id_parent',
            'count_children'        => 'count_children',
            'count_public_children' => 'count_public_children',
            'ip'                    => 'ip',
            'id_member'             => 'id_member',
            'author'                => 'author',
            'source_app_id'         => 'source_app_id',
            'message'               => 'message',
            'date_created'          => 'date',
            'public'                => 'public',
            'lang'                  => 'lang',
            'date_modified'         => 'modified_date',
            'id_ext_module'         => 'id_ext_module',
            'ext_module'            => 'ext_module',
            'msg_id'                => 'msg_id',
            'msg_id_member'         => 'msg_id_member',
            'last_msg_id'           => 'last_msg_id',
            'msg_author'            => 'msg_author',
            'msg_date'              => 'msg_date',
            'rating_pos'            => 'rating_pos',
            'rating_neg'            => 'rating_neg'
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
 * AmiDiscussion/Discussion configuration table item model.
 *
 * @package    Config_AmiDiscussion_Discussion
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Discussion_TableItem extends Hyper_AmiDiscussion_TableItem{
    /**
     * Initializing table item data.
     *
     * @param AMI_ModTable $oTable  Module table model
     * @param DB_Query     $oQuery  Required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery = null){
        parent::__construct($oTable, $oQuery);
        // Add virtual field callback
        $this->setFieldCallback('src_ext_module', array($this, 'fcbSourceExtModule'));
    }

    /**
     * Virtual 'age' field callback.
     *
     * @param  array $aData  Field data
     * @return array
     */
    protected function fcbSourceExtModule(array $aData){
        $action = $aData['action'];

        switch($action){
            case 'get':
                $aData['value'] = $this->aData['ext_module'];
                break;
            case 'set':
                $aData['_skip'] = TRUE;
                break;
        }

        return $aData;
    }
}

/**
 * AmiDiscussion/Discussion configuration table list model.
 *
 * @package    Config_AmiDiscussion_Discussion
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Discussion_TableList extends Hyper_AmiDiscussion_TableList{
}
