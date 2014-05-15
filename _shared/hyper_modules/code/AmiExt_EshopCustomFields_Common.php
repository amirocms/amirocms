<?php
/**
 * AmiExt/EshopCustomFields extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiExt_EshopCustomFields
 * @version   $Id: AmiExt_EshopCustomFields_Common.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/EshopCustomFields extension configuration.
 *
 * Common admin/front code.
 *
 * @package    Config_AmiExt_EshopCustomFields
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_EshopCustomFields_Common extends Hyper_AmiExt{
    /**
     * Pre init event handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  int    $handlerModId  Handler module id
     * @param  int    $srcModId      Sources module id
     * @return array
     */
    public function handlePreInit($name, array $aEvent, $handlerModId, $srcModId){
        return $aEvent;
    }

    /**
     * Get all admin filter fields.
     *
     * @return null|object
     */
    public function getFields(){
        return
            AMI::getResourceModel('ext_eshop_custom_fields/table')
            ->getList()
            ->addColumns(array('id', 'name', 'fnum', 'ftype', 'value_type', 'default_gui_type'))
            ->addWhereDef(
                DB_Query::getSnippet("AND i.`default_params` LIKE %s")
                ->q('%admin_filter";s:1:"1"%')
            )
            ->addOrder('name', 'asc')
            ->load();
    }
}
