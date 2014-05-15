<?php
/**
 * AmiJobs/Resume configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiJobs_Resume
 * @version   $Id: AmiJobs_Resume_Table.php 42102 2013-10-07 11:53:16Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiJobs/Resume configuration table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Config_AmiJobs_Resume
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_Resume_Table extends Hyper_AmiJobs_Table{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_jobs_resume';

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     */
    public function __construct(array $aAttributes = array()){
        $this->setDependence('jobs_cat', 'dp', 'dp.id=i.id_cat', 'LEFT JOIN');
        $this->setActiveDependence('dp');

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
            'announce'         => 'title',
            'body'             => 'title'
        );

        $this->addFieldsRemap($aRemap);
    }
}

/**
 * AmiJobs/Resume configuration table item model.
 *
 * @package    Config_AmiJobs_Resume
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_Resume_TableItem extends Hyper_AmiJobs_TableItem{
    /**
     * Common fields validators
     *
     * @var array
     */
    protected $aCommonFieldsValidators =
        array(
            'lang'         => array('filled'),
            'date_created' => array('filled', 'stop_on_error'),
        );
}

/**
 * AmiJobs/Resume configuration table list model.
 *
 * @package    Config_AmiJobs_Resume
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_Resume_TableList extends Hyper_AmiJobs_TableList{
}
