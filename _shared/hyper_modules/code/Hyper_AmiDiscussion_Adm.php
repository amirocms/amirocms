<?php
/**
 * AmiDiscussion hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiDiscussion
 * @version   $Id: Hyper_AmiDiscussion_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiDiscussion hypermodule admin action controller.
 *
 * @package    Hyper_AmiDiscussion
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDiscussion_Adm extends AMI_Mod{
    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request object
     * @param AMI_Response $oResponse  Response object
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        parent::__construct($oRequest, $oResponse);
        $this->addComponents(array('filter', 'list', 'form'));
    }
}

/**
 * AmiDiscussion hypermodule model.
 *
 * @package    Hyper_AmiDiscussion
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDiscussion_State extends AMI_ModState{
}

/**
 * AmiDiscussion hypermodule admin filter component action controller.
 *
 * @package    Hyper_AmiDiscussion
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDiscussion_FilterAdm extends AMI_ModFilter{
}

/**
 * AmiDiscussion hypermodule item list component filter model.
 *
 * @package    Hyper_AmiDiscussion
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDiscussion_FilterModelAdm extends AMI_Filter{
    /**
     * Flag specifying to display 'Author' and 'IP' filter fields
     *
     * @var bool
     */
    protected $displayAuthorAndIP = FALSE;

    /**
     * Flag specifying to use tree
     *
     * @var bool
     */
    protected $useTree = null;

    /**
     * Constructor.
     */
    public function __construct(){
        $this->addViewField(
            array(
                'name'          => 'flt_id_parent',
                'type'          => 'hidden',
                'flt_default'   => '0',
                'flt_condition' => '=',
                'flt_column'    => 'id_parent',
                'act_as_int'    => TRUE
            )
        );

        if(!is_null($this->useTree) && $this->getModId() !== ''){
            $this->useTree = AMI::issetAndTrueOption($this->getModId(), 'use_tree_view');
        }
        if(is_null($this->useTree) || $this->useTree){
            $this->addViewField(
                array(
                    'name'          => 'flt_parent_level',
                    'type'          => 'hidden',
                    'flt_type'      => 'hidden',
                    'flt_default'   => '0'
                )
            );
        }
        $this->addViewField(
            array(
                'name'          => 'datefrom',
                'type'          => 'datefrom',
                'flt_type'      => 'date',
                'flt_default'   => AMI_Lib_Date::formatUnixTime(AMI_Lib_Date::UTIME_MIN),
                'flt_condition' => '>=',
                'flt_column'    => 'date_created',
                'validate'      => array('date','date_limits'),
                'session_field' => TRUE
            )
        );
        $this->addViewField(
            array(
                'name'          => 'dateto',
                'type'          => 'dateto',
                'flt_type'      => 'date',
                'flt_default'   => AMI_Lib_Date::formatUnixTime(AMI_Lib_Date::UTIME_MAX),
                'flt_condition' => '<=',
                'flt_column'    => 'date_created',
                'validate'      => array('date','date_limits'),
                'session_field' => TRUE
            )
        );
        if($this->displayAuthorAndIP){
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
                    'name'          => 'flt_ip',
                    'type'          => 'input',
                    'flt_type'      => 'text',
                    'flt_default'   => ''
                )
            );
        }
    }

    /**
     * Sets module Id.
     *
     * @param string $modId  Module Id
     * @return void
     */
    public function setModId($modId){
        parent::setModId($modId);
        if(is_null($this->useTree)){
            $this->useTree = AMI::issetAndTrueOption($this->getModId(), 'use_tree_view');
            if(!$this->useTree){
                $this->dropViewFields(array('flt_parent_level'));
            }
        }
    }

    /**
     * Patches filter conditions.
     *
     * @param  string $field  Field name
     * @param  array  $aData  Filter data
     * @return array
     * @amidev Temporary
     */
    protected function processFieldData($field, array $aData){
        $aData = parent::processFieldData($field, $aData);
        switch($field){
            case 'flt_ip':
                if($aData['value'] !== ''){
                    $val = $this->prepareSqlField('topic', $aData['value'], 'text');
                    $sql = " AND `i`.`ip` = INET_ATON('" . $val. "')";
                    $aData['forceSQL'] = $sql;
                }
                break;
            case 'flt_id_parent':
                if(!$this->useTree){
                    $aData['skip'] = TRUE;
                }
                break;
        }
        return $aData;
    }
}

/**
 * AmiDiscussion hypermodule admin filter component view.
 *
 * @package    Hyper_AmiDiscussion
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDiscussion_FilterViewAdm extends AMI_ModFilterViewAdm{
    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        $this->addScriptFile('_admin/' . $GLOBALS['CURRENT_SKIN_PATH'] . '_js/ami_discussion_filter.js');
        return parent::init();
    }
}

/**
 * AmiDiscussion hypermodule admin form component action controller.
 *
 * @package    Hyper_AmiDiscussion
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDiscussion_FormAdm extends AMI_ModFormAdm{
    /**
     * Returns true if component needs to be started always in full environment.
     *
     * @return bool
     */
    public function isFullEnv(){
        return FALSE;
    }
}

/**
 * AmiDiscussion hypermodule admin form component view.
 *
 * @package    Hyper_AmiDiscussion
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDiscussion_FormViewAdm extends AMI_ModFormViewAdm{
}

/**
 * AmiDiscussion hypermodule admin list component action controller.
 *
 * @package    Hyper_AmiDiscussion
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDiscussion_ListAdm extends AMI_ModListAdm{
    /**
     * Flag specifying to display publish/unpublish actions.
     *
     * @var bool
     */
    protected $displayPublic = FALSE;

    /**
     * Flag specifying to display replay/edit actions.
     *
     * @var bool
     */
    protected $displayReplyEdit = TRUE;

    /**
     * Initialization.
     *
     * @return Hyper_AmiDiscussion_ListAdm
     */
    public function init(){
        parent::init();
        if($this->displayReplyEdit){
            $this->addActions(array('reply', 'edit'));
        }
        $this->addActions(array('delete'));
        if($this->displayPublic){
            $this->addColActions(array('public'), TRUE);
            $this->addGroupActions(
                array(
                    array(self::REQUIRE_FULL_ENV . 'public',   'common_section'),
                    array(self::REQUIRE_FULL_ENV . 'unpublic', 'common_section'),
                )
            );
        }
        $this->addGroupActions(
            array(
                array(self::REQUIRE_FULL_ENV . 'delete', 'delete_section'),
            )
        );

        return $this;
    }
}

/**
 * AmiDiscussion hypermodule admin list component view.
 *
 * @package    Hyper_AmiDiscussion
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDiscussion_ListViewAdm extends AMI_ModListView_JSON{
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
     * Service object
     *
     * @var AmiCommonMessage_Service
     */
    protected $oService;

    /**
     * Init columns.
     *
     * @return Hyper_AmiDiscussion_ListViewAdm
     */
    public function init(){
        parent::init();

        $this->oService = AmiCommonMessage_Service::getInstance(array($this->getModId()));
        $this->formatColumn('author', array($this, 'fmtAuthor'));

        // Patch authors
        AMI_Event::addHandler('on_list_body_row', array($this, 'handleListBodyRow'), $this->getModId());

        $this->addScriptFile('_admin/' . $GLOBALS['CURRENT_SKIN_PATH'] . '_js/ami_discussion_list.js');

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
        $this->oService->patchAuthor($aEvent['aScope']['author']);
        $this->oService->patchSysUser($aEvent['aScope']);
        return $aEvent;
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
                        'url'       => AMI::getSingleton('core')->getAdminLink('members'),
                        'author'    => $value,
                        'id_member' => $aArgs['oItem']->id_member
                    )
                )
            : $value;
    }
    /*
    public function get(){
        AMI::getSingleton('db')->displayQueries(TRUE);
        $result = parent::get();
        AMI::getSingleton('db')->displayQueries(FALSE);
        return $result;
    }
    */
}

/**
 * AmiDiscussion hypermodule list action controller.
 *
 * @category   AMI
 * @package    Hyper_AmiDiscussion
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiDiscussion_ListActionsAdm extends AMI_ModListActions{
}

/**
 * AmiDiscussion hypermodule list group action controller.
 *
 * @category   AMI
 * @package    Hyper_AmiDiscussion
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiDiscussion_ListGroupActionsAdm extends AMI_ModListGroupActions{
}
