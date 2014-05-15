<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Library
 * @version   $Id: AMI_Lib_Image.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Image library.
 *
 * @package Library
 * @static
 * @since   x.x.x
 * @amidev  Temporary
 */
class AMI_Lib_Image{
    /**
     * Image directories files
     *
     * @var array
     */
    protected static $aImgDirFilesList = array();

    /**
     * Image service shutdown function registration flag
     *
     * @var bool
     */
    protected static $imgDirFilesIndexFuncRegistered = FALSE;

    /**
     * Saves image index file.
     *
     * @param  string $dirPath    Path to the directory
     * @param  string $indexFile  Index file name
     * @param  int    $modTime    Filesystem mtime
     * @return void
     */
    function saveImagesIndexFile($dirPath, $indexFile, $modTime){
        // Save index file to disk
        $fp = @fopen($indexFile, 'w');
        if($fp){
            fwrite($fp, $modTime);
            fwrite($fp, serialize(self::$aImgDirFilesList[$dirPath]));
            fclose($fp);
            @chmod($indexFile, 0666);
        }
    }

    /**
     * Returns image attributes.
     *
     * @param  string $dirPath   Path to the directory
     * @param  mixed $imageFile  Image file name
     * @return array
     */
    function getDirOnlyOneImageParameters($dirPath, $imageFile){
        $indexFile = '_index_img_file.dat_';
        $dirPath = rtrim($dirPath, "/\\");
        $indexFile = $dirPath . '/' . $indexFile;
        $fs = @stat($dirPath);

        if(!isset(self::$aImgDirFilesList[$dirPath])){
            self::$aImgDirFilesList[$dirPath] = array();
            if(file_exists($indexFile)){
                $fp = @fopen($indexFile, 'r');
                if($fp){
                    $lastTime = fread($fp, mb_strlen($fs['mtime']));
                    if($lastTime == $fs['mtime']){
                        $len = filesize($indexFile) - mb_strlen($fs['mtime']);
                        if($len > 0){
                            // Load from index file
                            self::$aImgDirFilesList[$dirPath] = unserialize(fread($fp, $len));
                        }
                    }
                }
                @fclose($fp);
            }
        }

        if(!isset(self::$aImgDirFilesList[$dirPath]) || !isset(self::$aImgDirFilesList[$dirPath][$imageFile])){
            // Get file info from disk
            $aImgParams = @getImageSize($dirPath . '/' . $imageFile);
            self::$aImgDirFilesList[$dirPath][$imageFile] = array($aImgParams[0], $aImgParams[1], $aImgParams[2]);

            if(!self::$imgDirFilesIndexFuncRegistered){
                register_shutdown_function(array('AMI_Lib_Image', 'saveImagesIndexFile'), $dirPath, $indexFile, $fs['mtime']);
                self::$imgDirFilesIndexFuncRegistered = true;
            }
        }

        return self::$aImgDirFilesList[$dirPath][$imageFile];
    }

    /**
     * Returns image size using particular error handling.
     *
     * @param  string $path  Image path
     * @return mixed  Result of php:getImageSize() function
     * @see    getImageSize()
     */
    function imageGetSize($path){
        $imgDir = dirname($path);
        $imgFile = basename($path);
        $res = null;

        $res = self::getDirOnlyOneImageParameters($imgDir, $imgFile);
        if(!$res){
            // shit happens
            $res = @getImageSize($path);
        }

        if(is_array($res) || !is_object($cms) || mb_substr($path, -18 == '/_index_file.dat_') || mb_substr($path, -12) == '.placeholder'){
            return $res;
        }
        /*
        static $email, $gui;
        if(!isset($email)){
            $email = $cms->Core->GetOption('errors_notification_email');
        }
        if($email){
            if(!$gui){
                $gui = new GUI_template();
            }
            $gui->addBlock('_error_notification', $GLOBALS["LOCAL_FILES_REL_PATH"] . '_admin/templates/letters/_error_notification.tpl');
            $data = array(
                'title'      => $GLOBALS['ROOT_PATH_WWW'],
                'url'        => $GLOBALS['ROOT_PATH_WWW'] . mb_substr(AMI::getSingleton('env/request')->getURL('uri'), 1),
                'image_path' => $path
            );
            $subject = $gui->parse('_error_notification:subject', $data);
            $body = $gui->parse('_error_notification', $data);
            @mail($email, $subject, $body);
        }
        */
        return $res;
    }
}
