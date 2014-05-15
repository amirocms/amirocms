<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModTableList.php 47576 2014-02-06 06:00:38Z Leontiev Anton $
 * @since     5.10.0
 */

/**
 * Module component list model interface.
 *
 * Use AMI_ModTableList children, interface usage will be described later.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @see        AmiSample_TableList
 * @since      5.10.0
 */
interface AMI_iModTableList{
    /**
     * Loads data from table and init recordset.
     *
     * @return AMI_ModTableList
     */
    public function load();

    /**
     * Sets the SQL_CALC_FOUND_ROWS selection flag.
     *
     * @return this
     */
    public function addCalcFoundRows();

    /**
     * Get the number of found rows when bCalcFoundRows is true.
     *
     * @return AMI_ModTableList
     */
    public function getNumberOfFoundRows();

    /**
     * Adds order.
     *
     * @param  string $field  Field name
     * @param  string $direction  Order
     * @return AMI_ModTableList
     */
    public function addOrder($field, $direction = '');

    /**
     * Sets limit parameters.
     *
     * @param  int $start  Start
     * @param  int $limit  Limit
     * @return AMI_ModTableList
     */
    public function setLimitParameters($start, $limit);

    /**
     * Adds several columns at time for list selection.
     *
     * @param  array  $aColumns  Array of column field names,
     *                           may be in format AMI_ModTable::getAvailableFields()
     * @param  string $model     Dependent model alias
     * @return AMI_ModTableList
     */
    public function addColumns(array $aColumns, $model = '');

    /**
     * Returns columns names that will be selected.
     *
     * @return array
     * @amidev
     */
    public function getColumns();

    /**
     * Returns array of available fields.
     *
     * Wrapper for {@link AMI_ModTable::getAvailableFields()}.
     *
     * @param  bool $bAppendEventFields  Fire 'on_get_available_fields' event to append extension fields
     * @return array
     */
    public function getAvailableFields($bAppendEventFields = true);

    /**
     * Exludes fields not from available list.
     *
     * Wrapper for {@link AMI_ModTable::filterFields()}.
     *
     * @param  array $aFields  Fields
     * @return array
     * @since  5.12.0
     */
    public function filterFields(array $aFields);

    /**
     * Sets module id.
     *
     * @param  string $id  Module Id.
     * @return void
     * @amidev
     */
    public function setModId($id);

    /**
     * Gets position of the applied element.
     *
     * @param  string $fieldName   Field name to search in
     * @param  integer $appliedId  Field value of the applied element
     * @param  integer $position   Initial position
     * @return integer
     */
    public function getPosition($fieldName, $appliedId, $position);
}

/**
 * Module table list model.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @since      5.10.0
 */
abstract class AMI_ModTableList implements AMI_iModTableList, Countable, SeekableIterator{
    /**
     * AMI_ModTable object
     *
     * @var AMI_ModTable
     */
    protected $oTable;

    /**
     * Recordset columns
     *
     * @var array
     */
    protected $aColumns = array();

    /**
     * Expression columns struct
     *
     * @var   array
     * @since 5.14.0
     */
    protected $aExpressionColumns = array();

    /**
     * Do calculate found rows
     *
     * @var bool
     */
    protected $bCalcFoundRows = false;

    /**
     * Number of found rows (works only when bCalcFoundRows is true).
     *
     * @var int
     */
    protected $numberOfFoundRows = 0;

    /**
     * Current list item position.
     *
     * @var int
     * @since 6.0.2
     * @amidev
     */
    protected $itemPosition = 0;

    /**
     * DB_Query object
     *
     * @var DB_Query
     */
    protected $oQuery;

    /**
     * DB recordset
     *
     * @var DB_iRecordset
     */
    protected $oRS;

    /**
     * Binded data
     *
     * @var array
     * @amidev
     */
    protected $aLateDataBindings = array();

    /**
     * Invalid fields autoexclusion flag
     *
     * @var bool
     * @see AMI_ModTableList::setInvalidColumnExclusion()
     * @see AMI_ModTableList::addColumns()
     */
    private $bExcludeInvalidColumns = true;

    /**
     * Initializing table list data.
     *
     * @param AMI_ModTable $oTable  Table model
     * @param DB_Query     $oQuery  DB_Query object, required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery){
        // d::vd('AMI_ModTableList::__construct(' . $this->getModId() . ')');###
        // d::trace();###
        $this->oTable = $oTable;
        $this->oQuery = $oQuery;
        $aEvent = array(
            'modId'  => $this->getModId(),
            'oList'  => $this,
            'oTable' => $oTable,
            'oQuery' => $oQuery
        );
        AMI_Event::fire('on_list_init', $aEvent, $this->getModId());
    }

    /**
     * Destructor.
     *
     * @see   self::cleanupExtensions()
     * @since 6.0.2
     */
    public function __destruct(){
        // d::vd('AMI_ModTableList::__destruct(' . $this->getModId() . ')');###
        // d::trace();###
        $this->oTable = NULL;
        $this->oQuery = NULL;
    }

    /**
     * Returns table object.
     *
     * @return AMI_ModTable
     * @since  5.14.8
     */
    public function getTable(){
        return $this->oTable;
    }

    /**
     * Loads data from table and init recordset.
     *
     * @return AMI_ModTableList
     */
    public function load(){
        $aEvent = array(
            'modId'             => $this->getModId(),
            'oList'             => $this,
            'oQuery'            => $this->oQuery
        );
        /**
         * Called before recordset initialization. Allows to add additional list query parameters.
         *
         * @event      on_list_recordset_loaded $modId
         * @eventparam string           modId              Module Id
         * @eventparam AMI_ModTableList oList              List object
         */
        AMI_Event::fire('on_list_load', $aEvent, $this->getModId());
        $this->addSelectedFields($this->oQuery);
        $this->oRS = $this->getListRecordset();
        if($this->bCalcFoundRows){
            $oDB = AMI::getSingleton('db');
            $this->numberOfFoundRows = $oDB->fetchValue('SELECT FOUND_ROWS()');
            $aEvent += array(
                'numberOfFoundRows' => $this->numberOfFoundRows,
            );
            /**
             * Called after recordset initialization, if included counting the number of entries. Allows obtain a number of records..
             *
             * @event      on_list_recordset_loaded $modId
             * @eventparam string           modId              Module Id
             * @eventparam int              numberOfFoundRows  Number of found rows
             * @eventparam AMI_ModTableList oList              List object (since 5.14.8)
             */
            AMI_Event::fire('on_list_recordset_loaded', $aEvent, $this->getModId());
        }
        if(sizeof($this->aLateDataBindings)){
            $this->execLateDataBinding();
        }
        return $this;
    }

    /**
     * Set the SQL_CALC_FOUND_ROWS selection flag.
     *
     * @param  bool $bState  Flag
     * @return this
     */
    public function addCalcFoundRows($bState = true){
        $this->bCalcFoundRows = $bState;
        return $this;
    }

    /**
     * Get the number of found rows when bCalcFoundRows is true.
     *
     * @return AMI_ModTableList
     */
    public function getNumberOfFoundRows(){
        return $this->numberOfFoundRows;
    }

    /**
     * Adds order.
     *
     * Use 'rand()' for random sorting,
     * field from result of AMI_ModTableList::getAvailableFields() or
     * field from dependent table model in format "{$alias}.{$field}".
     *
     * @param  string $field  Field name
     * @param  string $direction  Order
     * @return AMI_ModTableList
     * @todo   Example
     * @todo   Order replacement functionality for expression columns
     */
    public function addOrder($field, $direction = ''){
        $notByRand = $field !== 'rand()';
        /**
         * Dependent model alias
         *
         * @var string
         */
        $alias = '';
        if(mb_substr($field, 0, 2) === 'i.'){
            $field = mb_substr($field, 2);
        }
        if(strpos($field, '.') === false){
            $oTable = $this->oTable;
        }else{
            list($alias, $field) = explode('.', $field, 2);
            $depResId = $this->oTable->getDependenceResId($alias);
            if(empty($depResId)){
                trigger_error("No dependent model found '" . $alias . "'", E_USER_ERROR);
            }
            $oTable = AMI::getResourceModel($depResId . '/table');
        }
        if($notByRand){
            if(
                !$oTable->hasField($field, true) &&
                $alias != '' &&
                !$this->oTable->getList()->hasColumn($field, $alias)
            ){
                trigger_error("Unknown order field '" . $alias . '.' . $field . "'", E_USER_ERROR);
            }
            $prefix = $alias != '' ? $alias . '.' : $this->getMainTableAlias(true);
        }else{
            $prefix = '';
            $direction = '';
        }
        $field =
                isset($this->aExpressionColumns[$alias]) && isset($this->aExpressionColumns[$alias][$field])
                        ? $this->aExpressionColumns[$alias][$field]
                        : $oTable->getFieldName($field, $prefix);
        $this->oQuery->addOrder($field, $direction);
        return $this;
    }

    /**
     * Adds grouping.
     *
     * @param  DB_Snippet|string $expression  Expression
     * @return AMI_ModTableList
     * @see    DB_Query::addGrouping()
     */
    public function addGrouping($expression){
        $this->checkSnippet($expression);
        $this->oQuery->addGrouping($expression);
        return $this;
    }

    /**
     * Adds where definition.
     *
     * @param  DB_Snippet|string $expression  Expression
     * @return AMI_ModTableList
     * @see    DB_Query::addWhereDef()
     */
    public function addWhereDef($expression){
        $this->checkSnippet($expression);
        $this->oQuery->addWhereDef($expression);
        return $this;
    }

    /**
     * Sets where definition.
     *
     * @param  DB_Snippet|string $expression  Expression
     * @return AMI_ModTableList
     * @see    DB_Query::setWhereDef()
     */
    public function setWhereDef($expression){
        $this->checkSnippet($expression);
        $this->oQuery->setWhereDef($expression);
        return $this;
    }

    /**
     * Adds item search condition.
     *
     * Example:
     * <code>
     * // Load not published news items list
     * $oModelItem =
     *     AMI::getResourceModel('news/table')->getList()
     *         ->addColumns(array('id', 'header', 'announce'))
     *         ->addSearchCondition(
     *             array(
     *                 'public' => 0
     *             )
     *         )
     *         ->load();
     * </code>
     *
     * @param array $aCondition  Item search condition - array(field => value)
     *
     * @return AMI_ModTableItem
     * @since  6.0.2
     * @see    AMI_ModTableList::addWhereDef()
     */
    public function addSearchCondition(array $aCondition){
        foreach($aCondition as $field => $value){
            if($this->oTable->hasField($field)){
                $field = $this->oTable->getFieldName($field);
                $this->addWhereDef(
                    DB_Query::getSnippet(' AND `' . $field . '` = %s')
                        ->q($value)
                );
            }else{
                trigger_error('Unknown field "' . $field . '" in search condition', E_USER_WARNING);
            }
        }
        return $this;
    }


    /**
     * Adds having definition.
     *
     * @param  DB_Snippet|string $expression  Expression
     * @return AMI_ModTableList
     * @see    DB_Query::addHavingDef()
     */
    public function addHavingDef($expression){
        $this->checkSnippet($expression);
        $this->oQuery->addHavingDef($expression);
        return $this;
    }

    /**
     * Sets having definition.
     *
     * @param  DB_Snippet|string $expression  Expression
     * @return AMI_ModTableList
     * @see    DB_Query::setHavingDef()
     */
    public function setHavingDef($expression){
        $this->checkSnippet($expression);
        $this->oQuery->setHavingDef($expression);
        return $this;
    }

    /**
     * Sets limit parameters.
     *
     * @param  int $start  Start
     * @param  int $limit  Limit
     * @return AMI_ModTableList
     * @see    DB_Query::setLimitParameters()
     */
    public function setLimitParameters($start, $limit){
        $this->oQuery->setLimitParameters($start, $limit);
        return $this;
    }

    /**
     * Adds expression to selection.
     *
     * @param  DB_Snippet|string $expression  Expression
     * @return AMI_ModTableList
     * @see    AMI_ModTableList::addExpressionColumn()
     * @deprecated Sinse 5.14.0, use AMI_ModTableList::addExpressionColumn() instead.
     */
    public function addExpressionField($expression){
        ### trigger_error('AMI_ModTableList::addExpressionField() method is deprecated and will be forbidden after 5.12.20 CMS build, please refer to AMI_ModTableList::addExpressionColumn() to get more details', E_USER_WARNING);###
        $this->checkSnippet($expression);
        $this->oQuery->addExpressionField($expression);
        return $this;
    }

    /**
     * Adds expression column to list selection.
     *
     * Example:
     * <code>
     * // ...
     * class DemoModule_TableList extends AMI_ModTableList{
     *     public function __construct(AMI_ModTable $oTable, DB_Query $oQuery){
     *         parent::__construct($oTable, $oQuery);
     *         $this->addExpressionColumn('id2', '(id * 2)');
     *     }
     * }
     * // ...
     * $modId = 'demo_module';
     * $oTable = AMI::getResourceModel($modId . '/table');
     * $oList = $oTable->getList()->addColumn('id2')->load();
     * foreach($oList as $oItem){
     *     AMI::getSingleton('response')->write($oItem->id . ' ' . $oItem->id2 . '<br />');
     * }
     * </code>
     *
     * @param  string            $name   Column field name
     * @param  DB_Snippet|string $expression  Expression
     * @param  string            $model  Dependent model alias
     * @return AMI_ModTableList
     * @since  5.14.0
     */
    public function addExpressionColumn($name, $expression, $model = ''){
        $this->checkSnippet($expression);
        if(empty($this->aExpressionColumns[$model])){
            $this->aExpressionColumns[$model] = array();
        }
        $this->aExpressionColumns[$model][$name] = $expression;
        $this->addColumn($name, $model);
        return $this;
    }

    /**
     * Checks if expression column named $name exist.
     *
     * @param string $name   Column name
     * @param string $model  Dependent model alias
     * @return bool
     * @amidev
     */
    public function hasExpressionColumn($name, $model = ''){
        return isset($this->aExpressionColumns[$model]) && isset($this->aExpressionColumns[$model][$name]);
    }

    /**
     * Returns main table alias.
     *
     * @param  bool $bAsPrefix  Append '.' if true
     * @return string
     * @see    DB_Query::getMainTableAlias()
     */
    public function getMainTableAlias($bAsPrefix = false){
        return $this->oQuery->getMainTableAlias($bAsPrefix);
    }

    /**
     * Adds columns to SELECT from table in DB_Query.
     *
     * @param DB_Query $oQuery  Query
     * @param string   $prefix  Prefix for every field
     * @return void
     */
    public function addSelectedFields(DB_Query $oQuery, $prefix = ''){
        if($this->bCalcFoundRows){
            $oQuery->setPrefix('SQL_CALC_FOUND_ROWS');
        }
        if(sizeof($this->aColumns) > 0){
            $aCols = array();
            $aStructs = array();

            foreach($this->aColumns as $modelAlias => $aColumns){
                if($modelAlias == $prefix){
                    $modelAlias = '';
                }
                $checkExcpressions = isset($this->aExpressionColumns[$modelAlias]);
                if(isset($this->aExpressionColumns[$modelAlias])){
                    $aColumns = array_diff_key($aColumns, $this->aExpressionColumns[$modelAlias]);
                    foreach($this->aExpressionColumns[$modelAlias] as $field => $expression){
                        if($modelAlias != ''){
                            $field = $modelAlias . '_' . $field;
                        }
                        $oQuery->addExpressionField(DB_Query::getSnippet('%s %s')->plain($expression)->plain($field));
                    }
                }
                if(sizeof($aColumns)){
                    if(empty($modelAlias)){
                        $aStructs = array_merge($aStructs, $this->oTable->getColumnsStruct(array_keys($aColumns), $prefix));
                    }else{
                        $resId = $this->oTable->getDependenceResId($modelAlias);
                        if(is_null($resId)){
                            trigger_error('No dependent resource found for alias "'.$modelAlias.'"', E_USER_ERROR);
                        }
                        $aStructs = array_merge($aStructs, AMI::getResourceModel($resId . '/table')->getColumnsStruct(array_keys($aColumns), $modelAlias));
                    }
                }
            }

            foreach($aStructs as $aStruct){
                $oQuery->addField(
                    $aStruct['name'],
                    $aStruct['prefix'],
                    $aStruct['alias']
                );
            }
        }
        /*
                else{
                    // Normal behaviour. Sometime we don't need to fetch
                    // trigger_error('AMI_ModTableList::addSelectedFields() columns are not set', E_USER_ERROR);
                }
        */
    }

    /**
     * Repairs item search hash.
     *
     * @return AMI_ModTableList
     * @todo   Implement
     * @amidev temporary
     */
    /*
        public function repairSearchHash(){
            trigger_error('Implement AMI_ModTableList::repairSearchHash()', E_USER_ERROR);
            return $this;
        }
    */

    /**
     * Repairs item position.
     *
     * @todo   Implement
     * @return AMI_ModTableList
     * @amidev temporary
     */
    /*
        public function repairPositions(){
            trigger_error('Implement AMI_ModTableList::repairPositions()', E_USER_ERROR);
            return $this;
        }
    */

    /**
     * Adds column to list selection.
     *
     * @param  string $name   Column field name
     * @param  string $model  Dependent model alias
     * @return AMI_ModTableList
     */
    public function addColumn($name, $model = ''){
        /*
                if(isset($this->aColumns[$model][$name])){
                    trigger_error("Column with name '" . $name . "' already exists", E_USER_NOTICE);
                }else{
        */
        if(!isset($this->aColumns[$model][$name])){
            if(!isset($this->aColumns[$model])){
                $this->aColumns[$model] = array();
            }
            $this->aColumns[$model][$name] = true;
        }
        return $this;
    }

    /**
     * Adds several columns at time for list selection.
     *
     * @param  array  $aColumns  Array of column field names,
     *                           may be in format AMI_ModTable::getAvailableFields()
     * @param  string $model     Dependent model alias
     * @return AMI_ModTableList
     */
    public function addColumns(array $aColumns, $model = ''){
        if($this->bExcludeInvalidColumns){
            $aColumns = $this->filterFields($aColumns, $model);
        }
        foreach($aColumns as $modelAlias => $name){
            if(is_array($name)){
                $this->addColumns($name, $modelAlias);
            }else{
                $this->addColumn($name, $model);
            }
        }
        return $this;
    }

    /**
     * Specifies to autoexlude or not invalid columns during AMI_ModTableList::addColumns().
     *
     * @param  bool $bExclude  Autoexlude flag
     * @return AMI_ModTableList
     * @since  5.12.0
     */
    public function setInvalidColumnExclusion($bExclude){
        $this->bExcludeInvalidColumns = (bool)$bExclude;
        return $this;
    }

    /**
     * Returns columns names that will be selected.
     *
     * @return array
     * @amidev
     */
    public function getColumns(){
        $aColumns = array();
        foreach($this->aColumns as $aModelColumns){
            $aColumns = array_merge($aColumns, $aModelColumns);
        }
        return $aColumns;
    }

    /**
     * Returns real field name by its alias.
     *
     * Wrapper for {@link AMI_ModTable::getFieldName()}.
     *
     * @param  string $alias  Alias
     * @return string
     * @see    AMI_ModTable::getFieldName()
     * @deprecated After db field names standardization this method will became useless.
     */
    public function getFieldName($alias){
        $tableAlias = $this->getMainTableAlias(true);
        if(strpos($alias, '.') !== false){
            list($tableAlias, $alias) = explode('.', $alias);
            return AMI::getResourceModel($this->oTable->getDependenceResId($tableAlias) . '/table')->getFieldName($alias, $tableAlias . '.');
        }
        return $this->oTable->getFieldName($alias, $tableAlias);
    }

    /**
     * Returns array of available fields.
     *
     * Wrapper for {@link AMI_ModTable::getAvailableFields()}.
     *
     * @param  bool $bAppendEventFields  Fire 'on_get_available_fields' event to append extension fields
     * @return array
     * @see    AMI_ModTable::getAvailableFields()
     */
    public function getAvailableFields($bAppendEventFields = true){
        $aFields = $this->oTable->getAvailableFields($bAppendEventFields);
        if(sizeof($this->aExpressionColumns)){
            foreach($this->aExpressionColumns as $modelAlias => $aExpFields){
                $aExpFields = array_keys($aExpFields);
                if($modelAlias != ''){
                    if(!isset($aFields[$modelAlias])){
                        $aFields[$modelAlias] = array();
                    }
                    foreach($aExpFields as $field){
                        $aFields[$modelAlias][] = $field;
                    }
                }else{
                    foreach($aExpFields as $field){
                        $aFields[] = $field;
                    }
                }
            }
        }

        // Merge active dependencies model fields
        $aDependentAliases = $this->oTable->getActiveDependenceAliases();
        foreach($aDependentAliases as $alias){
            $dependentResId = $this->oTable->getDependenceResId($alias);
            if(!isset($aFields[$alias])){
                $aFields[$alias] = array();
            }
            $aFields[$alias] += AMI::getResourceModel($dependentResId . '/table')->getAvailableFields();
        }

        return $aFields;
    }

    /**
     * Returns TRUE if list model has passed column (including expression columns).
     *
     * @param  string $column              Column name
     * @param  string $alias               Model alias
     * @param  bool   $bAppendEventFields  See AMI_ModTable::getAvailableFields()
     * @return bool
     * @see    AMI_ModTable::getAvailableFields()
     * @since  5.14.0
     */
    public function hasColumn($column, $alias = '', $bAppendEventFields = TRUE){
        $aFields = $this->getAvailableFields($bAppendEventFields);
        return
                $alias == ''
                        ? in_array($column, $aFields)
                        : isset($aFields[$alias]) && in_array($column, $aFields[$alias]);
    }

    /**
     * Returns field name with its alias or expression.
     *
     * @param  string $column              Column name
     * @param  string $alias               Model alias
     * @param  bool   $bAppendEventFields  See AMI_ModTable::getAvailableFields()
     * @return bool
     * @see    AMI_ModTable::addExpressionColumn()
     * @see    AMI_ModTable::getAvailableFields()
     * @since  5.14.0
     */
    public function getColumn($column, $alias = '', $bAppendEventFields = TRUE){
        $result = null;
        if($this->hasColumn($column, $alias, $bAppendEventFields)){
            $result =
                    isset($this->aExpressionColumns[$alias]) && isset($this->aExpressionColumns[$alias][$column])
                            ? $this->aExpressionColumns[$alias][$column]
                            : $this->getFieldName(($alias == '' ? '' : $alias . '.') . $column);
        }
        return $result;
    }

    /**
     * Exludes fields not from available list.
     *
     * Wrapper for {@link AMI_ModTable::filterFields()}.
     *
     * @param  array $aFields      Fields
     * @param  string $modelAlias  Model alias
     * @return array
     * @since  5.12.0
     */
    public function filterFields(array $aFields, $modelAlias = ''){
        if(!$modelAlias){
            return $this->oTable->filterFields($aFields);
        }else{
            if(in_array($modelAlias, $this->oTable->getActiveDependenceAliases())){
                $resId = $this->oTable->getDependenceResId($modelAlias);
                return AMI::getResourceModel($resId . '/table')->filterFields($aFields);
            }
        }
        return array();
    }

    /**
     * Adds navigation columns.
     *
     * @return AMI_ModTableList
     * @see    AMI_ModTable::getNavFields()
     * @see    AMI_ModTableList::addColumns()
     */
    public function addNavColumns(){
        return $this->addColumns($this->oTable->getNavFields());
    }

    /**
     * Returns DB_Query object.
     *
     * @return DB_Query
     * @amidev ???
     */
    public function getQuery(){
        return $this->oQuery;
    }

    /**
     * Deletes column from list selection.
     *
     * @param  string $name   Column field name
     * @param  string $model  Dependent model alias
     * @return AMI_ModTableList
     * @since  5.14.4
     */
    public function dropColumn($name, $model = ''){
        foreach(array('aColumns', 'aExpressionColumns') as $property){
            $entity = &$this->$property;
            if(isset($entity[$model])){
                unset($entity[$model][$name]);
                if(!sizeof($entity[$model])){
                    unset($entity[$model]);
                }
            }
        }
        return $this;
    }

    /**#@+
     * Iterator interface implementation.
     */

    /**
     * Returns the current element.
     *
     * @return AMI_ModTableItem
     */
    public function current(){
        $this->checkRecordset();
        $oItem = AMI::getResourceModel($this->getModId() . '/table/model/item', array($this->oTable, $this->oQuery));
        $aCurItem = $this->oRS->current();
        if(sizeof($this->aLateDataBindings)){
            foreach($this->aLateDataBindings as $field => $aData){
                if(isset($aData['bindEnumerating']['modelIdsField'])){
                    $delim = isset($aData['bindEnumerating']['delimiter']) ? $aData['bindEnumerating']['delimiter'] : ';';
                    $aIds = $this->getBindEnumeratingIDs($aCurItem, $aData);
                    $aCurItem[$field] = '';
                    if(!empty($aIds)){
                        rsort($aIds);
                        foreach($aIds as $id){
                            $aCurItem[$field] .= $aData['result'][$id].$delim;
                        }
                        $aCurItem[$field] = trim($aCurItem[$field], $delim);
                    }
                }else{
                    $aCurItem[$field] =
                        $aCurItem[$aData['modelField']] &&
                        isset($aData['result'][$aCurItem[$aData['modelField']]])
                            ? $aData['result'][$aCurItem[$aData['modelField']]]
                            : $aData['default'];
                }
            }
        }
        $method = $this->oTable->getAttr('doRemapListItems', FALSE) ? 'setDataAndRemap' : 'setData';

        return call_user_func(array($oItem, $method), $aCurItem, FALSE, TRUE);
    }

    /**
     * Returns the key of the current element.
     *
     * @return mixed
     */
    public function key(){
        $this->checkRecordset();
        return $this->oRS->key();
    }

    /**
     * Move forward to next element.
     *
     * @return void
     */
    public function next(){
        $this->checkRecordset();
        $this->oRS->next();
    }

    /**
     * Rewinds the Iterator to the first element.
     *
     * @return void
     */
    public function rewind(){
        $this->checkRecordset();
        $this->oRS->rewind();
    }

    /**
     * Checks if current position is valid.
     *
     * @return bool
     */
    public function valid(){
        $this->checkRecordset();
        return $this->oRS->valid();
    }

    /**#@-*/

    /**
     * Seeks to a position.
     *
     * SeekableIterator::seek() implementation.
     *
     * @param  int $position  The position to seek to
     * @return bool
     */
    public function seek($position){
        $this->checkRecordset();
        return $this->oRS->seek($position);
    }

    /**
     * Counts elements of an object.
     *
     * Countable::count() implementation.
     *
     * @return int
     */
    public function count(){
        $this->checkRecordset();
        return $this->oRS->count();
    }

    /**
     * Forces list to collect page sublinks after all data loaded.
     *
     * Example:
     * <code>
     * $oList = AMI::getResourceModel('news/table')
     *      ->addColumns(array('id','id_page'))
     *      ->requestModLinks()
     *      ->load();
     * foreach($oList as $oItem){
     *      // getFrontLink will use data from the list queries
     *      d::vd($oItem->getFrontLink());
     * }
     * </code>
     *
     * @return AMI_ModTableList
     * @since 5.12.8
     */
    public function requestModLinks(){
        if($this->oTable->hasField('id_page')){
            $oWhereSnippet = DB_Query::getSnippet('AND `module_name` = %s')->q($this->getModId());
            $this->setLateDataBinding('id_page', 'page_sublink', 'pages', 'sublink', $oWhereSnippet);
        }
        return $this;
    }

    /**
     * Sets a dependent model and loads its data to a new column $fieldAlias after the list data was loaded.
     *
     * Example:
     * <code>
     * class MyModule_TableList extends AMI_ModTableList{
     *     // Binds table users with "id=id_user" condition.
     *     // Fills "user_name" column with the values of the "firstname" column.
     *     public function __construct(AMI_ModTable $oTable, DB_Query $oQuery){
     *         parent::__construct($oTable, $oQuery);
     *         $this->setLateDataBinding('id_user', 'user_name', 'users', 'firstname');
     *     }
     * }
     * </code>
     *
     * @param string $modelField            Current model's field name with id's from binded model
     * @param string $fieldAlias            Field name to contain data from binded model
     * @param string $bindedModel           Binded model name e.g. 'users', 'pages', etc.
     * @param string $bindedField           Field of the binded model to get data from
     * @param DB_Snippet $oWhereDefSnippet  Additional addWhereDef snipet [optional]
     * @param string $bindedKey             Field name of the binded model to be used as a foreign key
     * @param string $default               Default value, will be returned if no corresponding row found in binded model
     * @param array $bindEnumerating        Bind IDs enumerating
     * @return AMI_ModTableList
     * @since 5.12.8
     */
    public function setLateDataBinding($modelField, $fieldAlias, $bindedModel, $bindedField, DB_Snippet $oWhereDefSnippet = null, $bindedKey = null, $default = null, array $bindEnumerating = array()){
        // Validate input data
        if(!$this->oTable->hasField($modelField) && !$this->oTable->getList()->hasColumn($modelField)){
            trigger_error('Field `' . $modelField . '` not found in current model ' . $this->getModId() . '/table/list', E_USER_WARNING);
            return $this;
        }
        /**
         * @var AMI_ModTable
         */
        $oForeignTable = AMI::getResourceModel($bindedModel . '/table');
        $sourceBindedField = $bindedField;
        if(is_array($bindedField)){
            list($bindedAlias, $bindedField) = each($bindedField);
        }else{
            $bindedAlias = '';
        }
        if(!$oForeignTable->hasField($bindedField) && !$oForeignTable->getList()->hasColumn($bindedField, $bindedAlias)){
            trigger_error('Field `' . $bindedField . '` not found in a binded model ' . $bindedModel . '/table', E_USER_WARNING);
            return $this;
        }
        if(!is_null($bindedKey)){
            if(!$oForeignTable->hasField($bindedKey) && !$oForeignTable->getList()->hasColumn($bindedKey)){
                trigger_error('Field `' . $bindedKey . '` not found in a binded model ' . $bindedModel . '/table', E_USER_WARNING);
                return $this;
            }
        }else{
            $bindedKey = $oForeignTable->getItem()->getPrimaryKeyField();
        }

        // Save binding
        $this->aLateDataBindings[$fieldAlias] = array(
            'modelField'      => $modelField,
            'bindedModel'     => $bindedModel,
            'bindedField'     => $sourceBindedField,
            'bindedKey'       => $bindedKey,
            'ids'             => array(),
            'result'          => array(),
            'whereSnippet'    => $oWhereDefSnippet,
            'default'         => $default,
            'bindEnumerating' => $bindEnumerating
        );

        return $this;
    }

    /**
     * Sets module id.
     *
     * @param  string $modId  Module Id.
     * @return void
     * @amidev
     */
    public function setModId($modId){
        $this->modId = $modId;
    }

    /**
     * Executes late data binding.
     *
     * @return AMI_ModTableList
     * @amidev
     */
    protected function execLateDataBinding(){
        foreach($this->oRS as $aRecord){
            foreach($this->aLateDataBindings as &$aData){
                if($aRecord[$aData['modelField']]){
                    if(isset($aData['bindEnumerating']['modelIdsField'])){
                        $aIds = $this->getBindEnumeratingIDs($aRecord, $aData);
                        if(!empty($aIds)){
                            foreach($aIds as $id){
                                $aData['ids'][] = $id;
                            }
                        }
                    }else{
                        $aData['ids'][] = $aRecord[$aData['modelField']];
                    }
                }
            }
        }
        $this->oRS->rewind();
        foreach($this->aLateDataBindings as $field => &$aData){
            if(sizeof($aData['ids'])){
                $oBindedModel = AMI::getResourceModel($aData['bindedModel'] . '/table');
                $oBindedList = $oBindedModel->getList()
                    ->addColumns(array($aData['bindedKey'], $aData['bindedField']))
                    ->addWhereDef(
                        DB_Query::getSnippet("AND `%s` IN (%s)")
                        ->plain($aData['bindedKey'])
                        ->implode(array_unique($aData['ids']))
                    );
                if(isset($aData['whereSnippet'])){
                    $oBindedList->addWhereDef($aData['whereSnippet']);
                }
                $oRS = $oBindedList->load();
                foreach($oRS as $oRecord){
                    $aRecord = iterator_to_array($oRecord);
                    if(is_array($aData['bindedField'])){
                        reset($aData['bindedField']);
                        list($bindedAlias, $bindedField) = each($aData['bindedField']);
                        $bindedField = $bindedAlias . '_' . $bindedField;
                    }else{
                        $bindedField = $aData['bindedField'];
                    }
                    $aData['result'][$aRecord[$aData['bindedKey']]] = $aRecord[$bindedField];
                }
            }
        }
        return $this;
    }

    /**
     * Returns bind enumerating IDs.
     *
     * @param array $aRecord  Array of data
     * @param array $aData  Array of bind params
     *
     * @return array
     * @amidev
     */
    protected function getBindEnumeratingIDs(array $aRecord, array $aData){
        $delim = isset($aData['bindEnumerating']['delimiter']) ? $aData['bindEnumerating']['delimiter'] : ';';
        return explode($delim, trim($aRecord[$aData['bindEnumerating']['modelIdsField']], $delim));
    }

    /**
     * Make the query and return recordset for selected fields.
     *
     * @return DB_iRecordset
     */
    protected function getListRecordset(){
        $aEvent = array(
            'modId'  => $this->getModId(),
            'oQuery' => $this->oQuery
        );
        /**
         * Allows to manipulate with elements list's request (object of class DB_Query).
         *
         * @event      on_list_recordset $modId
         * @eventparam string   modId  Module Id
         * @eventparam DB_Query oList  Query list
         */
        AMI_Event::fire('on_list_recordset', $aEvent, $this->getModId());
        $this->_getListRecordset();
        /**
         * @var AMI_iDB
         */
        $oDB = AMI::getSingleton('db');
        return $oDB->select($this->oQuery);
    }

    /**
     * Function for manipulations in DB_Query before selection in AMI_ModTableList::getListRecordset().
     *
     * @return void
     * @see    AMI_ModTableList::getListRecordset()
     * @amidev
     */
    protected function _getListRecordset(){
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
     * Checks recordset initialization.
     *
     * @return void
     */
    private function checkRecordset(){
        if(is_null($this->oRS) || !($this->oRS instanceof DB_iRecordset)){
            trigger_error("Recordset is not initialized, use AMI_ModTableList::load()", E_USER_ERROR);
        }
    }

    /**
     * Checks query snippet.
     *
     * @param  DB_Snippet|string &$snippet  Query snippet
     * @return void
     * @todo   Delete after 5.12.4 and changing quotes warning to fatals
     */
    private function checkSnippet(&$snippet){
        if(!is_object($snippet) && (mb_strpos($snippet, "'") !== false || mb_strpos($snippet, '"') !== false)){
            // trigger_error('Using quotes in queries is forbidden, please refer to DB_Query::getSnippet() to get more details', E_USER_ERROR);
            trigger_error('Quotes are forbidden, please refer to DB_Query::getSnippet() to get more details', E_USER_ERROR);
        }
    }

    /**
     * Gets position of the applied element.
     *
     * @param  string $fieldName      Field name to search in
     * @param  integer $appliedValue  Field value of the applied element
     * @param  integer $position      Initial position
     * @return integer
     */
    public function getPosition($fieldName, $appliedValue, $position) {
        $this->load();
        foreach($this as $oItem){
            if($oItem->$fieldName == $appliedValue){
                break;
            }
            $position++;
        }
        return $position;
    }

    /**
     * Gets list pager parameters.
     *
     * @param  array $aPagerData  Pager data
     * @return array
     * @amidev
     */
    public function getPager(array $aPagerData){
        $aPager = array();
        $pageSize = $aPagerData['pageSize']; // Number of items per page
        $position = $aPagerData['position']; // List position
        $calcPages = $aPagerData['calcPages']; // Calculate first/last pages
        $currentItemOffset = // Current item offset in the 'tape' mode
            isset($aPagerData['currentItemOffset']) ? $aPagerData['currentItemOffset'] : 0;

        if(($this->numberOfFoundRows <= $pageSize) || ($pageSize <= 0)){
            return $aPager;
        }
        $pagesCount = $this->getPagesCount($pageSize);
        $activePage = $this->getActivePage($pageSize, ($aPagerData['mode'] == "tape") ? $this->itemPosition : $position);

        $maxPagesCount = AMI::getOption('core', 'default_page_size');
        $visPagesCount = min(ceil($this->numberOfFoundRows / $pageSize), $maxPagesCount ? $maxPagesCount : 10);
        $visMiddle = ceil($visPagesCount / 2);
        $visStartPage = 0;
        $visEndPage = -1;

        if($activePage >= ($visStartPage + $visMiddle)){
            $visStartPage = $activePage - $visMiddle + 1;
        }
        if($visStartPage + $visPagesCount > $pagesCount){
            $visStartPage = $pagesCount - $visPagesCount;
        }

        $tapePosition = 0;
        $forceViewStartLink = false;
        $forceViewEndLink = !empty($aPagerData['forceViewEndLink']);

        if($aPagerData['mode'] == "tape"){
            if($aPagerData['tapePosition'] > 0){
                $itemPosition = $this->itemPosition % $pageSize;

                if($this->itemPosition >= $aPagerData['tapePosition']){
                    $forceViewStartLink = true;
                }

                if(($this->numberOfFoundRows - $this->itemPosition) > $aPagerData['tapePosition']){
                    $forceViewEndLink = true;
                    $endLinkOffset = $this->numberOfFoundRows - 1;
                }

                if($currentItemOffset < $aPagerData['tapePosition'] - 1){
                    $tapePosition = $aPagerData['tapePosition'] - 1;
                }else{
                    $itemPosition = $this->itemPosition % $pageSize;
                    if($this->itemPosition < $pageSize){
                        $tapePosition = $aPagerData['tapePosition'] - 1 + max(0, ($itemPosition - $aPagerData['tapePosition'] + 1));
                    }else{
                        $tapePosition = $itemPosition;
                    }
                }
            }else{
                $tapePosition = $currentItemOffset;
                $endLinkOffset = $this->numberOfFoundRows - 1;
            }
        }else{
            $endLinkOffset = min($this->numberOfFoundRows - 1, ($pagesCount - 1) * $pageSize + $tapePosition);
        }

        if($forceViewStartLink || ($activePage > 0 && $visStartPage > 0)){
            $aPager[] = array("type" => "first", "pagenum" => 1, "start" => 0);
        }
        if(($activePage > 0)){
            $aPager[] = array(
                "type" => "prev",
                "pagenum" => "",
                "start" => (($activePage - 1) * $pageSize + $tapePosition)
            );
        }
        for($i = $visStartPage; $i < ($visStartPage + $visPagesCount); $i++){
            $startOffset = min($this->numberOfFoundRows - 1, $i * $pageSize + $tapePosition);
            if($i == $activePage){
                $aPager[] = array("type" => "active", "pagenum" => ($i+1), "start" => "", "_start" => $startOffset);
            }else{
                $aPager[] = array("type" => "page", "pagenum" => ($i+1), "start" => $startOffset);
            }
        }
        $visEndPage = $i;

        if($activePage != ($pagesCount - 1)){
            $startOffset = min($this->numberOfFoundRows - 1, ($activePage + 1) * $pageSize + $tapePosition);
            $aPager[] = array("type" => "next", "pagenum" => "", "start" => $startOffset);
        }

        if($forceViewEndLink || (($activePage < ($pagesCount - 1)) && ($visEndPage < $pagesCount))){
            $aPager[] = array("type" => "last", "pagenum" => $pagesCount, "start" => $endLinkOffset);
        }

        if($calcPages && ($aPagerData['mode'] != "tape")){
            foreach($aPager as $key => $val){
                $aPager[$key]["page_start"] = ($val["pagenum"] - 1) * $pageSize + 1;
                $aPager[$key]["page_end"] = min($val["pagenum"] * $pageSize, $this->numberOfFoundRows);
            }
        }

        return $aPager;
    }

    /**
     * Arranges pager position in accordance with applied item id.
     *
     * @param  int $itemId         Id of applied item
     * @param  string $orderField  Pager order field
     * @param  string $orderDir    Pager order direction
     * @param  string $idField     Id fieldname with table alias
     * @return int
     * @amidev
     */
    public function arrangePosition($itemId, $orderField, $orderDir = 'desc', $idField = 'id'){
        $position = 0;
        $db = AMI::getSingleton('db');
        $modId = $this->getModId();
        $oItem = AMI::getResourceModel($modId . '/table')->find($itemId);
        $aItem = $oItem->getData();

        $catModId = $modId . '_cat';
        $useCats = AMI::isModInstalled($catModId) && AMI::issetAndTrueOption($modId, 'use_categories');
        $catSnippet = $useCats ? ' AND i.id_cat = '.(int)AMI_Registry::get('page/catId', 0) : '';

        // Select items using order
        $oQuery = clone $this->oQuery;
        $oQuery->resetFields();
        $oQuery->addExpressionField("COUNT(i.id) pcount");
        if(!empty($orderField) && isset($aItem[$orderField])){
            $oQuery->addWhereDef(DB_Query::getSnippet($catSnippet . " AND i.". $orderField . ($orderDir == 'asc' ? '<' : '>') . "%s ")->q($aItem[$orderField]));
        }
        $oRecordset = $db->select($oQuery);

        if($oRecordset){
            if($aRecord = $db->fetchRow($oQuery)){
                if(!$oQuery->isGroupingUsed()){
                    $position = $aRecord['pcount'];
                }else{
                    $position = $oRecordset->count();
                }
            }

            // Move using order by
            if(isset($aItem[$orderField])){
                $oQuery = clone $this->oQuery;
                $oQuery->addField('*');
                $oQuery->addWhereDef(DB_Query::getSnippet(($catSnippet . " AND i.". $orderField . '=' . "%s") . " ")->q($aItem[$orderField]));
                $oRecordset = $db->select($oQuery);
                foreach($oRecordset as $aRecord){
                    if($aRecord[$idField] == $itemId){
                        break;
                    }
                    $position++;
                }
            }

            $this->adjustPosition(1);
        }

        $this->itemPosition = $position;
        return $position;
    }

    /**
     * Adjust the item position.
     *
     * @param  int $pageSize          List page size
     * @param  bool $correctPosition  Correct the item position
     * @return int
     * @amidev
     */
    public function adjustPosition($pageSize = 10, $correctPosition = true){
        if($pageSize == 0){
            return $this->itemPosition = 0;
        }

        if($this->itemPosition < 0 ){
            $this->itemPosition = 0;
        }

        if($correctPosition){
            // correct position to first item on page
            $this->itemPosition = floor($this->itemPosition / $pageSize) * $pageSize;
        }

        return $this->itemPosition;
    }

    /**
     * Returns the pages number.
     *
     * @param  int $pageSize  Number of items per page
     * @return int
     * @amidev
     */
    public function getPagesCount($pageSize = 10){
        $res = 1;
        if($pageSize > 0){
            $res = ceil($this->numberOfFoundRows / $pageSize);
        }
        return $res;
    }

    /**
     * Returns the active page number.
     *
     * @param  int $pageSize  Number of items per page
     * @param  int $position  List position
     * @return int
     * @amidev
     */
    public function getActivePage($pageSize = 10, $position = 0){
        $res = 1;
        if($pageSize > 0){
            $res = intval($position / $pageSize);
        }
        return $res;
    }

    /**
     * Sets current item position.
     *
     * @param  int $position  Item position
     * @return AMI_ModTableList
     * @amidev
     */
    public function setItemPosition($position = 0){
        $this->itemPosition = $position;
        return $this;
    }

    /**
     * Gets current item position.
     *
     * @return int
     * @amidev
     */
    public function getItemPosition(){
        return $this->itemPosition;
    }
}
