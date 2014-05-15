<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module
 * @version   $Id: AMI_Mod.php 48684 2014-03-14 06:03:17Z Kolesnikov Artem $
 * @since     5.12.0
 */

/**
 * Module action controller.
 *
 * @package    Module
 * @subpackage Controller
 * @since      5.12.0
 * @todo       Decsribe methods
 */
abstract class AMI_Mod{
    /**
     * Component opton specifying to init component directly on start
     *
     * @var   int
     * @since 5.14.8
     */
    const INIT_ON_START = 0x01;

    /**
     * Array of extensions having key as module id and array of initialized extensions
     *
     * @var   array
     * @since 5.14.8
     */
    // protected static $aExtensions = array();

    /**
     * Path to status message locales
     *
     * @var string
     */
    protected $statusMessagePath = '';

    /**
     * Array of objects impleneting AMI_iModComponent interface
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aComponents = array();

    /**
     * Components options
     *
     * @var array
     */
    protected $aComponentsOptions = array();

    /**
     * Set to true after components initialization
     *
     * @var bool
     * @amidev Temporary
     */
    protected $bComponentsInitialized = false;

    /**
     * Set to true if module used to act as a specblock
     *
     * @var bool
     * @amidev Temprary
     */
    protected $bSpecblockMode = false;

    /**
     * Unsupported by side extensions list
     *
     * @var    array
     * @todo   delete on useless
     * @amidev
     */
    protected $aUnsupportedExt = array();

    /**
     * Request object
     *
     * @var AMI_Request
     */
    private $oRequest;

    /**
     * Response object
     *
     * @var AMI_Response
     */
    private $oResponse;

    /**
     * Module id
     *
     * @var string
     * @see AMI_Mod::getModId()
     */
    private $modId = '';

    /**
     * Specifies to use components
     *
     * @var bool
     */
    private $bUseComponents = true;

    /**
     * Resource mapping
     *
     * @var  array
     * @todo fill empty values
     * @see  AMI_Mod::__construct()
     */
    private $aExtResourceMapping = array(
        // 'ext_audit' => '',
        // 'ext_modules_custom_fields' => 'ext_custom_fields',
        'ext_images' => 'ext_image',
        // 'ext_reindex' => '',
        // 'ce_page_break' => ''
    );

    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request
     * @param AMI_Response $oResponse  Response
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        $modId = $this->getModId();

        // Load status messages
        if($this->statusMessagePath === ''){
            $object = new ReflectionObject($this);
            $filename = $object->getFilename();
            if(strpos($filename, 'plugins') === false){
                // Modules
                $this->statusMessagePath = AMI_iTemplate::LOCAL_LNG_MOD_PATH . '/' . $modId . '_messages.lng';
            }else{
                // Plugins
                $this->statusMessagePath = AMI_iTemplate::LNG_MOD_PATH . '/_messages.lng';
            }
        }
        if($this->statusMessagePath !=='' && !is_null($this->statusMessagePath)){
            // Not working in shared mode
            //if(AMI_Registry::get('oGUI')->isValidFile($this->statusMessagePath)){
                $oResponse->loadStatusMessages($this->statusMessagePath);
            //}
        }
        AMI_Event::addHandler('disable_mod_init', array($this, 'handleDisableInit'), AMI_Event::MOD_ANY);
        $this->oRequest = $oRequest;
        $this->oResponse = $oResponse;

        $this->bSpecblockMode = AMI_Registry::get('ami_specblock_mode', false);

        // Load module extensions
        AMI::cleanupModExtensions($modId);
        AMI::initModExtensions($modId, '', $this);
        AMI_Registry::set(
            'AMI/Environment/Model/DefaultAttributes',
            array(
                'extModeOnConstruct' => 'none',
                'extModeOnDestruct'  => 'none'
            )
        );

        /*
        if($this->getModState()->issetOption('extensions')){
            // $aMissedExtensions = array();
            $aExtensions = $this->getModState()->getOption('extensions');
            if(!is_array($aExtensions) && !empty($aExtensions)){
                $aExtensions = array($aExtensions);
            }

            // ext_image hack for multifeeds: turn on if root module has ext_image
            if(AMI::isCategoryModule($modId)){
                list($hyper, $config) = AMI_ModDeclarator::getInstance()->getHyperData($modId);
                if($hyper == 'ami_multifeeds'){
                    $rootModId = str_replace('_cat', '', $modId);
                    $aRootExtensions = AMI::getOption($rootModId, 'extensions');
                    if(in_array('ext_images', $aRootExtensions) || in_array('ext_image', $aRootExtensions)){
                        $aExtensions[] = 'ext_image';
                    }
                }
            }
            // Advertisement extension hack
            if(
                AMI_Registry::get('side') == 'adm' && AMI::issetOption('adv_places', 'adv_modules') &&
                is_array(AMI::getOption('adv_places', 'adv_modules')) &&
                in_array($modId, AMI::getOption('adv_places', 'adv_modules'))
            ){
                $aExtensions[] = 'ext_adv';
            }
            // Images extension hack
            $extImageIndex = array_search('ext_images', $aExtensions);
            if($extImageIndex !== false){
                $aExtensions[$extImageIndex] = 'ext_image';
            }
            // Category extension hack
            if($this->getModState()->issetAndTrueOption('use_categories')){
                $aExtensions[] = 'ext_category';
            }

            $side = AMI_Registry::get('side');
            $aUnsupportedExt =
                isset($this->aUnsupportedExt[$side])
                    ? $this->aUnsupportedExt[$side]
                    : array();
            if($this->getModState()->issetProperty('unsupported_extensions')){
                $aUnsupportedExt =
                    array_merge(
                        $aUnsupportedExt,
                        $this->getModState()->getProperty('unsupported_extensions')
                    );
            }
            // Initialize extension using exceptions
            foreach($aExtensions as $extModId){
                if(
                    AMI::isModInstalled($extModId) ||
                    ($extModId == 'ext_category') || ($extModId == 'ext_image') || ($extModId == 'ext_adv')
                ){
                    $skip = FALSE;
                    // Before mapping
                    if(in_array($extModId, $aUnsupportedExt) || in_array('*', $aUnsupportedExt)){
                        $skip = TRUE;
                    }else{
                        if(isset($this->aExtResourceMapping[$extModId])){
                            $extModId = $this->aExtResourceMapping[$extModId];
                        }
                        // After mapping
                        if(in_array($extModId, $aUnsupportedExt)){
                            $skip = TRUE;
                        }
                    }
                    if(!$skip && $extModId !== ''){
                        $extResId = $extModId;
                        if(AMI_ModDeclarator::getInstance()->isRegistered($extModId)){
                            // Hypermod
                            list($extHyper, $extConfig) = AMI_ModDeclarator::getInstance()->getHyperData($extModId);
                            if(in_array($extConfig, $aUnsupportedExt)){
                                $skip = TRUE;
                            }
                            $extResId = $extModId . '/module/controller/' . AMI_Registry::get('side', 'adm');
                        }
                        if(!$skip && !isset(self::$aExtensions[$modId][$extModId])){
                            if(AMI::isResource($extResId)){
                                // Initialize extension
                                self::$aExtensions[$modId][$extModId] =
                                    AMI::getResource(
                                        $extResId,
                                        array(
                                            $modId,
                                            $modId,
                                            $this
                                        )
                                    );
                            }
                            // elseif($extModId !== 'ext_eshop_custom_fields'){
                            //     $aMissedExtensions[] = "'" . $extModId . "'";###
                            //     // trigger_error("Extension '" . $extModId . "' has no resource id", E_USER_NOTICE);###
                            // }
                        }
                        // else{
                        //     d::vd($extModId . ' skipped');
                        // }
                    }
                }
            }
            // if(sizeof($aMissedExtensions) && AMI_Service::isDebugVisible()){
            //     AMI_Registry::push('disable_error_mail', TRUE);
            //     trigger_error("Missing resource ids for extensions: " . implode(', ', $aMissedExtensions), E_USER_WARNING);###
            //     AMI_Registry::pop('disable_error_mail');
            // }
        }
        */
        AMI_Event::dropHandler('disable_mod_init', $this);
    }

    /**
     * Destructor.
     *
     * @since  6.0.2
     */
    public function __destruct(){
        unset(
            $this->aComponents,
            $this->aComponentsOptions,
            $this->aUnsupportedExt,
            $this->oRequest,
            $this->oResponse
        );
        AMI::cleanupModExtensions($this->getModId());
    }

    /**
     * Get aviable extensions for this module (depends of it's hypermodule).
     *
     * @return array
     * @amidev Temporary
     */
    public function getAviableModExtensions(){
        $aExtensions = array();
        list($hyper, $config) = AMI_ModDeclarator::getInstance()->getHyperData($this->getModId());
        $isCatModule = ($this instanceof AMI_CatModule_Adm);
        $oCore = $GLOBALS['Core'];
        $aModNames = $oCore->GetModNames();
        $aAllExt = array();
        foreach($aModNames as $name){
            if(preg_match('/^ext_/', $name)){
                $aAllExt[] = $name;
                $aSupHyper = AMI_Ext::getHypermodSubtypes($name);
                if($aSupHyper && isset($aSupHyper[$hyper])){
                    if(!empty($aSupHyper[$hyper][$isCatModule ? 'cat' : 'root'])){
                        $aExtensions[] = $name;
                    }
                }
            }
        }
        return $aExtensions;
    }


    /**
     * Get current module extension option value.
     *
     * @return array
     * @amidev Temporary
     */
    public function getModExtensions(){
        $aExtensions = $this->getModState()->getOption('extensions');
        return $aExtensions;
    }

    /**
     * Initializes module.
     *
     * @return AMI_Mod
     * @amidev Temporary
     */
    public function init(){
        AMI_Event::addHandler('dispatch_request', array($this, 'dispatchRequest'), $this->getModId());
        $aEvent = $this->prepareEvent();
        /**
         * Called before initialization module component.
         *
         * @event      on_mod_pre_init $modId
         * @eventparam string       modId         Module id
         * @eventparam AMI_Mod|null oController   Module controller object
         * @eventparam string       tableModelId  Table model resource id
         * @eventparam AMI_Request  oRequest      Request object
         * @eventparam AMI_Response oResponse     Response object
         */
        AMI_Event::fire('on_mod_pre_init', $aEvent, $this->getModId()); #were ext_init_by_module_name
        $this->initComponents();
        /**
         * Called after initialization module component.
         *
         * @event      on_mod_post_init $modId
         * @eventparam string       modId         Module id
         * @eventparam AMI_Mod|null oController   Module controller object
         * @eventparam string       tableModelId  Table model resource id
         * @eventparam AMI_Request  oRequest      Request object
         * @eventparam AMI_Response oResponse     Response object
         */
        AMI_Event::fire('on_mod_post_init', $aEvent, $this->getModId()); #were ext_init_by_module_name
        return $this;
    }

    /**
     * Adds module component.
     *
     * Example:
     * <code>
     * class AmiSample_Adm extends AMI_Mod{
     *     // ...
     *     public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
     *         parent::__construct($oRequest, $oResponse);
     *         $this->addComponent(AMI::getResource($this->getModId() . '/' . $this->getType() .'/controller/adm'));
     *     }
     *     // ...
     * }
     * </code>
     *
     * @param  AMI_iModComponent $oComponent  Module component controller
     * @param  string            $serialId    Component serial id (optional)
     * @param  array             $aOptions    Component options (optional)
     * @return AMI_Mod
     * @see    AMI_Mod::addComponents()
     * @todo   Describe $aOptions parameter
     */
    public function addComponent(AMI_iModComponent $oComponent, $serialId = null, array $aOptions = array()){
        if($this->bUseComponents){
            if(is_null($serialId)){
                $serialId = $this->getModId() . '_' . sizeof($this->aComponents);
            }
            $this->aComponents[$serialId] = $oComponent;
            $this->aComponentsOptions[$serialId] = $aOptions;
        }
        return $this;
    }

    /**
     * Initialize components.
     *
     * @return void
     * @amidev
     */
    public function initComponents(){
        if($this->bUseComponents && !$this->bComponentsInitialized){
            $oRequest = AMI::getSingleton('env/request');
            $componentId = $oRequest->get('componentId', false);

            $aEvent = array(
                'componentId' => $componentId,
                'oController' => $this,
                'aComponents' => &$this->aComponents,
                'aOptions'    => &$this->aComponentsOptions,
            );
            /**
             * Called before module controller components initialization.
             *
             * @event      on_before_init_componets $modId
             * @eventparam string  componentId  Current component id
             * @eventparam AMI_Mod oController  Module controller object
             * @eventparam &array  aComponents  Array with components structure
             * @eventparam &array  aOptions     Array with components options structure
             * @since      5.14.8
             */
            AMI_Event::fire('on_before_init_componets', $aEvent, $this->getModId());

            // Debug purpose
            if(!sizeof($this->aComponentsOptions)){
                d::dump('No components were added', '', array('no_pre' => true));
            }
            foreach($this->aComponentsOptions as $serialId => $aOptions){
                $hasRelated = ($componentId) ? in_array($componentId, $this->getRelatedComponents($serialId)) : false;
                if(!$componentId || ($componentId == $serialId) || $hasRelated){
                    $oComponent = $this->aComponents[$serialId];
                    if($oComponent instanceof AMI_ModComponentStub){
                        if(!isset($aOptions['type'])){
                            trigger_error('Cannot determine component type', E_USER_WARNING);
                            continue;
                        }
                        $type = $aOptions['type'] . (isset($aOptions['postfix']) ? '_' . $aOptions['postfix'] : '');
                        $resId = isset($aOptions['resource']) ? $aOptions['resource'] : $this->getModId() . '/' . $type . '/controller/' . AMI_Registry::get('side');
                        if(isset($aOptions['controller_class'])){
                            AMI::addResource($resId, $aOptions['controller_class']);
                        }
                        if(isset($aOptions['view_class'])){
                            $viewResId = isset($aOptions['view_resource']) ? $aOptions['view_resource'] : $this->getModId() . '/' . $type . '/view/' . AMI_Registry::get('side');
                            AMI::addResource($viewResId, $aOptions['view_class']);
                        }
                        $oComponent = AMI::getResource($resId);
                        if(isset($aOptions['postfix'])){
                            $oComponent->setPostfix($aOptions['postfix']);
                        }
                        $this->aComponents[$serialId] = $oComponent;
                    }
                    $oComponent->setSerialId($serialId);
                    if($componentId || (isset($aOptions['options']) && ($aOptions['options'] & self::INIT_ON_START))){
                        $this->aComponents[$serialId]->init();
                    }
                }
            }
            $this->bComponentsInitialized = true;

            /**
             * Called before module controller components initialization.
             *
             * @event      on_after_init_componets $modId
             * @eventparam string  componentId  Current component id
             * @eventparam AMI_Mod oController  Module controller object
             * @eventparam &array  aComponents  Array with components structure
             * @eventparam &array  aOptions     Array with components options structure
             * @since      5.14.8
             */
            AMI_Event::fire('on_after_init_componets', $aEvent, $this->getModId());
        }
    }

    /**
     * Adds module components by its types.
     *
     * Example:
     * <code>
     * class AmiSample_Adm extends AMI_Mod{
     *     // ...
     *     public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
     *         parent::__construct($oRequest, $oResponse);
     *         // Add following components: 'filter', 'list', 'form'
     *         $this->addComponents(array('filter', 'list', 'form'));
     *     }
     *     // ...
     * }
     * </code>
     *
     * @param  array $aComponents  Components to add
     * @param  array $aOptions     Components options, will be described later (since 5.14.6)
     * @return AMI_Mod
     * @todo   Describe $aOptions parameter
     */
    public function addComponents(array $aComponents, array $aOptions = array()){
        if($this->bUseComponents){
            $side = AMI_Registry::get('side');
            $modId = $this->getModId();
            $aDefaultComponentOptions = array(
                'filter'    => array('related_to' => 'list'),
                'async'     => array('options' => self::INIT_ON_START),
                'specblock' => array('options' => self::INIT_ON_START)
            );
            foreach($aComponents as $aOpt){
                if(!is_array($aOpt)){
                    $type = $aOpt;
                    if(isset($aDefaultComponentOptions[$type])){
                        $aOpt = $aDefaultComponentOptions[$type] + array('type' => $type);
                    }else{
                        $aOpt = array('type' => $type);
                    }
                }
                $id = isset($aOpt['id']) ? $aOpt['id'] : null;
                $this->addComponent(new AMI_ModComponentStub, $id, $aOpt + $aOptions);
            }
        }
        return $this;
    }

    /**
     * Removes module components by its types.
     *
     * Example:
     * <code>
     * class AmiSample_Adm extends AmiParent_Adm{
     *     // ...
     *     public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
     *         parent::__construct($oRequest, $oResponse);
     *         // Remove following components: 'filter'
     *         $this->removeComponents(array('filter'));
     *     }
     *     // ...
     * }
     * </code>
     *
     * @param  array $aTypes  Component types
     * @return AMI_Mod
     * @since  5.12.4
     */
    public function removeComponents(array $aTypes){
        if($this->bUseComponents){
            if(in_array('*', $aTypes)){
                $this->aComponentsOptions = array();
                $this->aComponents = array();
            }
            $aComponentsList = $this->aComponentsOptions;
            foreach($aComponentsList as $componentId => $aOptions){
                if(
                    isset($aOptions['type']) && in_array($aOptions['type'], $aTypes) &&
                    isset($this->aComponents[$componentId])
                ){
                    unset($this->aComponentsOptions[$componentId]);
                    unset($this->aComponents[$componentId]);
                }
            }
        }
        return $this;
    }

    /**
     * Returns compoent ids according to its types / full environment flag.
     *
     * @return array
     * @amidev Temporary
     */
    public function getComponentList(){
        $aList = array();
        foreach(array_keys($this->aComponents) as $serialId){
            $aList[$serialId] = array(
                'type'    => $this->aComponents[$serialId]->getType(),
                'fullEnv' => $this->aComponents[$serialId]->isFullEnv(),
                'related' => $this->getRelatedComponents($serialId)
            );
            $aOptions = $this->aComponentsOptions[$serialId];
            if(isset($aOptions['primary'])){
                $aList[$serialId]['primary'] = $aOptions['primary'];
            }
        }
        return $aList;
    }

    /**
     * Returns client locale path.
     *
     * Used to override default admin JavaScript controller messages.<br />
     * See example locale "client.lng".<br /><br />
     *
     * Example:
     * <code>
     * // AmiSample_Adm::getClientLocalePath()
     * public function getClientLocalePath(){
     *     return '_local/plugins_distr/' . $this->getModId() .  '/templates/client.lng';
     * }
     * </code>
     *
     * @return string
     * @example client.lng
     */
    public function getClientLocalePath(){
        return AMI_iTemplate::LOCAL_LNG_MOD_PATH . '/' . $this->getModId() . '_client.lng';
    }

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     * @amidev Temporary
     */

    /**
     * Dispatches module action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @todo   Source module id support?
     */
    public function dispatchRequest($name, array $aEvent, $handlerModId, $srcModId){
        // For partial async mode
        $this->checkPartialAsyncId();
        $this->dispatch(AMI::getSingleton('env/request')->get('mod_action', 'none'));
        return $aEvent;
    }

    /**
     * Handle event specifying to disable module components initialization.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleDisableInit($name, array $aEvent, $handlerModId, $srcModId){
        $this->bUseComponents = FALSE;
        return $aEvent;
    }

    /**#@-*/

    /**
     * Returns module component views.
     *
     * @return array  Array of AMI_View objects
     * @amidev Temporary
     */
    public function getViews(){
        $aViews = array();
        foreach(array_keys($this->aComponents) as $index){
            $aViews[] = $this->aComponents[$index]->getView();
        }
        return $aViews;
    }

    /**
     * Returns module id.
     *
     * @return string
     */
    public function getModId(){
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
     * Returns module state object.
     *
     * @return AMI_ModState
     */
    public function getModState(){
        return AMI::getResourceModel($this->getModId() . '/module', array(), FALSE);
    }

    /**
     * Returns request parameter.
     *
     * @param  string $key     Key
     * @param  mixed $default  Default value
     * @return mixed
     * @amidev Temporary
     */
    public function getRequestParam($key, $default = null){
        trigger_error("Method AMI_Mod::getRequestParam is deprecated since 5.12.8, use AMI::getSingleton('env/request') object instead.", E_USER_WARNING);
        return $this->oRequest->get($key, $default);
    }

    /**
     * Returns request scope.
     *
     * @return array
     * @amidev Temporary
     */
    public function getRequestScope(){
        trigger_error("Method AMI_Mod::getRequestScope is deprecated since 5.12.8, use AMI::getSingleton('env/request') object instead.", E_USER_WARNING);
        return $this->oRequest->getScope();
    }

    /**
     * Prepares event data.
     *
     * @return array  Module event array
     * @amidev Temporary
     */
    protected function prepareEvent(){
        return array(
            'modId'        => $this->getModId(),
            'oController'  => $this,
            'tableModelId' => $this->getModId() . '/table',
            'oRequest'     => $this->oRequest,
            'oResponse'    => $this->oResponse
        );
    }

    /**
     * Dispathes action.
     *
     * @param  string $action  Action
     * @return AMI_Mod
     * @todo   Fire/rigister specifying target module name (containing serial id)
     * @amidev Temporary
     */
    public function dispatch($action){
        $aEvent = $this->prepareEvent();
        $aEvent['action'] = &$action; // todo specify reference usage???
        /**
         * Fires to dispatch module controller action.
         *
         * @event      dispatch_mod_action $modId
         * @eventparam string       modId         Module id
         * @eventparam AMI_Mod|null oController   Module controller object
         * @eventparam string       tableModelId  Table model resource id
         * @eventparam AMI_Request  oRequest      Request object
         * @eventparam AMI_Response oResponse     Response object
         * @eventparam string       action        Action
         */
        AMI_Event::fire('dispatch_mod_action', $aEvent, $this->getModId());
        /**
         * Fires to dispatch module controller action.
         *
         * @event      dispatch_mod_action $modId
         * @eventparam string       modId         Module id
         * @eventparam AMI_Mod|null oController   Module controller object
         * @eventparam string       tableModelId  Table model resource id
         * @eventparam AMI_Request  oRequest      Request object
         * @eventparam AMI_Response oResponse     Response object
         * @eventparam string       action        Action
         */
        AMI_Event::fire('dispatch_mod_action_' . $action, $aEvent, $this->getModId());
        return $this;
    }

    /**
     * Returns list of components that are related to component with specified serial id.
     *
     * @param string $serialId  Component serial Id
     * @return array
     * @amidev Temporary
     */
    protected function getRelatedComponents($serialId){
        $aResult = array();
        if(isset($this->aComponentsOptions[$serialId]) && isset($this->aComponentsOptions[$serialId]['related_to'])){
            $related = $this->aComponentsOptions[$serialId]['related_to'];
            if(is_array($related)){
                // ids case
                $aResult = $related;
            }else{
                // type case
                foreach($this->aComponentsOptions as $componentId => $aOptions){
                    if(isset($aOptions['type']) && ($aOptions['type'] == $related) && ($componentId != $serialId)){
                        $aResult[] = $componentId;
                    }
                }
                $aResult = array_unique($aResult);
            }
        }
        return $aResult;
    }

    /**
     * Returns request object.
     *
     * @return AMI_Request
     * @amidev Temporary
     */
    protected function getRequest(){
        trigger_error("Method AMI_Mod::getRequest is deprecated since 5.12.8, use AMI::getSingleton('env/request') instead.", E_USER_WARNING);
        return $this->oRequest;
    }

    /**
     * Returns response object.
     *
     * @return AMI_Response
     * @amidev Temporary
     */
    protected function getResponse(){
        trigger_error("Method AMI_Mod::getResponse is deprecated since 5.12.8, use AMI::getSingleton('response') instead.", E_USER_WARNING);
        return $this->oResponse;
    }

    /**
     * Returns state source module id.
     *
     * @return string
     * @amidev Temporary
     */
    protected function getStateSourceModId(){
        return $this->getModId();
    }

    /**
     * Disables initializing and loading of extension with specified id.
     *
     * @param string $extId  Extension id
     * @return void
     * @amidev Temporary
     */
    protected function disableExtension($extId){
        $this->aExtResourceMapping[$extId] = '';
    }

    /**
     * Requests page reload if current element id is affected by action.
     *
     * @return void
     * @amidev Temporary
     */
    protected function checkPartialAsyncId(){
        $oRequest = AMI::getSingleton('env/request');
        $oResponse = AMI::getSingleton('response');

        // Is for partial async mode only
        if($oRequest->get('partial_async', false)){
            $actionId = $oRequest->get('mod_action_id', '');
            $activeId = $oRequest->get('active_id', false);
            $aIds = explode(',', $actionId);
            if($activeId && in_array($activeId, $aIds)){
                $oResponse->setPageReload();
            }
        }
    }
}

/**
 * Module model.
 *
 * Used to flexible overriding module id/option source module id by children modules.<br />
 * Will be described later. To create your own filter component action controller you should create the child for this class.<br /><br />
 *
 * Example:
 * <code>
 * // AmiSample_Adm.php
 * class AmiSample_State extends AMI_ModState{
 * }
 * </code>
 *
 * @package    Module
 * @subpackage Model
 * @since      5.12.0
 * @todo       Describe usage
 */
abstract class AMI_ModState{
    /**
     * Module id
     *
     * @var string
     */
    private $modId = '';

    /**
     * Returns module property existance.
     *
     * @param  string $name  Module property name
     * @return bool
     * @amidev Temporary?
     */
    public function issetProperty($name){
        return AMI::issetProperty($this->getStateSourceModId(), $name);
    }

    /**
     * Returns module property value.
     *
     * @param  string $name  Module property name
     * @return mixed
     * @todo   specify necessity
     * @amidev Temporary?
     */
    public function getProperty($name){
        return AMI::getProperty($this->getStateSourceModId(), $name);
    }

    /**
     * Returns module option value.
     *
     * @param  string $name  Module option name
     * @return mixed
     */
    public function getOption($name){
        return AMI::getOption($this->getStateSourceModId(), $name);
    }

    /**
     * Returns module option presence.
     *
     * @param  string $name  Module option name
     * @return bool
     */
    public function issetOption($name){
        return AMI::issetOption($this->getStateSourceModId(), $name);
    }

    /**
     * Returns true if module option is set and is true.
     *
     * @param  string $name  Module option name
     * @return bool
     */
    public function issetAndTrueOption($name){
        return AMI::issetAndTrueOption($this->getStateSourceModId(), $name);
    }

    /**
     * Returns module id.
     *
     * @return string
     */
    public function getModId(){
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
     * @amidev Temporary
     */
    public function setModId($modId){
        $this->modId = $modId;
    }

    /**
     * Returns state source module id.
     *
     * Will be describe later.
     *
     * @return string
     * @todo   Describe
     */
    protected function getStateSourceModId(){
        return $this->getModId();
    }
}
