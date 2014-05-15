<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Module_ModManager
 * @version   $Id: ModManagerAudit_Table.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @amidev
 */

/**
 * Module Manager Audit table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Module_ModManager
 * @subpackage Model
 * @resource   mod_manager_audit/table/model <code>AMI::getResourceModel('mod_manager_audit/table')</code>
 * @amidev
 */
class ModManagerAudit_Table extends SrvAudit_Table{
}

/**
 * Module Manager Audit table item model.
 *
 * @package    Module_ModManager
 * @subpackage Model
 * @resource   mod_manager_audit/table/model/item <code>AMI::getResourceModel('mod_manager_audit/table')->getItem()</code>
 * @amidev
 */
class ModManagerAudit_TableItem extends SrvAudit_TableItem{
}

/**
 * Module Manager Audit table list model.
 *
 * @package    Module_ModManager
 * @subpackage Model
 * @resource   mod_manager_audit/table/model/list <code>AMI::getResourceModel('mod_manager_audit/table')->getList()</code>
 * @amidev
 */
class ModManagerAudit_TableList extends SrvAudit_TableList{
}
