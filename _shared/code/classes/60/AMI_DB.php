<?php
/**
 * Database access layer.
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   DB
 * @version   $Id: AMI_DB.php 50133 2014-04-21 07:16:39Z Leontiev Anton $
 * @since     5.10.0
 */

/**
 * Database recordset interface.
 *
 * @package DB
 */
interface DB_iRecordset extends SeekableIterator, Countable{
}

/**
 * Database recordset.
 *
 * @package DB
 */
class DB_Recordset implements DB_iRecordset{
    /**
     * Contains internal pointer position
     *
     * @var int
     */
    protected $position;

    /**
     * Contains current record
     *
     * @var mixed  array|bool
     */
    protected $record;

    /**
     * MySQL query result resource
     *
     * @var resource
     */
    protected $resource;

    /**
     * Contains result type
     *
     * @var  int
     * @link http://php.net/manual/en/function.mysql-fetch-array.php php:mysql_fetch_array()()
     */
    protected $resultType = 0;

    /**
     * Contains flag to discard mysql_free_result in object clones
     *
     * @var bool
     */
    private $free = true;

    /**
     * Constructor.
     *
     * @param resource $resource  MySQL query result resource
     * @param int $resultType  See {@link http://php.net/manual/en/function.mysql-fetch-array.php mysql_fetch_array}
     *                         $result_type parameter description
     */
    public function __construct($resource, $resultType = MYSQL_ASSOC){
        $this->resource = $resource;
        $this->resultType = (int)$resultType;
        $this->rewind();
    }

    /**
     * Cloninig.
     */
    public function __clone(){
        $this->free = false;
    }

    /**
     * Destructor.
     */
    public function __destruct(){
        if($this->free && is_resource($this->resource)){
            mysql_free_result($this->resource);
        }
    }

    /**#@+
     * Iterator interface implementation.
     */

    /**
     * Returns the current element.
     *
     * @return array
     */
    public function current(){
        return $this->record;
    }

    /**
     * Returns the key of the current element.
     *
     * @return mixed
     */
    public function key(){
        return $this->position;
    }

    /**
     * Move forward to next element.
     *
     * @return void
     */
    public function next(){
        $this->position++;
        $t = microtime();
        $this->record = mysql_fetch_array($this->resource, $this->resultType);
        $GLOBALS['aAMIBench']['DB']['fetchTime'] += AMI_Service::microtimeDiff($t, microtime());
        $GLOBALS['aAMIBench']['DB']['fetchCount']++;
        $GLOBALS['_total_fqueries']++;
    }

    /**
     * Rewinds the Iterator to the first element.
     *
     * @return void
     */
    public function rewind(){
        if($this->count()){
            mysql_data_seek($this->resource, 0);
            $this->position = 0;
            $t = microtime();
            $this->record = mysql_fetch_array($this->resource, $this->resultType);
            $GLOBALS['aAMIBench']['DB']['fetchTime'] += AMI_Service::microtimeDiff($t, microtime());
            $GLOBALS['aAMIBench']['DB']['fetchCount']++;
            $GLOBALS['_total_fqueries']++;
        }else{
            $this->position = -1;
            $this->record = false;
        }
    }

    /**
     * Checks if current position is valid.
     *
     * @return bool
     */
    public function valid(){
        return (bool)$this->record;
    }

    /**#@-*/

    /**
     * SeekableIterator::seek() implementation.
     *
     * Seeks to a position.
     *
     * @param  int $position  The position to seek to
     * @return bool
     */
    public function seek($position){
        $position = (int)$position;
        $res = @mysql_data_seek($this->resource, $position);
        if($res){
            $this->position = $position;
            $t = microtime();
            $this->record = mysql_fetch_array($this->resource, $this->resultType);
            $GLOBALS['aAMIBench']['DB']['fetchTime'] += AMI_Service::microtimeDiff($t, microtime());
            $GLOBALS['aAMIBench']['DB']['fetchCount']++;
            $GLOBALS['_total_fqueries']++;
        }
        return $res;
    }

    /**
     * Countable::count() implementation.
     *
     * Counts elements of an object.
     *
     * @return int
     */
    public function count(){
        return mysql_num_rows($this->resource);
    }
}

/**
 * Database column recordset.
 *
 * @package DB
 */
class DB_Recordset_Column extends DB_Recordset{
    /**
     * Constructor.
     *
     * @param resource $resource  MySQL query result resource
     */
    public function __construct($resource){
        $this->resource = $resource;
        $this->resultType = MYSQL_NUM;
        $this->rewind();
    }

    /**
     * Converts record to a string.
     *
     * @return string
     * @see    AMI_DB::fetchCol() example
     */
    public function __toString(){
        return implode(',', iterator_to_array($this));
    }

    /**
     * Iterator::current() implementation.
     *
     * @return mixed
     */
    public function current(){
        return $this->record[0];
    }
}

/**
 * Database API interface. Reserved for others PHP DB drivers.
 *
 * @package DB
 * @amidev
 */
interface AMI_iDB{
    /**
     * Returns an instance of an object.
     *
     * @param  array $aOptions  Reserved options array
     * @return AMI_iDB
     */
    public static function getInstance(array $aOptions = array());

    /**
     * Executes query.
     *
     * Query cannot contain any quotes. Use {@link DB_Query::getSnippet()} to pass complex data.
     *
     * @param  DB_Snippet|string $query  Query
     * @param  int $flags  Will be described later
     * @return resource|false  PHP DB driver result resource or false
     */
    public function query($query, $flags = 0);

    /**
     * Executes SELECT query and returns the recordset object.
     *
     * Query cannot contain any quotes. Use {@link DB_Query::getSnippet()} to pass complex data.
     *
     * @param  string $query  Query
     * @param  int $fetchType  See {@link http://php.net/manual/en/function.mysql-fetch-array.php mysql_fetch_array}
     *                         $result_type parameter description
     * @param  int $flags  Will be described later
     * @return DB_iRecordset|false
     */
    public function select($query, $fetchType = MYSQL_ASSOC, $flags = 0);

    /**
     * Executes SELECT query and returns the recordset object containing only one column.
     *
     * Query cannot contain any quotes. Use {@link DB_Query::getSnippet()} to pass complex data.
     *
     * @param  string $query  Query
     * @param  int $flags  Will be described later
     * @return DB_iRecordset|false
     */
    public function fetchCol($query, $flags = 0);

    /**
     * Returns single record.
     *
     * Query cannot contain any quotes. Use {@link DB_Query::getSnippet()} to pass complex data.
     *
     * @param  string $query  Query
     * @param  int $fetchType  See {@link http://php.net/manual/en/function.mysql-fetch-array.php mysql_fetch_array}
     *                         $result_type parameter description
     * @param  int $flags  Will be described later
     * @return array|false
     */
    public function fetchRow($query, $fetchType = MYSQL_ASSOC, $flags = 0);

    /**
     * Returns single value.
     *
     * Query cannot contain any quotes. Use {@link DB_Query::getSnippet()} to pass complex data.
     *
     * @param  string $query  Query
     * @param  int $flags  Will be described later
     * @return string|false|null
     */
    public function fetchValue($query, $flags = 0);

    /**
     * Escapes and quotes value.
     *
     * @param  int|float|string $value  Value
     * @return string
     * @amidev
     */
    public function quote($value);

    /**
     * Escapes value.
     *
     * @param  int|float|string $value  Value
     * @return string
     */
    public function escape($value);

    /**
     * Returns last insert id.
     *
     * @return int|false
     */
    public function getInsertId();

    /**
     * Returns affected rows number.
     *
     * @return int
     */
    public function getAffectedRows();

    /**
     * Returns PHP DB driver last error number.
     *
     * @return int
     */
    public function getErrorNumber();

    /**
     * Returns last error message.
     *
     * @return string
     */
    public function getErrorMessage();

    /**
     * @return resource|null  PHP DB driver resource or null
     */
/*
    public function getDriverResource();
*/

    /**
     * Allows to execute unsafe (ALTER/DROP/etc) query once (one query after this method is called).
     *
     * @return void
     */
    public function allowUnsafeQueryOnce();

    /**
     * Enables or disables debug queries output.
     *
     * @param  bool $bDebug  True to display queries
     * @return void
     */
    public function displayQueries($bDebug = true);
}

/**
 * Database API.
 *
 * @package  DB
 * @resource db <code>AMI::getSingleton('db')</code>
 */
class AMI_DB implements AMI_iDB{
    /**
     * Flag saing to use AMI_DB::$oRS for string SELECT queries result
     */
     // const FLAG_USE_COMMON_RECORDSET = 0x80;

    /**
     * Bypass sql safety check
     *
     * @see DBC_TRUSTED_QUERY
     * @amidev
     */
    const QUERY_TRUSTED = 0x01;

    /**
     * Bypass error handling
     *
     * @see DBC_NO_HALT
     * @amidev
     */
    const QUERY_NO_HALT = 0x40;

    /**
     * Bypass rights checking
     *
     * @see DBC_SYS_QUERY
     * @amidev
     */
    const QUERY_SYSTEM = 0x04;

    /**
     * Common recordset. Its value sets in AMI_DB::select().
     *
     * @var DB_iRecordset
     */
     // protected $oRS = null;

    /**
     * Instance
     *
     * @var AMI_DB
     */
    private static $oInstance;

    /**
     * Decorated system DB object
     *
     * @var DB_si|CMS_simpleDb
     */
    private $oDB;

    /**
     * Flag used to execute query once
     *
     * @var int
     * @see AMI_DB::allowUnsafeQueryOnce()
     * @see AMI_DB::query()
     */
    private $forcedFlag = 0;

    /**
     * Debug state
     *
     * @var bool
     * @see AMI_DB::setDebug()
     */
    private $bDebug = false;

    /**
     * Forbid quotes flag in raw queries.
     *
     * @var bool
     */
    private $doForbidQuotes = TRUE;

    /**
     * Returns an instance of AMI_DB.
     *
     * @param  array $aOptions  Reserved options array
     * @return AMI_DB
     * @amidev
     */
    public static function getInstance(array $aOptions = array()){
        if(is_null(self::$oInstance)){
            self::$oInstance = new AMI_DB($aOptions);
        }
        return self::$oInstance; // clone self::$oInstance;
    }

    /**
     * Escapes and quotes value.
     *
     * @param  int|float|string $value  Value
     * @return string
     * @amidev
     */
    public function quote($value){
        return "'" . $this->escape($value) . "'";
    }

    /**
     * Escapes value.
     *
     * @param  int|float|string $value  Value
     * @return string
     * @since  5.12.4
     */
    public function escape($value){
        return
            self::$oInstance->oDB
                ? mysql_real_escape_string($value, self::$oInstance->oDB->_dbLink)
                : mysql_real_escape_string($value);
    }

    /**
     * Executes query.
     *
     * Query cannot contain any quotes. Use {@link DB_Query::getSnippet()} to pass complex data.
     *
     * Example:
     * <code>
     * AMI::getSingleton('db')->query("OPTIMIZE TABLE `visit`");
     * </code>
     *
     * @param  DB_Snippet|string $query  Query
     * @param  int $flags  Will be described later
     * @return resource|false  PHP DB driver result resource or false
     */
    public function query($query, $flags = 0){
        $this->prepareSnippet($query);
        $this->oDB->_keepRes = true;
        $t = microtime();
        $res = $this->oDB->query($query, $flags | $this->forcedFlag);
        $dt = AMI_Service::microtimeDiff($t, microtime());
        $GLOBALS['aAMIBench']['DB']['queryTime'] += $dt;
        $GLOBALS['aAMIBench']['DB']['queryCount']++;
        if($this->bDebug){
            $highlightTotal = $dt > 0.5;
            $dQuery = trim($query);
            if(preg_match('/^SELECT\s+(.*)\s+FROM\s+/si', $dQuery, $aMatches)){
                $dQuery = str_replace($aMatches[1], preg_replace('/(\S)\,(\S)/', '$1, $2', $aMatches[1]), $dQuery);
            }
            $GLOBALS['aAMIBench']['DB']['queries'] .=
                "<div style=\"font-size: 9px; font-family: Verdana,Geneva,Arial,Helvetica,sans-serif; padding-bottom: 10px;\"><b>[" . $GLOBALS['aAMIBench']['DB']['queryCount'] . "] Delay</b>: " .
                ($highlightTotal ? '<span style="color: #f00;">' : '') . number_format($dt, 5) . ($highlightTotal ? '</span>' : '') .
                " sec, total: " . number_format($GLOBALS['aAMIBench']['DB']['queryTime'], 5) .
                " sec, total fetch count: " . $GLOBALS['aAMIBench']['DB']['fetchCount'] . "<br>\n" .
                "<span style=\"color: #00F;\">\n " . $dQuery . "\n</span></div>\n";
            // $GLOBALS['aAMIBench']['DB']['queryCharts'][] = array(
            // 'sql' => $query,
            // 'total' => $GLOBALS['aAMIBench']['DB']['queryTime'],
            // );
        }
        if(!($flags & self::QUERY_NO_HALT) && $this->getErrorNumber()){
            trigger_error('DB error: ' . $this->getErrorMessage() . "\n" . $query, E_USER_ERROR);
        }
        $this->forcedFlag = 0;
        return $res;
    }

    /**
     * Executes SELECT query and returns the recordset object.
     *
     * Query cannot contain any quotes. Use {@link DB_Query::getSnippet()} to pass complex data.
     *
     * Example:
     * <code>
     * $oDB = AMI::getSingleton('db');
     * $oRS = $oDB->select("SELECT * FROM `visit`");
     * foreach($oRS as $aRecord){
     *     // $aRecord is an array
     * }
     * </code>
     *
     * @param  DB_Snippet|string $query      Query
     * @param  int               $fetchType  See {@link http://php.net/manual/en/function.mysql-fetch-array.php
     *                                       mysql_fetch_array} $result_type parameter description
     * @param  int               $flags      Will be described later
     * @return DB_Recordset|false
     */
    public function select($query, $fetchType = MYSQL_ASSOC, $flags = 0){
        $resource = $this->query($query, $flags);
        if(is_resource($resource)){
            $oRS = new DB_Recordset($resource, $fetchType);
        }else{
            $oRS = FALSE;
        }
/*
        if($flags & self::FLAG_USE_COMMON_RECORDSET){
            $this->oRS = $oRS;
        }
*/
        return $oRS;
    }

    /**
     * Executes SELECT query and returns the recordset object containing only one column.
     *
     * Query cannot contain any quotes. Use {@link DB_Query::getSnippet()} to pass complex data.
     *
     * Example:
     * <code>
     * $aColumn = AMI::getSingleton('db')->fetchCol("SELECT DISTINCT(`id_page`) FROM `visit` WHERE `id_user` = 1");
     * foreach($aColumn as $value){
     *     ...
     * }
     * </code>
     *
     * @param  DB_Snippet|string $query  Query
     * @param  int               $flags  Will be described later
     * @return DB_Recordset_Column|false
     */
    public function fetchCol($query, $flags = 0){
        $resource = $this->query($query, $flags);
        $oRS = $resource ? new DB_Recordset_Column($resource) : FALSE;
/*
        if($flags & self::FLAG_USE_COMMON_RECORDSET){
            $this->oRS = $oRS;
        }
*/
        return $oRS;
    }

    /**
     * Returns single record.
     *
     * Query cannot contain any quotes. Use {@link DB_Query::getSnippet()} to pass complex data.
     *
     * Example:
     * <code>
     * // @var array
     * $aRecord = AMI::getSingleton('db')->fetchRow("SELECT * FROM `visit` LIMIT 1");
     * </code>
     *
     * @param  DB_Snippet|string $query      Query
     * @param  int               $fetchType  See {@link http://php.net/manual/en/function.mysql-fetch-array.php
     *                                       mysql_fetch_array} $result_type parameter description
     * @param  int               $flags      Will be described later
     * @return array|false
     */
    public function fetchRow($query, $fetchType = MYSQL_ASSOC, $flags = 0){
        $oRS = $this->select($query, $fetchType, $flags);
        return $oRS && $oRS->count() ? $oRS->current() : FALSE;
    }

    /**
     * Returns single value.
     *
     * Query cannot contain any quotes. Use {@link DB_Query::getSnippet()} to pass complex data.
     *
     * Example:
     * <code>
     * // @var string
     * $count = AMI::getSingleton('db')->fetchValue("SELECT COUNT(`id`) FROM `visit`");
     * </code>
     *
     * @param  DB_Snippet|string $query  Query
     * @param  int               $flags  Will be described later
     * @return mixed  string|null|false
     */
    public function fetchValue($query, $flags = 0){
        $oRS = $this->select($query, MYSQL_NUM, $flags);
        if($oRS && $oRS->count()){
            $res = $oRS->current();
            $res = $res[0];
        }else{
            $res = false;
        }
        return $res;
    }

    /**
     * Returns last insert id.
     *
     * @return int|false
     */
    public function getInsertId(){
        return
            is_callable(array('lastInsertId', $this->oDB))
            ? $this->oDB->lastInsertId()
            : mysql_insert_id($this->oDB->_dbLink);
    }

    /**
     * Returns affected rows number.
     *
     * @return int
     */
    public function getAffectedRows(){
        return mysql_affected_rows($this->oDB->_dbLink);
    }

    /**
     * Returns PHP DB driver last error number.
     *
     * @return int
     */
    public function getErrorNumber(){
        return @mysql_errno($this->oDB->_dbLink);
    }

    /**
     * Returns last error message.
     *
     * @return string
     */
    public function getErrorMessage(){
        return @mysql_error($this->oDB->_dbLink);
    }

    /**
     * Returns PHP DB driver resource.
     *
     * @return resource|null  PHP DB driver resource or null
     */
/*
    public function getDriverResource(){
        return self::$oInstance->oDB ? self::$oInstance->oDB->_dbLink : null;
    }
*/

    /**
     * Allows to execute unsafe (ALTER/DROP/etc) query once (one query after this method is called).
     *
     * Example:
     * <code>
     * AMI::getSingleton('db')->function allowUnsafeQueryOnce()->query("DROP TABLE `table`");
     * </code>
     *
     * @return AMI_DB
     */
    public function allowUnsafeQueryOnce(){
        $this->forcedFlag = self::QUERY_TRUSTED;
        return $this;
    }

    /**
     * Allows to use quotes in raw query once.
     *
     * Dirty hack for module installer/uninstaller.
     *
     * @return AMI_DB
     * @amidev
     */
    public function allowQuotesInQueryOnce(){
        $this->doForbidQuotes = FALSE;
        return $this;
    }

    /**
     * Enables or disables debug queries output.
     *
     * @param  bool $bDebug  True if display queries
     * @return void
     */
    public function displayQueries($bDebug = true){
        $this->bDebug = (bool)$bDebug;
    }

    /**
     * Returns base DB object.
     *
     * @return DB_si
     * @amidev
     */
    public function getCoreDB(){
        return $this->oDB;
    }

    /**
     * Sets base DB object.
     *
     * @param  DBConnection|CMS_simpleDb $oDB  Core DB object
     * @return void
     * @amidev
     * @see    http://jira.cmspanel.net/browse/CMS-11163
     */
    public function setCoreDB($oDB){
        $this->oDB = $oDB;
    }

    /**
     * Constructor.
     *
     * @param  array $aOptions  Reserved options array
     */
    private function __construct(array $aOptions = array()){
        if(empty($aOptions['ami_db'])){
            trigger_error('Missing initial parameters', E_USER_ERROR);
        }
        $this->oDB = $aOptions['ami_db'];
    }

    /**
     * Cloninig.
     *
     * Clones common recordset.
     */
    private function __clone(){
        /*
        if(is_object($this->oRS)){
            $this->oRS = clone $this->oRS;
        }
        */
    }

    /**
     * Prepares query snippet.
     *
     * @param  DB_Snippet|string &$snippet  Query snippet
     * @return void
     * @since  5.12.4
     */
    private function prepareSnippet(&$snippet){
        if(
             !is_object($snippet) && $this->doForbidQuotes && (
                mb_strpos($snippet, "'") !== FALSE || mb_strpos($snippet, '"') !== FALSE
            )
        ){
            AMI_Registry::set('_deprecated_error', TRUE);
            trigger_error(
                'Quotes are forbidden, please refer to DB_Query::getSnippet() to get more details',
                E_USER_ERROR
            );
        }
        if(is_object($snippet)){
            $snippet = $snippet->get();
        }
        $this->doForbidQuotes = TRUE;
    }
}

/**
 * Database query snippet.
 *
 * Contains functionality to construct query snippets having quotes.
 *
 * Example:
 * <code>
 * // PlgAJAXResp::initModel()
 * $oModelList->addWhereDef(
 *     DB_Query::getSnippet("AND %s = %s")
 *         ->plain($prefix . $this->oTableModel->getFieldName('id_page'))
 *         ->q($this->oRequest->get('id_page'))
 * );
 * // ~ AND id_page = '....'
 * </code>
 *
 * @package DB
 */
class DB_Snippet{
    /**
     * SQL template
     *
     * @var string
     */
    protected $snippet = '';

    /**
     * Template arguments
     *
     * @var array
     */
    protected $aArgs = array();

    /**
     * Constructor.
     *
     * @param string $snippet  Query snippet having '%s' placeholders
     */
    public function __construct($snippet){
        $this->snippet = (string)$snippet;
        if(mb_strpos($this->snippet, "'") !== FALSE || mb_strpos($this->snippet, '"') !== FALSE){
            AMI_Registry::set('_deprecated_error', TRUE);
            trigger_error(
                'Quotes are forbidden, please refer to DB_Query::getSnippet() to get more details',
                E_USER_ERROR
            );
        }
    }

    /**
     * Adds quoted value.
     *
     * Example:
     * <code>
     * $oQuery->addWhereDef(
     *     DB_Query::getSnippet("AND name = %s")
     *         ->q($exactName)
     * );
     * </code>
     *
     * @param  string|int $value  Argument value
     * @return DB_Snippet
     */
    public function q($value){
        $this->aArgs[] = AMI::getSingleton('db')->quote($value);
        return $this;
    }

    /**
     * Adds plain value.
     *
     * Plain value cannot contain any quotes.
     *
     * Example:
     * <code>
     * $oQuery->addWhereDef(
     *     DB_Query::getSnippet("AND %s = 'value'")
     *         ->plain($fieldName)
     * );
     * </code>
     *
     * @param  string|int $value  Argument value containing no quotes
     * @return DB_Snippet
     */
    public function plain($value){
        if(!is_object($value) && (mb_strpos($value, "'") !== false || mb_strpos($value, '"') !== false)){
            trigger_error('Quotes are forbidden, please refer to DB_Query::getSnippet() to get more details', E_USER_ERROR);
        }
        $this->aArgs[] = $value;
        return $this;
    }

    /**
     * Adds imploded array.
     *
     * Example:
     * <code>
     * $oQuery->addWhereDef(
     *     DB_Query::getSnippet("AND id IN (%s)")
     *         ->implode(array(1, 2, 3))
     * );
     * </code>
     *
     * @param  array $aValue  Argument value containing array
     * @param  bool  $asInt   Implode array having integer values (since 5.14.0)
     * @return DB_Snippet
     */
    public function implode(array $aValue, $asInt = FALSE){
        $this->aArgs[] = implode(',', array_map($asInt ? 'intval' : array(AMI::getSingleton('db'), 'quote'), $aValue));
        return $this;
    }

    /**
     * Returns parsed snippet as string.
     *
     * @return string
     */
    public function get(){
        $tplArgCount = preg_match_all('/(?:^|[^%])%/', $this->snippet, $aNull);
        $argCount = sizeof($this->aArgs);
        if($tplArgCount === $argCount){
            return vsprintf($this->snippet, array_map(array($this, 'cbSnippetToString'), $this->aArgs));
        }
        trigger_error('Template arguments count(' . $tplArgCount . ') ' . ($tplArgCount < $argCount ? 'less' : 'greater') . ' than passed arguments(' . $argCount . ')', E_USER_ERROR);
    }

    /**
     * Converts snippet to string.
     *
     * @param  DB_Snippet|string $value  Value
     * @return string
     * @see    DB_Snippet::get()
     */
    private function cbSnippetToString($value){
        return is_object($value) ? $value->get() : $value;
    }
}

/**
 * Database query constructor.
 *
 * Contains functionality to construct SELECT, INSERT and UPDATE queries.
 *
 * @package DB
 * @example db_query_constructor.php DB_Query usage example
 */
class DB_Query{
    /**
     * @amidev Temporary
     */
    const OPTIMIZE_ORDER  = 0x01;

    /**
     * @amidev Temporary
     */
    const OPTIMIZE_CLAUSE = 0x02;

    /**
     * @amidev Temporary
     */
    const OPTIMIZE_BOTH   = 0x03; // self::OPTIMIZE_ORDER & self::OPTIMIZE_CLAUSE

    /**
     * Contains main table name
     *
     * @var string
     */
    private $table = '';

    /**
     * Contains main table alias
     *
     * @var string
     */
    private $alias = '';

    /**
     * Contains joined tables with its aliases
     *
     * @var array
     */
    private $aJoins = array();

    /**
     * Contains fields involved in SELECT
     *
     * @var array
     */
    private $aFields = array();

    /**
     * Contains order fields
     *
     * @var array
     */
    private $aOrders = array();

    /**
     * Contains grouping fields
     *
     * @var array
     */
    private $aGrouping = array();

    /**
     * Contains where condition
     *
     * @var string
     */
    private $where = '';

    /**
     * Contains having condition
     *
     * @var string
     */
    private $having = '';

    /**
     * Contains limit definition
     *
     * @var string
     */
    private $limit = '';

    /**
     * Contains SELECT query prefix
     *
     * @var string
     */
    private $prefix = '';

    /**
     * Contains SELECT query postfix
     *
     * @var string
     */
    // private $postfix = '';

    /**
     * Flag specifying to allow quotes in query snippets once per related methods call.
     *
     * @var bool
     */
    private $allowQuotesOnce = false;

    /**
     * SELECT queries optimization flags
     *
     * @var int
     * @see DB_Query::optimize()
     */
    private $optimization = 0;

    /**
     * Names of added fields
     *
     * @var array
     * @amidev
     */
    private $aFieldNames = array();

    /**
     * Returns query template object.
     *
     * @param  string $snippet  Query snippet having '%s' placeholders
     * @return DB_Snippet
     * @since  5.12.4
     */
    public static function getSnippet($snippet){
        return new DB_Snippet($snippet);
    }

    /**
     * Returns INSERT query.
     *
     * Example:
     * <code>
     * echo DB_Query::getInsertQuery("visit", array(
     *     'date'    => DB_Query::getSnippet('%s')->plain('NOW()'), // plain not quoted value
     *     'id_user' => 1,
     *     'id_page' => 2,
     *     'ip'      => DB_Query::getSnippet('INET_ATON(%s)')->q('192.168.0.1'), // quoted IP value
     * ));
     * </code>
     * will output
     * <pre>
     * INSERT INTO visit
     * (`date`,`id_user`,`id_page`,`ip`)
     * VALUES
     * (NOW(),'1','2',INET_ATON('192.168.0.1'))
     * </pre>
     *
     * @param  string $table        Table name
     * @param  array  $aRecord      Array containing keys as field names and values as field values
     * @param  bool   $lowPriority  Low priority flag
     * @return DB_Snippet
     */
    public static function getInsertQuery($table, array $aRecord, $lowPriority = false){
        $table = preg_replace('/[^A-Za-z_0-9]/', '', $table);
        list($oFields, $oValues) = self::getFieldsValues($aRecord);
        $oQuery =
            DB_Query::getSnippet('INSERT %sINTO %s (%s) VALUES (%s)')
            ->plain($lowPriority ? 'LOW_PRIORITY ' : '')
            ->plain($table)
            ->plain($oFields)
            ->plain($oValues);
        return $oQuery;
    }

    /**
     * Returns REPLACE query.
     *
     * @param  string $table        Table name
     * @param  array  $aRecord      Array containing keys as field names and values as field values
     * @param  bool   $lowPriority  Low priority flag
     * @return DB_Snippet
     * @see    DB_Query::getInsertQuery()
     */
    public static function getReplaceQuery($table, array $aRecord, $lowPriority = false){
        $table = preg_replace('/[^A-Za-z_0-9]/', '', $table);
        list($oFields, $oValues) = self::getFieldsValues($aRecord);
        $oQuery =
            DB_Query::getSnippet('REPLACE %s%s (%s) VALUES (%s)')
            ->plain($lowPriority ? 'LOW_PRIORITY ' : '')
            ->plain($table)
            ->plain($oFields)
            ->plain($oValues);
        return $oQuery;
    }

    /**
     * Returns UPDATE query.
     *
     * Example:
     * <code>
     * echo DB_Query::getUpdateQuery(
     *     'ami_sample_plugin',
     *      array(
     *         'name'  => DB_Query::getSnippet('%s + %s')->plain('name')->q('...'),
     *         'about' => DB_Query::getSnippet('%s + %s')->plain('name')->q(' + about'),
     *      ),
     *      DB_Query::getSnippet('WHERE id IN (%s)')->implode(array(1, 2, 3, 4))
     * );
     * </code>
     * will output
     * <pre>
     * UPDATE ami_sample_plugin
     * SET `name`=name + '...', `about`=name + ' + about'
     * WHERE id IN ('1','2','3','4')
     * </pre>
     *
     * @param  string             $table        Table name
     * @param  array              $aRecord      Array containing keys as field names and values as field values
     * @param  DB_Stniplet|string $condition    Condition
     * @param  bool               $lowPriority  Low priority flag
     * @return DB_Snippet
     */
    public static function getUpdateQuery($table, array $aRecord, $condition = '', $lowPriority = false){
        $table = preg_replace('/[^A-Za-z_0-9]/', '', $table);
        $fields = '';
        foreach($aRecord as $field => $value){
            $field = preg_replace('/[^A-Za-z_0-9]/', '', $field);
            $fields .= "`%s` = %s,";
        }
        $fields = mb_substr($fields, 0, -1) . ' ';

        $oQuery =
            DB_Query::getSnippet('UPDATE %s%s SET ' . $fields . '%s')
            ->plain($lowPriority ? 'LOW_PRIORITY ' : '')
            ->plain($table);
        foreach($aRecord as $field => $value){
            $oQuery->plain(preg_replace('/[^A-Za-z_0-9]/', '', $field));
            if(is_null($value)){
                $oQuery->plain('NULL');
            }elseif(is_object($value)){
                $oQuery->plain($value);
            }else{
                $oQuery->q($value);
            }
        }
        $oQuery->plain($condition);
        return $oQuery;
    }

    /**
     * Constructor.
     *
     * @param string $table  Main table name
     * @param string $alias  Main table alias
     */
    public function __construct($table = '', $alias = ''){
        if($table !== ''){
            $this->setMainTableName($table);
            $this->setMainTableAlias($alias);
        }
    }

    /**
     * Converts object to query string.
     *
     * @return string
     * @see    DB_Query::get()
     */
    public function __toString(){
        return $this->get();
    }

    /**
     * Sets main table name.
     *
     * @param  string $name  Main table name
     * @return DB_Query
     */
    public function setMainTableName($name){
        if($name != ''){
            $this->table = $name;
        }else{
            trigger_error('Empty table name', E_USER_ERROR);
        }
        return $this;
    }

    /**
     * Sets main table alias.
     *
     * @param  string $alias  Main table alias
     * @return DB_Query
     */
    public function setMainTableAlias($alias){
        $this->alias = $alias;
        return $this;
    }

    /**
     * Adds joined table.
     *
     * Example:
     * <code>
     * $oQuery = new DB_Query('user', 'u');
     * $oQuery
     *     ->addField("name", "u")
     *     ->addExpressionField("COUNT(v.id) cnt")
     *     ->addJoinedTable("visit", "v", "u.id = v.id_user", "LEFT OUTER JOIN");
     * </code>
     *
     * @param  string $name  Joined table name
     * @param  string $alias  Joined table alias
     * @param  string $joinCondition  Join condition
     * @param  string $joinType  Join type
     * @return DB_Query
     */
    public function addJoinedTable($name, $alias, $joinCondition, $joinType = 'LEFT JOIN'){
        if($name == ''){
            trigger_error('Empty joined table name', E_USER_ERROR);
        }
        if($alias == ''){
            trigger_error('Empty joined table alias', E_USER_ERROR);
        }
        $this->aJoins[$alias] = array('name' => $name, 'condition' => $joinCondition, 'type' => $joinType);
        return $this;
    }

    /**
     * Sets SELECT query prefix.
     *
     * Example:
     * <code>
     * $oQuery = new DB_Query('visit');
     * $oQuery->setPrefix('SQL_CALC_FOUND_ROWS');
     * $oQuery->addField('id');
     * echo $oQuery;
     * </code>
     * will output
     * <pre>
     * SELECT SQL_CALC_FOUND_ROWS id
     * FROM visit
     * </pre>
     *
     * @param  string $prefix  Prefix
     * @return DB_Query
     */
    public function setPrefix($prefix){
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Sets SELECT query postfix.
     *
     * @param  string $postfix
     * @return DB_Query
     */
/*
    public function setPostfix($postfix){
        $this->postfix = $postfix;
        return $this;
    }
*/

    /**
     * Formats field as a part of SQL SELECT query.
     *
     * @param  string $name    Field name
     * @param  string $prefix  Field prefix
     * @param  string $alias   Field alias
     * @return string
     * @amidev Temporary
     */
    protected function _formatFieldForQuery($name, $prefix = '', $alias = ''){
        if($prefix == ''){
            $prefix = $this->alias;
        }
        return
            ($prefix == '' ? '' : $prefix . '.') . $name .
            ($alias == '' ? '' : ' `' . trim($alias, '`') . '`');
    }

    /**
     * Adds field to SELECT query.
     *
     * Example:
     * <code>
     * $oQuery = new DB_Query('visit', 'i');
     * $oQuery->addField('id', '', 'visit_id');
     * $select = $oQuery->getFieldsAsString();
     * // $select contains i.id visit_id
     * </code>
     *
     * @param  string $name    Field name
     * @param  string $prefix  Field prefix
     * @param  string $alias   Field alias
     * @return DB_Query
     */
    public function addField($name, $prefix = '', $alias = ''){
        /**
         * Workaround not to add fields that were already added by event handlers
         */
        $field = $alias ? $alias : $name;
        if(!in_array($field, $this->aFieldNames)){
            $this->aFieldNames[] = $field;
            $this->aFields[] = $this->_formatFieldForQuery($name, $prefix, $alias);
        }
        return $this;
    }

    /**
     * Removes field from SELECT query.
     *
     * Example:
     * <code>
     * $oQuery->dropField('id', '', 'visit_id');
     * </code>
     *
     * @param  string $name    Field name
     * @param  string $prefix  Field prefix
     * @param  string $alias   Field alias
     * @return DB_Query
     */
    public function dropField($name, $prefix = '', $alias = ''){
        $field = $this->_formatFieldForQuery($name, $prefix, $alias);
        if(in_array($field, $this->aFields)){
            unset($this->aFields[array_search($field, $this->aFields)]);
        }
        return $this;
    }

    /**
     * Adds expression to selection.
     *
     * Expression cannot contain any quotes. Use {@link DB_Query::getSnippet()} to pass complex data.
     *
     * Example:
     * <code>
     * $oQuery = new DB_Query('visit');
     * $oQuery->addExpressionField("COUNT(id) quantity");
     * </code>
     *
     * @param  DB_Snippet|string $expression  Expression
     * @return DB_Query
     */
    public function addExpressionField($expression){
        $this->prepareSnippet($expression);
        $this->aFields[] = $expression;
        return $this;
    }

    /**
     * Adds several fields.
     *
     * Example:
     * <code>
     * $oQuery = new DB_Query('visit', 'i');
     * $oQuery->addFields(array('id', 'date'), 'i', 'visit_');
     * </code>
     *
     * @param  array  $aNames       Array containing field names
     * @param  string $prefix       Field prefix
     * @param  string $aliasPrefix  Field alias prefix
     * @return DB_Query
     * @see    DB_Query::addField()
     */
    public function addFields(array $aNames, $prefix = '', $aliasPrefix = ''){
        if($prefix == ''){
            $prefix = $this->alias;
        }
        if($prefix != ''){
            $prefix .= '.';
        }
        foreach($aNames as $name){
            $this->aFields[] = $prefix . $name . ($aliasPrefix == '' ? '' : ' ' . $aliasPrefix . $name);
        }
        return $this;
    }

    /**
     * Resets field list.
     *
     * @return DB_Query
     */
    public function resetFields(){
        $this->aFields = array();
        return $this;
    }

    /**
     * Adds order.
     *
     * Example:
     * <code>
     * $oQuery = new DB_Query('visit');
     * $oQuery->addOrder('date', 'desc');
     * $oQuery->addOrder('id_user', 'asc');
     * </code>
     *
     * @param  DB_Snippet|string $expression  Order field or expression (DB_Snippet is supported since 5.14.0)
     * @param  string            $direction   Order direction
     * @return DB_Query
     */
    public function addOrder($expression, $direction = ''){
        $this->prepareSnippet($expression);
        $this->aOrders[] = $expression . ($direction == '' ? '' : ' ' . $direction);
        return $this;
    }

    /**
     * Sets new order.
     *
     * Example:
     * <code>
     * $oQuery->setOrder('date', 'asc');
     * </code>
     *
     * @param  DB_Snippet|string $expression  Order field or expression (DB_Snippet is supported since 5.14.0)
     * @param  string            $direction   Order direction
     * @return DB_Query
     * @since 5.14.8
     */
    public function setOrder($expression, $direction = ''){
        $this->aOrders = array();
        $this->addOrder($expression, $direction);
        return $this;
    }

    /**
     * Prepends order to order fields.
     *
     * Example:
     * <code>
     * $oQuery->setOrder('date', 'asc');
     * $oQuery->prependOrder('id', 'desc');  // Result: ORDER BY id DESC, date ASC
     * </code>
     *
     * @param  DB_Snippet|string $expression  Order field or expression (DB_Snippet is supported since 5.14.0)
     * @param  string            $direction   Order direction
     * @return DB_Query
     * @since 6.0.4
     */
    public function prependOrder($expression, $direction = ''){
        $this->prepareSnippet($expression);
        array_unshift($this->aOrders, $expression . ($direction == '' ? '' : ' ' . $direction));
        $this->addOrder($expression, $direction);
        return $this;
    }

    /**
     * Adds grouping.
     *
     * Expression cannot contain any quotes. Use {@link DB_Query::getSnippet()} to pass complex data.
     *
     * Affects "GROUP BY" query snippet.
     *
     * @param  DB_Snippet|string $expression  Expression
     * @return DB_Query
     */
    public function addGrouping($expression){
        $this->prepareSnippet($expression);
        $this->aGrouping[] = $expression;
        return $this;
    }

    /**
     * Adds "FROM" query snippet definition.
     *
     * @param  string $expression
     * @return DB_Query
     * @todo   Detect usability
     */
/*
    public function addFromDef($expression){
        $this->from .= ' ' . $expression;
        return $this;
    }
*/

    /**
     * Sets FROM definition.
     *
     * @param  string $expression
     * @return DB_Query
     * @todo   Detect usability
     */
/*
    public function setFromDef($expression){
        $this->from = $expression;
        return $this;
    }
*/

    /**
     * Adds where definition.
     *
     * Expression cannot contain any quotes. Use {@link DB_Query::getSnippet()} to pass complex data.
     *
     * Example:
     * <code>
     * $oQuery = new DB_Query('visit');
     * $oQuery->setWhereDef("1");
     * $oQuery->addWhereDef("AND id_user = 1");
     * $oQuery->addWhereDef(
     *     DB_Query::getSnippet("AND date BETWEEN %s AND %s")
     *         ->q('2010-01-01')
     *         ->q('2010-01-31')
     * );
     * </code>
     *
     * @param  DB_Snippet|string $expression  Expression
     * @return DB_Query
     */
    public function addWhereDef($expression){
        $this->prepareSnippet($expression);
        $this->where .= ' ' . $expression;
        return $this;
    }

    /**
     * Sets where definition.
     *
     * Expression cannot contain any quotes. Use {@link DB_Query::getSnippet()} to pass complex data.
     *
     * Example:
     * <code>
     * $oQuery = new DB_Query('visit');
     * $oQuery->setWhereDef("AND id_user = 1");
     * </code>
     *
     * @param  DB_Snippet|string $expression  Expression
     * @return DB_Query
     */
    public function setWhereDef($expression){
        $this->prepareSnippet($expression);
        $this->where = $expression;
        return $this;
    }

    /**
     * Adds having definition.
     *
     * Expression cannot contain any quotes. Use {@link DB_Query::getSnippet()} to pass complex data.
     *
     * Example:
     * <code>
     * $oQuery = new DB_Query('visit');
     * $oQuery->addField('*');
     * $oQuery->addGrouping('ip');
     * $oQuery->setHavingDef("1");
     * $oQuery->addHavingDef("AND COUNT(id) > 1");
     * </code>
     *
     * @param  DB_Snippet|string $expression  Expression
     * @return DB_Query
     */
    public function addHavingDef($expression){
        $this->prepareSnippet($expression);
        $this->having .= ' ' . $expression;
        return $this;
    }

    /**
     * Sets having definition.
     *
     * Expression cannot contain any quotes. Use {@link DB_Query::getSnippet()} to pass complex data.
     *
     * Example:
     * <code>
     * $oQuery = new DB_Query('visit');
     * $oQuery->addField('*');
     * $oQuery->addGrouping('ip');
     * $oQuery->setHavingDef("COUNT(id) > 1");
     * </code>
     *
     * @param  DB_Snippet|string $expression  Expression
     * @return DB_Query
     */
    public function setHavingDef($expression){
        $this->prepareSnippet($expression);
        $this->having = $expression;
        return $this;
    }

    /**
     * Sets limit parameters.
     *
     * @param  int $start  Start offset
     * @param  int $limit  Limit
     * @return DB_Query
     */
    public function setLimitParameters($start, $limit){
        if($start || $limit){
            $this->limit = ($start > 0 ? (int)($start) . ',' : '') . (int)($limit);
        }else{
            $this->limit = '';
        }
        return $this;
    }

    /**
     * Returns main table name.
     *
     * @return string
     */
    public function getMainTableName(){
        return $this->table;
    }

    /**
     * Returns main table name.
     *
     * @param  bool $bAsPrefix  Append '.' if true
     * @return string
     */
    public function getMainTableAlias($bAsPrefix = FALSE){
        $alias = $this->alias;
        if($bAsPrefix && $alias != ''){
            $alias .= '.';
        }
        return $alias;
    }

    /**
     * Returns fields as array.
     *
     * @return array
     */
    public function getFieldsAsArray(){
        return $this->aFields;
    }

    /**
     * Returns fields as string.
     *
     * @return string
     */
    public function getFieldsAsString(){
        return implode(',', $this->aFields);
    }

    /**
     * Returns FROM definition.
     *
     * @return string
     * @todo   Detect usability
     */
/*
    public function getFromDef(){
        return ' FROM ' . $this->from . ' ';
    }
*/

    /**
     * Returns where definition.
     *
     * @return string
     * @todo   Detect usability
     */
/*
    public function getWhereDef(){
        return ' WHERE ' . $this->where . ' ';
    }
*/

    /**
     * Returns GROUP definition.
     *
     * @return string
     * @todo   Detect usability
     */
/*
    public function getGroupDef(){
        return sizeof($this->aGrouping) ? ' GROUP BY ' . implode(', ', $this->aGrouping) : '';
    }
*/

    /**
     * Returns ORDER fields.
     *
     * @return string
     * @todo   Detect usability
     */
/*
    public function getOrderDef(){
        $order = '';
        if(sizeof($this->aOrders)){
            $order = implode(', ', $this->aOrders);
        }
        return $order;
    }
*/

    /**
     * Optimize SELECT query.
     *
     * @param  int $type  Optimization type
     * @return DB_Query
     * @amidev Temporary
     * @see    Pager::OptimizeSQLJoin()
     * @todo   self::OPTIMIZE_CLAUSE
     * @todo   Add optimization instructions to dependencies description in table models?
     */
    public function optimize($type = self::OPTIMIZE_BOTH){
        $this->optimization = (int)$type;
        return $this;
    }

    /**
     * Returns query string.
     *
     * @param  string $forceFields  Return only these fields in selection if passed
     * @return string
     */
    public function get($forceFields = ''){
        $bStraightJoin = false;

        if(($this->optimization & self::OPTIMIZE_ORDER) && sizeof($this->aOrders)){
            // Try to optimize query
            $order = $this->aOrders[0];
            $orderParts = explode('.', $order);
            if(sizeof($orderParts) > 1){
                $orderAlias = $orderParts[0];
                if($orderAlias != 'i'){
                    if(array_key_exists($orderAlias, $this->aJoins)){
                        // Replace tables order
                        $tblName = $this->table;
                        $tblAlias = $this->alias;
                        $this->table = $this->aJoins[$orderAlias]['name'];
                        $this->alias = $orderAlias;
                        $this->aJoins = array($tblAlias => $this->aJoins[$orderAlias]) + $this->aJoins;
                        $this->aJoins[$tblAlias]['name'] = $tblName;
                        // d::vd('Table `' . $tblName . '` switched with joined table `' . $this->aJoins[$orderAlias]['name'] . '`');
                        unset($this->aJoins[$orderAlias]);
                        $bStraightJoin = true;
                    }
                }
            }
        }

        // Build SQL

        $prefix = $this->prefix == '' ? '' : trim($this->prefix) . ' ';

        // if( $this->SQLData["options"] & _DB_STRAIGHT_JOIN ){
        if($bStraightJoin){
            $prefix .= " STRAIGHT_JOIN ";
        }

        $fields = $forceFields == '' ? $this->getFieldsAsString() : $forceFields;
        $sql = 'SELECT ' . $prefix . $fields. ' FROM ' . $this->table . ' ' . $this->alias;
        foreach($this->aJoins as $alias => $data){
            $sql .= ' ' . $data['type'] . ' ' . $data['name'] . ' ' . $alias . ' ON ' . $data['condition'];
        }
        if($this->where != ''){
            $sql .= ' WHERE 1 ' . $this->where;
        }
        if(sizeof($this->aGrouping)){
            $sql .= ' GROUP BY ' . implode(', ', $this->aGrouping);
        }
        if($this->having != ''){
            $sql .= ' HAVING ' . $this->having;
        }
        if(sizeof($this->aOrders)){
            $sql .= ' ORDER BY ' . implode(', ', $this->aOrders);
        }
        if($this->limit !== ''){
            $sql .= ' LIMIT ' . $this->limit;
        }
/*
        if($this->postfix !== ''){
            $sql .= ' ' . $this->postfix;
        }
*/
        return $sql;
    }

    /**
     * Allow to add query snippet containing quotes once per related methods call.
     *
     * @return DB_Query
     * @amidev
     */
    public function allowQuotesOnce(){
        $this->allowQuotesOnce = true;
        return $this;
    }

    /**
     * Returns TRUE if grouping is used.
     *
     * @return bool
     * @amidev ???
     */
    public function isGroupingUsed(){
        return sizeof($this->aGrouping) > 0;
    }

    /**
     * Service method.
     *
     * @param  array $aRecord  Fields as keys, values as values
     * @return array (string $oFields, string $oValues)
     */
    private static function getFieldsValues(array $aRecord){
        $fields = '';
        $values = '';
        foreach($aRecord as $value){
            $fields .= '`%s`,';
            $values .= '%s,';
        }
        $fields = mb_substr($fields, 0, -1); // there is no UTF-8
        $values = mb_substr($values, 0, -1);
        $oFields = DB_Query::getSnippet($fields);
        $oValues = DB_Query::getSnippet($values);
        foreach($aRecord as $field => $value){
            $oFields->plain(preg_replace('/[^A-Za-z_0-9]/', '', $field));
            if(is_null($value)){
                $oValues->plain('NULL');
            }elseif(is_object($value)){
                $oValues->plain($value);
            }else{
                $oValues->q($value);
            }
        }
        return array($oFields, $oValues);
    }

    /**
     * Prepares query snippet.
     *
     * @param  DB_Snippet|string &$snippet  Query snippet
     * @return void
     */
    private function prepareSnippet(&$snippet){
        if(
             !is_object($snippet) && !$this->allowQuotesOnce && (
                mb_strpos($snippet, "'") !== false || mb_strpos($snippet, '"') !== false
            )
        ){
            AMI_Registry::set('_deprecated_error', TRUE);
            // trigger_error('Using quotes in queries is deprecated and will be forbidden after 5.12.4 CMS build, please refer to DB_Query::getSnippet() to get more details', E_USER_WARNING);
            trigger_error(
                'Quotes are forbidden, please refer to DB_Query::getSnippet() to get more details',
                E_USER_ERROR
            );
        }
        $this->allowQuotesOnce = false;
        if(is_object($snippet)){
            $snippet = $snippet->get();
        }
    }
}
