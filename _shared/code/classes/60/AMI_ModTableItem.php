<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModTableItem.php 49167 2014-03-28 09:51:42Z Leontiev Anton $
 * @since     5.10.0
 */

/**
 * Module component item model interface.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @see        AmiSample_TableItem
 * @since      5.10.0
 */
interface AMI_iModTableItem{
    /**
     * Returns primary key field name.
     *
     * @return string
     */
    public function getPrimaryKeyField();

    /**
     * Loads data by specified condition or sets new item data.
     *
     * @return AMI_ModTableItem
     */
    public function load();

    /**
     * Sets field callback on AMI_ModTableItem::__get() and AMI_ModTableItem::__set().
     *
     * @param  string   $field     Field name
     * @param  callback $callback  Formatter callback
     * @return AMI_ModTableItem
     * @since  5.12.0
     */
    public function setFieldCallback($field, $callback);

    /**
     * Checks if the field has a specified callback.
     *
     * @param  string   $field     Field name
     * @param  callback $callback  Formatter callback
     * @return bool
     * @since 5.14.4
     */
    public function hasFieldCallback($field, $callback);

    /**
     * Returns data array.
     *
     * @return array
     */
    public function getData();

    /**
     * Returns raw data array.
     *
     * @return array
     */
    public function getRawData();

    /**
     * Returns module id.
     *
     * @return string
     */
    public function getModId();
}

/**
 * Module table item model.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @since      5.10.0
 */
abstract class AMI_ModTableItem implements AMI_iModTableItem, Iterator, AMI_iFormModTableItem{
    /**
     * Module table model
     *
     * @var AMI_ModTable
     */
    protected $oTable;

    /**
     * DB query object
     *
     * @var DB_Query
     */
    protected $oQuery;

    /**
     * Element id value
     *
     * @var int|string
     */
    protected $id;

    /**
     * Primary column field name
     *
     * @var string
     */
    protected $primaryKeyField = 'id';

    /**
     * Default value for empty element id
     *
     * @var int|string
     */
    protected $idEmpty = 0;

    /**
     * Element data
     *
     * @var array
     */
    protected $aData = array();

    /**
     * Iterator pointer
     *
     * @var int
     * @see Iterator implementation
     */
    protected $dataIndex = -1;

    /**
     * Fields to load
     *
     * @var array
     * @see AMI_ModTableItem::addFields()
     */
    protected $aFields = array();

    /**
     * Original data, used in meta processor
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aOrigData = array();

    /**
     * Tainted fields list
     *
     * @var   array
     * @since 5.12.8
     */
    protected $aOrigFields = array('public' => false, 'cat_id' => false);

    /**
     * Virtual field types
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aVirtualFields = array();

    /**
     * Module id
     *
     * @var string
     *
     * @see AMI_Mod::getModId()
     */
    private $modId = '';

    /**
     * Meta data
     *
     * @var array
     * @see AMI_ModTableItem::save()
     */
    private $aMeta = array(
        // 'auto'   => true,
        'filled' => false
    );

    /**
     * Item search condition
     *
     * @var array
     */
    private $aCondition = array();

    /**
     * Field callbacks
     *
     * @var array
     */
    private $aFieldCallbacks = array();

    /**
     * Flag storing model data changes to avoid excess saving
     *
     * @var bool
     */
    private $skipSave = false;

    /**
     * Table item modifier
     *
     * @var AMI_ModTableItemModifier
     */
    private $oTableItemModifier;

    /**
     * Virtual field type filter
     *
     * @var string
     * @see AMI_ModTableItem::getVirtualFields()
     */
    private $fieldFilter;

    /**
     * Fields storing serialized data
     *
     * @var array
     * @see AMI_ModTableItem::fcbSerialized()
     */
    protected $aSerializedFields = array();

    /**
     * See usage
     *
     * @var bool
     */
    private $suppressModPageError = FALSE;

    /**
     * Allow to save model flag.
     *
     * @var bool
     * @amidev Temporary
     */
    protected $bAllowSave;

    /**
     * Constructor.
     *
     * Initializes table item data.
     *
     * @param AMI_ModTable $oTable  Module table model
     * @param DB_Query     $oQuery  Required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery = null){
        // d::vd('AMI_ModTableItem::__construct(' . $this->getModId() . ')');###
        if(!($oTable instanceof AMI_ModTable)){
            trigger_error("First argument must be an instance of AMI_ModTable class", E_USER_ERROR);
        }
        $this->id = $this->idEmpty;
        $this->oTable = $oTable;
        $this->oQuery = $oQuery;
    }

    /**
     * Destructor.
     *
     * @see   self::cleanupExtensions()
     * @since 6.0.2
     */
    public function __destruct(){
        // d::vd('AMI_ModTableItem::__destruct(' . $this->getModId() . ')');###
        // d::trace();###
        $this->aFieldCallbacks = NULL;
    }

    /**
     * Property access getter.
     *
     * Returns value from data array.
     *
     * @param  string $name  Property name
     * @return mixed
     */
    public function __get($name){
        return $this->getValue($name);
    }

    /**
     * Property access setter.
     *
     * Sets value to data array.
     *
     * @param  string $name  Property name
     * @param  string $value  Property value
     * @return void
     */
    public function __set($name, $value){
        $this->setValue($name, $value);
        $this->rewind();
    }

    /**
     * Property access getter.
     *
     * Check if key is exists in data array.
     *
     * @param  string $name  Property name
     * @return bool
     */
    public function __isset($name){
        return array_key_exists($name, $this->aData);
    }

    /**
     * Property access setter.
     *
     * Unsets value in data array by key.
     *
     * @param  string $name  Property name
     * @return void
     */
    public function __unset($name){
        if(in_array($name, $this->oTable->getHTMLFields())){
            // $this->aMeta['auto'] = false;
            $this->aData[$name] = '';
        }else{
            unset($this->aData[$name]);
            $this->rewind();
        }
    }

    /**#@+
     * Iterator interface implementation.
     */

    /**
     * Returns the current element.
     *
     * @return mixed
     */
    public function current(){
        $aKeys = array_keys($this->aData);
        return $this->getValue($aKeys[$this->dataIndex]);
    }

    /**
     * Returns the key of the current element.
     *
     * @return mixed
     */
    public function key(){
        $aKeys = array_keys($this->aData);
        return $aKeys[$this->dataIndex];
    }

    /**
     * Move forward to next element.
     *
     * @return void
     */
    public function next(){
        $this->dataIndex++;
        if($this->dataIndex > sizeof($this->aData)){
            $this->dataIndex = -1;
        }
    }

    /**
     * Rewinds the Iterator to the first element.
     *
     * @return void
     */
    public function rewind(){
        $this->dataIndex = sizeof($this->aData) ? 0 : -1;
    }

    /**
     * Checks if current position is valid.
     *
     * @return bool
     */
    public function valid(){
        $aKeys = array_keys($this->aData);
        return isset($aKeys[$this->dataIndex]);
    }

    /**#@-*/

    /**
     * Returns current item primary key value.
     *
     * @return mixed
     */
    public function getId(){
        return $this->id;
    }

    /**
     * Resets primary key.
     *
     * @return AMI_ModTableItem
     * @since  5.12.4
     */
    public function resetId(){
        $this->id = $this->getEmptyId();
        return $this;
    }

    /**
     * Returns item empty primary key value.
     *
     * @return mixed
     * @since  5.12.4
     */
    public function getEmptyId(){
        return $this->idEmpty;
    }

    /**
     * Returns primary key field name.
     *
     * @return string
     */
    public function getPrimaryKeyField(){
        return $this->primaryKeyField;
    }

    /**
     * Adds fields to load.
     *
     * Example:
     * <code>
     * $modId = 'articles';
     * $oTable = AMI::getResourceModel($modId . '/table');
     *
     * // Load all model fields, try to avoid loading all parameters when it isn't necessary
     * $oItem = $oTable->find(1, array('*'));
     *
     * // Load only 'id', 'header' fields
     * $oItem = $oTable->find(1, array('id', 'header'));
     * </code>
     *
     * @param  array $aFields  Fields to load
     * @param  bool $reset     Reset previous field list
     * @return AMI_ModTableItem
     * @see    AMI_ModTable::find()
     * @since  5.12.8
     */
    public function addFields(array $aFields = array('*'), $reset = false){
        if($reset){
            $this->aFields = array();
        }
        foreach($aFields as $model => $field){
            if(is_array($field)){
                if(empty($this->aFields[$model])){
                    $this->aFields[$model] = array();
                }
                foreach($field as $fld){
                    $this->aFields[$model][] = $fld;
                }
            }else{
                if(!in_array($field, $this->aFields)){
                    $this->aFields[] = $field;
                }
            }
        }
        return $this;
    }

    /**
     * Loads data by specified condition or sets new item data.
     *
     * @return AMI_ModTableItem
     * @see    AMI_ModTableItem::addSearchCondition()
     */
    public function load(){
        // Deprecated call of ::load($id)
        if(func_num_args()){
            AMI_Registry::set('_deprecated_error', TRUE);
            trigger_error("Retrieving elements by AMI_ModTableItem::load() method passing primary key argument is deprecated since 5.12.8, use AMI_ModTable::find() instead", E_USER_WARNING);
            $id = func_get_arg(0);
            return $this->addSearchCondition(array($this->primaryKeyField => $id))->load();
        }
        $aRecord = null;
        $hasCondition = !empty($this->aCondition);
        if($hasCondition){
            $aStructs = array();
            $bMissingCall = !sizeof($this->aFields);
            if($bMissingCall){
                trigger_error("Missing call AMI_ModTableItem::addFields() before using AMI_ModTableItem::load()", E_USER_WARNING);
            }
            if(in_array('*', $this->aFields) || $bMissingCall){
                $aStructs = $this->oTable->getColumnsStruct();
            }else{
                foreach($this->aFields as $model => $field){
                    if(is_array($field)){
                        foreach($this->oTable->getColumnsStruct(array($model => $field), $model) as $aStruct){
                            $aStructs[] = $aStruct;
                        }
                    }else{
                        foreach($this->oTable->getColumnsStruct(array($field)) as $aStruct){
                            $aStructs[] = $aStruct;
                        }
                    }
                }
            }
            foreach($aStructs as $aStruct){
                $this->oQuery->addField($aStruct['name'], $aStruct['prefix'], $aStruct['alias']);
            }
            /**
             * @var AMI_iDB
             */
            $oDB = AMI::getSingleton('db');
            foreach($this->aCondition as $field => $value){
                $this->oQuery->addWhereDef(
                    $this->oQuery
                    ->getSnippet('AND %s = %s')
                    ->plain($this->oTable->getFieldName($field, $this->oQuery->getMainTableAlias(TRUE)))
                    ->q($value)
                );
            }
            $aRecord = $oDB->fetchRow($this->oQuery);
        }
        if($aRecord){
            $this->setData($aRecord);
            $this->id = $aRecord[$this->primaryKeyField];
            if($this->oTable->hasField('sm_data') && isset($aRecord['sm_data'])){
                $aMeta = @unserialize($aRecord['sm_data']);
                if(is_array($aMeta)){
                    unset($aMeta['is_kw_manual']);
                    unset($aMeta['filled']);
                    $this->aOrigData['html_meta'] = $aMeta;
                }else{
                    unset($this->aOrigData['html_meta']);
                }
            }
            if($this->oTable->hasField('sublink') && isset($aRecord['sublink'])){
                $this->aMeta['original_sublink'] = $aRecord['sublink'];
            }
            if(sizeof($this->aOrigFields)){
                $this->aOrigData['aData'] = array();
                foreach($this->aOrigFields as $field => $isHashed){
                    if(isset($this->aData[$field])){
                        $this->aOrigData['aData'][$field] = $isHashed ? md5($this->aData[$field]) : $this->aData[$field];
                    }
                }
            }
            $this->skipSave = TRUE;
        }else{
            $this->aData = array();
            $this->id = $this->idEmpty;
            unset($this->aOrigData['html_meta'], $this->aOrigData['aData']);
            // unset($this->aOrigData['aData'], $this->aOrigData['aData']);
            $this->skipSave = FALSE;
        }
        return $this;
    }

    /**
     * Sets origin fields.
     *
     * Example:
     * <code>
     * // AmiSample_TableItem::__construct()
     * // ...
     * $this->setOriginFields(array('nickname'));
     * </code>
     *
     * @param  array $aFields  Fields
     * @param  bool  $bAsHash  Store hash instead of value
     * @param  bool  $bAppend  Append to existing fields
     * @return AMI_ModTableItem
     * @see    AMI_ModTableItem::getOriginFields()
     * @see    AMI_ModTableItem::getOriginData()
     * @see    AMI_ModTableItem::getDiffFromOrigin()
     * @since  5.12.8
     */
    public function setOriginFields(array $aFields, $bAsHash = false, $bAppend = true){
        if(!$bAppend){
            $this->aOrigFields = array();
        }
        foreach($aFields as $field){
            $this->aOrigFields[$field] = (bool)$bAsHash;
        }
        return $this;
    }

    /**
     * Returns origin fields as array having keys as field names and values as hashing flag.
     *
     * @return array
     * @see    AMI_ModTableItem::setOriginFields()
     * @see    AMI_ModTableItem::getOriginData()
     * @see    AMI_ModTableItem::getDiffFromOrigin()
     * @since  5.12.8
     */
    public function getOriginFields(){
        return $this->aOrigFields;
    }

    /**
     * Returns origin data.
     *
     * Example:
     * <code>
     * // AmiSample_FormAdm::_save()
     * // ...
     * if(is_object($this->oModelItem)){
     *     AMI::getSingleton('response')
     *         ->addStatusMessage(
     *             'origin_data',
     *             array('data' => d::getDumpAsString($this->oModelItem->getOriginData()))
     *         );
     * }
     * </code>
     *
     * @return array
     * @see    AMI_ModTableItem::setOriginFields()
     * @see    AMI_ModTableItem::getOriginFields()
     * @see    AMI_ModTableItem::getDiffFromOrigin()
     * @since  5.12.8
     */
    public function getOriginData(){
        return isset($this->aOrigData['aData']) ? $this->aOrigData['aData'] : array();
    }

    /**
     * Returns difference from origin data.
     *
     * Example:
     * <code>
     * // AmiSample_FormAdm::_save()
     * // ...
     * if(is_object($this->oModelItem)){
     *     AMI::getSingleton('response')
     *         ->addStatusMessage(
     *             'difference_from_origin',
     *             array('data' => d::getDumpAsString($this->oModelItem->getDiffFromOrigin()))
     *         );
     * }
     * </code>
     *
     * @return array
     * @see    AMI_ModTableItem::setOriginFields()
     * @see    AMI_ModTableItem::getOriginFields()
     * @see    AMI_ModTableItem::getOriginData()
     * @since  5.12.8
     */
    public function getDiffFromOrigin(){
        if(isset($this->aOrigData['aData'])){
            $aData = array();
            foreach(array_keys($this->aOrigData['aData']) as $field){
                $aData[$field] = empty($this->aOrigFields[$field]) ? $this->aData[$field] : md5($this->aData[$field]);
            }
            $aResult = array_diff_assoc($aData, $this->aOrigData['aData']);
        }else{
            $aResult = array();
        }
        return $aResult;
    }

    /**
     * Returns aData setuped on loading.
     *
     * @return array
     */
/*
    public function getOriginalData(){
        return isset($this->aOrigData['aData']) ? $this->aOrigData['aData'] : array();
    }
*/
    /**
     * Adds item search condition.
     *
     * Example:
     * <code>
     * // Load published News item with id = 1
     * $oModelItem =
     *     AMI::getResourceModel('news/table')->getItem()
     *         ->addFields(array('*'))
     *         ->addSearchCondition(
     *             array(
     *                 'id'     => 1,
     *                 'public' => 1
     *             )
     *         )
     *         ->load();
     * </code>
     *
     * @param array $aCondition  Item search condition - array(field => value)
     *
     * @return AMI_ModTableItem
     * @since  5.12.8
     * @see    AMI_ModTableItem::load()
     */
    public function addSearchCondition(array $aCondition){
        $this->aCondition += $aCondition;
        return $this;
    }

    /**
     * Loads data by fields array.
     *
     * @param  array $aFields  Filter array key => value
     * @return AMI_ModTableItem
     * @since  5.12.0
     * @amidev Deprecated
     */
    public function loadByFields(array $aFields = null){
        return $this->addSearchCondition($aFields)->load();
    }

    /**
     * Sets field callback on AMI_ModTableItem::__get() and AMI_ModTableItem::__set().
     *
     * Used for computational/virtual fields or to convert field value from/to internal/external format.<br /><br />
     *
     * Example:
     * <code>
     * class DemoModule_TableItem extends AMI_ModTableItem{
     *     public function __construct(AMI_ModTable $oTable, DB_Query $oQuery = null){
     *         parent::__construct($oTable, $oQuery);
     *         // Add own field callback for virtual field (no db field)
     *         $this->setFieldCallback('virtual_field', array($this, 'fcbVirtualField'));
     *     }
     *
     *     // $aData has following structure:
     *     //     'modId'     - module id
     *     //     'name'      - field name
     *     //     'action'    - 'get'/'set'/'save'/'after_save'
     *     //     'value'     - field value passed by reference
     *     //     'oItem'     - AMI_ModTableItem object
     *     // To skip storing virtual field callback can set following flag:
     *     // $aData['_skip'] = true;
     *     protected function fcbVirtualField(array $aData){
     *         $action = $aData['action'];
     *         switch($action){
     *             case 'get':
     *                 // For example always return 1
     *                 $aData['value'] = 1;
     *                 break;
     *             case 'set':
     *                 // Increment some counter
     *                 $aData['oItem']->counter++;
     *                 $aData['_skip'] = true;
     *                 break;
     *
     *         return $aData;
     *     }
     *
     *     // Notice that field callback is called if this field is present in form or you could override AMI_ModTableItem::save() next way:
     *     public function save(){
     *         $this->virtual_field = ...;
     *         return parent::save();
     *     }
     * }
     * </code>
     *
     * @param  string   $field     Field name
     * @param  callback $callback  Formatter callback
     * @return AMI_ModTableItem
     * @see    AMI_ModTableItem::__get()
     * @see    AMI_ModTableItem::__set()
     * @since  5.12.0
     */
    public function setFieldCallback($field, $callback){
        if(is_array($callback) && $callback[0] == $this){
            $callback[0] = '-';
        }
        $this->aFieldCallbacks[$field] = $callback;

        return $this;
    }

    /**
     * Checks if the field has a specified callback.
     *
     * @param  string   $field     Field name
     * @param  callback $callback  Formatter callback
     * @return bool
     * @since 5.14.4
     */
    public function hasFieldCallback($field, $callback){
        if(isset($this->aFieldCallbacks[$field])){
            $aCallback = $this->aFieldCallbacks[$field];
            return isset($aCallback[1]) && ($aCallback[1] === $callback);
        }
        return false;
    }

    /**
     * Saves current item data.
     *
     * Facade method for AMI_ModTableItemModifier::getValidatorException().<br /><br />
     *
     * Example:
     * <code>
     * $oModelItem = AMI::getResourceModel('ami_sample/table')->getItem();
     * $oModelItem->nickname = 'Nickname';
     * $oModelItem->birth = '1.1.2011';
     * try{
     *     $oModelItem->save();
     * }catch(AMI_ModTableItemException $oException){
     *     d::vd($oException->getData());
     * }
     * </code>
     *
     * @return AMI_ModTableItem
     * @throws AMI_ModTableItemException  If validation failed.
     * @since  5.12.0
     */
    public function save(){
        // Forbid saving before 6.0 or our modules
        /*
        if(
            !AMI_Registry::get('ami_allow_model_save', false) &&  // Since 6.0.6
            (mb_substr($this->oTable->getTableName(), 0, 4) === 'cms_') &&
            !(isset($this->bAllowSave) && $this->bAllowSave)
        )
        */
        if(isset($this->bAllowSave) && !$this->bAllowSave){
            trigger_error('Forbidden!', E_USER_ERROR);
        }

        if($this->skipSave){
            return $this;
        }

        $aData = array();
        foreach(array_unique(array_keys($this->aFieldCallbacks)) as $name){
            $value = isset($this->aData[$name]) ? $this->aData[$name] : NULL;
            $this->_useFieldCallback('save', $name, $value);
            if(!is_null($value)){
                $aData[$name] = $value;
            }
        }
        $this->aData = $aData + $this->aData;

        $this->getModifier()->save();

        foreach(array_unique(array_keys($this->aFieldCallbacks)) as $name){
            $value = isset($this->aData[$name]) ? $this->aData[$name] : NULL;
            $this->_useFieldCallback('after_save', $name, $value);
        }

        if(isset($this->bAllowSave) && $this->bAllowSave){
            $this->bAllowSave = FALSE;
        }
        $this->skipSave = TRUE;

        return $this;
    }

    /**
     * Deletes item from table and clear data array.
     *
     * Facade method for AMI_ModTableItemModifier::delete().<br /><br />
     *
     * Example:
     * <code>
     * $oTable = AMI::getResourceModel('ami_sample/table');
     * $oItem = $oTable->getItem();
     * // Delete item by primary key field
     * $oItem->delete(1);
     *
     * // Load and delete item
     * $oItem = $oTable->find(2);
     * $oModelItem->delete();
     * </code>
     *
     * @param  mixed $id  Primary key value of item
     * @return AMI_ModTableItem
     * @since  5.12.0
     */
    public function delete($id = null){
        // Forbid saving before 6.0 or our modules
        if(
            !AMI_Registry::get('ami_allow_model_save', false) &&
            (mb_substr($this->oTable->getTableName(), 0, 4) === 'cms_') &&
            !(isset($this->bAllowSave) && $this->bAllowSave)
        ){
            trigger_error('Forbidden!', E_USER_ERROR);
        }

        if(is_null($id)){
            $id = $this->id;
        }

        $aEvent = array(
            'id'       => $id,
            'oItem'    => $this,
            '_discard' => FALSE
        );
        /**
         * Called before model item deletion.
         *
         * Set $aEvent['_discard'] to TRUE to discard action.
         *
         * @event      on_before_delete_model_item $modId
         * @eventparam AMI_ModTableItem oItem     Table item model
         * @eventparam mixed            id        Table item Id to delete
         * @eventparam bool             _discard  Set to TRUE to discard action
         * @since      6.0.6
         */
        AMI_Event::fire('on_before_delete_model_item', $aEvent, $this->getModId());

        if(empty($aEvent['_discard'])){
            if($id !== $this->idEmpty){
                foreach(array_unique(array_keys($this->aFieldCallbacks)) as $name){
                    $this->_useFieldCallback('delete', $name, $id);
                }
                if($this->getModifier()->delete($id)){
                    $this->id = $this->idEmpty;
                    $this->aData = array();
                    $this->skipSave = false;
                }
            }
        }

        return $this;
    }

    /**
     * Returns field value from data array.
     *
     * @param  string $name  Field name
     * @return mixed  Field value or null if value is not found
     */
    public function getValue($name){
        $value = isset($this->aData[$name]) ? $this->aData[$name] : null;
        $this->_useFieldCallback('get', $name, $value);
        return $value;
    }

    /**
     * Sets field value in data array.
     *
     * @param  string $name  Field name
     * @param  mixed $value  Field value
     * @return AMI_ModTableItem
     * @amidev Temporary
     */
    public function setValue($name, $value){
        /*
        if(in_array($name, $this->oTable->getHTMLFields())){
            $this->aMeta['auto'] =
                $this->aMeta['auto'] &&
                (isset($this->aData[$name]) ? $this->aData[$name] === $value : false);
        }
        */
        if($this->_useFieldCallback('set', $name, $value)){
            if($this->skipSave && ($value !== (isset($this->aData[$name]) ? $this->aData[$name] : null))){
                $this->skipSave = FALSE;
            }
            $this->aData[$name] = $value;
        }

        return $this;
    }

    /**
     * Sets model values from array.
     *
     * Example:
     * <code>
     * $oModelItem = AMI::getResourceModel('ami_sample/table')->getItem();
     * $oModelItem->setValues(
     *     array(
     *         'nickname' => 'Nickname',
     *         'birth'    => '1.1.2011'
     *     )
     * );
     * try{
     *     $oModelItem->save();
     * }catch(AMI_ModTableItemException $oException){
     *     d::vd($oException->getData());
     * }
     * </code>
     *
     * @param  array $aData  Data array
     * @return AMI_ModTableItem
     * @see    AMI_ModTableItem::__set()
     * @since  5.12.0
     */
    public function setValues(array $aData){
        $aEvent = array(
            'aData'      => &$aData,
            'oTable'     => $this->oTable,
            'oItem'      => $this,
        );
        /**
         * Fires before setting new table item values.
         *
         * @event      on_before_set_values_model_item $modId
         * @eventparam array            aData       New values
         * @eventparam AMI_ModTableItem oItem       Table item model
         */
        AMI_Event::fire('on_before_set_values_model_item', $aEvent, $this->getModId());
        foreach($aData as $name => $value){
            $this->setValue($name, $value);
        }
        return $this;
    }

    /**
     * Sets data array.
     *
     * @param  array $aData    New data array
     * @param  bool  $bAppend  Append data to current data array or set new
     * @param  bool  $bSetPK   Set primary key of present in the data
     * @return AMI_ModTableItem
     * @amidev
     */
    public function setData(array $aData, $bAppend = false, $bSetPK = false){
        if(isset($aData['sm_data'])){
            $aHTMLFields = @unserialize($aData['sm_data']);
            unset($aData['sm_data']);
            if(is_array($aHTMLFields)){
                foreach($this->oTable->getHTMLFields() as $key => $field){
                    if(isset($aHTMLFields[$key])){
                        $aData[$field] = $aHTMLFields[$key];
                    }
                }
                // $aData['is_kw_manual'] = $aHTMLFields['is_kw_manual'];
                // $this->aMeta['auto'] = empty($aHTMLFields['is_kw_manual']);
                $this->aMeta['filled'] = !empty($aHTMLFields['filled']);
            }
        }

        $aEvent = array(
            'aData'      => &$aData,
            'doAppend'   => &$bAppend,
            'oTable'     => $this->oTable,
            'oTableItem' => $this, // @deprecated since 5.14.0
            'oItem'      => $this,
        );
        /**
         * Fires before setting new table item values.
         *
         * @event      on_before_set_data_model_item $modId
         * @eventparam array            aData       New values
         * @eventparam AMI_ModTableItem oItem       Table item model
         * @eventparam bool             &doAppend   Do append values or clear values before setting
         * @eventparam AMI_ModTable     oTable      Table model
         */
        AMI_Event::fire('on_before_set_data_model_item', $aEvent, $this->getModId());
        if($this->oTable->getAttr('doRemapListItems', FALSE)){
            $aData = $bAppend ? array_merge($this->aData, $aData) : $aData;
            $this->aData = array();
            foreach($aData as $name => $value){
                $this->setValue($name, $value);
            }
        }else{
            $this->aData = $bAppend ? array_merge($this->aData, $aData) : $aData;
        }
        if($bSetPK && !empty($this->aData[$this->primaryKeyField])){
            $this->id = $this->aData[$this->primaryKeyField];
        }
        $this->skipSave = false;
        return $this;
    }

    /**
     * Sets data array and remap it.
     *
     * @param  array $aData    New data array
     * @param  bool  $bAppend  Append data to current data array or set new
     * @param  bool  $bSetPK   Set primary key of present in the data
     * @return AMI_ModTableItem
     * @amidev
     */
    public function setDataAndRemap(array $aData, $bAppend = false, $bSetPK = false){
        $this->setData($aData, $bAppend, $bSetPK);
        return $this->setData($this->remapFields($this->aData, false), $bAppend, $bSetPK);
    }

    /**
     * Returns data array.
     *
     * @return array
     */
    public function getData(){
        $aData = array();
        $aNames = array_keys($this->aData);
        foreach(array_keys($this->aFieldCallbacks) as $name){
            $aNames[] = $name;
        }
        $aNames = array_unique($aNames);
        foreach($aNames as $name){
            $value = isset($this->aData[$name]) ? $this->aData[$name] : NULL;
            $this->_useFieldCallback('get', $name, $value);
            $aData[$name] = $value;
        }

        return $aData;
    }

    /**
     * Returns query object.
     *
     * @return DB_Query
     * @amidev Temporary?
     */
    public function getQuery(){
        return $this->oQuery;
    }

    /**
     * Returns raw data array.
     *
     * @return array
     * @amidev
     */
    public function getRawData(){
        return $this->aData;
    }

    /**
     * Returns orig/meta data.
     *
     * @return array
     * @amidev
     */
    public function getMetaData(){
        return
            array(
                'aMeta'     => $this->aMeta,
                'aOrigData' => $this->aOrigData
            );
    }

    /**
     * Generates element sublink.
     *
     * @return AMI_ModTableItem
     * @todo   Implement
     * @amidev Temporary
     */
    public function genSublink(){
        trigger_error('Implement AMI_ModTable::genSublink()', E_USER_ERROR);
        return $this;
    }

    /**
     * Generates HTML meta data.
     *
     * @param  bool $bForce  Overwrite handmade data
     * @return AMI_ModTableItem
     * @todo   Implement
     * @amidev Temporary
     */
    public function genHTMLMeta($bForce = false){
        trigger_error('Implement AMI_ModTable::genHTMLMeta()', E_USER_ERROR);
        return $this;
    }

    /**
     * Changes item position.
     *
     * @param  string $location  One of 'up'|'down'|'top'|'bottom'
     * @return AMI_ModTableItem
     * @todo   Implement
     * @amidev Temporary
     */
    public function changePosition($location){
        trigger_error('Implement AMI_ModTableList::changePosition()', E_USER_ERROR);
        return $this;
    }

    /**
     * Returns module id.
     *
     * @return string
     */
    public function getModId(){
        if(empty($this->modId)){
            $this->modId = AMI::getModId(get_class($this));
        }
        return $this->modId;
    }

    /**
     * Set module id.
     *
     * @param  string $modId  Module Id.
     * @return void
     * @amidev
     */
    public function setModId($modId){
        $this->modId = $modId;
    }

    /**
     * Suppresses fatal error if no page in PM is present when module front links are requested.
     *
     * @return void
     * @amidev
     */
    public function suppressModPageError(){
        $this->suppressModPageError = TRUE;
    }

    /**
     * Generates item full front URL.
     *
     * @return string
     */
    public function getFullURL(){
        $frontLink = $this->getFrontLink();
        $fullUrl = '';
        if($frontLink){
            $fullUrl = $frontLink . $this->getURL();
        }
        return $fullUrl;
    }

    /**
     * Generates item module front link.
     *
     * @return string
     */
    public function getFrontLink(){
        $url = '';
        $isMultilang = AMI::issetAndTrueOption('core', 'allow_multi_lang'); // fast env option
        $isMultipage = AMI::issetAndTrueOption('core', 'multi_page_allowed') && AMI::issetAndTrueOption($this->getModId(), 'multi_page');
        $pageId = 0;
        $lang = $this->getValue('lang');
        if(is_null($lang)){
            trigger_error(
                "No page locale value found for module '" . $this->getModId() .
                "' (" . $this->id . "). You should add `lang' selecting from DB  by calling addNavColumns()",
                E_USER_ERROR
            );
        }
        if($isMultilang){
            $url = $lang . '/';
        }
        if($isMultipage){
            $aNavModNames = $this->getNavModNames();
            $multipageMod = array_shift($aNavModNames);
            $pageId = $this->getValue('id_page');

            if(($this->getModId() != $multipageMod) && $multipageMod){
                // Ask another module
                $aEvent = array(
                    'pageId'     => null,
                    'oTableItem' => $this, // @deprecated since 5.14.0
                    'oItem'      => $this
                );
                /**
                 * Call when receive page ID.
                 *
                 * @event      on_get_id_page $modId
                 * @eventparam int/null         pageId      Page id
                 * @eventparam AMI_ModTableItem oItem       Table item model
                 */
                AMI_Event::fire('on_get_id_page', $aEvent, $multipageMod);
                $pageId = $aEvent['pageId'];
            }
            if(is_null($pageId)){
                if(!$this->suppressModPageError){
                    trigger_error(
                        "No page ID value found for module '" . $multipageMod .
                        "' (" . $this->id . "). You should add `id_page' selecting from DB by calling addNavColumns()",
                        E_USER_ERROR
                    );
                }else{
                    return '';
                }
            }
        }
        if(isset($this->aData['page_sublink']) && $this->aData['page_sublink']){
            $url .= $this->aData['page_sublink'];
        }else{
            $url .= $this->getModLink($lang, $pageId);
        }
        return $url;
    }

    /**
     * Returns link for current module & specific lang& pageID.
     *
     * @param  string $locale  Locale
     * @param  int $pageId  Page id (used for multipage modules)
     * @return string|false
     */
    public function getModLink($locale = 'en', $pageId = 0){
        return AMI_PageManager::getModLink($this->getModId(), $locale, $pageId, $this->suppressModPageError);
    }

    /**
     * Generates item front URL.
     *
     * @return string
     */
    public function getURL(){
        $url = $this->getValue('_url');
        if(is_null($url)){
            $aNavModNames = $this->getNavModNames();

            // TODO. Remove this hack ('catid' should be removed from all stop_arg_names options)
            unset($aNavModNames['catid']);

            $aNavData = array('id_sublink' => $this->getValue('sublink'));
            $aEvent = array(
                'aNavModNames' => &$aNavModNames,
                'aNavData'     => &$aNavData,
                'oTableItem'   => $this, // @deprecated since 5.14.0
                'oItem'        => $this
            );
            /**
             * Called when received the data to generate a reference to the element.
             *
             * @event      on_get_nav_data $modId
             * @eventparam array            aNavModNames  Internal navigation structure
             * @eventparam array            aNavData      Internal navigation data
             * @eventparam AMI_ModTableItem oItem         Table item model
             */
            AMI_Event::fire('on_get_nav_data', $aEvent, $this->getModId());
            $url = '';
            foreach($aNavModNames as $name => $modId){
                if(isset($aNavData[$name . '_sublink']) && $aNavData[$name . '_sublink'] !== ''){
                    $url .= '/' . $aNavData[$name . '_sublink'];
                }else{
                    trigger_error(
                        "No sublink value found for module '" . $modId .
                        "' (" . $this->id . "). You should add `sublink' selecting from DB by calling addNavColumns()",
                        E_USER_ERROR
                    );
                }
            }
            $this->setValue('_url', $url);
        }
        return $url;
    }

    /**
     * Returns validators.
     *
     * Format example:
     * <code>
     * array(
     *     'field1' => array('validator1', 'validator2', ...),
     *     'field2' => ...
     * )
     * </code>
     *
     * @return array
     * @todo   Complete example
     * @amidev
     */
    public function getValidators(){
        return $this->oTable->getValidators();
    }

    /**
     * Returns validator exception object validator after save or null.
     *
     * Facade method for AMI_ModTableItemModifier::getValidatorException()
     *
     * @return AMI_ModTableItemException|null
     */
    public function getValidatorException(){
        return is_null($this->oTableItemModifier) ? null : $this->getModifier()->getValidatorException();
    }

    /**
     * Remaps table fields in array to/from table standard.
     *
     * @param  array $aData       Array of fields where keys would be remapped
     * @param  bool  $bToAliases  True form remapping from table to code and false otherwise
     * @return array
     * @amidev
     */
    public function remapFields(array $aData, $bToAliases = true){
        $aRemap = $this->oTable->getFieldsRemap();
        foreach($aRemap as $field => $value){
            if($value == AMI_ModTable::FIELD_DOESNT_EXIST){
                unset($aRemap[$field]);
            }
        }
        if(sizeof($aRemap) > 0){
            if(!$bToAliases){
                $aRemap = array_flip($aRemap);
            }
            foreach($aRemap as $fieldName => $fieldAlias){
                if($fieldName !== $fieldAlias && array_key_exists($fieldName, $aData)){
                    $aData[$fieldAlias] = $aData[$fieldName];
                    unset($aData[$fieldName]);
                }
            }
        }
        return $aData;
    }

    /**
     * Returns array of navigation data.
     *
     * @return array
     */
    private function getNavModNames(){
        $aNavModNames = array();
        $modId = $this->getModId();
        if(AMI::issetOption($this->getModId(), 'stop_arg_names')){
            $aNavModNames = AMI::getOption($this->getModId(), 'stop_arg_names');
        }elseif(preg_match('~^(.+)_cat$~', $modId, $aMatches) && AMI::isModInstalled($aMatches[1] . '_item') && AMI::issetOption($aMatches[1] . '_item', 'stop_arg_names')){
            $aNavModNames = AMI::getOption($aMatches[1] . '_item', 'stop_arg_names', 'stop_arg_names');
        }
        return $aNavModNames;
    }

    /**
     * Returns table object.
     *
     * @return AMI_ModTable
     */
    public function getTable(){
        return $this->oTable;
    }

    /**
     * Returns table object.
     *
     * @return AMI_ModTable
     * @amidev Temporary
     */
    public function getSerializedFields(){
        return $this->aSerializedFields;
    }

    /**
     * Returns virtual fields i.e. file data.
     *
     * @param  string $type  Field type
     * @return array
     * @amidev Temporary?
     */
    public function getVirtualFields($type = null){
        $this->fieldFilter = $type;
        return
            is_null($type)
                ? $this->aVirtualFields
                : array_filter($this->aVirtualFields, array($this, 'cbFilterVirtualFields'));
    }

    /**
     * Sets field type, i.e. file type support.
     *
     * Example:
     * <code>
     * // AMI_ModTableItem::__construct()
     * $this->setFieldType(
     *     'file',
     *     'file',
     *     array(
     *         'path'     => '_local/plugins/' . $this->getModId() . '/',
     *         'aMapping' => array('cf_fld' => 'name')
     *     )
     * );
     * </code>
     *
     * @param  string $field    Field name
     * @param  string $type     Field type
     * @param  array  $aParams  Extra params
     * @amidev Temporary
     * @return AMI_ModTableItem
     */
    protected function setFieldType($field, $type, array $aParams = array()){
        $this->aVirtualFields[$field] = array('type' => $type, 'oObj' => null, 'aParams' => $aParams);
        if(empty($this->aFieldCallbacks[$field])){
            $this->setFieldCallback($field, array($this, 'fcbVirtual' . ucfirst($type)));
            if($type == 'file'){
                $this->oTable->addValidators(array($field => array('file')));
            }
        }
        return $this;
    }

    /**
     * Returns table item modifier object.
     *
     * @return AMI_ModTableItemModifier
     */
    protected function getModifier(){
        if(is_null($this->oTableItemModifier)){
            if(!AMI::isResource($this->getModId() . '/table/model/item/modifier')){
                AMI::addModResources($this->getModId(), 'table', array('model/item/modifier'));
            }
            $this->oTableItemModifier = AMI::getResourceModel($this->getModId() . '/table/model/item/modifier', array($this->oTable, $this));
        }
        return $this->oTableItemModifier;
    }

    /**
     * Calls field callbacks.
     *
     * @param  string $action  Action 'get'|'set'|'save'
     * @param  string $name    Field name
     * @param  mixed  &$value  Field value
     * @return bool            False if field couldn't be stored in model
     * @see    AMI_ModTableItem::setFieldCallback()
     * @todo   Complete, document in API rererence
     */
    private function _useFieldCallback($action, $name, &$value){
        $res = TRUE;

        if(isset($this->aFieldCallbacks[$name])){
            $aData = array(
                'modId'      => $this->getModId(),
                'action'     => $action,
                'name'       => $name,
                'value'      => &$value,
                'oItemModel' => $this, // @deprecated since 5.14.0
                'oItem'      => $this
            );
            $callback = $this->aFieldCallbacks[$name];
            if(is_array($callback) && $callback[0] === '-'){
                $callback[0] = $this;
            }
            $aData = call_user_func($callback, $aData);
            if(!empty($aData['_skip'])){
                $res = FALSE;
            }
        }

        return $res;
    }

    /**
     * Auto HTML-entities field callback.
     *
     * @param  array $aData  Formatter data
     * @return array
     */
    private function fcbHTMLEntities(array $aData){
        $action = $aData['action'];

        switch($action){
            case 'get':
                $aData['value'] = AMI_Lib_String::unhtmlEntities($aData['value']);
                break;
            case 'set':
                $aData['value'] = AMI_Lib_String::htmlChars($aData['value']);
                break;
        }

        return $aData;
    }

    /**
     * Serialized field callback.
     *
     * @param  array $aData  Formatter data
     * @return array
     * @since  6.0.6
     */
    protected function fcbSerialized(array $aData){
        $action = $aData['action'];
        $name = $aData['name'];
        $this->aSerializedFields[$name] = $name;

        switch($action){
            case 'get':
                $aData['value'] = null;
                if(isset($this->aData[$name])){
                    if(is_string($this->aData[$name])){
                        $this->aData[$name] = unserialize($this->aData[$name]);
                    }
                    $aData['value'] = $this->aData[$name];
                }
                break;
            case 'save':
                $aData['value'] = null;
                if(isset($this->aData[$name])){
                    $this->aData[$name] = serialize($this->aData[$name]);
                    $aData['value'] = $this->aData[$name];
                }
                break;
        }

        return $aData;
    }

    /**
     * File type field callback.
     *
     * @param  array $aData  Formatter data
     * @return array
     * @see    AMI_ModTableItem::setFieldType()
     */
    private function fcbVirtualFile(array $aData){
        if(isset($this->aVirtualFields[$aData['name']])){
            $name = $aData['name'];
            $aParams = $this->aVirtualFields[$name]['aParams'];
            $action = $aData['action'];

            switch($action){
                case 'get':
                    if(empty($this->aVirtualFields[$name]['oObj'])){
                        $factoryResId =
                            isset($aParams['factoryResId'])
                                ? $aParams['factoryResId']
                                : 'env/file';
                        $type =
                            isset($aParams['type'])
                                ? $aParams['type']
                                : 'local';
                        $aFileParams = array(
                            'path'        => $aParams['path'],
                            'name'        => null,
                            'realName'    => null,
                            'type'        => null,
                            'contentType' => null
                        );
                        if(isset($aParams['aMapping'])){
                            foreach($aParams['aMapping'] as $field => $param){
                                if(array_key_exists($field, $this->aData)){
                                    $aFileParams[$param] = $this->aData[$field];
                                }
                            }
                        }
                        $this->aVirtualFields[$name]['oObj'] =
                            AMI::getResource($factoryResId)
                                ->get($aFileParams);
                    }
                    $aData['value'] = $this->aVirtualFields[$name]['oObj'];
                    break;
                case 'set':
                    if(is_object($aData['value'])){
                        $this->aVirtualFields[$name]['oObj'] = $aData['value'];
                    }elseif(isset($aParams['aMapping'])){
                        // cleanup data
                        $aValidators = $this->getValidators();
                        foreach(array_keys($aParams['aMapping']) as $field){
                            if(isset($aValidators[$field])){
                                $this->aData[$field] =
                                    in_array('required', $aValidators[$field])
                                        ? ''
                                        : NULL;
                            }else{
                                $this->aData[$field] = NULL;
                            }
                        }
                    }
                    break;
            }
        }

        return $aData;
    }

    /**
     * Returns loaded fields.
     *
     * @return array
     * @see    AMI_ModTableItem::addFields()
     * @amidev
     */
    public function getLoadedFields(){
        return $this->aFields;
    }

     /**
      * Filters virtual fields by type.
      *
      * @param  array $aField  Field data
      * @return bool
      * @see    AMI_ModFormView::getVirtualFields()
      */
     private function cbFilterVirtualFields(array $aField){
         return $this->fieldFilter === $aField['type'];
     }
}

/**
 * Common module table item model.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @since      5.14.4
 */
class AMI_Module_TableItem extends AMI_ModTableItem{
    /**
     * Common fields validators
     *
     * @var array
     */
    protected $aCommonFieldsValidators =
        array(
            'lang'         => array('filled'),
            'header'       => array('filled', 'stop_on_error'),
            'announce'     => array('filled', 'stop_on_error'),
            'date_created' => array('filled', 'stop_on_error'),
            'body'         => array('required')
        );

    /**
     * Initializing table item data.
     *
     * @param AMI_ModTable $oTable  Module table model
     * @param DB_Query     $oQuery  Required for load or save operations
     * @amidev
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery = null){
        parent::__construct($oTable, $oQuery);

        $this->oTable->addValidators($this->aCommonFieldsValidators);
        $this->setFieldCallback('header', array($this, 'fcbHTMLEntities'));
    }
}
