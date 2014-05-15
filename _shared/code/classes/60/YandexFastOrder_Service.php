<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module_YandexFastOrder
 * @version   $Id: YandexFastOrder_Service.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Yandex fast order service functions.
 *
 * @package    Module_YandexFastOrder
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class YandexFastOrder_Service extends AMI_Module_Service{
    /**
     * Dispatches raw service action.
     *
     * @return void
     */
    public function dispatchRawAction(){
        switch($_REQUEST['action']){
            case 'get_address':
                // getting address from Yandex
                $this->openYandexAddresses();
                break;
        }
    }

    /**
     * Dispatches service action.
     *
     * @param  AMI_Request  $oRequest   Request
     * @param  AMI_Response $oResponse  Response
     * @return void
     */
    public function dispatchAction(AMI_Request $oRequest, AMI_Response $oResponse){
        switch($_REQUEST['action']){
            case 'set_address':
                // setting address onto order form
                $this->setShippingAddress($oRequest, $oResponse);
                break;
        }
    }

    /**
     * Open Yandex addresses page.
     *
     * @return void
     */
    public function openYandexAddresses(){
        header(
            'Location: http://market.yandex.ru/addresses.xml?callback=' . urlencode($GLOBALS['ROOT_PATH_WWW'] . 'ami_service.php?service=yandex_fast_order&action=set_address'),
            TRUE,
            301
        );
        die;
    }

    /**
     * Set shipping address.
     *
     * @param  AMI_Request  $oRequest   Request object
     * @param  AMI_Response $oResponse  Response object
     * @return void
     */
    public function setShippingAddress(AMI_Request $oRequest, AMI_Response $oResponse){
        $aScope = array();
        $aVarMap = array(
            'firstname'        => 'firstname',
            'lastname'         => 'lastname',
            'fathersname'      => 'fathersname',
            'email'            => 'email',
            'phone'            => 'phone',
            'contact'          => 'phone',
            'phone-extra'      => 'phone-extra',
            'city_custom'      => 'city',
            'country_custom'   => 'country',
            'street_custom'    => 'street',
            'house_custom'     => 'building',
            'building_custom'  => 'suite',
            'app_custom'       => 'flat',
            'entrance_custom'  => 'entrance',
            'floor_custom'     => 'floor',
            'code_custom'      => 'intercom',
            'zip_custom'       => 'zip',
            'station_custom'   => 'metro',
            'cargolift'        => 'cargolift',
            'comments'         => 'comment',
        );
        foreach($aVarMap as $key => $value){
            $aScope[$key] = isset($_REQUEST[$value]) ? AMI_Lib_String::jParse($_REQUEST[$value]) : '';
        }
        $tplBlockName = 'yandex_fast_order';
        $tplFileName = 'templates/yandex_fast_order.tpl';

        $oTpl = AMI::getSingleton('env/template_sys');
        $oTpl->addBlock($tplBlockName, $tplFileName);

        $oResponse->write($oTpl->parse($tplBlockName, $aScope));
    }
}
