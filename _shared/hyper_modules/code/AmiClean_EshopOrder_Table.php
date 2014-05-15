<?php
/**
 * AmiClean/EshopOrder module configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Config_AmiClean_EshopOrder
 * @version   $Id: AmiClean_EshopOrder_Table.php 49097 2014-03-26 12:02:06Z Leontiev Anton $
 * @since     5.12.4
 */

/**
 * AmiClean/EshopOrder module table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * E-shop Order fields description:
 * - <b>status</b> - order status (string),
 * - <b>total</b> - order total (double),
 * - <b>tax</b> - tax value (double),
 * - <b>shipping</b> - shipping value (double),
 * - <b>id_user</b> - user identifier (iont),
 * - <b>login</b> - user login (string),
 * - <b>firstname</b> - user firstname (string),
 * - <b>lastname</b> - user lastname (string),
 * - <b>email</b> - email (string),
 * - <b>comments</b> - order comments (string),
 * - <b>status_history</b> - array of statuses having unix time as keys and data structs as values (array),
 * - <b>custom_info</b> - order custom info (array),
 * - <b>system_info</b> - order system info (array),
 * - <b>data</b> - order data (array)
 *
 * @package    Config_AmiClean_EshopOrder
 * @subpackage Model
 * @since      5.12.4
 * @resource   eshop_order/table/model <code>AMI::getResourceModel('eshop_order/table')</code>
 */
class AmiClean_EshopOrder_Table extends Hyper_AmiClean_Table{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_es_orders';

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model
     * @todo  Describe several fields
     */
    public function __construct(array $aAttributes = array()){
        $this->setDependence('users', 'u', 'u.id=i.id_member', 'LEFT OUTER JOIN');
        $this->setActiveDependence('u');
        $this->addSystemFields(
            array(
                'statuses_history', 'ext_data', 'custinfo', 'sysinfo',
            )
        );

        parent::__construct($aAttributes);

        $aRemap = array(
            /*
            'id'           => 'id',
            'lang'         => 'lang',
            'status'       => 'status',
            */
            'date_created' => 'order_date',
            /*
            'total'        => 'total',
            'tax'          => 'tax',
            'shipping'     => 'shipping',
            */
            'id_user'      => 'id_member',
            'login'        => 'username',

            'status_history' => 'statuses_history',
            'date_created'   => 'order_date',
            'date_modified'  => 'modified_date',
            'date_approved'  => 'approve_date',
            'date_exported'  => 'export_date',

            'custom_info'    => 'custinfo',
            'system_info'    => 'sysinfo',
            'data'           => 'ext_data',

            /*
            ,
            'firstname'    => 'firstname',
            'lastname'     => 'lastname',
            'email'        => 'email',
            'comments'     => 'comments'
            */
        );
        $this->addFieldsRemap($aRemap);
    }

    /**
     * Override table name setter 'cause parent method setting up table using $modId (Since our $modId equals 'eshop_order' we gettin' 'cms_eshop_order' instead 'cms_es_orders').
     *
     * @param string $tableName  Table name
     * @return void
     * @todo check for $this->tableName in parent method
     * @amidev
     */
    public function setTableName($tableName){
        $this->tableName = 'cms_es_orders';
    }
}

/**
 * AmiClean/EshopOrder module table item model.
 *
 * @package    Config_AmiClean_EshopOrder
 * @subpackage Model
 * @resource   eshop_order/table/model/item <code>AMI::getResourceModel('eshop_order/table')->getItem()</code>
 */
class AmiClean_EshopOrder_TableItem extends Hyper_AmiClean_TableItem{
    /**
     * Initializing table item data.
     *
     * @param AMI_ModTable $oTable  Module table model
     * @param DB_Query     $oQuery  Required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery = null){
        parent::__construct($oTable, $oQuery);

        $this->oTable->addValidators(
            array(
                'lang' => array('filled'),
                'name' => array('filled')
            )
        );
        foreach(array('name', 'adm_comments') as $field){
            $this->setFieldCallback($field, array($this, 'fcbHTMLEntities'));
        }
        foreach(array('status_history', 'custom_info', 'system_info', 'data') as $field){
            $this->setFieldCallback($field, array($this, 'fcbSerialized'));
        }
    }

    /**
     * Saves current item data.
     *
     * @return bool
     */
    public function save(){
        global $AMI_ENV_SETTINGS, $Core;

        if(
            (isset($AMI_ENV_SETTINGS) && $AMI_ENV_SETTINGS['mode'] == 'full') ||
            (isset($Core) && is_object($Core) && $Core instanceof CMS_Core)
        ){
            $this->bAllowSave = true;
        }

        return parent::save();
    }
}

/**
 * AmiClean/EshopOrder module table list model.
 *
 * @package    Config_AmiClean_EshopOrder
 * @subpackage Model
 * @resource   eshop_order/table/model/list <code>AMI::getResourceModel('eshop_order/table')->getList()</code>
 */
class AmiClean_EshopOrder_TableList extends Hyper_AmiClean_TableList{
    /**
     * Initializing table list data.
     *
     * @param AMI_ModTable $oTable  Table model
     * @param DB_Query     $oQuery  DB_Query object, required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery){
        parent::__construct($oTable, $oQuery);
        $this->addExpressionColumn('amount', '(i.tax + i.shipping + i.total)');
        $this->addExpressionColumn('fullname', DB_Query::getSnippet('CONCAT(u.firstname, %s, u.lastname)')->q(' '), 'u');
    }
}
