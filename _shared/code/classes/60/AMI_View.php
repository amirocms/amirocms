<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   ModuleComponent
 * @version   0.8 alpha
 * @since     5.10.0
 */

/**
 * View abstraction.
 *
 * @package    ModuleComponent
 * @subpackage View
 */
abstract class AMI_View{
    /**
     * Template file name
     *
     * @var string
     */
    protected $tplFileName = '';

    /**
     * Template block name
     *
     * @var string
     */
    protected $tplBlockName = '';

    /**
     * Locale file name
     *
     * @var string
     */
    protected $localeFileName = '';

    /**
     * Locale
     *
     * @var array
     */
    protected $aLocale = array();

    /**
     * Scope
     *
     * @var array
     */
    protected $aScope = array();

    /**
     * Script files
     *
     * @var    array
     * @todo   True implementation
     * @amidev Temporary
     */
    protected $aScriptFiles = array();

    /**
     * Script JS code
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aScripts = array();

    /**
     * Model
     *
     * @var mixed
     */
    protected $oModel = null;

    /**
     * Module id
     *
     * @var string
     * @see AMI_View::getModId()
     */
    private $modId;

    /**
     * Constructor.
     */
    public function __construct(){
        $this->prepareTemplates();
        $aModLocalePath = $this->getModLocalePath();
        if($aModLocalePath){
            $this->addLocale($this->getTemplate()->parseLocale($aModLocalePath));
        }
    }

    /**
     * Initialize, processing after setting model.
     *
     * @return AMI_View
     */
    public function init(){
    	return $this;
    }

    /**
     * Sets view scope.
     *
     * @param  array $aScope  Scope
     * @return AMI_View
     */
    public function setScope(array $aScope){
        $this->aScope = $aScope;
        return $this;
    }

    /**
     * Adds locale.
     *
     * @param  array $aLocale      Locale array
     * @param  bool  $doOverwrite  Overwrite existing keys (since 5.14.0)
     * @return AMI_View
     */
    public function addLocale(array $aLocale, $doOverwrite = true){
        $this->aLocale = $doOverwrite ? array_merge($this->aLocale, $aLocale) : $this->aLocale + $aLocale;
        return $this;
    }

    /**
     * Adds locale from resource.
     *
     * @param  string $path  Locales path
     * @return AMI_View
     */
    /*
    public function addLocaleResource($path){
        $this->addLocale($this->getTemplate()->parseLocale($path));
        return $this;
    }
    */

    /**
     * Sets up model object.
     *
     * @param  mixed $oModel   Model
     * @param  array $aLocale  Locale
     * @return AMI_View
     */
    public function setModel($oModel, array $aLocale = array()){
        $this->aLocale = array_merge($this->aLocale, $aLocale);
        return $this->_setModel($oModel);
    }

    /**
     * Returns view data.
     *
     * @return mixed  string | array | etc.
     */
    public abstract function get();

    /**
     * Sets up model object.
     *
     * @param  mixed $oModel  Model
     * @return AMI_View
     */
    protected function _setModel($oModel){
        $this->oModel = $oModel;
        return $this;
    }

    /**
     * Returns module specific locale path.
     *
     * @return string
     * @since  5.12.0
     */
    protected function getModLocalePath(){
        return '';
    }

    /**
     * Returns module id.
     *
     * @return string
     */
    protected function getModId(){
        if(empty($this->modId)){
            $this->modId = AMI::getModId(get_class($this));
        }
        return $this->modId;
    }

    /**
     * Set module id.
     *
     * @param string $modId  Module id
     * @return void
     */
    public function setModId($modId){
        $this->modId = $modId;
    }

    /**
     * Returns template object.
     *
     * @return AMI_iTemplate
     */
    protected function getTemplate(){
        return AMI::getResource('env/template_sys');
    }

    /**
     * Parses block template and returns the result as a string.
     *
     * @param  string $setName  Set name
     * @param  array  $aScope   Scope
     * @return string
     * @since  5.14.4
     */
    protected function parse($setName, array $aScope = array()){
        $oTpl = $this->getTemplate();
        $oTpl->setBlockLocale($this->tplBlockName, $this->aLocale);
        return $oTpl->parse($this->tplBlockName . ':' . $setName, $aScope);
    }

    /**
     * Parses set.
     *
     * @param  string $setPrefix    Set prefix
     * @param  string $setPostfix   Set postfix
     * @param  string $aValues      Scope, can be array or string, if value is not defined - setPostfix used
     * @param  string $value        Value
     * @param  bool   $isSkipEmpty  Allows to skip empty not array values
     * @return string
     * @amidev Temporary
     */
    protected function parseSet($setPrefix, $setPostfix, $aValues = "", $value = "", $isSkipEmpty = false){
        // Moved from CMS_Base::getSetsText() method.
        $res = "";
        if(isset($this->tplBlockName) && !empty($this->tplBlockName)){
            // skip empty values if $isSkipEmpty is set and $aValues is not array and is empty
            if(!$isSkipEmpty || is_array($aValues) || !empty($aValues)){
                $name = empty($value) ? $setPostfix : $value;
                $values = $aValues;
                if(!is_array($aValues)){
                    $values = array();
                    $values[$name] = $aValues;
                }
                if(empty($setPrefix)){
                    $vSet = "";
                }else{
                    $vSet = ":" . $setPrefix . "_" . $setPostfix;
                }
                $res = $this->getTemplate()->parse($this->tplBlockName . $vSet, $values);
            }
        }
        return $res;
    }

    /**
     * Returns prepared view scope.
     *
     * @param  string $type    View type
     * @param  array  $aScope  Scope
     * @return array
     */
    protected function getScope($type, array $aScope = array()){
        $modId = $this->getModId();
        $aScope += $this->aScope + array('_mod_id' => $modId);
        $aEvent = array(
            'type'    => $type,
            'block'   => $this->tplBlockName,
            'aScope'  => &$aScope,
            'modId'   => $modId,
            'oView'   => $this,
            'aLocale' => $this->aLocale
        );
        if(property_exists($this, 'tplSimpleFieldPrefix')){
            $aEvent['tpl_sf_prefix'] = $this->tplSimpleFieldPrefix;
        }
        // AMI_Event::fire('on_before_view', $aEvent);

        /**
         * Allows to modify view scope.
         *
         * @event      on_before_view_* $modId
         * @eventparam string   type     View type
         * @eventparam string   block    Block name
         * @eventparam array    aScope   Scope
         * @eventparam string   modId    Module id
         * @eventparam AMI_View oView    View object
         * @eventparam array    aLocale  Locales
         */
        AMI_Event::fire('on_before_view_' . $type, $aEvent, $modId);

        return $aScope;
    }

    /**
     * Adds script file.
     *
     * Example:
     * <code>
     * // AmiSample_FormViewAdm::__construct()
     * // ...
     * // Add custom script to admin vorm view
     * $this->addScriptFile('_local/plugins_distr/' . $this->getModId() .  '/templates/form.adm.js');
     * </code>
     *
     * @param  string $file  Script file
     * @return AMI_View
     * @todo   True implementation
     */
    public function addScriptFile($file){
        if(mb_strpos($file, '..') !== FALSE){
            trigger_error("Invalid script path '" . $file . "'", E_USER_ERROR);
        }
        $this->aScriptFiles[] = $file;
        return $this;
    }

    /**
     * Adds script code.
     *
     * Example:
     * <code>
     * $this->addScriptCode($this->getTemplate()->parse($this->tplBlockName . ':code'));
     * </code>
     *
     * @param  string $code  JS code without html SCRIPT tags
     * @return AMI_View
     * @since  6.0.6
     */
    public function addScriptCode($code){
        $this->aScripts[] = $code;

        return $this;
    }

    /**
     * Returns module scripts.
     *
     * @param  array $aScope  Scope
     * @return AMI_View
     * @todo   True implementation
     * @amidev Temporary?
     */
    protected function getScripts(array $aScope = array()){
        $scripts = '';
        $oTpl = $this->getTemplate();
        foreach($this->aScriptFiles as $file){
            $path = strncmp($file, '_admin/', 7) != 0 ? $GLOBALS['ROOT_PATH'] . $file : mb_substr($file, 7);
            $scripts .= $oTpl->parseString(file_get_contents($path), $aScope);
        }
        foreach($this->aScripts as $jscode){
            $scripts .= $jscode . "\r\n";
        }
        return $scripts;
    }

    protected function addOpenGraphTags($header, $description, $image = ''){
        // todo - check option

        $scheme = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on") ? 'http' : 'https';
        $url = $scheme . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

        // todo - other types support, maybe using AMI_Registry
        $type = 'article';

        $oGUI = AMI_Registry::get('oGUI');
        $oGUI->addMeta('property', 'og:url', $url);
        $oGUI->addMeta('property', 'og:type', $type);
        $oGUI->addMeta('property', 'og:title', $header);
        if($description != ''){
            $description = AMI_Lib_String::htmlChars(AMI_Lib_String::truncate(strip_tags($description), 250));
            $oGUI->addMeta('property', 'og:description', $description);
        }
        if($image != ''){
            $oGUI->addMeta('property', 'og:image', $image);
        }
    }

    /**
     * Prepares templates paths and blockname.
     *
     * @return void
     * @amidev
     */
    protected function prepareTemplates(){
        if($this->getModId()){
            $postfix = '';
            $isPlugin = false;
            $prefix = $this->getModId();

            $object = new ReflectionObject($this);
            $filename = $object->getFilename();
            if(mb_strpos($filename, 'plugins') > 0){
                $isPlugin = true;
                $prefix = '';
            }
            if($this instanceof AMI_ModListView){
                $postfix = 'list';
            }
            if($this instanceof AMI_ModFilterView){
                $postfix = 'filter';
            }
            if(!$postfix && $this instanceof AMI_ModFormView){
                $postfix = 'form';
            }
            if($postfix){
                $prefix = $prefix . '_';
            }
            $name = $prefix . $postfix;
            if($name){
                if($this instanceof AMI_ExtView){
                    $this->setDefaultExtTemplates($name);
                }else{
                    if($this->tplFileName === ''){
                        $this->tplFileName = ($isPlugin ? AMI_iTemplate::TPL_MOD_PATH : AMI_iTemplate::LOCAL_TPL_MOD_PATH) . '/' . $name . '.tpl';
                    }
                    if($this->tplBlockName === ''){
                        $this->tplBlockName = $name;
                    }
                    if($this->localeFileName === ''){
                        $this->localeFileName = ($isPlugin ? AMI_iTemplate::LNG_MOD_PATH : AMI_iTemplate::LOCAL_LNG_MOD_PATH) . '/' . $name . '.lng';
                    }
                }
            }
        }

        $addTemplate = !is_null($this->tplFileName) && ($this->tplBlockName !== '' && $this->tplFileName !== '');
        $addLocale = !is_null($this->localeFileName) && ($this->localeFileName !== '');

        $oTpl = $this->getTemplate();
        if($addTemplate){
            $oTpl->addBlock($this->tplBlockName, $this->tplFileName);
        }
        if($addLocale){
            $this->aLocale = $oTpl->parseLocale($this->localeFileName);
        }
    }
}

/**
 * Empty view.
 *
 * This view can be used when no view data is present.
 *
 * @package    ModuleComponent
 * @subpackage View
 * @since      5.12.0
 */
class AMI_ViewEmpty extends AMI_View{
    /**
     * Constructor.
     */
    public function __construct(){
    }

    /**
     * Returns view data.
     *
     * Empty view always returns null.
     *
     * @return null
     */
    public function get(){
        return null;
    }
}
