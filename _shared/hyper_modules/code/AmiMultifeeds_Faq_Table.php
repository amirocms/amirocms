<?php
/**
 * AmiMultifeeds/FAQ configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Config_AmiMultifeeds_FAQ
 * @version   $Id: AmiMultifeeds_Faq_Table.php 45064 2013-12-05 09:27:10Z Leontiev Anton $
 * @since     6.0.2
 */

/**
 * AmiMultifeeds/FAQ configuration table model.
 *
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Faq_Table extends Hyper_AmiMultifeeds_Table{
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
            'date'             => 'date',

            'sublink'          => 'sublink',
            'id_page'          => 'id_page',
            'lang'             => 'lang',

            'question'         => 'question',
            'answer'           => 'answer',

            'sticky'           => 'sticky',
            'date_sticky_till' => 'date_sticky_till',
            'hide_in_list'     => 'hide_in_list',
            'date_created'     => 'date_created',
            'date_modified'    => 'date_modified'
        );
        $this->addFieldsRemap($aRemap);
    }
}

/**
 * AmiMultifeeds/FAQ configuration table item model.
 *
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Faq_TableItem extends Hyper_AmiMultifeeds_TableItem{
    /**
     * Common fields validators
     *
     * @var array
     */
    protected $aCommonFieldsValidators = array();

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
                'question'     => array('filled', 'stop_on_error'),
                'answer'       => array('required', 'stop_on_error'),
                'date_created' => array('filled', 'stop_on_error')
            )
        );
        // Add virtual fields callback
        $this->setFieldCallback('announce', array($this, 'fcbVirtual'));
        $this->setFieldCallback('body', array($this, 'fcbVirtual'));
    }

    /**
     * Virtual 'announce'/'body' field callback.
     *
     * @param  array $aData  Field data
     * @return array
     */
    protected function fcbVirtual(array $aData){
        $aMapping = array(
            'announce' => 'question',
            'body'     => 'answer'
        );
        if($aData['action'] === 'get'){
            $aData['value'] = $aData['oItem']->getValue($aMapping[$aData['name']]);
        }else{
            $aData['oItem']->setValue($aMapping[$aData['name']], $aData['value']);
        }
        return $aData;
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
 * AmiMultifeeds/FAQ configuration table list model.
 *
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Faq_TableList extends Hyper_AmiMultifeeds_TableList{
    /**
     * Initializing table list data.
     *
     * @param AMI_ModTable $oTable  Table model
     * @param DB_Query     $oQuery  DB_Query object, required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery){
        parent::__construct($oTable, $oQuery);
        $oQuery->addExpressionField('(LENGTH(i.answer) > 0) is_answered');
    }

    /**
     * Returns array of available fields.
     *
     * @param  bool $bAppendEventFields  Fire 'on_get_available_fields' event to append extension fields
     * @return array
     */
    public function getAvailableFields($bAppendEventFields = true){
        $aFields = $this->oTable->getAvailableFields($bAppendEventFields);
        $aFields[] = 'is_answered';
        return $aFields;
    }
}
