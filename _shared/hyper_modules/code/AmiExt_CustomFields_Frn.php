<?php
/**
 * AmiExt/CustomFields extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiExt_CustomFields
 * @version   $Id: AmiExt_CustomFields_Frn.php 45021 2013-12-04 07:56:53Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/CustomFields extension configuration front controller.
 *
 * @package    Config_AmiExt_CustomFields
 * @subpackage Controller
 * @resource   ext_custom_fields/module/controller/frn <code>AMI::getResource('ext_custom_fields/module/controller/frn')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_CustomFields_Frn extends AmiExt_CustomFields_Common{
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
        $this->catModPostfix = '_cat';
        $modId = $aEvent['modId'];
        if(!AMI::isModInstalled($modId . $this->catModPostfix)){
            return $aEvent;
        }
        // $aEvent = parent::handlePreInit($name, $aEvent, $handlerModId, $srcModId);

        $oView = $this->getView(AMI_Registry::get('side'));

        parent::extPreInit();

        $this->catMode = $this->oService->getModuleUsageMode($modId . $this->catModPostfix);

        $aDefaultFrontData = array(
            'dataset_id'       => 0,
            'category_id'      => 0,
            'page_id'          => 0,
            'db_fields'        => array(),
            'common_db_fields' => array()
        );
        $this->aCrossHandlerData[$modId] = $aDefaultFrontData + array('module' => $modId);
        $this->aCrossHandlerData[$modId . $this->catModPostfix] = $aDefaultFrontData + array('module' => $modId . $this->catModPostfix);

        AMI_Event::addHandler('on_table_get_list', array($this, 'handleTableGetList'), $modId);
        AMI_Event::addHandler('on_table_get_list', array($this, 'handleTableGetList'), $modId . $this->catModPostfix);
        AMI_Event::addHandler('on_table_get_item_post', array($this, 'handleTableGetItem'), $modId);
        AMI_Event::addHandler('on_query_add_table', array($this, 'handleAddTable'), $modId);
        AMI_Event::addHandler('on_query_add_table', array($this, 'handleAddTable'), $modId . $this->catModPostfix);

        if($oView){
            $oView->setExt($this);
            AMI_Event::addHandler('on_list_body_row', array($oView, 'handleDetails'), $modId);
            AMI_Event::addHandler('on_item_details', array($oView, 'handleDetails'), $modId);

            AMI_Event::addHandler('on_before_view_items', array($oView, 'handleBeforeView'), $modId);
            AMI_Event::addHandler('on_before_view_cats', array($oView, 'handleBeforeView'), $modId);
            AMI_Event::addHandler('on_before_view_details', array($oView, 'handleBeforeView'), $modId);
            AMI_Event::addHandler('on_before_view_sticky_items', array($oView, 'handleBeforeView'), $modId);
            AMI_Event::addHandler('on_before_view_sticky_cats', array($oView, 'handleBeforeView'), $modId);
        }

        return $aEvent;
    }

    /**
     * Handles getting table.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::get()
     */
    public function handleTableGetList($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['oItem'] = $aEvent['oTable']->getItem();

        return parent::handleTableGetItem($name, $aEvent, $handlerModId, $srcModId);
    }

    /**
     * Detects and returns dataset id proceeding from module, category id, page id.
     *
     * @param AMI_ModTableItem $oItem         Item model
     * @param string           $handlerModId  Module Id
     * @return int
     */
    protected function detectDatasetId(AMI_ModTableItem $oItem = null, $handlerModId = null){
        AMI_Event::disableHandler('on_table_get_item_post');

        $datasetId = 0;
        $modId = $handlerModId ? $handlerModId : $this->getModId();
        if($this->isCatModule($handlerModId)){
            $mode = $this->catMode;
        }else{
            $mode = $this->mode;
        }
        switch($mode){
            case 'categories':
                $catId = AMI_Registry::get('page/catId', 0);
                if($catId){
                    $this->aCrossHandlerData[$modId]['category_id'] = $catId;

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
                $pageId = AMI_Registry::get('page/id', 0);
                if($pageId > 0){
                    $sql = "SELECT `id_dataset` FROM `cms_pages` WHERE `id` = " . $pageId;
                    $datasetId = (int)AMI::getSingleton('db')->fetchValue($sql);

                    if($datasetId){
                        $this->aCrossHandlerData[$modId]['page_id'] = $pageId;
                    }
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
                                'module_name' => $modId,
                                'used_simple' => 1,
                                'lang'        => AMI_Registry::get('lang_data')
                            )
                        )
                        ->load();
                $datasetId = (int)$oItem->getId();
                break; // case 'simple'
        }
        if(empty($datasetId)){
            $datasetId = $this->oService->getSysDatasetId($modId);
        }
        $this->aCrossHandlerData[$modId]['dataset_id'] = $datasetId;

        AMI_Event::enableHandler('on_table_get_item_post');
        return $datasetId;
    }
}

/**
 * AmiExt/CustomFields extension configuration front view.
 *
 * @package    Config_AmiExt_CustomFields
 * @subpackage View
 * @resource   ext_custom_fields/view/frn <code>AMI::getResource('ext_custom_fields/view/frn')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_CustomFields_ViewFrn extends AMI_ExtView{
    /**
     * Template block name
     *
     * @var string
     */
    protected $tplBlockName = 'modules_custom_fields_ext';

    /**
     * View body type
     *
     * @var string
     */
    protected $viewBodyType = '';

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * On before view event handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::get()
     */
    public function handleBeforeView($name, array $aEvent, $handlerModId, $srcModId){
        $this->tplBlockName = $aEvent['block'];
        $this->aLocale = array_merge($aEvent['aLocale'], $this->aLocale);
        $this->viewBodyType = $aEvent['type'];

        return $aEvent;
    }

    /**
     * Fills front custom fields data.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleDetails($name, array $aEvent, $handlerModId, $srcModId){
        if(AMI::getSingleton('env/request')->get('action') == 'show_details'){
            global $conn, $cms;

            $data = array(
                'metas'   => AMI_Registry::get('oGUI')->getMetas(),
                'details' => ''
            );
            $field = AMI::getSingleton('env/request')->get('field');
            $module = AMI::getSingleton('env/request')->get('module');
            $lang = AMI_Registry::get('lang_data');
            $aInstalledModules = $this->oExt->oService->getInstalledModules();

            if($field && $module && preg_match('/^[0-9a-z_]+$/si', $field) && in_array($module, $aInstalledModules)){
                $sql =
                    "SELECT `description` " .
                    "FROM `cms_modules_custom_fields` " .
                    "WHERE " .
                        "`system_name` = %s AND " .
                        "`module_name` = %s";

                $oDB = AMI::getSingleton('db');
                $description = $oDB->fetchValue(DB_Query::getSnippet($sql)->q($field)->q($module));
                if($description){
                    $description = unserialize($description);
                    if(is_array($description) && isset($description[$lang])){
                        $data['details'] = $description[$lang];
                    }
                }
            }

            echo $this->parse('cf_details_popup', $data);
            $cms->Core->Cache->pageIsComplitedForSave = TRUE;
            $conn->Out();
            die;
        }

        $isSubitem = false;
        if(isset($aEvent['type']) && $aEvent['type'] == 'subitems'){
            $isSubitem = true;
        }

        if(!$isSubitem && ($name != 'on_item_details') && ($this->viewBodyType == 'cats' || $this->viewBodyType == 'sticky_cats')){
            $handlerModId = $handlerModId . '_cat';
            $aCustomFields = $this->oExt->getCategoryCustomFields();
        }else{
            $aCustomFields = $this->oExt->getCustomFields();
        }

        $bodyType = AMI_Registry::get('AMI/Module/Environment/bodyType', 'specblock');

        $commonCustomFields = '';
        if(in_array($bodyType, array('details', 'items', 'cats')) && sizeof($this->oExt->aCrossHandlerData[$handlerModId]['db_fields'])){
            $splitter = $this->parse('common_cf_splitter');

            foreach($this->oExt->aCrossHandlerData[$handlerModId]['db_fields'] as $fieldId => $field){
                if(!array_key_exists($field, $aEvent['aData']) || !isset($aCustomFields[$fieldId]) || empty($aEvent['aData'][$field])){
                    continue;
                }
                if(($this->viewBodyType == 'sticky_items' || $this->viewBodyType == 'sticky_cats') && (mb_strpos($aCustomFields[$fieldId]['show_body_type'], ';body_urgent_items;') === false)){
                    continue;
                }
                if(($this->viewBodyType == 'items' || $this->viewBodyType == 'cats') && (mb_strpos($aCustomFields[$fieldId]['show_body_type'], ';body_items;') === false)){
                    continue;
                }

                $isCommonList = isset($this->oExt->aCrossHandlerData[$handlerModId]['common_db_fields'][$fieldId]);
                $aField = $aCustomFields[$fieldId];
                $aSetData = $this->oExt->aCrossHandlerData[$handlerModId] + array(
                    'id'      => $fieldId,
                    'prefix'  => $aField['prefix'],
                    'postfix' => $aField['postfix'],
                    'caption' => $aField['caption'],
                    'name'    => $aField['system_name'],
                    'type'    => $aField['ftype'],
                    'value'   => $aEvent['aData'][$field]
                );
                if(!$isCommonList){
                    $aSetData['is_unique'] = true;
                }
                if($aField['description']){
                    $aSetData['details'] = $this->parse('cf_details', $aSetData);
                }
                unset($aSetData['db_fields'], $aSetData['common_db_fields']);
                $fieldHTML = $this->parse('common_cf', $aSetData);
                if($isCommonList){
                    $commonCustomFields .= $fieldHTML . $splitter;
                }else{
                    $aEvent['oItem']->setValue(AmiExt_CustomFields_Common::PREFIX . $aField['system_name'], $fieldHTML);
                }
            }

        }
        if(mb_strlen($commonCustomFields)){
            $splitterLength = mb_strlen($splitter);
            if($splitterLength){
                $commonCustomFields = mb_substr($commonCustomFields, 0, -$splitterLength);
            }
            $aSetData = $this->oExt->aCrossHandlerData[$handlerModId] + array('list' => $commonCustomFields);
            unset($aSetData['db_fields'], $aSetData['common_db_fields']);
            $aEvent['aData']['common_cf_list'] = $this->parse('common_cf_list', $aSetData);
        }

        return $aEvent;
    }

    /**#@-*/
}
