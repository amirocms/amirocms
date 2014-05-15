<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module
 * @version   $Id: AMI_Module_Frn.php 44504 2013-11-27 10:21:28Z Leontiev Anton $
 * @since     5.14.8
 */

/**
 * Front module action controller.
 *
 * @package    Module
 * @subpackage Controller
 * @since      5.14.8
 */
abstract class AMI_Module_Frn extends AMI_Mod{
    /**
     * Flag specifies that page should be indexed
     *
     * @var bool
     * @amidev Temporary
     */
    protected $isPageIndexing = true;

    /**
     * Flag specifies that page must be indexed
     *
     * @var bool
     * @amidev Temporary
     */
    protected $isPageForceIndexing = false;

    /**
     * We cannot rise data using custom vars from _ActionFillFrontData extension action
     *
     * @var int
     * @amidev Temporary
     */
    protected $forcePageSize = 0;

    /**
     * Force body type
     *
     * @var string
     */
    protected $forceBodyType = '';

    /**
     * Default module component
     *
     * @var string
     */
    protected $defaultBodyType = 'items';

    /**
     * Array of installed extensions template flags
     *
     * @var    array
     * @amidev Temporary?
     */
    protected $aTplExtFlags = array();

    /**
     * Path to status message locales
     *
     * @var string
     */
    // protected $statusMessagePath = '';

    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request object
     * @param AMI_Response $oResponse  Response object
     * @todo  Add bodytype to registry to be used in extensions???
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        AMI_Registry::set('ami_skip_50_module', TRUE);
        $oDeclarator = AMI_ModDeclarator::getInstance();
        $modId = $this->getModId();

        if(!in_array('ajax', AMI::getProperty($this->getModId(), 'front_request_types'))){
            /**
             * @var AMI_Request
             */
            $oRequest = AMI::getSingleton('env/request');
            $catModId = $modId . '_cat';
            $useCats = AMI::isModInstalled($catModId) && AMI::issetAndTrueOption($modId, 'use_categories');

            if(AMI::issetAndTrueOption($modId, 'show_body_browse') && ($oRequest->get('mode', null) == 'browse')){
                AMI_Registry::set('AMI/Module/Environment/Filter/active', false);
                $oTable = AMI::getResourceModel($this->getModId().'/table');
                $catSnippet = '';
                if($useCats){
                    $oTable->setActiveDependence('cat');
                    $catSnippet = ' AND i.id_cat = '.(int)AMI_Registry::get('page/catId', 0);
                }
                $oList = $oTable->getList()->addColumn('id')->addOrder(AMI::getOption($modId, 'front_page_sort_col'), AMI::getOption($modId, 'front_page_sort_dim'))->setLimitParameters($oRequest->get('offset', 0), 1);
                if($catSnippet){
                    $oList->addWhereDef(DB_Query::getSnippet($catSnippet));
                }
                $oList->load();
                if($oList->count() > 0){
                    foreach($oList as $oItem){
                        $aItem = $oItem->getData();
                        AMI_Registry::set('page/itemId', $aItem['id']);
                        break;
                    }
                }
            }

            $type = $this->defaultBodyType; // Default body type (items)
            $itemId = (int)AMI_Registry::get('page/itemId', 0);
            if($this->isSpecblock()){
                $type = 'specblock';
            }elseif($itemId < 0){
                $this->forcePageIndexing(FALSE);
                // $type = '404';   // body type: empty
                $type = 'details';
            }elseif($itemId){
                $type = 'details'; // body type: itemD
            }

            if($useCats){
                // Initialize category submodule extensions
                AMI::initModExtensions($catModId);
            }

            $aComponents = array(
                $type => array(
                    'type'    => $type,
                    'options' => AMI_Mod::INIT_ON_START
                )
            );
            $this->addComponents($aComponents);

            $aModEnvData = array(
                'modId'     => $this->getModId(),
                'bodyType'  => $type,
                'Filter'    => array('active' => $oRequest->get('body_filtered', false) ? true : false),
                'Extension' => array(
                    'ext_category' => array(
                        'enableCatFilter' => AMI_Registry::get('AMI/Module/Environment/Extension/ext_category/enableCatFilter', FALSE)
                    )
                )
            );
            AMI_Registry::set('AMI/Module/Environment', $aModEnvData);

            list($hyper, $config) = $oDeclarator->getHyperData($modId);
            $oTpl = AMI::getResource('env/template_sys');
            $oTpl->addGlobalVars(
                array(
                    'AMI_HYPER_ID' => $hyper,
                    'AMI_CONF_ID'  => $config,
                    'AMI_MOD_ID'   => $modId
                )
            );
            $oCommonView = AMI::getResource('module/common/view/frn');
            $oCommonView->setModId($modId);
            AMI_Event::addHandler(
                'on_after_init_componets',
                array($oCommonView, 'handleAfterInitComponents'),
                $modId,
                AMI_Event::PRIORITY_LOW
            );
        }

        parent::__construct($oRequest, $oResponse);

        // #CMS-11471 {

        $key = 'AMI/Environment/Model/IstalledExtensions/' . $modId;
        if(AMI_Registry::exists($key)){
            $aCurrentExt =
                array_values(
                    array_filter(
                        array_keys(AMI_Registry::get($key)),
                        array('AMI', 'cbFilterExtViews')
                    )
                );
            $this->aTplExtFlags = array();
            foreach($aCurrentExt as $extModId){
                list(, $config) = $oDeclarator->getHyperData($extModId);
                $flag = 'ext_' . $config . '_enabled';
                $this->aTplExtFlags[$flag] =
                    AMI_Registry::get($key . '/' . $extModId)
                        ->isInstalled();
            }
            if(sizeof($this->aTplExtFlags)){
                // Add flags to template global scope
                $oTpl = AMI::getSingleton('env/template_sys');
                $oTpl->addGlobalVars($this->aTplExtFlags);
            }
        }

        // } #CMS-11471
    }

    /**
     * Destructor.
     *
     * @since  6.0.2
     */
    public function __destruct(){
        // #CMS-11471 {

        if(sizeof($this->aTplExtFlags)){
            // Delete flags from template global scope
            $oTpl = AMI::getSingleton('env/template_sys');
            $oTpl->deleteGlobalVars(array_keys($this->aTplExtFlags));
        }

        // } #CMS-11471

        parent::__destruct();
    }

    /**
     * Initializes module.
     *
     * @return AMI_Module_Frn
     */
    public function init(){
        parent::init();
        $action = AMI::getSingleton('env/request')->get('action', 'none');
        if($action !== 'none'){
            $aEvent = array('action' => &$action);
            AMI_Event::fire('dispatch_action', $aEvent, $this->getModId());
            if($action !== 'none'){
                AMI_Event::fire('dispatch_action_' . $action, $aEvent, $this->getModId());
            }
        }
        return $this;
    }

    /**
     * Returns client locale path.
     *
     * @return string
     */
    public function getClientLocalePath(){
        return AMI_iTemplate::LNG_MOD_PATH . '/_client.lng';
    }

    /**
     * Returns specblock content.
     *
     * @param  string $postfix  Name postfix
     * @return string
     */
    public function getSpecblock($postfix = ''){
        $res = '';
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
        return $res;
    }

    /**
     * Returns specblock component.
     *
     * @param  string $postfix  Name postfix
     * @return AMI_ModComponent|null
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
        return AMI_Registry::get('ami_specblock_mode', false);
    }

    /**
     * Sets force page size.
     *
     * SEO hack for forum and ext_discussion.
     *
     * @param  int $size  Page size
     * @return void
     */
    public function setForcePageSize($size){
        $this->forcePageSize = (int)$size;
    }

    /**
     * SEO processing.
     *
     * @return AMI_Module_Frn
     * @amidev Temporary
     */
    public function processSEO(){
        $modId = $this->getModId();

        $aEvent = array(
            'modId'        => $modId,
            'oController'  => $this
        );
        /**
         * Called before SEO processing.
         *
         * @event      on_before_seo_process $modId
         * @eventparam string modId  Module Id
         * @eventparam AMI_Mod oController  Module controller object
         */
        AMI_Event::fire('on_before_seo_process', $aEvent, $modId);

        $oRequest = AMI::getSingleton('env/request');

        foreach(array('catoffset', 'offset', 'catid') as $varName){
            $value = $oRequest->get($varName, null);
            if(isset($value)){
                $this->checkIntParam($value);
            }
        }

        $forcePageSize = $this->forcePageSize;
        $this->forcePageSize = 0;
        $oTpl = AMI::getSingleton('env/template_sys');

        $this->isPageIndexing = $this->isPageIndexing && !in_array($modId, AMI::getProperty('core', 'disabled_se_indexing_mods'));

        if(!$this->isPageForceIndexing && $this->isPageIndexing && AMI::issetOption($modId, 'disable_se_indexing_pages')){
            $aDisableIndexingPages = AMI::getOption($modId, 'disable_se_indexing_pages');
            $itemDKey = array_search('body_itemD', $aDisableIndexingPages);
            if($itemDKey !== false){
                $aDisableIndexingPages[$itemDKey] = 'body_details';
            }

            if($oRequest->get('offset', null)){
                // internal list pages
                // force_page_size is forum hack for thread page size, $this->moduleName != 'forum' right but slow
                if($oRequest->get('action', null) == 'rsrtme' && empty($forcePageSize)){
                    $this->isPageIndexing = !in_array('body_items_internal', $aDisableIndexingPages);
                }elseif($oRequest->get('action', null) == 'search'){
                    $this->isPageIndexing = !in_array('body_filtered_internal', $aDisableIndexingPages);
                }
            }

            $this->isPageIndexing = $this->isPageIndexing && (!$oTpl->getGlobalVar('PRINT_VERSION') ? true : !in_array('print_version', $aDisableIndexingPages));

            if($this->isPageIndexing){
                $bodyType = $this->forceBodyType == '' ? AMI_Registry::get('AMI/Module/Environment/bodyType') : $this->forceBodyType;
                foreach(array('items', 'browse', 'filtered', 'details', 'search') as $pageType){
                    if($bodyType == $pageType){
                        $this->isPageIndexing = !in_array('body_' . $pageType, $aDisableIndexingPages);
                        if(!$this->isPageIndexing){
                            break;
                        }
                    }
                }
                // force_page_size is forum hack for thread page size or ext_discussion page size
                if(
                    $this->isPageIndexing && $bodyType == 'details' &&
                    $oRequest->get('offset', 0) && empty($forcePageSize)
                ){
                    $this->isPageIndexing = false;
                }
            }
        }

        // detect URL duplication {
        if(!$this->isPageForceIndexing && $this->isPageIndexing && AMI::issetAndTrueProperty($modId, 'stop_use_sublinks')){
            $aNavData = array_keys(AMI_Registry::get('nav_data', array()));
            $aSourceGet = $oRequest->getSourceGet();
            foreach($aNavData as $getParameter){
                if(isset($aSourceGet[$getParameter])){
                    $this->disablePageIndexing();
                    break;
                }
            }
        }

        $aGETVars = array();
        if(!$this->isPageForceIndexing && $this->isPageIndexing){
            $aURI = parse_url(AMI::getSingleton('env/request')->getURL('uri'));
            if(isset($aURI['query'])){
                parse_str($aURI['query'], $aGETVars);
                mb_internal_encoding('UTF-8'); // PHP bug, fixed in 5.2.11
            }
            if(isset($aGETVars['catid']) && empty($aGETVars['action'])){
                $this->disablePageIndexing();
            }
        }
        if(!$this->isPageForceIndexing && $this->isPageIndexing){
            // check offsets
            $aRequest = $oRequest->getScope();
            unset($aRequest['id']);

            if($modId == 'forum'){
                unset($aRequest['id_message'], $aGETVars['id_message']);
            }
            // force_page_size is forum hack for thread page size
            $aOffsetArgs = array(
            	'offset' => empty($forcePageSize) ? AMI::getOption($modId, 'page_size') : $forcePageSize,
                'part' => 0
            );
            $isCatModule = !AMI::isCategoryModule($modId) && AMI::issetAndTrueOption($modId, "use_categories");
            if($isCatModule){ // TODO || isset($this->oEshop)){
                // $aOffsetArgs['catoffset'] = $isCatModule ? AMI::getOption($modId . '_cat', 'page_size') : $this->oEshop->mEshopCat->GetOption('page_size');

                $aOffsetArgs['catoffset'] = AMI::getOption($modId . '_cat', 'page_size');
                unset($aRequest['catid'], $aGETVars['catid']);
            }
            foreach($aOffsetArgs as $arg => $pageSize){
                if(isset($aRequest[$arg])){
                    $pageSize = (int)$pageSize;
                    if($pageSize && ($aRequest[$arg] % $pageSize !== 0)){
                        $this->disablePageIndexing();
                        break;
                    }
                    unset($aRequest[$arg], $aGETVars[$arg]);
                    if(isset($aRequest['action']) && $aRequest['action'] === 'rsrtme'){
                        unset($aRequest['action'], $aGETVars['action']);
                    }
                }
            }
            $key = 'AMI/Module/Environment/IstalledExtensions/' . $modId;
            if(
                isset($aRequest['forum_ext']) &&
                AMI_Registry::exists($key . '/ext_discussion') &&
                AMI_Registry::get($key . '/ext_discussion')->isInstalled()
            ){
                unset($aRequest['forum_ext'], $aRequest['offset'], $aGETVars['forum_ext'], $aGETVars['offset']);
            }

            if(isset($aRequest['_print_version'])){
                unset($aRequest['_print_version'], $aGETVars['_print_version']);
            }

            // pm#5362 {
            $g = $aRequest;
            foreach(array_keys($g) as $k){
                if(mb_substr($k, 0, 4) === '404;'){
                    unset($aRequest[$k]);
                }
            }
            // } pm#5362

            // pm#5408 {
            if(is_object($GLOBALS['oCache']) && $GLOBALS['oCache']->Enabled){
                $GLOBALS['oCache']->stripGet($aRequest);
                $GLOBALS['oCache']->stripGet($aGETVars);
            }
            // } pm#5408

            if(sizeof($aRequest) || sizeof($aGETVars)){
                $this->disablePageIndexing();
            }
        }
        // } detect URL duplication

        if(!$this->isPageIndexing){
            AMI_registry::get('oGUI')->setRobotsMeta(false);
        }

        return $this;
    }

    /**
     * SEO processing: check integer page params.
     *
     * @param  string $value  Param value
     * @return AMI_Module_Frn
     * @amidev Temporary
     */
    protected function checkIntParam($value){
        if((int)$value != $value || $value < 0){
            // protect trash links
            $this->disablePageIndexing();
        }
        return $this;
    }

    /**
     * Used to disable search engine indexing by adding meta tag 'robots' with 'noindex,follow' value.
     *
     * @return AMI_Module_Frn
     */
    public final function disablePageIndexing(){
        if(!$this->isPageForceIndexing){
            $this->isPageIndexing = false;
        }
        return $this;
    }

    /**
     * Used to force enable search engine indexing for page.
     *
     * @param  bool $index  Indexing flag
     * @return AMI_Module_Frn
     */
    public final function forcePageIndexing($index = TRUE){
        $this->isPageIndexing = (bool)$index;
        $this->isPageForceIndexing = (bool)$index;
        return $this;
    }

    /**
     * Sets force body type for search engine indexing options checking.
     *
     * @param  string $bodyType  Page body type
     * @return AMI_Module_Frn
     */
    protected final function forceBodyType($bodyType){
        $this->forceBodyType = $bodyType;
        return $this;
    }

    /**
     * Sets a module default body type.
     *
     * @param string $bodyType  Default bodytype
     * @return void
     */
    protected function setDefaultBodyType($bodyType){
        $this->defaultBodyType = $bodyType;
    }
}
