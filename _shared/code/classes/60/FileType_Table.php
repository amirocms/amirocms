<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Config_AmiFiles_Files
 * @version   $Id: FileType_Table.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * File type table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Config_AmiFiles_Files
 * @subpackage Model
 * @resource   file_type/table/model <code>AMI::getResourceModel('file_type/table')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class FileType_Table extends AMI_ModTable{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_ftypes';
}

/**
 * File type table item model.
 *
 * @package    Config_AmiFiles_Files
 * @subpackage Model
 * @resource   file_type/table/model/item <code>AMI::getResourceModel('file_type/table')->getItem()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class FileType_TableItem extends AMI_ModTableItem{
}

/**
 * File type table list model.
 *
 * @package    Config_AmiFiles_Files
 * @subpackage Model
 * @resource   file_type/table/model/list <code>AMI::getResourceModel('file_type/table')->getList()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class FileType_TableList extends AMI_ModTableList{
}
