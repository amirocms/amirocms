<?php
/**
 * AmiExt/CePageBreak extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Config_AmiExt_CePageBreak
 * @version   $Id: AmiExt_CePageBreak_Frn.php 42499 2013-10-22 11:01:11Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/CePageBreak extension configuration front controller.
 *
 * @package    Config_AmiExt_CePageBreak
 * @subpackage Controller
 * @resource   ce_page_break/module/controller/frn <code>AMI::getResource('ce_page_break/module/controller/frn')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_CePageBreak_Frn extends Hyper_AmiExt{
    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Extension initialization.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Ext::__construct()
     * @see    AMI_Mod::init()
     */
    public function handlePreInit($name, array $aEvent, $handlerModId, $srcModId){
        $oView = $this->getView('frn');
        $oView->setExt($this);
        AMI_Event::addHandler('on_item_details', array($oView, 'handleDetails'), $aEvent['modId']);
        return $aEvent;
    }

    /**#@-*/
}

/**
 * AmiExt/CePageBreak extension configuration front view.
 *
 * @package    Config_AmiExt_CePageBreak
 * @subpackage Controller
 * @resource   ce_page_break/view/frn <code>AMI::getResource('ce_page_break/view/frn')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_CePageBreak_ViewFrn extends AMI_ExtView{
    /**
     * Tag using to break a page
     *
     * @var string
     */
    protected $pageBreakTag = '##body_page_break##';

    /**
     * Pagination block name
     *
     * @var string
     */
    protected $paginationBlockName = '_pagerDetails';

    /**
     * Pagination template name
     *
     * @var string
     */
    protected $paginationTemplate = 'templates/pager_item_details.tpl';

    /**
     * Adds js section to admin form.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModFormView::get()
     */
    public function handleDetails($name, array $aEvent, $handlerModId, $srcModId){
        $breaker = $this->pageBreakTag;
        $oItem = $aEvent['oItem'];
        $bodyContent = $oItem->body;
        $oGui = AMI_Registry::get('oGUI');
        $oRequest = AMI::getSingleton('env/request');
        $allParts = $oRequest->get("all_parts", false);
        $part = $oRequest->get("part", 0);
        if($oGui->PrintVersion || !empty($allParts)){
            $oItem->body = str_replace($breaker, "", $bodyContent);
        }else{
            $lengthBreaker = mb_strlen($breaker);
            $aParts = Array();
            $num = 0;
            $pos = -1;
            $aParts[$num]["start"] = 0;
            while(($pos = mb_strpos($bodyContent, $breaker, $pos + 1)) !== false){
                $aParts[$num++]["end"] = $pos;
                $aParts[$num]["start"] = $pos + $lengthBreaker;
            }

            $aParts[$num]["end"] = mb_strlen($bodyContent);
            if(!isset($aParts[$part])){
                $part = 0;
            }
            if($num > 0){
                $body = mb_substr($bodyContent, $aParts[$part]["start"], ($aParts[$part]["end"] - $aParts[$part]["start"]));
                $link = AMI_Registry::get('path/www_root');
                // Add language
                if(AMI::getOption('core', 'allow_multi_lang')){
                    $defaultLang = AMI::getOption('core', 'default_data_lang');
                    $link .= (AMI_Registry::get('lang_data', $defaultLang) . '/');
                }
                $link .= AMI_PageManager::getModLink($handlerModId, AMI_Registry::get('lang_data', 'en'));
                if(isset($aEvent['aData']['cat_sublink'])){
                    $link .= '/' . $aEvent['aData']['cat_sublink'];
                }
                if(isset($aEvent['aData']['sublink'])){
                    $link .= '/' . $aEvent['aData']['sublink'];
                }
                $link .= '?action=rsrtme&part=[START]';
                $pager = $this->getPagination($link, count($aParts), $part);
                // $aEvent['aScope']['body'] = $body . $pager;
                $aEvent['aScope']['pager_item_details'] = $pager;
            }
        }
        return $aEvent;
    }

    /**
     * Returns pagination html for page break extension data.
     *
     * @param string $link  Link to a page
     * @param int $total    Total pages
     * @param int $current  Current page
     * @return string
     */
    protected function getPagination($link, $total, $current){
        $oTpl = $this->getTemplate();
        $aPagination = $this->getPager($total, $current);
        $oTpl->addBlock($this->paginationBlockName, $this->paginationTemplate);

        $pagination = '';
        $activePageLink = '';
        $aScope = array();
        $isAfterActive = false;
        $cLinkHtml = $link;

        foreach($aPagination as $aItem){
            if($aItem['type'] == 'active'){
                $activePageLink = str_replace('[START]', $aItem['_start'], $cLinkHtml);
                $isAfterActive = true;
            }

            if(isset($aItem['page_start'])){
                $page = $oTpl->parse($this->paginationBlockName.':page_tpl', $aItem);
            }else{
                $page = $aItem['pagenum'];
            }

            $cLinkHtmlReady = str_replace('[START]', $aItem['start'], $cLinkHtml);
            $aLocalScope = array('page' => $page, 'link' => $cLinkHtmlReady, 'start' => $aItem['start']);
            $sitem = $oTpl->parse($this->paginationBlockName . ':'.$aItem['type'], $aLocalScope);
            $spacer = $oTpl->parse($this->paginationBlockName . ':spacer', $aLocalScope);

            if(!empty($pagination)){
                $pagination .= $spacer;
            }
            $pagination .= $sitem;

            if($aItem['type'] == 'page'){
                $varName = $isAfterActive ? 'page_after_active' : 'page_before_active';
            }else{
                $varName = $aItem['type'];
            }

            if(isset($aScope[$varName])){
                $aScope[$varName] .= $spacer.$sitem;
            }else{
                $aScope[$varName] = $sitem;
            }
        }

        $aScope['body'] = $pagination;
        $aScope['page_size'] = '';
        $postfix =
            $oTpl->issetSet($this->paginationBlockName . ':body') && $total > 1
                ? ':body'
                : '';
        $pager = $oTpl->parse($this->paginationBlockName . $postfix, $aScope);
        $linkAll = str_replace('&part=[START]', '', $link);
        $pagesAll = $oTpl->parse($this->paginationBlockName . ':pages_all', array('offset_link' => $linkAll));
        $aPager = array(
            'pager' => $pager,
            'pages_all' => $pagesAll
        );
        $pagination = $oTpl->parse($this->paginationBlockName . ':pager_body', $aPager);
        return $pagination;
    }

    /**
     * Get pager data array.
     *
     * @param int $pagesCount  Total pages count
     * @param int $activePage  Current page
     * @return array
     */
    protected function getPager($pagesCount, $activePage){
        $aPager = array();
        $pageSize = 1;
        $maxPagesCount = AMI::getOption('core', 'default_page_size');
        $visPagesCount = min($pagesCount, $maxPagesCount ? $maxPagesCount : 10);
        $visMiddle = ceil($visPagesCount / 2);
        $visStartPage = 0;
        $visEndPage = -1;

        if($activePage >= ($visStartPage + $visMiddle)){
            $visStartPage = $activePage - $visMiddle + 1;
        }
        if($visStartPage + $visPagesCount > $pagesCount){
            $visStartPage = $pagesCount - $visPagesCount;
        }

        $endLinkOffset = ($pagesCount - 1) * $pageSize;

        if($activePage > 0  && $visStartPage > 0){
            $aPager[] = array("type" => "first", "pagenum" => 1, "start" => 0);
        }
        if(($activePage > 0)){
            $aPager[] = array(
                "type" => "prev",
                "pagenum" => "",
                "start" => (($activePage - 1) * $pageSize)
            );
        }
        for($i = $visStartPage; $i < ($visStartPage + $visPagesCount); $i++){
            $startOffset = $i * $pageSize;
            if($i == $activePage){
                $aPager[] = array("type" => "active", "pagenum" => ($i+1), "start" => "", "_start" => $startOffset);
            }else{
                $aPager[] = array("type" => "page", "pagenum" => ($i+1), "start" => $startOffset);
            }
        }
        $visEndPage = $i;

        if($activePage != ($pagesCount - 1)){
            $startOffset =($activePage + 1) * $pageSize;
            $aPager[] = array("type" => "next", "pagenum" => "", "start" => $startOffset);
        }

        if(($activePage < ($pagesCount - 1)) && ($visEndPage < $pagesCount)){
            $aPager[] = array("type" => "last", "pagenum" => $pagesCount, "start" => $endLinkOffset);
        }

        if($calcPages){
            foreach($aPager as $key => $val){
                $aPager[$key]["page_start"] = ($val["pagenum"] - 1) * $pageSize + 1;
                $aPager[$key]["page_end"] = $val["pagenum"] * $pageSize;
            }
        }

        return $aPager;
    }

    /**
     * Prepares templates paths and blockname.
     *
     * @return void
     * @amidev
     */
    protected function prepareTemplates(){
        // This extension has no templates
    }

    /**#@-*/
}