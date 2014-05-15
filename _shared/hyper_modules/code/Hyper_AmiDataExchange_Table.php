<?php
/**
 * AmiDataExchange hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiDataExchange
 * @version   $Id: Hyper_AmiDataExchange_Table.php 42102 2013-10-07 11:53:16Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiDataExchange hypermodule table model.
 *
 * @package    Hyper_AmiDataExchange
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDataExchange_Table extends AMI_ModTable{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = '';

    /**
     * Returns table fields structure.
     *
     * @return array
     */
    public function getTableFieldsData(){
        return array();
    }
}

/**
 * AmiDataExchange hypermodule table item model.
 *
 * @package    Hyper_AmiDataExchange
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDataExchange_TableItem extends AMI_Module_TableItem{
}

/**
 * AmiDataExchange hypermodule table list model.
 *
 * @package    Hyper_AmiDataExchange
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDataExchange_TableList extends AMI_ModTableList{
}
