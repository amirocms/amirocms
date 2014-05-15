<?php
/**
 * AmiClean/EshopOrder configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiClean_EshopOrder
 * @version   $Id: AmiClean_EshopOrder_Rules.php 47267 2014-01-31 05:27:09Z Medvedev Konstantin $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiClean/EshopOrder configuration rules.
 *
 * @package    Config_AmiClean_EshopOrder
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_EshopOrder_Rules extends Hyper_AmiClean_Rules{
    /**
     * Handler for ##modId##:getOptionsModCB callback.
     *
     * @param mixed $callbackData   Data for callback
     * @param mixed &$optionsData    Options data
     * @param string $callbackMode  Callback mode
     * @param mixed &$result         Result data
     * @param array &$aData          Data
     * @return bool
     */
    public function getSMSGates($callbackData, &$optionsData, $callbackMode, &$result, array &$aData){
        if(!is_array($callbackData["value"])){
            $callbackData["value"] = ($callbackData["value"] != "") ? array($callbackData["value"]) : array();
        }
        $oTpl = AMI::getSingleton('env/template_sys');
        $aWords = $oTpl->parseLocale('templates/lang/options/eshop_order_rules_values.lng');
        switch($callbackMode){
            case self::GET_ALL:
                $result = array(
                    array(
                        "name" => 'sms_gate_none',
                        "caption" => $aWords['sms_gate_none'],
                        "selected"  => "selected"
                    )
                );

                if(!sizeof($modId = AMI_ModDeclarator::getInstance()->getRegistered('ami_clean', 'ami_sms_gates'))){
                    return true;
                } else {
                    $result = array(
                        array(
                            "name" => 'sms_gate_none',
                            "caption" => $aWords['sms_gate_none'],
                            "selected"  => ""
                        )
                    );
                }
                $modelName = current($modId) . "/table";
                $oModel = AMI::getResourceModel($modelName);
                $oList = $oModel->getList()->addColumns(array('name', 'header'))->addSearchCondition(array('public' => 1))->load();

                foreach($oList as $oItem){
                    $result[] = array(
                        "name"      => $oItem->name,
                        "caption"   => $oItem->header,
                        "selected"  => ((in_array($oItem->name, $callbackData["value"])) ? "selected" : "")
                    );
                }

                $isSelected = false;
                foreach($result as $res){
                    if($res['selected'] == 'selected') {
                        $isSelected = true;
                    }
                }
                if(!$isSelected) $result[0]['selected'] = 'selected';

                $aData['allow_empty'] = true;
                break;
            case self::GET_VAL:
                $result = $callbackData["value"];
                break;
        }
        return true;
    }

    /**
     * Handler for ##modId##:getOptionsModCB callback.
     *
     * @param mixed $callbackData   Data for callback
     * @param mixed &$optionsData    Options data
     * @param string $callbackMode  Callback mode
     * @param mixed &$result         Result data
     * @param array &$aData          Data
     * @return bool
     */
    public function getSMSTemplate($callbackData, &$optionsData, $callbackMode, &$result, array &$aData){
        if(!is_array($callbackData["value"])){
            $callbackData["value"] = ($callbackData["value"] != "") ? array($callbackData["value"]) : array();
        }
        $oTemplate = AMI::getResource('env/template_sys');
        $aLocales = $oTemplate->parseLocale("_local/_admin/templates/lang/eshop_order.lng");

        $message = $aLocales['order_new_status'];
        $savedMessage = AMI::getOption('eshop_order', 'sms_notification_template');

        switch($callbackMode){
            case self::GET_VAL:
                $result = $savedMessage ? $savedMessage : $message;
                break;
        }
        return true;
    }
}
