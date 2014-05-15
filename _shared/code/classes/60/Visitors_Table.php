<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Users
 * @version   $Id$
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Visitors table model.
 *
 * @package    Users
 * @subpackage Model
 * @resource   users/visitors/table/model <code>AMI::getResourceModel('users/visitors/table')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class Visitors_Table extends AMI_ModTable{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_visitors';
}

/**
 * News module table item model.
 *
 * @package    Users
 * @subpackage Model
 * @resource   users/visitors/table/model/item <code>AMI::getResourceModel('users/visitors/table')->getItem()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class Visitors_TableItem extends AMI_ModTableItem{
    /**
     * Save operation is allowed
     *
     * @var bool
     */
    protected  $bAllowSave = true;
}

/**
 * News module table list model.
 *
 * @package    Users
 * @subpackage Model
 * @resource   users/visitors/table/model/list <code>AMI::getResourceModel('users/visitors/table')->getList()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class Visitors_TableList extends AMI_ModTableList{
}
