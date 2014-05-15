<?php
/**
 * AmiExt/Image extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Config_AmiExt_Image
 * @version   $Id: AmiExt_Image.php 47117 2014-01-28 13:56:01Z Kolesnikov Artem $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/Image extension configuration action controller.
 *
 * Common admin/front code.
 *
 * @package    Config_AmiExt_Image
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class AmiExt_Image extends Hyper_AmiExt{
    /**
     * Flag specifying to use obsolete options format and DB fields mapping.
     *
     * @var bool
     */
    protected $doMapping;

    /**
     * Array of extension fields
     *
     * @var array
     */
    protected $aExtFields = array(
        'ext_picture'       => 'ext_img',
        'ext_small_picture' => 'ext_img_small',
        'ext_popup_picture' => 'ext_img_popup'
    );

    /**
     * Autogeneration GET-parameters postfixes
     *
     * @var array
     */
    protected $aExtFieldToPostfix = array(
        'ext_img'       => '_pc',
        'ext_img_small' => '_sm',
        'ext_img_popup' => ''
    );

    /**
     * Autogeneration allowable to resize image types
     *
     * @var array
     */
    protected $aAllowableResize = array();

    /**
     * Returns mapping flag.
     *
     * @return bool
     * @amidev
     */
    public function doMapping(){
        return $this->doMapping;
    }

    /**
     * Returns file name postfixes for creating imsges.
     *
     * @return array
     * @amidev
     */
    public function getPostfixes(){
        return $this->aExtFieldToPostfix;
    }

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
     * @todo   Avoid hack with 'blog'
     */
    public function handlePreInit($name, array $aEvent, $handlerModId, $srcModId){
        $this->doMapping =
            $this->optSrcId == 'blog' ||
            (
                AMI::isSingletonInitialized('core')
                    ? AMI::getSingleton('core')->issetModOption($this->optSrcId, 'item_pictures')
                    : AMI::issetOption($this->optSrcId, 'item_pictures')
            );

        $modId = $aEvent['modId'];

        $aExtFieldToPostfix = array();
        if($this->doMapping){
            foreach($this->aExtFields as $field => $apiField){
                $field = mb_substr($field, 4); // cut 'ext_' prefix
                $property = 'generate_' . $field . '_postf';
                if(AMI::issetProperty($this->getModId(), $property)){
                    $aExtFieldToPostfix[$apiField] = AMI::getProperty($modId, $property);
                }
            }
        }elseif(AMI::issetProperty($this->getModId(), 'ext_img_postfixes')){
            $aExtFieldToPostfix = AMI::getProperty($modId, 'ext_img_postfixes');
        }
        $this->aExtFieldToPostfix = $aExtFieldToPostfix + $this->aExtFieldToPostfix;

        if($this->doMapping){
            AMI_Event::addHandler('on_add_field_mapping', array($this, 'handleAddFieldMapping'), $modId);
        }
        AMI_Event::addHandler('on_get_available_fields', array($this, 'handleGetAvailableFields'), $modId);

        $option = $this->doMapping ? 'generate_pictures' : 'ext_img_creatable';
        $this->aAllowableResize = $this->checkModOption($option) ? AMI::getOption($this->optSrcId, $option) : array();
        if(!is_array($this->aAllowableResize)){
            $this->aAllowableResize = array();
        }
        return $aEvent;
    }

    /**
     * Appends image fields to field mapping.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTable::getAvailableFields()
     */
    public function handleAddFieldMapping($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['aFields'] += array_flip($this->aExtFields);
        return $aEvent;
    }

    /**
     * Appends image fields to available fields.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTable::getAvailableFields()
     */
    public function handleGetAvailableFields($name, array $aEvent, $handlerModId, $srcModId){
        if(!in_array($this->aExtFields['ext_picture'], $aEvent['aFields'])){
            foreach($this->aExtFields as $field){
                $aEvent['aFields'][] = $field;
            }
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
        $aEvent['oQuery']->addFields($this->doMapping ? array_keys($this->aExtFields) : $this->aExtFields);
        return $aEvent;
    }

    /**#@-*/
}

/**
 * AmiExt/Image extension configuration view.
 *
 * Common admin/front code.
 *
 * @package    Config_AmiExt_Image
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class AmiExt_Image_View extends AMI_ExtView{
    /**
     * Generate and return link to picture, or return false.
     *
     * @param  string $curModule      Module
     * @param  string $cPicType       Picture type
     * @param  string $cOrigPicPath   Orig pic path
     * @param  int    $origFileLevel  Orig file file
     * @param  string $cPicPath       Pic path
     * @param  int    $cRecID         Rec id
     * @param  array  &$vRes          Result
     * @param  string $lnkAddon       Link addon
     * @return bool
     * @todo  Document it
     * @amidev Temporary
     */
    protected function genLinkToPic($curModule, $cPicType, $cOrigPicPath, $origFileLevel, $cPicPath, $cRecID, array &$vRes, $lnkAddon = false){

        $aEvent = array(
            'modId' => &$curModule
        );
        AMI_Event::fire('on_image_link_generation', $aEvent, $curModule);

        // Base initialization
        $vRes = array("width" => 0, "height" => 0, "src" => "", "param" => "", "_return" => false);

        if(!empty($cPicPath) and $cOrigPicPath != $cPicPath){
            $cOrigPicPath = $cPicPath;
        }

        // Get current picture storing subpath
        $picSubPath = $curModule;
        $isInst = false;
        /*
        // Import issues
        if(mb_strpos($picSubPath, "inst_") === 0){
            $isInst = true;
            $picSubPath = mb_substr($picSubPath, 5);
        }
 
        */
        if(($pos = mb_strpos($picSubPath, "_")) !== false){
            $picSubPath = mb_substr($picSubPath, 0, $pos);
        }
        if($isInst){
            $picSubPath = 'inst_' . $picSubPath;
        }

        $genPics = array();
        $option = $this->oExt->doMapping() ? 'generate_pictures' : 'ext_img_creatable';
        if($this->oExt->getModOption($option)){
            $genPics = $this->oExt->getModOption($option);
        }

        $origFileName = "";
        $origFileExt = "";
        $origFileX = 0;
        $origFileY = 0;

        // Get original file dimension
        if(!empty($cOrigPicPath) && sizeof($genPics) > 0 && is_file($GLOBALS['ROOT_PATH'].$cOrigPicPath)){
            $aPicData = AMI_Lib_Image::imageGetSize($GLOBALS['ROOT_PATH'].$cOrigPicPath);
            $origFileX = $aPicData[0];
            $origFileY = $aPicData[1];
            if(($pos = mb_strrpos($cOrigPicPath, ".")) !== false){
                $origFileExt = mb_substr($cOrigPicPath, $pos + 1);
                $origFileName = mb_substr($cOrigPicPath, 0, $pos);
            }
        }

        // Check if picture supposed to be generated
        $possiblyWillBeGenerated = false;
        if($this->oExt->doMapping()){
            $field = $cPicType . 'picture';
            $optionMaxWidth = $cPicType . 'picture_maxwidth';
            $optionMaxHeght = $cPicType . 'picture_maxheight';
            $doCreateBigger = $this->oExt->getModOption('generate_bigger_image') && $cPicType != 'popup_';
        }else{
            // Compatibility hack
            if(strpos($cPicType, 'ext_') !== 0){
                $aExtMapping = array(
                    '' => 'ext_img',
                    'popup_' => 'ext_img_popup',
                    'small_' => 'ext_img_small'
                );
                $cPicType = $aExtMapping[$cPicType];
            }
            $field = $cPicType;
            $optionMaxWidth = $cPicType . '_maxwidth';
            $optionMaxHeght = $cPicType . '_maxheight';
            $doCreateBigger = $this->oExt->getModOption('ext_img_create_bigger') && $cPicType != 'ext_img_popup';
        }
        if(!empty($origFileName) && in_array($field, $genPics) && (empty($cPicPath) || $cPicPath == $cOrigPicPath)){
            $possiblyWillBeGenerated = true;
        }

        // Return false if no picture for the item expected
        if(empty($cPicPath) && !($possiblyWillBeGenerated && $origFileX > 0 && $origFileY > 0 &&
            $this->oExt->getModOption($optionMaxWidth) > 0 && $this->oExt->getModOption($optionMaxHeght) > 0)){
            return FALSE;
        }

        // Create current picture data
        if(!empty($cPicPath) && ($size = AMI_Lib_Image::imageGetSize($GLOBALS['ROOT_PATH'].$cPicPath))){
            $src = mb_strpos($cPicPath, $GLOBALS['ROOT_PATH_WWW']) !== 0 ? $GLOBALS['ROOT_PATH_WWW'] . $cPicPath : $cPicPath;
            $vRes['width'] = $size[0];
            $vRes['height'] = $size[1];
            $vRes['param'] = $src;
            $vRes['src'] = $src;

            // Return true if no picture for the item will be generated and current picture is real
            if(!($possiblyWillBeGenerated && $origFileX > 0 && $origFileY > 0 && $this->oExt->getModOption($optionMaxWidth) > 0 && $this->oExt->getModOption($optionMaxHeght) > 0)){
                $vRes["_return"] = true;
                return true;
            }
        }

        // Check level of the file that should be generated
        $aLevelMap =
            $this->oExt->doMapping()
            ? array(
                'popup_' => 3,
                'small_' => 1,
                ''       => 2
            )
            : array(
                'ext_img_popup' => 3,
                'ext_img_small' => 1,
                'ext_img'      => 2,
            );

        $currLevel = isset($aLevelMap[$cPicType]) ? $aLevelMap[$cPicType] : 0;

        if($origFileLevel < $currLevel){
            $vRes["_return"] = !empty($vRes["src"]);
            return !empty($vRes["src"]);
        }

        if($origFileX === 0 or $origFileY === 0){
            return false;
        }

        // no not generate if dimension of original is equal to generated
        if(!empty($cOrigPicPath) && sizeof($genPics) > 0 && is_file($GLOBALS['ROOT_PATH'].$cOrigPicPath)){
            $_newFileX = $origFileX;
            $_newFileY = $origFileY;
            $newFileMaxX = $this->oExt->getModOption($optionMaxWidth);
            $newFileMaxY = $this->oExt->getModOption($optionMaxHeght);
            if($_newFileX > $newFileMaxX || $_newFileY > $newFileMaxY || ($_newFileX < $newFileMaxX && $_newFileY < $newFileMaxY && $doCreateBigger)){
                if($_newFileX / $newFileMaxX >= $_newFileY / $newFileMaxY){
                    $_newFileY = ceil($_newFileY*$newFileMaxX/$_newFileX);
                    $_newFileX = $newFileMaxX;
                }else{
                    $_newFileX = ceil($_newFileX*$newFileMaxY/$_newFileY);
                    $_newFileY = $newFileMaxY;
                }
            }

            if($_newFileX == $origFileX and $_newFileY == $origFileY){
                $vRes["width"] = $origFileX;
                $vRes["height"] = $origFileY;
                $vRes["param"] = $GLOBALS['ROOT_PATH_WWW'].$cOrigPicPath;
                $vRes["src"] = $GLOBALS['ROOT_PATH_WWW'].$cOrigPicPath;
                $vRes["_return"] = true;
                return true;
            }
        }

        // Create current picture virtual data if should be created
        if($possiblyWillBeGenerated){
            $newFileMaxX = $this->oExt->getModOption($optionMaxWidth);
            $newFileMaxY = $this->oExt->getModOption($optionMaxHeght);
            $newFileX = $origFileX ? $origFileX : $newFileMaxX;
            $newFileY = $origFileY ? $origFileY : $newFileMaxY;
            if($newFileX > $newFileMaxX || $newFileY > $newFileMaxY || ($newFileX < $newFileMaxX && $newFileY < $newFileMaxY && $doCreateBigger)){
                if($newFileX / $newFileMaxX >= $newFileY / $newFileMaxY){
                    $newFileY = ceil($newFileY*$newFileMaxX/$newFileX);
                    $newFileX = $newFileMaxX;
                }else{
                    $newFileX = ceil($newFileX*$newFileMaxY/$newFileY);
                    $newFileY = $newFileMaxY;
                }
            }

            $path = $GLOBALS['ROOT_PATH'] . AMI_Registry::get('CUSTOM_PICTURES_HTTP_PATH') . $picSubPath . '/generated';
            if(!is_dir($path)){
                if(!mkdir($path, 0777, TRUE)){
                    trigger_error("ExtImages::genLinkToPic: unable to create path '" . $path . "'", E_USER_WARNING);
                    return FALSE;
                }else{
                    chmod($path, 0777);
                }
            }
            $aPostfixes = $this->oExt->getPostfixes();
            $pfx = $this->oExt->doMapping() ? rtrim('ext_img_' . trim($cPicType, '_'), '_') : $cPicType;

            $genFileName =
                AMI_Registry::get('CUSTOM_PICTURES_HTTP_PATH') . $picSubPath . '/generated/' . basename($origFileName) . '_' . $newFileX . 'x' . $newFileY .
                (isset($aPostfixes[$pfx]) ? $aPostfixes[$pfx] : '') .
                (AMI::issetProperty($curModule, 'watermark_postf') ? AMI::getProperty($curModule, 'watermark_postf') : '') .
                (AMI::issetAndTrueProperty($curModule, 'static_watermark') && mb_substr($cPicType, 'small') !== FALSE ? '_swt' : '') .
                '.' . $origFileExt;

            $checkFileName =
                AMI_Registry::get('CUSTOM_PICTURES_HTTP_PATH') . $picSubPath . '/generated/' . basename($origFileName) . '_' . $newFileX . 'x' . $newFileY .
                '.' . $origFileExt;

            $vRes['width'] = $newFileX;
            $vRes['height'] = $newFileY;

            if(is_file($GLOBALS['ROOT_PATH'] . $checkFileName)){
                $vRes['param'] = $GLOBALS['ROOT_PATH_WWW'] . $checkFileName;
                $vRes['src'] = $GLOBALS['ROOT_PATH_WWW'] . $checkFileName;
            }else{
                $type = $this->oExt->doMapping() ? $cPicType . 'picture' : $cPicType;
                $vRes["param"] = "sname=" . rawurlencode($genFileName) . "&src=___gen=1|mod=" . $curModule . "|id=" . $cRecID . "|type=" . $type . "|lang=" . AMI_Registry::get('lang_data');
                $vRes["param"] .= $lnkAddon;
                $vRes["src"] = $GLOBALS['ROOT_PATH_WWW']."show_pic.php?".$vRes["param"];
            }
        }

        if(!empty($vRes["src"]) && !empty($vRes["param"]) && $vRes["width"] > 0 && $vRes["height"] > 0){
            $vRes["_return"] = true;
            return true;
        }else{
            return false;
        }
    }
}