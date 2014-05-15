<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModRules.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.14.4
 */

/**
 * Module rules callbacks.
 *
 * @package    ModuleComponent
 */
abstract class AMI_ModRules{
    const GET_VAL = 'getvalue';
    const SET_VAL = 'apply';
    const GET_ALL = 'getallvalues';
    const CORRECT = 'correctvalue';

    /**
     * Module instance
     *
     * @var AMI_Mod
     */
    protected $oModInstance = null;

    /**
     * Categories list
     *
     * @var array
     */
    protected $aCategories = null;

    /**
     * Module Id
     *
     * @var string
     */
    protected $modId = null;

    /**
     * Returns available module extensions.
     *
     * @param  string $modId  Module id
     * @return array
     * @see    AMI_ModRules::ruleExtensionsCB()
     * @see    AMI_Tx_ModInstall::runExtensions()
     * @amidev Temporary?
     */
    public static function getAvailableExts($modId){
        $aAvailableExts = AMI_Ext::getAviableModExtensions($modId);
        // Hack for Custom Fields
        // Todo - remove with something.
        if(
            in_array('ext_custom_fields', $aAvailableExts) &&
            !in_array('ext_modules_custom_fields', $aAvailableExts)
        ){
            $aAvailableExts[] = 'ext_modules_custom_fields';
        }
        // Backward hack for ext_images

        if(
            in_array('ext_images', $aAvailableExts) &&
            !in_array('ext_image', $aAvailableExts)
        ){
            unset($aAvailableExts[array_search('ext_images', $aAvailableExts)]);
        }

        return $aAvailableExts;
    }

    /**
     * Constructor.
     */
    public function  __construct(){
    }

    /**
     * Handler for ##modId##:correctStopArgNames callback.
     *
     * @param mixed $callbackData   Data for callback
     * @param mixed &$optionsData    Options data
     * @param string $callbackMode  Callback mode
     * @param mixed &$result         Result data
     * @param array &$aData          Data
     * @return bool
     */
    public function correctStopArgNames($callbackData, &$optionsData, $callbackMode, &$result, array &$aData){
        switch($callbackMode){
            case self::GET_VAL:
            case self::SET_VAL:
                $result = $callbackData["value"];
                $oCore = $GLOBALS['Core'];
                $propModId = $result ? $this->getModId() . '_cat' : $this->getModId();
                $optValue = $oCore->getModProperty($propModId, "stop_arg_names");
                $oCore->setModOption($this->getModId(), "stop_arg_names", $optValue);
        }
        return true;
    }

    /**
     * Handler for ##modId##:getOptionsModCB callback.
     *
     * @param mixed $callbackData   Data for callback
     * @param mixed &$optionsData    Options data
     * @param string $callbackMode  Callback mode
     * @param mixed &$result         Result data
     * @param array &$aData          Data
     * @return bool
     */
    public function getOptionsModCB($callbackData, &$optionsData, $callbackMode, &$result, array &$aData){
        if(!is_array($callbackData["value"])){
            $callbackData["value"] = ($callbackData["value"] != "") ? array($callbackData["value"]) : array();
        }
        switch($callbackMode){
            case self::GET_ALL:
                $result = array();
                if(!is_array($this->aCategories)){
                    $this->aCategories = array();
                    $oList = AMI::getResourceModel($this->getModId() . '/table/model')->getList();
                    $oList->addColumns(array('id', 'header'))->load();
                    foreach($oList as $oItem){
                        $this->aCategories[] = array(
                            "name"      => $oItem->getId(),
                            "caption"   => $oItem->header,
                            "selected"  => ((in_array($oItem->getId(), $callbackData["value"])) ? "selected" : "")
                        );
                    }
                }
                $aData['allow_empty'] = true;
                $result = $this->aCategories;
                break;
            case self::CORRECT:
                $aIds = array();
                if(is_array($this->aCategories)){
                    foreach($this->aCategories as $aData){
                        $aIds[] = $aData["name"];
                    }
                }
                $result = array_intersect($aIds, $callbackData["value"]);
                break;
            case self::GET_VAL:
                $result = $callbackData["value"];
                break;
        }
        return true;
    }

    /**
     * Handler for ##modId##:setImagesWatermark callback.
     *
     * @param mixed $callbackData   Data for callback
     * @param mixed &$optionsData    Options data
     * @param string $callbackMode  Callback mode
     * @param mixed &$result         Result data
     * @param array &$aData          Data
     * @todo Move to the photoalbums rules class
     * @return bool
     */
    public function setImagesWatermark($callbackData, &$optionsData, $callbackMode, &$result, array &$aData){
        $oCore = $GLOBALS['Core'];
        switch($callbackMode){
            case self::GET_VAL:
                $result = $oCore->getModOption($this->getModId(), "static_watermark");
                break;
            case self::SET_VAL:
                if($oCore->getModOption($this->getModId(), "static_watermark") != $callbackData['value']){
                    require_once($GLOBALS["FUNC_INCLUDES_PATH"]."func_file_system.php");
                    $path = $GLOBALS["ROOT_PATH"] . $GLOBALS["CUSTOM_PICTURES_HTTP_PATH"] . $this->getModId() . "/generated/";
                    $list = getDirFileList($path, "*_swt*");
                    foreach($list as $file){
                        @unlink($path . $file);
                    }
                }
                break;
        }
        return true;
    }

    /**
     * Handler for ##modId##:ruleExtensionsCB callback.
     *
     * @param  mixed  $callbackData  Data for callback
     * @param  mixed  &$optionsData  Options data
     * @param  string $callbackMode  Callback mode
     * @param  mixed  &$result       Result data
     * @param  array  &$aData        Data
     * @return bool
     */
    public function ruleExtensionsCB($callbackData, &$optionsData, $callbackMode, &$result, array &$aData){
        $aAllowedValues = $callbackData['cbAllowedValues'];
        $aAvailableExts = self::getAvailableExts($this->getModId());
        $oResponse = AMI::getSingleton('response');
        $oRequest = AMI::getSingleton('env/request');

        $aIntsalledAllowedValues = array_values(array_intersect($aAvailableExts, $aAllowedValues));
        unset($aAvailableExts);
        $passedValue = $callbackData['value'];

        switch($callbackMode){
            case self::GET_ALL:
                static $words = null;
                if(is_null($words)){
                    $oTpl = AMI::getSingleton('env/template_sys');
                    $words50 = $oTpl->parseLocale('templates/lang/options/default_rules_values.lng');
                    $words60 = $oTpl->parseLocale(AMI_iTemplate::LOCAL_LNG_MOD_PATH . '/_menu.lng');
                    $words = array_merge($words50, $words60);
                    // Hack for Custom Fields caption.
                    // Todo - remove with something.
                    if(isset($words['ext_custom_fields'])){
                        $words['ext_modules_custom_fields'] = $words['ext_custom_fields'];
                    }
                }
                $result = array();
                foreach($aIntsalledAllowedValues as $value){
                    if(!isset($words[$value])){
                        trigger_error('Undefined caption for ' . $optionsData['OptionsModuleName'] . " '" . $value . "'", E_USER_WARNING);
                    }
                    $result[] = array(
                        'name'     => $value,
                        'caption'  => isset($words[$value]) ? $words[$value] : '<b>' . $value . '</b>',
                        'selected' => in_array($value, $passedValue) ? 'selected' : ''
                    );
                }
                if(sizeof($result)){
                    require_once $GLOBALS['FUNC_INCLUDES_PATH'] . 'func_array.php';
                    sortMultiArray($result, 'caption');
                }
                $aData['allow_empty'] = true;
                break; // case 'getallvalues'
            case self::CORRECT:
                $result = array();
                foreach($passedValue as $value){
                    if(in_array($value, $aAllowedValues)){
                        $result[] = $value;
                    }
                }
                break; // case 'correctvalue'
            case self::GET_VAL:
                $result = $passedValue;
                break; // case 'getvalue'
            case self::SET_VAL:
                /*
                $previousValue = AMI::getOption($optionsData['OptionsModuleName'], $sourceOptionName);
                if($previousValue != $passedValue){
                    // $aIntsalledAllowedValues = array_values(array_intersect($this->_aInstalledModules, $aAllowedValues));
                    foreach($previousValue as $value){
                        if(!in_array($value, $passedValue) && !in_array($value, $aIntsalledAllowedValues)){
                            $passedValue[] = $value;
                        }
                    }
                    $aData['real_value'] = $passedValue;
                }
                */
                break; // case 'apply'
        }
        return true;
    }


    /**
     * Returns module id.
     *
     * @return string
     */
    public function getModId(){
        if(empty($this->modId)){
            $this->modId = AMI::getModId(get_class($this));
        }
        return $this->modId;
    }

    /**
     * Set module id.
     *
     * @param string $modId  Module id
     * @return void
     * @amidev Temporary
     */
    public function setModId($modId){
        $this->modId = $modId;
    }

    /**
     * Clean off module instance.
     *
     * @return void
     */
    public function cleanup(){
        $this->oModInstance = null;
        $this->aCategories = null;
    }
}