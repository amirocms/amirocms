<?php
/**
 * AmiSubscribe hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiSubscribe
 * @version   $Id: Hyper_AmiSubscribe_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiSubscribe hypermodule admin action controller.
 *
 * @package    Hyper_AmiSubscribe
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiSubscribe_Adm extends AMI_Module_Adm{
}

/**
 * Module model.
 *
 * @package    Hyper_AmiSubscribe
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiSubscribe_State extends AMI_ModState{
}

/**
 * AmiSubscribe hypermodule admin filter component action controller.
 *
 * @package    Hyper_AmiSubscribe
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiSubscribe_FilterAdm extends AMI_Module_FilterAdm{
}

/**
 * AmiSubscribe hypermodule item list component filter model.
 *
 * @package    Hyper_AmiSubscribe
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiSubscribe_FilterModelAdm extends AMI_Module_FilterModelAdm{
}

/**
 * AmiSubscribe hypermodule admin filter component view.
 *
 * @package    Hyper_AmiSubscribe
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiSubscribe_FilterViewAdm extends AMI_Module_FilterViewAdm{
}

/**
 * AmiSubscribe hypermodule admin form component action controller.
 *
 * @package    Hyper_AmiSubscribe
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiSubscribe_FormAdm extends AMI_Module_FormAdm{
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
 * AmiSubscribe hypermodule admin form component view.
 *
 * @package    Hyper_AmiSubscribe
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiSubscribe_FormViewAdm extends AMI_Module_FormViewAdm{
}

/**
 * AmiSubscribe hypermodule admin list component action controller.
 *
 * @package    Hyper_AmiSubscribe
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiSubscribe_ListAdm extends AMI_Module_ListAdm{
}

/**
 * AmiSubscribe hypermodule admin list component actions controller.
 *
 * @package    Hyper_AmiSubscribe
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiSubscribe_ListActionsAdm extends AMI_Module_ListActionsAdm{
}

/**
 * AmiSubscribe hypermodule admin list component group actions controller.
 *
 * @package    Hyper_AmiSubscribe
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiSubscribe_ListGroupActionsAdm extends AMI_Module_ListGroupActionsAdm{
}

/**
 * AmiSubscribe hypermodule admin list component view.
 *
 * @package    Hyper_AmiSubscribe
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiSubscribe_ListViewAdm extends AMI_Module_ListViewAdm{
}
