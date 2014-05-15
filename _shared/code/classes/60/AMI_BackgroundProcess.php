<?php
/**
 * Class AMI_BackgroundProcess.
 *
 * Allows to manage "background" processes.
 *
 * Example:
 * <code>
 * $AMI_ENV_SETTINGS = array('mode' => 'full', 'disable_cache' => true);
 * require 'ami_env.php';
 *
 * $oResponse = AMI::getSingleton('response');
 * $oResponse->start();
 *
 * // Create AMI_BackgroundProcess object
 * $oBackgroundProcess = new AMI_BackgroundProcess();
 *
 * // Add background process into the queue
 * // The method "process" of class "User_BackgroundProcess" will be performed and should exist
 * $oBackgroundProcess->registerHandler("User_BackgroundProcess::process");
 *
 * $oResponse->write('The background process was added into the queue.');
 * $oResponse->send();
 * </code>
 *
 * @package    Service
 * @since      6.0.6
 */
class AMI_BackgroundProcess{
    /**
     * Instance
     *
     * @var AMI_BackgroundProcess
     */
    protected static $oInstance;

    /**
     * CMS background process object
     *
     * @var CMS_BackgroundProcess
     * @amidev
     */
    protected $oBackgroundProcess;

    /**
     * Returns an instance of AMI_BackgroundProcess.
     *
     * @param  array $aArgs  Constructor arguments
     * @return AMI_BackgroundProcess
     * @amidev
     */
    public static function getInstance(array $aArgs = array()){
        if(is_null(self::$oInstance)){
            self::$oInstance = new AMI_BackgroundProcess();
        }
        return self::$oInstance;
    }

    /**
     * Constructor.
     */
    public function __construct(){
        require_once $GLOBALS['CLASSES_PATH'] . 'CMS_BackgroundProcess.php';
        $this->oBackgroundProcess = new CMS_BackgroundProcess();
    }

    /**
     * Register background handler.
     *
     * @param string $handler  Handler name like "className::methodName".
     * @return void
     */
    public function registerHandler($handler){
        $this->oBackgroundProcess->registerHandler($handler, '');
    }

    /**
     * Unregister background handler.
     *
     * @param string $handler  Handler name like "className::methodName".
     * @return void
     */
    public function unregisterHandler($handler){
        $this->oBackgroundProcess->unregisterHandler($handler);
    }
}
