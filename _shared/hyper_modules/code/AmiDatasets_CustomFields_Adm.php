<?php
/**
 * AmiDatasets/CustomFields configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiDatasets_CustomFields
 * @version   $Id: AmiDatasets_CustomFields_Adm.php 44694 2013-11-29 08:02:39Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiDatasets/CustomFields configuration admin action controller.
 *
 * @package    Config_AmiDatasets_CustomFields
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDatasets_CustomFields_Adm extends Hyper_AmiDatasets_Adm{
}

/**
 * AmiDatasets/CustomFields configuration model.
 *
 * @package    Config_AmiDatasets_CustomFields
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDatasets_CustomFields_State extends Hyper_AmiDatasets_State{
}

/**
 * AmiDatasets/CustomFields configuration admin filter component action controller.
 *
 * @package    Config_AmiDatasets_CustomFields
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDatasets_CustomFields_FilterAdm extends Hyper_AmiDatasets_FilterAdm{
}

/**
 * AmiDatasets/CustomFields configuration item list component filter model.
 *
 * @package    Config_AmiDatasets_CustomFields
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDatasets_CustomFields_FilterModelAdm extends Hyper_AmiDatasets_FilterModelAdm{
    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();
        $this->addViewField(
            array(
                'name'          => 'admin_form',
                'type'          => 'select',
                'flt_type'      => 'select',
                'flt_default'   => '',
                'data'          => array(
                    array('caption' => 'admin_form_visible', 'value' => 1),
                    array('caption' => 'admin_form_invisible', 'value' => 2)
                ),
                'not_selected'  => array('id' => '', 'caption' => 'all')
            )
        );
    }

    /**
     * Adds .............
     *
     * @param string $field  Field name
     * @param array $aData  Filter data
     * @return array
     * @amidev Temporary
     */
    protected function processFieldData($field, array $aData){
        if($field === 'admin_form' && $aData['value'] > 0){
            $aData['forceSQL'] = "AND IF(i.`admin_form` = 'none', 2, 1) = " . (int)$aData['value'];
        }
        return $aData;
    }
}

/**
 * AmiDatasets/CustomFields configuration admin filter component view.
 *
 * @package    Config_AmiDatasets_CustomFields
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDatasets_CustomFields_FilterViewAdm extends Hyper_AmiDatasets_FilterViewAdm{
}

/**
 * AmiDatasets/CustomFields configuration admin form component action controller.
 *
 * @package    Config_AmiDatasets_CustomFields
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDatasets_CustomFields_FormAdm extends Hyper_AmiDatasets_FormAdm{
}

/**
 * AmiDatasets/CustomFields configuration form component view.
 *
 * @package    Config_AmiDatasets_CustomFields
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDatasets_CustomFields_FormViewAdm extends Hyper_AmiDatasets_FormViewAdm{
}

/**
 * AmiDatasets/CustomFields configuration admin list component action controller.
 *
 * @package    Config_AmiDatasets_CustomFields
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDatasets_CustomFields_ListAdm extends Hyper_AmiDatasets_ListAdm{
    /**
     * Initialization.
     *
     * @return AmiDatasets_Datasets_ListAdm
     */
    public function init(){
        AMI_Event::addHandler('on_query_add_table', array($this, 'handleAddTable'), $this->getModId());
        $this->getModel()->setActiveDependence('d');
        $this->addJoinedColumns(array('datasets'), 'd');
        $this->addColActions(array(self::REQUIRE_FULL_ENV . 'public'), TRUE);
        $this->addActions(array('edit', self::REQUIRE_FULL_ENV . 'delete'));
        $this->addGroupActions(
            array(
                array(self::REQUIRE_FULL_ENV . 'public',   'common_section'),
                array(self::REQUIRE_FULL_ENV . 'unpublic', 'common_section')
            )
        );
        parent::init();
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
        $aModIds = AmiExt_CustomFields_Service::getInstance()->getInstalledModules();
        $aEvent['oQuery']->addWhereDef(sizeof($aModIds)?DB_Query::getSnippet('AND i.`module_name` IN (%s)')->implode($aModIds):'AND 0');
        $aEvent['oQuery']->addGrouping('i.id');
        return $aEvent;
    }
}

/**
 * AmiDatasets/CustomFields configuration admin list component view.
 *
 * @package    Config_AmiDatasets_CustomFields
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDatasets_CustomFields_ListViewAdm extends Hyper_AmiDatasets_ListViewAdm{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'system_name';

    /**
     * Order column direction
     *
     * @var bool
     */
    protected $orderDirection = 'asc';

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiDatasets_CustomFields_ListViewAdm
     */
    public function init(){
        $this
            ->addColumnType('id', 'hidden')
            ->addColumn('public')
            ->addColumn('module')
            ->formatColumn(
                'module',
                array($this, 'fmtModule')
            )
            ->addColumn('system_name')
            ->addColumn('name')
            ->formatColumn(
                'name',
                array($this, 'fmtLocalizedCaption')
            )
            ->addColumn('default_caption')
            ->setColumnTensility('default_caption')
            ->formatColumn(
                'default_caption',
                array($this, 'fmtLocalizedCaption')
            )
            ->addColumn('ftype')
            ->formatColumn(
                'ftype',
                array($this, 'fmtType')
            )
            ->addColumnType('d_datasets', 'int')
            ->addSortColumns(array('public', 'module', 'system_name', 'ftype'));
        parent::init();
        return $this;
    }

    /**#@+
     * Column formatter.
     *
     * @see    AMI_ModListView::formatColumn()
     */

    /**
     * Converts module id to its caption.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return string
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtLocalizedCaption($value, array $aArgs){
        $value = unserialize($value);
        $locale = AMI_Registry::get('lang_data', 'en');
        $value = isset($value[$locale]) ? $value[$locale] : '&mdash;';
        return $value;
    }

    /**
     * Localizes field type.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return string
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtType($value, array $aArgs){
        $value = $this->aLocale['field_type_' . $value];
        return $value;
    }

    /**#@-*/
}

/**
 * AmiDatasets/CustomFields configuration module admin list actions controller.
 *
 * @package    Config_AmiDatasets_CustomFields
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
/*
class AmiDatasets_CustomFields_ListActionsAdm extends Hyper_AmiDatasets_ListActionsAdm{
}
*/

/**
 * AmiDatasets/CustomFields configuration module admin list group actions controller.
 *
 * @package    Config_AmiDatasets_CustomFields
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
/*
class AmiDatasets_CustomFields_ListGroupActionsAdm extends Hyper_AmiDatasets_ListGroupActionsAdm{
}
*/
