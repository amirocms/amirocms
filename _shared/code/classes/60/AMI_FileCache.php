<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Environment
 * @version   $Id: AMI_FileCache.php 46677 2014-01-17 06:09:59Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Simple file cache.
 *
 * @package Environment
 * @since   x.x.x
 * @amidev  Temporary
 */
class AMI_FileCache{
    /**
     * Global expiration time, in seconds
     *
     * @var int
     */
    const EXP_TIME = 2592000; // 1 month

    /**
     * Maximum of expired files
     *
     * @var int
     */
    const MAX_EXP_FILES = 100;

    /**
     * URI to build hash
     *
     * @var string
     */
    protected $uri;

    /**
     * Storage path
     *
     * @var string
     */
    protected $path;

    /**
     * Cached file name
     *
     * @var string
     */
    protected $hash;

    /**
     * Cached content
     *
     * @var string
     */
    protected $content;

    /**
     * Constructor.
     */
    public function __construct(){
        $this->path = $GLOBALS['ROOT_PATH'] . '_mod_files/_cache/';
    }

    /**
     * Initialization.
     *
     * @param  string $uri  URI to build hash
     * @return void
     */
    public function init($uri = ''){
        $uri = (string)$uri;
        if($uri === ''){
            $uri =
                isset($_SERVER['REQUEST_URI'])
                ? preg_replace('/^\//', '', $_SERVER['REQUEST_URI'])
                : preg_replace('/^.*?https?:\/\/.+?\//', '', $_SERVER['QUERY_STRING']);

        }
        $this->uri = $uri;
        $this->buildHash();
    }

    /**
     * Returns TRUE if cache file doesn't exist or expired.
     *
     * @return bool
     */
    public function isExpired(){
        $cachedFile = $this->path . $this->hash . '.tmp';
        if(file_exists($cachedFile) && (filemtime($cachedFile) + self::EXP_TIME) > time()){
            $this->content = file_get_contents($cachedFile);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Stores content.
     *
     * @param  string $content   Content to store
     * @param  bool $addComment  Add comment with creation time to the beginning of file
     * @return bool  TRUE on success or FALSE otherwise
     */
    public function store($content, $addComment = TRUE){
        if($addComment){
            $content =
                "/*\r\n" .
                " * Date created: " . date('Y-m-d H:i:s') . "\r\n" .
                " */\r\n" .
                $content;
        }
        $this->content = $content;
        $cachedFile = $this->path . $this->hash . '.tmp';
        $uniqueFile = $this->path . $this->hash . '.' . uniqid() . '.tmp';
        AMI_Registry::push('disable_error_mail', TRUE);
        $res =
            AMI_Lib_FS::saveFile($uniqueFile, $content) &&
            rename($uniqueFile, $cachedFile);
        if(!$res){
            // remove broken file in case of insufficient space
            @unlink($uniqueFile);
        }
        AMI_Registry::pop('disable_error_mail');
        return $res;
    }

    /**
     * Returns cached content.
     *
     * @return stribg
     */
    public function get(){
        if($this->checkProbability(0.001)){
            $this->cleanupExpiredFiles();
        }
        return $this->content;
    }

    /**
     * Resets all cache.
     *
     * @param  string $mask  Mask
     * @return void
     */
    public function reset($mask = '*'){
        $mask =
            '/^' . str_replace(
                array('\\*', '\\?'),
                array('.*', '.'),
                quotemeta($mask)
            ) .
            '$/i';
        foreach(new DirectoryIterator($this->path) as $oFile){
            $file = $oFile->getFilename();
            if(
                !$oFile->isDot() &&
                $file != '.htaccess' &&
                preg_match($mask, $file)
            ){
                unlink($this->path . $file);
            }
        }
    }

    /**
     * Builds hash.
     *
     * @return void
     */
    protected function buildHash(){
        $hash = str_replace(array('.', '-'), '_', $this->uri);
        $hash = preg_replace(
            array('/[^a-zA-Z0-9_]/', '/__/'),
            array('_', '_'),
            $hash
        );
        $this->hash = mb_substr($hash, 0, 128, 'ASCII');
    }

    /**
     * Deletes expired cache files.
     *
     * @return void
     */
    protected function cleanupExpiredFiles(){
        $count = 0;
        foreach(new DirectoryIterator($this->path) as $oFile){
            $file = $oFile->getFilename();
            $path = $this->path . $file;
            if(
                !$oFile->isDot() &&
                ($file != '.htaccess') &&
                !preg_match('/\.[a-z\d]+\.tmp$/', $file) &&
                ((filemtime($path) + self::EXP_TIME) <= time())
            ){
                unlink($path);
                $count++;
            }
        }
        $max = self::MAX_EXP_FILES * 100;
        if($count > $max){
            trigger_error('Expired files count ' . $count . ' exceeds maximum ' . $max, E_USER_WARNING);
        }
    }

    /**
     * Checks probability.
     *
     * @param  float $probability  Probability
     * @return bool
     */
    protected function checkProbability($probability){
        return $probability != 1 ? mt_rand(0, mt_getrandmax()) < $probability * mt_getrandmax() : TRUE;
    }
}
