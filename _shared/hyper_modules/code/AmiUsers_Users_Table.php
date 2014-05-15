<?php
/**
 * AmiUsers/Users configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiUsers_Users
 * @version   $Id: AmiUsers_Users_Table.php 50563 2014-05-13 06:48:49Z Leontiev Anton $
 * @since     5.10.0
 */

/**
 * Users module table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * Users fields description:
 * - <b>login</b> - user login (string),
 * - <b>email</b> - user email (string),
 * - <b>firstname</b> - firstname (string),
 * - <b>lastname</b> - user lastname (string),
 * - <b>address1</b> - user address1 (string),
 * - <b>address2</b> - user address2 (string),
 * - <b>city</b> - user city (string),
 * - <b>state</b> - user state (string),
 * - <b>zip</b> - user zip code (string),
 * - <b>country</b> - user country (string),
 * - <b>phone</b> - user phone number (string),
 * - <b>phone_cell</b> - user cell phone number (string),
 * - <b>phone_work</b> - user work phone number (string),
 * - <b>company</b> - user company (string),
 * - <b>company_site</b> - user company site (string),
 * - <b>icq</b> - user icq number (string),
 * - <b>photo</b> - user photo (string),
 * - <b>forum_sign</b> - user forum sign (string),
 * - <b>forum_posts_num</b> - user forum post number (int),
 * - <b>info</b> - user info (string),
 * - <b>active</b> - flag specifying if user account is active (0/1),
 * - <b>ip</b> - user ip address (string),
 * - <b>balance</b> - user balance (in base currency, double),
 * - <b>eshop_discount</b> - user personal discount (in percent, double).
 * - <b>source_app_user_id</b> - User ID in remote application
 * - <b>source_app_id</b> - Id of corresponding remote application
 *
 * @package    Config_AmiUsers_Users
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      5.10.0
 */
class AmiUsers_Users_Table extends Hyper_AmiUsers_Table{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_members';

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model
     */
    public function __construct(array $aAttributes = array()){
        parent::__construct($aAttributes);

        $this->addSystemFields(
            array(
                'password', 'status', 'validate_key', 'validate_attempts', 'expire_date',
                'balance_history', 'uid', 'eshop_discount_type'
            )
        );

        $aRemap = array(
            'id'               => 'id',
            'public'           => AMI_ModTable::FIELD_DOESNT_EXIST,
            'hide_in_list'     => AMI_ModTable::FIELD_DOESNT_EXIST,

            'sublink'          => AMI_ModTable::FIELD_DOESNT_EXIST,
            'id_page'          => AMI_ModTable::FIELD_DOESNT_EXIST,
            'lang'             => 'lang',

            'login'           => 'username',
            'company_site'    => 'companyweb',
            'forum_posts_num' => 'posts_count',
            'date_created'    => 'date',
            'date_modified'   => 'modified_date'
        );
        $this->addFieldsRemap($aRemap);
        $this->setModId('users');
    }

    /**
     * Sets new table name for a model.
     *
     * @param string $tableName  New table name
     * @return void
     * @amidev Temporary
     */
    public function setTableName($tableName){
    }

    /**
     * Returns module id.
     *
     * @return string
     */
    /*
    protected function getModId(){
        return 'members';
    }
    */

    /**
     * Returns array of available fields.
     *
     * @param  bool $bAppendEventFields  Fire 'on_get_available_fields' event to append extension fields
     * @return array
     */
    public function getAvailableFields($bAppendEventFields = true){
        $aFields = parent::getAvailableFields($bAppendEventFields);
        $index = array_search('custom_data', $aFields);
        if($index !== false){
            unset($aFields[$index]);
        }
        return $aFields;
    }

    /**
     * Validate user by login and password.
     *
     * @param  string $login  User login
     * @param  string $password  User password
     * @param  bool $isHashed  True if password is hashed
     * @return bool
     * @since  5.12.0
     */
    public function validateLogin($login, $password, $isHashed = false){
        if(!$isHashed){
            $password = md5($password);
        }
        $oQuery =
            DB_Query::getSnippet(
                'SELECT `id` FROM %s ' .
                'WHERE `username` = %s AND `password` = %s LIMIT 1'
            )
            ->plain($this->tableName)
            ->q($login)
            ->q($password);
        $id = AMI::getSingleton('db')->fetchValue($oQuery);

        return !empty($id);
    }

    /**
     * Check if user exists or not.
     *
     * @param  string $login  User login
     * @return int|false
     * @since  5.12.0
     */
    public function checkUserExists($login){
        if(empty($login)){
            return true;
        }

        $oQuery =
            DB_Query::getSnippet(
                'SELECT `id` FROM %s ' .
                'WHERE `username` = %s LIMIT 1'
            )
            ->plain($this->tableName)
            ->q($login);
        $id = AMI::getSingleton('db')->fetchValue($oQuery);

        return empty($id) ? false : (int)$id;
    }

    /**
     * Check if user exists or not by remote credentials (source appplication id and source appplication user id).
     *
     * @param  string $sourceAppId  User source Id
     * @param  string $sourceAppUserId  User source username
     * @return int|false
     * @since  5.12.0
     */
    public function checkRemoteUserExists($sourceAppId, $sourceAppUserId){
        $oQuery =
            DB_Query::getSnippet(
                'SELECT `id` FROM %s ' .
                'WHERE `source_app_id` = %s AND `source_app_user_id` = %s LIMIT 1'
            )
            ->plain($this->tableName)
            ->q($sourceAppId)
            ->q($sourceAppUserId);
        $id = AMI::getSingleton('db')->fetchValue($oQuery);
        return empty($id) ? false : $id;
    }
}

/**
 * Users module table item model.
 *
 * @package    Config_AmiUsers_Users
 * @subpackage Model
 * @see        AMI_Session::getUserData()
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}//model/item')->getItem()*</code>
 * @since      5.10.0
 */
class AmiUsers_Users_TableItem extends Hyper_AmiUsers_TableItem{
    /**
     * Initializing table item data.
     *
     * @param AMI_ModTable $oTable  Module table model
     * @param DB_Query     $oQuery  Required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery = null){
        parent::__construct($oTable, $oQuery);

        foreach(array(
            'firstname', 'lastname', 'address1', 'address2', 'city', 'state',
            'phone', 'phone_cell', 'phone_work', 'company', 'company_site'
        ) as $field){
            $this->setFieldCallback($field, array($this, 'fcbHTMLEntities'));
        }
    }

    /**
     * Allow to save member data.
     *
     * Example:
     * <code>
     * $oUser = AMI::getResourceModel('users/table')->getItem();
     * $oUser->username = 'test' . rand(1,100);
     * $oUser->password = $oUser->generatePassword();
     * $oUser->firstname = '';
     * $oUser->lastname = '';
     * $oUser->email  = 'test' . rand(1,100) . '@test.com';
     *
     * $oUser->save();
     * </code>
     *
     * @param  bool $bSendMail  Send email to member if it new one or do not send.
     * @return Users_TableItem|false
     * @throws AMI_ModTableItemException  If member changes failed.
     * @since  5.12.0
     * @todo   Define AMI_ModTableItemException constant for the cases?
     */
    public function save($bSendMail = false){
        $oSession = AMI::getSingleton('env/session', array(true));
        /**
         * @var CMS_Member
         */
        $oMember = $oSession->getMember();
        $db = new DB_si;
        $oCMS = $GLOBALS['cms'];

        if(empty($this->id)){
            $genData = '';
            if($oMember->getLoginField() != 'username' && empty($this->aData[$oMember->getLoginField()])){
                $genData = 'username';
            }
            $aTmpUsedFields = $oMember->UsedFields;
            $aTmpOblogatoryFields = $oMember->ObligatoryFields;
            $oMember->setUsed(array_keys($this->aData), true, true, true);
            $oMember->setObligatory(array(), true, true, true);
            $result = $oMember->createMember($oCMS, $db, $this->aData, $bSendMail, $genData, false, isset($this->aData['email']) && $this->aData['email'] == '' ? true : false);
            $oMember->setUsed($aTmpUsedFields, true, true);
            $oMember->setObligatory($aTmpOblogatoryFields, true, true);
            unset($aTmpUsedFields, $aTmpOblogatoryFields);
            if(!$result){
                throw new AMI_ModTableItemException('Member creation failed');
                return false;
            }
            $oQuery =
                DB_Query::getSnippet(
                    'SELECT `id` FROM %s ' .
                    'WHERE `username` = %s LIMIT 1'
                )
                ->plain($this->oTable->getTableName())
                ->q($this->username);
                $this->id = AMI::getSingleton('db')->fetchValue($oQuery);
        }else{
            AMI_Event::addHandler('on_before_user_update', array($this, 'handleBeforeUserUpdateAddCustomFields'), AMI_Event::MOD_ANY);
            $result = $oMember->updateMember($oCMS, $db, $this->id, $this->aData);
            AMI_Event::dropHandler('on_before_user_update', array($this, 'handleBeforeUserUpdateAddCustomFields'), AMI_Event::MOD_ANY);
            if(!$result){
                throw new AMI_ModTableItemException('Member update failed');
            }
        }

        if(isset($this->source_app_id) && isset($this->source_app_user_id)){
            $oQuery =
                DB_Query::getUpdateQuery(
                    $this->oTable->getTableName(),
                    array(
                        'source_app_id'      => $this->source_app_id,
                        'source_app_user_id' => $this->source_app_user_id
                    ),
                    DB_Query::getSnippet('WHERE `username` = %s')->q($this->username)
                );
            AMI::getSingleton('db')->query($oQuery);
        }

        return $result;
    }

    /**
     * Appends custom fields in 6.0 environment.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleBeforeUserUpdateAddCustomFields($name, array $aEvent, $handlerModId, $srcModId){
        foreach($aEvent['aSourceData'] as $key => $value){
            if(preg_match('/^cf_/', $key)){ // TODO: get cf prefix from options
                $aEvent['aData'][$key] = trim($value);
            }
        }
        return $aEvent;
    }

    /**
     * Activate member without any confirm.
     *
     * @param  bool $bState  Bool, activate or deactivate.
     * @return Users_Table
     * @since  5.12.0
     * @todo   Define AMI_ModTableItemException constant for the case?
     */
    public function setActiveState($bState){
        if(!empty($this->id)){
            $oQuery =
                DB_Query::getUpdateQuery(
                    'cms_members',
                    array(
                        'active' => (int)$bState,
                        'status' => DB_Query::getSnippet('`status` | 2')
                    ),
                    DB_Query::getSnippet('WHERE `id` = %s')->q($this->id)
                );
            AMI::getSingleton('db')->query($oQuery);
        }else{
            throw new AMI_ModTableItemException('Member activate failed');
        }
        return $this;
    }


    /**
     * Now disallow to delete members data.
     *
     * @param  mixed $id  Primary key value of item
     * @return void
     * @amidev
     * @todo   Define AMI_ModTableItemException constant for the case?
     */
    public function delete($id = null){
        /*
        $oSession = AMI::getSingleton('env/session', array(true));
        $oMember = $oSession->getMember();
        if($oMember->isLoggedIn()){
            $oDB = new DB_si;
            $oCMS = $GLOBALS['cms'];

            if(empty($id))$id = $this->id;

            $oMember->User['username'] = 'delete';
            $result = $oMember->deleteMember($oCMS, $oDB, $id);
            if($result){
                return $result;
            }else{
                throw new AMI_ModTableItemException('Member deletion failed');
                return false;
            }
        }else{
            throw new AMI_ModTableItemException('Member deletion failed. Not logged in.');
            return false;
        }
        */
        throw new AMI_ModTableItemException('Delete method unavailable.');
    }

    /**
     * Loads data for $id or set new item data.
     *
     * @return Users_Table
     * @todo remove load($id) in next build
     */
    public function load(){
        if(func_num_args()){
            $id = func_get_arg(0);
            parent::load($id);
        }else{
            parent::load();
        }
        $this->prepareData();

        return $this;
    }

    /**
     * Sets data array and remap it.
     *
     * @param  array $aData    New data array
     * @param  bool  $bAppend  Append data to current data array or set new
     * @param  bool  $bSetPK   Set primary key of present in the data
     * @return Users_Table
     * @amidev
     */
    public function setDataAndRemap(array $aData, $bAppend = false, $bSetPK = false){
        parent::setDataAndRemap($aData, $bAppend, $bSetPK);

        $this->prepareData();

        return $this;
    }

    /**
     * Validates user by password.
     *
     * Return true if given password is password of current user.
     *
     * @param  string $password  User password
     * @param  bool   $isHashed  True if password is hashed
     * @return bool
     * @since  5.12.0
     */
    public function validatePassword($password, $isHashed = false){
        if(!$isHashed){
            $password = md5($password);
        }
        $oQuery =
            DB_Query::getSnippet(
                'SELECT `id` FROM %s ' .
                'WHERE `id` = %s AND `password` = %s LIMIT 1'
            )
            ->plain($this->oTable->getTableName())
            ->q($this->id)
            ->q($password);
        $id = AMI::getSingleton('db')->fetchValue($oQuery);

        return !empty($id);
    }

    /**
     * Set new user password.
     *
     * Return true if new password was set
     *
     * Example:
     * <code>
     * $id = ...;
     * $oUser = AMI::getResourceModel('users/table')->getItem($id)
     * $oUser->password = $oUser->generatePassword();
     * $oUser->savePassword();
     * </code>
     *
     * @param  bool $isHashed  True if password is hashed
     * @return bool
     * @since  5.12.4
     */
    public function savePassword($isHashed = false){
        if(mb_strlen($this->password)){
            // Damn #4873 compatibility
            if(!$isHashed){
                $this->password = str_replace(array('#', '%', '\''), array('&#035;', '&#037;', '&#039;'), htmlspecialchars(trim($this->password)));
            }
            $oSession = AMI::getSingleton('env/session', array(true));
            $oMember = $oSession->getMember();
            $oDB = new DB_si;
            $oMember->User['username'] = $this->aData['login'];
            $oMember->User['id'] = $this->aData['id'];
            $oMember->setPass($oDB, $this->password, $isHashed);
            return true;
        }
        return false;
    }

    /**
     * Generates new password.
     *
     * Example:
     * <code>
     * $id = ...;
     * $oUser = AMI::getResourceModel('users/table')->getItem($id)
     * $oUser->password = $oUser->generatePassword();
     * $oUser->savePassword();
     * </code>
     *
     * @param  int $length  Length of password
     * @return string
     * @since  5.12.0
     * @see    Users_TableItem::savePassword()
     */
    public function generatePassword($length = 7){
        return AMI::getSingleton('env/session')->getMember()->GenPassword($length);
    }

    /**
     * Returns users's model obligatory fields.
     *
     * @return array
     */
    public function getObligatoryFields(){
        return AMI::getSingleton('env/session')->getMember()->ObligatoryFields;
    }

    /**
     * Sets users's model obligatory fields.
     *
     * @param array $aFields  Array of fields
     * @return bool
     */
    public function setObligatoryFields(array $aFields){
        return AMI::getSingleton('env/session')->getMember()->setObligatory($aFields, true, true);
    }

    /**
     * Prepares data for public usage.
     *
     * @return void
     */
    private function prepareData(){
        if(!empty($this->aData['custom_data'])){
            $aCustomData = @unserialize($this->aData['custom_data']);
            if(is_array($aCustomData)){
                $this->aData += $aCustomData;
            }
        }
        $this->aData = array_diff_key($this->aData, array_flip($this->oTable->getSystemFields()));
        unset($this->aData['custom_data'], $this->aData['date_timestamp']);
    }
}

/**
 * Users module table list model.
 *
 * @package    Config_AmiUsers_Users
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table/model/list')->getList()*</code>
 * @since      5.10.0
 */
class AmiUsers_Users_TableList extends Hyper_AmiUsers_TableList{
}
