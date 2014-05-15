<?php
/**
 * AmiJobs/History configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiJobs_History
 * @version   $Id: AmiJobs_History_Table.php 42102 2013-10-07 11:53:16Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiJobs/History configuration table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Config_AmiJobs_History
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_History_Table extends Hyper_AmiJobs_Table{
    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     */
    public function __construct(array $aAttributes = array()){
        $this->tableName = 'cms_' . $this->getModId();
        $this->setDependence('jobs_resume', 'resume', 'resume.id_jobs_history=i.id', 'LEFT JOIN');
        $this->setActiveDependence('resume');

        parent::__construct($aAttributes);

        $aRemap = array(
            'id'               => 'id',
            'public'           => 'public',
            'date'             => 'date',
            'header'           => 'title',
            'title'            => 'title',
            'sublink'          => 'sublink',
            'id_page'          => 'id_page',
            'lang'             => 'lang',
            'date_created'     => 'date',
            'sticky'           => 'urgent',
            'date_sticky_till' => 'urgent_date',
            'login'            => 'user_id',
            'num'              => 'id',
        );
        $this->addFieldsRemap($aRemap);
    }
}

/**
 * AmiJobs/History configuration table item model.
 *
 * @package    Config_AmiJobs_History
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_History_TableItem extends Hyper_AmiJobs_TableItem{
}

/**
 * AmiJobs/History configuration table list model.
 *
 * @package    Config_AmiJobs_History
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_History_TableList extends Hyper_AmiJobs_TableList{
}
