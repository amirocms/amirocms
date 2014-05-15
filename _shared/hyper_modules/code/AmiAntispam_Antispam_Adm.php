<?php
/**
 * AmiAntispam/Antispam configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiAntispam_Antispam
 * @version   $Id: AmiAntispam_Antispam_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiAntispam/Antispam configuration admin action controller.
 *
 * @package    Config_AmiAntispam_Antispam
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAntispam_Antispam_Adm extends Hyper_AmiAntispam_Adm{
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
 * AmiAntispam/Antispam configuration model.
 *
 * @package    Config_AmiAntispam_Antispam
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAntispam_Antispam_State extends Hyper_AmiAntispam_State{
}

/**
 * AmiAntispam/Antispam configuration admin filter component action controller.
 *
 * @package    Config_AmiAntispam_Antispam
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAntispam_Antispam_FilterAdm extends Hyper_AmiAntispam_FilterAdm{
}

/**
 * AmiAntispam/Antispam configuration item list component filter model.
 *
 * @package    Config_AmiAntispam_Antispam
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAntispam_Antispam_FilterModelAdm extends Hyper_AmiAntispam_FilterModelAdm{
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

        /*
        $oTpl = AMI::getResource('env/template_sys');
        $words50 = $oTpl->parseLocale('templates/lang/_menu_all.lng');
        $words60 = $oTpl->parseLocale(AMI_iTemplate::LOCAL_LNG_MOD_PATH . '/_menu.lng');
        $words = array_merge($words50, $words60);

        $a50Mods = array(
            'blog'            => 'blog',
            "forum"           => "forum",
            "votes"           => "votes",
            "guestbook"       => "guestbook",
            "members"         => "members",
            "subscribe"       => "subscribe",
            "feedback"        => "feedback",
            "adv_advertisers" => "adv_advertisers",
            "jobs_employer"   => "jobs_employer"
        );
        foreach($a50Mods as $modId){
            $aModules[] = array(
                'name'  => $words[$modId],
                'value' => $modId
            );
        }

        $aModIds = array_keys(AMI_Ext::getSupportedModules('ext_twist_prevention'));
        foreach($aModIds as $modId){
            $aModules[] = array(
                'name'  => $words[$modId],
                'value' => $modId
            );
        }
        */

        $aModIds = array(
            'blog',
            'forum',
            'votes',
            'guestbook',
            'members',
            'subscribe',
            'feedback',
            'adv_advertisers',
            'jobs_employer',
            'eshop_item',
            'kb_item',
            'portfolio_item'
        );
        foreach(array_keys(AMI_Ext::getSupportedModules('ext_twist_prevention')) as $modId){
            $aModIds[] = $modId;
        }
        $aCaptions = AMI_Service_Adm::getModulesCaptions($aModIds, TRUE, array('eshop', 'kb', 'portfolio'));
        $aModules = array();
        foreach($aCaptions as $modId => $caption){
            $aModules[] = array(
                'name'  => $caption,
                'value' => $modId
            );
        }

        $this->addViewField(
            array(
                'name'          => 'ext_module',
                'type'          => 'select',
                'flt_type'      => 'select',
                'flt_default'   => '-1',
                'flt_condition' => '=',
                'flt_column'    => 'ext_module',
                'data'          => $aModules,
                'not_selected'  => array('id' => '-1', 'caption' => 'all'),
                'session_field' => true
            )
        );

        $aYesNo = array(
            array(
                'value' => 1,
                'caption' => 'yes'
            ),
            array(
                'value' => 0,
                'caption' => 'no'
            )
        );

        $this->addViewField(
            array(
                'name'          => 'twist',
                'type'          => 'select',
                'flt_type'      => 'select',
                'flt_default'   => '-1',
                'flt_condition' => '=',
                'flt_column'    => 'twist',
                'data'          => $aYesNo,
                'not_selected'  => array('id' => '-1', 'caption' => 'all'),
                'session_field' => true
            )
        );
        $this->addViewField(
            array(
                'name'          => 'is_generated',
                'type'          => 'select',
                'flt_type'      => 'select',
                'flt_default'   => '-1',
                'flt_condition' => '=',
                'flt_column'    => 'is_generated',
                'data'          => $aYesNo,
                'not_selected'  => array('id' => '-1', 'caption' => 'all'),
                'session_field' => true
            )
        );
        $aReasons = array(
            array(
                'value'     => "no_cookies",
                'caption'   => "no_cookies"
            ),
            array(
                'value'     => "invalid_captcha",
                'caption'   => "invalid_captcha"
            ),
            array(
                'value'     => "too_frequently",
                'caption'   => "too_frequently"
            ),
            array(
                'value'     => "no_javascript",
                'caption'   => "no_javascript"
            ),
        );
        $this->addViewField(
            array(
                'name'          => 'reason',
                'type'          => 'select',
                'flt_type'      => 'select',
                'flt_default'   => '-1',
                'flt_condition' => '=',
                'flt_column'    => 'reason',
                'data'          => $aReasons,
                'not_selected'  => array('id' => '-1', 'caption' => 'all'),
                'session_field' => true
            )
        );
    }

    /**
     * Handle filter custom logic.
     *
     * @param  string $field  Field name
     * @param  array  $aData  Filter data
     * @return array
     */
    protected function processFieldData($field, array $aData){
        if(in_array($field, array('ext_module', 'is_generated', 'twist', 'reason'))){
            if($aData['value'] == "-1"){
                $aData['skip'] = true;
            }
        }
        return $aData;
    }
}

/**
 * AmiAntispam/Antispam configuration admin filter component view.
 *
 * @package    Config_AmiAntispam_Antispam
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAntispam_Antispam_FilterViewAdm extends Hyper_AmiAntispam_FilterViewAdm{
    /**
     * Filter default elemnts template (placeholders)
     *
     * @var array
     */
    protected $aPlaceholders = array(
        '#filter',
            'datefrom', 'dateto',
        'filter'
    );
}

/**
 * AmiAntispam/Antispam configuration admin form component action controller.
 *
 * @package    Config_AmiAntispam_Antispam
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAntispam_Antispam_FormAdm extends Hyper_AmiAntispam_FormAdm{
}

/**
 * AmiAntispam/Antispam configuration form component view.
 *
 * @package    Config_AmiAntispam_Antispam
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAntispam_Antispam_FormViewAdm extends Hyper_AmiAntispam_FormViewAdm{
}

/**
 * AmiAntispam/Antispam configuration admin list component action controller.
 *
 * @package    Config_AmiAntispam_Antispam
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAntispam_Antispam_ListAdm extends Hyper_AmiAntispam_ListAdm{
}

/**
 * AmiAntispam/Antispam configuration admin list component view.
 *
 * @package    Config_AmiAntispam_Antispam
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAntispam_Antispam_ListViewAdm extends Hyper_AmiAntispam_ListViewAdm{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'id';

    /**
     * Order column direction
     *
     * @var bool
     */
    protected $orderDirection = 'asc';

    /**
     * Modules captions
     *
     * @var array
     */
    protected $words = null;

    /**
     * Real modules captions
     *
     * @var array
     */
    protected $aCaptions;

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
            ->addColumn('ext_module')
            ->addColumn('ip')
            ->addColumn('vid')
            ->addColumn('is_generated')
            ->addColumn('twist')
            ->addColumn('reason')
            ->formatColumn('is_generated', array($this, 'fmtYesNo'))
            ->formatColumn('twist', array($this, 'fmtYesNo'))
            ->formatColumn('reason', array($this, 'fmtFromLocale'))
            ->formatColumn('ext_module', array($this, 'fmtModuleName'))
            ->formatColumn('ip', array($this, 'fmtIP'))
            ->addSortColumns(array('date', 'ip', 'vid', 'is_generated', 'twist', 'reason'))
            ->formatColumn(
                'date',
                array($this, 'fmtDateTime'),
                array(
                    'format' => AMI_Lib_Date::FMT_BOTH
                )
            );

        $aModIds = array(
            'blog',
            'forum',
            'votes',
            'guestbook',
            'members',
            'subscribe',
            'feedback',
            'adv_advertisers',
            'jobs_employer',
            'eshop_item',
            'kb_item',
            'portfolio_item'
        );
        foreach(array_keys(AMI_Ext::getSupportedModules('ext_twist_prevention')) as $modId){
            $aModIds[] = $modId;
        }
        $this->aCaptions = AMI_Service_Adm::getModulesCaptions($aModIds, TRUE, array('eshop', 'kb', 'portfolio'));


        return $this;
    }

    /**
     * Formats IP address stored as long.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     */
    protected function fmtIP($value, array $aArgs){
        return long2ip($value);
    }

    /**
     * Formats column value from list locales.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     */
    protected function fmtFromLocale($value, array $aArgs){
        return isset($this->aLocale[$value]) ? $this->aLocale[$value] : '';
    }

    /**
     * Formats 1 and 0 as Yes or No.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     */
    protected function fmtYesNo($value, array $aArgs){
        return $this->aLocale[$value ? 'yes' : 'no'];
    }

    /**
     * Formats IP address stored as long.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     */
    protected function fmtModuleName($value, array $aArgs){
        if(is_null($this->words)){
            $oTpl = $this->getTemplate();
            $words50 = $oTpl->parseLocale('templates/lang/_menu_all.lng');
            $words60 = $oTpl->parseLocale(AMI_iTemplate::LOCAL_LNG_MOD_PATH . '/_menu.lng');
            $this->words = array_merge($words50, $words60);
        }
        if(isset($this->aCaptions[$value])){
            return $this->aCaptions[$value];
        }
        return isset($this->words[$value]) ? $this->words[$value] : 'unknown';
    }
}

/**
 * AmiAntispam/Antispam configuration module admin list actions controller.
 *
 * @package    Config_AmiAntispam_Antispam
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAntispam_Antispam_ListActionsAdm extends Hyper_AmiAntispam_ListActionsAdm{
}

/**
 * AmiAntispam/Antispam configuration module admin list group actions controller.
 *
 * @package    Config_AmiAntispam_Antispam
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAntispam_Antispam_ListGroupActionsAdm extends Hyper_AmiAntispam_ListGroupActionsAdm{
}
