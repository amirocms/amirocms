<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Library
 * @version   $Id: AMI_Lib_FS.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * File system library.
 *
 * @package Library
 * @static
 * @since   x.x.x
 * @amidev  Temporary
 */
class AMI_Lib_FS{
    const SCAN_FILES = 0x01;
    const SCAN_DIRS  = 0x02;
    const SCAN_BOTH  = 0x03; // self::SCAN_FILES & self::SCAN_DIRS
    const SCAN_CI    = 0x04; // case insensitive

    /**
     * Scans fs directory path recursively.
     *
     * @param  string $path      Starting path
     * @param  string $fileMask  File mask
     * @param  string $dirMask   Dir mask
     * @param  int $flags        Scan flags: AMI_Lib_FS::SCAN_FILES, AMI_Lib_FS::SCAN_DIRS, AMI_Lib_FS::SCAN_BOTH, AMI_Lib_FS::SCAN_CI
     * @param  null|int $level   Subdir recursion level, unlimited if null
     * @return array
     */
    public static function scan($path, $fileMask = '*', $dirMask = '*', $flags = self::SCAN_BOTH, $level = null){
        $res = array();
        $path = rtrim($path, '/');
        $fMask =
            '/^' . str_replace(
                array('\\*', '\\?'),
                array('.*', '.'),
                quotemeta($fileMask)
            ) .
            '$/' . ($flags & self::SCAN_CI ? 'i' : '');
        $dMask =
            '/^' . str_replace(
                array('\\*', '\\?'),
                array('.*', '.'),
                quotemeta($dirMask)
            ) .
            '$/' . ($flags & self::SCAN_CI ? 'i' : '');
        if($dh = @opendir($path)){
            while(false !== ($file = readdir($dh))){
                if($file != '.' && $file != '..'){
                    $newPath = $path . '/' . $file;
                    if(is_dir($newPath)){
                        if(($flags & self::SCAN_DIRS) && (is_null($level) || $level > 0) && preg_match($dMask, $file)){
                            if(($flags & self::SCAN_BOTH) !== self::SCAN_BOTH){
                                $res[] = $newPath;
                            }
                            $res =
                                array_merge(
                                    $res,
                                    self::scan($newPath, $fileMask, $dirMask, $flags, is_null($level) ? null : $level - 1)
                                );
                        }
                    }elseif($flags & self::SCAN_FILES && preg_match($fMask, $file)){
                        $res[] = $newPath;
                    }
                }
            }
            closedir($dh);
        }
        return $res;
    }

    /**
     * Prepares file name to store in FS.
     *
     * @param  string $name  File name
     * @return string
     */
    public static function prepareName($name){
        $name = AMI_Lib_String::transliterate($name, AMI_Registry::get('lang_data'));
        while(mb_strpos($name, '..') !== FALSE){
            $name = str_replace('..', '.', $name);
        }
        $name = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $name);
        return $name;
    }

    /**
     * Check path for safety.
     *
     * @param  string $path      File path
     * @param  bool   $fromRoot  Flag specifying to validate from site root path
     * @return string
     */
    public static function validatePath($path, $fromRoot = TRUE){
        $testPath = $path;
        $testPath = str_replace(array('\\', '//'), '/', $testPath);
        $res = TRUE;
        if(preg_match('~(^\.\./|/\.\./|/\.\.$|^\.\.$)~', $testPath)){
            trigger_error("Path '" . $path . "' is invalid", E_USER_WARNING);
            $res = FALSE;
        }
        if($fromRoot && mb_strpos($testPath, $GLOBALS['ROOT_PATH']) !== 0){
            trigger_error("Root path restriction, '" . $path . "' is invalid", E_USER_WARNING);
            $res = FALSE;
        }
        return $res;
    }

    /**
     * Copy file and generate message for failure, path can be empty.
     *
     * @param  string $origFileName  Original file name
     * @param  string $newFileName   New file name
     * @param  string $path          File path
     * @param  int    $chmod         Chmod
     * @return bool
     */
    public static function copyFile($origFileName, $newFileName, $path = "", $chmod = 0666){
        $res = false;
        if(!empty($origFileName)){
            if(@copy($path . $origFileName, $path . $newFileName)){
                @chmod($path . $newFileName, $chmod);
                $res = TRUE;
            }else{
                trigger_error(
                    "Unable to copy file '" . $path . $origFileName  .
                    "' to '" . $path . $newFileName . "'",
                    E_USER_WARNING
                );
            }
        }
        return $res;
    }

    /**
     * Delete file and generate message for failure, path can be empty.
     *
     * @param  string $fileName  File name
     * @param  string $path      File path
     * @return bool
     */
    public static function deleteFile($fileName, $path = ''){
        $res = false;
        if(!empty($fileName)){
            if(@unlink($path . $fileName)){
                $res = TRUE;
            }else{
                trigger_error(
                    "Unable to delete file '" . $path . $fileName . "'",
                    E_USER_WARNING
                );
            }
        }
        return $res;
    }

    /**
     * Calculating directory size.
     *
     * @param  string $directory  Directory path.
     * @param  bool   $recursive  Recursive or not
     * @return int
     */
    public static function getDirectorySize($directory, $recursive = FALSE){
        $size = 0;
        if(substr($directory, -1) == '/'){
            $directory = substr($directory, 0, -1);
        }
        if(!file_exists($directory) || !is_dir($directory) || !is_readable($directory)){
            return -1;
        }
        $handle = opendir($directory);
        if($handle){
            while(($file = readdir($handle)) !== false){
                $path = $directory.'/'.$file;
                if($file != '.' && $file != '..'){
                    if(is_file($path)){
                        $size += filesize($path);
                    }elseif(is_dir($path) && $recursive){
                        $handlesize = self::getDirectorySize($path, $recursive);
                        if($handlesize >= 0){
                            $size += $handlesize;
                        }else{
                            return -1;
                        }
                    }
                }
            }
            closedir($handle);
        }
        return $size;
    }

    /**
     * Saves file.
     *
     * @param  string $path     Path
     * @param  string $content  Content
     * @param  int    $mode     Mode
     * @param  bool   $append   Append to existing file
     * @return bool
     */
    public static function saveFile($path, $content, $mode = 0666, $append = false){
        $res = FALSE;
        $flags = $append ? FILE_APPEND : 0;
        if(self::validatePath($path)){
            $res = file_put_contents($path, $content, $flags);
            if($res){
                @chmod($path, $mode);
            }
        }
        return $res;
    }

    /**
     * Deleletes folders/files recursively.
     *
     * @param  string $path      Path
     * @param  bool   $withPath  Flag specfying to delete specified path too
     * @return bool
     */
    public static function deleteRecursive($path, $withPath = FALSE){
        $res = FALSE;
        if(self::validatePath($path)){
            $res = TRUE;
            $aDirs = self::scan($path, '*', '*', self::SCAN_DIRS, 1);
            foreach($aDirs as $dir){
                $res = self::deleteRecursive($dir, TRUE);
                if(!$res){
                    break;
                }
            }
            if($res){
                $aFiles = self::scan($path, '*', '*', self::SCAN_FILES, 1);
                foreach($aFiles as $file){
                    $res = unlink($file);
                    if(!$res){
                        break;
                    }
                }
                if($res && $withPath && file_exists($path)){
                    $res = rmdir($path);
                }
            }
        }
        return $res;
    }
}
