<?php
/**
 * AmiSubscribe/Topic configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiSubscribe_Topic
 * @version   $Id: AmiSubscribe_Topic_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiSubscribe/Topic configuration admin action controller.
 *
 * @package    Config_AmiSubscribe_Topic
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Topic_Adm extends Hyper_AmiSubscribe_Adm{
}

/**
 * AmiSubscribe/Topic configuration model.
 *
 * @package    Config_AmiSubscribe_Topic
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Topic_State extends Hyper_AmiSubscribe_State{
}

/**
 * AmiSubscribe/Topic configuration admin filter component action controller.
 *
 * @package    Config_AmiSubscribe_Topic
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Topic_FilterAdm extends Hyper_AmiSubscribe_FilterAdm{
}

/**
 * AmiSubscribe/Topic configuration item list component filter model.
 *
 * @package    Config_AmiSubscribe_Topic
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Topic_FilterModelAdm extends Hyper_AmiSubscribe_FilterModelAdm{
    /**
     * Common filter fields
     *
     * @var   array
     */
    protected $aCommonFields = array('header');
}

/**
 * AmiSubscribe/Topic configuration admin filter component view.
 *
 * @package    Config_AmiSubscribe_Topic
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Topic_FilterViewAdm extends Hyper_AmiSubscribe_FilterViewAdm{
}

/**
 * AmiSubscribe/Topic configuration admin form component action controller.
 *
 * @package    Config_AmiSubscribe_Topic
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Topic_FormAdm extends Hyper_AmiSubscribe_FormAdm{
}

/**
 * AmiSubscribe/Topic configuration form component view.
 *
 * @package    Config_AmiSubscribe_Topic
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Topic_FormViewAdm extends Hyper_AmiSubscribe_FormViewAdm{
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
 * AmiSubscribe/Topic configuration admin list component action controller.
 *
 * @package    Config_AmiSubscribe_Topic
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Topic_ListAdm extends Hyper_AmiSubscribe_ListAdm{
    /**
     * List actions controller resource id
     *
     * @var string
     */
    protected $listActionsResId = 'subs_topic/list_actions/controller/adm';

    /**
     * List group actions controller resource id
     *
     * @var string
     */
    protected $listGrpActionsResId = 'subs_topic/list_group_actions/controller/adm';

    /**
     * Initialization.
     *
     * @return AmiSubscribe_Topic_ListAdm
     */
    public function init(){
        parent::init();

        $this->addColActions(array(self::REQUIRE_FULL_ENV . 'active'), true);
        $this->dropActions(AMI_ModListAdm::ACTION_COLUMN, array('public'));
        $this->dropActions(self::ACTION_GROUP);

        return $this;
    }
}

/**
 * AmiSubscribe/Topic configuration admin list component view.
 *
 * @package    Config_AmiSubscribe_Topic
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Topic_ListViewAdm extends Hyper_AmiSubscribe_ListViewAdm{
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
            '#columns', 'active', 'header', 'columns',
            '#actions', 'edit', 'delete', 'actions',
        'list_header'
    );

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiSubscribe_Topic_ListViewAdm
     */
    public function init(){
        parent::init();

        // Init columns
        $this
            ->removeColumn('announce')
            ->removeColumn('position')
            ->removeColumn('public')
            ->removeColumn('date_created')
            ->addColumn('active')
            ->addSortColumns(array('active', 'header'));

        return $this;
    }
}

/**
 * AmiSubscribe/Topic configuration module admin list actions controller.
 *
 * @package    Config_AmiSubscribe_Topic
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Topic_ListActionsAdm extends Hyper_AmiSubscribe_ListActionsAdm{
}

/**
 * AmiSubscribe/Topic configuration module admin list group actions controller.
 *
 * @package    Config_AmiSubscribe_Topic
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSubscribe_Topic_ListGroupActionsAdm extends Hyper_AmiSubscribe_ListGroupActionsAdm{
}
