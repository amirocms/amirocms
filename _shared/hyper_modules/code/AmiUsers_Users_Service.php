<?php
/**
 * AmiUsers configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiUsers_Users
 * @version   $Id: AmiUsers_Users_Rules.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiUsers/Users service functions.
 *
 * @package    Config_AmiUsers_Users
 * @subpackage Service
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiUsers_Users_Service extends AMI_Module_Service{
    /**
     * Adds web-service handlers.
     *
     * @param  array $aEvent  'on_webservice_start' event data
     * @return void
     */
    public function addWebserviceHandlers(array &$aEvent){
        parent::addWebserviceHandlers($aEvent);
        $oWS = $aEvent['oWebService'];
        $oWS->requireFullEnv();
        $action = $aEvent['action'];
        $aActionXHandler = array(
            'sys.user.profile.get'  => 'handleGetUserProfileAction',
            'sys.user.profile.update' => 'handleUpdateUserProfileAction',
        );
        if(isset($aActionXHandler[$action])){
            $oWS->setPublicAccess($action);
            $oWS->setAuthRequired($action);
            if($aActionXHandler[$action]){
                AMI_Event::addHandler(
                    'on_webservice_{' . $action . '}_action',
                    array($this, $aActionXHandler[$action]),
                    AMI_Event::MOD_ANY,
                    AMI_Event::PRIORITY_HIGH
                );
            }
        }
        return $aEvent;
    }

    /**
     * Handles sys.user.profile.get action.
     *
     * @param  array $aEvent  'on_webservice_{sys.user.profile.get}_action' event data
     * @return array
     */
    public function handleGetUserProfileAction($name, array &$aEvent){
        $aEvent['_break_event'] = true;

        $aSafeFields = array(
            'login',
            'id',
            'firstname',
            'lastname',
            'email',
            'nickname',
            'phone',
            'phone_cell',
            'phone_work',
            'address1',
            'address2',
            'city',
            'state',
            'zip',
            'country',
            'company',
            'active',
            'balance',
            'eshop_discount',
            'eshop_discount_exp_date',
        );

        $oUser = AMI::getSingleton('env/session')->getUserData();

        $aProfile = array();
        $aData = $oUser->getData();
        foreach($aData as $field => $value){
            if(in_array($field, $aSafeFields)){
                $aProfile[$field] = $value;
            }
        }

        $aEvent['user'] = $aProfile;

        $this->oWebService->ok($aEvent);
        return $aEvent;
    }

    /**
     * Handles sys.user.profile.update action.
     *
     * @param  array $aEvent  'on_webservice_{sys.user.profile.update}_action' event data
     * @return array
     */
    public function handleUpdateUserProfileAction($name, array &$aEvent){

        $aFieldsSafeToUpdate = array(
            'firstname',
            'lastname',
            'email',
            'nickname',
            'phone',
            'phone_cell',
            'phone_work',
            'address1',
            'address2',
            'city',
            'state',
            'zip',
            'country',
            'company'
        );

        $aUsedFields = AMI::getOption('members', 'used_fields');
        $aRequiredFields = AMI::getOption('members', 'required_fields');

        $oUser = AMI::getSingleton('env/session')->getUserData();
        $oRequest = AMI::getSingleton('env/request');

        $aUpdates = array();
        $aSkipped = array();

        foreach($aFieldsSafeToUpdate as $field){
            $value = $oRequest->get($field, FALSE);
            if($value !== FALSE){
                if(!in_array($field, $aUsedFields)){
                    $aSkipped[] = $field;
                    continue;
                }
                if(!strlen($value) && in_array($field, $aRequiredFields)){
                    $this->oWebService->error(AmiClean_Webservice_Service::ERR_INVALID_ARGUMENT_VALUE, 'Field "' . $field . '" cannot be empty');
                }
                $aUpdates[$field] = $value;
            }
        }
        if(count($aUpdates)){
            $oUser->setData($aUpdates);
            $oUser->save();
        }

        $aEvent['updated'] = array_keys($aUpdates);
        $aEvent['skipped'] = $aSkipped;

        $this->oWebService->ok($aEvent);

        return $aEvent;
    }
}
