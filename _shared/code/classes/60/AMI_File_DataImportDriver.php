<?php
/**
 * Base functionality for file import.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   DataImportDriver
 * @version   $Id: AMI_File_DataImportDriver.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Base file data import driver.
 *
 * @package     DataImportDriver
 * @since       x.x.x
 * @amidev      Temporary
 */
abstract class AMI_File_DataImportDriver extends AMI_DataImportDriver{
    /**
     * File name with path
     *
     * @var string
     */
    protected $fileName;

    /**
     * File resource stream
     *
     * @var resource
     */
    protected $stream;

    /**
     * Request settings
     *
     * @var array
     */
    protected $aRequestData;

    /**
     * Connection settings
     *
     * @var array
     */
    protected $aRequestSettings = array();

    /**
     * Flag is intialized
     *
     * @var bool
     */
    protected $bIsInitialized = false;

    /**
     * Set file name.
     *
     * @param string $resourceName  Name of a resource
     * @return $this
     * @throws AMI_DataImportException  Initialize exception.
     */
    public function setResourceName($resourceName){
        $fileName = AMI_Registry::get('path/root') . '/_mod_files/_upload/' . AMI_Lib_FS::prepareName($resourceName);
        if(false === AMI_Lib_FS::validatePath($fileName)){
            throw new AMI_DataImportException(
                'exception_file_is_not_exists',
                AMI_DataImportException::ERROR_INIT
            );
        }

        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Setting up request settings.
     *
     * @param array $aRequestSettings  Array with the request settings
     * @return mixed|void
     */
    public function setRequestSettings(array $aRequestSettings = array()){
        $this->aRequestSettings = $aRequestSettings;

        return $this;
    }

    /**
     * Open the file for reading.
     *
     * @return $this
     * @throws Exception Connection exception.
     */
    public function initConnection(){

        // add event before initializing connection
        $aEvent = array('aRequestSettings' => $this->aRequestSettings, 'aRequestData' => $this->aRequestData);
        AMI_Event::fire('ami_data_httpimport_driver_before_init', $aEvent, $this->modId);
        $this->aRequestSettings = $aEvent['aRequestSettings'];
        $this->aRequestData = $aEvent['aRequestData'];


        // add event before initializing connection
        $aEvent = array('aRequestSettings' => $this->aRequestSettings);
        AMI_Event::fire('ami_data_fileimport_driver_before_init', $aEvent, $this->modId);
        $this->aRequestSettings = $aEvent['aRequestSettings'];

        if($this->isImportResourceAvailable()){
            $this->stream = fopen($this->fileName, 'rb');
            $this->bIsInitialized = true;
        }else{
            throw new AMI_DataImportException(
                'exception_file_access_denied',
                AMI_DataImportException::ERROR_CONN_OPEN
            );
        }

        return $this;
    }

    /**
     * Read data from file if resource available and put it into storage.
     *
     * @return $this
     * @throws Exception Reading exception.
     */
    public function readData(){
        if(true == $this->bIsInitialized){
            trigger_error('Connection to resource is not available', E_USER_ERROR);
        }

        if(isset($this->aRequestSettings['dataPartialSize'])){
            while(false == feof($this->stream)){
                $this->rawData .= fgets($this->stream, $this->aRequestSettings['dataPartialSize']);
            }
        }else{
            $this->rawData = fread($this->stream, filesize($this->stream));
        }

        return $this;
    }

    /**
     * Close file stream.
     *
     * @return $this
     */
    public function closeConnection(){
        if(false == $this->bIsInitialized){
            trigger_error('Unable to close uninitialized resource', E_USER_WARNING);
        }else{
            fclose($this->stream);
        }

        return $this;
    }

    /**
     * Returns true if file exists and resource is available.
     *
     * @return bool
     */
    public function isImportResourceAvailable(){
        return (file_exists($this->fileName) && is_readable($this->fileName));
    }

}
