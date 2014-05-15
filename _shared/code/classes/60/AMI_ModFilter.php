<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModFilter.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.12.0
 */

/**
 * Module filter component action controller.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.12.0
 * @see        AMI_ModFilterAdm
 */
abstract class AMI_ModFilter extends AMI_ModForm{
    /**
     * Item model
     *
     * @var    AMI_Filter
     * @amidev Temporary
     */
    protected $oModelItem;

    /**
     * Returns component type.
     *
     * @return string
     */
    public function getType(){
        return 'form_filter';
    }

    /**
     * Initialization.
     *
     * @return $this
     */
    public function init(){
        $this->oItem = AMI::getResource($this->getModId() . '/filter/model/' . AMI_Registry::get('side'));
        $this->oItem->setModId($this->getModId());

        $aEvent = array(
           'oFilter' => $this->oItem
        );

        /**
         * Called when the controller is initialized filter components that can handle the filter component.
         *
         * @event      on_filter_init $modId
         * @eventparam AMI_Filter oFilter  Filter object
         */
        AMI_Event::fire('on_filter_init', $aEvent, $this->getModId());
        foreach(array('view', 'reset') as $action){
            AMI_Event::addHandler('dispatch_mod_action_filter_' . $action, array($this, AMI::actionToHandler($action)), $this->getModId());
        }
        AMI_Event::addHandler('dispatch_mod_action_list_view', array($this, AMI::actionToHandler('apply')), $this->getModId());
        AMI_Event::addHandler('on_list_recordset', array($this, 'handleListRecordset'), $this->getModId());
        return $this;
    }

    /**
     * Returns component view.
     *
     * @return AMI_ModFilterView|AMI_ViewEmpty
     */
    public function getView(){
        return $this->_getView('/filter/view/' . AMI_Registry::get('side'));
    }

    /**
     * Initializes model.
     *
     * @return mixed
     * @amidev Temporary
     */
    protected function initModel(){
        return null;
    }

    /**
     * Returns true if component needs to be started always in full environment.
     *
     * @return bool
     */
    public function isFullEnv(){
        return FALSE;
    }

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     * @amidev Temporary
     */

    /**
     * Dispatches view action.
     *
     * @param  string $name          Event name
     * @param  array $aEvent         Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchView($name, array $aEvent, $handlerModId, $srcModId){
        $this->_view($aEvent);
        return $aEvent;
    }

    /**
     * Dispatches apply action.
     *
     * @param  string $name          Event name
     * @param  array $aEvent         Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchApply($name, array $aEvent, $handlerModId, $srcModId){
        $this->_apply($aEvent);
        return $aEvent;
    }

    /**
     * Dispatches reset action.
     *
     * @param  string $name          Event name
     * @param  array $aEvent         Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchReset($name, array $aEvent, $handlerModId, $srcModId){
        $this->_reset($aEvent);
        return $aEvent;
    }

    /**
     * List recordset handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListRecordset($name, array $aEvent, $handlerModId, $srcModId){
        $this->oItem->applyFilter($aEvent);
        return $aEvent;
    }

    /**#@-*/

    /**#@+
     * Module filter action dispatching handlers.
     * @amidev Temporary
     */

    /**
     * View action handler.
     *
     * @param  array &$aEvent  Event data
     * @return void
     * @todo   Avoid DateTools::getEndDayTimestamp(1) autoload hack
     */
    protected function _view(array &$aEvent){
        $this->displayView();
        $this->oItem->setData(
            AMI::getSingleton('env/request')->getScope()
        );
    }

    /**
     * Apply action handler.
     *
     * @param  array &$aEvent  Event data
     * @return void
     */
    protected function _apply(array &$aEvent){
        $this->oItem->setData(AMI::getSingleton('env/request')->getScope());
    }

    /**
     * Reset action handler.
     *
     * @param  array &$aEvent  Event data
     * @return void
     */
    protected function _reset(array &$aEvent){
    }

    /**#@-*/
}

/**
 * Module admin filter component action controller.
 *
 * Will be described later. To create your own filter component action controller you should create the child for this class.<br /><br />
 *
 * Example:
 * <code>
 * // AmiSample_Filter.php
 * class AmiSample_FilterAdm extends AMI_ModFilterAdm{
 * }
 * </code>
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.12.0
 */
abstract class AMI_ModFilterAdm extends AMI_ModFilter{
}