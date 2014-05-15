<?php
/**
 * AmiMultifeeds/Articles configuration instance.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module_##modId##
 * @version   $Id: --modId--_Table.php 45820 2013-12-23 12:02:21Z Leontiev Anton $
 * @since     5.14.4
 */

/**
 * ##modId## module table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * ##modId## fields description:
 * - <b>author</b> - article author (string),
 * - <b>source</b> - article source (string).
 *
 * @package    Module_##modId##
 * @subpackage Model
 * @since      5.14.4
 */
class ##modId##_Table extends AmiMultifeeds_Articles_Table{
}

/**
 * ##modId## module table item model.
 *
 * @package    Module_##modId##
 * @subpackage Model
 * @since      5.14.4
 */
class ##modId##_TableItem extends AmiMultifeeds_Articles_TableItem{
}

/**
 * ##modId## module table list model.
 *
 * @package    Module_##modId##
 * @subpackage Model
 * @since      5.14.4
 */
class ##modId##_TableList extends AmiMultifeeds_Articles_TableList{
}
