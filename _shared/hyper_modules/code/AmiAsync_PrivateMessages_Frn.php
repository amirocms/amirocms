<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   AmiAsync/PrivateMessages
 * @version   $Id: AmiAsync_PrivateMessages_Frn.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiAsync/PrivateMessages configuration front action controller.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_Frn extends Hyper_AmiAsync_Frn{
    /**
     * Constructor.
     *
     * @param AMI_Request $oRequest    Request object
     * @param AMI_Response $oResponse  Response object
     */
    public function  __construct(AMI_Request $oRequest, AMI_Response $oResponse){

        // Require login
        AMI::getSingleton('env/session')
            ->requireAuthorizedUser(
                AMI_Registry::get('ami_request_type', 'plain') === 'ajax'
                ? ''
                : $GLOBALS['vGlobVars']['www_root_lang'] . AMI_PageManager::getModLink('members', AMI_Registry::get('lang_data')),
                AMI::getSingleton('env/request')->getURL()
            );

        if(AMI_Registry::get('ami_request_type', 'plain') === 'ajax'){
            $oRequest = AMI::getSingleton('env/request');
            $mode = $oRequest->get('mode', 'inbox');
            AMI_Registry::set('private_messages_mode', $mode);
            $oResponse->loadStatusMessages(AMI_iTemplate::LNG_MOD_PATH . '/' . $this->getModId() . '_messages.lng');
        }
        parent::__construct($oRequest, $oResponse);
    }

    /**
     * Returns client locale path.
     *
     * @return string
     */
    public function getClientLocalePath(){
        return AMI_iTemplate::LNG_MOD_PATH . '/' . $this->getModId() . '_client.lng';
    }

    /**
     * SEO processing.
     *
     * @return AMI_Module_Frn
     * @amidev Temporary
     */
    public function processSEO(){
        return $this;
    }
}

/**
 * AmiAsync/PrivateMessages configuration front async component action controller.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_AsyncFrn extends Hyper_AmiAsync_AsyncFrn{
}

/**
 * AmiAsync/PrivateMessages configuration front async component view.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_AsyncViewFrn extends Hyper_AmiAsync_AsyncViewFrn{
    /**
     * Template file name
     *
     * @var string
     */
    protected $tplFileName = '_shared/code/templates/modules/private_messages.tpl';

    /**
     * Locale filename.
     *
     * @var string
     */
    // protected $localeFileName = 'templates/lang/modules/private_messages.lng';
}

/**
 * AmiAsync/PrivateMessages configuration front form component action controller.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_FormFrn extends Hyper_AmiAsync_FormFrn{
    /**
     * Returns true if component needs to be started always in full environment.
     *
     * @return bool
     */
    public function isFullEnv(){
        return FALSE;
    }

    /**
     * Initialization.
     *
     * @return AMI_Module_FormFrn
     */
    public function init(){
        AMI_Event::addHandler(
            'dispatch_mod_action_form_read_all',
            array($this, AMI::actionToHandler('read_all')),
            $this->getModId()
        );
        return parent::init();
    }

    /**
     * Save action dispatcher.
     *
     * @param  array &$aEvent  Event data
     * @return void
     * @exitpoint
     */
    protected function _save(array &$aEvent){
        /**
         * @var AMI_Response
         */
        $oResponse = $aEvent['oResponse'];
        /**
         * @var AMI_Mod
         */
        $oModController = $aEvent['oController'];

        $id = AMI::getSingleton('env/request')->get('id');
        $aStatusMessage = array();
        try{
            $aRequest = $this->convertRequestDates(AMI::getSingleton('env/request')->getScope());
            $this->oItem = $this->getModel()->getItem()->setValues($aRequest);

            $oSession = AMI::getSingleton('env/session');
            if(!$oSession->isStarted()){
                $oSession->start();
            }
            $oUser = $oSession->getUserData();
            $userId = ($oUser) ? $oUser->getId() : null;
            $recipientId = AMI::getSingleton('env/request')->get('id_recipient');

            // User can not send message to himself or to a system user
            if(($userId != $recipientId ) && ($recipientId != 0)){
                $this->oItem->setValues(
                    array(
                        'id_owner'      => $userId,
                        'id_sender'     => $userId,
                        'id_recipient'  => $recipientId,
                        'is_read'       => 1
                    )
                );
                $this->oItem->save();
            }
            $id = $this->oItem->getId();
            if($id !== $this->oItem->getEmptyId()){
                // Success

                // Create a copy for the recipient
                $oModelItem = $this->getModel()->getItem()->setValues($aRequest);
                $oModelItem->setValues(
                    array(
                        'id_owner'      => $recipientId,
                        'id_sender'     => $userId,
                        'id_recipient'  => $recipientId,
                        'id_body'       => $this->oItem->id_body
                    )
                );
                $oModelItem->save();

                // Send notification to the recipient
                if($oModelItem->id){
                    PrivateMessages_EmailNotifier::send($oModelItem->id);
                }

                // Check the contact list and add corresponding contacts if not found
                $oDB = AMI::getSingleton('db');
                $query =
                    "SELECT id, id_owner, id_member, nickname, is_contact, is_deleted " .
                        "FROM `%s` " .
                        "WHERE " .
                        "(id_owner=%s AND id_member=%s) OR " .
                        "(id_owner=%s AND id_member=%s) OR " .
                        "(id_owner=%s AND id_member=%s) OR " .
                        "(id_owner=%s AND id_member=%s)";
                $aResult = $oDB->select(
                    DB_Query::getSnippet($query)
                    ->plain(AMI::getResourceModel('private_message_contacts/table')->getTableName())
                    ->plain($userId)->plain($recipientId)
                    ->plain($recipientId)->plain($userId)
                    ->plain($userId)->plain($userId)
                    ->plain($recipientId)->plain($recipientId)
                );
                $bUserUserPresent = FALSE;
                $bRecepientRecepientPresent = FALSE;
                $bRecepientUserPresent = FALSE;
                $bUserRecepientPresent = FALSE;
                foreach($aResult as $aRow){
                    if($aRow['id_owner'] === $aRow['id_member']){
                        if($aRow['id_owner'] == $userId){
                            $bUserUserPresent = TRUE;
                        }
                        if($aRow['id_owner'] === $recipientId){
                            $bRecepientRecepientPresent = TRUE;
                        }
                    }else{
                        if($aRow['id_owner'] === $userId){
                            $bUserRecepientPresent = TRUE;
                            // Mark contact as active contact
                            if(!$aRow['is_contact']){
                                $oContact = AMI::getResourceModel('private_message_contacts/table')->find($aRow['id']);
                                $oContact->is_contact = 1;
                                $oContact->save();
                            }
                        }
                        if($aRow['id_owner'] === $recipientId){
                            $bRecepientUserPresent = TRUE;
                        }
                    }
                }

                // Create absent contacts
                if(!$bUserUserPresent){
                    $this->_createContact($userId, $userId);
                }
                if(!$bRecepientRecepientPresent){
                    $this->_createContact($recipientId, $recipientId);
                }
                if(!$bUserRecepientPresent){
                    $this->_createContact($userId, $recipientId, TRUE);
                }
                if(!$bRecepientUserPresent){
                    $this->_createContact($recipientId, $userId);
                }

                $oResponse->setMessage('form_item_created', self::SAVE_SUCCEED);
                $aStatusMessage = array('status_add', AMI_Response::STATUS_MESSAGE);
            }else{
                // Fail
                $oResponse->setMessage('form_item_not_created', self::SAVE_FAILED);
                $aStatusMessage = array('status_add_fail', AMI_Response::STATUS_MESSAGE_ERROR);
            }
        }catch(AMI_ModTableItemException $oException){
            // Fail
            d::pr($oException->getData(), 'AMI_ModTableItemException caught on item save');
            if($id){
                $oResponse->setMessage('form_item_not_saved', self::SAVE_FAILED);
                $aStatusMessage = array('status_apply_fail', AMI_Response::STATUS_MESSAGE_ERROR);
            }else{
                $oResponse->setMessage('form_item_not_created', self::SAVE_FAILED);
                $aStatusMessage = array('status_add_fail', AMI_Response::STATUS_MESSAGE_ERROR);
            }
        }

        if(!empty($aStatusMessage)){
            $oResponse->addStatusMessage($aStatusMessage[0], array(), $aStatusMessage[1]);
        }

        /**
         * @var AMI_RequestHTTP
         */
        $oRequest = AMI::getSingleton('env/request');
        $oRequest->set('applied_id', $id);
        if($oRequest->get('return_type', 'current') === 'new'){
            $oRequest->set('id', 0);
            $this->oItem = null;
        }elseif($id){
            $oRequest->set('id', $id);
        }
        /**
         * Processing controller actions of the AMI_Mod module.
         *
         * @event      dispatch_mod_action_form_edit $modId
         * @eventparam string modId  Module id
         * @eventparam AMI_Mod oController  Module controller object
         * @eventparam string tableModelId  Table Model Id
         * @eventparam AMI_Request oRequest  Request object
         * @eventparam AMI_Response oResponse  Response object
         * @eventparam string action  Action Name
         */
        AMI_Event::fire('dispatch_mod_action_form_edit', $aEvent, $this->getModId());
    }

    /**
     * Dispatch read_all action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function dispatchReadAll($name, array $aEvent, $handlerModId, $srcModId){
        $oDB = AMI::getSingleton('db');
        $oSession = AMI::getSingleton('env/session');
        if(!$oSession->isStarted()){
            $oSession->start();
        }
        $oUser = $oSession->getUserData();
        $userId = $oUser ? (int)$oUser->getId() : null;

        if($userId){
            // Authorized user only
            $oDB->query(
                DB_Query::getSnippet('UPDATE `%s` SET is_read=1 WHERE id_owner=%s')
                ->plain($this->getModel()->getTableName())
                ->plain($userId)
            );
            $oResponse = $aEvent['oResponse'];
            $oResponse->setType('JSON');
            $oResponse->setMessage('form_item_created', self::SAVE_SUCCEED);
            $oResponse->addStatusMessage('status_marked_as_read', array(), AMI_Response::STATUS_MESSAGE);
        }
        $this->displayView();
        return $aEvent;
    }

    /**
     * Creates a record in `cms_private_message_contacts` table.
     *
     * @param  int $idOwner     Owner's id
     * @param  int $idMember    Member's id
     * @param  bool $isContact  Mark as an active contact
     * @return void
     * @amidev
     */
    protected function _createContact($idOwner, $idMember, $isContact = FALSE){
        $oSession = AMI::getSingleton('env/session');
        if(!$oSession->isStarted()){
            $oSession->start();
        }
        $oUser = $oSession->getUserData();
        $oMember =
            $oUser->getId() == $idMember
                ? $oUser
                : AMI::getResourceModel('users/table')->find($idMember, array('id', 'firstname', 'lastname', 'login', 'nickname'));
        $oContact = AMI::getResourceModel('private_message_contacts/table')->getItem();
        $oContact->setValues(
            array(
                'id_owner'   => (int)$idOwner,
                'id_member'  => (int)$idMember,
                'nickname'   => AMI::getResource($this->getModId() . '/user/handler')->getUserNickname($oMember),
                'is_contact' => $isContact ? 1 : 0,
                'is_deleted' => 0
            )
        );
        $oContact->save();
    }
}

/**
 * AmiAsync/PrivateMessages configuration front form component view.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_FormViewFrn extends Hyper_AmiAsync_FormViewFrn{
    /**
     * Locale filename.
     *
     * @var string
     */
    protected $localeFileName = 'templates/lang/modules/private_messages_form.lng';

    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        $this->addTemplate('templates/modules/private_messages_form.tpl');

        $this->addField(array('name' => 'id', 'type' => 'hidden'));
        $this->addField(array('name' => 'mod_action', 'value' => 'form_save', 'type' => 'hidden'));
        $this->addField(array('name' => 'header'));

        $recipientId = AMI::getSingleton('env/request')->get('recipient', false);
        $oRecipient = AMI::getResourceModel('users/table')->find($recipientId);
        $recipientExists = $oRecipient->id > 0;
        $hasId = AMI::getSingleton('env/request')->get('id', false);
        if($recipientId && !$recipientExists){
            AMI_Event::addHandler('on_form_fields_form', array($this, 'handleFormFieldsUserDeleted'), $this->getModId());
        }
        if($recipientExists && !$hasId){
            $this->addField(array('name' => 'id_recipient', 'type' => 'hidden', 'value' => $recipientId, 'position' => 'header.before'));
            AMI_Event::addHandler('on_before_form_field_{id_recipient}', array($this, 'handleIdRecipient'), $this->getModId());
            AMI_Event::addHandler('on_form_fields_form', array($this, 'handleFormFields'), $this->getModId());
        }elseif($hasId){
            AMI_Event::addHandler('on_form_fields_form', array($this, 'handleFormFieldsWithId'), $this->getModId());
        }else{
            $oUser = AMI::getSingleton('env/session')->getUserData();
            $userId = ($oUser) ? $oUser->getId() : null;
            $oContacts = AMI::getResourceModel('private_message_contacts/table')
                ->getList()
                ->addColumns(array('id_member', 'nickname'))
                ->addWhereDef(DB_Query::getSnippet(' AND id_owner = %s AND is_contact = 1 AND is_deleted = 0')->q($userId))
                ->load();
            $aData = array();
            foreach($oContacts as $oContact){
                if($oContact->id_member && $oContact->nickname){
                    $aData[] = array(
                        'name'  => $oContact->nickname,
                        'value' => $oContact->id_member
                    );
                }
            }
            $this->addField(array('name' => 'id_recipient', 'type' => 'select', 'data' => $aData, 'position' => 'header.before'));
        }
        $this->addField(array('name' => 'b_body', 'type' => 'bb', 'cols' => 80, 'rows' => 10, 'position' => 'header.after', 'validate' => array('filled')));
        return $this;
    }

    /**
     * Handle id_recipient field.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleIdRecipient($name, array $aEvent, $handlerModId, $srcModId){
        $userId = isset($aEvent['aField']['value']) ? $aEvent['aField']['value'] : 0;
        if($userId){
            $oUser = AMI::getResourceModel('users/table')->find($userId);
            if($oUser->id){
                $aEvent['aScope']['username'] =
                    AMI::getResource($handlerModId . '/user/handler')
                        ->getUserNickname($oUser);
            }
        }
        return $aEvent;
    }

    /**
     * Handle form fields if user was deleted.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleFormFieldsUserDeleted($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['aScope']['inbox'] = AMI_Registry::get('private_messages_mode', FALSE) == 'inbox';
        $aEvent['aScope']['formDisplay'] = FALSE;
        $aEvent['aScope']['user_was_deleted'] =  TRUE;
        return $aEvent;
    }

    /**
     * Handle form fields in new mode.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleFormFields($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['aScope']['inbox'] = AMI_Registry::get('private_messages_mode', FALSE) == 'inbox';
        $aEvent['aScope']['formDisplay'] = TRUE;
        $oRequest = AMI::getSingleton('env/request');
        $recipientId = $oRequest->get('recipient', FALSE);
        if($recipientId){
            $oUser = AMI::getSingleton('env/session')->getUserData();
            $userId = ($oUser) ? $oUser->getId() : null;
            $aEvent['aScope']['message_to_yourself'] = $recipientId == $userId;
        }
        return $aEvent;
    }

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
        $id = AMI::getSingleton('env/request')->get('id');
        $aEvent['aScope'] += array('msg_restricted' => FALSE);
        if($id){
            $oUser = AMI::getSingleton('env/session')->getUserData();
            $userId = ($oUser) ? $oUser->getId() : null;
            if($userId){
                $oUser = AMI::getResourceModel('users/table')->find($userId, array('*'));
            }
            // All fields are required to display the message!
            $oModel = AMI::getResourceModel($handlerModId . '/table')->find($id, array('*'));
            if($oModel->id_owner != $userId){
                $aEvent['aScope'] = array('msg_restricted' => TRUE);
                return $aEvent;
            }
            // Mark as read
            if(!$oModel->is_read){
                $oModel->is_read = 1;
                $oModel->save();
            }

            $currentMode = AMI_Registry::get('private_messages_mode');
            $aEvent['aScope'][$currentMode] = TRUE;

            $correspondent = 0;
            if($currentMode == 'sent'){
                $oContact =
                    AMI::getResourceModel('private_message_contacts/table')
                        ->find($oModel->recipient_id, array('id', 'is_deleted', 'id_member'));
                if(!$oContact->is_deleted){
                    $correspondent = $oContact->id_member;
                }
            }

            $canReply = ($currentMode == 'inbox') && ($oModel->sender_id != $userId) && (!$oModel->is_broadcast);
            if($canReply){
                $oContact =
                    AMI::getResourceModel('private_message_contacts/table')
                        ->find($oModel->sender_id, array('id', 'is_deleted', 'id_member'));
                if($oContact->is_deleted){
                    $canReply = FALSE;
                }else{
                    $correspondent = $oContact->id_member;
                }
            }

            $prefix = '';
            $lang = AMI_Registry::get('lang');
            $isMultilang = AMI::getOption('core', 'allow_multi_lang'); // fast env option
            if($isMultilang){
                $prefix = $lang . '/';
            }

            $isFront = (AMI_Registry::get('side') != 'adm');
            $oPrivateMessagesView = AMI::getSingleton('private_messages/service/view');
            if($oModel->is_broadcast && defined('AMI_PRIVATE_MESSAGE_BROADCAST_SERVICE_SENDER_NAME')){
                $sender = AMI_PRIVATE_MESSAGE_BROADCAST_SERVICE_SENDER_NAME;
            }else{
                $sender =
                    $oModel->id_owner == $oModel->id_sender
                        ? AMI::getResource($handlerModId . '/user/handler')->getUserNickname($oUser)
                        : $oModel->sender_nickname;
            }
            $aMessage = array(
                'msg_id'        => $oModel->id,
                // 'msg_is_admin'  => $oModel->is_broadcast,
                'msg_sender'    => $sender,
                'msg_recipient' =>
                $oModel->id_owner == $oModel->id_recipient
                    ? AMI::getResource($handlerModId . '/user/handler')->getUserNickname($oUser)
                    : $oModel->recipient_nickname,
                'msg_header'    => $oModel->header,
                'msg_body'      => $oModel->b_body,
                'msg_datetime'  => AMI_Lib_Date::formatDateTime($oModel->date_created, AMI_Lib_Date::FMT_BOTH),
                'username'      => $oModel->sender_nickname,
                'msg_can_reply' => $canReply,
                'msg_user_id'   => $correspondent,
                'members_link'  => $isFront ? $prefix . AMI_PageManager::getModLink('members', $lang) : '',
                'forum_link'    => $isFront ? $prefix . AMI_PageManager::getModLink('forum', $lang) : '',
                'send_pm_link'  => $oPrivateMessagesView->getUserSendMessageLink($correspondent, $lang),
                'is_front'      => $isFront,
                'is_broadcast'  => $oModel->is_broadcast
            );
            $aEvent['aScope'] += $aMessage;
            $aEvent['oFormView']->addField(array('name' => 'id_recipient', 'type' => 'hidden', 'value' => $oModel->id_sender, 'position' => 'header.before'));
            $header = html_entity_decode($oModel->header);
            if(strpos($header, 'Re: ') !== 0){
                $header = 'Re: ' . $header;
            }
            $aEvent['oFormView']->addField(array('name' => 'header', 'value' => $header));
            $aEvent['oFormView']->addField(array('name' => 'b_body', 'type' => 'bb', 'cols' => 80, 'rows' => 10, 'position' => 'header.after', 'value' => str_replace(array('&#035;', '&#037;', '&#039;'), array('#', '%', '\''), $oModel->b_body)));
        }
        return $aEvent;
    }
}

/**
 * AmiAsync/PrivateMessages configuration front filter component action controller.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_FilterFrn extends Hyper_AmiAsync_FilterFrn{
    /**
     * Initialization.
     *
     * @return PrivateMessages_FilterFrn
     */
    public function  init(){
        // Hack to update filter values to the real ones
        $oRequest = AMI::getSingleton('env/request');
        $oSession = AMI::getSingleton('env/session');
        if(!$oSession->isStarted()){
            $oSession->start();
        }
        $oUser = $oSession->getUserData();
        $userId = ($oUser) ? $oUser->getId() : null;
        $bFilterSelected = $oRequest->get('inbox') || $oRequest->get('sent') || $oRequest->get('deleted');
        if($oRequest->get('sent')){
            $oRequest->set('sent', $userId);
        }
        if(!$bFilterSelected || $oRequest->get('inbox')){
            $oRequest->set('inbox', $userId);
        }

        return parent::init();
    }
}

/**
 * AmiAsync/PrivateMessages configuration front filter component view.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_FilterViewFrn extends Hyper_AmiAsync_FilterViewFrn{
    /**
     * Default private messages filter placeholders
     *
     * @var array
     */
    protected $aPlaceholders = array(
        '#filter',
        '#folders',
        'inbox', 'sent', 'deleted',
        'folders',
        '#common',
        'datefrom', 'dateto', 'is_read', 'header',
        'common',
        'filter'
    );

    /**
     * Template filename
     *
     * @var string
     */
    protected $tplFileName = 'templates/modules/private_messages_filter.tpl';

    /**
     * Locale filename.
     *
     * @var string
     */
    protected $localeFileName = 'templates/lang/modules/private_messages_filter.lng';
}

/**
 * AmiAsync/PrivateMessages configuration item list component filter model.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_FilterModelFrn extends Hyper_AmiAsync_FilterModelFrn{
    /**
     * Constructor.
     */
    public function __construct(){
        $this->addViewField(
            array(
                'name'          => 'inbox',
                'type'          => 'checkbox',
                'flt_default'   => '0',
                'flt_condition' => '=',
                'flt_column'    => 'id_recipient',
                'disable_empty' => true,
                'act_as_int'    => true
            )
        );
        $this->addViewField(
            array(
                'name'          => 'sent',
                'type'          => 'checkbox',
                'flt_default'   => '0',
                'flt_condition' => '=',
                'flt_column'    => 'id_sender',
                'disable_empty' => true,
                'act_as_int'    => true
            )
        );
        $this->addViewField(
            array(
                'name'          => 'deleted',
                'type'          => 'checkbox',
                'flt_default'   => '0',
                'flt_condition' => '=',
                'flt_column'    => 'is_deleted',
                'act_as_int'    => true
            )
        );
        $this->addViewField(
            array(
                'name'          => 'datefrom',
                'type'          => 'datefrom',
                'flt_type'      => 'date',
                'flt_default'   => AMI_Lib_Date::formatUnixTime(AMI_Lib_Date::UTIME_MIN),
                'flt_condition' => '>=',
                'flt_column'    => 'date_created'
            )
        );
        $this->addViewField(
            array(
                'name'          => 'dateto',
                'type'          => 'dateto',
                'flt_type'      => 'date',
                'flt_default'   => AMI_Lib_Date::formatUnixTime(AMI_Lib_Date::UTIME_MAX),
                'flt_condition' => '<=',
                'flt_column'    => 'date_created'
            )
        );
        $this->addViewField(
            array(
                'name'          => 'is_read',
                'type'          => 'checkbox',
                'flt_default'   => 0,
                'flt_condition' => '>=',
                'flt_column'    => 'is_read',
                'act_as_int'    => true
            )
        );
        $this->addViewField(
            array(
                'name'          => 'header',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'header'
            )
        );
    }

    /**
     * Adds current user id as id_owner.
     *
     * @param  string $field  Field name
     * @param  array  $aData  Filter data
     * @return array
     */
    protected function processFieldData($field, array $aData){
        if(in_array($field, array('inbox', 'sent', 'deleted'))){
            if($aData['value']){
                $oSession = AMI::getSingleton('env/session');
                if(!$oSession->isStarted()){
                    $oSession->start();
                }
                $oUser = $oSession->getUserData();
                $userId = ($oUser) ? $oUser->getId() : -1;
                $aData['exception'] = " AND (i.`id_owner` = " . $userId . ") ";
                if($field == 'sent'){
                    $aData['exception'] .= " AND (i.`is_broadcast` = 0) ";
                }
            }
        }elseif(($field == 'is_read') && ($aData['value'] == 0)){
            $aData['skip'] = true;
        }elseif(($field == 'is_read') && ($aData['value'] == 1)){
            $aData['forceSQL'] = " AND (i.`is_read` = 0) ";
        }
        return $aData;
    }
}

/**
 * AmiAsync/PrivateMessages configuration front list component action controller.
 *
 * @category   AMI
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_ListFrn extends Hyper_AmiAsync_ListFrn{
    /**
     * List actions controller resource id
     *
     * @var string
     */
    protected $listActionsResId = 'private_messages/list_actions/controller/frn';

    /**
     * List group actions controller resource id
     *
     * @var string
     */
    protected $listGrpActionsResId = 'private_messages/list_group_actions/controller/frn';

    /**
     * Initialization.
     *
     * @return PrivateMessages_ListFrn
     */
    public function init(){
        $this->addGroupActions(
            array(
                array(self::REQUIRE_FULL_ENV . 'read', 'read_section'),
            )
        );
        parent::init();
        $this->addJoinedColumns(array('nickname', 'is_deleted'), 'sender');
        $this->addJoinedColumns(array('nickname', 'is_deleted'), 'recipient');
        $this->addHiddenAction(self::REQUIRE_FULL_ENV . 'unread');
        return $this;
    }
}

/**
 * AmiAsync/PrivateMessages configuration front list actions controller.
 *
 * @category   AMI
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_ListActionsFrn extends Hyper_AmiAsync_ListActionsFrn{
    /**
     * Dispatches 'delete' action.
     *
     * Deletes item.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchDelete($name, array $aEvent, $handlerModId, $srcModId){
        $oItem = $this->getItem($this->getRequestId(), array('id', 'id_owner', 'is_deleted', 'id_body'));
        $oUser = AMI::getSingleton('env/session')->getUserData();
        $userId = ($oUser) ? (int)$oUser->getId() : null;
        $statusMsg = 'status_del_fail';
        if($userId && ($oItem->id_owner == $userId)){
            if($oItem->is_deleted){
                $oItem->delete();
                if(!$oItem->getId()){
                    $statusMsg = 'status_del';
                }
            }else{
                $oItem->is_deleted = 1;
                $oItem->save();
                $statusMsg = 'status_del_moved';
            }
        }
        $aEvent['oResponse']->addStatusMessage($statusMsg);
        $this->refreshView();
        return $aEvent;
    }

    /**
     * Unread action handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchUnread($name, array $aEvent, $handlerModId, $srcModId){
        $oItem = $this->getItem($this->getRequestId(), array('id', 'id_owner', 'id_body'));
        $oUser = AMI::getSingleton('env/session')->getUserData();
        $userId = ($oUser) ? (int)$oUser->getId() : null;
        if($userId && ($oItem->id_owner == $userId)){
            // Authorized user only
            $oItem->is_read = 0;
            $oItem->save();
        }
        $aEvent['oResponse']->addStatusMessage('status_unread');
        $this->refreshView();
        return $aEvent;
    }
}

/**
 * AmiAsync/PrivateMessages configuration front list group actions controller.
 *
 * @category   AMI
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_ListGroupActionsFrn extends Hyper_AmiAsync_ListGroupActionsFrn{
    /**
     * Dispatches group 'delete' action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchGrpDelete($name, array $aEvent, $handlerModId, $srcModId){
        $count = 0;
        $oUser = AMI::getSingleton('env/session')->getUserData();
        $userId = $oUser ? (int)$oUser->getId() : null;
        foreach($this->getRequestIds() as $id){
            $oItem = $this->getItem($id, array('id', 'id_owner', 'is_deleted', 'id_body'));
            if($userId && ($oItem->id_owner == $userId)){
                if($oItem->is_deleted){
                    $oItem->delete();
                }else{
                    $oItem->is_deleted = 1;
                    $oItem->save();
                }
            }
            $count += 1;
        }
        $aEvent['oResponse']->addStatusMessage('status_grp_del', array('num_items' => $count));
        $this->refreshView();
        return $aEvent;
    }

    /**
     * Dispatches group 'read' action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchGrpRead($name, array $aEvent, $handlerModId, $srcModId){
        $oDB = AMI::getSingleton('db');
        $oUser = AMI::getSingleton('env/session')->getUserData();
        $userId = $oUser ? (int)$oUser->getId() : null;
        if($userId){
            // Authorized user only
            $oDB->query(
                DB_Query::getSnippet('UPDATE `%s` SET is_read=1 WHERE id_owner=%s AND id IN (%s)')
                ->plain(AMI::getResourceModel($handlerModId . '/table')->getTableName())
                ->plain($userId)
                ->implode($this->getRequestIds())
            );
            $aEvent['oResponse']->addStatusMessage('status_marked_as_read', array(), AMI_Response::STATUS_MESSAGE);
        }
        $this->refreshView();
        return $aEvent;
    }
}
/**
 * AmiAsync/PrivateMessages configuration front list component view.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_ListViewFrn extends Hyper_AmiAsync_ListViewFrn{
    /**
     * Template filename
     *
     * @var string
     */
    protected $tplFileName = 'templates/modules/private_messages_list.tpl';

    /**
     * Locale filename.
     *
     * @var string
     */
    protected $localeFileName = 'templates/lang/modules/private_messages_list.lng';

    /**
     * List default elemnts template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#list_header',
        '#common',  'date_created', '#columns', 'columns', 'header', 'common',
        '#actions', 'front_view', 'edit', 'actions',
        'list_header'
    );

    /**
     * Initialization.
     *
     * @return PrivateMessages_ListViewFrn
     */
    public function init(){
        // Init columns
        $oRequest = AMI::getResource('env/request');
        $this
            ->addColumnType('is_read', 'hidden')
            ->addColumnType('is_broadcast', 'hidden')
            ->addColumnType('id_sender', 'hidden')
            ->addColumnType('sender_is_deleted', 'hidden')
            ->addColumnType('recipient_is_deleted', 'hidden')
            ->addColumn('date_created')
            ->addColumn('header')
            ->setColumnTensility('header')
            ->addSortColumns(
                array(
                    'sender_nickname',
                    'recipient_nickname',
                    'date_created',
                    'header'
                )
            );

        $isInbox = $oRequest->get('inbox') == 1 ? true : false;
        $isSent = $oRequest->get('sent') == 1 ? true : false;
        $isDeleted = $oRequest->get('deleted') == 1 ? true : false;
        if(!$isInbox && !$isSent && !$isDeleted){ // default tab "Inbox"
            $isInbox = true;
        }

        if(!$isInbox){
            $this
                ->addColumnType('recipient_nickname', 'text')
                ->setColumnTensility('recipient_nickname');
        }
        if(!$isSent){
            $this
                ->addColumnType('sender_nickname', 'text')
                ->setColumnTensility('sender_nickname');
        }

        // Format 'date_created' column in local date format
        $this->formatColumn(
            'date_created',
            array($this, 'fmtDateTime'),
            array(
                'format' => 'PHP'
            )
        );

        $this->formatColumn('date_created', array($this, 'fmtEditLink'));
        $this->formatColumn('header', array($this, 'fmtEditLink'));

        $this->addLocale($this->getTemplate()->parseLocale(AMI_iTemplate::LNG_MOD_PATH . '/' . $this->getModId() . '_list.lng'));
        AMI_Event::addHandler('on_list_columns', array($this, 'handleListColumns'), $this->getModId());
        AMI_Event::addHandler('on_list_body_row', array($this, 'handleListBodyRow'), $this->getModId());

        return parent::init();
    }

    /**
     * Adds default sorting by date_created.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListColumns($name, array $aEvent, $handlerModId, $srcModId){
        if(is_null($aEvent['aScope']['order_column'])){
            $aEvent['aScope']['order_column'] = 'date_created';
            $aEvent['aScope']['order_direction'] = 'desc';
        }
        return $aEvent;
    }

    /**
     * Inserts admin username if sender_id = 0.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListBodyRow($name, array $aEvent, $handlerModId, $srcModId){
        $mode = AMI_Registry::get('private_messages_mode', 'inbox');
        if($aEvent['oItem']->is_broadcast == 1){
            $aEvent['aScope']['sender_nickname'] =
                defined('AMI_PRIVATE_MESSAGE_BROADCAST_SERVICE_SENDER_NAME')
                    ? AMI_PRIVATE_MESSAGE_BROADCAST_SERVICE_SENDER_NAME :
                    $this->aLocale['username_admin'];
        }
        if(in_array($mode, array('inbox', 'sent'))){
            $oSession = AMI::getSingleton('env/session');
            if(!$oSession->isStarted()){
                $oSession->start();
            }
            $oUser = $oSession->getUserData();
            $ownerUsername = AMI::getResource($handlerModId . '/user/handler')->getUserNickname($oUser);
            $aEvent['aScope'][(($mode == 'inbox') ? 'recipient' : 'sender') . '_nickname'] = $ownerUsername;
        }
        return $aEvent;
    }

    /**
     * Edit link formatter.
     *
     * @param  string $value  Value to format
     * @param  array $aArgs   Arguments
     * @return string
     * @amidev
     */
    protected function fmtEditLink($value, array $aArgs){
        return '<a style="cursor:pointer;" class="amiModuleLink" data-ami-action="list_edit" data-ami-parameters="id=' . $aArgs['aScope']['id'] . '&ami_full=1">' . $value . '</a>';
    }
}
