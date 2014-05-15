<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module_Articles
 * @version   $Id: AmiDataExchange_--modId--_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @amidev    Temporary
 */

/**
 * Articles module admin action controller.
 *
 * @package    Module_Articles
 * @subpackage Controller
 * @amidev     Temporary
 */
class AmiDataExchange_##modId##_Adm extends Hyper_AmiDataExchange_Adm{
    /**
     * Array of default components
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aDefaultComponents = array('list', 'form');

    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request object
     * @param AMI_Response $oResponse  Response object
     */
    //public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
    //    parent::__construct($oRequest, $oResponse);
    //}
}

/**
 * Articles module admin form component action controller.
 *
 * @package    Module_Articles
 * @subpackage Controller
 * @amidev     Temporary
 */
class AmiDataExchange_##modId##_FormAdm extends Hyper_AmiDataExchange_FormAdm{
}

/**
 * Articles module form component view.
 *
 * @package    Module_Articles
 * @subpackage View
 * @amidev     Temporary
 */
class AmiDataExchange_##modId##_FormViewAdm extends AMI_ModFormView{
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
            $oExtCat = AMI::getResource('ext_category/module/controller/adm', array('##modId##'));
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
 * @package    Module_Articles
 * @subpackage Model
 * @amidev     Temporary
 */
class AmiDataExchange_##modId##_State extends Hyper_AmiDataExchange_State{
}

/**
 * Module admin list component action controller.
 *
 * @package    Hypermodule_DataExchange
 * @subpackage Controller
 * @amidev     Temporary
 */
class AmiDataExchange_##modId##_ListAdm extends Hyper_AmiDataExchange_ListAdm{
    /**
     * List actions controller resource id
     *
     * @var string
     */
    protected $listActionsResId = '##modId##_import/list_actions/controller/adm';

    /**
     * List group actions controller resource id
     *
     * @var string
     */
    protected $listGrpActionsResId = '##modId##_import/list_group_actions/controller/adm';

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
 * @amidev     Temporary
 */
class AmiDataExchange_##modId##_ListViewAdm extends Hyper_AmiDataExchange_ListViewAdm{
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
            ->addColumnType('id', 'text')
            ->addColumnType('type', 'date')
            ->addColumnType('size', 'float')
            ->setColumnTensility('id');
        $this->formatColumn(
            'type',
            array($this, 'fmtType')
        );
        $this->formatColumn(
            'size',
            array($this, 'fmtSize')
        );
        $this->putPlaceholder('import', 'type.before');

        // get file icons
        $oFilesModel = AMI::getResourceModel($this->getModId().'/table');
        $aFileTypes = $oFilesModel->getFileTypes();
        $aIconData = array();
        $aIconData['path'] = $GLOBALS["ROOT_PATH_WWW"].AMI::getOption('##modId##', 'icons_path');
        $oTpl = $this->getTemplate();
        foreach($aFileTypes as $ext => $aData){
            $aIconData['alt'] = $aData['name'];
            $aIconData['icon'] = "small_".$aData['icon'];
            $this->aFileIcons[$ext] = $oTpl->parse($this->tplBlockName . ':icon', $aIconData);
        }
    }

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AMI_Module_ListViewAdm
     */
    public function init(){
    }

    /**
     * Type column formatter.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtType($value, array $aArgs){
        $ext = '.'.mb_strtolower(get_file_ext($aArgs['aScope']['id']));
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
 * @amidev     Temporary
 */
class AmiDataExchange_##modId##_ListActionsAdm extends Hyper_AmiDataExchange_ListActionsAdm{
}

/**
 * DataExchange hyper module admin list component group actions controller.
 *
 * @package    Module_Hyper_AmiDataExchange
 * @subpackage Controller
 * @amidev     Temporary
 */
class AmiDataExchange_##modId##_ListGroupActionsAdm extends Hyper_AmiDataExchange_ListGroupActionsAdm{
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
