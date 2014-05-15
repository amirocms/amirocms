<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module_Articles
 * @version   $Id: AmiDataExchange_--modId--_Table.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @amidev    Temporary
 */

/**
 * Articles module table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 *
 * Articles fields description:
 * - <b>author</b> - article author (string),
 * - <b>source</b> - article source (string).
 *
 * @package    Module_Articles
 * @subpackage Model
 * @amidev     Temporary
 */
class AmiDataExchange_##modId##_Table extends AMI_FileIterator{
    /**
     * Import path
     *
     * @var string
     */
    protected $importPath = '';

    /**
     * Max import file size
     *
     * @var int
     */
    protected $maxImportFileSize = 0;

    /**
     * Array of file types
     *
     * @var array
     */
    protected $aFileTypes = null;

    /**
     * Files prefix
     *
     * @var string
     */
    protected $filesPrefix;

    /**
     * Files extension
     *
     * @var string
     */
    protected $filesExtension;

    /**
     * New files path
     *
     * @var string
     */
    protected $filesPath;

    /**
     * Constructor.
     *
     * @param string $path  Import path
     */
    public function __construct($path = null){
        $path = AMI_Registry::get('MODULE_PICTURES_PATH') . (AMI::issetOption($this->getModId(), 'import_path') ? AMI::getOption($this->getModId(), 'import_path') : AMI::getOption('files', 'import_path'));
        $this->importPath = $path;

        $this->maxImportFileSize = AMI::issetOption($this->getModId(), 'max_import_size') ? AMI::getOption($this->getModId(), 'max_import_size') : AMI::getOption('files', 'max_import_size');

        $this->filesPrefix = AMI::issetOption($this->getModId(), 'files_prefix') ? AMI::getOption($this->getModId(), 'files_prefix') : AMI::getOption('files', 'files_prefix');

        $this->filesExtension = AMI::issetOption($this->getModId(), 'files_extension') ? AMI::getOption($this->getModId(), 'files_extension') : AMI::getOption('files', 'files_extension');

        $this->filesPath = AMI_Registry::get('MODULE_PICTURES_PATH') . (AMI::issetOption($this->getModId(), 'files_path') ? AMI::getOption($this->getModId(), 'files_path') : AMI::getOption('files', 'files_path'));

        parent::__construct($path);
    }

    /**
     * Gets import path.
     *
     * @return string
     */
    public function getImportPath(){
        return $this->importPath;
    }

    /**
     * Returns max import file size.
     *
     * @return int
     */
    public function getMaxImportFileSize(){
        return $this->maxImportFileSize;
    }

    /**
     * Returns files prefix.
     *
     * @return int
     */
    public function getFilesPrefix(){
        return $this->filesPrefix;
    }

    /**
     * Returns files extension.
     *
     * @return int
     */
    public function getFilesExtension(){
        return $this->filesExtension;
    }

    /**
     * Returns new files path.
     *
     * @return int
     */
    public function getNewFilesPath(){
        return $this->filesPath;
    }

    /**
     * Returns file types.
     *
     * @return array
     */
    public function getFileTypes(){
        if(!is_null($this->aFileTypes)){
            return $this->aFileTypes;
        }

        // get file types
        $oQuery = new DB_Query('cms_ftypes');
        $oQuery->addFields(array('id', 'name', 'icon', 'extensions'));
        $oRS = AMI::getSingleton('db')->select($oQuery);
        if($oRS->count()){
            foreach($oRS as $aRow){
                $aExts = explode(';', mb_strtolower($aRow['extensions']));
                foreach($aExts as $key => $value){
                    if(!empty($value) || mb_strlen($aRow['extensions']) == 0){
                        $this->aFileTypes['.'.$value] = $aRow;
                    }
                }
            }
        }
        return $this->aFileTypes;
    }
}

/**
 * Articles module table item model.
 *
 * @package    Module_Articles
 * @subpackage Model
 * @amidev     Temporary
 */
class AmiDataExchange_##modId##_TableItem extends AMI_FileIteratorItem{
}

/**
 * Articles module table list model.
 *
 * @package    Module_Articles
 * @subpackage Model
 * @amidev     Temporary
 */
class AmiDataExchange_##modId##_TableList extends AMI_FileIteratorList{
}
