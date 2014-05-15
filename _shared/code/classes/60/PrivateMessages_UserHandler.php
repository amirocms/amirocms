<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiAsync_PrivateMessages
 * @version   $Id: PrivateMessages_UserHandler.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Private messages user handler interface.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Service
 * @since      x.x.x
 * @amidev     Temporary
 */
interface PrivateMessages_iUserHandler{
    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Dispatches 'on_before_user_update' action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchUpdate($name, array $aEvent, $handlerModId, $srcModId);


    /**
     * Dispatches 'on_before_user_delete' action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchDelete($name, array $aEvent, $handlerModId, $srcModId);

    /**#@-*/
}

/**
 * Private messages user handler.
 *
 * @package    AmiAsync/PrivateMessages
 * @subpackage Service
 * @resource   private_messages/user/handler <code>AMI::getResource('private_messages/user/handler', array(), true)</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class PrivateMessages_UserHandler implements PrivateMessages_iUserHandler{
    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Dispatches 'on_before_user_update' action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @todo   Move models to /hyper_modules/code/AmiAsync_PrivateMessages_Table and change resource mapping
     */
    public function dispatchUpdate($name, array $aEvent, $handlerModId, $srcModId){
        $aUser = $aEvent['oUser']->getData();
        $aSourceUser = AMI::getResourceModel('users/table')->find($aUser['id'], array('*'))->getData();
        $aDiff = array_diff_assoc($aUser, $aSourceUser);
        $aUsedDiff = array();
        foreach(array('login', 'nickname', 'firstname', 'lastname', 'active') as $field){
            if(isset($aDiff[$field])){
                $aUsedDiff[$field] = $aDiff[$field];
            }
        }
        if(sizeof($aUsedDiff)){
            // There are changes affecting Private Messages module
            /**
             * @var AMI_iDB
             */
            $oDB = AMI::getSingleton('db');
            AMI::getResourceModel('private_messages/table'); // To autoload
            $table = AMI::getResourceModel('private_message_contacts/table')->getTableName();
            if(isset($aUsedDiff['active'])){
                $oQuery = DB_Query::getUpdateQuery(
                    $table,
                    array(
                        'is_deleted' => (int)!$aUsedDiff['active']
                    ),
                    'WHERE `id_member` = ' . $aUser['id']
                );
                $oDB->query($oQuery);
                unset($aUsedDiff['active']);
            }
            if(sizeof($aUsedDiff)){
                // nickname changes
                $aUsedDiff = $aUsedDiff + $aSourceUser;
                $oUser = AMI::getResourceModel('users/table')->getItem();
                $oUser->login = $aUsedDiff['login'];
                $oUser->nickname = $aUsedDiff['nickname'];
                $oUser->firstname = $aUsedDiff['firstname'];
                $oUser->lastname = $aUsedDiff['lastname'];
                $oQuery = DB_Query::getUpdateQuery(
                    $table,
                    array(
                        'nickname' => $this->getUserNickname($oUser)
                    ),
                    'WHERE `id_member` = ' . $aUser['id']
                );
                $oDB->query($oQuery);
            }
        }
        return $aEvent;
    }

    /**
     * Dispatches 'on_before_user_delete' action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @todo   Move model to /hyper_modules/code/AmiAsync_PrivateMessages_Table and change resource mapping
     */
    public function dispatchDelete($name, array $aEvent, $handlerModId, $srcModId){
        // Autoload model
        AMI::getResourceModel('private_messages/table');
        $oQuery = DB_Query::getUpdateQuery(
            AMI::getResourceModel('private_message_contacts/table')->getTableName(),
            array('is_deleted' => 1),
            'WHERE `id_member` = ' . $aEvent['userId']
        );
        AMI::getSingleton('db')->query($oQuery);
        return $aEvent;
    }

    /**#@-*/

    /**
     * Returns member's name to be used in module.
     *
     * @param  AmiUsers_Users_TableItem $oMember  Member to read data for
     * @return string
     */
    public function getUserNickname(AmiUsers_Users_TableItem $oMember){
        switch(AMI::getOption('common_settings', 'display_nickname_as')){
            case 'nickname':
                $nickname = $oMember->nickname;
                break;
            case 'username':
                $nickname = $oMember->login;
                break;
            case 'name_surname':
                $nickname = trim($oMember->firstname . ' ' . $oMember->lastname);
        }
        return $nickname;
    }
}
