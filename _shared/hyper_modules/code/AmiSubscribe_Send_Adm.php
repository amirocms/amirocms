<?php
/**
 * AmiSubscribe/Send configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiSubscribe_Send
 * @version   $Id: AmiSubscribe_Send_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiSubscribe/Send configuration admin action controller.
 *
 * @package    Config_AmiSubscribe_Send
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Send_Adm extends Hyper_AmiSubscribe_Adm{
}

/**
 * AmiSubscribe/Send configuration model.
 *
 * @package    Config_AmiSubscribe_Send
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Send_State extends Hyper_AmiSubscribe_State{
}

/**
 * AmiSubscribe/Send configuration admin filter component action controller.
 *
 * @package    Config_AmiSubscribe_Send
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Send_FilterAdm extends Hyper_AmiSubscribe_FilterAdm{
}

/**
 * AmiSubscribe/Send configuration item list component filter model.
 *
 * @package    Config_AmiSubscribe_Send
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Send_FilterModelAdm extends Hyper_AmiSubscribe_FilterModelAdm{
    /**
     * Common filter fields
     *
     * @var   array
     */
	protected $aCommonFields = array('datefrom', 'dateto');

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
                    'name'          => 'id_topic',
                    'type'          => 'select',
                    'flt_condition' => '=',
                    'not_selected'  => array('id' => '', 'caption' => 'flt_all_topics'),
                    'data'          => $aData
                )
            );
        }
    }
}

/**
 * AmiSubscribe/Send configuration admin filter component view.
 *
 * @package    Config_AmiSubscribe_Send
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Send_FilterViewAdm extends Hyper_AmiSubscribe_FilterViewAdm{
	/**
 	 * Placeholders array
 	 *
 	 * @var array
 	 */
    protected $aPlaceholders = array(
        '#filter',
            'id_topic', 'datefrom', 'dateto',
        'filter'
    );
}

/**
 * AmiSubscribe/Send configuration admin form component action controller.
 *
 * @package    Config_AmiSubscribe_Send
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Send_FormAdm extends Hyper_AmiSubscribe_FormAdm{
}

/**
 * AmiSubscribe/Send configuration form component view.
 *
 * @package    Config_AmiSubscribe_Send
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Send_FormViewAdm extends Hyper_AmiSubscribe_FormViewAdm{
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
 * AmiSubscribe/Send configuration admin list component action controller.
 *
 * @package    Config_AmiSubscribe_Send
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Send_ListAdm extends Hyper_AmiSubscribe_ListAdm{
    /**
     * List actions controller resource id
     *
     * @var string
     */
    protected $listActionsResId = 'subs_send/list_actions/controller/adm';

    /**
     * List group actions controller resource id
     *
     * @var string
     */
    protected $listGrpActionsResId = 'subs_send/list_group_actions/controller/adm';

    /**
     * Initialization.
     *
     * @return AmiSubscribe_Send_ListAdm
     */
    public function init(){
        parent::init();

        $this->dropActions(AMI_ModListAdm::ACTION_COLUMN, array('public'));
        $this->dropActions(self::ACTION_GROUP);

        return $this;
    }

}

/**
 * AmiSubscribe/Send configuration admin list component view.
 *
 * @package    Config_AmiSubscribe_Send
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Send_ListViewAdm extends Hyper_AmiSubscribe_ListViewAdm{
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
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#list_header',
            '#columns', 'topic', 'attach', 'header', 'status', 'send_to', 'date_created', 'columns',
            '#actions', 'edit', 'delete', 'actions',
        'list_header'
    );

    /**
     * History of send items
     *
     * @var array
     */
    protected $aHistoryItems = array();

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiSubscribe_Send_ListViewAdm
     */
    public function init(){
        parent::init();

        $oTpl = AMI::getResource('env/template_sys');
        $this->aHistoryItems = $oTpl->parseLocale('templates/lang/subs_send.lng');

        // Init columns
        $this
            ->removeColumn('announce')
            ->removeColumn('position')
            ->removeColumn('public')
            ->addColumnType('attempts_left', 'hidden')
            ->addColumn('topic')
            ->addColumn('attach')
            ->setColumnWidth('attach', 'extra-narrow')
            ->setColumnAlign('attach', 'center')
            ->addColumn('status')
            ->addColumn('send_to')
            ->setColumnTensility('header', true)
            ->formatColumn('attach', array($this, 'fmtAttachment'))
            ->formatColumn('status', array($this, 'fmtStatus'))
            ->formatColumn('send_to', array($this, 'fmtSendTo'))
            ->addSortColumns(array('topic', 'attach', 'header', 'status', 'send_to'));

        $this->setColumnWidth('topic', 'wide');
        $this->setColumnWidth('attach', 'extra-narrow');
        $this->setColumnWidth('status', 'narrow');

        return $this;
    }

    /**
     * Formats attachment.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     */
    protected function fmtAttachment($value, array $aArgs){
        if($value != ''){
            $aData = array();
            $aData["att_id"] = $aArgs['aScope']['id'];
            $aData["name"] = $value;
            $aData["module"] = $aArgs['aScope']['_mod_id'];
            $value = $this->getTemplate()->parse($this->tplBlockName . ':attach_column', $aData);
        }
        return $value;
    }

    /**
     * Formats status.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     */
    protected function fmtStatus($value, array $aArgs){
        if($aArgs['aScope']['send_to'] != 'host_users' && $aArgs['aScope']['send_to'] != 'local_sites'){
            return $this->aHistoryItems[(($aArgs['aScope']['attempts_left'] == 0) ? 'sent' : 'queued')];
        }else{
            return $this->aHistoryItems['status_saved'];
        }
    }

    /**
     * Formats send.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     */
    protected function fmtSendTo($value, array $aArgs){
        return $this->aHistoryItems[$value];
    }
}

/**
 * AmiSubscribe/Send configuration module admin list actions controller.
 *
 * @package    Config_AmiSubscribe_Send
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Send_ListActionsAdm extends Hyper_AmiSubscribe_ListActionsAdm{
}

/**
 * AmiSubscribe/Send configuration module admin list group actions controller.
 *
 * @package    Config_AmiSubscribe_Send
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Send_ListGroupActionsAdm extends Hyper_AmiSubscribe_ListGroupActionsAdm{
}
