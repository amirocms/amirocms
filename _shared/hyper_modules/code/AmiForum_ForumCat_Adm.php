<?php
/**
 * AmiForum/Forum configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiForum_Forum
 * @version   $Id: AmiForum_ForumCat_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiForum/Forum configuration admin action controller.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage Controller
 * @resource   {$modId}_cat/module/controller/adm <code>AMI::getResource('{$modId}_cat/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_ForumCat_Adm extends Hyper_AmiForum_Cat_Adm{
}

/**
 * AmiForum/Forum configuration model.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage Model
 * @resource   {$modId}_cat/module/model <code>AMI::getResourceModel('{$modId}_cat/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_ForumCat_State extends Hyper_AmiForum_Cat_State{
}

/**
 * AmiForum/Forum configuration admin filter component action controller.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage Controller
 * @resource   {$modId}_cat/filter/controller/adm <code>AMI::getResource('{$modId}_cat/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_ForumCat_FilterAdm extends Hyper_AmiForum_Cat_FilterAdm{
}

/**
 * AmiForum/Forum configuration item list component filter model.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage Model
 * @resource   {$modId}_cat/filter/model/adm <code>AMI::getResource('{$modId}_cat/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_ForumCat_FilterModelAdm extends Hyper_AmiForum_Cat_FilterModelAdm{
}

/**
 * AmiForum/Forum configuration admin filter component view.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage View
 * @resource   {$modId}_cat/filter/view/adm <code>AMI::getResource('{$modId}_cat/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_ForumCat_FilterViewAdm extends Hyper_AmiForum_Cat_FilterViewAdm{
}

/**
 * AmiForum/Forum configuration admin form component action controller.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage Controller
 * @resource   {$modId}_cat/form/controller/adm <code>AMI::getResource('{$modId}_cat/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_ForumCat_FormAdm extends Hyper_AmiForum_Cat_FormAdm{
}

/**
 * AmiForum/Forum configuration form component view.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage View
 * @resource   {$modId}_cat/form/view/adm <code>AMI::getResource('{$modId}_cat/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_ForumCat_FormViewAdm extends Hyper_AmiForum_Cat_FormViewAdm{
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
 * AmiForum/Forum configuration admin list component action controller.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage Controller
 * @resource   {$modId}_cat/list/controller/adm <code>AMI::getResource('{$modId}_cat/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_ForumCat_ListAdm extends Hyper_AmiForum_Cat_ListAdm{
}

/**
 * AmiForum/Forum configuration admin list component view.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage View
 * @resource   {$modId}_cat/list/view/adm <code>AMI::getResource('{$modId}_cat/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_ForumCat_ListViewAdm extends Hyper_AmiForum_Cat_ListViewAdm{
    /**
     * Service object
     *
     * @var AmiCommonMessage_Service
     */
    protected $oService;

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiForum_ForumCat_ListViewAdm
     */
    public function init(){
        parent::init();

        $this->oService = AmiCommonMessage_Service::getInstance(array($this->getModId()));

        $this
            ->addColumn('msg_date', 'announce.after')
            ->setColumnTensility('msg_date')
            ->addColumnType('is_separator', 'hidden')
            ->addColumnType('msg_id', 'none')
            ->addColumnType('msg_id_thread', 'none')
            ->addColumnType('msg_topic', 'none')
            ->addColumnType('msg_id_member', 'none')
            ->addColumnType('msg_author', 'none');

        foreach(array('announce', 'msg_date') as $column){
            $this->setColumnClass($column, 'td_small_text');
        }

        $this->addSortColumns(array('msg_date'));

        $this->formatColumn('msg_date', array($this, 'fmtLastMessage'));
        $this->formatColumn('num_items', array($this, 'fmtNumItems'));

        $this->addScriptFile('_admin/' . $GLOBALS['CURRENT_SKIN_PATH'] . '_js/forum_cat_list.js');


        return $this;
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
        if(!$aArgs['oItem']->is_separator && $aArgs['oItem']->msg_id){
            $aScope = $aArgs['oItem']->getData();
            $aScope['msg_date'] = $this->fmtHumanDateTime($aScope['msg_date'], array());
            $aScope['forum_link'] = AMI::getSingleton('core')->getAdminLink($this->getModId());
            $aScope['members_link'] = AMI::getSingleton('core')->getAdminLink('members');
            $result = $this->parse('last_message', $aScope);
        }else{
            $result = '';
        }
        return $result;
    }

    /**
     * Topics/posts column formatter.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtNumItems($value, array $aArgs){
        $aScope = $aArgs['oItem']->getData();
        $aScope['forum_link'] = 'forum.php'; // hack
        return
            $this->parse(
                'topics_messages',
                $aScope
            );
    }
}

/**
 * AmiForum/Forum configuration module admin list actions controller.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage Controller
 * @resource   {$modId}_cat/list_actions/controller/adm <code>AMI::getResource('{$modId}_cat/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_ForumCat_ListActionsAdm extends Hyper_AmiForum_Cat_ListActionsAdm{
}

/**
 * AmiForum/Forum configuration module admin list group actions controller.
 *
 * @package    Config_AmiForum_Forum
 * @subpackage Controller
 * @resource   {$modId}_cat/list_group_actions/controller/adm <code>AMI::getResource('{$modId}_cat/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiForum_ForumCat_ListGroupActionsAdm extends Hyper_AmiForum_Cat_ListGroupActionsAdm{
}
