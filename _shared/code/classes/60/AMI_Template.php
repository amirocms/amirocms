<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Template
 * @version   $Id: AMI_Template.php 49169 2014-03-28 11:08:50Z Leontiev Anton $
 * @since     5.10.0
 */

/**
 * Template parser class interface.
 *
 * @package Template
 * @since   5.10.0
 */
interface AMI_iTemplate{
    /**#@+
     * Templates/locales path part.
     *
     * @var   string
     * @since 5.14.8
     */

    /**
     * Hypermodule/configuration template path part.
     */
    const TPL_HYPER_PATH     = 'templates/hyper';

    /**
     * Module template path part.
     */
    const TPL_MOD_PATH       = 'templates/modules';

    /**
     * Hypermodule/configuration locale path part.
     */
    const LNG_HYPER_PATH     = 'templates/lang/hyper';

    /**
     * Module locale path part.
     */
    const LNG_MOD_PATH       = 'templates/lang/modules';

    /**
     * Module local template path part.
     */
    const LOCAL_TPL_MOD_PATH = '_local/_admin/templates/modules';

    /**
     * Module local locale path part.
     */
    const LOCAL_LNG_MOD_PATH = '_local/_admin/templates/lang/modules';

    /**
     * Module shared template path part.
     *
     * @amidev
     */
    const SHARED_TPL_MOD_PATH = '_shared/code/templates/modules';

    /**
     * @amidev
     */
    const TPL_MOD_PATH_FORM = 'templates/lang/modules/_form.tpl';

    /**#@-*/

    /**#@+
     * Templates/locales path part.
     *
     * @var   string
     * @since 6.0.2
     */

    /**
     * Common template path part.
     */
    const TPL_PATH = 'templates';

    /**
     * Common locale path part.
     */
    const LNG_PATH = 'templates/lang';

    /**
     * Module local template path part.
     */
    const LOCAL_TPL_PATH = '_local/_admin/templates';

    /**
     * Module local locale path part.
     */
    const LOCAL_LNG_PATH = '_local/_admin/templates/lang';

    /**#@-*/

    /**
     * Adds block.
     *
     * @param  string $name      Internal block name
     * @param  string $path      Template path
     * @param  bool   $override  Flag specifying to override existing loaded block
     * @return void
     */
    public function addBlock($name, $path, $override = FALSE);

    /**
     * Merges block to the existing block.
     *
     * @param  string $name  Internal block name
     * @param  string $path  Template path
     * @return void
     * @since  5.12.4
     */
    public function mergeBlock($name, $path);

    /**
     * Drops block.
     *
     * @param  string $name  Internal block name
     * @return void
     */
    public function dropBlock($name);

    /**
     * Returns true if block set is set.
     *
     * @param  string $name  Set name in format "{$name}:{$setName}"
     * @return bool
     */
    public function issetSet($name);

    /**
     * Parses block set.
     *
     * @param  string $name    Set name in format "{$blockName}:{$setName}"
     * @param  array  $aScope  Scope
     * @return string
     */
    public function parse($name, array $aScope = array());

    /**
     * Parses string.
     *
     * @param  string $string  String to parse
     * @param  array  $aScope  Scope
     * @return string
     */
    public function parseString($string, array $aScope = array()); // @since  5.12.0

    /**
     * Sets current template locale.
     *
     * @param  string $locale  New locale name
     * @return void
     */
    public function setLocale($locale);

    /**
     * Parses locale and return array having locale names as keys and its values as values.
     *
     * @param  string $path      Locales path
     * @param  string $locale    Locale for data in locale file. Use 'auto' for current locale.
     * @param  bool   $override  Flag specifying to override existing loaded locale
     * @return array
     */
    public function parseLocale($path, $locale = 'auto', $override = FALSE);

    /**
     * Sets block locale.
     *
     * @param  string $name      Block name
     * @param  array  $aLocale   Array of locale data as language_variable => translation
     * @param  bool   $doAppend  Append locale to existing one
     * @return void
     */
    public function setBlockLocale($name, array $aLocale, $doAppend = false);

    /**
     * Returns block locale.
     *
     * @param  string $name  Block name
     * @return array
     * @since  5.14.0
     */
    public function getBlockLocale($name);
}

/**
 * Template parser lite class that works with disk templates only.
 *
 * @package  Template
 * @see      PlgAJAXRespFront.php, PlgAJAXRespFront::__construct()
 * @see      PlgAJAXRespFront.php, PlgAJAXRespFront::getHTML()
 * @example  template/AMI_Template.php AMI_Template usage example
 * @example  template/AMI_Template.tpl AMI_Template usage example template
 * @example  template/AMI_Template.lng AMI_Template usage example locale file
 * @resource env/template <code>AMI::getResource('env/template')</code>
 * @since    5.10.0
 */
class AMI_Template implements AMI_iTemplate{
    /**
     * Default locale
     *
     * @var string
     */
    private $locale = 'en';

    /**
     * Set of template blocks
     *
     * @var array
     */
    private $aBlocks = array();

    /**
     * Data set that accessible in parse runtime
     *
     * @var array
     */
    private $aRuntimeScope = array();

    /**
     * Locales cache
     *
     * @var array
     */
    private $aLocales = array();

    /**#@+
     * AMI_iTemplate interface implementation
     */

    /**
     * Adds block.
     *
     * @param  string $name      Internal block name
     * @param  string $path      Template path
     * @param  bool   $override  Flag specifying to override existing loaded block
     * @return void
     */
    public function addBlock($name, $path, $override = FALSE){
        if(isset($this->aBlocks[$name])){
            if(!$override){
                return;
            }
            $this->dropBlock($name);
        }
        $this->aBlocks[$name] = $this->_parseTemplate($path);
    }

    /**
     * Merges block to the existing block.
     *
     * @param  string $name  Internal block name
     * @param  string $path  Template path
     * @return void
     * @since  5.12.4
     */
    public function mergeBlock($name, $path){
        if(isset($this->aBlocks[$name])){
            $this->aBlocks[$name] = array_merge_recursive($this->aBlocks[$name], $this->_parseTemplate($path));
        }else{
            $this->addBlock($name, $path);
        }
    }

    /**
     * Drops block.
     *
     * @param  string $name  Internal block name
     * @return void
     */
    public function dropBlock($name){
        if(isset($this->aBlocks[$name])){
            unset($this->aBlocks[$name]);
        }
    }

    /**
     * Returns true if block set is set.
     *
     * @param  string $name  Set name in format "{$blockName}:{$setName}"
     * @return bool
     */
    public function issetSet($name){
        $blockName = $name;
        $setName = 'main_body';

        if(($iPosition = mb_strpos($name, ":")) !== false){
            $setName = mb_substr($name, $iPosition + 1);
            $blockName = mb_substr($blockName, 0, $iPosition);
        }

        return isset($this->aBlocks[$blockName]) && isset($this->aBlocks[$blockName]['sets'][$setName]);
    }

    /**
     * Parses block set.
     *
     * @param  string $name    Set name in format "{$blockName}:{$setName}"
     * @param  array  $aScope  Scope
     * @return string
     */
    public function parse($name, array $aScope = array()){
        $content = '';
        $blockName = $name;
        $setName = 'main_body';

        if(($iPosition = mb_strpos($name, ":")) !== false){
            $setName = mb_substr($name, $iPosition + 1);
            $blockName = mb_substr($blockName, 0, $iPosition);
        }

        if(isset($this->aBlocks[$blockName])){
            // Correct addon
            if(isset($this->aBlocks[$blockName]['sets:parameters'][$setName]) && sizeof($this->aBlocks[$blockName]['sets:parameters'][$setName]) > 0){
                $aPrameters = &$this->aBlocks[$blockName]['sets:parameters'][$setName];
                foreach($this->aBlocks[$blockName]["sets:priorities"][$setName] as $parametersIndex => $null){
                    $isAllFound = true;
                    foreach($aPrameters[$parametersIndex] as $varName => $varValue){
                        if($aScope[$varName] != $varValue){
                            $isAllFound = false;
                            break;
                        }
                    }
                    if($isAllFound){
                        $setName .= ':'.$parametersIndex;
                        break;
                    }
                }
            }

            $content = $this->_parseSet($this->aBlocks[$blockName]['sets'][$setName], $this->aBlocks[$blockName]['locale'], $aScope);
        }

        return $content;
    }

    /**
     * Parses string.
     *
     * @param  string $string  String to parse
     * @param  array  $aScope  Scope
     * @return string
     */
    public function parseString($string, array $aScope = array()){ // @since  5.12.0
        $this->aBlocks['_tmp_'] = $this->_parseContent($string);
        $result = $this->parse('_tmp_', $aScope);
        $this->dropBlock('_tmp_');
        return $result;
    }

    /**
     * Sets current template locale.
     *
     * @param  string $locale  New locale name
     * @return void
     */
    public function setLocale($locale){
        if(preg_match('/[a-z]{2}/', $locale)){
            $this->locale = $locale;
        }
    }

    /**
     * Parses locale and return array having locale names as keys and its values as values.
     *
     * @param  string $path      Locale path
     * @param  string $locale    Locale for data in locale file, use 'auto' for current locale
     * @param  bool   $override  Flag specifying to override existing loaded locale
     * @return array
     */
    public function parseLocale($path, $locale = 'auto', $override = FALSE){
        $aResult = array();

        if($locale == 'auto'){
            $locale = $this->locale;
        }

        if(!$override && isset($this->aLocales[$locale . '|' . $path])){
            return $this->aLocales[$locale . '|' . $path];
        }

        $content = $this->_readFile($path);
        $content = preg_replace('/##--.*?--##/s', '', $content);

        $langPostfix = "%" . $locale;
        $langPostfixLength = mb_strlen($langPostfix);

        $aSplitted = mb_split("%%", $content);
        $iSplittedCount = count($aSplitted);
        for($i = 1; $i < $iSplittedCount; $i += 2){
            if(mb_strpos($aSplitted[$i], $langPostfix) === (mb_strlen($aSplitted[$i]) - $langPostfixLength)){
                $aResult[mb_substr($aSplitted[$i], 0, mb_strlen($aSplitted[$i]) - $langPostfixLength)] = trim($aSplitted[$i+1]);
            }
        }

        $this->aLocales[$locale . '|' . $path] = $aResult;

        return $aResult;
    }

    /**
     * Sets block locale.
     *
     * @param  string $name      Block name
     * @param  array  $aLocale   Array of locale data as language_variable => translation
     * @param  bool   $doAppend  Append locale to existing one
     * @return void
     */
    public function setBlockLocale($name, array $aLocale, $doAppend = false){
        if(isset($this->aBlocks[$name])){
            $this->aBlocks[$name]['locale'] =
                $doAppend
                    ? array_merge($this->aBlocks[$name]['locale'], $aLocale)
                    : $aLocale;
        }
    }

    /**
     * Returns block locale.
     *
     * @param  string $name  Block name
     * @return array
     * @since  5.14.0
     */
    public function getBlockLocale($name){
        return
            isset($this->aBlocks[$name])
                ? $this->aBlocks[$name]['locale']
                : array();
    }

    /**#@-*/

    /**
     * Reads content from file.
     *
     * @param  string $path  File path relative to system root
     * @return string
     */
    private function _readFile($path){
        $content = '';
        if(is_file($path)){
            $content = file_get_contents($path);
        }
        return $content;
    }

    /**
     * Parses set name and return original set name and parameters string.
     *
     * @param  string $fullSetName  Set name with parameters
     * @return array
     */
    private function getSetParameters($fullSetName){
        $aResult = array(
            'name'       => trim($fullSetName),
            'parameters' => array()
        );

        if(preg_match('/(.*?)\((.*)\)/', $fullSetName, $aSetParts)){
            $aResult['name'] = trim($aSetParts[1]);
            $setParameters = ','.$aSetParts[2];
            if(preg_match_all('/, *([a-z0-9_]+) *?\=(?: *?(\'|")(.*?)\2|([^,]*))/si', $setParameters, $aParamParts) > 0){
                $iFoundCount = sizeof($aParamParts[0]);
                for($i = 0; $i < $iFoundCount; $i++){
                    if(empty($aParamParts[2][$i])){
                        $aResult['parameters'][$aParamParts[1][$i]] = trim($aParamParts[4][$i]);
                    }else{
                        $aResult['parameters'][$aParamParts[1][$i]] = trim($aParamParts[3][$i]);
                    }
                }
            }
        }

        return $aResult;
    }

    /**
     * Splits template by sets and do some prepareing operations and returns template data.
     *
     * @param  string $path  File path relative to system root
     * @return array
     */
    private function _parseTemplate($path){
        $content = $this->_readFile($path);
        return $this->_parseContent($content);
    }

    /**
     * Splits content by sets and do some prepareing operations and returns template data.
     *
     * @param  string $content  File path relative to system root
     * @return array
     */
    private function _parseContent($content){
        $aResult = array(
            'sets'            => array(),
            'sets:priorities' => array(),
            'sets:parameters' => array(),
            'locale'          => array()
        );

        $bBodyCreated = false;
        $content = preg_replace('/##--.*?--##/s', '', $content);
        while(preg_match('/<!--#set\s+var="(.+?)"\s+value="(.*?)"-->/s', $content, $aMatches)){
            $aSetNames = explode(';', $aMatches[1]);
            foreach($aSetNames as $setName){
                if(!empty($setName)){
                    // Get set parameters
                    $aSetNameData = $this->getSetParameters($setName);
                    $setName = $aSetNameData['name'];
                    $setParameters = &$aSetNameData['parameters'];
                    if(sizeof($setParameters) > 0){
                        $parameterIndex = isset($aResult['sets:parameters'][$setName]) ? sizeof($aResult['sets:parameters'][$setName]) : 0;
                        if($parameterIndex == 0){
                            $aResult['sets:parameters'][$setName] = array();
                        }
                        $setPriority = 0;
                        if(isset($setParameters['priority'])){
                            $setPriority = intval($setParameters['priority']);
                            unset($setParameters['priority']);
                        }
                        $aResult["sets:priorities"][$setName][$parameterIndex] = $parameterIndex - $setPriority * 1000;
                        $aResult['sets:parameters'][$setName][$parameterIndex] = $setParameters;
                        $setName .= ':'.$parameterIndex;
                    }

                    if($setName == 'main_body'){
                        $bBodyCreated = true;
                    }

                    $aResult['sets'][$setName] = $aMatches[2];
                }
            }
            $content = str_replace($aMatches[0], '', $content);
        }

        if(!$bBodyCreated){
            $aResult['sets']['main_body'] = $content;
        }

        return $aResult;
    }

    /**
     * Sets variables array that will be accessible in parse operation runtime.
     *
     * @param  array $aScope  Scope
     * @return void
     */
    private function _setRuntimeScope(array $aScope){
        $this->aRuntimeScope = $aScope;
    }

    /**
     * Returns valriable value from runtime scope.
     *
     * @param  array $aMatches  Matches for regular expression of variable parsing
     * @return string
     */
    private function _getScopeVariable(array $aMatches){
        return
            isset($this->aRuntimeScope[$aMatches[1]])
            ? $this->aRuntimeScope[$aMatches[1]]
            : '';
    }

    /**
     * Replaces variables in set to values and return result.
     *
     * @param  string $content  Set content
     * @param  array  $aLocale  Locale data
     * @param  array  $aScope   Scope
     * @return string
     */
    private function _parseSet($content, array $aLocale, array $aScope){
        $this->_setRuntimeScope($aScope);
        $content = preg_replace_callback('/##(.*?)##/', array($this, '_getScopeVariable'), $content);
        $this->_setRuntimeScope($aLocale);
        $content = preg_replace_callback('/%%(.*?)%%/', array($this, '_getScopeVariable'), $content);
        $this->_setRuntimeScope(array());
        return $content;
    }
}

/**
 * System template parser class.
 *
 * @package  Template
 * @see      PlgAJAXRespAdmin.php, PlgAJAXRespAdmin::__construct()
 * @see      PlgAJAXRespAdmin.php, PlgAJAXRespAdmin::getResponse()
 * @example  template/AMI_TemplateSystem.php AMI_TemplateSystem usage example
 * @example  template/templates/AMI_TemplateSystem.tpl AMI_TemplateSystem usage example template
 * @example  template/templates/AMI_TemplateSystem.inclusion.tpl AMI_TemplateSystem usage example template inclusion
 * @example  template/templates/lang/AMI_TemplateSystem.lng AMI_TemplateSystem usage example locale file
 * @resource env/template_sys <code>AMI::getResource('env/template_sys')</code>
 * @since    5.10.0
 */
class AMI_TemplateSystem implements AMI_iTemplate{
    /**
     * System templater
     *
     * @var GUI_template
     */
    private $oTpl;

    /**
     * Internal read mode / current dir stack
     *
     * @var array
     */
    private $aStack = array();

    /**
     * Constructor.
     */
    public function __construct(){
        if(AMI_Registry::exists('oGUI')){
            $this->oTpl = AMI_Registry::get('oGUI');
        }else{
            $this->oTpl = new gui;
            AMI_Registry::set('oGUI', $this->oTpl);
        }
    }

    /**
     * Sets source for templates or language files for location. Mask and subdirectories are not supported.
     *
     * Example:
     * <code>
     * $oTpl = new AMI_TemplateSystem;
     * $oTpl->setLocationSource('templates', 'db');
     * </code>
     *
     * @param  string $location  Path to template files
     * @param  string $mode      Template location: 'fs' | 'db' | 'auto'
     * @return void
     */
    public function setLocationSource($location, $mode = 'auto'){
        if($mode === 'fs'){
            $mode = 'disk';
        }
        $this->oTpl->setLocationReadMode($location, $mode);
    }

    /**
     * Adds block.
     *
     * @param  string $name      Internal block name
     * @param  string $path      Template path
     * @param  bool   $override  Flag specifying to override existing loaded block
     * @return void
     */
    public function addBlock($name, $path, $override = FALSE){
        $this->pushState($path);
        $this->oTpl->addBlock($name, $path, $override);
        $this->popState();
    }

    /**
     * Merges block to the existing block.
     *
     * @param  string $name  Internal block name
     * @param  string $path  Template path
     * @return void
     * @since  5.12.4
     */
    public function mergeBlock($name, $path){
        $this->pushState($path);
        $this->oTpl->mergeBlock($name, $path);
        $this->popState();
    }

    /**
     * Drops block.
     *
     * @param  string $name  Internal block name
     * @return void
     */
    public function dropBlock($name){
        $this->oTpl->dropBlock($name);
    }

    /**
     * Returns true if block set is set.
     *
     * @param  string $name  Set name in format "{$blockName}:{$setName}"
     * @return bool
     */
    public function issetSet($name){
        return $this->oTpl->issetSet($name);
    }

    /**
     * Returns array of available variables in set.
     *
     * @return array
     * @since  5.12.8
     */
    public function getSetScope(){
        return $this->oTpl->getAvailableVars();
    }

    /**
     * Sets current set variable.
     *
     * @param  string $name  Variable name
     * @param  string $value  Variable value
     * @return void
     * @since  5.12.8
     */
    public function setScopeVar($name, $value = ''){
        $this->oTpl->setVariable($name, $value);
    }

    /**
     * Parses block set.
     *
     * @param  string $name    Set name in format "{$blockName}:{$setName}"
     * @param  array  $aScope  Scope
     * @return string
     */
    public function parse($name, array $aScope = array()){
        return $this->oTpl->get($name, $aScope);
    }

    /**
     * Parses string.
     *
     * @param  string $string  String to parse
     * @param  array  $aScope  Scope
     * @return string
     */
    public function parseString($string, array $aScope = array()){ // @since  5.12.0
        return $this->oTpl->parseText($string, $aScope);
    }

    /**
     * Sets current template locale.
     *
     * @param  string $locale  New locale name
     * @return void
     */
    public function setLocale($locale){
        $this->oTpl->_setLang($locale);
    }

    /**
     * Parses locale and return array having locale names as keys and its values as values.
     *
     * @param  string $path      Locale path
     * @param  string $locale    Locale for data in locale file, use 'auto' for current locale
     * @param  bool   $override  Flag specifying to override existing loaded locale
     * @return array
     */
    public function parseLocale($path, $locale = 'auto', $override = FALSE){
        $this->pushState($path);
        if($locale != 'auto'){
            $prevLocale = $this->oTpl->getLang();
        }
        if(!$override && isset($this->aLocales[$locale . '|' . $path])){
            return $this->aLocales[$locale . '|' . $path];
        }
        if($locale != 'auto'){
            $this->oTpl->_setLang($locale);
        }
        $aRes = $this->oTpl->parseLangFile($path);
        if($locale != 'auto'){
            $this->oTpl->_setLang($prevLocale);
        }
        $this->popState();
        $this->aLocales[$locale . '|' . $path] = $aRes;
        return $aRes;
    }

    /**
     * Sets block locale from array.
     *
     * @param  string $name      Block name
     * @param  array  $aLocale   Array of locale data as language_variable => translation
     * @param  bool   $doAppend  Append locale to existing one
     * @return void
     */
    public function setBlockLocale($name, array $aLocale, $doAppend = FALSE){
        $this->oTpl->html[$name]['lang'][$this->oTpl->lang] =
            $doAppend && isset($this->oTpl->html[$name]) && isset($this->oTpl->html[$name]['lang'][$this->oTpl->lang])
                ? array_merge($this->oTpl->html[$name]['lang'][$this->oTpl->lang], $aLocale)
                : $aLocale;
    }

    /**
     * Returns block locale.
     *
     * @param  string $name  Block name
     * @return array
     * @since  5.14.0
     */
    public function getBlockLocale($name){
        return
            isset($this->oTpl->html[$name]) && isset($this->oTpl->html[$name]['lang'][$this->oTpl->lang])
                ? $this->oTpl->html[$name]['lang'][$this->oTpl->lang]
                : array();
    }

    /**
     * Adds JavaScript inclusion.
     *
     * @param  string $path        Path to the javascript file
     * @param  bool   $useAsync    Use async script inclusion if TRUE
     * @param  bool   $isFullPath  The path is full if TRUE
     * @return void
     * @since  5.14.4
     */
    public function addScript($path, $useAsync = FALSE, $isFullPath = FALSE){
        $tmp = $this->oTpl->UseFullPath;
        $this->oTpl->UseFullPath = $isFullPath;
        $this->oTpl->addScript($path, $useAsync);
        $this->oTpl->UseFullPath = $tmp;
    }

    /**
     * Adds JavaScript code to the page.
     *
     * @param  string $code  JavaScript code
     * @return void
     * @since  5.14.8
     */
    public function addScriptCode($code){
        $this->oTpl->addHtmlScript($code);
    }

    /**
     * Adds the global variables. Parser will replace template's variables with the local first and then with the globals.
     *
     * @param array $aVars  Array of the global variables. $aVars["variable"] = value;
     * @since  5.14.8
     * @return void
     * @amidev
     */
    public function addGlobalVars(array $aVars){
        $this->oTpl->addGlobalVars($aVars);
    }

    /**
     * Deletes the global variables.
     *
     * @param array $aVarNames  Array of the global variables to delete
     * @since  6.0.2
     * @return void
     * @amidev
     */
    public function deleteGlobalVars(array $aVarNames){
        $this->oTpl->removeGlobalVars($aVarNames);
    }

    /**
     * Gets global vaiable.
     *
     * @param string $varName  Name of desired global variable
     * @since  5.14.8
     * @return mixed
     * @amidev
     */
    public function getGlobalVar($varName){
        return $this->oTpl->getGlobalVar($varName);
    }

    /**
     * Checks the file is valid.
     *
     * @param  string $file  File path
     * @return bool
     * @amidev
     */
    public function isValidFile($file){
        return $this->oTpl->isValidFile($file);
    }

    /**
     * Saves state.
     *
     * Pushes read mode (db/fs) and current work directory to the internal stack,
     * changes theese parameters based on passed path.
     *
     * @param  string $path  Resource path
     * @return void
     * @amidev
     */
    private function pushState($path){
        $this->aStack[] = array(
            $this->oTpl->getReadFromDB(),
            getcwd()
        );
        if(strncmp($path, '_local/plugins', 14) == 0){
            $this->oTpl->setReadFromDB(false);
            chdir($GLOBALS['ROOT_PATH']);
        }
    }

    /**
     * Restores state.
     *
     * Pops read mode (db/fs) and current work directory from the internal stack.
     *
     * @return void
     * @see    AMI_TemplateCommon::pushState()
     * @amidev
     */
    private function popState(){
        $aData = array_pop($this->aStack);
        if($this->oTpl->getReadFromDB() !== $aData[0]){
            $this->oTpl->setReadFromDB($aData[0]);
        }
        if(getcwd() !== $aData[1]){
            chdir($aData[1]);
        }
    }
}
