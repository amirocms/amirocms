<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_File.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * File interface.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
interface AMI_iFile{
    /**#@+
     * Path type.
     *
     * @see    AMI_iFile::getLocalName()
     */

    /**
     * No path
     */
    const PATH_NONE = 0;

    /**
     * Full path
     */
    const PATH_FULL = 1;

    /**
     * Path relative to web root
     */
    const PATH_WEB  = 2;

    /**#@-*/

    /**
     * Moves file.
     *
     * @param  string $newName  New name
     * @return AMI_iFile|null
     */
    public function move($newName);

    /**
     * Copies file.
     *
     * @param  string $newName  New name
     * @return AMI_iFile|null
     */
    public function copy($newName);

    /**
     * Deletes file.
     *
     * @return bool
     */
    public function delete();

    /**
     * Returns content.
     *
     * @return string|null
     */
    public function getContent();

    /**
     * Adds validators.
     *
     * @param  array $aValidators  Array of validators
     * @return AMI_iFile|null
     * @see    AMI_iFile::isValid()
     */
    public function addValidators(array $aValidators);

    /**
     * Returns TRUE if all validators passed.
     *
     * @return bool
     * @see    AMI_iFile::addValidators()
     * @see    AMI_iFile::hasError()
     * @see    AMI_iFile::getErrors()
     */
    public function isValid();

    /**
     * Retrurns errors list.
     *
     * @return array
     */
    public function getErrors();

    /**
     * Returns TRUE if has specified errors.
     *
     * @param  int $code  Error code mask
     * @return bool
     */
    public function hasError($code);

    /**
     * Returns local name.
     *
     * @param  int $pathType  AMI_iFile::PATH_*
     * @return string|null
     */
    public function getLocalName($pathType = AMI_iFile::PATH_FULL);

    /**
     * Returns source name.
     *
     * @return string|null
     */
    public function getName();

    /**
     * Returns size (in bytes).
     *
     * @return int|null
     */
    public function getSize();

    /**
     * Returns type.
     *
     * @return int|null
     */
    public function getType();

    /**
     * Returns content type.
     *
     * @return string|null
     */
    public function getContentType();

    /**
     * Returns extension.
     *
     * @return string|null
     */
    public function getExtension();

    /**
     * Sets parameter value.
     *
     * @param  string $name   Parameter name
     * @param  mixed  $value  Parameter value
     * @return AMI_iFile
     */
    public function setParameter($name, $value);

    /**
     * Returns parameter.
     *
     * @param  string $name  Parameter name
     * @return string|null
     */
    public function getParameter($name);
}

/**
 * File validator interface.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
interface AMI_iFileValidator{
    /**
     * Returns name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns valid flag.
     *
     * @param  AMI_iFile $oFile  File object
     * @return bool
     */
    public function isValid(AMI_iFile $oFile);

    /**
     * Returns validation error.
     *
     * @return string
     */
    public function getError();
}

/**
 * File field class.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @resource   env/file/local <code>AMI_FileFactory::getFile(array('name' => 'filename', 'path' => 'path/to/file'), 'env/file/local')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AMI_File_Local implements AMI_iFile{
    /**
     * Resource id
     *
     * @var string
     */
    protected $resId = 'env/file/local';

    /**
     * Factory resource id
     *
     * @var string
     */
    protected $factoryResId = 'env/file';

    /**
     * Parameters
     *
     * @var array
     */
    protected $aParams;

    /**
     * Array of validators
     *
     * @var array
     */
    protected $aValidators = array();

    /**
     * Array of errors
     *
     * @var array
     */
    protected $aErrors;

    /**
     * Constructor.
     *
     * @param array $aParams  Params
     */
    public function __construct(array $aParams = array()){
        $this->init();
        $this->aParams = $aParams + $this->aParams;
        $this->aParams['path'] = $this->getPath($this->aParams['path']);
        $path = $this->aParams['path'] . $this->aParams['name'];
        if(mb_strlen($path) && !is_file($path)){
            $this->init();
        }
        $this->getSize();
    }

    /**
     * Returns file name.
     *
     * @return string
     */
    public function __toString(){
        return (string)$this->aParams['realName'];
    }

    /**
     * Moves file.
     *
     * @param  string $newName  New name
     * @return AMI_File_Local|null
     */
    public function move($newName){
        if(rename($this->getLocalName(), $newName)){
            $this->aParams['path'] = $this->getPath($newName);
            $this->aParams['name'] = basename($newName);
            return $this;
        }else{
            return null;
        }
    }

    /**
     * Copies file and returns new instance.
     *
     * @param  string $newName  New name
     * @return AMI_File_Local|null
     */
    public function copy($newName){
        if(copy($this->getLocalName(), $newName)){
            $aParams = $this->aParams;
            $aParams['path'] = $this->getPath($newName);
            $aParams['name'] = basename($newName);
            return AMI::getSingleton($this->factoryResId)->get($aParams);
        }else{
            return null;
        }
    }

    /**
     * Deletes file.
     *
     * @return bool
     */
    public function delete(){
        $result =
            mb_strlen($this->aParams['path']) &&
            mb_strlen($this->aParams['name'])
                ? unlink($this->aParams['path'] . $this->aParams['name'])
                : FALSE;
        if($result){
            $this->init();
        }
        return $result;
    }

    /**
     * Returns content.
     *
     * @return string|null
     */
    public function getContent(){
        $path = $this->getLocalName();
        if(is_null($path) || !file_exists($path)){
            return null;
        }else{
            $content = file_get_contents($path);
            return $content !== FALSE ? $content : null;
        }
    }

    /**
     * Adds validators.
     *
     * @param  array $aValidators  Array of validators
     * @return AMI_iFile
     * @see    AMI_iFile::isValid()
     */
    public function addValidators(array $aValidators){
        foreach($aValidators as $oValidator){
            if(is_object($oValidator) && ($oValidator instanceof AMI_iFileValidator)){
                $this->aValidators[$oValidator->getName()] = $oValidator;
            }else{
                trigger_error('Validator must be instace of AMI_iFileValidator', E_USER_WARNING);
            }
        }
        return $this;
    }

    /**
     * Returns TRUE if all validators passed.
     *
     * @param  bool $collectAll  Collect all errors
     * @return bool
     * @see    AMI_iFile::addValidators()
     * @see    AMI_iFile::hasError()
     * @see    AMI_iFile::getErrors()
     */
    public function isValid($collectAll = false){
        $res = TRUE;
        $this->aErrors = array();
        foreach($this->aValidators as $oValidator){
            if(!$oValidator->isValid($this)){
                $this->aErrors[$oValidator->getName()] = $oValidator->getError();
                $res = FALSE;
                if(!$collectAll){
                    break;
                }
            }
        }
        return $res;
    }

    /**
     * Retrurns error list.
     *
     * @return array
     */
    public function getErrors(){
        $this->isValid(TRUE);
        return $this->aErrors;
    }

    /**
     * Returns TRUE if has specified errors.
     *
     * @param  string $validatorName  Validator name
     * @return bool
     */
    public function hasError($validatorName){
        $this->isValid(TRUE);
        return isset($this->aErrors[$validatorName]);
    }

    /**
     * Returns local name.
     *
     * @param  int $pathType  AMI_iFile::PATH_*
     * @return string|null
     */
    public function getLocalName($pathType = AMI_iFile::PATH_FULL){
        switch($pathType){
            case AMI_iFile::PATH_NONE:
                return $this->aParams['name'];
            case AMI_iFile::PATH_FULL:
                $path = $this->aParams['path'] . $this->aParams['name'];
                return mb_strlen($path) ? $path : null;
                break;
        }
        return null;
    }

    /**
     * Returns source name.
     *
     * @return string|null
     */
    public function getName(){
        return $this->aParams['realName'];
    }

    /**
     * Returns size (in bytes).
     *
     * @return int|null
     */
    public function getSize(){
        $path = $this->aParams['path'] . $this->aParams['name'];
        if(
            is_null($this->aParams['size']) &&
            mb_strlen($this->aParams['path']) &&
            mb_strlen($this->aParams['name']) &&
            file_exists($path)

        ){
            $this->aParams['size'] = filesize($path);
        }
        return
            $this->aParams['size'] !== FALSE && $this->aParams['size'] !== null
            ? sprintf('%u', $this->aParams['size'])
            : NULL;
    }

    /**
     * Returns type.
     *
     * @return int|null
     */
    public function getType(){
        return $this->aParams['type'];
    }

    /**
     * Returns content type.
     *
     * @return string|null
     */
    public function getContentType(){
        return $this->aParams['contentType'];
    }

    /**
     * Returns extension.
     *
     * @return string|null
     */
    public function getExtension(){
        return is_null($this->aParams['name']) ? null : pathinfo($this->aParams['name'], PATHINFO_EXTENSION);
    }

    /**
     * Returns parameter.
     *
     * @param  string $name  Parameter name
     * @return string|null
     */
    public function getParameter($name){
        return $this->aParams[$name];
    }

    /**
     * Sets parameter value.
     *
     * @param  string $name   Parameter name, 'path' | 'name'
     * @param  mixed  $value  Parameter value
     * @return AMI_File_Local
     */
    public function setParameter($name, $value){
        switch($name){
            case 'path':
                $value = $this->getPath($value);
                break;
            case 'name':
                $value = basename($value);
                break;
        }
        $this->aParams[$name] = $value;
        return $this;
    }

    /**
     * Initializes object.
     *
     * @return AMI_File_Local
     */
    protected function init(){
        $this->aParams =
            array(
                'path'        => null, // storage path
                'name'        => null, // storage name
                'realName'    => null, // real name
                'type'        => null,
                'contentType' => null,
                'size'        => null
            );
        $this->aErrors = array();
        return $this;
    }

    /**
     * Converts path.
     *
     * @param  string $path  Path to convert
     * @return string
     */
    protected function getPath($path){
        if(
            mb_substr($path, 0, 1) != '/' &&
            !preg_match('/^[a-zA-Z]+:/', $path)
        ){
            $path = AMI_Registry::get('path/root') . $path;
        }
        if(!is_dir($path)){
            $path = dirname($path);
        }
        return realpath($path) . '/';
    }
}

/**
 * Local file presence validotor.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @resource   env/file/local/validator/presence <code>AMI::getResource('env/file/local/validator/presence')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AMI_File_LocalValidatePresence implements AMI_iFileValidator{
    /**
     * Flag specified is file valid
     *
     * @var bool
     */
    protected $bValid = FALSE;

    /**
     * Last passed file object path
     *
     * @var string
     */
    protected $lastPath = '';

    /**
     * Returns name.
     *
     * @return string
     */
    public function getName(){
        return 'presence';
    }

    /**
     * Returns valid flag.
     *
     * @param  AMI_iFile $oFile  File object
     * @return bool
     */
    public function isValid(AMI_iFile $oFile){
        $this->lastPath = $oFile->getLocalName();
        $this->bValid = file_exists($this->lastPath) && is_readable($this->lastPath);
        return $this->bValid;
    }

    /**
     * Returns validation error.
     *
     * @return string
     */
    public function getError(){
        $error = '';
        if(!$this->bValid){
            $error = file_exists($this->lastPath) ? 'File cannot be read' : 'File not found';
        }
        return $error;
    }
}

/**
 * File factory.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @resource   env/file <code>AMI::getResource('env/file')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AMI_FileFactory{
    /**
     * Module id
     *
     * @var string
     */
    protected $modId;

    /**
     * Common validators
     *
     * @var  array
     * @todo Avild hardcoded 'local'
     */
    protected $aCommonValidators = array('env/file/local/validator/presence');

    /**
     * Array of validators
     *
     * @var array
     */
    protected $aValidators = array();

    /**
     * Constructor.
     *
     * @param string $modId  Module id
     */
    public function __construct($modId = ''){
        $this->modId = (string)$modId;
        $this->init();
    }

    /**
     * Sets common validators.
     *
     * @param  array $aValidators  Array of validators
     * @return AMI_FileFactory
     */
    public function setValidators(array $aValidators){
        foreach($aValidators as $oValidator){
            if(is_object($oValidator) && ($oValidator instanceof AMI_iFileValidator)){
                $this->aValidators[$oValidator->getName()] = $oValidator;
            }else{
                trigger_error('Validator must be instace of AMI_iFileValidator', E_USER_WARNING);
            }
        }
        return $this;
    }

    /**
     * Returns file object.
     *
     * @param array  $aParams  Parameters
     * @param string $type     File type
     * @return AMI_iFile
     */
    public function get(array $aParams = array(), $type = 'local'){
        /**
         * @var AMI_iFile
         */
        $oFile = AMI::getResource('env/file/' . $type, array($aParams));
        if(sizeof($this->aValidators)){
            $oFile->addValidators($this->aValidators);
        }
        return $oFile;
    }

    /**
     * Returns uploaded files.
     *
     * @param  array $aCodes  File codes to return
     * @return array
     */
    public function getUploaded(array $aCodes){
        // Read temporary directory for uploaded files
        // todo: rid off global vars and ${'...'} stuff
        global ${'CONFIG_INI'};
        $tmpDir =
            isset(${'CONFIG_INI'}['defaults']['upload_path'])
            ? ${'CONFIG_INI'}['defaults']['upload_path']
            : $GLOBALS['ROOT_PATH'] . '_mod_files/_upload/tmp';
        $aResult = array();
        while(in_array('uploaded', $aCodes)){
            unset($aCodes[array_search('uploaded', $aCodes)]);
        }
        if(sizeof($aCodes)){
            /**
             * @var AMI_iDB
             */
            $oDB = AMI::getSingleton('db');
            $oRS = $oDB->select(
                DB_Query::getSnippet(
                    "SELECT `code`, `filename_temp`, `filename`, `content_type` " .
                    "FROM `cms_tmp_files` " .
                    "WHERE `code` IN (%s) AND `date_expired` >= NOW()"
                )->implode($aCodes)
            );
            foreach($oRS as $aRecord){
                $aResult[$aRecord['code']] = $this->get(
                    array(
                        'path'        => $tmpDir . '/' . $aRecord['filename_temp'],
                        'name'        => $aRecord['filename_temp'],
                        'realName'    => $aRecord['filename'],
                        // 'type'       => null,
                        'contentType' => $aRecord['content_type']
                    )
                );
            }
        }
        return $aResult;
    }

    /**
     * Moves several files to the same path.
     *
     * @param  AMI_ModTableItem $oItem       Item model
     * @param  string           $targetPath  Target path
     * @param  array            $aFiles      Array of AMI_iFile objects
     * @return bool
     */
    public function move(AMI_ModTableItem $oItem, $targetPath, array $aFiles){
        $aSourcePathes = array();
        $aMoved = array();
        $res = FALSE;
        do{
            foreach($aFiles as $oFile){
                $aSourcePathes[] = basename(str_replace('\\', '/', $oFile->getLocalName()));
                $aEvent = array(
                    'oItem'      => $oItem,
                    'oFile'      => $oFile,
                    'targetPath' => $targetPath,
                    'newName'    => $oFile->getLocalName(AMI_iFile::PATH_NONE),
                );
                /**
                 * Called before file move.
                 *
                 * @event      on_file_move $modId
                 * @eventparam AMI_ModTableItem oItem  Item Object
                 * @eventparam AMI_iFile oFile  File Object
                 * @eventparam string targetPath  Target dir path
                 * @eventparam string newName  New filename
                 */
                AMI_Event::fire('on_file_move', $aEvent, $oItem->getModId());
                $path = $aEvent['targetPath'] . $aEvent['newName'];
                if(!$oFile->move($path)){
                    foreach($aMoved as $oMovedFile){
                        $oMovedFile->delete();
                    }
                    break 2;
                }
                $aMoved[] = $oFile;
            }
            $res = TRUE;
        }while(FALSE);
        if(sizeof($aSourcePathes)){
            $oQuery = DB_Query::getSnippet('DELETE FROM `cms_tmp_files` WHERE `filename_temp` IN (%s)')->implode($aSourcePathes);
            /**
             * @var AMI_iDB
             */
            $oDB = AMI::getSingleton('db');
            $oDB->query($oQuery);
        }
        return $res;
    }

    /**
     * Initialize factory.
     *
     * @return AMI_FileFactory
     */
    protected function init(){
        /*
        $aEvent = array(
            'modId'        => $this->modId,
            'oFileFactory' => $this
        );
        AMI_Event::fire('on_file_factory_init', $aEvent, $this->modId);
        */
        $aValidators = array();
        foreach($this->aCommonValidators as $resId){
            $aValidators[] = AMI::getResource($resId);
        }
        $this->setValidators($aValidators);
        return $this;
    }
}
