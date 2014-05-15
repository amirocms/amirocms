<?php
/**
 * Class AMI_MailQueue.
 *
 * Allows to add e-mails into the queue and send in background.
 *
 * Example:
 * <code>
 * $AMI_ENV_SETTINGS = array('mode' => 'full', 'disable_cache' => true);
 * require 'ami_env.php';
 *
 * $oResponse = AMI::getSingleton('response');
 * $oResponse->start();
 *
 * // Create AMI_Mail object
 * $oMail = new AMI_Mail('from@domain.name', 'Email subject', 'Email body');
 *
 * // Create array of recipients
 * $aRecipients = array(
 *     'user1@domain.name' => array('greeting' => 'Mr.', 'username' => 'Name1'),
 *     'user2@domain.name' => array('greeting' => 'Ms.', 'username' => 'Name2')
 * );
 *
 * // The fields in double pluses (++greeting++, ++username++) will be replaced with a user data from $aRecipients array
 * $oMail->body = 'Hello, ++greeting++ ++username++';
 *
 * // Create AMI_MailQueue object
 * $oMailQueue = new AMI_MailQueue();
 *
 * // Add AMI_Mail object into the queue
 * $oMailQueue->addMail($oMail, $aRecipients);
 *
 * // Add background process for sending e-mails in queue
 * $oMailQueue->addBackgroundProcess();
 *
 * $oResponse->write('The e-mail was added into the queue for sending in background.');
 * $oResponse->send();
 * </code>
 *
 * @package    Service
 * @since      6.0.6
 */
class AMI_MailQueue{
    /**
     * Constructor.
     */
    public function __construct(){
    }

    /**
     * Add e-mail into the queue.
     *
     * @param  AMI_Mail $oMail     AMI_Mail object
     * @param  array $aRecipients  Contains e-mails as keys and arrays of data to replace in letter
     * @return AMI_MailQueue
     */
    public function addMail($oMail, $aRecipients){
        global $db;

        $aMailData = array(
            'lang'    => AMI_Registry::get('lang_data'),
            'subject' => addslashes($oMail->subject),
            'headers' => addslashes(serialize($oMail->getHeaders())),
            'body'    => addslashes($oMail->body)
        );

        require_once $GLOBALS['CLASSES_PATH'] . 'CMS_MailQueue.php';
        CMS_MailQueue::addLetter($db, $aMailData, $aRecipients);

        return $this;
    }

    /**
     * Create background process for the mail queue.
     *
     * @param  bool $run  Instant run added process
     * @return AMI_MailQueue
     */
    public function addBackgroundProcess($run = FALSE){
        require_once $GLOBALS['CLASSES_PATH'] . 'CMS_BackgroundProcess.php';
        $process = new CMS_BackgroundProcess();
        $process->registerHandler('CMS_MailQueue::processQueue', '');
        if($run){
            $process->process('CMS_MailQueue::processQueue');
        }
        return $this;
    }
}
