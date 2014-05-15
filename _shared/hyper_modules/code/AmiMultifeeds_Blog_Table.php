<?php
/**
 * AmiMultifeeds/Blog configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Config_AmiMultifeeds_Blog
 * @version   $Id: AmiMultifeeds_Blog_Table.php 45064 2013-12-05 09:27:10Z Leontiev Anton $
 * @since     6.0.2
 */

/**
 * AmiMultifeeds/Blog configuration table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Config_AmiMultifeeds_Blog
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Blog_Table extends Hyper_AmiMultifeeds_Table{
    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     */
    public function __construct(array $aAttributes = array()){
        $this->tableName = 'cms_' . $this->getModId();
        $this->setDependence($this->getModId() . '_cat', 'cat', 'cat.id=i.id_cat');

        parent::__construct($aAttributes);

        $aRemap = array(
            'id'               => 'id',
            'public'           => 'public',
            // 'date'             => 'date',

            'sublink'          => 'sublink',
            'id_page'          => 'id_page',
            'lang'             => 'lang',

            'sticky'           => 'sticky',
            'date_sticky_till' => 'date_sticky_till',
            'hide_in_list'     => 'hide_in_list',
            'date_created'     => 'date_created',
            'date_modified'    => 'date_modified',
            // 'ext_tags'         => 'tags',
            // 'ext_dsc_disable'  => 'disable_comments'
        );
        $this->addFieldsRemap($aRemap);
    }
}

/**
 * AmiMultifeeds/Blog configuration table item model.
 *
 * @package    Config_AmiMultifeeds_Blog
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Blog_TableItem extends Hyper_AmiMultifeeds_TableItem{
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
                'lang'         => array('filled'),
                'header'       => array('filled', 'stop_on_error'),
                'announce'     => array('filled', 'stop_on_error'),
                'date_created' => array('date_time', 'filled', 'stop_on_error'),
                'body'         => array('required')
            )
        );
        $this->setFieldCallback('header', array($this, 'fcbHTMLEntities'));
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
 * AmiMultifeeds/Blog configuration table list model.
 *
 * @package    Config_AmiMultifeeds_Blog
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Blog_TableList extends Hyper_AmiMultifeeds_TableList{
}
