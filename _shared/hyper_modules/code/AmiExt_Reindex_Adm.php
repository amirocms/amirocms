<?php
/**
 * AmiExt/Reindex extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiExt_Reindex
 * @version   $Id: AmiExt_Reindex_Adm.php 42166 2013-10-10 12:29:32Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/Reindex extension configuration admin controller.
 *
 * @package    Config_AmiExt_Reindex
 * @subpackage Controller
 * @resource   ext_reindex/module/controller/adm <code>AMI::getResource('ext_reindex/module/controller/adm')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Reindex_Adm extends Hyper_AmiExt{

    /**
     * ModuleReindexFunctions object.
     *
     * @var object
     */
    private $reindexFunctions;

    /**
     * Public action flag.
     *
     * @var bool
     */
    private $bPublicAction = false;

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Callback called after module is uninstalled without cheking unistallation mode.
     *
     * Cleans up uninstalled module data.
     *
     * @param  string         $modId  Unnstalled module id
     * @param  AMI_Tx_Cmd_Args $oArgs  Transaction command arguments
     * @return void
     */
    public function onModPostUninstallUnmasked($modId, AMI_Tx_Cmd_Args $oArgs){
        $oDB = AMI::getSingleton('db');
        $oQuery =
            DB_Query::getSnippet(
                "DELETE FROM `cms_site_search` " .
                "WHERE `module_name` = %s"
            )->q($modId);
        $oDB->query($oQuery);
        $oQuery =
            DB_Query::getSnippet(
                "DELETE FROM `cms_site_search_index` " .
                "WHERE `module_name` = %s"
            )->q($modId);
        $oDB->query($oQuery);
    }

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

    	if(isset($GLOBALS['cms']) && $GLOBALS['cms']->Core->getModOption('reindex', 'allow_runtime_indexing')){
            AMI_Event::addHandler('on_after_delete_model_item', array($this, 'handleModelDelete'), $aEvent['modId'], AMI_Event::PRIORITY_LOW);
            AMI_Event::addHandler('on_after_save_model_item', array($this, 'handleModelSave'), $aEvent['modId'], AMI_Event::PRIORITY_LOW);
            AMI_Event::addHandler('dispatch_mod_action_list_public', array($this, 'handlePublicAction'), $aEvent['modId'], AMI_Event::PRIORITY_HIGH);
            AMI_Event::addHandler('dispatch_mod_action_list_grp_public', array($this, 'handlePublicAction'), $aEvent['modId'], AMI_Event::PRIORITY_HIGH);
            AMI_Event::addHandler('dispatch_mod_action_list_un_public', array($this, 'handlePublicAction'), $aEvent['modId'], AMI_Event::PRIORITY_HIGH);
            AMI_Event::addHandler('dispatch_mod_action_list_grp_un_public', array($this, 'handlePublicAction'), $aEvent['modId'], AMI_Event::PRIORITY_HIGH);

            $oCMS = $GLOBALS['cms'];
            $oDB = new DB_si;
            $oActiveModule = $oCMS->Core->getModule($srcModId);
            // $oActiveModule->InitEngine($oCMS, $oDB);
            // $oModule = $oActiveModule->Engine;
            $oModule = NULL;

            $this->reindexFunctions = new ModuleReindexFunctions($oCMS, $oDB, $oActiveModule, $oModule);
        }

    	return $aEvent;
    }

    /**#@-*/

    /**
     * Handle public action. Setup public flag for future purpose.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTableList::getListRecordset()
     */
    public function handlePublicAction($name, array $aEvent, $handlerModId, $srcModId){

    	$this->bPublicAction = true;

    	return $aEvent;
    }

    /**
     * Handle model save actions.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTableList::getListRecordset()
     */
    public function handleModelSave($name, array $aEvent, $handlerModId, $srcModId){
        $lang = AMI_Registry::get('lang_data');
        if($this->bPublicAction){
            $this->reindexFunctions->publishById($aEvent['oItem']->id, $this->reindexFunctions->module, $this->reindexFunctions->db, $lang);
        }else{
            $this->reindexFunctions->reindexById($aEvent['oItem']->id, $this->reindexFunctions->module, $this->reindexFunctions->db, $lang);
        }

        return $aEvent;
    }

    /**
     * Handle model delete actions.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTableList::getListRecordset()
     */
    public function handleModelDelete($name, array $aEvent, $handlerModId, $srcModId){

        $this->reindexFunctions->deleteFromIndexById($aEvent['aData']['id'], $srcModId);

        return $aEvent;
    }
}
