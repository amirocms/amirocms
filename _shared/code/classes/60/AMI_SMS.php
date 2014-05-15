<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   DriverComponent
 * @version   $Id: AMI_SMS.php 46895 2014-01-22 12:52:42Z Medvedev Konstantin $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * SMS Driver interface.
 *
 * @package    Eshop_SMS_Notification
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
interface AMI_SMS{
    /**
     * Send SMS.
     *
     * @return bool
     */
    public function send();

    /**
     * Sets error message.
     *
     * @param  string $errorMEssage
     * @return string
     */
    public function setError($errorMessage);

    /**
     * Sets error message.
     *
     * @param  string $errorMEssage
     * @return string
     */
    public function setMessage($message);

    /**
     * Returns params fields.
     *
     * @return array
     */
    public function getParamsFields();

    /**
     * Returns params fields.
     *
     * @return array
     */
    public function getSMSGateParams();

    /**
     * Retrurns error message.
     *
     * @return string
     */
    public function getError();

    /**
     * Retrurns  message.
     *
     * @return string
     */
    public function getMessage();

    /**
     * Returns true if params are valid.
     *
     * @return string
     */
    public function isValid();

}