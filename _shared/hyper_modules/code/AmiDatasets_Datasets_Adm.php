<?php
/**
 * AmiDatasets/Datasets configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiDatasets_Datasets
 * @version   $Id: AmiDatasets_Datasets_Adm.php 42588 2013-10-23 12:57:45Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiDatasets/Datasets configuration admin action controller.
 *
 * @package    Config_AmiDatasets_Datasets
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDatasets_Datasets_Adm extends Hyper_AmiDatasets_Adm{
}

/**
 * AmiDatasets/Datasets configuration model.
 *
 * @package    Config_AmiDatasets_Datasets
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDatasets_Datasets_State extends Hyper_AmiDatasets_State{
}

/**
 * AmiDatasets/Datasets configuration admin filter component action controller.
 *
 * @package    Config_AmiDatasets_Datasets
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDatasets_Datasets_FilterAdm extends Hyper_AmiDatasets_FilterAdm{
}

/**
 * AmiDatasets/Datasets configuration item list component filter model.
 *
 * @package    Config_AmiDatasets_Datasets
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDatasets_Datasets_FilterModelAdm extends Hyper_AmiDatasets_FilterModelAdm{
}

/**
 * AmiDatasets/Datasets configuration admin filter component view.
 *
 * @package    Config_AmiDatasets_Datasets
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDatasets_Datasets_FilterViewAdm extends Hyper_AmiDatasets_FilterViewAdm{
}

/**
 * AmiDatasets/Datasets configuration admin form component action controller.
 *
 * @package    Config_AmiDatasets_Datasets
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDatasets_Datasets_FormAdm extends Hyper_AmiDatasets_FormAdm{
}

/**
 * AmiDatasets/Datasets configuration form component view.
 *
 * @package    Config_AmiDatasets_Datasets
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDatasets_Datasets_FormViewAdm extends Hyper_AmiDatasets_FormViewAdm{
}

/**
 * AmiDatasets/Datasets configuration admin list component action controller.
 *
 * @package    Config_AmiDatasets_Datasets
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDatasets_Datasets_ListAdm extends Hyper_AmiDatasets_ListAdm{
    /**
     * List group actions controller resource id
     *
     * @var string
     */
    protected $listGrpActionsResId = 'modules_datasets/list_group_actions/controller/adm';

    /**
     * Initialization.
     *
     * @return AmiDatasets_Datasets_ListAdm
     */
    public function init(){
        AMI_Event::addHandler('on_query_add_table', array($this, 'handleAddTable'), $this->getModId());

        $this->addActions(array('edit', self::REQUIRE_FULL_ENV . 'copy', self::REQUIRE_FULL_ENV . 'delete'));
        $this->addGroupActions(
            array(
                array(self::REQUIRE_FULL_ENV . 'copy', 'copy_section')
            )
        );
        parent::init();
        return $this;
    }

    /**
     * Adds installed modules filter to list model.
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
        $aEvent['oQuery']->addWhereDef(
            sizeof($aModIds)
            ? DB_Query::getSnippet('AND i.`module_name` IN (%s)')->implode($aModIds)
            : 'AND 0'
        );
        return $aEvent;
    }
}

/**
 * AmiDatasets/Datasets configuration admin list component view.
 *
 * @package    Config_AmiDatasets_Datasets
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDatasets_Datasets_ListViewAdm extends Hyper_AmiDatasets_ListViewAdm{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'name';

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
     * @return AmiDatasets_Datasets_ListViewAdm
     */
    public function init(){
        $this
            ->addColumnType('id', 'hidden')
            ->addColumn('module')
            ->formatColumn(
                'module',
                array($this, 'fmtModule')
            )
            ->setColumnWidth('module', 'wide')
            ->addColumn('name')
            ->setColumnTensility('name')
            ->addColumnType('fields_map', 'none')
            ->addColumnType('fields_qty', 'int')
            ->formatColumn(
                'fields_qty',
                array($this, 'fmtFieldsQty')
            )
            ->addColumn('postfix')
            ->addColumnType('is_sys', 'none')
            ->addSortColumns(array('module', 'name'));
        parent::init();
        return $this;
    }

    /**
     * Event handler.
     *
     * Handling action cell to disallow deleting of system datasets.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleActionCell($name, array $aEvent, $handlerModId, $srcModId){
        if(!empty($aEvent['aScope']['is_sys'])){
            unset($aEvent['aScope']['_action_col']['delete']);
        }
        $aEvent = parent::handleActionCell($name, $aEvent, $handlerModId, $srcModId);
        return $aEvent;
    }

    /**#@+
     * Column formatter.
     */

    /**
     * Calculates fields quanity.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return string
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtFieldsQty($value, array $aArgs){
        $value = trim($aArgs['oItem']->fields_map, ';');
        if(preg_match('/^\d+$/', $value)){
            $value = 1;
        }else{
            if(
                preg_match('/;0{1,};/', ';' . $value . ';') ||
                preg_match('/;0{0,};/', $value)
            ){
                trigger_error("Invalid field `" . $aArgs['oItem']->getTable()->getTableName() . "`.`fields_map`(id=" . $aArgs['oItem']->id . ") value '" . $value . "'", E_USER_WARNING);
            }
            $value = preg_replace('/^(.*)?;0{0,};/', ';', ';' . $value . ';');
            $value = trim($value, ';');
            if(preg_match('/^\d+$/', $value)){
                $value = 1;
            }else{
                $value = mb_substr_count($value, ';');
                if($value){
                    $value++;
                }
            }
        }
        return $value;
    }

    /**#@-*/
}

/**
 * AmiDatasets/Datasets configuration module admin list actions controller.
 *
 * @package    Config_AmiDatasets_Datasets
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
/*
class AmiDatasets_Datasets_ListActionsAdm extends Hyper_AmiDatasets_ListActionsAdm{
}
*/

/**
 * AmiDatasets/Datasets configuration module admin list group actions controller.
 *
 * @package    Config_AmiDatasets_Datasets
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDatasets_Datasets_ListGroupActionsAdm extends Hyper_AmiDatasets_ListGroupActionsAdm{
    /**
     * Event handler.
     *
     * Dispatches group copy action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function dispatchGrpCopy($name, array $aEvent, $handlerModId, $srcModId){
        $count = 0;
        $oTpl = AMI::getSingleton('env/template_sys');
        $aLocale = $oTpl->parseLocale('templates/lang/main.lng');
        foreach($this->getRequestIds() as $id){
            $oItem = $this->getItem($id);
            $oItem->resetId();
            $oItem->name = $aLocale['copy_of'] . ' ' . $oItem->name;
            $oItem->setData(
                array(
                    'is_sys'          => 0,
                    'used_simple'     => 0,
                    'used_categories' => '',
                    'used_pages'      => ''
                ),
                TRUE
            );
            $oItem->save();
            if($oItem->getId()){
                $count += 1;
            }
        }
        $aEvent['oResponse']->addStatusMessage('status_grp_copy', array('num_items' => $count));
        $this->refreshView();
        return $aEvent;
    }
}
