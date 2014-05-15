<?php
/**
 * AmiExt/RSS extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Config_AmiExt_RSS
 * @version   $Id: AmiExt_Rss_Frn.php 47117 2014-01-28 13:56:01Z Kolesnikov Artem $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/RSS extension configuration front controller.
 *
 * @package    Config_AmiExt_RSS
 * @subpackage Controller
 * @resource   ext_rss/module/controller/frn <code>AMI::getResource('ext_rss/module/controller/frn')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Rss_Frn extends Hyper_AmiExt{

    /**
     * Extension view
     *
     * @var AMI_ExtView
     */
    protected $oView;

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Extension pre-initialization.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handlePreInit($name, array $aEvent, $handlerModId, $srcModId){
        $modId = $aEvent['modId'];

        $oView = $this->getView('frn');
        if($oView){
            $oView->setExt($this);
            $this->oView = $oView;
            if(AMI::getSingleton('env/request')->get('action') == 'export_rss'){
                AMI_Service::hideDebug();
                $GLOBALS['CONNECT_OPTIONS']['disable_cache_warn'] = TRUE;
                AMI_Event::addHandler('on_before_view_items', array($oView, 'handleBeforeView'), $modId, AMI_Event::PRIORITY_HIGH);
                AMI_Event::addHandler('on_list_view', array($oView, 'handleListView'), $modId, AMI_Event::PRIORITY_HIGH);
                AMI_Event::addHandler('on_list_recordset', array($oView, 'handleListRecordset'), $modId, AMI_Event::PRIORITY_HIGH);
            }
            AMI_Event::addHandler('on_list_view', array($oView, 'handleRSSLinks'), $modId);
            AMI_Event::addHandler('on_before_view_small', array($oView, 'handleRSSLinksSmall'), $modId);
        }
        return $aEvent;
    }
    /**#@-*/
}

/**
 * AmiExt/RSS extension configuration front view.
 *
 * @package    Config_AmiExt_RSS
 * @subpackage View
 * @resource   ext_rss/view/frn <code>AMI::getResource('ext_rss/view/frn')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Rss_ViewFrn extends AMI_ExtView{
    /**
     * Template block name
     *
     * @var string
     */
    protected $tplBlockName = 'rss_ext';

    /**
     * Template simple fields prefix
     *
     * @var string
     * @amidev Temporary
     */
    protected $tplSimpleFieldPrefix = '';

    /**
     * RSS link
     *
     * @var string
     */
    protected $linkRSS = '';

    /**
     * Item link
     *
     * @var string
     */
    protected $itemLink = '';

    /**
     * Gets module block name.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleBeforeView($name, array $aEvent, $handlerModId, $srcModId){
        $this->tplBlockName = $aEvent['block'];
        $this->getTemplate()->dropBlock($this->tplBlockName);
        $this->getTemplate()->addBlock($this->tplBlockName, $this->tplFileName);
        $this->getTemplate()->setBlockLocale($this->tplBlockName, $this->aLocale);

        $this->process();

        $aEvent['_break_event'] = true;

        return $aEvent;
    }

    /**
     * Processes data to channel.
     *
     * @return void
     */
    protected function process(){
        // Define options of RSS <channel>
        $aChannel = Array();
        $modId = $this->oExt->getModId();
        $oCore = $GLOBALS['Core'];

        // Required options not null (title, description, link)
        if(AMI::issetOption($modId, "rss_channel_title")){
            $aChannel["rss_tpl_channel_title"] = AMI::getOption($modId, "rss_channel_title");
        }
        if($aChannel["rss_tpl_channel_title"] == ""){
            $aChannel["rss_tpl_channel_title"] = $oCore->GetOption("company_name");
        }

        if(AMI::issetOption($modId, "rss_channel_description")){
            $aChannel["rss_tpl_channel_description"] = AMI::getOption($modId, "rss_channel_description");
        }
        if($aChannel["rss_tpl_channel_description"] == ""){
            $aChannel["rss_tpl_channel_description"] = $aChannel["rss_tpl_channel_title"];
        }

        // Nav_Data link definition
        $this->linkRSS = AMI_Registry::get('path/www_root') . $this->_HrefLink($GLOBALS['frn']->ActiveScriptNavLink);
        $aChannel["rss_tpl_channel_link"] = $this->linkRSS;

        // Optional options
        $aChannel["rss_tpl_channel_copyright"] = AMI::issetOption($modId, "rss_channel_copyright") ? AMI::getOption($modId, "rss_channel_copyright") : '';
        if(AMI::issetOption($modId, "rss_channel_webmaster") && isEmail(AMI::getOption($modId, "rss_channel_webmaster"))){
            $aChannel["rss_tpl_channel_webmaster"] = AMI::getOption($modId, "rss_channel_webmaster");
        }else{
            $aChannel["rss_tpl_channel_webmaster"] = $oCore->GetOption("company_email");
        }
        $aChannel["rss_tpl_channel_pubdate"]       = date("r");// format date is "D, d F Y H:i:s Z"
        $aChannel["rss_tpl_channel_lastbuilddate"] = $aChannel["rss_tpl_channel_pubdate"];
        $aChannel["rss_tpl_channel_lang"]          = AMI_Registry::get('lang');

        $aChannel['rss_tpl_channel_encoding'] = 'UTF-8';

        // Additional options
        if(AMI::issetOption($modId, "rss_channel_image") && AMI::getOption($modId, "rss_channel_image") != -1){
            $aChannel["rss_tpl_channel_img_url"] =
                AMI_Registry::get('path/www_root') .
                AMI::getProperty("ext_rss", "rss_channel_dir") .
                AMI::getOption($modId, "rss_channel_image");
            $aChannel["rss_tpl_channel_img_title"] = $aChannel["rss_tpl_channel_title"];
            $aChannel["rss_tpl_channel_img_link"] = $aChannel["rss_tpl_channel_link"];
        }
        if(AMI::issetOption($modId, "rss_channel_style") && AMI::getOption($modId, "rss_channel_style") != -1){
            $aChannel["rss_tpl_channel_style"] = AMI_Registry::get('path/www_root') . AMI::getProperty("ext_rss", "rss_channel_dir") . AMI::getOption($modId, "rss_channel_style");
        }

        // Throw out tags and special chars to RSS validation for options from <channel>
        $aChannel["rss_tpl_channel_title"] = $this->removeTagsSpecialChars($aChannel["rss_tpl_channel_title"]);
        $aChannel["rss_tpl_channel_description"] = $this->removeTagsSpecialChars($aChannel["rss_tpl_channel_description"]);
        $aChannel["rss_tpl_channel_copyright"] = $this->removeTagsSpecialChars($aChannel["rss_tpl_channel_copyright"]);
        $aChannel["rss_tpl_channel_img_title"] = $aChannel["rss_tpl_channel_title"];

        foreach($aChannel as $key => $val){
            $aChannel[$key] = trim($val);
        }

        AMI_Registry::get('oGUI')->addGlobalVars($aChannel);
    }

    /**
     * Output RSS.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListView($name, array $aEvent, $handlerModId, $srcModId){
        global $conn;

        // Export RSS
        if(AMI::getSingleton('env/request')->get('action') == 'export_rss'){
            AMI_Service::hideDebug();

            // We just output content and die here
            $conn->AddHeader('Content-Type: text/xml; charset=UTF-8');
            if($conn->EnableBuffering){
                ob_clean();
            }
            echo $aEvent[$this->oExt->getModId() != 'forum' ? 'item_list' : 'itemD_list'];
            $GLOBALS['Core']->Cache->pageIsComplitedForSave = true;
            $conn->Out();
            die;

            /*
            // Debug Mode
            // We just output content and die here
            echo "<pre>".htmlspecialchars($aEvent["item_list"])."</pre>";
            $GLOBALS["conn"]->Out();
            die();
            */
        }
        return $aEvent;
    }

    /**
     * Updates SQL query for RSS extension.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListRecordset($name, array $aEvent, $handlerModId, $srcModId){
        $modId = $this->oExt->getModId();
        $oQuery = $aEvent['oQuery'];

        // Export RSS
        if(AMI::getSingleton('env/request')->get('action') == 'export_rss'){
            // Throw out SimpleSet for DB Fields
            // $this->mod->simpleSetFields = Array();

            // Mapping data
            // todo: Add event handler ???
            // $vCustom["fields"]["ext_rss"] = Array("action"=>"callback", "object"=>&$this, "method"=>"_FillFrontDataMapping");
            AMI_Event::addHandler('on_list_body_row', array($this, 'handleListBodyRow'), $modId);

            if(AMI_Registry::get('page/scriptLink', '') != ""){
                $this->itemLink = AMI_Registry::get('path/www_root') . AMI_Registry::get('page/scriptLink');
            }else{
                $this->itemLink = mb_substr(AMI_Registry::get('path/www_root'), 0, -1);
            }


            if(AMI::issetOption($modId, 'rss_elements_period')){
                $oQuery->setLimitParameters(0, 2147483647);
                $period = AMI::getOption($modId, 'rss_elements_period');
                if(stripos($period, 'week') !== false){
                    // MySQL 4 fix, it doesn't support weeks in intervals
                    // @todo: wipe out when MySQL 5 will be used
                    $numDays = 0;
                    preg_match('/^(\d*)\s*.*$/i', $period, $matches);
                    $numDays = 7 * (int)$matches[1];
                    if($numDays <= 6){
                        $numDays = 7;
                    }
                    $period = $numDays . ' DAY';
                }

                // Mapping
                $aFields = array(
                    AMI::getOption($modId, 'rss_elements_period_field') => ''
                );
                $aFields = AMI::getResourceModel($modId . '/table')->getItem()->remapFields($aFields);
                $aFields = array_keys($aFields);
                $periodField = $aFields[0];

                $oQuery->addWhereDef(
                    DB_Query::getSnippet($periodField . " >= CONCAT(DATE_SUB(CURDATE(), INTERVAL " . $period . "), %s)")
                    ->q('00:00:00')
                );
            }else{
                // Get count of RSS elements
                $rssNumElements = intval(AMI::getOption($modId, 'rss_num_elements'));
                if($rssNumElements > 0){
                    $oQuery->setLimitParameters(0, $rssNumElements);
                }
            }
            // Order by Option "rss_item_pubdate"
            if(AMI::issetOption($modId, "rss_item_pubdate")){
                $rssItemPubdate = str_replace("rss_", "", AMI::getOption($modId, "rss_item_pubdate"));
                $rssItemPubdate = $this->getItemPubDateField($rssItemPubdate);
                if(!empty($rssItemPubdate) && ($rssItemPubdate != "none")){
                    $oQuery->setOrder($rssItemPubdate, "DESC");
                }
            }
        }
        return $aEvent;
    }

    /**
     * Adds RSS links to module body and header.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleRSSLinks($name, array $aEvent, $handlerModId, $srcModId){
        $modId = $this->oExt->getModId();
        if(AMI::issetOption($modId, "rss_display_page") && AMI::issetOption($modId, "rss_display_image")){
            $url = '';
            $catId = AMI_Registry::get('page/catId', 0);
            if($catId){
                if(AMI_ModDeclarator::getInstance()->isRegistered($modId . '_cat')){
                    $oItem = AMI::getResourceModel($modId . '_cat/table')->find($catId);
                    $url = $oItem->getURL();
                }
            }

            if($modId != 'forum'){
                $this->linkRSS = AMI_Registry::get('path/www_root') . AMI_Registry::get('page/scriptLink', '') . $url . '?';
            }elseif(isset($aEvent['offset_link'])){
                $this->linkRSS = AMI_Registry::get('path/www_root') . preg_replace('/\?.*$/', '', $aEvent['offset_link']) . '?';
            }

            $aDisplayPage = (array)AMI::getOption($modId, "rss_display_page");
            if(
                in_array('rss_generate', $aDisplayPage) &&
                (AMI::getOption($modId, 'rss_display_image') != 'none') &&
                AMI_PageManager::hasModPublicPage($modId, AMI_Registry::get('lang'))
            ){
                $linkImage = $this->parse(AMI::getOption($modId, "rss_display_image"));
                $aRssData = array(
                    'rss_tpl_linkimg' => $linkImage,
                    'rss_tpl_href'    => $this->linkRSS . 'action=export_rss',
                    'noindex'         => false
                );
                if(AMI::issetOption($modId, 'disable_se_indexing_pages')){
                    $aRssData['noindex'] = in_array('page_ext_rss', AMI::getOption($modId, 'disable_se_indexing_pages'));
                }
                $aEvent["rss_generate"] = $this->parse("rss_generate", $aRssData);
            }

            if(in_array("rss_autolink", $aDisplayPage)){
                AMI_Registry::get('oGUI')->addHeaderTag("link", "rel=\"alternate\" type=\"application/rss+xml\" title=\"RSS\" " . "href=\"" . $this->linkRSS . "action=export_rss\"");
            }
        }
        return $aEvent;
    }

    /**
     * Adds RSS links to a small block.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleRSSLinksSmall($name, array $aEvent, $handlerModId, $srcModId){
        $modId = $this->oExt->getModId();

        // Skip event if RSS export in progress
        if((AMI::getSingleton('env/request')->get('action') == 'export_rss') || !AMI::getProperty($modId, "rss_link_smallblock")){
            return $aEvent;
        }

        // Check if module page exist
        $omitLinkRSS = false;
        if(AMI::issetOption($modId, "spec_id_pages")){
            $aSpecIdPages = AMI::getOption($modId, "spec_id_pages");
            $aPages = AMI_PageManager::getModPages($modId, AMI_Registry::get('lang'));
            if(($key = array_search("0", $aSpecIdPages)) !== false){
                unset($aSpecIdPages[$key]);
            }
            if(sizeof($aPages) > 1 && (sizeof($aSpecIdPages) != 1 || in_array(-1, $aSpecIdPages))){
                $omitLinkRSS = true;
            }
        }

        // Add RSS image
        if(!$omitLinkRSS && AMI::issetOption($modId, "rss_display_page") && AMI::issetOption($modId, "rss_display_image")){
            $url = '';
            $catId = AMI_Registry::get('page/catId', 0);
            if($catId){
                if(AMI_ModDeclarator::getInstance()->isRegistered($modId . '_cat')){
                    $oItem = AMI::getResourceModel($modId . '_cat/table')->find($catId);
                    $url = $oItem->getURL();
                }
            }

            $this->linkRSS = AMI_Registry::get('path/www_root') . AMI_Registry::get('page/scriptLink') . $url . '?';

            $aDisplayPage = (array)AMI::getOption($modId, "rss_display_page");
            if(
                in_array('rss_generate', $aDisplayPage) &&
                (AMI::getOption($modId, 'rss_display_image') != 'none') &&
                AMI_PageManager::hasModPublicPage($modId, AMI_Registry::get('lang'))
            ){
                $linkImage = $this->parse(AMI::getOption($modId, "rss_display_image"));
                $aRssData = array(
                    'rss_tpl_linkimg' => $linkImage,
                    'rss_tpl_href'    => $this->linkRSS . 'action=export_rss',
                );
                $aEvent["aScope"]["rss_generate"] = $this->parse("rss_generate", $aRssData);
            }
        }
        return $aEvent;
    }

    /**
     * Fills item list picture column.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListBodyRow($name, array $aEvent, $handlerModId, $srcModId){
        $aItem = &$aEvent['aData'];
        $modId = $this->oExt->getModId();
        $oCore = $GLOBALS['Core'];
        // Define options of RSS <item> which are optional
        if(AMI::issetOption($modId, "rss_item_title")){
            $rssItemTitle                      = str_replace("rss_", "", AMI::getOption($modId, "rss_item_title"));
            $aItem["rss_tpl_item_title"]       = $aItem[$rssItemTitle];
            $aItem["rss_tpl_item_title"]       = $this->removeTagsSpecialCharsDB($aItem["rss_tpl_item_title"]);
        }
        if(AMI::issetOption($modId, "rss_item_description")){
            $rssItemDescription               = str_replace("rss_", "", AMI::getOption($modId, "rss_item_description"));
            $aItem["rss_tpl_item_description"] = $aItem[$rssItemDescription];
            $aItem["rss_tpl_item_description"] = $this->improveURL($aItem["rss_tpl_item_description"]);
            $aItem["rss_tpl_item_description"] = $this->removeTagsSpecialCharsDB($aItem["rss_tpl_item_description"]);
            $hasDetails = trim(strip_tags($aItem['body'])) !== '';
            $aItem['rss_tpl_item_description_details_link'] = $hasDetails;
            $link = $this->itemLink;
            if(isset($aItem['cat_sublink'])){
                $link .= ('/' . $aItem['cat_sublink']);
            }
            if(isset($aItem['sublink'])){
                $link .= ('/' . $aItem['sublink']);
            }
            $aItem['rss_tpl_item_link'] = $link;
            if($hasDetails){
                // Define option <link> of RSS <item>
                /*
                todo: event
                if(method_exists($this->mod, 'cbRSSItem')){
                    $this->mod->cbRSSItem($aItem);
                }
                */
                $aItem['rss_details'] = $this->parse('rss_details', array('link' => $aItem['rss_tpl_item_link']));
                $aItem['rss_details'] = $this->removeTagsSpecialCharsDB(
                    $rssItemDescription,
                    $this->improveURL($aItem['rss_details'])
                );
            }
        }
        if(AMI::issetOption($modId, "rss_item_fulltext")){
            $rssItemFullText                  = str_replace("rss_", "", AMI::getOption($modId, "rss_item_fulltext"));
            $aItem["rss_tpl_item_fulltext"]    = isset($aItem[$rssItemFullText]) ? $aItem[$rssItemFullText] : '';
            $aItem["rss_tpl_item_fulltext"]    = $this->improveURL($aItem["rss_tpl_item_fulltext"]);
            $aItem["rss_tpl_item_fulltext"]    = $this->removeTagsSpecialCharsDB($aItem["rss_tpl_item_fulltext"]);
        }
        if(AMI::issetOption($modId, "rss_item_pubdate")){
            $rssItemPubdate                   = str_replace("rss_", "", AMI::getOption($modId, "rss_item_pubdate"));
            $rssItemPubdate = $this->getItemPubDateField($rssItemPubdate);
            $aItem["rss_tpl_item_pubdate"]     = date("r", strtotime($aItem[$rssItemPubdate]));
        }
        if(AMI::issetOption($modId, "rss_item_guid")){
            if(AMI::getOption($modId, "rss_item_guid") == "rss_link"){
                $aItem["rss_tpl_item_guid"]    = $this->linkRSS . "?id=".$aItem["id"];
            }
        }
        // RSS <item> image
        if(AMI::issetOption($modId, 'rss_item_enclosure')){
            $map = array(
                'rss_small_image' => 'small_picture_src',
                'rss_image'       => 'picture_src',
                'rss_popup_image' => 'popup_picture_src'
            );
            $source = AMI::getOption($modId, 'rss_item_enclosure');
            $fileNameImage = isset($map[$source]) ? (isset($aItem[$map[$source]]) ? $aItem[$map[$source]] : '') : '';
            $fileNameImage = trim(str_replace(AMI_Registry::get('path/www_root'), '', $fileNameImage));
            if(file_exists($fileNameImage)){
                $imgTypes = $oCore->GetProperty("images_mimes");
                $fileExtension = mb_strtoupper(get_file_ext($fileNameImage));
                if(isset($imgTypes[$fileExtension])){
                    $aItem["rss_tpl_enclosure_src"]  = AMI_Registry::get('path/www_root') . $fileNameImage;
                    $aItem["rss_tpl_enclosure_size"] = filesize($fileNameImage);
                    $aItem["rss_tpl_enclosure_type"] = $imgTypes[$fileExtension];
                }
            }
        }
        return $aEvent;
    }

    /**
     * Apply pubdate field from options to model mapping.
     *
     * @param string $pubDateField  Pubdate field from options
     * @return string
     */
    private function getItemPubDateField($pubDateField){
        $oItem = AMI::getResourceModel($this->oExt->getModId() . '/table')->getItem();
        $aFields = array(
            'm_date' => 'date_modified',
            'c_date' => 'date_created'
        );
        if(isset($aFields[$pubDateField])){
            $pubDateField = $aFields[$pubDateField];
        }
        return $pubDateField;
    }

    /**
     * Improve relative URL to RSS validation for options from <channel>.
     *
     * @param  string &$url  URL
     * @return string
     */
    private function improveURL(&$url){
        $patternHref = "/href=(\"|')?([^\"'\s]+)/si";
        preg_match_all($patternHref, $url, $matches);
        AMI_Registry::push('disable_error_mail', true);
        foreach($matches[2] as $key => $strVal){
            $parseUrl = parse_url($strVal);
            if(!isset($parseUrl["host"]) || $parseUrl["host"]== ""){
                $url = str_replace($strVal, AMI_Registry::get('path/www_root') . $strVal, $url);
            }
        }
        $patternSrc  = "/src=(\"|')?([^\"'\s]+)/si";
        preg_match_all($patternSrc, $url, $matches);
        foreach($matches[2] as $key => $strVal){
            $parseUrl = parse_url($strVal);
            if(!isset($parseUrl["host"]) || $parseUrl["host"]== ""){
                $url = str_replace($strVal, AMI_Registry::get('path/www_root') . $strVal, $url);
            }
        }
        $url = preg_replace('/(' . preg_quote(AMI_Registry::get('path/www_root'), '/') .')+/i', AMI_Registry::get('path/www_root'), $url);
        AMI_Registry::pop('disable_error_mail');
        return $url;
    }

    /**
     * Escapes XML characters.
     *
     * @param  string &$str  String to escape
     * @return string
     */
    private function _escapeXmlChars(&$str) {
        $aXmlReplaceChars = array(
            '&' => '&amp;',
            '<' => '&lt;',
            '>' => '&gt;',
            '"' => '&quot;'
        );
        return strtr($str, $aXmlReplaceChars);
    }

    /**
     * Throw out tags and special chars to RSS validation for options from <channel>.
     *
     * @param  string $vData  String to remove chars from
     * @return string
     */
    private function removeTagsSpecialChars($vData){
        $vData = unhtmlentities($vData);
        $vData = strip_tags($vData);
        $aSetHTML = get_html_translation_table(HTML_SPECIALCHARS, ENT_QUOTES);
        foreach($aSetHTML as $key => $val){
            $aSetChars[] = htmlspecialchars($val);
            $aSetChars[] = htmlspecialchars($key);
            $aSetChars[] = $key;
        }
        $aSetChars[] = "'";
        $aSetChars[] = "&apos;";
        $aSetChars[] = "&nbsp;";
        $aSetChars[] = "apos;";
        $aSetChars[] = "nbsp;";
        $vData = str_replace($aSetChars, "", $vData);
        return $vData;
    }

    /**
     * Throw out tags and special chars to RSS validation for options defined by DB fields.
     *
     * @param  string $vData  String to remove chars from
     * @return string
     */
    private function removeTagsSpecialCharsDB($vData) {
        $vData = $this->_escapeXmlChars($vData);
        $vData = str_replace("'", "&apos;", $vData);
        $vData = str_replace("&nbsp;", " ", $vData);
        $vData = preg_replace("/(&lt;)+$/", "", $vData);
        $vData = preg_replace("/^(&gt;)+/", "", $vData);
        $vData = preg_replace("/(&amp;lt;)+$/", "", $vData);
        $vData = preg_replace("/^(&amp;gt;)+/", "", $vData);
        $vData = trim($vData);
        return $vData;
    }

    /**
     * Processes link href.
     *
     * @param  string $link  Source link
     * @return string
     */
    private function _hrefLink($link){
        $link = stripslashes($link);
        $anchor = "";
        if(($pos = mb_strpos($link, "#")) !== false){
            $anchor = mb_substr($link, $pos);
            $link = mb_substr($link, 0, $pos);
        }
        $link = preg_replace('/&(?:cat)?offset=(&|$)/i', '\\1', $link);
        $link = preg_replace('/\?(?:cat)?offset=(&|$)/i', '?', $link);
        if($link[mb_strlen($link)-1] == "?"){
            $link = mb_substr($link, 0, mb_strlen($link)-1);
        }
        return $link . $anchor;
    }
}
