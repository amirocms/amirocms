<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModTable.php 50134 2014-04-21 10:35:13Z Leontiev Anton $
 * @since     5.10.0
 */

/**
 * Module component table model interface.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @see        AmiSample_Table
 * @since      5.10.0
 */
interface AMI_iModTable{
    /**
     * Returns table fields structure.
     *
     * @return array
     */
    public function getTableFieldsData();

    /**
     * Returns item model object.
     *
     * @param  string $type  Postfix for 'on_query_add_joined_columns' event
     * @return AMI_ModTableItem
     */
    public function getItem($type = '');

    /**
     * Returns list model object.
     *
     * @param  string $type  Postfix for 'on_query_add_joined_columns' event
     * @return AMI_ModTableList
     */
    public function getList($type = '');

    /**
     * Returns item model object and load data for key field param.
     *
     * @param  int $id  Primary key value
     * @param  array $aFields  Fields to load (since 5.12.8)
     * @return AMI_ModTableItem
     * @see    AMI_ModTableItem::addFields() for $aFields parameter explanation
     */
    public function find($id, array $aFields = array('*'));

    /**
     * Returns item model object and load data for non-primary key field.
     *
     * @param  array $aSearchCondition  Filter array key => value
     * @param  array $aFields           Fields to load (since 5.12.8)
     * @return AMI_ModTableItem
     */
    public function findByFields(array $aSearchCondition, array $aFields = array('*'));

    /**
     * Checks if model has a field.
     *
     * @param  string $name  Field name in table
     * @param  bool $bAppendEventFields  Fire 'on_get_available_fields' event to append extension fields
     * @return bool
     */
    public function hasField($name, $bAppendEventFields = false);

    /**
     * Returns array of available fields.
     *
     * @param  bool $bAppendEventFields  Fire 'on_get_available_fields' event to append extension fields
     * @return array
     */
    public function getAvailableFields($bAppendEventFields = true);

    /**
     * Set module id.
     *
     * @param mixed $id  Module Id.
     * @return void
     */
    public function setModId($id);
}

/**
 * Module table model.
 *
 * Module table models have high abstraction level,
 * modules ids are obtained from resource ids or class names by default.<br /><br />
 * Since 6.0.2 you can pass following attributes to model constructor:
 * - extModeOnConstruct:
 * - - 'common' - initialize extensions using current module option),
 * - - 'none' (by default),
 * - - 'all' - initialize all supported extensions;
 * - extModeOnDestruct:
 * - - 'none' - do not cleanup extension handlers (by default);
 * - - 'cleanup' - cleanup all extension handlers.
 * - doRemapListItems (since 6.0.4):
 * - - FALSE (by default) - fast items loading, no fields remapping, no using fields callbacks
 * - - TRUE - load fields with remapping and using fields callbacks
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @example    models/models.sql  Models tables SQL
 * @example    models/models.php  Models usage example
 * @example    models/models.tpl  Models usage example template
 * @example    models/MyModule1_TableModels.php  Models creation example 1
 * @example    models/MyModule2_TableModels.php  Models creation example 2
 * @see        AMI_ArrayIterator
 * @since      5.10.0
 */
abstract class AMI_ModTable implements AMI_iModTable{
    /**
     * Fields mapping and DESCRIBE TABLE optimization
     *
     * @amidev
     */
    const FIELD_DOESNT_EXIST = '';

    /**
     * Flag specifying extensions are initialized already
     *
     * @var array
     * @see self::initExtensions()
     * @see self::cleanupExtensions()
     */
    protected static $extInitialized = array();

    /**
     * Database table name, must be declared in child classes
     *
     * @var string
     */
    protected $tableName = '';

    /**
     * Associated table names
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aAssociatedTables = array();

    /**
     * Attributes
     *
     * @var   array
     * @since 6.0.2
     */
    protected $aAttributes = array();

    /**
     * Remapping for field names, pairs: fieldName => remappedFieldName
     *
     * @var array
     * @amidev
     */
    protected $aFieldNamesRemap = array();

    /**
     * Table fields and field types as result of DESCRIBE
     *
     * @var array
     */
    private static $aTableFields = array();

    /**
     * Result of "SHOW CREATE TABLE" query
     *
     * @var string
     */
    private static $aTableDescription = array();

    /**
     * Prefix rules for field aliases in select statement. Array of tablePrefix => fieldPrefix
     *
     * @var array
     */
    private $aPrefixRules = array();

    /**
     * Class name with dependent table resource name.
     *
     * Each array item:
     * <code>
     * 'alias' => array(
     *     'res'          => '...',
     *     'condition'    => '...',
     *     'field_prefix' => '...',
     *     'join_type'    => '...',
     *     'active'       => '...'
     * )
     * </code>
     * where:
     * - alias is joined table alias (e.g. 'c');
     * - res is joined table resource name (e.g. 'news_cat/table'), required;
     * - condition is join condition (e.g. 'i.id_category=c.id'), required;
     * - field_prefix is prefix for fields in selection
     *   (e.g. 'cat', fields shown as cat_name, cat_id, cat_description etc);
     *   if omitted alias will be prefix (with ending "_");
     * - active is status of dependence activity;
     *   active (true) if joining is required, not active (false) is joining is available but not required at the momnet.
     *
     * @var array
     */
    private $dependentRes = array();

    /**
     * Module id
     *
     * @var string
     * @see AMI_ModTable::getModId()
     */
    private $modId = '';

    /**
     * System fields
     *
     * @var  array
     * @todo Advert extension remapping
     */
    private $aSysFields = array(
        'id_cat', 'id_category',
        'link_alias', 'sys_rights_r', 'sys_rights_w', 'sys_rights_d',
        // 'rate_opt', 'votes_rate', 'votes_count', 'votes_weight', // Ratings extension fields
        'votes_weight', // Ratings extension fields
        'adv_place', 'shown_details', 'shown_items', // Advert extension fields
        // 'tags' // Tags extension
        // Image extension, these fields are available if ext_iamge is turned on
        'ext_img', 'ext_img_small', 'ext_img_popup', 'ext_picture', 'ext_popup_picture', 'ext_small_picture',
        'ext_dsc_disable'
    );

    /**
     * System validators
     *
     * @var array
     */
    private $aSysValidators = array(
        'int', 'float', 'double', 'datetime', 'date', 'time', 'char', 'varchar',
        'tinytext', 'text', 'mediumtext', // 'longtext',
        'tinyblob', 'blob', 'mediumblob' // 'longblob',
    );

    /**
     * Current validators
     *
     * @var array
     */
    private $aValidators = null;

    /**
     * User validators
     *
     * @var array
     */
    private $aUserValidators = array();

    /**
     * Navigation fields
     *
     * @var array
     */
    private $aNavFields = array(
        'sublink', 'id_page', 'lang'
    );

    /**
     * HTML meta fields (sm_data)
     *
     * @var array
     */
    private $aHTMLFields = array(
        'title'        => 'html_title',
        'keywords'     => 'html_keywords',
        'description'  => 'html_description',
        'is_kw_manual' => 'html_is_kw_manual',
        'og_image'     => 'og_image'
    );

    /**
     * Constructor.
     *
     * Initializing table data (describe, field rules).
     *
     * @param array $aAttributes  Attributes of table model (since 6.0.2)
     */
    public function __construct(array $aAttributes = array()){
        // d::vd('AMI_ModTable::__construct(' . $this->getModId() . ')');###
        $this->initAttributes($aAttributes);

        if(!empty($this->tableName) && AMI::isResource('response')){
            /**
             * @var AMI_Response
             */
            $oResponse = AMI::getSingleton('response');
            $oResponse->addPageModule($this->getModId());
        }

        foreach($this->dependentRes as $alias => $classData){
            $this->aPrefixRules[$alias] = isset($classData['field_prefix']) ? $classData['field_prefix'] : ($alias . '_');
        }
    }

    /**
     * Destructor.
     *
     * @see   self::cleanupExtensions()
     * @since 6.0.2
     */
    public function __destruct(){
        // d::vd('AMI_ModTable::__destruct(' . $this->getModId() . ')');###
        // d::trace();###
        $this->cleanupExtensions();
    }

    /**
     * Sets attribute.
     *
     * @param  string $name   Attribute name
     * @param  mixed  $value  Attribute value
     * @return AMI_ModTable
     * @since  6.0.2
     */
    public function setAttr($name, $value){
        $this->aAttributes[$name] = $value;

        return $this;
    }

    /**
     * Returns TRUE if attribute is present.
     *
     * @param  string $name  Attribute name
     * @return bool
     * @since  6.0.2
     */
    public function issetAttr($name){
        return array_key_exists($name, $this->aAttributes);
    }

    /**
     * Returns attribute.
     *
     * @param  string $name          Attribute name
     * @param  mixed  $defaultValue  Default value to return
     * @return mixed
     * @since  6.0.2
     */
    public function getAttr($name, $defaultValue = null){
        // if(!is_array($this->aAttributes))d::vd(get_class($this));###
        return
            array_key_exists($name, $this->aAttributes)
                ? $this->aAttributes[$name]
                : $defaultValue;
    }

    /**
     * Drops attribute.
     *
     * @param  string $name  Attribute name
     * @return AMI_ModTable
     * @since  6.0.2
     */
    public function dropAttr($name){
        unset($this->aAttributes[$name]);
        return $this;
    }

    /**
     * Returns table fields structure.
     *
     * @return array
     * @amidev
     */
    public function getTableFieldsData(){
        if(!empty($this->tableName) && empty(self::$aTableFields[$this->tableName])){
            self::$aTableFields[$this->tableName] = array();
            $this->storeTableBlockName();

            $sql = 'DESCRIBE ' . $this->tableName;
            /**
             * @var AMI_iDB
             */
            $oDB = AMI::getSingleton('db');
            $oRS = $oDB->select($sql, MYSQL_ASSOC, DBC_RAW_QUERY);
            foreach($oRS as $oItem){
                $fieldType = $oItem['Type'];
                $aData = array();
                if(mb_strpos($fieldType, 'enum') === 0){
                    $aData['type'] = 'enum';
                    $aData['variants'] = explode(',', mb_substr($fieldType, 5, mb_strlen($fieldType) - 6));
                    foreach($aData['variants'] as &$value){
                        $value = trim($value, "'");
                    }
                }elseif(preg_match('/(\w+)\((\d+)[,\d]*?\)/', $fieldType, $aMatches)){
                    $aData['type'] = $aMatches[1];
                    $aData['length'] = intval($aMatches[2]);
                }else{
                    $aData['type'] = $fieldType;
                    $aData['length'] = ($fieldType == 'varchar' ? 255 : 0);
                }
                self::$aTableFields[$this->tableName][$oItem['Field']] = $aData;
            }
        }
        return self::$aTableFields[$this->tableName];
    }

    /**
     * Returns MySQL types of model fields.
     *
     * @param mixed $type  Type of fields (null = all, string, or array of types)
     * @return array
     * @amidev
     */
    public function getFieldsByTypes($type = null){
        $aFields = array();
        $aFieldsData = $this->getTableFieldsData();
        $aFieldsList = $this->getAvailableFields();
        foreach($aFieldsList as $modelField){
            if(!is_array($modelField)){ // do not catch dependent model's fields
                $tableField = isset($this->aFieldNamesRemap[$modelField]) ? $this->aFieldNamesRemap[$modelField] : $modelField;
                if(isset($aFieldsData[$tableField])){
                    if(is_null($type) || ($type == $aFieldsData[$tableField]['type']) || (is_array($type) && in_array($aFieldsData[$tableField]['type'], $type))){
                        $aFields[$modelField] = $aFieldsData[$tableField]['type'];
                    }
                }
            }
        }
        return $aFields;
    }

    /**
     * Returns item model object.
     *
     * @param  string $type  Postfix for 'on_query_add_joined_columns' event
     * @return AMI_ModTableItem
     */
    public function getItem($type = ''){
        $this->initExtensions();
        $aEvent = array(
            // @var int
            'modId'  => $this->getModId(),
            // @var AMI_ModTable
            'oTable' => $this
        );

        /**
         * Called when receive array of validators, allow add own validators.
         *
         * @event      on_table_get_item $modId
         * @eventparam string       modId   Module id
         * @eventparam AMI_ModTable oTable  Table model
         */
        AMI_Event::fire('on_table_get_item', $aEvent, $this->getModId());

        $oItem = AMI::getResourceModel($this->getModId() . '/table/model/item', array($this, $this->getQueryBase($type)));
        $aEvent['oTableItem'] = $oItem; // @deprecated since 5.14.0
        $aEvent['oItem'] = $oItem;

        /**
         * Called when receive array of validators, allow add own validators.
         *
         * @event      on_table_get_item_post $modId
         * @eventparam string           modId   Module id
         * @eventparam AMI_ModTable     oTable  Table model
         * @eventparam AMI_ModTableItem oItem   Table item model
         */
        AMI_Event::fire('on_table_get_item_post', $aEvent, $this->getModId());

        return $oItem;
    }

    /**
     * Returns list model object.
     *
     * @param  string $type  Postfix for 'on_query_add_joined_columns' event
     * @return AMI_ModTableList
     */
    public function getList($type = ''){
        $this->initExtensions();
        $aEvent = array(
            // @var int
            'modId'  => $this->getModId(),
            // @var AMI_ModTable
            'oTable' => $this
        );

        /**
         * Allows to handle table list model creation.
         *
         * @event      on_table_get_list $modId
         * @eventparam string       modId   Module id
         * @eventparam AMI_ModTable oTable  Table model
         */
        AMI_Event::fire('on_table_get_list', $aEvent, $this->getModId());
        $oList = AMI::getResourceModel($this->getModId() . '/table/model/list', array($this, $this->getQueryBase($type)));
        $oList->setModId($this->getModId());
        return $oList;
    }

    /**
     * Returns database table name.
     *
     * @return string
     */
    public function getTableName(){
        return $this->tableName;
    }

    /**
     * Returns associated table names.
     *
     * @return array
     * @amidev Temporary
     */
    public function getAssociatedTableNames(){
        return $this->aAssociatedTables;
    }

    /**
     * Sets new table name for a model.
     *
     * @param string $tableName  New table name
     * @return void
     * @amidev Temporary
     */
    public function setTableName($tableName){
        $this->tableName = $tableName;
    }

    /**
     * Returns item model object and load data for key field param.
     *
     * @param  int $id  Primary key value
     * @param  array $aFields  Fiedls to load (since 5.12.8)
     * @return AMI_ModTableItem
     * @see    AMI_ModTableItem::addFields() for $aFields parameter explanation
     */
    public function find($id, array $aFields = array('*')){
        $oItem = $this->getItem()->addFields($aFields)->addSearchCondition(array('id' => $id))->load();
        return $oItem;
    }

    /**
     * Returns item model object and load data for non-primary key field.
     *
     * @param  array $aSearchCondition  Filter array key => value
     * @param  array $aFields           Fiedls to load (since 5.12.8)
     * @return AMI_ModTableItem
     */
    public function findByFields(array $aSearchCondition, array $aFields = array('*')){
        $oItem = $this->getItem()->addFields($aFields)->addSearchCondition($aSearchCondition)->load();
        return $oItem;
    }


    /**
     * Returns item model object and add new data without saving.
     *
     * @param  array $aData  Array with item data to be stored
     * @return AMI_ModTableItem
     */
    public function add(array $aData = array()){
        $oItem = $this->getItem()->setValues($aData);
        return $oItem;
    }

    /**
     * Activate or deactivate dependence for the next query.
     *
     * @param  string $alias  Table model alias (see setDependence)
     * @param  bool $isActive  Is dependant mode active
     * @return mixed  Previous state or null id dependence is not found
     */
    public function setActiveDependence($alias, $isActive = true){
        $res = null;
        if(isset($this->dependentRes[$alias])){
            $res = $this->dependentRes[$alias]['active'];
            $this->dependentRes[$alias]['active'] = $isActive;
        }
        return $res;
    }

    /**
     * Returns dependent active model aliases.
     *
     * @return array
     * @amidev temporary?
     */
    public function getActiveDependenceAliases(){
       $aAliases = array();
       foreach(array_keys($this->dependentRes) as $alias){
           if($this->dependentRes[$alias]['active']){
               $aAliases[] = $alias;
           }
       }

       return $aAliases;
    }

    /**
     * Returns dependence resource name.
     *
     * @param  string $alias  Required alias of a dependence
     * @return string|null  Resource Id corresponding to alias or null if no resource found
     */
    public function getDependenceResId($alias){
        return
            isset($this->dependentRes[$alias]['res'])
                ? $this->dependentRes[$alias]['res']
                : NULL;
    }

    /**
     * Checks if model has a field.
     *
     * See {@link PlgAJAXResp::initModel()} for usage example.
     *
     * @param  string $name  Field name in table
     * @param  bool $bAppendEventFields  Fire 'on_get_available_fields' event to append extension fields
     * @return bool
     * @todo   Reset $this->aHTMLFields for cms_pages, it has these fields.
     */
    public function hasField($name, $bAppendEventFields = false){
        $this->initExtensions();
        if(isset($this->aFieldNamesRemap[$name])){
            return $this->aFieldNamesRemap[$name] != self::FIELD_DOESNT_EXIST;
        }
        if(in_array($name, $this->aHTMLFields)){
            return $this->hasField('sm_data');
        }
        if($bAppendEventFields){
            $aFields = array();
            $aEvent = array('aFields' => &$aFields);
            /**
             * Allows modify list of fields available from AMI_ModTable::getAvailableFields(true), AMI_ModTableList::getAvailableFields(true).
             *
             * @event      on_get_available_fields $modId
             * @eventparam array aFields  Array of fields
             */
            AMI_Event::fire('on_get_available_fields', $aEvent, $this->getModId());
            if(isset($aFields[$name])){
                return TRUE;
            }
        }
        if(!empty(self::$aTableFields[$this->tableName])){
            return isset(self::$aTableFields[$this->tableName][$name]);
        }
        $this->buildTableDescription();
        return
            mb_strpos(
                self::$aTableDescription[$this->tableName],
                '`' . $name . '`'
            ) !== FALSE;
    }

    /**
     * Returns next primary key field value.
     *
     * @return int|null
     * @since  5.14.0
     */
    public function getNextPKValue(){
        $this->buildTableDescription();

        return
            preg_match('/AUTO_INCREMENT=(\d+)/', self::$aTableDescription[$this->tableName], $aMatches)
                ? (int)$aMatches[1]
                : NULL;
    }

    /**
     * Returns real field name by its alias.
     *
     * See {@link PlgAJAXResp::__construct()} for usage example.
     *
     * @param  string $alias   Alias
     * @param  string $prefix  Prefix
     * @return string
     * @deprecated  After db field names standardization this method will became useless.
     */
    public function getFieldName($alias, $prefix = ''){
        $aStruct = $this->getColumnStruct($alias, $prefix);

        return $aStruct['prefix'] . $aStruct['name'];
    }

    /**
     * Returns array of available fields.
     *
     * Dependend model fields returns model alias as index and array of its fields as value.<br /><br />
     *
     * Example:
     * <code>
     * $aAvailableFields = AMI::getResourceModel('articles/table')->getAvailableFields(true);
     * </code>
     *
     * <br />will contain:
     * <pre>
     * array(
     *     0 => 'announce',
     *     1 => 'body',
     *     ....
     *     'cat' => array(
     *          0 => 'id',
     *          1 => 'header',
     *          ...
     *     )
     * </pre>
     *
     * Common possible fields description:
     * - <b>id</b> - item identifier (int),
     * - <b>public</b> - flag specifying front-side item displaying (0/1),
     * - <b>header</b> - item header (string),
     * - <b>announce</b> - short item description (string),
     * - <b>body</b> - full item description (string),
     * - <b>lang</b> - item locale (string, 2-3 chars),
     * - <b>date_created</b> - item creation date,
     * - <b>date_modified</b> - item modification date,
     * - <b>sublink</b> - part of front-side item link (string),
     * - <b>id_page</b> - front-side page id, used for multipage modules (int),
     * - <b>id_owner</b> - owner user id (int),
     * - <b>position</b> - item position, used in lists when ordering by position (int),
     * - <b>details_noindex</b> - forbid page indexing for search engines (0/1),
     * - <b>hide_in_list</b> - do not display item in lists on front-side (0/1),
     * - <b>sticky</b> - flag specifying if item is sticky for lists on front-side (0/1),
     * - <b>date_sticky_till</b> - date when sticky flag will be reset (string, datetime),
     * - <b>html_title</b> - HTML meta title (string),
     * - <b>html_keywords</b> - Comma separated HTML meta keywords (string),
     * - <b>html_description</b> - HTML meta description (string),
     * - <b>html_is_kw_manual</b> - are HTML meta fields filled manually(0/1), since 5.12.8,
     *
     * Common Images extension fields description (will be able if Images extension is turned on for module):
     * - <b>ext_img</b> - item image (string),
     * - <b>ext_img_small</b> - item small image (string),
     * - <b>ext_img_popup</b> - item popup image (string).
     *
     * To override options for image autogeneration system functionality,<br />
     * you can specify following Images extension options in fast environment context.<br />
     * These options can be changed from module options interface at "Extension Images" section.<br /><br />
     * Usage example:
     * <code>
     * $modId = 'news';
     * // Images that can be resize automatically
     * AMI::setOption($modId, 'generate_pictures', array('picture', 'popup_picture', 'small_picture'));
     * // Default preference image
     * AMI::setOption($modId, 'prior_source_picture', 'popup_picture');
     * // Resized image maximum width
     * AMI::setOption($modId, 'picture_maxwidth', 300);
     * // Resized image maximum height
     * AMI::setOption($modId, 'picture_maxheight', 300);
     * // Small image maximum width
     * AMI::setOption($modId, 'small_picture_maxwidth', 80);
     * // Small image maximum height
     * AMI::setOption($modId, 'small_picture_maxheight', 80);
     * // Popup image maximum width
     * AMI::setOption($modId, 'popup_picture_maxwidth', 800);
     * //  Popup image maximum height
     * AMI::setOption($modId, 'popup_picture_maxheight', 600);
     * // Enlarge image from original if required
     * AMI::setOption($modId, 'generate_bigger_image', true);
     * </code>
     *
     * <br />Common Ratings extension fields description:
     * - <b>ext_rate_opt</b> - item rating options,
     * - <b>ext_rate_count</b> - item votes count,
     * - <b>ext_rate_rate</b> - item rating.
     *
     * Common Tags extension fields description:
     * - <b>ext_tags</b> - tags identifiers (comma-separated integers),
     *
     * Common Discussion extension fields description:
     * - <b>ext_dsc_disable</b> - disable item discussion possibility
     *
     * <b>Attention! Following fields can be available in case of Categories extension activity.</b><br />
     * Common Categories extension fields description (array key will be category model alias, cat as usual).
     * - <b>id</b> - item category identifier (int),
     * - <b>header</b> - item category header (string),
     * - <b>sublink</b> - part of front-side item category link (string).
     *
     * @param  bool $bAppendEventFields  Fire 'on_get_available_fields' event to append extension fields
     * @return array
     * @todo   Move extension fields description to appropriate classes
     * @todo   Reset $this->aHTMLFields for cms_pages, it has these fields
     */
    public function getAvailableFields($bAppendEventFields = true){
        $this->getTableFieldsData();
        $aFields = array_diff(
            array_merge(
                array_keys(array_diff_key(self::$aTableFields[$this->tableName], array_flip($this->aFieldNamesRemap))),
                array_keys($this->aFieldNamesRemap)
            ),
            $this->aSysFields
        );
        unset($aFields[self::FIELD_DOESNT_EXIST]);
        foreach($this->aFieldNamesRemap as $mappedField => $field){
            if($field === self::FIELD_DOESNT_EXIST && in_array($mappedField, $aFields)){
                unset($aFields[array_search($mappedField, $aFields)]);
            }
        }
        if($this->hasField('sm_data')){
            foreach($this->aHTMLFields as $field){
                $aFields[] = $field;
            }
        }
        if($bAppendEventFields){
            $this->initExtensions();
            $aEvent = array('aFields' => &$aFields);
            /**
             * Allows modify list of fields available from AMI_ModTable::getAvailableFields(true), AMI_ModTableList::getAvailableFields(true).
             *
             * @event      on_get_available_fields $modId
             * @eventparam array aFields  Array of fields
             */
            AMI_Event::fire('on_get_available_fields', $aEvent, $this->getModId());
        }
        return $aFields;
    }

    /**
     * Exludes fields not from available list.
     *
     * @param  array $aFields  Fields
     * @return array
     * @see    AMI_ModTableList::addColumns()
     * @see    AMI_ModTableList::setInvalidColumnExclusion()
     * @since  5.12.0
     */
    public function filterFields(array $aFields){
        return array_intersect($aFields, $this->getAvailableFields(true));
    }

    /**
     * Returns columns structures (prefix, name, alias).
     *
     * @param  array $aColumns  Columns
     * @param  string $prefix  Field prefix
     * @return array
     * @amidev
     */
    public function getColumnsStruct(array $aColumns = array(), $prefix = ''){
        if(empty($aColumns)){
            $aColumns = $this->getAvailableFields();
        }
        $aStructs = array();
        $bNoSMData = true;
        foreach($aColumns as $model => $name){
            if(is_array($name)){
                // Dependent model structs
                $aStructs = array_merge(
                    $aStructs,
                    AMI::getResourceModel($this->getDependenceResId($model) . '/table')->
                    getColumnsStruct($name, $model)
                );
            }else{
                if(in_array($name, $this->aHTMLFields)){
                    if($bNoSMData){
                        $name = 'sm_data';
                        $bNoSMData = false;
                    }else{
                        continue;
                    }
                }
                $aStructs[] = $this->getColumnStruct($name, $prefix);
            }
        }
        return $aStructs;
    }

    /**
     * Returns remap fields array.
     *
     * @return array
     * @amidev
     */
    public function getFieldsRemap(){
        return $this->aFieldNamesRemap;
    }

    /**
     * Returns fields that should be ignored during selection.
     *
     * @return array
     * @amidev
     */
    public function getSystemFields(){
        return $this->aSysFields;
    }

    /**
     * Returns navigation fields.
     *
     * @return array
     * @amidev
     */
    public function getNavFields(){
        $aResult = array();
        for($i = sizeof($this->aNavFields) - 1; $i >= 0; $i--){
            if($this->hasField($this->aNavFields[$i])){
                $aResult[] = $this->aNavFields[$i];
            }
        }
        return $aResult;
    }

    /**
     * Returns HTML meta fields.
     *
     * @return array
     * @amidev
     */
    public function getHTMLFields(){
        return $this->aHTMLFields;
    }

    /**
     * Adds validators array for fields.
     *
     * Model automatically validates fields in terms of database fields description for following types:<br />
     *     'int', 'float', 'double',<br />
     *     'char', 'varchar',<br />
     *     'tinytext', 'text', 'mediumtext',<br />
     *     'tinyblob', 'blob', 'mediumblob',<br />
     *     'datetime', 'date', 'time'.<br /><br />
     *
     * There are predifined system validators, other validators are custom and can be descibe by developers:
     * - 'required' means that field must be declared in model;
     * - 'filled' means that field must be declared in model and not equal to the empty string.
     *
     * Example:
     * <code>
     * class DemoModule_Table extends AMI_ModTable{
     * }
     *
     * class DemoModule_TableItem extends AMI_ModTableItem{
     *     public function __construct(AMI_ModTable $oTable, DB_Query $oQuery = null){
     *         parent::__construct($oTable, $oQuery);
     *         $this->oTable->addValidators(
     *             array(
     *                 'header' => array('filled', 'virtual_field_presence'),
     *                 'body'   => array('required')
     *             )
     *         );
     *         // Custom validator handler
     *         AMI_Event::addHandler('on_save_validate_{virtual_field_presence}', array($this, 'validateVirtualFieldPresence'), $this->getModId());
     *     }
     *
     *     // $aEvent is array containing following data:
     *     // - 'field' - field name,
     *     // - 'value' - field value,
     *     // - 'oItem' - AMI_ModTableItem object
     *     // - 'oTable' - AMI_ModTable object
     *     // - 'oTableItemModifier' - AMI_ModTableItemModifier object
     *     // );
     *     public function validateVirtualFieldPresence($name, array $aEvent, $handlerModId, $srcModId){
     *         if(!isset($aEvent['oItem']->virtual_field)){
     *             $aEvent['message'] = 'Missing `virtual_field` when `header` field is present';
     *         }
     *         return $aEvent;
     *     }
     * }
     *
     * $modId = 'demo_module';
     * AMI::addResourceMapping(
     *     array(
     *         $modId . '/table/model'      => 'DemoModule_Table',
     *         $modId . '/table/model/item' => 'DemoModule_TableItem'
     *     )
     * );
     *
     * $oItem = AMI::getResourceModel($modId . '/table')->$oTable->getItem();
     * try{
     *     $oItem->save();
     *     $oResponse->write('Item is saved successfully');
     * }catch(AMI_ModTableItemException $e){
     *     $oResponse->write('Item is not saved, validation failed');
     *     d::vd($e->getData());
     * }
     * </code>
     *
     * @param  array $aValidators  Array of validators in format 'aField' => array('validator1', 'validator2', ...)
     * @return AMI_ModTable
     */
    public function addValidators(array $aValidators){
        foreach($aValidators as $field => $aFieldValidators){
            if(!isset($this->aUserValidators[$field])){
                $this->aUserValidators[$field] = array();
            }
            $this->aUserValidators[$field] = array_unique(array_merge($this->aUserValidators[$field], $aFieldValidators));
        }
        return $this;
    }

    /**
     * Returns array of validators in format 'aField' => array('validator1', 'validator2', ...).
     *
     * @return array
     * @amidev
     */
    public function getValidators(){
        if(is_null($this->aValidators)){
            $this->initExtensions();
            // Add main table filters
            $aEvent = array(
                'modId'  => $this->getModId(),
                'oTable' => $this
            );
            /**
             * Allows to modify table item model validators.
             *
             * @event      on_get_validators $modId
             * @eventparam string        modId   Module id
             * @eventparam AMI_ModTable  oTable  Table model
             */
            AMI_Event::fire('on_get_validators', $aEvent, $this->getModId());

            // Add system validators. Validators should be in the outer but not DB field names
            $aRemap = array_flip($this->aFieldNamesRemap);

            $aSysValidators = array();
            foreach($this->getTableFieldsData() as $name => $aField){
                if(in_array($aField['type'], $this->aSysValidators)){
                    $aSysValidators[isset($aRemap[$name]) ? $aRemap[$name] : $name] = array($aField['type']);
                }
            }
            $this->addValidators($aSysValidators);
            $this->aValidators = $this->aUserValidators;
        }
        return $this->aValidators;
    }

    /**
     * Returns column types.
     *
     * @param  array $aColumns  List columns, the result in AMI_ModTable::getAvailableFields() format
     * @return array
     * @see    AMI_ModTable::getAvailableFields()
     * @amidev
     */
/*
    public function getColumnTypes(array $aColumns){
        $aTypes = array();
        $aFields = $this->getTableFieldsData();
        echo '<pre>';
        foreach($aColumns as $model => $column){
            if(is_array($column)){
                $resId = $this->getDependenceResId($model);
                if(is_null($resId)){
                    trigger_error('No dependent resource found for alias "' . $model . '"', E_USER_ERROR);
                }
                $aDependemtTypes = AMI::getResourceModel($resId . '/table')->getColumnTypes($column, $model);
                foreach($aDependemtTypes as $field => $type){
                    $aTypes[$model . '_' . $field] = $type;
                }
            }else{
                $aTypes[$column] = $aFields[$this->getFieldName($column)]['type'];
            }
        }
        return $aTypes;
    }
*/
    /**
     * Adds remap array for fields.
     *
     * @param  array $aRemap  Array of remap in format 'field' => 'alias'
     * @return void
     * @todo   Rename db fields and avoid any remapping
     * @amidev
     */
    public function addFieldsRemap(array $aRemap){
        $this->initExtensions();
        $aEvent = array(
            'aFields' => &$aRemap,
            'oTable'  => $this
        );
        /**
         * Allows extensions to add mapping database fields.
         *
         * @event      on_add_field_mapping $modId
         * @eventparam AMI_ModTable oTable   Table model object (since 6.0.2)
         * @eventparam array        aFields  Array of fields
         */
        AMI_Event::fire('on_add_field_mapping', $aEvent, $this->getModId());
        $this->aFieldNamesRemap += $aRemap;
    }

    /**
     * Sets table dependences (from other table models for JOIN SQL part).
     *
     * Since 5.12.8 this method scope is changed from protected to public.<br /><br />
     * Example:
     * <code>
     * class Articles_Table extends AMI_ModTable{
     *     protected $tableName = 'cms_articles';
     *
     *     public function __construct(array $aAttributes = array()){
     *         $this->setDependence('articles_cat', 'cat', 'cat.id=i.id_cat');
     * ...
     * </code>
     *
     * @param  string $modId      Module id
     * @param  string $alias      Joined table alias
     * @param  string $condition  Join condition (ON)
     * @param  string $joinType   Type of join, INNER JOIN by default
     * @return void
     */
    public function setDependence($modId, $alias, $condition, $joinType = 'INNER JOIN'){
        if(isset($this->dependentRes[$alias])){
            trigger_error('Alias ' . $alias . ' is already added to dependence for ' . get_class(), E_USER_ERROR);
        }
        $this->dependentRes[$alias] = array(
            'res'       => $modId,
            'condition' => $condition,
            'join_type' => $joinType,
            'active'    => false
        );
    }

    /**
     * Change dependence mod Id for specified alias.
     *
     * @param  string $alias  Joined table alias
     * @param  string $modId  Module id
     * @return void
     * @amidev Temporary
     */
    public function changeDependenentModId($alias, $modId){
        if(!isset($this->dependentRes[$alias])){
            trigger_error('Alias ' . $alias . ' has not been added to dependence for ' . get_class(), E_USER_ERROR);
        }
        $this->dependentRes[$alias]['res'] = $modId;
    }

    /**
     * Set module id.
     *
     * @param  string $modId  Module Id
     * @return void
     * @amidev
     */
    public function setModId($modId){
        $this->modId = $modId;
    }

    /**
     * Sets passed attributes.
     *
     * @param  array $aAttributes  Attributes
     * @return void
     * @since  6.0.2
     */
    protected function initAttributes(array $aAttributes){
        if(!is_array($aAttributes)){
            $aAttributes = array();
        }
        // d::vd($aAttributes, 'initAttributes');###
        $this->aAttributes = $aAttributes;
    }

    /**
     * Returns module id.
     *
     * @return string
     */
    protected function getModId(){
        if(empty($this->modId)){
            $this->modId = AMI::getModId(get_class($this));
        }
        return $this->modId;
    }

    /**
     * Adds fields that will be ignored during selection.
     *
     * @param  array $aSysFields  System fields
     * @return void
     * @amidev
     */
    protected function addSystemFields(array $aSysFields){
        $this->aSysFields = array_merge($this->aSysFields, $aSysFields);
    }

    /**
     * Disables HTML meta fields.
     *
     * @return AMI_ModTable
     * @see    Pages_Table::__construct()
     * @amidev
     */
    protected function disableHTMLFields(){
        $this->aHTMLFields = array();
        return $this;
    }

    /**
     * Set pair tableName => moduleId to registry variable tableBlockNames. Used for cache expiring.
     *
     * @return void
     */
    protected function storeTableBlockName(){
        if(AMI_Registry::exists('tableBlockNames')){
            $aBlockNames = AMI_Registry::get('tableBlockNames');
        }else{
            $aBlockNames = array();
        }

        $modId = $this->getModId();
        if(AMI::isModInstalled($modId) && AMI_Registry::exists('_source_mod_id')){
            $modId = 'small_' . AMI_Registry::get('_source_mod_id');
        }
        $aBlockNames[$this->tableName] = $modId;
        AMI_Registry::set('tableBlockNames', $aBlockNames);
    }

    /**
     * Initialize extensions for model.
     *
     * @return void
     * @since  6.0.2
     */
    protected function initExtensions(){
        // #CMS-11322
        $modId = $this->getModId();
        if(!empty(self::$extInitialized[$modId])){
            return;
        }
        self::$extInitialized[$modId] = TRUE;
        $attr = 'extModeOnConstruct';
        $default = AMI_Registry::get('AMI/Environment/Model/DefaultAttributes/' . $attr, 'none');
        $mode = $this->getAttr($attr, $default);
        switch($mode){
            case 'common':
                AMI::initModExtensions($modId);
                break;
            case 'none':
                break;
            case 'all':
                if(AMI::issetOption($modId, 'extensions')){
                    $aExt = AMI_ModRules::getAvailableExts($modId);
                    if($aExt){
                        $aExtBackup = AMI::getOption($modId, 'extensions');
                        AMI::setOption($modId, 'extensions', $aExt);
                        AMI::initModExtensions($modId);
                        AMI::setOption($modId, 'extensions', $aExtBackup);
                    }
                }
                break;
            default:
                AMI::initModExtensions($modId);
                trigger_error(
                    "Invalid attribute '" . $attr . "' value '" . $mode . "', '" . $default . "' was used",
                    E_USER_WARNING
                );
        }
    }

    /**
     * Deinitialize extensions.
     *
     * @return void
     * @since  6.0.2
     */
    protected function cleanupExtensions(){
        // #CMS-11322
        $modId = $this->getModId();
        $attr = 'extModeOnDestruct';
        $default = AMI_Registry::get('AMI/Environment/Model/DefaultAttributes/' . $attr, 'none');
        $mode = $this->getAttr($attr, $default);
        switch($mode){
            case 'none':
                break;
            case 'cleanup':
                AMI::cleanupModExtensions($modId);
                break;
            default:
                trigger_error(
                    "Invalid attribute '" . $attr . "' value '" . $mode . "', '" . $default . "' was used",
                    E_USER_WARNING
                );
        }
        unset(self::$extInitialized[$modId]);
    }

    /**
     * Loads table description from SQL server.
     *
     * @return void
     */
    private function buildTableDescription(){
        if(!empty($this->tableName) && empty(self::$aTableDescription[$this->tableName])){
            $sql = 'SHOW CREATE TABLE `' . $this->tableName . '`';
            /**
             * @var AMI_DB
             */
            $oDB = AMI::getSingleton('db');
            $oDB->allowUnsafeQueryOnce();
            $aRow = $oDB->fetchRow($sql, MYSQL_NUM, DBC_RAW_QUERY);
            self::$aTableDescription[$this->tableName] = $aRow[1];
        }
    }

    /**
     * Returns column structure (prefix, name, alias).
     *
     * @param  string $name  Name of a field
     * @param  string $prefix  Field prefix
     * @return array
     */
    private function getColumnStruct($name, $prefix = ''){
        $alias = '';
        if(isset($this->aFieldNamesRemap[$name]) && $this->aFieldNamesRemap[$name] != self::FIELD_DOESNT_EXIST){
            $alias = $name;
            $name = $this->aFieldNamesRemap[$name];
        }
        if(!empty($this->aPrefixRules[$prefix])){
            $alias = $this->aPrefixRules[$prefix] . (empty($alias) ? $name : $alias);
        }elseif(!empty($prefix)){
            $alias = $prefix . '_' . (empty($alias) ? $name : $alias);
        }
        $aStruct = array(
            'name'   => $name,
            'prefix' => $prefix,
            'alias'  => $alias
        );
        return $aStruct;
    }

    /**
     * Returns the query object that will be passed to list model.
     *
     * @param  string $type  Postfix for 'on_query_add_joined_columns' event
     * @param  bool $bJoinDependentTables  Is table joins required
     * @return DB_Query
     */
    private function getQueryBase($type = '', $bJoinDependentTables = true){
        $alias = 'i';
        $oQuery = new DB_Query($this->tableName, $alias);

        // Add main table filters
        $aEvent = array(
            'modId'  => $this->getModId(),
            'oQuery' => $oQuery,
            'oTable' => $this,
            'alias'  => $alias
        );
        /**
         * Allow to modify DB query object.
         *
         * @event      on_query_add_table AMI_Event::MOD_ANY
         * @eventparam string modId  Module id
         * @eventparam DB_Query oQuery  DB query object
         * @eventparam AMI_ModTable oTable  Table model
         * @eventparam string alias  Model alias
         */
        AMI_Event::fire('on_query_add_table', $aEvent, AMI_Event::MOD_ANY);
        /**
         * Allow to modify DB query object.
         *
         * @event      on_query_add_table $modId
         * @eventparam string modId  Module id
         * @eventparam DB_Query oQuery  DB query object
         * @eventparam AMI_ModTable oTable  Table model
         * @eventparam string alias  Model alias
         */
        AMI_Event::fire('on_query_add_table', $aEvent, $this->getModId());

        if($bJoinDependentTables){
            foreach($this->dependentRes as $alias => $classData){
                if($classData['active']){
                    $oDependent = AMI::getResourceModel($classData['res'] . '/table');
                    $sDependentTable = $oDependent->getTableName();
                    if(!empty($sDependentTable)){
                        $oQuery->addJoinedTable(
                            $sDependentTable,
                            $alias,
                            $classData['condition'],
                            $this->dependentRes[$alias]['join_type']
                        );
                        $oTableList = $oDependent->getList();
                        // Add joined dependent table columns for select
                        $aEvent = array(
                            'modId'  => $oDependent->getModId(),
                            'oQuery' => $oQuery,
                            'oTable' => $oDependent,
                            'oList'  => $oTableList,
                            'alias'  => $alias
                        );
                        /**
                         * Event, called for add columns to the query result, a compound derived from the dependent model.
                         *
                         * @event      on_query_add_joined_columns $modId
                         * @eventparam string           modId   Module id
                         * @eventparam DB_Query         oQuery  DB query object
                         * @eventparam AMI_ModTable     oTable  Table model
                         * @eventparam AMI_ModTableList oList   Table list model
                         * @eventparam string           alias   Model alias
                         */
                        AMI_Event::fire(
                            'on_query_add_joined_columns' . ($type? '_' . $type : ''),
                            $aEvent,
                            $oDependent->getModId()
                        );
                        // Add joined dependent table filters
                        $aEvent = array(
                            'modId'  => $oDependent->getModId(),
                            'oQuery' => $oQuery,
                            'oTable' => $oDependent,
                            'alias'  => $alias
                        );
                        // Commented due to PM 4838#c1, item 4
                        // AMI_Event::fire('on_query_add_table', $aEvent, AMI_Event::MOD_ANY);
                        /**
                         * Allow to modify DB query object.
                         *
                         * @event      on_query_add_table $modId
                         * @eventparam string modId  Module id
                         * @eventparam DB_Query oQuery  DB query object
                         * @eventparam AMI_ModTable oTable  Table model
                         * @eventparam string alias  Model alias
                         */
                        AMI_Event::fire('on_query_add_table', $aEvent, $oDependent->getModId());
                        $oTableList->addSelectedFields($oQuery, $alias);
                    }
                }
            }
        }
        return $oQuery;
    }
}
