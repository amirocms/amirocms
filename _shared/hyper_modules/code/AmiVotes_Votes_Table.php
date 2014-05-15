<?php
/**
 * AmiVotes/Votes configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiVotes_Votes
 * @version   $Id: AmiVotes_Votes_Table.php 42102 2013-10-07 11:53:16Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiVotes/Votes configuration table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Config_AmiVotes_Votes
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiVotes_Votes_Table extends Hyper_AmiVotes_Table{
    /**
     * Associated table names
     *
     * @var    array
     * @amidev Temporary
     * @todo   Avoid hardcode!!!
     */
    protected $aAssociatedTables = array('cms_votevals');

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     */
    public function __construct(array $aAttributes = array()){
        $this->tableName = 'cms_' . $this->getModId();
        // $this->setDependence($this->getModId() . '_cat', 'cat', 'cat.id=i.id_cat');

        parent::__construct($aAttributes);

        $aRemap = array(
            'id'               => 'id',
            'header'           => 'question',
            'public'           => 'public',
            'date'             => 'date_end',
            'sublink'          => 'sublink',
            'lang'             => 'lang',
            'date_created'     => 'date_start',
            'status'           => 'date_end',
            'announce'         => 'question',
            'body'             => 'question'
        );
        $this->addFieldsRemap($aRemap);
    }
}

/**
 * AmiVotes/Votes configuration table item model.
 *
 * @package    Config_AmiVotes_Votes
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiVotes_Votes_TableItem extends Hyper_AmiVotes_TableItem{
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
}

/**
 * AmiVotes/Votes configuration table list model.
 *
 * @package    Config_AmiVotes_Votes
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiVotes_Votes_TableList extends Hyper_AmiVotes_TableList{
}
