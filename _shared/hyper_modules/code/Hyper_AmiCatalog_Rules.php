<?php
/**
 * AmiCatalog hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiCatalog
 * @version   $Id: Hyper_AmiCatalog_Rules.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiCatalog hypermodule rules.
 *
 * @package    Hyper_AmiCatalog
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiCatalog_Rules extends AMI_ModRules{
    /**
     * Module rules values locales
     *
     * @var array
     */
    protected $aLocale;

    /**
     * Categories tree
     *
     * @var array
     */
    protected $aCatTree;

    /**
     * Handler for ##modId##:ruleFrontPageSortColCB callback.
     *
     * @param  mixed  $callbackData  Data for callback
     * @param  mixed  &$optionsData  Options data
     * @param  string $callbackMode  Callback mode
     * @param  mixed  &$aResult      Result data
     * @param  array  &$aData        Data
     * @return bool
     */
    public function ruleFrontPageSortColCB($callbackData, &$optionsData, $callbackMode, &$aResult, array &$aData){
        // Moved from ModuleEshopItem::ruleFrontPageSortColCB()

        $aAllowedValues = array('date', 'name', 'price', 'sku', 'position', 'votes_rate');
        $passedValue = $callbackData['value'];
        switch($callbackMode){
            case 'getallvalues':
                $this->initLocale();

                $aResult = array();
                foreach($aAllowedValues as $value){
                    $aResult[] = array(
                        'name'     => $value,
                        'caption'  => $this->aLocale['front_page_sort_col_' . mb_strtoupper($value)],
                        'selected' => $passedValue != $value ? '' : 'selected'
                    );
                }
                break;
            case 'correctvalue':
                $aResult = in_array($passedValue, $aAllowedValues) ? $passedValue : 0;
                break;
            case 'getvalue':
                $aResult = $passedValue;
                break;
            case 'apply':
                $modId = $this->getModId();
                $previousValue = AMI::getOption($modId, 'front_page_sort_col');
                if($previousValue != $passedValue){
                    $table = AMI_ModDeclarator::getInstance()->getAttr($modId, 'db_table');

                    $db = AMI::getSingleton('db')->getCoreDB();

                    $db->setSafeSQLOptions('DESCRIBE');
                    $sql = 'SHOW INDEX FROM ' . $table;
                    $rs = $db->select($sql);
                    $db->clearSafeSQLOptions();
                    $keyFound = FALSE;
                    $indicesNotDisabled = TRUE;
                    while($record = $rs->nextRecord()){
                        if($record['Key_name'] == 'i_frn_sort_col'){
                            $keyFound = TRUE;
                            $rs->free();
                            break;
                        }
                    }
                    if($keyFound){
                        $db->setSafeSQLOptions('alter');
                        $sql = 'ALTER TABLE ' . $table . ' DISABLE KEYS';
                        @set_time_limit(29);
                        $db->execute($sql);
                        $indicesNotDisabled = FALSE;
                        $db->setSafeSQLOptions('alter');
                        $db->setSafeSQLOptions("drop");
                        $sql = 'ALTER TABLE ' . $table . ' DROP INDEX `i_frn_sort_col`';
                        @set_time_limit(29);
                        $db->execute($sql);
                    }
                    if($indicesNotDisabled){
                        $db->setSafeSQLOptions('alter');
                        $sql = 'ALTER TABLE ' . $table . ' DISABLE KEYS';
                        @set_time_limit(29);
                        $db->execute($sql);
                    }
                    $db->setSafeSQLOptions('alter');
                    $sql = 'ALTER TABLE ' . $table . ' ADD INDEX `i_frn_sort_col` (`id_category`, `' . $passedValue . '`);';
                    @set_time_limit(29);
                    $db->execute($sql);
                    $db->setSafeSQLOptions('alter');
                    $sql = 'ALTER TABLE ' . $table . ' ENABLE KEYS';
                    @set_time_limit(29);
                    $db->execute($sql);
                    $db->clearSafeSQLOptions();
                }
                break;
        }
        return TRUE;
    }

    /**
     * Handler for ##modId##:getOptionsFrontAvailableSortCB callback.
     *
     * @param  mixed  $callbackData  Data for callback
     * @param  mixed  &$optionsData  Options data
     * @param  string $callbackMode  Callback mode
     * @param  mixed  &$aResult      Result data
     * @param  array  &$aData        Data
     * @return bool
     * @see    http://jira.cmspanel.net/browse/CMS-10486
     */
    public function getOptionsFrontAvailableSortCB($callbackData, &$optionsData, $callbackMode, &$aResult, array &$aData){
        // Moved from ModuleEshopItem::getOptionsFrontAvailableSortCB()

        $modId = $this->getModId();
        $aColumns = AMI::getProperty($modId, 'sort_fields');
        if(
            !in_array('ext_rating', AMI::getOption($modId, 'extensions')) ||
            !AMI::isModInstalled('ext_rating')
        ){
            $aColumns = array_diff($aColumns, array('votes_count', 'votes_rate'));
        }
        switch($callbackMode){
            case 'getallvalues':
                $this->initLocale();
                $aResult = array();
                foreach($aColumns as $col){
                    if(isset($this->aLocale['front_sort_' . $col]) && $this->aLocale['front_sort_' . $col] != ''){
                        $aResult[] = array(
                            'name'     => $col,
                            'caption'  => $this->aLocale['front_sort_' . $col],
                            'selected' => is_array($callbackData['value']) && in_array($col, $callbackData['value']) ? 'selected' : ''
                        );
                    }
                }
                break;
            case 'correctvalue':
                $aResult = is_array($callbackData['value']) ? array_intersect($callbackData['value'], $aColumns) : array();
                break;
            case 'getvalue':
                $aResult = $callbackData['value'];
                break;
            case 'apply':
                $aValue = AMI::getOption($modId, 'sort_pages_setup');
                $aKeys = array_keys($aValue);
                if(!sizeof($aKeys)){
                    $aKeys[] = 'body_items;body_search;body_urgent_items';
                }
                foreach($aKeys as $key){
                    $aValue[$key] = $callbackData['value'];
                }
                AMI::setOption($modId, 'sort_pages_setup', $aValue);
                break;
        }
        $aData['allow_empty'] = 1;
        return TRUE;
    }

    /**
     * Handler for ##modId##:getOptionsExtrafieldPriceCB callback.
     *
     * @param  mixed  $callbackData  Data for callback
     * @param  mixed  &$optionsData  Options data
     * @param  string $callbackMode  Callback mode
     * @param  mixed  &$aResult      Result data
     * @param  array  &$aData        Data
     * @return bool
     */
    function getOptionsExtrafieldPriceCB($callbackData, &$optionsData, $callbackMode, &$aResult, array &$aData){
        // Moved from ModuleEshopItem::getOptionsExtrafieldPriceCB()

        static $isDBUpdated = null;

        $aPrices = array(
            'price1', 'price2', 'price3', 'price4', 'price5', 'price6', 'price7', 'price8',
            'price9', 'price10', 'price11', 'price12', 'price13', 'price14', 'price15', 'price16'
        );
        switch($callbackMode){
            case 'getallvalues':
                $this->initLocale();
                $aResult = array();
                for($i = 1; $i <= sizeof($aPrices); $i++){
                    $aResult[] = array(
                        'name'     => 'price' . $i,
                        'caption'  => $this->aLocale['price' . $i],
                        'selected' => (is_array($callbackData['value']) && in_array($i, $callbackData['value'])) ? 'selected' : ''
                    );
                }
                break;
            case 'correctvalue':
                if(is_array($callbackData['value'])){
                    $aResult = array_intersect($callbackData['value'], $aPrices);

                    if(is_null($isDBUpdated)){
                        $db = AMI::getSingleton('db')->getCoreDB();

                        $oDeclarator = AMI_ModDeclarator::getInstance();
                        $modId = $this->getModId();
                        $table = $oDeclarator->getAttr($modId, 'db_table');
                        $catTable = $oDeclarator->getAttr($oDeclarator->getSection($modId) . '_cat', 'db_table');

                        $db->setSafeSQLOptions('describe');
                        $sql = 'describe ' . $table;
                        $db->query($sql);
                        $aPricesDB = array();
                        while($db->next_record()){
                            if(preg_match('/^price\d+$/i', $db->Record['Field'], $matches)){
                                $aPricesDB[] = $matches[0];
                            }
                        }
                        foreach($aPrices as $priceInd => $priceName){
                            if(in_array($priceName, $aResult) && !in_array($priceName, $aPricesDB)){
                                // alter items table
                                $db->setSafeSQLOptions("alter");
                                $sql =
                                    "ALTER TABLE " . $table . " " .
                                    "ADD `price" . ($priceInd + 1) . "` double default NULL";
                                @set_time_limit(29);
                                $db->query($sql);

                                // alter cats table
                                $db->setSafeSQLOptions("alter");
                                $sql =
                                    "ALTER TABLE " . $catTable . " " .
                                    "ADD `price" . ($priceInd + 1) . "` varchar(255) NOT NULL default ''";
                                @set_time_limit(29);
                                $db->query($sql);
                                $db->setSafeSQLOptions("alter");
                                $sql =
                                    "ALTER TABLE " . $catTable . " " .
                                    "ADD `price_caption" . ($priceInd + 1) . "` varchar(255) NOT NULL default ''";
                                @set_time_limit(29);
                                $db->query($sql);
                            }elseif(!in_array($priceName, $aResult) && in_array($priceName, $aPricesDB) && (($priceInd+1) > 5)){
                                $db->setSafeSQLOptions("alter");
                                $db->setSafeSQLOptions("drop");
                                $sql =
                                    "ALTER TABLE " . $table . " " .
                                    "DROP `price" . ($priceInd + 1) . "`";
                                @set_time_limit(29);
                                $db->query($sql);
                                $db->setSafeSQLOptions("alter");
                                $db->setSafeSQLOptions("drop");
                                $sql =
                                    "ALTER TABLE " . $catTable . " " .
                                    "DROP `price" . ($priceInd + 1) . "`";
                                @set_time_limit(29);
                                $db->query($sql);
                                $db->setSafeSQLOptions("alter");
                                $db->setSafeSQLOptions("drop");
                                $sql =
                                    "ALTER TABLE " . $catTable . " " .
                                    "DROP `price_caption" . ($priceInd + 1) . "`";
                                @set_time_limit(29);
                                $db->query($sql);
                            }
                        }

                        $isDBUpdated = TRUE;
                    }
                }else{
                    $aResult = array();
                }
                break;
            case 'getvalue':
                $aResult = $callbackData['value'];
                break;
        }
        $aData['allow_empty'] = 1;
        return TRUE;
    }

    /**
     * Handler for ##modId##:getOptionsModCatsCB callback.
     *
     * @param  mixed  $callbackData  Data for callback
     * @param  mixed  &$optionsData  Options data
     * @param  string $callbackMode  Callback mode
     * @param  mixed  &$aResult      Result data
     * @param  array  &$aData        Data
     * @return bool
     */
    function getOptionsModCatsCB($callbackData, &$optionsData, $callbackMode, &$aResult, array &$aData){
        // Moved from ModuleEshopItem::getOptionsModCatsCB()

        switch($callbackMode){
            case 'getallvalues':
                $aResult = array();
                if(!is_array($this->aCatTree)){
                    global $cms;

                    $oDeclarator = AMI_ModDeclarator::getInstance();
                    $modId = $this->getModId();


                    require_once $GLOBALS['CLASSES_PATH'] . 'EshopAdmin.php';
                    $aWords = array();
                    $oEshop = new EshopAdmin($cms, $aWords);
                    // init Eshop
                    $oEshop->init($this->modId);

                    $section = $oDeclarator->getSection($modId);
                    /*
                    $catTable = $oDeclarator->getAttr($section . '_cat', 'db_table');
                    */
                    $oTpl = AMI::getSingleton('env/template_sys');
                    $aLocale = $oTpl->parseLocale('templates/lang/options/' . $section . '_item_callbacks.lng');

                    /*
                    $tree = new TreeModel($cms);
                    $tree->setMainTableAlias('c');

                    // field sets
                    $tree->addFieldsSet('name', 'c.name');

                    // filter
                    $tree->addFilter('lang', "c.lang='" . $cms->Gui->lang_data . "'");
                    $tree->sqlAllClausesDisable();
                    $tree->setDbTableParams($catTable . ' c', 'id_parent');
                    $tree->setRootNodeId($this->rootCatId, $this->rootParentCatId);
                    */

                    $oEshop->tree->sqlOrderEnable();

                    // hack
                    $savedSort = $cms->Pager->SortCol;
                    $cms->Pager->SortCol = 'name';

                    $oEshop->tree->setCallbackObject($this);

                    $oEshop->tree->setCallbackMethod('createChildsArrayFlt', 'treeGetChildFromDBRecord');
                    $oEshop->tree->createChildsArrayFlt('name');

                    $oEshop->tree->setCallbackMethod('buildTreeOnChildsArray', 'treeBuildRecord');

                    $this->aCatTree = array();
                    $this->aCatTree[] = array(
                        'name'     => 0,
                        'caption'  => $aLocale['current_eshop_category'],
                        'selected' => (0 == $callbackData['value']) ? 'selected' : ''
                    );
                    $this->aCatTree[] = array(
                        'name'    => 0,
                        'caption' => $aLocale['fixed_eshop_category'],
                        'group'   => 'start'
                    );

                    $aResult = array('catList' => &$this->aCatTree, 'selectedCatId' => $callbackData['value']);

                    $oEshop->tree->initResult($aResult);
                    $oEshop->tree->buildTreeOnChildsarray();
                    $oEshop->tree->sqlOrderDisable();

                    // hack
                    $cms->Pager->SortCol = $savedSort;

                    $this->aCatTree[] = array(
                        'name'    => 0,
                        'caption' => 'dfhghgf2',
                        'group'   => 'end'
                    );
                }
                $aResult = $this->aCatTree;
                break;
            case 'correctvalue':
                $aIds = array();
                $aResult = 0;
                foreach($this->aCatTree as $aData){
                    if($callbackData['value'] == $aData['name']){
                        $aResult = $callbackData['value'];
                        break;
                    }
                }
                break;
            case 'getvalue':
                $aResult = $callbackData['value'];
                break;
        }
        return TRUE;
    }

    /**
     * Callback for building tree.
     *
     * @param  array &$aChildren  Children array
     * @param  array $aRecord     Db record
     * @param  array $aExtInfo    Extra info
     * @return void
     */
    public function treeGetChildFromDBRecord(array &$aChildren, array $aRecord, array $aExtInfo){
        $aChildren[] = array('id' => $aRecord['id'], 'name' => $aRecord['name']);
    }

    /**
     * Callback for building tree.
     *
     * @param  array &$aResult  Result array
     * @param  array $aChild    Child array
     * @param  array $aExtInfo  Extra info array
     * @return bool
     * @see    Hyper_AmiCatalog_Rules::getOptionsModCatsCB()
     */
    public function treeBuildRecord(array &$aResult, array $aChild, array $aExtInfo){
        $indent = str_repeat('&middot;&nbsp;', $aExtInfo['level']);
        $aResult['catList'][] = array(
            'name'     => $aChild['id'],
            'caption'  => $indent . AMI_Lib_String::truncate($aChild['name'], 50, TRUE),
            'selected' => $aResult['selectedCatId'] != $aChild['id'] ? '' : 'selected'
        );
        return TRUE;
    }

    /**
     * Initializes module rules values locale data.
     *
     * @return void
     */
    protected function initLocale(){
        if(is_null($this->aLocale)){
            $oTpl = AMI::getSingleton('env/template_sys');
            $this->aLocale = $oTpl->parseLocale('templates/lang/options/' . $this->getModId() . '_rules_values.lng');
        }
    }
}
