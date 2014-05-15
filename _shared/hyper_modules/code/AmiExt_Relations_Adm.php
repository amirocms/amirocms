<?php
/**
 * AmiExt/Relations extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiExt_Relations
 * @version   $Id: AmiExt_Relations_Adm.php 40881 2013-08-19 14:09:34Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/Relations extension configuration admin controller.
 *
 * @package    Config_AmiExt_Relations
 * @subpackage Controller
 * @resource   ext_relations/module/controller/adm <code>AMI::getResource('ext_relations/module/controller/adm')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Relations_Adm extends Hyper_AmiExt{
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
                "DELETE FROM `cms_relations` " .
                "WHERE `module` = %s OR `related_module` = %s"
            )->q($modId)->q($modId);
        $oDB->query($oQuery);
    }

    /**#@+
     * Event handler.
     *
     * @see AMI_Event::addHandler()
     * @see AMI_Event::fire()
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
        $oView = $this->getView(AMI_Registry::get('side'));
        $oView->setExt($this);
        AMI_Event::addHandler('on_form_fields_form', array($oView, 'handleForm'), $aEvent['modId']);
        return $aEvent;
    }

    /**#@-*/

    /**
     * Returns number of relations.
     *
     * @param  string $objId  Object id
     * @param  string $modId  Module id
     * @return int
     */
    public function getRelationsNumber($objId, $modId){
        $res = 0;

        if($objId && $modId){
            $oQuery = new DB_Query('cms_relations');
            $oQuery->addFields(array('related_objects'));
            $oQuery->addWhereDef(DB_Query::getSnippet('AND module = %s AND id_object = %s')->q($modId)->q($objId));
            $oRelationsRS = AMI::getSingleton('db')->select($oQuery);
            if($oRelationsRS->count()){
                foreach($oRelationsRS as $aRow){
                    $relatedObjects = explode(',', trim($aRow['related_objects'], ','));
                    if(is_array($relatedObjects)){
                        $res += sizeof($relatedObjects);
                    }
                }
            }
        }

        return $res;
    }
}

/**
 * AmiExt/Relations extension configuration admin view.
 *
 * @package    Config_AmiExt_Relations
 * @subpackage View
 * @resource   ext_relations/view/adm <code>AMI::getResource('ext_relations/view/adm')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Relations_ViewAdm extends AMI_ExtView{
    /**
     * Template block name
     *
     * @var string
     */
    protected $tplBlockName = 'relations_ext';

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Adds relations field onto admin form.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleForm($name, array $aEvent, $handlerModId, $srcModId){
        $oFormView = $this->oExt->getFormViewInstance($aEvent);
        if(!$oFormView){
            return $aEvent;
        }
        $oTpl = $this->getTemplate();
        $oTpl->setBlockLocale($this->tplBlockName, $this->aLocale);
        $aScope = $aEvent['aScope'];
        $oFormView->addTemplate($this->tplFileName, 'ext_relations', $this->aLocale);
        $aScope['id'] = (int)($aEvent['oItem']->getValue('id'));
        $aScope['relations_number'] = $this->oExt->getRelationsNumber($aScope['id'], $aScope['_mod_id']);
        $oFormView->addField(
            array(
                'name'     => 'ext_relations',
                'position' => 'ext_relations.end',
                'html'     => $oTpl->parse($this->tplBlockName . ':ext_relations_field', $aScope)
            )
        );
        return $aEvent;
    }

    /**#@-*/
}
