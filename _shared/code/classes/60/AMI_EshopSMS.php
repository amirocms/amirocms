<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module_Catalog
 * @version   $Id: AMI_EshopSMS.php 48218 2014-02-26 09:06:10Z Medvedev Konstantin $
 * @since     x.x.x
 * @amidev
 */

/**
 * Class AMI_EshopSMS.
 *
 * This class contains listener of 'on_order_after_status_change' event and sends<br />
 * SMS to customer with information of new order status.
 *
 * @package    Module_Catalog
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
class AMI_EshopSMS{
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
        if(is_array($aStatusesActions[$aEvent['status']]) && in_array('send_sms_order_status_changed', $aStatusesActions[$aEvent['status']])){

            // Skip send SMS if user notify is off
            if(isset($aEvent['aParams']['dontNotifyUser']) && $aEvent['aParams']['dontNotifyUser']){
                return $aEvent;
            }

            $SMSGate = AMI::getOption('eshop_order', 'sms_gate');
            if($SMSGate == 'sms_gate_none') return $aEvent;

            $db = AMI::getSingleton('db');
            $oItem = $aEvent['oTableItem'];
            $orderId = $oItem->id;
            $memberId = $oItem->id_member;
            $sql = "SELECT phone_cell, phone, phone_work FROM cms_members WHERE id=" . $memberId;
            $aCustomerDetails = $db->fetchRow(DB_Query::getSnippet($sql));
            $phoneNumber = $aCustomerDetails['phone_cell'];
            if(!$phoneNumber) $phoneNumber = $aCustomerDetails['phone'];
            if(!$phoneNumber) $phoneNumber = $aCustomerDetails['phone_work'];

            $sql = "SELECT custinfo FROM cms_es_orders WHERE id=" . $orderId;
            $aOrderCustomerInfo = $db->fetchRow(DB_Query::getSnippet($sql));
            $aOrderCustomerInfo = unserialize($aOrderCustomerInfo['custinfo']);
            if($aOrderCustomerInfo['contact']) $phoneNumber = $aOrderCustomerInfo['contact'];
            
            if(!$aCustomerDetails){
                return $aEvent;
            }

            $oTemplate = AMI::getResource('env/template_sys');
            $aLocales = $oTemplate->parseLocale("_local/_admin/templates/lang/eshop_order.lng");

            $message = AMI::getOption('eshop_order', 'sms_notification_template');
            if(!$message) $message = $aLocales['order_new_status'];

            $message = str_replace("#orderId#", $orderId, $message);
            $message = str_replace("#status#", $aLocales[$aEvent['status']], $message);

            $db = AMI::getSingleton('db');
            $sQL = "SELECT
                      `settings`
                    FROM `cms_ami_sms_gates`
                    WHERE `name` = %s";

            $aSettings = $db->fetchRow(DB_Query::getSnippet($sQL)->q($SMSGate));
            if(!$aSettings || !$aSettings['settings']){
                trigger_error("SMS gate params not found. Set them on the SMS drivers management page",E_USER_NOTICE);
                return $aEvent;
            }

            $aParams = unserialize($aSettings['settings']);
            $aParams['to'] = $phoneNumber;
            $aParams['text'] = $message;
            $className = 'AMI_SMS_' . ucfirst($SMSGate);
            require_once AMI_Registry::get('path/root') . "_local/modules/code/" . $className . ".php";
            if(class_exists($className)){
                $oSMSGate = new $className($aParams);
                $oSMSGate->send();
                if($err = $oSMSGate->getError()){
                    trigger_error($className . " error: $err",E_USER_NOTICE);
                }
                trigger_error($className . " sending message to $phoneNumber: $oSMSGate->getMessage()",E_USER_NOTICE);
            }
        }

        return $aEvent;
    }
}

