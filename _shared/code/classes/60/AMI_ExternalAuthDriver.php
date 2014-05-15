<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Service
 * @version   $Id: AMI_ExternalAuthDriver.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.12.4
 */

/**
 * External auth driver abstract class.
 *
 * See {@link VBulletin_ExternalAuthDriver} for usage example.
 *
 * @package Service
 * @since   5.12.4
 */
abstract class AMI_ExternalAuthDriver{
    /**
     * Error 1001: wrong user object (invalid object type or null)
     *
     * @var int
     */
    const ERR_WRONG_OBJECT = 1001;

    /**
     * Error 1001: message
     *
     * @var string
     */
    const MSG_WRONG_OBJECT = 'Wrong user object';

    /**
     * Instance
     *
     * @var AMI_ExternalAuthDriver
     */
    protected static $oInstance = null;

    /**
     * Settings array
     *
     * @var array
     */
    protected $aSettings = array();

    /**
     * Initialize settings.
     *
     * @param array $aSettings  Settings
     * @return void
     */
    public function init(array $aSettings = array()){
        $this->aSettings = array_merge($this->aSettings, $aSettings);
    }

    /**
     * Object could be created, through getInstance only.
     */
    protected function __construct(){
    }

    /**
     * Object could be cloned.
     */
    protected function __clone(){
    }
}
