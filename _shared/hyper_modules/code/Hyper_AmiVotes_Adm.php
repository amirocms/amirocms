<?php
/**
 * AmiVotes hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiVotes
 * @version   $Id: Hyper_AmiVotes_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiVotes hypermodule admin action controller.
 *
 * @package    Hyper_AmiVotes
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiVotes_Adm extends AMI_Module_Adm{
}

/**
 * Module model.
 *
 * @package    Hyper_AmiVotes
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiVotes_State extends AMI_ModState{
}

/**
 * AmiVotes hypermodule admin filter component action controller.
 *
 * @package    Hyper_AmiVotes
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiVotes_FilterAdm extends AMI_Module_FilterAdm{
}

/**
 * AmiVotes hypermodule item list component filter model.
 *
 * @package    Hyper_AmiVotes
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiVotes_FilterModelAdm extends AMI_Module_FilterModelAdm{
}

/**
 * AmiVotes hypermodule admin filter component view.
 *
 * @package    Hyper_AmiVotes
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiVotes_FilterViewAdm extends AMI_Module_FilterViewAdm{
}

/**
 * AmiVotes hypermodule admin form component action controller.
 *
 * @package    Hyper_AmiVotes
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiVotes_FormAdm extends AMI_Module_FormAdm{
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
 * AmiVotes hypermodule admin form component view.
 *
 * @package    Hyper_AmiVotes
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiVotes_FormViewAdm extends AMI_Module_FormViewAdm{
}

/**
 * AmiVotes hypermodule admin list component action controller.
 *
 * @package    Hyper_AmiVotes
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiVotes_ListAdm extends AMI_Module_ListAdm{
}

/**
 * AmiVotes hypermodule admin list component actions controller.
 *
 * @package    Hyper_AmiVotes
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiVotes_ListActionsAdm extends AMI_Module_ListActionsAdm{
}

/**
 * AmiVotes hypermodule admin list component group actions controller.
 *
 * @package    Hyper_AmiVotes
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiVotes_ListGroupActionsAdm extends AMI_Module_ListGroupActionsAdm{
}

/**
 * AmiVotes hypermodule admin list component view.
 *
 * @package    Hyper_AmiVotes
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiVotes_ListViewAdm extends AMI_Module_ListViewAdm{
}
