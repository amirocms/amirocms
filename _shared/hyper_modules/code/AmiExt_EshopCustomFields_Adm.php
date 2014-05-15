<?php
/**
 * AmiExt/EshopCustomFields extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiExt_EshopCustomFields
 * @version   $Id: AmiExt_EshopCustomFields_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/EshopCustomFields extension configuration admin controller.
 *
 * @package    Config_AmiExt_EshopCustomFields
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_EshopCustomFields_Adm extends AmiExt_EshopCustomFields_Common{
    /**
     * Custom fields array
     *
     * @var array
     */
    protected $aFields = array();

    /**
     * Available data types
     *
     * @var array
     */
    protected $aAvailableTypes  = array('scalar', 'ref_value');

    /**
     * Available field types
     *
     * @var array
     */
    protected $aAvailableFTypes = array('char', 'int');

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
        $aEvent = parent::handlePreInit($name, $aEvent, $handlerModId, $srcModId);
        $modId = $aEvent['modId'];

        if(preg_match('/(_item|_cat)$/', $modId)){
            AMI_Event::addHandler('on_filter_init', array($this, 'handleFilterInit'), $modId);

            $oView = $this->getView(AMI_Registry::get('side'));
            if($oView){
                $oView->setExt($this);
                AMI_Event::addHandler('on_form_fields_form_filter', array($oView, 'handleFilterFields'), $modId);
            }
        }

        return $aEvent;
    }

    /**
     * Initialize additional fields.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  int    $handlerModId  Handler module id
     * @param  int    $srcModId      Sources module id
     * @return array
     */
    public function handleFilterInit($name, array $aEvent, $handlerModId, $srcModId){
        $modId = $this->getModId();

        $oFields = $this->getFields();
        foreach($oFields as $oField){

            if(!in_array($oField->value_type, $this->aAvailableTypes)
                || !in_array($oField->ftype, $this->aAvailableFTypes)
            ){
                continue;
            }

            $this->aFields[] = $oField;

            $aEvent['oFilter']->addViewField(
                array(
                    'name'          => 'custom_field_' . $oField->fnum,
                    'type'          => 'input',
                    'flt_type'      => 'text', // $oField->default_gui_type,
                    'flt_condition' => 'like',
                    'flt_default'   => '',
                    'flt_column'    => 'custom_field_' . $oField->fnum,
                    'flt_alias'     => 'i',
                )
            );
        }

        return $aEvent;
    }

    /**
     * Returns captions for all fields.
     *
     * @return array
     */
    public function getFieldsCaptions(){
        $aCaptions = array();
        $lang = AMI_Registry::get('lang_data');
        if(!empty($this->aFields)){
            foreach($this->aFields as $oField){
                $aName = unserialize($oField->name);
                $aCaptions['caption_custom_field_' . $oField->fnum] = isset($aName[$lang]) ? $aName[$lang] : 'caption_custom_field_' . $oField->fnum;
            }
        }

        return $aCaptions;
    }
}

/**
 * AmiExt/EshopCustomFields extension configuration admin view.
 *
 * @package    Config_AmiExt_EshopCustomFields
 * @subpackage View
 * @resource   ce_page_break/view/adm <code>AMI::getResource('ce_page_break/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_EshopCustomFields_ViewAdm extends AMI_ExtView{
    /**
     * Template block name
     *
     * @var string
     */
    protected $tplBlockName = 'eshop_custom_fields';

    /**
     * Filter fields handler which adding fields captions.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  int    $handlerModId  Handler module id
     * @param  int    $srcModId      Source module id
     * @return array
     */
    public function handleFilterFields($name, array $aEvent, $handlerModId, $srcModId){
        $oFormView = $this->oExt->getFormViewInstance($aEvent);
        if(!$oFormView){
            return $aEvent;
        }

        $oFormView->addLocale($this->oExt->getFieldsCaptions());
        return $aEvent;
    }
}