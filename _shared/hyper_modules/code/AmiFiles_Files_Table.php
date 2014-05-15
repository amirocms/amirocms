<?php
/**
 * AmiFiles/Files configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiFiles_Files
 * @version   $Id: AmiFiles_Files_Table.php 45064 2013-12-05 09:27:10Z Leontiev Anton $
 * @since     6.0.2
 */

/**
 * AmiFiles/Files configuration table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Config_AmiFiles_Files
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      6.0.2
 */
class AmiFiles_Files_Table extends Hyper_AmiFiles_Table{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_files';

    /**
     * Associated table names
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aAssociatedTables = array('cms_ftypes');

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     */
    public function __construct(array $aAttributes = array()){
        $this->setDependence('files_cat', 'cat', 'cat.id=i.id_cat');
        $this->setDependence('file_type', 'ft', 'ft.id=i.ftype');
        $this->setActiveDependence('ft');
        // $this->addSystemFields(array('mdate'));

        parent::__construct($aAttributes);

        $aRemap = array(
            'id'               => 'id',
            'public'           => 'public',

            'sublink'          => 'sublink',
            'id_page'          => 'id_page',
            'lang'             => 'lang',

            'header'           => 'name',
            'body'             => 'description',
            'name_fs'          => 'filename',
            'name_orig'        => 'original_fname',
            'size'             => 'filesize',
            'type'             => 'ftype',

            'sticky'           => 'urgent',
            'date_sticky_till' => 'urgent_date',
            'hide_in_list'     => 'public_direct_link',
            'date_created'     => 'cdate',
            'date_modified'    => 'mdate'
        );
        $this->addFieldsRemap($aRemap);
    }

    /**
     * Sets new table name for a model.
     *
     * @param  string $tableName  New table name
     * @return void
     * @amidev Temporary
     */
    public function setTableName($tableName){
        // Discard table name changing
    }
}

/**
 * AmiFiles/Files configuration table item model.
 *
 * @package    Config_AmiFiles_Files
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      6.0.2
 */
class AmiFiles_Files_TableItem extends Hyper_AmiFiles_TableItem{
    /**
     * Common fields validators
     *
     * @var array
     */
    protected $aCommonFieldsValidators =
        array(
            'lang'         => array('filled'),
            'header'       => array('filled', 'stop_on_error'),
            'body'         => array('required')
        );

    /**
     * Initializing table item data.
     *
     * @param AMI_ModTable $oTable  Module table model
     * @param DB_Query     $oQuery  Required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery = null){
        parent::__construct($oTable, $oQuery);
        $this->setFieldType(
            'file',
            'file',
            array(
                'path'     => '_mod_files/ftpfiles',
                'aMapping' => array(
                    'name_orig' => 'realName',
                    'name_fs'   => 'name',
                    'type'      => 'type',
                    'size'      => 'size'
                )
            )
        );
    }

    /**
     * Saves current item data.
     *
     * @return bool
     */
    public function save(){
        if(isset($GLOBALS['AMI_ENV_SETTINGS']['mode']) && $GLOBALS['AMI_ENV_SETTINGS']['mode'] == 'full'){
            $this->bAllowSave = true;
        }
        return parent::save();
    }
}

/**
 * AmiFiles/Files configuration table list model.
 *
 * @package    Config_AmiFiles_Files
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      6.0.2
 */
class AmiFiles_Files_TableList extends Hyper_AmiFiles_TableList{
}
