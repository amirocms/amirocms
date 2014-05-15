<?php
/**
 * AmiMultifeeds5/Jobs configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiMultifeeds5_JobsCat
 * @version   $Id: AmiMultifeeds5_JobsCat_Adm.php 42166 2013-10-10 12:29:32Z Leontiev Anton $
 * @since     x.x.x
 * @amidev
 */

/**
 * AmiMultifeeds5/Jobs configuration admin action controller.
 *
 * @package    Config_AmiMultifeeds5_JobsCat
 * @subpackage Controller
 * @resource   {$modId}_cat/module/controller/adm <code>AMI::getResource('{$modId}_cat/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_JobsCat_Adm extends Hyper_AmiMultifeeds5_Cat_Adm{
}

/**
 * AmiMultifeeds5/Jobs configuration model.
 *
 * @package    Config_AmiMultifeeds5_JobsCat
 * @subpackage Model
 * @resource   {$modId}_cat/module/model <code>AMI::getResourceModel('{$modId}_cat/module')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_JobsCat_State extends Hyper_AmiMultifeeds5_Cat_State{
}

/**
 * AmiMultifeeds5/Jobs configuration admin filter component action controller.
 *
 * @package    Config_AmiMultifeeds5_JobsCat
 * @subpackage Controller
 * @resource   {$modId}_cat/filter/controller/adm <code>AMI::getResource('{$modId}_cat/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_JobsCat_FilterAdm extends Hyper_AmiMultifeeds5_Cat_FilterAdm{
}

/**
 * AmiMultifeeds5/Jobs configuration item list component filter model.
 *
 * @package    Config_AmiMultifeeds5_JobsCat
 * @subpackage Model
 * @resource   {$modId}_cat/filter/model/adm <code>AMI::getResource('{$modId}_cat/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_JobsCat_FilterModelAdm extends Hyper_AmiMultifeeds5_Cat_FilterModelAdm{
}

/**
 * AmiMultifeeds5/Jobs configuration admin filter component view.
 *
 * @package    Config_AmiMultifeeds5_JobsCat
 * @subpackage View
 * @resource   {$modId}_cat/filter/view/adm <code>AMI::getResource('{$modId}_cat/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_JobsCat_FilterViewAdm extends Hyper_AmiMultifeeds5_Cat_FilterViewAdm{
    /**
     * Filter default elemnts template (placeholders)
     *
     * @var array
     */
    protected $aPlaceholders = array(
        '#filter',
            'header', 'id_page', 'sticky',
        'filter'
    );
}

/**
 * AmiMultifeeds5/Jobs configuration admin form component action controller.
 *
 * @package    Config_AmiMultifeeds5_JobsCat
 * @subpackage Controller
 * @resource   {$modId}_cat/form/controller/adm <code>AMI::getResource('{$modId}_cat/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_JobsCat_FormAdm extends Hyper_AmiMultifeeds5_Cat_FormAdm{
}

/**
 * AmiMultifeeds5/Jobs configuration form component view.
 *
 * @package    Config_AmiMultifeeds5_JobsCat
 * @subpackage View
 * @resource   {$modId}_cat/form/view/adm <code>AMI::getResource('{$modId}_cat/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_JobsCat_FormViewAdm extends Hyper_AmiMultifeeds5_Cat_FormViewAdm{
}

/**
 * AmiMultifeeds5/Jobs configuration admin list component action controller.
 *
 * @package    Config_AmiMultifeeds5_JobsCat
 * @subpackage Controller
 * @resource   {$modId}_cat/list/controller/adm <code>AMI::getResource('{$modId}_cat/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_JobsCat_ListAdm extends Hyper_AmiMultifeeds5_Cat_ListAdm{
    /**
     * Initialization.
     *
     * @return Hyper_ArticlesCat_ListAdm
     */
    public function init(){
        parent::init();
        $this->dropActions(AMI_ModListAdm::ACTION_GROUP, array('index_details', 'no_index_details'));
        return $this;
    }
}

/**
 * AmiMultifeeds5/Jobs configuration admin list component view.
 *
 * @package    Config_AmiMultifeeds5_JobsCat
 * @subpackage View
 * @resource   {$modId}_cat/list/view/adm <code>AMI::getResource('{$modId}_cat/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_JobsCat_ListViewAdm extends Hyper_AmiMultifeeds5_Cat_ListViewAdm{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'header';

    /**
     * Order column direction
     *
     * @var bool
     */
    protected $orderDirection = 'asc';
    /**
     * List default elemnts template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#list_header',
            '#flags', 'position', 'public', 'flags',
            '#common', 'cat_header', 'common',
            '#columns', 'header', 'announce', 'num_items', 'id_page', 'columns',
            '#actions', 'front_view', 'edit', 'actions',
        'list_header'
    );
    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiMultifeeds5_JobsCat_ListViewAdm
     */
    public function init(){
        parent::init();
        $this->setColumnTensility('header', false);
        $this->setColumnWidth('header', 'extra-wide');
        return $this;
    }
}

/**
 * AmiMultifeeds5/Jobs configuration module admin list actions controller.
 *
 * @package    Config_AmiMultifeeds5_JobsCat
 * @subpackage Controller
 * @resource   {$modId}_cat/list_actions/controller/adm <code>AMI::getResource('{$modId}_cat/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_JobsCat_ListActionsAdm extends Hyper_AmiMultifeeds5_Cat_ListActionsAdm{
}

/**
 * AmiMultifeeds5/Jobs configuration module admin list group actions controller.
 *
 * @package    Config_AmiMultifeeds5_JobsCat
 * @subpackage Controller
 * @resource   {$modId}_cat/list_group_actions/controller/adm <code>AMI::getResource('{$modId}_cat/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_JobsCat_ListGroupActionsAdm extends Hyper_AmiMultifeeds5_Cat_ListGroupActionsAdm{
}
