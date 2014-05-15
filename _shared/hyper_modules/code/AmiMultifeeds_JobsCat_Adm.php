<?php
/**
 * AmiMultifeeds/Jobs configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiMultifeeds_JobsCat
 * @version   $Id: AmiMultifeeds_JobsCat_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiMultifeeds/Jobs configuration admin action controller.
 *
 * @package    Config_AmiMultifeeds_JobsCat
 * @subpackage Controller
 * @resource   {$modId}_cat/module/controller/adm <code>AMI::getResource('{$modId}_cat/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_JobsCat_Adm extends Hyper_AmiMultifeeds_Cat_Adm{
}

/**
 * AmiMultifeeds/Jobs configuration model.
 *
 * @package    Config_AmiMultifeeds_JobsCat
 * @subpackage Model
 * @resource   {$modId}_cat/module/model <code>AMI::getResourceModel('{$modId}_cat/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_JobsCat_State extends Hyper_AmiMultifeeds_Cat_State{
}

/**
 * AmiMultifeeds/Jobs configuration admin filter component action controller.
 *
 * @package    Config_AmiMultifeeds_JobsCat
 * @subpackage Controller
 * @resource   {$modId}_cat/filter/controller/adm <code>AMI::getResource('{$modId}_cat/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_JobsCat_FilterAdm extends Hyper_AmiMultifeeds_Cat_FilterAdm{
}

/**
 * AmiMultifeeds/Jobs configuration item list component filter model.
 *
 * @package    Config_AmiMultifeeds_JobsCat
 * @subpackage Model
 * @resource   {$modId}_cat/filter/model/adm <code>AMI::getResource('{$modId}_cat/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_JobsCat_FilterModelAdm extends Hyper_AmiMultifeeds_Cat_FilterModelAdm{
}

/**
 * AmiMultifeeds/Jobs configuration admin filter component view.
 *
 * @package    Config_AmiMultifeeds_JobsCat
 * @subpackage View
 * @resource   {$modId}_cat/filter/view/adm <code>AMI::getResource('{$modId}_cat/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_JobsCat_FilterViewAdm extends Hyper_AmiMultifeeds_Cat_FilterViewAdm{
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
 * AmiMultifeeds/Jobs configuration admin form component action controller.
 *
 * @package    Config_AmiMultifeeds_JobsCat
 * @subpackage Controller
 * @resource   {$modId}_cat/form/controller/adm <code>AMI::getResource('{$modId}_cat/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_JobsCat_FormAdm extends Hyper_AmiMultifeeds_Cat_FormAdm{
}

/**
 * AmiMultifeeds/Jobs configuration form component view.
 *
 * @package    Config_AmiMultifeeds_JobsCat
 * @subpackage View
 * @resource   {$modId}_cat/form/view/adm <code>AMI::getResource('{$modId}_cat/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_JobsCat_FormViewAdm extends Hyper_AmiMultifeeds_Cat_FormViewAdm{
}

/**
 * AmiMultifeeds/Jobs configuration admin list component action controller.
 *
 * @package    Config_AmiMultifeeds_JobsCat
 * @subpackage Controller
 * @resource   {$modId}_cat/list/controller/adm <code>AMI::getResource('{$modId}_cat/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_JobsCat_ListAdm extends Hyper_AmiMultifeeds_Cat_ListAdm{
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
 * AmiMultifeeds/Jobs configuration admin list component view.
 *
 * @package    Config_AmiMultifeeds_JobsCat
 * @subpackage View
 * @resource   {$modId}_cat/list/view/adm <code>AMI::getResource('{$modId}_cat/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_JobsCat_ListViewAdm extends Hyper_AmiMultifeeds_Cat_ListViewAdm{
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
     * @return AmiMultifeeds_JobsCat_ListViewAdm
     */
    public function init(){
        parent::init();
        $this->setColumnTensility('header', false);
        $this->setColumnWidth('header', 'extra-wide');
        return $this;
    }
}

/**
 * AmiMultifeeds/Jobs configuration module admin list actions controller.
 *
 * @package    Config_AmiMultifeeds_JobsCat
 * @subpackage Controller
 * @resource   {$modId}_cat/list_actions/controller/adm <code>AMI::getResource('{$modId}_cat/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_JobsCat_ListActionsAdm extends Hyper_AmiMultifeeds_Cat_ListActionsAdm{
}

/**
 * AmiMultifeeds/Jobs configuration module admin list group actions controller.
 *
 * @package    Config_AmiMultifeeds_JobsCat
 * @subpackage Controller
 * @resource   {$modId}_cat/list_group_actions/controller/adm <code>AMI::getResource('{$modId}_cat/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_JobsCat_ListGroupActionsAdm extends Hyper_AmiMultifeeds_Cat_ListGroupActionsAdm{
}
