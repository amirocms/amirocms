<?php
/**
 * AmiCatalog configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiCatalog_Items
 * @version   $Id: AmiCatalog_ItemsCat_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiCatalog configuration admin action controller.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_ItemsCat_Adm extends Hyper_AmiCatalog_Cat_Adm{
}

/**
 * AmiCatalog configuration model.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_ItemsCat_State extends Hyper_AmiCatalog_Cat_State{
}

/**
 * AmiCatalog configuration admin filter component action controller.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_ItemsCat_FilterAdm extends Hyper_AmiCatalog_Cat_FilterAdm{
	/**
     * Initialization.
     *
     * @return $this
     */
    public function init(){
        AMI_Event::addHandler('on_filter_init', array($this, 'handleFilterInit'), $this->getModId());
        parent::init();
        return $this;
    }

    /**
     * Adds id_source field.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleFilterInit($name, array $aEvent, $handlerModId, $srcModId){
        $section = AMI_ModDeclarator::getInstance()->getSection($srcModId);
        $fltDatasetsData = array();
        $oEshopCatModel = AMI::getResourceModel($section . '_cat/table')->getItem();
        $aDatasets = $oEshopCatModel->getDatasetsList();
        foreach($aDatasets as $id => $name){
            $fltDatasetsData[] = array(
                'id' => $id,
                'name' => $name
            );
        }
        $aEvent['oFilter']->addViewField(
            array(
                'name'          => 'dataset_id',
                'type'          => 'select',
                'flt_default'   => '',
                'flt_condition' => '=',
                'flt_column'    => 'dataset_id',
                'data'          => $fltDatasetsData,
                'not_selected'  => array('id' => '', 'caption' => 'flt_all'),
                'act_as_int'    => TRUE
            )
        );

        $aCatList = array();
        if(AMI_ModDeclarator::getInstance()->isRegistered('ext_' . $section . '_category')){
            $oExtCat = AMI::getResource('ext_' . $section . '_category/module/controller/adm', array($section . '_item'));
            $aCatList = $oExtCat->getCatList(true);
        }

        $aEvent['oFilter']->addViewField(
            array(
                'name'          => 'category',
                'type'          => 'select',
                'flt_default'   => '',
                'flt_condition' => '=',
                'flt_column'    => 'id',
                'data'          => $aCatList,
                'not_selected'  => array('id' => '', 'caption' => 'flt_all'),
                'session_field' => TRUE,
                'act_as_int'    => TRUE
            )
        );

        $aEvent['oFilter']->addViewField(
            array(
                'name'          => 'id_external',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'id_external',
            )
        );
        return $aEvent;
	}
}

/**
 * AmiCatalog configuration item list component filter model.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_ItemsCat_FilterModelAdm extends Hyper_AmiCatalog_Cat_FilterModelAdm{
    /**
     * Patches filter conditions.
     *
     * @param string $field  Field name
     * @param array  $aData  Filter data
     * @return array
     * @amidev Temporary
     */
    protected function processFieldData($field, array $aData){
        switch($field){
            case 'category':
                $aData['forceSQL'] = '';
                $catId = intval($aData['value']);
                if($catId > 0){
                    $aData['forceSQL'] = " AND i.id_parent = ".$catId;
                }
                break;
        }
        return $aData;
    }
}

/**
 * AmiCatalog configuration admin filter component view.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_ItemsCat_FilterViewAdm extends Hyper_AmiCatalog_Cat_FilterViewAdm{
    /**
     * Filter default elemnts template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#filter',
             'dataset_id', 'category', 'header', 'id_external', 'sticky',
        'filter'
    );

    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        $aScope = array(
            'cat_id'          => (int)AMI_Filter::getFieldValue('category', 0),
            'path_strip_text' => AMI::getOption($this->getModId(), 'path_settings_strip_text')
        );
        list(
            $aScope['path_max_length'],
            $aScope['cat_max_length'],
            $aScope['cat_start_qty'],
            $aScope['cat_end_qty']
        ) = AMI::getOption($this->getModId(), 'path_settings');
        $aScope += $this->aScope;
        $this->aScope['path'] = $this->parse('path_all', $aScope);
        $this->addScriptCode($this->parse('javascript', $aScope));

        return parent::init();
    }

    /**
     * Sets path scope variable displaying under filter form.
     *
     * @return void
     */
    protected function setPath(){
        $this->aScope['path'] = $this->parse('path_all');
    }
}

/**
 * AmiCatalog configuration admin form component action controller.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_ItemsCat_FormAdm extends Hyper_AmiCatalog_Cat_FormAdm{
}

/**
 * AmiCatalog configuration form component view.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_ItemsCat_FormViewAdm extends Hyper_AmiCatalog_Cat_FormViewAdm{
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
 * AmiCatalog configuration admin list component action controller.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_ItemsCat_ListAdm extends Hyper_AmiCatalog_Cat_ListAdm{
    /**
     * Initialization.
     *
     * @return AmiSubscribe_Topic_ListAdm
     */
    public function init(){
        parent::init();

        $this->getModel()->setActiveDependence('p');
        $this->addJoinedColumns(array('header'), 'p');

        $this->dropActions(self::ACTION_GROUP);

        $aExtensions = AMI::getOption($this->getModId(), 'extensions');
        if(is_array($aExtensions) && in_array('ext_eshop_cat_external_link', $aExtensions)){
            $this->addActions(array('external_link'));
        }

        return $this;
    }
}

/**
 * AmiCatalog configuration admin list component view.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_ItemsCat_ListViewAdm extends Hyper_AmiCatalog_Cat_ListViewAdm{
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
            '#columns', 'p_header', 'header', 'dataset', 'announce', 'instruct', 'num_childs', 'columns',
            '#actions', 'edit', 'delete', 'external_link', 'actions',
        'list_header'
    );

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiEshopDiscounts_Discounts_ListViewAdm
     */
    public function init(){
        $oRequest = AMI::getSingleton('env/request');
        $catSessionValue = AMI::getSingleton('env/cookie')->get('filter_field_category_' . AMI_Registry::get('lang_data'), false);
        if(!$catSessionValue && !$oRequest->get('category', null)){
            $this->orderColumn = 'p.header';
        }

        parent::init();

        // Init columns
        $this
            ->removeColumn('num_items')
            ->formatColumn('header', array($this, 'fmtHeader'))
            ->addColumnType('id_parent', 'hidden')
            ->addColumnType('dataset_id', 'hidden')
            ->addColumn('dataset')
            ->setColumnWidth('dataset', 'wide')
            ->addColumnType('announce', 'longtext')
            ->addColumnType('num_items', 'hidden')
            ->addColumnType('num_public_items', 'hidden')
            ->addSortColumns(array('header'));
        // AMI::getSingleton('db')->displayQueries();
        if(!$catSessionValue && !$oRequest->get('category', null)){
            $this
                ->addColumn('p_header')
                ->addSortColumns(array('p_header'));
        }

        if(AMI::getOption($this->getModId(), 'instructions_on')){
            $this
                ->addColumn('instruct')
                ->setColumnWidth('instruct', 'extra-narrow')
                ->addSortColumns(array('instruct'));

            $this->setColumnLayout('instruct', array('align' => 'center'));
            $this->formatColumn(
                'instruct',
                array($this, 'fmtColIcon'),
                array(
                    'class'             => 'move',
                    'has_inactive'      => true,
                    'caption'           => $this->aLocale['list_instruct'],
                    'caption_inactive'  => $this->aLocale['list_instruct_inactive']
                )
            );
        }

        $oEshop = AMI::getSingleton('eshop');
        if($oEshop->isItemCountersEnabled()){
            $this
                ->addColumnType('num_childs', 'int')
                ->formatColumn('num_childs', array($this, 'fmtNumItems'));
        }

        AMI_Event::addHandler('on_list_body_{dataset}', array($this, 'handleDatasetCell'), $this->getModId());

        $this->addScriptCode($this->getTemplate()->parse($this->tplBlockName . ':javascript'));

        return $this;
    }

    /**
     * Prepare Dataset field.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::get()
     */
    public function handleDatasetCell($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['aScope']['list_col_value'] = $aEvent['oTableItem']->getDatasetName($aEvent['aScope']['dataset_id']);
        return $aEvent;
    }

    /**
     * Childs column formatter.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtNumItems($value, array $aArgs){
        return $aArgs['aScope']['num_items'] . '/' . $aArgs['aScope']['num_public_items'];
    }

    /**
     * Header column formatter.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtHeader($value, array $aArgs){
        $oTpl = $this->getTemplate();
        return $oTpl->parse($this->tplBlockName . ':col_cat_name', $aData = array('mod_id' => $this->getModId(), 'id' => $aArgs['aScope']['id'], 'name' => $value));
    }
}

/**
 * AmiCatalog configuration module admin list actions controller.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage Controller
 * @resource   {$modId}_cat/list_actions/controller/adm <code>AMI::getResource('{$modId}_cat/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_ItemsCat_ListActionsAdm extends Hyper_AmiCatalog_Cat_ListActionsAdm{
}

/**
 * AmiCatalog configuration module admin list group actions controller.
 *
 * @package    Config_AmiCatalog_Items
 * @subpackage Controller
 * @resource   {$modId}_cat/list_group_actions/controller/adm <code>AMI::getResource('{$modId}_cat/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_ItemsCat_ListGroupActionsAdm extends Hyper_AmiCatalog_Cat_ListGroupActionsAdm{
}
