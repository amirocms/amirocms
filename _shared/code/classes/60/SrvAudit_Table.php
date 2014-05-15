<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Module_Audit
 * @version   $Id: SrvAudit_Table.php 42102 2013-10-07 11:53:16Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Audit module table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Module_Audit
 * @subpackage Model
 * @resource   srv_audit/table/model <code>AMI::getResourceModel('srv_audit/table')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class SrvAudit_Table extends AMI_ModTable{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_srv_audit';

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     * @todo  Describe several fields
     */
    public function __construct(array $aAttributes = array()){
        $this->addSystemFields(array('id_iteration'));

        parent::__construct($aAttributes);

        $aRemap = array(
            'date_created'  => 'date',
            'date_modified' => 'modified_date',
            'mod_id'        => 'module_name',
            'status_orig'   => 'original_status',
            'status_audit'  => 'audit_status',
            'data'          => 'comments'
        );
        $this->addFieldsRemap($aRemap);
    }
}

/**
 * Audit module table item model.
 *
 * @package    Module_Audit
 * @subpackage Model
 * @resource   srv_audit/table/model/item <code>AMI::getResourceModel('srv_audit/table')->getItem()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class SrvAudit_TableItem extends AMI_ModTableItem{
    /**
     * Allows to save model item in fast env (for ModManager codegen feature).
     *
     * @var bool
     */
    protected $bAllowSave = TRUE;

    /**
     * Initializing table item data.
     *
     * @param AMI_ModTable $oTable  Module table model
     * @param DB_Query     $oQuery  Required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery = null){
        parent::__construct($oTable, $oQuery);

        $this->oTable->addValidators(
            array(
                'lang'     => array('filled'),
                'mod_id'   => array('filled'),
                'id_item'  => array('filled'),
                'id_actor' => array('filled'),
                'id_owner' => array('filled'),
                'data'     => array('filled'),
            )
        );
        $this->setValue('mod_id', preg_replace('/_audit$/', '', $this->getModId()));
        $this->setFieldCallback('data', array($this, 'fcbSerialized'));
    }
}

/**
 * Audit module table list model.
 *
 * @package    Module_Audit
 * @subpackage Model
 * @resource   srv_audit/table/model/list <code>AMI::getResourceModel('srv_audit/table')->getList()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class SrvAudit_TableList extends AMI_ModTableList{
    /**
     * Initializing table list data.
     *
     * @param AMI_ModTable $oTable  Table model
     * @param DB_Query     $oQuery  DB_Query object, required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery){
        parent::__construct($oTable, $oQuery);

        $this->oQuery->addWhereDef(
            DB_Query::getSnippet(" AND i.`module_name` = %s")
            ->q($this->getModId())
        );
    }
}
