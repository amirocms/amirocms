<?php
/**
 * AmiCatalog/Items configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiCatalog_Items
 * @version   $Id: AmiCatalog_Items_Table.php 48452 2014-03-05 13:03:38Z Maximov Alexey $
 */

/**
 * AmiCatalog/Items configuration table model.
 *
 * <b>Since 6.0.2 this model available for saving (excluding custom fields).</b><br /><br />
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.<br /><br />
 * Fields description:
 * - <b>on_special</b> - special bit mask (int);
 * - <b>special_announce</b> - product announce displaying in the special products specblock (string);
 * - <b>price</b> - product base price (double);
 * - <b>price[1..N]</b> - product extra prices (double);
 * - <b>sku</b> - product sku code (string);
 * - <b>id_external</b> - product external id used during data exchange (string);
 * - <b>letter</b> - product header first letter (char);
 * - <b>size</b> - product sizes (string);
 * - <b>weight</b> - product weight (double);
 * - <b>rest</b> - product rest (int);
 * - <b>max_quantity</b> - product max quantity per order (int);
 * - <b>tax</b> - product tax value (double);
 * - <b>tax_type</b> - product tax value type (abs'|'percent', string);
 * - <b>charge_tax_type</b> - product tax charge_type ('charge'|'detach', string);
 * - <b>shipping</b> - product shipping value (double);
 * - <b>shipping_type</b> - product shipping value type (abs'|'percent', string);
 * - <b>discount</b> - product discount value (double);
 * - <b>discount_type</b> - product discount value type (abs'|'percent', string);
 * - <b>id_source</b> - source product id (used for product copies, int);
 * - <b>type</b> - type of item ('eshop_goods'|'eshop_digitals', string);
 * - <b>allow_fraction</b> - flag specifying to allow fractional product quantity
 * during order, since 6.0.2 (0/1);
 * - <b>copies_count</b> - count of product copies in other categories (int);
 * - <b>custom_field_*</b> - product custom field value (mixed);
 * - <b>aCopies</b> - if 'useCopies' attribute is TRUE and product have copies<br />
 * in other categories, array of category Ids will be loaded / processed,<br />
 * since 6.0.2 (array);
 * - <b>aProperties</b> - if 'loadProperties' attribute is TRUE and product have<br />
 * properties, array of property models will be loaded, since 6.0.2 (array, read only).<br />
 * To load copies use:<br />
 * - $oTable = AMI::getResourceModel('{$modId}/table')->setAttr('useCopies', TRUE).<br />
 * To load properties use:<br />
 * - $oTable = AMI::getResourceModel('{$modId}/table')->setAttr('loadProperties', TRUE).<br />
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      6.0.2
 */
class AmiCatalog_Items_Table extends Hyper_AmiCatalog_Table{
    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model
     * @todo  Describe several fields
     */
    public function __construct(array $aAttributes = array()){
        // #CMS-11434 hack for extensions
        $this->aFieldNamesRemap += array(
            'votes_rate' => 'votes_rate',
            'disable_comments' => 'disable_comments'
        );

        // d::vd($aAttributes);###
        $this->initAttributes($aAttributes);
        $this->addSystemFields(
            array(
                'num_files_all', 'files_size', 'hs_item', 'hs_cat',
                'show_all_props', 'id_shipping_type', 'tax_class_type', 'id_tax_class'
            )
        );

        // Parent constructor is called from self::setModId()
        // parent::__construct();

        $aRemap = array(
            'id'               => 'id',
            'public'           => 'public',

            'id_cat'           => 'id_category',
            'sublink'          => 'sublink',
            'id_page'          => AMI_ModTable::FIELD_DOESNT_EXIST,
            'lang'             => 'lang',

            'header'           => 'name',
            'body'             => 'description',
            'date_exported'    => 'export_date',
            'copies_count'     => 'links_count',
            'sticky'           => 'urgent',
            'date_sticky_till' => 'urgent_date',
            'hide_in_list'     => 'public_direct_link',
            'date_created'     => 'date',
            'date_modified'    => 'modified_date',
            'ext_dsc_disable'  => 'disable_comments',
            'flags'            => 'on_special',
            'type'             => 'item_type',
            'num_files'        => 'num_files'
        );
        $this->addFieldsRemap($aRemap);
    }

    /**
     * Set module id.
     *
     * @param  string $modId  Module Id.
     * @return void
     * @amidev
     */
    public function setModId($modId){
        parent::setModId($modId);

        $this->tableName = 'cms_' . $this->getTablePrefix() . '_items';
        $section = AMI_ModDeclarator::getInstance()->getSection($this->getModId());
        $dependentRes = $this->getDependenceResId('cat');
        if(!isset($dependentRes)){
            $this->setDependence($section . '_cat', 'cat', 'cat.id=i.id_category');
        }

        parent::__construct($this->aAttributes);
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
        switch($section){
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
 * AmiCatalog/Items configuration table item model.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      6.0.2
 */
class AmiCatalog_Items_TableItem extends Hyper_AmiCatalog_TableItem{
    /**
     * Categories extra price info cache
     *
     * @var array
     */
    protected static $aCatPrices = array();

    /**
     * Lite fiedls list
     *
     * @var    array
     * @see    https://jira.cmspanel.net:8443/browse/CMS-11316
     * @amidev Temporary?
     */
    protected $aLiteFields = array(
        'id', 'id_owner', 'sys_rights_r', 'sys_rights_w', 'sys_rights_d', // 'sm_data',
        // 'body', 'lang', 'public', 'header', 'announce',
        // 'sublink',
        'date_exported', 'copies_count',
        'sticky', 'date_sticky_till', 'hide_in_list',
        'date_created', 'date_modified', 'flags',
        // 'price' - because of properties prices recalculation
        // 'price1', 'price2', 'price3', 'price4', 'price5',
        // 'price6', 'price7', 'price8', 'price9', 'price10', 'price11',
        // 'price12', 'price13', 'price14', 'price15', 'price16',
        'type', 'num_files', 'special_announce', 'price_mask',
        'tax', 'tax_type', 'charge_tax_type', 'shipping', 'shipping_type',
        'discount', 'discount_type', 'sku',
        'rate_opt', 'votes_rate', 'votes_count', // 'link_alias',
        'position', 'max_quantity', 'id_external', 'letter',
        'size', 'rest', 'id_source', 'details_noindex', // 'weight' - because of properties prices recalculation
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
     * Price formatter defaults
     *
     * @var    array
     * @see    EshopItem_TableItem::fmtPrice()
     * @amidev Temporary?
     */
    protected $aPriceDefaults = array(
        'number_decimal_digits' => 2,
        'decimal_point'         => '.'
    );

    /**
     * Old environment module
     *
     * @var    ModuleEshopItem
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
        $this->setFieldCallback('price', array($this, 'fcbPrice'));

        $aItemFields = $this->oTable->getAvailableFields();
        foreach($aItemFields as $field){
            if(!is_array($field) && mb_substr($field, 0, 13) == 'custom_field_'){
                $this->setFieldCallback($field, array($this, 'fcbHTMLEntities'));
            }
        }

        // #CMS-11316 {
        // Collect changed fields except $this->aLiteFields
        $modId = $this->getModId();
        $aOriginFields = array_diff($this->oTable->getAvailableFields(), $this->aLiteFields);
        $aOriginFields = array_filter($aOriginFields, array($this, 'cbFilterOriginFields'));
        if(AMI::issetOption($modId, 'search_fields_setup')){
            $aSearch = AMI::getOption($modId, 'search_fields_setup');
            $aRemap = $this->oTable->getFieldsRemap();
            foreach($aSearch['hs_item']['fields'] as $field){
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
        $this->setFieldCallback('aProperties', array($this, 'fcbProperties'));

        // } #CMS-11316
    }

    /**
     * Loads data by specified condition or sets new item data.
     *
     * @return AmiCatalog_Items_TableItem
     * @amidev Temporary?
     */
    public function load(){
        parent::load();

        if($this->id){
            if(
                !empty($this->aData['copies_count']) &&
                $this->oTable->getAttr($this->getModId(), 'useCopies', FALSE)
            ){
                // Load copies
                $this->aData['aCopies'] = array();
                $oCopies =
                    $this->oTable
                    ->getList()
                    ->addColumn('id')
                    ->addColumn('id_cat')
                    ->addWhereDef('AND `id_source` = ' . $this->id)
                    ->load();
                foreach($oCopies as $oCopy){
                    $this->aData['aCopies'][] = array(
                        'itemId'        => $oCopy->getId(),
                        'categoryId'    => $oCopy->id_cat
                    );
                }
                unset($oCopies, $oCopy);
                $this->aOrigData['aData']['aCopies'] = $this->aData['aCopies'];
            }
            $this->loadProperties();
        }

        return $this;
    }

    /**
     * Saves current item data.
     *
     * @return AmiCatalog_Items_TableItem
     * @throws AMI_ModTableItemException  If validation failed.
     * @amidev Temporary?
     */
    public function save(){
        // #CMS-11316 {
        // Wipe out null values
        $this->aData = array_filter($this->aData, array($this, 'cbFilterData'));
        $aChangedFields = array();
        unset($this->aData['aProperties']);
        foreach($this->aData as $key => $value){
            if(
                array_key_exists($key, $this->aOrigData['aData']) &&
                $value !== $this->aOrigData['aData'][$key]
            ){
                $aChangedFields[] = $key;
            }
        }
        foreach($this->aOrigData['aData'] as $key => $value){
            if(!array_key_exists($key, $this->aData)){
                $aChangedFields[] = $key;
            }
        }
        if($aChangedFields){
            if(isset($this->aData['aCopies'])){
                $this->aData['item_links'] = array();
                if(is_array($this->aData['aCopies']) && sizeof($this->aData['aCopies'])){
                    foreach($this->aData['aCopies'] as $aCopy){
                        $this->aData['item_links'][] = $aCopy['categoryId'];
                    }
                }
            }
            $this->aData['id_external_manual'] = in_array('id_external', $aChangedFields);
            $this->aData['rewrite_ratings'] =
                in_array('ext_rate_rate', $aChangedFields) ||
                in_array('ext_rate_count', $aChangedFields);

            $this->runInPrevEnv(empty($this->aData['id']) ? 'add' : 'apply');
            unset($this->aData['item_links']);
            $this->loadProperties();
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
        // } #CMS-11316
    }

    /**
     * Deletes item from table and clear data array.
     *
     * @param  mixed $id  Primary key value of item
     * @return AmiCatalog_Items_TableItem
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
     * Returns additional price data with applied discounts, taxes and currency convertions.
     *
     * @param  int    $priceNumber  Price number (1..N)
     * @param  string $currency     Result currency (default 'RUR')
     * @return array
     * @since  6.0.2
     */
    public function getAdditionalPrice($priceNumber, $currency = 'RUR'){
        $oEshop = AMI::getSingleton('eshop');
        $priceNumber = (int)$priceNumber;
        $aPrice = array(
            'price'         => $this->{'price' . $priceNumber},
            'priceNumber'   => $priceNumber,
            'tax'           => $this->tax,
            'taxType'       => $this->tax_type,
            'chargeTaxType' => $this->charge_tax_type,
            'discount'      => $this->discount,
            'discountType'  => $this->discount_type,
            'currency'      => $currency,
        );
        return $oEshop->calcPrice($aPrice, $withDiscounts);
    }

    /**
     * Read only mode for aProperties.
     *
     * @param  array $aData  Formatter data
     * @return array
     * @amidev Temporary?
     */
    protected function fcbProperties(array $aData){
        if($aData['action'] === 'set'){
            $aData['_skip'] = TRUE;
            trigger_error("Readonly property 'aProperties' cannot be set!", E_USER_WARNING);
        }

        return $aData;
    }

    /**
     * Callback filterring array values.
     *
     * @param  mixed $value  Array element
     * @return bool
     * @see    AmiCatalog_Items_TableItem::__construct()
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
     * @see    AmiCatalog_Items_TableItem::save()
     * @amidev Temporary
     */
    protected function cbFilterData($value){
        return !is_null($value);
    }

    /**
     * Price field callback.
     *
     * @param  array $aData  Field formatter data
     * @return array
     * @amidev Temporary?
     */
    protected function fcbPrice(array $aData){
        $action = $aData['action'];

        switch($action){
            case 'get':
                $aOptions = array();
                foreach($this->aPriceDefaults as $key => $value){
                    $aOptions[$key] =
                        AMI::issetOption($aData['modId'], $key)
                            ? AMI::getOption($aData['modId'], $key)
                            : $value;
                }
                if(!is_null($aData['value'])){
                    $aData['value'] = number_format(
                        $aData['value'],
                        $aOptions['number_decimal_digits'],
                        $aOptions['decimal_point'],
                        ''
                    );
                }
                break;
            case 'set':
                $aData['value'] = (float)preg_replace('/[^\d.]/', '', $aData['value']);
                break;
        }

        return $aData;
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
            // $section = AMI_ModDeclarator::getInstance()->getSection($modId);
            /*
            $aTemplates = array(
                $section . '_item_list'    => 'templates/' . $section . '_item_list.tpl',
                $section . '_item_subform' => 'templates/' . $section . '_item_form.tpl',
                $section . '_item_form'    => 'templates/form.tpl'
            );
            */
            if($isFront){
                $cms->Core->Side = 'admin';
                $cms->Filter = new Filter($cms);
            }

            $this->oModule->InitEngine($cms, $db);
            /*
            $localePath =
                $isFront
                    ? 'templates/lang/modules/_messages.lng'
                    : 'templates/lang/_common_msgs.lng';
            */
            /*
                array(),
                $localePath,
                $localePath
            */
            /*
                $aTemplates,
                'templates/lang/_' . $section . '_msgs.lng',
                'templates/lang/' . $section . '_item.lng'
            */
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

        $this->oModule->Engine->cms->Vars = array();
        $this->oModule->Engine->cms->VarsPost = array();
        // d::vd($this->aData, 'aData');###
        $aRemap = $this->oTable->getFieldsRemap();
        $aRemap['cat_id'] = 'cat';
        $aRemap['id_cat'] = 'cat';
        if(isset($this->aData['date_created'])){
            $this->aData['date_created'] = AMI_Lib_Date::formatDateTime($this->aData['date_created']);
        }else{
            $this->aData['date_created'] = AMI_Lib_Date::formatDateTime(date('Y-m-d'));
        }
        if($action == 'apply'){
            $this->oModule->Engine->cms->VarsPost['from_60'] = TRUE;
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
        // @todo: Move to common part of ratings extension
        if(isset($this->aData['ext_rate_opt'])){
            // Conver bit-mask field to 4 different checkboxes
            $aRateOptions = array('allow_ratings', 'display_ratings', 'sort_by_ratings', 'display_votes');
            foreach($aRateOptions as $idx => $field){
                $this->aData[$field] = $this->aData['ext_rate_opt'] & (2 ^ $idx) ? 1 : 0;
            }
            unset($this->aData['ext_rate_opt']);
        }

        $oEshop = AMI::getSingleton('eshop');
        if($oEshop->areOtherPricesEnabled()){
            // Parse information about extra prices from category
            $hasCatId = isset($this->aData['cat_id']);
            $aPrices = array();
            foreach($oEshop->getOtherPrices() as $num){
                $field = 'price' . $num;
                // if(isset($this->aData[$field])){
                    if($hasCatId){
                        $aPrices[$num] = $field;
                    }else{
                        // Have no ctagory id, cannot save field correctly
                        unset($this->aData[$field]);
                    }
                // }
            }
            if(sizeof($aPrices)){
                if(!isset(self::$aCatPrices[$this->aData['cat_id']])){
                    $aPrices[] = 'id';
                    $oCatItem =
                        AMI::getResourceModel($this->oTable->getDependenceResId('cat') . '/table')
                        ->find($this->aData['cat_id'], $aPrices);
                    $aCatData = $oCatItem->getData();
                    $aCatPrices = array();
                    array_pop($aPrices);

                    foreach($aPrices as $num => $field){
                        $aPriceData = explode('#', $aCatData[$field]);
                        $aCatPrices = array();
                        if($aPriceData[0] !== ''){
                            $aCatPrices['price_expr' . $num] = $aPriceData[0];
                        }
                        $aPriceData = explode(':', $aPriceData[1]);
                        $aCatPrices['currency' . $num] = $aPriceData[0];
                        if(sizeof($aPriceData) > 1){
                            $aCatPrices['currency' . $num] = $aPriceData[1];
                        }
                    }
                    self::$aCatPrices[$this->aData['cat_id']] = $aCatPrices;
                    unset($aCatPrices, $aPriceData, $aCatData, $oCatItem);
                }
                $this->aData = self::$aCatPrices[$this->aData['cat_id']] + $this->aData;
                if(isset($this->aData['price_mask'])){
                    $mask = $this->aData['price_mask'];
                }else{
                    // build price mask
                    $mask = 0;
                    foreach($aPrices as $num => $field){
                        if(!isset($this->aData[$field])){
                            $mask = $mask | pow(2, $num - 1);
                        }
                    }
                    $this->aData['price_mask'] = $mask;
                }
                foreach($aPrices as $num => $field){
                    if($mask & pow(2, $num - 1)){
                        $this->aData['price_checkbox' . $num] = 1;
                    }else{
                        unset($this->aData['price_checkbox' . $num]);
                    }
                }
            }
            unset($aPrices, $hasCatId);
        }
        unset($oEshop);

        $this->aData['catname'] = '';
        if(isset($this->aData['type']) && isset($this->aData['max_quantity'])){
            $aRemap['max_quantity'] = $this->aData['type'] . '_quantity';
        }

        // d::w('Returning from ' . __FILE__ . ' at ' . __LINE__);return;###

        foreach($this->aData as $field => $value){
            if(isset($aRemap[$field])){
                $field = $aRemap[$field];
            }
            // $this->oModule->Engine->cms->Vars[$field] = $value;
            $this->oModule->Engine->cms->VarsPost[$field] = $value;
        }
        if(isset($this->oModule->Engine->cms->VarsPost['cat'])){
            $this->oModule->Engine->cms->Vars['cat'] = $this->oModule->Engine->cms->VarsPost['cat'];
        }
        $this->oModule->Engine->cms->Vars['item_links'] =
            isset($this->oModule->Engine->cms->VarsPost['item_links'])
            ? $this->oModule->Engine->cms->VarsPost['item_links']
            : array();

        $id = empty($this->aData['id']) ? 0 : $this->aData['id'];
        // d::vd($this->oModule->Engine->oEshop->cms->VarsPost, 'oEshop VarsPost');d::w('<h style="color: red;">not saved</h1>');return;###
        $this->oModule->Engine->ProcessAction($action, $id);

        if('add' === $action){
            $this->id = $this->oModule->Engine->appliedId;
            $this->aData['id'] = $this->id;
        }

        if($isFront){
            // Restore CMS object
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

        unset($this->aData['item_links']);

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
     * Loads item properties.
     *
     * @return void
     */
    protected function loadProperties(){
        if($this->id && $this->oTable->getAttr($this->getModId(), 'loadProperties', FALSE)){
            // Load properties
            $this->aData['aProperties'] = array();
            $oProperties =
                AMI::getResourceModel($this->getModId() . '_props/table')
                ->getList()
                ->addColumn('*')
                ->addWhereDef('AND `id_item` = ' . $this->id)
                ->load();
            foreach($oProperties as $oProperty){
                $this->aData['aProperties'][$oProperty->id] = $oProperty;
            }
            $this->aOrigData['aData']['aProperties'] = $this->aData['aProperties'];
            unset($oProperties, $oProperty);
        }
    }
}

/**
 * AmiCatalog/Items configuration table list model.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      6.0.2
 */
class AmiCatalog_Items_TableList extends Hyper_AmiCatalog_TableList{
}
