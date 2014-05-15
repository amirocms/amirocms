<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModSubitemsView.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.14.8
 */

/**
 * Module front subitems body type view.
 *
 * @package    ModuleComponent
 * @subpackage View
 * @since      5.14.8
 */
class AMI_ModSubitemsView extends AMI_ModItemsView{
    /**
     * Body type view
     *
     * @var string
     */
    protected $bodyType = 'subitems';

    /**
     * List model
     *
     * @var AMI_ModTableList
     */
    protected $oList;

    /**
     * Parameters
     *
     * @var array
     */
    protected $aParams;

    /**
     * Sets parameters.
     *
     * @param  array $aParams  Parameters
     * @return void
     */
    public function setParameters(array $aParams){
        $this->aParams = $aParams;
    }

    /**
     * Sets up Model object.
     *
     * @param  mixed $oModel  Model
     * @return AMI_View
     */
    protected function _setModel($oModel){
        $this->oModel = $oModel->getTable();
        $this->oList = $oModel;
        return $this;
    }

    /**
     * Initialize list model.
     *
     * @return void
     */
    protected function initListModel(){
        $this->oList = $this->oList;
    }

    /**
     * Loads list model.
     *
     * Subitems model is already loaded.
     *
     * @param  array $aColumns  Columns
     * @return void
     */
    protected function loadModel(array $aColumns){
    }

    /**
     * Checks to display item.
     *
     * @param AMI_iModTableItem $oItem  Item model
     * @param int               $index  Item index
     * @return boolean
     */
    protected function checkItem(AMI_iModTableItem $oItem, $index){
        return
            ($this->aParams['limit'] > 0
                ? $index < $this->aParams['limit']
                : TRUE
            ) &&
            $oItem->cat_id == $this->aParams['catId'];
    }


    /**
     * Fill the field callbacks.
     *
     * @return void
     */
    protected function prepareFieldCallbacks(){
        parent::prepareFieldCallbacks();
        $this->oCommonView->setFieldCallback(
            'splitter',
            array(
                'object' => $this,
                'method' => 'getSplitterCB'
            )
        );
    }

    /**
     * Appends splitter to list.
     *
     * @param  array &$aItem  Item data
     * @param  array &$aData  List data
     * @return void
     */
    public function getSplitterCB(array &$aItem, array &$aData){
        $this->oCommonView->getSplitterCB($aItem, $aData);

        $aTplData = $this->oCommonView->getTplData();
        $row = isset($aItem['row_index']) ? $aItem['row_index'] : 0;
        if($this->aParams['splitterPeriod'] && (($row % $this->aParams['splitterPeriod']) == 0)){
            $aData['list'] .= $this->oTpl->parse(
                $this->tplBlockName . ':' . $aTplData['prefix']['splitter'] . 'nSplitter',
                $aItem
            );
        }
    }
}
