<?php
/**
 * AmiSubscribe/Subscribe configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiSubscribe_Subscribe
 * @version   $Id: AmiSubscribe_Subscribe_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiSubscribe/Subscribe configuration admin action controller.
 *
 * @package    Config_AmiSubscribe_Subscribe
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Subscribe_Adm extends Hyper_AmiSubscribe_Adm{
    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request object
     * @param AMI_Response $oResponse  Response object
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        parent::__construct($oRequest, $oResponse);
        AMI_Event::addHandler('on_list_init', array($this, 'handleListInit'), $this->getModId());
    }

    /**#@+
     * Event handler.
     *
     * @see AMI_Event::addHandler()
     * @see AMI_Event::fire()
     */

    /**
     * Adds late data binding for topics solumn.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListInit($name, array $aEvent, $handlerModId, $srcModId){
        if($handlerModId === $this->getModId()){
            AMI_Event::disableHandler('on_list_init');
            $idColumn = AMI::getResourceModel($aEvent['modId'] . '/table')->getItem()->getPrimaryKeyField();
            AMI_Event::enableHandler('on_list_init');
            $aEvent['oList']->setLateDataBinding(
                $idColumn, 'topics', 'subs_topic', 'header', DB_Query::getSnippet(''),
                'id', 0, array('modelIdsField' => 'topics', 'delimiter' => ';')
            );
        }
        return $aEvent;
    }

    /**#@-*/
}

/**
 * AmiSubscribe/Subscribe configuration model.
 *
 * @package    Config_AmiSubscribe_Subscribe
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Subscribe_State extends Hyper_AmiSubscribe_State{
}

/**
 * AmiSubscribe/Subscribe configuration admin filter component action controller.
 *
 * @package    Config_AmiSubscribe_Subscribe
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Subscribe_FilterAdm extends Hyper_AmiSubscribe_FilterAdm{
}

/**
 * AmiSubscribe/Subscribe configuration item list component filter model.
 *
 * @package    Config_AmiSubscribe_Subscribe
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Subscribe_FilterModelAdm extends Hyper_AmiSubscribe_FilterModelAdm{
    /**
     * Common filter fields
     *
     * @var   array
     */
    protected $aCommonFields = array();

    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();

        if(AMI::issetAndTrueOption('subscribe', 'topics')){
            $aData = array();
            $oList = AMI::getResourceModel('subs_topic/table')->getList()->addColumn('*')->addOrder('name')->load();
            foreach($oList as $oItem){
                $aData[] = array('value' => $oItem->id , 'name' => $oItem->name);
            }
            $this->addViewField(
                array(
                    'name'          => 'topics',
                    'type'          => 'select',
                    'flt_condition' => '=',
                    'not_selected'  => array('id' => '', 'caption' => 'flt_all_topics'),
                    'data'          => $aData
                )
            );
        }

        $this->addViewField(
            array(
                'name'          => 'u_email',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'email',
                'flt_alias'     => 'u'
            )
        );

        $this->addViewField(
            array(
                'name'          => 'u_date_created',
                'type'          => 'datefrom',
                'flt_type'      => 'date',
                'flt_default'   => AMI_Lib_Date::formatUnixTime(AMI_Lib_Date::UTIME_MIN),
                'flt_condition' => '>=',
                'flt_alias'     => 'u',
                'flt_column'    => 'date',
				'validate' 		=> array('date','date_limits'),
				'session_field' => true
            )
        );

        $this->addViewField(
            array(
                'name'          => 'date_stop',
                'type'          => 'dateto',
                'flt_type'      => 'date',
                'flt_default'   => AMI_Lib_Date::formatUnixTime(AMI_Lib_Date::UTIME_MAX),
                'flt_condition' => '<=',
                'flt_column'    => 'date_stop',
				'validate' 		=> array('date','date_limits'),
				'session_field' => true
            )
        );
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
        if($field == 'topics'){
            if($aData['value'] == ''){
                $aData['skip'] = true;
            }else{
                $aData['forceSQL'] = " AND topics LIKE '%;".(int)$aData['value'].";%'";
            }
        }
        return $aData;
    }
}

/**
 * AmiSubscribe/Subscribe configuration admin filter component view.
 *
 * @package    Config_AmiSubscribe_Subscribe
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Subscribe_FilterViewAdm extends Hyper_AmiSubscribe_FilterViewAdm{
	/**
 	 * Placeholders array
 	 *
 	 * @var array
 	 */
    protected $aPlaceholders = array(
        '#filter',
            'topics',
        'filter'
    );
}

/**
 * AmiSubscribe/Subscribe configuration admin form component action controller.
 *
 * @package    Config_AmiSubscribe_Subscribe
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Subscribe_FormAdm extends Hyper_AmiSubscribe_FormAdm{
}

/**
 * AmiSubscribe/Subscribe configuration form component view.
 *
 * @package    Config_AmiSubscribe_Subscribe
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Subscribe_FormViewAdm extends Hyper_AmiSubscribe_FormViewAdm{
    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        return parent::init();
    }
}

/**
 * AmiSubscribe/Subscribe configuration admin list component action controller.
 *
 * @package    Config_AmiSubscribe_Subscribe
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Subscribe_ListAdm extends Hyper_AmiSubscribe_ListAdm{
    /**
     * List actions controller resource id
     *
     * @var string
     */
    protected $listActionsResId = 'subscribe/list_actions/controller/adm';

    /**
     * List group actions controller resource id
     *
     * @var string
     */
    protected $listGrpActionsResId = 'subscribe/list_group_actions/controller/adm';

    /**
     * Initialization.
     *
     * @return EshopOrder_ListAdm
     */
    public function init(){
        $this->addJoinedColumns(array('date_created', 'login', 'email', 'firstname', 'lastname', 'ip'), 'u');

        parent::init();

        $this->dropActions(AMI_ModListAdm::ACTION_ALL, array('edit', 'delete'));
        $this->addActions(array('edit', 'reset', 'delete'));
        $this->addColActions(array(self::REQUIRE_FULL_ENV . 'active'), true);
        $this->dropActions(AMI_ModListAdm::ACTION_COLUMN, array('public'));
        $this->dropActions(self::ACTION_GROUP);

        return $this;
    }
}

/**
 * AmiSubscribe/Subscribe configuration admin list component view.
 *
 * @package    Config_AmiSubscribe_Subscribe
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Subscribe_ListViewAdm extends Hyper_AmiSubscribe_ListViewAdm{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'u.date_created';

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
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#list_header',
            '#columns', 'active', 'u_login', 'u_email', 'u_firstname', 'u_lastname', 'topics', 'u_date_created', 'columns',
            '#actions', 'edit', 'reset', 'delete', 'actions',
        'list_header'
    );

    /**
     * Number of topics in list
     *
     * @var int
     */
    protected $numTopicsInList = 2;

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiSubscribe_Subscribe_ListViewAdm
     */
    public function init(){
        parent::init();

        $this->numTopicsInList = intval(AMI::getOption($this->getModId(), 'topics_in_list'));

        // Init columns
        $this
            ->addColumnType('id_member', 'hidden')
            ->addColumnType('date_stop', 'hidden')
            ->addColumnType('u_ip', 'hidden')
            ->removeColumn('header')
            ->removeColumn('announce')
            ->removeColumn('position')
            ->removeColumn('public')
            ->removeColumn('date_created')
            ->addColumn('active')
            ->addColumn('u_login')
            ->addColumn('u_email')
            ->addColumn('u_firstname')
            ->addColumn('u_lastname')
            ->addColumn('topics')
            ->addColumn('u_date_created')
            ->setColumnTensility('topics', true)
            ->addColumnType('u_date_created', 'datetime')
            ->formatColumn('topics', array($this, 'fmtTopics'))
            ->formatColumn('u_date_created', array($this, 'fmtDateSubscribe'))
            ->addSortColumns(array('active', 'u_login', 'u_email', 'u_firstname', 'u_lastname', 'u_date_created'));

        if(!AMI::issetAndTrueOption($this->getModId(), 'topics')){
            $this->dropPlaceholders(array('topics'));
        }

        $this->addScriptCode($this->getTemplate()->parse($this->tplBlockName . ':javascript'));

        return $this;
    }

    /**
     * Formats topics list.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     */
    protected function fmtTopics($value, array $aArgs){
        $topicsCol = '';
        if(!empty($value)){
            $aTopics = explode(';', $value);
            $cnt = 0;
            foreach($aTopics as $topic){
                if($cnt <= $this->numTopicsInList){
                    if($cnt++ < $this->numTopicsInList){
                        $topicsCol .= $this->getTemplate()->parse($this->tplBlockName . ':topics_column', $aData = array('value' => AMI_Lib_String::truncate($topic, 45)));
                    }else{
                        $topicsCol .= "...";
                    }
                }
            }
        }
        return $topicsCol;
    }

    /**
     * Formats subscribe date.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     */
    protected function fmtDateSubscribe($value, array $aArgs){
        $aData = array(
            'date'      => AMI_Lib_Date::formatDateTime($value, AMI_Lib_Date::FMT_BOTH),
            'date_stop' =>
                ($aArgs['aScope']['date_stop'] == '0000-00-00 00:00:00'
                ? '-'
                : AMI_Lib_Date::formatDateTime($aArgs['aScope']['date_stop'], AMI_Lib_Date::FMT_BOTH)),
            'ip' => $aArgs['aScope']['u_ip']
        );
        return $this->getTemplate()->parse($this->tplBlockName . ':subscribe_column', $aData);
    }
}

/**
 * AmiSubscribe/Subscribe configuration module admin list actions controller.
 *
 * @package    Config_AmiSubscribe_Subscribe
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Subscribe_ListActionsAdm extends Hyper_AmiSubscribe_ListActionsAdm{
}

/**
 * AmiSubscribe/Subscribe configuration module admin list group actions controller.
 *
 * @package    Config_AmiSubscribe_Subscribe
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Subscribe_ListGroupActionsAdm extends Hyper_AmiSubscribe_ListGroupActionsAdm{
}
