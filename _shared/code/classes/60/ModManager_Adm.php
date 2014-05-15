<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module_ModManager
 * @version   $Id: ModManager_Adm.php 49683 2014-04-11 11:37:06Z Kolesnikov Artem $
 * @since     x.x.x
 * @amidev
 */

/**
 * Module manager service class.
 *
 * @package    Module_ModManager
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
final class ModManager_Adm_Service{
    /**
     * Sends status message about module installation/uninstallation error.
     *
     * @param  AMI_Response  $oResponse   Error
     * @param  AMI_Exception $oException  Exception
     * @return void
     */
    public static function sendErrorMessage(AMI_Response $oResponse, AMI_Exception $oException){
        $aExceptions = array();
        $message = '';

        $oPrevException = $oException;
        do{
            $aExceptions[] = $oPrevException;
            $oPrevException =
                method_exists($oPrevException, 'getPrevious')
                    ? $oPrevException->getPrevious()
                    : null;
        }while($oPrevException);
        $aExceptions = array_reverse($aExceptions);
        foreach($aExceptions as $oException){
            $aResult = self::getError($oException);
            if('' !== $aResult['message']){
                break;
            }
        }

        $message = $aResult['message'] !== '' ? $aResult['message'] : 'exception_other';
        $notifyByEmail = $aResult['notifyByEmail'];
        $aParams = $oException->getData();
        if(!$aParams){
            $aParams = array();
        }

        if(!$notifyByEmail){
            AMI_Registry::push('disable_error_mail', TRUE);
        }
        trigger_error(
            'Module manager: ' . $oException->getMessage() . "\n" . $oException->getTraceAsString(),
            E_USER_WARNING
        );
        if(!$notifyByEmail){
            AMI_Registry::pop('disable_error_mail');
        }
        $oResponse->addStatusMessage($message, $aParams, AMI_Response::STATUS_MESSAGE_ERROR);
    }

    /**
     * Returns locale key for exception.
     *
     * @param AMI_Exception $oException  Exception
     * @return array
     */
    protected function getError(AMI_Exception $oException){
        $message = '';
        $notifyByEmail = FALSE;
        $aParams = $oException->getData();
        if(!$aParams){
            $aParams = array();
        }

        switch($oException->getCode()){
            case AMI_Tx_Exception::CMD_DB_ON_CREATE_TABLE:
                $message = 'exception_db_' . $aParams['type'];
                break;
            case AMI_Tx_Exception::CMD_EXISTING_FILE:
                $message = 'exception_target_file_exists';
                break;
            case AMI_Tx_Exception::CMD_ON_BACKUP_FILE:
                $notifyByEmail = TRUE;
                $message = 'exception_cannot_backup_file';
                break;
            case AMI_Tx_Exception::CMD_ON_CREATE_FILE:
                $notifyByEmail = TRUE;
                $message = 'exception_cannot_' . $aParams['type'] . '_file';
                break;
            case AMI_Package_Exception::INVALID_TEMPLATE_MODE:
                $message = 'exception_invalid_template_mode';
                break;
            case AMI_Package_Exception::INVALID_MOD_ID:
                $message = 'exception_invalid_mod_id';
                break;
            case AMI_Package_Exception::ALREADY_INSTALLED:
                $message = 'exception_already_installed';
                break;
            case AMI_Package_Exception::INVALID_HYPER_MOD_CONFIG:
                $notifyByEmail = TRUE;
                $message = 'exception_invalid_hyper_mod_config';
                break;
            case AMI_Package_Exception::INVALID_CAPTIONS_DATA:
                $notifyByEmail = TRUE;
                $message = 'exception_invalid_captions_data';
                break;
            case AMI_Package_Exception::EXISTING_CAPTIONS:
                $message = 'exception_existing_captions';
                break;
            case AMI_Package_Exception::BROKEN_CAPTIONS_FILE:
                $notifyByEmail = TRUE;
                $message = 'exception_broken_captions_file';
                break;
            case AMI_Package_Exception::ON_CREATE_CAPTIONS:
                $message = 'exception_on_create_captions';
                break;
            case AMI_Package_Exception::MISSING_DECLARATION_TPL:
                $notifyByEmail = TRUE;
                $message = 'exception_missing_declaration_tpl';
                break;
            case AMI_Package_Exception::OPEN_DECLARATION:
                $message = 'exception_open_declaration';
                break;
            case AMI_Package_Exception::PARSE_DECLARATION:
                $notifyByEmail = TRUE;
                $message = 'exception_parse_declaration';
                break;
            case AMI_Package_Exception::EXISTING_DECLARATION:
                $message = 'exception_existing_declaration';
                break;
            case AMI_Package_Exception::ON_CREATE_DECLARATION:
                $message = 'exception_on_create_declaration';
                break;
            case AMI_Package_Exception::INSTANCE_LIMIT:
                $message = 'exception_instance_limit';
                break;
            case AMI_Package_Exception::NOT_INSTALLED:
                $message = 'exception_not_installed';
                break;
            case AMI_Package_Exception::HAS_DEPENDENCIES:
                $message = 'exception_has_dependencies';
                break;
            case AMI_Package_Exception::CUSTOM_ERROR:
                $message = $oException->getMessage();
                break;
        }

        return
            array(
                'message'       => $message,
                'notifyByEmail' => $notifyByEmail
            );
    }
}

/**
 * Module Manager module admin action controller.
 *
 * @package    Module_ModManager
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
final class ModManager_Adm extends Hyper_AmiClean_Adm{
    /**
     * Popup mode flag
     *
     * @var bool
     */
    protected $popupMode;

    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request object
     * @param AMI_Response $oResponse  Response object
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        global $Core;

        $this->statusMessagePath = AMI_iTemplate::LNG_MOD_PATH . '/mod_manager_messages.lng';

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

        $this->popupMode = AMI_Registry::get('popup_mode', FALSE);

        try{
            AMI_PackageManager::getInstance();
            // No locks, ok
            if($this->popupMode){
                // hack
                $this->addComponents(
                    array(
                        'form' =>
                            array(
                                'id'   => $this->getModId() . '_2',
                                'type' => 'form'
                            )
                    )
                );
            }else{
                $this->addComponents(array('filter', 'list'));
            }
        }catch(AMI_Exception $oException){
            // Locked
            $this->addComponents(array('locked'));
        }
    }

    /**
     * Returns client locale path.
     *
     * @return string
     */
    public function getClientLocalePath(){
        return AMI_iTemplate::LNG_MOD_PATH . '/mod_manager_client.lng';
    }
}

/**
 * Module Manager module admin filter component action controller.
 *
 * @package    Module_ModManager
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
final class ModManager_FilterAdm extends Hyper_AmiClean_FilterAdm{
    /**
     * List recordset handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListRecordset($name, array $aEvent, $handlerModId, $srcModId){
        foreach(
            array(
                'header'        => 'caption',
                'search_id'     => 'id',
                'section'       => 'section',
                'hypermodule'   => 'hypermod',
                'configuration' => 'config'
            ) as
            $filterField => $conditionField
        ){
            $value = $this->oItem->getValue($filterField);
            if($value){
                $aEvent['oList']->addSearchCondition(array($conditionField => $value));
            }
        }

        return $aEvent;
    }
}

/**
 * Module Manager module admin filter component model.
 *
 * @package    Module_ModManager
 * @subpackage Model
 * @since      x.x.x
 * @amidev
 */
final class ModManager_FilterModelAdm extends Hyper_AmiClean_FilterModelAdm{
    /**
     * Constructor.
     */
    public function __construct(){
        $this->addViewField(
            array(
                'name'          => 'header',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'header'
            )
        );
        $this->addViewField(
            array(
                'name'          => 'search_id',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'search_id'
            )
        );

        $oDeclarator = AMI_ModDeclarator::getInstance();
        $oTpl = AMI::getResource('env/template_sys');

        $aItems = $oDeclarator->getRegisteredEnv('sections');
        $aLocale = $oTpl->parseLocale('templates/lang/_menu_owners.lng');
        $aSelectBox = array();
        foreach($aItems as $item){
            $aSelectBox[] = array(
                'name'  => $aLocale[$item],
                'value' => $item
            );
        }
        AMI_Lib_Array::sortMultiArray($aSelectBox, 'name');
        $this->addViewField(
            array(
                'name'          => 'section',
                'type'          => 'select',
                'flt_type'      => 'select',
                'flt_default'   => '',
                'flt_condition' => '=',
                'data'          => $aSelectBox,
                'session_field' => true,
                'not_selected'  => array('id' => '', 'caption' => 'all')
            )
        );

        $locale = AMI_Registry::get('lang');

        $aItems = $oDeclarator->getRegisteredEnv('hypermodules');
        $aSelectBox = array();
        foreach($aItems as $item){
            $metaClassName = 'Hyper_' . AMI::getClassPrefix($item) . '_Meta';
            if(class_exists($metaClassName)){
                /**
                 * @var AMI_Hyper_Meta
                 */
                $oMeta = new $metaClassName;
                $aSelectBox[] = array(
                    'name'  => $oMeta->getTitle($locale),
                    'value' => $item
                );
            }
        }
        AMI_Lib_Array::sortMultiArray($aSelectBox, 'name');
        $this->addViewField(
            array(
                'name'          => 'hypermodule',
                'type'          => 'select',
                'flt_type'      => 'select',
                'flt_default'   => '',
                'flt_condition' => '=',
                'data'          => $aSelectBox,
                'session_field' => true,
                'not_selected'  => array('id' => '', 'caption' => 'all')
            )
        );

        /*
        $aItems = $oDeclarator->getRegistered();
        $aSelectBox = array();
        AMI_Service::setAutoloadWarning(FALSE);
        foreach($aItems as $modId){
            list($hypermod, $config) = $oDeclarator->getHyperData($modId);
            $metaClassName = AMI::getClassPrefix($hypermod) . '_' . AMI::getClassPrefix($config) . '_Meta';
            if(class_exists($metaClassName)){
                $oMeta = new $metaClassName;
                if($oMeta->isVisible()){
                    $aSelectBox[$hypermod . '_' . $config] = array(
                        'name'  => $oMeta->getTitle($locale),
                        'value' => $config
                    );
                }
            }
        }
        AMI_Service::setAutoloadWarning(TRUE);
        AMI_Lib_Array::sortMultiArray($aSelectBox, 'name');
        $this->addViewField(
            array(
                'name'          => 'configuration',
                'type'          => 'select',
                'flt_type'      => 'select',
                'flt_default'   => '',
                'flt_condition' => '=',
                'data'          => $aSelectBox,
                'not_selected'  => array('id' => '', 'caption' => 'all')
            )
        );
        */
    }
}

/**
 * Module Manager module admin filter component view.
 *
 * @package    Module_ModManager
 * @subpackage View
 * @since      x.x.x
 * @amidev
 */
final class ModManager_FilterViewAdm extends Hyper_AmiClean_FilterViewAdm{
    /**
     * Prepares templates paths and blockname.
     *
     * @return void
     * @amidev
     */
    protected function prepareTemplates(){
        $this->tplFileName = AMI_iTemplate::TPL_MOD_PATH . '/' . $this->getModId() . '_filter.tpl';
        $this->localeFileName = AMI_iTemplate::LNG_MOD_PATH . '/' . $this->getModId() . '_filter.lng';
        parent::prepareTemplates();
    }
}

/**
 * Module Manager module admin form component action controller.
 *
 * @package    Module_ModManager
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 * @todo       Validate approptiate package id & package version.
 */
final class ModManager_FormAdm extends Hyper_AmiClean_FormAdm{
    /**
     * Returns true if component needs to be started always in full environment.
     *
     * @return bool
     */
    public function isFullEnv(){
        return TRUE;
    }

    /**
     * Save action dispatcher.
     *
     * @param  array &$aEvent  Event data
     * @return void
     */
    protected function _save(array &$aEvent){
        /**
         * @var AMI_RequestHTTP
         */
        $oRequest = $aEvent['oRequest'];

        $onUpdate = $oRequest->get('update', FALSE);
        if($onUpdate){
            $this->onUpdate($aEvent);
        }else{
            $this->onSave($aEvent);
        }
        $oRequest->set('id', 0);
        $this->oItem = null;

        /**
         * Processing controller actions of the AMI_Mod module.
         *
         * @event      dispatch_mod_action_form_edit $modId
         * @eventparam string       modId         Module id
         * @eventparam AMI_Mod      oController   Module controller object
         * @eventparam string       tableModelId  Table Model Id
         * @eventparam AMI_Request  oRequest      Request object
         * @eventparam AMI_Response oResponse     Response object
         * @eventparam string       action        Action name
         */
        AMI_Event::fire('dispatch_mod_action_form_edit', $aEvent, $this->getModId());
        if($onUpdate){
            $aEvent['oResponse']->write('');
            $aEvent['oResponse']->send();
        }
    }

    /**
     * Update action dispatcher.
     *
     * Updates captions for existing instance.
     *
     * @param  array &$aEvent  Event data
     * @return void
     */
    protected function onUpdate(array &$aEvent){
        // Validate request
        /**
         * @var AMI_RequestHTTP
         */
        $oRequest = $aEvent['oRequest'];
        $modId = $oRequest->get('update');
        $oDeclarator = AMI_ModDeclarator::getInstance();
        if(!$oDeclarator->isRegistered($modId)){
            return;
        }
        $aCaptions = $oRequest->get('captions', FALSE);
        if(!is_array($aCaptions)){
            return;
        }
        // Prepare captions
        AMI_Lib_Array::renameKey($aCaptions, '-', '', FALSE, TRUE);
        /*
        foreach(array_keys($aCaptions) as $locale){
            $aKeys = array_keys($aCaptions[$locale]);
            foreach($aKeys as $key){
                AMI_Lib_Array::renameKey($aCaptions[$locale], $key, $modId . $key);
            }
        }
        */
        /**
         * @var AMI_Response
         */
        $oResponse = $aEvent['oResponse'];
        $oModManipulator = new AMI_ModUpdateCaptions(
            $modId,
            $aCaptions
        );
        // $oModManipulator->setDebug(TRUE);
        // Update captions
        try{
            $oModManipulator->run();
            // Update taborder
            $taborder = (int)$oRequest->get('taborder', 1000);
            if(AMI::getProperty($modId, 'taborder') != $taborder){
                $file = AMI_Registry::get('path/root') . '_local/modules/declaration/declares.php';
                $code = file_get_contents($file);
                if(
                    $code !== FALSE &&
                    preg_match('~// \[' . $modId . '\] {\s+\$oDeclarator->startConfig\(\'[^\']+\'\, \'?(\d+)\'?\)~s', $code, $aMatches)
                ){
                    $search = $aMatches[0];
                    $replace = str_replace(' ' . $aMatches[1] . ')', ' ' . $oRequest->get('taborder', 1000) . ')', $search);
                    $code = str_replace($search, $replace, $code);
                    file_put_contents($file, $code);
                }
                // Rebuild properties
                $GLOBALS['Core']->WriteOption('core', 'requre_rebuild_modules', 1);
                $skipUpdateRightsVersion = TRUE;
            }

            // Success
            $oResponse->setMessage('install_success', self::SAVE_SUCCEED);
            if(empty($skipUpdateRightsVersion)){
                $oResponse->addStatusMessage('update_captions_success');
            }
            $GLOBALS['Core']->UpdateRightsVersion();
            $oResponse->setPageReload();
        }catch(AMI_Exception $oException){
            // Fail
            $oResponse->setMessage('install_fail', self::SAVE_FAILED);
            $oResponse->addStatusMessage('update_captions_fail', AMI_Response::STATUS_MESSAGE_ERROR);
            if(is_object($oModManipulator->oPkgCommon)){
                $oModManipulator->oPkgCommon->loadExceptionLocale($oException);
            }
            ModManager_Adm_Service::sendErrorMessage($oResponse, $oException);
            d::w('<span style="color: red;">' . $oException->getMessage() . '</span>');
            d::trace($oException->getTrace());
        }
        $oRequest->set('applied_id', $modId);
    }

    /**
     * Save action dispatcher.
     *
     * Installs new hypermodule/configuration instance.
     *
     * @param  array &$aEvent  Event data
     * @return void
     */
    protected function onSave(array &$aEvent){
        // Validate request
        /**
         * @var AMI_RequestHTTP
         */
        $oRequest = $aEvent['oRequest'];
        $section = (string)$oRequest->get('section', '');
        $pkgId = (string)$oRequest->get('pkg_id', '');

        if($section === ''){
            $this->installPseudoinstance($aEvent);
            return;
        }
        $taborder = (int)$oRequest->get('taborder', '');
        if(empty($taborder)){
            $taborder = 1000;
        }

        $hypermodConfig = (string)$oRequest->get('hypermod_config', '');
        if(mb_strpos($hypermodConfig, '/') === FALSE){
            trigger_error("Invalid 'hypermod_config' field value '" . $hypermodConfig . "'", E_USER_ERROR);
        }
        list($hypermod, $config) = explode('/', $hypermodConfig, 2);
        /**
         * @var AMI_HyperConfig_Meta
         */
        $oMeta = AMI_Package::getMeta($hypermod, $config);
        if(is_object($oMeta) && $oMeta->getInstanceId()){
            // Fixed instance id
            $modId = $oMeta->getInstanceId();
        }else{
            // Passed instance id
            $modId = trim((string)$oRequest->get('new_mod_id', ''));
        }

        // Autogenerate instance id {
        $aSourceCaptions = $oMeta->getCaptions();
        if($modId === ''){
            $modId = mb_strtolower($pkgId);
            if(strpos($modId, '.') !== FALSE){
                $modId = substr($modId, strpos($modId, '.') + 1);
            }
            $modId = preg_replace('/[^a-z_ ]/', '', $modId);
            $modId = preg_replace('/(\s|_)+/', '_', $modId);
            if(mb_strpos($modId, 'inst_') !== 0){
                $modId = 'inst_' . $modId;
            }
            $oDeclarator = AMI_ModDeclarator::getInstance();

            // Count instances having same hypermodule/configuration
            $aModIds = array_filter(
                $oDeclarator->getRegistered($hypermod, $config),
                array($this, 'cbFilterCatMod')
            );
            if('ami_multifeeds' === $hypermod){
                $aModIds =
                    array_merge(
                        $aModIds,
                        array_filter(
                            $oDeclarator->getRegistered('ami_multifeeds5', $config),
                            array($this, 'cbFilterCatMod')
                        )
                    );
            }
            $qty = sizeof($aModIds);
            if($qty){
                $start = $qty;
                $captionPostfix = ' ' . $qty;
            }else{
                $start = 1;
                $captionPostfix = '';
            }
            if($oDeclarator->isRegistered($modId)){
                $found = FALSE;
                $srcModId = $modId;
                for($digits = 3; $digits < 5; $digits++){
                    $max = 10 ^ $digits;
                    for($count = $start; $count < $max; $count++){
                        $modId = $srcModId . sprintf('%0' . $digits . 'd', $count);
                        if(!$oDeclarator->isRegistered($modId)){
                            $captionPostfix = ' ' . $count;
                            $found = TRUE;
                            break 2;
                        }
                    }
                }
                if(!$found){
                    $modId = '';
                }
            }
        }
        // } Autogenerate instance id

        $aCaptions = $oRequest->get('captions', FALSE);
        if(!is_array($aCaptions)){
            trigger_error("Missing 'captions' field", E_USER_ERROR);
        }
        AMI_Lib_Array::renameKey($aCaptions, '-', '', FALSE, TRUE);
        /*
        foreach(array_keys($aCaptions) as $locale){
            $aKeys = array_keys($aCaptions[$locale]);
            foreach($aKeys as $key){
                AMI_Lib_Array::renameKey($aCaptions[$locale], $key, $modId . $key);
            }
        }
        */

        // Rename captions
        if($captionPostfix != ''){
            $aAllowedTargets = array(
                ''     => array('/^header$/', '/^menu_group/', '/^menu$/', '/^specblock/'),
                '_cat' => array('/^header$/')
            );
            foreach(array_keys($aCaptions) as $locale){
                foreach($aCaptions[$locale] as $modPostfix => $aCaptionData){
                    foreach($aCaptionData as $target => $value){
                        if(isset($aAllowedTargets[$modPostfix])){
                            foreach($aAllowedTargets[$modPostfix] as $regExp){
                                if(
                                    preg_match($regExp, $target) &&
                                    $value === $aSourceCaptions[$locale][$modPostfix][$target][1]
                                ){
                                    $pos = mb_strpos($value, ' : ');
                                    if($pos === FALSE){
                                        $aCaptions[$locale][$modPostfix][$target] .= $captionPostfix;
                                    }else{
                                        $aParts = explode(' : ', $value, 2);
                                        $aCaptions[$locale][$modPostfix][$target] =
                                            $aParts[0] . $captionPostfix . ' : ' . $aParts[1];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // Installation

        /**
         * @var AMI_Response
         */
        $oResponse = $aEvent['oResponse'];

        $pkgId = $oRequest->get('pkg_id');

        /*
        $oModManipulator = new AMI_Tx_ModInstall(
            $section,
            $taborder,
            $hypermod,
            $config,
            $modId,
            $aCaptions,
            (int)$oRequest->get('install_mode', AMI_iTx_Cmd::MODE_COMMON)
        );
        */
        $oModManipulator = new AMI_Package_Install(
            $pkgId,
            $section,
            $taborder,
            $modId,
            $aCaptions,
            (int)$oRequest->get('install_mode', AMI_iTx_Cmd::MODE_COMMON)
        );

        // $oModManipulator->setDebug(TRUE);

        $locale = AMI_Registry::get('lang');
        $instance =
            AMI_Package::getMeta($hypermod)->getTitle($locale) . ' : ' .
            AMI_Package::getMeta($hypermod, $config)->getTitle($locale) . ' : ' .
            $aCaptions[$locale]['']['menu'];

        try{
            $oModManipulator->run();
            // Success
            $installedModId = $oModManipulator->getModId();
            $oResponse->setMessage('install_success', self::SAVE_SUCCEED);
            $oResponse->addStatusMessage(
                'status_add',
                array(
                    'instance' => $instance,
                    'modId'    => $installedModId
                )
            );
            $oResponse->setPageReload();
        }catch(AMI_Exception $oException){
            // Fail
            $oResponse->setMessage('install_fail', self::SAVE_FAILED);
            $oResponse->addStatusMessage('status_add_fail', array('instance' => $instance), AMI_Response::STATUS_MESSAGE_ERROR);
            if(is_object($oModManipulator->oPkgCommon)){
                $oModManipulator->oPkgCommon->loadExceptionLocale($oException);
            }
            ModManager_Adm_Service::sendErrorMessage($oResponse, $oException);
            d::w('<span style="color: red;">' . $oException->getMessage() . '</span>');
            d::trace($oException->getTrace());
        }
        $oRequest->set('applied_id', $modId);
    }

    /**
     * Save action dispatcher.
     *
     * Installs new hypermodule/configuration pseudoinstance.
     *
     * @param  array &$aEvent  Event data
     * @return void
     */
    protected function installPseudoinstance(array &$aEvent){
        /**
         * @var AMI_RequestHTTP
         */
        $oRequest = $aEvent['oRequest'];
        /**
         * @var AMI_Response
         */
        $oResponse = $aEvent['oResponse'];

        $pkgId = (string)$oRequest->get('pkg_id', '');

        $oModManipulator = new AMI_PseudoPackage_Install(
            $pkgId,
            $oRequest->get('install_mode', AMI_iTx_Cmd::MODE_COMMON)
        );

        // $oModManipulator->setDebug(TRUE);
        $locale = AMI_Registry::get('lang');
        $modId = $oModManipulator->getModId();
        $aPkgInfo = $oModManipulator->getPkgInfo();
        $package =
            is_array($aPkgInfo)
            ? $aPkgInfo['information'][$locale]['title'] . ' (' . $pkgId . ')'
            : $pkgId;

        try{
            $oModManipulator->run();
            // Success
            $oResponse->setMessage('install_success', self::SAVE_SUCCEED);
            $oResponse->addStatusMessage(
                'status_add_pseudo',
                array('package' => $package)
            );
            $oResponse->setPageReload();
        }catch(AMI_Exception $oException){
            // Fail
            $oResponse->setMessage('install_fail', self::SAVE_FAILED);
            $oResponse->addStatusMessage(
                'status_add_fail_pseudo',
                array('package' => $package),
                AMI_Response::STATUS_MESSAGE_ERROR
            );
            if(is_object($oModManipulator->oPkgCommon)){
                $oModManipulator->oPkgCommon->loadExceptionLocale($oException);
            }
            ModManager_Adm_Service::sendErrorMessage($oResponse, $oException);
            d::w('<span style="color: red;">' . $oException->getMessage() . '</span>');
            d::trace($oException->getTrace());
        }
        $oRequest->set('applied_id', $modId);
    }

    /**
     * Filters categories modules.
     *
     * @param  string $modId  Module id
     * @return bool
     * @see    ModManager_FormAdm::onSave()
     */
    private function cbFilterCatMod($modId){
        return !preg_match('/_cat$/', $modId);
    }
}

/**
 * Module Manager module admin form component view.
 *
 * @package    Module_ModManager
 * @subpackage View
 * @since      x.x.x
 * @amidev
 */
final class ModManager_FormViewAdm extends Hyper_AmiClean_FormViewAdm{
    /**
     * Form default elements template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#form', '#simple', 'simple', '#advanced', 'advanced', 'form'
    );

    /**
     * Array of form buttons
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aFormButtons = array('add');

    /**
     * Current configuration fs path
     *
     * @var string
     */
    protected $configPath;

    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        global $Core;

        $oPackageManager = $this->getPkgManager();
        if(!$oPackageManager){
            // Locked
            parent::init();
            return $this;
        }

        $this->addSection('simple', '');
        $this->addSection('advanced', '');

        $aMeta = array('');

        $this->addField(array('name' => 'mod_action', 'value' => 'form_save', 'type' => 'hidden'));
        $this->addField(array('name' => 'ami_full', 'value' => 1, 'type' => 'hidden'));

        $oRequest = AMI::getSingleton('env/request');
        $modId = $oRequest->get('id', FALSE);

        $pkgId = $oRequest->get('pkg_id', FALSE);
        $pkgVersion = $oRequest->get('pkg_ver', FALSE);
        $pkgHyperConfig = '';
        if($modId || ($pkgId && $pkgVersion)){
            $this->addField(array('name' => 'mode', 'value' => 'popup', 'type' => 'hidden'));
        }
        if($pkgId && $pkgVersion){
            $this->addField(array('name' => 'pkg_id', 'value' => $pkgId, 'type' => 'hidden'));
            $this->addField(array('name' => 'pkg_ver', 'value' => $pkgVersion, 'type' => 'hidden'));
        }else{
            $oDeclarator = AMI_ModDeclarator::getInstance();
            $pkgId = $oDeclarator->getAttr($modId, 'id_pkg');
        }
        $aPkgInfo = $oPackageManager->getManifest($pkgId);
        if(!$aPkgInfo){
            trigger_error(
                "Error parsing package '" . $pkgId . '/' . $pkgVersion . "' manifest: " .
                d::getDumpAsString($oPackageManager->getError()),
                E_USER_ERROR
            );
        }
        if($pkgId && $pkgVersion){
            $pkgHyperConfig =
                $aPkgInfo['install'][0]['hypermodule'] . '/' . $aPkgInfo['install'][0]['configuration'];
        }

        if($modId || $pkgHyperConfig){
            // Exising instance opened, changing captions form
            $aConfigs = AMI_Package::getAvailableConfigs(TRUE, FALSE);
            $this->addField(array('name' => 'update', 'value' => $modId, 'type' => 'hidden'));

            if($modId){
                $oItem = AMI::getResourceModel($this->getModId() . '/table')->find($modId, array('*'));
                $this->addField(array('name' => 'caption', 'type' => 'static', 'value' => $oItem->caption));
                $locale = AMI_Registry::get('lang');
                $this->addField(array('name' => 'distrib', 'type' => 'static', 'value' => $aPkgInfo['information'][$locale]['title']));

                $selectedHyperConfig = $oItem->hyper_config;
                $this->addField(array('name' => 'new_mod_id', 'value' => $modId, 'type' => 'hidden', 'attributes' => array('disabled' => 'disabled')));
            }else{
                $selectedHyperConfig = $pkgHyperConfig;
            }
            $this->addField(array('name' => 'hypermod_config', 'value' => $selectedHyperConfig, 'type' => 'hidden'));
            // Search for opened instance hypermodule/configuration

            $hyperConfig = '';
            foreach($aConfigs as $aConfig){
                if(($aConfig['hypermod'] . '/' . $aConfig['config']) === $selectedHyperConfig){
                    $hyperConfig = $aConfig['hypermod_caption'] . ' / ' . $aConfig['config_caption'];
                    break;
                }
            }
            if($hyperConfig == ''){
                trigger_error("Passed hypermodule/configuration '" . $selectedHyperConfig . "' not found in package '" . $pkgId . '/' . $pkgVersion . "'", E_USER_ERROR);
            }
            $this->addField(
                array(
                    'name'  => 'hypermod_config_caption',
                    'type'  => 'static',
                    'value' => $hyperConfig
                )
            );

            foreach($aConfigs as $aConfig){
                if(
                    empty($aConfig['info']) ||
                    ($aConfig['hypermod'] . '/' . $aConfig['config']) !== $selectedHyperConfig
                ){
                    continue;
                }else{
                    break;
                }
            }

            // Section caption
            $aLocale = $this->getTemplate()->parseLocale('templates/lang/_menu_owners.lng');
            $aOwners = array_keys($Core->GetOwnersList());
            if($modId){
                $section = $oItem->section;
                if(isset($aLocale[$section])){
                    $section = $aLocale[$section];
                }
                $this->addField(array('name' => 'section', 'type' => 'static', 'value' => $section));
                $this->addField(array('name' => 'id', 'type' => 'static', 'value' => $modId));

                $oMod = &$Core->GetModule($modId);
                if($oMod->IsAdminAllowed()){
                    $this->addField(
                        array(
                            'name'     => 'taborder',
                            'value'    => AMI_ModDeclarator::getInstance()->getTabOrder($modId),
                            'validate' => array('custom', 'filled', 'stop_on_error'),
                        )
                    );
                }
            }else{
                $oMeta = AMI_Package::getMeta($aConfig['hypermod'], $aConfig['config']);
                $this->addInstallModeAndNewInstanceId((bool)$aConfig['captions'], $oMeta);
                if($aConfig['captions']){
                    $this->addSectionAndTabOrderField();
                }
                if(is_object($oMeta)){
                    // Try to load form script
                    $aInfo = AMI_Service::getClassInfo(get_class($oMeta));
                    $configPath =
                        $aInfo[0]
                        ? AMI_Registry::get('path/hyper_shared')
                        : AMI_Registry::get('path/hyper_local') . 'distrib/';
                    $configPath .=
                        'configs/' . $aConfig['hypermod'] . '/' . $aConfig['config'] . '/';
                    if(file_exists($configPath . 'install_form.php')){
                        $this->configPath = $configPath;
                        $this->oTpl->setLocationSource($configPath, 'fs');
                        require_once $configPath . 'install_form.php';
                    }
                }
            }

            // Load captions from locales files

            $oTpl = $this->getTemplate();

            $aCaptions = $aConfig['captions'];
            if($modId){
                foreach($aConfig['captions'] as $locale => $aData){
                    $aSpecblockLocales = $oTpl->parseLocale('templates/lang/_ce_specblocks_items.lng', $locale);
                    $aLocale = array(
                        'header'         => $oTpl->parseLocale('templates/lang/_headers.lng', $locale),
                        'menu_group'     => $oTpl->parseLocale('_local/_admin/templates/lang/modules/_menu_group.lng', $locale),
                        'menu'           => $oTpl->parseLocale('templates/lang/_menu_all.lng', $locale),
                        'specblock'      => $aSpecblockLocales,
                        'specblock_desc' => $aSpecblockLocales,
                        'description'    => $oTpl->parseLocale('templates/lang/start.lng', $locale)
                    );

                    foreach($aData as $modIdTail => $aConfigCaptions){
                        $modId = $oItem->id . $modIdTail;
                        foreach($aLocale as $target => $aTargetLocale){
                            if($target !== 'specblock' && $target !== 'specblock_desc'){
                                if(
                                    isset($aConfigCaptions[$target]) &&
                                    isset($aTargetLocale[$modId])
                                ){
                                    $aCaptions[$locale][$modIdTail][$target][1] =
                                        $aTargetLocale[$modId];
                                }
                            }else{
                                foreach(array_keys($aConfigCaptions) as $caption){
                                    if(preg_match('/^specblock(_desc)?(:|$)/', $caption, $aMatches)){
                                        $key = 'spec_small_' . $modId;
                                        $pos = mb_strpos($caption, ':');
                                        if($pos !== FALSE){
                                            $key .= '_' . mb_substr($caption, $pos + 1);
                                        }
                                        if($aMatches[1]){
                                            $key .= '_desc';
                                        }
                                        if(isset($aTargetLocale[$key])){
                                            $aCaptions[$locale][$modIdTail][$caption][1] =
                                                $aTargetLocale[$key];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $aMeta[$selectedHyperConfig] =
                array(
                    'meta'       => $aConfig['info'],
                    'captions'   => $aCaptions,
                    'instanceId' => $aConfig['instanceId']
                );
            if(!$modId && $aCaptions){
                // Add checkbox to add module to all groups with control panel access
                if($GLOBALS['Core']->isInstalled('sys_groups')){
                    $this->addField(
                        array('name' => 'add_to_groups', 'type' => 'checkbox', 'attributes' => array('checked' => 'checked'), 'position' => 'advanced.end')
                    );
                }
            }
        }else{
            // New instance form
            $aConfigs = AMI_Package::getAvailableConfigs(TRUE);

            $this->addField(array('name' => 'hint_mod_meta', 'type' => 'hint'));

            // Hypermodule/configuration {

            $aAmiCleanConfigs = array();

            $aSelectbox = array();
            foreach($aConfigs as $aConfig){
                if($aConfig['hypermod'] === 'ami_clean'){
                    $aAmiCleanConfigs[] = $aConfig;
                    continue;
                }
                $aSelectbox[] = array(
                    'name'  => $aConfig['hypermod_caption'] . ' / ' . $aConfig['config_caption'],
                    'value' => $aConfig['hypermod'] . '/' . $aConfig['config']
                );
            }
            foreach($aAmiCleanConfigs as $aConfig){
                $aSelectbox[] = array(
                    'name'  => $aConfig['hypermod_caption'] . ' / ' . $aConfig['config_caption'],
                    'value' => $aConfig['hypermod'] . '/' . $aConfig['config']
                );
            }
            $this->addField(
                array(
                    'name'         => 'hypermod_config',
                    'type'         => 'select',
                    'data'         => $aSelectbox,
                    'not_selected' => array('id' => '', 'caption' => 'select_hypermodule_and_configuration'),
                    'validate'     => array('filled', 'stop_on_error')
                )
            );

            // } Hypermodule/configuration

            $this->addInstallModeAndNewInstanceId();
            $this->addSectionAndTabOrderField();

            foreach($aConfigs as $aConfig){
                if(empty($aConfig['info'])){
                    continue;
                }
                $aMeta[$aConfig['hypermod'] . '/' . $aConfig['config']] =
                    array(
                        'meta'       => $aConfig['info'],
                        'captions'   => $aConfig['captions'],
                        'instanceId' => $aConfig['instanceId']
                    );
            }

            // Add checkbox to add module to all groups with control panel access
            if($GLOBALS['Core']->isInstalled('sys_groups')){
                $this->addField(
                    array('name' => 'add_to_groups', 'type' => 'checkbox', 'attributes' => array('checked' => 'checked'), 'position' => 'advanced.end')
                );
            }
        }

        // Hypermodules / configurations meta info passed to JavaScript
        $metaKey = $aConfig['hypermod'] . '/' . $aConfig['config'];
        if(isset($aMeta[$metaKey]) && isset($aMeta[$metaKey]['meta']) && isset($aMeta[$metaKey]['meta']['author'])){
            $aMeta[$metaKey]['meta']['author'] = str_replace('href="http', 'href="http://www.amiro.ru/go.php?link=http', $aMeta[$metaKey]['meta']['author']);
        }

        $this->addField(
            array(
                'name'             => 'hypermod_config_info',
                'type'             => 'hypermod_config_info',
                'value'            => json_encode($aMeta),
                'skipHTMLEncoding' => TRUE
            )
        );

        if($aConfig['captions']){
            // Add tabs
            $aLocales = AMI_Registry::get('lang') == 'ru' ? array('ru', 'en') : array('en', 'ru');
            $this->addTabContainer('tabset', 'advanced.end');
            foreach($aLocales as $notSelected => $locale){
                $this->addTab('captions_' . $locale, 'tabset', $notSelected ? AMI_ModFormView::TAB_STATE_COMMON : AMI_ModFormView::TAB_STATE_ACTIVE);
            }
        }

        $this->addScriptFile('_admin/' . $GLOBALS['CURRENT_SKIN_PATH'] . '_js/mod_manager_form.js');

        parent::init();
        return $this;
    }

    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        if($this->oItem->id){
            $this->aFormButtons = array('apply', 'cancel');
        }
        return parent::get();
    }

    /**
     * Prepares templates paths and blockname.
     *
     * @return void
     * @amidev
     */
    protected function prepareTemplates(){
        $this->tplFileName    = AMI_iTemplate::TPL_MOD_PATH . '/mod_manager_form.tpl';
        $this->localeFileName = AMI_iTemplate::LNG_MOD_PATH . '/mod_manager_form.lng';
        parent::prepareTemplates();
    }

    /**
     * Returns prepared view scope.
     *
     * @param  string $type    View type
     * @param  array  $aScope  Scope
     * @return array
     */
    /*
    protected function getScope($type, array $aScope = array()){
        $aScope = parent::getScope($type, $aScope);
        $aScope['mode'] = 'popup';
        return $aScope;
    }
    */

    /**
     * Adds section / tab order fields.
     *
     * @return void
     * @amidev
     */
    protected function addSectionAndTabOrderField(){
        global $Core;

        // Section {

        $aLocale = $this->getTemplate()->parseLocale('templates/lang/_menu_owners.lng');
        $aSelectbox = array();
        $aOwners = array_keys($Core->GetOwnersList());
        $aMaxTabOrders = array();
        $initialTabOrder = 0;
        foreach($aOwners as $section){
            if($Core->IsOwnerInstalled($section) && isset($aLocale[$section]) && $section != 'system'){
                $aSelectbox[] = array(
                    'name'  => $aLocale[$section],
                    'value' => $section
                );
                $curMaxTabOrder = 0;
                foreach(array_keys($Core->GetModulesByOwner($section)) as $moduleId){
                    if(AMI::issetProperty($moduleId, 'taborder')){
                        $modTabOrder = (int)AMI::getProperty($moduleId, 'taborder');
                        if($modTabOrder > $curMaxTabOrder){
                            $curMaxTabOrder = $modTabOrder;
                        }
                    }
                }
                $aMaxTabOrders[$section] = $curMaxTabOrder + 1000;
                if($section === 'modules'){
                    $initialTabOrder = $aMaxTabOrders[$section];
                }
            }
        }
        $this->addField(
            array(
                'name'          => 'section',
                'type'          => 'select',
                'data'          => $aSelectbox,
                'value'         => 'modules',
                // 'not_selected'  => array('id' => '', 'caption' => 'select_section'),
                'validate'      => array('filled', 'stop_on_error')
            )
        );

        // } Section
        // Tab order {

        $this->addField(
            array(
                'name'             => 'taborder',
                'type'             => 'taborder',
                'validate'         => array('custom', 'filled', 'stop_on_error'),
                'value'            => $initialTabOrder,
                'js_value'         => json_encode($aMaxTabOrders),
                'skipHTMLEncoding' => TRUE
            )
        );

        // } Tab order
    }

    /**
     * Overload addField method to use 'simple' section as default.
     *
     * @param  array $aField  Field data
     * @return ModManager_FormViewAdm
     */
    public function addField(array $aField){
        $aField += array('position' => 'simple.end');
        return parent::addField($aField);
    }

    /**
     * Adds install mode / new instance id fields.
     *
     * @param  bool $newInstance      Flag specifying to display new instance field
     * @param  AMI_Hyper_Meta $oMeta  Meta object or null
     * @return void
     * @amidev
     */
    protected function addInstallModeAndNewInstanceId($newInstance = TRUE, AMI_Hyper_Meta $oMeta = null){
        // Install mode {

        $aModes = array(
            array('caption' => 'install_mode_common',    'value' => AMI_iTx_Cmd::MODE_COMMON),
            array('caption' => 'install_mode_append',    'value' => AMI_iTx_Cmd::MODE_APPEND),
            array('caption' => 'install_mode_overwrite', 'value' => AMI_iTx_Cmd::MODE_OVERWRITE)
        );
        if($oMeta){
            $aAllowedModes = $oMeta->getAllowedModes('install');
            foreach(array_keys($aModes) as $key){
                if(!in_array($aModes[$key]['value'], $aAllowedModes)){
                    unset($aModes[$key]);
                }
            }
        }
        if(sizeof($aModes) > 1){
            $this->addField(
                array(
                    'name'     => 'install_mode',
                    'type'     => 'select',
                    'data'     => $aModes,
                    'validate' => array('filled', 'stop_on_error'),
                    'hint'     => true
                )
            );
        }else{
            $aModes = array_values($aModes);
            $this->addField(array('name' => 'install_mode', 'type' => 'hidden', 'value' => $aModes[0]['value']));
        }
        // $this->addField(array('name' => 'install_mode_tooltip', 'type' => 'install_mode_tooltip'));

        // } Install mode
        // Instance id {

        if($newInstance){
            $this->addField(
                array(
                    'name'     => 'new_mod_id',
                    'validate' => array('custom'),
                )
            );
        }

        // } Instance id
    }

    /**
     * Returns package manager single instance.
     *
     * @return AMI_PackageManager|null  Null in case of lock
     */
    private function getPkgManager(){
        $oPackageManager = null;
        try{
            $oPackageManager = AMI_PackageManager::getInstance();
        }catch(AMI_Tx_PackageManagerException $oException){
            $this->addField(array('name' => 'warning_locked', 'type' => 'static'));
            // Do nothing, return null
        }
        return $oPackageManager;
    }
}

/**
 * Module Manager module admin list component action controller.
 *
 * @package    Module_ModManager
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
final class ModManager_ListAdm extends Hyper_AmiClean_ListAdm{
    /**
     * List actions controller resource id
     *
     * @var string
     */
    protected $listActionsResId = 'mod_manager/list_actions/controller/adm';

    /**
     * Constructor.
     */
    public function __construct(){
        $this->addActions(
            array(
                'info',
                'edit',
                'gen_code',
                'import',
                self::REQUIRE_FULL_ENV . 'uninstall'
            )
        );
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
 * Module Manager module admin list component view.
 *
 * @package    Module_ModManager
 * @subpackage View
 * @since      x.x.x
 * @amidev
 */
final class ModManager_ListViewAdm extends Hyper_AmiClean_ListViewAdm{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'order';

    /**
     * Order column direction
     *
     * @var bool
     */
    protected $orderDirection = 'desc';

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AMI_Module_ListViewAdm
     */
    public function init(){
        $this->addScriptFile('_admin/' . $GLOBALS['CURRENT_SKIN_PATH'] . '_js/mod_manager.js');

        // Init columns
        $this
            // ->addColumn('icon')
            // ->addColumnType('icon', 'date')
            // ->setColumnWidth('icon', '36px')
            ->addColumnType('order', 'int')
            ->setColumnWidth('order', 'extra-narrow')
            ->addColumn('date_installed')
            ->addColumnType('date_installed', 'date')
            ->addColumn('caption')
            ->setColumnTensility('caption')
            ->addColumn('distrib_caption')
            ->addColumnType('distrib_id', 'hidden')
            ->addColumn('section_caption')
            ->addColumnType('taborder', 'int')
            ->addColumnType('id', 'hidden')
            ->addColumnType('hypermod', 'none')
            ->addColumn('hypermod_caption')
            ->addColumnType('config', 'none')
            ->addColumn('config_caption')
            ->addColumnType('meta', 'hidden')
            ->addColumnType('is_sys', 'none')
            ->addColumnType('import_ext', 'hidden')
            ->addColumnType('import_data', 'hidden')
            ->addColumnType('import_options', 'hidden')
            ->addColumnType('import_templates', 'hidden')
            ->addColumnType('import_sources', 'hidden')
            // ->addSortColumns(array('id', 'hypermod_caption', 'config_caption', 'caption'))
            ->formatColumn(
                'date_installed',
                array($this, 'fmtDateTime'),
                array('format' => AMI_Lib_Date::FMT_DATE)
            )
            ->formatColumn(
                'hypermod_caption',
                array($this, 'fmtAddHint'),
                array(
                    'hint' => ':=hypermod'
                )
            )
            ->formatColumn(
                'config_caption',
                array($this, 'fmtAddHint'),
                array(
                    'hint' => ':=config'
                )
            )
            // Truncate 'caption' column by 100 symbols
            ->formatColumn(
                'caption',
                array($this, 'fmtTruncate'),
                array(
                    'length' => 100
                )
            )
            ->formatColumn(
                'caption',
                array($this, 'fmtIcon')
            )
            ->addSortColumns(
                array(
                    'order',
                    'date_installed',
                    'caption',
                    'distrib_caption',
                    'section_caption',
                    'taborder',
                    'hypermod_caption',
                    'config_caption' // ,
                    // 'id'
                )
            );
        AMI_Event::addHandler('on_list_body_row', array($this, 'handleListBodyRow'), $this->getModId());

        return $this;
    }

    /**
     * Event handler.
     *
     * Handling action cell, disablig some actions for instance.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleActionCell($name, array $aEvent, $handlerModId, $srcModId){
        $modId = $aEvent['aScope']['id'];
        $oDeclarator = AMI_ModDeclarator::getInstance();
        $hypermod = $aEvent['aScope']['hypermod'];
        $config = $aEvent['aScope']['config'];
        $oMeta = AMI_Package::getMeta($hypermod, $config);
        if(is_object($oMeta)){
            $canImport      = $oMeta->isImportAllowed();
            $isImportSource = in_array($modId, $oMeta->getImportSourceModIds());
            $editable       = $oMeta->isEditable();
            $canGenCode     = $oMeta->canGenCode();
            $isPermanent    = $oMeta->isPermanent();
        }else{
            $canImport      = FALSE;
            $isImportSource = FALSE;
            $editable       = FALSE;
            $canGenCode     = FALSE;
            $isPermanent    = FALSE;
        }
        if(!$canImport || $isImportSource){
            unset($aEvent['aScope']['_action_col']['import']);
        }
        if(empty($aEvent['aScope']['is_sys']) || !$canGenCode){
            unset($aEvent['aScope']['_action_col']['gen_code']);
        }
        if(
            $isPermanent ||
            empty($aEvent['aScope']['meta']['modes']) ||
            ($modId == 'mod_manager')/* ||
            empty($aEvent['aScope']['meta']['modes']['uninstall'])*/
        ){
            unset($aEvent['aScope']['_action_col']['uninstall']);
        }
        // #CMS-11173 {
        if($oDeclarator->isRegistered($modId) && $oDeclarator->getAttr($modId, 'core_v5')){
            // Prevent base instance uninstallation
            // unset($aEvent['aScope']['_action_col']['uninstall']);
            unset($aEvent['aScope']['_action_col']['gen_code']);
            // Temporary hack
            if($oDeclarator->getSection($modId) !== 'modules'){
                unset($aEvent['aScope']['_action_col']['edit']);
            }
        }
        if(!$editable){
            unset($aEvent['aScope']['_action_col']['edit']);
        }
        // } #CMS-11173
        $aEvent = parent::handleActionCell($name, $aEvent, $handlerModId, $srcModId);

        return $aEvent;
    }

    /**
     * Prepares templates paths and blockname.
     *
     * @return void
     * @amidev
     */
    protected function prepareTemplates(){
        $this->tplFileName    = AMI_iTemplate::TPL_MOD_PATH . '/mod_manager_list.tpl';
        $this->localeFileName = AMI_iTemplate::LNG_MOD_PATH . '/mod_manager_list.lng';
        parent::prepareTemplates();
    }

    /**
     * Column formatter.
     *
     * Adds hint for cell value.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments:
     *                       - <b>hint</b> - cell value hint.
     * @return string
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtAddHint($value, array $aArgs){
        $hint = $aArgs['hint'];
        $isItemValue = mb_substr($hint, 0, 2) === ':=';
        if($isItemValue){
            $hint = mb_substr($aArgs['hint'], 2);
            $hint = $aArgs['oItem']->$hint;
        }
        $value = $value !== '' ? "<span title=\"{$hint}\">{$value}</span>" : "<span style=\"color: #f00;\">{$hint}</span>";
        return $value;
    }

    /**
     * Handles import cells.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListBodyRow($name, array $aEvent, $handlerModId, $srcModId){
        $modId = $aEvent['aScope']['id'];
        $aTypes = array('data', 'options', 'templates');
        $hypermod = $aEvent['aScope']['hypermod'];
        $config = $aEvent['aScope']['config'];
        $oMeta = AMI_Package::getMeta($hypermod, $config);
        foreach($aTypes as $type){
            $aEvent['aScope']['import_' . $type] = 0;
            if(is_object($oMeta)){
                if($oMeta->isImportAllowed() && in_array($type, $oMeta->getImportAllowedTypes())){
                    $aEvent['aScope']['import_' . $type] = 1;
                }
            }
        }
        $aEvent['aScope']['import_ext'] = AMI::getOption('mod_manager', 'ext_templates_imported');
        $aSources = array();
        if(is_object($oMeta)){
            $aSources = $oMeta->getImportSourceModIds();
            foreach($aSources as $i => $sourceModId){
                if(!$GLOBALS['Core']->isInstalled($sourceModId)){
                    unset($aSources[$i]);
                }
            }
        }
        if(sizeof($aSources)){
            $aEvent['aScope']['import_sources'] = implode(',', $aSources);
        }else{
            // Remove import icon if no source modules found
            $pos = array_search('import', $aEvent['aScope']['_actions']);
            if(($pos !== false) && !is_null($pos)){
                unset($aEvent['aScope']['_actions'][$pos]);
                unset($aEvent['aScope']['_action_col']['import']);
            }
        }
        return $aEvent;
    }

    /**
     * Column formatter.
     *
     * Formats icon column.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return string
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtIcon($value, array $aArgs){
        global $Core;

        $id = $aArgs['oItem']->id;
        $oMod = &$Core->GetModule($id);

        /*
        $icon = '_local/_admin/images/icons/' . $id . '.gif';
        if($oMod && $oMod->IsAdminAllowed()){
            $value = '<a href="' . $oMod->GetAdminLink() . '" title="' . $this->aLocale['to_module'] . '">' . $value . ' &raquo;&raquo;<br /><span style="padding-left: 34px;">[' . $id . ']</span></a>';
        }

        return
            ('<img src="' .
            (file_exists(AMI_Registry::get('path/root') . $icon)
            ? AMI_Registry::get('path/www_root') . $icon
            : 'skins/vanilla/icons/icon-_mod.gif'
            ) .
            '" style="width: 34px; height: 34px; vertical-align: middle;" alt="" title="" /> ') .
            $value;
        */

        $aScope = array(
            'id'        => $id,
            'icon_path' => AMI_Service::getModResourceURL('mod_icon', array('modId' => $id)),
            'admin_url' => $oMod && $oMod->IsAdminAllowed() ? $oMod->GetAdminLink() : '',
            'caption'   => $value
        );

        return $this->parse('caption_field', $aScope);
    }
}

/**
 * Module Manager module model.
 *
 * @package Module_ModManager
 * @since   x.x.x
 * @amidev
 */
final class ModManager_State extends Hyper_AmiClean_State{
}

/**
 * Module Manager module admin list action controller.
 *
 * @category   AMI
 * @package    Module_ModManager
 * @subpackage Controller
 * @todo       Human representation for exception codes
 * @since      x.x.x
 * @amidev
 */
final class ModManager_ListActionsAdm extends Hyper_AmiClean_ListActionsAdm{
    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     * @see    AMI_ModListAdm::addActions()
     * @see    AMI_ModListAdm::addColActions()
     */

    /**
     * Instance uninstall action handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListAdm::addActionCallback()
     */
    public function dispatchUninstall($name, array $aEvent, $handlerModId, $srcModId){
        $modId = $this->getRequestId();
        $oDeclarator = AMI_ModDeclarator::getInstance();
        /**
         * @var AMI_Response
         */
        $oResponse = $aEvent['oResponse'];
        /**
         * @var AMI_RequestHTTP
         */
        $oRequest = $aEvent['oRequest'];
        $softUniinstall = $oRequest->get('mode', FALSE) !== 'hardcore';
        if($softUniinstall){
            AMI_Event::addHandler('on_tx_action', array($this, 'onTxAction'), AMI_Event::MOD_ANY);
        }
        $locale = AMI_Registry::get('lang');
        if($oDeclarator->isRegistered($modId)){
            list($hypermod, $config) = $oDeclarator->getHyperData($modId);
            $instance =
                AMI_Package::getMeta($hypermod)->getTitle($locale) . ' : ' .
                AMI_Package::getMeta($hypermod, $config)->getTitle($locale) . ' : ' .
                AMI::getOption($modId, 'admin_menu_caption') . " ({$modId})";
            // $oModManipulator = new AMI_Tx_ModUninstall($modId, AMI_iTx_Cmd::MODE_COMMON);
            $oModManipulator = new AMI_Package_Uninstall(
                $modId,
                $softUniinstall ? AMI_iTx_Cmd::MODE_SOFT : AMI_iTx_Cmd::MODE_COMMON
            );

            try{
                $oModManipulator->run();

                // Success
                $oResponse->addStatusMessage('uninstall_success');
                $oResponse->addStatusMessage(
                    'status_uninstall',
                    array('instance' => $instance)
                );
                if(AMI_Registry::get('mod_manager_uninstalled_mod_id', FALSE) === FALSE){
                    AMI_Registry::set('mod_manager_uninstalled_mod_id', $modId);
                }
                // ModManager_Adm_Service::redirect($oRequest, $oResponse, 'status_uninstall', array('instance' => $instance));
                $this->refreshView();
            }catch(AMI_Exception $oException){
                // Fail
                $oResponse->addStatusMessage('uninstall_fail', array(), AMI_Response::STATUS_MESSAGE_ERROR);
                if(is_object($oModManipulator->oPkgCommon)){
                    $oModManipulator->oPkgCommon->loadExceptionLocale($oException);
                }
                ModManager_Adm_Service::sendErrorMessage($oResponse, $oException);
                d::w('<span style="color: red;">' . $oException->getMessage() . '</span>');
                d::trace($oException->getTrace());
                $this->refreshView();
            }
        }elseif(preg_match('/^pseudo_(\d+)$/', $modId, $aMatches)){
            // Check for pseudo instance
            $oModManipulator = new AMI_PseudoPackage_Uninstall(
                $aMatches[1],
                AMI_iTx_Cmd::MODE_COMMON
            );
            $instance = $modId;
            try{
                $oModManipulator->run();

                // Success
                $aPkgInfo = $oModManipulator->getPkgInfo();
                $package =
                    isset($aPkgInfo['information'][$locale]) &&
                    isset($aPkgInfo['information'][$locale]['title'])
                    ? $aPkgInfo['information'][$locale]['title'] . '(' . $aPkgInfo['id'] . ')'
                    : $aPkgInfo['id'];
                $oResponse->addStatusMessage('uninstall_success');
                $oResponse->addStatusMessage(
                    'status_uninstall_pseudo',
                    array('package' => $package)
                );
                if(AMI_Registry::get('mod_manager_uninstalled_mod_id', FALSE) === FALSE){
                    AMI_Registry::set('mod_manager_uninstalled_mod_id', $modId);
                }
                $this->refreshView();
            }catch(AMI_Exception $oException){
                // Fail
                $oResponse->addStatusMessage('uninstall_fail', array(), AMI_Response::STATUS_MESSAGE_ERROR);
                if(is_object($oModManipulator->oPkgCommon)){
                    $oModManipulator->oPkgCommon->loadExceptionLocale($oException);
                }
                ModManager_Adm_Service::sendErrorMessage($oResponse, $oException);
                d::w('<span style="color: red;">' . $oException->getMessage() . '</span>');
                d::trace($oException->getTrace());
                $this->refreshView();
            }
        }else{
            // Not registered instance
            $oResponse->addStatusMessage('uninstall_fail', array(), AMI_Response::STATUS_MESSAGE_ERROR);
            $oResponse->addStatusMessage('module_not_installed', array('mod_id' => $modId), AMI_Response::STATUS_MESSAGE_ERROR);
            $this->refreshView();
        }

        return $aEvent;
    }


    /**
     * Instance data and options migration action handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListAdm::addActionCallback()
     */
    public function dispatchImport($name, array $aEvent, $handlerModId, $srcModId){
        global $Core;
        $modId = $this->getRequestId();
        $oDeclarator = AMI_ModDeclarator::getInstance();
        /**
         * @var AMI_Response
         */
        $oResponse = $aEvent['oResponse'];
        if($oDeclarator->isRegistered($modId)){
            $aModules = AMI_ModDeclarator::getInstance()->getSubmodules($modId);
            array_unshift($aModules, $modId);

            /**
             * @var AMI_RequestHTTP
             */
            $oRequest = $aEvent['oRequest'];
            $importData = $oRequest->get('import_data', FALSE) == 1;
            $importOptions = $oRequest->get('import_options', FALSE) == 1;
            $importTemplates = $oRequest->get('import_templates', FALSE) == 1;
            $importExt = $oRequest->get('import_ext', FALSE) == 1;
            $sourceModId = $oRequest->get('source_mod_id');
            $locale = AMI_Registry::get('lang');
            list($hypermod, $config) = $oDeclarator->getHyperData($modId);

            $instance =
                AMI_Package::getMeta($hypermod)->getTitle($locale) . ' : ' .
                AMI_Package::getMeta($hypermod, $config)->getTitle($locale) . ' : ' .
                AMI::getOption($modId, 'admin_menu_caption') . ' (' . $modId . ')';

            $aModManipulators = array();
            foreach($aModules as $moduleId){
                if(AMI::isCategoryModule($moduleId) && !$Core->IsInstalled($sourceModId . '_cat')){
                    continue;
                }
                $aModManipulators[] =
                    new AMI_ModImport(
                        $moduleId,
                        $sourceModId,
                        AMI_iTx_Cmd::MODE_COMMON,
                        $importData,
                        $importOptions,
                        $importTemplates,
                        $importExt
                    );
            }
            $res = TRUE;
            $aFinishedManipulators = array();
            // Run all transactions without finish commits
            try{
                foreach($aModManipulators as $oModManipulator){
                    $oModManipulator->run(FALSE);
                    $aFinishedManipulators[] = $oModManipulator;
                }
                // Success
                $oResponse->addStatusMessage('import_success');
                $this->refreshView();
            }catch(AMI_Exception $oException){
                // Fail, rollback all transactions that already finished
                foreach($aFinishedManipulators as $oModManipulator){
                    $oModManipulator->rollback();
                }
                $oResponse->resetStatusMessages();
                $oResponse->addStatusMessage('import_fail', array(), AMI_Response::STATUS_MESSAGE_ERROR);
                if(is_object($oModManipulator->oPkgCommon)){
                    $oModManipulator->oPkgCommon->loadExceptionLocale($oException);
                }
                ModManager_Adm_Service::sendErrorMessage($oResponse, $oException);
                d::w('<span style="color: red;">' . $oException->getMessage() . '</span>');
                d::trace($oException->getTrace());
                $this->refreshView();
            }
        }else{
            // Not registered instance
            $oResponse->addStatusMessage('import_fail', array(), AMI_Response::STATUS_MESSAGE_ERROR);
            $oResponse->addStatusMessage('module_not_installed', array('mod_id' => $modId), AMI_Response::STATUS_MESSAGE_ERROR);
            $this->refreshView();
        }
        return $aEvent;
    }

    /**
     * Local PHP-code generation action handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListAdm::addActionCallback()
     */
    public function dispatchGenCode($name, array $aEvent, $handlerModId, $srcModId){
        $modId = $this->getRequestId();
        $oDeclarator = AMI_ModDeclarator::getInstance();
        /**
         * @var AMI_RequestHTTP
         */
        $oRequest = $aEvent['oRequest'];
        /**
         * @var AMI_Response
         */
        $oResponse = $aEvent['oResponse'];
        $mode = $oRequest->get('mode', FALSE) !== 'overwrite' ? AMI_iTx_Cmd::MODE_APPEND : AMI_iTx_Cmd::MODE_OVERWRITE;
        $messagePrefix = $mode == AMI_iTx_Cmd::MODE_APPEND ? 'local_code_append_' : 'local_code_overwrite_';
        if($oDeclarator->isRegistered($modId)){
            $oModManipulator = new AMI_ModInstallInstanceLocalCode($modId, $mode);
            try{
                $oModManipulator->run();
                // Success
                $oResponse->addStatusMessage($messagePrefix . 'success', array('path' => '_local/modules/code'));
            }catch(AMI_Exception $oException){
                // Fail
                $oResponse->addStatusMessage($messagePrefix . 'fail', array(), AMI_Response::STATUS_MESSAGE_ERROR);
                if(is_object($oModManipulator->oPkgCommon)){
                    $oModManipulator->oPkgCommon->loadExceptionLocale($oException);
                }
                ModManager_Adm_Service::sendErrorMessage($oResponse, $oException);
                d::w('<span style="color: red;">' . $oException->getMessage() . '</span>');
                d::trace($oException->getTrace());
            }
        }else{
            // Not registered instance
            $oResponse->addStatusMessage($messagePrefix . 'fail', array(), AMI_Response::STATUS_MESSAGE_ERROR);
            $oResponse->addStatusMessage('module_not_installed', array('mod_id' => $modId), AMI_Response::STATUS_MESSAGE_ERROR);
        }
        $this->refreshView();
        return $aEvent;
    }

    /**#@-*/

    /**
     * Transaction action handler.
     *
     * Discards deleting db tables on instance uninstall.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Tx::run()
     * @see    ModManager_ListActionsAdm::dispatchUninstall()
     */
    public function onTxAction($name, array $aEvent, $handlerModId, $srcModId){
        $aDiscardedActions = array(
            'uninstallRulesCaptions',
            'uninstallCaptions',
            'uninstallIcons',
            'uninstallTplResource',
            'uninstallLocalCode',
            'uninstallOptions',
            'uninstallDB',
            'onPostUninstall',
            'uninstallAll'
        );
        if(
            get_class($aEvent['oTx']) === 'AMI_Tx_ModUninstall' &&
            in_array($aEvent['action'], $aDiscardedActions)
        ){
            $aEvent['_discard'] = TRUE;
        }
        return $aEvent;
    }
}

/**
 * Module Manager module admin list group action controller.
 *
 * @category   AMI
 * @package    Module_ModManager
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
final class ModManager_ListGroupActionsAdm extends Hyper_AmiClean_ListGroupActionsAdm{
}

/**
 * Module Manager module admin locked component action controller.
 *
 * @package    Module_ModManager
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
final class ModManager_LockedAdm extends Hyper_AmiClean_ComponentAdm{
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
 * Module Manager module admin locked component view.
 *
 * @package    Module_ModManager
 * @subpackage View
 * @since      x.x.x
 * @amidev
 */
class ModManager_LockedViewAdm extends Hyper_AmiClean_ComponentViewAdm{
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
