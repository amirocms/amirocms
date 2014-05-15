<?php
/**
 * AmiUsers hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiUsers
 * @version   $Id: Hyper_AmiUsers_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiUsers hypermodule admin action controller.
 *
 * @package    Hyper_AmiUsers
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiUsers_Adm extends AMI_Mod{
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
 * AmiUsers hypermodule model.
 *
 * @package    Hyper_AmiUsers
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiUsers_State extends AMI_ModState{
}

/**
 * AmiUsers hypermodule admin filter component action controller.
 *
 * @package    Hyper_AmiUsers
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiUsers_FilterAdm extends AMI_ModFilter{
}

/**
 * AmiUsers hypermodule item list component filter model.
 *
 * @package    Hyper_AmiUsers
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiUsers_FilterModelAdm extends AMI_Filter{
    /**
     * Constructor.
     */
    public function __construct(){
    }
}

/**
 * AmiUsers hypermodule admin filter component view.
 *
 * @package    Hyper_AmiUsers
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiUsers_FilterViewAdm extends AMI_ModFilterViewAdm{
}

/**
 * AmiUsers hypermodule admin form component action controller.
 *
 * @package    Hyper_AmiUsers
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiUsers_FormAdm extends AMI_ModFormAdm{
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
 * AmiUsers hypermodule admin form component view.
 *
 * @package    Hyper_AmiUsers
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiUsers_FormViewAdm extends AMI_ModFormViewAdm{
}

/**
 * AmiUsers hypermodule admin list component action controller.
 *
 * @package    Hyper_AmiUsers
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiUsers_ListAdm extends AMI_ModListAdm{
    /**
     * List group actions controller resource id
     *
     * @var string
     */
    protected $listGrpActionsResId = 'members/list_actions/group';
}

/**
 * AmiUsers hypermodule admin list component view.
 *
 * @package    Hyper_AmiUsers
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiUsers_ListViewAdm extends AMI_ModListView_JSON{

    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'date_created';

    /**
     * Order column direction
     *
     * @var bool
     */
    protected $orderDirection = 'desc';

    /**
     * List default elemnts template (placeholders)
     *
     * @var array
     */
    protected $aPlaceholders = array(
        '#list_header',
            '#columns', 'columns',
            '#actions', 'actions',
        'list_header'
    );
}

/**
 * AmiUsers hypermodule list action controller.
 *
 * @package    Hyper_AmiUsers
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiUsers_ListActionsAdm  extends AMI_ModListActions{
    /**
     * System user id
     *
     * @var int
     */
    protected $sysUserId;

    /**
     * Source core user object
     *
     * @var AMS_Member
     */
    protected $oMember;

    /**
     * Constructor.
     */
    public function __construct(){
        $sql =
            "SELECT u.id " .
            "FROM `cms_host_users` su " .
            "LEFT JOIN `cms_members` u ON u.id = su.id_member " .
            "WHERE su.sys_user = 1";
        $this->sysUserId = (int)AMI::getSingleton('db')->fetchValue($sql);
        if(!$this->sysUserId){
            trigger_error('System user not found', E_USER_ERROR);
        }
    }

    /**
     * Event handler.
     *
     * Sets up CMS_Member instance and calls parent.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListAdm::addActionCallback()
     */
    public function setActionData($name, array $aEvent, $handlerModId, $srcModId){
        if(!is_object($this->oMember)){
            $aObligatoryFields = AMI::getOption($handlerModId, 'admin_required_fields');
            if(is_string($aObligatoryFields)){
                $aObligatoryFields = explode('|', $aObligatoryFields);
            }
            $none = '';
            $this->oMember = new CMS_Member($none);
            $this->oMember->setObligatory($aObligatoryFields, TRUE, TRUE);
        }
        return parent::setActionData($name, $aEvent, $handlerModId, $srcModId);
    }
}

/**
 * AmiUsers hypermodule list group action controller.
 *
 * @package    Hyper_AmiUsers
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiUsers_ListGroupActionsAdm  extends AMI_ModListGroupActions{
}
