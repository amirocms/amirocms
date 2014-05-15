<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Service
 * @version   $Id: AMI_EventDebug.php 42098 2013-10-07 11:30:34Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary?
 */

/**
 * Event manager debugger classs.
 *
 * <code>
 * $oEvtDebugger = AMI_Debug::getEventDebugger();
 * $oEvtDebugger->inlineEnable();
 * ...
 * $oEvtDebugger->printHandlers();
 * $oEvtDebugger->printHandlers(array('object' => 'ExtCategory'));
 * $oEvtDebugger->printCallStack();
 * </code>
 *
 * @package Service
 * @since   x.x.x
 * @amidev  Temporary?
 */
class AMI_EventDebug{
    /**
     * Events handlers array
     *
     * @var array
     */
    private $aHandlers = array();

    /**
     * Enable of inline debug output flag.
     *
     * @var bool
     */
    private $bInlineEnabled = false;

    /**
     * Call stack array.
     *
     * @var array
     */
    private $aCallStack = array();

    /**
     * Call stack array
     *
     * @var array
     */
    private $aCallStackByUid = array();

    /**
     * Time (microtime) when class was constructed.
     *
     * @var float
     */
    private $constructTime = false;

    /**
     * Current event's depth nasting level.
     *
     * @var float
     */
    private $depthLevel = false;

    /**
     * Cunstructor.
     *
     * Initialize internal variables and setup fire event handlers.
     */
    public function __construct(){
        $this->constructTime = microtime(TRUE);
        AMI_Event::setDebug(TRUE);
        AMI_Event::addHandler('on_start_fire_event', array($this, 'onStartFireEvent'), AMI_Event::MOD_ANY);
        AMI_Event::addHandler('on_end_fire_event', array($this, 'onEndFireEvent'), AMI_Event::MOD_ANY);
        AMI_Event::addHandler('on_add_handler', array($this, 'onAddHandlerEvent'), AMI_Event::MOD_ANY);
        $this->depthLevel = 0;
    }

    /**
     * Enable Inline debug output.
     *
     * @return void
     */
    public function inlineEnable(){
        $this->bInlineEnabled = true;
    }

    /**
     * OnStartFireEvent handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function onStartFireEvent($name, array $aEvent, $handlerModId, $srcModId){
        $this->depthLevel++;
        $aDbg = debug_backtrace();
        $call = array(
            'event'        => $aEvent,
            'start_time'   => microtime(1),
            'level'        => $this->depthLevel,
            'start_memory' => memory_get_usage(),
            'file'         => $aDbg[3]['file'],
            'line'         => $aDbg[3]['line'],
        );

        if(isset($aDbg[4])){
            $call['function'] = $aDbg[4]['function'];
            $call['class']    = isset($aDbg[4]['class']) ? $aDbg[4]['class'] : '';
            $call['type']     = isset($aDbg[4]['type']) ? $aDbg[4]['type'] : '';
        }

        $this->aCallStack[] = $call;
        $this->aCallStackByUid[$aEvent['uid']] = count($this->aCallStack) - 1;

        if($this->bInlineEnabled){
            AMI_Debug::write(
                "<fieldset onmouseover='this.style.borderColor=\"#4d94db\"'" .
                "onmouseout='this.style.borderColor=\"\"'>" .
                "<legend onclick='this.parentNode.childNodes(1).style.display=(this.parentNode.childNodes(1).style.display==\"none\"?\"block\":\"none\")' style='font-weight:bold'>[ " .
                $aEvent['name'] . ( isset($aEvent['target']) && $aEvent['target'] ? "(" . $aEvent['target'] . ")" : "") . " ]</legend>" .
                "<div style='display:none;'><b>Event: " . $aEvent['name'] . "</b><br>" .
                "<b>File: " . $call['file'] . ":" . $aDbg[3]['line'] . " <br>" .
                ( isset($call['class']) ? "Function: " . $call['class'] . $call['type'] . $call['function'] . "() <br>" : "" ) .
                "aData:</b> " . AMI_Debug::getDumpAsString($aEvent['data']) . "</div>"
            );
        }

        return $aEvent;
    }

    /**
     * OnEndFireEvent handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function onEndFireEvent($name, array $aEvent, $handlerModId, $srcModId){
        $this->depthLevel--;
        $evnt = $this->aCallStack[$this->aCallStackByUid[$aEvent['uid']]];

        $evnt['end_time'] = microtime(1);
        $evnt['self_time'] = $evnt['end_time'] - $evnt['start_time'];
        $evnt['end_memory'] = memory_get_usage();
        $evnt['self_memory'] = $evnt['end_memory'] - $evnt['start_memory'];

        $this->aCallStack[$this->aCallStackByUid[$aEvent['uid']]] = $evnt;

        if($this->bInlineEnabled){
            AMI_Debug::write("</fieldset>");
        }

        return $aEvent;
    }

    /**
     * Return html colored (from black to red) value depend of it's maximum.
     *
     * @param mixed $value  Value itself
     * @param mixed $max  Maximum value
     * @param string $format  Printf format
     * @param string $postString  Postfix string
     * @return string
     */
    private function getColoredValue($value, $max = false, $format = "%.5f", $postString = ""){
        if(!$max || $value > $max){
            $max = $value;
        }
        return
            '<span style="color:#' . sprintf("%02X", 0xff * $value / ($max ? $max : 100000)) . '0000">' .
            sprintf($format, $value) . $postString . '</span>';
    }

    /**
     * Output current events call stack.
     *
     * @return void
     */
    public function printCallStack(){
        $aStat = array();

        foreach ($this->aCallStack as $call){
            foreach (array('self_time', 'self_memory') as $v){
                if(!isset($aStat[$v]))
                    $aStat[$v] = array();
                if(!isset($aStat[$v]['max']) || $aStat[$v]['max'] < $call[$v])
                    $aStat[$v]['max'] = $call[$v];
                if(!isset($aStat[$v]['min']) || $aStat[$v]['min'] > $call[$v])
                    $aStat[$v]['min'] = $call[$v];
            }
        }

        AMI_Debug::write(
            '<fieldset><legend><b> Events Call Stack </b></legend><table width=100% border=0 cellspacing=5px>' .
            '<tr style="background:#6aaf6a;font-weight:bold;color:white"><th>Name</th><th>Start Time</th><th>Self Time</th>' . // '<th>End Time</th>'.
            '<th>Self Memory</th><th>Start Memory</th>' . // '<th>End Memory</th>'.
            '<th>Caller</th>' .
            '<th>File</th>' .
            '</tr>'
        );

        $count = 0;
        foreach ($this->aCallStack as $call){
            $aEvent = $call['event'];
            AMI_Debug::write(
                '<tr ' . ($count++ % 2 ? 'bgcolor="#efefef"' : '') . ' onmouseover="this.style.background=\'#dfefdf\'" onmouseout="this.style.background=\'\'" >' .
                '<td><nobr>' . (str_repeat('--', ( $call['level'] > 0 ? $call['level'] - 1 : 0))) . '[<b> ' . $aEvent['name'] . ' </b>]</nobr></td>' .
                '<td>' . sprintf("%1.5f", $call['start_time'] - $this->constructTime) . '</td>' .
                '<td>+' . $this->getColoredValue($call['self_time'], $aStat['self_time']['max']) . '</td>' .
                // '<td>'.sprintf("%1.5f",$call['end_time'] - $this->constructTime).'</td>'.
                '<td>' . ( $call['self_memory'] > 0 ? '+' : '' ) .
                $this->getColoredValue($call['self_memory'], $aStat['self_memory']['max'], "%d", sprintf('(%.2fMb)', $call['self_memory'] / 1024 / 1024)) . '</td>' .
                '<td>' . sprintf("%d", $call['start_memory']) . sprintf('(%.2fMb)', $call['start_memory'] / 1024 / 1024) . '</td>' .
                // '<td>'.sprintf("%d",$call['end_memory']).'</td>'.
                "<td><nobr>" . ( isset($call['class']) ? $call['class'] . $call['type'] . $call['function'] . '()' : "" ) . "</nobr></td>" .
                "<td>" . $call['file'] . ":" . $call['line'] . "</td>" .
                '<td width=100%>&nbsp</td>' .
                '</tr>'
            );
        }
        AMI_Debug::write('</table></fieldset>');
    }

    /**
     * OnAddHandlerEvent handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function onAddHandlerEvent($name, array $aEvent, $handlerModId, $srcModId){
        $aHandler = $aEvent;
        $aDbg = debug_backtrace();
        $aHandler['backtrace'] = $aDbg[3];
        $aHandler['backtrace_next'] = isset($aDbg[4]) ? $aDbg[4] : array();
        $this->aHandlers[$aEvent['name']][] = $aHandler;

        return $aEvent;
    }

    /**
     * Prints out handlers table.
     *
     * @param  array $aFilter  Enumeration: 'event' - filter by event name
     *                        'object'- filter by object
     *                        'function'- filter by function
     * @return void
     */
    public function printHandlers(array $aFilter = array()){
        AMI_Debug::write(
            '<fieldset><legend><b> Handlers List </b></legend><table width="100%" border="0" cellspacing="5px">' .
            '<tr style="background:#6a6aaf;font-weight:bold;color:white">' .
            '<th>#</th><th>Event Name</th><th>Object</th><th>Function</th>' .
            '<th>Handled from</th><th>Handled from file</th></tr>'
        );

        $count = 0;
        foreach ($this->aHandlers as $name => $aHandlers){
            foreach ($aHandlers as $aHandler){
                $object = '';
                $method = '';
                if(is_array($aHandler['handler'])){
                    $object = is_object($aHandler['handler'][0]) ? get_class($aHandler['handler'][0]) : $aHandler['handler'][0];
                    $method = $aHandler['handler'][1];
                } else {
                    $object = '';
                    $method = $aHandler['handler'];
                }
                $function = isset($aHandler['backtrace_next']['class']) ? $aHandler['backtrace_next']['class'] . $aHandler['backtrace_next']['type'] . $aHandler['backtrace_next']['function'] . '()' : '';
                if(
                    (isset($aFilter['event']) && $aFilter['event'] !== $name) ||
                    (isset($aFilter['object']) && $aFilter['object'] !== $object) ||
                    (isset($aFilter['function']) && $aFilter['function'] !== $function)
                ){
                    continue;
                }
                AMI_Debug::write(
                    '<tr ' . ( $count++ % 2 ? 'bgcolor="#efefef"' : '' ) . ' onmouseover="this.style.background=\'#dfefdf\'" onmouseout="this.style.background=\'\'" >' .
                    '<td align="right">' . $count . "</td>" .
                    "<td>" . $aHandler['name'] . "</td><td>" . $object .
                    "</td><td>" . $method . "</td>" .
                    "<td><nobr>" . $function . "</nobr></td>" .
                    "<td>" . $aHandler['backtrace']['file'] . ":" . $aHandler['backtrace']['line'] . "</td>" .
                    '<td width=100%>&nbsp</td>' .
                    '</tr>'
                );
            }
        }
        AMI_Debug::write('</table></fieldset>');
    }
}
