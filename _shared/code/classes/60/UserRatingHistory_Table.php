<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   AmiExt_UserRating
 * @version   $Id: UserRatingHistory_Table.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * User rating history table model.
 *
 * @package    AmiExt_UserRating
 * @subpackage Model
 * @resource   users/rating/table/model <code>AMI::getResourceModel('users/rating/table')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class UserRatingHistory_Table extends AMI_ModTable{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_members_rating_history';
}

/**
 * News module table item model.
 *
 * @package    Users
 * @subpackage Model
 * @resource   users/rating/table/model/item <code>AMI::getResourceModel('users/rating/table')->getItem()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class UserRatingHistory_TableItem extends AMI_ModTableItem{
}

/**
 * News module table list model.
 *
 * @package    Users
 * @subpackage Model
 * @resource   users/rating/table/model/list <code>AMI::getResourceModel('users/rating/table')->getList()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class UserRatingHistory_TableList extends AMI_ModTableList{
}
