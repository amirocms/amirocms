<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   AmiAsync/PrivateMessages
 * @version   $Id: AmiAsync_PrivateMessages_Service.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Private Messages service functions.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_Service extends Hyper_AmiAsync_Service{
    /**
     * Dispatches raw service action.
     *
     * @return void
     */
    public function dispatchRawAction(){
        switch($_GET['action']){
            case 'get_local_sites_messages':
                $this->outLocalSitesMessages();
                break;
            case 'get_count':
            default:
                $this->outPrivateMessagesNum();
                break;
        }
    }

    /**
     * Dispatches service action.
     *
     * @param AMI_Request  $oRequest   Request
     * @param AMI_Response $oResponse  Response
     * @return void
     */
    public function dispatchAction(AMI_Request $oRequest, AMI_Response $oResponse){
        switch($_GET['action']){
            case 'view_messages':
            default:
                $this->goPrivateMessagesPage();
                break;
        }
    }

    /**
     * Redirect to the private messages page.
     *
     * @return void
     * @exitpoint
     */
    public function goPrivateMessagesPage(){
        // Get private messages module page link
        $modPMLink = '';
        $lang = !empty($_GET['lang_data']) ? $_GET['lang_data'] : 'ru';
        $modPMLink = AMI_PageManager::getModLink('private_messages', $lang);

        if(!empty($_GET['multi_lang'])){
            $modPMLink = $_GET['lang_data'] . '/' . $modPMLink;
        }
        $modPMLink .= '#mid=private_messages&inbox=1&is_read=1';

        // Redirect to the messages page
        $oResponse = AMI::getSingleton('response');
        $oResponse->HTTP->setRedirect('/' . $modPMLink);
        die;
    }

    /**
     * Get number of unread messages.
     *
     * @return void
     * @exitpoint
     */
    public function outPrivateMessagesNum(){
        global $db;

        // Get user id
        $userId = false;
        $cookieName = 'session_';
        if(!empty($_GET['scname'])){
            $cookieName = $_GET['scname'];
        }
        if(!empty($GLOBALS['_COOKIE'][$cookieName])){
            $res = $db->query("SELECT id_member FROM cms_sessions WHERE id = '" . mysql_real_escape_string($GLOBALS['_COOKIE'][$cookieName]) . "' AND expired > NOW() " . (!empty($GLOBALS['CONFIG_INI']['session']['session_no_ip_bind']) ? "" : (" AND ip = '" . $_SERVER['REMOTE_ADDR'] . "'")));
            if($res){
                $resData = $db->nextRecord();
                $userId = $resData['id_member'];
            }
        }

        // Get number of unread messages
        $res = -2;
        if(!$userId){
            $res = -2;
        }else{
            $res = 0;
            $res = $db->query("SELECT COUNT(id) as msg_num FROM cms_private_messages WHERE id_owner = ".intval($userId)." AND id_owner = id_recipient AND is_deleted = 0 AND is_read = 0");
            if($res){
                $resData = $db->nextRecord();
                $res = $resData['msg_num'];
            }
        }
        $this->send($res);
    }

    /**
     * Get local sites messages.
     *
     * @return void
     */
    public function outLocalSitesMessages(){
        global $db;
        $errParams = false;

        $lang = isset($_GET['lang']) ? $_GET['lang'] : 'ru';
        $lang = mb_strtolower($lang);
        if(!preg_match('/[a-z]{2,3}/', $lang)){
            $errParams = true;
        }
        if(isset($_GET['last_request']) && !is_numeric($_GET['last_request'])){
            $errParams = true;
        }else{
            $lastRequest = isset($_GET['last_request']) ? intval($_GET['last_request']) : 0;
        }
        usleep(200000);

        if(!isset($_GET['rnd1']) || !isset($_GET['rnd2'])){
            $errParams = true;
        }elseif(md5($_GET['rnd1'] . 'nS7elJs32sd') != $_GET['rnd2']){
            $errParams = true;
        }

        $bBroadcastAllowed =
            defined('AMI_PRIVATE_MESSAGE_BROADCAST_SERVICE_DOMAIN') &&
                (AMI_PRIVATE_MESSAGE_BROADCAST_SERVICE_DOMAIN === $GLOBALS['ROOT_PATH_WWW']);

        if($errParams || !$bBroadcastAllowed){
            AMI_Service::log("PrivateMessages_Service::outLocalSitesMessages() " . ($bBroadcastAllowed ? "wrong params or hack atempt" : "broadcast is not allowed") . "\nRequest:\n" . print_r($GLOBALS['_REQUEST'], true) . "\nServer env.:\n" . print_r($GLOBALS['_SERVER'], true), $GLOBALS['ROOT_PATH'] . '_admin/_logs/err.log');
            $this->send('', 503);
        }

        $aMessages = array();
        $db->query("SELECT id, date_created, UNIX_TIMESTAMP(date_created) AS date_created_ts, header, body FROM cms_private_message_local_sites WHERE lang = '" . mysql_real_escape_string($lang) . "' AND UNIX_TIMESTAMP(date_created) > " . mysql_real_escape_string($lastRequest));
        while($db->next_record()){
            $aMessages[$db->Record['id']] = array(
                'date_created' => $db->Record['date_created'],
                'date_created_ts' => $db->Record['date_created_ts'],
                'header' => $db->Record['header'],
                'body' => $db->Record['body']
            );
        }
        $this->send(sizeof($aMessages) ? json_encode($aMessages) : '');
    }
}
