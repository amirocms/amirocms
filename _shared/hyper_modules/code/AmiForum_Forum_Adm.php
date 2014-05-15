<?php
/**
 * AmiForum/Forum configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiForum_Forum
 * @version   $Id: AmiForum_Forum_Adm.php 45507 2013-12-18 12:14:29Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiForum/Forum configuration admin action controller.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_Forum_Adm extends Hyper_AmiForum_Adm{
}

/**
 * AmiForum/Forum configuration model.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_Forum_State extends Hyper_AmiForum_State{
}

/**
 * AmiForum/Forum configuration admin filter component action controller.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_Forum_FilterAdm extends Hyper_AmiForum_FilterAdm{
    /**
     * Discards category extension filter field session storing.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleFilterInit($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent = parent::handleFilterInit($name, $aEvent, $handlerModId, $srcModId);
        $aViewFields = &$aEvent['oFilter']->getViewFieldsByRef();
        foreach($aViewFields as $index => $aField){
            if($aField['name'] !== 'category'){
                continue;
            }
            unset($aViewFields[$index]['session_field']);
            break;
        }
        return $aEvent;
    }
}

/**
 * AmiForum/Forum configuration item list component filter model.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_Forum_FilterModelAdm extends Hyper_AmiForum_FilterModelAdm{
    /**
     * Constructor.
     */
    public function __construct(){
        $index = array_search('header', $this->aCommonFields);
        if($index !== FALSE){
            unset($this->aCommonFields[$index]);
        }
        parent::__construct();

        $oRequest = AMI::getSingleton('env/request');
        $inThread = (bool)$oRequest->get('id_thread', FALSE);

        $this->addViewField(
            array(
                'name'          => 'topics_only',
                'type'          => 'hidden',
                'flt_default'   => ''
            )
        );
        $this->addViewField(
            array(
                'name'          => 'id_thread',
                'type'          => 'hidden',
                'flt_default'   => '',
                'flt_condition' => '=',
                'act_as_int'    => TRUE,
                'disable_empty' => TRUE
            )
        );
        $this->addViewField(
            array(
                'name'          => 'id_message',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => ''
            )
        );
        $this->addViewField(
            array(
                'name'          => 'search',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'name'
            )
        );
        if(!$inThread){
            $this->addViewField(
                array(
                    'name'          => 'locked',
                    'type'          => 'checkbox',
                    'flt_default'   => '',
                    'flt_condition' => '=',
                    'act_as_int'    => TRUE,
                    'disable_empty' => TRUE
                )
            );
        }
        $this->addViewField(
            array(
                'name'          => 'flt_author',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'author'
            )
        );
        $this->addViewField(
            array(
                'name'          => 'ip',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => ''
            )
        );
        if(!$inThread){
            $this->addViewField(
                array(
                    'name'          => 'unanswered',
                    'type'          => 'checkbox',
                    'flt_type'      => 'hidden',
                    'flt_default'   => ''
                )
            );
        }

        if($inThread){
            $this->dropViewFields(array('sticky'));
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
        switch($field){
            case 'topics_only':
                if($aData['value']){
                    $sql = " AND (`i`.`id` = `i`.`id_thread`)";
                    $aData['forceSQL'] = $sql;
                }
                break;
            case 'search':
                if($aData['value'] !== ''){
                    $val = $this->prepareSqlField('topic', $aData['value'], 'text');
                    $sql = " AND (`i`.`topic` LIKE '%" . $val. "%' OR `i`.`message` LIKE '%" . $val. "%')";
                    $aData['forceSQL'] = $sql;
                }
                break;
            case 'unanswered':
                if($aData['value']){
                    $sql = " AND `i`.`msg_answers` = 0";
                    $aData['forceSQL'] = $sql;
                }
                break;
            case 'ip':
                if($aData['value'] !== ''){
                    $val = $this->prepareSqlField('topic', $aData['value'], 'text');
                    $sql = " AND `i`.`ip` = INET_ATON('" . $val. "')";
                    $aData['forceSQL'] = $sql;
                }
                break;

        }
        return $aData;
    }
}

/**
 * AmiForum/Forum configuration admin filter component view.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_Forum_FilterViewAdm extends Hyper_AmiForum_FilterViewAdm{
    /**
     * Section (category) id
     *
     * @var int
     */
    protected $sectionId;

    /**
     * Thread id
     *
     * @var int
     */
    protected $threadId;

    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();
        $this->addPlaceholders(array('id' => 'sticky.before'));
        $this->addScriptFile('_admin/' . $GLOBALS['CURRENT_SKIN_PATH'] . '_js/forum_filter.js');

        $oRequest = AMI::getSingleton('env/request');
        $this->categoryId = (int)$oRequest->get('category', 0);
        $this->threadId = (int)$oRequest->get('id_thread', 0);
    }

    /**
     * Sets path scope variable displaying under filter form.
     *
     * @return void
     */
    protected function setPath(){
        if($this->categoryId || $this->threadId){
            $aPath = array();
            $aPath[] = $this->parse('path_all');
            if($this->categoryId){
                $aPath[] = $this->parse('path_section', $this->aScope + array('section_id' => $this->categoryId));
            }
            if($this->threadId){
                $oItem = AMI::getResourceModel($this->getModId() . '/table')->find($this->threadId, array('id', 'topic'));
                if($oItem){
                    $aPath[] = $this->parse('path_topic', array('topic' => $oItem->topic));
                }
            }
            $this->aScope['path'] = implode($this->parse('path_splitter'), $aPath);
        }
    }
}

/**
 * AmiForum/Forum configuration admin form component action controller.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_Forum_FormAdm extends Hyper_AmiForum_FormAdm{
}

/**
 * AmiForum/Forum configuration form component view.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_Forum_FormViewAdm extends Hyper_AmiForum_FormViewAdm{
}

/**
 * AmiForum/Forum configuration admin list component action controller.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_Forum_ListAdm extends Hyper_AmiForum_ListAdm{
    /**
     * Default list order
     *
     * @var array
     * @amidev
     */
    protected $aDefaultOrder = array(
        'col' => 'msg_date',
        'dir' => 'desc'
    );

    /**
     * Thread id
     *
     * @var int
     */
    protected $threadId;

    /**
     * Flag specifying to hide category column on displaing of topic messages list
     *
     * @var bool
     */
    protected $hideCategoryColumn;

    /**
     * Initialization.
     *
     * @return AmiForum_Forum_ListAdm
     */
    public function init(){
        $oRequest = AMI::getSingleton('env/request');
        $oCookies = AMI::getSingleton('env/cookie');

        $sortColumn = $oRequest->get('sort_column', $oCookies->get($this->getSerialId() . '_order_column', null));
        if($oRequest->get('id_thread', 0)){
            if($sortColumn === 'msg_date'){
                $oRequest->set('sort_column', 'date_created');
                $oRequest->set('sort_dir', 'desc');
            }
        }else{
            if($sortColumn === 'date_created'){
                $oRequest->set('sort_column', 'msg_date');
                $oRequest->set('sort_dir', 'desc');
            }
        }

        $this->addActions(array('reply'));
        $this->threadId = (int)$oRequest->get('id_thread', 0);

        parent::init();

        $this->hideCategoryColumn = $this->threadId || $oRequest->get('category', 0);
        $hidePublicColumn = !$oRequest->get('id_thread', 0);
        AMI_Event::addHandler('on_list_columns', array($this, 'handleListColumns'), $this->getModId(), AMI_Event::PRIORITY_LOW);
        if($hidePublicColumn){
            $this->dropActions(AMI_ModListAdm::ACTION_COLUMN, array('public'));
        }
        if($this->threadId){
            $this->dropActions(AMI_ModListAdm::ACTION_GROUP, array('gen_sublink', 'gen_html_meta', 'gen_html_meta_force', 'index_details', 'no_index_details'));
        }else{
            $this->dropActions(AMI_ModListAdm::ACTION_GROUP, array('public', 'unpublic'));
            $this->addGroupActions(
                array(
                    array('lock', 'common_section'),
                    array('unlock', 'common_section')
                )
            );
        }

        return $this;
    }

    /**
     * Drops category column.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView_JSON::get()
     */
    public function handleListColumns($name, array $aEvent, $handlerModId, $srcModId){
        // hack manipulating category columns
        $aEvent['oView']->addColumnType('cat_id', 'hidden');
        if($this->hideCategoryColumn){
            $aEvent['oView']->removeColumn('cat_header');
        }
        return $aEvent;
    }

    /**
     * Prepares order column.
     *
     * @param  string $name          Event name
     * @param  array $aEvent         Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleSortColumn($name, array $aEvent, $handlerModId, $srcModId){
        $oRequest = AMI::getSingleton('env/request');
        $oCookies = AMI::getSingleton('env/cookie');
        $sortColumn = $oRequest->get('sort_column', $oCookies->get('order_column', null));
        if($this->threadId && $sortColumn == 'msg_date'){
            $oRequest->set('sort_column', 'date_created');
            $oRequest->set('sort_dir', 'desc');
        }elseif(!$this->threadId && !$oRequest->get('sort_column', FALSE)){
            $oRequest->set('sort_column', 'msg_date');
            $oRequest->set('sort_dir', 'desc');
        }
        $aEvent = parent::handleSortColumn($name, $aEvent, $handlerModId, $srcModId);
        return $aEvent;
    }
}

/**
 * AmiForum/Forum configuration admin list component view.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_Forum_ListViewAdm extends Hyper_AmiForum_ListViewAdm{
    /**
     * List default elements template (placeholders)
     *
     * @var array
     */
    protected $aPlaceholders = array(
        '#list_header',
            '#flags', 'position', 'public', 'flags',
            '#common', 'cat_header', 'common',
            '#columns', 'topic', 'message', 'author', 'msg_date', 'msg_answers', 'date_created', 'columns',
            '#actions', 'actions',
        'list_header'
    );

    /**
     * Service object
     *
     * @var AmiCommonMessage_Service
     */
    protected $oService;

    /**
     * Thread id
     *
     * @var int
     */
    protected $threadId;

    /**
     * Flag specifying to cut seconds from date format
     *
     * @var bool
     */
    protected $doCutSeconds = TRUE;

    /**
     * Array storing source date formats
     *
     * @var array
     */
    protected $aSourceDateFormats = array();

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiForum_Forum_ListViewAdm
     */
    public function init(){
        parent::init();

        $this->oService = AmiCommonMessage_Service::getInstance(array($this->getModId()));

        $oRequest = AMI::getSingleton('env/request');
        $this->threadId = (int)$oRequest->get('id_thread', 0);
        $hideCategoryColumn = $this->threadId || $oRequest->get('category', 0);
        $hidePublicColumn = !$oRequest->get('id_thread', 0);
        if($hideCategoryColumn){
            $this->removeColumn('cat_header');
        }
        if($hidePublicColumn){
            $this->removeColumn('public');
        }

        $this->removeColumn('position');
        $this->removeColumn('header');
        $this->removeColumn('announce');
        $this
            ->addColumn('author')
            ->addColumn('msg_date')
            ->setColumnTensility('msg_date')
            ->addColumnType('msg_answers', 'int')
            ->setColumnWidth('msg_answers', 'extra-narrow')
            ->addColumnType('date_created', 'datetime')
            ->addColumnType('msg_id', 'hidden')
            ->addColumnType('id_thread', 'hidden')
            ->addColumnType('id_member', 'none')
            ->addColumnType('msg_topic', 'none')
            ->addColumnType('msg_id_member', 'none')
            ->addColumnType('msg_author', 'none')
            ->addColumnType('locked', 'none')

            ->setColumnAlign('msg_answers', 'center');


        foreach(array('cat_header', 'topic', 'msg_date', 'date_created') as $column){
            $this->setColumnClass($column, 'td_small_text');
        }

        if($this->threadId){
            $this->removeColumn('msg_date');
            $this->removeColumn('msg_answers');
            $this->addColumn('message', 'columns.begin');
        }else{
            $this
                ->addColumn('topic')
                ->setColumnTensility('topic');
            if($hideCategoryColumn){
                $this->addColumn('message', 'topic.after');
            }
        }

        $this->setColumnTensility('message');

        $this->addSortColumns(array('topic', 'author', 'msg_date', 'msg_answers'));

        $this->formatColumn('topic', array($this, 'fmtTopic'));

        $this->formatColumn('message', array($this->oService, 'fmtSmilesToTags'));
        $this->formatColumn('message', array($this, 'fmtTruncate'), array('length' => 1024, 'doStripTags' => TRUE, 'doSaveWords' => AMI::getOption('core', 'strip_strings_by_words'), 'doHTMLEncode' => false));
        $this->formatColumn('author', array($this, 'fmtAuthor'));
        $this->formatColumn('msg_date', array($this, 'fmtLastMessage'));
        $this->formatColumn('msg_answers', array($this, 'fmtAnswers'));
        $this->formatColumn(
            'date_created',
            array($this, 'fmtHumanDateTime'),
            array('format' => AMI_Lib_Date::FMT_BOTH)
        );

        // Patch authors
        AMI_Event::addHandler('on_list_body_row', array($this, 'handleListBodyRow'), $this->getModId());

        // cut seconds
        AMI_Event::addHandler('on_get_date_format', array($this, 'handleDateFormat'), AMI_Event::MOD_ANY);

        $this->addScriptFile('_admin/' . $GLOBALS['CURRENT_SKIN_PATH'] . '_js/forum_list.js');

        return $this;
    }

    /**
     * Patches authors.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListBodyRow($name, array $aEvent, $handlerModId, $srcModId){
        foreach(array('author', 'msg_author') as $key){
            $this->oService->patchAuthor($aEvent['aScope'][$key]);
        }
        $this->oService->patchSysUser($aEvent['aScope']);

        return $aEvent;
    }

    /**
     * Event handler.
     *
     * Cuts seconds from date column.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleDateFormat($name, array $aEvent, $handlerModId, $srcModId){
        foreach(array_keys($aEvent['date_format']) as $locale){
            foreach(array('PHP', 'PHP_TIME') as $format){
                $fmt = $aEvent['date_format'][$locale][$format];
                if(!isset($this->aSourceDateFormats[$locale])){
                    $this->aSourceDateFormats[$locale] = array();
                }
                if(!isset($this->aSourceDateFormats[$locale][$format])){
                    $this->aSourceDateFormats[$locale][$format] = $fmt;
                }
                $aEvent['date_format'][$locale][$format] =
                    $this->doCutSeconds
                        ?
                            preg_replace(
                                array(
                                    '/s/',
                                    '/\:(\s|$)/'
                                ),
                                '',
                                $fmt
                            )
                        : $this->aSourceDateFormats[$locale][$format];
            }
        }
        return $aEvent;
    }

    /**
     * Event handler.
     *
     * Handling action cell to disallow replying in locked topics.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleActionCell($name, array $aEvent, $handlerModId, $srcModId){
        if(!empty($aEvent['aScope']['locked'])){
            unset($aEvent['aScope']['_action_col']['reply']);
        }
        $aEvent = parent::handleActionCell($name, $aEvent, $handlerModId, $srcModId);
        return $aEvent;
    }

    /**
     * Topic column formatter.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtTopic($value, array $aArgs){
        return
            $this->parse(
                'topic',
                array(
                    'id_thread' => $aArgs['oItem']->id_thread,
                    'cat_id'    => isset($aArgs['oItem']->cat_id) ? $aArgs['oItem']->cat_id : 0,
                    'topic'     => $value
                )
            );
    }

    /**
     * Author column formatter.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::handleFormatCell()
     * @todo   Use templater
     */
    protected function fmtAuthor($value, array $aArgs){
        return
            $aArgs['oItem']->id_member
            ?
                $this->parse(
                    'author',
                    array(
                        'url'       => $GLOBALS['Core']->getAdminLink('members'),
                        'author'    => $value,
                        'id_member' => $aArgs['oItem']->id_member
                    )
                )
            : $value;
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
        if($aArgs['oItem']->id_thread == $aArgs['oItem']->id){
            $aScope = $aArgs['oItem']->getData();
            $this->doCutSeconds = FALSE;
            $aScope['msg_date'] = $this->fmtHumanDateTime($aScope['msg_date'], array());
            $this->doCutSeconds = TRUE;
            $aScope['members_link'] = $GLOBALS['Core']->getAdminLink('members');
            $result = $this->parse('last_message', $aScope);
        }else{
            $result = '';
        }
        return $result;
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
        return
            $aArgs['oItem']->id_thread == $aArgs['oItem']->id
            ?
                $this->parse(
                    'topic',
                    array(
                        'id_thread' => $aArgs['oItem']->id_thread,
                        'cat_id'    => empty($aArgs['oItem']->cat_id) ? 0 : $aArgs['oItem']->cat_id,
                        'topic'     => $value
                    )
                )
            : '';
    }

/*
    public function get(){###
        AMI::getSingleton('db')->displayQueries();
        $res = parent::get();
        AMI::getSingleton('db')->displayQueries(FALSE);
        return $res;
    }
*/
}

/**
 * AmiForum/Forum configuration module admin list actions controller.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_Forum_ListActionsAdm extends Hyper_AmiForum_ListActionsAdm{
}

/**
 * AmiForum/Forum configuration module admin list group actions controller.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_Forum_ListGroupActionsAdm extends Hyper_AmiForum_ListGroupActionsAdm{
}
