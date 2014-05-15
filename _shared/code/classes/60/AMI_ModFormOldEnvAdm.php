<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModForm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 */

/**
 * Module form component action controller.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @amidev
 * @since      x.x.x
 */
abstract class AMI_ModFormOldEnvAdm extends AMI_ModFormAdm{
    /**
     * 
     * @return boolean
     */
    public function isFullEnv(){
        return TRUE;
    }
}

/**
 * Old environment form view.
 *
 * @package    ModuleComponent
 * @subpackage View
 * @amidev
 * @since      x.x.x
 */
class AMI_ModFormOldEnvViewAdm extends AMI_ModFormViewAdm{

    /**
     * Old env module id.
     *
     * @var string
     */
    protected $formModId;

    /**
     * Returns view data.
     *
     * @return null
     */
    public function get(){
        if($this->formModId){
            return  AMI_OldEnv::getAdmForm($this->formModId);
        }else{
            return parent::get();
        }
    }
}