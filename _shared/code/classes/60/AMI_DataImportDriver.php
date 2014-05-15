<?php
/**
 * Base functionality for all import drivers.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   DataImportDriver
 * @version   $Id: AMI_DataImportDriver.php 44727 2013-11-29 15:06:09Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Data import driver interface.
 *
 * This interface declare methods which MUST containings in child driver class.
 *
 * @package     DataImportDriver
 * @since       x.x.x
 * @amidev      Temporary
 */
interface AMI_iDataImportDriver{
    /**
     * Main method which executed import from resource.
     *
     * Method must be overriden into child class
     * You can use method AMI_DataImportDriver::handleField() for handle field value
     * After import you must set flag $bIsImported to true.
     *
     * @return mixed
     */
    public function doImport();

    /**
     * This method must be overrided in child class and must returns false.
     *
     * If import resource is not available (i.e. site is down or file is not exists...).
     *
     * @return bool
     */
    public function isImportResourceAvailable();

    /**
     * Set resource name.
     *
     * @param mixed $resourceName  The model name of the module
     * @return $this
     */
    public function setResourceName($resourceName);

    /**
     * Setting up request settings.
     *
     * @param array $aRequestSettings  Array of the request settings
     * @return mixed
     */
    public function setRequestSettings(array $aRequestSettings = array());

    /**
     * Open stream to the resource - file or the site.
     *
     * @return $this
     */
    public function initConnection();

    /**
     * Read data from stream.
     *
     * @return $this
     */
    public function readData();

    /**
     * Close the stream.
     *
     * @return bool
     */
    public function closeConnection();
}

/**
 * Data import exception.
 *
 * Can be thrown when
 * - connection to resource was failed;
 * - reading data was failed;
 * - connection closing was failed;
 * - parsing (importing) readed data was failed.
 *
 * @package    DataImport
 * @since      x.x.x
 * @amidev     Temporary
 */
class AMI_DataImportException extends Exception{
    /**
     * Initializing error
     */
    const ERROR_INIT = 0x00;

    /**
     * Connection error
     */
    const ERROR_CONN_OPEN = 0x01;

    /**
     * Reading error
     */
    const ERROR_READ_DATA = 0x02;

    /**
     * Connection closing error
     */
    const ERROR_CONN_CLOSE = 0x03;

    /**
     * Importing (parsing) error
     */
    const ERROR_IMPORTING = 0x04;

    /**
     * Saving error
     */
    const ERROR_SAVING = 0x05;

    /**
     * Exception extra data
     *
     * @var mixed
     */
    protected $data;

    /**
     * Locale code of the message
     *
     * @var string
     */
    protected $localeCode;

    /**
     * Constructor.
     *
     * @param string $localeCode  The exception message
     * @param int    $code        The exception code
     * @param mixed  $data        The exception extra data
     */
    public function __construct($localeCode = '', $code = 0, $data = null){
        $this->localeCode = $localeCode;
        $this->code = $code;
        $this->data = $data;
    }

    /**
     * Returns locale code of the message.
     *
     * @return string
     */
    public function getLocaleCode(){
        return $this->localeCode;
    }

    /**
     * Returns exceprion data.
     *
     * @return mixed
     */
    public function getData(){
        return $this->data;
    }

    /**
     * Get localized string where all incoming parametres changed to placeholders values.
     *
     * @param string $localeString  Localized string with placeholders
     * @return string
     */
    public function getLocalized($localeString){
        return isset($this->data['placeholders'])
            ? vsprintf($localeString, $this->data['placeholders'])
            : $this->message;
    }
}


/**
 * Data import abstraction.
 *
 * @package     ModuleComponent
 * @since       x.x.x
 * @amidev      Temporary
 */
abstract class AMI_DataImportDriver implements AMI_iDataImportDriver{
    /**
     * Driver name
     *
     * @var string
     */
    protected $driverName;

    /**
     * Module identifier
     *
     * @var mixed
     */
    protected $modId;

    /**
     * Resource identifier
     *
     * @var string
     */
    protected $resourceId;

    /**
     * The model
     *
     * @var object
     */
    protected $oModel;

    /**
     * Import fields
     *
     * @var array
     */
    protected $aFields = array();

    /**
     * Imported data
     *
     * @var array
     */
    protected $aImportedData = array();

    /**
     * Fields for form pane
     *
     * @var array
     */
    protected $aSettingFields = array();

    /**
     * Available fields types
     *
     * @var array
     */
    protected $aTypes = array('varchar', 'text', 'date', 'timestamp', 'integer');

    /**
     * Import content encoding
     *
     * @var string
     */
    protected $contentEncoding = 'utf8';

    /**
     * Is data succesfully imported
     *
     * @var bool
     */
    protected $bIsImported = false;

    /**
     * Count of rows saved in database
     *
     * @var int
     */
    protected $savedRows = 0;

    /**
     * Errors count
     *
     * @var int
     */
    protected $errorsCount = 0;

    /**
     * Raw import data
     *
     * @var string
     */
    protected $rawData;

    /**
     * Import settings loaded from cms_data_import.driver_data
     *
     * @var array
     */
    protected $aImportSettings = array();

    /**
     * Available saving errors count
     *
     * @var integer
     */
    protected $availableErrorsCount = 10;

    /**
     * Item exception data
     *
     * @var array
     * @see AMI_DataImportDriver::save()
     */
    protected $aItemExceptionData;

    /**
     * Set up mod identifier.
     *
     * @param  string $modId  Module identifier
     * @return $this
     */
    public function setModId($modId){
        $this->modId = (string)$modId;
        return $this;
    }

    /**
     * Add fields into import array.
     *
     * Specification:
     *
     * <code>
     * $aField = array(
     *      'name'      => 'title',     //field name
     *      'handleAs'  => 'varchar',   //field handling type
     *      'mapTo'     => 'title',     //field name in destination table
     * );
     * </code>
     *
     * Handle types:
     * - varchar
     * - text
     * - date
     * - timestamp
     * - integer
     *
     * @param  array $aField  Array with import field specification
     * @return $this
     * @see    https://confluence.cmspanel.net:8443/pages/viewpage.action?pageId=7635069
     */
    public function addImportField(array $aField){

        if(isset($aField['handleAs']) && !in_array($aField['handleAs'], $this->aTypes)){
            trigger_error('Field handle type is unsupported', E_USER_WARNING);
        }

        if(!isset($aField['mapTo'])){
            trigger_error('Field mapping is not exist', E_USER_WARNING);
        }

        // $this->aFields[trim($aField['name'])] = $aField;
        $this->aFields[] = $aField;

        return $this;
    }

    /**
     * Add setting field which will be added into form pane.
     *
     * @param array $aField  Array with settings field specification
     * @return $this
     */
    public function addSettingField(array $aField){

        // checks field name - some names newer cannot be used
        if(in_array($aField['name'], array('table_name', 'table_fields', 'import_fields', 'driver_data', 'driver_settings', 'id_cat'))){
            trigger_error('This name is reserved and cannot be used: ' . $aField['name'], E_USER_WARNING);
        }

        $this->aSettingFields[$aField['name']] = $aField;
        return $this;
    }

    /**
     * Drop fields from storage.
     *
     * @param string $fieldName  Name of the field that will be dropped
     * @return $this
     */
    public function dropImportField($fieldName){
        // if(isset($this->aFields[$fieldName])){
        // unset($this->aFields[$fieldName]);
        // }
        foreach($this->aFields as $key => $aField){
            if($aField['name'] == $fieldName){
                unset($this->aFields[$key]);
                break;
            }
        }

        return $this;
    }

    /**
     * Returns added fields definitions.
     *
     * @return array
     */
    public function getImportFields(){
        return $this->aFields;
    }

    /**
     * Add fields to form pane.
     *
     * @param AMI_ModFormView $oFormView  Forn view controller object
     * @return $this
     */
    public function addToForm(AMI_ModFormView $oFormView){

        // get available modules list
        $oDeclarator = AMI_ModDeclarator::getInstance();
        $aModIds = array();
        foreach(array('ami_multifeeds5', 'ami_multifeeds') as $hypermod){
            foreach(array('news', 'articles', 'blog') as $config){
                $aTmpModIds = $oDeclarator->getRegistered($hypermod, $config);
                foreach($aTmpModIds as $modId){
                    if(AMI::isModInstalled($modId)){
                        $aModIds[] = $modId;
                    }
                }
            }
        }

        if(!sizeof($aModIds)){
            $oFormView
                ->addField(array('type' => 'hint', 'name' => 'no_destination_modules'));

            return $this;
        }

        $aMultifeedsModules = array();
        $aConfigurations = AMI_Service_Adm::getModulesCaptions($aModIds);
        foreach($aConfigurations as $cfgModId => $cfgName){
            if(!AMI::isCategoryModule($cfgModId)){
                $aModData = array(
                    'name'      => $cfgName,
                    'value'     => $cfgModId
                );
                if(AMI::issetAndTrueOption($cfgModId, 'use_categories')){
                    $aModData['attributes'] = array(
                        'ami-data-has-category' => 1
                    );
                }
                $aMultifeedsModules[] = $aModData;
            }
        }

        // add default fields which must have anyone driver
        $oFormView
            ->addField(
                array(
                    'name'     => 'table_name',
                    'type'     => 'select',
                    'data'     => $aMultifeedsModules,
                    'position' => 'data_driver.end'
                )
            )
            ->addField(
                array(
                    'name'     => 'id_cat',
                    'validate' => array('filled', 'required', 'int'),
                    'position' => 'data_driver.end'
                )
            )
            ->addField(array('name' => 'table_fields', 'type' => 'input', 'validate' => array('filled', 'required', 'table_fields'), 'position'  => 'data_driver.end'))
            ->addField(array('name' => 'import_fields', 'type' => 'input', 'validate' => array('filled', 'required'), 'position'  => 'data_driver.end'));

        // add fields added by self::addSettingsField()
        $aSettingsNames = array();

        // add fields which established in driver class
        if(!empty($this->aDriverSettings)){
            $this->aSettingFields = array_merge($this->aDriverSettings, $this->aSettingFields);
        }

        // add settings which established from the outside
        foreach($this->aSettingFields as $aField){
            $aTmpField = array(
                'name'          => $aField['name'],
                'type'          => $aField['type'],
                'validate'      => isset($aField['validate']) ? $aField['validate'] : array(),
                'data'          => isset($aField['data']) ? $aField['data'] : array(),
                'position'      => isset($aField['position']) ? $aField['position'] : 'data_driver.end',
                'attributes'    => array('class'  => $this->driverName . '_fields driver_fields'),
            );

            // add extended data
            foreach(array('hint', 'caption', 'value', 'caption_text', 'flt_default', 'attributes') as $k){
                if(isset($aField[$k])){
                    $aTmpField[$k] = $aField[$k];
                }
            }

            $oFormView->addField($aTmpField);

            $aSettingsNames[] = $aField['name'];
        }

        // add settings fielsd list
        $oFormView->addField(array('name' => 'driver_settings', 'type' => 'hidden', 'value' => implode(',', $aSettingsNames)));

        return $this;
    }

    /**
     * Set up content encoding.
     *
     * @param string $encoding  Encoding of the content
     * @return $this
     */
    public function setContentEncoding($encoding){
        if(!in_array($encoding, array('utf-8', 'utf-16', 'utf-32', 'windows-1251', 'windows-1252', 'koi8-r'))){
            trigger_error('Unable to set up encoding', E_USER_WARNING);
        }

        $this->contentEncoding = $encoding;

        return $this;
    }

    /**
     * Set up destination table and up the model.
     *
     * @param string $resourceId  Resource identifier
     * @return $this
     * @throws AMI_DataImportException Ininitalizing exception.
     */
    public function setResourceId($resourceId){
        if(AMI::isResource($resourceId . '/table/model')){
            $this->oModel = AMI::getResourceModel($resourceId . '/table');
        }else{
            throw new AMI_DataImportException(
                'exception_table_model_is_not_exists',
                AMI_DataImportException::ERROR_INIT
            );
        }
        $this->resourceId = $resourceId;

        return $this;
    }

    /**
     * Returns true if data succesfully imported.
     *
     * @return mixed
     */
    public function isImported(){
        return $this->bIsImported;
    }

    /**
     * Save data in destination table.
     *
     * @param integer $taskId           Task identifier
     * @param bool    $bAllowDuplicate  Add duplicated records
     * @return bool
     * @throws Exception Saving exception.
     */
    public function save($taskId = null, $bAllowDuplicate = false){
        if(!$this->isImported()){
            throw new AMI_DataImportException(
                'exception_data_is_not_imported',
                AMI_DataImportException::ERROR_SAVING
            );
        }

        if(!is_object($this->oModel)){
            throw new AMI_DataImportException(
                'exception_table_model_is_not_exists',
                AMI_DataImportException::ERROR_SAVING
            );
        }

        $oDb = AMI::getSingleton('db');

        // get items hashes
        $oService = AMI::getResource('data_import/service');
        $aNewHashes = array();
        if(!empty($taskId) && ctype_digit($taskId)){
            $aHashes = $oService->getHashes($taskId);
        }else{
            $aHashes = array();
        }

        // save data
        $x = 0;
        $errorsNum = 0;

        $useCats =
            isset($this->aRequestSettings) &&
            isset($this->aRequestSettings['id_cat']) &&
            AMI::issetAndTrueOption($this->resourceId, 'use_categories');

        foreach($this->aImportedData as $aRow){
            // get model item
            $oItem = $this->oModel->getItem();
            if($useCats){
                $oItem->id_cat = $this->aRequestSettings['id_cat'];
            }
            $i = 0;
            foreach($aRow as $fieldName => $fieldValue){
                // get field name in the table
                $mappedField = trim($this->aFields[$i]['mapTo']);
                // Cut Amiro.CMS gadgets HTML code
                if(
                    in_array($mappedField, array('announce', 'body')) &&
                    FALSE !== mb_strpos($fieldValue, '<span class="gadget"')
                ){
                    $fieldValue =
                        preg_replace(
                            '~<span class="gadget".*?</span class="gadget">~',
                            '',
                            $fieldValue
                        );
                }

                // echo $mappedField . '=' . $fieldValue . "\r\n";
                // set up field value
                $oItem->$mappedField = $fieldValue;

                $i++;
            }

            // add extra parameters
            $oItem->lang = 'ru';
            $oItem->public = 1;
            if(empty($oItem->date_created)){
                $oItem->date_created = date('Y-m-d H:i:s', time());
            }

            // check hash
            $hash = md5($oItem->header . $oItem->date_created);
            if(in_array($hash, $aHashes) && false == $bAllowDuplicate){
                continue 1;
            }

            // add event before save item
            $aEvent = array('oItem' => $oItem);
            /**
             * Fires before saving item during working of data import driver.
             *
             * @event      on_data_import_driver_before_save_item
             * @eventparam AMI_ModTableItem oItem   Table item model
             * @since      6.0.2
             */
            AMI_Event::fire('on_data_import_driver_before_save_item', $aEvent, $this->modId);
            $oItem = $aEvent['oItem'];

            // try to validate and save the data
            try{
                $oItem->save();
                // add new hash
                if(isset($taskId) && ctype_digit($taskId)){
                    $aNewHashes[] = array('id_task' => $taskId, 'data_hash' => $hash);
                }
            }catch(AMI_ModTableItemException $oException){
                if(
                    is_null($this->aItemExceptionData) &&
                    AMI_ModTableItemException::VALIDATION_FAILED === $oException->getCode()
                ){
                    $aData = $oException->getData();
                    $this->aItemExceptionData = $aData[0];
                    unset($aData);
                }
                // something is wrong but we have one more attempt..
                if($errorsNum < $this->availableErrorsCount){
                    $errorsNum++;
                    continue; // foreach($this->aImportedData as $aRow)
                }else{
                    throw new AMI_DataImportException(
                        is_null($this->aItemExceptionData)
                            ? 'exception_too_many_errors_while_saving'
                            : 'exception_too_many_errors_while_saving_details',
                        AMI_DataImportException::ERROR_SAVING,
                        $this->aItemExceptionData
                    );
                }
            }

            $x++;
        }

        // write new hashes
        if(!empty($aNewHashes)){
            foreach($aNewHashes as $aHash){
                if(!in_array($aHash['data_hash'], $aHashes)){
                    $oDb->query(
                        DB_Query::getInsertQuery('cms_data_import_history', $aHash, true)
                    );
                }
            }
        }

        $this->savedRows = $x;
        $this->errorsCount = $errorsNum;
        return true;
    }

    /**
     * Returns saved rows count.
     *
     * @return int
     */
    public function getSavedRowsCount(){
        return $this->savedRows;
    }

    /**
     * Returns count of errors of saving.
     *
     * @return int
     */
    public function getErrorsCount(){
        return $this->errorsCount;
    }

    /**
     * Returns count of errors of saving.
     *
     * @return array
     */
    public function getExceptionData(){
        return $this->aItemExceptionData;
    }

    /**
     * Returns imported rows count.
     *
     * @return int
     */
    public function getImportedRowsCount(){
        return count($this->aImportedData);
    }

    /**
     * Setting up count of available errors.
     *
     * @param integer $count  Available errors count
     * @return $this
     */
    public function setAvailableErrorsCount($count){
        $this->availableErrorsCount = (int)$count;

        return $this;
    }

    /**
     * Handle field value.
     *
     * @param string $fieldName  Name of the field
     * @return $this
     */
    protected function handleField($fieldName){
        if(!isset($this->aFields[$fieldName])){
            trigger_error('Field is not exist', E_USER_WARNING);
        }

        switch($this->aFields[$fieldName]['handleAs']){
            default:
            case 'varchar':
                // cut string to 255 symbols - it is maximum value of varchar in mysql
                $tmpValue = mb_substr((string)$this->aFields[$fieldName]['value'], 0, 255);
                break;

            case 'text':
                // remove potential xss
                $tmpValue = preg_replace(
                    '<script|<html|<head|<title|<body|<pre|<table|<a\s+href|<img|<plaintext|<cross\-domain\-policy#usi',
                    '',
                    $this->aFields[$fieldName]['value']
                );
                break;

            case 'integer':
                // prevent integer overflow
                // @see http://www.php.net/manual/en/language.types.integer.php
                $tmpValue = (int)$this->aFields[$fieldName]['value'];
                break;

            case 'date':
                // convert incoming string to timestamp and then convert it to our format
                $tmpValue = date('Y-m-d H:i:s', (int)strtotime((string)$this->aFields[$fieldName]['value'], time()));
                break;

            case 'timestamp':
                // convert any integer to 2010-12-14 23:59:24 and then convert to integer
                // if date is not valid we already get timestamp of 1970-01-01 00:00:01
                $tmpValue = (int)strtotime(date('Y-m-d H:i:s', (int)$this->aFields[$fieldName]['value']), time());
                break;
        }

        // this event adds additionally handle
        $aEvent = array('fieldName' => $fieldName, 'fieldValue' => $tmpValue);
        AMI_Event::fire('ami_data_import_driver_handle_field', $aEvent, $this->modId);
        $tmpValue = isset($aEvent['fieldValue']) ? $aEvent['fieldValue'] : null;

        $this->aFields[$fieldName]['value'] = $tmpValue;

        return $this;
    }

    /**
     * Handle form data after submit.
     *
     * @param string $name   Event name
     * @param array $aEvent  Event array
     * @return array
     */
    public function handleAfterFormSubmit($name, array $aEvent){
        return $aEvent;
    }

    /**
     * Returns raw imported data.
     *
     * @return string
     */
    public function getRawData(){
        if(false === $this->isImported()){
            trigger_error('Data is not imported', E_USER_WARNING);
        }

        return $this->rawData;
    }

    /**
     * Load settings for the import driver that can be used later.
     *
     * @param mixed $settings  Setting of the task for the driver
     * @return $this
     */
    public function loadSettings($settings){
        if(!empty($settings)){
            // settings already in array
            if(is_array($settings)){
                $this->aImportSettings = $settings;
            }elseif(is_string($settings) && mb_strlen($settings) > 4){
                // settings is a serialized string
                $tmpSettings = @unserialize($settings);
                if(is_array($tmpSettings) && !empty($tmpSettings)){
                    $this->aImportSettings = $tmpSettings;
                }
            }
        }

        return $this;
    }

    /**
     * Additional function that return current date.
     *
     * @param mixed $args  Some data with row information
     * @return bool|string
     */
    public function fieldCurrentDate($args){
        return date('Y-m-d H:i:s', time());
    }

}
