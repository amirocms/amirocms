<?php
/**
 * AmiMultifeeds hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiMultifeeds
 * @version   $Id: Hyper_AmiMultifeeds_Adm.php 45064 2013-12-05 09:27:10Z Leontiev Anton $
 * @since     6.0.2
 */

/**
 * Multifeeds hypermodule admin action controller.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
abstract class Hyper_AmiMultifeeds_Adm extends AMI_Module_Adm{
}

/**
 * Module model.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Model
 * @since      6.0.2
 */
abstract class Hyper_AmiMultifeeds_State extends AMI_ModState{
}

/**
 * Multifeeds hypermodule admin filter component action controller.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
abstract class Hyper_AmiMultifeeds_FilterAdm extends AMI_Module_FilterAdm{
}

/**
 * Multifeeds hypermodule item list component filter model.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Model
 * @since      6.0.2
 */
abstract class Hyper_AmiMultifeeds_FilterModelAdm extends AMI_Module_FilterModelAdm{
}

/**
 * Multifeeds hypermodule admin filter component view.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage View
 * @since      6.0.2
 */
abstract class Hyper_AmiMultifeeds_FilterViewAdm extends AMI_Module_FilterViewAdm{
}

/**
 * Multifeeds hypermodule admin form component action controller.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
abstract class Hyper_AmiMultifeeds_FormAdm extends AMI_Module_FormAdm{
    /**
     * Returns true if component needs to be started always in full environment.
     *
     * @return bool
     */
    public function isFullEnv(){
        return FALSE;
    }
}

/**
 * Multifeeds hypermodule admin form component view.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage View
 * @since      6.0.2
 */
abstract class Hyper_AmiMultifeeds_FormViewAdm extends AMI_Module_FormViewAdm{
}

/**
 * Multifeeds hypermodule admin list component action controller.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
abstract class Hyper_AmiMultifeeds_ListAdm extends AMI_Module_ListAdm{
    /**
     * Initialization.
     *
     * @return AMI_Module_FormAdm
     */
    public function init(){
        parent::init();
        // Drop full-env action, replace with fast-env
        $this->dropActions('common', array('edit'));
        $this->addActions(array('edit'));
        return $this;
    }
}

/**
 * Multifeeds hypermodule admin list component actions controller.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
abstract class Hyper_AmiMultifeeds_ListActionsAdm extends AMI_Module_ListActionsAdm{
}

/**
 * Multifeeds hypermodule admin list component group actions controller.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
abstract class Hyper_AmiMultifeeds_ListGroupActionsAdm extends AMI_Module_ListGroupActionsAdm{
}

/**
 * Multifeeds hypermodule admin list component view.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage View
 * @since      6.0.2
 */
abstract class Hyper_AmiMultifeeds_ListViewAdm extends AMI_Module_ListViewAdm{
}
