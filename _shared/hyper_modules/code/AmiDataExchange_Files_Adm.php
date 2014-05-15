<?php
/**
 * AmiDataExchange/Files configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiDataExchange_Files
 * @version   $Id: AmiDataExchange_Files_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiDataExchange/Files configuration admin action controller.
 *
 * @package    Config_AmiDataExchange_Files
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDataExchange_Files_Adm extends Hyper_AmiDataExchange_Adm{
    /**
     * Array of default components
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aDefaultComponents = array('filter', 'list', 'form');

    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request object
     * @param AMI_Response $oResponse  Response object
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        parent::__construct($oRequest, $oResponse);
    }
}

/**
 * AmiDataExchange/Files configuration admin filter component action controller.
 *
 * @package    Config_AmiDataExchange_CustomFields
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDataExchange_Files_FilterAdm extends Hyper_AmiDataExchange_FilterAdm{
    /**
     * List recordset handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListRecordset($name, array $aEvent, $handlerModId, $srcModId){
        $header = $this->oItem->getValue('header');
        if($header){
            $aEvent['oList']->addSearchCondition(array('filename' => $header));
        }
        return $aEvent;
    }
}

/**
 * AmiDataExchange/Files configuration item list component filter model.
 *
 * @package    Config_AmiDataExchange_CustomFields
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDataExchange_Files_FilterModelAdm extends Hyper_AmiDataExchange_FilterModelAdm{

    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();

        $this->dropViewFields(array('datefrom', 'dateto'));
    }
}

/**
 * AmiDataExchange/Files configuration admin filter component view.
 *
 * @package    Config_AmiDataExchange_CustomFields
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDataExchange_Files_FilterViewAdm extends Hyper_AmiDataExchange_FilterViewAdm{
}

/**
 * AmiDataExchange/Files configuration admin form component action controller.
 *
 * @package    Config_AmiDataExchange_Files
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDataExchange_Files_FormAdm extends Hyper_AmiDataExchange_FormAdm{
}

/**
 * AmiDataExchange/Files configuration form component view.
 *
 * @package    Config_AmiDataExchange_Files
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDataExchange_Files_FormViewAdm extends AMI_ModFormView{
    /**
     * Reset form default elements template
     *
     * @var array
     */
    protected $aPlaceholders = array('#form', 'form');

    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        $aCatList = array();
        if(AMI_ModDeclarator::getInstance()->isRegistered('ext_category')){
            $oExtCat = AMI::getResource('ext_category/module/controller/adm', array('files'));
            $aCatList = $oExtCat->getCatList(true);
        }
        $this->addField(array('name' => 'cat_id', 'type' => 'select', 'data' => $aCatList));
        $this->addField(array('name' => 'force_rewrite', 'type' => 'checkbox', 'default_checked' => true, 'value' => '1', 'position' => 'cat_id.after'));
        $this->addField(array('name' => 'public', 'type' => 'checkbox', 'position' => 'force_rewrite.after'));
        $this->addField(array('name' => 'remove', 'type' => 'checkbox', 'position' => 'public.after'));

        return parent::init();
    }
}

/**
 * Module model.
 *
 * @package    Config_AmiDataExchange_Files
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDataExchange_Files_State extends Hyper_AmiDataExchange_State{
}

/**
 * Module admin list component action controller.
 *
 * @package    Hypermodule_DataExchange
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDataExchange_Files_ListAdm extends Hyper_AmiDataExchange_ListAdm{
    /**
     * List actions controller resource id
     *
     * @var string
     */
    protected $listActionsResId = 'files_import/list_actions/controller/adm';

    /**
     * List group actions controller resource id
     *
     * @var string
     */
    protected $listGrpActionsResId = 'files_import/list_group_actions/controller/adm';

    /**
     * Initialization.
     *
     * @return AMI_ModListAdmCommon
     */
    public function init(){
        parent::init();

        $this->dropActions(self::ACTION_ALL);

        $this->addGroupActions(
            array(
                array(self::REQUIRE_FULL_ENV . 'import', 'import_section'),
            )
        );
        $this->addActionCallback('group', 'grp_import');

        return $this;
    }
}

/**
 * Module admin list component view.
 *
 * @package    Hypermodule_DataExchange
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDataExchange_Files_ListViewAdm extends Hyper_AmiDataExchange_ListViewAdm{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'filename';

    /**
     * Order column direction
     *
     * @var bool
     */
    protected $orderDirection = 'asc';

    /**
     * Array of file icons
     *
     * @var    array
     */
    protected $aFileIcons = array();

    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();

        // Init columns
        $this
            ->addColumnType('id', 'hidden')
            ->addColumnType('filename', 'text')
            ->addColumnType('extension', 'date')
            ->addColumnType('filesize', 'float')
            ->setColumnTensility('filename')
            ->addSortColumns(array('filename'));
        $this->formatColumn(
            'extension',
            array($this, 'fmtType')
        );
        $this->formatColumn(
            'filesize',
            array($this, 'fmtSize')
        );
        $this->putPlaceholder('import', 'extension.before');

        // get file icons
        $oFilesModel = AMI::getResourceModel($this->getModId().'/table');
        $aFileTypes = $oFilesModel->getFileTypes();
        $aIconData = array();
        $aIconData['path'] = $GLOBALS["ROOT_PATH_WWW"].AMI::getOption('files', 'icons_path');
        $oTpl = $this->getTemplate();
        foreach($aFileTypes as $ext => $aData){
            $aIconData['alt'] = $aData['name'];
            $aIconData['icon'] = "small_".$aData['icon'];
            $this->aFileIcons[$ext] = $oTpl->parse($this->tplBlockName . ':icon', $aIconData);
        }
    }

    /**
     * Type column formatter.
     *
     * @param  mixed $ext  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtType($ext, array $aArgs){
        if(!empty($this->aFileIcons[$ext])){
            $value = $this->aFileIcons[$ext];
        }else{
            $value = $this->aFileIcons['.'];
        }

        return $value;
    }

    /**
     * Size column formatter.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtSize($value, array $aArgs){
        return AMI_Lib_String::getBytesAsText($value, $this->aLocale, 1);
    }
}

/**
 * DataExchange hyper module admin list actions controller.
 *
 * @package    Module_Hyper_AmiDataExchange
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDataExchange_Files_ListActionsAdm extends Hyper_AmiDataExchange_ListActionsAdm{
}

/**
 * DataExchange hyper module admin list component group actions controller.
 *
 * @package    Module_Hyper_AmiDataExchange
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiDataExchange_Files_ListGroupActionsAdm extends Hyper_AmiDataExchange_ListGroupActionsAdm{
    /**
     * Dispatches group 'import' action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchGrpImport($name, array $aEvent, $handlerModId, $srcModId){
        $oAmiDataExchange = new AMI_DataExchange($handlerModId);
        $oAmiDataExchange->setActiveDriver('FilesExchangeDriver');
        $aResult = $oAmiDataExchange->dispatchImport($aEvent);

        $aEvent['oResponse']->resetStatusMessages();
        if(is_array($aResult)){
            if(isset($aResult['error'])){
                $aEvent['oResponse']->addStatusMessage($aResult['error']);
            }else{
                if(isset($aResult['imported'])){
                    $aEvent['oResponse']->addStatusMessage('status_imported', array('num' => $aResult['imported']));
                }
                if(!empty($aResult['replaced'])){
                    $aEvent['oResponse']->addStatusMessage('status_replaced', array('num' => $aResult['replaced']));
                }
                if(!empty($aResult['not_imported'])){
                    $aEvent['oResponse']->addStatusMessage('status_not_imported', array('num' => $aResult['not_imported']));
                }
                if(!empty($aResult['deleted'])){
                    $aEvent['oResponse']->addStatusMessage('status_deleted', array('num' => $aResult['deleted']));
                }
                if(!empty($aResult['not_deleted'])){
                    $aEvent['oResponse']->addStatusMessage('status_not_deleted', array('num' => $aResult['not_deleted']));
                }
            }
        }else{
            $aEvent['oResponse']->addStatusMessage('status_import_error');
        }

        $this->refreshView();
        return $aEvent;
    }
}
