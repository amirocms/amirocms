<?php
/**
 * AmiExt/EshopCategory extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Config_AmiExt_EshopCategory
 * @version   $Id: AmiExt_EshopCategory.php 44828 2013-12-02 12:15:14Z Medvedev Konstantin $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/Category extension configuration controller.
 *
 * Common admin/front code.
 *
 * @package    Config_AmiExt_EshopCategory
 * @subpackage Controller
 * @resource   ext_eshop_category/module/controller <code>AMI::getResource('ext_eshop_category/module/controller')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_EshopCategory extends AmiExt_Category{
    /**
     * Section (owner)
     *
     * @var string
     */
    protected $section;

    /**
     * Category parents struct
     *
     * @var array
     */
    protected $aCats = array();

    /**
     * Category levels struct
     *
     * @var array
     */
    protected $aLevels = array();

    /**
     * Constructor.
     *
     * @param string  $modId        Module id
     * @param string  $optSrcId     Options source module id
     * @param AMI_Mod $oController  Module controller
     */
    public function __construct($modId, $optSrcId = '', AMI_Mod $oController = null){
        $aPrefix = explode('_', $modId, 2);
        $this->owner = $aPrefix[0];
        $this->extId = 'ext_' . $this->owner . '_category';
        parent::__construct($modId, $optSrcId, $oController);
    }

    /**
     * Returns categories list.
     *
     * @param  string $force  Force getting category list
     * @return array
     */
    public function getCatList($force = false){
        if(is_null($this->aCat) || $force){
            $this->aCat = $this->aCats = array();
            $oList = $this->getCatListModel();
            foreach($oList as $oItem){
                $this->aCats[] = array(
                    'id'        => $oItem->id,
                    'name'      => $oItem->header,
                    'id_parent' => $oItem->id_parent
                );
            }
            $this->getCatListOrdered(0, 0, $this->getCatListIndex());
        }
        return $this->aCat;
    }

    /**
     * Handling save action and create new category if corresponding form fild filled.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleDispatchModActionFormSave($name, array $aEvent, $handlerModId, $srcModId){
        // Do nothing. All work done by old environment!
        return $aEvent;
    }

    /**
     * Recursive function to get a level-ordered child list of categories.
     *
     * @param int $parentId  Items parent id
     * @param int $level     Items current level
     * @param array $aIndex  List index array
     * @return void
     */
    protected function getCatListOrdered($parentId, $level, array $aIndex) {
        if(isset($aIndex[$parentId])){
            foreach($aIndex[$parentId] as $index){
                $aItem = $this->aCats[$index];
                $this->aCat[] = array(
                    'id'            => $aItem['id'],
                    'name'          => $aItem['name'],
                    'level'         => $level,
                    'attributes'    => ' data-ami-level="' . $level . '" data-ami-caption="' . htmlspecialchars($aItem['name'], ENT_COMPAT, 'UTF-8') . '"'
                );
                unset($this->aCats[$index]);
                $this->getCatListOrdered($aItem['id'], ($level + 1), $aIndex);
            }
        }
        return;
    }

    /**
     * Builds index table for recursive tree, pretty optimized.
     *
     * @return array
     */
    protected function getCatListIndex() {
        $aParentIndex = array();
        foreach($this->aCats as $index => $aItem){
            $parentId = $aItem['id_parent'];
            if(!isset($aParentIndex[$parentId])){
                $aParentIndex[$parentId] = array();
            }
            $aParentIndex[$parentId][] = $index;
        }
        return $aParentIndex;
    }

    /**#@-*/

    /**
     * Returns categories table list model.
     *
     * @return AMI_iModTableList
     */
    protected function getCatListModel(){
        $orderingColumn = AMI::getOption($this->owner . '_cat', 'front_page_sort_col');
        $orderingDirection = AMI::getOption($this->owner . '_cat', 'front_page_sort_dim');
        return
            AMI::getResourceModel($this->resName . '/table')
            ->getList()
            ->addColumns(array('id', 'header', 'id_parent', 'name', 'position'))
            ->addOrder('id_parent', 'asc')
            ->addOrder(($orderingColumn == 'position' ? 'position' : 'name'), ($orderingDirection == 'asc' ? 'asc' : 'desc'))
            ->load();
    }

    /**
     * Returns filter field structure.
     *
     * @return array
     */
    protected function getFilterField(){
        $aField = parent::getFilterField();
        $oRequest = AMI::getSingleton('env/request');
        if(!$oRequest->get('category', 0)){
            $aField['disableSQL'] = TRUE;
        }
        return $aField;
    }

    /**
     * Returns not selected row data for drpdown select boxes.
     *
     * @return array
     */
    protected function getNotSelectedRow(){
        return array('id' => '0', 'caption' => 'flt_all_categories');
    }

    /**
     * Returns categories submodule Id.
     *
     * @param  string $modId  Module Id
     * @return string
     */
    protected function getCatSubmodId($modId){
        return $this->owner . '_cat';
    }
}
