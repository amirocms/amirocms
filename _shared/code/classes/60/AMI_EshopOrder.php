<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module_Catalog
 * @version   $Id: AMI_EshopOrder.php 48662 2014-03-13 12:21:58Z Kolesnikov Artem $
 * @since     5.14.8
 */

/**
 * E-shop order business layer.
 *
 * @package    Module_Catalog
 * @subpackage Controller
 * @resource   eshop_order <code>AMI::getSingleton('eshop_order')</code>
 * @since      5.14.8
 */
class AMI_EshopOrder{
    /**#@+
     * Result formats.
     *
     * @amidev Temporary
     */

    /**
     * As is
     */
    const FMT_NONE = 0;

    /**
     * Numeric
     */
    const FMT_NUMERIC = 1;

    /**
     * Money
     */
    const FMT_MONEY = 2;

    /**#@-*/

    /**
     * Section/owner
     *
     * @var string
     */
    private $section = 'eshop';

    /**
     * Database tables prefix
     *
     * @var string
     */
    private $dbTablePrefix = 'cms_es_';

    /**
     * Instance
     *
     * @var AMI_Eshop
     */
    private static $oInstance;

    /**
     * Module's default currency code
     *
     * @var string
     */
    private $defaultCurrency;

    /**
     * Orders products cache
     *
     * @var array
     */
    private $aProductCache = array();

    /**
     * Returns an instance of AMI_Core.
     *
     * @param  array $aArgs  Constructor arguments
     * @return AMI_Core
     * @amidev
     */
    public static function getInstance(array $aArgs = array()){
        if(is_null(self::$oInstance)){
            self::$oInstance = new AMI_EshopOrder();
        }
        return self::$oInstance;
    }

    /**
     * Class constructor.
     *
     * @amidev
     */
    protected function  __construct(){
        $this->defaultCurrency = AMI::getOption($this->section . '_order', 'default_currency');
        $this->defaultCurrency = $this->defaultCurrency[AMI_Registry::get('lang_data')];
    }

    /**
     * Get order amount (total).
     *
     * @param  AMI_ModTableItem $oOrder  Order item
     * @param  string           $key     What to get: items_total, items_tax, shipping_tax, excise_tax, shipping, amount
     * @param  int              $format  Format to return:
     *                                       - AMI_EshopOrder::FMT_NONE,
     *                                       - AMI_EshopOrder::FMT_NUMERIC,
     *                                       - AMI_EshopOrder::FMT_MONEY
     * @param  bool             $force   Force recalculation
     * @return mixed
     * @amidev
     */
    public function getTotal(AMI_ModTableItem $oOrder, $key, $format = self::FMT_NONE, $force = false){
        $aOrderData = AMI_Registry::get($this->section . '_order_' . $oOrder->id, FALSE);
        if(($aOrderData === FALSE) || $force){
            $aOrderData = $this->getOrderData($oOrder);
            AMI_Registry::set($this->section . '_order_' . $oOrder->id, $aOrderData);
        }
        $amount = $aOrderData[$key];

        return $this->format($amount, $format);
    }


    /**
     * Format a value.
     *
     * @param float $value  Value to format
     * @param int $format   Format to return: AMI_EshopOrder::FMT_NONE, AMI_EshopOrder::FMT_NUMERIC, AMI_EshopOrder::FMT_MONEY
     * @return mixed
     * @amidev
     */
    public function format($value, $format){
        /**
         * @var AMI_Eshop
         */
        $oEshop = AMI::getSingleton('eshop');

        $result = $value;
        switch($format){
            case self::FMT_NUMERIC:
                $result = $oEshop->formatNumber($value, TRUE, TRUE);
                break;
            case self::FMT_MONEY:
                $result = $oEshop->formatMoney($value, $this->defaultCurrency);
                break;
        }
        return $result;
    }

    /**
     * Get order data details.
     *
     * @param Ami_ModTableItem $oOrder  Order item
     * @return array
     * @amidev
     */
    public function getOrderData(Ami_ModTableItem $oOrder){
        $aResult = array(
            'items_total'       => 0, // Items total cost (no taxes)
            'items_tax'         => 0, // Items tax
            'shipping_tax'      => 0, // Shipping tax
            'excise_tax'        => 0, // Excise tax
            'payment_tax'       => 0, // Payment tax
            'applied_discount'  => 0, // Applied discount
            'shipping'          => 0, // Shipping cost
            'amount'            => 0  // Total order amount (items + taxes + shipping)
        );

        /**
         * @var AMI_Eshop
         */
        $oEshop = AMI::getSingleton('eshop');
        $aOrderItems = $this->getItems(array($oOrder));
        $amount = 0;
        $shippingTax = 0;
        $exciseTax = 0;
        if(isset($aOrderItems[$oOrder->id])){
            foreach($aOrderItems[$oOrder->id] as $oOrderItem){
                $code = $oOrderItem->data['currency']['code'];
                $itemPrice = $oEshop->convertCurrency($oOrderItem->data['item_info']['cur_price'], $code, $this->defaultCurrency);
                $itemPrice = $oEshop->formatNumber($itemPrice, TRUE, TRUE);
                $aResult['items_total'] += $itemPrice * $oOrderItem->qty;
                $taxItem = $oEshop->convertCurrency($oOrderItem->data['item_info']['tax_item'], $code, $this->defaultCurrency);
                $taxItem = $oEshop->formatNumber($taxItem, TRUE, TRUE);
                $aResult['items_tax'] += $taxItem * $oOrderItem->qty;
                if(isset($oOrderItem->data['absolute_discount'])){
                    $discount = $oOrderItem->data['absolute_discount'];
                    $discount = $oEshop->convertCurrency($discount, $code, $this->defaultCurrency);
                    $discount = $oEshop->formatNumber($discount, TRUE, TRUE);
                    $aResult['applied_discount'] += $discount;
                }
                $amount += ($itemPrice + $taxItem) * $oOrderItem->qty;
            }
            $shippingTax += $oEshop->formatNumber($oEshop->convertCurrency($oOrder->shipping_tax, $code, $this->defaultCurrency), TRUE, TRUE);
            $exciseTax += $oEshop->formatNumber($oEshop->convertCurrency($oOrder->excise_tax, $code, $this->defaultCurrency), TRUE, TRUE);
            $amount += $shippingTax;
            $amount += $exciseTax;
        }
        $oEshop->storeCurrencyData();
        $oEshop->setCurrencyData(!empty($oOrder->data['currency']) ? $oOrder->data['currency'] : array(), $oOrder->data['base_currency']['code']);
        $shipping = $oEshop->convertCurrency($oOrder->shipping, $oEshop->getBaseCurrency(), $this->defaultCurrency);
        $amount += $shipping;
        $aResult['shipping'] = $shipping;
        $aResult['amount'] = $amount;

        // Payment tax
        $aSysInfo = $oOrder->system_info;
        if(is_array($aSysInfo)){
            if(isset($aSysInfo['fee_percent'])){
                $paymentTax = $amount * $aSysInfo['fee_percent'] / 100;
                $feeConst = $oEshop->convertCurrency($aSysInfo['fee_const'], $aSysInfo['fee_curr'], $this->defaultCurrency);
                $paymentTax += $feeConst;
                $aResult['payment_tax'] = $oEshop->formatNumber($paymentTax, TRUE, TRUE);
            }
        }
        $oEshop->restoreCurrencyData();

        return $aResult;
    }

    /**
     * Get order items array for a set of orders.
     *
     * @param  mixed $aOrders  Array of EshopOrder_TableItem (or List iterator)
     * @return array
     * @amidev
     */
    public function getItems($aOrders){
        $aOrderItems = array();
        $aOrderIds = array();
        foreach($aOrders as $oOrder){
            if(isset($this->aProductCache[$oOrder->id])){
                $aOrderItems[$oOrder->id] = $this->aProductCache[$oOrder->id];
            }else{
                $aOrderIds[] = $oOrder->id;
            }
        }
        if(sizeof($aOrderIds)){
            /**
             * @var EshopOrderItem_TableList
             */
            $oList = AMI::getResourceModel('eshop_order_item/table')->getList();
            $oList
                ->addColumns(array('id', 'id_order', 'id_product', 'qty', 'data', 'price', 'owner_name', 'price_number'))
                ->addWhereDef(DB_Query::getSnippet('AND `id_order` IN (%s)')->implode($aOrderIds, FALSE))
                ->load();
            foreach($oList as $oItem){
                if(empty($aOrderItems[$oItem->id_order])){
                    $aOrderItems[$oItem->id_order] = array();
                }
                $aOrderItems[$oItem->id_order][] = $oItem;
            }
            foreach($aOrderIds as $orderId){
                $this->aProductCache[$orderId] = $aOrderItems[$orderId];
            }
        }
        return $aOrderItems;
    }

    /**
     * Get formatted adress.
     *
     * @param  array $aCustInfo  Customer info array
     * @return string
     * @amidev
     */
    public function getCustomerAddress(array $aCustInfo){
        $customerAddress = '';
        $delimiter = '';
        if(isset($aCustInfo['city']) && $aCustInfo['city'] != ''){
            $customerAddress .= $aCustInfo['city'];
            $delimiter = ', ';
        }
        if(isset($aCustInfo['street']) && $aCustInfo['street'] != ''){
            $customerAddress .= $delimiter . $aCustInfo['street'];
            $delimiter = ', ';
        }
        if(isset($aCustInfo['house']) && $aCustInfo['house'] != ''){
            $customerAddress .= $delimiter . $aCustInfo['house'];
            $delimiter = ', ';
            if(isset($aCustInfo['building']) && $aCustInfo['building'] != ''){
                $customerAddress .= '/' . $aCustInfo['building'];
            }
            if(isset($aCustInfo['app']) && $aCustInfo['app'] != ''){
                $customerAddress .= $delimiter . $aCustInfo['app'];
                $delimiter = ', ';
            }
        }
        return $customerAddress;
    }

    /**
     * Creates order.
     *
     * @param array $aProductItems     Array of items array(array(itemId, qty, [priceN], [propId], [fixedPrice]), ...)
     * @param array $aCustomerInfo     Customer info
     * @param array $aShippingInfo     Shipping info
     * @param array $aPaymentInfo      Payment info
     * @param array $aOrderData        Additional order data
     * @param bool  $skipInvalidItems  Create order even if invalid poduct present
     * @return bool
     * @amidev
     */
    public function createOrder($aProductItems, $aCustomerInfo = array(), $aShippingInfo = array(), $aPaymentInfo = array(), $aOrderData = array(), $skipInvalidItems = TRUE){
        global $frn, $oSession, $db, $oOrder;

        $orderId = FALSE;

        AMI::setOption($this->section . '_item', 'apply_discount_to_fields', array());
        AMI::setOption($this->section . '_discounts', 'products_discounts_syncopation', 'products_discount');

        $frn->Eshop = NULL;
        $frn->WorkingModuleName = '';
        CreateEshop($frn, TRUE);
        $frn->Eshop->initByOwnerName($this->section);
        CreateCart($frn, $oSession);
        $frn->Member->Cart->init();
        $frn->Member->Cart->ownerName = $this->section;

        $defaultPriceNumber = 0;

        $totalQty = 0;
        foreach($aProductItems as $aProduct){
            $productId = $aProduct[0];
            $qty = $aProduct[1];
            $priceNumber = isset($aProduct[2]) ? (int)$aProduct[2] : $defaultPriceNumber;
            $propId = isset($aProduct[3]) ? (int)$aProduct[3] : 0;
            $fixedPrice = isset($aProduct[4]) ? $aProduct[4] : 0;
            $frn->Member->Cart->add($productId, $qty, $priceNumber, $propId, $fixedPrice);
            $totalQty += $qty;
        }
        $this->aProducts = NULL;

        if($frn->Member->Cart->itemcount() > 0){
            $frn->Member->Cart->cleanup();
            $frn->Member->Cart->recalcTotal($db, TRUE);
        }

        $addedProductsQty = $frn->Member->Cart->itemcount();
        if(!$skipInvalidItems && ($totalQty != $addedProductsQty)){
            return FALSE;
        }

        if(empty($oOrder) || !is_object($oOrder)){
            $oOrder = new EshopOrder();
            $oOrder->aCurrency = $frn->Eshop->aCurrency;
        }

        $aUserData = array(
            'firstname' => isset($aCustomerInfo['firstname']) ? $aCustomerInfo['firstname'] : $oUser->firstname,
            'lastname'  => isset($aCustomerInfo['lastname']) ? $aCustomerInfo['lastname'] : $oUser->lastname,
            'company'   => isset($aCustomerInfo['company']) ? $aCustomerInfo['company'] : $oUser->company,
            'email'     => isset($aCustomerInfo['email']) ? $aCustomerInfo['email'] : $oUser->email,
            'phone'     => isset($aCustomerInfo['phone']) ? $aCustomerInfo['phone'] : $oUser->phone,
        );

        $shippingName = isset($aShippingInfo['name']) ? $aShippingInfo['name'] : 'Unknown';
        unset($aShippingInfo['name']);
        $aCustomData = array(
            'get_type_name'      => $shippingName,
            'shipping_conflicts' => 'show_intersection',
            'contact'            => $aUserData['phone']
        ) + $aShippingInfo;

        $aSysData = array(
            'person_type' => 'natural',
            'ip'          => getenv('REMOTE_ADDR'),
            'driver'      => isset($aPaymentInfo['method']) ? $aPaymentInfo['method'] : 'stub',
            'fee_percent' => 0,
            'fee_curr'    => '',
            'fee_const'   => isset($aPaymentInfo['fee']) ? $aPaymentInfo['fee'] : 0
        );

        $aProducts = array();
        $frn->Member->Cart->calcPayment($db, $aProducts, FALSE, TRUE);

        $oUser = AMI::getSingleton('env/session')->getUserData();
        $orderId = $oOrder->create(
            $frn,
            $oUser->id,
            $oUser->login,
            'user',
            '',
            $aUserData['firstname'],
            $aUserData['lastname'],
            $aUserData['company'],
            $aUserData['email'],
            addslashes(serialize($aCustomData)),
            addslashes(serialize($aSysData)),
            '',
            '',
            '',
            array(
                'tax'          => 0,
                'excise_tax'   => 0,
                'shipping_tax' => 0
            ),
            0,
            $frn->Member->Cart->total,
            AMI_Registry::get('lang_data'),
            $frn->Member->Cart->currency
        );
        if(!$orderId){
            return FALSE;
        }

        // @todo: fill
        $priceInfo = array();
        $aExtInfo = array();
        $aOwners = $frn->Member->Cart->InsertItems($aProducts, $orderId, $priceInfo, $aExtInfo);

        $frn->Gui->addBlock('purchase', 'templates/' . $frn->Eshop->ownerName . '_purchase.tpl');
        $productsQty = sizeof($aProducts) - 1;
        $aScope = array(
            'order_id'         => $orderId,
            'single_item_name' => '',
            'items_name_list'  => ''
        );
        $setName = 'order';
        // $orderName = createOrderName($aScope, $aProducts, sizeof($aProducts) - 1, 'order');
        if($productsQty == 1){
            $aScope['single_item_name'] = $aProducts[0]['name'];
        }else{
            $separator = $frn->Gui->get('purchase:order_item_name_separator', $aScope);
            for($i = 0; $i < $productsQty; $i++){
                $aScope['single_item_name'] = $aProducts[$i]['name'];
                $aScope['items_name_list'] .= $separator . $aProducts[$i]['name'];
            }
            $aScope['single_item_name'] = '';
            $aScope['items_name_list'] = mb_substr($aScope['items_name_list'], mb_strlen($separator));
        }
        $orderName = $frn->Gui->get('purchase:order_item_name', $aScope);

        $oQuery =
            DB_Query::getUpdateQuery(
                'cms_es_orders',
                array(
                    'name'         => $orderName,
                    'owners'       => ';' . $this->section . ';' // explode(';', $aOwners)
                )
                + $aOrderData,
                "WHERE `id` = " . $orderId
            );
        AMI::getSingleton('db')->query($oQuery);

        $oOrder->updateStatus($frn, $orderId, 'user', 'draft');

        return $orderId;
    }

    /**
     * Returns default currency.
     *
     * @return string
     * @amidev
     */
    public function getDefaultCurrency(){
        return $this->defaultCurrency;
    }

    /**
     * Returns default currency.
     *
     * @param string $currencyCode  Default currency code
     * @return void
     * @amidev
     */
    public function setDefaultCurrency($currencyCode){
        $this->defaultCurrency = $currencyCode;
    }

    /**
     * Clear cache for products and order data.
     *
     * @param  int $orderId  Order id
     * @return void
     * @amidev
     */
    public function clearCache($orderId){
        AMI_Registry::set($this->section . '_order_' . $orderId, FALSE);
        $this->aProductCache = array();
    }

    /**
     * Returns available payment methods.
     *
     * Data format: array('natural' => 'authorize|webmoney|yandex', 'juridical' => 'stub|print').
     *
     * @return array
     */
    public function getPaymentMethods(){
        return AMI::getOption($this->section . '_order', 'allowed_drivers');
    }
}
