<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiDataExchange
 * @version   $Id: Hyper_AmiDataExchange_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiDataExchange module admin action controller.
 *
 * @package    Hyper_AmiDataExchange
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiDataExchange_Adm extends AMI_Module_Adm{
    /**
     * Array of default components
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aDefaultComponents = array('form');
}

/**
 * Module model.
 *
 * @package    Hyper_AmiDataExchange
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiDataExchange_State extends AMI_ModState{
}

/**
 * AmiDataExchange module admin filter component action controller.
 *
 * @package    Hyper_AmiDataExchange
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiDataExchange_FilterAdm extends AMI_Module_FilterAdm{
}

/**
 * AmiDataExchange module admin filter component model.
 *
 * @package    Hyper_AmiDataExchange
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiDataExchange_FilterModelAdm extends AMI_Module_FilterModelAdm{
}

/**
 * AMI_DataExchange module filter component view.
 *
 * @package    Hyper_AmiDataExchange
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiDataExchange_FilterViewAdm extends AMI_ModFilterView{
}

/**
 * AmiDataExchange module admin form component action controller.
 *
 * @package    Hyper_AmiDataExchange
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiDataExchange_FormAdm extends AMI_Module_FormAdm{
    /**
     * Returns true if component needs to be started always in full environment.
     *
     * @return bool
     */
    public function isFullEnv(){
        return FALSE;
    }
}

/**
 * AMI_DataExchange module form component view.
 *
 * @package    Hyper_AmiDataExchange
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class AMI_DataExchange_FormViewAdm extends AMI_ModFormView{
    /**
     * Reset form default elements template
     *
     * @var array
     */
    protected $aPlaceholders = array('#form', 'form');

    /**
     * AMI_DataExchange object
     *
     * @var AMI_DataExchange
     */
    private $oAmiDataExchange;

    /**
     * Path for imported files
     *
     * @var string
     */
    private $importPath;

    /**
     * Constructor.
     */
    public function __construct(){
        $this->importPath = AMI_Registry::get("MODULE_PICTURES_PATH").AMI::getOption($this->getModId(), 'import_path');
        $this->oAmiDataExchange = new AMI_DataExchange($this->getModId());
        parent::__construct();
    }

    /**
     * Add common import fields.
     *
     * @return void
     */
    protected function addImportCommonFields(){
        $oTpl = $this->getTemplate();

        $this->addTab('importtab', 'data_exchange');
        $this->addField(array('name' => 'exchange_type', 'value' => 'import', 'type' => 'hidden'));
        $this->addField(array('name' => 'import_tooltip', 'type' => 'hint', 'position' => 'importtab.begin', 'value' => ''));

        $aDriversList = $this->oAmiDataExchange->getImportDriversList();
        $isShowFiles = false;
        $isShowDirs = false;
        $aShowFileMask = array();
        $aDriverSubfoldersList = array();
        $aSelectDrivers = array();
        foreach($aDriversList as $driver){
            if(is_array($driver["pathtypes"])){
                foreach($driver["pathtypes"] as $key => $type){
                    if($type == "files"){
                        $isShowFiles = true;
                    }elseif($type == "dirs"){
                        $isShowDirs = true;
                    }
                }
            }
            if(is_array($driver["filemask"])){
                foreach($driver["filemask"] as $key => $mask){
                    if(!in_array($mask, $aShowFileMask)){
                        array_push($aShowFileMask, $mask);
                    }
                }
            }else{
                array_push($aShowFileMask, "*");
            }

            $aSelectDrivers[] = array(
                'name'  => $driver['title'],
                'value' => $driver['name']
            );
        }
        $this->addField(
            array(
                'name' => 'import_driver',
                'type' => 'select',
                'data' => $aSelectDrivers,
                'not_selected'  => array('id' => '', 'caption' => 'select_driver'),
                'position' => 'import_tooltip.after'
            )
        );

        $this->addField(array('name' => 'data_source_type', 'type' => 'radio', 'position' => 'import_driver.after', 'data' => array(array('id' => 'radio_data_source_type_ftp', 'value' => 'ftp', 'checked' => true, 'caption' => ''))));

        // Folders list
        $aSelectFolders = array();
        $showFileMaskRegExp = "";
        if(sizeof($aShowFileMask) <= 0 || in_array("*", $aShowFileMask)){
            $showFileMaskRegExp = "|.*";
        }else{
            foreach($aShowFileMask as $mask){
                $showFileMaskRegExp .= "|(".str_replace("\\*", ".*?", quotemeta($mask)).")";
            }
        }
        $showFileMaskRegExp = mb_substr($showFileMaskRegExp, 1);
        if(is_dir($this->importPath)){
            $importDir = dir($this->importPath);
            if(!is_object($importDir)){
                trigger_error("Unable to find directory ".$this->importPath, E_USER_ERROR);
            }
            while(false !== ($entry = $importDir->read())){
                if($entry != "." && $entry != ".." && AMI_Lib_FS::validatePath($this->importPath.$entry) && ($isShowFiles && is_file($this->importPath.$entry) && preg_match('/'.$showFileMaskRegExp.'$/si', $entry) || $isShowDirs && is_dir($this->importPath.$entry))){

                    $fsize = AMI_Lib_FS::getDirectorySize($this->importPath.$entry);
                    $fileData = array(
                        "file_size" => AMI_Lib_String::getBytesAsText($fsize, $this->aLocale, 1),
                        "file_size_bytes" => number_format($fsize, 0, '.', ' '),
                        "title" => $entry
                    );
                    $aSelectFolders[] = array(
                        'name' => $oTpl->parse($this->tplBlockName . ':data_source_ftp_caption', $fileData),
                        'value' => $entry
                    );
                }
            }
        }
        $this->addField(
            array(
                'name' => 'data_source_ftp',
                'type' => 'select',
                'data' => $aSelectFolders,
                'not_selected'  => array('id' => '', 'caption' => 'select_ftp_folder'),
                'position' => 'data_source_type.after'
            )
        );
    }
}

/**
 * AmiDataExchange module form component view.
 *
 * @package    Hyper_AmiDataExchange
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiDataExchange_FormViewAdm extends AMI_DataExchange_FormViewAdm{
    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        $this->addTabContainer('data_exchange');

        $aCatList = array();
        $useCategories = true;
        $itemsModId = str_replace('_data_exchange', '', $this->getModId());
        if(AMI_ModDeclarator::getInstance()->isRegistered('ext_category')){
            $oExtCat = AMI::getResource('ext_category/module/controller/adm', array($itemsModId));
            $aCatList = $oExtCat->getCatList(true);
        }else{
            $useCategories = false;
        }
        if(!AMI::issetAndTrueOption($itemsModId, 'use_categories')){
            $useCategories = false;
        }

        if(!$useCategories){
            $this->addField(array('name' => 'photoalbum_import_not_allowed', 'type' => 'hint', 'value' => ''));
            $this->viewType = 'no_buttons';
            return parent::init();
        }

        // Add import tab
        $this->addImportCommonFields();

        $this->addField(array('name' => 'photoalbum_additional_params', 'type' => 'hint', 'position' => 'data_source_ftp.after', 'value' => ''));

        $this->addField(array('name' => 'cattype_2', 'type' => 'radio', 'position' => 'photoalbum_additional_params.after', 'data' => array(array('id' => 'cattype_2', 'checked' => true, 'caption' => 'photoalbum_cats_use'))));

        $this->addField(array('name' => 'cat_id', 'type' => 'select', 'position' => 'cattype_2.after', 'data' => $aCatList));
        $this->addField(array('name' => 'catname', 'position' => 'cat_id.after'));
        $curLastPosition = 'catname';

        $allowPageSelect = false;
        if(
            $itemsModId &&
            AMI::issetAndTrueOption('core', 'multi_page_allowed') &&
            AMI::issetAndTrueOption($itemsModId, 'multi_page')
        ){
            $aModulePages = array();
            $aPages = AMI_PageManager::getModPages($itemsModId, AMI_Registry::get('lang_data'));
            foreach($aPages as $aPage){
                $aModulePages[] = array(
                    'name'  => $aPage['name'],
                    'value' => $aPage['id']
                );
            }
            $allowPageSelect = true;
        }

        if($allowPageSelect){
            $this->addField(
                array(
                    'name' => 'id_page_cattype_2',
                    'type' => 'select',
                    'data' => $aModulePages,
                    'not_selected'  => array('id' => 0, 'caption' => 'common_item'),
                    'position' => 'catname.after'
                )
            );
            $curLastPosition = 'id_page_cattype_2';
        }

        $this->addField(array('name' => 'cattype_1', 'type' => 'radio', 'position' => $curLastPosition.'.after', 'data' => array(array('id' => 'cattype_1', 'caption' => 'photoalbum_cats_create'))));

        $curLastPosition = 'cattype_1';
        if($allowPageSelect){
            $this->addField(
                array(
                    'name' => 'id_page_cattype_1',
                    'type' => 'select',
                    'data' => $aModulePages,
                    'not_selected'  => array('id' => 0, 'caption' => 'common_item'),
                    'position' => 'cattype_1.after'
                )
            );
            $curLastPosition = 'id_page_cattype_1';
        }

        $this->addField(array('name' => 'force_rewrite', 'type' => 'checkbox', 'default_checked' => true, 'value' => '1', 'position' => $curLastPosition.'.after'));
        $this->addField(array('name' => 'photoalbum_note', 'type' => 'hint', 'position' => 'importtab.end', 'value' => ''));

        return parent::init();
    }
}

/**
 * DataExchange hyper module admin list component action controller.
 *
 * @package    Module_Hyper_AmiDataExchange
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDataExchange_ListAdm extends AMI_Module_ListAdm{
}

/**
 * DataExchange hyper module admin list component actions controller.
 *
 * @package    Module_Hyper_AmiDataExchange
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDataExchange_ListActionsAdm extends AMI_Module_ListActionsAdm{
}

/**
 * DataExchange hyper module admin list component group actions controller.
 *
 * @package    Module_Hyper_AmiDataExchange
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDataExchange_ListGroupActionsAdm extends AMI_Module_ListGroupActionsAdm{
}

/**
 * DataExchange hyper module admin list component view.
 *
 * @package    Module_Hyper_AmiDataExchange
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiDataExchange_ListViewAdm extends AMI_Module_ListViewAdm{
    /**
     * Init columns.
     *
     * @return Hyper_AmiDataExchange_ListViewAdm
     */
    public function init(){
        // Discard all columns creation.
        return $this;
    }
}
