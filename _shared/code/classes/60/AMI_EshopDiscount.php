<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module_Catalog
 * @version   $Id: AMI_EshopDiscount.php 44136 2013-11-20 11:01:37Z Medvedev Konstantin $
 * @since     x.x.x
 * @amidev
 */

/**
 * Class AMI_EshopDiscount.
 *
 * This class contains listener of 'on_order_before_status_change' event and logic<br />
 * of applying personal discounts for user.
 *
 * @package    Module_Catalog
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
class AMI_EshopDiscount{
    /**
     * Constructor
     *
     * Adds listener of on_order_before_status_change event
     */
    public function __construct(){
        AMI_Event::addHandler('on_order_after_status_change', array($this, 'onOrderAfterStatusChange'), AMI_Event::MOD_ANY);
    }

    /**
     * Event handler.
     *
     * Dispatches 'on_order_after_status_change' event.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     * @see    EshopOrder::updateStatus()
     */
    public function onOrderAfterStatusChange($name, array &$aEvent, $handlerModId, $srcModId){
        $aStatusesActions = AMI::getOption('eshop_order', 'statuses_actions');
        if(is_array($aStatusesActions[$aEvent['status']]) && in_array('add_discount_to_user', $aStatusesActions[$aEvent['status']])){
            $oItem = $aEvent['oTableItem'];
            $orderId = $oItem->id;
            $memberId = $oItem->id_member;
            $sql = "SELECT ext_data as ext_data FROM cms_es_orders o ".
                "WHERE o.id=" . $orderId;

            $db = AMI::getSingleton('db');
            $aResult = $db->fetchRow(DB_Query::getSnippet($sql));
            if(!$aResult) {
                return $aEvent;
            }

            $extData = unserialize($aResult['ext_data']);

            if(isset($extData['discountForUser'])){
                if($extData['discountForUser']['is_applied']){
                    return $aEvent;
                }
                $discountForUser = $extData['discountForUser'];
                $extData['discountForUser']['is_applied'] = true;
                $sql = "UPDATE cms_es_orders SET ext_data=%s WHERE id=" . $orderId;
                $res = $db->query(DB_Query::getSnippet($sql)->q(serialize($extData)));
                $sql = "SELECT discount, discount_exp_date FROM cms_es_users WHERE id_member=" . $memberId;
                $aResult = $db->fetchRow(DB_Query::getSnippet($sql));
                if(!$aResult){
                    return $aEvent;
                }
                if($discountForUser['discountType'] == 'percent'){
                    if($aResult['discount'] > $discountForUser['discount']){
                       return $aEvent;
                    }

                    $validity = $discountForUser['validity'];
                    $validityDate = new DateTime("+$validity days");
                    $sql = "UPDATE cms_es_users SET discount=%s, discount_exp_date=%s  WHERE id_member=" . $memberId;
                    $db->query(DB_Query::getSnippet($sql)->q($discountForUser['discount'])->q($validityDate->format('Y-m-d H:i:s')));

                    $sql = "UPDATE cms_members SET eshop_discount=%s, eshop_discount_exp_date=%s  WHERE id=" . $memberId;
                    $db->query(DB_Query::getSnippet($sql)->q($discountForUser['discount'])->q($validityDate->format('Y-m-d H:i:s')));
                }

                if($discountForUser['discountType'] == 'abs'){
                    $sess = "";
                    $cmsMember = new CMS_Member($sess);
                    $cCurrency = AMI::getSingleton('eshop')->getDefaultCurrencyData();
                    $coreDb = AMI::getSingleton('db')->getCoreDB();
                    $cmsMember->updateBalance($coreDb, $memberId, $discountForUser['discount'], $cCurrency->code, false, 'user', "applied from discount");
                }
            }
        }

        return $aEvent;
    }
}

