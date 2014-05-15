<?php
/**
 * AmiCatalog/Items configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiCatalog_Items
 * @version   $Id: AmiCatalog_ItemsCat_Table.php 45064 2013-12-05 09:27:10Z Leontiev Anton $
 * @since     6.0.2
 */

/**
 * AmiCatalog/Items configuration category table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      6.0.2
 */
class AmiCatalog_ItemsCat_Table extends Hyper_AmiCatalog_Cat_Table{
    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model
     */
    public function __construct(array $aAttributes = array()){
        $this->tableName = 'cms_' . $this->getTablePrefix() . '_cats';

        $section = AMI_ModDeclarator::getInstance()->getSection($this->getModId());
        $this->setDependence($section . '_cat', 'p', 'p.id=i.id_parent', 'LEFT JOIN');

        parent::__construct($aAttributes);

        $aRemap = array(
            'id'               => 'id',
            'public'           => 'public',

            'sublink'          => 'sublink',
            'id_page'          => 'id_page',
            'lang'             => 'lang',

            'header'           => 'name',
            'body'             => 'description',
            'sticky'           => 'urgent',
            'date_sticky_till' => 'urgent_date',
            'hide_in_list'     => 'public_direct_link',
            'date_modified'    => 'modified_date',
            'num_items'        => 'count_childs',
            'num_public_items' => 'count_public_childs'
        );
        $this->addFieldsRemap($aRemap);
    }

    /**
     * Sets new table name for a model.
     *
     * @param string $tableName  New table name
     * @return void
     * @amidev Temporary
     */
    public function setTableName($tableName){
    }

    /**
     * Returns prefix for eshop and its clones tables.
     *
     * @return string
     */
    public function getTablePrefix(){
        $section = AMI_ModDeclarator::getInstance()->getSection($this->getModId());
        $prefix = $section;
        switch ($section){
            case 'eshop':
                $prefix = 'es';
                break;
            case 'kb':
                $prefix = 'kb';
                break;
            case 'portfolio':
                $prefix = 'po';
        }
        return $prefix;
    }
}

/**
 * AmiCatalog/Items configuration category table item model.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      6.0.2
 */
class AmiCatalog_ItemsCat_TableItem extends Hyper_AmiCatalog_Cat_TableItem{
    /**
     * Lite fiedls list
     *
     * @var    array
     * @see    https://jira.cmspanel.net:8443/browse/CMS-11317
     * @amidev Temporary?
     */
    protected $aLiteFields = array(
        'id', 'id_owner', 'sys_rights_r', 'sys_rights_w', 'sys_rights_d', // 'sm_data',
        // 'body', 'lang', 'public', 'header', 'announce',
        // 'sublink',
        'sticky', 'date_sticky_till', 'hide_in_list',
        'date_modified',
        // 'price' - because of properties prices recalculation
        // 'price_caption1', 'price_caption2', 'price_caption3', 'price_caption4', 'price_caption5',
        // 'price1', 'price2', 'price3', 'price4', 'price5',
        // 'price_mask', 'is_price_list',
        // ? 'special_flag', 'instruct', 'instruction', 'hs_cat', 'id_page',
        // ? 'dataset_id', 'dataset_data', 'all_parents', 'adv_campaign_type',
        // ? 'num_items', 'num_public_items'
        'id_discount', 'id_shipping_type', 'allow_fraction',
        'tax_class_type', 'id_tax_class',
        'position', 'id_external',
        'details_noindex',
        'html_title', 'html_keywords', 'html_description', 'html_is_kw_manual'
    );

    /**
     * Allow to save model flag.
     *
     * @var    bool
     * @amidev Temporary
     */
    protected $bAllowSave = TRUE;

    /**
     * Datasets array
     *
     * @var array
     */
    protected static $aDatasets = null;

    /**
     * Old environment module
     *
     * @var    ModuleEshopCat
     * @amidev Temporary?
     */
    protected $oModule;

    /**
     * Initializing table item data.
     *
     * @param AMI_ModTable $oTable  Module table model
     * @param DB_Query     $oQuery  Required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery = null){
        parent::__construct($oTable, $oQuery);
        $this->oTable->addValidators(
            array(
                'lang'     => array('filled'),
                'header'   => array('filled'),
                'announce' => array('required'),
                'body'     => array('required')
            )
        );
        $this->setFieldCallback('header', array($this, 'fcbHTMLEntities'));

        $aItemFields = $this->oTable->getAvailableFields();
        foreach($aItemFields as $field){
            if(!is_array($field) && mb_substr($field, 0, 13) == 'custom_field_'){
                $this->setFieldCallback($field, array($this, 'fcbHTMLEntities'));
            }
        }

        if(is_null(self::$aDatasets)){
            $table = 'cms_' . $this->oTable->getTablePrefix() . '_datasets';
            $oQuery = new DB_Query($table);
            $oQuery->addFields(array('id', 'name'));
            $oQuery->addWhereDef(DB_Query::getSnippet(' AND lang = %s')->q(AMI_Registry::get('lang_data')));
            $oDatasetsRS = AMI::getSingleton('db')->select($oQuery);
            if($oDatasetsRS->count()){
                foreach($oDatasetsRS as $aRow){
                    self::$aDatasets[$aRow['id']] = $aRow['name'];
                }
            }
        }

        // #CMS-11317 {
        // Collect changed fields except $this->aLiteFields
        $modId = $this->getModId();
        $aOriginFields = array_diff($this->oTable->getAvailableFields(), $this->aLiteFields);
        $aOriginFields = array_filter($aOriginFields, array($this, 'cbFilterOriginFields'));
        if(AMI::issetOption($modId, 'search_fields_setup')){
            $aSearch = AMI::getOption($modId, 'search_fields_setup');
            $aRemap = $this->oTable->getFieldsRemap();
            foreach($aSearch['hs_cat']['fields'] as $field){
                $index = array_search($field, $aRemap);
                if($index !== FALSE){
                    $field = $index;
                }
                $aOriginFields[] = $field;
            }
        }
        $this->setOriginFields($aOriginFields);
        $aFields = array_fill_keys($aOriginFields, null);
        $this->aData = $aFields;
        $this->aOrigData['aData'] = $aFields;
        // } #CMS-11317
    }

    /**
     * Saves current cat item data.
     *
     * @return AmiCatalog_ItemsCat_TableItem
     * @throws AMI_ModTableItemException  If validation failed.
     * @amidev Temporary?
     */
    public function save(){
        // #CMS-11317 {
        // return $this; // In progress

        // Wipe out null values
        $this->aData = array_filter($this->aData, array($this, 'cbFilterData'));
        $aChangedFields = array();
        foreach($this->aData as $key => $value){
            if(
                array_key_exists($key, $this->aOrigData['aData']) &&
                $value !== $this->aOrigData['aData'][$key]
            ){
                $aChangedFields[] = $key;
            }
        }
        // d::pr($aChangedFields);
        if($aChangedFields){
            $this->aData['id_external_manual'] = in_array('id_external', $aChangedFields);
            $this->runInPrevEnv(empty($this->aData['id']) ? 'add' : 'apply');

            return $this;
        }
        /*
        // Skip custom fields
        $aCustomFieldsBak = array();
        foreach(array_keys($this->aData) as $field){
            if(mb_strpos($field, 'custom_field_') === 0){
                $aCustomFieldsBak[$field] = $this->aData[$field];
                unset($this->aData[$field]);
            }
        }
        */
        parent::save();
        // $this->aData += $aCustomFieldsBak;

        return $this;
        // } #CMS-11317
    }

    /**
     * Deletes category/items from table and clear data array.
     *
     * @param  mixed $id  Primary key value of item
     * @return AmiCatalog_ItemsCat_TableItem
     * @amidev Temporary?
     */
    public function delete($id = null){
        /*
        // Forbid saving before 6.0 or our modules
        if(
            !AMI_Registry::get('ami_allow_model_save', false) &&
            (mb_substr($this->oTable->getTableName(), 0, 4) === 'cms_') &&
            !(isset($this->bAllowSave) && $this->bAllowSave)
        ){
            trigger_error('Forbidden!', E_USER_ERROR);
        }
        */
        if(!is_null($id)){
            $this->aData['id'] = $id;
        }
        try{
            $this->runInPrevEnv('del');
            $this->id = $this->idEmpty;
            $this->aData = array();
            $this->skipSave = FALSE;
        }catch(AMI_ModTableItemException $oException){
            // Nothing to do
        }
        return $this;
    }

    /**
     * Callback filterring array values.
     *
     * @param  mixed $value  Array element
     * @return bool
     * @see    AmiCatalog_ItemsCat_TableItem::__construct()
     * @amidev Temporary
     */
    protected function cbFilterOriginFields($value){
        return !is_array($value);
    }

    /**
     * Callback filterring null values.
     *
     * @param  mixed $value  Array element
     * @return bool
     * @see    AmiCatalog_ItemsCat_TableItem::save()
     * @amidev Temporary
     */
    protected function cbFilterData($value){
        return !is_null($value);
    }

    /**
     * Runs previous environment to run complex business logic.
     *
     * @param  string $action  Action ('add'/'save'/'del')
     * @return void
     * @throws AMI_ModTableItemException  If validation failed.
     * @todo   Throw exception if errno !=0
     * @amidev Temporary?
     */
    protected function runInPrevEnv($action){
        $modId = $this->getModId();
        $isFront = AMI_Registry::get('side') != 'adm';

        global $cms, $db;

        $aGuiDebug = $cms->Gui->debug;
        $cms->Gui->debug = array();

        /*
        $section = AMI_ModDeclarator::getInstance()->getSection($this->getModId());
        $aExt = AMI::getOption($modId, 'extensions');
        $index = array_search('ext_' . $section . '_custom_fields', $aExt);
        if($index !== FALSE){
            // Disable custom fields extension
            $aExtBak = $aExt;
            unset($aExt[$index]);
            AMI::setOption($modId, 'extensions', $aExt);
        }
        */

        if(empty($this->oModule)){
            $this->oModule = &$cms->Core->GetModule($modId);
            if($isFront){
                $cms->Core->Side = 'admin';
                $cms->Filter = new Filter($cms);
            }
            $this->oModule->InitEngine($cms, $db);
            $this->oModule->Engine->Init();
        }

        if($isFront){
            $this->oModule->Engine->cms->PushFrontSettings($db, AMI_Registry::get('lang_data'));
            require_once $GLOBALS['CLASSES_PATH'] . 'Admin.php';
            $oAdmCMS = new Admin($cms->Core);
            $oAdmCMS->constructorPostActions();
            $oAdmCMS->InitFromObject($cms, $this->oModule);
            $oAdmCMS->Filter = new Filter($oAdmCMS);
            $oAdmCMS->InitMessages(
                AMI::getSingleton('env/template_sys')
                ->parseLocale('templates/lang/modules/_messages.lng')
            );
            $oTmpCMS = clone($cms);
            // $cms = &$oAdmCMS;###???
            $this->oModule->Engine->cms = &$oAdmCMS;
            // $this->oModule->Engine->cms->Messages = $cms->Messages;###
            $cms->SuppressStatusErrors = TRUE;
            $this->oModule->Engine->realSide = 'front';
            $this->oModule->Engine->side = 'admin';
            $this->oModule->Engine->_InitAdmin();
            $this->oModule->Engine->oEshop->cms = &$oAdmCMS;
            for($i = 0; $i < $this->oModule->Engine->oEshop->numItemTypes; $i++){
                $this->oModule->Engine->oEshop->_oExtensions[$i]->cms = &$oAdmCMS;
            }
            foreach(array_keys($this->oModule->Engine->ext) as $extModId){
                $this->oModule->Engine->ext[$extModId]->cms = &$oAdmCMS;
            }
            AMI_Registry::set('side', 'adm');
        }

        // $this->oModule->Engine->cms->Vars = array();
        $this->oModule->Engine->cms->VarsPost = array();
        $aRemap = $this->oTable->getFieldsRemap();
        $aRemap += array(
            'id_parent'  => 'parent',
            'dataset_id' => 'dataset'
        );

        if($action == 'apply'){
            // $this->oModule->Engine->cms->VarsPost['action'] = 'save';
            // $this->oModule->Engine->cms->VarsPost['action_original'] = 'apply';
            if(!empty($this->aOrigData['html_meta'])){
                foreach($this->aOrigData['html_meta'] as $field => $value){
                    $field = 'original_html_' . $field;
                    // $this->oModule->Engine->cms->Vars[$field] = $value;
                    $this->oModule->Engine->cms->VarsPost[$field] = $value;
                }
                // $this->oModule->Engine->cms->Vars['is_keywords_manual'] = $this->aData['html_is_kw_manual'];
                $this->oModule->Engine->cms->VarsPost['is_keywords_manual'] = $this->aData['html_is_kw_manual'];
            }
            if(isset($this->aOrigData['aData']['sublink'])){
                // $this->oModule->Engine->cms->Vars['original_sublink'] = $this->aOrigData['aData']['sublink'];
                $this->oModule->Engine->cms->VarsPost['original_sublink'] = $this->aOrigData['aData']['sublink'];
            }
        }

        $oEshop = AMI::getSingleton('eshop');
        if($oEshop->areOtherPricesEnabled()){
            // Parse information about extra prices from category
            $aPrices = array();
            foreach($oEshop->getOtherPrices() as $num){
                $field = 'price' . $num;
                if(isset($this->aData[$field])){
                    $aPrices[$num] = $field;
                }
            }
            if(sizeof($aPrices)){
                $aCatPrices = array();
                foreach($aPrices as $num => $field){
                    $aPriceData = explode('#', $this->aData[$field]);
                    $aCatPrices['price' . $num] = $aPriceData[0];
                    $aPriceData = explode(':', $aPriceData[1]);
                    $aCatPrices['currency' . $num] = $aPriceData[0];
                    $aCatPrices['db_currency' . $num] = sizeof($aPriceData) > 1 ? $aPriceData[1] : '';
                }
                $this->aData = $aCatPrices + $this->aData;
                unset($aCatPrices, $aPriceData);
                /*
                $mask = $this->aData['price_mask'];
                foreach($aPrices as $num => $field){
                    if($mask & (2 ^ $num)){
                        $this->aData['price_checkbox' . $num] = 1;
                    }else{
                        unset($this->aData['price_checkbox' . $num]);
                    }
                }
                */
            }
            unset($aPrices);
        }

        foreach($this->aData as $field => $value){
            if(isset($aRemap[$field])){
                $field = $aRemap[$field];
            }
            // d::pr($field.' = '.$value);
            // $this->oModule->Engine->cms->Vars[$field] = $value;
            $this->oModule->Engine->cms->VarsPost[$field] = $value;
        }
        // $this->oModule->Engine->cms->VarsPost['id_parent'] = '';


        $id = empty($this->aData['id']) ? 0 : $this->aData['id'];
        // d::vd($this->oModule->Engine->cms->Vars, 'Vars');d::w('<h style="color: red;">not saved</h1>');return;###
        $this->oModule->Engine->ProcessAction($action, $id);

        if('add' === $action){
            $this->id = $this->oModule->Engine->appliedId;
            $this->aData['id'] = $this->id;
        }

        if($isFront){
            $cms = &$oTmpCMS;
            $this->oModule->Engine->cms = &$oTmpCMS;
            $this->oModule->Engine->oEshop->cms = &$oTmpCMS;
            for($i = 0; $i < $this->oModule->Engine->oEshop->numItemTypes; $i++){
                $this->oModule->Engine->oEshop->_oExtensions[$i]->cms = &$oTmpCMS;
            }
            foreach(array_keys($this->oModule->Engine->ext) as $extModId){
                $this->oModule->Engine->ext[$extModId]->cms = &$oTmpCMS;
            }
            $cms->Core->Side = 'front';
            $cms->PopFrontSettings($db, AMI_Registry::get('lang_data'));
            AMI_Registry::set('side', 'frn');
        }
        $cms->Gui->debug = $aGuiDebug;

        /*
        if(isset($aExtBak)){
            // Restore custom fields extension
            AMI::setOption($modId, 'extensions', $aExtBak);
        }
        */

        if($this->oModule->Engine->errno){
            $aErrors = array(
                array(
                    'validator' => 'full_env',
                    'message'   => $this->oModule->Engine->error,
                    'code'      => $this->oModule->Engine->errno,
                    'lastQuery' => AMI_Registry::get('deprecated/amiLastSQLQuery', '')
                )
            );
            throw new AMI_ModTableItemException(
                'Validation failed: ' . var_export($aErrors, TRUE),
                AMI_ModTableItemException::VALIDATION_FAILED,
                $aErrors
            );
        }
    }

    /**
     * Returns dataset name.
     *
     * @param int $idDataset  Dataset id
     * @return string
     */
    public function getDatasetName($idDataset){
        return empty(self::$aDatasets[$idDataset]) ? '' : self::$aDatasets[$idDataset];
    }

    /**
     * Returns datasets list.
     *
     * @return array
     */
    public function getDatasetsList(){
        return self::$aDatasets;
    }
}

/**
 * AmiCatalog/Items configuration category table list model.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      6.0.2
 */
class AmiCatalog_ItemsCat_TableList extends Hyper_AmiCatalog_Cat_TableList{
}
