<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Environment
 * @version   $Id: AMI_Registry.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.10.0
 */

/**
 * Registry.
 *
 * Since 5.12.0 version following data is available for reading.<br /><br />
 *
 * Common context:
 * - lang_data - data language (string).
 *
 * Admin context:
 * - lang - interface language (string).
 *
 * Full front module context:
 * - page/id - requested page id (from Site Manager, int);
 * - page/modId - requested page module id (from Site Manager, string, 'page_404' if requested page not found);
 * - page/itemId - item id (from requested module, string, '0' if no element was requested, '-1' if element sublink is wrong);
 * - page/catId - item category id (from requested module, string, '0' if no category was requested, '-1' if category sublink is wrong);
 * - page/isAvailable - front availability flag (bool);
 * - page/seoData/index - robots meta 'index' (since 5.12.4);
 * - page/seoData/follow - robots meta 'follow' (since 5.12.4).
 *
 * Example:
 * <code>
 * // Let our plugin is placed at all eshop catalog pages and we need to display a message for some products.
 * // my_specblock.php
 * $aProductIds = array(1, 2, 3);
 * $resultHtml = '';
 * if(
 *     AMI_Registry::get('page/isAvailable') &&
 *     AMI_Registry::get('page/modId') == 'eshop_item') &&
 *     in_array(AMI_Registry::get('page/itemId'), $aProductIds)
 * ){
 *     $resultHtml = 'Extraordinary product';
 * }
 * </code>
 *
 * @package Environment
 * @static
 */
final class AMI_Registry{
    /**
     * Data
     *
     * @var array
     */
    private static $aRegistry = array();

    /**
     * Data stack
     *
     * @var array
     */
    private static $aStack = array();

    /**
     * @var array
     */
     // static private $readOnly = array();

    /**
     * Sets key value.
     *
     * Example:
     * <code>
     * AMI_Registry::set('myKey1', 'myValue1');
     * AMI_Registry::set('myKey2', 'myValue2');
     * </code>
     *
     * @param  string $key    Key
     * @param  mixed  $value  Value
     * @return void
     */
    public static function set($key, $value){
        if(mb_strpos($key, '/') === FALSE){
            self::$aRegistry[$key] = $value;
        }else{
            $aKeys = explode('/', $key);
            $aTarget = &self::$aRegistry;
            foreach($aKeys as $key){
                if(!isset($aTarget[$key])){
                    $aTarget[$key] = array();
                }
                $aPrevious = $aTarget;
                $aTarget = &$aTarget[$key];
            }
            $aTarget = $value;
        }
    }

    /**
     * Sets/unsets multiple keys/values
     *
     * @param  array $array
     * @param  array $keys  All pairs of key/value will be set to registry if empty, specified keys otherwise
     * #param  bool $readOnly
     * @return void
     */
    /*
    public static function setArray(array $array, array $keys = array()){ // , $readOnly = false
        if(!sizeof($keys)){
            $keys = array_keys($array);
        }
        foreach($keys as $key){
            self::set($key, $array[$key]); // , $readOnly
        }
    }
    */

    /**
     * Sets/unsets key value by reference.
     *
     * @param  string $key    Key
     * @param  mixed &$value  Value
     * #param  bool $readOnly
     * @return void
     * @amidev temporary?
     */
    public static function setByRef($key, &$value){ // , $readOnly = false
        // if(!isset(self::$readOnly[$key])){
        self::$aRegistry[$key] = &$value;
        /*
            if($readOnly){
                self::$readOnly[$key] = true;
            }
        }else{
            trigger_error('Cannot set read only key ' . $key, E_USER_ERROR);
        }
        */
    }

    /**
     * Returns value by specified key.
     *
     * Example:
     * <code>
     * AMI_Registry::set('myKey1', 'myValue1');
     * ...
     * $value = AMI_Registry::get('myKey1');
     * // $value == 'myValue1'
     * </code>
     *
     * <br />
     *
     * Example:
     * <code>
     * AMI_Registry::set('options', array(
     *     'section1' => array(
     *          'key1' => 'value1',
     *          'key2' => 'value2'
     *     ),
     *     'section2' => array(
     *          'key1' => array(
     *              'k1' => 'v1',
     *              'k2' => 'v2'
     *          )
     * ));
     * ...
     * $aSection1 = AMI_Registry::get('options/section1', array());
     * // $aSection1 == array(
     * //     'key1' => 'value1',
     * //     'key2' => 'value2'
     * // )
     * ...
     * $k2 = AMI_Registry::get('options/section2/key1/k2');
     * // $k2 == 'v2'
     *
     * $k3 = AMI_Registry::get('options/section2/key1/k3', 'k3DefaultValue');
     * // $k3 == 'k3DefaultValue'
     *
     * </code>
     *
     * @param  string $key     Key
     * @param  mixed $default  Default value, will be returned if there is no key in the registry
     * @return mixed
     */
    public static function get($key, $default = null){
        if(mb_strpos($key, '/') === FALSE){
            if(is_null($default)){
                return self::$aRegistry[$key];
            }else{
                return isset(self::$aRegistry[$key]) ? self::$aRegistry[$key] : $default;
            }
        }else{
            $aPath = explode('/', $key);
            $count = sizeof($aPath);
            if($count){
                $index = 0;
                $entity = self::$aRegistry;
                while($index < $count){
                    if(is_array($entity) && array_key_exists($aPath[$index], $entity)){
                        $entity = $entity[$aPath[$index]];
                        $index++;
                    }else{
                        if(is_null($default)){
                            trigger_error("Invalid key '" . $key . "'", E_USER_WARNING);
                        }
                        return $default;
                    }
                }
                return $entity;
            }else{
                trigger_error("Invalid key '" . $key . "'", E_USER_WARNING);
            }
        }
    }

    /**
     * Returns key existence.
     *
     * @param  string $key  Key
     * @return bool
     */
    public static function exists($key){
        if(mb_strpos($key, '/') === FALSE){
            return isset(self::$aRegistry[$key]);
        }else{
            $aKeys = explode('/', $key);
            $aTarget = &self::$aRegistry;
            foreach($aKeys as $key){
                if(!isset($aTarget[$key])){
                    return FALSE;
                }
                $aTarget = &$aTarget[$key];
            }
            return TRUE;
        }
    }

    /**
     * Deletes key and returns value before deletion.
     *
     * @param  string $key  Key
     * @return mixed
     */
    public static function delete($key){
        $value = null;
        if(mb_strpos($key, '/') === FALSE){
            if(isset(self::$aRegistry[$key])){
                $value = self::$aRegistry[$key];
            }
            unset(self::$aRegistry[$key]);
            return $value;
        }else{
            $aKeys = explode('/', $key);
            $lastKey = array_pop($aKeys);
            $aTarget = &self::$aRegistry;
            foreach($aKeys as $key){
                if(!isset($aTarget[$key])){
                    return $value;
                }
                $aTarget = &$aTarget[$key];
            }
            if(isset($aTarget[$lastKey])){
                $value = $aTarget[$lastKey];
            }
            unset($aTarget[$lastKey]);
            return $value;
        }
    }

    /**
     * Pushes current key value, replaces by passed value.
     *
     * @param  string $key   Key
     * @param  mixed $value  Value
     * @return void
     * @see    AMI_Registry::pop()
     * @amidev
     */
    public static function push($key, $value){
        if(empty(self::$aStack[$key])){
            self::$aStack[$key] = array();
        }
        self::$aStack[$key][] = self::get($key, '{registry-key-null}');
        self::set($key, $value);
    }

    /**
     * Pops key value from stack.
     *
     * @param  string $key  Key
     * @return void
     * @see    AMI_Registry::push()
     * @amidev
     */
    public static function pop($key){
        if(!empty(self::$aStack[$key])){
            $value = array_pop(self::$aStack[$key]);
            self::set($key, $value !== '{registry-key-null}' ? $value : null);
            if(!sizeof(self::$aStack[$key])){
                unset(self::$aStack[$key]);
            }
        }
    }

    /**
     * Returns all registry.
     *
     * @return array
     * @amidev
     */
    public static function getAll(){
        return self::$aRegistry;
    }

    /**
     * Singleton constructor.
     */
    private function __construct(){
    }

    /**
     * Singleton cloning.
     */
    private function __clone(){
    }
}
