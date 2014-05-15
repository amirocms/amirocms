<?php
/**
 * AmiJobs/LoginHistory configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiLoginHistory_LoginHistory
 * @version   $Id: AmiLoginHistory_LoginHistory_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiJobs/LoginHistory configuration admin action controller.
 *
 * @package    Config_AmiLoginHistory_LoginHistory
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiLoginHistory_LoginHistory_Adm extends Hyper_AmiLoginHistory_Adm{
    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request object
     * @param AMI_Response $oResponse  Response object
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        parent::__construct($oRequest, $oResponse);
        $this->addComponents(array('filter', 'list'));
    }
}

/**
 * AmiJobs/LoginHistory configuration model.
 *
 * @package    Config_AmiLoginHistory_LoginHistory
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiLoginHistory_LoginHistory_State extends Hyper_AmiLoginHistory_State{
}

/**
 * AmiJobs/LoginHistory configuration admin filter component action controller.
 *
 * @package    Config_AmiLoginHistory_LoginHistory
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiLoginHistory_LoginHistory_FilterAdm extends Hyper_AmiLoginHistory_FilterAdm{
}

/**
 * AmiJobs/LoginHistory configuration item list component filter model.
 *
 * @package    Config_AmiLoginHistory_LoginHistory
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiLoginHistory_LoginHistory_FilterModelAdm extends Hyper_AmiLoginHistory_FilterModelAdm{
    /**
     * Constructor.
     */
    public function __construct(){
        $this->addViewField(
            array(
                'name'          => 'datefrom',
                'type'          => 'datefrom',
                'flt_type'      => 'date',
                'flt_default'   => AMI_Lib_Date::formatUnixTime(AMI_Lib_Date::UTIME_MIN),
                'flt_condition' => '>=',
                'flt_column'    => 'date',
                'validate' 		=> array('date','date_limits'),
                'session_field' => true
            )
        );
        $this->addViewField(
            array(
                'name'          => 'dateto',
                'type'          => 'dateto',
                'flt_type'      => 'date',
                'flt_default'   => AMI_Lib_Date::formatUnixTime(AMI_Lib_Date::UTIME_MAX),
                'flt_condition' => '<=',
                'flt_column'    => 'date',
                'validate' 		=> array('date','date_limits'),
                'session_field' => true
            )
        );
        $this->addViewField(
            array(
                'name'          => 'login',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'login'
            )
        );
        $this->addViewField(
            array(
                'name'          => 'ip',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'ip'
            )
        );
        $this->addViewField(
            array(
                'name'          => 'show_all',
                'type'          => 'hidden',
                'flt_type'      => 'hidden',
                'flt_default'   => '0',
                'flt_condition' => '=',
                'flt_column'    => 'ip'
            )
        );
        if(hostMode() & HOSTMODE_ADMIN){
            $this->addViewField(
                array(
                    'name'          => 'domain',
                    'type'          => 'input',
                    'flt_type'      => 'text',
                    'flt_default'   => '',
                    'flt_condition' => 'like',
                    'flt_column'    => 'domain'
                )
            );
        }
    }

    /**
     * Adds current user id as id_owner.
     *
     * @param string $field  Field name
     * @param array $aData  Filter data
     * @return array
     * @amidev Temporary
     */
    protected function processFieldData($field, array $aData){
        if($field == 'show_all'){
            if($aData['value']){
                $aData['skip'] = true;
            }else{
                $aData['forceSQL'] = " AND `ip` NOT IN ('92.125.152.98', '92.125.152.101', '89.189.185.215') ";
            }
        }
        return $aData;
    }
}

/**
 * AmiJobs/LoginHistory configuration admin filter component view.
 *
 * @package    Config_AmiLoginHistory_LoginHistory
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiLoginHistory_LoginHistory_FilterViewAdm extends Hyper_AmiLoginHistory_FilterViewAdm{
    /**
     * Filter default elemnts template (placeholders)
     *
     * @var array
     */
    protected $aPlaceholders = array(
        '#filter',
           'login', 'datefrom', 'dateto', 'ip',
        'filter'
    );
}

/**
 * AmiJobs/LoginHistory configuration admin list component action controller.
 *
 * @package    Config_AmiLoginHistory_LoginHistory
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiLoginHistory_LoginHistory_ListAdm extends Hyper_AmiLoginHistory_ListAdm{
}

/**
 * AmiJobs/LoginHistory configuration admin list component view.
 *
 * @package    Config_AmiLoginHistory_LoginHistory
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiLoginHistory_LoginHistory_ListViewAdm extends Hyper_AmiLoginHistory_ListViewAdm{
    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AMI_Module_ListViewAdm
     */
    public function init(){
        parent::init();
        // Init columns
        $this
            ->addColumnType('id', 'hidden')
            ->addColumnType('date', 'datetime')
            ->addColumn('login');
        if(hostMode() & HOSTMODE_ADMIN){
            $this->addColumn('domain');
        }
        $this
            ->addColumn('status')
            ->addColumn('ip')
            ->addSortColumns(array('date', 'ip', 'login', 'status'))
            ->setColumnTensility('login')
            ->setColumnWidth('date', '120px')
            ->formatColumn(
                'date',
                array($this, 'fmtDateTime'),
                array(
                    'format' => AMI_Lib_Date::FMT_BOTH
                )
            )
            ->formatColumn(
                'status',
                array($this, 'fmtStatus')
            );
        return $this;
    }

    /**
     * Formats status value.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     */
    protected function fmtStatus($value, array $aArgs){
        return $this->aLocale[$value];
    }
}

/**
 * AmiJobs/LoginHistory configuration module admin list actions controller.
 *
 * @package    Config_AmiLoginHistory_LoginHistory
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiLoginHistory_LoginHistory_ListActionsAdm extends Hyper_AmiLoginHistory_ListActionsAdm{
}

/**
 * AmiJobs/LoginHistory configuration module admin list group actions controller.
 *
 * @package    Config_AmiLoginHistory_LoginHistory
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiLoginHistory_LoginHistory_ListGroupActionsAdm extends Hyper_AmiLoginHistory_ListGroupActionsAdm{
}
