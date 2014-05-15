<?php
/**
 * Base hypermodule table models.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiClean
 * @version   $Id: Hyper_AmiClean_Table.php 45064 2013-12-05 09:27:10Z Leontiev Anton $
 * @since     6.0.2
 */

/**
 * Base hypermodule table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * Articles fields description:
 * - <b>author</b> - article author (string),
 * - <b>source</b> - article source (string).
 *
 * @package    Hyper_AmiClean
 * @subpackage Model
 * @since      6.0.2
 */
class Hyper_AmiClean_Table extends AMI_ModTable{
}

/**
 * Base hypermodule table item model.
 *
 * @package    Hyper_AmiClean
 * @subpackage Model
 * @since      6.0.2
 */
class Hyper_AmiClean_TableItem extends AMI_ModTableItem{
}

/**
 * Base hypermodule table list model.
 *
 * @package    Hyper_AmiClean
 * @subpackage Model
 * @since      6.0.2
 */
class Hyper_AmiClean_TableList extends AMI_ModTableList{
}
