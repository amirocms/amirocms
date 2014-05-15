<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModTableItemException.php 49332 2014-04-02 10:36:10Z Leontiev Anton $
 * @since     5.12.0
 */

/**
 * Module table item model exception.
 *
 * Can be thrown when
 * - item not found during loading;
 * - validation failed during saving.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @see        AMI_ModTableItem::validate()
 * @see        AMI_ModTableItem::load()
 * @see        AMI_ModTableItem::loadByFields()
 * @since      5.12.0
 */
class AMI_ModTableItemException extends Exception{
    /**
     * Item not found code
     */
    const NOT_FOUND = 0x01;

    /**
     * Validation failed code
     */
    const VALIDATION_FAILED = 0x02;

    /**
     * Action discarded code
     *
     * @since 6.0.6
     */
    const ACTION_DISCARDED = 0x03;

    /**
     * Exception extra data
     *
     * @var mixed
     */
    protected $data;

    /**
     * Constructor.
     *
     * @param string $message  The exception message
     * @param int    $code     The exception code
     * @param mixed  $data     The exception extra data
     */
    public function __construct($message = '', $code = 0, $data = null){
        $this->message = $message;
        $this->code = $code;
        $this->data = $data;
    }

    /**
     * Returns exceprion data.
     *
     * @return mixed
     */
    public function getData(){
        return $this->data;
    }
}
