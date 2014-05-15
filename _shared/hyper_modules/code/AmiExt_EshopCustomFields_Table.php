<?php
/**
 * AmiExt/EshopCustomFields configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiExt_EshopCustomFields
 * @version   $Id: AmiExt_EshopCustomFields_Table.php 42102 2013-10-07 11:53:16Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/EshopCustomFields configuration table model.
 *
 * @package    Config_AmiExt_EshopCustomFields
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_EshopCustomFields_Table extends AMI_ModTable{
    /**
     * Set up table to cms_es_custom_types
     *
     * @var string
     */
    protected $tableName = 'cms_es_custom_types';

    /**
     * Overload table name setter.
     *
     * @param string $tableName  Table name
     * @return void
     */
    public function setTableName($tableName){
    }
}

/**
 * AmiExt/EshopCustomFields configuration table list model.
 *
 * @package    Config_AmiExt_EshopCustomFields
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_EshopCustomFields_TableList extends AMI_ModTableList{
}

/**
 * AmiExt/EshopCustomFields configuration table item model.
 *
 * @package    Config_AmiExt_EshopCustomFields
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_EshopCustomFields_TableItem extends AMI_ModTableItem{
}
