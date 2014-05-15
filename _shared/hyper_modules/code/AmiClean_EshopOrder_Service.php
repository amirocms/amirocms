<?php
/**
 * AmiClean/EshopOrder module configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiClean_EshopOrder
 * @version   $Id: AmiClean_EshopOrder_Service.php 49503 2014-04-08 05:09:05Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiClean/EshopOrder service functions.
 *
 * @package    Config_AmiClean_EshopOrder
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_EshopOrder_Service extends AMI_Module_Service{
    /**
     * Section
     *
     * @var string
     */
    protected $section;

    /**
     * Options
     *
     * @var array
     */
    protected $aOptions = array();

    /**
     * Array of ordered products
     *
     * @var array
     * @see this::validateOnOrderCreate()
     * @see this::handleOrderCreateAction()
     */
    protected $aProducts;

    /**
     * Dispatches service action.
     *
     * @return void
     */
    public function dispatchRawAction(){
        $GLOBALS['CONNECT_OPTIONS']['disable_cache_warn'] = TRUE;
        switch($_REQUEST['action']){
            case 'get_order_cost':
                usleep(200000);
                AMI_Registry::set('srv_get_order_cost', TRUE);
                global $frn, $db, $oCart, $oEshop;
                $aOrderCostData = array();
                $aOrderCostData['error'] = '';

                $frn->Vars["action"] = 'add';
                require $GLOBALS['DEFAULT_INCLUDES_PATH'] . 'eshop_order.php';

                $this->send(json_encode($aOrderCostData));
                break;
            default:
                AMI::getSingleton('response')->HTTP->setServiceUnavailable(3600);
                break;
        }
    }

    /**
     * Adds web-service handlers.
     *
     * @param  array $aEvent  'on_webservice_start' event data
     * @return void
     */
    public function addWebserviceHandlers(array &$aEvent){
        parent::addWebserviceHandlers($aEvent);

        $action = $aEvent['action'];
        $aActionXHandler = array(
            'sys.item_add'  => 'handleOrderCreateAction',
            'sys.item_list' => FALSE,
            'sys.item_get'  => 'handleOrderDetailsAction'
        );
        if(isset($aActionXHandler[$action])){
            $this->oRequest = AMI::getSingleton('env/request');
            $this->section = 'eshop';
            $this->oWebService->setAuthRequired($action);

            if($aActionXHandler[$action]){
                AMI_Event::addHandler(
                    'on_webservice_{' . $action . '}_action',
                    array($this, $aActionXHandler[$action]),
                    AMI_Event::MOD_ANY,
                    AMI_Event::PRIORITY_HIGH
                );
            }

            if('sys.item_add' !== $action){
                $aEvent['aSafeFields'] = AMI::getResourceModel('eshop_order/table')->getAvailableFields();
                AMI_Event::addHandler(
                    'on_query_add_table',
                    array($this, 'filterUserOrderQuery'),
                    $this->section . '_order'
                );
            }
        }
    }

    /**
     * Filters query by `id_owner`.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public static function filterUserOrderQuery($name, array $aEvent, $handlerModId, $srcModId){
        $oUser = AMI::getSingleton('env/session')->getUserData();
        $alias = $aEvent['alias'];
        if($alias){
            $alias .= '.';
        }
        $aEvent['oQuery']->addWhereDef(
            DB_Query::getSnippet("AND %s`id_member` = %s")
            ->plain($alias)
            ->plain($oUser->id)
        );

        return $aEvent;
    }

    /**
     * Handles create order action.
     *
     * @param  string $name     Event name
     * @param  array  &$aEvent  Event data
     * @return array
     */
    public function handleOrderCreateAction($name, array &$aEvent){
        $this->aOptions['allowFractionalQty'] =
            AMI::getOption($this->section . '_order', 'allow_fractional_quantity');

        $this->validateOnOrderCreate();

        global $frn, $oSession, $db, $oOrder;

        AMI::setOption($this->section . '_item', 'apply_discount_to_fields', array());
        AMI::setOption($this->section . '_discounts', 'products_discounts_syncopation', 'products_discount');

        $frn->Eshop = NULL;
        $frn->WorkingModuleName = '';
        CreateEshop($frn, TRUE);
        $frn->Eshop->initByOwnerName($this->section);
        CreateCart($frn, $oSession);
        $frn->Member->Cart->init();
        $frn->Member->Cart->ownerName = $this->section;

        $defaultPriceNumber = $this->oRequest->get('priceNumber', 0);
        $totalQty = 0;
        foreach($this->aProducts as $aProduct){
            $productId = $aProduct[0];
            $qty = $aProduct[1];
            $priceNumber = isset($aProduct[2]) ? $aProduct[2] : $defaultPriceNumber;
            $frn->Member->Cart->add($productId, $qty, $priceNumber);
            $totalQty += $qty;
        }
        $this->aProducts = NULL;

        if($frn->Member->Cart->itemcount() > 0){
            $frn->Member->Cart->cleanup();
            $frn->Member->Cart->recalcTotal($db, TRUE);
        }

        $addedProductsQty = $frn->Member->Cart->itemcount();
        if($totalQty != $addedProductsQty){
            $this->cleanupCookies();
            $this->oWebService->error(
                AmiClean_Webservice_Service::ERR_ITEM_ADD_FAILED,
                'Creating order is discarded, requested products qty: ' . $totalQty .
                ', added to cart: ' . $addedProductsQty
            );
        }

        if(empty($oOrder) || !is_object($oOrder)){
            $oOrder = new EshopOrder();
            $oOrder->aCurrency = $frn->Eshop->aCurrency;
        }

        // @todo: fill
        $aCustomData = array(
            'get_type_name'      => '',
            'shipping_conflicts' => '',
            'contact'            => $oUser->phone
        );
        $aSysData = array(
            'person_type' => 'natural',
            'ip'          => getenv('REMOTE_ADDR'),
            'driver'      => 'stub',
            'fee_percent' => 0,
            'fee_curr'    => '',
            'fee_const'   => 0
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
            $oUser->firstname,
            $oUser->lastname,
            $oUser->company,
            $oUser->email,
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
            $this->oWebService->error(
                AmiClean_Webservice_Service::ERR_ITEM_ADD_FAILED,
                'Creating order failed'
            );
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
                    'adm_comments' => $this->appName,
                    'owners'       => ';' . $this->section . ';' // explode(';', $aOwners)
                ),
                "WHERE `id` = " . $orderId
            );
        AMI::getSingleton('db')->query($oQuery);

        $oOrder->updateStatus($frn, $orderId, 'user', 'draft');
        $aEvent['order'] = array(
            'id'     => $orderId,
            'status' => 'draft'
        );
        $this->oWebService->cleanupCookies();
        $aEvent['_break_event'] = TRUE;
        $this->oWebService->ok($aEvent);

        return $aEvent;
    }

    /**
     * Returns order details.
     *
     * @param  string $name     Event name
     * @param  array  &$aEvent  Event data
     * @return array
     */
    public function handleOrderDetailsAction($name, array &$aEvent){
        $orderId = $this->oRequest->get('itemId', 0);
        $oItem =
            AMI::getResourceModel($this->section . '_order/table')
            ->find($orderId);
        if(!$oItem->id){
            $this->oWebService->error(
                AmiClean_Webservice_Service::ERR_ITEM_NOT_FOUND,
                "Eshop order '" . $orderId . "' not found"
            );
        }
        $aEvent['order'] = array(
            'order' => $oItem->getData()
        );
        unset($oItem);
        $oTable = AMI::getResourceModel($this->section . '_order_item/table');
        $oList =
            $oTable
            ->getList()
            ->addColumns($oTable->getAvailableFields())
            ->addWhereDef(
                DB_Query::getSnippet('AND `id_order` = %s')
                ->plain($orderId)
            )
            ->load();
        $aEvent['order']['products'] = array_map(array($this, 'cbItemToArray'), iterator_to_array($oList));

        $aEvent['_break_event'] = TRUE;
        $this->oWebService->ok($aEvent);

        return $aEvent;
    }

    /**
     * Renders item model to array.
     *
     * @param  AMI_ModTableItem $oItem
     * @return array
     */
    protected function cbItemToArray($oItem){
        return $oItem->getData();
    }

    /**
     * Validates request on eshop order creating.
     *
     * @return void
     */
    protected function validateOnOrderCreate(){
        $this->oWebService->requireFullEnv();
        $modId = $this->oRequest->get('modId', FALSE);
        if(FALSE === $modId){
            $this->oWebService->error(
                AmiClean_Webservice_Service::ERR_MISSING_REQUIRED_ARGUMENT,
                "Missing required 'modId' argument"
            );
        }
        if('eshop_order' !== $modId){
            $this->oWebService->error(
                AmiClean_Webservice_Service::ERR_NO_ACCESS_RIGHTS,
                "No access rights for action 'sys.item_add' in module '" . $modId . "'"
            );
        }

        $aPrices = AMI::getSingleton('eshop')->getOtherPrices();
        array_unshift($aPrices, 0);

        $priceNumber = $this->oRequest->get('priceNumber', FALSE);
        if((FALSE !== $priceNumber) && (!is_numeric($priceNumber) || !in_array($priceNumber, $aPrices))){
            $this->oWebService->error(
                AmiClean_Webservice_Service::ERR_INVALID_ARGUMENT_VALUE,
                "Invalid 'priceNumber' argument value, allowed: " . implode(', ', $aPrices)
            );
        }
        $products = $this->oRequest->get('products', FALSE);
        if(!$products){
            $this->oWebService->error(
                AmiClean_Webservice_Service::ERR_MISSING_REQUIRED_ARGUMENT,
                "Missing required 'products' argument"
            );
        }
        $aProducts = json_decode($products, TRUE);
        if(!is_array($aProducts) || empty($aProducts)){
            $this->oWebService->error(
                AmiClean_Webservice_Service::ERR_INVALID_ARGUMENT_VALUE,
                "Invalid 'products' argument value"
            );
        }
        $index = 0;
        foreach($aProducts as $aProduct){
            $index++;
            $size = sizeof($aProduct);
            if(($size < 2) || ($size > 3)){
                $this->oWebService->error(
                    AmiClean_Webservice_Service::ERR_INVALID_ARGUMENT_VALUE,
                    "Invalid elements quantity in " . $index . " products row (" . $size . " instead of 2 or 3)"
                );
            }
            $productId = $aProduct[0];
            $qty = $aProduct[1];
            $priceNumber = isset($aProduct[2]) ? $aProduct[2] : 0;
            if(!is_int($productId)){
                $this->oWebService->error(
                    AmiClean_Webservice_Service::ERR_INVALID_ARGUMENT_VALUE,
                    "Invalid product id '" . $productId . "' in " . $index . " products row"
                );
            }
            if($this->aOptions['allowFractionalQty'] ? !is_numeric($qty) : !is_int($qty)){
                $this->oWebService->error(
                    AmiClean_Webservice_Service::ERR_INVALID_ARGUMENT_VALUE,
                    "Invalid product quantity '" . $qty . "' in " . $index . " products row"
                );
            }
            if(!is_numeric($priceNumber) || !in_array($priceNumber, $aPrices)){
                $this->oWebService->error(
                    AmiClean_Webservice_Service::ERR_INVALID_ARGUMENT_VALUE,
                    "Invalid product price number '" . $priceNumber . "' in " . $index . " products row, allowed: " .
                    implode(', ', $aPrices)
                );
            }
        }
        $this->aProducts = $aProducts;
    }
}
