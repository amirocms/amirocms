<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiForum_Forum
 * @version   $Id: AmiCommonMessage_Service.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Forum/Disussion/Guestbook service class.
 *
 * @package  Config_AmiForum_Forum
 * @since    x.x.x
 * @amidev   Temporary
 */
class AmiCommonMessage_Service{
    /**
     * Module id
     *
     * @var string
     */
    protected $modId;

    /**
     * System users
     *
     * @var array
     */
    protected $aSysUsers = array();

    /**
     * Flag specifying to display sys users as asministrators
     *
     * @var bool
     */
    protected $displaySysUserAsAdmin;

    /**
     * Nickname displaying mode
     *
     * @var string
     */
    protected $displayNicknameAs;

    /**
     * Template
     *
     * @var AMI_TemplateSystem
     */
    protected $oTpl;

    /**
     * Locales
     *
     * @var array
     */
    protected $aLocale;

    /**
     * Instance
     *
     * @var AmiCommonMessage_Service
     */
    private static $oInstance;

    /**
     * Returns an instance of AmiCommonMessage_Service.
     *
     * @param  array $aArgs  Constructor arguments
     * @return AmiCommonMessage_Service
     * @amidev
     */
    public static function getInstance(array $aArgs = array()){
        if(is_null(self::$oInstance)){
            self::$oInstance = new AmiCommonMessage_Service($aArgs[0]);
        }
        return self::$oInstance;
    }

    /**
     * Patches message/section author if empty.
     *
     * @param  string &$author  Author to patch
     * @return void
     */
    public function patchAuthor(&$author){
        if(trim($author) == ''){
            $author = $this->aLocale['empty_nickname'];
        }
    }

    /**
     * Patches message/section record author fields according to options.
     *
     * @param  array &$aRecord        Record
     * @param  bool $doResetMemberId  Flag used to reset member id
     * @return void
     */
    public function patchSysUser(array &$aRecord, $doResetMemberId = FALSE){
        if($this->displaySysUserAsAdmin){
            foreach(
                array (
                    'id_member'     => 'author',
                    'msg_id_member' => 'msg_author'
                ) as $memberIdKey => $authorKey
            ){
                if(isset($aRecord[$memberIdKey])){
                    $isAdmin = isset($this->aSysUsers[$aRecord[$memberIdKey]]);
                    $aRecord['is_admin'] = $isAdmin && $memberIdKey == 'id_member';
                    if($isAdmin){
                        $aRecord['source_' . $authorKey] = $aRecord[$authorKey];
                        $aRecord[$authorKey] = $this->oTpl->parse($this->modId . '_service:admin_nickname', array('author' => $this->aSysUsers[$aRecord[$memberIdKey]]));
                    }
                }
            }
        }
        if($doResetMemberId){
            $aRecord['id_member'] = 0;
        }
    }

    /**
     * Message column formatter.
     *
     * Converts smiles to BB-tags.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::formatColumn()
     */
    public function fmtSmilesToTags($value, array $aArgs){
        return AMI_Lib_BB::convertSmilesToTags($value);
    }

    /**
     * Singleton counstructor.
     *
     * @param string $modId  Module id
     */
    protected function __construct($modId){
        $this->modId = $modId;
        $this->displaySysUserAsAdmin = AMI::issetAndTrueOption($modId, 'sys_user_as_administration');
        $this->displayNicknameAs = AMI::getOption('common_settings', 'display_nickname_as');
        if($this->displaySysUserAsAdmin){
            $this->loadSysUsers();
        }
        $this->oTpl = AMI::getSingleton('env/template_sys');
        $this->oTpl->addBlock($this->modId . '_service', AMI_iTemplate::TPL_HYPER_PATH . '/ami_forum_forum_service.tpl');
        $this->aLocale = $this->oTpl->parseLocale(AMI_iTemplate::LNG_HYPER_PATH . '/ami_forum_forum_common.lng');
    }

    /**
     * Singleton cloning.
     */
    protected function __clone(){
    }

    /**
     * Loads available sys users.
     *
     * @return void
     */
    protected function loadSysUsers(){
        global $Core;

        /**
         * @var AMI_DB
         */
        $oDB = AMI::getSingleton('db');

        // get sys user info
        switch($this->displayNicknameAs){
            case 'nickname':
                $oQueryPart = DB_Query::getSnippet('`m`.`nickname`');
                break;
            case 'username':
                $oQueryPart = DB_Query::getSnippet('`m`.`username`');
                break;
            case 'name_surname':
                $oQueryPart = DB_Query::getSnippet("CONCAT(`m`.`firstname`, %s, `m`.`lastname`)")->q(' ');
        }
        $oQueryPart = DB_Query::getSnippet(', %s `user_caption` ')->plain($oQueryPart);

        if(empty($GLOBALS['BUILDER_VERSION']) || $GLOBALS['BUILDER_VERSION'] < 2){
            $oQuery =
                DB_Query::getSnippet(
                    "SELECT `hu`.`id_member`%s" .
                    "FROM `cms_host_users` `hu` " .
                    "LEFT JOIN `cms_members` `m` ON `m`.`id` = `hu`.`id_member` " .
                    "WHERE `hu`.`sys_user` = 1"
                )->plain($oQueryPart);
        }else{
            $oQuery =
                DB_Query::getSnippet(
                    "SELECT `c`.`id_admin` as `id_member`%s" .
                    "FROM `cms_hst_res_cms_inst` `c` " .
                    "LEFT JOIN `cms_members` `m` ON `m`.`id` = `c`.`id_admin` " .
                    "WHERE `c`.`is_sys` = 1"
                )->plain($oQueryPart);
        }
        $oRS = $oDB->select($oQuery);
        foreach($oRS as $aRecord){
            $this->aSysUsers[$aRecord['id_member']] = trim($aRecord['user_caption']);
        }

        // get users ids having admin login
        if($Core->isInstalled('sys_groups')){
            $oQuery =
                DB_Query::getSnippet(
                    "SELECT `hu`.`id_member`%s" .
                    "FROM `cms_sys_users` `hu` " .
                    "LEFT JOIN `cms_sys_groups` `sg` ON `sg`.`id` = `hu`.`id_group` " .
                    "LEFT JOIN `cms_members` `m` ON `m`.`id` = hu.`id_member` " .
                    "WHERE `sg`.`login` = 1"
                )->plain($oQueryPart);
            $oRS = $oDB->select($oQuery);
            foreach($oRS as $aRecord){
                $this->aSysUsers[$aRecord['id_member']] = trim($aRecord['user_caption']);
            }
        }
    }
}
