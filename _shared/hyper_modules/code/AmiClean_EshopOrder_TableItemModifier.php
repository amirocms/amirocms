<?php
/**
 * AmiClean/EshopOrder module configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Config_AmiClean_EshopOrder
 * @version   $Id: AmiClean_EshopOrder_TableItemModifier.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiClean/EshopOrder module table item model modifier.
 *
 * @package    Config_AmiClean_EshopOrder
 * @subpackage Model
 * @resource   eshop_order/table/model/item/modifier <code>AMI::getResourceModel('eshop_order/table')->getItem()->getModifier()</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_EshopOrder_TableItemModifier extends Hyper_AmiClean_TableItemModifier{
    /**
     * Eshop order item table resource id
     *
     * @var string
     */
    protected $productTableResId = 'eshop_order_item/table';

    /**
     * Deletes item from table.
     *
     * @param  mixed $id  Primary key value of item
     * @return bool       True if any record were deleted
     */
    public function delete($id = null){
        $passedId = (int)(is_null($id) ? $this->oTableItem->getId() : $id);
        $result = parent::delete($id);
        if($result){
            $sql =
                "DELETE FROM `" . AMI::getResourceModel($this->productTableResId)->getTableName() . "` " .
                    "WHERE `id_order` = " . $passedId;
            AMI::getSingleton('db')->query($sql);
        }
        return $result;
    }
}
