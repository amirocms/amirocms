<?php
/**
 * AmiExt/Image extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Config_AmiExt_Image
 * @version   $Id: AmiExt_Image_Frn.php 47117 2014-01-28 13:56:01Z Kolesnikov Artem $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/Image extension configuration front controller.
 *
 * @package    Config_AmiExt_Image
 * @subpackage Controller
 * @resource   ext_image/module/controller/frn <code>AMI::getResource('ext_image/module/controller/frn')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Image_Frn extends AmiExt_Image{
    /**
     * Autogeneration source image type
     *
     * @var string
     * @see AmiExt_Image_Frn::checkGenerationAbility()
     */
    protected $srcImageField;

    /**
     * Extension modId
     *
     * @var string
     */
    protected $extId = 'ext_image';

    /**
     * Current module Id
     *
     * @var string
     */
    protected $curModuleId = '';

    /**
     * Extension View
     *
     * @var AMI_View
     */
    protected $oView = null;

    /**
     * Sync mode body type
     *
     * @var string
     */
    protected $bodyType;

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

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
        $this->curModuleId = $modId;

        if($this->doMapping){
            AMI_Event::addHandler('on_add_field_mapping', array($this, 'handleAddFieldMapping'), $modId . '_cat');
        }
        AMI_Event::addHandler('on_get_available_fields', array($this, 'handleGetAvailableFields'), $modId . '_cat');
        AMI_Event::addHandler('on_item_details', array($this, 'handleSetData'), $modId);
        AMI_Event::addHandler('on_list_recordset', array($this, 'handleListRecordset'), $modId);
        AMI_Event::addHandler('on_list_recordset', array($this, 'handleListRecordset'), $modId . '_cat');
        AMI_Event::addHandler('on_table_get_item_post', array($this, 'handleAfterGetItem'), $modId . '_cat');
        AMI_Event::addHandler('on_before_set_data_model_item', array($this, 'handleSetData'), $modId);
        AMI_Event::addHandler('on_before_set_data_model_item', array($this, 'handleSetData'), $modId . '_cat');

        $this->bodyType = AMI_Registry::get('AMI/Module/Environment/bodyType', '');
        if($this->bodyType){
            $oView = $this->getView('frn');
            if($oView){
                $oView->setExt($this);
                $this->oView = $oView;
                AMI_Event::addHandler('on_before_view_items', array($oView, 'handleBeforeView'), $modId);
                AMI_Event::addHandler('on_before_view_sticky_items', array($oView, 'handleBeforeView'), $modId);
                AMI_Event::addHandler('on_before_view_sticky_cats', array($oView, 'handleBeforeView'), $modId);
                AMI_Event::addHandler('on_before_view_items', array($oView, 'handleBeforeView'), $modId);
                AMI_Event::addHandler('on_before_view_details', array($oView, 'handleBeforeView'), $modId);
                AMI_Event::addHandler('on_list_body_row', array($oView, 'handleListBodyRow'), $modId);
                AMI_Event::addHandler('on_item_details', array($oView, 'handleItemDetails'), $modId);
                AMI_Event::addHandler('on_get_category_details', array($oView, 'handleCategoryDetails'), $modId);
            }
        }
        return $aEvent;
    }
    /**#@-*/

    /**
     * Update current module on set item data.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleSetData($name, array $aEvent, $handlerModId, $srcModId){
        $this->curModuleId = $srcModId;
        if(!$this->bodyType){
            $this->setAsyncData($aEvent);
        }
        return $aEvent;
    }

    /**
     * Add extension images fields.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListRecordset($name, array $aEvent, $handlerModId, $srcModId){
        $this->curModuleId = $srcModId;
        $aEvent = parent::handleListRecordset($name, $aEvent, $handlerModId, $srcModId);
        return $aEvent;
    }

    /**
     * Saves current item modId.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleAfterGetItem($name, array $aEvent, $handlerModId, $srcModId){
        $this->curModuleId = $srcModId;
        return $aEvent;
    }

    /**
     * Get working module Id.
     *
     * @return string
     * @amidev Temporary
     */
    public function getCurModuleId(){
        return $this->curModuleId;
    }

    /**
     * Sets fields in async mode.
     *
     * @param  array &$aEvent  Event data
     * @return void
     */
    protected function setAsyncData(array &$aEvent){
        $aEmptyFields = array();
        if($this->doMapping){
            foreach($this->aExtFields as $deprecField => $field){
                if(isset($aEvent['aData'][$field]) && empty($aEvent['aData'][$field]) && in_array(mb_substr($deprecField, 4), $this->aAllowableResize)){
                    $aEmptyFields[] = $field;
                }
            }
        }else{
            foreach($this->aExtFields as $field){
                if(isset($aEvent['aData'][$field]) && empty($aEvent['aData'][$field]) && in_array($field, $this->aAllowableResize)){
                    $aEmptyFields[] = $field;
                }
            }
        }
        if(sizeof($aEmptyFields) && $this->checkGenerationAbility($aEvent['aData'])){
            $optionCreateBigger = $this->doMapping ? 'generate_bigger_image' : 'ext_img_create_bigger';

            $aFlippedMap = array_flip($this->aExtFields);
            foreach($aEmptyFields as $field){
                $optNamePart = $this->doMapping ? mb_substr($aFlippedMap[$field], 4) : $field;
                $srcImagePath = $aEvent['aData'][$this->srcImageField];
                $aSrcImageSize = AMI_Lib_Image::imageGetSize($srcImagePath);
                if(is_array($aSrcImageSize)){
                    // generate other image sizes
                    $maxWidth = (int)AMI::getOption($this->getModId(), $optNamePart . '_maxwidth');
                    $maxHeight = (int)AMI::getOption($this->getModId(), $optNamePart . '_maxheight');
                    if(!$maxWidth || !$maxHeight){
                        // there is no options for this image
                        continue;
                    }
                    $width = $aSrcImageSize[0] ? $aSrcImageSize[0] : $maxWidth;
                    $height = $aSrcImageSize[1] ? $aSrcImageSize[1] : $maxHeight;
                    if(
                        $width > $maxWidth ||
                        $height > $maxHeight || (
                            $width < $maxWidth &&
                            $height < $maxHeight &&
                            AMI::getOption($this->getModId(), $optionCreateBigger)
                            && mb_strpos($field, 'popup') === false
                        )
                    ){
                        if($width / $maxWidth >= $height / $maxHeight){
                            $height = ceil($height * $maxWidth / $width);
                            $width = $maxWidth;
                        }else{
                            $width = ceil($width * $maxHeight / $height);
                            $height = $maxHeight;
                        }
                    }
                    $aPathInfo = pathinfo($srcImagePath);
                    $namePostfix = '_' . $width . 'x' . $height . $this->aExtFieldToPostfix[$field];
                    $newPath =
                        $aPathInfo['dirname'] .
                        (mb_strpos($srcImagePath, '/generated/') === false ? '/generated' : '') .
                        '/' . $aPathInfo['filename'] . $namePostfix . '.' . $aPathInfo['extension'];
                    if(file_exists($newPath)){
                        $aEvent['aData'][$field] = $newPath;
                    }else{
                        $aEvent['aData'][$field] =
                            $GLOBALS['ROOT_PATH_WWW'] . 'show_pic.php?' .
                            'sname=' . rawurlencode($newPath) .
                            '&src=___gen=1|mod=' . $this->getModId() . '|id=' . $aEvent['aData']['id'] .
                                '|type=' . $optNamePart .
                                '|lang=' . (isset($aEvent['aData']['lang']) ? $aEvent['aData']['lang'] : 'en');
                    }
                }
            }
        }
    }

    /**
     * Checks autogeneration ability.
     *
     * @param  array $aData  Event data (item fields)
     * @return bool
     */
    protected function checkGenerationAbility(array $aData){
        if(is_null($this->srcImageField)){
            $option = $this->doMapping ? 'prior_source_picture' : 'ext_img_source';
            $this->srcImageField = (string)AMI::getOption($this->optSrcId, $option);
            if(isset($this->aExtFields['ext_' . $this->srcImageField])){
                // convert CMS option to mapped value
                $this->srcImageField = $this->aExtFields['ext_' . $this->srcImageField];
            }
        }
        return
            !empty($this->srcImageField) &&
            !empty($aData[$this->srcImageField]);
    }
}

/**
 * AmiExt/Image extension configuration front view.
 *
 * @package    Config_AmiExt_Image
 * @subpackage View
 * @resource   ext_image/view/frn <code>AMI::getResource('ext_image/view/frn')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Image_ViewFrn extends AmiExt_Image_View{
    /**
     * Template block name
     *
     * @var string
     */
    protected $tplBlockName = 'image_ext';

    /**
     * Template simple fields prefix
     *
     * @var string
     * @amidev Temporary
     */
    protected $tplSimpleFieldPrefix = '';

    /**
     * Data to initialize cat images when module view retreived
     *
     * @var array
     * @amidev Temporary
     */
    protected $initCatImagesData = null;

    /**
     * Flag specifies that category images data added to global vars
     *
     * @var bool
     * @amidev Temporary
     */
    protected $catImagesInitialized = false;
    /**
     * Gets module block name.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleBeforeView($name, array $aEvent, $handlerModId, $srcModId){
        $this->tplBlockName = $aEvent['block'];
        $this->aLocale = array_merge($aEvent['aLocale'], $this->aLocale);
        $this->tplSimpleFieldPrefix = isset($aEvent['tpl_sf_prefix']) ? ('' . $aEvent['tpl_sf_prefix']) : '';
        if(!is_null($this->initCatImagesData) && is_array($this->initCatImagesData) && !$this->catImagesInitialized){
            $this->initCatImages($this->initCatImagesData);
            $this->catImagesInitialized = true;
        }
        return $aEvent;
    }


    /**
     * Category details hanler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleCategoryDetails($name, array $aEvent, $handlerModId, $srcModId){
        $this->curModuleId = $srcModId . '_cat';
        $this->initCatImagesData = $aEvent['oItem']->getData();
        return $aEvent;
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
        $useData = isset($aEvent['aData']);
        if($useData){
            $aEvent['aData'] = $this->getImages($aEvent['aData']);
        }else{
            $aEvent['aScope'] = $this->getImages($aEvent['aScope']);
        }
        return $aEvent;
    }

    /**
     * Fills item details picture.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleItemDetails($name, array $aEvent, $handlerModId, $srcModId){
        $this->tplSimpleFieldPrefix = 'itemD_';
        $aEvent['aData'] = $this->getImages($aEvent['aData']);
        return $aEvent;
    }

    /**
     * Initialize category images.
     *
     * @param  array $aItem  Category item data
     * @return void
     */
    public function initCatImages(array $aItem){
        $oldPrefix = $this->tplSimpleFieldPrefix;
        $bodyType = AMI_Registry::get('AMI/Module/Environment/bodyType', false);
        $this->tplSimpleFieldPrefix = ($bodyType == 'details') ? 'itemD_cat_' : 'item_cat_';
        $aImages = $this->getImages($aItem);
        $this->tplSimpleFieldPrefix = $oldPrefix;
        AMI_Registry::get('oGUI')->addGlobalVars(
            array(
                'cat_img'        => isset($aImages['img']) ? $aImages['img'] : '',
                'cat_img_small'  => isset($aImages['img_small']) ? $aImages['img_small'] : '',
                'cat_img_popup'  => isset($aImages['img_popup']) ? $aImages['img_popup'] : '',
            )
        );
    }

    /**
     * Returns current item images.
     *
     * @param array $aItem  Item data
     * @return array
     */
    private function getImages(array $aItem){
        $curModule = $this->oExt->getCurModuleId();
        $bodyType = AMI_Registry::get('AMI/Module/Environment/bodyType', false);
        // CMS-11440 #1
        if(empty($aItem['cat_id']) && ($bodyType == 'cats') && !AMI::isCategoryModule($curModule)){
            $curModule .= '_cat';
        }
        $tplPrefix = $this->tplSimpleFieldPrefix;
        if(AMI::isCategoryModule($curModule)){
            $tplPrefix = ($bodyType == 'cats') ? 'cat_' : (($bodyType == 'details') ? 'itemD_cat_' : 'item_cat_');
        }
        $oTpl = $this->getTemplate();

        $vRes = array();
        $vResP = array();
        $picData = array();
        // Get image link
        $level = 3;
        $itemId = 0;
        if(!empty($aItem["id"])){
            $itemId = $aItem["id"];
        }elseif(!empty($aItem["cat_id"])){
            $itemId = $aItem["cat_id"];
        }
        $origFilePath = null;
        $origFileLevel = null;
        foreach(array("_popup", "", "_small") as $picPfx){
            $picpref = !empty($picPfx) ? (str_replace('_', '', $picPfx) . '_') : '';
            if(!empty($aItem['ext_img' . $picPfx])){
                $aItem[$picpref . 'picture'] = $aItem['ext_img' . $picPfx];
            }else if(!empty($aItem['ext_' . $picpref . 'picture'])){
                $aItem[$picpref . 'picture'] = $aItem['ext_' . $picpref . 'picture'];
            }
        }
        foreach(array("popup_", "", "small_") as $picpref){
            if(!empty($aItem[$picpref."picture"])){
                $origFilePath = $aItem[$picpref."picture"];
                $origFileLevel = $level;
                break;
            }
            $level --;
        }

        $aPicData = array();
        foreach(array('', 'small_') as $preffx){
            $field = $preffx . "picture";
            $key = empty($preffx) ? 'img' : 'img_small';
            if($this->genLinkToPic($curModule, $preffx, $origFilePath, $origFileLevel, isset($aItem[$field]) ? $aItem[$field] : null, $itemId, $vRes, FALSE)){
                $aPicData = array_merge(
                    $aPicData,
                    array(
                        'img'       => $vRes["src"],
                        'picture'   => $vRes["src"],
                        'src'       => $vRes["src"],
                        'title'     => $aItem['header'],
                        'alt'       => $aItem['header'],
                        'alt_js'    => AMI_Lib_String::jParse(str_replace('&#039;', "'", $aItem['header'])),
                        'width'     => $vRes["width"],
                        'height'    => $vRes["height"],
                        $preffx."picture_width" => $vRes["width"],
                        $preffx."picture_height" => $vRes["height"]
                    )
                );

                $aItem[$preffx."picture_src"] = $vRes["src"];
                $aItem[$preffx."picture_title"] = $aItem['header'];
                $aItem[$preffx."picture_width"] = $vRes["width"];
                $aItem[$preffx."picture_height"] = $vRes["height"];

                // Create the small picture code
                if($preffx == "small_"){
                    $aItem["small_img_popup"] = $this->parse($tplPrefix . $key, $aPicData);
                    $aItem[$key] = $aItem["small_img_popup"];
                }

                // Create popup picture if required
                if($this->genLinkToPic($curModule, 'popup_', $origFilePath, $origFileLevel, isset($aItem['popup_picture']) ? $aItem['popup_picture'] : null, $itemId, $vResP, FALSE)){
                    if($preffx == "" || empty($aItem["img"])){
                        $aItem["popup_picture_src"] = $vResP["param"];
                        $aItem["popup_picture_width"] = $vResP["width"];
                        $aItem["popup_picture_height"] = $vResP["height"];

                        $aPicData["src"] = $vResP["param"];
                        $aPicData["width"] = $vResP["width"];
                        $aPicData["height"] = $vResP["height"];

                        // Create popup picture code
                        if($preffx == ""){
                            $aItem["img"] = $this->parse($tplPrefix . 'img_popup', $aPicData);
                            // from _ActionApplyVars
                            if(($bodyType == 'details') && !AMI::isCategoryModule($curModule) && !empty($aItem['img'])){
                                $aBigPic = array(
                                    'img'   => $vRes["src"],
                                    'title' => $aItem['header']
                                );
                                $aItem["big_img"] = $this->parse($tplPrefix . 'big_img', $aBigPic);
                            }
                        }
                    }
                    if($preffx == "small_"){
                        $aItem["small_img_popup"] = $this->parse($tplPrefix . 'small_img_popup', $aPicData);
                    }

                // If do not create popup make picture code
                }else if($preffx == ""){
                    $aItem["img"] = $this->parse($tplPrefix . "img", $aPicData);
                    if(($bodyType == 'details') && !AMI::isCategoryModule($curModule) && !empty($aItem['img']) && empty($aItem['big_img'])){
                        $aBigPic = array(
                            'img'   => $vRes["src"],
                            'title' => $aItem['header']
                        );
                        $aItem["big_img"] = $this->parse($tplPrefix . 'big_img', $aBigPic);
                    }
                }
            }else{
                $aItem[$key] = '';
            }
        }
        $aReplacement = array(
            'small_picture'     => 'img_small',
            'popup_picture'     => 'img_popup',
            'picture'           => 'img'
        );

        // CMS-11246#CommentId=74120
        foreach($aItem as $key => $value){
            foreach($aReplacement as $repKey => $repValue){
                if(strpos($key, $repKey) === 0){
                    $replace = str_replace($repKey, $repValue, $key);
                    if(!isset($aItem[$replace])){
                        $aItem[$replace] = $value;
                    }
                    break;
                }
            }
        }

        $aItem['ext_img'] =  !empty($aItem['picture_src']) ? $aItem['picture_src'] : '';
        $aItem['ext_img_small'] = !empty($aItem['small_picture_src']) ? $aItem['small_picture_src'] : '';
        $aItem['ext_img_popup'] = !empty($aItem['popup_picture_src']) ? $aItem['popup_picture_src'] : '';

        return $aItem;
    }
}
