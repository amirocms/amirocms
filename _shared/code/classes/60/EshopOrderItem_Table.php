<?php
/**
 * AmiClean/EshopOrder module configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Config_AmiClean_EshopOrder
 * @version   $Id: EshopOrderItem_Table.php 42102 2013-10-07 11:53:16Z Leontiev Anton $
 * @since     5.14.0
 */

/**
 * AmiClean/EshopOrder product table model.
 *
 * E-shop Order Item fields description:
 * - <b>id_order</b> - order id (from AmiClean_EshopOrder_Table) (int),
 * - <b>id_product</b> - product id (from AmiCatalog_Items_Table) (int),
 * - <b>price</b> - product price (double),
 * - <b>price_number</b> - price number (int),
 * - <b>qty</b> - product quantity (double),
 * - <b>id_prop</b> - product property id (int),
 * - <b>data</b> - product data struct (from AmiCatalog_Items_Table) (array)
 *
 * @package    Config_AmiClean_EshopOrder
 * @subpackage Model
 * @resource   eshop_order_item/table/model <code>AMI::getResourceModel('eshop_order_item/table')</code>
 * @since      5.14.0
 */
class EshopOrderItem_Table extends AMI_ModTable{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_es_order_items';

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     * @todo  Describe several fields
     */
    public function __construct(array $aAttributes = array()){
        parent::__construct($aAttributes);

        $aRemap = array('data' => 'ext_data');
        $this->addFieldsRemap($aRemap);
    }
}

/**
 * AmiClean/EshopOrder product table item model.
 *
 * @package    Config_AmiClean_EshopOrder
 * @subpackage Model
 * @resource   eshop_order_item/table/model/item <code>AMI::getResourceModel('eshop_order_item/table')->getItem()</code>
 * @since      5.14.0
 */
class EshopOrderItem_TableItem extends AMI_ModTableItem{
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
                'id_order'   => array('filled'),
                'id_product' => array('filled'),
                'price'      => array('filled'),
                'qty'        => array('filled'),
                'data'       => array('filled')
            )
        );
        $this->setFieldCallback('data', array($this, 'fcbSerialized'));
    }
}

/**
 * AmiClean/EshopOrder product table list model.
 *
 * @package    Config_AmiClean_EshopOrder
 * @subpackage Model
 * @resource   eshop_order_item/table/model/list <code>AMI::getResourceModel('eshop_order_item/table')->getList()</code>
 * @since      5.14.0
 */
class EshopOrderItem_TableList extends AMI_ModTableList{
}
