<?php
/**
 * AmiJobs/History configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiJobs_History
 * @version   $Id: AmiJobs_History_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiJobs/History configuration admin action controller.
 *
 * @package    Config_AmiJobs_History
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_History_Adm extends Hyper_AmiJobs_Adm{
}

/**
 * AmiJobs/History configuration model.
 *
 * @package    Config_AmiJobs_History
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_History_State extends Hyper_AmiJobs_State{
}

/**
 * AmiJobs/History configuration admin filter component action controller.
 *
 * @package    Config_AmiJobs_History
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_History_FilterAdm extends Hyper_AmiJobs_FilterAdm{
}

/**
 * AmiJobs/History configuration item list component filter model.
 *
 * @package    Config_AmiJobs_History
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_History_FilterModelAdm extends Hyper_AmiJobs_FilterModelAdm{
    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();

        $this->addViewField(
            array(
                'name'          => 'header',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'title',
            )
        );

        $this->addViewField(
            array(
                'name'          => 'fio',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'lname',
            )
        );

        $aStatuses = array('not viewed', 'marked', 'ignored', 'replied', 'request', 'accepted', 'moved');

        $aData = array();
        foreach($aStatuses as $status){
            $aData[] = array(
                'name'    => $status,
                'value'   => $status,
                'caption' => 'status_' . $status
            );
        }

        $this->addViewField(
            array(
                'name'          => 'status',
                'type'          => 'select',
                'flt_condition' => '=',
                'not_selected'  => array('id' => '', 'caption' => 'flt_all_statuses'),
                'data'          => $aData
            )
        );

        $aData = array();
        $oList = AMI::getResourceModel('jobs_cat/table')->getList()->addColumn('*')->load();
        foreach($oList as $oItem){
			$aData[] = array( 'value' => $oItem->id , 'name' => $oItem->name);
        }

        $this->addViewField(
            array(
                'name'          => 'department',
                'type'          => 'select',
                'flt_condition' => '=',
                'not_selected'  => array('id' => '', 'caption' => 'flt_all_statuses'),
                'data'          => $aData
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
        if(($field == 'department') && ($aData['value'])){
            $aData['forceSQL'] = " AND (i.`id_cat` = " . intval($aData['value']) . ") ";
        }
        if(($field == 'fio') && ($aData['value'])){
            $aData['forceSQL'] = " AND (CONCAT(i.`lname`, ' ', i.`fname`) LIKE '%" . $this->prepareSqlField('lname', $aData['value'], 'text') . "%') ";
        }
        return $aData;
    }
}

/**
 * AmiJobs/History configuration admin filter component view.
 *
 * @package    Config_AmiJobs_History
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_History_FilterViewAdm extends Hyper_AmiJobs_FilterViewAdm{
	/**
 	 * Placeholders array
 	 *
 	 * @var array
 	 */
    protected $aPlaceholders = array(
        '#filter',
            'department', 'datefrom', 'dateto', 'status', 'header', 'fio',
        'filter'
    );
}

/**
 * AmiJobs/History configuration admin form component action controller.
 *
 * @package    Config_AmiJobs_History
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_History_FormAdm extends Hyper_AmiJobs_FormAdm{
}

/**
 * AmiJobs/History configuration form component view.
 *
 * @package    Config_AmiJobs_History
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_History_FormViewAdm extends Hyper_AmiJobs_FormViewAdm{
}

/**
 * AmiJobs/History configuration admin list component action controller.
 *
 * @package    Config_AmiJobs_History
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_History_ListAdm extends Hyper_AmiJobs_ListAdm{
    /**
     * Initialization.
     *
     * @return JobsResume_ListAdm
     */
    public function init(){
        parent::init();
        $this->dropActions(AMI_ModListAdm::ACTION_COMMON, array('edit', 'delete'));
        $this->addActions(array('move', 'edit', 'reply', 'attach', 'print', 'delete'));
        $this->addActionCallback('common', 'edit');
        $this->addActionCallback('common', 'delete');
        $this->dropActions(AMI_ModListAdm::ACTION_GROUP, array('public', 'unpublic', 'gen_sublink', 'gen_html_meta', 'gen_html_meta_force', 'index_details', 'no_index_details'));
        $this->dropActions(AMI_ModListAdm::ACTION_COLUMN, array('public'));
        $this->addJoinedColumns(array('id'), 'resume');
        return $this;
    }
}

/**
 * AmiJobs/History configuration admin list component view.
 *
 * @package    Config_AmiJobs_History
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_History_ListViewAdm extends Hyper_AmiJobs_ListViewAdm{
    /**
     * List default elemnts template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#list_header',
            '#flags', 'flags',
            '#common', 'common',
            '#columns', 'num', 'department', 'title', 'date_created', 'lname', 'phone', 'login', 'status', 'columns',
            '#actions', 'front_view', 'edit', 'actions',
        'list_header'
    );
    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiJobs_History_ListViewAdm
     */
    public function init(){
        parent::init();
        $this
            ->removeColumn('header')
            ->removeColumn('announce')
            ->removeColumn('position')
            ->removeColumn('public')
            ->addColumnType('date_created', 'date')
            ->addColumnType('fname', 'hidden')
            ->addColumnType('id_file', 'hidden')
            ->addColumnType('num', 'date')
            ->addColumn('lname')
            ->addColumnType('resume_id', 'hidden')
            ->addColumn('status')
            ->addColumn('title')
            ->addColumn('login')
            ->addColumn('department')
            ->addColumn('phone')
            ->setColumnTensility('title', true)
            ->addSortColumns(
                array(
                    'date_created', 'lname', 'title', 'department'
                )
            );

        $this->setColumnWidth('lname', 'wide');
        $this->setColumnWidth('department', 'wide');

        $this->formatColumn(
            'status',
            array($this, 'fmtStatus'),
            array()
        );

        $this->formatColumn(
            'date_created',
            array($this, 'fmtDateTime'),
            array(
                'format' => AMI_Lib_Date::FMT_DATE
            )
        );

        AMI_Event::addHandler('on_list_body_{lname}', array($this, 'handleFullName'), $this->getModId());

        $this->getTemplate()->setBlockLocale($this->tplBlockName, $this->aLocale, true);
        $this->addScriptCode($this->getTemplate()->parse($this->tplBlockName . ':javascript'));

        return $this;
    }

    /**
     * Prepare Full Name field.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::get()
     */
    public function handleFullName($name, array $aEvent, $handlerModId, $srcModId){

        $aEvent['aScope']['list_col_value'] = $aEvent['aScope']['lname'].' '.$aEvent['aScope']['fname'];

        return $aEvent;
    }

    /**
     * Formats status value.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     */
    protected function fmtStatus($value, array $aArgs){
        return $this->aLocale['status_' . $value ];
    }
}

/**
 * AmiJobs/History configuration module admin list actions controller.
 *
 * @package    Config_AmiJobs_History
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_History_ListActionsAdm extends Hyper_AmiJobs_ListActionsAdm{
}

/**
 * AmiJobs/History configuration module admin list group actions controller.
 *
 * @package    Config_AmiJobs_History
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_History_ListGroupActionsAdm extends Hyper_AmiJobs_ListGroupActionsAdm{
}
