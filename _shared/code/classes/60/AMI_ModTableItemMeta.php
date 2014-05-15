<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModTableItemMeta.php 49242 2014-03-31 09:32:33Z Maximov Alexey $
 * @since     5.14.4
 */

/**
 * Model meta data processor interface.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @since     5.14.4
 */
interface AMI_iModTableItemMeta{
    /**
     * Event handler.
     *
     * Creates/updates module tabel item model meta data.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     * @see    AMI_ModListView::formatColumn()
     */
    public function handleSaveModelItem($name, array $aEvent, $handlerModId, $srcModId);
}

/**
 * Model meta data processor.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @see        AMI_ModTableItem::save()
 * @resource   {$modId}/table/item/model/meta <code>AMI::getResource('{$modId}/table/item/model/meta')*</code>
 * @since      5.14.4
 */
class AMI_ModTableItemMeta implements AMI_iModTableItemMeta{
    /**
     * Field names to generate meta
     *
     * @var array
     */
    protected $aFieldSources = array(
        'header'   => 'header',
        'announce' => 'announce',
        'body'     => 'body'
    );

    /**
     * Module id
     *
     * @var string
     * @see AMI_ModTableItemMeta::handleSaveModelItem()
     */
    protected $modId;

    /**
     * Prefix link for item sublink
     *
     * @var string
     */
    protected $prefixLink = '';

    /**
     * Max number words for sublink
     *
     * @var int
     */
    protected $maxWords = 5;

    /**
     * Max sublink length
     *
     * @var int
     */
    protected $maxLen = 64;

    /**
     * Sublink conflict item
     *
     * @var AMI_ModTableItem
     */
    private $oConflictItem;

    /**
     * Event handler.
     *
     * Creates/updates module tabel item model meta data.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     * @see    AMI_ModTableItem::save()
     */
    public function handleSaveModelItem($name, array $aEvent, $handlerModId, $srcModId){
        $this->modId = $handlerModId;
        if($aEvent['oTable']->hasField('sublink')){
            $this->processSublink($aEvent);
        }
        if($aEvent['oTable']->hasField('sm_data')){
            $this->processHTMLMeta($aEvent);
        }
        return $aEvent;
    }

    /**
     * Sublinks functionality.
     *
     * @param  array &$aEvent  Event data
     * @return void
     * @todo   Implement
     */
    protected function processSublink(array &$aEvent){
        $sublink = isset($aEvent['aData']['sublink']) ? $aEvent['aData']['sublink'] : '';
        $sublink = $this->prepareSublink($sublink);

        $origSublink =
            isset($aEvent['aMeta']['original_sublink'])
            ? $aEvent['aMeta']['original_sublink']
            : '';
        if(empty($sublink)){
            // Auto generate sublink

        	$newLink = $this->genAutoLink($aEvent["aData"][$this->aFieldSources['header']], $aEvent["aData"]["lang"], '', $aEvent['aData']);
            $this->oConflictItem = $this->getLinkConflictItem($aEvent['oTable'], $newLink, 'sublink', $aEvent['oItem']->getId());

            if($this->oConflictItem || $newLink == ''){
                AMI_Event::addHandler('on_after_save_model_item', array($this, 'addItemIdToSublink'), $this->modId);
            }else{
                AMI::getSingleton('response')->addStatusMessage(
                    'status_apply_note_linkauto',
                    array('link' => $newLink),
                    AMI_Response::STATUS_MESSAGE,
                    $this->modId
                );
            }

           	$aSublinkEvent = $aEvent;
            $aSublinkEvent['link'] = $newLink;
            /**
             * Called when generating a table item model sublink.
             *
             * @event      on_item_sublink_generation $modId
             * @eventparam bool         onCreate  TRUE on new item creation, FALSE on existing item saving
             * @eventparam AMI_ModTable oTable    Table model
             * @eventparam array        aData     Data array
             * @eventparam string       link      New link
             */
            AMI_Event::fire('on_item_sublink_generation', $aSublinkEvent, $this->modId);

            if($aSublinkEvent['link'] != $newLink){
                $newLink = $aSublinkEvent['link'];
                $this->oConflictItem = $this->getLinkConflictItem($aEvent['oTable'], $newLink);
                if($this->oConflictItem){
                    AMI_Event::addHandler('on_after_save_model_item', array($this, 'addItemIdToSublink'), $this->modId);
                }
            }
            unset($aSublinkEvent);

            $sublink = $newLink;

        }elseif($sublink != $origSublink){
            if(!$this->checkURLSymbols($sublink)){
                $sublink = $origSublink;
                trigger_error("The item's link contains not allowed symbols. Link was not changed.", E_USER_WARNING);
            }else{
                $this->oConflictItem = $this->getLinkConflictItem($aEvent['oTable'], $sublink, 'sublink', $aEvent['oItem']->getId());
                if($this->oConflictItem){
                    AMI_Event::addHandler('on_after_save_model_item', array($this, 'addItemIdToSublink'), $this->modId);
                }
            }
        }
        $aEvent['oItem']->setValue('sublink', $sublink);
        $aEvent['aData']['sublink'] = $sublink;
    }

    /**
     * Check unique of item sublink and return conflict item model.
     *
     * @param  AMI_ModTable $oTable            Item's table.
     * @param  string       $newlink           Sublink value
     * @param  string       $sublinkFieldName  Sublink field name
     * @param  int|string   $id                Current item id
     * @return AMI_ModTableItem|bool  False if there is no conflicts or conflict item model otherwise.
     */
    public function getLinkConflictItem(AMI_ModTable $oTable, $newlink, $sublinkFieldName = 'sublink', $id = 0){
        $oItem = $oTable->findByFields(array($sublinkFieldName => $newlink));
    	return empty($oItem->id) || ($id && $oItem->id == $id) ? false : $oItem;
    }

    /**
     * Event handler.
     *
     * Update item model (stored in $aEvent['oItem']) by adding ID to sublink variable ('-36')
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     * @see    AMI_ModTableItem::save()
     */
    public function addItemIdToSublink($name, array $aEvent, $handlerModId, $srcModId){
    	$aEvent['oItem']->sublink = $aEvent['aData']['sublink'] . '-' . $aEvent['oItem']->id;
        if($this->oConflictItem){
            $this->oConflictItem->suppressModPageError();
            AMI::getSingleton('response')
                ->addStatusMessage(
                    'status_apply_linkauto_item_found_warn',
                    array(
                        'front_link' => $GLOBALS['ROOT_PATH_WWW'] . $this->oConflictItem->getFullURL(),
                        'name'       => $this->oConflictItem->header,
                        'mod_id'     => $this->oConflictItem->getModId(),
                        'id'         => $this->oConflictItem->id
                    ),
                    AMI_Response::STATUS_MESSAGE_ERROR,
                    $this->modId
                );
            $this->oConflictItem = null;
        }
        AMI_Event::dropHandler('on_after_save_model_item', array($this, 'addItemIdToSublink'), $this->modId);
        AMI_Event::disableHandler('on_after_save_model_item');
        $aEvent['oItem']->save();
        AMI_Event::enableHandler('on_after_save_model_item');
        return $aEvent;
    }

    /**
     * HTML-meta fields functionality.
     *
     * @param  array &$aEvent  Event data
     * @return void
     * @see    AMI_ModTableItemMeta::handleSaveModelItem()
     * @todo   Avoid string type casting for 'is_kw_manual', 'filled'.
     */
    protected function processHTMLMeta(array &$aEvent){
        $bSaveMeta = false;
        if($aEvent['onCreate']){
            $aEvent['aData'] += array('sm_data' => '');
        }
        if(
            AMI::issetOption($this->modId, 'keywords_generate') &&
            isset($aEvent['aData'][$this->aFieldSources['header']]) &&
            isset($aEvent['aData'][$this->aFieldSources['body']]) &&
            AMI::getOption($this->modId, 'keywords_generate') !== 'none'
        ){
            $mode = AMI::getOption($this->modId, 'keywords_generate');

            $aHTMLMeta = array();
            foreach($aEvent['oTable']->getHTMLFields() as $key => $field){
                $aHTMLMeta[$key] = isset($aEvent['aData'][$field]) ? $aEvent['aData'][$field] : null;
            }
            $aHTMLMeta += array(
                // 'is_kw_manual' => (string)(int)!$aEvent['aMeta']['auto'],
                'filled'       => (string)(int)$aEvent['aMeta']['filled']
            );
            $name = AMI_Lib_String::unhtmlEntities($aEvent['aData'][$this->aFieldSources['header']]);

            $aHTMLMeta['keywords'] = str_replace('"', '', $aHTMLMeta['keywords']);
            $aHTMLMeta['description'] = str_replace('"', '', $aHTMLMeta['description']);

            // Check HTML meta difference {

            $isEqual = $mode == 'auto' && !empty($aEvent['aOrigData']['html_meta']);
            if($isEqual){
                foreach($aEvent['oTable']->getHTMLFields() as $key => $field){
                    if(
                        empty($aEvent['aOrigData']['html_meta'][$key]) ||
                        $aEvent['aData'][$field] !== $aEvent['aOrigData']['html_meta'][$key]
                    ){
                        $isEqual = false;
                        break;
                    }
                }
            }

            // } Check HTML meta difference

            if(
                $mode == 'force' ||
                (empty($aEvent['aData']['is_kw_manual']) && $isEqual) ||
                $this->isHTMLMetaEmpty($aEvent, $aEvent['aData'])
            ){
                // Generate HTML meta {

                $body = AMI_Lib_String::stripTags($aEvent['aData'][$this->aFieldSources['body']]);
                if($body === '' && isset($aEvent['aData'][$this->aFieldSources['announce']])){
                    $body = AMI_Lib_String::stripTags($aEvent['aData'][$this->aFieldSources['announce']]);
                }
                $body = str_replace(
                    "\r\n",
                    ' ',
                    AMI::getResource('env/template_sys')->parseString('##--!ver=0200 rules="-SETVAR"--##' . $body)
                );

                $aHTMLMeta['title'] = $this->getHTMLMetaTitle(
                    $this->modId,
                    isset($aEvent['aData']['lang']) ? $aEvent['aData']['lang'] : 'en',
                    isset($aEvent['aData']['id_page']) ? $aEvent['aData']['id_page'] : 0,
                    $name,
                    $aEvent['oItem']
                );

                $aHTMLMeta['keywords'] = $this->getHTMLMetaKeywords(
                    $name . ' ' . $body,
                    $aEvent['aData']['lang'],
                    AMI::getOption('pages', 'min_keyword_length')
                );
                $aHTMLMeta['description']  = $this->getHTMLMetaDescription($name . '. ' . $body);
                $aHTMLMeta['is_kw_manual'] = '0';
                $aData = array();
                foreach($aEvent['oTable']->getHTMLFields() as $key => $field){
                    $aData[$field] = $aHTMLMeta[$key];
                    $aEvent['oItem']->setValue($field, $aHTMLMeta[$key]);
                }
                $aHTMLMeta['filled'] = (string)!$this->isHTMLMetaEmpty($aEvent, $aData);
                AMI::getSingleton('response')->addStatusMessage(
                    'status_apply_note_kwauto',
                    array(),
                    AMI_Response::STATUS_MESSAGE,
                    $this->modId
                );
                // } Generate HTML meta
            }else if($mode == 'auto' && !$isEqual){
                $aHTMLMeta['is_kw_manual'] = '1';
                $aHTMLMeta['filled'] = (string)!$this->isHTMLMetaEmpty($aEvent, $aEvent['aData']);
                $aEvent['oItem']->setValue('html_is_kw_manual', 1);
            }
            $bSaveMeta = true;
        }else if(
            AMI::issetOption($this->modId, 'keywords_generate') &&
            AMI::getOption($this->modId, 'keywords_generate') === 'none'
        ){
            $aHTMLMeta = array(
                'title' => $aEvent['aData']['html_title'],
                'keywords' => $aEvent['aData']['html_keywords'],
                'description' => $aEvent['aData']['html_description'],
                'is_kw_manual' => '1',
                'og_image' => isset($aEvent['aData']['og_image']) ? $aEvent['aData']['og_image'] : ''
            );
            foreach($aEvent['oTable']->getHTMLFields() as $key => $field){
                $aData[$field] = $aHTMLMeta[$key];
                $aEvent['oItem']->setValue($field, $aHTMLMeta[$key]);
            }
            $aHTMLMeta['filled'] = (string)!$this->isHTMLMetaEmpty($aEvent, $aData);
            $bSaveMeta = true;
        }
        if($bSaveMeta && isset($aHTMLMeta)){
            if(!isset($aHTMLMeta['og_image']) || !$aHTMLMeta['og_image']){
                $aHTMLMeta['og_image'] = '';
            }
            $aEvent['aData']['sm_data'] = DB_Query::getSnippet('%s')->q(serialize($aHTMLMeta));
        }
    }

    /**
     * Returns HTML meta title.
     *
     * @param  string $modId            Module id
     * @param  string $locale           Locale
     * @param  int    $pageId           Page id
     * @param  string $defaultTitle     Default title if there is no 'html_title_template' module option
     * @param  AMI_ModTableItem $oItem  Item
     * @return string
     */
    protected function getHTMLMetaTitle($modId, $locale, $pageId, $defaultTitle, AMI_ModTableItem $oItem){
        /**
         * @var AMI_iDB
         */
        $oDB = AMI::getSingleton('db');
        $pageModId = $modId;
        if(is_callable(array($oItem->getTable(), 'getSubItemsModId'))){
            $pageModId = $oItem->getTable()->getSubItemsModId();
        }
        if($pageId < 1){
            $oQuery =
                DB_Query::getSnippet(
                    "SELECT `id` " .
                    "FROM `cms_pages` " .
                    "WHERE `module_name` = %s AND `public` = 1 AND `hidden` = 0 AND `lang` = %s " .
                    "ORDER BY `id` LIMIT 1"
                )
                ->q($pageModId)
                ->q($locale);
            $pageId = $oDB->fetchValue($oQuery);
        }
        $currentPageName = '';
        if($pageId){
            $currentPageName = AMI_PageManager::getModPageName((int)$pageId, $pageModId, $locale);
        }
        $defaultTitle = AMI_Lib_String::htmlChars($defaultTitle);
        $siteTitle = AMI::getOption('pages', 'site_title');
        $aScope = array(
            'object_name'       => $defaultTitle,
            'site_title'        => $siteTitle[$locale],
            'company_name'      => AMI::getOption('core', 'company_name'),
            'current_page_name' => $currentPageName,
            'item_auto_title'   => $defaultTitle,
            'splitter'          =>
                AMI::issetOption($modId, 'html_title_splitter')
                    ? AMI::getOption($modId, 'html_title_splitter')
                    : ''
        );

        if(AMI::issetOption($modId, 'html_title_template')){
            $aEvent = array(
                'aScope' => &$aScope,
                'oItem'  => $oItem
            );

            AMI_Event::fire('on_generate_html_meta_title', $aEvent, $modId);

            $title =
                AMI::getResource('env/template_sys')->parseString(
                    AMI::getOption($modId, 'html_title_template'),
                    $aScope
                );
        }else{
            $title = $defaultTitle;
        }

        return trim(strip_tags($title));
    }

    /**
     * Returns HTML meta keywords.
     *
     * @param  string $source  Source string
     * @param  string $locale  Locale
     * @param  int $minLength  Minimum keyword length
     * @param  int $maxLength  Maximum result length
     * @return string
     */
    protected function getHTMLMetaKeywords($source, $locale, $minLength, $maxLength = 255){
        $res = mb_strtolower(AMI_Lib_String::stripTags($source));
        $res = preg_replace('/[^' . AMI_Lib_String::getValidSymbolsRegExp($locale) . ']+/u', ' ', $res);
        if($minLength > 1){
            $res = trim(
                preg_replace(
                    array(
                        '/(^| +)[' . AMI_Lib_String::getValidSymbolsRegExp($locale) . ']{1,' . ($minLength - 1) . '}(?=$| +)/us',
                        '/ +/'
                    ),
                    array(
                        '\\1',
                        ' '
                    ),
                    $res
                )
            );
        }
        $res = str_replace(' ', ', ', $res . ' ');
        $res = mb_substr($res, 0, $maxLength);
        $res = preg_replace('/,[^,]*$/', '', $res);
        return $res;
    }

    /**
     * Returns HTML meta description.
     *
     * @param  string $source  Source string
     * @param  int $maxLength  Maximum result length
     * @return string
     */
    protected function getHTMLMetaDescription($source, $maxLength = 255){
        return AMI_Lib_String::truncate(
            strip_tags(preg_replace("/['\"]+/", '', $source)),
            $maxLength,
            false,
            AMI::getOption('core', 'strip_strings_by_words'), ''
        );
    }

    /**
     * Prepare string for sublink generation.
     *
     * @param  string $str  Source string
     * @return string
     */
    protected function prepareSublink($str){
        return preg_replace('/^(https?|ftp):\//', '\1://', trim(preg_replace('/\/{2,}/', '/', $str), '/ '));
    }

    /**
     * Auto-generate sublink.
     *
     * @param  string $str  Source string for sublink generation
     * @param  string $langTransliterate  Locale for string transliteration
     * @param  string $forceLink  Force sublink
     * @param  array $aItemData  Current item data
     * @return string
     */
    protected function genAutoLink($str, $langTransliterate = "", $forceLink = "", array $aItemData = array()){
        $autoSublink = "";
        $splitter = "";
        if($this->prefixLink != "" && $this->prefixLink[mb_strlen($this->prefixLink) - 1] != "/"){
            $splitter = "/";
        }

        if($forceLink != ""){
            $autoSublink = $forceLink;
        }else{
            $autoSublink = $this->createSublink(AMI_Lib_String::transliterate($str, $langTransliterate), $aItemData);
        }

        return $this->prefixLink.$splitter.$autoSublink;
    }

    /**
     * Generate sublink from any string.
     *
     * @param  string $str  Source string for sublink generation
     * @param  array $aItemData  Current item data that should be saved
     * @return string
     */
    protected function createSublink($str, array $aItemData){

        $str = mb_strtolower(AMI_Lib_String::unhtmlEntities($str, true));

        // Cut superfluous characters, except enumerated and a space since it will be necessary further
        $str = preg_replace('/[^0-9a-zA-Z\-\_\/ ]+?/', '', $str);

        // Cut off extreme slashes and spaces from both sides
        $str = trim($str, ' /');

        // Replace spaces on dashes. Several spaces will be replaced with a one dash.
        $str = preg_replace('/[ -\/]+/', '-', $str);
        $oldStrLen = mb_strlen($str);

        // Keep $maxWords words
        $str = preg_replace('/^(((.*?)(-|_|\.|\/|$)){1,'.intval($this->maxWords).'}).*?$/', '\1', $str);

        // Remove a final separator
        if(mb_strlen($str) < $oldStrLen){
            $str = mb_substr($str, 0, mb_strlen($str) - 1);
        }

        // Add dash if the beginning of a line doesn't match with the necessary character
        if(!preg_match('/^[0-9a-zA-Z\-\_\.]$/', $str[0])){
            $str = "-".$str;
        }

        // Truncate string
        $str = mb_substr($str, 0, $this->maxLen);

        // date prefix feature
        if(AMI::issetOption($this->modId, 'add_date_prefix')){
            if(AMI::getOption($this->modId, 'add_date_prefix')){
                $prefixDate = null;
                if(isset($aItemData["date_created"])){
                    $prefixDate = $aItemData["date_created"];
                }else if(isset($aItemData["date"])){
                    $prefixDate = $aItemData["date"];
                }

                if($prefixDate != null){
                    $str = date('Y-m-d', strtotime($prefixDate)) . '/' . $str;
                }
            }
        }

        return $str;
    }

    // from "lib/func_url.php" {
    /**
     * Check sublink symbols.
     *
     * @param  string $str  Sublink
     * @return boolean
     */
    protected function checkURLSymbols($str){
        $res = true;

        $str = AMI_Lib_String::unhtmlEntities($str);

        if(mb_strpos($str, " ") !== false){
            $res = false;
        }elseif(!preg_match('/^[0-9a-zA-Z\-\_\.\/:\?\=\%\&\#]*$/s', $str)){
            // Find illegal chars
            $res = false;
        }elseif(($pos = mb_strpos($str, "?")) !== false && (mb_strpos($str, "?", $pos + 1)) !== false){
            // Check for more than one "?"
            $res = false;
        }

        if($res){
            if($pos === false){
                $pos = mb_strlen($str);
            }

            if((($ampos = mb_strpos($str, "&")) !== false && $ampos < $pos) || (($eqpos = mb_strpos($str, "=")) !== false &&  $eqpos < $pos)){
                // "&" and "=" cannot be located before the ?
                $res = false;
            }elseif(preg_match('/^\/+.*?$/', $str)){
                // Find begining slashes
                $res = false;
            }elseif(!preg_match('/^[0-9a-zA-Z\-\_\.]/s', $str)){
                // Find illegal begining char
                $res = false;
            }
        }

        return $res;
    }

    /**
     * Returns true if current HTML meta data is empty.
     *
     * @param  array $aEvent  Event data
     * @param  array $aData   Item data
     * @return bool
     */
    private function isHTMLMetaEmpty(array $aEvent, array $aData){
        $aFields = $aEvent['oTable']->getHTMLFields();
        unset($aFields['is_kw_manual']);
        foreach($aFields as $field){
            if(!empty($aData[$field])){
                return false;
            }
        }
        return true;
    }
}
