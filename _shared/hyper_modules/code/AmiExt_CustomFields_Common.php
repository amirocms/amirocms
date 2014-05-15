<?php
/**
 * AmiExt/CustomFields extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiExt_CustomFields
 * @version   $Id: AmiExt_CustomFields_Common.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/CustomFields extension configuration.
 *
 * Common admin/front code.
 *
 * @package    Config_AmiExt_CustomFields
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class AmiExt_CustomFields_Common extends Hyper_AmiExt{
    /**
     * DB field prefix
     */
    const PREFIX = 'cf_';

    /**
     * Work mode (categories, pages, simple)
     *
     * @var string
     */
    protected $mode;

    /**
     * Dataset id
     *
     * @var int
     */
    protected $datasetId = null;

    /**
     * Contains loaded dataset data
     *
     * @var array
     */
    protected $aDataset = array();

    /**
     * Custom fields data
     *
     * @var array
     */
    protected $aCustomFields = array();

    /**
     * Category custom fields data
     *
     * @var array
     */
    protected $aCategoryCustomFields = array();

    /**
     * Flag specifying to use pagse dependence for filterring
     *
     * @var bool
     * @see AmiExt_CustomFields_Adm::handleFilterInit()
     * @see AmiExt_CustomFields_Adm::handleAddTable()
     */
    protected $usePagesDependence = false;

    /**
     * Service singleton
     *
     * @var ExtCustomFields_Service
     */
    public $oService;

    /**
     * Module postfix for category modules
     *
     * @var string
     */
    protected $catModPostfix = '';

    /**
     * Contains cross action handlers data
     *
     * @var array
     */
    public $aCrossHandlerData;

    /**
     * Category work mode
     *
     * @var string
     */
    protected $catMode;

    /**
     * Extension pre-initialization.
     *
     * @return void
     */
    public function extPreInit(){
        $this->oService = AMI::getSingleton($this->getExtId() . '/service');
        $this->mode = $this->oService->getModuleUsageMode($this->getModId());
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
     * Adds model validaton event handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handlePreInit($name, array $aEvent, $handlerModId, $srcModId){
        // AMI_Event::addHandler('on_get_validators', array($this, 'handleGetValidators'), $aEvent['modId']);

        return $aEvent;
    }

    /**
     * Adds fields validation.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTable::getValidators()
     * @todo   Implement.
     */
    /*
    public function handleGetValidators($name, array $aEvent, $handlerModId, $srcModId){
        // $aEvent['oTable']->addValidators(array('id_cat' => array('filled')));
        return $aEvent;
    }
    */

    /**
     * Handles getting item from table model.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleTableGetItem($name, array $aEvent, $handlerModId, $srcModId){
        /**
         * @var AMI_ModTableItem
         */
        $oItem = $aEvent['oItem'];

        $this->setDataset(null, $oItem, $handlerModId);

        $aFields =
            $this->isCatModule($handlerModId)
            ? $this->aCategoryCustomFields
            : $this->aCustomFields;

        foreach($aFields as $id => $aField){
            $oItem->setFieldCallback(self::PREFIX . $aField['system_name'], array($oItem, 'fcbHTMLEntities'));
            $this->aCrossHandlerData[$handlerModId]['db_fields'][$id] = self::PREFIX . $aField['system_name'];
            if(empty($aField['isnot_all'])){
                $this->aCrossHandlerData[$handlerModId]['common_db_fields'][$id] = self::PREFIX . $aField['system_name'];
            }
        }

        return $aEvent;
    }

    /**
     * Adds columns to list model.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::get()
     */
    public function handleAddTable($name, array $aEvent, $handlerModId, $srcModId){
        if($this->isCatModule($aEvent['modId'])){
            $aFields = $this->aCategoryCustomFields;
        }else{
            $aFields = $this->aCustomFields;
        }

        foreach($aFields as $id => $aField){
            $aEvent['oQuery']->addField(self::PREFIX . $aField['system_name'], $aEvent['alias']);
        }

        if($this->usePagesDependence && is_null($aEvent['oTable']->getDependenceResId('p'))){
            $aEvent['oTable']
                ->setDependence(
                    'pages',
                    'p',
                    'i.id_page = p.id' . (AMI::getSingleton('env/request')->get('id_dataset') == $this->oService->getSysDatasetId($this->getModId()) ? ' OR i.id_page = 0' : '')
                );
            $aEvent['oTable']->setActiveDependence('p');
        }
        return $aEvent;
    }

    /**#@-*/

    /**
     * Initilization by dataset id.
     *
     * @param  int $datasetId           Dataset id
     * @param  AMI_ModTableItem $oItem  Item model
     * @param  string $handlerModId     Module Id
     * @return void
     */
    public function setDataset($datasetId = null, AMI_ModTableItem $oItem = null, $handlerModId = null){
        if(is_null($datasetId)){
            $datasetId = $this->detectDatasetId($oItem, $handlerModId);
        }
        $datasetId = (int)$datasetId;
        if($this->datasetId === $datasetId){
            // dataset is already loaded
            return;
        }

        $modId = $handlerModId ? $handlerModId : $this->getModId();

        $oSnippet =
            $datasetId > 0
                ? DB_Query::getSnippet("AND i.id = %s AND i.`module_name` = %s")
                    ->plain($datasetId)
                    ->q($modId)
                : DB_Query::getSnippet("AND i.`module_name` = %s")
                    ->q($modId);

        /**
         * @var AMI_ModTableList
         */
        $oList = AMI::getResourceModel('modules_datasets/table')
            ->getList()
            ->addColumns(array('name', 'fields_map', 'fields_shared', 'fields_captions', 'postfix'))
            ->addWhereDef($oSnippet)
            ->load();

        $aDataset = array('fields_map' => '', 'fields_shared' => '', 'fields_captions' => array());

        foreach($oList as $oItem){
            $aRec = $oItem->getData();
            $aDataset['name'] = $aRec['name'];
            $aDataset['fields_map'] .= $aRec['fields_map'];
            $aDataset['fields_shared'] .= $aRec['fields_shared'];
            $aDataset['postfix'] = $aRec['postfix'];
            $aCaptionStruct = @unserialize($aRec['fields_captions']);
            if(!is_array($aCaptionStruct)){
                $aCaptionStruct = array();
            }
            $aDataset['fields_captions'] += $aCaptionStruct;
        }

        $this->string2Array($aDataset['fields_map']);
        $this->string2Array($aDataset['fields_shared']);

        if($aDataset['fields_map']){
            $this->loadFieldsByIds($aDataset['fields_map'], $aDataset['fields_captions'], $handlerModId);
        }
        $this->aDataset = $aDataset;
        $this->datasetId = $datasetId;

        if(AMI_Registry::get('side') != 'adm'){
            $this->aCrossHandlerData[$modId]['dataset_postfix'] = isset($aDataset['postfix']) ? $aDataset['postfix'] : '';
        }

        AMI_Event::enableHandler('on_table_get_item_post');
    }

    /**
     * Loads custom fields data by its ids in DB.
     *
     * @param  array  $aIds            Ids
     * @param  array  $aCaptionStruct  Captions
     * @param  string $handlerModId    Module Id
     * @return void
     */
    protected function loadFieldsByIds(array $aIds, array $aCaptionStruct = array(), $handlerModId = null){
        $fieldIds = array_diff(array_filter($aIds, 'is_numeric'), array_keys($this->aCustomFields));
        if(sizeof($fieldIds)){
            $lang = AMI_Registry::get('lang_data');
            $isAdmin = AMI_Registry::get('side') == 'adm';
            $aColumns = array_merge(
                array('id', 'prefix', 'postfix', 'system_name', 'ftype', 'default_caption'),
                ($isAdmin
                    ? array('public', 'admin_form', 'admin_ui', 'admin_filter')
                    : array('show_body_type', 'isnot_all', 'description')
                )
            );

            $oModelList = AMI::getResourceModel('modules_custom_fields/table')
                ->getList()
                ->addColumns($aColumns)
                ->dropColumn('datasets', 'd')
                ->addWhereDef(
                    DB_Query::getSnippet("AND i.`id` IN (%s)" . ($isAdmin ? '': ' AND i.`public` = 1'))
                    ->implode($fieldIds)
                )
                ->load();
            foreach($oModelList as $oModelItem){
                $aRec = $oModelItem->getData();
                $aRec['caption'] = $aRec['default_caption'];
                $id = (int)$aRec['id'];
                if(!$isAdmin){
                    $bodyType = AMI_Registry::get('AMI/Module/Environment/bodyType');
                    if($bodyType == 'cats'){
                        $bodyType = 'items';
                    }

                    $showBodyTypes = $aRec['show_body_type'];
                    $showBodyTypes = str_replace(";body_items;", ";items;", $showBodyTypes);
                    $showBodyTypes = str_replace(";body_urgent_items;", ";items;", $showBodyTypes);
                    $showBodyTypes = str_replace(";body_itemD;", ";details;", $showBodyTypes);

                    if(mb_strpos($showBodyTypes, ';' . $bodyType . ';') === false){
                        break;
                    }
                }
                foreach(array('prefix', 'postfix', 'caption') as $complexField){
                    $aRec[$complexField] = unserialize($aRec[$complexField]);
                    if(is_array($aRec[$complexField])){
                        if(isset($aRec[$complexField][$lang])){
                            $aRec[$complexField] = $aRec[$complexField][$lang];
                        }else{
                            $aRec[$complexField] = '';
                        }
                    }else{
                        $aRec[$complexField] = '';
                    }
                }
                $aRec['caption'] = isset($aCaptionStruct[$id]) ? $aCaptionStruct[$id] : $aRec['caption'];
                if(!mb_strlen($aRec['caption'])){
                    $aRec['caption'] = 'unknown';
                }
                if($isAdmin){
/*
                    $aDefaultParams = unserialize($aRec['default_params']);
                    if(is_array($aDefaultParams)){
                        $aRec += $aDefaultParams;
                    }
                    unset($aRec['default_params']);
*/
                }elseif($aRec['description']){
                    // front
                    $description = unserialize($aRec['description']);
                    $aRec['description'] = is_array($description) && isset($description[$lang]) && mb_strlen(trim(strip_tags($description[$lang])));
                }
                $modId = $handlerModId ? $handlerModId : $this->getModId();
                if($this->isCatModule($modId)){
                    $this->aCategoryCustomFields[$id] = $aRec;
                }else{
                    $this->aCustomFields[$id] = $aRec;
                }
            }
        }
    }

    /**
     * Converts string separated by ';' (field lists are stored by this way in datasets db table) to array.
     *
     * @param  string &$ids  Ids
     * @return bool  True if sizeof(result) > 0
     */
    protected function string2Array(&$ids){
        $ids = trim($ids, ';');
        $ids = $ids ? explode(';', $ids) : array();
        return sizeof($ids) > 0;
    }

    /**
     * Check the category module.
     *
     * @param  string $modId  Module Id
     * @return bool  Flag specifying the category module
     */
    protected function isCatModule($modId){
        return ((mb_substr($modId, -4) === '_cat') ? true : false);
    }

    /**
     * Returns custom fields array.
     *
     * @return array  Custom fields array
     */
    public function getCustomFields(){
        return $this->aCustomFields;
    }

    /**
     * Returns category custom fields array.
     *
     * @return array  Category custom fields array
     */
    public function getCategoryCustomFields(){
        return $this->aCategoryCustomFields;
    }
}
