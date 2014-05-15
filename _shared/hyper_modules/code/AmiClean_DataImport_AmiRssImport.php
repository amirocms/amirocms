<?php
/**
 * Base functionality for all import drivers.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   DataImportDriver
 * @version   $Id: AmiClean_DataImport_AmiRssImport.php 45284 2013-12-13 11:22:16Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * RSS data import driver.
 *
 * @package     DataImportDriver
 * @since       x.x.x
 * @amidev      Temporary
 */
class AmiClean_DataImport_AmiRssImport extends AMI_Http_DataImportDriver{
    /**
     * RSS driver name
     *
     * @var string
     */
    protected $driverName = 'ami_rss';

    /**
     * RSS parser object
     *
     * @var array
     */
    private $oZendFeed;

    /**
     * Default driver settings fields
     *
     * @var array
     */
    protected $aDriverSettings = array();

    /**
     * The constructor.
     */
    public function __construct(){

        $this->aDriverSettings = array(
            // array('name' => 'rss_type', 'type' => 'select', 'data' => $this->getRssTypes()),
            array('name' => 'source_url', 'type' => 'input', 'validate' => array('filled', 'required', 'url', 'custom'), 'position' => 'table_name.before'),
            array('name' => 'strip_tags', 'type' => 'checkbox'),
        );
    }

    /**
     * This method are overriden 'cause ZendReader open the connection automatically.
     *
     * @return $this
     * @throws AMI_DataImportException Initializing exception.
     */
    public function initConnection(){

        AMI_Service::addAutoloadPath(AMI_Registry::get('path/root') . '_shared/code/lib/zendFeed/');

        // require main zend rss class that contains all includes
        $tmpZendPath = AMI_Registry::get('path/root') . '_shared/code/lib/zendFeed/Zend_Feed.php';
        if(file_exists($tmpZendPath) && is_readable($tmpZendPath)){
            $this->bIsInitialized = true;
            return $this;
        }else{
            throw new AMI_DataImportException(
                'exception_unable_to_load_parser',
                AMI_DataImportException::ERROR_INIT
            );
            // trigger_error('Unable to load Zend rss parser - file is not exist', E_USER_ERROR);
        }
    }

    /**
     * Set up settings for the rss import and for parsing process.
     *
     * @param array $aRequestSettings  Settings of the request
     * @return $this
     */
    public function setRequestSettings(array $aRequestSettings = array()){
        $this->aRequestSettings = $aRequestSettings;
        return $this;
    }

    /**
     * This method are overriden 'cause ZendReader close the connection automatically.
     *
     * @return $this|bool
     */
    public function closeConnection(){
        unset($this->oZendFeed);
        return $this;
    }

    /**
     * This method is overriden: see self::doImport().
     *
     * @return $this|void
     */
    public function readData(){
        $this->bIsImported = true;
        return $this;
    }

    /**
     * This is the main function that execute the parsing.
     *
     * @return mixed|void
     * @throws AMI_DataImportException Data importing exception.
     */
    public function doImport(){
        // initialize zend parser
        try{
            $oZendFeed = Zend_Feed::import($this->requestUrl);
        }catch(Exception $oException){
            throw new AMI_DataImportException(
                'exception_unable_to_import_rss',
                AMI_DataImportException::ERROR_IMPORTING,
                array('driverMsg' => $oException->getMessage())
            );
        }

        $root = '';
        foreach($oZendFeed->getDOM()->getElementsByTagName('link') as $oLink){
            $aURL = parse_url($oLink->nodeValue);
            if(isset($aURL['host'])){
                $root =
                    (isset($aURL['scheme']) ? $aURL['scheme'] : 'http') . '://' .
                    $aURL['host'] .
                    (isset($aURL['port']) ? ':' . $aURL['port'] : '') . '/';
            }
            break;
        }

        // read data from the feed
        $aImportedData = array();

        // put all records into array
        foreach($oZendFeed as $oEntry){

            $aData = array();
            foreach($this->aFields as $fieldData){
                $fieldName = trim($fieldData['name']);

                // field is method?
                if(substr($fieldName, -2, 2) == '()'){
                    $methodName = 'field' . ucfirst(substr($fieldName, 0, strlen($fieldName) - 2));
                    if(method_exists($this, $methodName)){
                        // call driver method to parse the field
                        call_user_func_array(array($this, $methodName), array($oEntry));
                    }else{
                        $fieldValue = '';
                    }
                }else{
                    $fieldValue = call_user_func(array($oEntry, $fieldName));
                }

                // if field is pubdate we must convert it to our format
                // $aData[$fieldName] = $fieldName == 'pubDate'

                if($root){
                    AMI_Registry::push('disable_error_mail', true);
                    foreach(
                        array(
                            "/href=(\"|')?([^\"'\s]+)/si",
                            "/src=(\"|')?([^\"'\s]+)/si",
                            "/data-ami-mbpopup=(\"|')?([^\"'\s]+)/si"
                        ) as $pattern
                    ){
                        if(preg_match_all($pattern, $fieldValue, $aMatches)){
                            foreach($aMatches[2] as $url){
                                $aURL = parse_url($url);
                                if(!isset($aURL['host']) || '' == $aURL['host']){
                                    $fieldValue = str_replace($url, $root . $url, $fieldValue);
                                }
                            }

                        }
                    }
                    AMI_Registry::pop('disable_error_mail');
                }

                $aData[] = $fieldName == 'pubDate'
                    ? gmdate('Y-m-d H:i:s', strtotime($fieldValue, time()))
                    : $fieldValue;
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
     * Returns is connection available.
     *
     * @return bool
     */
    public function isImportResourceAvailable(){
        return $this->bIsInitialized;
    }

    /**
     * Returns types of rss feeds.
     *
     * @return array
     */
    public function getRssTypes(){
        return array(
            array('caption' => 'rss_type_10', 'value' => 'rss10'),
            array('caption' => 'rss_type_20', 'value' => 'rss20'),
            array('caption' => 'rss_type_atom10', 'value' => 'atom10'),
        );
    }

    /**
     * Example for additional method.
     *
     * @param mixed $oEntry  Entry of the imported data
     * @return string
     */
    public function fieldDoSomething($oEntry){
        return '';
    }

}
