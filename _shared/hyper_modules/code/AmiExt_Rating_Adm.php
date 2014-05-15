<?php
/**
 * AmiExt/Rating extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiExt_Rating
 * @version   $Id: AmiExt_Rating_Adm.php 45138 2013-12-06 10:22:18Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/Rating extension configuration admin controller.
 *
 * @package    Config_AmiExt_Rating
 * @subpackage Controller
 * @resource   ext_rating/module/controller/adm <code>AMI::getResource('ext_rating/module/controller/adm')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Rating_Adm extends Hyper_AmiExt{
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
     * Callback called after module is installed (stub).
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
        $table = $oTable->getTableName();
        $db->setSafeSQLOptions('alter');
        $sql =
            "ALTER TABLE " . $table . " " .
            "ADD `ext_rate_opt` tinyint(1) NOT NULL DEFAULT '15', " .
            "ADD `ext_rate_rate` float(7,6) unsigned NOT NULL DEFAULT '0.000000', " .
            "ADD `ext_rate_count` int(11) unsigned NOT NULL DEFAULT '0', " .
            "ADD `ext_rate_weight` int(11) unsigned NOT NULL DEFAULT '0'";
        $db->query($sql);
    }

    /**
     * Callback called after module is uninstalled.
     *
     * Cleans up uninstalled module data.
     *
     * @param  string         $modId  Unnstalled module id
     * @param  AMI_Tx_Cmd_Args $oArgs  Transaction command arguments
     * @return void
     */
    public function onModPostUninstall($modId, AMI_Tx_Cmd_Args $oArgs){
        $oDB = AMI::getSingleton('db');

        $oQuery =
            DB_Query::getSnippet(
                "SHOW TABLES LIKE %s"
            )->q('cms_rate_history');
        if($oDB->fetchRow($oQuery)){
            $oQuery =
                DB_Query::getSnippet(
                    "DELETE FROM `cms_rate_history` " .
                    "WHERE `id_module` = %s"
                )->q($modId);
            $oDB->query($oQuery);
        }
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
        $this->defaultRating = floatval($this->GetOption("default_rating"));
        $this->decimalPlaces = intval($this->GetOption('rating_decimal_places'));
        $this->minVotesToDisplay = $this->GetOption("minimum_votes_to_display");

        AMI_Event::addHandler('on_add_field_mapping', array($this, 'handleAddFieldMapping'), $aEvent['modId']);
        AMI_Event::addHandler('on_get_available_fields', array($this, 'handleGetAvailableFields'), $aEvent['modId']);
        AMI_Event::addHandler('on_list_recordset', array($this, 'handleListRecordset'), $aEvent['modId']);

        $oView = $this->getView('adm');
        $oView->setExt($this);

        AMI_Event::addHandler('on_form_fields_form', array($oView, 'handleForm'), $aEvent['modId']);
        AMI_Event::addHandler('on_before_save_model_item', array($this, 'handleSaveModelItem'), $aEvent['modId']);
        AMI_Event::addHandler('on_list_body_row', array($oView, 'handleListBodyRow'), $aEvent['modId']);
        AMI_Event::addHandler('on_list_columns', array($oView, 'handleListColumns'), $aEvent['modId']);
        AMI_Event::addHandler('on_list_sort_columns', array($oView, 'handleSortListColumns'), $aEvent['modId']);

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
            $aEvent['oQuery']
                ->addFields(
                    $this->doMapping
                        ? array_keys($this->aExtFields)
                        : array_values($this->aExtFields)
                );
        }

        return $aEvent;
    }

    /**
     * Save model item handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTableItem::setData()
     */
    public function handleSaveModelItem($name, array $aEvent, $handlerModId, $srcModId){
        if($handlerModId === $this->getModId()){
            // Set rating options
            $optionsVal = "00000000";
            foreach($this->aRateOptions as $optIndex => $optName){
                if(isset($aEvent['aData'][$optName]) && $aEvent['aData'][$optName]){
                    $optionsVal = $this->setOptionBit($optionsVal, $optIndex, 1);
                }
            }
            $aEvent['aData']["ext_rate_opt"] = bindec($optionsVal);
            $aEvent['oItem']->ext_rate_opt = $aEvent['aData']["ext_rate_opt"];
        }
        return $aEvent;
    }

    /**
     * Processes front action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchRate($name, array $aEvent, $handlerModId, $srcModId){
        $aRequestScope = AMI::getSingleton('env/request')->getScope();
        if(isset($aRequestScope["id_item"]) && intval($aRequestScope["id_item"])){
            $idItem = intval($aRequestScope["id_item"]);

            $aInsertHistory = array();
            // $aInsertHistory["id"] = "LAST_INSERT_ID()";
            $aInsertHistory["id_module"] = $aEvent['modId'];
            $aInsertHistory["id_item"] = $idItem;

            $sql = DB_Query::getInsertQuery('cms_rate_history', $aInsertHistory);
            AMI::getSingleton('db')->query($sql);

            $oResModel = AMI::getResourceModel($this->getModId() . '/table');
            $idColumn = $oResModel->getItem()->getPrimaryKeyField();
            if(!$idColumn){
                $idColumn = 'id';
            }

            // fetch the old ratings

            // recalculate counters, weights & ratings
            $aUpdateModule = array();
            $aUpdateModule['ext_rate_count'] = DB_Query::getSnippet('%s')->plain('ext_rate_count+1');
            $sql = DB_Query::getUpdateQuery(
                $oResModel->getTableName(),
                $aUpdateModule,
                'WHERE '.$idColumn.' = '.$idItem
            );
            AMI::getSingleton('db')->query($sql);
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
 * AmiExt/Rating extension configuration admin view.
 *
 * @package    Config_AmiExt_Rating
 * @subpackage View
 * @resource   ext_rating/view/adm <code>AMI::getResource('ext_rating/view/adm')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Rating_ViewAdm extends AMI_ExtView{
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
    public function handleListBodyRow($name, array $aEvent, $handlerModId, $srcModId){
        $aScope = array(
            'ext_rate_rate'  => round($aEvent['aScope']['ext_rate_rate'], $this->oExt->decimalPlaces),
            'ext_rate_count' => $aEvent['aScope']['ext_rate_count']
        );
        $oTpl = $this->getTemplate();
        $aEvent['aScope']['ext_rate_rate'] = $oTpl->parse($this->tplBlockName . ':ext_rate_rate_column', $aScope);
        $aEvent['aScope']['ext_rate_count'] = $oTpl->parse($this->tplBlockName . ':ext_rate_count_column', $aScope);
        return $aEvent;
    }

    /**
     * Adds votes/rating columns to admin list.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::get()
     */
    public function handleListColumns($name, array $aEvent, $handlerModId, $srcModId){
        if($handlerModId === $this->oExt->getModId()){
            $aEvent['oView']->addColumnType('ext_rate_count', 'int');
            $aEvent['oView']->setColumnWidth('ext_rate_count', 'narrow');
            $aEvent['oView']->addColumnType('ext_rate_rate', 'int');
            $aEvent['oView']->setColumnWidth('ext_rate_rate', 'narrow');
        }
        $aEvent['oView']->addLocale($this->aLocale);
        return $aEvent;
    }

    /**
     * Adds sorting for votes/rating columns to admin list.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::get()
     */
    public function handleSortListColumns($name, array $aEvent, $handlerModId, $srcModId){
        if($handlerModId === $this->oExt->getModId()){
            $aEvent['aColumns'][] = 'ext_rate_count';
            $aEvent['aColumns'][] = 'ext_rate_rate';
        }
        return $aEvent;
    }

    /**
     * Adds rating options onto admin form.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleForm($name, array $aEvent, $handlerModId, $srcModId){
        if(!empty($aEvent['oFormView'])){

            $aRateOptionsChecks = array();
            $optionsVal = "0000000".decbin($aEvent['oItem']->ext_rate_opt);
            foreach($this->oExt->aRateOptions as $optIndex => $optName){
                if($aEvent['oItem']->id){
                    $aRateOptionsChecks[$optName] = ($this->oExt->getOptionBit($optionsVal, $optIndex) == 1);
                }else{
                    $aDefOpts = $this->oExt->getDefaultRatingOptions();
                    $aRateOptionsChecks[$optName] = $aDefOpts[$optName];
                }
            }

            /**
             * @var AMI_iModFormView
             */
            $oView = $aEvent['oFormView'];

            $oView->putPlaceholder('ext_rating_values', 'options_tab.end', true);
            $oView->addTemplate($this->tplFileName, 'ext_rating_values', $this->aLocale);

            $oView->addField(array('name' => 'rewrite_ratings', 'type' => 'checkbox', 'position' => 'ext_rating_values.end'));
            $oView->addField(array('name' => 'ext_rate_count', 'position' => 'ext_rating_values.end'));
            $oView->addField(array_merge(array('name' => 'ext_rate_rate', 'position' => 'ext_rating_values.end', 'validate' => array('custom', 'stop_on_error')), ($aEvent['oItem']->id) ? array() : array('value' => $this->oExt->defaultRating)));
            $oView->addField(array('name' => 'allow_ratings', 'type' => 'checkbox', 'value' => $aRateOptionsChecks['allow_ratings'], 'position' => 'ext_rating_values.end'));
            $oView->addField(array('name' => 'display_ratings', 'type' => 'checkbox', 'value' => $aRateOptionsChecks['display_ratings'], 'position' => 'ext_rating_values.end'));
            $oView->addField(array('name' => 'sort_by_ratings', 'type' => 'checkbox', 'value' => $aRateOptionsChecks['sort_by_ratings'], 'position' => 'ext_rating_values.end'));
            $oView->addField(array('name' => 'display_votes', 'type' => 'checkbox', 'value' => $aRateOptionsChecks['display_votes'], 'position' => 'ext_rating_values.end'));

            $oView->addLocale($this->aLocale);
        }

        return $aEvent;
    }
    /**#@-*/
}
