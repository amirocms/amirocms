<?php
/**
 * AmiVotes/Votes configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiVotes_Votes
 * @version   $Id: AmiVotes_Votes_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiVotes/Votes configuration admin action controller.
 *
 * @package    Config_AmiVotes_Votes
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiVotes_Votes_Adm extends Hyper_AmiVotes_Adm{
    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request
     * @param AMI_Response $oResponse  Response
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        $this->disableExtension('ext_image');
        parent::__construct($oRequest, $oResponse);
    }
}

/**
 * AmiVotes/Votes configuration model.
 *
 * @package    Config_AmiVotes_Votes
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiVotes_Votes_State extends Hyper_AmiVotes_State{
}

/**
 * AmiVotes/Votes configuration admin filter component action controller.
 *
 * @package    Config_AmiVotes_Votes
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiVotes_Votes_FilterAdm extends Hyper_AmiVotes_FilterAdm{
}

/**
 * AmiVotes/Votes configuration item list component filter model.
 *
 * @package    Config_AmiVotes_Votes
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiVotes_Votes_FilterModelAdm extends Hyper_AmiVotes_FilterModelAdm{
}

/**
 * AmiVotes/Votes configuration admin filter component view.
 *
 * @package    Config_AmiVotes_Votes
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiVotes_Votes_FilterViewAdm extends Hyper_AmiVotes_FilterViewAdm{
	/**
 	 * Placeholders array
 	 *
 	 * @var array
 	 */
    protected $aPlaceholders = array(
        '#filter',
            'header', 'datefrom', 'dateto',
        'filter'
    );
}

/**
 * AmiVotes/Votes configuration admin form component action controller.
 *
 * @package    Config_AmiVotes_Votes
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiVotes_Votes_FormAdm extends Hyper_AmiVotes_FormAdm{
}

/**
 * AmiVotes/Votes configuration form component view.
 *
 * @package    Config_AmiVotes_Votes
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiVotes_Votes_FormViewAdm extends Hyper_AmiVotes_FormViewAdm{
    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        parent::init();
        $this->getTemplate()->setBlockLocale($this->tplBlockName, $this->aLocale);
        return $this;
    }
}

/**
 * AmiVotes/Votes configuration admin list component action controller.
 *
 * @package    Config_AmiVotes_Votes
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiVotes_Votes_ListAdm extends Hyper_AmiVotes_ListAdm{

    /**
     * List actions controller resource id
     *
     * @var string
     */
    protected $listActionsResId = 'votes/list_actions/controller/adm';

    /**
     * List group actions controller resource id
     *
     * @var string
     */
    protected $listGrpActionsResId = 'votes/list_group_actions/controller/adm';

    /**
     * Initialization.
     *
     * @return JobsResume_ListAdm
     */
    public function init(){
        parent::init();
        $this->dropActions(AMI_ModListAdm::ACTION_COMMON, array('edit', 'delete'));
        $this->addActions(array('edit', 'result', 'reset_num', 'delete'));
        $this->addActionCallback('common', 'edit');
        $this->addActionCallback('common', 'delete');
        return $this;
    }
}

/**
 * AmiVotes/Votes configuration admin list component view.
 *
 * @package    Config_AmiVotes_Votes
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiVotes_Votes_ListViewAdm extends Hyper_AmiVotes_ListViewAdm{
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
            '#columns', 'position', 'public', 'date_created', 'date', 'header', 'total', 'status', 'columns',
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
            ->removeColumn('announce')
            ->addColumnType('date_created', 'date')
            ->addColumnType('date', 'date')
            ->addColumnType('total', 'int')
            ->addColumn('status')
            ->addSortColumns(
                array(
                    'date', 'date_created', 'header', 'total', 'status'
                )
            );
        $this->setColumnAlign('status', 'center');
        $this->formatColumn(
            'date_created',
            array($this, 'fmtDateTime'),
            array(
                'format' => AMI_Lib_Date::FMT_DATE
            )
        );

        $this->formatColumn(
            'date',
            array($this, 'fmtDateTime'),
            array(
                'format' => AMI_Lib_Date::FMT_DATE
            )
        );
        $this->formatColumn(
            'header',
            array($this, 'fmtTruncate'),
            array(
                'length'       => 150,
                'doSaveWords'  => true,
                'doStripTags'  => true,
                'doHTMLEncode' => true
            )
        );

        AMI_Event::addHandler('on_list_body_{status}', array($this, 'handleStatusCell'), $this->getModId());

        return $this;
    }

    /**
     * Prepare Status field.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::get()
     */
    public function handleStatusCell($name, array $aEvent, $handlerModId, $srcModId){
        $dateEnd = AMI_Lib_Date::formatDateTime($aEvent['aScope']['date'], AMI_Lib_Date::FMT_UNIX);
        $aEvent['aScope']['list_col_value'] = $this->aLocale[($dateEnd > time()) ? 'available' : 'closed'];
        return $aEvent;
    }
}

/**
 * AmiVotes/Votes configuration module admin list actions controller.
 *
 * @package    Config_AmiVotes_Votes
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiVotes_Votes_ListActionsAdm extends Hyper_AmiVotes_ListActionsAdm{
    /**
     * Dispatches 'delete' action.
     *
     * Deletes item.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchDelete($name, array $aEvent, $handlerModId, $srcModId){
        $id = $this->getRequestId();
        $oItem = $this->getItem($id, array('id', 'public'));
        $oItem->delete();
        if($oItem->getId()){
            $statusMsg = 'status_del_fail';
        }else{
            $oQuery = AMI::getSingleton('db')->query(DB_Query::getSnippet('DELETE FROM cms_votevals WHERE id_vote = %s')->q($id));
            $statusMsg = 'status_del';
        }
        $aEvent['oResponse']->addStatusMessage($statusMsg);
        $this->refreshView();
        return $aEvent;
    }
}

/**
 * AmiVotes/Votes configuration module admin list group actions controller.
 *
 * @package    Config_AmiVotes_Votes
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiVotes_Votes_ListGroupActionsAdm extends Hyper_AmiVotes_ListGroupActionsAdm{
}
