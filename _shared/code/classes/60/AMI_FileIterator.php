<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_FileIterator.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Module file iterator model.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class AMI_FileIterator extends AMI_ArrayIterator{
    /**
     * Initial path to search files in
     *
     * @var string
     */
    protected $path = '';

    /**
     * Fields of the array
     *
     * @var array
     */
    protected $aFields = array(
        'id',
        'filepath',
        'filename',
        'filesize',
        'extension',
        'creation_date',
        'modification_date'
    );

    /**
     * Primary key field name
     *
     * @var string
     */
    protected $primaryKeyField = 'id';

    /**
     * Initializing file iterator data.
     *
     * @param string $path  Initial path
     */
    public function __construct($path = null){
        if(is_null($path)){
            $path = $GLOBALS['HOST_PATH'];
        }
        if(is_dir($path) && is_readable($path)){
            $this->path = rtrim(realpath($path), '/');
        }else{
            trigger_error(
                "Path '" . $path . "' is not a correct initial path for file iterator model",
                E_USER_ERROR
            );
        }
    }

    /**
     * Returns current model file path.
     *
     * @return string
     */
    public function getPath(){
        return $this->path;
    }
}

/**
 * Module file iterator list model.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class AMI_FileIteratorList extends AMI_ArrayIteratorList{
    /**
     * Loads data from table and init recordset.
     *
     * @return AMI_FileIteratorList
     */
    public function load(){
        $aEvent = array(
            'modId' => $this->getModId(),
            'oList' => $this,
        );
        /**
         * Allows to manipulate with elements list's request (object of class DB_Query).
         *
         * @event      on_list_recordset $modId
         * @eventparam string modId  Module Id
         * @eventparam AMI_FileIteratorList oList   Table list model
         */
        AMI_Event::fire('on_list_recordset', $aEvent, $this->getModId());

        if($this->aCondition['filename'] !== FALSE){
            $this->aCondition['filename'] = rtrim($this->aCondition['filename'], '*');
            $this->mask = $this->aCondition['filename'] . $this->mask;
        }
        $path = $this->oIterator->getPath() . '/' . $this->mask;
        $this->aRaw = array();
        if(AMI_Lib_FS::validatePath($path)){
            $this->aRaw = array_values(array_diff(glob($path), glob($path, GLOB_ONLYDIR)));
        }
        foreach($this->aRaw as $i => $filePath){
            $this->aRaw[$i] = array(
                'filepath' => $filePath,
                'filename' => str_replace($this->oIterator->getPath() . '/', '', $filePath),
            );
            $this->aRaw[$i]['id'] = $this->aRaw[$i]['filename'];
        }

        $this->total = sizeof($this->aRaw);
        $this->sortList();
        $this->storeKeys();
        $this->loadCurrentPage();
        $this->seek($this->start);
        $this->aRaw = false;
        return $this;
    }
}

/**
 * Module file iterator item model.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class AMI_FileIteratorItem extends AMI_ArrayIteratorItem{
    /**
     * Initializing item data.
     *
     * @param  AMI_FileIterator $oIterator  Module file iterator model
     * @param  array $aData                 Data
     * @param  array $aFields               Set of fields
     */
    public function __construct(AMI_FileIterator $oIterator, array $aData = array(), array $aFields = array()){
        if(isset($aData['filepath'])){
            $aStat = stat($aData['filepath']);
            $aData['filesize'] = $aStat['size'];
            $aData['creation_date'] = $aStat['ctime'];
            $aData['modification_date'] = $aStat['mtime'];
            $aData['extension'] = '.' . mb_strtolower(pathinfo($aData['filepath'], PATHINFO_EXTENSION), 'utf-8');
        }
        parent::__construct($oIterator, $aData, $aFields);
    }
}
