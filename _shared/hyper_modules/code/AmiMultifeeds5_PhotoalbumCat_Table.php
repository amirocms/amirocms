<?php
/**
 * AmiMultifeeds5/PhotoGallery configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Config_AmiMultifeeds5_PhotoGallery
 * @version   $Id: AmiMultifeeds5_PhotoalbumCat_Table.php 42166 2013-10-10 12:29:32Z Leontiev Anton $
 * @since     x.x.x
 * @amidev
 */

/**
 * AmiMultifeeds5/PhotoGallery configuration category module table model.
 *
 * @package    Config_AmiMultifeeds5_PhotoGallery
 * @subpackage Model
 * @resource   {$modId}_cat/table/model <code>AMI::getResourceModel('{$modId}_cat/table')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_PhotoalbumCat_Table extends Hyper_AmiMultifeeds5_Cat_Table{
    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     */
    public function __construct(array $aAttributes = array()){
        $this->tableName = 'cms_' . $this->getModId();
        // $this->subItemsModId = 'photoalbum';
        // $this->subItemsTableResId = 'photoalbum/table';

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
            // 'num_public_items' => 'num_public_items',
            // 'ext_dsc_disable'  => 'disable_comments'
        );
        $this->addFieldsRemap($aRemap);
    }
}

/**
 * AmiMultifeeds5/PhotoGallery configuration category module table item model.
 *
 * @package    Config_AmiMultifeeds5_PhotoGallery
 * @subpackage Model
 * @resource   {$modId}_cat/table/model/item <code>AMI::getResourceModel('{$modId}_cat/table')->getItem()*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_PhotoalbumCat_TableItem extends Hyper_AmiMultifeeds5_Cat_TableItem{
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
 * AmiMultifeeds5/PhotoGallery configuration category module table list model.
 *
 * @package    Config_AmiMultifeeds5_PhotoGallery
 * @subpackage Model
 * @resource   {$modId}_cat/table/model/list <code>AMI::getResourceModel('{$modId}_cat/table')->getList()*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_PhotoalbumCat_TableList extends Hyper_AmiMultifeeds5_Cat_TableList{
}
