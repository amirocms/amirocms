<?php
/**
 * @copyright Amiro.CMS. All rights reserved.
 * @category  AMI
 * @package   Service
 * @version   $Id: router.php 44504 2013-11-27 10:21:28Z Leontiev Anton $
 * @since     6.0.2
 */

if(empty($skip_detect_page)){
    // == detect request method
    $method = $_SERVER['REQUEST_METHOD'];
    if($method != 'POST'){
        $method = 'GET';
    }
    $Protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
    $frn->Method = $method;
    $frn->Protocol = $Protocol;

    if(isset($_POST['modlink'])){
        $_POST['modlink'] = trim(preg_replace('~[^a-zA-Z0-9\-_\./?=&]~s', '', $_POST['modlink']));
    }
    $vUrl =
        $method == 'POST' && !empty($_POST['modlink'])
            ? $ROOT_PATH_WWW . $_POST['modlink']
            : getCurrentUrl(TRUE);

    if(!$cached_mode){
        if(defined('AMI_FIRE_ON_DETECT_PAGE') && AMI_FIRE_ON_DETECT_PAGE){
            $aEvent = array(
                'url'    => getCurrentURL(),
                'oCache' => $oCache
            );
            AMI_Event::fire('custom_on_detect_page', $aEvent, AMI_Event::MOD_ANY);
        }

        // Detect language
        $lang = $frn->Core->GetOption('default_data_lang');
        $frn->Core->detectSpecialLanguageData();

        $forceLangRedirect = false;

        if($frn->Core->GetOption('allow_multi_lang')){
            $tmpLang = getLangFromURL($vUrl, $ROOT_PATH_WWW);
            if(mb_strlen($tmpLang) > 1 && in_array($tmpLang, $frn->Core->getAOption("installed_langs"))){
                $lang = $tmpLang;
            }else{
                if(!isset($GLOBALS['AMI_FRONT_ENTRY_POINT']) || !$GLOBALS['AMI_FRONT_ENTRY_POINT']){
                    $forceLangRedirect = true;
                }
            }
        }

        $lang_data = $lang;
    }

    $frn->setLang($lang);
    $frn->_systemIncJSCnt();

    $conn->lang = $lang;
    AMI_Registry::set('lang_data', $lang);

    // == detecting module
    $ActiveScriptLink = "";

    // try to detect module type
    $postfix = $frn->Core->GetOption('allow_multi_lang') ? $lang . '/' : '';

    require_once $GLOBALS['CLASSES_PATH'] . files_subpath . 'Page.php';

    $tmpPage = new Page($frn);
    $tmpPage->tree->UseHidden = false;
    $tmpPage->tree->UseLang = true;
    $vp = $tmpPage->getDefaultPageIds();
    $tmpPage->DefaultId = $vp[$lang];
    $tmpPage->PathRootId = 0;

    $vScriptLink = '';
    $link = '';
    if(
            empty($_isIndexPhpScript) &&
            preg_match('/' . preg_quote($ROOT_PATH_WWW . $postfix, '/') . '([^\?#&]*)/', $vUrl, $tmpMatches)
    ){
        $vScriptLink = $tmpMatches[1];
        preg_match('/' . preg_quote($ROOT_PATH_WWW . $postfix, '/') . '([^\?]*)/', $vUrl, $tmpMatches);
        $link = $tmpMatches[1];
    }
    if($GLOBALS['AMI_ESCAPE_REQUEST']){
        $vScriptLink = addslashes($vScriptLink);
    }
    $ActiveScriptLink = $vScriptLink;
    $checkScriptLink = $vScriptLink;

    if(!$cached_mode){
        $ActiveModule = $frn->PManager->GetName();
        if(!empty($AMI_ENV_SETTINGS['external_call'])){
            return;
        }

        $tmpItem = $tmpPage->DetectPageByLink($vScriptLink);

        if(
            sizeof($tmpItem) &&
            !$tmpItem[$lang]['redirect_id'] &&
            $tmpItem[$lang]['script_link'] != $link &&
            rtrim($tmpItem[$lang]['script_link'], '/') == rtrim($link, '/')
        ){
            // page found, check ending slash difference
            if($frn->Core->GetModOption('common_settings', 'slash_reaction') == 301){
                // 301
                echo ' ';
                $__url = $frn->fixURLSlash();
                $Core->Cache->pageIsComplitedForSave = true;
                doRedirect301($__url, $Core->Cache); // exit point
            }else{
                // 404
                $tmpItem = array();
            }
        }

        if(
            isset($tmpItem[$lang]["module_name"]) &&
            !empty($tmpItem[$lang]["module_name"]) &&
            (
                $frn->PManager->GetOption("wrong_modules_as_static") ||
                $frn->Core->IsFrontAllowed($tmpItem[$lang]["module_name"])
            )
        ){
            if($frn->Core->IsFrontAllowed($tmpItem[$lang]["module_name"])){
                $ActiveModule = $tmpItem[$lang]["module_name"];
            }
            $ActiveScriptLink = $tmpItem[$lang]["script_link"];
            $ActiveModulePageId = $tmpItem[$lang]["id"];
            $pageId = $ActiveModulePageId;
        }

        if(in_array($ActiveScriptLink, $frn->Core->GetProperty("direct_scripts"))){
            // run script directly
            unset($DetectPage);
            require $ROOT_PATH . $ActiveScriptLink;
            die;
        }

        $needToCorrectHomeLink = false;

        if($ActiveModule == "pages" || !isset($pageId) || !($pageId > 0)){
            if(
                $forceLangRedirect ||
                (
                    !empty($ActiveScriptLink) &&
                    $ActiveScriptLink != "pages.php" &&
                    empty($GLOBALS['AMI_FRONT_ENTRY_POINT']) &&
                    $vUrl != $ROOT_PATH_WWW . $postfix &&
                    (!isset($pageId) || !($pageId > 0))
                )
            ){
                cmsHandle404();
            }

            $originalPageId = $pageId = isset($pageId) ? $pageId : null;
            $tmpId = $pageId;
            $pageId = $tmpPage->AdjustPageId($pageId);
            if($pageId === false){
                trigger_error("Root page is missing or not published", E_USER_ERROR);
            }elseif($pageId < 1){
                $tmpPage->processRedirection($tmpId, $conn);
                $generate404 = true;
            }

            if($pageId == $tmpPage->DefaultId && $frn->Core->GetOption('use_ip_filter')){
                if($tmpPage->GetDefaultPageByIPArea($pageId) === false){
                    trigger_error("IP Filter error page not found for page [$pageId]", E_USER_ERROR);
                }
            }

            if($pageId == $tmpPage->DefaultId && $frn->Core->IsInstalled('srv_multi_sites')){
                if(!$tmpPage->CheckDefaultPageBySiteId($pageId)){
                    trigger_error("Default page for site " . $frn->Core->GetModOption('srv_multi_sites', 'id') . " not found", E_USER_ERROR);
                }
            }

            if($pageId <> $tmpId){
                $ActiveModulePageId = $pageId;
                $tmpItem = $tmpPage->DetectPageById($pageId);
                $ActiveModule = $frn->PManager->GetName();
                if(isset($tmpItem[$lang]["module_name"]) && !empty($tmpItem[$lang]["module_name"]) && $frn->Core->IsFrontAllowed($tmpItem[$lang]["module_name"])
                ){
                    $sourceScriptLink = $ActiveScriptLink;
                    $ActiveModule = $tmpItem[$lang]["module_name"];
                    $ActiveModulePageId = $tmpItem[$lang]["id"];
                    $pageId = $ActiveModulePageId;
                    $needToCorrectHomeLink = true;
                    $checkScriptLink = $tmpItem[$lang]["script_link"];
                    $ActiveScriptLink = $tmpItem[$lang]["script_link"];
                }
            }
        }
    }

    $stopArgModName = $frn->getStopArgModName($ActiveModule);

    $needDetectNav = false;
    if(empty($generate404)){
        $generate404 = false;
    }
    if($ActiveModule != 'pages' && $stopArgModName != ''){
        if($ActiveScriptLink != $vScriptLink){
            $vArgLink = mb_substr($vScriptLink, mb_strlen($ActiveScriptLink));
            if(!$frn->SetupStopArgs($stopArgModName, $vArgLink, $pageId)){
                $generate404 = trim($ActiveScriptLink, '/') != trim($vScriptLink, '/');
            }
        }else{
            $needDetectNav = true;
        }
    }elseif($frn->Method == 'GET'){
        if(!isset($sourceScriptLink)){
            $sourceScriptLink = $ActiveScriptLink;
        }
        $generate404 = trim($sourceScriptLink, '/') != trim($vScriptLink, '/');
    }
    if($generate404){
        cmsHandle404();
    }

    if(!empty($_isIndexPhpScript) && strpos($_SERVER['REQUEST_URI'], '/index.php') === 0){
        $Core->Cache->pageIsComplitedForSave = true;
        echo ' ';
        $null = null;
        doRedirect301($ROOT_PATH_WWW . $postfix, $null);
    }

    $LangFreeScriptLink = $ActiveScriptLink;
    $ActiveScriptLink = $postfix . $ActiveScriptLink;
    $ActiveScriptFullLink = $postfix . getScriptFullUrl(isset($vUrl) ? $vUrl : '', $ROOT_PATH_WWW . $postfix);
    $ActiveScriptNavLink = $ActiveScriptLink . (isset($vArgLink) ? $vArgLink : '');

    $aUrl = parse_url($vUrl);
    if(!isset($aUrl['query'])){
        $aUrl['query'] = '';
    }
    parse_str($aUrl['query'], $tmpArgs);
    mb_internal_encoding('UTF-8');
    if(!$GLOBALS['AMI_ESCAPE_REQUEST']){
        $tmpArgs = removeSpecial($tmpArgs, 'slashes');
    }

    $_TMPGET = array();
    foreach ($tmpArgs as $key => $val){
        $_TMPGET[$key] = $val;
    }
}

$frn->PrepareVars();


if(empty($skip_detect_page)){

    if($needDetectNav){
        $frn->detectNavData($stopArgModName, isset($vArgLink) ? $vArgLink : '');
    }

    $frn->SetPageId($pageId);
    $_isHomePage = false;

    if($pageId == $tmpPage->DefaultId){
        $_isHomePage = true;
    }

    $frn->SetPageModule($ActiveModule);
    $frn->SetFrontLink($ActiveScriptLink);
    $frn->ActiveScriptNavLink = $ActiveScriptNavLink;
}

$aPageData = array();
if(empty($Core->Cache->ExtraPageData) || empty($Core->Cache->ExtraPageData['registry'])){
    $aPageData =
        array(
            'id' => isset($pageId) ? (int) $pageId : 0,
            'modId' => (string) $ActiveModule,
            'scriptLink' => !empty($ActiveScriptLink) ? $ActiveScriptLink : '',
            'itemId' => (string) (empty($frn->Vars['id']) ? 0 : $frn->Vars['id']),
            'catId' => (string) (empty($frn->Vars['catid']) ? 0 : $frn->Vars['catid']),
            'isAvailable' => true,
            'seoData' => array('index' => true, 'follow' => true)
        );
}else{
    $aPageData = $Core->Cache->ExtraPageData['registry']['page'];
}
AMI_Registry::set('page', $aPageData);
if($vScriptLink !== $link){
    $frn->Gui->setRobotsMeta(FALSE, TRUE);
}
unset($link, $aPageData);

function cmsHandle404($redirect = true){
    global $forceLangRedirect, $frn, $mod404, $pageId, $ActiveModule, $Core, $conn, $ROOT_PATH_WWW, $postfix, $tmpPage, $vUrl, $_SERVER;

    $RedirectToHome = false;
    if(
            ($forceLangRedirect && rtrim($vUrl, '/') === rtrim($ROOT_PATH_WWW, '/')) ||
            $frn->PManager->GetOption('broken_links_to_home') ||
            !$frn->Core->IsFrontAllowed('page_404')
    ){
        $RedirectToHome = true;
    }else{
        $mod404 = $tmpPage->getPageInfoByModuleName("page_404", array("public"));
        if($mod404 !== false || $mod404["public"] != 0){
            $pageId = $mod404["id"];
            $ActiveModule = "page_404";
        }else{
            $RedirectToHome = true;
        }
    }
    if($redirect && $RedirectToHome && RUN_UPDATE !== true){
        $autoRedirectByLang = $Core->GetOption('auto_redirect_by_lang');
        if($forceLangRedirect && $autoRedirectByLang && mb_strpos($GLOBALS['_SERVER']['HTTP_ACCEPT_LANGUAGE'], 'ru') !== false){
            $postfix = 'ru/';
        }
        $Core->Cache->pageIsComplitedForSave = true;
        if($autoRedirectByLang){
            doRedirect($frn->Gui, $ROOT_PATH_WWW . $postfix, $conn); // exit point
        }else{
            echo ' ';
            doRedirect301($ROOT_PATH_WWW . $postfix, $Core->Cache); // exit point
        }
    }
    return array($RedirectToHome, $ActiveModule);
}

function getScriptFullUrl($cUrl, $cRootUrl) {
    $res = '';
    if (!empty($cUrl)) {
        $vS = mb_strpos($cUrl, $cRootUrl);
        if ($vS !== false) {
            $res = preg_replace(array("~'~u", '~<~u', '~>~u'), array('%27', '%3C', '%3E'), mb_substr($cUrl, $vS + mb_strlen($cRootUrl)));
        }
    }
    return $res;
}
