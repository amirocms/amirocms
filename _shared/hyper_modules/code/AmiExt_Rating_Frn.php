<?php
/**
 * AmiExt/Rating extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiExt_Rating
 * @version   $Id: AmiExt_Rating_Frn.php 43359 2013-11-11 06:44:07Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/Rating extension configuration front controller.
 *
 * @package    Config_AmiExt_Rating
 * @subpackage Controller
 * @resource   ext_rating/module/controller/frn <code>AMI::getResource('ext_rating/module/controller/frn')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Rating_Frn extends Hyper_AmiExt{
    /**
     * Bitwise options names: "allow_ratings" etc.
     *
     * @var array
     */
    public $aRateOptions = array("allow_ratings", "display_ratings", "sort_by_ratings", "display_votes");

    /**
     * Rating values are rounded to this both on admin and front sides
     *
     * @var int
     */
    public $decimalPlaces;

    /**
     * Number of rating pics for rating
     *
     * @var int
     */
    public $numRatingPics = 5;

    /**
     * Default rating value
     *
     * @var float
     */
    public $defaultRating;

    /**
     * Minimum votes number for display
     *
     * @var int
     */
    public $minVotesToDisplay;

    /**
     * Rating form type
     *
     * @var string
     */
    public $formType;

    /**
     * Is user logged in
     *
     * @var bool
     */
    public $userLoggedIn;

    /**
     * Link to members area
     *
     * @var string
     */
    public $membersLink;

    /**
     * User Id
     *
     * @var int
     */
    public $userId;

    /**
     * Visitor id
     *
     * @var string
     */
    public $vid;

    /**
     * IP address
     *
     * @var long
     */
    public $ip;

    /**
     * Category module id
     *
     * @var string
     */
    public $catModuleId;

    /**
     * Is rating enabled for cat module
     *
     * @var bool
     */
    public $catRatingsEnabled = FALSE;

    /**
     * Module name
     *
     * @var string
     */
    public $frontModuleName;

    /**
     * Flag specifying to do fields mapping
     *
     * @var bool
     */
    protected $doMapping;

    /**
     * Extension fields mapping
     *
     * @var array
     */
    protected $aExtFields = array(
        'votes_rate'    => 'ext_rate_rate',
        'votes_count'   => 'ext_rate_count',
        'rate_opt'      => 'ext_rate_opt',
        'votes_weight'  => 'ext_rate_weight'
    );

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
     * Returns default rating options values.
     *
     * @return array
     */
    public function getDefaultRatingOptions(){
        $aRatingOptions = array();
        foreach($this->aRateOptions as $option){
            $aRatingOptions[$option] = $this->getOption($option);
        }
        return $aRatingOptions;
    }

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Extension initialization.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Ext::__construct()
     * @see    AMI_Mod::init()
     */
    public function handlePreInit($name, array $aEvent, $handlerModId, $srcModId){
        AMI_Event::addHandler('on_get_available_fields', array($this, 'handleGetAvailableFields'), $aEvent['modId']);
        AMI_Event::addHandler('on_add_field_mapping', array($this, 'handleAddFieldMapping'), $aEvent['modId']);
        AMI_Event::addHandler('on_list_recordset', array($this, 'handleListRecordset'), $aEvent['modId']);

        // Do not initialize other handlers in fast environment mode
        if(AMI::getEnvMode() == 'fast'){
            return $aEvent;
        }

        $modId = $this->getExtId();
        $oCms = $GLOBALS['frn'];

        $this->defaultRating = floatval(AMI::getOption($modId, "default_rating"));
        $this->decimalPlaces = intval(AMI::getOption($modId, 'rating_decimal_places'));
        $this->minVotesToDisplay = AMI::getOption($modId, "minimum_votes_to_display");

        $this->frontModuleName = $this->getModId();

        $this->gradeSize = intval(AMI::getOption($modId, "grade_size"));
        $this->formType = AMI::getOption($modId, "form_type");

        // #CMS-11482 {

        $formTemplate =
            $this->checkModOption('form_template')
                ? $this->getModOption('form_template')
                : $this->getOption('form_template');
        AMI_Registry::get('oGUI')
            ->addBlock(
                $handlerModId . '_rating_tpl',
                'templates/modules/ext_' . $formTemplate
            );

        // } #CMS-11482

        $this->userLoggedIn = is_object($oCms->Member) && $oCms->Member->isLoggedIn();

        $this->catModuleName = AMI::isCategoryModule($handlerModId) ? $handlerModId : $handlerModId . '_cat';
        if(AMI_ModDeclarator::getInstance()->isRegistered($this->catModuleName)){
            $this->catRatingsEnabled = AMI::issetOption($this->catModuleName, 'extensions') && in_array($modId, AMI::getOption($this->catModuleName, "extensions"));
        }

        $this->userId = 0;
        if($this->userLoggedIn){
            $this->userId = $oCms->Member->getUserInfo("id");
        }
        $this->ip = ip2long(getenv('REMOTE_ADDR'));
        if(isset($oCms->VarsCookie['vid'])){
            $this->vid = $oCms->VarsCookie['vid'];
        }else{
            $this->vid = md5(getenv('REMOTE_ADDR') . ':' . rand(0, 1000000) . ':' . microtime());
            SetLocalCookie('vid', $this->vid, time() + 315360000);
        }

        $oView = $this->getView('frn');
        $oView->setExt($this);

        AMI_Event::addHandler('on_before_view_items', array($oView, 'handleBeforeView'), $aEvent['modId']);
        AMI_Event::addHandler('on_before_view_small', array($oView, 'handleBeforeView'), $aEvent['modId']);
        AMI_Event::addHandler('on_before_view_details', array($oView, 'handleBeforeView'), $aEvent['modId']);
        AMI_Event::addHandler('on_before_view_cats', array($oView, 'handleBeforeView'), $aEvent['modId']);
        AMI_Event::addHandler('on_before_view_sticky_items', array($oView, 'handleBeforeView'), $aEvent['modId']);

        return $aEvent;
    }

    /**
     * Appends rating fields to field mapping.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTable::getAvailableFields()
     */
    public function handleAddFieldMapping($name, array $aEvent, $handlerModId, $srcModId){
        $this->doMapping = $aEvent['oTable']->hasField('votes_rate', FALSE);
        if($this->doMapping){
            $aEvent['aFields'] += array_flip($this->aExtFields);
        }

        return $aEvent;
    }

    /**
     * Appends rating fields to available fields.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTable::getAvailableFields()
     */
    public function handleGetAvailableFields($name, array $aEvent, $handlerModId, $srcModId){
        if(!in_array('ext_rate_rate', $aEvent['aFields'])){
            $aEvent['aFields'] +=
                $this->doMapping
                    ? array_keys($this->aExtFields)
                    : array_values($this->aExtFields);
        }

        return $aEvent;
    }


    /**
     * Add extension ratings fields.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListRecordset($name, array $aEvent, $handlerModId, $srcModId){
        if($handlerModId === $aEvent['modId']){
            /*
            $aEvent['oQuery']->addFields(array_keys($this->aExtFields));
            foreach($this->aExtFields as $field => $alias){
                $aEvent['oQuery']->addField($field, '', $alias);
            }
            */
            $aEvent['oQuery']
                ->addFields(
                    $this->doMapping
                        ? array_keys($this->aExtFields)
                        : array_values($this->aExtFields)
                );
        }

        return $aEvent;
    }

    /**#@-*/

    /**#@+
     * Bitwise functions for options operations.
     */

    /**
     * Gets one single bit from the TINYINT by position and returns it (0 or 1).
     *
     * If we get $bitPos out of range - return -1
     *
     * @param  string $num     Binary string
     * @param  int    $bitPos  The position of the required bit in the byte
     * @return int
     */
    public function getOptionBit($num, $bitPos){
        if(mb_strlen($num) > $bitPos){
            return $num[mb_strlen($num) - $bitPos - 1];
        }else{
            return (-1);
        }
    }

    /**
     * Sets/unsets one single bit in the binary number and returns the modified number.
     *
     * If we get $bitPos out of range - return the number untouched.
     *
     * @param  string $num     Binary string
     * @param  int    $bitPos  The position of the required bit in the byte
     * @param  int    $val     Bit value
     * @return string
     */
    public function setOptionBit($num, $bitPos, $val){
        if(mb_strlen($num) > $bitPos){
            $num[mb_strlen($num) - $bitPos - 1] = $val;
        }
        return $num;
    }

    /**#@-*/
}

/**
 * AmiExt/Rating extension configuration front view.
 *
 * @package    Config_AmiExt_Rating
 * @subpackage View
 * @resource   ext_rating/view/frn <code>AMI::getResource('ext_rating/view/frn')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Rating_ViewFrn extends AMI_ExtView{
    /**
     * Template block name
     *
     * @var string
     */
    protected $tplBlockName = 'rating_ext';

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Fills admin item list rating/votes columns.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::get()
     */
    public function handleVotesBlock($name, array $aEvent, $handlerModId, $srcModId){
        $aItem = &$aEvent['aData'];
        $oExt = $this->oExt;
        $modId = $handlerModId;
        $oCore = $GLOBALS['Core'];

        // todo: explore this fix
        if(!isset($aItem['ext_rate_rate'])){
            return $aEvent;
        }

        $aItem["module_name"] = $modId;
        list($hyper, $config) = AMI_ModDeclarator::getInstance()->getHyperData($modId);
        $aItem["config_name"] = $config;
        $aItem["rating_img"] = round((($oExt->numRatingPics) * ($aItem["ext_rate_rate"])) / (($oExt->defaultRating) * 2 - 1), 0);
        $aItem["votes_rate"] = round($aItem["ext_rate_rate"], $oExt->decimalPlaces);
        $aItem["votes_count"] = $aItem["ext_rate_count"];

        // read the options and display relevant data for the current item
        $itemOptions = "0000000" . decbin($aItem["ext_rate_opt"]);
        $aItem['rating_block'] = $aItem['votes_block'] = '';
        if(AMI::getOption($oExt->getExtId(), "minimum_votes_to_display") <= $aItem["ext_rate_count"]){
            if($oExt->getOptionBit($itemOptions, array_search("display_ratings", $oExt->aRateOptions))){
                $aItem["rating_block"] = AMI_Registry::get('oGUI')->get($modId . "_rating_tpl:rating_block", $aItem);
            }
            if($oExt->getOptionBit($itemOptions, array_search("display_votes", $oExt->aRateOptions))){
                $aItem["votes_block"] = AMI_Registry::get('oGUI')->get($modId . "_rating_tpl:votes_block", $aItem);
            }
        }
        return $aEvent;
    }

    /**
     * Category rating data in details mode.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleCatRatingItemDetails($name, array $aEvent, $handlerModId, $srcModId){
        $aData = &$aEvent['aData'];
        $catId = AMI_Registry::get('page/catId');
        if($catId){
            $doMapping  = $this->oExt->doMapping();
            $rateField  = $doMapping ? 'votes_rate'  : 'ext_rate_rate';
            $countField = $doMapping ? 'votes_count' : 'ext_rate_count';
            $oCat =
                AMI::getResourceModel($this->oExt->catModuleName . '/table')
                    ->find(
                        $catId,
                        array(
                            'id',
                            $rateField,
                            $countField
                        )
                    );
            $aCatData = $oCat->getData();
            if(AMI::getOption($this->oExt->getExtId(), "minimum_votes_to_display") <= $countField){
                $aData["cat_votes_rate"] = $aCatData[$rateField];
                $aData["cat_votes_count"] = $aCatData[$countField];
                $aData["cat_votes_rate"] = round($aData["cat_votes_rate"], $this->oExt->decimalPlaces);
                $aData["cat_ext_rate_img"] = round((($this->oExt->numRatingPics) * ($aData["cat_ext_rate_rate"])) / (($this->oExt->defaultRating) * 2 - 1), 0);
                $aData["cat_rating_block"] = AMI_Registry::get('oGUI')->get($this->oExt->getModId() . "_rating_tpl:cat_rating_block", $aData);
                $aData["cat_votes_block"] = AMI_Registry::get('oGUI')->get($this->oExt->getModId() . "_rating_tpl:cat_votes_block", $aData);
            }
        }
        return $aEvent;
    }

    /**
     * On before view event handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::get()
     */
    public function handleBeforeView($name, array $aEvent, $handlerModId, $srcModId){
        $bodyType = $aEvent['type'];
        $showform = true;
        $oExt = $this->oExt;
        $oCore = $GLOBALS['Core'];
        switch($bodyType){
            case 'cats':
                if($oExt->catRatingsEnabled){
                    // AMI_Event::addHandler('on_add_field_mapping', array($this->oExt, 'handleAddFieldMapping'), $oExt->catModuleName);
                    AMI_Event::addHandler('on_get_available_fields', array($this->oExt, 'handleGetAvailableFields'), $oExt->catModuleName);
                    AMI_Event::addHandler('on_list_recordset', array($this->oExt, 'handleListRecordset'), $oExt->catModuleName);
                    AMI_Registry::get('oGUI')->addBlock($oExt->catModuleName . "_rating_tpl", "templates/" . AMI::getOption($oExt->getExtId(), "form_template"));
                    // cats ratings callback
                    // !!! $vCustom["fields"]["cat_rating_data"] = Array("action"=>"callback", "object"=>&$this, "method"=>"_GetFrontItemCB");
                }
                break;
            case "items":
            case 'sticky_items':
            case "filtered":
                if($oExt->catRatingsEnabled){
                    // !!! $this->_GetFrontItemCB($vData);
                    // AMI_Event::addHandler('on_list_body_row', array($this, 'handleListBodyRow'), $modId);
                    if(!AMI::getOption($oExt->catModuleName, "average_cat_rating")){
                        $oExt->frontModuleName = $oExt->catModuleName;
                        $catId = AMI_Registry::get('page/catId');
                        if($catId && (!AmiExt_Rating_Service::isAlreadyRated($catId))){
                            AMI_Event::addHandler('on_list_body_row', array($this, 'handleAddRatingForm'), $oExt->frontModuleName);
                        }
                        $oExt->frontModuleName = $oExt->getModId();
                    }
                }
                break;
            case "browse":
            case "details":
                if($oExt->catRatingsEnabled){
                    AMI_Event::addHandler('on_item_details', array($this, 'handleCatRatingItemDetails'), $oExt->getModId());
                }
                break;
        }

        // ratings callback
        AMI_Event::addHandler('on_list_body_row', array($this, 'handleVotesBlock'), $handlerModId);
        AMI_Event::addHandler('on_item_details', array($this, 'handleVotesBlock'), $handlerModId);

        // get the members link
        if($oCore->IsInstalled("members") && AMI::getOption($oExt->getExtId(), "rating_by_registered_only")){
            $mMembers = $oCore->GetModule("members");
            $this->oExt->membersLink = $mMembers->GetFrontLink();
        }

        // add a confirm box set
        if(AMI::getOption($oExt->getExtId(), "rating_by_registered_only") && !$oExt->userLoggedIn){
            AMI_Event::addHandler('on_list_body_row', array($this, 'handleAddConfirmation'), $handlerModId);
            AMI_Event::addHandler('on_item_details', array($this, 'handleAddConfirmation'), $handlerModId);
        }

        // form callback
        $aMapping = array(
            'items'         => 'body_items',
            'details'       => 'body_itemD',
            'sticky_items'  => 'body_urgent_items',
            'filtered'      => 'body_filtered'
        );
        $mappedBT = isset($aMapping[$bodyType]) ? $aMapping[$bodyType] : false;
        if($showform && in_array($mappedBT, AMI::getOption($oExt->getExtId(), "show_form_in"))){
            AMI_Event::addHandler('on_list_body_row', array($this, 'handleAddRatingForm'), $handlerModId);
            AMI_Event::addHandler('on_item_details', array($this, 'handleAddRatingForm'), $handlerModId);
        }

        return $aEvent;
    }

    /**
     * Adds rating form to item scope.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleAddRatingForm($name, array $aEvent, $handlerModId, $srcModId){
        $aSkipFormBodyTypes = array('subitems');
        if(isset($aEvent['type']) && in_array($aEvent['type'], $aSkipFormBodyTypes)){
            return $aEvent;
        }
        $aItem = &$aEvent['aData'];
        $oExt = $this->oExt;

        $itemOptions = "0000000" . decbin($aItem['ext_rate_opt']);
        if($oExt->getOptionBit($itemOptions, array_search('allow_ratings', $this->oExt->aRateOptions))){
            $oGUI = AMI_Registry::get('oGUI');
            $frontModId = $oExt->frontModuleName;
            $modId = $oExt->getModId();
            // assemble the rating form
            $aItem["id_module"] = $frontModId;
            list($hyper, $config) = AMI_ModDeclarator::getInstance()->getHyperData($frontModId);
            $aItem["config_name"] = $config;
            $aItem["module_name"] = $frontModId;
            $aItem["rating_img"] = round((($oExt->numRatingPics) * ($aItem["ext_rate_rate"])) / (($oExt->defaultRating) * 2 - 1), 0);
            $aItem["votes_rate"] = round($aItem["ext_rate_rate"], $oExt->decimalPlaces);
            $aItem["votes_count"] = $aItem["ext_rate_count"];
            $aItem["grade_size"] = $oExt->gradeSize;
            $aItem["rating_form_body"] = $oGUI->get($modId . "_rating_tpl:rating_" . $oExt->formType . "_open", $aItem);
            for($i = 0; $i < $oExt->gradeSize; $i++){
                $aItem["rating_value"] = $i;
                $aItem["rating_text"] = "item " . $i;
                $aItem["rating_form_body"] .= $oGUI->get($modId . "_rating_tpl:rating_" . $oExt->formType . "_items", $aItem);
            }
            $aItem["rating_form_body"] .= $oGUI->get($modId . "_rating_tpl:rating_" . $oExt->formType . "_close", $aItem);
            $aItem["rating_form"] = $oGUI->get($modId . "_rating_tpl:rating_form", $aItem);
        }
        return $aEvent;
    }

    /**
     * Adds confirmation to item scope.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleAddConfirmation($name, array $aEvent, $handlerModId, $srcModId){
        $aItem = &$aEvent['aData'];
        $aItem["confirm_register"] = "true";
        $aItem["register_link"] = $this->oExt->membersLink;

        return $aEvent;
    }

    /**#@-*/
}
