<?php
/**
 * AmiExt/CustomFields extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiExt_CustomFields
 * @version   $Id: AmiExt_CustomFields_Adm.php 43841 2013-11-17 12:14:02Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/CustomFields extension configuration admin controller.
 *
 * @package    Config_AmiExt_CustomFields
 * @subpackage Controller
 * @resource   ext_custom_fields/module/controller/adm <code>AMI::getResource('ext_custom_fields/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_CustomFields_Adm extends AmiExt_CustomFields_Common{
    /**
     * Detected dataset id
     * @var int
     */
    // public $detectedPageId = 0;

    /**
     * Callback called after module is uninstalled.
     *
     * Cleans up uninstalled module data.
     *
     * @param  string         $modId  Unnstalled module id
     * @param  AMI_Tx_Cmd_Args $oArgs  Transaction command arguments
     * @return void
     */
    public function onModPostUninstall($modId, AMI_Tx_Cmd_Args $oArgs){
        $oDB = AMI::getSingleton('db');
        $oQuery =
            DB_Query::getSnippet(
                "DELETE FROM `cms_modules_datasets` " .
                "WHERE `module_name` = %s"
            )->q($modId);
        $oDB->query($oQuery);
        $oQuery =
            DB_Query::getSnippet(
                "DELETE FROM `cms_modules_custom_fields` " .
                "WHERE `module_name` = %s"
            )->q($modId);
        $oDB->query($oQuery);
    }

    /**
     * Returns array with captions.
     *
     * @param  string $prefix  Prefix for each custom field name
     * @return array
     */
    public function getFieldsCaptions($prefix = ''){
	    $aCaptionStruct = array();
	    foreach($this->getModuleCustomFields() as $aField){
	        $aCaptionStruct[$prefix . AmiExt_CustomFields_Common::PREFIX . $aField['system_name']] = $aField['caption'];
	    }
	    return $aCaptionStruct;
    }

    /**
     * Returns available module element datasets for category module.
     *
     * @return AMI_ModTableList
     */
    public function getDatasets(){
        return
            AMI::getResourceModel('modules_datasets/table')
                ->getList()
                ->addColumns(array('id', 'id `value`', 'name'))
                ->addWhereDef(
                    DB_Query::getSnippet("AND `module_name` = %s")
                    ->q($this->oService->getParentForCatModule($this->getModId()))
                )
                ->addOrder('name', 'asc')
                ->load();
    }

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Extension pre-initialization.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handlePreInit($name, array $aEvent, $handlerModId, $srcModId){
        // $aEvent = parent::handlePreInit($name, $aEvent, $handlerModId, $srcModId);

        $modId = $aEvent['modId'];

        parent::extPreInit();

        AMI_Event::addHandler('on_table_get_item_post', array($this, 'handleTableGetItem'), $modId);
        AMI_Event::addHandler('on_before_view_list', array($this, 'handleBeforeViewList'), $modId);
        AMI_Event::addHandler('on_query_add_table', array($this, 'handleAddTable'), $modId);
        AMI_Event::addHandler('on_list_columns', array($this, 'handleListColumns'), $modId);
        AMI_Event::addHandler('on_filter_init', array($this, 'handleFilterInit'), $modId);

        $oView = $this->getView(AMI_Registry::get('side'));
        if($oView){
            $oView->setExt($this);
            AMI_Event::addHandler('on_form_fields_form_filter', array($oView, 'handleFilterFields'), $modId);
            AMI_Event::addHandler('on_form_fields_form', array($oView, 'handleFormFields'), $modId);
        }

        return $aEvent;
    }

    /**
     * Handles custom fields columns preparing.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleBeforeViewList($name, array $aEvent, $handlerModId, $srcModId){
        /**
         * @var AMI_ModTableItem
         */
         // $oItem = $aEvent['oItem'];
        $this->setDataset();
        /*
        if(AMI_Registry::get('side') == 'adm'){
            // Show all columns in admin
        }else{
            // Show all columns by correspondent dataset id
        }
        */
        return $aEvent;
    }

    /**
     * Add custom field columns to list view.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::get()
     */
    public function handleListColumns($name, array $aEvent, $handlerModId, $srcModId){
        foreach($this->getModuleCustomFields() as $id => $aField){
            $aEvent['columns'][] = AmiExt_CustomFields_Common::PREFIX . $aField['system_name'];
        }
        return $aEvent;
    }

    /**
     * Adds filter fields.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModFilterView::get()
     */
    public function handleFilterInit($name, array $aEvent, $handlerModId, $srcModId){
        $modId = $this->getModId();

        if($this->mode !== 'simple'){
            // common controls
            $parentModId = $this->oService->getParentForCatModule($modId);
            $aFieldsXModIds = array('id_dataset' => $parentModId);
            if($parentModId != $modId){
                // categories module, we need to add datasets filter fields
                $aFieldsXModIds['module_id_dataset'] = $modId;
            }

            $aElementDatasets = array();
            foreach($aFieldsXModIds as $modId){
                $aElementDatasets[$modId] = array();
            }
            $oSnippet =
                sizeof($aFieldsXModIds) == 1
                    ? DB_Query::getSnippet("AND `module_name` = %s")
                        ->q($parentModId)
                    : DB_Query::getSnippet("AND `module_name` IN (%s)")
                        ->implode($aFieldsXModIds);

            /**
             * @var AMI_ModTableList
             */
            $oList = AMI::getResourceModel('modules_datasets/table')
                ->getList()
                ->addColumns(array('id', 'name', 'module_name'))
                ->addWhereDef($oSnippet)
                ->addOrder('name', 'asc')
                ->load();
            foreach($oList as $oItem){
                $aElementDatasets[$oItem->module_name][$oItem->id] = $oItem->name;
            }

            foreach(array_keys($aFieldsXModIds) as $index => $field){
                // do not display select boxes a la [all, singular deteset]
                if(sizeof($aElementDatasets[$aFieldsXModIds[$field]]) < 2){
                    continue;
                }
                $mode = $this->oService->getModuleUsageMode($aFieldsXModIds[$field]);
                $aSelect = array();
                foreach($aElementDatasets[$aFieldsXModIds[$field]] as $id => $caption){
                    $aSelect[] = array(
                        'name'  => $caption,
                        'value' => $id
                    );
                }

                if($mode != 'pages' && $index){
                    // simple only, category module cannot have 'categories' mode
                    // no filter field for this mode
                    break; // default, leave fields foreach
                }
                switch($mode){
                    case 'pages':
                        $aEvent['oFilter']->addViewField(
                            array(
                                'name'          => $field,
                                'type'          => 'select',
                                'flt_type'      => 'select',
                                'flt_default'   => '0',
                                'flt_condition' => '=',
                                'flt_column'    => $field,
                                'flt_alias'     => 'p',
                                'data'          => $aSelect,
                                'not_selected'  => array('id' => '0', 'caption' => 'all_datasets')
                            )
                        );
                        if(AMI::getSingleton('env/request')->get('id_dataset')){
                            $this->usePagesDependence = true;
                        }
                        break;
                    case 'categories':
                        $aFilterField =
                            array(
                                'name'          => $field,
                                'type'          => 'select',
                                'flt_type'      => 'select',
                                'flt_default'   => '0',
                                'flt_condition' => '=',
                                'flt_column'    => $field,
                                'data'          => $aSelect,
                                'not_selected'  => array('id' => '0', 'caption' => 'all_datasets')
                            );
                        if(
                            AMI::issetOption($this->getModId(), 'multi_page') && !
                            (
                                AMI::issetAndTrueOption('core', 'multi_page_allowed') &&
                                AMI::getOption($this->getModId(), 'multi_page')
                            )
                        ){
                            $aFilterField['flt_alias'] = 'cat';
                        }
                        $aEvent['oFilter']->addViewField($aFilterField);
                        break;
                }
            }
        }

        /**
         * @var AMI_ModTableList
         */
        $oList =
            AMI::getResourceModel('modules_custom_fields/table')
            ->getList()
            ->dropColumn('datasets', 'd')
            ->addColumns(array('id'))
            ->addWhereDef(
                DB_Query::getSnippet("AND i.`module_name` = %s AND i.`admin_filter` = 1")
                ->q($this->getModId())
            )
            ->load();

        $aIds = array();
        foreach($oList as $oItem){
            $aIds[] = $oItem->id;
        }
        if(sizeof($aIds)){
            $this->loadFieldsByIds($aIds);
        }

        foreach($this->getCustomFields() as $id => $aField){
            if($aField['admin_filter'] && !empty($aField['caption'])){
                /*
                // for future pm#5302
                switch($aField['ftype']){
                    case 'int':
                    case 'float':
                        $ftype = $aField['ftype'];
                    default:
                        $ftype = 'text';
                }
                */
                $ftype = 'text';
                $aEvent['oFilter']->addViewField(
                    array(
                        'name'          => AmiExt_CustomFields_Common::PREFIX . $aField['system_name'],
                        'type'          => 'input',
                        'flt_type'      => $ftype,
                        'flt_default'   => '',
                        'flt_condition' => $aField['ftype'] == 'char' ? 'like' : '=',
                        'flt_column'    => AmiExt_CustomFields_Common::PREFIX . $aField['system_name']
                    )
                );
            }
        }

        return $aEvent;
    }

    /**#@-*/

    /**
     * Returns dataset data.
     *
     * @return array
     */
    public function getDataset(){
        return $this->aDataset;
    }

    /**
     * Returns fields structure.
     *
     * @return array
     */
    public function getModuleCustomFields(){
        if($this->isCatModule($this->getModId())){
            $aCustomFields = $this->getCategoryCustomFields();
        }else{
            $aCustomFields = parent::getCustomFields();
        }

        return $aCustomFields;
    }

    /**
     * Returns fields structure.
     *
     * @return array
     */
    public function getCustomFields(){
        $aCustomFields = $this->getModuleCustomFields();

        if(sizeof($this->aDataset)){
            $aCustomFields = array_filter($aCustomFields, array($this, 'cbFilterCustomFields'));
        }
        return $aCustomFields;
    }

    /**
     * Returns scope for admin js part.
     *
     * @param  AMI_ModTableItem $oItem  Item model
     * @return array
     */
    public function getScope(AMI_ModTableItem $oItem){
        $modId = $this->getModId();
        $sysDatasetId = $this->oService->getSysDatasetId($modId);
        $this->detectDatasetId($oItem);
        return
            array(
                'dataset_id'      => $this->datasetId ? $this->datasetId : $sysDatasetId,
                'sys_dataset_id'  => $sysDatasetId,
                'mod_id'          => $modId,
                'table'           => $this->mode == 'pages' ? 'cms_pages' : $oItem->getTable()->getTableName() . '_cat', ### @todo avoid hack!!!
                'display_tooltip' => $this->mode == 'pages' && $oItem->id_page < 1,
                'object_id'       => $oItem->getId(),
                'mode'            => $this->mode
            );
    }

    /**
     * Detects and returns dataset id proceeding from module, category id, page id.
     *
     * @param AMI_ModTableItem $oItem         Item model
     * @param string           $handlerModId  Module Id
     * @return int
     * @todo  Front side
     * @todo  categories mode
     */
    protected function detectDatasetId(AMI_ModTableItem $oItem = null, $handlerModId = null){
        AMI_Event::disableHandler('on_table_get_item_post');
        if(AMI_Registry::get('side') == 'adm'){
            $datasetId = 0;
###            $mode = $this->oService->getModuleUsageMode($this->getModId());
            /**
             * @var AMI_RequestHTTP
             */
            $oRequest = AMI::getSingleton('env/request');
            switch($this->mode){### $mode
                case 'categories':
                    // get category id from loaded item or filter
                    $forceCatId = $oRequest->get('ami_force_id_cat', false);
                    if($forceCatId !== false){
                        $catId = $forceCatId;
                    }else{
                        if($oItem && $oItem->cat_id){
                            $catId = $oItem->cat_id;
                        }elseif($oRequest->get('cat_id', false) !== false){
                            $catId = $oRequest->get('cat_id');
                        }
                    }
                    if(isset($catId)){
                        $catId = (int)$catId;
                        $catResId = $oItem->getTable()->getDependenceResId('cat');
                        if($catResId){
                            $oItem =
                                AMI::getResourceModel($catResId . '/table')
                                ->find($catId, array('id', 'id_dataset'))
                                ->load();
                            if($oItem){
                                $datasetId = (int)$oItem->id_dataset;
                            }
                        }
                    }
                    if(!$datasetId){
                        $this->_useSharedOnly = true;
                    }
                    break; // case 'categories'
                case 'pages':
                    // get page id from loaded item
                    $pageId = 0;
                    $forcePageId = $oRequest->get('ami_force_id_page', false);
                    if($forcePageId !== false){
                        $pageId = $forcePageId;
                    }else{
                        if($oItem && $oItem->id_page){
                            $pageId = $oItem->id_page;
                        }elseif($oRequest->get('id_page', false) !== false){
                            $pageId = $oRequest->get('id_page');
                        }
                    }
                    $pageId = (int)$pageId;
###                    $this->detectedPageId = $pageId;
                    if($pageId > 0){
                        $sql = "SELECT `id_dataset` FROM `cms_pages` WHERE `id` = " . $pageId;
                        $datasetId = (int)AMI::getSingleton('db')->fetchValue($sql);
                    }
                    break; // case 'pages'
                case 'simple':
                    // get dataset id for module
                    $oItem =
                        AMI::getResourceModel('modules_datasets/table')
                            ->getItem()
                            ->addFields(array('id'))
                            ->addSearchCondition(
                                array(
                                    'module_name' => $this->getModId(),
                                    'used_simple' => 1,
                                    'lang'        => AMI_Registry::get('lang_data')
                                )
                            )
                            ->load();
                    $datasetId = (int)$oItem->getId();
                    break; // case 'simple'
            }
        }else{
            // front
        }
        if(empty($datasetId)){
            $datasetId = $this->oService->getSysDatasetId($this->getModId());
        }
        AMI_Event::enableHandler('on_table_get_item_post');
        return $datasetId;
    }

    /**
     * Filter custom fields by current dataset.
     *
     * @param  array $aCustomField  Custom field struct
     * @return bool
     * @see    AmiExt_CustomFields_Adm::getCustomFields()
     */
    private function cbFilterCustomFields(array $aCustomField){
        return in_array($aCustomField['id'], $this->aDataset['fields_map']);
    }
}

/**
 * AmiExt/CustomFields extension configuration admin view.
 *
 * @package    Config_AmiExt_CustomFields
 * @subpackage View
 * @resource   ext_custom_fields/view/adm <code>AMI::getResource('ext_custom_fields/view/adm')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_CustomFields_ViewAdm extends AMI_ExtView{
    /**
     * Template block name
     *
     * @var string
     */
    protected $tplBlockName = 'custom_fields_ext';

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Adds filter locales.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModFilterView::get()
     */
    public function handleFilterFields($name, array $aEvent, $handlerModId, $srcModId){
        $oFormView = $this->oExt->getFormViewInstance($aEvent);
        if(!$oFormView){
            return $aEvent;
        }
        $oFormView->addLocale($this->aLocale);
        $oFormView->addLocale($this->oExt->getFieldsCaptions('filter_field_'));
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
        $oFormView = $this->oExt->getFormViewInstance($aEvent);
        if(!$oFormView){
            return $aEvent;
        }
        $this->oExt->setDataset(null, $aEvent['oItem']);
        $aLocale = $this->oExt->getFieldsCaptions('caption_');
        $oFormView->addLocale($aLocale);
        $oFormView->addLocale($this->aLocale);
        $oFormView->addTemplate($this->tplFileName);
        $oTpl = $this->getTemplate();
        $oTpl->setBlockLocale($this->tplBlockName, $this->aLocale, true);
        $oTpl->setBlockLocale($this->tplBlockName, $aLocale, true);
        $doAddTab = true;
        foreach($this->oExt->getCustomFields() as $aField){
            switch($aField['admin_form']){
                case 'top':
                    $position = 'ext_custom_fields_top.end';
                    break;
                case 'tab':
                    $position = 'ext_custom_fields_tab.end';
                    if($doAddTab){
                        $oFormView->addTab('ext_custom_fields_tab', 'default_tabset', AMI_ModFormView::TAB_STATE_COMMON, 'options_tab.before');
                        $aDataset = $this->oExt->getDataset();
                        $oFormView->addField(
                            array(
                                'name'     => 'dataset_name',
                                'position' => $position,
                                'html'     => $oTpl->parse($this->tplBlockName . ':cf_dataset', array('dataset' => $aDataset['name']))
                            )
                        );
                        $doAddTab = false;
                    }
                    break;
                case 'bottom':
                    $position = 'ext_custom_fields_bottom.end';
                default:
                    continue;
            }
            $name = AmiExt_CustomFields_Adm::PREFIX . $aField['system_name'];
            $aScope = array(
                'caption'         => $name,
                'name'            => $name,
                'value'           => AMI_Lib_String::htmlChars($aEvent['oItem']->getValue($name)),
                'element_caption' => $aLocale['caption_' . $name]
            ) + $aField;
            $aScope['value'] = $oTpl->parse($this->tplBlockName . ':cf_value', $aScope);
            $aField = array(
                'name'     => $name,
                'position' => $position,
                'html'     => $oTpl->parse($this->tplBlockName . ':cf_row', $aScope)
            );
            $oFormView->addField($aField);
        }

        $aScope = $aEvent['aScope'] + $this->oExt->getScope($aEvent['oItem']);
        $oFormView->addField(
            array(
                'name' => 'ext_custom_fields_js',
                'html' => $oTpl->parse($this->tplBlockName . ':js', $aScope)
            )
        );
        $aDatasets = array_map('iterator_to_array', iterator_to_array($this->oExt->getDatasets()));
        if(mb_substr($aScope['mod_id'], -4) === '_cat'){ // @todo: remove hack
            if(sizeof($aDatasets) > 1){
                $aField =
                    array(
                        'name' => 'id_dataset',
                        'type' => 'select',
                        'data' => $aDatasets
                    );
                $oFormView->addField($aField);
            }elseif(!empty($aDatasets)){
                $aField =
                    array(
                        'name' => 'id_dataset',
                        'type' => 'hidden',
                        'value' => $aDatasets[0]['id']
                    );
                $oFormView->addField($aField);
            }
        }

        return $aEvent;
    }

    /**#@-*/
}
