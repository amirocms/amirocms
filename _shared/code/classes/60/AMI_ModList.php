<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModList.php 44766 2013-11-30 08:41:31Z Kolesnikov Artem $
 * @since     5.12.0
 */

/**
 * Module list component action controller.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.12.0
 */
abstract class AMI_ModList extends AMI_ModComponent{
    /**
     * Default list order
     *
     * @var array
     */
    protected $aDefaultOrder = array(
        'col' => 'id',
        'dir' => 'asc'
    );

    /**
     * Returns component type.
     *
     * @return string
     */
    public function getType(){
        return 'list';
    }

    /**
     * Initialization.
     *
     * @return AMI_ModList
     */
    public function init(){
        // default action
        AMI_Event::addHandler('dispatch_mod_action_list_view', array($this, AMI::actionToHandler('view')), $this->getModId());
        // $this->addSubComponent(AMI::getResource('list/pagination'));
        return $this;
    }

    /**
     * Event handler.
     *
     * Default list action (view).
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     * @amidev Temporary
     */
    public function dispatchView($name, array $aEvent, $handlerModId, $srcModId){
        $this->displayView();
/*
        // todo: move sortable fields to the model
        $sortCol = AMI::getSingleton('env/request')->get('sort_col', false);
        if(!$sortCol){
            $sortCol =
                AMI::issetOption($this->getModId(), 'page_sort_col')
                    ? AMI::getOption($this->getModId(), 'page_sort_col')
                    : $this->aDefaultOrder['col'];
        }
        $sortDir = AMI::getSingleton('env/request')->get('sort_dir', false);
        if(!$sortDir){
            $sortDir =
                AMI::issetOption($this->getModId(), 'page_sort_dir')
                    ? AMI::getOption($this->getModId(), 'page_sort_dir')
                    : $this->aDefaultOrder['dir'];
        }
        d::vd($sortCol);###
        d::vd($this->getView()->getSortColumns());###
        if(!in_array($sortCol, $this->getView()->getSortColumns())){
            $sortCol = $this->aDefaultOrder['col'];
        }
        if($sortDir !== 'asc' && $sortDir !== 'asc'){
            $sortDir = 'asc';
        }
        $this->getModel()->getList()->addOrder($sortCol, $sortDir);
        d::pr($sortCol . ' ' . $sortDir);###
*/
        return $aEvent;
    }

    /**
     * Returns component view.
     *
     * @return AMI_ModListView
     * @see    AMI_ModListAdm::init()
     */
    public function getView(){
        $type = $this->getType();
        if($this->getPostfix() != ''){
            $type .= ('_' . $this->getPostfix());
        }
        return $this->_getView('/' . $type . '/view/' . AMI_Registry::get('side'));
    }
}
