<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Config_AmiAsync_PrivateMessages
 * @version   $Id: PrivateMessages_EmailNotifier.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Private messages email notifier.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Controller
 * @since      x.x.x
 * @amidev    Temporary
 */
class PrivateMessages_EmailNotifier{
    /**
     * Sends email notification for private message.
     *
     * @param  int $messageId  Message id
     * @return void
     */
    public static function send($messageId){
        $modId = 'private_messages';
        /**
         * @var PrivateMessages_Table
         */
        $oTable = AMI::getResourceModel($modId . '/table');
        /**
         * @var PrivateMessages_TableItem
         */
        $oMessage = $oTable->find($messageId);
        if(!$oMessage->getId()){
            return;
        }
        /**
         * @var Users_Table
         */
        $oUserTable = AMI::getResourceModel('users/table');
        $oRecipient = $oUserTable->find($oMessage->id_recipient);

        // Send message if recipient has e-mail
        if($oRecipient->email){
            $oView = AMI::getResource($modId . '/mail/view');

            // Build letter template scope
            $aScope =
                array(
                    // 'type'           => 'html',
                    'recipient_name' => trim($oRecipient->firstname . ' ' . $oRecipient->lastname),
                    'site_name'      => preg_replace(array ('/^\w+\:\/\//', '/\/.*$/'), '', $GLOBALS['ROOT_PATH_WWW']),
                    'site_url'       => $GLOBALS['ROOT_PATH_WWW'],
                    'broadcast'      => ''
                ) +
                AMI_Lib_Array::prependKeyPrefix($oMessage->getData(), 'message_') +
                AMI_Lib_Array::prependKeyPrefix($oRecipient->getData(), 'recipient_');
            $aScope['message_body'] = $aScope['message_b_body'];
            $aScope['message_body_plain'] = AMI_Lib_String::stripTags($aScope['message_body']);
            unset($aScope['message_b_body']);
            $messageURL = self::getModLink($modId);
            $aScope['message_url'] = $messageURL ? ($messageURL . '#mid=private_messages&id=' . $messageId) : '';
            $aScope['user_url'] = self::getModLink('members');

            if(defined('AMI_PRIVATE_MESSAGE_BROADCAST_SERVICE_NAME')){
                $aScope['service_name'] = AMI_PRIVATE_MESSAGE_BROADCAST_SERVICE_NAME;
            }
            if($oMessage->id_sender && $oMessage->id_sender != $oMessage->id_recipient){
                // Determine sender name
                $oSender = $oUserTable->find($oMessage->id_sender);
                if($oSender->getId()){
                    switch(AMI::getOption('common_settings', 'display_nickname_as')){
                        case 'nickname':
                            $sender = $oSender->nickname;
                            break;
                        case 'username':
                            $sender = $oSender->login;
                            break;
                        case 'name_surname':
                            $sender = trim($oSender->firstname . ' ' . $oSender->lastname);
                            break;
                    }
                    $aScope['sender_name'] = $sender;
                }
            }
            if($oMessage->is_broadcast && $oMessage->id_sender == $oMessage->id_recipient && defined('AMI_PRIVATE_MESSAGE_BROADCAST_SERVICE_SENDER_NAME')){
                $aScope['sender_name'] = AMI_PRIVATE_MESSAGE_BROADCAST_SERVICE_SENDER_NAME;
            }
            if($oMessage->is_broadcast){
                $aScope['broadcast'] = $oMessage->id_sender != $oMessage->id_recipient ? 'site' : 'service';
            }

            $oView->setScope($aScope);
            $oMail = new Mailer();
            $oMail->SenderAddress = self::getSenderEmail();
            $oMail->Subject = $oView->getPart('subject');
            $oMail->Body = $oView->getPart('body'); // . '<hr />' . $oMessage->getId() . '[' . $aScope['broadcast'] . ']';###
            $oMail->BodyFormat = 'html';
            /*
            $aScope['type'] = 'plain';
            $oView->setScope($aScope);
            $oMail->AddAttachments(
                array(
                    array(
                        'ftype'    => 'text/plain',
                        'fid'      => 'FILEID' . rand(10, 99) . '@' . uniqid(''),
                        'fbody'    => chr(0xEF) . chr(0xBB) . chr(0xBF) . $oView->getPart('body'), // UTF-8 BOM prefix
                        'friendly' => 'TEXT'
                    )
                )
            );
            */
            $oMail->Prepare();
            $oMail->RecipientAddress = $oRecipient->email;
            $oMail->Send();
        }
    }

    /**
     * Returns sender email according to single/shared mode.
     *
     * @return string
     */
    private function getSenderEmail(){
        static $domain, $senderEmail;

        if(is_null($senderEmail)){
            global $cms;
            if(($cms->Core->HostMode() & HOSTMODE_SHARED) && $cms->Core->IsInstalled('subs_send')){
                if(!isset($domain)){
                    $domain = parse_url($GLOBALS['ROOT_PATH_WWW']);
                    $domain = mb_strtolower($domain['host']);
                    if(mb_substr_count($domain, '.') > 1){
                        $domain = preg_replace('/^www[^\.]*\./', '', $domain);
                    }
                }
                $senderEmail = $cms->Core->getModOption('subs_send', 'from_mbox') . '@' . $domain;
            }else{
                $senderEmail = $cms->Core->GetOption('company_robot_email');
            }
        }
        return $senderEmail;
    }

    /**
     * Returns front page module link.
     *
     * @param  string $modId  Module id
     * @return string
     */
    private function getModLink($modId){
        global $cms;

        $oModule = &$cms->Core->GetModule($modId);
        return $oModule->IsPresentInPMandPublic() ? $GLOBALS['ROOT_PATH_WWW'] . $oModule->GetFrontLink() : '';
    }
}
