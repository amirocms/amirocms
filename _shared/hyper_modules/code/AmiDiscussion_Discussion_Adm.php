<?php
/**
 * AmiDiscussion/Discussion configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiDiscussion_Discussion
 * @version   $Id: AmiDiscussion_Discussion_Adm.php 48796 2014-03-18 11:27:14Z Kolesnikov Artem $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiDiscussion/Discussion configuration admin action controller.
 *
 * @package    Config_AmiDiscussion_Discussion
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Discussion_Adm extends Hyper_AmiDiscussion_Adm{
}

/**
 * AmiDiscussion/Discussion configuration model.
 *
 * @package    Config_AmiDiscussion_Discussion
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Discussion_State extends Hyper_AmiDiscussion_State{
}

/**
 * AmiDiscussion/Discussion configuration admin filter component action controller.
 *
 * @package    Config_AmiDiscussion_Discussion
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Discussion_FilterAdm extends Hyper_AmiDiscussion_FilterAdm{
}

/**
 * AmiDiscussion/Discussion configuration item list component filter model.
 *
 * @package    Config_AmiDiscussion_Discussion
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Discussion_FilterModelAdm extends Hyper_AmiDiscussion_FilterModelAdm{
    /**
     * Service class
     *
     * @var AmiDiscussion_Discussion_Service
     */
    protected $oService;

    /**
     * Constructor.
     *
     * @todo Avoid hardcoded 'discussion'
     */
    public function __construct(){
        $this->oService = AMI::getSingleton('ext_disucssion/service');

        $oRequest = AMI::getSingleton('env/request');

        $extModId = $oRequest->get('ext_module', FALSE);
        if($extModId !== FALSE){
            $this->useTree = AMI::issetAndTrueOption($extModId, 'use_tree_view');
        }

        $extModItemId = (int)$oRequest->get('id_ext_module', 0);
        $this->displayAuthorAndIP = (bool)$extModItemId;
        $isPopup = $oRequest->get('popup', FALSE);
        if($isPopup && !$oRequest->get('flt_id_parent', 0)){
            $oRequest->set('flt_id_parent', (int)$this->oService->getMainParentId('discussion', $extModId, $extModItemId)); // $this->getModId() returns empty string because of constructor
        }

        parent::__construct();

        if($isPopup){
            $this->addViewField(
                array(
                    'name'          => 'popup',
                    'type'          => 'hidden',
                    'flt_type'      => 'hidden',
                    'flt_default'   => ''
                )
            );
            $this->addViewField(
                array(
                    'name'          => 'ext_module',
                    'type'          => 'hidden',
                    'flt_type'      => 'hidden',
                    'flt_default'   => $extModId,
                    'flt_condition' => '=',
                    'flt_column'    => 'ext_module'
                )
            );
        }else{
            $aAllowedModules = $this->oService->getAllowedModules();
            $aSelect = array();
            foreach($aAllowedModules as $modId => $caption){
                $aSelect[] = array(
                    'name'  => $caption,
                    'value' => $modId
                );
            }
            $this->addViewField(
                array(
                    'name'          => 'ext_module',
                    'type'          => 'select',
                    'flt_type'      => 'select',
                    'flt_condition' => '=',
                    'flt_column'    => 'ext_module',
                    'flt_default'   => '',
                    'data'          => $aSelect,
                    'not_selected'  => array('id' => '', 'caption' => 'all'),
                    'position'      => 'filter.begin'
                )
            );
        }

        $this->addViewField(
            array(
                'name'          => 'id_ext_module',
                'type'          => 'hidden',
                'flt_default'   => $isPopup ? $oRequest->get('id_ext_module') : '0',
                'flt_condition' => '=',
                'flt_column'    => 'id_ext_module',
                'act_as_int'    => TRUE,
                'disable_empty' => TRUE
            )
        );
    }

    /**
     * Patches filter conditions.
     *
     * @param string $field  Field name
     * @param array  $aData  Filter data
     * @return array
     * @amidev Temporary
     */
    protected function processFieldData($field, array $aData){
        $aData = parent::processFieldData($field, $aData);
        switch($field){
            case 'flt_id_parent':
                unset($aData['skip']);
                break;
        }
        return $aData;
    }
}

/**
 * AmiDiscussion/Discussion configuration admin filter component view.
 *
 * @package    Config_AmiDiscussion_Discussion
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Discussion_FilterViewAdm extends Hyper_AmiDiscussion_FilterViewAdm{
    /**
     * Initialize fields.
     *
     * @return AmiDiscussion_Discussion_FilterViewAdm
     * @todo   Avoid hardcoded 'discussion'
     */
    public function init(){
        $oService = AMI::getSingleton('ext_disucssion/service');
        $oRequest = AMI::getSingleton('env/request');
        if($oRequest->get('popup', FALSE) && !$oRequest->get('flt_id_parent', 0)){
            $oRequest->set('flt_id_parent', (int)$oService->getMainParentId('discussion', $extModId, $extModItemId)); // $this->getModId() returns empty string because of constructor
        }
        return parent::init();
    }

    /**
     * Sets path scope variable displaying under filter form.
     *
     * @return void
     */
    protected function setPath(){
        $oRequest = AMI::getSingleton('env/request');
        $modId = $oRequest->get('ext_module', FALSE);
        $modItemId = (int)$oRequest->get('id_ext_module', 0);
        $this->aScope['path'] = '';
        if(!$oRequest->get('popup', FALSE)){
            if($modId){
                $this->aScope['path'] =
                    $this->parse(
                        'path_all_modules',
                        array(
                            'ext_module' => $modId,
                            'caption'    => AMI::getSingleton('ext_disucssion/service')->getModCaption($modId)
                        )
                    );
            }
            if($modItemId){
                $oItem =
                    AMI::getResourceModel($this->getModId() . '/table')
                        ->findByFields(
                            array(
                                'ext_module'    => $modId,
                                'id_ext_module' => $modItemId,
                                'id_parent'     => 0
                            ),
                            array('id', 'message')
                        );
                if($oItem->id){
                    $this->aScope['path'] .=
                        $this->parse(
                            'path_mod_item',
                            array(
                                'url'     => AMI::getSingleton('core')->getAdminLink($modId) . 'id=' . $modItemId . '&action=edit#mid=' . $modId . '&id=' . $modId,
                                'caption' => $oItem->message
                            )
                        );
                }
            }
        }
        if($oRequest->get('flt_parent_level', 0)){
            $this->aScope['path'] .= $this->parse('path_reset_parent', array('popup' =>  $oRequest->get('popup', FALSE)));
        }
    }
}

/**
 * AmiDiscussion/Discussion configuration admin form component action controller.
 *
 * @package    Config_AmiDiscussion_Discussion
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Discussion_FormAdm extends Hyper_AmiDiscussion_FormAdm{
}

/**
 * AmiDiscussion/Discussion configuration form component view.
 *
 * @package    Config_AmiDiscussion_Discussion
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Discussion_FormViewAdm extends Hyper_AmiDiscussion_FormViewAdm{
}

/**
 * AmiDiscussion/Discussion configuration admin list component action controller.
 *
 * @package    Config_AmiDiscussion_Discussion
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Discussion_ListAdm extends Hyper_AmiDiscussion_ListAdm{
    /**
     * Flag specifying to display replay/edit actions.
     *
     * @var bool
     */
    protected $displayReplyEdit = FALSE;

    /**
     * Initialization.
     *
     * @return Hyper_AmiDiscussion_ListAdm
     */
    public function init(){
        $oRequest = AMI::getSingleton('env/request');
        $oCookies = AMI::getSingleton('env/cookie');
        $sortColumn = $oRequest->get('sort_column', $oCookies->get($this->getSerialId() . '_order_column', null));
        if($oRequest->get('id_ext_module', FALSE)){
            $this->displayPublic = TRUE;
            $this->displayReplyEdit = TRUE;
            if(($sortColumn === 'msg_date') || ($sortColumn === 'last_msg_date')){
                $oRequest->set('sort_column', 'date_created');
            }
        }else{
            $this->addActions(array('add_subitem'));
            $this->getModel()->setActiveDependence('dd');
            $this->addJoinedColumns(array('author', 'id_member', 'message'), 'dd');
            if(($sortColumn === 'date_created') || ($sortColumn === 'msg_date')){
                $oRequest->set('sort_column', 'last_msg_date');
            }
        }
        return parent::init();
    }
}

/**
 * AmiDiscussion/Discussion configuration admin list component view.
 *
 * @package    Config_AmiDiscussion_Discussion
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Discussion_ListViewAdm extends Hyper_AmiDiscussion_ListViewAdm{
    /**
     * List default elements template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#list_header',
            '#flags', 'position', 'public', 'flags',
            '#columns', 'columns',
            '#actions', 'front_view', 'edit', 'actions',
        'list_header'
    );

    /**
     * Flag specifying to cut seconds from date format
     *
     * @var bool
     */
    protected $doCutSeconds = FALSE;

    /**
     * Service class
     *
     * @var AmiDiscussion_Discussion_Service
     */
    protected $oDiscussionService;

    /**
     * External module id
     *
     * @var int
     */
    protected $extModId;

    /**
     * External module item id
     *
     * @var int
     */
    protected $extModItemId;

    /**
     * Comment parent id
     *
     * @var int
     */
    protected $parentId;

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiDiscussion_Discussion_ListViewAdm
     */
    public function init(){
        parent::init();

        $this->oDiscussionService = AMI::getSingleton('ext_disucssion/service');

        $oRequest = AMI::getSingleton('env/request');

        $this->extModId = $oRequest->get('ext_module', FALSE);
        $this->extModItemId = $oRequest->get('id_ext_module', FALSE);
        $this->parentId = $oRequest->get('flt_parent_id', FALSE);



        #$this->extModId = 0;$this->extModItemId = 1;$this->parentId = 0;### all colimns

        $this
            ->addColumnType('id', 'hidden')
            ->addColumnType('src_ext_module', 'hidden');

        if($this->extModId){
            $this->addColumnType('ext_module', 'hidden');
        }else{
            $this
                ->addColumn('ext_module')
                ->setColumnWidth('ext_module', 'wide')
                ->formatColumn(
                    'ext_module',
                    array($this, 'fmtModule')
                );
        }
        if(!$this->extModItemId){
            $this
                ->addColumn('header') // ext_module_item_title
                ->setColumnTensility('header')
                ->addColumnType('message', 'none') // will replace header column
                ->addColumn('last_msg_date')
                ->setColumnTensility('last_msg_date')
                ->addColumn('dd_message')
                ->setColumnTensility('dd_message')
                ->addColumnType('msg_id', 'none')
                ->addColumnType('last_msg_id', 'none')
                ->addColumnType('dd_author', 'none')
                ->addColumnType('dd_id_member', 'none')
                ->addColumnType('msg_date', 'none')
                ->addColumnType('msg_id_member', 'none')
                ->addColumnType('msg_author', 'none');

            $this->formatColumn('header', array($this, 'fmtHeader'));
            $this->formatColumn('last_msg_date', array($this, 'fmtLastMessage'));
            $this->formatColumn('dd_message', array($this, 'fmtStripTags'));

            $this->addSortColumns(array('last_msg_date', 'msg_date'));

            $this->orderColumn = 'last_msg_date';
            $this->orderDirection = 'desc';
        }

        if($this->extModItemId){
            $this
                ->addColumn('message')
                ->addColumn('author')
                ->addColumn('date_created', 'author.after')
                ->addColumnType('date_created', 'datetime')
                ->addColumnType('id_member', 'none')
                ->setColumnTensility('message');

            $this->formatColumn('message', array($this->oService, 'fmtSmilesToTags'));
            $this->formatColumn('message', array($this, 'fmtTruncate'), array('length' => 1024, 'doStripTags' => TRUE, 'doSaveWords' => AMI::getOption('core', 'strip_strings_by_words'), 'doHTMLEncode' => false));
            $this->formatColumn('date_created', array($this, 'fmtDateTime'));

            $this->addSortColumns(array('public', 'author', 'date_created', 'last_msg_date'));
        }

        $this
            ->addColumnType('id_parent', 'none')
            ->addColumnType('id_ext_module', 'hidden')
            ->addColumnType('count_public_children', 'none')
            ->addColumn('count_children')
            ->setColumnAlign('count_children', 'center');

        $this->addSortColumns(array('count_children'));

        $this->formatColumn('count_children', array($this, 'fmtAnswers'));


        /*
        if(AMI::getOption($this->getModId(), 'use_tree_view')){
            $this
                ->addColumn('count_children')
                ->setColumnAlign('count_children', 'center');
        }
         */
        foreach(array('ext_module', 'msg_date', 'last_msg_date', 'dd_message', 'count_children', 'message', 'author', 'date_created') as $column){
            $this->setColumnClass($column, 'td_small_text');
        }

        return $this;
    }

    /**
     * Column formatter.
     *
     * Converts module id to its caption.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return string
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtModule($value, array $aArgs){
        return
            $value
                ? $this->parse(
                    'module',
                    array(
                        'ext_module' => $value,
                        'caption'    => $this->oDiscussionService->getModCaption($value)
                    )
                )
                : '';
    }

    /**
     * Answers column formatter.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtAnswers($value, array $aArgs){
        $aScope = array('caption' => $value) + $aArgs['oItem']->getData();
        if($this->extModItemId){
            $aScope['id_parent'] = $aScope['id'];
        }

        return $aScope['count_children'] ? $this->parse('answers', $aScope) : '';
    }

    /**
     * Header column formatter.
     *
     * Replaces header by message field value.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return string
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtHeader($value, array $aArgs){
        $aScope = array('caption' => $aArgs['oItem']->message) + $aArgs['oItem']->getData();
        if($this->extModItemId){
            $aScope['id_parent'] = $aScope['id'];
        }
        return $this->parse('header', $aScope);
    }

    /**
     * Last message column formatter.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtLastMessage($value, array $aArgs){
        if($aArgs['oItem']->last_msg_id){
            $aScope = $aArgs['oItem']->getData();
            $this->doCutSeconds = TRUE;
            $aScope['msg_date'] = $this->fmtHumanDateTime($aScope['last_msg_date'], array());
            $aScope['msg_id_member'] = $aScope['dd_id_member'];
            $aScope['msg_author'] = $aScope['dd_author'];
            $this->doCutSeconds = FALSE;
            $aScope['members_link'] = AMI::getSingleton('core')->getAdminLink('members');
            $result = $this->parse('msg_date', $aScope);
        }else{
            $result = '';
        }
        return $result;
    }
}

/**
 * AmiDiscussion/Discussion configuration module admin list actions controller.
 *
 * @package    Config_AmiDiscussion_Discussion
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Discussion_ListActionsAdm extends Hyper_AmiDiscussion_ListActionsAdm{
}

/**
 * AmiDiscussion/Discussion configuration module admin list group actions controller.
 *
 * @package    Config_AmiDiscussion_Discussion
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Discussion_ListGroupActionsAdm extends Hyper_AmiDiscussion_ListGroupActionsAdm{
}
