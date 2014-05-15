<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ArrayIterator.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.14.8
 */

/**
 * Module array iterator model.
 *
 * Usage example:
 * <code>
 * require 'ami_env.php';
 *
 * $oResponse = AMI::getSingleton('response');
 * $oResponse->start();
 *
 * $modId = 'my_module';
 *
 * // Make abstract classes children
 * class MyModule_Table extends AMI_ArrayIterator{}
 * class MyModule_TableItem extends AMI_ArrayIteratorItem{}
 * class MyModule_TableList extends AMI_ArrayIteratorList{}
 *
 * // Add resource mapping
 * $aResources = array(
 *     $modId . '/table/model'      => 'MyModule_Table',
 *     $modId . '/table/model/item' => 'MyModule_TableItem',
 *     $modId . '/table/model/list' => 'MyModule_TableList'
 * );
 * AMI::addResourceMapping($aResources);
 *
 * // Raw list data
 * $aList = array(
 *     // First row
 *     array('id' => 1, 'col1' => 'val11', 'col2' => 'val21', 'col3' => 'val31'),
 *     // Second row
 *     array('id' => 2, 'col1' => 'val12', 'col2' => 'val22', 'col3' => 'val32'),
 *     // same as row id =              1                  2                  -
 *     array('id' => 3, 'col1' => 'val11', 'col2' => 'val22', 'col3' => 'val33'),
 *     // ...
 * );
 *
 * // Iterator object passing raw list data
 * $oIterator = AMI::getResourceModel($modId . '/table', array($aList));
 *
 * // Object loaded from row having id = 2
 * $oItem = $oIterator->find(2);
 * $oResponse->write("<p>Item data having 'id' = 2</p>");
 * $oResponse->write('<pre>' . d::getDumpAsString($oItem->getData()) . '</pre>');
 *
 * $oItem = $oIterator->findByFields(array('col2' => 'val21'));
 * $oResponse->write("<p>Item data having 'col2' = 'val21'</p>");
 * $oResponse->write('<pre>' . d::getDumpAsString($oItem->getData()) . '</pre>');
 *
 * $oResponse->write("<p>Searching by column 'col1' = 'val11'</p>");
 * $oList = $oIterator->getList()->addSearchCondition(array('col1' => 'val11'))->load();
 * foreach($oList as $oItem){
 *     $oResponse->write('<pre>' . d::getDumpAsString($oItem->getData()) . '</pre>');
 * }
 *
 * $oResponse->send();
 * </code>
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @since      5.14.8
 */
abstract class AMI_ArrayIterator implements AMI_iModTable{
    /**
     * Initial data array
     *
     * @var array
     */
    protected $aData = array();

    /**
     * Array of fields
     *
     * @var array
     */
    protected $aFields = array();

    /**
     * Module id
     *
     * @var string
     * @see AMI_ArrayIterator::getModId()
     */
    private $modId = '';

    /**
     * Initializing array iterator data.
     *
     * @param array $aData  Initial data
     */
    public function __construct(array $aData = array()){
        $this->aData = $aData;
        if(sizeof($this->aData)){
            $this->aFields = array_keys($this->aData[0]);
        }
    }

    /**
     * Set module id.
     *
     * @param mixed $id  Module Id.
     * @return void
     */
    public function setModId($id){
        $this->modId = $id;
    }

    /**
     * Returns array of row fields.
     *
     * @return array
     */
    public function getTableFieldsData(){
        return $this->aFields;
    }

    /**
     * Returns initial data array.
     *
     * @return array
     */
    public function getData(){
        return $this->aData;
    }

    /**
     * Returns item model object.
     *
     * @param  string $type  Not implemented here
     * @return AMI_ArrayIteratorItem
     * @todo   Implement or avoid $type argoment
     */
    public function getItem($type = ''){
        $aEvent = array(
            'modId'  => $this->getModId(),
            'oTable' => $this
        );
        /**
         * Called when receive array of validators, allow add own validators.
         *
         * @event      on_table_get_item $modId
         * @eventparam string            modId   Module Id
         * @eventparam AMI_ArrayIterator oTable  Iterator model
         */
        AMI_Event::fire('on_table_get_item', $aEvent, $this->getModId());
        $oItem = AMI::getResourceModel($this->getModId() . '/table/model/item', array($this, array()));
        $aEvent['oTableItem'] = $oItem; // @deprecated since 5.14.0
        $aEvent['oItem'] = $oItem;
        /**
         * Called when receive array of validators, allow add own validators.
         *
         * @event      on_table_get_item_post $modId
         * @eventparam string modId          Module  Id
         * @eventparam AMI_ArrayIterator     oTable  Iterator model
         * @eventparam AMI_ArrayIteratorItem oItem   Iterator item model
         */
        AMI_Event::fire('on_table_get_item_post', $aEvent, $this->getModId());
        return $oItem;
    }

    /**
     * Returns list model object.
     *
     * @param  string $type  Not implemented here
     * @return AMI_ArrayIteratorList
     * @todo   Implement or avoid $type argoment
     */
    public function getList($type = null){
        $aEvent = array(
            'modId'  => $this->getModId(),
            'oTable' => $this
        );
        /**
         * Called before list request.
         *
         * @event      on_table_get_list $modId
         * @eventparam string            modId   Module Id
         * @eventparam AMI_ArrayIterator oTable  Iterator model
         */
        AMI_Event::fire('on_table_get_list', $aEvent, $this->getModId());
        $oList = AMI::getResourceModel($this->getModId() . '/table/model/list', array($this, $type));
        return $oList;
    }

    /**
     * Returns item model object and load data for primary key field param.
     *
     * @param  mixed $id       Primary key value
     * @param  array $aFields  Fields to load
     * @return AMI_ArrayIteratorItem
     * @see    AMI_ArrayIteratorItem::addFields() for $aFields parameter explanation
     */
    public function find($id, array $aFields = array()){
        return $this->findByFields(array('id' => $id), $aFields);
    }

    /**
     * Returns item model object and load data for non-primary key field.
     *
     * @param  array $aSearchCondition  Filter array key => value
     * @param  array $aFields           Fields to load
     * @return AMI_ArrayIteratorItem
     * @see    AMI_ArrayIteratorItem::addFields() for $aFields parameter explanation
     */
    public function findByFields(array $aSearchCondition, array $aFields = array()){
        $oList = $this->getList()->addSearchCondition($aSearchCondition)->load();
        $oItem = null;
        if($oList->count()){
            $oList->rewind();
            $oItem = $oList->current();
        }
        return $oItem;
    }

    /**
     * Checks if model has a field.
     *
     * See {@link PlgAJAXResp::initModel()} for usage example.
     *
     * @param  string $name  Field name in array
     * @param  bool $bAppendEventFields  Null
     * @return bool
     */
    public function hasField($name, $bAppendEventFields = null){
        return in_array($name, $this->aFields);
    }

    /**
     * Necessary for AMI_ModTableList compatibility.
     *
     * @param  string $alias   Alias
     * @param  string $prefix  Prefix
     * @return null
     */
    public function getFieldName($alias, $prefix = ''){
        return $prefix . $alias;
    }

    /**
     * Returns the array of available fields.
     *
     * Dependent model fields returns model alias as index and array of its fields as value.
     *
     * @param  bool $bAppendEventFields  Null
     * @return array
     */
    public function getAvailableFields($bAppendEventFields = null){
        return $this->aFields;
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
}

/**
 * Module array item model.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @since      5.14.8
 */
abstract class AMI_ArrayIteratorItem implements AMI_iFormModTableItem, AMI_iModTableItem{
    /**
     * Primary key field name
     *
     * @var string
     */
    protected $primaryKeyField = 'id';

    /**
     * Iterator model
     *
     * @var AMI_ArrayIterator
     */
    protected $oIterator;

    /**
     * Element id value
     *
     * @var int|string
     */
    protected $id;

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
     * @see AMI_ArrayIteratorItem::addFields()
     */
    protected $aFields = array();

    /**
     * Field callbacks
     *
     * @var array
     */
    protected $aFieldCallbacks = array();

    /**
     * Item search conditions
     *
     * @var array
     */
    protected $aCondition = array();

    /**
     * Initializing array item data.
     *
     * @param  AMI_ArrayIterator $oIterator  Array iterator model
     * @param  array $aData                  Set of data
     * @param  array $aFields                Set of fields
     */
    public function __construct(AMI_ArrayIterator $oIterator, array $aData = array(), array $aFields = array()){
        if(!($oIterator instanceof AMI_ArrayIterator)){
            trigger_error("First argument must be an instance of AMI_ArrayIterator class", E_USER_ERROR);
        }
        $this->oIterator = $oIterator;
        $this->aFields = sizeof($aFields) ? $aFields : $oIterator->getAvailableFields();
        $this->aData = $aData;
        $this->id =
            isset($aData[$this->getPrimaryKeyField()])
                ? $aData[$this->getPrimaryKeyField()]
                : FALSE;
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
        unset($this->aData[$name]);
        $this->rewind();
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
     * Returns primary key field name.
     *
     * @return string
     */
    public function getPrimaryKeyField(){
        return $this->primaryKeyField;
    }

    /**
     * Loads data.
     *
     * @return AMI_ArrayIteratorItem
     */
    public function load(){
        return $this;
    }

    /**
     * Sets field callback on AMI_ArrayIteratorItem::__get() and AMI_ArrayIteratorItem::__set().
     *
     * Used for computational/virtual fields or to convert field value from/to internal/external format.
     *
     * @param  string   $field     Field name
     * @param  callback $callback  Formatter callback
     * @return AMI_ArrayIteratorItem
     * @see    AMI_ArrayIteratorItem::__get()
     * @see    AMI_ArrayIteratorItem::__set()
     */
    public function setFieldCallback($field, $callback){
        $this->aFieldCallbacks[$field] = $callback;
        return $this;
    }

    /**
     * Checks if the field has a specified callback.
     *
     * @param  string   $field     Field name
     * @param  callback $callback  Formatter callback
     * @return bool
     */
    public function hasFieldCallback($field, $callback){
        if(isset($this->aFieldCallbacks[$field])){
            $aCallback = $this->aFieldCallbacks[$field];
            return isset($aCallback[1]) && ($aCallback[1] === $callback);
        }
        return false;
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
     * @return AMI_ArrayIteratorItem
     */
    public function setValue($name, $value){
        if(isset($this->aData[$name])){
            $this->aData[$name] = $value;
            $this->_useFieldCallback('set', $name, $value);
        }
        return $this;
    }

    /**
     * Sets model values from array.
     *
     * @param  array $aData  Data array
     * @return AMI_ArrayIteratorItem
     * @see    AMI_ArrayIteratorItem::__set()
     */
    public function setValues(array $aData){
        foreach($aData as $aRow){
            if(sizeof($aRow)){
                foreach($aRow as $name => $value){
                    $this->setValue($name, $value);
                }
            }
        }
        return $this;
    }

    /**
     * Returns data array.
     *
     * @return array
     */
    public function getData(){
        $aData = array();
        foreach($this->aData as $name => $value){
            $this->_useFieldCallback('get', $name, $value);
            $aData[$name] = $value;
        }
        if(!isset($aData[$this->primaryKeyField])){
            $aData[$this->primaryKeyField] = FALSE;
        }
        return $aData;
    }

    /**
     * Returns raw data array.
     *
     * @return array
     */
    public function getRawData(){
        return $this->aData;
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
     * @param mixed $id  Module Id.
     * @return void
     */
    public function setModId($id){
        $this->modId = $id;
    }

    /**
     * Adds item search condition.
     *
     * @param array $aCondition  Item search condition - array(field => value)
     *
     * @return AMI_ArrayIteratorItem
     * @see    AMI_ArrayIteratorItem::load()
     */
    public function addSearchCondition(array $aCondition){
        $this->aCondition += $aCondition;
        return $this;
    }

    /**
     * Returns validators.
     *
     * @return array
     */
    public function getValidators(){
        return null;
    }

    /**
     * Returns validator exception object validator after save or null.
     *
     * @return AMI_ModTableItemException|null
     */
    public function getValidatorException(){
        return null;
    }

    /**
     * Calls field callbacks.
     *
     * @param  string $action  Action 'get'|'set'
     * @param  string $name    Field name
     * @param  mixed  &$value  Field value
     * @return bool            False if field couldn't be stored in model
     * @see    AMI_ArrayIteratorItem::setFieldCallback()
     */
    protected function _useFieldCallback($action, $name, &$value){
        $res = true;
        if(isset($this->aFieldCallbacks[$name])){
            $aData = array(
                'modId'      => $this->getModId(),
                'action'     => $action,
                'name'       => $name,
                'value'      => &$value,
                'oItemModel' => $this, // @deprecated since 5.14.0
                'oItem'      => $this
            );
            $aData = call_user_func($this->aFieldCallbacks[$name], $aData);
            if(!empty($aData['_skip'])){
                $res = false;
            }
        }
        return $res;
    }
}

/**
 * Module iterator list model.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @since      5.14.8
 */
abstract class AMI_ArrayIteratorList implements AMI_iModTableList, Iterator{
    /**
     * AMI_ArrayIterator object
     *
     * @var AMI_ArrayIterator
     */
    protected $oIterator;

    /**
     * Recordset columns
     *
     * @var array
     */
    protected $aColumns = array();

    /**
     * Raw data array
     *
     * @var array
     */
    protected $aRaw = array();

    /**
     * Processed data array
     *
     * @var array
     */
    protected $aData = array();

    /**
     * Primary keys array
     *
     * @var array
     */
    protected $aKeys = array();

    /**
     * Current iterator element index
     *
     * @var int
     */
    protected $dataIndex = 0;

    /**
     * Start read position
     *
     * @var int
     */
    protected $start = 0;

    /**
     * Limit read
     *
     * @var int
     */
    protected $limit = 10;

    /**
     * Number of found rows
     *
     * @var int
     */
    protected $total = 0;

    /**
     * Default list mask
     *
     * @var string
     */
    protected $mask = '*';

    /**
     * Field to sort by
     *
     * @var string
     */
    protected $sortField;

    /**
     * Sort direction (PHP constant)
     *
     * @var int
     */
    protected $sortDirection;

    /**
     * Search condition
     *
     * @var array
     */
    protected $aCondition;

    /**
     * Search position
     *
     * @var mixed
     */
    protected $position;

    /**
     * Initializing array data.
     *
     * @param AMI_ArrayIterator $oIterator  Array iterator model
     * @param string $mask                  Search mask
     */
    public function __construct(AMI_ArrayIterator $oIterator, $mask = null){
        if(!is_null($mask)){
            $this->mask = $mask;
        }
        $this->oIterator = $oIterator;
        $aEvent = array(
            'modId'  => $this->getModId(),
            'oList'  => $this,
            'oTable' => $oIterator,
            'oQuery' => null
        );
        /**
         * Called when list initialization.
         *
         * @event      on_list_init $modId
         * @eventparam string modId  Module Id
         * @eventparam AMI_ArrayIteratorList oList  List Iterator
         * @eventparam AMI_ArrayIterator oTable  Iterator
         * @eventparam DB_Query oQuery  DBQuery object
         */
        AMI_Event::fire('on_list_init', $aEvent, $this->getModId());
    }

    /**
     * Loads data from array and init recordset.
     *
     * @return AMI_ArrayIteratorList
     */
    public function load(){
        $aEvent = array(
            'modId' => $this->getModId(),
            'oList' => $this,
        );
        /**
         * Allows to manipulate with elements list's request (object of class DB_Query).
         *
         * @event      on_list_recordset $modId
         * @eventparam string modId  Module Id
         * @eventparam AMI_ArrayIteratorList oList  --
         */
        AMI_Event::fire('on_list_recordset', $aEvent, $this->getModId());

        $this->aRaw = $this->oIterator->getData();
        $this->filterByConditions();
        $this->total = sizeof($this->aRaw);
        $this->sortList();
        $this->storeKeys();
        $this->loadCurrentPage();
        $this->seek($this->start);
        $this->aRaw = false;
        return $this;
    }

    /**
     * Filter raw data array by conditions.
     *
     * @param  mixed $position  Needle position
     * @return void
     */
    protected function filterByConditions($position = false){
        if(!$this->aCondition){
            return;
        }
        $this->position = $position;
        $this->aRaw = array_filter($this->aRaw, array($this, '_filterCallback'));
        $this->aRaw = array_values($this->aRaw);
    }

    /**
     * Filter callback function.
     *
     * @param  array $aItem  Array item
     * @return bool
     * @see    AMI_ArrayIteratorList::filterByConditions()
     */
    protected function _filterCallback(array $aItem){
        $countTotal = sizeof($this->aCondition);
        $foundTotal = 0;
        foreach($this->aCondition as $key => $value){
            if(isset($aItem[$key])){
                $isFound = mb_stripos($aItem[$key], $value, 0, 'utf-8');
                if(
                    $isFound !== FALSE &&
                    (
                        ($this->position !== FALSE && $isFound == $this->position) ||
                        $this->position === FALSE
                    )
                ){
                    $foundTotal++;
                }
            }
        }
        return $countTotal === $foundTotal;
    }

    /**
     * Fills array aData with the current page items.
     *
     * @return void
     */
    public function loadCurrentPage(){
        for($i = $this->start; $i < ($this->start + $this->limit); $i++){
            if(isset($this->aRaw[$i])){
                $this->aData[$i] =
                    AMI::getResourceModel(
                        $this->getModId() . '/table/model/item',
                        array($this->oIterator, $this->aRaw[$i])
                    );
            }
        }
    }

    /**
     * Sorts aData list by field.
     *
     * @param  string $sortField  Field to sort by
     * @param  string $sort       Sort type
     * @return void
     */
    public function sortList($sortField = '', $sort = null){
        if(is_null($sort)){
            $sort = version_compare(PHP_VERSION, '5.3.0', '>=') ? SORT_LOCALE_STRING : SORT_STRING;
        }
        if($this->sortField || $sortField){
            AMI_Lib_Array::sortMultiArray(
                $this->aRaw,
                $sortField ? $sortField : $this->sortField,
                $sort,
                $this->sortDirection
            );
        }
    }

    /**
     * Forms an array with list keys.
     *
     * @param  string $keyField  Primary key field
     * @return void
     */
    public function storeKeys($keyField = false){
        $this->aKeys = array();
        if(!$keyField){
            $keyField = $this->getPrimaryKeyField();
        }
        foreach($this->aRaw as $aRow){
            if(isset($aRow[$keyField])){
                $this->aKeys[] = $aRow[$keyField];
            }
        }
    }

    /**
     * Returns an array with list keys.
     *
     * @return array
     */
    public function getKeys(){
        return $this->aKeys;
    }

    /**
     * Necessary for AMI_ModTableList compatibility.
     *
     * @param  bool $bAsPrefix  Null
     * @return null
     */
    public function getMainTableAlias($bAsPrefix = null){
        return null;
    }

    /**
     * Necessary for AMI_ModTableList compatibility.
     *
     * @param  bool $bState  Null
     * @return AMI_ArrayIteratorList
     */
    public function addCalcFoundRows($bState = null){
        return $this;
    }

    /**
     * Get the number of found rows when bCalcFoundRows is true.
     *
     * @return AMI_ArrayIteratorList
     */
    public function getNumberOfFoundRows(){
        return $this->total;
    }

    /**
     * Sets order parameters.
     *
     * @param  string $field  Sort field name
     * @param  string $direction  Sort direction
     * @return AMI_ArrayIteratorList
     */
    public function addOrder($field, $direction = ''){
        $this->sortField = $field;
        $this->sortDirection = ($direction == 'desc') ? SORT_DESC: SORT_ASC;
        return $this;
    }

    /**
     * Necessary for AMI_ModTableList compatibility.
     *
     * @param  string $expression  Null
     * @return AMI_ArrayIteratorList
     */
    public function addWhereDef($expression){
        return $this;
    }

    /**
     * Necessary for AMI_ModTableList compatibility.
     *
     * @param  string $name        Column field name
     * @param  string $expression  Expression
     * @param  string $model       Dependent model alias
     * @return AMI_ArrayIteratorList
     */
    public function addExpressionColumn($name, $expression, $model = ''){
        return $this;
    }

    /**
     * Necessary for AMI_ModTableList compatibility.
     *
     * @return bool False everityme
     */
    public function getQuery(){
        return FALSE;
    }

    /**
     * Sets limit parameters.
     *
     * @param  int $start  Start position
     * @param  int $limit  Limit
     * @return AMI_ArrayIteratorList
     */
    public function setLimitParameters($start, $limit){
        $this->start = $start;
        $this->limit = $limit;
        return $this;
    }

    /**
     * Necessary for AMI_ModTableList compatibility.
     *
     * @param  array  $aColumns  Array of column field names
     * @param  string $model     Null
     * @return AMI_ArrayIteratorList
     */
    public function addColumns(array $aColumns, $model = null){
        return $this;
    }

    /**
     * Necessary for AMI_ModTableList compatibility.
     *
     * @return array
     */
    public function getColumns(){
        return array();
    }

    /**
     * Returns array of available fields.
     *
     * Wrapper for {@link AMI_ArrayIterator::getAvailableFields()}.
     *
     * @param  bool $bAppendEventFields  Null
     * @return array
     * @see    AMI_ArrayIterator::getAvailableFields()
     */
    public function getAvailableFields($bAppendEventFields = null){
        return $this->oIterator->getAvailableFields($bAppendEventFields);
    }

    /**
     * Excludes fields not from available list.
     *
     * Wrapper for {@link AMI_ArrayIterator::filterFields()}.
     *
     * @param  array $aFields  Fields
     * @return array
     */
    public function filterFields(array $aFields){
        return $this->oIterator->filterFields($aFields);
    }

    /**
     * Necessary for AMI_ModTableList compatibility.
     *
     * @return AMI_ArrayIteratorList
     */
    public function addNavColumns(){
        return $this;
    }

    /**#@+
     * Iterator interface implementation.
     */

    /**
     * Returns the current element.
     *
     * @return AMI_ArrayIteratorItem
     */
    public function current(){
        return $this->aData[$this->dataIndex];
    }

    /**
     * Returns the key of the current element.
     *
     * @return integer
     */
    public function key(){
        return $this->dataIndex;
    }

    /**
     * Move forward to the next element.
     *
     * @return void
     */
    public function next(){
        $this->dataIndex++;
    }

    /**
     * Rewinds the Iterator to the first element.
     *
     * @return void
     */
    public function rewind(){
        $this->dataIndex = $this->start;
    }

    /**
     * Checks if current position is valid.
     *
     * @return bool
     */
    public function valid(){
        return isset($this->aData[$this->dataIndex]) && ($this->dataIndex < ($this->start + $this->limit));
    }

    /**#@-*/

    /**
     * Seeks to a position.
     *
     * SeekableIterator::seek() implementation.
     *
     * @param  integer $position  The index position to seek to
     * @return void
     */
    public function seek($position){
        $this->dataIndex = $position;
    }

    /**
     * Counts elements of an object.
     *
     * Countable::count() implementation.
     *
     * @return int
     */
    public function count(){
        $limit = $this->start + $this->limit;
        $total = ($this->total > $limit) ? $limit : $this->total;
        return $total - $this->start;
    }

    /**
     * Adds search condition.
     *
     * @param  array $aCondition  List search condition - array(field => value)
     *
     * @return AMI_ArrayIteratorList
     * @see    AMI_ArrayIteratorList::load()
     */
    public function addSearchCondition(array $aCondition){
        foreach($aCondition as $sKey => $sValue){
            $this->aCondition[$sKey] = $sValue;
        }
        return $this;
    }

    /**
     * Sets module id.
     *
     * @param  string $modId  Module Id.
     * @return void
     */
    public function setModId($modId){
        $this->modId = $modId;
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
     * Gets position of the applied element.
     *
     * @param  string $fieldName      Field name to search in
     * @param  integer $appliedValue  Field value of the applied element
     * @param  integer $position      Initial position
     * @return integer
     */
    public function getPosition($fieldName, $appliedValue, $position){
        $this->load();
        $position += array_search($appliedValue, $this->getKeys());
        return $position;
    }

    /**
     * Returns primary key field name.
     *
     * @return string
     */
    public function getPrimaryKeyField(){
        return $this->oIterator->getItem()->getPrimaryKeyField();
    }

    /**
     * Returns sort field.
     *
     * @return string
     */
    public function getSortField(){
        return $this->sortField;
    }

    /**
     * Returns TRUE if list model has passed column .
     *
     * @param  string $column              Column name
     * @param  string $alias               Model alias
     * @param  bool   $bAppendEventFields  See AMI_ArrayIterator::getAvailableFields()
     * @return bool
     */
    public function hasColumn($column, $alias = '', $bAppendEventFields = TRUE){
        return true;
    }
}
