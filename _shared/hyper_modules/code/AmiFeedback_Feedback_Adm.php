<?php
/**
 * AmiFeedback/Feedback configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiFeedback_Feedback
 * @version   $Id: AmiFeedback_Feedback_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiFeedback/Feedback configuration admin action controller.
 *
 * @package    Config_AmiFeedback_Feedback
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiFeedback_Feedback_Adm extends Hyper_AmiFeedback_Adm{
}

/**
 * AmiFeedback/Feedback configuration model.
 *
 * @package    Config_AmiFeedback_Feedback
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiFeedback_Feedback_State extends Hyper_AmiFeedback_State{
}

/**
 * AmiFeedback/Feedback configuration admin filter component action controller.
 *
 * @package    Config_AmiFeedback_Feedback
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiFeedback_Feedback_FilterAdm extends Hyper_AmiFeedback_FilterAdm{
}

/**
 * AmiFeedback/Feedback configuration item list component filter model.
 *
 * @package    Config_AmiFeedback_Feedback
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiFeedback_Feedback_FilterModelAdm extends Hyper_AmiFeedback_FilterModelAdm{
    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();

        $this->addViewField(
            array(
                'name'          => 'author',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'lastname',
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
        if(($field == 'author') && ($aData['value'])){
            $val = $this->prepareSqlField('lastname', $aData['value'], 'text');
            $sql = " AND (CONCAT(i.`lastname`, ' ', i.`firstname`) LIKE '%" . $val . "%' OR i.`email` LIKE '%" . $val . "%') ";
            $aData['forceSQL'] = $sql;
        }
        return $aData;
    }
}

/**
 * AmiFeedback/Feedback configuration admin filter component view.
 *
 * @package    Config_AmiFeedback_Feedback
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiFeedback_Feedback_FilterViewAdm extends Hyper_AmiFeedback_FilterViewAdm{
	/**
 	 * Placeholders array
 	 *
 	 * @var array
 	 */
    protected $aPlaceholders = array(
        '#filter',
            'id_page', 'datefrom', 'dateto', 'author', 'header',
        'filter'
    );
}

/**
 * AmiFeedback/Feedback configuration admin form component action controller.
 *
 * @package    Config_AmiFeedback_Feedback
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiFeedback_Feedback_FormAdm extends Hyper_AmiFeedback_FormAdm{
}

/**
 * AmiFeedback/Feedback configuration form component view.
 *
 * @package    Config_AmiFeedback_Feedback
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiFeedback_Feedback_FormViewAdm extends Hyper_AmiFeedback_FormViewAdm{
}

/**
 * AmiFeedback/Feedback configuration admin list component action controller.
 *
 * @package    Config_AmiFeedback_Feedback
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiFeedback_Feedback_ListAdm extends Hyper_AmiFeedback_ListAdm{
    /**
     * Initialization.
     *
     * @return JobsResume_ListAdm
     */
    public function init(){
        parent::init();
        $this->dropActions(AMI_ModListAdm::ACTION_COMMON, array('edit', 'delete'));
        $this->addActions(array('show', 'delete'));
        $this->addActionCallback('common', 'delete');
        $this->dropActions(AMI_ModListAdm::ACTION_GROUP, array('public', 'unpublic', 'gen_sublink', 'gen_html_meta', 'gen_html_meta_force', 'index_details', 'no_index_details'));
        $this->dropActions(AMI_ModListAdm::ACTION_COLUMN, array('public'));
        return $this;
    }
}

/**
 * AmiFeedback/Feedback configuration admin list component view.
 *
 * @package    Config_AmiFeedback_Feedback
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiFeedback_Feedback_ListViewAdm extends Hyper_AmiFeedback_ListViewAdm{
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
            '#columns', 'date_created', 'header', 'lastname', 'email', 'id_page', 'columns',
            '#actions', 'actions',
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
            ->removeColumn('announce')
            ->removeColumn('position')
            ->removeColumn('public')
            ->addColumnType('date_created', 'date')
            ->addColumnType('firstname', 'hidden')
            ->addColumn('lastname')
            ->addColumn('email')
            ->setColumnTensility('header', true)
            ->addSortColumns(
                array(
                    'date_created', 'lastname', 'email'
                )
            );

        $this->setColumnWidth('lastname', 'wide');
        $this->setColumnWidth('email', 'wide');

        $this->formatColumn(
            'date_created',
            array($this, 'fmtDateTime'),
            array(
                'format' => AMI_Lib_Date::FMT_DATE
            )
        );

        AMI_Event::addHandler('on_list_body_{lastname}', array($this, 'handleFIOCell'), $this->getModId());

        return $this;
    }

    /**
     * Prepare FIO field.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::get()
     */
    public function handleFIOCell($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['aScope']['list_col_value'] = $aEvent['aScope']['lastname'].' '.$aEvent['aScope']['firstname'];
        return $aEvent;
    }
}

/**
 * AmiFeedback/Feedback configuration module admin list actions controller.
 *
 * @package    Config_AmiFeedback_Feedback
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiFeedback_Feedback_ListActionsAdm extends Hyper_AmiFeedback_ListActionsAdm{
}

/**
 * AmiFeedback/Feedback configuration module admin list group actions controller.
 *
 * @package    Config_AmiFeedback_Feedback
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiFeedback_Feedback_ListGroupActionsAdm extends Hyper_AmiFeedback_ListGroupActionsAdm{
}
