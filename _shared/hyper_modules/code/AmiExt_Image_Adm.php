<?php
/**
 * AmiExt/Image extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Config_AmiExt_Image
 * @version   $Id: AmiExt_Image_Adm.php 44497 2013-11-27 07:00:26Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/Image extension configuration admin controller.
 *
 * @package    Config_AmiExt_Image
 * @subpackage Controller
 * @resource   ext_image/module/controller/adm <code>AMI::getResource('ext_image/module/controller/adm')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Image_Adm extends AmiExt_Image{
    /**
     * Converts inherited option values to new format.
     *
     * @param  string $name   Option name
     * @param  mixed  $value  Option value
     * @return mixed
     */
    public static function convertOptionValue($name, $value){
        $aMap = array(
            'picture'       => 'ext_img',
            'popup_picture' => 'ext_img_popup',
            'small_picture' => 'ext_img_small'
        );
        $result = $value;
        switch($name){
            case 'ext_img_list_col':
            case 'ext_img_source':
                if(isset($aMap[$value])){
                    $result = $aMap[$value];
                }
                break;
            default:
                if(is_array($value)){
                    foreach($value as $key => $part){
                        if(isset($aMap[$part])){
                            $result[$key] = $aMap[$part];
                        }
                    }
                }
                break;
        }

        return $result;
    }

    /**
     * Callback called after module is installed.
     *
     * Alers module table to add extension fields.
     *
     * @param  string         $modId  Installed module id
     * @param  AMI_Tx_Cmd_Args $oArgs  Transaction command arguments
     * @return void
     */
    public function onModPostInstall($modId, AMI_Tx_Cmd_Args $oArgs){
        global $db;

        $oTable = AMI::getResourceModel($modId . '/table');
        if(!$oTable->hasField('ext_picture') && !$oTable->hasField('ext_img')){
            $table = $oTable->getTableName();
            $db->setSafeSQLOptions('alter');
            $sql =
                "ALTER TABLE " . $table . " " .
                /*
                "ADD `ext_picture` varchar(128) NOT NULL DEFAULT '', " .
                "ADD `ext_popup_picture` varchar(128) NOT NULL DEFAULT '', " .
                "ADD `ext_small_picture` varchar(128) NOT NULL DEFAULT ''";
                */
                "ADD `ext_img` varchar(128) NOT NULL DEFAULT '', " .
                "ADD `ext_img_popup` varchar(128) NOT NULL DEFAULT '', " .
                "ADD `ext_img_small` varchar(128) NOT NULL DEFAULT ''";
            $db->query($sql);
        }
    }

    /**
     * Extension pre-initialization.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handlePreInit($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent = parent::handlePreInit($name, $aEvent, $handlerModId, $srcModId);

        $modId = $aEvent['modId'];
        $side = AMI_Registry::get('side');

        $oView = $this->getView($side);
        if($oView){
            $oView->setExt($this);
            AMI_Event::addHandler('on_form_fields_form', array($oView, 'handleFormFields'), $modId);
        }
        // Add list column and list column callback
        $option = $this->doMapping ? 'col_picture_type' : 'ext_img_list_col';
        if($this->checkModOption($option) && $this->getModOption($option) != 'none'){
            AMI_Event::addHandler('on_list_recordset', array($this, 'handleListRecordset'), $modId);
            if($oView){
                AMI_Event::addHandler('on_list_columns', array($oView, 'handleListColumns'), $modId);
                AMI_Event::addHandler('on_list_body_row', array($oView, 'handleListBodyRow'), $modId);
            }
        }
        return $aEvent;
    }
}

/**
 * AmiExt/Image extension configuration admin view.
 *
 * @package    Config_AmiExt_Image
 * @subpackage View
 * @resource   ext_image/view/adm <code>AMI::getResource('ext_image/view/adm')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Image_ViewAdm extends AmiExt_Image_View{
    /**
     * Template block name
     *
     * @var string
     */
    protected $tplBlockName = 'image_ext';

    /**
     * Site url
     *
     * @var string
     */
    protected $wwwUrl;

    /**
     * Use column with big image
     *
     * @var boolean
     */
    protected $bUseHiddenColumn = false;

    /**
     * Initialize some data.
     */
    public function __construct(){
        global $Core;

        parent::__construct();

        // set up settings
        $this->bUseHiddenColumn =
            $Core->isInstalled('eshop_order') && AMI::issetAndTrueOption('eshop_order', 'show_image_in_order_details');
        $this->wwwUrl = AMI_Registry::get('path/www_root');
    }

    /**
     * Add picture column to admin list view.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView_JSON::get()
     */
    public function handleListColumns($name, array $aEvent, $handlerModId, $srcModId){
        $oView = $aEvent['oView'];
        $oView->addColumn('picture', 'flags.after');
        $oView->addColumnType('picture', 'image');
        $oView->setColumnAlign('picture', 'center');

        // add hidden column with BIG image for popups
        if(true == $this->bUseHiddenColumn){
            $oView->addColumn('picture_big');
            $oView->addColumnType('picture_big', 'hidden');
        }

        $option = $this->oExt->doMapping() ? 'col_picture_type' : 'ext_img_list_col';
        $this->aLocale['list_col_picture'] = $this->aLocale['list_col_' . $this->oExt->getModOption($option)];
        $oView->addLocale($this->aLocale);
        return $aEvent;
    }

    /**
     * Adds picture section to admin form.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModFormView::get()
     */
    public function handleFormFields($name, array $aEvent, $handlerModId, $srcModId){
        $oFormView = $this->oExt->getFormViewInstance($aEvent);
        if(!$oFormView){
            return $aEvent;
        }
        $oFormView->addLocale($this->aLocale);
        $option = $this->oExt->doMapping() ? 'item_pictures' : 'ext_img_fields';
        $aExtImages = $this->getExtImages($this->oExt->checkModOption($option) ? $this->oExt->getModOption($option) : array());
        if(!sizeof($aExtImages)){
            return $aEvent;
        }

        $oTpl = $this->getTemplate();
        $aScope = $aEvent['aScope'];
        $aScope['admin_lang'] = AMI_Registry::get('lang');
        $aScope['root_path_www'] = $GLOBALS["ROOT_PATH_WWW"];
        $oFormView->putPlaceholder('ext_image_fields', 'ext_image.begin', true);
        $oFormView->addTemplate($this->tplFileName, 'ext_image_fields', $this->aLocale);
        $oTpl->setBlockLocale($this->tplBlockName, $this->aLocale);
        // Hack for right pictures order
        foreach(array('ext_img_popup', 'ext_img', 'ext_img_small') as $img){
            if(in_array($img, $aExtImages)){
                $aScope['name'] = $img;
                $aScope['value'] = $aEvent['oItem']->getValue($img);
                $this->prepareImageData($aScope, $img);
                $oFormView->addField(
                    array(
                        'name'     => $img,
                        'position' => 'ext_image_fields.end',
                        'html'     => $oTpl->parse($this->tplBlockName . ':ext_img_field', $aScope)
                    )
                );
            }
        }
        $oFormView->addField(
            array(
                'name' => 'pictures_js',
                'position' => 'ext_image_fields.end',
                'html' => $oTpl->parse($this->tplBlockName . ':pictures_js', $aScope)
            )
        );
        return $aEvent;
    }

    /**
     * Preparing image data.
     *
     * @param  array &$vData  Image data
     * @param  string $name   Image name
     * @return void
     */
    private function prepareImageData(array &$vData, $name){
        if(!isset($vData["pictures_js_vars"])){
            $vData["pictures_js_vars"] = "";
            $vData["pictures_js_script"] = "";
        }

        $oTpl = $this->getTemplate();
        $oTpl->setBlockLocale($this->tplBlockName, $this->aLocale);
        $modId = $this->oExt->getModId();
        $imageCategory = AMI::issetProperty($modId, 'picture_cat') ? AMI::getProperty($modId, 'picture_cat') : '';
        $aPicData = array(
            'url' => 'ce_img_proc.php?cat=' . $imageCategory . '&fld=' . $name . '&lang=' . AMI_Registry::get('lang_data') . '&module=' . $this->oExt->getModId(),
            'id' => $name
        );
        $addPic = $oTpl->parse($this->tplBlockName . ':images_add', $aPicData);
        $editPic = $oTpl->parse($this->tplBlockName . ':images_edit', $aPicData);

        $optionCreate = $this->oExt->doMapping() ? 'generate_pictures' : 'ext_img_creatable';
        $optionSource = $this->oExt->doMapping() ? 'prior_source_picture' : 'ext_img_source';

        $vData['generate_' . $name] = in_array($name, $this->getExtImages($this->oExt->getModOption($optionCreate))) ? "1" : "0";
        $aPriorSrcImage = $this->getExtImages(array(0 => $this->oExt->getModOption($optionSource)));
        $vData['prior_source_picture'] = $aPriorSrcImage[0];
        $vData['url'] = $aPicData['url'];
        $vData['edit_'.$name] = empty($vData[$name]) ? $addPic : $editPic;
        $vData["pictures_js_vars"] .= $oTpl->parse($this->tplBlockName . ':pictures_js_vars', array("name" => $name, "add_value" => AMI_Lib_String::jparse($addPic), "edit_value" => AMI_Lib_String::jparse($editPic)));
        $vData["pictures_js_script"] .= $oTpl->parse($this->tplBlockName . ":pictures_js_script", array("name" => $name, "root_path_www" => $vData['root_path_www']));
    }

    /**
     * Fills item list picture column.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListBodyRow($name, array $aEvent, $handlerModId, $srcModId){
        $curModule = $this->oExt->getModId();
        $oTpl = $this->getTemplate();
        $oTpl->setBlockLocale($this->tplBlockName, $this->aLocale);
        $aItem = $aEvent['aScope'];
        if(!empty($aItem["id"])){
            $itemId = $aItem["id"];
        }elseif(!empty($aItem["cat_id"])){
            $itemId = $aItem["cat_id"];
        }

        $optionCreate = $this->oExt->doMapping() ? 'generate_pictures' : 'ext_img_creatable';
        $optionColumn = $this->oExt->doMapping() ? 'col_picture_type' : 'ext_img_list_col';
        $field = ($this->oExt->doMapping() ? 'ext_' : '') . $this->oExt->getModOption($optionColumn);

        if($this->oExt->checkModOption($optionCreate) && in_array($this->oExt->getModOption($optionColumn), $this->oExt->getModOption($optionCreate))){
            // Get image link
            $vRes = array();
            $level = 3;
            $origFilePath = null;
            $origFileLevel = 0;

            foreach(array('popup', '', 'small') as $picpref){
                if($this->oExt->doMapping()){
                    $field = $picpref ? 'ext_' . $picpref . '_picture' : 'ext_picture';
                }else{
                    $field = 'ext_img' . ($picpref ? '_' . $picpref : '');
                }
                if(!empty($aItem[$field])){
                    $origFilePath = $aItem[$field];
                    $origFileLevel = $level;
                    break;
                }
                $level --;
            }

            $preffx = $this->oExt->getModOption($optionColumn);
            if($this->oExt->doMapping()){
                if(($pos = mb_strpos($preffx, '_')) !== FALSE){
                    $preffx = mb_substr($preffx, 0, $pos + 1);
                }else{
                    $preffx = '';
                }
                $field = $preffx . 'picture';
            }else{
                $field = $preffx;
            }

            if($this->genLinkToPic($curModule, $preffx, $origFilePath, $origFileLevel, isset($aItem[$field]) ? $aItem[$field] : null, $itemId, $vRes, FALSE)){
                $imgData = array("img_url" => $vRes["src"]);
                $aItem['picture_col'] = $oTpl->parse($this->tplBlockName . ':picture_col', $imgData);
            }else{
                $aItem["picture_col"] = $oTpl->parse($this->tplBlockName . ':no_picture');
            }
        }else if(!empty($aItem[$field])){
            $aItem['picture_col'] = $oTpl->parse(
                $this->tplBlockName . ':picture_col',
                array('img_url' => $GLOBALS['ROOT_PATH_WWW'] . $aItem[$field])
            );
        }else{
            $aItem["picture_col"] = $oTpl->parse($this->tplBlockName . ':no_picture');
        }

        $aEvent['aScope']['picture'] = $aItem['picture_col'];

        // add hidden BIG image for popups
        if(true == $this->bUseHiddenColumn){
            $aEvent['aScope']['picture_big'] =
                $this->wwwUrl .
                (
                    isset($aItem['ext_img_popup'])
                        ? $aItem['ext_img_popup']
                        : $aItem['ext_popup_picture']
                );
        }

        return $aEvent;
    }

    /**
     * Return image extension names.
     *
     * @param  array $aPictures  Image extension option names
     * @return array
     */
    private function getExtImages(array $aPictures){
        if($this->oExt->doMapping()){
            $aExtImages = array();
            foreach($aPictures as $pic){
                if($pic == 'picture'){
                    $aExtImages[] = 'ext_img';
                }elseif($pic == 'small_picture'){
                    $aExtImages[] = 'ext_img_small';
                }elseif($pic == 'popup_picture'){
                    $aExtImages[] = 'ext_img_popup';
                }
            }
            return $aExtImages;
        }else{
            return $aPictures;
        }
    }
}
