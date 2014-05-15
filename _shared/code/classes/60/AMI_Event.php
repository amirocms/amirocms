<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Service
 * @version   $Id: AMI_Event.php 43075 2013-11-05 08:08:33Z Leontiev Anton $
 * @since     5.10.0
 */

/**
 * Event manager allows to add/drop event handlers, fire events.
 *
 * @package Service
 * @example events.php Usage example
 * @static
 */
final class AMI_Event{
    /**
     * Low event handler priority
     *
     * @var int
     * @see AMI_Event::addHandler()
     */
    const PRIORITY_LOW = 25;

    /**
     * Default event handler priority
     *
     * @var int
     * @see AMI_Event::addHandler()
     */
    const PRIORITY_DEFAULT = 50;

    /**
     * High event handler priority
     *
     * @var int
     * @see AMI_Event::addHandler()
     */
    const PRIORITY_HIGH = 75;

    /**
     * Event handler special module id
     *
     * @var int
     * @see AMI_Event::addHandler()
     */
    const MOD_ANY = -1;

    /**
     * Event handlers
     *
     * @var array
     */
    private static $aHandlers = array();

    /**
     * Contains ordered by priority flags for each event name
     *
     * @var array
     */
    private static $aOrdered = array();

    /**
     * Contains disabled events.
     *
     * @var array
     */
    private static $aDisabled = array();

    /**
     * Contains fired events names && target module name to avoid recurring firing during its execution
     *
     * @var array
     */
    private static $aFiredEvents = array();

    /**
     * Array of hidden event debugger events
     *
     * @var array
     */
    private static $aDebugEvents = array('on_add_handler', 'on_start_fire_event', 'on_end_fire_event');

    /**
     * Enables {@link AMI_Event::$aDebugEvents} events
     *
     * @var bool
     */
    private static $bDebug = false;

    /**
     * Adds event handler.
     *
     * To break event handling handler must set $aEvent['_break_event'] to true.<br /><br />
     *
     * Example:
     * <code>
     * // @param  string $name  Event name
     * // @param  array $aEvent  Event data
     * // @param  string $handlerModId  Third AMI_Event::addhandler() $handlerModId argument
     * // @param  string $srcModId  Third AMI_Event::fire() $targetModId argument
     * // @return array
     * function eventHandler($name, array $aEvent, $handlerModId, $srcModId){
     *     $aEvent['var_from_handler'] = 1;
     *     $aEvent['_break_event'] = true;
     *     return $aEvent;
     * }
     * </code>
     *
     * @param  string $name          Event name
     * @param  callback $handler     Event handler callback
     * @param  string $handlerModId  Handler module id or AMI_Event::MOD_ANY
     * @param  int $priority         Event priority:
     *                               AMI_Event::PRIORITY_LOW, AMI_Event::PRIORITY_DEFAULT or AMI_Event::PRIORITY_HIGH
     * @return void
     * @example events.php Usage example
     */
    public static function addHandler($name, $handler, $handlerModId, $priority = self::PRIORITY_DEFAULT){
        if($handlerModId !== self::MOD_ANY && !AMI::validateModId($handlerModId)){
            trigger_error("Invalid handler module id '" . $handlerModId . "'", E_USER_ERROR);
        }
        $key = $handlerModId . '|';
        if(is_array($handler)){
            $key .= (is_object($handler[0]) ? get_class($handler[0]) : $handler[0]) . '|' . $handler[1];
        }else{
            $key .= $handler;
        }
        $addHandler = TRUE;
        if(empty(self::$aHandlers[$name])){
            self::$aHandlers[$name] = array();
        }elseif(isset(self::$aHandlers[$name][$key])){
            $addHandler = FALSE;
        }
        if($addHandler){
            self::$aHandlers[$name][$key] = array($priority, $handler, $handlerModId);
            self::$aOrdered[$name] = false;
        }
        if(self::$bDebug && !in_array($name, self::$aDebugEvents)){
            $aOn = array('name' => $name, 'handler' => $handler, 'handlerModId' => $handlerModId, 'added' => $addHandler);
            self::fire('on_add_handler', $aOn, self::MOD_ANY);
        }
    }

    /**
     * Drops event handler(s).
     *
     * Example:
     * <code>
     * // drop all 'some_event_name' event handlers
     * AMI_Event::dropHandler('some_event_name');
     *
     * // drop all 'some_event_name' event handlers processing by $object methods only
     * AMI_Event::dropHandler('some_event_name', $object);
     *
     * // drop all event handlers having $handlerModId equals to 'news'
     * AMI_Event::dropHandler('', null, 'news');
     *
     * // drop all 'some_event_name' event handlers processing by $object and having $handlerModId equals to 'news'
     * AMI_Event::dropHandler('some_event_name', $object, 'news');
     * </code>
     *
     * @param  string $name          Event name
     * @param  mixed $entity         Object|string (Object or class/function name)
     * @param  string $handlerModId  Handler module id
     * @return void
     * @example events.php Usage example
     */
    public static function dropHandler($name = '', $entity = null, $handlerModId = null){
        // Detect entity type
        if(is_object($entity)){
            $entityType = 'object';
        }elseif(is_string($entity)){
            $entityType = function_exists($entity) ? 'function' : 'class';
        }elseif(is_array($entity)){
            $entityType = 'callback';
        }else{
            $entityType = '';
        }
        $aNames = $name === '' ? array_keys(self::$aHandlers) : array($name);
        foreach($aNames as $name){
            if(empty(self::$aHandlers[$name])){
                // There aren't handlers for specified event name
                continue;
            }
            $aIndices = array_keys(self::$aHandlers[$name]);
            switch($entityType){
                case 'object':
                case 'function':
                    // Clean up methods of specified object / specified functions
                    foreach($aIndices as $index){
                        if(
                            is_array(self::$aHandlers[$name][$index]) &&
                            self::$aHandlers[$name][$index][1][0] === $entity &&
                            // module id
                            (is_null($handlerModId) || self::$aHandlers[$name][$index][2] === $handlerModId)
                        ){
                            self::cleanupHandler($name, $index);
                        }
                    }
                    break;
                case 'class':
                    // Clean up static specified classes methods
                    foreach($aIndices as $index){
                        if(
                            is_array(self::$aHandlers[$name][$index]) && (
                                self::$aHandlers[$name][$index][1][0] == $entity ||
                                get_class(self::$aHandlers[$name][$index][1][0]) == $entity
                            ) &&
                            // module id
                            (is_null($handlerModId) || self::$aHandlers[$name][$index][2] === $handlerModId)
                        ){
                            self::cleanupHandler($name, $index);
                        }
                    }
                    break;
                case 'callback':
                    // Clean up specified callbacks
                    foreach($aIndices as $index){
                        if(
                            is_array(self::$aHandlers[$name][$index]) && (
                                (self::$aHandlers[$name][$index][1][0] == $entity[0] ||
                                    (is_object(self::$aHandlers[$name][$index][1][0]) && is_object($entity[0])
                                        ? get_class(self::$aHandlers[$name][$index][1][0]) == get_class($entity[0])
                                        : self::$aHandlers[$name][$index][1][0] == $entity[0]
                                    )
                                ) &&
                                self::$aHandlers[$name][$index][1][1] == $entity[1]
                            ) &&
                            // module id
                            (is_null($handlerModId) || self::$aHandlers[$name][$index][2] === $handlerModId)
                        ){
                            self::cleanupHandler($name, $index);
                        }
                    }
                    break;
                default:
                    if(is_null($handlerModId)){
                        // Cleanup all handlers with specified name
                        unset(self::$aHandlers[$name], self::$aOrdered[$name]);
                    }else{
                        // Cleanup all handlers with specified name and hadler module id
                        foreach($aIndices as $index){
                            if(self::$aHandlers[$name][$index][2] === $handlerModId){
                                self::cleanupHandler($name, $index);
                            }
                        }
                    }
            }
        }
    }

    /**
     * Disable handler.
     *
     * @param  string $name  Event name
     * @return void
     * @amidev temporary
     */
    public static function disableHandler($name){
        self::$aDisabled[$name] = TRUE;
    }

    /**
     * Enable handler.
     *
     * @param  string $name  Event name
     * @return void
     * @amidev temporary
     */
    public static function enableHandler($name){
        unset(self::$aDisabled[$name]);
    }

    /**
     * Returns true if there are handlers for specified event.
     *
     * @param  string $name  Event name
     * @return bool
     * @amidev temporary
     */
    public static function hasHandlers($name){
        return isset(self::$aHandlers[$name]);
    }

    /**
     * Returns handlers for passed event or all.
     *
     * @param  string $name  Event name
     * @return array
     * @amidev
     */
    public static function getHandlers($name = NULL){
        return is_null($name) ? self::$aHandlers : self::$aHandlers[$name];
    }

    /**
     * Fires event.
     *
     * @param  string $name         Event name
     * @param  array  &$aEvent      Event data
     * @param  string $targetModId  Target module id
     * @return void
     * @example events.php Usage example
     */
    public static function fire($name, array &$aEvent, $targetModId){
        // d::w("EVENT: [{$targetModId}] {$name}<br />");##
        if($targetModId !== self::MOD_ANY && !AMI::validateModId($targetModId)){
            trigger_error("Invalid target module id '" . $targetModId . "'", E_USER_ERROR);
        }
        if(self::$bDebug && !in_array($name, self::$aDebugEvents)){
            $uid = rand(0, 1000000);
            $aOn = array('name' => $name, 'data' => $aEvent, 'target' => $targetModId, 'uid' => $uid);
            self::fire('on_start_fire_event', $aOn, self::MOD_ANY);
            unset($aOn);
        }
        if(isset(self::$aHandlers[$name]) && !(isset(self::$aDisabled[$name]) && self::$aDisabled[$name])){
            if(isset(self::$aFiredEvents[$name.'|'.$targetModId])){
                trigger_error("Event '" . $name . "' is fired already for module '" . $targetModId . "'", E_USER_WARNING);
            }else{
                self::$aFiredEvents[$name . '|' . $targetModId] = TRUE;
                if(!self::$aOrdered[$name]){
                    AMI_Lib_Array::sortMultiArrayPreserveKeys(self::$aHandlers[$name], 0, SORT_NUMERIC, SORT_DESC);
                    self::$aOrdered[$name] = true;
                }
                $handlers = array_keys(self::$aHandlers[$name]);
                foreach($handlers as $index){
                    if(
                        (
                            !empty(self::$aHandlers[$name][$index][2]) &&
                            !empty($targetModId) &&
                            $targetModId != self::$aHandlers[$name][$index][2]
                        ) ||
                        (empty(self::$aHandlers[$name][$index][1]))
                    ){
                        // Targeted event, not for this target
                        continue;
                    }
                    // Call handler
                    $aEvent = call_user_func(
                        self::$aHandlers[$name][$index][1],
                        $name, $aEvent, self::$aHandlers[$name][$index][2], $targetModId
                    );
                    // Check result
                    if(!is_array($aEvent)){
                        $aHandler = self::$aHandlers[$name][$index];
                        if(is_array($aHandler[1])){
                            $method =
                                (is_object($aHandler[1][0])
                                ? get_class($aHandler[1][0]) . '->'
                                : $aHandler[1][0] . '::') .
                                $aHandler[1][1];
                        }else{
                            $method = $aHandler[1];
                        }
                        unset($aHandler);
                        trigger_error("Event '" . $name . "' doesn't return data from " . $method, E_USER_ERROR);
                    }
                    if(!empty($aEvent['_break_event'])){
                        unset($aEvent['_break_event']);
                        break;
                    }
                }
                unset(self::$aFiredEvents[$name . '|' . $targetModId]);
            }
        }
        if(self::$bDebug && !in_array($name, self::$aDebugEvents)){
            $aOn = array('name' => $name, 'data' => $aEvent, 'uid' => $uid);
            self::fire('on_end_fire_event', $aOn, self::MOD_ANY);
        }
    }

    /**
     * Setup internal debugging.
     *
     * @param  bool $bDebug  Enable/disable flag
     * @return void
     * @todo   Separate debug implementation from production implementation?
     * @amidev
     */
    public static function setDebug($bDebug){
        self::$bDebug = (bool)$bDebug;
    }

    /**
     * Cleanups handler.
     *
     * @param  string $name   Event name
     * @param  int    $index  Index
     * @return void
     * @see    AMI_Event::dropHandler()
     */
    private static function cleanupHandler($name, $index){
        unset(self::$aHandlers[$name][$index]);
        if(sizeof(self::$aHandlers[$name])){
            ksort(self::$aHandlers[$name]);
            self::$aOrdered[$name] = false;
        }else{
            unset(self::$aHandlers[$name], self::$aOrdered[$name]);
        }
    }

    /**
     * Constructor.
     *
     * Static class, object creating is forbidden.
     */
    private function __construct(){
    }

    /**
     * Cloning.
     *
     * Static class, cloning is forbidden.
     */
    private function __clone(){
    }
}
