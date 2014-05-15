<?php
/**
 * AmiSearch/Search configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiSearch_Search
 * @version   $Id: AmiSearch_Search_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiSearch/Search configuration admin action controller.
 *
 * @package    Config_AmiSearch_Search
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSearch_Search_Adm extends Hyper_AmiSearch_Adm{
    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request object
     * @param AMI_Response $oResponse  Response object
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        AMI_Registry::set('AMI/HyperConfig/Model/ami_search/search/switched', TRUE);
        parent::__construct($oRequest, $oResponse);
    }
}

/**
 * AmiSearch/Search configuration model.
 *
 * @package    Config_AmiSearch_Search
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSearch_Search_State extends Hyper_AmiSearch_State{
}

/**
 * AmiSearch/Search configuration admin filter component action controller.
 *
 * @package    Config_AmiSearch_Search
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSearch_Search_FilterAdm extends Hyper_AmiSearch_FilterAdm{
}

/**
 * AmiSearch/Search configuration item list component filter model.
 *
 * @package    Config_AmiSearch_Search
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSearch_Search_FilterModelAdm extends Hyper_AmiSearch_FilterModelAdm{
    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();

        $this->addViewField(
            array(
                'name'          => 'group',
                'type'          => 'checkbox',
                'flt_default'   => '0',
                'flt_condition' => '',
                'flt_column'    => 'pages',
                'disable_empty' => true
            )
        );

        $this->addViewField(
            array(
                'name'          => 'header',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'query'
            )
        );

        $this->addViewField(
            array(
                'name'          => 'query',
                'type'          => 'hidden',
                'flt_default'   => '',
                'flt_condition' => '=',
                'flt_column'    => 'query'
            )
        );
        $this->addViewField(
            array(
                'name'          => 'pages',
                'type'          => 'select',
                'flt_default'   => '',
                'flt_condition' => '=',
                'flt_column'    => 'pages',
                'data'          => $this->getSearchPages(),
                'multiple'      => TRUE,
                'not_selected'  => array(
                    'id'       => 0,
                    'caption'  => 'common_item'
                )
            )
        );
        return $this;
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
        if(($field == 'group') && $aData['value'] == 1){
            $this->groupByField = 'query';
            $aData['skip'] = true;
        }
        if(($field == 'query') && $aData['value'] == ''){
            $aData['skip'] = true;
        }
        if($field == 'pages'){
            if(($aData['value'] == '') || (is_array($aData['multi_values']) && (!count($aData['multi_values']) || ((count($aData['multi_values']) == 1) && !(int)$aData['multi_values'][0])))){
                $aData['skip'] = true;
            }else{
                $aData['forceSQL'] = ' AND (0 ';
                $cnt = 0;
                foreach($aData['multi_values'] as $value){
                    if((int)$value != 0){
                        $aData['forceSQL'] .= ' OR FIND_IN_SET("' . (int)$value . '", pages) > 0';
                    }
                }
                $aData['forceSQL'] .= ' OR FIND_IN_SET("0", pages) > 0 )';
            }
        }
        return $aData;
    }

    /**
     * Apply filter on query object.
     *
     * @param  array $aEvent  Event data
     * @return AMI_Filter
     * @amidev Temporary
     */
    public function applyFilter(array $aEvent){
        parent::applyFilter($aEvent);
        if(!is_null($this->groupByField)){
            $aEvent['oQuery']->dropField('quantity');
            $aEvent['oQuery']->addExpressionField("SUM(i.quantity) AS quantity");
            $aEvent['oQuery']->addExpressionField("COUNT(i.id) AS inner_count");
        }
       return $this;
    }

    /**
     * Returns list of module pages affected by reindex extension.
     *
     * @param bool $addModuleNames  Add module name to the caption or not
     * @return array
     */
    public function getSearchPages($addModuleNames = true){
        $res = array();
        $supportedModules = array_keys(AMI_Ext::getSupportedModules('ext_reindex'));
        if($addModuleNames){
            $aModuleNames = AMI::getSingleton('env/template_sys')->parseLocale('templates/lang/_menu_all.lng');
        }
        $aCommonLng = AMI::getSingleton('env/template_sys')->parseLocale('templates/lang/main.lng');
        $modLng = $aCommonLng['module'];
        $oPagesModel = AMI::getResourceModel('pages/table/model');
        $oPagesList = $oPagesModel
            ->getList()
            ->addColumns(array('id', 'header', 'id_mod'))
            ->addWhereDef(DB_Query::getSnippet("AND parent_id = 0"))
            ->load();
        foreach($oPagesList as $oPage){
            $parentId = $oPage->getId();
            $pageId = $parentId;
            $res[] = array(
                'value' => $pageId,
                'name' => $oPage->header . ($addModuleNames && $oPage->{'id_mod'} != 'pages' ? ' (' . $modLng . ': ' . $aModuleNames[$oPage->{'id_mod'}] . ')' : '')
            );
        }
        $oPagesList = $oPagesModel
            ->getList()
            ->addColumns(array('id', 'header', 'id_mod'))
            ->addWhereDef(
                DB_Query::getSnippet("AND parent_id > 0 AND module_name = %s OR module_name IN (%s)")
                ->q('pages')
                ->implode($supportedModules)
            )
            ->load();
        foreach($oPagesList as $oPage){
            $parentId = $oPage->getId();
            $pageId = $parentId;
            $res[] = array(
                'value' => $pageId,
                'name' => $oPage->header . ($addModuleNames && $oPage->{'id_mod'} != 'pages' ? ' (' . $modLng . ': ' . $aModuleNames[$oPage->{'id_mod'}] . ')' : '')
            );
        }
        return $res;
    }
}

/**
 * AmiSearch/Search configuration admin filter component view.
 *
 * @package    Config_AmiSearch_Search
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSearch_Search_FilterViewAdm extends Hyper_AmiSearch_FilterViewAdm{
}

/**
 * AmiSearch/Search configuration admin form component action controller.
 *
 * @package    Config_AmiSearch_Search
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSearch_Search_FormAdm extends Hyper_AmiSearch_FormAdm{
}

/**
 * AmiSearch/Search configuration form component view.
 *
 * @package    Config_AmiSearch_Search
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSearch_Search_FormViewAdm extends Hyper_AmiSearch_FormViewAdm{
}

/**
 * AmiSearch/Search configuration admin list component action controller.
 *
 * @package    Config_AmiSearch_Search
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSearch_Search_ListAdm extends Hyper_AmiSearch_ListAdm{
    /**
     * Default list order
     *
     * @var array
     * @amidev
     */
    protected $aDefaultOrder = array(
        'col' => 'date_updated',
        'dir' => 'desc'
    );

    /**
     * Initialization.
     *
     * @return JobsResume_ListAdm
     */
    public function init(){
        // AMI::getSingleton('db')->displayQueries(true);
        parent::init();
        $this->dropActions(AMI_ModListAdm::ACTION_COMMON, array('edit', 'delete'));
        $this->addActions(array('show', 'delete'));
        $this->addActionCallback('common', 'delete');
        $this->dropActions(AMI_ModListAdm::ACTION_GROUP, array('public', 'unpublic', 'gen_sublink', 'gen_html_meta', 'gen_html_meta_force', 'index_details', 'no_index_details'));
        $this->dropActions(AMI_ModListAdm::ACTION_COLUMN, array('public'));
        return $this;
    }

}

/**
 * AmiSearch/Search configuration admin list component view.
 *
 * @package    Config_AmiSearch_Search
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSearch_Search_ListViewAdm extends Hyper_AmiSearch_ListViewAdm{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'date_updated';
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
            '#flags', 'flags',
            '#common', 'common',
            '#columns', 'query', 'date_created', 'date_updated', 'quantity', 'count_pages', 'columns',
            '#actions', 'front_view', 'edit', 'actions',
        'list_header'
    );

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiSearch_Search_ListViewAdm
     */
    public function init(){
        parent::init();
        $this
            ->removeColumn('header')
            ->removeColumn('announce')
            ->removeColumn('position')
            ->removeColumn('public')
            ->addColumnType('date_created', 'date')
            ->addColumnType('date_updated', 'date')
            ->addColumnType('quantity', 'int')
            ->addColumnType('count_pages', 'int')
            ->addColumnType('front_link', 'hidden')
            ->addColumn('query')
            ->setColumnTensility('query', true)
            ->addSortColumns(
                array(
                    'query', 'quantity', 'count_pages', 'date_created', 'date_updated'
                )
            );
        $this->formatColumn('date_created', array($this, 'fmtDateTime'), array('format' => AMI_Lib_Date::FMT_DATE));
        $this->formatColumn('date_updated', array($this, 'fmtDateTime'), array('format' => AMI_Lib_Date::FMT_DATE));
        AMI_Event::addHandler('on_list_body_{query}', array($this, 'handleQueryCell'), $this->getModId());
        AMI_Event::addHandler('on_list_body_{count_pages}', array($this, 'handleCountCell'), $this->getModId());

        return $this;
    }

    /**
     * Prepare Query field.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::get()
     */
    public function handleQueryCell($name, array $aEvent, $handlerModId, $srcModId){
        if(!isset($aEvent['aScope']['inner_count']) || ($aEvent['aScope']['inner_count'] < 2)){
            $aScope = array(
                'link' => $GLOBALS['ROOT_PATH_WWW'] . $aEvent['aScope']['front_link'],
                'value' => $aEvent['aScope']['query']
            );
            $aEvent['aScope']['list_col_value'] = $this->parse('query_column', $aScope);
        }
        return $aEvent;
    }

    /**
     * Prepare Count field.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::get()
     */
    public function handleCountCell($name, array $aEvent, $handlerModId, $srcModId){
        if(isset($aEvent['aScope']['inner_count']) && ($aEvent['aScope']['inner_count'] > 1)){
            $aScope = array(
                'mod_id' => $this->getModId(),
                'value' => $aEvent['aScope']['query']
            );
            $aEvent['aScope']['list_col_value'] = $this->parse('count_column', $aScope);
        }
        return $aEvent;
    }
}

/**
 * AmiSearch/Search configuration module admin list actions controller.
 *
 * @package    Config_AmiSearch_Search
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSearch_Search_ListActionsAdm extends Hyper_AmiSearch_ListActionsAdm{
}

/**
 * AmiSearch/Search configuration module admin list group actions controller.
 *
 * @package    Config_AmiSearch_Search
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiSearch_Search_ListGroupActionsAdm extends Hyper_AmiSearch_ListGroupActionsAdm{
}
