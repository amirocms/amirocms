<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Core
 * @version   $Id: AMI_PackageManager.php 50450 2014-05-05 07:40:35Z Leontiev Anton $
 * @amidev    Temporary
 */

/**
 * Transaction CMS package manipulating command exception.
 *
 * @package Core
 * @since   x.x.x
 * @amidev  Temporary
 */
class AMI_Tx_PackageManager_Exception extends AMI_Exception{
    const INVALID_PKG_ID      = 1;
    const INVALID_PKG_VERSION = 2;
    const LOCKED              = 3;
    const INVALID_MODES       = 4;
}

/**
 * Transaction CMS package unpacking.
 *
 * @package Core
 * @since   x.x.x
 * @amidev  Temporary
 * @todo    Decide about resource
 */
final class AMI_Tx_UnpackPackage extends AMI_Package_Manipulator{
    /**
     * Transaction type for audit
     *
     * @var string
     */
    protected $transactionType = 'installPackage';

    /**
     * Audit status for successfull transaction
     *
     * @var string
     */
    protected $auditSuccessStatus = 'installed';

    /**
     * Package id
     *
     * @var string
     */
    protected $pkgId;

    /**
     * Package version
     *
     * @var string
     */
    protected $pkgVersion;

    /**
     * Constructor.
     *
     * @param string $pkgId       Package
     * @param string $pkgVersion  Package version
     * @param int    $mode        Flags specifying installation mode, AMI_iTx_Cmd::MODE_*
     */
    public function __construct($pkgId, $pkgVersion, $mode = AMI_iTx_Cmd::MODE_COMMON){
        $this->pkgId      = (string)$pkgId;
        $this->pkgVersion = (string)$pkgVersion;
        $this->mode       = (int)$mode;

        $this->txName =
            'Unpacking package: ' .
            "pkgId = '" . $this->pkgId . "', " .
            "pkgVersion = '" . $this->pkgVersion . "', " .
            "mode = " . $this->getModeAsString($this->mode);
        AMI_Tx::log($this->txName);

        $this->init();
        $this->validate();

        $this->addAction('unpackPackage');
        $this->addAction('validateMetaModes');
    }

    /**
     * Validates initial data.
     *
     * @return void
     * @throws AMI_Tx_PackageManager_Exception  In case of problems.
     */
    protected function validate(){
        if(!preg_match('/^[a-z](?:[a-z\d]|_[a-z])+(\.[a-z](?:[a-z\d]|_[a-z])+)?$/', $this->pkgId)){
            throw new AMI_Tx_PackageManager_Exception(
                "Invalid package id '" . $this->pkgId . "'",
                AMI_Tx_PackageManager_Exception::INVALID_PKG_ID
            );
        }
        if(!preg_match('/^[0-9]+\.[0-9]+$/', $this->pkgVersion)){
            throw new AMI_Tx_PackageManager_Exception(
                "Invalid package version '" . $this->pkgVersion . "'",
                AMI_Tx_PackageManager_Exception::INVALID_PKG_VERSION
            );
        }
    }

    /**
     * Returns data for audit.
     *
     * @return array
     */
    protected function getAuditData(){
        return
            array(
                'pkgId'      => $this->pkgId,
                'pkgVersion' => $this->pkgVersion,
                'mode'       => $this->mode
            );
    }

    /**
     * Initialization.
     *
     * @return void
     */
    protected function init(){
    }

    /**
     * Transaction action.
     *
     * Creates local module code files.
     *
     * @return void
     */
    protected function unpackPackage(){
        $oStorage = new AMI_Storage_FS;
        $oStorage->setMode(AMI_iStorage::MODE_MAKE_FOLDER_ON_COPY);
        $oPackageManager = AMI_PackageManager::getInstance();
        $srcPath = $oPackageManager->getTemporaryUnpackPath();
        $destPath = AMI_Registry::get('path/root') . '_local/modules/';
        foreach(array('code', 'distrib/configs') as $path){
            $aFiles = AMI_Lib_Fs::scan($srcPath . '/' . $path);
            foreach($aFiles as $source){
                $target = $destPath . str_replace($srcPath, '', $source);
                $this->aTx['storage']->addCommand(
                    'storage/copy',
                    new AMI_Tx_Cmd_Args(
                        array(
                            'mode'     => $this->mode | AMI_iTx_Cmd::MODE_DELETE_BACKUP_ON_COMMIT,
                            'source'   => $source,
                            'target'   => $target,
                            'oStorage' => $oStorage
                        )
                    )
                );
            }
        }
        $this->aTx['storage']->addCommand(
            'storage/copy',
            new AMI_Tx_Cmd_Args(
                array(
                    'mode'     => $this->mode | AMI_iTx_Cmd::MODE_DELETE_BACKUP_ON_COMMIT,
                    'source'   => $srcPath . '/manifest.xml',
                    'target'   => $oPackageManager->getPackageManifestFileName($this->pkgId),
                    'oStorage' => $oStorage
                )
            )
        );
    }

    /**
     * Transaction action.
     *
     * Validates install/uninstall modes from meta.
     *
     * @return void
     */
    protected function validateMetaModes(){
        $oPkgManager = AMI_PackageManager::getInstance();
        $oPkgManager->validateMetaModes($this->pkgId);
    }
}

/**
 * Module Manager module admin action controller.
 *
 * @package    Core
 * @subpackage Controller
 * @amidev     Temporary
 */
final class AMI_PackageManager{
    const LOCK_TTL = 120; // 2 minutes

    const ERR_DL_NO_FREE_SPACE = 1;
    const ERR_DL_NO_CONNECT    = 2;
    const ERR_DL_INCOMPLETE    = 3;
    const ERR_DL_UNKNOWN       = 4;
    const ERR_DL_NO_LOCAL_FILE = 5;
    const ERR_DL_WRONG_CHUNK   = 6;

    const ERR_VALIDATE_CLEANUP = 9;

    const ERR_PARSE_INTERRUPTED       = 10;
    const ERR_PARSE_MISSING_ATTRIBUTE = 11;
    const ERR_PARSE_INVALID_ATTRIBUTE = 12;
    const ERR_PARSE_INVALID_TAG       = 13;
    const ERR_PARSE_MISSING_TAG       = 14;
    const ERR_PARSE_MISSING_FILE      = 15;

    const DL_CHUNK_SIZE = 200000;

    const TYPE_COMMON          = 0x01;
    const TYPE_PSEUDO          = 0x02;
    const TYPE_BOTH            = 0x03;
    const STATUS_INSTALLED     = 0x01;
    const STATUS_NOT_INSTALLED = 0x02;
    const STATUS_BOTH          = 0x03;

    /**
     * Lock file path
     *
     * @var string
     */
    protected static $lockPath;

    /**
     * Instance
     *
     * @var AMI_PackageManager
     */
    protected static $oInstance;

    /**
     * Lock object
     *
     * @var AMI_lock
     */
    protected $oLock;

    /**
     * Service URL
     *
     * @var string
     */
    // protected $serviceURL = '';

    /**
     * HTTP Request object
     *
     * @var AMI_HTTPRequest
     */
    protected $oHTTPRequest;

    /**
     * Last validated path
     *
     * @var string
     */
    protected $validatedPath = '';

    /**
     * Manifest cache
     *
     * @var array
     */
    protected $aManifests = array();

    /**
     * Last package archive object
     *
     * @var PHPTar
     */
    protected $oArchive;

    /**
     * Last parsed package id called by AMI_PackageManager::getManifest()
     *
     * @var string
     */
    protected $lastPkgId = '';

    /**
     * Package info.
     *
     * @var array
     */
    protected $aPkgInfo;

    /**
     * XML rules to parse manifest
     *
     * @var array
     */
    protected $aXMLRules;

    /**
     * Flag specifying to interrupt parsing
     *
     * @var bool
     */
    protected $aParseError;

    /**
     * XML tag stack
     *
     * @var array
     */
    protected $aTagStack;

    /**
     * Temporary variables
     *
     * @var array
     */
    protected $aTemp;

    /**
     * Packages type
     *
     * @var int
     * @see self::getDownloadedPackages()
     * @see self::cbFilterPackages()
     */
    protected $pkgType;

    /**
     * Packages status
     *
     * @var int
     * @see self::getDownloadedPackages()
     * @see self::cbFilterPackages()
     */
    protected $pkgStatus;

    /**
     * Returns AMI_PackageManager instance.
     *
     * @param  bool $skipLock  Skip locking package manager
     * @return AMI_PackageManager
     * @throws AMI_Tx_PackageManager_Exception  If locked.
     */
    public static function getInstance($skipLock = FALSE){
        if(is_null(self::$oInstance)){
            self::$oInstance = new AMI_PackageManager($skipLock);
        }
        return self::$oInstance;
    }

    /**
     * Returns lock file path.
     *
     * @return string
     */
    public static function getLockPath(){
        return self::$lockPath;
    }

    /**
     * Validates install/uninstall modes for package.
     *
     * @param  string $pkgId  Package Id
     * @return void
     * @throws AMI_Tx_PackageManager_Exception In case of fail.
     * @amidev Temporary?
     */
    public function validateMetaModes($pkgId){
        $aPkgInfo = $this->getManifest($pkgId);
        if(is_array($aPkgInfo)){
            $aModes = array();
            foreach($aPkgInfo['install'] as $aInfo){
                if(!AMI_Package::validateMetaModes($aInfo['hypermodule'], $aInfo['configuration'], $aModes)){
                    throw new AMI_Tx_PackageManager_Exception(
                        "Invalid modes for package '" . $pkgId . " containing '" .
                        $aInfo['hypermodule'] . '/' . $aInfo['configuration'] .
                        "' configuration meta:\n" . var_export($aModes, TRUE),
                        AMI_Tx_PackageManager_Exception::INVALID_MODES,
                        null,
                        array(
                            'hypermodule'   => $aInfo['hypermodule'],
                            'configuration' => $aInfo['configuration']
                        )
                    );
                }
            }
        }
    }

    /**
     * Download package.
     *
     * @param  string $pkgId  Package id
     * @return array
     */
    /*
    public function download($pkgId){
        $file = (string)$pkgId . '.tgz';
        $packageContent = self::makeRequest($this->serviceURL . $file);
        list($errno, $error) = $this->oHTTPRequest->getError();
        if(!$errno){
            $path = $GLOBALS['ROOT_PATH'] . '_mod_files/_upload/tmp/' . $file;
            $size = AMI_Lib_FS::saveFile($path, $packageContent);
            if($size !== mb_strlen($packageContent, 'ASCII')){
                $errno = self::ERR_DL_NO_FREE_SPACE;
                $aErrorData = array('path' => $path);
                $error = 'error_download_no_free_space';
            }
        }else{
            if(in_array($errno, array(5, 6, 7))){
                // CURLE_COULDNT_RESOLVE_PROXY, CURLE_COULDNT_RESOLVE_PROXY, CURLE_COULDNT_CONNECT
                $errno = self::ERR_DL_NO_CONNECT;
                $aErrorData = array('error' => $error);
                $error = 'error_download_connection';
            }elseif(in_array($errno, array(22, 26, 28, 36, 56))){
                // CURLE_HTTP_RETURNED_ERROR, CURLE_READ_ERROR, CURLE_OPERATION_TIMEDOUT,
                // CURLE_BAD_DOWNLOAD_RESUME, CURLE_RECV_ERROR
                $errno = self::ERR_DL_INCOMPLETE;
                $aErrorData = array('error' => $error);
                $error = 'error_download_incomplete';
            }else{
                $error = '[ ' . $errno . ' ] ' . $error;
                $errno = self::ERR_DL_UNKNOWN;
                $aErrorData = array('error' => $error);
                $error = 'error_download_unknown';
            }
        }
        // d::vd($this->oHTTPRequest->getInfo());###
        $aResult =
            $errno
            ?
                array(
                    'success'      => FALSE,
                    'errorCode'    => $errno,
                    'errorMessage' => $error,
                    'aErrorData'   => $aErrorData
                )
            :
                array(
                    'success'   => TRUE,
                    'localPath' => $path
                );
        return $aResult;
    }
    */

    /**
     * Returns remote file information.
     *
     * @param  string $pkgId  Package id
     * @return array
     */
    public function getRemoteFileInfo($pkgId, $hash = ''){
        $url = $this->getRemoteFileURL($pkgId, $hash);

        // Get headers only to retreive length and filename
        $aSettings = array(
            'returnBody'     => FALSE,
            'returnHeaders'  => TRUE,
            'followLocation' => TRUE,
            'keepSession'    => TRUE, // Needed to call getInfo
            'useCookies'     => TRUE,
            'cookieFile'     => tmpfile()
        );
        $oHTTPRequest = new AMI_HTTPRequest($aSettings);
        $headers = $oHTTPRequest->send($url, array(), AMI_HTTPRequest::METHOD_GET);
        $aInfo = $oHTTPRequest->getInfo();
        $filesize = 0;
        if(isset($aInfo['download_content_length'])){
            $filesize = (int)$aInfo['download_content_length'];
            if($filesize <= 0){
                $filesize = 0;
            }
        }

        return array(
            'url'       => $url,
            'error'     => !$filesize ? 1 : 0,
            'curlError' => $oHTTPRequest->getError(),
            'fileSize'  => $filesize,
            'sessionId' => md5(uniqid() . time()),
            'chunks'    => ceil($filesize / self::DL_CHUNK_SIZE)
        );
    }

    /**
     * Download specified chunk of file and append it to local file.
     *
     * @param  string $pkgId      Package id
     * @param  string $sessionId  Download session id
     * @param  int $chunk         Chunk number to download (0 - first)
     * @param  int $chunks        Total count of chunks
     * @param  int $filesize      Final file size
     * @return array
     * @throws AMI_Tx_PackageManager_Exception  If locked.
     */
    public function downloadChunk($pkgId, $sessionId, $chunk, $chunks, $filesize, $hash = ''){
        /*
        if(!$this->oLock->check()){
            $this->genLockError();
        }
        */
        $localFile = $this->getTemporaryFileName($pkgId, $sessionId);

        if(!$chunk && file_exists($localFile)){
            unlink($localFile);
        }

        $error = '';
        $errno = 0;
        $aErrorData = array();

        if($chunk && !file_exists($localFile)){
            $errno = self::ERR_DL_NO_LOCAL_FILE;
            $error = 'error_download_no_local_file';
            $aErrorData = array('file' => $localFile);
        }else{
            // Define range start and end bytes
            $startByte = $chunk * self::DL_CHUNK_SIZE;
            $endByte = ($chunk + 1) * self::DL_CHUNK_SIZE - 1;
            if($endByte > ($filesize - 1)){
                $endByte = $filesize - 1;
            }
            // Make sure we download right chunk
            if($chunk && (filesize($localFile) != $startByte)){
                $errno = self::ERR_DL_WRONG_CHUNK;
                $error = 'error_download_wrong_chunk';
                $aErrorData = array(
                    'path'          => $localFile,
                    'chunk'         => $chunk,
                    'realFileSize'  => filesize($localFile),
                    'needFileSize'  => $startByte
                );
            }else{
                // Make curl request and get data chunk
                $curlHandler = curl_init();
                curl_setopt($curlHandler, CURLOPT_URL, $this->getRemoteFileURL($pkgId, $hash));
                curl_setopt($curlHandler, CURLOPT_RANGE, $startByte . '-' . $endByte);
                curl_setopt($curlHandler, CURLOPT_BINARYTRANSFER, 1);
                curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, 1);
                $cookieFile = tmpfile();
                curl_setopt($curlHandler, CURLOPT_COOKIEJAR, $cookieFile);
                curl_setopt($curlHandler, CURLOPT_COOKIEFILE, $cookieFile);
                $bCanFollow = @curl_setopt($curlHandler, CURLOPT_FOLLOWLOCATION, 1);
                if($bCanFollow){
                    $data = curl_exec($curlHandler);
                }else{
                    // Use hack for hosting with open_basedir = true;
                    require_once($GLOBALS["HOST_PATH"] . "_shared/code/lib/func_file_system.php");
                    $data = curl_redir_exec($curlHandler);
                }
                curl_close($curlHandler);

                // todo: check for CURL errors and size of received data
                // make common protected function to use in download and downloadChunk methods

                $size = AMI_Lib_FS::saveFile($localFile, $data, 0666, true);
                clearstatcache();
                if($size != ($endByte - $startByte + 1)){
                    $errno = self::ERR_DL_INCOMPLETE;
                    $error = 'error_download_incomplete';
                    $aErrorData = array(
                        'path'          => $localFile,
                        'savedSize'     => $size,
                        'neededSize'    => ($endByte - $startByte + 1)
                    );
                }elseif(filesize($localFile) != ($endByte + 1)){
                    $errno = self::ERR_DL_NO_FREE_SPACE;
                    $error = 'error_download_no_free_space';
                    $aErrorData = array('path' => $localFile);
                }
            }
        }
        return
            $errno
            ?
                array(
                    'success'      => FALSE,
                    'errorCode'    => $errno,
                    'errorMessage' => $error,
                    'aErrorData'   => $aErrorData
                )
            :
                array(
                    'success'       => TRUE,
                    'errorCode'     => 0,
                    'errorMessage'  => '',
                    'downloaded'    => $endByte + 1
                );
    }

    /**
     * Validates downloaded package.
     *
     * @param  string $path  Path to downloaded package
     * @return bool
     * @throws AMI_Tx_PackageManager_Exception  If locked.
     */
    public function validate($path){
        /*
        if(!$this->oLock->check()){
            $this->genLockError();
        }
        */
        $tempPath = $this->getTemporaryUnpackPath();
        do{
            $res = AMI_Lib_FS::deleteRecursive($tempPath, TRUE);
            if(!$res){
                trigger_error("Cannot cleanup temporary package storage '" . $tempPath . "'", E_USER_WARNING);
                break;
            }
            $res = mkdir($tempPath, 0777);
            @chmod($tempPath, 0777);
            if(!$res){
                trigger_error("Cannot create temporary package storage '" . $tempPath . "'", E_USER_WARNING);
                break;
            }
            $oTar = new PHPTar($path, TRUE);
            $oTar->options(
                array(
                    // 'basedir'          => $GLOBALS['ROOT_PATH'] . '_local/modules/',
                    'force_chmod_dir'  => 0777,
                    'force_chmod_file' => 0777
                )
            );
            $res = $oTar->extract($tempPath);
            if(!$res){
                trigger_error("Cannot unpack '" . $path . " to temporary package storage '" . $tempPath . "': " . $oTar->errorsAsString(), E_USER_WARNING);
                break;
            }
            $manifestPath = $tempPath . '/manifest.xml';
            $res = file_exists($manifestPath);
            if(!$res){
                trigger_error("Manifest file at '" . $manifestPath . "' not found", E_USER_WARNING);
            }
            $res = $res && is_readable($manifestPath) && (filesize($manifestPath) > 0);
            if(!$res){
                trigger_error("Manifest file at '" . $manifestPath . "' cannot be read", E_USER_WARNING);
            }
            $res = $res && (filesize($manifestPath) > 0);
            if(!$res){
                trigger_error("Manifest file at '" . $manifestPath . "' has zero length", E_USER_WARNING);
            }
        }while(FALSE);

        $this->validatedPath = $res ? $path : '';

        return $res;
    }

    /**
     * Retreive data from amiro.ru market service.
     *
     * @param  string $pkgId  Package Id
     * @return boolean
     */
    public function correctManifest($pkgId, $manifestPath = FALSE, $skipWarning = FALSE){
        if($manifestPath === FALSE){
            $manifestPath = $this->getPackageManifestFileName($pkgId);
        }
        if(!file_exists($manifestPath) || !is_readable($manifestPath)){
            if(!$skipWarning){
                trigger_error('Manifest file is missing or not readable: ' . $manifestPath, E_USER_WARNING);
            }
            return false;
        }
        $manifest = file_get_contents($manifestPath);
        if(strpos($manifest, "<package id") === FALSE){
            trigger_error('Invalid manifest file format detected: ' . $manifestPath, E_USER_WARNING);
            return false;
        }
        $aSettings = array(
            'returnBody'     => TRUE,
            'returnHeaders'  => FALSE,
            'followLocation' => TRUE
        );
        $oHTTPRequest = new AMI_HTTPRequest($aSettings);
        $url = 'http://www.amiro.ru/market_get_distr_info.php';
        $data = $oHTTPRequest->send($url, array('distrib' => $pkgId), AMI_HTTPRequest::METHOD_GET);
        if($data){
            $aData = AMI_Lib_JSON::decode($data);
            if(is_array($aData) && isset($aData['data'])){
                $aData = $aData['data'];
                if(isset($aData['header']) && $aData['header']){
                    $manifest = preg_replace('/<title>(.*)<\/title>/i', '<title>' . $aData['header'] . '</title>', $manifest);
                }
                if(isset($aData['icon']) && isset($aData['announce']) && $aData['icon']){
                    $aData['announce'] = '<img src="http://www.amiro.ru/' . $aData['icon'] . '" align="left" />' . $aData['announce'];
                }
                if(isset($aData['announce']) && $aData['announce']){
                    $manifest = preg_replace('/<description>(.*)<\/description>/i', '<description><![CDATA[' . $aData['announce'] . ']]></description>', $manifest);
                }
                file_put_contents($manifestPath, $manifest);
            }
        }
        return true;
    }

    /**
     * Unpacks downloaded package.
     *
     * @param  string $path       Path to downloaded package
     * @param  bool   $overwrite  Overwrite existent files
     * @return bool
     * @throws AMI_Tx_PackageManager_Exception  If locked.
     */
    public function unpack($path, $overwrite = FALSE){
        $this->lock();

        /*
        if(!$this->oLock->check()){
            $this->genLockError();
        }
         */
        if($this->validatedPath !== $path && !$this->validate($path)){
            trigger_error("Cannot validate path '" . $path . "'", E_USER_WARNING);
            @unlink($path);
            return FALSE;
        }
        @unlink($path);
        $tempPath = $this->getTemporaryUnpackPath();
        $manifestPath = $tempPath . '/manifest.xml';
        $manifest = file_get_contents($manifestPath);
        if($manifest === FALSE){
            trigger_error("Cannot open manifest '" . $manifestPath . "'", E_USER_WARNING);
            return FALSE;
        }
        if(!$this->parseManifest($manifest, FALSE) && $this->aParseError['errorCode'] != self::ERR_PARSE_INTERRUPTED){
            return FALSE;
        }
        if(!$this->aPkgInfo){
            trigger_error("No package info found in manifest '" . $manifestPath . "'", E_USER_WARNING);
            return FALSE;
        }

        $this->correctManifest($this->aPkgInfo['id'], FALSE, TRUE);

        // AMI_Tx_UnpackPackage::setDebug(TRUE);
        $oTx = new AMI_Tx_UnpackPackage(
            $this->aPkgInfo['id'],
            $this->aPkgInfo['version'],
            $overwrite ? AMI_iTx_Cmd::MODE_OVERWRITE : AMI_iTx_Cmd::MODE_COMMON
        );
        try{
            $oTx->run();
            // Success
            $tempPath = $this->getTemporaryUnpackPath();
            AMI_Lib_FS::deleteRecursive($tempPath, TRUE);
            $res = TRUE;
        }catch(AMI_Exception $oException){
            d::w('<span style="color: red;">[ ' . $oException->getCode() . ' ] ' . $oException->getMessage() . '</span>');
            d::trace($oException->getTrace());
            $res = FALSE;
        }

        return $res;
    }

    /**
     * Returns manifest.
     *
     * @param  string $pkgId  Package id
     * @param  bool   $parse  Flag specifying to parse XML into array
     * @return string|array|false
     */
    public function getManifest($pkgId, $parse = TRUE){
        $pkgId = (string)$pkgId;
        if($parse && (($this->lastPkgId === $pkgId) || !mb_strlen($pkgId))){
            return $this->aPkgInfo;
        }
        $this->lastPkgId = '';
        $path = $this->getPackageManifestFileName($pkgId);
        $res = FALSE;
        if(file_exists($path) && is_readable($path)){
            $res = file_get_contents($path);
            if($res !== '' && $parse){
                if($this->parseManifest($res)){
                    $this->lastPkgId = $pkgId;
                    $res = $this->aPkgInfo;
                }else{
                    $res = FALSE;
                }
            }
        }else{
            $this->aParseError = array(
                'errorCode'    => self::ERR_PARSE_MISSING_FILE,
                'errorMessage' => 'error_parse_missing_file',
                'aErrorData'   => array('path' => $path)
            );
        }
        return $res;
    }

    /**
     * Returns error structure.
     *
     * @return array
     */
    public function getError(){
        return $this->aParseError;
    }

    /**
     * Returns temporary local filename of downloading file.
     *
     * @param string $pkgId      Package id
     * @param string $sessionId  Download session id
     * @return string
     * @amidev Temporary
     */
    public function getTemporaryFileName($pkgId, $sessionId){
        return AMI_Registry::get('path/root') . '_mod_files/_upload/tmp/' . md5($pkgId . $sessionId) . '.tmp';
    }

    /**
     * Returns local filename of package archive.
     *
     * @param  string $pkgId  Package id
     * @return string
     * @amidev Temporary
     */
    public function getPackageTgzFileName($pkgId){
        return AMI_Registry::get('path/root') . '_local/modules/distrib/packages/' . $pkgId . '.tgz';
    }

    /**
     * Returns local filename of package manifest.
     *
     * @param  string $pkgId  Package id
     * @return string
     * @amidev Temporary
     */
    public function getPackageManifestFileName($pkgId){
        return AMI_Registry::get('path/root') . '_local/modules/distrib/packages/' . $pkgId . '.xml';
    }

    /**
     * Returns temporary folder for unpacking package archive.
     *
     * @return string
     * @amidev Temporary
     */
    public function getTemporaryUnpackPath(){
        return AMI_Registry::get('path/root') . '_mod_files/_upload/tmp/_package';
    }

    /**
     * Get list of downloaded packages information.
     *
     * @param  int $type    Packages type:
     *                      AMI_PackageManager::TYPE_COMMON |
     *                      AMI_PackageManager::TYPE_PSEUDO |
     *                      AMI_PackageManager::TYPE_BOTH
     * @param  int $status  Installed status:
     *                      AMI_PackageManager::STATUS_INSTALLED |
     *                      AMI_PackageManager::STATUS_NOT_INSTALLED |
     *                      AMI_PackageManager::STATUS_BOTH
     * @return array
     */
    public function getDownloadedPackages($type = self::TYPE_BOTH, $status = self::STATUS_BOTH){
        $this->pkgType = (int)$type;
        $this->pkgStatus = (int)$status;
        $aFiles = AMI_Lib_FS::scan(AMI_Registry::get('path/root') . '_local/modules/distrib/packages', '*.xml', AMI_Lib_FS::SCAN_FILES);
        $aPackages = array();
        foreach($aFiles as $manifestFile){
            $pkgId = basename($manifestFile, '.xml');
            $aRes = $this->getManifest($pkgId);
            $aPackages[$pkgId] = array(
                'version' => $aRes['version'],
                'instCnt' => $this->countPackageInstallations($pkgId),
                'admin'   => $this->packageHasAdmin($pkgId) ? '1' : '0',
                'pseudo'  => 0,
                'instId'  => $this->packageFirstInstance($pkgId)
            );
        }
        $fakeDeclares = AMI_Registry::get('path/hyper_local') . 'declaration/pseudo.php';
        $oStorage = new AMI_Storage_FS;
        $aRecords =
            $oStorage->exists($fakeDeclares)
            ? require($fakeDeclares)
            : array();
        foreach($aRecords as $aRecord){
            $aPkgInfo = $aRecord['pkgInfo'];
            $pkgId = $aPkgInfo['id'];
            $version = $aPkgInfo['version'];
            if(isset($aPackages[$pkgId])){
                $aPackages[$pkgId]['instCnt']++;
                $aPackages[$pkgId]['pseudo'] = 1;
            }else{
                $aPackages[$pkgId] = array(
                    'version'   => $aPkgInfo['version'],
                    'instCnt'   => 1,
                    'admin'     => 0,
                    'pseudo'    => 1,
                    'instId'    => ''
                );
            }
        }
        $aPackages = array_filter($aPackages, array($this, 'cbFilterPackages'));

        return $aPackages;
    }

    /**
     * Package filterring callback.
     *
     * @param  array $aPkgInfo
     * @return bool
     * @see    self::getDownloadedPackages()
     */
    protected function cbFilterPackages($aPkgInfo){
        $validType = FALSE;
        if($this->pkgType & self::TYPE_COMMON){
            $validType = !$aPkgInfo['pseudo'];
        }
        if($this->pkgType & self::TYPE_PSEUDO){
            $validType = $validType || $aPkgInfo['pseudo'];
        }

        $validStatus = FALSE;
        if($this->pkgStatus & self::STATUS_INSTALLED){
            $validStatus = $aPkgInfo['instCnt'] > 0;
        }
        if($this->pkgStatus & self::STATUS_NOT_INSTALLED){
            $validStatus = $validStatus || (0 == $aPkgInfo['instCnt']);
        }

        return $validType && $validStatus;
    }

    /**
     * Constructor.
     *
     * Creating object is for internal use only.
     *
     * @param  bool $skipLock  Skip locking package manager
     * @throws AMI_Tx_PackageManager_Exception  If locked.
     */
    protected function __construct($skipLock = FALSE){
    }

    /**
     * Makes HTTP requqest and returns response.
     *
     * @param  string $url       URL
     * @param  string $aRequest  Request data
     * @param  int    $method    Request method: AMI_HTTPRequest::METHOD_GET / AMI_HTTPRequest::METHOD_POST
     * @return string
     */
    /*
    protected function makeRequest($url, $aRequest = array(), $method = AMI_HTTPRequest::METHOD_GET){
        if(is_null($this->oHTTPRequest)){
            $this->oHTTPRequest = new AMI_HTTPRequest(array('keepSession' => TRUE));
        }
        $content = $this->oHTTPRequest->send($url, $aRequest, $method);
        return $content;
    }
    */

    /**
     * Parses manifest.
     *
     * @param  string $content   XML content
     * @param  bool   $parseAll  Flag specifying to parse only package id and version or all manifest
     * @return bool
     */
    protected function parseManifest($content, $parseAll = TRUE){
        $this->aXMLRules = array(
            'package' => array(
                'obligatory' => TRUE,
                'attributes' => array(
                    'id' => array(
                        'obligatory' => TRUE,
                        'callback'   => array($this, 'onPackageAttributeId')
                    ),
                    'version' => array(
                        'obligatory' => TRUE,
                        'callback'   => array($this, 'onPackageAttributeVersion')
                    ),
                    'manifestVersion' => array(
                        'obligatory' => TRUE,
                        'callback'   => array($this, 'onPackageAttributeManifestVersion')
                    )
                )
            )
        );
        if(!$parseAll){
            $this->aXMLRules['package']['callbackOnStart'] = array($this, 'onGetPackageAttributes');
        }

        $oParser = xml_parser_create('UTF-8');
        xml_parser_set_option($oParser, XML_OPTION_CASE_FOLDING, 0);
        xml_set_object($oParser, $this);
        xml_set_element_handler($oParser, 'onTagStart', 'onTagEnd');
        xml_set_character_data_handler($oParser, 'onTagContent');

        $this->aPkgInfo = array();
        $this->aParseError = array();
        $this->aTagStack = array();

        $res = xml_parse($oParser, $content);
        if(!$res){
            $code = xml_get_error_code($oParser);
            $this->aParseError['xmlError'] = array(
                'code'    => $code,
                'message' => xml_error_string($code),
                'line'    => xml_get_current_line_number($oParser),
                'char'    => xml_get_current_column_number($oParser)
            );
        }
        xml_parser_free($oParser);
        if($this->aParseError){
            $res = FALSE;
        }else{
            // check visited obligatory tags and attributes
            $res = $this->checkObligatory($this->aXMLRules);
            if($res){
                $this->aPkgInfo['manifest'] = $content;
            }
        }
        return $res;
    }

    /**
     * Checcks obligatory tags/attributes after manifest is parsed.
     *
     * @param  array  &$aBranch  Part of XML rules
     * @param  string $tag       Tag name
     * @return bool
     */
    protected function checkObligatory(array &$aBranch, $tag = ''){
        $aKeys = array_keys($aBranch);
        foreach($aKeys as $key){
            if(is_array($aBranch[$key])){
                $parent = preg_replace(
                    array('/^package\./', '/\.[^.]+$/'),
                    '',
                    $key
                );
                if(
                    isset($this->aPkgInfo[$parent]) &&
                    !empty($aBranch[$key]['obligatory']) &&
                    empty($aBranch[$key]['visited'])
                ){
                    $this->aParseError =
                        $tag === ''
                        ? array(
                            'errorCode'    => self::ERR_PARSE_MISSING_TAG,
                            'errorMessage' => 'error_parse_missing_tag',
                            'aErrorData'   => array('tag' => $key)
                        )
                        : array(
                            'errorCode'    => self::ERR_PARSE_MISSING_ATTRIBUTE,
                            'errorMessage' => 'error_parse_missing_tag_attribute',
                            'aErrorData'   => array('tag' => $tag, 'attribute' => $key)
                        );
                    return FALSE;
                }
                if($tag === ''){
                    if(
                        isset($this->aPkgInfo[$parent]) &&
                        !$this->checkObligatory($aBranch[$key], $key)
                    ){
                        return FALSE;
                    }
                }
            }
        }
        return TRUE;
    }

    /**
     * XML parser on tag start callback.
     *
     * @param  resource $oParser  XML parser resource
     * @param  string   $tagName  Tag name
     * @param  array    $aAttrs   Array of attributes
     * @return void
     */
    protected function onTagStart($oParser, $tagName, array $aAttrs = array()){
        if($this->aParseError){
            return;
        }
        $this->aTagStack[] = $tagName;
        $tagName = mb_strtolower($tagName);
        $tag = implode('.', $this->aTagStack);
        if(!isset($this->aXMLRules[$tag])){
            // Invalid tag
            $this->aParseError = array(
                'errorCode'    => self::ERR_PARSE_INVALID_TAG,
                'errorMessage' => 'error_parse_invalid_tag',
                'aErrorData'   => array('tag' => $tag)
            );
            return;
        }
        if(!empty($this->aXMLRules[$tag]['obligatory'])){
            $this->aXMLRules[$tag]['visited'] = TRUE;
        }
        if(isset($this->aXMLRules[$tag]['attributes'])){
            foreach($this->aXMLRules[$tag]['attributes'] as $attrName => $aAttrStruct){
                if(!empty($aAttrStruct['obligatory'])){
                    if(!isset($aAttrs[$attrName])){
                        $this->aParseError = array(
                            'errorCode'    => self::ERR_PARSE_MISSING_ATTRIBUTE,
                            'errorMessage' => 'error_parse_invalid_tag_attribute',
                            'aErrorData'   => array('tag' => $tag, 'attribute' => $attrName)
                        );
                        return;
                    }
                    $this->aXMLRules[$tag]['attributes'][$attrName]['visited'] = TRUE;
                }
                if(isset($aAttrs[$attrName]) && isset($aAttrStruct['callback'])){
                    call_user_func($aAttrStruct['callback'], $tag, $attrName, $aAttrs[$attrName]);
                    if($this->aParseError){
                        return;
                    }
                }
            }
        }
        if(isset($this->aXMLRules[$tag]['callbackOnStart'])){
            call_user_func($this->aXMLRules[$tag]['callbackOnStart'], $tag, $aAttrs);
        }
    }

    /**
     * XML parser on tag end callback.
     *
     * @param  resource $oParser  XML parser resource
     * @param  string   $tagName  Tag name
     * @return void
     */
    protected function onTagEnd($oParser, $tagName){
        if($this->aParseError){
            return;
        }
        array_pop($this->aTagStack);
    }

    /**
     * XML parser on tag content callback.
     *
     * @param  resource $oParser  XML parser resource
     * @param  string   $content  Content
     * @return void
     */
    protected function onTagContent($oParser, $content){
        $tag = implode('.', $this->aTagStack);
        if(isset($this->aXMLRules[$tag]['callbackOnContent'])){
            call_user_func($this->aXMLRules[$tag]['callbackOnContent'], $tag, $content);
        }
    }

    /**
     * Callback to parse package id value.
     *
     * @param  string $tagName    Tag name
     * @param  string $attrName   Attribute name
     * @param  string $attrValue  Attribute value
     * @return void
     * @see    AMI_PackageManager::onTagStart()
     */
    protected function onPackageAttributeId($tagName, $attrName, $attrValue){
        $this->aPkgInfo['id'] = $attrValue;
    }

    /**
     * Callback to parse package version value.
     *
     * @param  string $tagName    Tag name
     * @param  string $attrName   Attribute name
     * @param  string $attrValue  Attribute value
     * @return void
     * @see    AMI_PackageManager::onTagStart()
     */
    protected function onPackageAttributeVersion($tagName, $attrName, $attrValue){
        $this->aPkgInfo['version'] = $attrValue;
    }

    /**
     * Callback to parse package version value.
     *
     * @param  string $tagName    Tag name
     * @param  string $attrName   Attribute name
     * @param  string $attrValue  Attribute value
     * @return void
     * @see    AMI_PackageManager::onTagStart()
     */
    protected function onPackageAttributeManifestVersion($tagName, $attrName, $attrValue){
        $this->aPkgInfo['manifestVersion'] = $attrValue;
        switch($attrValue){
            case '1.0':
                $this->aXMLRules += array (
                    'package.information' => array(
                        'obligatory' => TRUE,
                        'attributes' => array(
                            'lang' => array(
                                'obligatory' => TRUE,
                                'callback'   => array($this, 'onInformationLangAttribute')
                            )
                        )
                    ),
                    'package.information.title' => array(
                        'obligatory'        => TRUE,
                        'callbackOnContent' => array($this, 'onInformationTagContent')
                    ),
                    'package.information.description' => array(
                        'obligatory'        => TRUE,
                        'callbackOnContent' => array($this, 'onInformationTagContent')
                    ),
                    'package.information.author' => array(
                        'obligatory'        => TRUE,
                        'callbackOnContent' => array($this, 'onInformationTagContent')
                    ),
                    'package.information.source' => array(
                        'obligatory'        => TRUE,
                        'callbackOnContent' => array($this, 'onInformationTagContent')
                    ),
                    'package.contents' => array(
                        'callbackOnStart' => array($this, 'onContentsTagStart')
                    ),
                    'package.contents.content' => array(
                        'obligatory' => TRUE,
                        'attributes' => array(
                            'preinstall'    => array(),
                            'postinstall'   => array(),
                            'hypermodule'   => array(),
                            'configuration' => array(),
                            'version'       => array()
                        ),
                        'callbackOnStart' => array($this, 'onContentTagStart')
                    ),
                    'package.installation' => array('obligatory' => TRUE),
                    'package.installation.install' => array(
                        'obligatory' => TRUE,
                        'attributes' => array(
                            'hypermodule'   => array('obligatory' => TRUE),
                            'configuration' => array()
                        ),
                        'callbackOnStart' => array($this, 'onInstallTagStart')
                    ),
                    'package.dependencies' => array(),
                    'package.dependencies.dependency' => array(
                        'obligatory' => TRUE,
                        'attributes' => array(
                            'hypermodule'   => array('obligatory' => TRUE),
                            'configuration' => array(),
                            'version'       => array('obligatory' => TRUE)
                        ),
                        'callbackOnStart' => array($this, 'onDependencyTagStart')
                    )
                );
                break;
            default:
                trigger_error("Unsupported manifest version '" . $attrValue . "'", E_USER_WARNING);
                $this->aParseError = array(
                    'errorCode'    => self::ERR_PARSE_INVALID_ATTRIBUTE,
                    'errorMessage' => 'error_parse_invalid_attribute_value',
                    'aErrorData'   => array('tag' => $tagKey, 'attribute' => $attrName, 'value' => $attrValue)
                );
                break;
        }
    }

    /**
     * Callback to parse package id/version and interrupt parser.
     *
     * @param  string $tagName  Tag name
     * @param  array  $aAttrs   Array of attributes
     * @return void
     * @see    AMI_PackageManager::onTagStart()
     */
    protected function onGetPackageAttributes($tagName, array $aAttrs){
        $this->aParseError = array('errorCode' => self::ERR_PARSE_INTERRUPTED);
    }

    /**
     * Callback to parse information tag lang attribute value.
     *
     * @param  string $tagName    Tag name
     * @param  string $attrName   Attribute name
     * @param  string $attrValue  Attribute value
     * @return void
     * @see    AMI_PackageManager::onTagStart()
     */
    protected function onInformationLangAttribute($tagName, $attrName, $attrValue){
        if(!isset($this->aPkgInfo['information'])){
            $this->aPkgInfo['information'] = array();
        }
        $this->aPkgInfo['information'][$attrValue] = array();
        $this->aTemp['lang'] = $attrValue;
    }

    /**
     * Callback to parse information tags content.
     *
     * @param  string $tagName  Tag name
     * @param  string $content  Content
     * @return void
     * @see    AMI_PackageManager::onTagStart()
     */
    protected function onInformationTagContent($tagName, $content){
        preg_match('/\.([^.]+)$/', $tagName, $aMatches);
        #if($aMatches[1] === 'title')d::vd($content);###
        $this->aPkgInfo['information'][$this->aTemp['lang']][$aMatches[1]] = $content;
    }

    /**
     * Callback to prepare contents tag package data.
     *
     * @param  string $tagName  Tag name
     * @param  array  $aAttrs   Array of attributes
     * @return void
     * @see    AMI_PackageManager::onTagStart()
     */
    protected function onContentsTagStart($tagName, array $aAttrs){
        if(!isset($this->aPkgInfo['contents'])){
            $this->aPkgInfo['contents'] = array();
        }
    }

    /**
     * Callback to parse content tag.
     *
     * @param  string $tagName  Tag name
     * @param  array  $aAttrs   Array of attributes
     * @return void
     * @see    AMI_PackageManager::onTagStart()
     */
    protected function onContentTagStart($tagName, array $aAttrs){
        if(isset($aAttrs['preinstall'])){
            $this->aPkgInfo['contents']['preinstall'] = $aAttrs['preinstall'];
        }else{
            if(!$this->validateHyperConfAttrs($aAttrs, array('hypermodule', 'version'))){
                return;
            }
            if(!isset($this->aPkgInfo['code'])){
                $this->aPkgInfo['code'] = array();
            }
            $this->aPkgInfo['code'][] = $aAttrs;
        }
    }

    /**
     * Callback to parse install tag.
     *
     * @param  string $tagName  Tag name
     * @param  array  $aAttrs   Array of attributes
     * @return void
     * @see    AMI_PackageManager::onTagStart()
     */
    protected function onInstallTagStart($tagName, array $aAttrs){
        if(!$this->validateHyperConfAttrs($aAttrs, array('hypermodule'))){
            return;
        }
        $first = empty($this->aPkgInfo['install']);
        if($first){
            $this->aPkgInfo['install'] = array();
        }elseif(!isset($aAttrs['postfix'])){
            $tag = implode('.', $this->aTagStack);
            $this->aParseError = array(
                'errorCode'    => self::ERR_PARSE_MISSING_ATTRIBUTE,
                'errorMessage' => 'error_parse_missing_tag_attribute',
                'aErrorData'   => array('tag' => $tag, 'attribute' => 'postfix')
            );
            return;
        }
        $this->aPkgInfo['install'][] = $aAttrs;
    }

    /**
     * Callback to parse dependency tag.
     *
     * @param  string $tagName  Tag name
     * @param  array  $aAttrs   Array of attributes
     * @return void
     * @see    AMI_PackageManager::onTagStart()
     */
    protected function onDependencyTagStart($tagName, array $aAttrs){
        if(!$this->validateHyperConfAttrs($aAttrs, array('hypermodule', 'version'))){
            return;
        }
        if(!isset($this->aPkgInfo['dependencies'])){
            $this->aPkgInfo['dependencies'] = array();
        }
        $this->aPkgInfo['dependencies'][] = $aAttrs;
    }

    /**
     * Validates version/hypermodule/configuration attributes.
     *
     * @param  array $aAttrs            Array of attributes
     * @param  array $aObligatoryAttrs  Array of obligatory attributes
     * @return bool
     */
    protected function validateHyperConfAttrs(array $aAttrs, array $aObligatoryAttrs){
        $tag = implode('.', $this->aTagStack);

        foreach($aObligatoryAttrs as $attrName){
            if(!isset($aAttrs[$attrName])){
                $this->aParseError = array(
                    'errorCode'    => self::ERR_PARSE_MISSING_ATTRIBUTE,
                    'errorMessage' => 'error_parse_missing_tag_attribute',
                    'aErrorData'   => array('tag' => $tag, 'attribute' => $attrName)
                );
                return FALSE;
            }
        }

        foreach(array(
            'version'       => '/^[0-9]+\.[0-9]+$/',
            'hypermodule'   => '/^[a-z](?:[a-z\d]|_[a-z])+$/',
            'configuration' => '/^[a-z](?:[a-z\d]|_[a-z])+$/'
        ) as $attrName => $attrRegExp){
            if(
                isset($aAttrs[$attrName]) && !preg_match($attrRegExp, $aAttrs[$attrName]) &&
                ('hypermodule' !== $attrName ? TRUE : 'ami_multifeeds5' !== $aAttrs[$attrName])
            ){
                $this->aParseError = array(
                    'errorCode'    => self::ERR_PARSE_INVALID_ATTRIBUTE,
                    'errorMessage' => 'error_parse_invalid_attribute_value',
                    'aErrorData'   => array('tag' => $tag, 'attribute' => $attrName, 'value' => $aAttrs[$attrName])
                );
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Returns URL of package file.
     *
     * @param  string $pkgId  Package id
     * @return string
     * @amidev
     */
    protected function getRemoteFileURL($pkgId, $hash){
        $url = 'http://amiro.ru/market_dl.php?pkg_id=' . $pkgId;
        if($hash){
            $url .= ('&marketHash=' . urlencode($hash));
        }
        return $url;
    }

    /**
     * Count number of package installations.
     *
     * @param  string $pkgId  Package Id
     * @return int
     */
    protected function countPackageInstallations($pkgId){
        $aInstallations = array();
        $oDeclarator = AMI_ModDeclarator::getInstance();
        $aAllModIds = $oDeclarator->getRegistered();
        foreach($aAllModIds as $modId){
            if(is_null($oDeclarator->getParent($modId))){
                if($GLOBALS['Core']->isInstalled($modId)){
                    $instPkgId = $oDeclarator->getAttr($modId, 'id_pkg', '');
                    if($instPkgId == $pkgId){
                        $instId = $oDeclarator->getAttr($modId, 'id_install', '');
                        $aInstallations['inst_' . $instId] = $instId;
                    }
                }
            }
        }
        return sizeof($aInstallations);
    }

    /**
     * Returns true if package instance is accessible in admin area.
     *
     * @param  string $pkgId  Package Id
     * @return boolean
     */
    protected function packageHasAdmin($pkgId){
        $oDeclarator = AMI_ModDeclarator::getInstance();
        $modId = $this->packageFirstInstance($pkgId);
        if($modId){
            $flags = $oDeclarator->getAttr($modId, 'flags');
            if($flags & AMI_ModDeclarator::INTERFACE_ADMIN){
                return true;
            }
        }
        return false;
    }

    /**
     * Returns first package instance Id.
     *
     * @param  string $pkgId  Package Id
     * @return string
     */
    protected function packageFirstInstance($pkgId){
        $oDeclarator = AMI_ModDeclarator::getInstance();
        $aAllModIds = $oDeclarator->getRegistered();
        foreach($aAllModIds as $modId){
            if(is_null($oDeclarator->getParent($modId))){
                $instPkgId = $oDeclarator->getAttr($modId, 'id_pkg', '');
                if($instPkgId == $pkgId){
                    return $modId;
                }
            }
        }
        return '';
    }

    /**
     * Generate fatal error based on error of lock.
     *
     * @return void
     * @throws AMI_Tx_PackageManager_Exception  If locked.
     */
    protected function genLockError(){
        $aError = $this->oLock->getError();
        throw new AMI_Tx_PackageManager_Exception(
            '[ ' . $aError['code'] . ' ] ' . $aError['message'],
            AMI_Tx_PackageManager_Exception::LOCKED
        );
    }

    /**
     * Try to create lock file.
     *
     * @return void
     */
    protected function lock(){
        self::$lockPath = AMI_Registry::get('path/root') . '_mod_files/_upload/tmp/package_manager.lock';
        $this->oLock = new AMI_Lock;
        $oSession = AMI::getSingleton('env/session');
        if(!$oSession->isStarted()){
             // start session if it is not started
             $oSession->start();
        }
        $oUser = $oSession->getUserData();
        $login = is_object($oUser) ? $oUser->login : '{API}';
        $res = $this->oLock->create(
            self::$lockPath,
            $login . ' / ' . $_SERVER['REMOTE_ADDR'] . ' / ' . uniqid('', TRUE),
            self::LOCK_TTL
        );
        if(!$res){
            $this->genLockError();
        }
    }
}
