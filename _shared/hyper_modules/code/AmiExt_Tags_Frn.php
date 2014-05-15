<?php
/**
 * AmiExt/Tags extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiExt_Tags
 * @version   $Id: AmiExt_Tags_Frn.php 47246 2014-01-30 14:28:59Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/Tags extension configuration front controller.
 *
 * @package    Config_AmiExt_Tags
 * @subpackage Controller
 * @resource   ext_tags/module/controller/frn <code>AMI::getResource('ext_tags/module/controller/frn')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Tags_Frn extends Hyper_AmiExt{
    /**
     * Current module Id
     *
     * @var string
     */
    protected $curModuleId = '';

    /**
     * Array of extension fields
     *
     * @var array
     */
    protected $aExtFields = array('tags');

    /**
     * Ids to collect tags
     *
     * @var array
     */
    protected $aTagIds = array();

    /**
     * Tags data
     *
     * @var array
     */
    protected $aTagsData = array();

    /**
     * Flag specifies that tags has been loaded
     *
     * @var bool
     * @amidev Temporary
     */
    protected $isTagsLoaded = false;

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
        $modId = $aEvent['modId'];
        $this->curModuleId = $modId;
        $oView = $this->getView(AMI_Registry::get('side'));
        $bodyType = AMI_Registry::get('AMI/Module/Environment/bodyType', FALSE);

        if(!AMI::isModInstalled('srv_tags_reindex') || ($bodyType == 'specblock')){
            $oTpl = AMI::getResource('env/template_sys');
            $oTpl->addGlobalVars(
                array(
                    'EXT_TAGS'         => '0',
                    'ext_tags_enabled' => FALSE // #CMS-11470
                )
            );
            return $aEvent;
        }

        AMI_Event::addHandler('on_list_recordset', array($this, 'handleListRecordset'), $modId);
        AMI_Event::addHandler('on_list_recordset_loaded', array($this, 'handleListRecordsetLoaded'), $modId);
        AMI_Event::addHandler('on_item_details', array($this, 'handleDetails'), $modId);

        if($oView){
            $oView->setExt($this);
            AMI_Event::addHandler('on_before_view_items', array($oView, 'handleBeforeView'), $modId);
            AMI_Event::addHandler('on_before_view_details', array($oView, 'handleBeforeView'), $modId);
            AMI_Event::addHandler('on_list_body_row', array($oView, 'handleDetails'), $modId);
            AMI_Event::addHandler('on_item_details', array($oView, 'handleDetails'), $modId);
        }

        return $aEvent;
    }

    /**
     * Add extension tags fields.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListRecordset($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['oQuery']->addFields($this->aExtFields);
        return $aEvent;
    }

    /**
     * Handles list recordset.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListRecordsetLoaded($name, array $aEvent, $handlerModId, $srcModId){
        $this->isTagsLoaded = false;
        $this->aTagIds = array();
        foreach($aEvent['oList'] as $oItem){
            $this->setTagIds($oItem->tags);
        }
        $aEvent['oList']->rewind();
        return $aEvent;
    }

    /**
     * Handles item details.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleDetails($name, array $aEvent, $handlerModId, $srcModId){
        $this->isTagsLoaded = false;
        $this->aTagIds = array();
        $this->setTagIds($aEvent['aData']['tags']);
        return $aEvent;
    }

    /**#@-*/

    /**
     * Get working module Id.
     *
     * @return string
     * @amidev Temporary
     */
    public function getCurModuleId(){
        return $this->curModuleId;
    }

    /**
     * Set tag ids.
     *
     * @param  string $tags  Tags comma separated
     * @return array
     * @amidev Temporary
     */
    public function setTagIds($tags = ''){
        if($tags){
            $aTagIds = explode(',', trim($tags, ','));
            if(is_array($aTagIds) && sizeof($aTagIds)){
                foreach($aTagIds as $idTag){
                    if(!in_array($idTag, $this->aTagIds)){
                        $this->aTagIds[] = (int)$idTag;
                        $this->isTagsLoaded = false;
                    }
                }
            }
        }
        $this->loadTagsData();
        return $this->aTagIds;
    }

    /**
     * Get tag ids.
     *
     * @return array
     * @amidev Temporary
     */
    public function getTagIds(){
        return $this->aTagIds;
    }

    /**
     * Load and returns tags data.
     *
     * @return array
     * @amidev Temporary
     */
    public function loadTagsData(){
        if(!$this->isTagsLoaded && sizeof($this->aTagIds)){
            $tags = implode(',', $this->aTagIds);
            $oTagsQuery = new DB_Query('`cms_tags`');
            $oTagsQuery->addField('*');
            $oTagsQuery->addWhereDef(DB_Query::getSnippet('AND `id` IN (%s)')->plain($tags));
            $oTagsRS = AMI::getSingleton('db')->select($oTagsQuery);
            if($oTagsRS->count()){
                foreach($oTagsRS as $aRow){
                    $this->aTagsData[$aRow['id']] = $aRow;
                }
            }
            $this->isTagsLoaded = true;
        }

        return $this->aTagsData;
    }

    /**
     * Get tags data.
     *
     * @return array
     * @amidev Temporary
     */
    public function getTagsData(){
        return $this->aTagsData;
    }
}

/**
 * AmiExt/Tags extension configuration front view.
 *
 * @package    Config_AmiExt_Tags
 * @subpackage View
 * @resource   ext_tags/view/frn <code>AMI::getResource('ext_tags/view/frn')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Tags_ViewFrn extends AMI_ExtView{
    /**
     * Template block name
     *
     * @var string
     */
    protected $tplBlockName = 'tags_ext';

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Gets module block name.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleBeforeView($name, array $aEvent, $handlerModId, $srcModId){
        $this->tplBlockName = $aEvent['block'];
        $this->aLocale = array_merge($aEvent['aLocale'], $this->aLocale);
        return $aEvent;
    }

    /**
     * Fills front tags data.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleDetails($name, array $aEvent, $handlerModId, $srcModId){
        $aData = $aEvent['aData'];
        $curModule = $this->oExt->getCurModuleId();

        $tags = isset($aData['tags']) ? trim($aData['tags'], ',') : '';
        if($tags !== ''){
            $aTagsData = $this->oExt->getTagsData();
            $aTagIds = explode(',', $tags);
            if(is_array($aTagIds) && sizeof($aTagIds)){
                $tagFrontLink = AMI_PageManager::getModLink(
                    'srv_tags',
                    AMI_Registry::get('lang_data'),
                    0,
                    TRUE,
                    TRUE
                );
                if($tagFrontLink !== FALSE){
                    $res = '';
                    foreach($aTagIds as $idTag){
                        if(isset($aTagsData[$idTag])){
                            $aItemNavData = array();
                            $aItemNavData['modId'] = 'srv_tags';
                            $aItemNavData['id'] = $aTagsData[$idTag]['id'];
                            $aItemNavData['id_sublink'] = $aTagsData[$idTag]['sublink'];
                            $aNavData = AMI_PageManager::applyNavData($aItemNavData);

                            $aTplData = $aTagsData[$idTag];
                            $aTplData['tag_front_link'] = $tagFrontLink;
                            $aTplData = array_merge($aTplData, $aNavData);
                            $res .= $this->parse('tag_link', $aTplData);
                        }
                    }
                    $aTplData['list'] = $res;
                    $aEvent['aData']['tags_extension'] = $this->parse('tags_extension', $aTplData);
                    unset($aTplData['list']);
                }
            }
        }

        return $aEvent;
    }

    /**#@-*/
}
