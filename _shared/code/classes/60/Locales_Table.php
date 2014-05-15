<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Module_Locales
 * @version   $Id: Locales_Table.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Locales table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Module_Locales
 * @subpackage Model
 * @resource   locales/table/model <code>AMI::getResourceModel('locales/table')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class Locales_Table extends AMI_ModTable{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_locales';
}

/**
 * Locales table item model.
 *
 * @package    Module_Locales
 * @subpackage Model
 * @resource   locales/table/model/item <code>AMI::getResourceModel('locales/table')->getItem()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class Locales_TableItem extends AMI_ModTableItem{
}

/**
 * Locales table list model.
 *
 * @package    Module_Locales
 * @subpackage Model
 * @resource   locales/table/model/list <code>AMI::getResourceModel('locales/table')->getList()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class Locales_TableList extends AMI_ModTableList{
}
