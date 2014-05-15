<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   DataExchange
 * @version   $Id: AMI_DataExchange.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary?
 */

/**
 * AMI_DataExchange class for managing of external import/export drivers. The old (5.x) CMS_DataExchange class will be moved to this class.
 *
 * @package    AMI_DataExchange
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary?
 */
class AMI_DataExchange{
    /**
     * Module id
     *
     * @var string
     */
    private $modId = '';

    /**
     * Array with name of drivers, which module is supporting
     *
     * @var array
     */
    private $aAllowedDrivers = array();

    /**
     * Active driver name, which selected for import/export operation
     *
     * @var string
     */
    private $activeDriverName = '';

    /**
     * Active driver object
     *
     * @var AMI_DataExchange
     */
    private $oActiveDriver = null;

    /**
     * Array drivers list
     *
     * @var array
     */
    private $aDriversList = array();

    /**
     * Array with objects of all supported drivers
     *
     * @var array
     */
    private $aDriversObjects = array();

    /**
     * Constructor.
     *
     * @param string $modId  Module id
     */
    public function __construct($modId){
        $this->modId = $modId;
        $this->aAllowedDrivers = AMI::getProperty($this->modId, 'allowed_exchange_drivers');
        if($this->aAllowedDrivers && !is_array($this->aAllowedDrivers)){
            $this->aAllowedDrivers = array($this->aAllowedDrivers);
        }
    }

    /**
     * Get import drivers list.
     *
     * @return array  Array with drivers, which is supporting import of data
     * @amidev Temporary
     */
    public function getImportDriversList(){
        return $this->getDriversList('import');
    }

    /**
     * Set active driver.
     *
     * @param  string $driverName  Name of driver
     * @return AMI_DataExchange
     */
    public function setActiveDriver($driverName){
        if(in_array($driverName, $this->aAllowedDrivers)){
            $this->activeDriverName = $driverName;
        }
        return $this;
    }

    /**
     * Get drivers list.
     *
     * @param  string $type  Type of drivers (import or export)
     * @return array  Array with drivers
     * @amidev Temporary
     */
    protected function getDriversList($type){
        $aList = array();
        $this->initDriversList();
        for($i = 0; $i < sizeof($this->aDriversList); $i++){
            if(in_array($type, $this->aDriversList[$i]["supported_actions"])){
                $aList[] = $this->aDriversList[$i];
            }
        }
        return $aList;
    }

    /**
     * Created and initialize drivers objects.
     *
     * @return void
     * @amidev Temporary
     */
    protected function initDriversList(){
        if(sizeof($this->aDriversList) == 0){
            foreach($this->aAllowedDrivers as $i => $drvName){
                $this->aDriversObjects[$drvName] = new $this->aAllowedDrivers[$i]();
                $this->aDriversObjects[$drvName]->init('get_info');
                $this->aDriversList[] = $this->aDriversObjects[$drvName]->getInfo();
            }
        }
    }

    /**
     * Created and initialize active driver object.
     *
     * @return bool  True on success, false on fail
     */
    protected function initActiveDriver(){
        $res = FALSE;

        if(in_array($this->activeDriverName, $this->aAllowedDrivers) && class_exists($this->activeDriverName)){
            $this->oActiveDriver = new $this->activeDriverName();
            $res = TRUE;
        }
        /*
        else{
            $this->module->SetLastError(10001, "class not found");
        }
        */
        return $res;
    }

    /**
     * Run import of data.
     *
     * @param  array $aData  Import data
     * @return bool  True if import successfully completed, false otherwise
     */
    public function dispatchImport(array $aData){
        $res = FALSE;

        if($this->initActiveDriver()){
            return $this->oActiveDriver->start('import', $aData);
        }

        return $res;
    }
}

/**
 * AMI_ExchangeDriver - base class for external import/export drivers.
 *
 * The old (5.x) ExchangeDriver class will be moved to this class.
 *
 * @package    AMI_DataExchange
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary?
 */
class AMI_ExchangeDriver{
    /**
     * Driver words
     *
     * @var array
     */
    public $words;

    /**
     * Driver name
     *
     * @var string
     */
    public $name;

    /**
     * Constructor.
     */
    public function __construct(){
        $this->name = 'ExchangeDriver';
    }

    /**
     * Driver initialization.
     *
     * @param string $type  Initialization type (import, export, or get_info)
     * @return AMI_ExchangeDriver
     */
    public function init($type){
        $oTpl = AMI_Registry::get('oGUI');
        $this->words = $oTpl->ParseLangFile("templates/ExchangeDrivers/lang/" . $this->name . ".lng");
        return $this;
    }

    /**
     * Get driver info.
     *
     * @return array  Array with driver info (name, title, and supported actions)
     */
    public function getInfo(){
        $aInfo = array(
            'name'              => '',
            'title'             => '',
            'supported_actions' => array()
        );
        return $aInfo;
    }
}

/**
 * PhotoExchangeDriver - driver for photoalbums importing.
 *
 * @package    AMI_DataExchange
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary?
 */
class PhotoExchangeDriver extends AMI_ExchangeDriver{
    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();
        $this->name = 'PhotoExchangeDriver';
    }

    /**
     * Get driver info.
     *
     * @return array  Array with driver info (name, title, and supported actions)
     */
    public function getInfo(){
        $aInfo = array(
            'name'              => 'PhotoExchangeDriver',
            'title'             => $this->words['title'],
            'supported_actions' => array('import'),
            'pathtypes'         => array('dirs'),
            'filemask'          => array('*.jpg', '*.jpeg', '*.gif', '*.png')
        );
        return $aInfo;
    }
}

/**
 * FilesExchangeDriver - driver for files importing.
 *
 * @package    AMI_DataExchange
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary?
 */
class FilesExchangeDriver extends AMI_ExchangeDriver{
    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();
        $this->name = 'FilesExchangeDriver';
    }

    /**
     * Get driver info.
     *
     * @return array  Array with driver info (name, title, and supported actions)
     */
    public function getInfo(){
        $aInfo = array(
            'name'              => 'FilesExchangeDriver',
            'title'             => $this->words['title'],
            'supported_actions' => array('import'),
            'pathtypes'         => array('dirs'),
            'filemask'          => array('*.jpg', '*.jpeg', '*.gif', '*.png')
        );
        return $aInfo;
    }

    /**
     * Run import of files.
     *
     * @param  string $actionType  Action type
     * @param  array  $aData       Import Data array
     * @return array
     */
    public function start($actionType, array $aData){
        switch($actionType){
            case 'import':
                $aResult = $this->processImport($aData);
                break;
            default:
                $aResult = array('error' => 'unknown_action_type');
        }
        return $aResult;
    }

    /**
     * Processes 'import' action.
     *
     * @param  array $aData  Import Data array
     * @return array
     */
    protected function processImport(array $aData){
        // data initialization
        $filesModId = preg_replace('/_import$/i', '', $aData['modId']);
        $aResult = array('imported' => 0);
        $aFiles = array();
        $idCat = intval($aData['oRequest']->get('cat_id', null));
        $isForceRewrite = intval($aData['oRequest']->get('force_rewrite', null));
        $isPublic = intval($aData['oRequest']->get('public', null));
        $isRemove = intval($aData['oRequest']->get('remove', null));
        $ids = $aData['oRequest']->get('mod_action_id', null);
        if(!empty($ids)){
            $aFiles = explode(',', $ids);
        }

        // get files data
        $numReplaced = 0;
        $numError = 0;
        $numDelError = 0;
        $filesCount = sizeof($aFiles);
        $oController = $aData['oController'];
        $oFilesModel = AMI::getResourceModel($filesModId.'/table');
        $oFilesDataExchangeModel = AMI::getResourceModel($aData['tableModelId']);
        $handleCatCounters = false;
        if(AMI_ModDeclarator::getInstance()->isRegistered('ext_category')){
            $handleCatCounters = true;
            $oExtCat = AMI::getResource('ext_category/module/controller/adm', array($filesModId));
        }
        $importPath = $oFilesDataExchangeModel->getImportPath();
        $maxImportFileSize = $oFilesDataExchangeModel->getMaxImportFileSize();
        $filesPrefix = $oFilesDataExchangeModel->getFilesPrefix();
        $filesExtension = $oFilesDataExchangeModel->getFilesExtension();
        $filesPath = $oFilesDataExchangeModel->getNewFilesPath();
        $aFileTypes = $oFilesDataExchangeModel->getFileTypes();

        // process of files import
        if($filesCount > 0){
            foreach($aFiles as $index => $fileName){
                $isRewriteName = true;
                $fileName = rawurldecode(AMI_Lib_String::unhtmlentities($fileName));

                // check that the file exists
                if(is_file($importPath.$fileName) && AMI_Lib_FS::validatePath($importPath.$fileName)){
                    $fname = $importPath.$fileName;
                    $fsize = filesize($fname);

                    // check that the file size is allowed
                    if($fsize > 0 && $fsize < $maxImportFileSize){
                        $ftype  = isset($aFileTypes['.'.mb_strtolower(get_file_ext($fileName))]) ? intval($aFileTypes['.'.mb_strtolower(get_file_ext($fileName))]['id']) : 0;
                        if(empty($ftype)){
                            $ftype = intval($aFileTypes['.']['id']);
                        }
                        $originalFileName = addslashes($fileName);

                        // check that the file exists already
                        $oQuery = new DB_Query($oFilesModel->getTableName());
                        $oQuery
                            ->addFields(array('id', 'filename'))
                            ->addWhereDef(DB_Query::getSnippet('AND original_fname = %s AND lang = %s AND id_cat = %s')->q($originalFileName)->q(AMI_Registry::get('lang_data'))->q($idCat))
                            ->setLimitParameters(0, 1);
                        $aFileRow = AMI::getSingleton('db')->fetchRow($oQuery);

                        $idFileItem = 0;
                        if(is_array($aFileRow)){
                            // rewrite existing file
                            $existsFileId = $aFileRow["id"];
                            $existsFileName = $aFileRow["filename"];
                            $oFileItem = $oFilesModel->find($existsFileId);
                            if($isForceRewrite){
                                $idFileItem = $existsFileId;
                                if(!empty($existsFileName)){
                                    AMI_Lib_FS::deleteFile($filesPath.$existsFileName);
                                }
                                $isRewriteName = false;
                            }
                        }else{
                            // create and save file model item
                            $oFileItem = $oFilesModel->getItem();
                            if($handleCatCounters){
                                AMI_Event::addHandler('on_before_save_model_item', array($oExtCat, 'handleSaveModelItem'), $oFileItem->getModId());
                            }
                            $oFileItem->header = $originalFileName;
                            $oFileItem->announce = ' ';
                            $oFileItem->body = '';
                            $oFileItem->ftype = $ftype;
                            $oFileItem->public = $isPublic;
                            $oFileItem->cdate = DB_Query::getSnippet('NOW()');
                            $oFileItem->lang = AMI_Registry::get('lang_data');
                            $oFileItem->id_cat = $idCat;
                            $oFileItem->save();
                            $idFileItem = $oFileItem->getId();
                            if($handleCatCounters){
                                AMI_Event::dropHandler('on_before_save_model_item', array($oExtCat, 'handleSaveModelItem'), $oFileItem->getModId());
                            }
                        }

                        // import new file
                        if($idFileItem > 0){
                            $newFileName = $filesPrefix.$idFileItem."_".$fileName.$filesExtension;
                            $newFileName = AMI_Lib_FS::prepareName($newFileName);

                            AMI_Registry::push('disable_error_mail', true);
                            if(AMI_Lib_FS::copyFile($fname, $filesPath.$newFileName)){
                                // copy new file, save the file model item data
                                $oFileItem->filename = addslashes($newFileName);
                                $oFileItem->filesize = $fsize;
                                $oFileItem->original_fname = $originalFileName;
                                $oFileItem->mdate = DB_Query::getSnippet('NOW()');
                                if($isRewriteName){
                                    $oFileItem->header = $originalFileName;
                                }
                                $oFileItem->save();
                                if($isRemove){
                                    if(!AMI_Lib_FS::deleteFile($fname)){
                                        $numDelError++;
                                    }
                                }
                                if($existsFileId > 0 && $idFileItem == $existsFileId){
                                    $numReplaced += 1;
                                }
                                $existsFileId = 0;
                            }else{
                                // handle errors at copying
                                $numError++;
                                $numDelError++;
                                if($handleCatCounters){
                                    AMI_Event::addHandler('on_after_delete_model_item', array($oExtCat, 'handleDeleteModelItem'), $oFileItem->getModId());
                                }
                                $oFileItem->delete();
                                if($handleCatCounters){
                                    AMI_Event::dropHandler('on_after_delete_model_item', array($oExtCat, 'handleDeleteModelItem'), $oFileItem->getModId());
                                }
                            }
                            AMI_Registry::pop('disable_error_mail');
                        }else{
                            $numError++;
                            $numDelError++;
                        }
                    }else{
                        $numError++;
                        $numDelError++;
                    }
                }else{
                    $numError++;
                    $numDelError++;
                }
            }
            $numImported = $filesCount - $numError;

            // handle counters
            $aResult['imported'] = $numImported;
            $aResult['replaced'] = $numReplaced;
            $aResult['not_imported'] = $numError;

            if($isRemove){
                if(!$numDelError){
                    $aResult['deleted'] = $filesCount - $numDelError;
                }else{
                    $aResult['not_deleted'] = $numDelError;
                }
            }
        }else{
            $aResult = array('error' => 'empty_files_list');
        }

        return $aResult;
    }
}
