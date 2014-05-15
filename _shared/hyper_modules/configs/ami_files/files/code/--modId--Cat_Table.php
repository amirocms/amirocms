<?php
/**
 * AmiFiles/Files configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Config_##modId##
 * @version   $Id: --modId--Cat_Table.php 44504 2013-11-27 10:21:28Z Leontiev Anton $
 * @since     5.14.4
 */

/**
 * AmiFiles/Files configuration category table model.
 *
 * @package    Config_##modId##
 * @subpackage Model
 * @resource   {$modId}_cat/table/model <code>AMI::getResourceModel('{$modId}_cat/table')*</code>
 * @since      5.14.4
 */
class ##modId##Cat_Table extends Hyper_AmiMultifeeds_Cat_Table{
    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model
     */
    public function __construct(array $aAttributes = array()){
        $this->tableName = 'cms_' . $this->getModId();

        parent::__construct($aAttributes);

        $aRemap = array(
            'id'               => 'id',
            'public'           => 'public',

            'sublink'          => 'sublink',
            'id_page'          => 'id_page',
            'lang'             => 'lang',

            'header'           => 'name',
            'sticky'           => 'urgent',
            'date_sticky_till' => 'urgent_date',
            'hide_in_list'     => 'public_direct_link',
            'date_modified'    => 'modified_date',
        );
        $this->addFieldsRemap($aRemap);
    }
}

/**
 * AmiFiles/Files configuration category table item model.
 *
 * @package    Config_##modId##
 * @subpackage Model
 * @resource   {$modId}_cat/table/model/item <code>AMI::getResourceModel('{$modId}_cat/table')->getItem()*</code>
 * @since      5.14.4
 */
class ##modId##Cat_TableItem extends Hyper_AmiMultifeeds_Cat_TableItem{
}

/**
 * AmiFiles/Files configuration category table list model.
 *
 * @package    Config_##modId##
 * @subpackage Model
 * @resource   {$modId}_cat/table/model/list <code>AMI::getResourceModel('{$modId}_cat/table')->getList()*</code>
 * @since      5.14.4
 */
class ##modId##Cat_TableList extends Hyper_AmiMultifeeds_Cat_TableList{
}
