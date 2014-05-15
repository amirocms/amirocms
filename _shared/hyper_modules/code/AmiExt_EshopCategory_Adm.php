<?php
/**
 * AmiExt/EshopCategory extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Config_AmiExt_EshopCategory
 * @version   $Id: AmiExt_EshopCategory_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

class_exists('AmiExt_Category_Adm');

/**
 * AmiExt/EshopCategory extension configuration admin controller.
 *
 * @package    Config_AmiExt_EshopCategory
 * @subpackage Controller
 * @resource   ext_eshop_category/module/controller/adm <code>AMI::getResource('ext_eshop_category/module/controller/adm')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_EshopCategory_Adm extends AmiExt_EshopCategory{
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
/*
    public function handlePreInit($name, array $aEvent, $handlerModId, $srcModId){
        parent::handlePreInit($name, $aEvent, $handlerModId, $srcModId);

        $oCookies = AMI::getSingleton('env/cookie');

        return $aEvent;
    }
*/
}

/**
 * AmiExt/EshopCategory extension configuration admin view.
 *
 * @package    Config_AmiExt_EshopCategory
 * @subpackage View
 * @resource   ext_eshop_category/view/adm <code>AMI::getResource('ext_eshop_category/view/adm')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_EshopCategory_ViewAdm extends AmiExt_Category_ViewAdm{
    /**
     * Event handler.
     *
     * Adds category column to admin list view, patch order column.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     * @see    AMI_ModListView_JSON::get()
     */
    public function handleListColumns($name, array $aEvent, $handlerModId, $srcModId){
        $this->showColumn = !AMI_Filter::getFieldValue('category', 0);
        parent::handleListColumns($name, $aEvent, $handlerModId, $srcModId);
        return $aEvent;
    }
}
