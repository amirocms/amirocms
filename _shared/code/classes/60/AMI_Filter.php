<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_Filter.php 41412 2013-09-09 08:30:35Z Kolesnikov Artem $
 * @since     5.12.0
 */

/**
 * Module component item list filter model.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @since      5.12.0
 */
abstract class AMI_Filter implements AMI_iFormModTableItem{
    /**
     * Array of filter view fields
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aViewFields = array();

    /**
     * Array of filter fields
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aFields = array();

    /**
     * Array of extra field params
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aExtraFieldParams = array();

    /**
     * Array of allowed types of filter fields
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aAllowedTypes =
        array(
            '', 'static', 'hidden', 'text', 'date', 'select', 'radio',
            'submit', 'button', 'checkbox', 'subcats_search', 'flagmap',
            'Vsplitter', 'Hsplitter', 'Sblock', 'Fblock', 'search', 'sql',
            'timestamp', 'numeric', 'ip'
        );

    /**
     * Fields counter
     *
     * @var    int
     * @amidev Temporary
     */
    protected $fieldsCount = 0;

    /**
     * Array of session fields
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aSessionFields = array();

    /**
     * Filter element data
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aData = array();

    /**
     * Date formats
     *
     * @var    mixed
     * @todo   Do not store here
     * @amidev Temporary
     */
    protected $aDateFMT = array(
        'conf' => 'MM/DD/YYYY',
        'php'  => 'm/d/Y',
        'db'   => '%m/%d/%Y'
    );

    /**
     * Flag specifying to try load date format from core options.
     *
     * @var    bool
     * @amidev Temporary
     */
    protected $tryToLoadDateFmt = TRUE;

    /**
     * Module id
     *
     * @var    string
     * @amidev Temporary
     */
    protected $modId = '';

    /**
     * List model
     *
     * @var    AMI_ModTableList
     * @amidev Temporary
     */
    protected $oList;

    /**
     * Array of dropped fields
     *
     * @var   array
     * @since 5.14.0
     */
    protected $aDroppedFields = array();

    /**
     * Field to group by
     *
     * @var    string
     * @amidev Temporary
     */
    protected $groupByField = null;

    /**
     * Returns filter field value.
     *
     * @param  string $name     Field name
     * @param  mixed  $default  Default value to return
     * @return mixed
     * @since  5.14.6
     */
    public static function getFieldValue($name, $default = null){
        $oRequest = AMI::getSingleton('env/request');
        $oCookies = AMI::getSingleton('env/cookie');
        return
            $oRequest->get(
                $name,
                $oCookies->get(self::getCookieName($name), $default)
            );
    }

    /**
     * Adds view field.
     *
     * Example:
     * <code>
     * // Add text field
     * $this->addViewField(
     *     array(
     *         // Filter field name
     *         'name'          => 'nickname',
     *
     *         // Text input field {
     *
     *         'type'          => 'input',
     *         'flt_type'      => 'text',
     *
     *         // } Text input field
     *
     *         // Default field value
     *         'flt_default'   => '',
     *
     *         // SQL condition, 'like'|'='|'<='|'>="
     *         'flt_condition' => 'like',
     *
     *         // Module table model field name
     *         'flt_column'    => 'nickname',
     *
     *         // To filter by dependent model field (joined table field) (since 5.12.8)
     *         'flt_alias'     => 'i'
     *     )
     * );
     *
     * // Add date/datime filter fields {
     *
     * // Minimum border
     * $this->addViewField(
     *     array(
     *         'name'          => 'datefrom',
     *         'type'          => 'datefrom',
     *         'flt_type'      => 'date',
     *         'flt_default'   => AMI_Lib_Date::formatUnixTime(AMI_Lib_Date::UTIME_MIN),
     *         'flt_condition' => '>=',
     *         'flt_column'    => 'birth'
     *     )
     * );
     *
     * // Maximum border
     * $this->addViewField(
     *     array(
     *         'name'          => 'dateto',
     *         'type'          => 'dateto',
     *         'flt_type'      => 'date',
     *         'flt_default'   => AMI_Lib_Date::formatUnixTime(AMI_Lib_Date::UTIME_MAX),
     *         'flt_condition' => '<',
     *         'flt_column'    => 'birth'
     *     )
     * );
     *
     * // } Add date/datime filter fields
     *
     * </code>
     *
     * @param  array $aField  Field structure
     * @return AMI_Filter
     * @todo   Descibe other types
     */
    public function addViewField(array $aField){
        if(isset($aField['multiple']) && $aField['multiple']){
            if(!isset($aField['attributes'])){
                $aField['attributes'] = array();
            }
            $aField['attributes']['multiple'] = true;
        }        
        if(!in_array($aField['name'], $this->aDroppedFields)){
            $this->aViewFields[] = $aField;
        }
        return $this;
    }

    /**
     * Drops view fields.
     *
     * @param  array $aNames  Array of names of fields to drop
     * @return AMI_Filter
     * @since  5.14.0
     */
    public function dropViewFields(array $aNames){
        foreach($aNames as $name){
            $this->aDroppedFields[] = $name;
        }
        $this->aDroppedFields = array_unique($this->aDroppedFields);
        foreach(array_keys($this->aViewFields) as $index){
            if(in_array($this->aViewFields[$index]['name'], $aNames)){
                unset($this->aViewFields[$index]);
            }
        }
        return $this;
    }

    /**
     * Returns view fields.
     *
     * @return array
     * @amidev Temporary
     */
    public function getViewFields(){
        return $this->aViewFields;
    }

    /**
     * Returns view fields by reference for internal use only.
     *
     * @return array
     * @amidev
     */
    public function &getViewFieldsByRef(){
        return $this->aViewFields;
    }

    /**
     * Sets module Id.
     *
     * @param  string $modId  Module Id
     * @return void
     */
    public function setModId($modId){
        $this->modId = $modId;
    }

    /**
     * Returns module Id.
     *
     * @return string
     */
    public function getModId(){
        return $this->modId;
    }

    /**
     * Apply filter on query object.
     *
     * @param  array $aEvent  Event data
     * @return AMI_Filter
     * @amidev Temporary
     */
    public function applyFilter(array $aEvent){
        $this->modId = $aEvent['modId'];
        // Skip non-DB filtering (CMS-10746)
        if(isset($aEvent['oQuery'])){
            $aEvent['oQuery']->allowQuotesOnce();
            $aEvent['oQuery']->addWhereDef($this->getWhereSql());
            if(!is_null($this->groupByField)){
                $aEvent['oQuery']->addGrouping($this->groupByField);
            }
        }
        return $this;
    }

    /**
     * Returns array of validators in format 'aField' => array('validator1', 'validator2', ...).
     *
     * @return array
     * @amidev Temporary
     */
    public function getValidators(){
        return array();
    }

    /**
     * Returns data value.
     *
     * @param  string $name  Name
     * @return mixed
     * @amidev Temporary
     */
    public function getValue($name){
        return isset($this->aData[$name]) ? $this->aData[$name] : null;
    }

    /**
     * Sets data.
     *
     * @param  array $aData  Data
     * @return AMI_Filter
     * @amidev Temporary
     */
    public function setData(array $aData){
        $this->aData = $aData;
        $this->aFields = array();
        foreach($this->aViewFields as $aField){
            if(isset($aField['session_field']) && $aField['session_field']){
                // Load values from server cookies if was not set by request data
                $sessionValue = AMI::getSingleton('env/cookie')->get(self::getCookieName($aField['name']), FALSE);
                if(!isset($this->aData[$aField['name']]) && ($sessionValue !== FALSE)){
                    $this->aData[$aField['name']] = $sessionValue;
                }
            }
            $this->addField($aField);
        }
        return $this;
    }

    /**
     * Returns validator exception object validator after save or null.
     *
     * @return AMI_ModTableItemException|null
     * @amidev Temporary
     */
    public function getValidatorException(){
        return null;
    }

    /**
     * Returns item id.
     *
     * @return mixed
     */
    public function getId(){
        // reserved, will be used to save/load filter settings
        return null;
    }

    /**
     * Returns cookie name for specified field.
     *
     * @param  string $field   Field name
     * @param  string $locale  Locale
     * @return string
     * @amidev Temporary
     */
    public static function getCookieName($field, $locale = ''){
        return
            'filter_field_' . $field  . '_' .
            ($locale === '' ? AMI_Registry::get('lang_data') : (string)$locale);
    }

    /**
     * Create 'where' part of filter SQL.
     *
     * @return AMI_DBQueryTemplate
     * @amidev Temporary
     */
    protected function getWhereSql(){
        $sql = ' ';
        $oSearchCmd = FALSE;

        /**
         * @var AMI_ModTable
         */
        $oTable = AMI::getResourceModel($this->modId . '/table');

        foreach($this->aFields as $vName => $vData){
            $vData = $this->processFieldData($vName, $vData);
            if(isset($vData['skip'])){
                // Skip field
                continue;
            }
            if(isset($vData['forceSQL'])){
                // Append force SQL and go to the next field
                $sql .= $vData['forceSQL'];
                continue;
            }
            if($vData['type'] == 'text' && $vData['condition'] == '=' && strlen($vData['value']) < 1){
                // Empty text field doesn't afect on search
                continue;
            }
            if((!empty($vData['condition']) || $vData['type'] == 'search') && !$vData['disableSQL']){
                // Fields mapping
                if($oTable->hasField($vData['table_field'])){
                    $vData['encodeHTML'] = $oTable->getItem()->hasFieldCallback($vData['table_field'], 'fcbHTMLEntities');
                    $vData['table_field'] =
                        $oTable->getFieldName(
                            $vData['table_field'],
                            empty($vData['table_alias']) ? 'i.' : $vData['table_alias']
                        );
                }else{
                    $vData['table_field'] = $oTable->getList()->getColumn($vData['table_field'], trim($vData['table_alias'], '.'));
                    if(is_object($vData['table_field'])){
                        $vData['table_field'] = $vData['table_field']->get();
                    }
                }

                // Prepare field for SQL
                if(is_array($vData['value'])){
                    $aKeys = array_keys($vData['value']);
                    foreach($aKeys as $index){
                        $vData['value'][$index] = $this->prepareSqlField($vName, $vData['value'][$index], $vData['type']);
                    }
                    $val = $vData['value'];
                }else{
                    $val =
                        $this->prepareSqlField(
                            $vName,
                            $vData['value'],
                            $vData['type'],
                            !isset($vData['encodeHTML']) ? TRUE : $vData['encodeHTML']
                        );
                }
                $prefix = '';
                $postfix = '';
                $reverse = FALSE;
                if(isset($vData['exception']) && !empty($vData['exception'])){
                    $prefix = '( ';
                    $postfix = ' ' . $vData['exception'] . ' )';
                }

                // Make SQL according to field type
                switch($vData['type']){
                    case 'select':
                        if($vData['multiple'] && count($vData['multi_values'])){
                            $val = $vData['multi_values'];
                        }
                        break;
                    case 'ip':
                        $val = $vData['value'] ? sprintf('%u', ip2long($vData['value'])) : NULL;
                        break; // case 'ip'
                    case 'timestamp':
                        $val = $vData['uvalue'];
                        break; // case 'timestamp'
                    case 'date':
                        if(empty($vData['uvalue'])){
                            $val = '';
                            break;
                        }else if(is_array($vData['uvalue'])){
                            // Interval
                            foreach($vData['uvalue'] as $k => $uval){
                                if(!empty($uval)){
                                    if(is_int($uval)){
                                       $uval =  AMI_Lib_Date::formatUnixTime($uval, AMI_Lib_Date::FMT_DATE);
                                    }
                                    $val[$k] =  AMI_Lib_Date::formatDateTime($uval, AMI_Lib_Date::FMT_DATE, TRUE);
                                    $val[$k] .= (isset($vData['add_time'])) ? $vData['add_time'] : '';
                                }
                            }
                        }else{
                            if(is_int($vData['uvalue'])){
                                $vData['uvalue'] =  AMI_Lib_Date::formatUnixTime($vData['uvalue'], AMI_Lib_Date::FMT_DATE);
                            }
                            $val = AMI_Lib_Date::formatDateTime($vData['uvalue'], AMI_Lib_Date::FMT_DATE, TRUE);
                            $val .= (isset($vData['add_time'])) ? $vData['add_time'] : '';
                        }
                        break; // case 'date'
                    case 'flagmap':
                        $val = explode(':', chunk_split(_Hex2Bin($vData['value']), 63, ':'));
                        if(empty($val[sizeof($val) - 1])){
                            unset($val[sizeof($val) - 1]);
                        }
                        break; // case 'flagmap'
                    case 'search':
                        if(!is_object($oSearchCmd)){
                            require_once $GLOBALS['CLASSES_PATH'] . 'searchCmd.php';
                            $searchModule = $GLOBALS['cms']->ActiveModule->GetName();
                            $oSearchCmd = new searchCmd($GLOBALS['cms'], $searchModule);
                        }

                        $fSql = '0';
                        if($oSearchCmd->parseCmd($vData['value'])){
                            if(sizeof($vData['table_field']) > 0){
                                $fSql = $oSearchCmd->getSuperSQL($vData['table_field']);
                            }
                        }
                        if(!empty($fSql)){
                            $sql .= ' AND ((' . $fSql . '))';
                        }
                        continue 2; // case 'search'
                }

                $vcond = mb_strtolower(trim($vData['condition']));
                if(($vcond == 'like' || $vcond == 'not like') && $vData['type'] != 'flagmap'){
                    // "Simple" case
                    if((!is_array($val) && $val == '') || (is_array($val) && sizeof($val) == 0)){
                        // Skip empty values
                        continue;
                    }
                    $reverse = TRUE;
                    $aCond =
                        $this->getLikeSQL(
                            $val,
                            $vData['table_field'],
                            $vcond == 'like',
                            isset($vData['exception']) ? $vData['exception'] : FALSE
                        );
                    $sql .= $aCond['res'];
                }else{
                    // Complex case
                    if($vData['type'] == 'flagmap'){
                        $usedFlagsMask = array();
                        if(is_array($vData['params'][1])){
                            $flagsData = &$vData['params'][1];
                            for($i = 1; $i <= sizeof($flagsData); $i++){
                                $usedFlagsMask[ceil($i/63)] .=
                                    ($flagsData[$i]['u'] == '1' && $flagsData[$i]['f'] == '1' ? '1' : '0');
                            }
                        }
                        $fSql = '';
                        switch($vcond){
                            case 'equal':
                                $fldCount = 1;
                                foreach($val as $null => $realVal){
                                    if(mb_strpos($realVal, '1') !== FALSE){
                                        $fSql .=
                                            (!empty($fSql) ? ' AND ' : '') .
                                            $vData['table_field'] . '_' . $fldCount . "&CONV('" .
                                            strrev(str_pad($usedFlagsMask[$fldCount], 64, '0')) .
                                            "', 2, 10)=CONV('" . strrev(str_pad($realVal, 64, '0')) ."', 2, 10)";
                                    }
                                    $fldCount++;
                                }
                                break; // case 'equal'
                            case 'like':
                                $fldCount = 1;
                                foreach($val as $null => $realVal){
                                    if(mb_strpos($realVal, '1') !== FALSE){
                                        $fSql .=
                                            (!empty($fSql) ? ' OR ' : '') .
                                            $vData['table_field'] . '_' . $fldCount . "&CONV('".
                                            strrev(str_pad($usedFlagsMask[$fldCount], 64, '0')) .
                                            "', 2, 10)&CONV('" . strrev(str_pad($realVal, 64, '0')) . "', 2, 10)<>0";
                                    }
                                    $fldCount++;
                                }
                                break; // case 'like'
                            default:
                                // 'have' condition
                                $fldCount = 1;
                                foreach($val as $null => $realVal){
                                    if(mb_strpos($realVal, '1') !== FALSE){
                                        $fSql .=
                                            (!empty($fSql) ? ' AND ' : '') .
                                            $vData['table_field'] . '_' . $fldCount . "|CONV('" .
                                            strrev(str_pad($realVal, 64, '0')) . "', 2, 10)=" .
                                            $vData['table_field'] . '_' . $fldCount;
                                    }
                                    $fldCount++;
                                }
                                break; // default
                        }

                        if(!empty($vData['exception']) && mb_strpos($vData['exception'], '=|') === 0){
                            $fSql = ' AND ((' . $fSql . ')' . mb_substr($vData['exception'], 2) . ')';
                        }else if(!empty($vData['exception'])){
                            $fSql = ' AND ' . $prefix . '0' . $postfix;
                        }else{
                            $fSql = ' AND ' . $prefix . '(' . $fSql . ')' . $postfix;
                        }
                    }else{ // if($vData['type'] == 'flagmap')
                        $condPart = '';
                        $condPartDirect = '';
                        if(mb_strpos($vcond, 'interval') !== FALSE){
                            // Interval condition
                            if(!is_array($val)){
                                // One limit
                                $vcond = '=';
                            }else{
                                // Two limits
                                $fieldType = mb_substr($vcond, 10, mb_strlen($vcond) - 11);
                                $leftCond = $vcond[0] == '[' ? '>=' : '>';
                                $rightCond = $vcond[0] == '[' ? '<=' : '<';
                                if($fieldType == 'int'){
                                    $firstValue = intval($val[0]);
                                    $secondValue = intval($val[1]);
                                }elseif($fieldType == 'float'){
                                    $firstValue = floatval($val[0]);
                                    $secondValue = floatval($val[1]);
                                }else{
                                    $firstValue = empty($val[0]) ? '' : "'" . $val[0] . "'";
                                    $secondValue = empty($val[1]) ? '' : "'" . $val[1] . "'";
                                }
                                $condPartDirect =
                                    (!empty($firstValue) ? $vData['table_field'] . $leftCond . $firstValue : '') .
                                    (!empty($firstValue) && !empty($secondValue) ? ' AND ' : '') .
                                    (!empty($secondValue) ? $vData['table_field'] . $rightCond . $secondValue : '');
                                if($reverse){
                                    $condPart =
                                        (!empty($firstValue) ? $firstValue . $rightCond . $vData['table_field'] : '') .
                                        (!empty($firstValue) && !empty($secondValue) ? ' AND ' : '') .
                                        (!empty($secondValue) ? $secondValue . $leftCond . $vData['table_field'] : '');
                                }else{
                                    $condPart = $condPartDirect;
                                }
                            }
                        }else{ // if(mb_strpos($vcond, 'interval') !== FALSE){
                            if(!is_array($val)){
                                $val = array($val);
                            }
                            for($i = 0; $i < sizeof($val); $i++){
                                $quotes = "'";
                                if($vData['act_as_int'] && is_numeric($val[$i])){
                                    $quotes = '';
                                    $val[$i] = $val[$i];
                                }
                                $condPartDirect .=
                                    ($i > 0 ? ' OR ' : '') . $vData['table_field'] . $vcond . $quotes . $val[$i] . $quotes;
                                if($reverse){
                                    $condPart .=
                                        ($i > 0 ? ' OR ' : '') . $quotes . $val[$i] . $quotes . $vcond . $vData['table_field'];
                                }else{
                                    $condPart .=
                                        ($i > 0 ? ' OR ' : '') . $vData['table_field'] . $vcond . $quotes . $val[$i] . $quotes;
                                }
                            }
                            $condPartDirect = '(' . $condPartDirect . ')';
                            $condPart = '(' . $condPart . ')';
                        }

                        if($reverse){
                            $fSql = ' AND ' . $prefix . $condPart . $postfix;
                        }else{
                            if(!empty($vData['exception']) && mb_strpos($vData['exception'], '=|') === 0){
                                $fSql =
                                    ' AND (' . $condPartDirect .
                                    (empty($condPartDirect) && $vData['exception_type'] == 'force' ? '0 ' : '') .
                                    mb_substr($vData['exception'], 2) . ')';
                            }else if(!empty($vData['exception'])){
                                $fSql =
                                    isset($vData['exception_type']) && $vData['exception_type'] == 'force'
                                    ? ' AND ' . $prefix . '0' . $postfix
                                    : ' AND ' . $prefix . $condPart . $postfix;
                            }else{
                                $fSql = ' AND ' . $prefix . $condPart . $postfix;
                            }
                        }
                    }
                    $sql .= $fSql;
                }
            }
        }

        return $sql . ' ';
    }

    /**
     * Creates 'like' SQL.
     *
     * @param  array|string $aVal       Value
     * @param  string       $field      Field anme
     * @param  bool         $positive   Flag specifying condition is positive
     * @param  string       $exception  SQL exception
     * @param  boolean      $condition  Condition (<, >, etc.)
     * @return array
     * @amidev Temporary
     */
    protected function getLikeSQL($aVal, $field, $positive = TRUE, $exception = '', $condition = FALSE){
        $res = array(
            'prefix'  => '',
            'field'   => $field,
            'cond'    => ' like ',
            'postfix' => '',
            'res'     => ''
        );
        if(!is_array($aVal)){
            $aVal = array($aVal);
        }

        $num = sizeof($aVal);
        if($condition && $condition != 'like' && $condition != 'not like'){
            if(mb_strpos($condition, 'interval') !== FALSE){
                // interval conditions
                if(!isset($aVal[1])){
                    $aVal[1] = $aVal[0];
                }
                $leftCond = $condition[0] == '[' ? '>=' : '>';
                $rightCond = $condition[0] == '[' ? '<=' : '<';
                $fieldType = mb_substr($condition, 10, mb_strlen($condition) - 11);
                if($fieldType == 'int'){
                    // int
                    $firstValue = (int)$aVal[0];
                    $secondValue = (int)$aVal[1];
                }elseif($fieldType == 'float'){
                    // float
                    $firstValue = (float)$aVal[0];
                    $secondValue = (float)$aVal[1];
                }else{
                    // uther values
                    $firstValue = empty($aVal[0]) ? '' : "'" . $aVal[0] . "'";
                    $secondValue = empty($aVal[1]) ? '' : "'" . $aVal[1] . "'";
                }

                $res['res'] .=
                    $res['prefix'] . $res['field'] . $leftCond . $firstValue .
                    (!empty($secondValue) ? ' AND ' . $res['field'] . $rightCond . $secondValue : '') .
                    $res['postfix'];

            }else{ // if(mb_strpos($condition, 'interval') !== FALSE){

                // not interval conditions
                for($i=0; $i<$num; $i++){
                    $res['res'] .= $res['prefix'] . $res['field'] . $condition . "'" . $aVal[$i] . "'" . $res['postfix'];
                    if($i < ($num - 1)){
                        $res['res'] .= ' OR ';
                    }
                }
            }
        }else{ // if($condition && $condition != 'like' && $condition != 'not like'){

            // 'like'/'not like' condition

            if($condition == 'like'){
                $positive = TRUE;
            }elseif($condition == 'not like'){
                $positive = FALSE;
            }

            for($i = 0; $i < $num; $i++){
                // prepare value
                $res['val'] = $aVal[$i];
                $res['val'] = preg_replace('/^(&#039;|&amp;|&quot;)(.*)\1$/si', '$2', $res['val']);
                $isEqualCondition = FALSE;
                if($res['val'] != $aVal[$i]){
                    $aVal[$i] = $res['val'];
                    $isEqualCondition = TRUE;
                }
                if(!$positive){
                    $res['cond'] = ' not' . $res['cond'];
                }
                $val = $aVal[$i];
                $val = str_replace(array('%', '_'), array("\\%", "\\_"), $val);
                $val = preg_replace('/\*+/si', '%', $val);
                $val = preg_replace('/\?+/si', '_', $val);
                if(mb_strlen($val) > 0 && mb_substr($val, -1) != '%' && !$isEqualCondition){
                    $val .= '%';
                }
                if(mb_strlen($val) > 0 && mb_substr($val, 0, 1) != '%' && !$isEqualCondition){
                    $val = '%'. $val;
                }
                $res['val'] = $val;
                $res['res'] .= $res['prefix'] . $res['field'] . $res['cond'] . "'" . $res['val'] . "'" . $res['postfix'];

                if($i < ($num - 1)){
                    $res['res'] .= ' OR ';
                }
            }
        }

        if($num > 1 || !empty($exception)){
            if(!empty($exception) && mb_strpos($exception, '=|') === 0){
                // Direct SQL
                $res['res'] = ' AND (' . $res['res'] . mb_substr($exception, 2) . ')';
            }else if(!empty($exception)){
                $res['res'] = ' AND (0' . $exception . ')';
            }else{
                $res['res'] = ' AND (' . $res['res'] . ')';
            }
        }else{
            $res['res'] = ' AND (' . $res['res'] . ')';
        }

        return $res;
    }

    /**
     * Prepares filter field value for using in SQL query.
     *
     * @param  string $fieldName   Field name
     * @param  string $fieldValue  Field value
     * @param  string $fieldType   Field type
     * @param  bool   $encodeHTML  Encode flag
     * @return string
     * @amidev Temporary
     */
    protected function prepareSqlField($fieldName, $fieldValue, $fieldType, $encodeHTML = TRUE){
        if($fieldType == 'text'){
            /*
            Unused ???
            $supposedPostName = $fieldName;
            if(strncmp($supposedPostName, 'flt_', 4) == 0){
                $supposedPostName = mb_substr($supposedPostName, 4);
            }
            */
            if($encodeHTML){
                $fieldValue =  AMI_Lib_String::htmlChars($fieldValue);
            }
        }
        $fieldValue = addslashes($fieldValue);
        return $fieldValue;
    }

    /**
     * Adds filter field.
     *
     * @param  array $aAddField  Field data
     * @return mixed
     * @amidev Temporary
     */
    protected function addField(array $aAddField){
        if($this->tryToLoadDateFmt){
            // Load date format from options first time method is called
            $this->tryToLoadDateFmt = FALSE;
            $option = 'dateformat_' . (AMI_Registry::get('side') === 'adm' ? 'admin' : 'front');
            if(AMI::issetOption('core', $option)){
                $aFMT = AMI::getOption('core', $option);
                $locale = AMI_Registry::get('lang_data');
                if(isset($aFMT[$locale])){
                    $aFMT = $aFMT[$locale];
                }else{
                    $aFMT = null;
                    trigger_error("Date format is not set for locale '" . $locale . "'", E_USER_WARNING);
                }
                $this->aDateFMT['conf'] = DateTools::getJustDateFormat($aFMT);
                $this->aDateFMT['conf_dtime'] = $aFMT;
                $this->aDateFMT['php'] = DateTools::conf2phpFormat($this->aDateFMT['conf']);
                $this->aDateFMT['db'] = DateTools::php2mysqlFormat($this->aDateFMT['php']);
                $this->aDateFMT['php_dtime'] = DateTools::conf2phpFormat($this->aDateFMT['conf_dtime']);
                $this->aDateFMT['db_dtime'] = DateTools::php2mysqlFormat($this->aDateFMT['php_dtime']);
                $this->aDateFMT['php_time'] = DateTools::conf2phpFormat($this->aDateFMT['conf_dtime'], TRUE);
                $this->aDateFMT['db_time'] = DateTools::php2mysqlFormat($this->aDateFMT['php_dtime'], TRUE);
            }
        }

        // Backward capability
        $cName = $aAddField['name'];
        $cType = isset($aAddField['flt_type']) ? $aAddField['flt_type'] : $aAddField['type'];
        $cDefaultValue = isset($aAddField['flt_default']) ? $aAddField['flt_default'] : '';
        $cCondition = isset($aAddField['flt_condition']) ? $aAddField['flt_condition'] : '';
        $cTableFieldName = isset($aAddField['flt_column']) ? $aAddField['flt_column'] : '';
        $bSessionField = !empty($aAddField['session_field']);
        $cMultiValues = '';
        $cMultiple = isset($aAddField['multiple']) && $aAddField['multiple'] ? 1 : 0;
        $cSize = 1;
        $showAll = isset($aAddField['not_selected']) ? $aAddField['not_selected'] : false;
        $disableEmpty = isset($aAddField['disable_empty']) ? $aAddField['disable_empty'] : false;

        if(!in_array($cType, $this->aAllowedTypes)){
            // Validate field type
            trigger_error("Unknown filter field [$cName] type [$cType].", E_USER_WARNING);
            return FALSE;
        }

        if(isset($this->aData[$cName])){
            $cValue = $this->aData[$cName];
            if($bSessionField){
                // Save session field to cookies for an hour
                AMI::getSingleton('env/cookie')->set(self::getCookieName($cName), $cValue, time() + 3600);
            }
        }else{
            $cValue = '';
        }

        if(is_array($cValue)){
            // Trim all array values
            foreach($cValue as $index => $val){
                $cValue[$index] = trim($val);
            }
            $vVal = $cValue;
        }else{
            $vVal = trim($cValue);
            if($vVal == ''){
                $vVal = $cDefaultValue;
            }
        }

        $res = $vVal;
        $aField = $this->aExtraFieldParams;
        // Field type
        $aField['type'] = $cType;
        // Flag specifying ability to have multiple selected values
        $aField['multiple'] = $cMultiple;
        // Number of elements in field
        $aField['size'] = $cSize > 1 ? $cSize : '1';
        // =, >=, like and etc.
        $aField['condition'] = $cCondition;
        // Table field name if isn't same as filter field name
        $aField['table_field'] = $cTableFieldName;
        // Table alias
        $aField['table_alias'] = '';
        if(isset($aAddField['flt_alias'])){
            // Prepend field alias
            $aField['table_alias'] = $aAddField['flt_alias'] != ' ' ? $aAddField['flt_alias'] . '.' : ' ';
        }
        // Array of values to force disable SQL
        $aField['show_all'] = $showAll;
        // Flag specifying to disable SQL filter
        $aField['disableSQL'] = !empty($aAddField['disableSQL']);
        // Flag specifying to store field value in session and
        // repair it when admin user returns to module page
        $aField['sessionField'] = $bSessionField;
        // Flag specifying to do not isolate value by "'" is SQL
        $aField['act_as_int'] = !empty($aAddField['act_as_int']);

        // Todo: need to check the real field format
        if($cType == 'date'){
            if(strpos($cCondition, '<') === 0){
                $aField['add_time'] = ' 23:59:59';
            }
            if(strpos($cCondition, '>') === 0){
                $aField['add_time'] = ' 00:00:00';
            }
        }

        if(empty($aField['table_field'])){
            // If no table field passed filter field name will be used
            $aField['table_field'] = $cName;
        }

        if($disableEmpty && !$vVal){
            // Disable SQL on empty values
            $aField['disableSQL'] = TRUE;
        }

        switch($cType){
            case 'numeric':
                $aField['value'] = doubleval($vVal);
                break; // case 'numeric'

            case 'timestamp':
            case 'date':
                // Check if processing of date not required
                if($cDefaultValue === false){
                    if(!is_array($cValue) && empty($cValue)){
                        $res = $vVal = '';
                        break;
                    }else if(is_array($cValue) && mb_strpos($aField['condition'], 'interval') !== false && empty($cValue[0]) && empty($cValue[1])){
                        $res = $vVal = array('', '');
                        break;
                    }else if(is_array($cValue)){
                        $isEmpty = true;
                        for($i = 0; $i < count($cValue); $i++){
                            if(!empty($cValue[$i])){
                                $isEmpty = false;
                                break;
                            }
                        }
                        if($isEmpty){
                            $res = $vVal = array();
                            break;
                        }
                    }
                }

                $vDefault = $cDefaultValue;
                if($vDefault === false){
                    $vDefault = '';
                }else if(empty($vDefault)){
                    $vDefault = time();
                }
                if(!is_array($this->aDateFMT)){
                    trigger_error("Filter: date formats is not initialized.", E_USER_WARNING);
                }

                $valIsArr = is_array($vVal);
                if(!$valIsArr){
                    $vVal = array($vVal);
                    $cValue = array($cValue);
                    $uDate = array();
                    $aField['uvalue'] = array();
                    $aField['value'] = array();
                }

                // Generate message for invalid date
                $isMessgGenerated = FALSE;
                for($i = 0; $i < sizeof($vVal); $i++){
                    if(empty($cValue[$i]) && $cDefaultValue === FALSE){
                        continue;
                    }
                    if(
                        !$isMessgGenerated &&
                        !empty($cValue[$i]) &&
                        (DateTools::isvaliddate($vVal[$i], $this->aDateFMT["conf"]) === FALSE)
                    ){
                        $aStat = array();
                        $aStat['message'] = $this->aCaptions[$cName];
                        $key = 'invalid_value_' . $cName;
                        if(isset($this->aMessages[$key])){
                            $aStat['type'] = $key;
                            $aStat['message'] = $this->aMessages[$key];
                        }else{
                            $aStat['type'] = 'invalid_value';
                            $aStat['message'] = $this->aMessages['invalid_value'] . ' [' . $aStat['message'] . ']';
                        }
                        $this->aStatus[] =$aStat;
                        $isMessgGenerated = TRUE;
                    }

                    // @todo Need to move getting $format to common place
                    $locale = AMI_Registry::get('lang_data');
                    $optionName = 'dateformat_' . (AMI_Registry::get('side') === 'frn' ? 'front' : 'admin');
                    if(AMI::issetOption('core', $optionName)){
                        $aFormat = AMI::getOption('core', $optionName);
                        if(isset($aFormat[$locale])){
                            $format = DateTools::getJustDateFormat($aFormat[$locale]);
                        }
                    }else{
                        // workaround for fast entry point
                        $format = DateTools::php2confFormat(
                            AMI::getDateFormat(AMI_Registry::get('lang', 'en'), AMI_Lib_Date::FMT_DATE)
                        );
                    }

                    // Correct min/max dates
                    if($cCondition != '<='){
                        $uDate[$i] = DateTools::getCorrectUDate($vVal[$i], $format, $vDefault);
                    }else{
                        $uDate[$i] = DateTools::getCorrectUDate($vVal[$i], $format, $vDefault);
                        $uDate[$i] = DateTools::getEndDayTimestamp($uDate[$i]);
                    }
                    $vVal[$i] = date($this->aDateFMT['php'], $uDate[$i]);
                    if($cType == 'timestamp' && empty($cDefaultValue) && empty($cValue[$i])){
                        $aField['value'][$i] = '';
                    }else{
                        $aField['value'][$i] = $vVal[$i];
                    }
                    $aField['uvalue'][$i] = $uDate[$i];
                }

                if(!$valIsArr){
                    $vVal = $vVal[0];
                    $cValue = $cValue[0];
                    $uDate = $uDate[0];
                    $aField['uvalue'] = $aField['uvalue'][0];
                    $aField['value'] = $aField['value'][0];
                }

                $aField['min_max'] = $cMultiValues;
                $res = $uDate;
                break; // case 'date', case 'timestamp'

            case 'flagmap':
                $aField['value'] = $vVal;
                $aField['params'] = $cMultiValues;
                break; // case 'flagmap'

            case 'select':
                if(
                    is_array($aField['show_all']) &&
                    isset($aField['show_all']['id']) &&
                    $vVal === $aField['show_all']['id']
                ){
                    $aField["disableSQL"] = TRUE;
                }
                if($aField['multiple']){
                    $cMultiValues = explode(';', rtrim($vVal, ';'));
                }
                $aField['value'] = $vVal;
                $aField['multi_values'] = $cMultiValues;
                break; // case 'select'

            case 'radio':
            case 'checkbox':
                $aField['value'] = $vVal;
                $aField['multi_values'] = $cMultiValues;
                break; // case 'radio', case 'checkbox'

            case 'static':
                $aField['disableSQL'] = TRUE;
                $aField['template'] = empty($cValue) ? 'static' : $cValue;
                $aField['value'] = $cDefaultValue;
                break; // case 'static'

            case 'Vsplitter':
            case 'Hsplitter':
            case 'Sblock':
            case 'Fblock':
                // These fields don't affect on SQL
                $aField['disableSQL'] = TRUE;
                break;

            default:
                $aField['value'] = $vVal;
                $res = $vVal;
                break; // default
        }

        if(!isset($this->aFields[$cName])){
            $aField['position'] = $this->fieldsCount;
            $this->fieldsCount++;
        }else{
            $aField['position'] = $this->aFields[$cName]['position'];
        }
        $this->aFields[$cName] = $aField;
        return $res;
    }

    /**
     * Setting extra field params.
     *
     * @param  array $aParams  Parameters
     * @return void
     * @amidev Temporary
     */
    protected function setExtraFieldParams(array $aParams = array()){
        $this->aExtraFieldParams = $aParams;
    }

    /**
     * For custom data manipulation.
     *
     * Example:
     * <code>
     * // $field - Filter field name
     * // $aData - array that contains filter field data, wich will be
     * //          used to build the list SQL query.
     * //          keys:
     * //             'value' => current filter field's value
     * //             'skip' => if set to true to skip filtering by this field
     * protected function processFieldData($field, array $aData){
     *     // skip filtering by no_filter field if it has a value
     *     if(($field == 'no_filter') && $aData['value']){
     *         $aData['skip'] = true;
     *     }
     *     return $aData;
     * }
     * </code>
     *
     * @param string $field  Field name
     * @param array $aData  Filter data
     * @return array
     * @since 5.14.0
     */
    protected function processFieldData($field, array $aData){
        return $aData;
    }
}
