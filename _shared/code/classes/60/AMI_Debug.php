<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Debug
 * @version   $Id: AMI_Debug.php 49521 2014-04-08 10:30:24Z Leontiev Anton $
 * @since     5.10.0
 */

/**
 * Debugger class.
 *
 * Use short aliases from examples i.e. d::vd() instead of AMI_Debug::dump() when available.
 *
 * @package Debug
 * @see     d
 * @static
 */
class AMI_Debug{
    /**
     * AMI_EventDebug instance
     *
     * @var AMI_EventDebug
     */
    private static $oEventDebugger;

    /**
     * Allowable dump methods
     *
     * @var array
     */
    private static $aDumpMethods = array('print_r', 'var_dump', 'var_export', 'debug_zval_dump');

    /**
     * Dump label counters
     *
     * @var array
     */
    private static $aDumpCounters = array();

    /**
     * Debug output buffer
     *
     * @var string
     */
    private static $buffer = '';

    /**
     * Returns and cleans debug output buffer.
     *
     * @return string
     * @amidev
     */
    public static function getBuffer(){
        $buffer = self::$buffer;
        if($buffer !== ''){
            $buffer .= "\n";
        }
        self::$buffer = '';
        return $buffer;
    }

    /**
     * Returns entity full dump as string.
     *
     * Example:
     * <code>
     * echo d::getDumpAsString(array('k000', 'k001' => array('k010' => array('k100' => array(1, 2, 3)))));
     * </code>
     * will output:
     * <pre>
     * Array(
     *     [0] => k000
     *     [k001] => Array(
     *             [k010] => Array(
     *                     [k100] => Array(
     *                             [0] => 1
     *                             [1] => 2
     *                             [2] => 3
     *                         )
     *                 )
     *         )
     * )
     * </pre>
     *
     * @param  mixed    $entity    Entity to dump
     * @param  callback $callback  Dump callback ('print_r'|'var_dump'|'var_export'|'debug_zval_dump') or anything else
     * @param  array    $aArgs     Callback arguments
     * @return string
     */
    public static function getDumpAsString($entity, $callback = 'print_r', array $aArgs = array()){
        array_unshift($aArgs, $entity);
        ob_start();
        call_user_func_array($callback, $aArgs);
        return ob_get_clean();
    }

    /**
     * Returns trace HTML as string.
     *
     * Example:
     * <code>
     * echo d::getTraceAsString();
     * </code>
     *
     * @param  array $aTrace  Array having the same structure as debug_backtrace() result
     * @return string
     */
    public static function getTraceAsString(array $aTrace = null){
        if(is_null($aTrace)){
            $aTrace = debug_backtrace();
            array_shift($aTrace);
        }
        $html =
            "<fieldset><legend> <b>Debug backtrace" .
            (isset($aTrace[0]['file']) ? ': ' . basename($aTrace[0]['file']) . ":" . $aTrace[0]['line'] : '') .
            "</b> </legend><table border='0' class='backtrace' width=100%>\n".
            "<tr style='background:#df6a6a;font-weight:bold;color:white'>\n<th>File</th><th>Line</th><th>Caller</th><th>Args</th>\n</tr>\n";
        $count = 0;
        foreach($aTrace as $r){
            if(isset($r['args'])){
                $aParams = array();
                foreach($r['args'] as $arg){
                    if(is_object($arg)){
                        $aParams[] = 'Object (' . get_class($arg) . ')';
                    }elseif(is_array($arg)){
                        $aParams[] = 'Array (' . sizeof($arg) . ')';
                    }else{
                        $aParams[] = (string)$arg;
                    }
                }
                $args = implode('<br />', $aParams);
            }else{
                $args = '';
            }
            $argsQty = isset($r['args']) ? count($r['args']) : '';
            $argsQty = $argsQty > 0 ? $argsQty : '';
            $html .=
                '<tr onmouseover="this.style.background=\'#efdfdf\'" onmouseout="this.style.background=\'\'" ' .
               ($count++ % 2 ? 'bgcolor="#efefef"':'').'><td>' . (isset($r['file']) ? $r['file'] : '&nbsp;') . '</td><td>' .
                (isset($r['line']) ? $r['line'] : '&nbsp;') . '</td><td>' .
                (isset($r['class'])
                    ? $r['class'] . $r['type'] . $r['function'] . '(' . $argsQty . ')'
                    : (isset($r['function']) ? $r['function'] . '(' . $argsQty . ')' : '&nbsp;') ) . '</td>'.
                '<td>' . $args . "</td></tr>\n";
        }
        $html .= "</table></fieldset>\n";
        return $html;
    }

    /**
     * Writes string to debug output.
     *
     * Example:
     * <code>
     * d::w('<h1>Some debug string</h1>');
     * </code>
     *
     * @param  string $string  String to write
     * @return void
     */
    public static function write($string){
        if(AMI_Service::isDebugSkipped()){
            return;
        }
        if(AMI_Service::isDebugBuffered()){
            self::$buffer .= $string;
        }else{
            echo $string;
        }
    }

    /**
     * Dumps entity structure to debug output.
     *
     * All objects are replaced by its class names,<br />
     * array depth limitation ($aOptions['nesting']) is supported.
     *
     * @param  mixed $entity  Entity to dump
     * @param  string $title  Entity title
     * @param  array $aOptions  Array containing dump options:
     * - 'nesting' - max nesting level for arrays (2 by default, 0 for no limits);
     * - 'method' - dump method, 'print_r'|'var_dump'|'var_export' ('print_r' by default);
     * - 'max_count' - maximum dumps per title (none by default);
     * - 'html_ent' = use htmlentities() (since 6.0.6);
     * 
     * @return void
     * @see    d::pr()
     * @see    d::vd()
     * @see    d::ve()
     */
    public static function dump($entity, $title = '', array $aOptions = array('nesting' => 2, 'method' => 'print_r')){
        if(AMI_Service::isDebugSkipped()){
            return;
        }
        if(empty(self::$aDumpCounters[$title])){
            self::$aDumpCounters[$title] = 1;
        }else{
            self::$aDumpCounters[$title]++;
        }
        $aOptions += array('nesting' => 2, 'method' => 'print_r', 'html_ent' => FALSE);
        $method = $aOptions['method'];
        if(isset($aOptions['max_count']) && self::$aDumpCounters[$title] > $aOptions['max_count']){
            return;
        }
        $_entity = unserialize(serialize($entity));
        self::entityCleaner($_entity, (int)$aOptions['nesting'], 0);
        if(AMI_Service::isDebugBuffered()){
            ob_start();
        }
        $aTrace = debug_backtrace();
        if($title !== ''){
            $title .= ' (' . self::$aDumpCounters[$title] . ') ';
        }
        echo
            "<fieldset style='background:#ffe9e9;'><legend><b>&nbsp;", $title,
            isset($aTrace[1]['file']) ? basename($aTrace[1]['file']) : '',
            isset($aTrace[1]['line']) ? ': ' . $aTrace[1]['line'] : '',
            "&nbsp;</b></legend>";
        if(empty($aOptions['no_pre'])){
            echo '<pre>';
        }
        ob_start();
        $method($_entity);
        $dump = ob_get_clean();
        if($aOptions['html_ent']){
            $dump = htmlspecialchars($dump, ENT_COMPAT, 'UTF-8');
        }
        echo $dump;
        if(empty($aOptions['no_pre'])){
            echo '</pre>';
        }
        echo "</fieldset>\n";
        if(AMI_Service::isDebugBuffered()){
            self::write(ob_get_clean());
        }
    }

    /**
     * Dumps backtrace to debug output.
     *
     * @param  array $aTrace  Array having the same structure as debug_backtrace() result
     * @return void
     */
    public static function trace(array $aTrace = null){
        self::write(self::getTraceAsString($aTrace));
    }

    /**
     * Prints included php files list to debug output.
     *
     * @return string
     * @amidev Tenmporary
     */
    public static function getIncludedFilesHTML(){
        $files = get_included_files();
        $html =
            "<a class=\"deb\" href=\"javascript:\" onclick=\"AMI.$('#tableCoreDebug').toggle();return false\">included files</a> " .
            "<table id=\"tableCoreDebug\" cellpadding=\"2\" cellspacing=\"2\" border=\"1\" class=\"deb\" style=\"display: none;\">\n";
        $sum = 0;
        foreach(get_included_files() as $i => $path){
            $sum += ($size = filesize($path));
            $html .= "<tr><td align=\"right\">" . ($i+1) . "</td><td>{$path}</td><td align=\"right\">" . number_format($size, 0, '', ',') . "</td></tr>\n";
        }
        $html .= "<tr><td colspan=\"3\" align=\"right\">" . number_format($sum, 0, '', ',') . "</td></tr>\n</table>\n";
        return $html;
    }

    /**
     * Adds benchmark.
     *
     * Each benchmark contains:
     * - time difference from previous bench to itself;
     * - time difference from script start time to itself;
     * - memory usage difference from previous bench to itself;
     * - peak memory usage on registering bechmark;
     * - bechmark caller (file, line number).
     *
     * Example:
     * <code>
     * AMI::getSingleton('response')->displayBench();
     * d::b('on start');
     * // some code
     * sleep(1);
     * d::b('on end');
     * </code>
     *
     * @param  string $label  Label
     * @return void
     * @see    d::b()
     * @see    AMI_Response::displayBench()
     */
    public static function bench($label){
        if(AMI_Service::isDebugSkipped()){
            return;
        }
        $t = microtime(true);
        if(function_exists('memory_get_usage')){
            $mu = memory_get_usage();
        }
        if(function_exists('memory_get_peak_usage')){
            $mpu = memory_get_peak_usage();
        }
        $aTrace = debug_backtrace();
        $caller =
            (isset($aTrace[1]['file']) ? basename($aTrace[1]['file']) : '') .
            (isset($aTrace[1]['line']) ? ': ' . $aTrace[1]['line'] : '');
        $GLOBALS['aAMIBench']['benches'][$label] = array(
            't'  => $t,
            'm'  => isset($mu) ? $mu : null,
            'p'  => isset($mpu) ? $mpu : null,
            'c' => $caller
        );
    }

    /**
     * Returns class parents as array.
     *
     * @param  mixed $object  The tested object or class name
     * @return array
     * @since  5.14.4
     */
    public static function getClassParents($object){
        $aParents = array(is_object($object) ? get_class($object) : $object);
        while($parent = get_parent_class($object)){
            $aParents[] = $parent;
            $object = $parent;
        }
        return array_reverse($aParents);
    }


    /**
     * Printss class parents.
     *
     * @param  mixed $object  The tested object or class name
     * @return void
     * @since  5.14.4
     */
    public static function printClassParents($object){
        $aParents = self::getClassParents($object);
        d::pr(implode('<br />&lt;- ', $aParents) . '<br /><br />');
    }

    /**
     * Returns event debugger instance.
     *
     * @return AMI_EventDebug
     * @amidev temporary
     */
    public static function getEventDebugger(){
        if(is_null(self::$oEventDebugger)){
            self::$oEventDebugger = new AMI_EventDebug();
        }
        return self::$oEventDebugger;
    }

    /**
     * Self redirection checker.
     *
     * @param  string $label  Label
     * @param  string $url    URL
     * @return void
     * @todo   Wipe after collecting info
     * @link   https://pm.cmspanel.net/show_bug.cgi?id=4509#c12
     * @amidev
     */
    public static function checkSelfRedirection($label, $url){

        $oRequest = AMI::getSingleton('env/request');

        if(defined('AMIRO_HOST') && !is_null($oRequest->getURL('host'))){
            if(
                trim(preg_replace('~^https?://~', '', $oRequest->getURL('url')), '/') ===
                trim(preg_replace('~^https?://~', '', $url), '/')
            ){
                trigger_error('Self redirection detected for ' . $label, E_USER_WARNING);
                $e = new Exception();
                AMI_Service::log(
                    '[' . $oRequest->getURL('host') . $oRequest->getURL('uri') . '] [' . $label . '] [' . $url . "] [[[\n" . $e->getTraceAsString() . "\n]]]",
                    dirname($GLOBALS['BENCH_LOG_FILE']) . DIRECTORY_SEPARATOR . 'selfredirection.log'
                );
            }
        }
    }

    /**
     * AMI_Debug::checkSelfRedirection() alias.
     *
     * @param  string $label  Label
     * @param  string $url    URL
     * @return void
     * @amidev
     */
    public static function csr($label, $url){
        self::checkSelfRedirection($label, $url);
    }

    /**
     * Returns colorized bench time.
     *
     * @param  float  $time  Microtime
     * @param  string $type  Mode: 'diff'|'total'
     * @return string
     * @amidev
     * @see    AMI_Response::getBenchString()
     */
    public static function getBenchTimeHTML($time, $type = 'diff'){
        $res = '#000';
        if($type == 'diff'){
            if($time > 0.2){
                $res = '#f00';
            }elseif($time < 0.01){
                $res = '#080';
            }
        }else{
            if($time > 0.4){
                $res = '#f00';
            }elseif($time < 0.2){
                $res = '#080';
            }
        }
        return
            '<span style="color: ' . $res . ';">' .
            number_format($time, $type == 'diff' ? 5 : 3, '.', '') . '</span>';
    }

    /**
     * Cleans up entity for dumping.
     *
     * @param  mixed &$entity  Entity
     * @param  int   $nesting  Maximum nesting
     * @param  int   $current  Current nesting
     * @return void
     */
    private static function entityCleaner(&$entity, $nesting, $current){
        if(is_object($entity)){
            $entity = '<b><i>object(' . get_class($entity) . ')</i></b>';
        }elseif(is_array($entity)){
            if($nesting && $current > $nesting){
                $entity = '<b><i>array(' . sizeof($entity) . ')</i></b>';
            }else{
                $current++;
                $keys = array_keys($entity);
                for($i = 0, $qty = sizeof($keys); $i < $qty; $i++){
                    self::entityCleaner($entity[$keys[$i]], $nesting, $current);
                }
            }
        }elseif(is_resource($entity)){
            $entity .= '';
        }
    }
}

/**
 * Short alias for AMI_Debug and its methods.
 *
 * @package Debug
 */
class d extends AMI_Debug{
    /**
     * Writes string to debug output.
     *
     * Short alias for AMI_Debug::write().
     *
     * Example:
     * <code>
     * d::w('My string');
     * </code>
     * will output
     * <pre>My string</pre>
     *
     * @param  string $string  String to write
     * @return void
     * @see    AMI_Debug::write()
     */
    public static function w($string){
        parent::write($string);
    }

	/**
     * Dumps entity structure to debug output using print_r().
     *
     * Short alias for AMI_Debug::dump() using print_r().<br />
     *
     * Example:
     * <code>
     * d::pr(array('k000', 'k001' => array('k010' => array('k100' => array(1, 2, 3)))));
     * </code>
     * will output:
     * <pre>
     * Array(
     *     [0] => k000
     *     [k001] => Array(
     *             [k010] => Array(
     *                     [k100] => <b><i>array(3)</i></b>
     *                 )
     *         )
     * )
     * </pre>
     *
     * Example:
     * <code>
     * d::pr(array('k000', 'k001' => array('k010' => array('k100' => array(1, 2, 3)))), 'title1', array('nesting' => 1));
     * </code>
     * will output:
     * <pre>
     * Array(
     *     [0] => k000
     *     [k001] => Array(
     *             [k010] => array(1)
     *         )
     * )
     * </pre>
     *
     * Example:
     * <code>
     * d::pr(array('k000', 'k001' => array('k010' => array('k100' => array(1, 2, 3)))), 'title2', array('nesting' => 0));
     * </code>
     * will output:
     * <pre>
     * Array(
     *     [0] => k000
     *     [k001] => Array(
     *             [k010] => Array(
     *                     [k100] => Array(
     *                             [0] => 1
     *                             [1] => 2
     *                             [2] => 3
     *                         )
     *                 )
     *         )
     * )
     * </pre>
     *
     * @param  mixed $entity  Entity to dump
     * @param  string $title  Entity title
     * @param  array $aOptions  Array containing dump options:
     * - 'nesting' - max nesting level for arrays (2 by default, 0 for no limits),
     * - 'method' - dump method, 'print_r'|'var_dump'|'var_export' ('print_r' by default),
     * - 'max_count' - maximum dumps per title (none by default)
     * @return void
     * @see    AMI_Debug::dump()
     */
	public static function pr($entity, $title = '', array $aOptions = array()){
        $aOptions['method'] = 'print_r';
        parent::dump($entity, $title, $aOptions);
    }

    /**
     * Dumps entity structure to debug output.
     *
     * Short alias for AMI_Debug::dump() using var_dump().
     *
     * @param  mixed $entity  Entity to dump
     * @param  string $title  Entity title
     * @param  array $aOptions  Array containing dump options:
     * - 'nesting' - max nesting level for arrays (2 by default, 0 for no limits),
     * - 'method' - dump method, 'print_r'|'var_dump'|'var_export' ('print_r' by default),
     * - 'max_count' - maximum dumps per title (none by default)
     * @return void
     * @see    d::pr()
     * @see    AMI_Debug::dump()
     */
    public static function vd($entity, $title = '', array $aOptions = array()){
        $aOptions['method'] = 'var_dump';
        parent::dump($entity, $title, $aOptions);
    }

    /**
     * Short alias for AMI_Debug::dump() using var_dump().
     *
     * Prints dump once per title.
     *
     * @param  mixed  $entity    Entity
     * @param  string $title     Title
     * @param  array  $aOptions  Options
     * @return void
     * @see    d::vd()
     * @see    AMI_Debug::dump()
     */
    public static function vd1($entity, $title = '', array $aOptions = array()){
        $aOptions['method'] = 'var_dump';
        $aOptions['max_count'] = 1;
        parent::dump($entity, $title, $aOptions);
    }

    /**
     * Short alias for AMI_Debug::dump() using var_export().
     *
     * @param  mixed  $entity    Entity
     * @param  string $title     Title
     * @param  array  $aOptions  Options
     * @return void
     * @see    d::pr()
     * @see    AMI_Debug::dump()
     */
    public static function ve($entity, $title = '', array $aOptions = array()){
        $aOptions['method'] = 'var_export';
        parent::dump($entity, $title, $aOptions);
    }

    /**
     * Short alias for AMI_Debug::dump() using debug_zval_dump().
     *
     * @param  mixed  $entity    Entity
     * @param  string $title     Title
     * @param  array  $aOptions  Options
     * @return void
     * @see    d::pr()
     * @see    AMI_Debug::dump()
     * @since  5.14.4
     */
    public static function zd($entity, $title = '', array $aOptions = array()){
        $aOptions['method'] = 'debug_zval_dump';
        parent::dump($entity, $title, $aOptions);
    }

    /**
     * Adds bench.
     *
     * Short alias for AMI_Debug::bench().
     *
     * Example:
     * <code>
     * AMI::getSingleton('response')->displayBench();
     * d::b('on start');
     * // some code
     * sleep(1);
     * d::b('on end');
     * </code>
     *
     * @param  string $label  Label
     * @return void
     * @see    AMI_Debug::bench()
     */
    public static function b($label){
        parent::bench($label);
    }
}
