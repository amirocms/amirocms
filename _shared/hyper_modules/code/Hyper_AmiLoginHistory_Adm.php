<?php
/**
 * AmiLoginHistory hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiLoginHistory
 * @version   $Id: Hyper_AmiLoginHistory_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @amidev    Temporary
 */

/**
 * AmiLoginHistory hypermodule admin action controller.
 *
 * @package    Hyper_AmiLoginHistory
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiLoginHistory_Adm extends AMI_Mod{
}

/**
 * AmiLoginHistory hypermodule model.
 *
 * @package    Hyper_AmiLoginHistory
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiLoginHistory_State extends AMI_ModState{
}

/**
 * AmiLoginHistory hypermodule admin filter component action controller.
 *
 * @package    Hyper_AmiLoginHistory
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiLoginHistory_FilterAdm extends AMI_ModFilter{
}

/**
 * AmiLoginHistory hypermodule item list component filter model.
 *
 * @package    Hyper_AmiLoginHistory
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiLoginHistory_FilterModelAdm extends AMI_Filter{
}

/**
 * AmiLoginHistory hypermodule admin filter component view.
 *
 * @package    Hyper_AmiLoginHistory
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiLoginHistory_FilterViewAdm extends AMI_ModFilterViewAdm{
}

/**
 * AmiLoginHistory hypermodule admin form component action controller.
 *
 * @package    Hyper_AmiLoginHistory
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiLoginHistory_FormAdm extends AMI_ModFormAdm{
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
 * AmiLoginHistory hypermodule admin form component view.
 *
 * @package    Hyper_AmiLoginHistory
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiLoginHistory_FormViewAdm extends AMI_ModFormViewAdm{
}

/**
 * AmiLoginHistory hypermodule admin list component action controller.
 *
 * @package    Hyper_AmiLoginHistory
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiLoginHistory_ListAdm extends AMI_ModListAdm{
}

/**
 * AmiLoginHistory hypermodule admin list component view.
 *
 * @package    Hyper_AmiLoginHistory
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiLoginHistory_ListViewAdm extends AMI_ModListView_JSON{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'date';

    /**
     * Order column direction
     *
     * @var bool
     */
    protected $orderDirection = 'desc';
}

/**
 * AmiLoginHistory hypermodule list action controller.
 *
 * @category   AMI
 * @package    Hyper_AmiLoginHistory
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiLoginHistory_ListActionsAdm  extends AMI_ModListActions{
}

/**
 * AmiLoginHistory hypermodule list group action controller.
 *
 * @category   AMI
 * @package    Hyper_AmiLoginHistory
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiLoginHistory_ListGroupActionsAdm  extends AMI_ModListGroupActions{
}

