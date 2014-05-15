<?php
/**
 * AmiDiscussion/Guestbook configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiDiscussion_Guestbook
 * @version   $Id: AmiDiscussion_Guestbook_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiDiscussion/Guestbook configuration admin action controller.
 *
 * @package    Config_AmiDiscussion_Guestbook
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Guestbook_Adm extends Hyper_AmiDiscussion_Adm{
}

/**
 * AmiDiscussion/Guestbook configuration model.
 *
 * @package    Config_AmiDiscussion_Guestbook
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Guestbook_State extends Hyper_AmiDiscussion_State{
}

/**
 * AmiDiscussion/Guestbook configuration admin filter component action controller.
 *
 * @package    Config_AmiDiscussion_Guestbook
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Guestbook_FilterAdm extends Hyper_AmiDiscussion_FilterAdm{
}

/**
 * AmiDiscussion/Guestbook configuration item list component filter model.
 *
 * @package    Config_AmiDiscussion_Guestbook
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Guestbook_FilterModelAdm extends Hyper_AmiDiscussion_FilterModelAdm{
    /**
     * Flag specifying to display 'Author' and 'IP' filter fields
     *
     * @var bool
     */
    protected $displayAuthorAndIP = TRUE;
}

/**
 * AmiDiscussion/Guestbook configuration admin filter component view.
 *
 * @package    Config_AmiDiscussion_Guestbook
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Guestbook_FilterViewAdm extends Hyper_AmiDiscussion_FilterViewAdm{
    /**
     * Sets path scope variable displaying under filter form.
     *
     * @return void
     */
    protected function setPath(){
        $oRequest = AMI::getSingleton('env/request');
        if((int)$oRequest->get('flt_id_parent', 0)){
            $this->aScope['path'] = $this->parse('path_all');
        }
    }
}

/**
 * AmiDiscussion/Guestbook configuration admin form component action controller.
 *
 * @package    Config_AmiDiscussion_Guestbook
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Guestbook_FormAdm extends Hyper_AmiDiscussion_FormAdm{
}

/**
 * AmiDiscussion/Guestbook configuration form component view.
 *
 * @package    Config_AmiDiscussion_Guestbook
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Guestbook_FormViewAdm extends Hyper_AmiDiscussion_FormViewAdm{
}

/**
 * AmiDiscussion/Guestbook configuration admin list component action controller.
 *
 * @package    Config_AmiDiscussion_Guestbook
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Guestbook_ListAdm extends Hyper_AmiDiscussion_ListAdm{
    /**
     * Flag specifying to display publish/unpublish actions.
     *
     * @var bool
     */
    protected $displayPublic = TRUE;
}

/**
 * AmiDiscussion/Guestbook configuration admin list component view.
 *
 * @package    Config_AmiDiscussion_Guestbook
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Guestbook_ListViewAdm extends Hyper_AmiDiscussion_ListViewAdm{
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
            '#common', 'cat_header', 'common',
            '#columns', 'columns', 'date_created',
            '#actions', 'front_view', 'edit', 'actions',
        'list_header'
    );

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiDiscussion_Guestbook_ListViewAdm
     */
    public function init(){
        parent::init();

        $this
            ->addColumnType('id', 'hidden')
            ->addColumn('subject')
            ->addColumn('message')
            ->addColumn('author')
            ->addColumnType('date_created', 'datetime')
            ->addColumnType('id_member', 'none')

            ->setColumnTensility('message');

        if(AMI::getOption($this->getModId(), 'use_tree_view')){
            $this
                ->addColumn('count_children')
                ->setColumnAlign('count_children', 'center');
        }

        $this->addSortColumns(array('public', 'subject', 'date_created', 'author'));

        foreach(array('message', 'date_created') as $column){
            $this->setColumnClass($column, 'td_small_text');
        }

        $this->formatColumn('message', array($this->oService, 'fmtSmilesToTags'));
        $this->formatColumn('message', array($this, 'fmtTruncate'), array('length' => 1024, 'doStripTags' => TRUE, 'doSaveWords' => AMI::getOption('core', 'strip_strings_by_words'), 'doHTMLEncode' => false));
        $this->formatColumn('count_children', array($this, 'fmtAnswers'));
        $this->formatColumn('date_created', array($this, 'fmtDateTime'));

        return $this;
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
            $value
                ? $this->parse('answers', array('answers' => $value, 'id' => $aArgs['oItem']->id))
                : '';
    }
}

/**
 * AmiDiscussion/Guestbook configuration module admin list actions controller.
 *
 * @package    Config_AmiDiscussion_Guestbook
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Guestbook_ListActionsAdm extends Hyper_AmiDiscussion_ListActionsAdm{
}

/**
 * AmiDiscussion/Guestbook configuration module admin list group actions controller.
 *
 * @package    Config_AmiDiscussion_Guestbook
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDiscussion_Guestbook_ListGroupActionsAdm extends Hyper_AmiDiscussion_ListGroupActionsAdm{
}
