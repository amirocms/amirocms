<?php
/**
 * AmiExt/CustomFields extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiExt_CustomFields
 * @version   $Id: AmiExt_CustomFields_Service.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/CustomFields extension configuration service class.
 *
 * @package    Config_AmiExt_CustomFields
 * @subpackage Controller
 * @resource   ext_custom_fields/service <code>AMI::getResource('ext_custom_fields/service')</code>
 * @amidev     Temporary
 */
class AmiExt_CustomFields_Service{
    /**
     * Custom fields data
     *
     * @var array
     */
    public $aCustomFields = array();

    /**
     * Instance
     *
     * @var ExtCustomFields_Service
     */
    private static $oInstance;

    /**
     * Applicable module list, also contains modules captions after initialization
     *
     * @var array
     */
    private $aAllowedModules = array(
        'members'         => ''
    );

    /**
     * Applicable installed modules list
     *
     * @var array
     */
    private $aIntsalledModules = array();

    /**
     * Internal flag
     *
     * @var bool
     * @see CMS_API_ModulesCustomFields::checkSystemDatasets()
     */
    private $areSysDatasetsChecked = false;

    /**
     * Returns AmiExt_CustomFields_Service instance.
     *
     * @return AmiExt_CustomFields_Service
     */
    public static function getInstance(){
        if(self::$oInstance == null){
            self::$oInstance = new self;
        }
        return self::$oInstance;
    }

    /**
     * Destroes instance.
     *
     * @return void
     * @todo   Use when all business logic processed or detect usage necessity
     */
    public static function destroyInstance(){
        self::$oInstance == null;
    }

    /**
     * Returns allowed modules list for Custom Fields extension.
     *
     * @return array  Array ('news' => 'localized news module cpation', ...)
     */
    public function getAllowedModules(){
        return $this->aAllowedModules;
    }

    /**
     * Returns module caption.
     *
     * @param  string $modId  Module id
     * @return string|null
     */
    public function getModCaption($modId){
        return isset($this->aAllowedModules[$modId]) ? $this->aAllowedModules[$modId] : null;
    }

    /**
     * Returns allowed and installed modules list for Custom Fields extension.
     *
     * @return array  Array ('news', 'blog', ...)
     */
    public function getInstalledModules(){
        return $this->aIntsalledModules;
    }

    /**
     * Check system datasets and create missing default datasets for modules
     *
     * @return void
     */
/*
    public function checkSystemDatasets(){
        if($this->areSysDatasetsChecked){
            return;
        }
        $aModules = $this->aIntsalledModules;
        $sql =
            "SELECT DISTINCT `module_name` " .
            "FROM `cms_modules_datasets` " .
            "WHERE `is_sys` = 1 AND `lang` = '" . $this->_cms->lang_data . "'";
        $rs = $this->_db->select($sql);
        while($record = $rs->nextRecord()){
            if(($index = array_search($record['module_name'], $aModules)) !== false){
                unset($aModules[$index]);
            }
        }
        if(sizeof($aModules)){
            // modules having no default (system) dadasets detected
            // 'Default set', 'Набор по-умолчанию'
            $tmpLang = $this->_cms->Gui->getLang();
            $this->_cms->Gui->lang = $this->_cms->lang_data;
            $aDictionary = $this->_cms->Gui->ParseLangFile('templates/lang/modules_datasets_defaults.lng');
            if(empty($aDictionary['default_dataset_name'])){
                $this->_cms->Gui->lang = 'en';
                $aDictionary = $this->_cms->Gui->ParseLangFile('templates/lang/modules_datasets_defaults.lng');
            }
            $this->_cms->Gui->lang = $tmpLang;
            foreach($aModules as $modId){
                $aSQL = array(
                    'module_name'   => $modId,
                    'postfix'       => '_system',
                    'name'          => $aDictionary['default_dataset_name'],
                    'lang'          => $this->_cms->lang_data,
                    'modified_date' => '=|NOW()',
                    'is_sys'        => 1
                );
                $usage = $this->getModuleUsageMode($modId);
                if($usage == 'simple'){
                    $aSQL['used_simple'] = 1;
                }
                $sql = $this->_db->genInsertSQL('cms_modules_datasets', $aSQL);
                $this->_db->execute($sql);
                if(empty($aSQL['used_simple'])){
                    $datasetId = $this->_db->lastInsertId();
                    // we need last insert dataset id to fill appropriate data
                    if($usage == 'categories'){
                        // categories
                        //if($this->_cms->Core->GetModule($modId . '_cat')){
                            $table = $this->_cms->Core->GetModule($modId . '_cat')->GetTableName();
                          }
                          //}else{
                            // jobs_resume
                            //$table = $this->_cms->Core->GetModule(preg_replace('/_.*$/', '', $modId) . '_cat')->GetTableName();
..                        }
                        $sql =
                            "SELECT `id` " .
                            "FROM " . $table . " " .
                            "WHERE `lang` = '" . $this->_cms->lang_data . "'";
                        $rs = $this->_db->select($sql);
                        if($rs->numRows()){
                            $ids = '';
                            while($record = $rs->nextRecord()){
                                $ids .= $record['id'] . ',';
                            }
                            $ids = mb_substr($ids, 0, -1);
                            $sql = $this->_db->genUpdateSQL($table, array('id_dataset' => $datasetId), "WHERE `id` IN (" . $ids . ")");
                            $this->_db->execute($sql);
                            $sql = $this->_db->genUpdateSQL('cms_modules_datasets', array(
                                'used_categories' => ';' . str_replace(',', ';', $ids) . ';'
                            ), "WHERE `id` = " . $datasetId);
                            $this->_db->execute($sql);
                        }
                    }else{
                        // pages
                        $sql =
                            "SELECT `id` FROM " .
                            "`cms_pages` WHERE `module_name` = '" . preg_replace('/_cat$/', '', $modId) . "' AND `lang` = '" . $this->_cms->lang_data . "'";
                        $rs = $this->_db->select($sql);
                        if($rs->numRows()){
                            $ids = '';
                            while($record = $rs->nextRecord()){
                                $ids .= $record['id'] . ',';
                            }
                            $ids = mb_substr($ids, 0, -1);
                            $sql = $this->_db->genUpdateSQL('cms_pages', array('id_dataset' => $datasetId), "WHERE `id` IN (" . $ids . ")");
                            $this->_db->execute($sql);
                            $sql = $this->_db->genUpdateSQL('cms_modules_datasets', array(
                                'used_pages' => ';' . str_replace(',', ';', $ids) . ';'
                            ), "WHERE `id` = " . $datasetId);
                            $this->_db->execute($sql);
                        }
                    }
                }
            }
        }
        $this->areSysDatasetsChecked = true;
    }
*/

    /**
     * Returns system dataset id by module id.
     *
     * @param string $modId  Module id
     * @return int
     */
    public function getSysDatasetId($modId){
        static $aModsXSysDatasets = array();

        if(!isset($aModsXSysDatasets[$modId])){
            AMI_Event::disableHandler('on_table_get_item_post');
            $oItem =
                AMI::getResourceModel('modules_datasets/table')
                    ->getItem()
                    ->addFields(array('id'))
                    ->addSearchCondition(
                        array(
                            'is_sys'      => 1,
                            'module_name' => $modId,
                            'lang'        => AMI_Registry::get('lang_data')
                        )
                    )
                    ->load();
            $aModsXSysDatasets[$modId] = (int)$oItem->getId();
            AMI_Event::enableHandler('on_table_get_item_post');
        }
        return $aModsXSysDatasets[$modId];
    }

    /**
     * Returns usage mode by module id.
     *
     * @param  string $modId  Module id
     * @return string         'simple'|'categories'|'pages'
     */
    public function getModuleUsageMode($modId){
        static $aModXModes = array();

        if(!isset($aModXModes[$modId])){
            if(AMI::issetAndTrueOption($modId, 'use_categories')){
                $aModXModes[$modId] = 'categories';
            }elseif(
                AMI::issetAndTrueOption('core', 'multi_page_allowed') &&
                AMI::issetAndTrueOption($this->getParentForCatModule($modId), 'multi_page')
            ){
                $aModXModes[$modId] = 'pages';
            }else{
                $aModXModes[$modId] = 'simple';
            }
        }
        return $aModXModes[$modId];
    }

    /**
     * Returns parent module name for category module id.
     *
     * @param  string $modId  Module id
     * @return string
     */
    public function getParentForCatModule($modId){
        if(mb_substr($modId, -4) == '_cat'){
            $res = mb_substr($modId, 0, -4);###
        }else{
            $res = $modId;
        }
        return $res;
    }

    /**
     * Loads custom fields data by its ids.
     *
     * @param array $aIds       Ids
     * @param array $aCaptionStruct  Captions
     * @return void
     */
    public function loadFieldsByIds(array $aIds, array $aCaptionStruct = array()){
        $fieldIds = array_diff(array_filter($aIds, 'is_numeric'), array_keys($this->aCustomFields));
        if(sizeof($fieldIds)){
            $isAdmin = $this->_cms->Side == 'admin';
            $sql =
                "SELECT `id`, `prefix`, `postfix`, `system_name`, `ftype`, `default_caption` `caption`" .
                    ($isAdmin
                        ? ", `public`, `admin_form`, `admin_ui`, `admin_filter`"
                        : ', `show_body_type`, `isnot_all`, `description`'
                    ) .
                " FROM `cms_modules_custom_fields` " .
                "WHERE `id` IN (" . implode(',', $fieldIds) . ")";
            if(!$isAdmin){
                $sql .= ' AND `public` = 1';
            }

            $rs = $this->_db->select($sql);
            while($record = $rs->nextRecord()){
                $id = (int)$record['id'];
                foreach(array('prefix', 'postfix', 'caption') as $complexField){
                    $record[$complexField] = unserialize($record[$complexField]);
                    if(is_array($record[$complexField])){
                        if(isset($record[$complexField][$this->_cms->lang_data])){
                            $record[$complexField] = $record[$complexField][$this->_cms->lang_data];
                        }else{
                            $record[$complexField] = '';
                        }
                    }else{
                        $record[$complexField] = '';
                    }
                }
                $record['caption'] = isset($aCaptionStruct[$id]) ? $aCaptionStruct[$id] : $record['caption'];
                if(!mb_strlen($record['caption'])){
                    $record['caption'] = 'unknown';
                }
                if($isAdmin){
/*
                    $aDefaultParams = unserialize($record['default_params']);
                    if(is_array($aDefaultParams)){
                        $record += $aDefaultParams;
                    }
                    unset($record['default_params']);
*/
                }elseif($record['description']){
                    // front
                    $description = unserialize($record['description']);
                    $record['description'] = is_array($description) && isset($description[$this->_cms->lang_data]) && mb_strlen(trim(strip_tags($description[$this->_cms->lang_data])));
                }
                $this->aCustomFields[$id] = $record;
            }
        }
    }

    /**
     * Converts string separated by ';' (field lists are stored by this way in datasets db table) to array.
     *
     * @param  string &$ids  Comma separated ids
     * @return bool  True if sizeof(result) > 0
     */
    public function string2Array(&$ids){
        $ids = trim($ids, ';');
        $ids = $ids ? explode(';', $ids) : array();
        return sizeof($ids) > 0;
    }

    /**
     * Initialize modules captions from dictionary.
     *
     * @return void
     */
    protected function initModulesCaptions(){
        $this->aAllowedModules = AMI_Service_Adm::getModulesCaptions(array_keys($this->aAllowedModules));
    }

    /**
     * Singleton counstructor.
     */
    private function __construct(){
        $this->aAllowedModules += AMI_Ext::getSupportedModules('ext_custom_fields');
        if(is_array($this->aAllowedModules) && sizeof($this->aAllowedModules)){
            foreach(array_keys($this->aAllowedModules) as $modId){
                if(AMI::isModInstalled($modId)){
                    $this->aIntsalledModules[] = $modId;
                }
            }
        }

        if(AMI_Registry::get('side') == 'adm'){
            $this->initModulesCaptions();
        }
    }

    /**
     * Singleton cloning.
     */
    private function __clone(){
    }
}
