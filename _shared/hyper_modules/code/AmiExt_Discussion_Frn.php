<?php
/**
 * AmiExt/Discussion extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiExt_Discussion
 * @version   $Id: AmiExt_Discussion_Frn.php 50071 2014-04-18 13:32:21Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/Discussion extension configuration front controller.
 *
 * @package    Config_AmiExt_Discussion
 * @subpackage Controller
 * @resource   ext_discussion/module/controller/frn <code>AMI::getResource('ext_discussion/module/controller/frn')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Discussion_Frn extends Hyper_AmiExt{
    /**
     * Flag specifying to do fields mapping
     *
     * @var bool
     */
    protected $doMapping;

    /**
     * Mapped field name
     *
     * @var string
     */
    protected $fieldName;

    /**
     * Front module id
     *
     * @var string
     */
    protected $frnModId = '';

    /**
     * Old emvironment module
     *
     * @var CMS_Module
     */
    protected $oModule;

    /**
     * Old emvironment module engine
     *
     * @var ModuleDiscussion
     */
    protected $oEngine;

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
     * @todo  avoiding preg_replace hack
     */
    public function __construct($modId, $optSrcId = '', AMI_Mod $oController = null){
        $this->isCatMod = preg_match('/_cat$/', $modId);
        $this->frnModId = preg_replace('/^ext_/', '', $this->getExtId());
        $this->addModule($this->frnModId);

        parent::__construct($modId, $optSrcId, $oController);
    }

    /**
     * Initializes old anvironment module.
     *
     * @param  string $extModule  Custom extension module id
     * @return ModuleDiscussion
     * @amidev Temporary?
     */
    public function initOldEnvModule($extModule = ''){
        static
            $lastExtModId,
            $aExtOptions = array(
                'page_size', 'messages_pages', 'add_messages_by_registered_only',
                'noindex_external_links', 'sys_user_as_administration', 'front_images',
                'watch_comments', 'from_mbox', 'updatable_period', 'front_page_sort_col',
                'front_page_sort_dim'
            ),
            $aModuleOptions = array(
                'notify_admin_about_new_message', 'forum_webmaster_email', 'use_tree_view',
                'forum_pager_page_number_as_bound', 'show_forum_link_in_small'
            );

        if(isset($lastExtModId) && $lastExtModId == $extModule && get_class($this->oModule)){
            return $this->oEngine;
        }

        global $Core, $cms, $db;

        $this->oModule = &$Core->GetModule($this->frnModId);
        $this->oModule->InitEngine($cms, $db);
        $this->oEngine = &$this->oModule->Engine;
        foreach($aExtOptions as $optionName){
            $this->oModule->SetOption($optionName, $this->getOption($optionName));
        }
        foreach($aModuleOptions as $optionName){
            $this->oModule->SetOption(
                $optionName,
                $this->issetModOption($optionName) ? $this->getModOption($optionName) : FALSE
            );
        }

        $this->oEngine->Init(
            array(
                'discussion_list' => 'templates/discussion.tpl'
            ),
            'templates/lang/_ext_discussion_msgs.lng',
            'templates/lang/forum.lng'
        );
        $this->oEngine->fltExtModule = $extModule == '' ? $this->getModId() : $extModule;

        return $this->oEngine;
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
        $modId = $aEvent['modId'];
        $oView = $this->getView('frn');
        $oView->setExt($this);

        AMI_Event::addHandler('on_add_field_mapping', array($this, 'handleAddFieldMapping'), $modId);
        AMI_Event::addHandler('on_get_available_fields', array($this, 'handleGetAvailableFields'), $modId);

        if(
            $this->getModOption('show_forum_count_replies') &&
            $this->getModOption('show_forum_link_in_list')
        ){
            AMI_Event::addHandler('on_list_init', array($this, 'handleListInit'), $modId);
        }

        AMI_Event::addHandler('on_query_add_table', array($this, 'handleQuery'), $modId);
        AMI_Event::addHandler('on_post_process_fields', array($oView, 'handlePostProcessFields'), $modId);


        if(
            !AMI_Registry::exists('ami_specblock_mode') &&
            ($this->isCatMod ? AMI_Registry::get('page/itemId', 0) < 1 : TRUE)
        ){
            $mainModId = preg_replace('/_cat$/', '', $modId);
            AMI_Event::addHandler('dispatch_action_forum_add', array($this, 'handleAddCommentAction'), $mainModId);
            AMI_Event::addHandler('dispatch_action_forum_watching', array($this, 'handleAddCommentWatching'), $mainModId);
        }

        $oRequest = AMI::getSingleton('env/request');
        if(
            !$this->isCatMod &&
            AMI_Registry::get('page/itemId', 0) > 0 &&
            ($this->getModOption('show_forum_at_details') || $oRequest->get('forum_ext', FALSE))
        ){
            // Item details
            if(is_object($this->oModController)){
                $this->oModController->setForcePageSize($this->getOption('page_size'));
            }
        }

        if(
            AMI_Registry::exists('page/catId') &&
            AMI_Registry::get('page/catId') > 0 &&
            $this->isCatMod &&
            $oRequest->get('forum_ext', FALSE)
        ){
            $itemModId = preg_replace('/_cat$/', '', $modId);
            AMI_Event::addHandler('on_before_init_componets', array($this, 'handleBeforeInitComponents'), $itemModId);
        }

        return $aEvent;
    }

    /**
     * Manipulates with front components.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleBeforeInitComponents($name, array $aEvent, $handlerModId, $srcModId){
        $type = 'cat_details';
        $aComponents = array(
            $type => array(
                'type'    => $type,
                'options' => AMI_Mod::INIT_ON_START
            )
        );
        /**
         * @var AMI_Mod
         */
        $oController = $aEvent['oController'];
        $oController->addComponents($aComponents);

        return $aEvent;
    }

    /**
     * Appends image fields to field mapping.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTable::getAvailableFields()
     */
    public function handleAddFieldMapping($name, array $aEvent, $handlerModId, $srcModId){
        $this->doMapping = $aEvent['oTable']->hasField('disable_comments', FALSE);
        $this->fieldName = $this->doMapping ? 'disable_comments' : 'ext_dsc_disable';
        if($this->doMapping){
            $aEvent['aFields'] += array('ext_dsc_disable' => 'disable_comments');
        }

        return $aEvent;
    }

    /**
     * Appends extension fields to available fields.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTable::getAvailableFields()
     */
    public function handleGetAvailableFields($name, array $aEvent, $handlerModId, $srcModId){
        if(!in_array('ext_dsc_disable', $aEvent['aFields'])){
            $aEvent['aFields'][] = 'ext_dsc_disable';
        }

        return $aEvent;
    }

    /**
     * Adds late data binding for comments counter.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListInit($name, array $aEvent, $handlerModId, $srcModId){
        $modId = $this->getModId();
        if(in_array($handlerModId, array($modId, $modId . '_cat'))){
            AMI_Event::disableHandler('on_list_init');
            $idColumn = AMI::getResourceModel($aEvent['modId'] . '/table')->getItem()->getPrimaryKeyField();
            AMI_Event::enableHandler('on_list_init');
            $aEvent['oList']->setLateDataBinding(
                $idColumn, 'ext_discussion_count', 'discussion', 'count_public_children',
                DB_Query::getSnippet(" AND `ext_module` = %s AND `id_parent` = 0 AND `public` = 1 GROUP BY `id_ext_module`")->q($handlerModId),
                'id_ext_module', 0
            );
        }
        return $aEvent;
    }

    /**
     * Add disable_comments field.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleQuery($name, array $aEvent, $handlerModId, $srcModId){
        if($this->doMapping){
            $aEvent['oQuery']->addField('disable_comments', $aEvent['alias'], 'ext_dsc_disable');
        }else{
            $aEvent['oQuery']->addField('ext_dsc_disable');
        }
        return $aEvent;
    }

    /**
     * Dispatch adding comment action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    ExtDiscussion::_ActionProcessFront()
     * @todo   Process eshop comments correct
     * @todo   REDIRECT!!!
     */
    public function handleAddCommentAction($name, array $aEvent, $handlerModId, $srcModId){
        if(
            AMI::getSingleton('env/request')->get('forum_action', FALSE) !== 'add' ||
            !$this->areCommentsAllowed()
        ){
            return $aEvent;
        }

        $oEngine = $this->initOldEnvModule();
        $oEngine->fltExtModuleId = AMI_Registry::get('page/' . ($this->isCatMod ? 'catId' : 'itemId'));
        $appliedCommentId = $oEngine->extFrontActionAdd();
        if($appliedCommentId){
            if($oEngine->isMessagePublished()){
                $oEngine->clearFrontCache($srcModId);
            }
            /*
            $this->mod->forceRedirect = true;
            $this->cms->VarsGet = array ('status_msg' => serialize(array ('sys' => $this->cms->SysStatusMsgs, 'plain' => $this->cms->StatusMsgs)));
            $this->cms->ActiveScript = preg_replace(array ('/\&?offset\=\d+/', '/\&action=[^&]*' . '/', '/\&id_message=\d+/', '/\&?go_last_page\=1/'), '', $GLOBALS['vGlobVars']['script_full_link']);
        //                    $this->mod->redirectionAnchor = '&action=locate&id_message=' . $appliedId . '#d' . $appliedId;
            // we have ordering by date only for now
            $this->mod->redirectionAnchor = ($this->_discussionModule->GetOption('front_page_sort_dim') == 'asc' ? '&offset=99999999999' : '') . '#d' . $appliedId;
            */
            $url =
                preg_replace(
                    array(
                        '/\&?offset\=\d+/',
                        '/\&action=[^&]*' . '/',
                        '/\&id_message=\d+/',
                        '/\&?go_last_page\=1/'
                    ),
                    '',
                    $GLOBALS['vGlobVars']['script_full_link']
                );
            $url .=
                (mb_strpos($url, '?') !== FALSE ? '&' : '?') .
                'status_msg=' . rawurlencode(serialize(array('sys' => $oEngine->cms->SysStatusMsgs, 'plain' => $oEngine->cms->StatusMsgs))) .
                ($this->oModule->GetOption('front_page_sort_dim') == 'asc' ? '&go_last_page=1' : '') . '#bba' . $appliedCommentId;

            AMI::getSingleton('response')->HTTP->setRedirect($url, 301);
        }

        return $aEvent;
    }

    /**
     * Dispatch adding comment action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    ExtDiscussion::_ActionProcessFront()
     * @todo   Process eshop comments correct
     * @todo   REDIRECT!!!
     */
    public function handleAddCommentWatching($name, array $aEvent, $handlerModId, $srcModId){
        if(
            AMI::getSingleton('env/request')->get('subaction', FALSE) !== 'stop' ||
            !$this->areCommentsAllowed()
        ){
            return $aEvent;
        }
        $oEngine = $this->initOldEnvModule();
        $oEngine->fltExtModuleId = AMI_Registry::get('page/itemId');
        $oEngine->extFrontActionStopWatching();

        return $aEvent;
    }

    /**#@-*/

    /**
     * Checks if comments are allowed.
     *
     * @return bool
     */
    protected function areCommentsAllowed(){
        AMI_PageManager::getPageItemData();
        $aPage = AMI_Registry::get('page');
        $itemId = $this->isCatMod ? $aPage['catId'] : $aPage['itemId'];
        if(
            $itemId < 1 ||
            (isset($aPage['catPublic']) ? !$aPage['catPublic'] : FALSE)
        ){
            // wrong page
            return FALSE;
        }

        /**
         * @var AMI_ModTableItem
         */
        $oItem = AMI::getResourceModel($this->getModId() . '/table')->find($itemId, array('id', 'ext_dsc_disable'));
        return !$oItem->ext_dsc_disable;
    }
}

/**
 * AmiExt/Discussion extension configuration front view.
 *
 * @package    Config_AmiExt_Discussion
 * @subpackage View
 * @resource   ext_discussion/view/frn <code>AMI::getResource('ext_discussion/view/frn')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Discussion_ViewFrn extends AMI_ExtView{
    /**
     * Template block name
     *
     * @var string
     */
    protected $tplBlockName = 'discussion_ext';

    /**
     * Flag specifying to disable search engine indexing
     *
     * @var bool
     */
    protected $disableSEIndexing;

    /**
     * Sets extension object.
     *
     * @param  AMI_Ext $oExt  Extension object
     * @return void
     */
    public function setExt(AMI_Ext $oExt){
        parent::setExt($oExt);

        if($this->getModId() === 'ext_discussion'){
            $this->getTemplate()->addGlobalVars(
                array(
                    'EXTENSION_FORUM' => '1',
                    'EXT_FORUM'       => '1'
                )
            );
        }
        $this->disableSEIndexing =
            $oExt->issetModOption('disable_se_indexing_pages') &&
            in_array('page_ext_discussion', $oExt->getModOption('disable_se_indexing_pages'));
    }

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Fills front item list comments column.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::get()
     */
    public function handlePostProcessFields($name, array $aEvent, $handlerModId, $srcModId){
        $isCatMod = preg_match('/_cat$/', $handlerModId);
        $oTpl = $this->getTemplate();
        $itemId = $aEvent['aItem']['id'];
        $aScope = $this->getScope($this->oExt->getExtId(), array('id_ext_module' => $itemId));
        $aScope['noindex'] = $this->disableSEIndexing;
        $aScope['ext_module'] = $srcModId;
        $aScope += $aEvent['aItem'];
        switch($aEvent['bodyType']){
            case 'items':
            case 'browse_items':
            case 'sticky_items':
            case 'small':
            case 'cats':
            case 'sticky_cats':
                $setTail = $aEvent['bodyType'] !== 'small' ? 'list' : 'small';
                if(
                    $aEvent['aItem']['ext_dsc_disable'] ||
                    !$this->oExt->getModOption('show_forum_link_in_' . $setTail)
                ){
                    // d::vd(!$this->oExt->getModOption('show_forum_link_in_' . $setTail), 'show_forum_link_in_' . $setTail);###
                    break;
                }
                $value =
                    isset($aEvent['aItem']['ext_discussion_count'])
                        ? $aEvent['aItem']['ext_discussion_count']
                        : 0;
                $aScope['count_replies'] = $value;
                $aEvent['aItem']['count_replies'] = $value;
                $oTpl->setBlockLocale($this->tplBlockName, $this->aLocale, true);
                if($aEvent['bodyType'] === 'small'){
                    $aScope['front_items_link'] =
                        preg_replace(
                            array(
                                '/&offset\=\d+/',
                                '/&catoffset\=\d+/',
                            ),
                            '',
                            $aScope['front_items_link']
                        );
                }
                $aEvent['aItem']['discussion_link'] = $oTpl->parse($aEvent['aItem']['modId'] . ':discussion_link_' . $setTail, $aScope);
                break;
            case 'details':
            case 'cat_details':
                if($isCatMod && AMI_Registry::get('page/itemId', 0) > 0){
                    // No category comments on item details page
                    return $aEvent;
                }
                $aScope['count_replies'] = 0; // #CMS-11411
                $aEvent['aItem']['count_replies'] = 0; // #CMS-11411
                if($aEvent['aItem']['ext_dsc_disable']){
                    $aEvent['aItem']['discussion_link'] = $oTpl->parse($aEvent['aItem']['modId'] . ':discussion_disabled', $aScope);
                }else{
                    $oRequest = AMI::getSingleton('env/request');
                    if($this->oExt->getModOption('show_forum_count_replies')){
                        $oItem =
                            AMI::getResourceModel('discussion/table')
                            ->findByFields(
                                array(
                                    'ext_module'    => $handlerModId,
                                    'id_ext_module' => $itemId,
                                    'id_parent'     => 0,
                                    'public'        => 1,
                                )
                            );
                        $aScope['count_replies'] = $oItem->count_public_children;
                        $aEvent['aItem']['count_replies'] = $oItem->count_public_children;
                    }
                    if(
                        !$oRequest->get('forum_ext', FALSE) &&
                        (
                            !$this->oExt->issetModOption('show_forum_at_details') ||
                            !$this->oExt->getModOption('show_forum_at_details')
                        )
                    ){
                        // 'forum_ext' request parameter is required
                        $aEvent['aItem']['discussion_link'] = $oTpl->parse($aEvent['aItem']['modId'] . ':discussion_link', $aScope);
                    }elseif($itemId){
                        // get discussion content from 5.0 module
                        $oEngine = $this->oExt->initOldEnvModule();
                        $oEngine->fltExtModuleId = $itemId;
                        if(
                            $oRequest->get('action', FALSE) === 'locate' &&
                            $oRequest->get('id_message', 0)
                        ){
                            $oEngine->_locateMessageId = (int)$oRequest->get('id_message', 0);
                        }

                        // create list and form {{{

                        $aCustom = array(
                            'real_nav_data' =>
                                AMI_Registry::get('page/scriptLink') .
                                (isset($aEvent['aItem']['nav_data']) ? $aEvent['aItem']['nav_data'] : '')
                        );
                        $aData = $oEngine->GetHtml($aCustom);
                        $oTpl->addGlobalVars(array('offset' => 0));
                        $aData = array(
                            'list' => $aData['body'],
                            'form' => $aData['form']
                        );
                        $aEvent['aItem']['discussion_extension'] =
                            $oTpl->parse($aEvent['aItem']['modId'] . ':discussion_extension', $aData);

                        // SEO
                        if(
                            $oRequest->get('forum_ext', FALSE) && (
                                $this->disableSEIndexing || ($this->oExt->issetModOption('show_forum_at_details') && $this->oExt->getModOption('show_forum_at_details') && !$oRequest->get('offset', 0))
                            )
                        ){
                            // disable search engine indexing for pages having forum_ext=1 in url
                            $this->oExt->getModController()->disablePageIndexing();
                        }
                    }
                }
                break;
        }
        return $aEvent;
    }

    /**#@-*/
}
