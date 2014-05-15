<?php
/**
 * AmiExt/Antispam extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiExt_Antispam
 * @version   $Id: AmiExt_Antispam_Frn.php 43147 2013-11-06 11:52:25Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/Antispam extension configuration front controller.
 *
 * @package    Config_AmiExt_Antispam
 * @subpackage Controller
 * @resource   ext_twist_prevention/module/controller/frn <code>AMI::getResource('ext_twist_prevention/module/controller/frn')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Antispam_Frn extends Hyper_AmiExt{
    /**
     * SPAM protection option source module id
     *
     * @var string
     */
    protected $optSrcModId = 'srv_twist_prevention';

    /**
     * SPAM protection default option source module id
     *
     * @var string
     */
    protected $defaultOptSrcModId = 'show_num_image';

    /**
     * Cookie name
     *
     * @var string
     */
    protected $cookieName = 'vid';

    /**
     * Cookie expiration time in seconds
     *
     * @var int
     */
    protected $cookieExpiration = 31536000; // 3600 * 24 * 365 seconds

    /**
     * Generated cookie value
     *
     * @var string
     */
    protected $generated;

    /**
     * State read from options
     *
     * @var array
     */
    protected $aState = array();

    /**
     * Flag specifying extension is used by category sumodule
     *
     * @var bool
     */
    protected $isCatMod;

    /**
     * Constructor.
     *
     * @param string  $modId        Module id
     * @param string  $optSrcId     Options source module id
     * @param AMI_Mod $oController  Module controller
     */
    public function __construct($modId, $optSrcId = '', AMI_Mod $oController = null){
        $this->isCatMod = preg_match('/_cat$/', $modId);

        parent::__construct($modId, $optSrcId, $oController);
    }

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Extension initialization.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Ext::__construct()
     * @see    AMI_Mod::init()
     */
    public function handlePreInit($name, array $aEvent, $handlerModId, $srcModId){
        // Do not initialize in fast environment
        if((AMI::getEnvMode() == 'fast') || !AMI::isModInstalled($this->optSrcModId)){
            return $aEvent;
        }

        global $cms;

        $modId = $aEvent['modId'];

        $this->aState['aVisitorsOriginality'] =
            array_flip(AMI::getOption($this->optSrcModId, 'visitors_originality_parameters'));

        $oRequest = AMI::getSingleton('env/request');
        $vid = $oRequest->getCookie($this->cookieName, FALSE);
        if(!$vid){
            $vid = md5(getenv('REMOTE_ADDR') . ':' . rand(0, 1000000) . ':' . microtime());
            SetLocalCookie($this->cookieName, $vid, time() + $this->cookieExpiration, '', TRUE);
            $cms->VarsCookie[$this->cookieName] = $vid;
            $this->generated = $vid;
        }
        $this->aState['vid'] = $vid;
        $this->aState['useJS'] = AMI::getOption($this->optSrcModId, 'use_js_protection');
        $this->aState['splitImage'] = AMI::getOption($this->optSrcModId, 'split_image');
        $this->aState['showCaptcha'] =
            AMI::getOption($modId, 'show_captcha') &&
            (
                !is_object($cms->Member) ||
                (
                    $cms->Member->isLoggedIn()
                        ? AMI::getOption($this->optSrcModId, 'show_captcha_for_registered_users')
                        : TRUE
                )
            );
        if($this->aState['useJS'] || $this->aState['showCaptcha']){
            $cms->Gui->addGlobalVars(
                array(
                    'EXTENSION_TWIST_PREVENTION' => '1',
                    'EXTENSION_TWIST_PREVENTION_' . mb_strtoupper($modId) => '1',
                    'ext_antispam_enabled' => TRUE // #CMS-11470
                )
            );
            if($this->aState['showCaptcha']){
                $cms->Gui->addScript('_js/md5.js');
                foreach(
                    array(
                        'captchaLength'     => 'image_digits_qty',
                        'captchaSymbolsSet' => 'image_symbols_set'
                    ) as $state => $optName
                ){
                    $optSource =
                        AMI::issetOption($this->optSrcModId, $optName)
                            ? $this->optSrcModId
                            : $this->defaultOptSrcModId;
                    $this->aState[$state] = AMI::getOption($optSource, $optName);
                }
            }

            $this->aState['checkOnClient'] = AMI::getOption($modId, 'js_checking');

            $oView = $this->getView('frn');
            $oView->setExt($this);
            AMI_Event::addHandler('dispatch_action', array($this, 'handleActions'), $modId);
            AMI_Event::addHandler('deprecated_v5_init_form_spam_protection', array($oView, 'handleGetCode'), $modId);

            $langFile = $cms->Gui->ParseLangFile('templates/lang/modules/' . $this->getExtId() . '.lng');
            $cms->AddMessages($langFile);
        }else{
            // #CMS-11470
            $cms->Gui->addGlobalVars(array('ext_antispam_enabled' => FALSE));
        }

        return $aEvent;
    }

    /**
     * Dispatch actions.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleActions($name, array $aEvent, $handlerModId, $srcModId){
        if(
            $this->isCatMod
                ? AMI_Registry::get('page/itemId', 0) > 0
                : AMI_Registry::get('page/itemId', 0) <= 0
        ){
            return $aEvent;
        }

        $action = $aEvent['action'];
        if(
            !AMI::issetProperty($srcModId, 'twist_actions') ||
            !in_array($aEvent['action'], AMI::getProperty($srcModId, 'twist_actions'))
        ){
            // Don't dispatch actions
            return $aEvent;
        }

        global $cms, $db;

        $twist = 0;
        if(checkProbability(0.001)){
            // delete outdated data
            $sql =
                "DELETE FROM `cms_twist_prevention` " .
                "WHERE " .
                // "    `ext_module` = '" . $moduleName . "' AND " .
                "`date` <= DATE_SUB(NOW(), INTERVAL " . AMI::getOption($this->optSrcModId, 'record_ttl') . ")";
            $db->query($sql);
            if(checkProbability(0.1)){
                $sql = "OPTIMIZE TABLES `cms_twist_prevention`";
                $db->query($sql);
            }
        }
        if(checkProbability(0.01)){
            // delete outdated data
            $sql =
                "DELETE FROM `cms_twist_prevention` " .
                "WHERE " .
                // "    `ext_module` = '" . $moduleName . "' AND " .
                "    `twist` = 0 AND " .
                "    `date` <= DATE_SUB(NOW(), INTERVAL " . AMI::getOption($srcModId, 'action_period') . ")";
            $db->query($sql);
        }
        $twistedAction = AMI::getOption($srcModId, 'generate_twist_action') ? $action . '_twist' : 'none';
        if(
            !AMI::getOption($this->optSrcModId, 'allow_disabled_cookies') &&
            !empty($this->generated)
        ){
            $aEvent['action'] = $twistedAction;
            $twistAlert = 'twist_enable_cookies';
            $reason = 'no_cookies';
        }
        $ip = "INET_ATON('" . getenv('REMOTE_ADDR') . "')";
        if(empty($this->generated)){
            $cookie =  "'" . addslashes($this->aState['vid']) . "'";
        }else{
            $cookie = "'" . $this->generated . "'";
        }
/*
        if(isset($this->_visitorsOriginalityParameters['php_session'])){
            session_start();
            $session = "'" . addslashes(session_id()) . "'";
        }
*/
        do{
            if(isset($reason)){
                $twist = 1;
                break;
            }
            if(
                $action != 'rate' && $this->aState['useJS'] && (
                    empty($cms->VarsPost['tp_freu_d']) || $cms->VarsPost['tp_freu_d'] != 43 ||
                    !isset($cms->VarsPost['tp_email']) || $cms->VarsPost['tp_email'] !== ''
                )
            ){
                $reason = 'no_javascript';
                $twistAlert = 'twist_enable_javascript';
                $twist = 1;
                $aEvent['action'] = $twistedAction;
                break;
            }
            if(
                AMI::getOption($srcModId, 'show_captcha') &&
                !in_array($action, AMI::getProperty($this->getExtId(), 'no_captcha_actions')) &&
                (!is_object($cms->Member) || ($cms->Member->isLoggedIn() ? AMI::getOption($this->optSrcModId, 'show_captcha_for_registered_users') : TRUE))
            ){
                $captcha = isset($cms->VarsPost['captcha']) ? mb_strtoupper(strval($cms->VarsPost['captcha'])) : '';
                $sid = isset($cms->VarsPost['captchaSID']) ? strval($cms->VarsPost['captchaSID']) : '';
                if(mb_strlen($captcha) != $this->aState['captchaLength']){
                    $twist = 1;
                }else{
                    /**
                     * @var AMI_iCaptcha
                     */
                    $oCaptcha =
                        AMI::getResource(
                            'captcha',
                            array(
                                $srcModId,
                                $sid,
                                $this->aState['captchaLength'],
                                AMI_Captcha::getConstantCharset($this->aState['captchaSymbolsSet'])
                            )
                        );
                    if($captcha != $oCaptcha->loadImageString()){
                        $twist = 1;
                    }else{
                        $oCaptcha->removeRecord();
                    }
                }
                if($twist){
                    $aEvent['action'] = $twistedAction;
                    $twistAlert = 'twist_invalid_captcha';
                    $reason = 'invalid_captcha';
                    break;
                }
            }

            // skip sumbission frequency check on self record changing
            if(empty($cms->VarsPost['id_update']) || !in_array($srcModId, array('discussion', 'forum', 'guestbook'))){
                // check sumbission frequency
                $where = array(
                    "`ext_module` = '" . $srcModId . "'",
                    "`action` = '" . $action . "'",
                    "`id_page` = '" . $cms->GetPageId() . "'",
                    "`date` >= DATE_SUB(NOW(), INTERVAL " . AMI::getOption($srcModId, 'action_period')  . ")",
                    "`twist` = 0"
                );
                if(isset($this->aState['aVisitorsOriginality']['ip_address'])){
                    $where[] = "ip = INET_ATON('" . getenv('REMOTE_ADDR') . "')";
                }
                if(empty($this->generated) && isset($this->aState['aVisitorsOriginality']['cookie'])){
                    $where[] = "vid = " . $cookie;
                }
    /*
                if(isset($this->_visitorsOriginalityParameters['php_session'])){
                    $where[] = "session = " . $session;
                }
    */
                $sql =
                    "SELECT 1 " .
                    "FROM `cms_twist_prevention` " .
                    "WHERE (" . implode(") AND (", $where) . ") " .
                    "LIMIT 1";
                $db->query($sql);
                if($db->num_rows() > 0){
                    $twist = 1;
                    $aEvent['action'] = $twistedAction;
                    $twistAlert = 'twist_detected';
                    $reason = 'too_frequently';
                }
            }
        }while(FALSE);

        $aSql = array (
            'date'         => '=|NOW()',
            'ext_module'   => $srcModId,
            'action'       => $action,
            'id_page'      => $cms->GetPageId(),
            'ip'           => '=|' . $ip,
            'vid'          => '=|' . $cookie,
            'is_generated' => '=|' . intval(!empty($this->generated)),
            'twist'        => '=|' . $twist
        );
/*
        if(isset($this->_visitorsOriginalityParameters['php_session'])){
            $aSql['session'] = '=|' . $session;
        }
*/
        if(isset($reason)){
            $aSql['reason'] = $reason;
        }
        $sql = $db->GenInsertSQL('cms_twist_prevention', $aSql);
        $db->query($sql);
        if($twist){
            $cms->Gui->globalVars['script_full_link'] = preg_replace('/status\_msg\=[^&]+/', '', $cms->Gui->globalVars['script_full_link']);
            unset($cms->VarsGet['status_msg']);
        }
        if(isset($twistAlert) && AMI::getOption($srcModId, 'show_alert')){
            $langFile = $cms->Gui->ParseLangFile('templates/lang/modules/' . $this->getExtId() . '_messages.lng');
            $cms->AddMessages($langFile);
            $cms->AddStatusMsg($twistAlert, 'red');
        }
        $cms->Gui->addGlobalVars(
            array(
                'NO_SPAM' => 1 - $twist,
                'NO_SPAM_' . mb_strtoupper($srcModId) => 1 - $twist
            )
        );

        return $aEvent;
    }

    /**#@-*/

    /**
     * Returns state.
     *
     * @return array
     */
    public function getState(){
        return $this->aState;
    }

    /**
     * Make data check.
     *
     * @param  array &$aEvent  Event data
     * @return array
     * @todo   Make real check
     */
    protected function check(array &$aEvent){
        // Make real check
        if(!AMI::getSingleton('env/request')->get('captcha', false)){
            $aEvent['_break_event'] = true;
        }
        return $aEvent;
    }
}

/**
 * AmiExt/Category extension configuration front view.
 *
 * @package    Config_AmiExt_Antispam
 * @subpackage View
 * @resource   ext_twist_prevention/view/frn <code>AMI::getResource('ext_twist_prevention/view/frn')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Antispam_ViewFrn extends AMI_ExtView{
    /**
     * Template block name
     *
     * @var string
     */
    protected $tplBlockName = 'ext_twist_prevention';

    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        return '';
    }

    /**
     * Get js/captcha on the details page.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleGetCode($name, array $aEvent, $handlerModId, $srcModId){
        $oTpl = $this->getTemplate();

        $aState = $this->oExt->getState();
        $modId = $this->getModId();

        $html = $oTpl->parse(
            $modId . ':captcha_row',
            array(
                'module'            => $srcModId,
                'length'            => isset($aState['captchaLength']) ? $aState['captchaLength'] : null,
                'use_js_protection' => $aState['useJS'],
                'split_image'       => $aState['splitImage'],
                'show_captcha'      => $aState['showCaptcha']
                // 'global_mode'       => false
            )
        );
        $js = $oTpl->parse(
            $modId . ':captcha_script',
            array(
                'length'             => isset($aState['captchaLength']) ? $aState['captchaLength'] : null,
                'use_js_protection'  => $aState['useJS'],
                'show_captcha'       => $aState['showCaptcha'],
                'check_captcha_code' => $aState['checkOnClient']
            )
        );

        $tail = mb_strtoupper($srcModId);
        $oTpl->addGlobalVars(
            array(
                'EXTENSION_TWIST_PREVENTION_DISCUSSION_HTML_' . $tail   => $html,
                'EXTENSION_TWIST_PREVENTION_DISCUSSION_SCRIPT_' . $tail => $js
            )
        );

        return $aEvent;
    }
}
