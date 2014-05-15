<?php
/**
 * AmiPageManager hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiPageManager
 * @version   $Id: Hyper_AmiPageManager_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiPageManager hypermodule admin action controller.
 *
 * @package    Hyper_AmiPageManager
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiPageManager_Adm extends AMI_Module_Adm{
}

/**
 * Module model.
 *
 * @package    Hyper_AmiPageManager
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiPageManager_State extends AMI_ModState{
}

/**
 * AmiPageManager hypermodule admin filter component action controller.
 *
 * @package    Hyper_AmiPageManager
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiPageManager_FilterAdm extends AMI_Module_FilterAdm{
}

/**
 * AmiPageManager hypermodule item list component filter model.
 *
 * @package    Hyper_AmiPageManager
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiPageManager_FilterModelAdm extends AMI_Module_FilterModelAdm{
}

/**
 * AmiPageManager hypermodule admin filter component view.
 *
 * @package    Hyper_AmiPageManager
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiPageManager_FilterViewAdm extends AMI_Module_FilterViewAdm{
}

/**
 * AmiPageManager hypermodule admin form component action controller.
 *
 * @package    Hyper_AmiPageManager
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiPageManager_FormAdm extends AMI_Module_FormAdm{
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
 * AmiPageManager hypermodule admin form component view.
 *
 * @package    Hyper_AmiPageManager
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiPageManager_FormViewAdm extends AMI_Module_FormViewAdm{
}

/**
 * AmiPageManager hypermodule admin list component action controller.
 *
 * @package    Hyper_AmiPageManager
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiPageManager_ListAdm extends AMI_Module_ListAdm{
}

/**
 * AmiPageManager hypermodule admin list component actions controller.
 *
 * @package    Hyper_AmiPageManager
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiPageManager_ListActionsAdm extends AMI_Module_ListActionsAdm{
}

/**
 * AmiPageManager hypermodule admin list component group actions controller.
 *
 * @package    Hyper_AmiPageManager
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiPageManager_ListGroupActionsAdm extends AMI_Module_ListGroupActionsAdm{
}

/**
 * AmiPageManager hypermodule admin list component view.
 *
 * @package    Hyper_AmiPageManager
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiPageManager_ListViewAdm extends AMI_Module_ListViewAdm{
}
