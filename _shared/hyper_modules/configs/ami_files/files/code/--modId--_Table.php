<?php
/**
 * AmiFiles/Files configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_##modId##
 * @version   $Id: --modId--_Table.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.14.4
 */

/**
 * AmiFiles/Files configuration table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Config_##modId##
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      5.14.4
 */
class ##modId##_Table extends Hyper_AmiFiles_Table{
}

/**
 * AmiFiles/Files configuration table item model.
 *
 * @package    Config_##modId##
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      5.14.4
 */
class ##modId##_TableItem extends Hyper_AmiFiles_TableItem{
}

/**
 * AmiFiles/Files configuration table list model.
 *
 * @package    Config_##modId##
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      5.14.4
 */
class ##modId##_TableList extends Hyper_AmiFiles_TableList{
}
