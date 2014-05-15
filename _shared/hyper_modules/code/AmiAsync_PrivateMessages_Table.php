<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   AmiAsync/PrivateMessages
 * @version   $Id: AmiAsync_PrivateMessages_Table.php 47549 2014-02-05 12:24:33Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiAsync/PrivateMessages configuration table model.
 *
 * Private Messages fields description:
 * - <b>id_owner</b> - owner id (int),
 * - <b>id_sender</b> - sender id (int),
 * - <b>id_recipient</b> - recipient id (int),
 * - <b>date_created</b> - message creation date (datetime),
 * - <b>header</b> - message header (string),
 * - <b>id_body</b> - body id,
 * - <b>is_read</b> - flag specifying if message was read (0/1),
 * - <b>is_deleted</b> - flag specifying if message was deleted (0/1),
 * - <b>is_replied</b> - flag specifying if message was replied (0/1),
 * - <b>is_broadcast</b> - flag specifying breadcast message (0/1),
 * - <b>b_body</b> - body (string).
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Model
 * @resource   private_messages/table/model <code>AMI::getResourceModel('private_messages/table')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_Table extends Hyper_AmiAsync_Table{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_private_messages';

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model
     */
    public function __construct(array $aAttributes = array()){
        $this->setDependence('private_message_bodies', 'b', 'b.id=i.id_body');
        $this->setDependence('private_message_contacts', 'recipient', 'recipient.id_member=i.id_recipient AND recipient.id_owner=i.id_owner', 'LEFT JOIN');
        $this->setDependence('private_message_contacts', 'sender', 'sender.id_member=i.id_sender AND sender.id_owner=i.id_owner', 'LEFT JOIN');
        $mode = AMI_Registry::get('private_messages_mode', 'inbox');
        if($mode != 'inbox'){
            $this->setActiveDependence('recipient');
        }
        if($mode != 'sent'){
            $this->setActiveDependence('sender');
        }

        parent::__construct($aAttributes);
    }

    /**
     * Returns item model object and load data for key field param.
     *
     * @param  int|null $id  Primary key value
     * @param  array  $aFields  Fields to load (since 5.12.8)
     * @return AMI_ModTableItem
     */
    public function find($id, array $aFields = array('*')){
        $this->setActiveDependence('b');
        return parent::find($id, $aFields);
    }
}

/**
 * AmiAsync/PrivateMessages configuration table item model.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Model
 * @resource   private_messages/table/model/item <code>AMI::getResourceModel('private_messages/table')->getItem()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_TableItem extends Hyper_AmiAsync_TableItem{
    /**
     * Flag specifying to update body
     *
     * @var bool
     */
    private $doUpdateBody = false;

    /**
     * Initializing table item data.
     *
     * @param AMI_ModTable $oTable  Module table model
     * @param DB_Query     $oQuery  Required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery = null){
        AMI_Event::addHandler('on_get_available_fields', array($this, 'handleGetAvailableFields'), $this->getModId());

        parent::__construct($oTable, $oQuery);

        $this->oTable->addValidators(
            array(
                'id_owner'     => array('filled', 'stop_on_error'),
                'id_sender'    => array('filled', 'stop_on_error'),
                'id_recipient' => array('filled', 'stop_on_error'),
                'date_created' => array('date_time', 'filled', 'stop_on_error'),
                'header'       => array('filled', 'stop_on_error'),
                'id_body'      => array('filled', 'stop_on_error')
            )
        );
        $this->setFieldCallback('header', array($this, 'fcbHTMLEntities'));
    }

    /**
     * Appends category fields to available fields.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTable::getAvailableFields()
     */
    public function handleGetAvailableFields($name, array $aEvent, $handlerModId, $srcModId){
        $aAliases = $this->oTable->getActiveDependenceAliases();
        if(in_array('b', $aAliases)){
            if(empty($aEvent['aFields']['b'])){
                $aEvent['aFields']['b'] = array('body');
            }
        }
        if(in_array('sender', $aAliases)){
            if(empty($aEvent['aFields']['sender'])){
                $aEvent['aFields']['sender'] = array('id', 'nickname', 'is_deleted');
            }
        }
        if(in_array('recipient', $aAliases)){
            if(empty($aEvent['aFields']['recipient'])){
                $aEvent['aFields']['recipient'] = array('id', 'nickname', 'is_deleted');
            }
        }
        return $aEvent;
    }

    /**
     * Auto HTML-entities field callback.
     *
     * @param  array $aData  Formatter data
     * @return array
     */
    private function fcbHTMLEntities(array $aData){
        $action = $aData['action'];

        switch($action){
            case 'get':
                $aData['value'] = AMI_Lib_String::unhtmlEntities($aData['value']);
                break;
            case 'set':
                $aData['value'] = AMI_Lib_String::htmlChars($aData['value']);
                break;
        }

        return $aData;
    }
}

/**
 * AmiAsync/PrivateMessages configuration table list model.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Model
 * @resource   private_messages/table/model/list <code>AMI::getResourceModel('private_messages/table')->getList()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_TableList extends Hyper_AmiAsync_TableList{
    /**
     * Initializing table list data.
     *
     * @param AMI_ModTable $oTable  Table model
     * @param DB_Query     $oQuery  DB_Query object, required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery){
        parent::__construct($oTable, $oQuery);
        $this->oQuery->optimize();
    }
}

/**
 * AmiAsync/PrivateMessages configuration body table model.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Model
 * @resource   private_message_bodies/table/model <code>AMI::getResourceModel('private_message_bodies/table')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class PrivateMessageBodies_Table extends Hyper_AmiAsync_Table{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_private_message_bodies';
}

/**
 * AmiAsync/PrivateMessages configuration body table item model.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Model
 * @resource   private_message_bodies/table/model/item <code>AMI::getResourceModel('private_message_bodies/table')->getItem()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class PrivateMessageBodies_TableItem extends Hyper_AmiAsync_TableItem{
    /**
     * Initializing table item data.
     *
     * @param AMI_ModTable $oTable  Module table model
     * @param DB_Query     $oQuery  Required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery = null){
        parent::__construct($oTable, $oQuery);
        $this->oTable->addValidators(array('body' => array('filled', 'stop_on_error')));
    }
}

/**
 * AmiAsync/PrivateMessages configuration body table list model.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Model
 * @resource   private_message_bodies/table/model/list <code>AMI::getResourceModel('private_message_bodies/table')->getList()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class PrivateMessageBodies_TableList extends Hyper_AmiAsync_TableList{
}

/**
 * AmiAsync/PrivateMessages configuration contacts table model.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Model
 * @resource   private_message_contacts/table/model <code>AMI::getResourceModel('private_message_contacts/table')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class PrivateMessageContacts_Table extends Hyper_AmiAsync_Table{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_private_message_contacts';

    /**
     * Initializing table data.
     */
}

/**
 * AmiAsync/PrivateMessages configuration contacts table item model.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Model
 * @resource   private_message_contacts/table/model/item <code>AMI::getResourceModel('private_message_contacts/table')->getItem()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class PrivateMessageContacts_TableItem extends Hyper_AmiAsync_TableItem{
}

/**
 * AmiAsync/PrivateMessages configuration contacts table list model.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Model
 * @resource   private_message_contacts/table/model/list <code>AMI::getResourceModel('private_message_contacts/table')->getList()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class PrivateMessageContacts_TableList extends Hyper_AmiAsync_TableList{
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
                'id_owner'  => array('filled', 'stop_on_error'),
                'id_member' => array('filled', 'stop_on_error'),
                'nickname'  => array('filled', 'stop_on_error')
            )
        );
    }
}
