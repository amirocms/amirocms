<?php
/**
 * AmiJobs/Resume configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiJobs_Resume
 * @version   $Id: AmiJobs_Resume_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiJobs/Resume configuration admin action controller.
 *
 * @package    Config_AmiJobs_Resume
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_Resume_Adm extends Hyper_AmiJobs_Adm{
}

/**
 * AmiJobs/Resume configuration model.
 *
 * @package    Config_AmiJobs_Resume
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_Resume_State extends Hyper_AmiJobs_State{
}

/**
 * AmiJobs/Resume configuration admin filter component action controller.
 *
 * @package    Config_AmiJobs_Resume
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_Resume_FilterAdm extends Hyper_AmiJobs_FilterAdm{
}

/**
 * AmiJobs/Resume configuration item list component filter model.
 *
 * @package    Config_AmiJobs_Resume
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_Resume_FilterModelAdm extends Hyper_AmiJobs_FilterModelAdm{
    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();

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

        $aStatuses = array('not viewed', 'marked', 'ignored', 'accepted', 'replied', 'request');

        $aData = array();
        foreach($aStatuses as $status){
            $aData[] = array(
                'name'    => $status,
                'value'   => $status,
                'caption' => $status
            );
        }

        $this->addViewField(
            array(
                'name'          => 'status',
                'type'          => 'select',
                'flt_condition' => '=',
                'not_selected'  => array('id' => '', 'caption' => 'flt_all_statuses'),
                'data'          => $aData,
                'flt_default'   => '',
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
                'data'          => $aData,
                'flt_default'   => '',
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
 * AmiJobs/Resume configuration admin filter component view.
 *
 * @package    Config_AmiJobs_Resume
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_Resume_FilterViewAdm extends Hyper_AmiJobs_FilterViewAdm{
	/**
 	 * Placeholders array
 	 *
 	 * @var array
 	 */
    protected $aPlaceholders = array(
        '#filter',
            'fio', 'header', 'department', 'datefrom', 'dateto', 'status', 'sticky',
        'filter'
    );
}

/**
 * AmiJobs/Resume configuration admin form component action controller.
 *
 * @package    Config_AmiJobs_Resume
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_Resume_FormAdm extends Hyper_AmiJobs_FormAdm{
}

/**
 * AmiJobs/Resume configuration form component view.
 *
 * @package    Config_AmiJobs_Resume
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_Resume_FormViewAdm extends Hyper_AmiJobs_FormViewAdm{
}

/**
 * AmiJobs/Resume configuration admin list component action controller.
 *
 * @package    Config_AmiJobs_Resume
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_Resume_ListAdm extends Hyper_AmiJobs_ListAdm{
    /**
     * List actions controller resource id
     *
     * @var string
     */
    protected $listActionsResId = 'jobs_resume/list_actions/controller/adm';

    /**
     * List group actions controller resource id
     *
     * @var string
     */
    protected $listGrpActionsResId = 'jobs_resume/list_group_actions/controller/adm';

    /**
     * Initialization.
     *
     * @return JobsResume_ListAdm
     */
    public function init(){
        parent::init();
        $this->dropActions(AMI_ModListAdm::ACTION_COMMON, array('edit', 'delete'));
        $this->addActions(array('edit', 'reply', 'attach', 'print', 'delete'));
        $this->addActionCallback('common', 'edit');
        $this->addActionCallback('common', 'delete');
        $this->addJoinedColumns(array('id', 'header'), 'dp');
        return $this;
    }
}

/**
 * AmiJobs/Resume configuration admin list component view.
 *
 * @package    Config_AmiJobs_Resume
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_Resume_ListViewAdm extends Hyper_AmiJobs_ListViewAdm{
    /**
     * List default elemnts template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#list_header',
            '#flags', 'public',
            '#common', 'common',
            '#columns', 'date_created', 'lname', 'title', 'department', 'phone',  'status', 'columns',
            '#actions', 'front_view', 'edit', 'actions',
        'list_header'
    );

    /**
     * Init columns.
     *
     * @return News_ListViewAdm
     */
    public function init(){
        parent::init();

        $this
            ->removeColumn('header')
            ->removeColumn('announce')
            ->removeColumn('position')
            ->addColumnType('date_created', 'date')
            ->addColumnType('fname', 'hidden')
            ->addColumnType('id_file', 'hidden')
            ->addColumnType('dp_id', 'hidden') // Department id (from joined table)
            ->addColumnType('dp_header', 'hidden') // Department name (from joined table)
            ->addColumn('lname')
            ->addColumn('status')
            ->addColumn('title')
            ->addColumn('department')
            ->addColumn('phone')
            ->setColumnTensility('lname')
            ->setColumnTensility('title')
            ->addSortColumns(
                array(
                    'date', 'fname', 'title', 'department', 'lname'
                )
            );

        $this->setColumnWidth('lname', 'wide');
        $this->setColumnWidth('title', 'extra-wide');

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
        AMI_Event::addHandler('on_list_body_{department}', array($this, 'handleDepartment'), $this->getModId());

        $this->addScriptCode($this->parse('javascript'));

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
     * Prepare Department field.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::get()
     */
    public function handleDepartment($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['aScope']['list_col_value'] = $this->fmtHeader($aEvent['aScope']['dp_header'], $aEvent);
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

    /**
     * Header column formatter.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::formatColumn()
     */
    public function fmtHeader($value, array $aArgs){
        $oTpl = $this->getTemplate();
        return
            $oTpl->parse(
                $this->tplBlockName . ':department_column',
                array(
                    '_mod_id' => $aArgs['aScope']['_mod_id'],
                    'id'      => $aArgs['aScope']['dp_id'],
                    'header'  => $value
                )
            );
    }
}

/**
 * AmiJobs/Resume configuration module admin list actions controller.
 *
 * @package    Config_AmiJobs_Resume
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_Resume_ListActionsAdm extends Hyper_AmiJobs_ListActionsAdm{
}

/**
 * AmiJobs/Resume configuration module admin list group actions controller.
 *
 * @package    Config_AmiJobs_Resume
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiJobs_Resume_ListGroupActionsAdm extends Hyper_AmiJobs_ListGroupActionsAdm{
}
