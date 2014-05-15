<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_Module_FormAdm.php 41585 2013-09-17 05:52:27Z Leontiev Anton $
 * @since     5.14.4
 */

/**
 * AMI_Module module admin form component action controller.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.14.4
 */
abstract class AMI_Module_FormAdm extends AMI_ModFormAdm{
    /**
     * Initialization.
     *
     * @return AMI_Module_FormAdm
     */
    public function init(){
        AMI_Event::addHandler('on_before_save_model_item', array($this, 'handleSaveModelItem'), $this->getModId());
        AMI_Event::addHandler('on_after_save_model_item', array($this, 'handleRepairCounters'), $this->getModId());
        $modId = $this->getModId();
        if(!AMI::issetProperty($modId, 'picture_cat')){
            // Temp hack for catalog and clones
            $aCatalogExclusions = array('eshop', 'kb', 'portfolio');
            if(mb_substr($modId, -4) === '_cat' && in_array(mb_substr($modId, 0, -4), $aCatalogExclusions)){
                $modId = mb_substr($modId, 0, -4).'_item_cat';
            }

            if(mb_substr($modId, -4) === '_cat' && AMI::issetProperty(mb_substr($modId, 0, -4), 'picture_cat')){
                $destModId = $modId;
                if(in_array(mb_substr($modId, 0, -9), $aCatalogExclusions)){
                    $destModId = mb_substr($modId, 0, -9) . '_cat';
                }

                AMI::setProperty($destModId, 'picture_cat', AMI::getProperty(mb_substr($modId, 0, -4), 'picture_cat'));
            }
        }
        return parent::init();
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
        if(isset($aEvent['aData']['sticky']) && $aEvent['aData']['sticky'] != 1){
            $aEvent['aData']['date_sticky_till'] = null;
            $aEvent['oItem']->date_sticky_till = null;
        }
        if(!empty($aEvent['aData']['sticky'])){
            $aEvent['aData']['hide_in_list'] = 1;
            $aEvent['oItem']->hide_in_list = 1;
        }
        return $aEvent;
    }

    /**
     * Repair counters for first created category.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTableItem::setData()
     */
    public function handleRepairCounters($name, array $aEvent, $handlerModId, $srcModId){
        if(isset($aEvent['success']) && $aEvent['success']){
            $oItem = $aEvent['oItem'];
            $id = $oItem->getId();
            // See CMS-11111
            if(AMI::isCategoryModule($handlerModId) && $id == 1){
                $itemsModId = $oItem->getTable()->getSubItemsModId();
                $oItemsTable = AMI::getResourceModel($itemsModId . '/table');
                $oAllItems =
                    $oItemsTable
                        ->getList()
                        ->addColumn('id')
                        ->addWhereDef(DB_Query::getSnippet("AND id_cat=%s")->q($id))
                        ->load();
                $oPublicItems =
                    $oItemsTable
                        ->getList()
                        ->addColumn('id')
                        ->addWhereDef(DB_Query::getSnippet("AND id_cat=%s AND public=1")->q($id))
                        ->load();
                $allItems = $oAllItems->count();
                $pubItems = $oPublicItems->count();
                if($allItems){
                    $oItem->num_items = $allItems;
                    $oItem->num_public_items = $pubItems;
                    $oDB = AMI::getSingleton('db');
                    // $oDB->displayQueries();
                    $sql = "UPDATE %s SET num_items=%s, num_public_items=%s WHERE id=%s";
                    $oDB->query(
                        DB_Query::getSnippet($sql)
                        ->plain($oItem->getTable()->getTableName())
                        ->q($allItems)
                        ->q($pubItems)
                        ->q($id)
                    );
                    // $oDB->displayQueries(false);
                }
            }
        }
        return $aEvent;
    }

    /**
     * Returns module file storage path.
     *
     * @return string
     * @amidev Temporary
     */
    protected function getFileStoragePath(){
        return '';
    }
}

/**
 * AMI_CatModule module admin form component action controller.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.14.4
 */
abstract class AMI_CatModule_FormAdm extends AMI_Module_FormAdm{
}
