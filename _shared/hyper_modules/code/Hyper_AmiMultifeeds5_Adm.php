<?php
/**
 * AmiMultifeeds5 hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiMultifeeds5
 * @version   $Id: Hyper_AmiMultifeeds5_Adm.php 42166 2013-10-10 12:29:32Z Leontiev Anton $
 * @since     x.x.x
 * @amidev
 */

/**
 * Multifeeds hypermodule admin action controller.
 *
 * @package    Hyper_AmiMultifeeds5
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
abstract class Hyper_AmiMultifeeds5_Adm extends AMI_Module_Adm{
}

/**
 * Module model.
 *
 * @package    Hyper_AmiMultifeeds5
 * @subpackage Model
 * @since      x.x.x
 * @amidev
 */
abstract class Hyper_AmiMultifeeds5_State extends AMI_ModState{
}

/**
 * Multifeeds hypermodule admin filter component action controller.
 *
 * @package    Hyper_AmiMultifeeds5
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
abstract class Hyper_AmiMultifeeds5_FilterAdm extends AMI_Module_FilterAdm{
}

/**
 * Multifeeds hypermodule item list component filter model.
 *
 * @package    Hyper_AmiMultifeeds5
 * @subpackage Model
 * @since      x.x.x
 * @amidev
 */
abstract class Hyper_AmiMultifeeds5_FilterModelAdm extends AMI_Module_FilterModelAdm{
}

/**
 * Multifeeds hypermodule admin filter component view.
 *
 * @package    Hyper_AmiMultifeeds5
 * @subpackage View
 * @since      x.x.x
 * @amidev
 */
abstract class Hyper_AmiMultifeeds5_FilterViewAdm extends AMI_Module_FilterViewAdm{
}

/**
 * Multifeeds hypermodule admin form component action controller.
 *
 * @package    Hyper_AmiMultifeeds5
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
abstract class Hyper_AmiMultifeeds5_FormAdm extends AMI_Module_FormAdm{
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
 * @package    Hyper_AmiMultifeeds5
 * @subpackage View
 * @since      x.x.x
 * @amidev
 */
abstract class Hyper_AmiMultifeeds5_FormViewAdm extends AMI_Module_FormViewAdm{
}

/**
 * Multifeeds hypermodule admin list component action controller.
 *
 * @package    Hyper_AmiMultifeeds5
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
abstract class Hyper_AmiMultifeeds5_ListAdm extends AMI_Module_ListAdm{
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
 * @package    Hyper_AmiMultifeeds5
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
abstract class Hyper_AmiMultifeeds5_ListActionsAdm extends AMI_Module_ListActionsAdm{
}

/**
 * Multifeeds hypermodule admin list component group actions controller.
 *
 * @package    Hyper_AmiMultifeeds5
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
abstract class Hyper_AmiMultifeeds5_ListGroupActionsAdm extends AMI_Module_ListGroupActionsAdm{
}

/**
 * Multifeeds hypermodule admin list component view.
 *
 * @package    Hyper_AmiMultifeeds5
 * @subpackage View
 * @since      x.x.x
 * @amidev
 */
abstract class Hyper_AmiMultifeeds5_ListViewAdm extends AMI_Module_ListViewAdm{
}
