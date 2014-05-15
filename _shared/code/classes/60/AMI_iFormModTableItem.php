<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_iFormModTableItem.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.12.0
 */

/**
 * Item model interface used in form view.
 *
 * Use AMI_ModTableItem/AMI_Filter children, interface usage will be described later.
 *
 * @package    ModuleComponent
 * @subpackage View
 * @since      5.12.0
 * @todo       Describe usage
 * @see        AmiClean_AmiSample_FilterViewAdm
 * @see        AmiClean_AmiSample_TableItem
 */
interface AMI_iFormModTableItem{
    /**
     * Returns validators.
     *
     * Returns array of validators in format 'aField' => array('validator1', 'validator2', ...).
     *
     * @return array
     * @see    AMI_ModFormView::get()
     */
    public function getValidators();

    /**
     * Returns field value from data array.
     *
     * @param  string $name  Field name
     * @return mixed  Field value or null if value is not found
     * @see    AMI_ModFormView::get()
     */
    public function getValue($name);

    /**
     * Returns validator exception object after save or null.
     *
     * @return AMI_ModTableItemException|null
     */
    public function getValidatorException();

    /**
     * Returns item id.
     *
     * @return mixed
     */
    public function getId();
}
