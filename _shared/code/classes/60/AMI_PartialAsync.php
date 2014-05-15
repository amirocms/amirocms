<?php
/**
 * @copyright Amiro.CMS. All rights reserved.
 * @category  AMI
 * @package   Service
 * @version   $Id: AMI_PartialAsync.php 45856 2013-12-24 09:48:43Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Partial Async helper for 50 modules.
 *
 * @package Service
 * @since   x.x.x
 * @amidev
 */
class AMI_PartialAsync{
    /**
     * Instance
     *
     * @var AMI_PartialAsync
     */
    protected static $oInstance = null;

    /**
     * Module id
     *
     * @var string
     */
    protected $modId;

    /**
     * Flag specifying whether asyn is working or not
     *
     * @var bool
     */
    protected $isActive = false;

    /**
     * Gets instance of AMI_PartialAsync.
     *
     * @return AMI_PartialAsync
     */
    public static function getInstance(){
        if(is_null(self::$oInstance)){
            self::$oInstance = new self();
        }
        return self::$oInstance;
    }

    /**
     * Constructor.
     */
    public function __construct(){
        if((AMI::getSingleton('env/cookie')->get('ami_engine') !== 6) || AMI::getSingleton('env/request')->get('force_sync', FALSE)){
            return;
        }
    }

    /**
     * Initialize async integration.
     *
     * @param string $modId  Module id
     * @param Admin &$adm    Admin object
     * @return void
     */
    public function init($modId, Admin &$adm){

        $this->isActive = true;
        $this->modId = $modId;

        AMI_Registry::set('lang', $GLOBALS['cms']->lang);
        AMI_Registry::set('lang_data', $GLOBALS['cms']->lang_data);
        AMI_Registry::set('injected60', true);

        // Reinit cookies
        AMI::getSingleton('env/cookie')->getVarsCookie(true);

        $adm->Gui->addStyle("base.css");
        $adm->Gui->addScript('jsapi.php?mod60&compat');
        // Common scripts
        AMI_Registry::set(
            'AMI/resources/j/e',
            '_h=' . mb_substr(md5($adm->Core->GetModOption('ce', 'smile_collection')), 0, 10)
        );
    }

    /**
     * Finalize async integration.
     *
     * @param Admin &$adm      Admin object
     * @param mixed &$oModule  Module object
     * @param string &$html    HTML data
     * @return void
     */
    public function finalize(Admin &$adm, &$oModule, &$html){
        if(!$this->isActive){
            return;
        }
        $adm->Gui->addHtmlScript('var module60compatible = true;');
        $oRequest = AMI::getSingleton('env/request');
        $adm->Gui->addHtmlScript("var module50action = '" . $oRequest->get('action', '') . "';");
        $appliedId = 0;
        if(isset($oModule->Engine) && is_object($oModule->Engine) && $oModule->Engine->appliedId){
            $appliedId = $oModule->Engine->appliedId;
        }
        if(isset($adm->Vars['id']) && $adm->Vars['id'] && ($adm->Vars['id'] != $appliedId)){
            $appliedId = $adm->Vars['id'];
        }
        if(AMI_Registry::get('PAM_no_appliedId_needed', false)){
            $appliedId = false;
        }
        if($appliedId){
            $adm->Gui->addHtmlScript('var module50appliedId = ' . $appliedId . ';');
        }
        $adm->Gui->addBlock('mod_page', AMI_iTemplate::TPL_MOD_PATH . '/_engine.tpl');
        $subFolder = AMI_Registry::get('subfolder', '');
        $aData = array(
            'partial_async' => 1,
            'mod_id'        => $this->modId,
            'code_postfix'  => !empty($GLOBALS['_h']['code_postfix']) ? $GLOBALS['_h']['code_postfix'] . '/' : '',
            'subfolder'     => $subFolder
        );
        $html['list_table'] = $adm->Gui->get("mod_page", $aData);
        $html['filter'] = '';
    }

    /**
     * Returns initialized module id.
     *
     * @return string/null
     */
    public function getInitializedModId(){
        return $this->modId;
    }

    /**
     * Returns TRUE if P/A mode is active.
     *
     * @return bool
     */
    public function isActive(){
        return $this->isActive;
    }
}
