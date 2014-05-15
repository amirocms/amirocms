<?php
/**
 * Base functionality for all import drivers.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   DataImportDriver
 * @version   $Id: AmiClean_DataImport_AmiCsvImport.php 44226 2013-11-21 13:17:29Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * CSV data import driver.
 *
 * @package     DataImportDriver
 * @since       x.x.x
 * @amidev      Temporary
 */
class AmiClean_DataImport_AmiCsvImport extends AMI_File_DataImportDriver{
    /**
     * RSS driver name
     *
     * @var string
     */
    protected $driverName = 'ami_csv';

    /**
     * The constructor.
     */
    public function __construct(){
        $aList = $this->getFileList();

        if(!empty($aList)){
            $this->aDriverSettings[] = array('name' => 'file_name', 'type' => 'select', 'validate' => array('filled', 'file_name'), 'hint' => true, 'data' => $aList, 'position' => 'table_name.before' );
        }else{
            $this->aDriverSettings[] = array('name' => 'file_name', 'type' => 'static', 'hint' => true, 'position' => 'table_name.before', 'caption_text' => 'csv_files_not_found' );
        }

        $this->aDriverSettings[] = array('name' => 'csv_delimiter', 'type' => 'input', 'flt_default' => ';', 'attributes' => array('class' => 'ami_csv_fields driver_fields', 'maxlength' => 3));
        $this->aDriverSettings[] = array('name' => 'csv_enclosure', 'type' => 'input', 'flt_default' => '"', 'attributes' => array('class' => 'ami_csv_fields driver_fields', 'maxlength' => 3));
    }

    /**
     * Read the data from csv file.
     *
     * @return $this
     */
    public function readData(){

        $delimiter = !empty($this->aRequestSettings['csv_delimiter'])
            ? $this->aRequestSettings['csv_delimiter']
            : ',';
        $enclosure = !empty($this->aRequestSettings['csv_enclosure'])
            ? $this->aRequestSettings['csv_enclosure']
            : '"';

        $this->rawData = array();

        if($this->bIsInitialized){
            $aCsvData = array();
            $bIsSomethingImported = false;

            while(false !== ($aData = fgetcsv($this->stream, 0, $delimiter, $enclosure))){
                $bIsSomethingImported = true;
                $aCsvData[] = $aData;
            }

            // php-based csv parser are failed?
            if(false === $bIsSomethingImported){
                $aCsvData = array();

                $tmpContent = file_get_contents($this->fileName);
                if(!empty($tmpContent)){
                    foreach(explode(PHP_EOL, $tmpContent) as $aLines){
                        $aLine = array();
                        foreach(explode($delimiter, $aLines) as $aField){
                            $aLine[] = $aField;
                        }
                    }
                    $aCsvData[] = $aLine;
                }
            }

            $this->rawData = $aCsvData;
        }

        return $this;
    }

    /**
     * Prepare array for import to the table.
     *
     * @return bool
     */
    public function doImport(){
        $aImportedData = array();


        // if some data has been imported ..
        if(!empty($this->rawData)){
            foreach($this->rawData as $aImportedRow){
                $i = 0;
                $aData = array();
                foreach($this->aFields as $fieldData){
                    $fieldName = trim($fieldData['name']);

                    // field is method?
                    if(substr($fieldName, -2, 2) == '()'){
                        $methodName = 'field' . ucfirst(substr($fieldName, 0, strlen($fieldName) - 2));
                        if(method_exists($this, $methodName)){
                            // call driver method to parse the field
                            call_user_func_array(array($this, $methodName), array($aImportedRow));
                        }else{
                            $fieldValue = '';
                        }
                    }else{
                        $fieldValue = $aImportedRow[$i];
                    }

                    $aData[] = $fieldValue;

                    $i++;
                }
            }
            $aImportedData[] = $aData;
        }

        // add event before initializing connection
        $aEvent = array('aImportedData' => $aImportedData);
        AMI_Event::fire('ami_data_import_after_import', $aEvent, $this->modId);
        $aImportedData = $aEvent['aImportedData'];

        $this->bIsImported = true;
        $this->rawData = null;
        $this->aImportedData = $aImportedData;

        return true;
    }

    /**
     * Return all .csv files names from _mod_files/_upload.
     *
     * @return array
     */
    protected function getFileList(){
        $path = AMI_Registry::get('path/root') . '_mod_files/_upload/';
        $aFiles = AMI_Lib_FS::scan($path, '*.csv', '*', AMI_Lib_FS::SCAN_FILES);

        $aList = array();

        foreach($aFiles as $fileName){
            $aTmpName = explode('/', $fileName);
            $tmpFileName = array_pop($aTmpName);
            $aList[] = array(
                'name'      => $tmpFileName,
                'value'     => $tmpFileName,
            );
        }

        return $aList;
    }
}



