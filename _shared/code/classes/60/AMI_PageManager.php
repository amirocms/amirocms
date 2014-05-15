<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Service
 * @version   $Id: AMI_PageManager.php 49853 2014-04-15 13:12:18Z Leontiev Anton $
 * @since     5.10.0
 */

/**
 * Site Manager pages service class.
 *
 * @package  Service
 * @since    5.10.0
 */
class AMI_PageManager{

    /**
     * Module pages array
     *
     * @var array
     * @amidev
     */
    private static $aPages = array();

    /**
     * Flag specifying the navigation is initialized
     *
     * @var bool
     * @amidev
     */
    private static $isNavInitialized = false;

    /**
     * Current navigation module
     *
     * @var string
     * @amidev
     */
    private static $curNavModId = null;

    /**
     * Navigation data
     *
     * @var bool
     * @amidev
     */
    private static $navStopMode = false;

    /**
     * Navigation data
     *
     * @var array
     * @amidev
     */
    private static $aNavModNames = array();

    /**
     * Navigation data
     *
     * @var bool
     * @amidev
     */
    private static $isNavStopLinkAllowed = false;

    /**
     * Item data
     *
     * @var bool
     * @amidev
     */
    private static $isItemDataLoaded = false;

    // resource env/page_mgr <code>AMI::getSingleton('env/page_mgr')</code>
    /**
     * @var AMI_PageManager
     */
     // private static $oInstance;

    /**
     * Returns an instance of an AMI_PageManager.
     *
     * @return AMI_PageManager
     * @amidev
     */
/*
    public static function getInstance(){
        if(is_null(self::$oInstance)){
            self::$oInstance = new AMI_PageManager();
        }
        return self::$oInstance;
    }
*/

    /**
     * Returns module link by its id and page id.
     *
     * @param  string $modId          Module id
     * @param  string $locale         Locale
     * @param  int    $pageId         Page id (used for multipage modules)
     * @param  bool   $suppressError  Flag specifying to suppress error if there is no page in page manager (since 5.14.4)
     * @param  bool   $prependLocale  Flag specifying to prepend locale part for multilingual mode (since 5.14.8)
     * @return string|false
     * @see    AMI_ModTableItem::getFrontLink()
     */
    public static function getModLink($modId, $locale = 'en', $pageId = 0, $suppressError = FALSE, $prependLocale = FALSE){
        static $aLinks = array();

        $localePrefix = $prependLocale && AMI::getOption('core', 'allow_multi_lang') ? $locale . '/' : '';

        if(preg_match('~^(eshop|kb|portfolio)_cat$~', $modId, $aMatches)){
            $modId = $aMatches[1] . '_item';
        }
        if(!isset($aLinks[$locale][$modId][$pageId])){
            $oQuery = new DB_Query('cms_pages');
            $oQuery->addField('script_link');
            if($pageId){
                $oQuery->addWhereDef($oQuery->getSnippet('AND `id` = %d')->plain((int)$pageId));
            }
            $oQuery->addWhereDef(
                $oQuery->getSnippet('AND `module_name` = %s AND `lang` = %s')
                ->q($modId)
                ->q($locale)
            );
            if(!$pageId){
                $oQuery
                    ->addOrder('id', 'ASC')
                    ->setLimitParameters(0, 1);
            }

            if(!isset($aLinks[$locale])){
                $aLinks[$locale] = array();
            }
            if(!isset($aLinks[$locale][$modId])){
                $aLinks[$locale][$modId] = array();
            }
            $aLinks[$locale][$modId][$pageId] = AMI::getSingleton('db')->fetchValue($oQuery);

            if($aLinks[$locale][$modId][$pageId] === false){
                if(
                    AMI::issetOption('pages', 'used_virtual_modules') &&
                    in_array($modId, AMI::getOption('pages', 'used_virtual_modules'))
                ){
                    $aVirtualLinks = AMI::getProperty('pages', 'virtual_links');
                    foreach($aVirtualLinks as $virtualLinkModId => $aLinks){
                        if(isset($aLinks[$modId])){
                            $aLinks[$locale][$modId][$pageId] =
                                self::getModLink($virtualLinkModId, $locale, 0, $suppressError) .
                                '/' . $aLinks[$modId];
                            break;
                        }
                    }
                }
            }
            /*
            $aEvent = array(
            	'locale' => $locale,
            	'pageId' => $pageId,
            	'link'	=> $aLinks[$locale][$modId][$pageId]
            );
            AMI_Event::fire('on_after_get_mod_link', $aEvent, $modId);
            if(isset($aEvent['link'])){
            	$aLinks[$locale][$modId][$pageId] = $aEvent['link'];
            }
            */
            if($aLinks[$locale][$modId][$pageId] === false && !$suppressError){
                trigger_error(
                    "No Site Manager page found for '" . $modId . "/" . $locale . "/" . $pageId . "'",
                    E_USER_ERROR
                );
            }
        }

        return $aLinks[$locale][$modId][$pageId] !== FALSE ? $localePrefix . $aLinks[$locale][$modId][$pageId] : FALSE;
    }

    /**
     * Returns array of pages whith module.
     *
     * @param  string $modId   Module id
     * @param  string $locale  Locale
     * @return array
     * @amidev
     */
    public static function getModPages($modId, $locale = 'en'){
        self::convertModId($modId);
        if(!isset(self::$aPages[$locale][$modId])){
            $oQuery = new DB_Query('cms_pages');
            $oQuery->addField('id');
            $oQuery->addField('name');
            $oQuery->addField('public');
            $oQuery->addWhereDef(
                $oQuery->getSnippet('AND `module_name` = %s AND `lang` = %s')
                ->q($modId)
                ->q($locale)
            );
            $oQuery->addOrder('id', 'ASC');
            $oRecordset = AMI::getSingleton('db')->select($oQuery);
            self::$aPages[$locale][$modId] = array();
            if(count($oRecordset)){
                foreach($oRecordset as $aRecord){
                    self::$aPages[$locale][$modId][] = $aRecord;
                }
            }
        }
        return self::$aPages[$locale][$modId];
    }

    /**
     * Check has module public pages or not.
     *
     * @param  string $modId   Module id
     * @param  string $locale  Locale
     * @return bool
     * @since  5.14.0
     * @amidev
     */
    public static function hasModPublicPage($modId, $locale = 'en'){
        self::convertModId($modId);
    	$aPages = self::getModPages($modId, $locale);
    	foreach($aPages as $aPage){
            if(!empty($aPage['public'])){
                return true;
            }
    	}
    	return false;
    }

    /**
     * Get page name by id.
     *
     * @param  int $pageId     Page id
     * @param  string $modId   Module id
     * @param  string $locale  Locale
     * @return string
     * @amidev
     */
    public static function getModPageName($pageId, $modId, $locale = 'en'){
        self::convertModId($modId);
        self::getModPages($modId, $locale);
        foreach(self::$aPages[$locale][$modId] as $aPage){
            if($aPage['id'] == $pageId){
                return $aPage['name'];
            }
        }
        return '';
    }

    /**
     * Returns module link by its id and page id.
     *
     * @param  string $modId   Module id
     * @param  string $locale  Locale
     * @param  int $pageId     Page id (used for multipage modules)
     * @return mixed  string | false
     */
/*
    public function getPageIdByLink($link, $locale = 'en'){
        /**
         * @var AMI_iDB
         * /
        $oDB = AMI::getSingleton('db');
        $sql =
            "SELECT `id` " .
            "FROM `cms_pages` " .
            "WHERE " .
                "`script_link` = " . $oDB->quote($link) . " AND " .
                "`lang` = " . $oDB->quote($locale);
        return $oDB->fetchValue($sql);
    }
*/

    /**
     * Get current page item and category headers.
     *
     * @return array  Array having possible keys 'itemHeader', 'catHeader'
     * @since  5.14.8
     */
    public static function getPageItemData(){
        $aPageData = AMI_Registry::get('page');
        $aItemData = array();
        if(!self::$isItemDataLoaded){
            if(
                $aPageData['modId'] &&
                AMI_ModDeclarator::getInstance()->isRegistered($aPageData['modId'])
            ){
                $oModel = AMI::getResourceModel($aPageData['modId'] . '/table');
                if($aPageData['itemId'] > 0){
                    $aFields = array('id');
                    if($oModel->hasField('header')){
                        $aFields[] = 'header';
                    }
                    if($aPageData['catId'] > 0 && $oModel->getDependenceResId('cat')){
                        $oModel->setActiveDependence('cat');
                        $aFields['cat'] = array('header', 'announce', 'public');
                    }
                    $oItem = $oModel->find($aPageData['itemId'], $aFields);
                    $aPageData['itemHeader'] = $oItem->header;
                    if(isset($aFields['cat'])){
                        $aPageData['catPublic'] = $oItem->cat_public;
                        $aPageData['catHeader'] = $oItem->cat_header;
                        $aPageData['catAnnounce'] = $oItem->cat_announce;
                    }
                }elseif($aPageData['catId'] > 0 && $oModel->getDependenceResId('cat')){
                    $oCatModel = AMI::getResourceModel($oModel->getDependenceResId('cat') . '/table');
                    $oCatItem = $oCatModel->find($aPageData['catId'], array('id', 'header', 'announce', 'public'));
                    $aPageData['catPublic'] = $oCatItem->public;
                    $aPageData['catHeader'] = $oCatItem->header;
                    $aPageData['catAnnounce'] = $oCatItem->announce;
                }
            }
            AMI_Registry::set('page', $aPageData);
            self::$isItemDataLoaded = true;
        }
        if(isset($aPageData['itemHeader'])){
            $aItemData['header'] = $aPageData['itemHeader'];
        }
        foreach(array('catPublic', 'catHeader', 'catAnnounce') as $k){
            if(isset($aPageData[$k])){
                $aItemData[$k] = $aPageData[$k];
            }
        }
        return $aItemData;
    }

    /**
     * Get page meta data.
     *
     * @param  array $aMetaData  Current array of page meta data
     * @return array  Array having page meta data
     * @since 5.14.8
     * @amidev Temporary
     */
    public function getPageMetaData(array $aMetaData){
        $res = array();

        if(!empty($aMetaData['aMeta']['filled'])){
            $res = $aMetaData['aOrigData']['html_meta'];
        }

        $res += $GLOBALS['ModuleHtml']['headers'];

        return $res;
    }

    /**
     * Initialize navigation settings.
     *
     * @param  string $modId  Module id
     * @return void
     * @amidev Temporary
     */
    public static function initNavSettings($modId = ''){
        // Moved from CMS_Base::InitNavSettings() method.

        self::$isNavStopLinkAllowed = AMI::getOption('core', 'stop_link_allowed');

        self::$navStopMode = AMI::issetAndTrueProperty($modId, 'stop_use_sublinks') && self::$isNavStopLinkAllowed;
        self::$aNavModNames = AMI::issetOption($modId, 'stop_arg_names') ? array_keys(AMI::getOption($modId, 'stop_arg_names')) : array();

        self::$isNavInitialized = true;
    }

    /**
     * Apply navigation settings.
     *
     * @param  array &$aNavData  Navigation data
     * @return array
     * @amidev Temporary
     */
    public static function applyNavData(array &$aNavData){
        // Moved from CMS_Base::ApplyNav() method.
        if(!empty($aNavData['modId']) && $aNavData['modId'] != self::$curNavModId){
            self::initNavSettings($aNavData['modId']);
            self::$curNavModId = $aNavData['modId'];
        }

        if(self::$navStopMode){
            $aNav = array();
            $sNav = "";
            $rNames = array_reverse(self::$aNavModNames);
            $found = false;
            foreach($rNames as $vName){
                $vVal = "";
                if(isset($aNavData[$vName])){
                    $vVal = $aNavData[$vName];
                    if(isset($aNavData[$vName."_sublink"]) && $aNavData[$vName."_sublink"] !== ""){
                        $vVal = $aNavData[$vName."_sublink"];
                    }
                    $sNav = $vVal . ($found ? "/" : "") . $sNav;
                    $found = true;
                }else{
                    $sNav = ($found ? "-/" : "") .$sNav;
                }
            }
            if($found){
                $aNav["nav_data"] = "/" . $sNav . "?";
            }else{
                $aNav["nav_data"] = "?";
            }
        }else{
            $aNav = array("nav_data" => "?");
            $sNav = "";
            foreach(self::$aNavModNames as $vName){
                $sNav .= $vName . "=" . $aNavData[$vName] . "&";
            }
            $aNav["nav_data"] = $aNav["nav_data"] . $sNav;
        }

        return $aNav + $aNavData;
    }

    /**
     * Searchs page in Page Manager by link.
     *
     * @param  string $link     Link
     * @param  bool   $asModel  Flag specifying to return result as table item model
     * @return AMI::getResourceModel('pages/table/item')|array
     * @since  6.0.2
     */
    public static function searchByLink($link, $asModel = TRUE){
        // Extract sublink variants
        $link = array_shift(explode('?', $link, 2));
        $aTmp = explode('/', $link);
        $aSublinks = array();
        $sublink = '';
        foreach($aTmp as $lnk){
            if(mb_strlen($lnk)){
                $sublink .= $lnk;
                $aSublinks[] = $sublink;
                // Some sublinks have '/' in tail
                $sublink .= '/';
                $aSublinks[] = $sublink;
            }
        }
        if($asModel){
            $oTable = new Pages_Table();
            $oItem = $oTable->getItem();
        }else{
            $aItem = array();
        }
        if(sizeof($aSublinks)){
            // Get page data with the most longer sublink
            $oQuery =
                DB_Query::getSnippet(
                    "SELECT * " .
                    "FROM `cms_pages` " .
                    "WHERE `public` = 1 AND `script_link` IN (%s) " .
                    "ORDER BY LENGTH(`script_link`) DESC " .
                    "LIMIT 1"
                )
                ->implode($aSublinks);
            $oDB = AMI::getSingleton('db');
            $aRow = $oDB->fetchRow($oQuery);
            if($aRow){
                if($asModel){
                    $oItem->setData($aRow, FALSE, TRUE);
                }else{
                    $aItem = $aRow;
                }
            }
        }
        return $asModel ? $oItem : $aItem;
    }

    /**
     * Creates a new page in root.
     *
     * @param string $modId  Module id
     * @param string $name   Page name (optional, default: module caption)
     * @param string $link   Page link (optional, default: module id)
     * @param int $layoutId  Layout id (optional)
     * @return bool
     * @amidev
     */
    public static function createPage($modId, $name='', $link='', $layoutId = false){
        $oDB = AMI::getSingleton('db');
        $body = "##spec_module_body##";
        $lang = AMI_Registry::get('lang_data', 'en');

        if(empty($name)){
            $aCaptions = AMI_Service_Adm::getModulesCaptions(array($modId));
            $name = $aCaptions[$modId];
        }

        if(empty($link)){
            $link = $modId;
        }

        if(!$layoutId){
            $sql = "SELECT * FROM cms_layouts WHERE lang=%s AND hidden=0 AND is_default=1";
            $aLayout = $oDB->fetchRow(DB_Query::getSnippet($sql)->q($lang));
            if(!$aLayout){
                trigger_error("No default layout id found for language '" . $lang . "'", E_USER_WARNING);
                return FALSE;
            }
        }else{
            $sql = "SELECT * FROM cms_layouts WHERE id=%s AND lang=%s AND hidden=0";
            $aLayout = $oDB->fetchRow(DB_Query::getSnippet($sql)->q($layoutId)->q($lang));
            if(!$aLayout){
                trigger_error("Invalid layout id '" . $layoutId . "' found for language '" . $lang . "'", E_USER_WARNING);
                return FALSE;
            }
        }
        $layoutId = $aLayout['id'];

        // Layout options
        $aOptions = Array();
        $templateBlocksNumber = AMI::getProperty('pages', 'template_blocks_number');
        $sql = "SELECT sb_data FROM cms_pages WHERE lay_id=%s AND block_mask=%s LIMIT 1";
        $sbData = $oDB->fetchValue(DB_Query::getSnippet($sql)->q($layoutId)->q(0));
        if($sbData){
            $aPageOptions = unserialize($sbData);
            if(is_array($aPageOptions)){
                foreach($aPageOptions as $aPageOptionsKey => $aPageOptionsValue){
                    $specblock = mb_ereg_replace("(_[0-9]+)", "", $aPageOptionsKey);
                    $sBlockNum = mb_substr($aPageOptionsKey, mb_strlen($specblock) + 4, 3);
                    if(($aPageOptionsKey != "spec_module_body") && ($sBlockNum != "000")){
                        $aOptions[$aPageOptionsKey] = $aPageOptionsValue;
                    }
                }
            }
        }

        // Search root page id
        $sql = "SELECT id FROM cms_pages WHERE parent_id=0 AND lang=%s LIMIT 1";
        $pid = $oDB->fetchValue(DB_Query::getSnippet($sql)->q($lang));
        if(!$pid){
            trigger_error("No root page id found for language '" . $lang . "'", E_USER_WARNING);
            return FALSE;
        }

        // Link conflict detection
        $sql = "SELECT id FROM cms_pages WHERE script_link=%s LIMIT 1";
        $linkId = $oDB->fetchValue(DB_Query::getSnippet($sql)->q($link));
        if($linkId){
            // Make uniqe link
            $link .= ('_' . uniqid());
        }

        $blocksSQL = "";
        $aBlocksData = array();
        for($i = 1; $i <= $templateBlocksNumber; $i++){
            $aBlocksData["lay_f" . $i . "_body"] = $aLayout["lay_f" . $i . "_body"];
            $blocksSQL .= "lay_f" . $i . "_body=%s, ";
        }

        // Position
        $sql = "SELECT MAX(position) FROM cms_pages WHERE parent_id=%s LIMIT 1";
        $position = (int)$oDB->fetchValue(DB_Query::getSnippet($sql)->q($pid)) + 1;

        $idSite = 0;

        $sql = "INSERT INTO cms_pages SET " .
                "parent_id=%s, " .          // $pid
                "all_parents=%s, " .        // $pid
                "name=%s, " .               // $name
                "module_name = %s," .       // $modId
                "lay_id=%s, " .             // $layoutId
                "script_link=%s, " .        // $link
                "body=%s, " .               // $body
                "removable=1, " .
                "hidden=0, " .
                "fixed_name=0, " .
                "sb_data = %s, " .          // serialize($aOptions)
                "public=1, " .
                "last_changed=NOW(), " .
                "show_me_at_parent=0, " .
                "show_in_sitemap=0, " .
                "show_siblings=0, " .
                "is_printable=0, " .
                "skip_search=0, " .
                "html_title=%s, " .         // $name
                "html_title_inherit=0, " .
                "html_keywords=%s, " .      // ""
                "html_description=%s, " .   // ""
                "html_head_tail=%s, " .     // ""
                "is_section=0, " .
                "menu_main=0, " .
                "menu_bottom=0, " .
                "menu_top=0, " .
                "img_menu_normal=%s, " .    // ""
                "img_menu_over=%s, " .      // ""
                "img_menu_active=%s, " .    // ""
                "use_noindex=1, " .
                "page_noindex=1, " .
                "use_hreflang=0, " .
                "id_site=%s, " .            // $idSite
                "lang=%s, ";                // $lang

        $sql .= $blocksSQL;
        $sql .= "position=%s";              // $position

        $oSnippet =
            DB_Query::getSnippet($sql)
                ->q($pid)
                ->q($pid)
                ->q($name)
                ->q($modId)
                ->q($layoutId)
                ->q($link)
                ->q($body)
                ->q(serialize($aOptions))
                ->q($name)
                ->q("")
                ->q("")
                ->q("")
                ->q("")
                ->q("")
                ->q("")
                ->q($idSite)
                ->q($lang);

        foreach($aBlocksData as $blocksData){
            $oSnippet->q($blocksData);
        }

        $oSnippet->q($position);

        $oDB->query($oSnippet);

        return TRUE;
    }

    /**
     * Converts category module ids to base module ids.
     *
     * @param  string &$modId  Module id
     * @return void
     * @amidev
     */
    protected static function convertModId(&$modId){
        if(preg_match('~^(eshop|kb|portfolio)_cat$~', $modId, $aMatches)){
            $modId = $aMatches[1] . '_item';
        }elseif(preg_match('~^(.*?)_cat$~', $modId, $aMatches)){
            $modId = $aMatches[1];
        }
    }

    /**
     * Constructor.
     */
    private function __construct(){
    }

    /**
     * Cloning.
     */
    private function __clone(){
    }
}
