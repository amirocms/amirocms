<?php
/**
 * AmiExt/Discussion extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiExt_Discussion
 * @version   $Id: AmiExt_Discussion_Adm.php 44447 2013-11-26 14:21:22Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/Discussion extension configuration admin controller.
 *
 * @package    Config_AmiExt_Discussion
 * @subpackage Controller
 * @resource   ext_discussion/module/controller/adm <code>AMI::getResource('ext_discussion/module/controller/adm')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Discussion_Adm extends Hyper_AmiExt{
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
     * Array of comment counters for item list
     *
     * @var array
     */
    protected $aCounters = array();

    /**
     * Front module id
     *
     * @var string
     */
    protected $frnModId = '';

    /**
     * Constructor.
     *
     * @param string  $modId        Module id
     * @param string  $optSrcId     Options source module id
     * @param AMI_Mod $oController  Module controller
     * @todo  avoiding preg_replace hack
     */
    public function __construct($modId, $optSrcId = '', AMI_Mod $oController = null){
        $this->frnModId = preg_replace('/^ext_/', '', $this->getExtId());
        $this->addModule($this->frnModId);

        parent::__construct($modId, $optSrcId, $oController);
    }

    /**
     * Callback called after module is installed.
     *
     * Alers module table to add extension fields.
     *
     * @param  string         $modId  Installed module id
     * @param  AMI_Tx_Cmd_Args $oArgs  Transaction command arguments
     * @return void
     */
    public function onModPostInstall($modId, AMI_Tx_Cmd_Args $oArgs){
        global $db;

        $oTable = AMI::getResourceModel($modId . '/table');
        $table = $oTable->getTableName();
        $db->setSafeSQLOptions('alter');
        $sql =
            "ALTER TABLE " . $table . " " .
            "ADD `ext_dsc_disable` tinyint unsigned NOT NULL DEFAULT '0'";
        $db->query($sql);
    }

    /**
     * Callback called after module is uninstalled without cheking unistallation mode.
     *
     * Cleans up uninstalled module data.
     *
     * @param  string         $modId  Unnstalled module id
     * @param  AMI_Tx_Cmd_Args $oArgs  Transaction command arguments
     * @return void
     * @todo   Possible repair internal counter `cms_members`.`msgs_count` later.
     */
    public function onModPostUninstallUnmasked($modId, AMI_Tx_Cmd_Args $oArgs){
        $oQuery =
            DB_Query::getSnippet(
                "DELETE FROM `cms_discussion` " .
                "WHERE `ext_module` = %s"
            )->q($modId);
        AMI::getSingleton('db')->query($oQuery);
    }

    public function getFieldName(){
        return $this->fieldName;
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
        $side = AMI_Registry::get('side');
        $oView = $this->getView($side);
        $oView->setExt($this);
        if($side == 'adm' || ($this->checkModOption('show_forum_link_in_list') && $this->getModOption('show_forum_link_in_list'))){
            AMI_Event::addHandler('on_list_init', array($this, 'handleListInit'), $modId);
            AMI_Event::addHandler('on_list_columns', array($oView, 'handleListColumns'), $modId);
            AMI_Event::addHandler('on_list_body_row', array($oView, 'handleListBodyRow'), $modId);
            AMI_Event::addHandler('on_form_fields_form', array($oView, 'handleFormFields'), $modId);
            AMI_Event::addHandler('on_before_save_model_item', array($this, 'handleSaveModelItem'), $modId);
        }
        if($side == 'frn'){
            AMI_Event::addHandler('on_before_view_details', array($this, 'handleDetails'), $modId);
        }
        AMI_Event::addHandler('on_add_field_mapping', array($this, 'handleAddFieldMapping'), $modId);
        AMI_Event::addHandler('on_get_available_fields', array($this, 'handleGetAvailableFields'), $modId);

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
        $this->doMapping = $aEvent['oTable']->hasField('disable_comments');
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
        if($handlerModId === $this->getModId()){
            AMI_Event::disableHandler('on_list_init');
            $idColumn = AMI::getResourceModel($aEvent['modId'] . '/table')->getItem()->getPrimaryKeyField();
            AMI_Event::enableHandler('on_list_init');
            $aEvent['oList']->setLateDataBinding(
                $idColumn, 'ext_discussion_count', 'discussion', 'count_children',
                DB_Query::getSnippet(" AND `ext_module`=%s AND `id_parent`=0 GROUP BY `id_ext_module`")->q($handlerModId),
                'id_ext_module', 0
            );
        }
        return $aEvent;
    }

    /**
     * Sets view scope for admin list.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    ExtDiscussion_ViewAdm::handleListBodyRow()
     * @see    AMI_View::getScope()
     * @todo   Get real link, AMI::getSingleton('core')->getAdminLink('discussion')...
     */
    public function handleBeforeViewList($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['aScope'] += array(
            'discussion_mod_link' => 'discussion.php', // hack, @todo get real link
            'ext_module'          => $this->oModState->getModId(),
            'count'               =>
                empty($this->aCounters[$aEvent['aScope']['id_ext_module']])
                    ? 0
                    : $this->aCounters[$aEvent['aScope']['id_ext_module']]
        );
        return $aEvent;
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
        if($handlerModId === $this->getModId()){
            if(empty($aEvent['aData'][$this->fieldName])){
                $aEvent['aData'][$this->fieldName] = 0;
            }
        }
        if($this->doMapping){
            $aEvent['aData']['ext_dsc_disable'] = $aEvent['aData']['disable_comments'];
        }
        $aEvent['oItem']->ext_dsc_disable = $aEvent['aData']['ext_dsc_disable'];
        return $aEvent;
    }

    /**
     * Fill front details using Discussion_Frn.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleDetails($name, array $aEvent, $handlerModId, $srcModId){
/*
        echo $this->getModId();
        $oModFrnController = AMI::getResourceModel($this->getExtModId() . '/module/controller/frn');
        $oModFrnController->setFrnParams(array(
            'mod_item_id' => $oModelItem->$idColumn,
            'mod_id'      => $handlerModId
        ));
        //$aEvent['aScope']['forum_extension'] = $oModFrnController->getViews();
        $aEvent['aScope']['discussion_block'] = $oModFrnController->getViews();
        d::vd();
        //$oModFrnController->getViews();
    	//$oModFrnController = AMI::getResourceModel($this->frnModId . '/module/controller/frn');
*/

        $oModel = $aEvent['view']->getModel();

        $oRequest = AMI::getSingleton('env/request');
        $oResponse = AMI::getSingleton('response');

        // Runs over
        $oModActionController = AMI::getResource($this->frnModId . '/module/controller/frn', array($oRequest, $oResponse));
        $oModActionController->init();
        $aViews = $oModActionController->getViews();

        // Add comment if any
        if($oRequest->get('discussion_action') && $oRequest->get('discussion_action') == 'form_save'){
            AMI_Registry::set('ami_allow_model_save', true);
        	$oModActionController->dispatch('form_save');
        	$oModFormActionController = AMI::getResource($this->frnModId . '/frn/form/controller', array($oRequest, $oResponse));
        }

        // View construction
        $strDiscussionBlocks = '';
        foreach($aViews as $oView){
        	if(AMI::getRawResource('discussion/frn/form/view') == get_class($oView)){
                $oView->addField(array('name' => 'mod_id', 'value' => $this->getModId(), 'type' => 'hidden'));
                $oView->addField(array('name' => 'id', 'value' => $oRequest->get('id'), 'type' => 'hidden'));
            }

            $strDiscussionBlocks .= $oView->get();
        }
        $oModel->setValue('discussion_block', $strDiscussionBlocks);

        return $aEvent;
    }

    /**#@-*/
}

/**
 * AmiExt/Discussion extension configuration admin view.
 *
 * @package    Config_AmiExt_Discussion
 * @subpackage View
 * @resource   ext_discussion/view/adm <code>AMI::getResource('ext_discussion/view/adm')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Discussion_ViewAdm extends AMI_ExtView{
    /**
     * Template block name
     *
     * @var string
     */
    protected $tplBlockName = 'discussion_ext';

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Add discussion column to admin list view.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView_JSON::get()
	 */
    public function handleListColumns($name, array $aEvent, $handlerModId, $srcModId){
        $oView = $aEvent['oView'];
        $oView->addColumnType('ext_discussion_count', 'int');
        $oView->setColumnWidth('ext_discussion_count', 'narrow');
        // $oView->addColumnType('picture', 'image');
        $this->aLocale['ext_discussion_count'] = $this->aLocale['title'];
        $oView->addLocale($this->aLocale);
        return $aEvent;
    }

    /**
     * Fills admin item list comments column.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::get()
     */
    public function handleListBodyRow($name, array $aEvent, $handlerModId, $srcModId){
        $idColumn = $aEvent['oItem']->getPrimaryKeyField();
        $aScope = $this->getScope('ext_discussion', array('id_ext_module' => $aEvent['oItem']->$idColumn));
        $oTpl = $this->getTemplate();
        $value = $aEvent['aScope']['ext_discussion_count'];
        $set = $value ? ':comments_column' : ':comments_column_empty';
        $aScope['ext_module'] = $handlerModId;
        $aScope['count'] = $aEvent['aScope']['ext_discussion_count'];
        $oTpl->setBlockLocale($this->tplBlockName, $this->aLocale, true);
        $aEvent['aScope']['ext_discussion_count'] = $oTpl->parse($this->tplBlockName . $set, $aScope);
        return $aEvent;
    }

    /**
     * Adds field to admin form.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModFormView::get()
     */
    public function handleFormFields($name, array $aEvent, $handlerModId, $srcModId){
        if(!empty($aEvent['oFormView'])){
            $aEvent['oFormView']
                ->addField(
                    array(
                        'name'     => $this->oExt->getFieldName(),
                        'type'     => 'checkbox',
                        'value'    => (int)!empty($aEvent['oItem']->ext_dsc_disable),
                        'position' => 'ext_rating_values.after|options_tab.end'
                    )
                )
                ->addLocale($this->aLocale);
        }
        return $aEvent;
    }

    /**#@-*/
}
