<?php
/**
 * AmiAsynv/PrivateMessages configuration instance.
 * 
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Module_##modId##
 * @version   $Id: --modId--_Table.php 40338 2013-08-07 09:29:36Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Private Messages module table model.
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
 * @package    Module_##modId##
 * @subpackage Model
 * @resource   private_messages/table/model <code>AMI::getResourceModel('private_messages/table')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class ##modId##_Table extends AmiAsync_PrivateMessages_Table{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_private_messages';
}

/**
 * Private Messages module table item model.
 *
 * @package    Module_##modId##
 * @subpackage Model
 * @resource   private_messages/table/model/item <code>AMI::getResourceModel('private_messages/table')->getItem()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class ##modId##_TableItem extends AmiAsync_PrivateMessages_TableItem{
}

/**
 * Private Messages module table list model.
 *
 * @package    Module_##modId##
 * @subpackage Model
 * @resource   private_messages/table/model/list <code>AMI::getResourceModel('private_messages/table')->getList()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class ##modId##_TableList extends AmiAsync_PrivateMessages_TableList{
}
