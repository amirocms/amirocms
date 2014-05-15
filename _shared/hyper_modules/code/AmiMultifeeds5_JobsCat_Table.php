<?php
/**
 * AmiMultifeeds5/Jobs configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiMultifeeds5_JobsCat
 * @version   $Id: AmiMultifeeds5_JobsCat_Table.php 42166 2013-10-10 12:29:32Z Leontiev Anton $
 * @since     x.x.x
 * @amidev
 */

/**
 * AmiMultifeeds5/Jobs configuration category table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Config_AmiMultifeeds5_JobsCat
 * @subpackage Model
 * @resource   {$modId}_cat/table/model <code>AMI::getResourceModel('{$modId}_cat/table')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_JobsCat_Table extends Hyper_AmiMultifeeds5_Cat_Table{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_jobs_cat';

    /**
     * Category items resource table string.
     *
     * @var string
     */
    protected $subItemsTableResource = 'jobs/table';

    /**
     * Category items module.
     *
     * @var string
     */
    protected $subItemsModuleName = 'jobs';

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     */
    public function __construct(array $aAttributes = array()){
        parent::__construct($aAttributes);

        $aRemap = array(
            'id'               => 'id',
            'public'           => 'public',
            'header'           => 'name',
            'sublink'          => 'sublink',
            'id_page'          => 'id_page',
            'lang'             => 'lang',
            'date_modified'    => 'modified_date',
            'sticky'           => 'urgent',
            'date_sticky_till' => 'urgent_date',
        );
        $this->addFieldsRemap($aRemap);
    }
}

/**
 * AmiMultifeeds5/Jobs configuration table item model.
 *
 * @package    Config_AmiMultifeeds5_JobsCat
 * @subpackage Model
 * @resource   {$modId}_cat/table/model/item <code>AMI::getResourceModel('{$modId}_cat/table')->getItem()*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_JobsCat_TableItem extends Hyper_AmiMultifeeds5_Cat_TableItem{
}

/**
 * AmiMultifeeds5/Jobs configuration table list model.
 *
 * @package    Config_AmiMultifeeds5_JobsCat
 * @subpackage Model
 * @resource   {$modId}_cat/table/model/list <code>AMI::getResourceModel('{$modId}_cat/table')->getList()*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_JobsCat_TableList extends Hyper_AmiMultifeeds5_Cat_TableList{
}
