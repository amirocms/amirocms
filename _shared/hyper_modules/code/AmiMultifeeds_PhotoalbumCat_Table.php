<?php
/**
 * AmiMultifeeds/PhotoGallery configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Config_AmiMultifeeds_PhotoGallery
 * @version   $Id: AmiMultifeeds_PhotoalbumCat_Table.php 45064 2013-12-05 09:27:10Z Leontiev Anton $
 * @since     6.0.2
 */

/**
 * AmiMultifeeds/PhotoGallery configuration category module table model.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage Model
 * @resource   {$modId}_cat/table/model <code>AMI::getResourceModel('{$modId}_cat/table')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_PhotoalbumCat_Table extends Hyper_AmiMultifeeds_Cat_Table{
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

            'header'           => 'header',
            'sticky'           => 'sticky',
            'date_sticky_till' => 'date_sticky_till',
            'hide_in_list'     => 'hide_in_list',
            'date_modified'    => 'date_modified',
            // 'num_public_items' => 'num_public_items',
        );
        $this->addFieldsRemap($aRemap);
    }
}

/**
 * AmiMultifeeds/PhotoGallery configuration category module table item model.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage Model
 * @resource   {$modId}_cat/table/model/item <code>AMI::getResourceModel('{$modId}_cat/table')->getItem()*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_PhotoalbumCat_TableItem extends Hyper_AmiMultifeeds_Cat_TableItem{
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
 * AmiMultifeeds/PhotoGallery configuration category module table list model.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage Model
 * @resource   {$modId}_cat/table/model/list <code>AMI::getResourceModel('{$modId}_cat/table')->getList()*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_PhotoalbumCat_TableList extends Hyper_AmiMultifeeds_Cat_TableList{
}
