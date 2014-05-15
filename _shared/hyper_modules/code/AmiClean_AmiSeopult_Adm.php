<?php
/**
 * AmiClean/AmiSeopult configuration.
 *
 * @copyright Amiro.CMS. All rights reserved.
 * @category  Module
 * @package   Config_AmiClean_AmiSeopult
 * @version   $Id: AmiClean_AmiSeopult_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiClean/AmiSeopult configuration admin action controller.
 *
 * @package    Config_AmiClean_AmiSeopult
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_AmiSeopult_Adm extends Hyper_AmiClean_Adm{
    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request object
     * @param AMI_Response $oResponse  Response object
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        parent::__construct($oRequest, $oResponse);
        $aHtmlComponent = array(
            'type' => 'html',
            'id'   => 'seopult'
        );
        $this->addComponents(array($aHtmlComponent));
    }
}

/**
 * AmiClean/AmiSeopult configuration model.
 *
 * @package    Config_AmiClean_AmiSeopult
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_AmiSeopult_State extends Hyper_AmiClean_State{
}

/**
 * AmiClean/AmiSeopult configuration html component controller.
 *
 * @package    Config_AmiClean_AmiSeopult
 * @subpackage Controller
 * @resource   {$modId}/html/controller/adm <code>AMI::getResource('{$modId}/html/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_AmiSeopult_HtmlAdm extends Hyper_AmiClean_ComponentAdm{
    /**
     * Specifies whether component has a model or not
     *
     * @var bool
     */
    protected $useModel = FALSE;

    /**
     * Initialization.
     *
     * @return AmiClean_AmiSeopult_Html
     */
    public function init(){
        AMI_Event::addHandler('dispatch_mod_action_register', array($this, AMI::actionToHandler('register')), $this->getModId());
        return parent::init();
    }

    /**
     * Seopult registration handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchRegister($name, array $aEvent, $handlerModId, $srcModId){
        $this->displayView();
        $host = AMI::getSingleton('env/request')->get('host', false);
        if($host && isset($GLOBALS['Core'])){
            $hash = md5(uniqid() . time());
            AMI::setOption($this->getModId(), 'domain', $host);
            AMI::setOption($this->getModId(), 'hash', $hash);

            $oHTTPRequest = new AMI_HTTPRequest(
                array(
                    'returnHeaders'  => false,
                    'followLocation' => false
                )
            );

            $res = $oHTTPRequest->send(
                AMI::getProperty($this->getModId(), 'register_url'),
                array(
                    'login'             => $host,
                    'url'               => $host,
                    'hash'              => $hash,
                    'email'             => '',
                    'partner'           => 'dceaa9ed82f4d6a79ff2c4c680a4b2de',
                    'partnerVersion'    => 'amiro-5146'
                )
            );

            $ok = false;

            $res = json_decode($res);
            if(is_object($res)){
                if(!$res->error){
                    $hasKey = $res->data && $res->data->cryptKey;
                    if($hasKey){
                        AMI::setOption($this->getModId(), 'crypt_key', $res->data->cryptKey);
                        $GLOBALS['Core']->SaveOptions($this->getModId(), false);
                        $ok = true;
                    }
                }else{
                    AMI::getSingleton('response')->setType('JSON');
                    AMI::getSingleton('response')->addStatusMessage($res->status->message, array(), AMI_Response::STATUS_MESSAGE_ERROR);
                }
            }
        }
        if(!$ok){
            AMI::setOption($this->getModId(), 'domain', '');
            AMI::setOption($this->getModId(), 'hash', '');
        }
        return $aEvent;
    }

    /**
     * Returns component type.
     *
     * @return string
     */
    public function getType(){
        return 'html';
    }
}

/**
 * AmiClean/AmiSeopult configuration html component view.
 *
 * @package    Config_AmiClean_AmiSeopult
 * @subpackage View
 * @resource   {$modId}/html/view/adm <code>AMI::getResource('{$modId}/html/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_AmiSeopult_HtmlViewAdm extends Hyper_AmiClean_ComponentViewAdm{
    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        $oRequest = AMI::getSingleton('env/request');
        // Use engine.php?mod_id=ami_seopult&clear_user_data to clear
        // domain, hash and crypt key options
        if($oRequest->get('clear_user_data', false) === 'clear'){
            AMI::setOption($this->getModId(), 'domain', '');
            AMI::setOption($this->getModId(), 'hash', '');
            AMI::setOption($this->getModId(), 'crypt_key', '');
            $GLOBALS['Core']->SaveOptions($this->getModId(), false);
        }
        $oTpl = $this->getTemplate();
        $domain = AMI::getOption($this->getModId(), 'domain');
        if($domain){
            // Draw main page with specfic URL for certain domain and hash
            $domain = AMI::getOption($this->getModId(), 'domain');
            $hash = AMI::getOption($this->getModId(), 'hash');
            $cryptKey = AMI::getOption($this->getModId(), 'crypt_key');

            $data = array(
                'login'         => $domain,
                'hash'          => $hash,
                'createdOn'     => date('Y:m:d h:i:s')
            );

            require_once $GLOBALS['CLASSES_PATH'] . "../lib/crypt.php";

            $crypt = urlencode(Simplecrypt::encrypt(json_encode($data), $cryptKey));

            $address = AMI::getProperty($this->getModId(), 'login_url') . '?k=zaa' . $hash . $crypt . '&parentUrl=' . urlencode($_SERVER['HTTP_REFERER']);
            $aScope = array(
                'address'   => $address
            );
            return $oTpl->parse($this->tplBlockName . ':body', $aScope);
        }else{
            // Domain is not set, draw intro page
            $pathData = parse_url(AMI_Registry::get('path/www_root'));
            $aScope = array(
                'host' => $pathData['host'],
                'demo' => false
            );

            $oSess = admSession();
            $udata = $oSess->GetVar('user');
            $flags = isset($udata['host_data']['flags']) ? $udata['host_data']['flags'] : 0;
            if(($flags & SF_DEMO) || in_array($pathData['host'], array('localhost', '127.0.0.1'))){
                $aScope['demo'] = true;
            }

            return $oTpl->parse($this->tplBlockName . ':intro', $aScope) . $oTpl->parse($this->tplBlockName . ':intro2', $aScope);
        }
    }
}
