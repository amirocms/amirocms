<?php
/**
 * AmiModulesTemplates hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiModulesTemplates
 * @version   $Id: Hyper_AmiModulesTemplates_Adm.php 48185 2014-02-25 05:39:32Z Kolesnikov Artem $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiModulesTemplates hypermodule admin action controller.
 *
 * @package    Hyper_AmiModulesTemplates
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiModulesTemplates_Adm extends AMI_Module_Adm{
    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request object
     * @param AMI_Response $oResponse  Response object
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        if(AMI::getSingleton('env/request')->get("form_only", false)){
            $this->aDefaultComponents = array('form');
        }
        parent::__construct($oRequest, $oResponse);
    }

    /**
     * Initializes module.
     *
     * @return AMI_Mod
     * @amidev Temporary
     */
    public function init(){
        parent::init();
        // set module permissions if "require_reset_modules" was set
        if(AMI::getSingleton('env/request')->get("mod_action", false) == "set_templates_permissions"){
            // full entry point only action
            if(isset($GLOBALS['Core']) && ($GLOBALS['Core'] instanceof CMS_Core)){
                $oCore = $GLOBALS['Core'];
                $option = "require_reset_modules";
                $vData = false;
                if($oCore->ReadOption($vData, $this->getModId(), $option)){
                    Hyper_AmiModulesTemplates_Service::setTemplatesPermissions();
                    $oCore->DeleteOption($this->getModId(), $option);
                    die('OK');
                }
            }
            die('NOTHING TO DO');
        }
        return $this;
    }
}

/**
 * Module model.
 *
 * @package    Hyper_AmiModulesTemplates
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiModulesTemplates_State extends AMI_ModState{
}

/**
 * AmiModulesTemplates hypermodule admin filter component action controller.
 *
 * @package    Hyper_AmiModulesTemplates
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiModulesTemplates_FilterAdm extends AMI_Module_FilterAdm{
}

/**
 * AmiModulesTemplates hypermodule item list component filter model.
 *
 * @package    Hyper_AmiModulesTemplates
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiModulesTemplates_FilterModelAdm extends AMI_Module_FilterModelAdm{
    /**
     * Constructor.
     */
    public function __construct(){
        $aDiskTemplates = Hyper_AmiModulesTemplates_Service::getModifiedTemplates();
        $bHasModifiedTemplates = isset($aDiskTemplates['modified']) && count($aDiskTemplates['modified']);
        $bHasNewTemplates = isset($aDiskTemplates['new']) && count($aDiskTemplates['new']);
        $oResponse = AMI::getSingleton('response');
        $oRequest = AMI::getSingleton('env/request');
        $modName = $oRequest->get('modname', '');
        if($modName && ($oRequest->get('mode', false) == 'popup')){
            if(strpos($modName, '.') === false){
                $oDeclarator = AMI_ModDeclarator::getInstance();
                $section = '';
                if($oDeclarator->isRegistered($modName)){
                    $section = $oDeclarator->getSection($modName);
                }else{
                    // Getting old modules owner bad style.
                    $sql = "SELECT module_owner FROM cms_modules_templates WHERE module=%s LIMIT 1";
                    $oDB = AMI::getSingleton('db');
                    $section = $oDB->fetchValue(DB_Query::getSnippet($sql)->q($modName));
                    if(!$section){
                        $section = '';
                    }
                }
                $oRequest->set('modname', $section . '.' . $modName);
            }
        }
        if($oRequest->get('mod_action') == 'filter_view'){
            if($bHasModifiedTemplates){
                $oResponse->setType('JSON');
                $oResponse->addStatusMessage(
                    'status_modified_templates',
                    array(
                        'num' => count($aDiskTemplates['modified'])
                    ),
                    AMI_Response::STATUS_MESSAGE_ERROR
                );
            }
            if($bHasNewTemplates){
                $oResponse->setType('JSON');
                $oResponse->addStatusMessage(
                    'status_new_templates',
                    array(
                        'num' => count($aDiskTemplates['new'])
                    ),
                    AMI_Response::STATUS_MESSAGE_ERROR
                );
            }
            if(defined('TEMPLATES_FROM_DISK')){
                $oResponse->setType('JSON');
                $oResponse->addStatusMessage(
                    'templates_read_from_disk',
                    array(),
                    AMI_Response::STATUS_MESSAGE_ERROR
                );
            }
        }

        $this->addViewField(
            array(
                'name'          => 'set',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'content',
                'disable_empty' => true
            )
        );

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
                'name'          => 'content',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'content'
            )
        );

        $this->addViewField(
            array(
                'name'          => 'modname',
                'type'          => 'select',
                'flt_condition' => '=',
                'data'          => Hyper_AmiModulesTemplates_Service::getModulesList(),
                'flt_column'    => 'module'
            )
        );

        $this->addViewField(
            array(
                'name'          => 'module',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'module',
                'disable_empty' => true
            )
        );

        $aData = array();
        $aTypes = array('shared', 'admin', 'front');
        foreach($aTypes as $type){
            $aData[] = array(
                'name'    => $type,
                'value'   => $type,
                'caption' => 'type_' . $type
            );
        }
        $this->addViewField(
            array(
                'name'          => 'type',
                'type'          => 'select',
                'flt_condition' => '=',
                'flt_default'   => '',
                'data'          => $aData,
                'flt_column'    => 'is_sys',
                'not_selected'  => array('id' => '', 'caption' => 'all'),
                'hint'          => true
            )
        );

        if($bHasModifiedTemplates){
            $bMultiSite = AMI::getSingleton('env/cookie')->get('multiSite/enabled', 0);
            if(!$bMultiSite){
                $this->addViewField(
                    array(
                        'name'          => 'show_modified',
                        'type'          => 'checkbox',
                        'flt_default'   => '0',
                        'flt_condition' => '',
                        'flt_column'    => 'is_sys',
                        'disable_empty' => true
                    )
                );
            }
        }

        $this->addViewField(
            array(
                'name'          => 'content_is_default',
                'type'          => 'checkbox',
                'flt_default'   => '0',
                'flt_condition' => '',
                'flt_column'    => 'content_is_default',
                'disable_empty' => true
            )
        );
    }

    /**
     * Handle id_page filter custom logic.
     *
     * @param  string $field  Field name
     * @param  array  $aData  Filter data
     * @return array
     */
    protected function processFieldData($field, array $aData){
        if($field == 'modname'){
            $value = $aData['value'];
            if(!$value){
                $aData['skip'] = true;
                // $aData['forceSQL'] = " AND module_owner != '' ";
            }else{
                $aParts = explode('.', $value);
                $owner = $aParts[0];
                $modId = $aParts[1];
                $aData['forceSQL'] = " AND (0 OR ";
                if($owner){
                    $aData['forceSQL'] .= "(module_owner = '" . $this->prepareSqlField('module_owner', $owner, 'text') . "' ";
                }
                if($modId == '_all'){
                    if(!$owner){
                        $aData['skip'] = true;
                    }else{
                        $aData['forceSQL'] .= ")";
                    }
                }elseif($modId == '_common'){
                    if(!$owner){
                        $aData['forceSQL'] .= " module = '' OR module like '!%' ";
                    }else{
                        $aData['forceSQL'] .= " AND module like '!%') ";
                    }
                }else{
                    if($owner){
                        $aData['forceSQL'] .= " AND ";
                    }
                    $aData['forceSQL'] .= "module = '" . $this->prepareSqlField('module', $modId, 'text') . "' ";
                    if($owner){
                        $aData['forceSQL'] .= ")";
                    }
                }
                $aData['forceSQL'] .= ") ";
            }
        }
        if($field == 'type'){
            $aData['forceSQL'] = " AND allowed = 1 ";
            if($aData['value'] && in_array($aData['value'], array('admin', 'front', 'shared'))){
                $aData['forceSQL'] .= " AND side = '" . $aData['value'] . "' ";
            }
        }
        if($field == 'set'){
            if($aData['value']){
                // and ( 0 or content regexp '<!--#set[ \r\n]+var="[^"]*aaa' ) )
                $aData['forceSQL'] = " AND (0 OR content regexp '<!--#set[ \r\n]+var=\"[^\"]*" . mysql_real_escape_string($aData['value']) . "') ";
            }
        }
        if($field == 'show_modified'){
            if($aData['value']){
                $aData['forceSQL'] = 'AND (0 ';
                $aDiskTemplates = Hyper_AmiModulesTemplates_Service::getModifiedTemplates();
                if(isset($aDiskTemplates["modified_ids"]) && count($aDiskTemplates["modified_ids"])){
                    $aData['forceSQL'] .= ' OR id in (' . implode(',', $aDiskTemplates["modified_ids"]) . ') ';
                }
                $aData['forceSQL'] .= ') ';
            }
        }
        if($field == 'content_is_default'){
            if($aData['value']){
                $aData['forceSQL'] = " AND (side != 'admin' AND content_default IS NOT NULL AND TRIM(REPLACE(REPLACE(`content`, '\r', ''), '\n', ' ')) != TRIM(REPLACE(REPLACE(`content_default`, '\r', ''), '\n', ' '))) ";
            }
        }
        return $aData;
    }
}

/**
 * AmiModulesTemplates hypermodule admin filter component view.
 *
 * @package    Hyper_AmiModulesTemplates
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiModulesTemplates_FilterViewAdm extends AMI_Module_FilterViewAdm{
	/**
 	 * Placeholders array
 	 *
 	 * @var array
 	 */
    protected $aPlaceholders = array(
        '#filter',
            'set', 'header', 'content', 'modname', 'module', 'type',
        'filter'
    );
}

/**
 * AmiModulesTemplates hypermodule admin form component action controller.
 *
 * @package    Hyper_AmiModulesTemplates
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiModulesTemplates_FormAdm extends AMI_Module_FormAdm{
    /**
     * Returns true if component needs to be started always in full environment.
     *
     * @return bool
     */
    public function isFullEnv(){
        return FALSE;
    }

    /**
     * Initialization.
     *
     * @return Hyper_AmiModulesTemplates_FormAdm
     * @amidev Temporary
     */
    public function init(){
        parent::init();
        foreach(array('import', 'export', 'repair', 'restore') as $action){
            AMI_Event::addHandler('dispatch_mod_action_form_' . $action, array($this, AMI::actionToHandler($action)), $this->getModId());
        }
        AMI_Event::addHandler('on_after_save_model_item', array($this, 'handleAfterSaveModelItem'), $this->getModId());
        AMI_Event::addHandler('on_save_validate_{tplname}', array($this, 'handleValidateTplName'), $this->getModId());
        return $this;
    }

    /**
     * Save model item handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTableItem::setData()
     */
    public function handleSaveModelItem($name, array $aEvent, $handlerModId, $srcModId){
        $oServerCookie = AMI::getSingleton('env/cookie');
        $oServerCookie->set('ta_highlight', $aEvent['aData']['ta_highlight'], time() + AMI_ServerCookie::LIFETIME_YEAR);
        $oServerCookie->set('ta_wrap', $aEvent['aData']['ta_wrap'], time() + AMI_ServerCookie::LIFETIME_YEAR);
        // SetLocalCookie('ta_position_'.($this->isLangs ? 'langs_' : '').$cId, $this->cms->VarsPost['ta_position'], time() + 3600, '');
        $oServerCookie->save();
        $oNow = DB_Query::getSnippet('NOW()');

        $header = $aEvent['aData']['header'];
        if(strpos($header, ('.' . Hyper_AmiModulesTemplates_Service::getFileExtension())) === FALSE){
             $header .= ('.' . Hyper_AmiModulesTemplates_Service::getFileExtension());
        }
        $aEvent['aData']['header'] = $header;
        $aEvent['oItem']->header = $header;

        if(!$aEvent['aData']['id']){
            $side = (mb_strpos($aEvent['aData']['path'], '_local') === 0) ? 'shared' : 'front';
            $aEvent['oItem']->side = $side;
            $modData = $aEvent['aData']['module'];
            list($owner, $module) = mb_strlen($modData) ? explode('.', $modData) : array('', '');
            if($module == '_common'){
                $module = '';
            }

            // Save siteId for multisite support
            $bMultiSite = $oServerCookie->get('multiSite/enabled', 0);
            if(!$bMultiSite){
                $siteId = $oServerCookie->get('multiSite/siteId', 0);
                $aEvent['aData']['site_id']  = $siteId;
                $aEvent['oItem']->site_id    = $siteId;
            }

            $aEvent['aData']['module_owner'] = $owner;
            $aEvent['aData']['module']       = $module;
            $aEvent['aData']['side']         = $side;
            $aEvent['oItem']->module_owner   = $owner;
            $aEvent['oItem']->module         = $module;
            $aEvent['oItem']->side           = $side;

            $aEvent['aData']['created']  = $oNow;
            $aEvent['oItem']->created    = $oNow;
        }
        $aEvent['aData']['modified']     = $oNow;
        $aEvent['oItem']->modified       = $oNow;
        $aEvent['aData']['synchronized'] = $oNow;
        $aEvent['oItem']->synchronized   = $oNow;

        return $aEvent;
    }

    /**
     * Name validator.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleValidateTplName($name, array $aEvent, $handlerModId, $srcModId){

        $id = $aEvent['oItem']->getId();
        $header = $aEvent['oItem']->header;
        $path = $aEvent['oItem']->path;

        if(strpos($aEvent['oItem']->module, '.') !== false){
            list($section, $module) = explode('.', $aEvent['oItem']->module);
        }else{
            $module = $aEvent['oItem']->module;
        }

        $header .= ('.' . Hyper_AmiModulesTemplates_Service::getFileExtension());

        $oCheckItem = AMI::getResourceModel($this->getModId() . '/table')->getItem();
        $oCheckItem
            ->addFields(
                array('id')
            )
            ->addSearchCondition(
                array(
                    'path'   => $path,
                    'header' => $header
                )
            )
            ->load();

        if($oCheckItem->getId() && ($oCheckItem->getId() != $id)){
            AMI::getSingleton('response')->addStatusMessage(
                'status_tpl_exists',
                array('tpl' => $path . $header),
                AMI_Response::STATUS_MESSAGE_ERROR
            );

            $aEvent['message'] = 'File named ' . $path . $header . ' already exists!';
        }

        // Check tpl name
        $oDeclarator = AMI_ModDeclarator::getInstance();
        if($oDeclarator->isRegistered($module)){
            if((mb_strpos($module, 'inst_') === 0) && (mb_strpos($header, $module) !== 0)){
                AMI::getSingleton('response')->addStatusMessage(
                    'status_invalid_header',
                    array(),
                    AMI_Response::STATUS_MESSAGE_ERROR
                );

                $aEvent['message'] = 'Header should start with module name!';
            }
        }

        return $aEvent;
    }

    /**
     * Succesful save model item handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleAfterSaveModelItem($name, array $aEvent, $handlerModId, $srcModId){
        if($aEvent['success']){
            Hyper_AmiModulesTemplates_Service::dropParsedData();

            $module = $aEvent['oItem']->module;
            $owner = $aEvent['oItem']->module_owner;

            $oCore = $GLOBALS['Core'];
            if(empty($owner) || $owner == 'pmanager' || (strlen($module) && $module[0] == '!')){
                $oCore->Cache->ClearAdd("all");
            }else if(!empty($module)){
                $oCore->Cache->ClearAdd("all");
                $oMod = $oCore->GetModule($module);
                $oCore->Cache->clearModCache($oMod);
            }
        }
        return $aEvent;
    }

    /**
     * Import action handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function dispatchImport($name, array $aEvent, $handlerModId, $srcModId){
        $this->displayView();
        $oCore = $GLOBALS['Core'];
        $oGui = AMI_Registry::get('oGUI');
        $bIsLangs = ($this->getModId() == 'modules_templates_langs');
        $syncType = ((int)AMI::getSingleton('env/request')->get('sync_type', 2) == 2);
        $res = $oGui->importTemplatesFromDisk(AMI::getSingleton('db')->getCoreDB(), $bIsLangs, false, $syncType);
        Hyper_AmiModulesTemplates_Service::dropParsedData();
        if($res['added'] > 0){
            Hyper_AmiModulesTemplates_Service::setTemplatesPermissions();
        }
        $oResponse = AMI::getSingleton('response');
        $oResponse->setType('JSON');
        $oResponse->setMessage('ok', self::SAVE_SUCCEED);
        $oResponse->addStatusMessage(
            'status_imported_templates',
            array(
                'added'     => (string)$res['added'],
                'updated'   => (string)$res['updated'],
                'ignored'   => (string)$res['ignored']
            ),
            AMI_Response::STATUS_MESSAGE
        );
        $oCore->Cache->ClearAdd("all");
        return $aEvent;
    }

    /**
     * Export action handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function dispatchExport($name, array $aEvent, $handlerModId, $srcModId){

        if(defined('TEMPLATES_FROM_DISK')){
            return $aEvent;
        }

        $this->displayView();
        $bIsLangs = ($this->getModId() == 'modules_templates_langs');
        $oGui = AMI_Registry::get('oGUI');
        $syncType = ((int)AMI::getSingleton('env/request')->get('sync_type', 2) == 2);
        $res = $oGui->exportTemplatesToDisk(AMI::getSingleton('db')->getCoreDB(), $bIsLangs, $syncType);
        Hyper_AmiModulesTemplates_Service::dropParsedData();

        $oResponse = AMI::getSingleton('response');
        $oResponse->setType('JSON');
        $oResponse->setMessage('ok', self::SAVE_SUCCEED);
        $oResponse->addStatusMessage(
            'status_exported_templates',
            array(
                'added'     => (string)$res['added'],
                'updated'   => (string)$res['updated'],
                'ignored'   => (string)$res['ignored']
            ),
            AMI_Response::STATUS_MESSAGE
        );

        if($res['failed'] > 0){
            $oResponse->addStatusMessage(
                'status_export_failed_templates',
                array(
                    'failed' => (string)$res['failed']
                ),
                AMI_Response::STATUS_MESSAGE_ERROR
            );
        }
        return $aEvent;
    }

    /**
     * Restore action handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function dispatchRestore($name, array $aEvent, $handlerModId, $srcModId){
        $this->displayView();
        $oCore = $GLOBALS['Core'];
        $oItem = AMI::getResourceModel($aEvent['tableModelId'])->find($aEvent['oRequest']->get('id', null), array('*'));
        $oItem->content = $oItem->content_default;
        $oItem->header = mb_substr($oItem->header, 0, mb_strlen($oItem->header) - 4);
        $oItem->save();

        $oResponse = AMI::getSingleton('response');
        $oResponse->setType('JSON');
        $oResponse->setMessage('ok', self::SAVE_SUCCEED);
        $oResponse->addStatusMessage(
            'status_restored',
            array(),
            AMI_Response::STATUS_MESSAGE
        );
        // $oCore->Cache->ClearAdd("all");

        $aEvent['oRequest']->set('applied_id', $oItem->id);
        AMI_Event::fire('dispatch_mod_action_form_edit', $aEvent, $this->getModId());

        $oCore->Cache->ClearAdd("all");
        return $aEvent;
    }
}

/**
 * AmiModulesTemplates hypermodule admin form component view.
 *
 * @package    Hyper_AmiModulesTemplates
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiModulesTemplates_FormViewAdm extends AMI_Module_FormViewAdm{
    /**
     * Common fields validators
     *
     * @var array
     */
    protected $aCommonFieldsValidators = array();

    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        $modeFormOnly = AMI::getSingleton('env/request')->get("form_only", false);

        $hlEditor = AMI::getSingleton('env/cookie')->get('ta_highlight', 'yes');
        $hlWrap = $hlEditor == 'yes' ? AMI::getSingleton('env/cookie')->get('ta_wrap', 'no') : 'no';
        $this->addField(array('name' => 'mod_id', 'value' => $this->getModId(), 'type' => 'hidden'));
        $this->addField(array('name' => 'id', 'type' => 'hidden'));
        if($modeFormOnly){
            $this->addField(array('name' => 'mode', 'value' => 'popup', 'type' => 'hidden'));
            $this->addField(array('name' => 'form_only', 'value' => '1', 'type' => 'hidden'));
        }
        $this->addField(array('name' => 'ta_highlight', 'type' => 'hidden', 'value' => $hlEditor));
        $this->addField(array('name' => 'ta_wrap', 'type' => 'hidden', 'value' => $hlWrap));
        $this->addField(array('name' => 'mod_action', 'value' => 'form_save', 'type' => 'hidden'));

        $oGUI = AMI_Registry::get('oGUI');

        $bMultiSite = AMI::getSingleton('env/cookie')->get('multiSite/enabled', 0);
        if(!$bMultiSite && !$modeFormOnly){
            $oGUI->AddGlobalVars(array("_ENABLE_BUTTONS" => 1));
        }

        if(defined('TEMPLATES_FROM_DISK')){
            $oGUI->AddGlobalVars(array("TPLS_READ_FROM_DISK" => 1));
        }

        $aDiskTemplates = Hyper_AmiModulesTemplates_Service::getModifiedTemplates();
        if(isset($aDiskTemplates["modified"]) && sizeof($aDiskTemplates["modified"])){
            $oGUI->AddGlobalVars(array("isChooseExportSyncType" => 1));
        }
        if(isset($aDiskTemplates["old"]) && sizeof($aDiskTemplates["old"])){
            $oGUI->AddGlobalVars(array("isChooseImportSyncType" => 1));
        }

        if($modeFormOnly){
            $this->aFormButtons = array('apply');
        }

    	return $this;
    }

    /**
     * Setting up model item object.
     *
     * @param  AMI_iFormModTableItem $oItem  Item model
     * @return AMI_ModFormView
     */
    public function setModelItem(AMI_iFormModTableItem $oItem){
        $readonly = 0;
        $oRequest = AMI::getSingleton('env/request');
        $this->oItem = $oItem;

        $viewDefaultContent = (bool)$oRequest->get('content_default', 0);
        $viewDiff = (bool)$oRequest->get('diff', 0);

        if(($oItem->side == 'admin') || $viewDefaultContent || $viewDiff){
            $readonly = 1;
            $oRequest->set('mod_action', 'form_show');
        }
        $this->aScope['diff_mode'] = 0;
        if($viewDefaultContent){
            $origin = $this->oItem->content;
            $this->oItem->content = $this->oItem->content_default;
            $this->addScriptCode('var defaultContentMode = 1;');
            if($viewDiff){
                $this->addScriptCode('var diffMode = 1;');
                $this->aScope['diff_mode'] = 1;
            }
        }
        $headerVal = $oItem->getId() ? mb_substr($oItem->header, 0, mb_strlen($oItem->header) - 4) : '';
        $aModules = Hyper_AmiModulesTemplates_Service::getModulesList(true);
        $module = $this->aLocale['_common'];

        $aDirectories = Hyper_AmiModulesTemplates_Service::getDirectoriesList();
        foreach($aDirectories as $i => $aDir){
            $dirSide = 'dir_site';
            if(strpos($aDir['value'], '_local') === 0){
                $dirSide = 'dir_local';
            }
            $aDirectories[$i]['id'] = $aDirectories[$i]['name'];
            $aDirectories[$i]['name'] = $this->aLocale[$dirSide] . ': ' . $aDirectories[$i]['name'];
        }
        if($oRequest->get('mod_action') == 'form_show'){
            $selectedModule = $this->oItem->module_owner ? ($this->oItem->module_owner . '.' . $this->oItem->module) : '._common';
            foreach($aModules as $aModule){
                if($aModule['value'] == $selectedModule){
                    $module = $aModule['name'];
                    break;
                }
            }
            $this->addField(array('name' => 'header', 'value' => $oItem->header, 'validate' => array('custom', 'tplname')));
            $this->addField(array('name' => 'module', 'value' => $module));
            $this->addField(array('name' => 'path', 'type' => 'select', 'data' => $aDirectories, 'hint' => true));
        }else{
            if(!$this->oItem->getId()){
                $this->oItem->module = $this->oItem->module_owner ? ($this->oItem->module_owner . '.' . $this->oItem->module) : '._common';
                $this->addField(array('name' => 'header', 'value' => $headerVal, 'validate' => array('custom')));
                $this->addField(array('name' => 'path', 'type' => 'select', 'data' => $aDirectories, 'hint' => true));
                $this->addField(array('name' => 'module', 'type' => 'select', 'data' => $aModules));
            }else{
                $selectedModule = $this->oItem->module_owner ? ($this->oItem->module_owner . '.' . $this->oItem->module) : '._common';
                foreach($aModules as $aModule){
                    if($aModule['value'] == $selectedModule){
                        $module = $aModule['name'];
                        break;
                    }
                }
                $this->addField(array('name' => 'header', 'value' => $headerVal, 'validate' => array('custom', 'tplname')));
                $this->addField(array('name' => 'path', 'value' => $this->oItem->path, 'type' => 'static'));
                $this->addField(array('name' => 'module', 'value' => $module, 'type' => 'static'));
            }
        }
        if(!$viewDiff){
            $this->addField(array('name' => 'content', 'type' => 'highlighted', 'rows' => 10, 'height' => AMI_Registry::get('popup_mode', false) ? '200' : '500', 'forceHTMLEncoding' => true));
        }else{
            $this->addScriptFile('_admin/skins/vanilla/_js/diff/difflib.js');
            $this->addScriptFile('_admin/skins/vanilla/_js/diff/diffview.js');
            $this->addScriptFile('_admin/skins/vanilla/_js/diff/jsdiff.js');
            $this->addField(array('name' => 'content', 'type' => 'diff_viewer'));
            $this->aScope['diff_source_length'] = mb_strlen(str_replace("\r\n", "\r", $oItem->content_default), 'ASCII');
            $this->aScope['diff_current_length'] = mb_strlen(str_replace("\r\n", "\r", $origin), 'ASCII');
        }

        if($viewDefaultContent){
            $content = $oItem->content_default;
            $this->aScope['line_pos'] = (int)$oRequest->get('line_pos', 0);
        }else{
            $content = $oItem->content;
        }
        $nsDefault = preg_replace("/\s+/", " ", preg_replace("/^[\s\n\r]+/", "", $oItem->content_default));
        $nsContent = preg_replace("/\s+/", " ", preg_replace("/^[\s\n\r]+/", "", $oItem->content));
        if($nsContent == $nsDefault){
            $this->aScope['content_is_default'] = 1;
        }else{
            $this->aScope['allow_restore_by_default'] = !defined('TEMPLATES_FROM_DISK');
        }

        $this->addScriptCode(
            $this->parse(
                'javascript',
                array(
                    'type'          => $this->getModId(),
                    'is_readonly'   => $readonly,
                    'currentTpl'    => $oItem->path . $oItem->header
                )
            )
        );

        $this->aScope['copy_button'] = 0;
        if($oRequest->get('copy_button', false)){
            $this->aScope['copy_button'] = 1;
            $this->addField(array('name' => 'copy_button', 'value' => 1, 'type' => 'hidden'));
        }
        if(!defined('TEMPLATES_FROM_DISK') && !empty($this->oItem->content_default)){
            $this->aFormButtons[] = 'restore';
        }

        if(!$viewDiff){
            $aMatches = array();
            preg_match_all("/^\%\%include.+\"(.*)\"\%\%/iUm", $content, $aMatches);
            if((count($aMatches) > 1) && (count($aMatches[0]))){
                $sectionAdded = false;

                foreach($aMatches[1] as $idx => $match){
                    $src = $aMatches[0][$idx];

                    $module = false;
                    $tplType = false;
                    if(strpos($src, 'include_template')){
                        $module = 'modules_templates';
                        $tplType = 'tpl';
                    }
                    if(strpos($src, 'include_language')){
                        $module = 'modules_templates_langs';
                        $tplType = 'lng';
                    }
                    if(!$module){
                        continue;
                    }

                    $path = substr($match, 0, strrpos($match, '/') + 1);
                    $tplName = substr($match, strlen($path));

                    $oModList = AMI::getResourceModel($module . '/table')
                        ->getList()
                        ->addColumns(array('id', 'side'))
                        ->addWhereDef(
                            DB_Query::getSnippet('AND path=%s AND name=%s')  // AND allowed=1
                            ->q($path)
                            ->q($tplName)
                        );

                    if($oItem->side != 'shared'){
                        $oModList->addWhereDef(
                            DB_Query::getSnippet('AND side=%s')
                            ->q($oItem->side)
                        );
                    }

                    $oList = $oModList->load();

                    if($oList && count($oList)){
                        foreach($oList as $oItem){
                            if(!$sectionAdded){
                                $this->addSection("tplhint", "content.before");
                                $this->addSection("includes", "content.after");
                                $sectionAdded = true;
                            }
                            $name = 'include_' . $oItem->getId();
                            $this->addField(
                                array(
                                    'name'      => $name,
                                    'value'     => $match,
                                    'type'      => 'hint',
                                    'id'        => $oItem->getId(),
                                    'module'    => $module,
                                    'tpl_type'  => $this->aLocale['type_' . $tplType],
                                    'side'      => $this->aLocale['tpl_' . $oItem->side],
                                    'hint'      => true,
                                    'hint_text' => $this->aLocale['hint_path'],
                                    'hint_label'=> $this->aLocale['tpl_' . $oItem->side]
                                ),
                                "includes.end"
                            );
                        }
                    }else{
                        if(!$sectionAdded){
                            $this->addSection("tplhint", "content.before");
                            $this->addSection("includes", "content.after");
                            $sectionAdded = true;
                        }
                        $name = 'include_' . uniqid();
                        $this->addField(
                            array(
                                'name'      => $name,
                                'value'     => $match,
                                'type'      => 'notfound',
                                'tpl_type'  => $this->aLocale['type_' . $tplType],
                            ),
                            "includes.end"
                        );
                    }
                }
            }
        }
        if(!$viewDefaultContent && !empty($this->oItem->content_default)){
            $this->addField(
                array(
                    'name'     => 'content_default',
                    'value'    => $this->oItem->header,
                    'id'       => $this->oItem->getId(),
                    'module'   => $this->getModId(),
                    'tpl_type' => $this->aLocale['type_' . ($this->getModId() == 'modules_templates' ? 'tpl' : 'lng')],
                    'side'     => $this->aLocale['tpl_' . $this->oItem->side],
                    'type'     => 'content_default'
                )
            );
        }
        return $this;
    }
}

/**
 * AmiModulesTemplates hypermodule admin list component action controller.
 *
 * @package    Hyper_AmiModulesTemplates
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiModulesTemplates_ListAdm extends AMI_Module_ListAdm{
    /**
     * Default list order
     *
     * @var array
     * @amidev
     */
    protected $aDefaultOrder = array(
        'col' => 'modified',
        'dir' => 'desc'
    );

    /**
     * Initialization.
     *
     * @return Hyper_AmiModulesTemplates_ListAdm
     */
    public function init(){
        $this->listActionsResId = $this->getModId() . '/list_actions/controller/adm';
        $this->listGrpActionsResId = $this->getModId() . '/list_group_actions/controller/adm';
        if(!defined('TEMPLATES_FROM_DISK')){
            $this->addGroupActions(array(array(self::REQUIRE_FULL_ENV . 'restore', 'common_section')));
        }
        parent::init();
        $this->dropActions(self::ACTION_GROUP, array('seo_section', 'common_section', 'meta_section'));
        $this->dropActions(self::ACTION_GROUP, array('delete', 'move_position'));

        $this->dropActions(AMI_ModListAdm::ACTION_COLUMN, array('public'));
        $this->dropActions(AMI_ModListAdm::ACTION_COMMON);
        $this->addActions(array('edit', 'show', 'copy', 'delete'));
        $this->addActionCallback('common', 'show');
        $this->addActionCallback('common', 'delete');
        if(!defined('TEMPLATES_FROM_DISK')){
            $this->addActions(array('copy'));
            $this->addActionCallback('common', 'copy');
            $this->addGroupActions(array(array(self::REQUIRE_FULL_ENV . 'restore', 'common_section')));
        }
        return $this;
    }
}

/**
 * AmiModulesTemplates hypermodule admin list component actions controller.
 *
 * @package    Hyper_AmiModulesTemplates
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiModulesTemplates_ListActionsAdm extends AMI_Module_ListActionsAdm{
    /**
     * Dispatches 'delete' action.
     *
     * Deletes item.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchDelete($name, array $aEvent, $handlerModId, $srcModId){
        $oItem = $this->getItem($this->getRequestId(), array('id', 'is_sys', 'side'));
        if($oItem && $oItem->getId()){
            if($oItem->is_sys || ($oItem->side == 'admin')){
                $aEvent['oResponse']->addStatusMessage('status_del_fail', array(), AMI_Response::STATUS_MESSAGE_ERROR);
                $this->refreshView();
                return $aEvent;
            }
        }
        $aEvent = parent::dispatchDelete($name, $aEvent, $handlerModId, $srcModId);
        // Clear parsed data if delete was succesful
        if(empty($aEvent['failed'])){
            Hyper_AmiModulesTemplates_Service::dropParsedData();
        }
        return $aEvent;
    }

    /**
     * Dispatches 'copy' action.
     *
     * Copies item.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchCopy($name, array $aEvent, $handlerModId, $srcModId){
        $bIsLangs = ($handlerModId == 'modules_templates_langs');

        $oItem = $this->getItem($this->getRequestId(), array('*'));
        if($oItem){
            if(defined('TEMPLATES_FROM_DISK') || !$oItem->getId() || ($oItem->side == 'admin')){
                $aEvent['oResponse']->addStatusMessage('status_copy_fail', array(), AMI_Response::STATUS_MESSAGE_ERROR);
                $this->refreshView();
                return $aEvent;
            }
        }
        $oItem->resetId();
        $oNow = DB_Query::getSnippet('NOW()');

        // Copy header
        $copyName = substr($oItem->header, 0, -4) . '_copy';

        $oCopyList = AMI::getResourceModel($handlerModId . '/table')->getlist();
        $oCopyList->addColumns(array('id', 'header'));
        $sql = " AND module = %s AND name LIKE %s";
        $oSnippet = DB_Query::getSnippet($sql);
        $oCopyList->addWhereDef($oSnippet->q($oItem->module)->q($copyName . '%'));
        $oCopyList->load();
        $names = '';
        foreach($oCopyList as $oCopyItem){
            $names .= ('|' . $oCopyItem->header);
        }
        $addOn = '';
        $aMatches = array();
        if(preg_match_all('/\|' . str_replace('.', '\.', $copyName) . '(\d*)\./iU', $names, $aMatches)){
            foreach($aMatches[1] as $idx){
                if((int)$idx >= (int)$addOn){
                    $addOn = (int)$idx + 1;
                }
            }
        }
        $copyName .= $addOn;
        $copyName .= ($bIsLangs) ? '.lng' : '.tpl';

        $oItem->is_sys       = 0;
        $oItem->header       = $copyName;
        $oItem->created      = $oNow;
        $oItem->modified     = $oNow;
        $oItem->synchronized = $oNow;
        $oItem->save();
        $aEvent['oResponse']->addStatusMessage('status_copy', array('name' => $copyName));
        AMI_Registry::set($handlerModId . '/editId', $oItem->getId());
        $this->refreshView();

        return $aEvent;
    }
}

/**
 * AmiModulesTemplates hypermodule admin list component group actions controller.
 *
 * @package    Hyper_AmiModulesTemplates
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiModulesTemplates_ListGroupActionsAdm extends AMI_Module_ListGroupActionsAdm{
    /**
     * Dispatches 'grp_restore' group action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchGrpRestore($name, array $aEvent, $handlerModId, $srcModId){
        $oCore = $GLOBALS['Core'];
        $aRequestIds = $this->getRequestIds($aEvent);
        $numRestored = 0;
        foreach($aRequestIds as $id){
            $oModel = AMI::getResourceModel($aEvent['tableModelId']);
            $oItem = $oModel->find($id, array('id', 'side', 'content', 'content_default'));
            if($oItem->getId() && ($oItem->side != 'admin') && !empty($oItem->content_default)){
                $oItem->content = $oItem->content_default;
                $oItem->save();
                $numRestored += 1;
            }
        }
        $aEvent['oResponse']->addStatusMessage('status_grp_restore', array('num_items' => $numRestored));
        $this->refreshView();
        $oCore->Cache->ClearAdd("all");
        return $aEvent;
    }
}

/**
 * AmiModulesTemplates hypermodule admin list component view.
 *
 * @package    Hyper_AmiModulesTemplates
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiModulesTemplates_ListViewAdm extends AMI_Module_ListViewAdm{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'modified';

    /**
     * Order column direction
     *
     * @var bool
     */
    protected $orderDirection = 'desc';

    /**
     * List default elemnts template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#list_header',
            '#flags', 'flags',
            '#common', 'common',
            '#columns', 'path', 'header', 'module', 'content_length', 'content_not_default', 'modified', 'columns',
            '#actions', 'edit', 'show', 'copy', 'delete', 'actions',
        'list_header'
    );

    /**
     * Modules names
     *
     * @var array
     */
    protected $aModuleNames = array();

    /**
     * Owners names
     *
     * @var array
     */
    protected $aOwnersNames = array();

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return Hyper_AmiModulesTemplates_ListViewAdm
     */
    public function init(){
        parent::init();
        $this->aModuleNames = AMI::getSingleton('env/template_sys')->parseLocale('templates/lang/_menu_all.lng');
        $this->aOwnersNames = AMI::getSingleton('env/template_sys')->parseLocale('templates/lang/_menu_owners.lng');
        $this
            ->removeColumn('date_created')
            ->removeColumn('announce')
            ->removeColumn('position')
            ->removeColumn('public')
            ->addColumnType('is_sys', 'hidden')
            ->addColumnType('side', 'hidden')
            ->addColumnType('content_default', 'hidden')
            ->addColumnType('is_restore_allowed', 'hidden')
            ->addColumn('content_not_default')
            ->setColumnWidth('content_not_default', 'extra-narrow')
            ->addColumn('path')
            ->addColumnType('module_owner', 'hidden')
            ->addColumn('module')
            ->addColumn('content_length')
            ->setColumnAlign('content_length', 'center')
            ->addColumnType('modified', 'datetime')
            ->setColumnTensility('path')
            ->setColumnTensility('module')
            ->addSortColumns(
                array(
                    'content_not_default', 'path', 'header', 'module', 'modified', 'content_length'
                )
            );

        $this->setColumnLayout('content_not_default', array('align' => 'center'));
        $this->formatColumn(
            'content_not_default',
            array($this, 'fmtColIcon'),
            array(
                'class'    => 'checked',
                'noAction' => TRUE
            )
        );
        $this->formatColumn(
            'modified',
            array($this, 'fmtDateTime'),
            array(
                'format' => AMI_Lib_Date::FMT_BOTH
            )
        );

        AMI_Event::addHandler('on_list_body_{path}', array($this, 'handlePathAndHeader'), $this->getModId());
        AMI_Event::addHandler('on_list_body_{header}', array($this, 'handlePathAndHeader'), $this->getModId());
        AMI_Event::addHandler('on_list_body_{module}', array($this, 'handleModuleName'), $this->getModId());
        AMI_Event::addHandler('on_list_body_{modified}', array($this, 'handleModifiedDate'), $this->getModId());
        AMI_Event::addHandler('on_list_body_{actions}', array($this, 'handleActions'), $this->getModId());
        AMI_Event::addHandler('on_list_body_{content_length}', array($this, 'handleContentLength'), $this->getModId());
        AMI_Event::addHandler('on_list_body_{is_restore_allowed}', array($this, 'handleIsRestoreAllowed'), $this->getModId());
        AMI_Event::addHandler('on_list_add_columns', array($this, 'handleListAddColumns'), $this->getModId());

        $this->addScriptCode($this->getTemplate()->parse($this->tplBlockName . ':javascript'));

        return $this;
    }

    /**
     * Adds expression column content_length.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListAddColumns($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['oList']->addExpressionColumn('content_length', 'LENGTH(content)');
        $sql =
            "(" .
                "`side` != %s AND " .
                "`content_default` IS NOT NULL AND " .
                "TRIM(" . // BOTH %s FROM " .
                    "REPLACE(" .
                        "REPLACE(`content`, %s, %s), " .
                        "%s, " .
                        "%s" .
                    ")" .
                ") != TRIM(" . // BOTH %s FROM " .
                    "REPLACE(" .
                        "REPLACE(`content_default`, %s, %s), " .
                        "%s, " .
                        "%s" .
                    ")" .
                ")" .
            ")";
        $oQuery =
            DB_Query::getSnippet($sql)
            ->q('admin')
            // ->q(" \r\n")
            ->q("\r")->q('')
            ->q("\n")->q(' ')
            // ->q(" \r\n")
            ->q("\r")->q('')
            ->q("\n")->q(' ');
        $aEvent['oList']->addExpressionColumn('content_not_default', $oQuery);

        return $aEvent;
    }

    /**
     * Prepare path/header field.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handlePathAndHeader($name, array $aEvent, $handlerModId, $srcModId){
        if($aEvent['aScope']['side'] == 'admin'){
            $aEvent['aScope']['list_col_value'] =
                $this->getTemplate()->parse(
                    $this->tplBlockName . ':admin_hint',
                    array('value' => $aEvent['aScope']['list_col_value'])
                );
        }
        return $aEvent;
    }
    /**
     * Prepare module field.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleModuleName($name, array $aEvent, $handlerModId, $srcModId){
        $modId = $aEvent['aScope']['module'];
        $ownerId = $aEvent['aScope']['module_owner'];
        $modName = $modId;
        if(mb_strpos($modName, '!') === 0){
            $modName = $this->aLocale['common'];
        }else{
            $aModNames = AMI_Service_Adm::getModulesCaptions(array($modId), TRUE, array(), TRUE);
            if(isset($aModNames[$modId]) && mb_strlen($aModNames[$modId]) > 0){
                $modName = $aModNames[$modId];
            }
        }
        $ownerName = ($ownerId && isset($this->aOwnersNames[$ownerId])) ? $this->aOwnersNames[$ownerId] : $ownerId;
        $aEvent['aScope']['list_col_value'] = ($modName && $ownerName) ? ($ownerName . ' : ' . $modName) : $this->aLocale['common'];
        return $aEvent;
    }

    /**
     * Prepare content_length field.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleContentLength($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['aScope']['list_col_value'] = AMI_Lib_String::getBytesAsText($aEvent['aScope']['list_col_value'], $this->aLocale, 1);
        return $aEvent;
    }

    /**
     * Prepare is_restore_allowed field.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleIsRestoreAllowed($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['aScope']['list_col_value'] = 0;
        if(($aEvent['aScope']['side'] != 'admin') && !empty($aEvent['oItem']->content_default)){
            $aEvent['aScope']['list_col_value'] = 1;
        }
        return $aEvent;
    }

    /**
     * Prepare modified date field.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleModifiedDate($name, array $aEvent, $handlerModId, $srcModId){
        if($aEvent['aScope']['side'] == 'admin'){
            return $aEvent;
        }
        $aDiskTemplates = Hyper_AmiModulesTemplates_Service::getModifiedTemplates();
        if(isset($aDiskTemplates["modified_ids"]) && count($aDiskTemplates["modified_ids"])){
            $value = $aEvent['aScope']['list_col_value'];
            $id = $aEvent['aScope']['id'];
            $aEvent['aScope']['list_col_value'] = (in_array($id, $aDiskTemplates["modified_ids"])) ? $this->parse('modified', array('value' => $value)) : $value;
        }
        return $aEvent;
    }

    /**
     * Prepare actions column.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleActions($name, array $aEvent, $handlerModId, $srcModId){
        if($aEvent['aScope']['side'] == 'admin'){
            unset($aEvent['aScope']["_action_col"]['delete']);
            unset($aEvent['aScope']["_action_col"]['edit']);
            unset($aEvent['aScope']["_action_col"]['copy']);
            unset($aEvent['aScope']["_actions"][array_search('delete', $aEvent['aScope']["_actions"])]);
            unset($aEvent['aScope']["_actions"][array_search('edit', $aEvent['aScope']["_actions"])]);
            unset($aEvent['aScope']["_actions"][array_search('copy', $aEvent['aScope']["_actions"])]);
            unset($aEvent['aScope']['list_col_value']['delete']);
            unset($aEvent['aScope']['list_col_value']['edit']);
            unset($aEvent['aScope']['list_col_value']['copy']);
        }else{
            if($aEvent['aScope']['is_sys']){
                unset($aEvent['aScope']["_action_col"]['delete']);
                unset($aEvent['aScope']["_actions"][array_search('delete', $aEvent['aScope']["_actions"])]);
                unset($aEvent['aScope']['list_col_value']['delete']);
            }
            unset($aEvent['aScope']["_action_col"]['show']);
            unset($aEvent['aScope']["_actions"][array_search('show', $aEvent['aScope']["_actions"])]);
            unset($aEvent['aScope']['list_col_value']['show']);
        }
        return $aEvent;
    }
}
