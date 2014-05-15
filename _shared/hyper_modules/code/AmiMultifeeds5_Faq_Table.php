<?php
/**
 * AmiMultifeeds5/FAQ configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Config_AmiMultifeeds5_FAQ
 * @version   $Id: AmiMultifeeds5_Faq_Table.php 47549 2014-02-05 12:24:33Z Leontiev Anton $
 * @since     x.x.x
 * @amidev
 */

/**
 * AmiMultifeeds5/FAQ configuration table model.
 *
 * @package    Config_AmiMultifeeds5_FAQ
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Faq_Table extends Hyper_AmiMultifeeds5_Table{
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

            'sticky'           => 'urgent',
            'date_sticky_till' => 'urgent_date',
            'hide_in_list'     => 'public_direct_link',
            'date_created'     => 'date',
            'date_modified'    => 'modified_date'
        );
        $this->addFieldsRemap($aRemap);
    }
}

/**
 * AmiMultifeeds5/FAQ configuration table item model.
 *
 * @package    Config_AmiMultifeeds5_FAQ
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Faq_TableItem extends Hyper_AmiMultifeeds5_TableItem{
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
        $action = $aData['action'];
        $aMapping = array(
            'announce' => 'question',
            'body'     => 'answer'
        );

        switch($action){
            case 'get':
                $aData['value'] = $aData['oItem']->getValue($aMapping[$aData['name']]);
                break;
            case 'set':
                $aData['oItem']->setValue($aMapping[$aData['name']], $aData['value']);
                break;
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
 * AmiMultifeeds5/FAQ configuration table list model.
 *
 * @package    Config_AmiMultifeeds5_FAQ
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Faq_TableList extends Hyper_AmiMultifeeds5_TableList{
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
