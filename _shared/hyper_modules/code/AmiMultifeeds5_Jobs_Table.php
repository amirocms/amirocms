<?php
/**
 * AmiMultifeeds5/Jobs configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiMultifeeds5_Jobs
 * @version   $Id: AmiMultifeeds5_Jobs_Table.php 42166 2013-10-10 12:29:32Z Leontiev Anton $
 * @since     x.x.x
 * @amidev
 */

/**
 * AmiMultifeeds5/Jobs configuration table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Config_AmiMultifeeds5_Jobs
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Jobs_Table extends Hyper_AmiMultifeeds5_Table{
    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     */
    public function __construct(array $aAttributes = array()){
        $this->setDependence('cat', 'cat', 'cat.id=i.id_cat');

        parent::__construct($aAttributes);

        $aRemap = array(
            'id'               => 'id',
            'public'           => 'public',
            'date'             => 'date',
            'sublink'          => 'sublink',
            'id_page'          => 'id_page',
            'lang'             => 'lang',
            'header'           => 'name',
            'date_created'     => 'date',
            'date_modified'    => 'modified_date',
            'sticky'           => 'urgent',
            'date_sticky_till' => 'urgent_date',
            'announce'         => 'name',
            'body'             => 'name'
        );
        $this->addFieldsRemap($aRemap);
    }
}

/**
 * AmiMultifeeds5/Jobs configuration table item model.
 *
 * @package    Config_AmiMultifeeds5_Jobs
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Jobs_TableItem extends Hyper_AmiMultifeeds5_TableItem{
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
 * AmiMultifeeds5/Jobs configuration table list model.
 *
 * @package    Config_AmiMultifeeds5_Jobs
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Jobs_TableList extends Hyper_AmiMultifeeds5_TableList{
}
