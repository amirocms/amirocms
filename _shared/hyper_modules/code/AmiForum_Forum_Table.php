<?php
/**
 * AmiForum/Forum configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiForum_Forum
 * @version   $Id: AmiForum_Forum_Table.php 45507 2013-12-18 12:14:29Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiForum/Forum configuration table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('forum/table')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_Forum_Table extends Hyper_AmiForum_Table{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_forum';

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model
     */
    public function __construct(array $aAttributes = array()){
        $this->setDependence($this->getModId() . '_cat', 'cat', '`cat`.`id` = `i`.`id_cat`');

        parent::__construct($aAttributes);

        $aRemap = array(
            'id'     => 'id',
            'public'           => 'public',

            'sublink'          => 'sublink',
            'lang'             => 'lang',

            'sticky'           => 'urgent',
            'date_sticky_till' => 'urgent_date',
            'hide_in_list'     => 'public_direct_link',
            'header'           => 'topic',
            'topic'            => 'topic',
            'date_created'     => 'date',
            'date_modified'    => 'modified_date'
        );
        $this->addFieldsRemap($aRemap);
    }

    /**
     * Sets new table name for a model.
     *
     * @param string $tableName  New table name
     * @return void
     */
    public function setTableName($tableName){
    }
}

/**
 * AmiForum/Forum configuration table item model.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('forum/table')->getItem()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_Forum_TableItem extends Hyper_AmiForum_TableItem{
    /**
     * Common fields validators
     *
     * @var array
     */
    protected $aCommonFieldsValidators =
        array(
            'lang'    => array('filled'),
            'topic'   => array('filled', 'stop_on_error'),
            'message' => array('filled', 'stop_on_error')
        );
}

/**
 * AmiForum/Forum configuration table list model.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('forum/table')->getList()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_Forum_TableList extends Hyper_AmiForum_TableList{
}
