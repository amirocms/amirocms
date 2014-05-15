<?php
/**
 * Front async hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiAsync
 * @version   $Id: Hyper_AmiAsync_Frn.php 45856 2013-12-24 09:48:43Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Front async hypermodule front action controller.
 *
 * @package    Hyper_AmiAsync
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiAsync_Frn extends AMI_Module_Frn{
    /**
     * Constructor.
     *
     * @param AMI_Request $oRequest    Request object
     * @param AMI_Response $oResponse  Response object
     */
    public function  __construct(AMI_Request $oRequest, AMI_Response $oResponse){

        parent::__construct($oRequest, $oResponse);

        if(AMI_Registry::get('ami_request_type', 'plain') === 'ajax'){
            $oResponse->loadStatusMessages(AMI_iTemplate::LNG_MOD_PATH . '/' . $this->getModId() . '_messages.lng');
            $this->addComponents(array('filter', 'list', 'form'));
        }else{
            $oTpl = AMI_Registry::get('oGUI');
            $oTpl->addMeta('http-equiv', 'X-UA-Compatible', 'IE=100');
            // Common scripts
            AMI_Registry::set('AMI/resources/j/e', '');
            // Common scripts
            AMI_Registry::set('AMI/resources/j/s', $this->getModId());
            $this->addComponents(array('async'));
        }
        $this->initComponents();
    }

    /**
     * Dispathes default action.
     *
     * @return void
     */
    protected function dispatchDefaultAction(){
    }
}

/**
 * Front async hypermodule front component.
 *
 * @package     Hyper_AmiAsync
 * @subpackage  Controller
 * @since       x.x.x
 * @amidev      Temporary
 */
abstract class Hyper_AmiAsync_ComponentFrn extends AMI_CustomComponent{
}

/**
 * Front async hypermodule from component view.
 *
 * @package     Hyper_AmiAsync
 * @subpackage  View
 * @since       x.x.x
 * @amidev      Temporary
 */
abstract class Hyper_AmiAsync_ComponentViewFrn extends AMI_CustomComponentView{
}

/**
 * Front async hypermodule front list component actions controller.
 *
 * @package    Hyper_AmiAsync
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiAsync_ListFrn extends AMI_Module_ListFrn{
}

/**
 * Front async hypermodule front components list actions.
 *
 * @package    Hyper_AmiAsync
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiAsync_ListActionsFrn extends AMI_Module_ListActionsFrn{
}

/**
 * Front async hypermodule front components list group actions.
 *
 * @package    Hyper_AmiAsync
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiAsync_ListGroupActionsFrn extends AMI_Module_ListGroupActionsFrn{
}

/**
 * Front async hypermodule front list component view.
 *
 * @package    Hyper_AmiAsync
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiAsync_ListViewFrn extends AMI_Module_ListViewFrn{
}

/**
 * Front async hypermodule fronr filter component action controller.
 *
 * @package    Hyper_AmiAsync
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiAsync_FilterFrn extends AMI_Module_FilterFrn{
}

/**
 * Front async hypermodule item list component filter model.
 *
 * @package    Hyper_AmiAsync
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiAsync_FilterModelFrn extends AMI_Module_FilterModelFrn{
}

/**
 * Front async hypermodule front filter component view.
 *
 * @package    Hyper_AmiAsync
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiAsync_FilterViewFrn extends AMI_Module_FilterViewFrn{
}

/**
 * Front async hypermodule fronr form component action controller.
 *
 * @package    Hyper_AmiAsync
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiAsync_FormFrn extends AMI_Module_FormFrn{
}

/**
 * Front async hypermodule item list component form model.
 *
 * @package    Hyper_AmiAsync
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiAsync_FormModelFrn extends AMI_Module_FormFrn{
}

/**
 * Front async hypermodule front form component view.
 *
 * @package    Hyper_AmiAsync
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiAsync_FormViewFrn extends AMI_Module_FormViewFrn{
}

/**
 * Front async hypermodule front async component.
 *
 * @package     Hyper_AmiAsync
 * @subpackage  Controller
 * @since       x.x.x
 * @amidev      Temporary
 */
abstract class Hyper_AmiAsync_AsyncFrn extends AMI_Module_AsyncFrn{
}

/**
 * Front async hypermodule front async view.
 *
 * @package     Hyper_AmiAsync
 * @subpackage  View
 * @since       x.x.x
 * @amidev      Temporary
 */
abstract class Hyper_AmiAsync_AsyncViewFrn extends AMI_Module_AsyncViewFrn{
    /**
     * Template file name
     *
     * @var string
     */
    protected $tplFileName = null;
}
