<?php
/**
 * AmiPageManager/Layouts configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiPageManager_Layouts
 * @version   $Id: AmiPageManager_Layouts_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiPageManager/Layouts configuration admin action controller.
 *
 * @package    Config_AmiPageManager_Layouts
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiPageManager_Layouts_Adm extends Hyper_AmiPageManager_Adm{
}

/**
 * AmiPageManager/Layouts configuration model.
 *
 * @package    Config_AmiPageManager_Layouts
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiPageManager_Layouts_State extends Hyper_AmiPageManager_State{
}

/**
 * AmiPageManager/Layouts configuration admin filter component action controller.
 *
 * @package    Config_AmiPageManager_Layouts
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiPageManager_Layouts_FilterAdm extends Hyper_AmiPageManager_FilterAdm{
}

/**
 * AmiPageManager/Layouts configuration item list component filter model.
 *
 * @package    Config_AmiPageManager_Layouts
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiPageManager_Layouts_FilterModelAdm extends Hyper_AmiPageManager_FilterModelAdm{
    /**
     * Common filter fields
     *
     * @var   array
     */
    protected $aCommonFields = array('header');
}

/**
 * AmiPageManager/Layouts configuration admin filter component view.
 *
 * @package    Config_AmiPageManager_Layouts
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiPageManager_Layouts_FilterViewAdm extends Hyper_AmiPageManager_FilterViewAdm{
}

/**
 * AmiPageManager/Layouts configuration admin form component action controller.
 *
 * @package    Config_AmiPageManager_Layouts
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiPageManager_Layouts_FormAdm extends Hyper_AmiPageManager_FormAdm{
}

/**
 * AmiPageManager/Layouts configuration form component view.
 *
 * @package    Config_AmiPageManager_Layouts
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiPageManager_Layouts_FormViewAdm extends Hyper_AmiPageManager_FormViewAdm{
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
 * AmiPageManager/Layouts configuration admin list component action controller.
 *
 * @package    Config_AmiPageManager_Layouts
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiPageManager_Layouts_ListAdm extends Hyper_AmiPageManager_ListAdm{
    /**
     * List actions controller resource id
     *
     * @var string
     */
    protected $listActionsResId = 'layouts/list_actions/controller/adm';

    /**
     * List group actions controller resource id
     *
     * @var string
     */
    protected $listGrpActionsResId = 'layouts/list_group_actions/controller/adm';

    /**
     * Initialization.
     *
     * @return AmiSubscribe_Send_ListAdm
     */
    public function init(){
        AMI_Event::addHandler('on_query_add_table', array($this, 'handleAddTable'), $this->getModId());
        $this->addJoinedColumns(array('id', 'script_link'), 'p');

        parent::init();

        $this->dropActions(AMI_ModListAdm::ACTION_ALL, array('edit', 'delete'));
        // 'restore', 'backup' has been removed
        $this->addActions(array('show', 'copy', 'edit', 'delete'));
        $this->addColActions(array(self::REQUIRE_FULL_ENV . 'is_default'), true);
        $this->dropActions(AMI_ModListAdm::ACTION_COLUMN, array('public'));
        $this->dropActions(self::ACTION_GROUP);

        return $this;
    }

    /**
     * Adds installed modules filter and grouping to list model.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTable::getQueryBase()
     */
    public function handleAddTable($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['oQuery']->addGrouping('i.id');
        return $aEvent;
    }
}

/**
 * AmiPageManager/Layouts configuration admin list component view.
 *
 * @package    Config_AmiPageManager_Layouts
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiPageManager_Layouts_ListViewAdm extends Hyper_AmiPageManager_ListViewAdm{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'id_layout';

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
            '#columns', 'is_default', 'id_layout', 'header', 'css_file', 'columns',
            '#actions', 'show', 'copy', 'edit', 'delete', 'actions',
        'list_header'
    );
    // 'restore', 'backup' has been removed

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiPageManager_Layouts_ListViewAdm
     */
    public function init(){
        parent::init();

        // Init columns
        $this
            ->removeColumn('announce')
            ->removeColumn('position')
            ->removeColumn('public')
            ->removeColumn('date_created')
            ->addColumnType('id', 'hidden')
            ->addColumnType('p_id', 'hidden')
            ->addColumnType('p_script_link', 'hidden')
            ->formatColumn('p_script_link', array($this, 'fmtScriptLink'))
            ->addColumn('is_default')
            ->addColumn('id_layout')
            ->addColumn('css_file')
            ->addColumnType('id_layout', 'int')
            ->addSortColumns(array('is_default', 'id_layout', 'css_file'));

        return $this;
    }

    /**
     * Formats script link for layout demo.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     */
    protected function fmtScriptLink($value, array $aArgs){
        $scriptLink = $value;
        if(!empty($aArgs['aScope']['p_id'])){
            if(!AMI_Lib_String::isFullLink($scriptLink)){
                $isMultilang = AMI::getOption('core', 'allow_multi_lang');
                if($isMultilang){
                    $scriptLink = AMI_Registry::get('lang')."/".$scriptLink;
                }
            }
            $scriptLink = AMI_Registry::get('path/www_root').$scriptLink;

            $aVars = array(AMI::getOption('core', 'layouts_export_flag') => "1");
            $scriptLink = AMI_Lib_String::addVarsToUrl($scriptLink, $aVars);
        }else{
            $scriptLink = '';
        }

        return $scriptLink;
    }
}

/**
 * AmiPageManager/Layouts configuration module admin list actions controller.
 *
 * @package    Config_AmiPageManager_Layouts
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiPageManager_Layouts_ListActionsAdm extends Hyper_AmiPageManager_ListActionsAdm{
}

/**
 * AmiPageManager/Layouts configuration module admin list group actions controller.
 *
 * @package    Config_AmiPageManager_Layouts
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiPageManager_Layouts_ListGroupActionsAdm extends Hyper_AmiPageManager_ListGroupActionsAdm{
}
