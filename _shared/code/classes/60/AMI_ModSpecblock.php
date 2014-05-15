<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModSpecblock.php 44766 2013-11-30 08:41:31Z Kolesnikov Artem $
 * @since     5.14.8
 */

/**
 * Module specblock component controller.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.14.8
 */
abstract class AMI_ModSpecblock extends AMI_ModComponent{
    /**
     * Initialization.
     *
     * @return AMI_iModComponent
     * @see    AMI_Mod::init()
     */
    public function init(){
        $this->displayView();
        return $this;
    }

    /**
     * Returns component type.
     *
     * @return string
     */
    public function getType(){
        return 'specblock';
    }

    /**
     * Returns component view.
     *
     * @return AMI_View
     */
    public function getView(){
        $type = $this->getType();
        if($this->getPostfix() != ''){
            $type .= ('_' . $this->getPostfix());
        }
        return $this->_getView('/' . $type . '/view/frn');
    }
}
