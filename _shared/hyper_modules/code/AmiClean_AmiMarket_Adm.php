<?php
/**
 * AmiClean/AmiMarket configuration.
 *
 * @copyright Amiro.CMS. All rights reserved.
 * @category  Module
 * @package   Config_AmiClean_AmiMarket
 * @version   $Id: AmiClean_AmiMarket_Adm.php 49380 2014-04-03 11:25:12Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiClean/AmiMarket configuration admin action controller.
 *
 * @package    Config_AmiClean_AmiMarket
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_AmiMarket_Adm extends Hyper_AmiClean_Adm{
    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request object
     * @param AMI_Response $oResponse  Response object
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        global $Core;

        // System user access only
        if(is_object($Core) && $Core instanceof CMS_Core){
            $isSysUser = $Core->isSysUser();
        }else{
            $isSysUser = AMI::getSingleton('core')->isSysUser();
        }
        if(!$isSysUser){
            return;
        }

        parent::__construct($oRequest, $oResponse);

        if($oRequest->get('mod_action') == 'html_view'){
            $oResponse->setType('JSON');
        }
        try{
            AMI_PackageManager::getInstance();
            $aComponents = array();
            if($oRequest->get('upload', FALSE) !== FALSE){
                // $aComponents[] = 'form';
                $aComponents[] = array(
                    'type' => 'form',
                    'id'   => 'upload'
                );
            }
            $aComponents[] = array(
                'type' => 'html',
                'id'   => 'market'
                
            );
            $this->addComponents($aComponents);
        }catch(AMI_Exception $oException){
            // Locked
            $this->addComponents(array('locked'));
        }
    }
}

/**
 * AmiClean/AmiMarket configuration model.
 *
 * @package    Config_AmiClean_AmiMarket
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_AmiMarket_State extends Hyper_AmiClean_State{
}

/**
 * AmiClean/AmiMarket configuration html component controller.
 *
 * @package    Config_AmiClean_AmiMarket
 * @subpackage Controller
 * @resource   {$modId}/html/controller/adm <code>AMI::getResource('{$modId}/html/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_AmiMarket_HtmlAdm extends Hyper_AmiClean_ComponentAdm{
    /**
     * Specifies whether component has a model or not
     *
     * @var bool
     */
    protected $useModel = FALSE;

    /**
     * Initialization.
     *
     * @return Market_Html
     */
    public function init(){
        return parent::init();
    }

    /**
     * Returns component type.
     *
     * @return string
     */
    public function getType(){
        return 'html';
    }


    /**
     * Returns true if component needs to be started always in full environment.
     *
     * @return bool
     */
    public function isFullEnv(){
        return TRUE;
    }    
}

/**
 * Market configuration html component view.
 *
 * @package    Config_Market
 * @subpackage View
 * @resource   {$modId}/html/view/adm <code>AMI::getResource('{$modId}/html/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_AmiMarket_HtmlViewAdm extends Hyper_AmiClean_ComponentViewAdm{
    /**
     * Component type
     *
     * @var string
     * @amidev Temporary
     */
    protected $type = 'html';

    /**
     * Package manager object
     *
     * @var AMI_PackageManager
     */
    protected $oPackageManager;

    /**
     * Packages data
     *
     * @var array
     */
    protected $aPackagesData = array();

    /**
     * Initialization.
     *
     * @return AmiClean_AmiMarket_HtmlViewAdm
     */
    public function init(){
        parent::init();

        $this->addScriptFile('_admin/' . $GLOBALS['CURRENT_SKIN_PATH'] . '_js/ami_market.js');

        $this->oPackageManager = AMI_PackageManager::getInstance();

        // Make a JS array with all installed packages packages and their versions...
        $aInstalledPackages = $this->oPackageManager->getDownloadedPackages();
        $aPackageData = array();
        foreach($aInstalledPackages as $pkgId => $aPkg){
            $aPackageData[] = "'" . $pkgId . "':{version:'" . $aPkg['version'] . "',instCnt:" . (int)$aPkg['instCnt'] . "}";
        }
        $this->addScriptCode("var aInstalledPackages = {" . implode(',', $aPackageData) . "};");
        $this->addScriptCode("var marketHash  = '" . $this->_buildMarketHash() . "';");

        return $this;
    }

    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        $pathData = parse_url(AMI_Registry::get('path/www_root'));
        $oRequest = AMI::getSingleton('env/request');
        $aScope = array(
            'showIframe' => ($oRequest->get('noframe', FALSE) === FALSE) ? '1' : FALSE,
            'host'       => $pathData['host'],
            'demo'       => false
        );

        $oSess = admSession();
        $udata = $oSess->GetVar('user');
        $flags = isset($udata['host_data']['flags']) ? $udata['host_data']['flags'] : 0;
        if(($flags & SF_DEMO) || in_array($pathData['host'], array('localhost', '127.0.0.1'))){
            $aScope['demo'] = true;
        }

        $link = $oRequest->get('link', '');
        if(strpos($link, '?') !== FALSE){
            $link = '';
        }
        $aScope['address'] = 'http://www.amiro.ru/market/' . $link . '?lay_id=97';

        $aScope['hash'] = $this->_buildMarketHash();
        $aScope['scripts'] = $this->getScripts();

        return $this->parse('market', $aScope);
    }

    /**
     * Returns hash string for Amiro.CMS market.
     *
     * @return string
     */
    private function _buildMarketHash(){
        $hash = '#';
        $aPackageData = $this->oPackageManager->getDownloadedPackages();
        $aPackageStrParts = array();
        if(sizeof($aPackageData)){
            foreach($aPackageData as $packageId => $aPackageV){
                $aPackageStrParts[] = $packageId . ':' . $aPackageV['version'] . ':' . $aPackageV['instCnt'] . ':' . $aPackageV['admin'] . ':' . $aPackageV['pseudo'] . ':' . $aPackageV['instId'];
            }
            $hash .= 'pkg_installed=';
            $hash .= rawurlencode(implode(';', $aPackageStrParts));
            $hash .= '&';
        }
        $version = substr($GLOBALS['CMS_VERSION'], 0, strrpos($GLOBALS['CMS_VERSION'], '.'));
        $hash .= ('cms_ver=' . $version);
        
        $edition = AMI::getEdition();
        $hash .= ('&cms_edition=' . $edition);

        return $hash;
    }
}

/**
 * Market module admin locked component action controller.
 *
 * @package    Config_Market
 * @subpackage Controller
 * @amidev
 */
final class AmiClean_AmiMarket_LockedAdm extends Hyper_AmiClean_ComponentAdm{
    /**
     * Flag specifying to use model
     *
     * @var   bool
     */
    protected $useModel = FALSE;

    /**
     * Returns component type.
     *
     * @return string
     */
    public function getType(){
        return 'locked';
    }
}

/**
 * Market module admin locked component view.
 *
 * @package    Config_Market
 * @subpackage View
 * @amidev
 */
class AmiClean_AmiMarket_LockedViewAdm extends Hyper_AmiClean_ComponentViewAdm{
    /**
     * Template file name
     *
     * @var string
     */
    protected $tplFileName = 'templates/modules/mod_manager_locked.tpl';

    /**
     * Locale file name
     *
     * @var string
     */
    protected $localeFileName = 'templates/lang/modules/mod_manager_locked.lng';

    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        $left = '';
        $lockPath = AMI_PackageManager::getLockPath();
        if(file_exists($lockPath)){
            $secondsLeft = AMI_PackageManager::LOCK_TTL - (time() - filemtime($lockPath));
            if($secondsLeft > 0){
                // $sec = $secondsLeft % 60;
                // $left = ($secondsLeft - $sec) / 60 . ':' . sprintf('%02d', $sec);
                $left = $secondsLeft;
            }
        }
        $html = $this->getTemplate()->parse($this->tplBlockName, array('left' => $left));

        return $html;
    }
}

/**
 * AmiClean/AmiMarket configuration form component controller.
 *
 * @package    Config_AmiClean_AmiMarket
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_AmiMarket_FormAdm extends Hyper_AmiClean_FormAdm{
    /**
     * Specifies whether component has a model or not
     *
     * @var bool
     */
    protected $useModel = FALSE;

    /**
     * Returns module file storage path.
     *
     * @return string
     */
    protected function getFileStoragePath(){
        return '_mod_files/ftpfiles/';
    }

    /**
     * Initialization.
     *
     * @return Files_FormAdm
     */
    public function init(){
        AMI_Event::addHandler('on_file_move', array($this, 'handleFileUpload'), $this->getModId());
        return parent::init();
    }

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Handles uploaded file movement.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_FileFactory::move()
     */
    public function handleFileUpload($name, array $aEvent, $handlerModId, $srcModId){
        $fileName = AMI_Lib_FS::prepareName('ami_market_' . uniqid() . "_" . time() . '.tar.gz');
        $aEvent['newName'] = $fileName;
        AMI_Registry::set('ami_market/filename', $fileName);
        return $aEvent;
    }

    /**#@-*/
    /**
     * Save action handler.
     *
     * @param  array &$aEvent  Event data
     * @return array
     */
    public function _save(array &$aEvent){
        $this->displayView();
        $oResponse = AMI::getSingleton('response');

        $aCodes = $this->getUploadedFileCodes();
        $oFileFactory = AMI::getResource('env/file');
        $aUploadedFiles = $oFileFactory->getUploaded($aCodes);
        foreach($aUploadedFiles as $code => $oFile){
            if($oFile->isValid()){
                // Unpack
                $localName = $oFile->getLocalName();
                $oPackageManager = AMI_PackageManager::getInstance();
                if($oPackageManager->unpack($localName, TRUE)){
                    // Install
                    $aPkgInfo = $oPackageManager->getManifest('', TRUE);
                    AMI_Registry::set('ami_market/aPackageInfo', $aPkgInfo);
                    AMI_Registry::set('ami_market/install', TRUE);
                    $oResponse->setType('HTML');
                }else{
                    $oResponse->addStatusMessage('status_unpack_failed', array(), AMI_Response::STATUS_MESSAGE_ERROR);
                    @unlink($localName);
                }
            }
        }

        $oRequest = AMI::getSingleton('env/request');
        $oRequest->set('id', 0);
        $oRequest->set('file', '');
        $this->oItem = null;

        AMI_Event::fire('dispatch_mod_action_form_edit', $aEvent, $this->getModId());
        return $aEvent;
    }
}

/**
 * Market configuration form component view.
 *
 * @package    Config_Market
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_AmiMarket_FormViewAdm extends Hyper_AmiClean_FormViewAdm{
    /**
     * Initialization.
     *
     * @return AmiClean_AmiMarket_HtmlViewAdm
     */
    public function init(){
        $this->addField(array('name' => 'id', 'value' => 1, 'type' => 'hidden'));
        $this->addField(array('name' => 'mod_id', 'value' => $this->getModId(), 'type' => 'hidden'));
        $this->addField(array('name' => 'mod_action', 'value' => 'form_save', 'type' => 'hidden'));
        $this->addField(array('name' => 'ami_full', 'value' => 1, 'type' => 'hidden'));
        $this->addField(array('name' => 'upload', 'value' => 1, 'type' => 'hidden'));
        $this->addField(array('name' => 'file', 'type' => 'file', 'validate' => array('filled')));
        if(AMI_Registry::exists('ami_market/install') && (AMI_Registry::get('ami_market/install') === TRUE)){
            $aPkgInfo = AMI_Registry::get('ami_market/aPackageInfo');
            $this->addScriptCode("aInstalledPackages['{$aPkgInfo['id']}'] = {instCnt: 0, version: '{$aPkgInfo['version']}'};");
            $this->addScriptCode('top.postMessage("installPackage|' . $aPkgInfo['id'] . '|' . $aPkgInfo['version'] . '", "*");');
        }
        return $this;
    }
}
