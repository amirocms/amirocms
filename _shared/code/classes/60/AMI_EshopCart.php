<?php
/**
 * @version $Id: AMI_EshopCart.php 48118 2014-02-21 10:37:35Z Kolesnikov Artem $
 */

require_once $GLOBALS['FUNC_INCLUDES_PATH'] . 'func_eshop.php';

/**
 * E-shop cart business layer.
 *
 * Allows to manage e-shop cart items.
 *
 * Example:
 * <code>
 * $AMI_ENV_SETTINGS = array('mode' => 'full', 'disable_cache' => true);
 * require 'ami_env.php';
 *
 * $oEshopCart = AMI::getResource('eshop/cart');
 *
 * // Add product to cart
 * $oEshopCart->addItem(209, 1, 0, 0);
 *
 * // Get cart total amount
 * $aTotal = $oEshopCart->getTotal();
 *
 * // Get array of cart items objects
 * $aCartItems = $oEshopCart->getItems();
 * </code>
 *
 * See {@link AMI_EshopCart::addItem()}, {@link AMI_EshopCart::getTotal()}, {@link AMI_EshopCart::getItems()}, {@link AMI_CartItem} for details.
 *
 * @package    Module_Catalog
 * @subpackage Controller
 * @resource   eshop <code>AMI::getSingleton('eshop/cart')</code>
 * @since      6.0.2
 */
class AMI_EshopCart{
    /**
     * Instance
     *
     * @var AMI_EshopCart
     */
    protected static $oInstance;

    /**
     * CMS cart object
     *
     * @var Cart
     */
    protected $oCart;

    /**
     * Array of cart items
     *
     * @var array
     */
    protected $aCartItems = array();

    /**
     * Array of cart amount values
     *
     * @var array
     */
    protected $aTotal = array();

    /**
     * Returns an instance of AMI_EshopCart.
     *
     * @param  array $aArgs  Constructor arguments
     * @return AMI_EshopCart
     * @amidev
     */
    public static function getInstance(array $aArgs = array()){
        if(is_null(self::$oInstance)){
            self::$oInstance = new AMI_EshopCart();
        }
        return self::$oInstance;
    }

    /**
     * Constructor.
     */
    public function __construct(){
        global $cms;

        if(!is_object($cms->Member->Cart)){
            $oSession = isset($GLOBALS['oSession']) && is_object($GLOBALS['oSession']) ? $GLOBALS['oSession'] : new CMS_Session($cms, AMI_Registry::get('lang_data'));
            CreateCart($cms, $oSession);
        }

        $this->oCart = $cms->Member->Cart;
        $this->setCurrency();

        $this->createCartItems();
    }

    /**
     * Set cart currency.
     *
     * @param  string $currency  Currency code
     * @return AMI_CartItem
     */
    public function setCurrency($currency = ''){
        global $cms;

        if(empty($currency)){
            $lang_data = AMI_Registry::get('lang_data');
            $mEshopCart = &$cms->Core->GetModule($cms->Eshop->ownerName.'_cart');
            $defCur = $mEshopCart->GetOption('default_currency');
            $this->oCart->currency = isset($defCur[$lang_data]) ? $defCur[$lang_data] : $defCur['en'];
        }else{
            $this->oCart->currency = $currency;
        }

        return $this;
    }

    /**
     * Create cart items.
     *
     * @return AMI_EshopCart
     * @amidev
     */
    protected function createCartItems(){
        global $cms;
        $this->aCartItems = array();
        $aItems = $this->oCart->getItems();

        if(!empty($aItems[$cms->Eshop->ownerName])){
            foreach($aItems[$cms->Eshop->ownerName] as $itemId => $aItemData){
                if(!(int)$itemId){
                    continue;
                }
                foreach($aItemData as $propId => $aPriceData){
                    foreach($aPriceData as $priceNum => $qty){
                        $oCartItem = new AMI_CartItem($itemId, $qty, $priceNum, $propId);
                        $this->aCartItems[] = $oCartItem;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Sets extra info array item.
     *
     * @param string $key   Extra info key
     * @param mixed $value  Extra info value
     * @return void
     * @amidev
     */
    public function setExtraInfo($key, $value){
        $this->oCart->extraInfo[$key] = $value;
        $this->updateCart();
    }

    /**
     * Returns extra information item.
     *
     * @param string $key  Extra info key
     * @return mixed
     * @amidev
     */
    public function getExtraInfo($key){
        return isset($this->oCart->extraInfo[$key]) ? $this->oCart->extraInfo[$key] : null;
    }
    
    /**
     * Update the cart.
     *
     * @return AMI_EshopCart
     * @amidev
     */
    protected function updateCart(){
        global $cms, $db;

        $this->oCart->cleanup();
        $this->oCart->cleanErrors();
        $this->oCart->recalcTotal($db, true);

        $oEshop = AMI::getSingleton('eshop');
        $oSession = isset($GLOBALS['oSession']) && is_object($GLOBALS['oSession']) ? $GLOBALS['oSession'] : new CMS_Session($cms, AMI_Registry::get('lang_data'));
        $oSession->addJsSessionCookie('eshop_cart_count', $this->oCart->itemcountTpl());
        $oSession->addJsSessionCookie('eshop_cart_total', $oEshop->formatMoney($this->oCart->total, $this->oCart->currency));
        $oSession->addJsSessionCookie('eshop_cart_total_plain', $this->oCart->total);

        setCartSessionVar(true, $this->oCart);
        $this->oCart->cms = $cms;

        return $this;
    }

    /**
     * Create AMI_CartItem object and adds it to cart.
     *
     * @param int   $itemId    Product id
     * @param float $qty       Product quantity
     * @param int   $priceNum  Price number
     * @param int   $propId    Prop id
     * @return AMI_EshopCart
     */
    public function addItem($itemId, $qty = 1, $priceNum = 0, $propId = 0){
        $this->updateCart();
        $itemId = (int)$itemId;
        $qty = (float)$qty;
        $priceNum = (int)$priceNum;
        $propId = (int)$propId;

        if($itemId > 0 && $qty > 0){
            // TODO: CMS cart has yet another parameter - $itemPrice. Used for items of eshop_account type.
            $this->oCart->add($itemId, $qty, $priceNum, $propId);
            $oCartItem = new AMI_CartItem($itemId, $qty, $priceNum, $propId);
            $this->aCartItems[] = $oCartItem;
        }else{
            trigger_error('Cart item was not added. Invalid item id or quantity parameters.', E_USER_WARNING);
        }

        $this->updateCart();
        return $this;
    }

    /**
     * Adds a cart item object.
     *
     * @param  AMI_CartItem $oCartItem  E-shop cart item
     * @return AMI_EshopCart
     */
    public function addCartItem(AMI_CartItem $oCartItem){
        $this->updateCart();
        $itemId = (int)$oCartItem->getItemId();
        $qty = (float)$oCartItem->getQty();
        $priceNum = (int)$oCartItem->getPriceNum();
        $propId = (int)$oCartItem->getPropId();

        if($itemId > 0 && $qty > 0){
            $this->oCart->add($itemId, $qty, $priceNum, $propId);
            $this->aCartItems[] = $oCartItem;
        }else{
            trigger_error('Cart item was not added. Invalid item id or quantity parameters.', E_USER_WARNING);
        }

        $this->updateCart();
        return $this;
    }

    /**
     * Returns a cart item.
     *
     * @param int $itemId    Product id
     * @param int $priceNum  Price number
     * @param int $propId    Prop id
     * @return mixed
     */
    public function getItem($itemId, $priceNum = 0, $propId = 0){
        foreach($this->aCartItems as $oCartItem){
            if(($itemId == $oCartItem->getItemId()) && ($propId > 0) && ($propId == $oCartItem->getPropId())){
                return $oCartItem;
            } else if (($itemId == $oCartItem->getItemId()) && ($propId <= 0) && ($priceNum >= 0) && ($priceNum == $oCartItem->getPriceNum())) {
                return $oCartItem;
            }
        }

        return null;
    }

    /**
     * Returns TRUE if the cart item exists.
     *
     * @param int $itemId    Product id
     * @param int $priceNum  Price number
     * @param int $propId    Prop id
     * @return bool
     */
    public function hasItem($itemId, $priceNum = 0, $propId = 0){
        $oCartItem = $this->getItem($itemId, $priceNum, $propId);
        return is_null($oCartItem) ? false : true;
    }

    /**
     * Deletes a cart item.
     *
     * @param int $itemId    Product id
     * @param int $priceNum  Price number
     * @param int $propId    Prop id
     * @return AMI_EshopCart
     */
    public function deleteItem($itemId, $priceNum = 0, $propId = 0){
        // $this->oCart->init();
        foreach($this->aCartItems as $ind => $oCartItem){
            if($itemId == $oCartItem->getItemId()){
                if(($propId > 0) && ($propId == $oCartItem->getPropId())){
                    unset($this->aCartItems[$ind]);
                    break;
                }elseif(($priceNum >= 0) && ($priceNum == $oCartItem->getPriceNum())){
                    unset($this->aCartItems[$ind]);
                    break;
                }
            }
        }

        $this->oCart->remove($itemId, $priceNum, $propId);

        /*
        foreach($this->aCartItems as $oCartItem){
            $this->oCart->add($oCartItem->getItemId(), $oCartItem->getQty(), $oCartItem->getPriceNum(), $oCartItem->getPropId());
        }
        */
        $this->updateCart();
        return $this;
    }

    /**
     * Returns a cart items.
     *
     * @param int $itemId    Product id
     * @param int $priceNum  Price number
     * @param int $propId    Prop id
     * @return array
     */
    public function getItems($itemId = null, $priceNum = null, $propId = null){
        if(is_null($itemId)){
            return $this->aCartItems;
        }

        $aResultItems = array();
        foreach($this->aCartItems as $oCartItem){
            if($itemId == $oCartItem->getItemId()){
                if(!is_null($propId) && ($propId == $oCartItem->getPropId())){
                    $aResultItems[] = $oCartItem;
                }elseif(!is_null($priceNum) && ($priceNum == $oCartItem->getPriceNum())){
                    $aResultItems[] = $oCartItem;
                }elseif(is_null($propId) && is_null($priceNum)){
                    $aResultItems[] = $oCartItem;
                }
            }
        }
        return $aResultItems;
    }

    /**
     * Sets a cart items.
     *
     * @param array $aCartItems    Array of cart items
     * @return array
     */
    public function setItems(array $aCartItems){
        $this->aCartItems = $aCartItems;
        $this->oCart->init();
        foreach($this->aCartItems as $oCartItem){
            $this->oCart->add($oCartItem->getItemId(), $oCartItem->getQty(), $oCartItem->getPriceNum(), $oCartItem->getPropId());
        }
        $this->updateCart();
        return $this;
    }

    /**
     * Clears the cart, removes all items.
     *
     * @return AMI_EshopCart
     */
    public function clear(){
        $this->aCartItems = array();
        $this->oCart->init();
        $this->updateCart();
        return $this;
    }

    /**
     * Returns TRUE if the cart is empty.
     *
     * @return bool
     */
    public function isEmpty(){
        return ($this->getItemsQuantity() == 0);
    }

    /**
     * Returns the quantity of cart items.
     *
     * @return float
     */
    public function getItemsQuantity(){
        return $this->oCart->itemcount();
    }

    /**
     * Recalculate the cart total amount.
     *
     * @return array
     */
    public function recalcTotal(){
        global $db;

        $this->oCart->recalcTotal($db, true);
        $this->createCartItems();

        $aResultItems = array();
        $this->oCart->calcPayment($db, $aResultItems, true, false, '');

        $countItems = sizeof($aResultItems) - 1;
        if($countItems > 0){
            for($index = 0; $index < $countItems; $index++){
                $oCartItem = $this->getItem($aResultItems[$index]['id'], $aResultItems[$index]['price_number'], $aResultItems[$index]['id_prop']);
                if(!is_null($oCartItem)){
                    $aPriceInfo = array(
                        'price'          => $aResultItems[$index]['price'],
                        'cur_price'      => $aResultItems[$index]['cur_price'],
                        'cur_price_tax'  => $aResultItems[$index]['cur_price_tax'],
                        'order_price'    => $aResultItems[$index]['order_price'],
                        'original_price' => $aResultItems[$index]['original_price'],
                        'currency'       => $aResultItems[$index]['currency']
                    );
                    $oCartItem->setPriceInfo($aPriceInfo);
                    $oCartItem->setPropInfo($aResultItems[$index]['prop_info']);
                    $aTax = array(
                        'tax'            => $aResultItems[$index]['tax'],
                        'tax_item_value' => $aResultItems[$index]['tax_item_value'],
                        'tax_type'       => $aResultItems[$index]['tax_type'],
                        'tax_item'       => $aResultItems[$index]['tax_item']
                    );
                    $oCartItem->setTax($aTax);
                    $aDiscount = array(
                        'absolute_discount'   => $aResultItems[$index]['absolute_discount'],
                        'percentage_discount' => $aResultItems[$index]['percentage_discount']
                    );
                    $oCartItem->setDiscount($aDiscount);
                    $aShipping = array(
                        'shipping'      => $aResultItems[$index]['shipping'],
                        'shipping_type' => $aResultItems[$index]['shipping_type'],
                        'shipping_item' => $aResultItems[$index]['shipping_item']
                    );
                    $oCartItem->setShipping($aShipping);
                }
            }
        }

        return $aResultItems;
    }

    /**
     * Calculate and returns the cart total amount values.
     *
     * @return array
     */
    public function getTotal(){
        $aResult = $this->recalcTotal();
        if(sizeof($aResult) > 0){
            $this->aTotal = array_pop($aResult);
        }
        return $this->aTotal;
    }

    /**
     * Join guest and user carts. Returns the count of items in the cart.
     *
     * @param  Cart $oUserCart  E-shop cart object stored in session
     * @return int
     * @amidev
     */
    public function joinCarts($oUserCart){
        global $cms;

        if(!$cms->Core->IsInstalled('eshop_cart')){
            return false;
        }

        $mEshopCart = &$cms->Core->GetModule($cms->Eshop->ownerName.'_cart');
        $ruleForJoining = $mEshopCart->GetOption('rule_for_carts_joining');

        // restore user session cart if guest cart is empty
        if($this->isEmpty()){
            $ruleForJoining = 'user';
        }

        if(($ruleForJoining == 'user') || ($ruleForJoining == 'join')){
            // array of items for the joined cart
            $aCartItems = array();

            // check session cart items
            $aItems = $oUserCart->getItems();
            if(!empty($aItems[$cms->Eshop->ownerName])){
                foreach($aItems[$cms->Eshop->ownerName] as $itemId => $aItemData){
                    if(!(int)$itemId){
                        continue;
                    }
                    foreach($aItemData as $propId => $aPriceData){
                        foreach($aPriceData as $priceNum => $qty){
                            if($ruleForJoining == 'user'){
                                $oCartItem = new AMI_CartItem($itemId, $qty, $priceNum, $propId);
                                $aCartItems[] = $oCartItem;
                            }else{
                                if(!$this->hasItem($itemId, $priceNum, $propId)){
                                    // guest cart does not have this item
                                    $oCartItem = new AMI_CartItem($itemId, $qty, $priceNum, $propId);
                                    $aCartItems[] = $oCartItem;
                                }else{
                                    // guest and user carts has this item
                                    $oGuestCartItem = $this->getItem($itemId, $priceNum, $propId);
                                    $guestQty = $oGuestCartItem->getQty();
                                    // item with max quantity will be added
                                    $oCartItem = new AMI_CartItem($itemId, ($guestQty > $qty) ? $guestQty : $qty, $priceNum, $propId);
                                    $aCartItems[] = $oCartItem;
                                    // delete guest item
                                    $this->deleteItem($itemId, $priceNum, $propId);
                                }
                            }
                        }
                    }
                }
            }

            // add items that in guest cart only
            if($ruleForJoining == 'join'){
                $aGuestCartItems = $this->getItems();
                foreach($aGuestCartItems as $oGuestCartItem){
                    $oCartItem = new AMI_CartItem($oGuestCartItem->getItemId(), $oGuestCartItem->getQty(), $oGuestCartItem->getPriceNum(), $oGuestCartItem->getPropId());
                    $aCartItems[] = $oCartItem;
                }
            }

            // set items for joined cart
            if(sizeof($aCartItems) > 0){
                $this->clear();
                $this->setItems($aCartItems);
            }
        }else{
            // keep guest cart
        }

        return $this->getItemsQuantity();
    }
}
