<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Service
 * @version   $Id$
 * @since     6.0.2
 */

/**
 * Extended exception.
 *
 * @package Service
 * @since   6.0.2
 */
class AMI_Exception extends Exception{
    /**
     * Data
     *
     * @var mixed
     */
    protected $data;

    /**
     * Construct the exception.
     *
     * @param string    $message    The Exception message to throw
     * @param int       $code       The Exception code
     * @param Exception $oPrevious  The previous exception used for the exception chaining
     * @param mixed     $data       Exception data
     */
    public function __construct($message = '', $code = 0, Exception $oPrevious = NULL, $data = NULL){
        if(version_compare(PHP_VERSION, '5.3.0', '>=')){
            parent::__construct($message, $code, $oPrevious);
        }else{
            parent::__construct($message, $code);
        }
        $this->data = $data;
    }

    /**
     * Returns exception data.
     *
     * @return mixed
     */
    public function getData(){
        return $this->data;
    }
}
