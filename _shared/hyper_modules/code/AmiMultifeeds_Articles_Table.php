<?php
/**
 * AmiMultifeeds/Articles configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiMultifeeds_Articles
 * @version   $Id: AmiMultifeeds_Articles_Table.php 45064 2013-12-05 09:27:10Z Leontiev Anton $
 * @since     6.0.2
 */

/**
 * AmiMultifeeds/Articles configuration table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * Articles fields description:
 * - <b>author</b> - article author (string),
 * - <b>source</b> - article source (string).
 *
 * @package    Config_AmiMultifeeds_Articles
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Articles_Table extends Hyper_AmiMultifeeds_Table{
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
            'id'      => 'id',
            'public'  => 'public',
            'sublink' => 'sublink',
            'id_page' => 'id_page',
            'lang'    => 'lang'
        );
        $this->addFieldsRemap($aRemap);
    }
}

/**
 * AmiMultifeeds/Articles configuration table item model.
 *
 * @package    Config_AmiMultifeeds_Articles
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Articles_TableItem extends Hyper_AmiMultifeeds_TableItem{
    /**
     * Initializing table item data.
     *
     * @param AMI_ModTable $oTable  Module table model
     * @param DB_Query     $oQuery  Required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery = null){
        parent::__construct($oTable, $oQuery);

        $this->setFieldCallback('author', array($this, 'fcbHTMLEntities'));
        $this->setFieldCallback('source', array($this, 'fcbHTMLEntities'));
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
 * AmiMultifeeds/Articles configuration table list model.
 *
 * @package    Config_AmiMultifeeds_Articles
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Articles_TableList extends Hyper_AmiMultifeeds_TableList{
    /**
     * Initializing table list data.
     *
     * @param AMI_ModTable $oTable  Table model
     * @param DB_Query     $oQuery  DB_Query object, required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery){
        parent::__construct($oTable, $oQuery);

        $oQuery->addExpressionField('(LENGTH(i.body) > 0) `body_notempty`');
    }

    /**
     * Returns array of available fields.
     *
     * @param  bool $bAppendEventFields  Fire 'on_get_available_fields' event to append extension fields
     * @return array
     */
    public function getAvailableFields($bAppendEventFields = true){
        $aFields = $this->oTable->getAvailableFields($bAppendEventFields);
        $aFields[] = 'body_notempty';

        return $aFields;
    }
}
