<?php
/**
 * AmiMultifeeds hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiMultifeeds
 * @version   $Id: Hyper_AmiMultifeeds_CatsFrn.php 45064 2013-12-05 09:27:10Z Leontiev Anton $
 * @since     6.0.2
 */

/**
 * Multifeeds hypermodule front cats body type action controller.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
class Hyper_AmiMultifeeds_CatsFrn extends AMI_ModCatsFrn{
}

/**
 * Multifeeds hypermodule front cats body type view.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage View
 * @since      6.0.2
 */
class Hyper_AmiMultifeeds_CatsViewFrn extends AMI_ModCatsView{
    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        $aData = parent::get();

        $aData["body"] = $this->oTpl->parse($this->tplBlockName . ':body_cats', $aData);
        if(!AMI_Registry::get('AMI/Module/Environment/useCatsAsSubcomponent', FALSE)){
            $aData["body"] = $this->oTpl->parse($this->tplBlockName, $aData);
        }

        return $aData['body'];
    }

    /**
     * Dispatches view.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchView($name, array $aEvent, $handlerModId, $srcModId){
        if(!isset($aEvent['cat_list'])){
            $aEvent['cat_list'] = $this->get();
        }
        return $aEvent;
    }
}

/**
 * Multifeeds hypermodule front subitems body type view.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage View
 * @since      6.0.2
 */
class Hyper_AmiMultifeeds_SubitemsViewFrn extends AMI_ModSubitemsView{
}

/**
 * Multifeeds hypermodule front sticky cats body type action controller.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @resource   {$modId}/items/controller/frn <code>AMI::getResource('{$modId}/items/controller/frn')*</code>
 * @since      6.0.2
 */
class Hyper_AmiMultifeeds_StickyCatsFrn extends Hyper_AmiMultifeeds_CatsFrn{
    /**
     * Initialization.
     *
     * @return AMI_ModComponentStub
     */
    public function init(){
        $this->bDispayView = true;

        return $this;
    }

    /**
     * Returns component type.
     *
     * @return string
     */
    public function getType(){
        return 'sticky_cats';
    }
}

/**
 * Multifeeds hypermodule front sticky cats body type view.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage View
 * @resource   {$modId}/items/controller/frn <code>AMI::getResource('{$modId}/items/controller/frn')*</code>
 * @since      6.0.2
 */
class Hyper_AmiMultifeeds_StickyCatsViewFrn extends AMI_ModCatsView{
    /**
     * Body type
     *
     * @var string
     */
    protected $bodyType = 'sticky_cats';

    /**
     * Constructor.
     */
    public function __construct(){
        // the main component already fire the event
        $this->fireOnCatListView = FALSE;

        AMI_Event::addHandler('on_cat_list_view', array($this, AMI::actionToHandler('view')), $this->getModId());

        parent::__construct();
    }

    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        $aData = array();

        $aBrowser = $this->oCommonView->getBrowserData();
        if($aBrowser['useSpecView']){
            $aShowAt = AMI::getOption($this->getModId() . '_cat', 'show_urgent_elements');
            if(!is_array($aShowAt)){
                $aShowAt = array($aShowAt);
            }

            if($this->oList->getActivePage($aBrowser['pageSize'], $aBrowser['listStart'])){
                // show at next pages
                if(!in_array('at_next_pages', $aShowAt)){
                    return $aData;
                }
            }else{
                // show at first page
                if(!in_array('at_first_page', $aShowAt)){
                    return $aData;
                }
            }

            $this->oCommonView->setBrowserData(
                array(
                    'pageSize'  => 0,
                    'listStart' => 0,
                    'listLimit' => 0
                )
            );
            $aData = parent::get();
        }

        return $aData;
    }

    /**
     * Dispatches view.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchView($name, array $aEvent, $handlerModId, $srcModId){
        $aData = $this->get();
        $aEvent['sticky_cat_list'] = !empty($aData['sticky_cat_list']) ? $aData['sticky_cat_list'] : '';
        if(!empty($aEvent['sticky_cat_list'])){
            $this->oTpl->addGlobalVars(array('STICKY_CAT_LIST_' . mb_strtoupper($this->getModId()) => $aEvent['sticky_cat_list']));
        }
        return $aEvent;
    }

    /**
     * Set filter.
     *
     * @return Hyper_AmiMultifeeds_StickyItemsViewFrn
     * @since  6.0.2
     */
    protected function setFilter(){
        $this->oList->addWhereDef(DB_Query::getSnippet("AND `i`.`" . $this->oModel->getFieldName('sticky') . "` = 1"));
        return $this;
    }
}
