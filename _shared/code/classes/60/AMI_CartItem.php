<?php
/**
 * E-shop cart item.
 *
 * Allows to get e-shop cart item info.
 *
 * Example:
 * <code>
 * $AMI_ENV_SETTINGS = array('mode' => 'full');
 * require 'ami_env.php';
 *
 * $oEshopCart = AMI::getResource('eshop/cart');
 *
 * // Get cart total amount
 * $aTotal = $oEshopCart->getTotal();
 *
 * // Get array of cart items objects
 * $aCartItems = $oEshopCart->getItems();
 *
 * foreach($aCartItems as $oCartItem){
 *     // Get cart item price info
 *     $oCartItem->getPriceInfo();
 *     // Get cart item tax values
 *     $oCartItem->getTax();
 *     // Get cart item discount values
 *     $oCartItem->getDiscount();
 *     // Get cart item shipping values
 *     $oCartItem->getShipping();
 * }
 * </code>
 *
 * @package    Module_Catalog
 * @subpackage Controller
 * @resource   eshop <code>AMI::getSingleton('eshop/cart_item')</code>
 * @since      6.0.2
 * @version    $Id: AMI_CartItem.php 44504 2013-11-27 10:21:28Z Leontiev Anton $
 */
class AMI_CartItem{
    /**
     * AmiCatalog table item
     *
     * @var AMI_ModTableItem
     */
    protected $oItem;

    /**
     * Product id
     *
     * @var int
     */
    protected $itemId;

    /**
     * Quantity of a product
     *
     * @var float
     */
    protected $qty = 1;

    /**
     * Price number
     *
     * @var int
     */
    protected $priceNum = 0;

    /**
     * Property id
     *
     * @var int
     */
    protected $propId = 0;

    /**
     * Array of cart item prop info
     *
     * @var array
     */
    protected $aPropInfo = array();

    /**
     * Array of cart item price info
     *
     * @var array
     */
    protected $aPriceInfo = array();

    /**
     * Array of cart item tax info
     *
     * @var array
     */
    protected $aTax = array();

    /**
     * Array of cart item discount info
     *
     * @var array
     */
    protected $aDiscount = array();

    /**
     * Array of cart item shipping info
     *
     * @var array
     */
    protected $aShipping = array();

    /**
     * Constructor.
     *
     * @param int   $itemId    Product id
     * @param float $qty       Product quantity
     * @param int   $priceNum  Price number
     * @param int   $propId    Prop id
     */
    public function __construct($itemId, $qty = 1, $priceNum = 0, $propId = 0){
        $this->itemId = (int)$itemId;
        $this->qty = (float)$qty;
        $this->propId = (int)$propId;
        $this->priceNum = ($this->propId > 0) ? null : (int)$priceNum;
    }

    /**
     * Returns AmiCatalog table item.
     *
     * @return AMI_ModTableItem
     */
    public function getItem(){
        if(!is_object($this->oItem) || empty($this->oItem)){
            $this->oItem = AMI::getResourceModel('eshop_item/table')->find($this->itemId, array('*'));
        }
        return $this->oItem;
    }

    /**
     * Returns product id.
     *
     * @return int
     */
    public function getItemId(){
        return $this->itemId;
    }

    /**
     * Set product id.
     *
     * @param  int $itemId  Product id
     * @return AMI_CartItem
     * @amidev
     */
    public function setItemId($itemId){
        $this->itemId = $itemId;
        return $this;
    }

    /**
     * Returns quantity of a product.
     *
     * @return float
     */
    public function getQty(){
        return $this->qty;
    }

    /**
     * Set quantity of a product.
     *
     * @param  float $qty  Quantity of a product
     * @return AMI_CartItem
     * @amidev
     */
    public function setQty($qty){
        $this->qty = $qty;
        return $this;
    }

    /**
     * Returns price number.
     *
     * @return int
     */
    public function getPriceNum(){
        return $this->priceNum;
    }

    /**
     * Set price number.
     *
     * @param  int $priceNum  Price number
     * @return AMI_CartItem
     * @amidev
     */
    public function setPriceNum($priceNum){
        $this->priceNum = $priceNum;
        return $this;
    }

    /**
     * Returns property id.
     *
     * @return int
     */
    public function getPropId(){
        return $this->propId;
    }

    /**
     * Set property id.
     *
     * @param  int $propId  Property id
     * @return AMI_CartItem
     * @amidev
     */
    public function setPropId($propId){
        $this->propId = $propId;
        return $this;
    }

    /**
     * Returns cart item prop info.
     *
     * @return array
     */
    public function getPropInfo(){
        return $this->aPropInfo;
    }

    /**
     * Set cart item prop info.
     *
     * @param  array $aPropInfo  Prop info
     * @return AMI_CartItem
     * @amidev
     */
    public function setPropInfo(array $aPropInfo = array()){
        $this->aPropInfo = $aPropInfo;
        return $this;
    }

    /**
     * Returns cart item price info.
     *
     * @return array
     */
    public function getPriceInfo(){
        return $this->aPriceInfo;
    }

    /**
     * Set cart item price info.
     *
     * @param  array $aPriceInfo  Price info
     * @return AMI_CartItem
     * @amidev
     */
    public function setPriceInfo(array $aPriceInfo = array()){
        $this->aPriceInfo = $aPriceInfo;
        return $this;
    }

    /**
     * Returns cart item tax info.
     *
     * @return array
     */
    public function getTax(){
        return $this->aTax;
    }

    /**
     * Set cart item tax info.
     *
     * @param  array $aTax  Tax info
     * @return AMI_CartItem
     * @amidev
     */
    public function setTax(array $aTax = array()){
        $this->aTax = $aTax;
        return $this;
    }

    /**
     * Returns cart item discount info.
     *
     * @return array
     */
    public function getDiscount(){
        return $this->aDiscount;
    }

    /**
     * Set cart item discount info.
     *
     * @param  array $aDiscount  Discount info
     * @return AMI_CartItem
     * @amidev
     */
    public function setDiscount(array $aDiscount = array()){
        $this->aDiscount = $aDiscount;
        return $this;
    }

    /**
     * Returns cart item shipping info.
     *
     * @return array
     */
    public function getShipping(){
        return $this->aShipping;
    }

    /**
     * Set cart item shipping info.
     *
     * @param  array $aShipping  Shipping info
     * @return AMI_CartItem
     * @amidev
     */
    public function setShipping(array $aShipping = array()){
        $this->aShipping = $aShipping;
        return $this;
    }
}
