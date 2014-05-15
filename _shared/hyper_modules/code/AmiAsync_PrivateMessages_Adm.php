<?php
/**
 * AmiAsync/PrivateMessages configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiAsync_PrivateMessages
 * @version   $Id: AmiAsync_PrivateMessages_Adm.php 45856 2013-12-24 09:48:43Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

class_exists('AmiAsync_PrivateMessages_Frn');

/**
 * AmiAsync/PrivateMessages configuration admin action controller.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_Adm extends Hyper_AmiAsync_Adm{
    /**
     * Constructor.
     *
     * @param AMI_Request $oRequest    Request object
     * @param AMI_Response $oResponse  Response object
     */
    public function  __construct(AMI_Request $oRequest, AMI_Response $oResponse){

        parent::__construct($oRequest, $oResponse);
        $oRequest = AMI::getSingleton('env/request');
        $mode = $oRequest->get('mode', 'inbox');
        AMI_Registry::set('private_messages_mode', $mode);

        $this->addComponents(array('filter', 'list', 'form'));
    }
}

/**
 * AmiAsync/PrivateMessages configuration admin list component action controller.
 *
 * @category   AMI
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_ListAdm extends AmiAsync_PrivateMessages_ListFrn{
    /**
     * List actions controller resource id
     *
     * @var string
     */
    protected $listActionsResId = 'private_messages/list_actions/controller/adm';

    /**
     * List group actions controller resource id
     *
     * @var string
     */
    protected $listGrpActionsResId = 'private_messages/list_group_actions/controller/adm';
}

/**
 * AmiAsync/PrivateMessages configuration admin list actions controller.
 *
 * @category   AMI
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Controller
 * @amidev    Temporary
 */
class AmiAsync_PrivateMessages_ListActionsAdm extends AmiAsync_PrivateMessages_ListActionsFrn{
}

/**
 * AmiAsync/PrivateMessages configuration admin list group actions controller.
 *
 * @category   AMI
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Controller
 * @amidev    Temporary
 */
class AmiAsync_PrivateMessages_ListGroupActionsAdm extends AmiAsync_PrivateMessages_ListGroupActionsFrn{
}

/**
 * AmiAsync/PrivateMessages configuration admin list component view.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_ListViewAdm extends AmiAsync_PrivateMessages_ListViewFrn{
    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();

        $this->addScriptFile('_admin/'.$GLOBALS['CURRENT_SKIN_PATH'].'_js/private_messages_list.js');
    }
}

/**
 * AmiAsync/PrivateMessages configuration admin form component action controller.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_FormAdm extends AmiAsync_PrivateMessages_FormFrn{
    /**
     * Initialization.
     *
     * @return AmiAsync_PrivateMessages_FormFrn
     */
    public function init(){
        smartSetCookie(false, 'amiMessages', '0', time() - 3600*24*12, null, null);
        smartSetCookie(false, 'amiAdmMessages', '0', time() - 3600*24*12, null, null);

        return parent::init();
    }
}

/**
 * AmiAsync/PrivateMessages configuration form component view.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_FormViewAdm extends AmiAsync_PrivateMessages_FormViewFrn{
    /**
     * Handle form fields in edit mode.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleFormFieldsWithId($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent = parent::handleFormFieldsWithId($name, $aEvent, $handlerModId, $srcModId);

        $aEvent['aScope']['msg_body_raw'] = $aEvent['aScope']['msg_body'];
        if($aEvent['aScope']['is_broadcast']){
            if(!$aEvent['aScope']['msg_sender']){
                $aEvent['aScope']['msg_sender'] = $this->aLocale['username_admin'];
            }
            $aEvent['aScope']['msg_recipient'] = $this->aLocale['username_admin'];
        }
        $aEvent['aScope']['msg_body'] = AMI_Lib_String::jparse($aEvent['aScope']['msg_body']);
        // Common scripts
        $aEvent['aScope']['bbEditorURL'] =
            $GLOBALS['ROOT_PATH_WWW'] .
                toStr(array(97, 109, 105, 114, 111, 95, 115, 121, 115, 95, 106, 115, 46, 112, 104, 112, 63, 115, 99, 114, 105, 112, 116, 61, 101, 100, 105, 116, 111, 114, 38, 95, 104, 61)) .
                mb_substr(md5(AMI::getOption('ce', 'smile_collection')), 0, 10) .
                '&dlang=' . AMI_Registry::get('lang_data') . '&ul=' .
                AMI::getOption('common_settings', 'url_max_length') .
                '&cv=' . AMI::getVersion('cms', 'code');

        return $aEvent;
    }
}

/**
 * Module model.
 *
 * @category   AMI
 * @package    Config_AmiAsync_PrivateMessages
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_State extends Hyper_AmiAsync_State{
}

/**
 * AmiAsync/PrivateMessages configuration admin filter component action controller.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_FilterAdm extends AmiAsync_PrivateMessages_FilterFrn{
}

/**
 * AmiAsync/PrivateMessages configuration item list component filter model.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_FilterModelAdm extends AmiAsync_PrivateMessages_FilterModelFrn{
}

/**
 * AmiAsync/PrivateMessages configuration admin filter component view.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_FilterViewAdm extends AmiAsync_PrivateMessages_FilterViewFrn{
}