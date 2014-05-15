<?php
/**
 * File lock implementation.
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Service
 * @version   $Id: AMI_Lock.php 43588 2013-11-13 09:56:24Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * File lock implementation.
 *
 * Allows to create/check/release file locks.
 *
 * Example:
 * <code>
 * $oLock = new AMI_Lock;
 * // You can view all warnings/errors in debug output
 * $oLock->setAttr('warn', FALSE); // TRUE by default
 * // Create lock
 * $res = $oLock->create(
 *     AMI_Registry::get('path/root') . 'path/to/lock',
 *     uniqid('', TRUE),
 *     60
 * );
 * if(!$res){
 *     trigger_error('Cannot create lock', E_USER_ERROR);
 * }
 * do{
 *     // Do something
 *     $res = $oLock->check();
 *     if(!$res){
 *         $aError = $oLock->getError();
 *         trigger_error($aError['message'], E_USER_ERROR);
 *     }
 * }while(...);
 * // Lock will be relased on object destruction
 * unset($oLock);
 * </code>
 *
 * @package  Service
 * @since    x.x.x
 * @amidev   Temporary
 */
class AMI_Lock{
    const ERR_ALREADY_CREATED  =  1;
    const ERR_INVALID_PATH     =  2;
    const ERR_CANNOT_CREATE    =  3;
    const ERR_LOCKED           =  4;
    const ERR_MISSING_CREATE   =  5;
    const ERR_OTHER_ID         =  6;
    const ERR_CANNOT_TOUCH     =  7;
    const ERR_DESTROYED        =  8;
    const ERR_CANNOT_RELEASE   =  9;

    const WARN_PREV_EXPIRED    = 20;

    /**
     * Default attributes
     *
     * @var array
     */
    protected $aAttrs = array(
        'warn'             => TRUE,
        'releaseOnDestroy' => TRUE,
        'pathFromRoot'     => TRUE
    );

    /**
     * Contains error info
     *
     * @var array
     */
    protected $aError;

    /**
     * Path to lock file
     *
     * @var string
     */
    protected $path;

    /**
     * Time to live, in secnds
     *
     * @var int
     */
    protected $ttl;

    /**
     * Lock created flag
     *
     * @var bool
     */
    protected $created = FALSE;

    /**
     * Lock id
     *
     * @var string
     */
    protected $id;

    /**
     * Constructor.
     */
    public function __construct(){
        register_shutdown_function(array($this, 'onShutdown'));
    }

    /**
     * Script shotdown handler.
     *
     * @return void
     */
    public function onShutdown(){
        if($this->created){
            $this->release();
        }
    }

    /**
     * Sets attribute.
     *
     * @param  string $name   Attribute name
     * @param  mixed  $value  Attribute value
     * @return mixed  Previous attribute value or null
     */
    public function setAttr($name, $value){
        if(isset($this->aAttrs[$name])){
            $prev = $this->aAttrs[$name];
            $this->aAttrs[$name] = $value;
            return $prev;
        }
        trigger_error("Unknown attribute name '" . $name . "'", E_USER_WARNING);
        return null;
    }

    /**
     * Sets attribute.
     *
     * @param  string $name  Attribute name
     * @return mixed
     */
    public function getAttr($name){
        if(isset($this->aAttrs[$name])){
            return $this->aAttrs[$name];
        }
        trigger_error("Unknown attribute name '" . $name . "'", E_USER_WARNING);
        return null;
    }

    /**
     * Creates the lock.
     *
     * @param  string $path  Path to lock file
     * @param  string $id    Unique lock id
     * @param  int    $ttl   Time to live in seconds
     * @return bool
     */
    public function create($path, $id, $ttl){
        if($this->created){
            $this->error(self::ERR_ALREADY_CREATED, 'File lock already created, use AMI_Lock::release() first');
            return FALSE;
        }

        $this->path = (string)$path;
        $this->id  = (string)$id;
        $this->ttl  = (int)$ttl;

        $this->resetError();
        if(!AMI_Lib_FS::validatePath($this->path, $this->getAttr('pathFromRoot'))){
            $this->error(
                self::ERR_INVALID_PATH,
                "Invalid path '" . $this->path . "'"
            );
            return FALSE;
        }

        // Try to create the lock
        if(file_exists($this->path)){
            $id = file_get_contents($this->path);
            $fileTime = filemtime($this->path);
            if($fileTime < (time() - $ttl)){
                if($this->id === $id){
                    $this->error(
                        self::ERR_CANNOT_CREATE,
                        "Cannot rewrite lock at '" . $this->path . "' having same id '" . $id .
                        "' created at " . date('Y-m-d H:i:s', $fileTime)
                    );
                    return FALSE;
                }
                if(file_put_contents($this->path, $this->id)){
                    $this->error(
                        self::WARN_PREV_EXPIRED,
                        "Previous lock at '" . $this->path . "' having id '" . $id .
                        "' created at " . date('Y-m-d H:i:s', $fileTime) . " is expired"
                    );
                    $this->created = TRUE;
                    return TRUE;
                }else{
                    $this->error(
                        self::ERR_CANNOT_CREATE,
                        "Cannot rewrite lock at '" . $this->path . "' having id '" . $id .
                        "' created at " . date('Y-m-d H:i:s', $fileTime)
                    );
                    return FALSE;
                }
            }else{
                $this->error(
                    self::ERR_LOCKED,
                    "Locked at '" . $this->path . "' by id '" . $id .
                    "' created at " . date('Y-m-d H:i:s', $fileTime)
                );
                return FALSE;
            }
        }
        if(file_put_contents($this->path, $this->id)){
            @chmod($this->path, 0666);
            $this->created = TRUE;
            return TRUE;
        }
        $this->error(
            self::ERR_CANNOT_CREATE,
            "Cannot create lock at '" . $this->path . "' having id '" . $id . "'"
        );
        return FALSE;
    }

    /**
     * Check the lock.
     *
     * @return bool
     */
    public function check(){
        if(!$this->created){
            $this->error(
                self::ERR_MISSING_CREATE,
                "Call AMI_Lock::create() first"
            );
            return FALSE;
        }
        if(file_exists($this->path)){
            $id = file_get_contents($this->path);
            if($id !== $this->id){
                $this->error(
                    self::ERR_OTHER_ID,
                    "Lock file at '" . $this->path . "' have other id '" . $id . "'"
                );
                return FALSE;
            }
            // Everything is ok, touch lock file
            if(touch($this->path)){
                $this->resetError();
                return TRUE;
            }else{
                $this->error(
                    self::ERR_CANNOT_TOUCH,
                    "Cannot touch lock file at '" . $this->path . "'"
                );
                return FALSE;
            }
        }
        $this->error(
            self::ERR_DESTROYED,
            "Lock file at '" . $this->path . "' is destroyed"
        );
        return FALSE;
    }

    /**
     * Relaese the lock.
     *
     * @return bool
     */
    public function release(){
        if(!$this->created){
            $this->error(
                self::ERR_MISSING_CREATE,
                "Call AMI_Lock::create() first"
            );
            return FALSE;
        }
        if(file_exists($this->path)){
            $id = file_get_contents($this->path);
            if($id !== $this->id){
                $this->error(
                    self::ERR_OTHER_ID,
                    "Lock file at '" . $this->path . "' have other id '" . $id . "'"
                );
                return FALSE;
            }
            // Everything is ok, touch lock file
            if(unlink($this->path)){
                $this->resetError();
                $this->created = FALSE;
                return TRUE;
            }else{
                $this->error(
                    self::ERR_CANNOT_RELEASE,
                    "Cannot release lock file at '" . $this->path . "'"
                );
                return FALSE;
            }
        }
        $this->error(
            self::ERR_DESTROYED,
            "Lock file at '" . $this->path . "' is destroyed"
        );
        return FALSE;
    }

    /**
     * Returns error info.
     *
     * @return array
     */
    public function getError(){
        return $this->aError;
    }

    /**
     * Destructor.
     */
    public function __destruct(){
        if($this->created && $this->getAttr('releaseOnDestroy')){
            $warn = $this->setAttr('warn', TRUE);
            $this->release();
            $this->setAttr('warn', $warn);
        }
    }

    /**
     * Resets error.
     *
     * @return void
     */
    protected function resetError(){
        $this->aError = array();
    }

    /**
     * Sets error.
     *
     * @param  int    $code     Error code
     * @param  string $message  Error message
     * @return void
     */
    protected function error($code, $message){
        $this->aError = array(
            'code'    => (int)$code,
            'message' => (string)$message
        );
        if($this->getAttr('warn')){
            AMI_Registry::push('disable_error_mail', TRUE);
            trigger_error('AMI_Lock [ ' . $this->aError['code'] . ' ] ' . $this->aError['message'], E_USER_WARNING);
            AMI_Registry::pop('disable_error_mail');
        }
    }
}
