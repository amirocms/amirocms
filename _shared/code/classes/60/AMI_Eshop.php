<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module_Catalog
 * @version   $Id: AMI_Eshop.php 50168 2014-04-22 08:48:30Z Leontiev Anton $
 * @since     6.0.2
 */

/**
 * E-shop business layer.
 *
 * @package    Module_Catalog
 * @subpackage Controller
 * @resource   eshop <code>AMI::getSingleton('eshop')</code>
 * @since      6.0.2
 */
class AMI_Eshop{
    /**
     * Instance
     *
     * @var AMI_Eshop
     */
    private static $oInstance;

    /**
     * Section/owner
     *
     * @var string
     */
    private $section = 'eshop';

    /**
     * Database tables prefix
     *
     * @var string
     */
    private $dbTablePrefix = 'cms_es_';

    /**#@+
     * Price format option.
     */

    /**
     * Number of decimals
     *
     * @var int
     */
    private $numberDecimals;

    /**
     * Number of decimals in percents
     *
     * @var int
     */
    private $numberDecimalsPer;

    /**
     * Number of decimal points
     *
     * @var int
     */
    private $decimalPoint;

    /**
     * Thousands separator
     *
     * @var string
     */
    private $thousandsSeparator;

    /**#@-*/

    /**
     * Array having currency codes as keys and data structs as values
     *
     * @var array
     * @see AMI_Eshop::initCurrency()
     */
    private $aCurrency;

    /**
     * Currency data backup
     *
     * @var array
     * @see AMI_Eshop::saveCurrencyData()
     * @see AMI_Eshop::restoreCurrencyData()
     */
    private $aCurrencyBackup;

    /**
     * Default currency code
     *
     * @var string
     * @see AMI_Eshop::initCurrency()
     */
    private $defaultCurrency;

    /**
     * Base currency code
     *
     * @var string
     * @see AMI_Eshop::initCurrency()
     */
    private $baseCurrency;

    /**
     * Price fields
     *
     * @var array
     * @see AMI_Eshop::initPrices()
     */
    private $aPriceFields;

    /**
     * Prices number
     *
     * @var int
     * @see AMI_Eshop::initPrices()
     */
    private $numPrices;

    /**
     * Do other prices enabled
     *
     * @var bool
     * @see AMI_Eshop::initPrices()
     */
    private $otherPricesEnabled;

    /**
     * Is items counters enabled
     *
     * @var bool
     */
    private $itemCountersEnabled;

    /**
     * Flag specifying that fractional product quantity is allowed
     *
     * @var bool
     */
    private $allowFractionalQty = FALSE;

    /**
     * Core v.5. Eshop instance
     *
     * @var Eshop
     */
    private $oEshop = null;

    /**
     * Returns an instance of AMI_Eshop.
     *
     * @return AMI_Eshop
     * @amidev
     */
    public static function getInstance(){
        if(is_null(self::$oInstance)){
            self::$oInstance = new AMI_Eshop();
        }
        return self::$oInstance;
    }

    /**
     * Updates categories has_props field.
     *
     * @param string $prefix     Table prefix (es, kb, po)
     * @param mixed $idCategory  Category id or null
     * @param mixed $idDataset   Dataset id or null
     * @return boolean
     * @amidev
     */
    public static function updateCatProps($prefix, $idCategory=null, $idDataset=null){
        if(!in_array($prefix, array('es', 'kb', 'po'))){
            trigger_error('Invalid table prefix "' . $prefix . '"', E_USER_WARNING);
            return FALSE;
        }
        $idCategory = (int)$idCategory;
        $idDataset = (int)$idDataset;

        $oDB = AMI::getSingleton('db');
        if(!$idCategory && !$idDataset){
            // Can be slow for large props tables
            $sql = "SELECT DISTINCT id_category FROM cms_" . $prefix . "_props";
            $oCol = $oDB->fetchCol($sql);
            $catIds = FALSE;
            if(count($oCol)){
                $catIds = (string)$oCol;
            }
        }elseif($idCategory && !$idDataset){
            $sql = "SELECT dataset_id FROM cms_" . $prefix . "_cats WHERE id = " . $idCategory;
            $idDataset = (int)$oDB->fetchValue($sql);
            if(!$idDataset){
                return FALSE;
            }
        }

        if($idDataset){
            $hasProps = 0;
            $sql = "SELECT fields_map FROM cms_" . $prefix . "_datasets WHERE id=" . $idDataset;
            $fields = $oDB->fetchValue($sql);
            if($fields){
                $aFields = explode(';', trim($fields, ';'));
                $aNFields = array();
                foreach($aFields as $field){
                    if(is_numeric($field) && ($field < 100000)){
                        $aNFields[] = $field;
                    }
                }
                if(count($aNFields)){
                    $nFields = implode(",", $aNFields);
                    $sql = "SELECT is_prop FROM cms_" . $prefix . "_custom_types WHERE id IN (" . $nFields . ")";
                    $oFields = $oDB->fetchCol($sql);
                    foreach($oFields as $fieldValue){
                        if($fieldValue){
                            $hasProps = 1;
                            break;
                        }
                    }
                }
            }
        }

        if($idCategory){
            $sql = "UPDATE cms_" . $prefix . "_cats SET has_props=" . $hasProps . " WHERE id=" . $idCategory;
            $oDB->query($sql);
            return TRUE;
        }elseif($idDataset){
            $sql = "UPDATE cms_" . $prefix . "_cats SET has_props=" . $hasProps . " WHERE dataset_id=" . $idDataset;
            $oDB->query($sql);
        }else{
            $sql = "UPDATE cms_" . $prefix . "_cats SET has_props=0 WHERE has_props=1";
            $oDB->query($sql);
            if($catIds){
                $sql = "UPDATE cms_" . $prefix . "_cats SET has_props=1 WHERE id IN (" . $catIds . ") AND has_props=0";
                $oDB->query($sql);
            }
        }
        return TRUE;
    }

    /**
     * Formats money.
     *
     * @param  float  $value         Value to format
     * @param  string $currency      Currency code
     * @param  bool   $onlyPositive  Flag specifying to return only positive numbers
     * @return string
     * @since  6.0.6
     */
    public function formatMoney($value, $currency = null, $onlyPositive = TRUE){
        if(is_null($currency)){
            $currency = $this->baseCurrency;
        }
        $res = '';
        if($value . '' != ''){
            $value = $this->formatNumber($value, $onlyPositive);
            $res = $this->getCurrencyString($value, $currency);
        }
        return $res;
    }

    /**
     * Set currency data.
     *
     * @param  array  $aData     Currency data
     * @param  string $currency  New base currency
     * @return AMI_Eshop
     * @amidev
     */
    public function setCurrencyData(array $aData, $currency){
        $this->aCurrency = $aData;
        $this->baseCurrency = $currency;
        return $this;
    }

    /**
     * Stores current currency data.
     *
     * @return AMI_Eshop
     * @amidev
     */
    public function storeCurrencyData(){
        $this->aCurrencyBackup = array($this->aCurrency, $this->baseCurrency);
        return $this;
    }

    /**
     * Restores currency data from backup.
     *
     * @return AMI_Eshop
     * @amidev
     */
    public function restoreCurrencyData(){
        list($this->aCurrency, $this->baseCurrency) = $this->aCurrencyBackup;
        $this->aCurrencyBackup = null;
        return $this;
    }

    /**
     * Converts currency.
     *
     * @param  float  $price  Price to convert
     * @param  string $from   Source currency code
     * @param  string $to     Destination currency code
     * @return float
     */
    public function convertCurrency($price, $from, $to){
        $from = $this->correctCurrencyCode($from);
        $to = $this->correctCurrencyCode($to);
        if($from != $to){
            if(isset($this->aCurrency[$from])){
                $price /= $this->aCurrency[$from]['exchange'];
            }
            if(isset($this->aCurrency[$to])){
                $price *= $this->aCurrency[$to]['exchange'];
            }
        }
        return $price;
    }

    /**
     * Returns TRUE if currency is present.
     *
     * @param  string $code  Code
     * @return bool
     * @amidev
     */
    public function issetCurrency($code){
        $currCode = $this->correctCurrencyCode($code);
        return isset($this->aCurrency[$code]);
    }

    /**
     * Formats number according to the price options.
     *
     * @param  float $value         Value to format
     * @param  bool  $onlyPositive  Flag specifying to return only positive numbers
     * @param  bool  $forCalc       Flag specifying to return numbers for calculation
     * @return float
     * @amidev
     */
    public function formatNumber($value, $onlyPositive = TRUE, $forCalc = FALSE){
        if($forCalc){
            $delim = '';
            $decimalPoint = '.';
        }else{
            $delim = $this->thousandsSeparator;
            $decimalPoint = $this->decimalPoint;
        }
        $res =
            number_format(
                $onlyPositive ? max(0, round($value, $this->numberDecimals)) : round($value, $this->numberDecimals),
                $this->numberDecimals,
                $decimalPoint,
                $delim
            );
        return $res;
    }

    /**
     * Formats percent.
     *
     * @param  float $value         Value to format
     * @param  bool  $onlyPositive  Flag specifying to return positive only numbers
     * @param  bool  $addSign       Flag specifying to return string with percent sign
     * @return float
     * @amidev
     */
    public function formatPercent($value, $onlyPositive = TRUE, $addSign = FALSE){
        if($onlyPositive){
            $res = number_format(max(0, round($value, $this->numberDecimalsPer)), $this->numberDecimalsPer, '.', '');
        }else{
            $res = number_format(round($value, $this->numberDecimalsPer), $this->numberDecimalsPer, '.', '');
        }
        if($addSign){
            $res .= "&nbsp;%";
        }
        return $res;
    }

    /**
     * Formats tax value depending on type and currency.
     *
     * @param float $value     Current tax value
     * @param array $aOptions  Formatting options
     * @return mixed
     * @amidev
     */
    public function formatTax($value, array $aOptions){

        $aOptions += array(
            'type'              => 'percent',
            'currencyFromn'     => "",
            'currencyTo'        => "",
            'currencyAddSign'   => true
        );
        extract($aOptions);

        if($type == "percent"){
            $value = $this->formatPercent($value, false, true);
        }else{
            $value = $this->convertCurrency($value, $currencyFrom, $currencyTo);
            $value = $this->formatNumber($value);
            if($currencyAddSign){
                $value = $this->getCurrencyString($value, $currencyTo);
            }
        }
        return $value;
    }

    /**
     * Is item type is enabled.
     *
     * @param  string $itemType  Item type
     * @return bool
     * @todo   Implement
     * @amidev
     */
    public function extItemTypeIsEnabled($itemType){
        return FALSE; // array_search($itemType, $this->aAllowExtItemTypes);
    }

    /**
     * Returns current base currency code.
     *
     * @return string
     * @amidev
     */
    public function getBaseCurrency(){
        return $this->baseCurrency;
    }

    /**
     * Returns default currency data.
     *
     * @return array
     * @amidev
     */
    public function getDefaultCurrencyData(){
        return
            isset($this->aCurrency[$this->defaultCurrency])
            ? $this->aCurrency[$this->defaultCurrency]
            : array();
    }

    /**
     * Returns number of decimals.
     *
     * @return int
     * @amidev
     */
    public function getNumberDecimals(){
        return $this->numberDecimals;
    }

    /**
     * Returns decimal point.
     *
     * @return string
     * @amidev
     */
    public function getDecimalPoint(){
        return $this->decimalPoint;
    }

    /**
     * Returns thousands separator.
     *
     * @return string
     * @amidev
     */
    public function getThousandsSeparator(){
        return $this->thousandsSeparator;
    }

    /**
     * Returns TRUE is other prices are enabled.
     *
     * @return bool
     * @amidev
     */
    public function areOtherPricesEnabled(){
        return $this->otherPricesEnabled;
    }

    /**
     * Returns TRUE if item counters are enabled.
     *
     * @return bool
     * @amidev
     */
    public function isItemCountersEnabled(){
        return $this->itemCountersEnabled;
    }

    /**
     * Returns TRUE if fractional product quantity is allowed.
     *
     * @return bool
     */
    public function isFractionalQtyAllowed(){
        return $this->allowFractionalQty;
    }

    /**
     * Returns array of extra price numbers.
     *
     * @return array
     * @amidev
     */
    public function getOtherPrices(){
        return $this->aPriceFields;
    }

    /**
     * Get currency data.
     *
     * @return array
     * @amidev
     */
    public function getCurrencies(){
        return $this->aCurrency;
    }

    /**
     * I don't have an idea what's that for.
     *
     * @param  string $format      Format
     * @param  string $delim       Delimiter
     * @param  string $prefix      Prefix
     * @param  string $postfix     Postfix
     * @param  string $replString  Replacement
     * @return string
     * @amidev
     */
    public function getPriceFields($format = '%d', $delim = ',', $prefix = ',', $postfix = '', $replString = '%d'){
        return $this->getOtherPricesFields($format, $delim, $prefix, $postfix, $replString);
    }

    /**
     * Returns other prices columns (for items & cats).
     *
     * @param  string $prefix     Item columns prefix, pass NULL to exclude item fields
     * @param  string $catPrefix  Category columns prefix, pass NULL to exclude category fields
     * @return array
     */
    public function getOtherPricesColumns($prefix = '', $catPrefix = 'cat_'){
        $aResult = array();
        if($this->otherPricesEnabled){
            foreach($this->aPriceFields as $num){
                if(!is_null($prefix)){
                    $aResult[] = $prefix. 'price' . $num;
                }
                if(!is_null($catPrefix)){
                    $aResult[] = $catPrefix . 'price' . $num;
                }
            }
        }
        return $aResult;
    }

    /**
     * I don't have an idea what's that for.
     *
     * @param  string $format      Format
     * @param  string $delim       Delimiter
     * @param  string $prefix      Prefix
     * @param  string $postfix     Postfix
     * @param  string $replString  Replacement
     * @return string
     * @amidev
     */
    private function getOtherPricesFields($format, $delim, $prefix, $postfix, $replString){
        $aRes = array();
        $res = '';
        if($this->otherPricesEnabled){
            if(!empty($tablePrefix)){
                $tablePrefix .= '.';
            }
            for($i=0;$i<$this->numPrices ;$i++){
                $aRes[] = str_replace($replString, $this->aPriceFields[$i], $format);
            }
            if(sizeof($aRes) > 0){
                $res = $prefix . implode($delim, $aRes) . $postfix;
            }
        }
        return $res;
    }

    /**
     * Return calculated price data for specified additional price.
     *
     * @param  array $aPrice  Price data array, keys:
     *         - <b>price</b>: price value;
     *         - <b>priceNumber</b>: price number 1..N, empty by default;
     *         - <b>tax</b>: tax value;
     *         - <b>taxType</b>: tax type ('percent', 'abs');
     *         - <b>chargeTaxType</b>: charge tax type ('charge', 'detach');
     *         - <b>discount</b>: discount value;
     *         - <b>discountType</b>: discount type ('percent', 'abs')
     *         - <b>currency</b>: currency code of result price;
     *         - <b>dbCurrency</b>: currency code of incoming price;
     *         - <b>addCurrency</b>: add curency name to result, true by default.
     * @return array
     */
    public function calcPrice(array $aPrice){
        $aPrice += array(
            'price'         => 0,
            'priceNumber'   => '',
            'tax'           => '',
            'taxType'       => '',
            'chargeTaxType' => '',
            'discount'      => '',
            'discountType'  => '',
            'currency'      => '',
            'dbCurrency'    => $this->defaultCurrency,
            'addCurrency'   => true,
            'cCustom'       => false,
            'taxesData'     => array()
        );
        $this->_initEshopV5();
        extract($aPrice);
        return $this->oEshop->calcPrice($price, $priceNumber, $tax, $taxType, $chargeTaxType, $discount, $discountType, $currency, $dbCurrency, $addCurrency, $cCustom, $taxesData);
    }

    /**
     * Initializes currencies.
     *
     * @return void
     * @amidev
     */
    private function initCurrency(){
        $modId = $this->section . '_currency';
        if(
            AMI::isModInstalled($modId) &&
            AMI::issetOption($modId, 'page_sort_col')
        ){
            $this->aCurrency = array();
            $oQuery =
                DB_Query::getSnippet(
                    "SELECT `id`, `name`, `code_digit`, `code`, `exchange`, `prefix`, `postfix`, `source`, `fault_attempts`, `is_base`, `on_small` " .
                    "FROM " . $this->dbTablePrefix . "currency " .
                    "WHERE `lang`= %s " .
                    "ORDER BY `code` ASC"
                )->q(AMI_Registry::get('lang_data'));
            /**
             * @var DB_Recordset
             */
            $oRS = AMI::getSingleton('db')->select($oQuery);
            foreach($oRS as $aRecord){
                $this->aCurrency[$aRecord['code']] = $aRecord;
                if($aRecord['is_base']){
                    $this->baseCurrency = $aRecord['code'];
                }
            }
        }
        $modId = $this->section . '_order';
        if(
            AMI::isModInstalled($modId) &&
            AMI::issetOption($modId, 'default_currency')
        ){
            $aCurrency = AMI::getOption($modId, 'default_currency');
            $this->defaultCurrency =
                isset($aCurrency[AMI_Registry::get('lang_data')])
                ? $aCurrency[AMI_Registry::get('lang_data')]
                : '';
        }
    }

    /**
     * Initializes prices.
     *
     * @return void
     * @amidev
     */
    private function initPrices(){
        $this->aPriceFields = array();
        $aOption = AMI::getOption($this->section . '_item', 'extrafield_price_on');
        if(!is_array($aOption)){
            $aOption = array($aOption);
        }
        foreach($aOption as $val){
            if(mb_strpos($val, 'price') !== FALSE){
                $num = intval(mb_substr($val, 5));
                if($num > 0){
                    $this->aPriceFields[] = $num;
                }
            }
        }
        $this->numPrices = sizeof($this->aPriceFields);
        $this->otherPricesEnabled = $this->numPrices > 0;
    }

    /**
     * Corrects currency code.
     *
     * @param  string $code  Currency code
     * @return string
     * @amidev
     */
    private function correctCurrencyCode($code){
        if(empty($code)){
            $code = $this->baseCurrency;
        }
        return $code;
    }

    /**
     * Formats value according to currency.
     *
     * @param  string $value     Price value
     * @param  string $currency  Currency code
     * @return string
     * @amidev
     */
    public function getCurrencyString($value, $currency){
        $res = $value;
        if(isset($this->aCurrency[$currency])){
            $delim = ' ';
            $prefix = $this->aCurrency[$currency]['prefix'];
            $postfix = $this->aCurrency[$currency]['postfix'];
            $prefix .= empty($prefix) ? '' : $delim;
            $postfix = (empty($postfix) ? '' : $delim) . $postfix;
            $res = $prefix . $res . $postfix;
        }
        return $res;
    }

    /**
     * Returns currency prefix.
     *
     * @param  string $currency  Currency code
     * @return string
     * @amidev
     */
    public function getCurrencyPrefix($currency){
        $res = false;
        $currency = $this->correctCurrencyCode($currency);
        if(isset($this->aCurrency[$currency])){
            $res = $this->aCurrency[$currency]['prefix'];
        }
        return $res;
    }

    /**
     * Returns currency postfix.
     *
     * @param  string $currency  Currency code
     * @return string
     * @amidev
     */
    public function getCurrencyPostfix($currency){
        $res = false;
        $currency = $this->correctCurrencyCode($currency);
        if(isset($this->aCurrency[$currency])){
            $res = $this->aCurrency[$currency]['postfix'];
        }
        return $res;
    }

    /**
     * Returns information about product by id.
     *
     * @param int $itemId  Catalog item id
     * @return array|null
     */
    public function getProductInfo($itemId){
        $oItem = AMI::getResourceModel($this->section . '_item/table', array(array('extModeOnConstruct' => 'common')))
            ->setAttr('useCopies', TRUE)
            ->setAttr('loadProperties', TRUE)
            ->find($itemId);
        if($oItem){
            $catId = $oItem->cat_id;
            $oCatItem = AMI::getResourceModel($this->section . '_cat/table', array(array('extModeOnConstruct' => 'common')))->find($catId);
            return array(
                'oItem'     => $oItem,
                'oCategory' => $oCatItem
            );
        }else{
            return null;
        }
    }

    /**
     * Initializes Core V5 Eshop instance.
     *
     * @return void
     */
    private function _initEshopV5(){
        $aLocale = array();
        if(is_null($this->oEshop)){
            $oGUI = AMI_Registry::get('oGUI');
            $aLocale = $oGUI->parseLangFile('templates/lang/eshop_item.lng');
            if(AMI_Registry::get('side') == 'adm'){
                $this->oEshop = new EshopAdmin($GLOBALS['cms'], $aLocale);
            }else{
                $this->oEshop = new EshopFront($GLOBALS['cms'], $aLocale);
            }
            $this->oEshop->init("");
        }
        return;
    }

    /**
     * Constructor.
     */
    private function __construct(){
        $modId = $this->section . '_item';
        if(AMI::isModInstalled($modId) && AMI::issetOption($modId, 'number_decimal_digits')){
            $this->numberDecimals     = AMI::getOption($modId, 'number_decimal_digits');
            $this->decimalPoint       = AMI::getOption($modId, 'decimal_point');
            $this->thousandsSeparator = AMI::getOption($modId, 'thousands_separator');
            $this->numberDecimalsPer  = AMI::getOption($modId, 'number_decimal_percent');

            $this->itemCountersEnabled = AMI::getOption($this->section . '_cat', 'item_counters_on');

            $this->initCurrency();
            $this->initPrices();
        }

        $modId = $this->section . '_order';
        if(AMI::isModInstalled($modId) && AMI::issetOption($modId, 'allow_fractional_quantity')){
            $this->allowFractionalQty = (bool)AMI::getOption($modId, 'allow_fractional_quantity');
        }
    }

    /**
     * Cloning is forbidden.
     */
    private function __clone(){
    }
}
