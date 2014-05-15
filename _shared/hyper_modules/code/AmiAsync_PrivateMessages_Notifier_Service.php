<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   AmiAsync/PrivateMessages
 * @version   $Id: AmiAsync_PrivateMessages_Notifier_Service.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Private messages notifier service functions.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_Notifier_Service extends Hyper_AmiAsync_Service{
    /**
     * Dispatches messages notiffier action.
     *
     * @return void
     */
    public function dispatchRawAction(){
        global $CONNECT_OPTIONS;

        $CONNECT_OPTIONS['disable_cache_warn'] = TRUE;
        $messageIds = getenv('AMI_PRIVATE_MESSAGE_IDS_TO_NOTIFY');
        if($messageIds){
            // Send notification if module is not installed or 'notify_by_email' option is true (by design)
            /*
            if(!AMI::isModInstalled('private_messages') || AMI::getOption('private_messages', 'notify_by_email')){
                $messageIds = explode(',', $messageIds);
                foreach($messageIds as $messageId){
                    PrivateMessages_EmailNotifier::send($messageId);
                }
            }
            */
            $messageIds = explode(',', $messageIds);
            foreach($messageIds as $messageId){
                PrivateMessages_EmailNotifier::send($messageId);
            }
            return;
        }else{
            AMI::getSingleton('response')->HTTP->setServiceUnavailable(3600);
        }
    }
}
