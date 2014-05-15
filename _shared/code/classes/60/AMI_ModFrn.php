<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModFrn.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Module front action controller.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 * @todo       Remove unused class?
 */
abstract class AMI_ModFrn extends AMI_Mod{
    /**
     * View types
     *
     * @var array
     */
    protected $aViewTypes = array();

    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request object
     * @param AMI_Response $oResponse  Response object
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        parent::__construct($oRequest, $oResponse);
        AMI_Event::addHandler('dispatch_mod_action_none', array($this, 'handleDefaultAction'), $this->getModId());
    }

    /**
     * Returns specblock content.
     *
     * @param  string $postfix  Name postfix
     * @return string
     */
    public function getSpecblock($postfix = ''){
        $res = '';
        if($this->isSpecblock()){
            $postfix = preg_replace(
                array(
                    '/^spec_small_' . $this->getModId() . '_/',
                    '/_?[0-9]+$/'
                ),
                '',
                $postfix
            );
            $oSpecblockComponent = $this->getSpecblockComponent($postfix);
            if($oSpecblockComponent){
                $res = (string)$oSpecblockComponent->getView()->get();
            }
        }
        return $res;
    }

    /**
     * Event handler.
     *
     * Dispatches default action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function handleDefaultAction($name, array $aEvent, $handlerModId, $srcModId){
        $this->dispatchDefaultAction();
        return $aEvent;
    }

    /**
     * Adds view type.
     *
     * @param  string $type       View type
     * @param  string $resId      Resource id
     * @param  string $eventName  Event name
     * @return void
     */
    protected function addViewType($type, $resId, $eventName){
        $this->addComponent(AMI::getResource($resId));
        $this->aViewTypes[$type] = array($resId, $eventName);
    }

    /**
     * Runs view.
     *
     * @param  string $type  View type
     * @return void
     */
    protected function runView($type){
        if(isset($this->aViewTypes[$type])){
            $aEvent = $this->prepareEvent();
            /**
             * Processing controller actions of the AMI_Mod module.
             *
             * @event      dispatch_mod_action_{type} $modId
             * @eventparam string modId  Module id
             * @eventparam AMI_Mod|null oController  Module controller object
             * @eventparam string tableModelId  Table model resource id
             * @eventparam AMI_Request oRequest  Request object
             * @eventparam AMI_Response oResponse  Response object
             */
            AMI_Event::fire('dispatch_mod_action_' . $this->aViewTypes[$type][1], $aEvent, $this->getModId());
        }else{
            trigger_error("Undefined view type '" . $type . "'", E_USER_ERROR);
        }
    }

    /**
     * Returns specblock component.
     *
     * @param  string $postfix  Name postfix
     * @return AMI_ModComponent|null
     * @amidev Temporary
     */
    protected function getSpecblockComponent($postfix = ''){
        foreach($this->aComponents as $serialId => $oComponent){
            if(
                $oComponent->getType() === 'specblock' &&
                (empty($this->aComponentsOptions[$serialId]['postfix']) ? '' : $this->aComponentsOptions[$serialId]['postfix']) === $postfix
            ){
                return $oComponent;
            }
        }
        return null;
    }

    /**
     * Returns true if module specblock requested.
     *
     * @return bool
     */
    protected function isSpecblock(){
        return $this->bSpecblockMode;
    }

    /**
     * Dispathes default action.
     *
     * @return void
     */
    protected abstract function dispatchDefaultAction();
}
