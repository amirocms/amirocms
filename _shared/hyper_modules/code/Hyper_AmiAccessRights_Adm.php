<?php
/**
 * AmiAccessRights hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiAccessRights
 * @version   $Id: Hyper_AmiAccessRights_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiAccessRights hypermodule admin action controller.
 *
 * @package    Hyper_AmiAccessRights
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiAccessRights_Adm extends AMI_Mod{
    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request object
     * @param AMI_Response $oResponse  Response object
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        parent::__construct($oRequest, $oResponse);
        $this->addComponents(array('filter', 'list', 'form'));
    }
}

/**
 * AmiAccessRights hypermodule model.
 *
 * @package    Hyper_AmiAccessRights
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiAccessRights_State extends AMI_ModState{
}

/**
 * AmiAccessRights hypermodule admin filter component action controller.
 *
 * @package    Hyper_AmiAccessRights
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiAccessRights_FilterAdm extends AMI_ModFilter{
}

/**
 * AmiAccessRights hypermodule item list component filter model.
 *
 * @package    Hyper_AmiAccessRights
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiAccessRights_FilterModelAdm extends AMI_Filter{
}

/**
 * AmiAccessRights hypermodule admin filter component view.
 *
 * @package    Hyper_AmiAccessRights
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiAccessRights_FilterViewAdm extends AMI_ModFilterViewAdm{
}

/**
 * AmiAccessRights hypermodule admin form component action controller.
 *
 * @package    Hyper_AmiAccessRights
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiAccessRights_FormAdm extends AMI_ModFormAdm{
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
 * AmiAccessRights hypermodule admin form component view.
 *
 * @package    Hyper_AmiAccessRights
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiAccessRights_FormViewAdm extends AMI_ModFormViewAdm{
}

/**
 * AmiAccessRights hypermodule admin list component action controller.
 *
 * @package    Hyper_AmiAccessRights
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiAccessRights_ListAdm extends AMI_ModListAdm{
}

/**
 * AmiAccessRights hypermodule admin list component view.
 *
 * @package    Hyper_AmiAccessRights
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiAccessRights_ListViewAdm extends AMI_ModListView_JSON{
}

/**
 * AmiAccessRights hypermodule list action controller.
 *
 * @category   AMI
 * @package    Hyper_AmiAccessRights
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiAccessRights_ListActionsAdm  extends AMI_ModListActions{
}

/**
 * AmiAccessRights hypermodule list group action controller.
 *
 * @category   AMI
 * @package    Hyper_AmiAccessRights
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiAccessRights_ListGroupActionsAdm  extends AMI_ModListGroupActions{
}
