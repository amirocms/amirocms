<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Service
 * @version   $Id: AMI_Storage.php 48697 2014-03-14 13:04:22Z Leontiev Anton $
 * @version   0.8 alpha
 * @since     6.0.2
 * @todo      Use stream context instead of drivers?
 */

/**
 * Storage interface.
 *
 * @package    Service
 * @subpackage Controller
 * @since      6.0.2
 */
interface AMI_iStorage{
    const MODE_MAKE_FOLDER_ON_COPY = 0x01;

    /**
     * Set mode.
     *
     * @param  int $mode  Mode
     * @return void
     */
    public function setMode($mode);

    /**
     * Checks whether a file or directory exists.
     *
     * @param  string $file  Path to the file
     * @return bool   Returns TRUE if the file or directory specified by path exists; FALSE otherwise
     */
    public function exists($file);

    /**
     * Reads entire file into a string.
     *
     * @param  string $file  Name of the file to read
     * @param  bool   $warn  Flag specifying to warn if cannot load
     * @return mixed  String or FALSE on failure
     */
    public function load($file, $warn = TRUE);

    /**
     * Write a string to a file.
     *
     * @param  string $file       Path to the file where to write the data
     * @param  string $content    The data to write
     * @param  bool   $asDefault  Save as default content
     * @return bool   Returns TRUE on success or FALSE on failure
     */
    public function save($file, $content, $asDefault = FALSE);

    /**
     * Copies file.
     *
     * @param  string $from  Path to the source file
     * @param  string $to    The destination path
     * @return bool   Returns TRUE on success or FALSE on failure
     */
    public function copy($from, $to);

    /**
     * Renames a file.
     *
     * @param  string $from  The old name
     * @param  string $to    The new name
     * @return bool   Returns TRUE on success or FALSE on failure
     */
    public function rename($from, $to);

    /**
     * Deletes a file.
     *
     * @param string $file  Path to the file
     * @return bool  Returns TRUE on success or FALSE on failure.
     */
    public function delete($file);

    /**
     * Makes directory.
     *
     * @param  string $path  The directory path
     * @return bool
     */
    public function mkdir($path);

    /**
     * Removes directory.
     *
     * @param  string $path    The directory path
     * @param  bool   $silent  Skip warning if failed
     * @return bool
     */
    public function rmdir($path, $silent = FALSE);
}

/**
 * File system storage driver.
 *
 * @package    Service
 * @subpackage Controller
 * @since      6.0.2
 * @resource   storage/fs <code>AMI::getResource('storage/fs')</code>
 */
class AMI_Storage_FS implements AMI_iStorage{
    /**
     * Mode
     *
     * @var int
     */
    protected $mode;

    /**
     * Set mode.
     *
     * @param  int $mode  Mode
     * @return void
     */
    public function setMode($mode){
        $this->mode = (int)$mode;
    }

    /**
     * Checks whether a file or directory exists.
     *
     * @param  string $file  Path to the file
     * @return bool   Returns TRUE if the file or directory specified by path exists; FALSE otherwise
     */
    public function exists($file){
        return file_exists($file);
    }

    /**
     * Reads entire file into a string.
     *
     * @param  string $file  Name of the file to read
     * @param  bool   $warn  Flag specifying to warn if cannot load
     * @return mixed  String or FALSE on failure
     */
    public function load($file, $warn = TRUE){
        return
            $warn
                ? file_get_contents($file)
                : @file_get_contents($file);
    }

    /**
     * Write a string to a file.
     *
     * @param  string $file       Path to the file where to write the data
     * @param  string $content    The data to write
     * @param  bool   $asDefault  Save as default content
     * @return bool   Returns TRUE on success or FALSE on failure
     */
    public function save($file, $content, $asDefault = FALSE){
        $res = file_put_contents($file, $content);
        /*
        if($res){
            chmod($file, 0666);
        }
        */
        return $res;
    }

    /**
     * Copies file.
     *
     * @param  string $from  Path to the source file
     * @param  string $to    The destination path
     * @return bool   Returns TRUE on success or FALSE on failure
     */
    public function copy($from, $to){
        $res = TRUE;
        if($this->mode & self::MODE_MAKE_FOLDER_ON_COPY){
            $dir = dirname($to);
            if(!file_exists($dir)){
                $res = mkdir($dir, 0777, TRUE);
            }
        }
        $res = $res && copy($from, $to);
        /*
        if($res){
            chmod($to, 0666);
        }
        */
        return $res;
    }

    /**
     * Renames a file.
     *
     * @param  string $from  The old name
     * @param  string $to    The new name
     * @return bool   Returns TRUE on success or FALSE on failure
     */
    public function rename($from, $to){
        return rename($from, $to);
    }

    /**
     * Deletes a file.
     *
     * @param string $file  Path to the file
     * @return bool  Returns TRUE on success or FALSE on failure.
     */
    public function delete($file){
        return unlink($file);
    }

    /**
     * Makes directory.
     *
     * @param  string $path  The directory path
     * @return bool
     */
    public function mkdir($path){
        return mkdir($path);
    }

    /**
     * Removes directory.
     *
     * @param  string $path    The directory path
     * @param  bool   $silent  Skip warning if failed
     * @return bool
     */
    public function rmdir($path, $silent = FALSE){
        return $silent ? @rmdir($path) : rmdir($path);
    }
}

/**
 * Template storage storage.
 *
 * @package    Service
 * @subpackage Controller
 * @since      6.0.2
 * @resource   storage/tpl <code>AMI::getResource('storage/tpl')</code>
 */
class AMI_Storage_Template implements AMI_iStorage{
    /**
     * Mode
     *
     * @var int
     */
    protected $mode;

    /**
     * Set mode.
     *
     * @param  int $mode  Mode
     * @return void
     */
    public function setMode($mode){
        $this->mode = (int)$mode;
    }

    /**
     * System teplate object
     *
     * @var gui
     */
    protected $oTpl;

    /**
     * DB object
     *
     * @var DB_si
     */
    protected $oDB;

    /**
     * Constructor.
     */
    public function __construct(){
        $this->oTpl = AMI_Registry::get('oGUI');
        $this->oDB  = new DB_si;
    }

    /**
     * Checks whether a file or directory exists.
     *
     * @param  string $file  Path to the file
     * @return int    Returns id
     */
    public function exists($file){
        list($path, $name) = $this->divideFile($file);
        $table = $this->getTable($name);
        /**
         * @var AMI_DB
         */
        $oDB = AMI::getSingleton('db');
        $oQuery =
            DB_Query::getSnippet(
                "SELECT `id` " .
                "FROM `{$table}` " .
                "WHERE `path` = %s AND `name` = %s"
            )
            ->q($path)
            ->q($name);
        $oDB->allowQuotesInQueryOnce();
        return (int)$oDB->fetchValue($oQuery->get() . $this->oTpl->getSqlFilter());
    }

    /**
     * Reads entire file into a string.
     *
     * @param  string $file  Name of the file to read
     * @param  bool   $warn  Flag specifying to warn if cannot load
     * @return mixed  String or FALSE on failure
     */
    public function load($file, $warn = TRUE){
        $this->switchTplMode();
        $content = $this->oTpl->_readFromFile($file);
        $this->switchTplMode();

        return $content;
    }

    /**
     * Write a string to a file.
     *
     * @param  string $file       Path to the file where to write the data
     * @param  string $content    The data to write
     * @param  bool   $asDefault  Save as default content
     * @return bool   Returns TRUE on success or FALSE on failure
     */
    public function save($file, $content, $asDefault = FALSE){
        list($path, $name) = $this->divideFile($file);
        $this->switchTplMode();
        $res = $this->oTpl->_putFileContentToDb(
            $this->oDB,
            $path,
            $name,
            time(),
            $content,
            preg_match('/\.lng$/', $name),
            TRUE,
            FALSE,
            TRUE,
            $asDefault
        );
        $this->switchTplMode();
        $this->oTpl->cacheClear();

        return $res === 1 || $res === 2;
    }

    /**
     * Copies file.
     *
     * @param  string $from  Path to the source file
     * @param  string $to    The destination path
     * @return bool   Returns TRUE on success or FALSE on failure
     */
    public function copy($from, $to){
        $res = FALSE;
        if($from !== $to && $this->exists($from)){
            $content = $this->load($from);
            $res = $this->save($to, $content);
        }
        return $res;
    }

    /**
     * Renames a file.
     *
     * @param  string $from  The old name
     * @param  string $to    The new name
     * @return bool   Returns TRUE on success or FALSE on failure
     */
    public function rename($from, $to){
        $res = FALSE;
        if($from !== $to){
            $id = $this->exists($from);
            if($id){
                /**
                 * @var AMI_DB
                 */
                $oDB = AMI::getSingleton('db');
                if($this->exists($to)){
                    $this->delete($to);
                }
                list($path, $name) = $this->divideFile($to);
                $table = $this->getTable($name);
                $oQuery = DB_Query::getUpdateQuery($table, array('path' => $path, 'name' => $name), "WHERE `id` = {$id}");
                $oDB->query($oQuery);
                $res = TRUE;
                if($res){
                    $this->oTpl->dropFileCache($from);
                }
            }
        }
        return $res;
    }

    /**
     * Deletes a file.
     *
     * @param string $file  Path to the file
     * @return bool  Returns TRUE on success or FALSE on failure.
     */
    public function delete($file){
        /**
         * @var AMI_DB
         */
        $oDB = AMI::getSingleton('db');
        list($path, $name) = $this->divideFile($file);
        $table = $this->getTable($name);
        $oQuery =
            DB_Query::getSnippet(
                "DELETE FROM `{$table}` " .
                "WHERE `path` = %s AND `name` = %s"
            )
            ->q($path)
            ->q($name);
        $oDB->allowQuotesInQueryOnce();
        $oDB->query($oQuery->get() . $this->oTpl->getSqlFilter());
        $res = (bool)$oDB->getAffectedRows();
        if($res){
            $this->oTpl->dropFileCache($file);
        }
        return $res;
    }

    /**
     * Divedes file for path and name.
     *
     * @param  string $file  File name
     * @return array  Array (path, name)
     */
    protected function divideFile($file){
        $path = dirname($file) . '/';
        $name = basename($file);
        return array($path, $name);
    }

    /**
     * Returns table name by file extension.
     *
     * @param  string $file  File name
     * @return string
     */
    protected function getTable($file){
        return 'cms_modules_templates' . (preg_match('/\.lng_?$/', $file) ? '_langs' : '');
    }

    /**
     * Makes directory.
     *
     * @param  string $path  The directory path
     * @return bool
     */
    public function mkdir($path){
        return TRUE;
    }

    /**
     * Removes directory.
     *
     * @param  string $path    The directory path
     * @param  bool   $silent  Skip warning if failed
     * @return bool
     */
    public function rmdir($path, $silent = FALSE){
        return TRUE;
    }

    /**
     * Switches template engine to front/admin mode.
     *
     * @return void
     */
    protected function switchTplMode(){
        static $savedState = NULL;

        if(is_null($savedState)){
            $savedState = $this->oTpl->getReadFromDB();
            if(TRUE !== $savedState){
                $this->oTpl->setReadFromDB(TRUE);
            }
        }else{
            if(TRUE !== $savedState){
                $this->oTpl->setReadFromDB($savedState);
            }
            $savedState = NULL;
        }
    }
}
