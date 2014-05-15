<?php
/**
 * AmiExt/Adv extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiExt_Adv
 * @version   $Id: AmiExt_Adv_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/Adv extension configuration admin controller.
 *
 * @package    Config_AmiExt_Adv
 * @subpackage Controller
 * @resource   ext_adv/module/controller/adm <code>AMI::getResource('ext_adv/module/controller/adm')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Adv_Adm extends Hyper_AmiExt{
    /**
     * Advertising places
     *
     * @var array
     */
    public $aAdvPlaces = array();

    /**
     * Show advertising places column
     *
     * @var bool
     */
    public $showAdvPlace = false;

    /**
     * Show advertising stats column
     *
     * @var bool
     */
    public $showAdvStat = false;

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
        $modId = $aEvent['modId'];
        $side = AMI_Registry::get('side');
        $oView = $this->getView($side);
        $oView->setExt($this);

        AMI_Event::addHandler('on_get_available_fields', array($this, 'handleGetAvailableFields'), $modId);

        if($this->checkModOption('show_adv_place_columns') && $this->getModOption('show_adv_place_columns')){
            $this->showAdvPlace = true;
            AMI_Event::addHandler('on_list_init', array($this, 'handleListInit'), $modId);
        }
        if($this->checkModOption('show_adv_stat_columns') && $this->getModOption('show_adv_stat_columns')){
            $this->showAdvStat = true;
        }
        if($this->showAdvPlace || $this->showAdvStat){
            AMI_Event::addHandler('on_list_recordset', array($this, 'handleListRecordset'), $modId);
            AMI_Event::addHandler('on_list_columns', array($oView, 'handleListColumns'), $modId);
            AMI_Event::addHandler('on_list_body_row', array($oView, 'handleListBodyRow'), $modId);
        }
        AMI_Event::addHandler('on_form_fields_form', array($oView, 'handleFormFields'), $modId);

        return $aEvent;
    }

    /**
     * Appends advertising fields to available fields.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTable::getAvailableFields()
     */
    public function handleGetAvailableFields($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['aFields'][] = 'adv_place';
        return $aEvent;
    }

    /**
     * Get advertising places.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListInit($name, array $aEvent, $handlerModId, $srcModId){
        $this->getAdvPlaces($aEvent['modId']);

        return $aEvent;
    }

    /**
     * Add extension advertising fields.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListRecordset($name, array $aEvent, $handlerModId, $srcModId){
        if($this->showAdvPlace){
            $aEvent['oQuery']->addField('adv_place');
        }
        if($this->showAdvStat){
            $aEvent['oQuery']->addFields(array('shown_items', 'shown_details'));
            $alias = $aEvent['oQuery']->getMainTableAlias();
            if($alias){
                $alias .= '.';
            }
            $aEvent['oQuery']->addExpressionField('IF('.$alias.'shown_items = 0, 0, '.$alias.'shown_details/'.$alias.'shown_items) shown_ctr');
        }
        return $aEvent;
    }

    /**#@-*/

    /**
     * Fills advertising places info.
     *
     * @param  string $modId  Module Id
     * @return array
     */
    public function getAdvPlaces($modId){
        $oQuery = new DB_Query('cms_adv_groups');
        $oQuery->addField('name');
        $oQuery->addExpressionField('(1000000000+id) AS id');
        $oQuery->addWhereDef(DB_Query::getSnippet('AND lang = %s')->q(AMI_Registry::get('lang_data')));
        $oQuery->addOrder('name');
        $oRS = AMI::getSingleton('db')->select($oQuery);
        if($oRS->count()){
            foreach($oRS as $aRow){
                $this->aAdvPlaces[$aRow['id']] = $aRow['name'];
            }
        }

        $oQuery = new DB_Query('cms_adv_places');
        $oQuery->addFields(array('id', 'name'));
        $oQuery->addWhereDef(DB_Query::getSnippet('AND lang = %s AND source_type = %s AND module_name LIKE %s')->q(AMI_Registry::get('lang_data'))->q('modbanner')->q('%;'.$modId.';%'));
        $oQuery->addOrder('name');
        $oRS = AMI::getSingleton('db')->select($oQuery);
        if($oRS->count()){
            foreach($oRS as $aRow){
                $this->aAdvPlaces[$aRow['id']] = $aRow['name'];
            }
        }

        return $this->aAdvPlaces;
    }

    /**
     * Fills advertising campaign types info.
     *
     * @param  string $modId  Module Id
     * @return array
     */
    public function getAdvCampaignTypes($modId){
        $aAdvCampaignTypes = array();

        if(mb_strpos($modId, "_cat") !== false){
            $reqModuleName = str_replace("_cat", "_item", $modId);
            $oQuery = new DB_Query('cms_adv_campaign_types', 'c');
            $oQuery->addFields(array('id', 'name'));
            $oQuery->addJoinedTable('cms_adv_places', 'p', 'POSITION(CONCAT(\';\',p.id,\';\') IN c.ids_places)>0');
            $oQuery->addWhereDef(DB_Query::getSnippet('AND c.lang = %s AND p.source_type = %s AND p.module_name LIKE %s')->q(AMI_Registry::get('lang_data'))->q('module')->q('%;'.$reqModuleName.';%'));
            $oQuery->addOrder('c.name');
            $oRS = AMI::getSingleton('db')->select($oQuery);
            if($oRS->count()){
                foreach($oRS as $aRow){
                    $aAdvCampaignTypes[] = array(
                        'id'   => $aRow['id'],
                        'name' => $aRow['name']
                    );
                }
            }
        }

        return $aAdvCampaignTypes;
    }
}

/**
 * AmiExt/Adv extension configuration admin view.
 *
 * @package    Config_AmiExt_Adv
 * @subpackage View
 * @resource   ext_adv/view/adm <code>AMI::getResource('ext_adv/view/adm')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Adv_ViewAdm extends AMI_ExtView{
    /**
     * Template file name
     *
     * @var string
     */
    protected $tplFileName = null;

    /**
     * Add advertising columns to admin list view.
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

        if($this->oExt->showAdvPlace){
            $oView->addColumn('adv_place', 'actions.before');
            $oView->addColumnType('adv_place', 'int');
        }

        if($this->oExt->showAdvStat){
            $oView->addColumn('shown_ctr', 'actions.before');
            $oView->addColumnType('shown_ctr', 'int');
        }

        $oView->addLocale($this->aLocale);
        return $aEvent;
    }

    /**
     * Fills item list advertising columns.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListBodyRow($name, array $aEvent, $handlerModId, $srcModId){
        if($this->oExt->showAdvPlace){
            $aEvent['aScope']['adv_place'] = isset($this->oExt->aAdvPlaces[$aEvent['aScope']['adv_place']]) ? ((($aEvent['aScope']['adv_place'] > 1000000000) ? $this->aLocale['adv_group'].': ' : '').$this->oExt->aAdvPlaces[$aEvent['aScope']['adv_place']]) : '';
        }
        if($this->oExt->showAdvStat){
            $aEvent['aScope']['shown_ctr'] = $aEvent['aScope']['shown_items'].'/'.$aEvent['aScope']['shown_details'].'/'.sprintf("%.2f", $aEvent['aScope']['shown_ctr']*100);
        }
        return $aEvent;
    }

    /**
     * Adds advertising fields to admin form.
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

        $aAdvPlaces = array();
        $this->oExt->getAdvPlaces($aEvent['aScope']['_mod_id']);
        foreach($this->oExt->aAdvPlaces as $idPlace => $namePlace){
            $aAdvPlaces[] = array(
                'id' => $idPlace,
                'name' => (($idPlace > 1000000000) ? $this->aLocale['adv_group'].': ' : '').$namePlace
            );
        }

        if(sizeof($aAdvPlaces)){
            $aAdvPlacesField = array(
                'name'         => 'adv_place',
                'position'     => 'ext_adv.end',
                'type'         => 'select',
                'data'         => $aAdvPlaces,
                'not_selected' => array(
                    'id' => '0',
                    'caption' => 'no_adv_place'
                ),
                'value'        => $aEvent['oItem']->getValue('adv_place')
            );
            $oFormView->addField($aAdvPlacesField);
        }

        $aAdvCampaignTypes = $this->oExt->getAdvCampaignTypes($aEvent['aScope']['_mod_id']);
        if(sizeof($aAdvCampaignTypes)){
            $aAdvCampaignsField = array(
                'name'         => 'adv_campaign_type',
                'position'     => 'ext_adv.end',
                'type'         => 'select',
                'data'         => $aAdvCampaignTypes,
                'not_selected' => array(
                    'id' => '0',
                    'caption' => 'no_adv_campaign_types'
                ),
                'value'        => $aEvent['oItem']->getValue('adv_campaign_type')
            );
            $oFormView->addField($aAdvCampaignsField);
        }

        $oFormView->addLocale($this->aLocale);

        return $aEvent;
    }
}
