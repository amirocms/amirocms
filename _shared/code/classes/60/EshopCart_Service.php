<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module_Catalog
 * @version   $Id: EshopCart_Service.php 49503 2014-04-08 05:09:05Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Eshop cart service functions.
 *
 * @package    Module_Catalog
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class EshopCart_Service extends AMI_Module_Service{

    /**
     * Dispatches service action.
     *
     * @return void
     * @amidev
     */
    public function dispatchRawAction(){
        switch($_GET['action']){
            case 'get_available_shippings':
                $this->getAvailableShipping();
                break;
            case 'get_items':
                $this->getItems();
                break;
            case 'get_items_info':
                $this->getItemsInfo();
                break;
            default:
                // $this->sendRawResponse('', 3600);
                AMI::getSingleton('response')->HTTP->setServiceUnavailable(3600);
                break;
        }
    }

    /**
     * Dispatches 'get_available_shippings' action.
     *
     * @return void
     */
    protected function getAvailableShipping(){
        /*
        usleep(200000);
        AMI_Registry::set('srv_get_available_shippings', TRUE);
        global $frn, $db, $oCart, $oEshop;

        $aShippingData = array();
        $aShippingData['error'] = '';

        $frn->Vars["action"] = 'none';
        require $GLOBALS['DEFAULT_INCLUDES_PATH'] . 'eshop_order.php';
        $this->send(json_encode($aShippingData));
        */

        usleep(200000);
        AMI_Registry::set('srv_get_available_shippings', true);
        global $frn, $db, $oCart, $oEshop;
        $aShippingData = array();
        $aShippingData['error'] = '';

        $frn->Vars["action"] = 'none';
        require $GLOBALS['DEFAULT_INCLUDES_PATH'] . 'eshop_order.php';

        AMI_Service::hideDebug();
        $this->sendRawResponse(json_encode($aShippingData));
    }

    /**
     * Dispatches 'get_items' action.
     *
     * @return void
     */
    protected function getItems(){
        global $db;
        $aCartData = array();
        $ownerName = 'eshop';
        $cookieName = 'session_';

        if(!empty($_GET['scname'])){
            $cookieName = $_GET['scname'];
        }

        if(!empty($GLOBALS['_COOKIE'][$cookieName])){
            $res = $db->query("SELECT data FROM cms_sessions WHERE id = '" . mysql_real_escape_string($GLOBALS['_COOKIE'][$cookieName]) . "' AND expired > NOW() " . (!empty($GLOBALS['CONFIG_INI']['session']['session_no_ip_bind']) ? "" : (" AND ip = '" . $_SERVER['REMOTE_ADDR'] . "'")));
            if($res){
                $resData = $db->nextRecord();
                $sessionData = unserialize($resData['data']);
                $sessionCartName = 'cart' . '_' . str_replace('session_', '', $cookieName);
                if(!empty($sessionData[$sessionCartName])){
                    $sessionCart = unserialize($sessionData[$sessionCartName]);
                    if(!empty($sessionCart->items[$ownerName])){
                        foreach($sessionCart->items[$ownerName] as $itemId => $aItemData){
                            if(!(int)$itemId){
                                continue;
                            }
                            foreach($aItemData as $propId => $aPriceData){
                                foreach($aPriceData as $priceNum => $qty){
                                    $aItemInfo = array();
                                    $aItemInfo = array(
                                        'itemId'    => $itemId,
                                        'qty'       => (int)$qty,
                                        'priceNum'  => ((int)$propId > 0) ? null : $priceNum,
                                        'propId'    => (int)$propId,
                                        'aPropInfo' => (((int)$propId > 0) && !empty($sessionCart->itemPropData[$ownerName][$itemId][$propId])) ? $sessionCart->itemPropData[$ownerName][$itemId][$propId] : array()
                                    );
                                    $aCartData[$itemId][] = $aItemInfo;
                                }
                            }
                        }
                    }
                }
            }
        }

        if(sizeof($aCartData) <= 0){
            $this->sendRawResponse('', 503);
            return;
        }

        AMI_Service::hideDebug();
        $this->sendRawResponse(json_encode($aCartData));
    }

    /**
     * Dispatches 'get_items_info' action.
     *
     * @return void
     */
    protected function getItemsInfo(){
        global $frn;
        $aCartData = array();

        if($frn->Core->IsInstalled('eshop_cart')){
            $oEshopCart = AMI::getSingleton('eshop/cart');
            $aTotal = $oEshopCart->getTotal();
            $oEshop = AMI::getSingleton('eshop');
            $aCartData['total'] = $aTotal;
            $aCartData['total']['price_formatted'] = isset($aCartData['total']['price']) ? $oEshop->formatMoney($aCartData['total']['price'], $oEshopCart->currency) : '';
            $aItems = $oEshopCart->getItems();
            if(sizeof($aItems) <= 0){
                AMI::getSingleton('response')->HTTP->setServiceUnavailable(3600);
                return;
            }
            foreach($aItems as $oCartItem){
                $aItemInfo = array();
                $aItemInfo = array(
                    'itemId'     => $oCartItem->getItemId(),
                    'qty'        => $oCartItem->getQty(),
                    'priceNum'   => $oCartItem->getPriceNum(),
                    'propId'     => $oCartItem->getPropId(),
                    'aPropInfo'  => $oCartItem->getPropInfo(),
                    'aPriceInfo' => $oCartItem->getPriceInfo(),
                    'aTax'       => $oCartItem->getTax(),
                    'aDiscount'  => $oCartItem->getDiscount(),
                    'aShipping'  => $oCartItem->getShipping()
                );
                $aItemInfo['aPriceInfo']['price_formatted'] = isset($aItemInfo['aPriceInfo']['price']) ? $oEshop->formatMoney($aItemInfo['aPriceInfo']['price'], $oEshopCart->currency) : '';
                $aItemInfo['aTax']['tax_formatted'] = isset($aItemInfo['aTax']['tax']) ? $oEshop->formatMoney($aItemInfo['aTax']['tax'], $oEshopCart->currency) : '';
                $aItemInfo['aDiscount']['discount_formatted'] = isset($aItemInfo['aDiscount']['absolute_discount']) ? $oEshop->formatMoney($aItemInfo['aDiscount']['absolute_discount'], $oEshopCart->currency) : '';
                $aItemInfo['aShipping']['shipping_formatted'] = isset($aItemInfo['aShipping']['shipping']) ? $oEshop->formatMoney($aItemInfo['aShipping']['shipping'], $oEshopCart->currency) : '';

                $aCartData[$oCartItem->getItemId()][] = $aItemInfo;
            }
        }

        AMI_Service::hideDebug();
        $this->sendRawResponse(json_encode($aCartData));
    }
}
