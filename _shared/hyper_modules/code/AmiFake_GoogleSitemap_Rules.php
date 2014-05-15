<?php
/**
 * AmiFake/GoogleSitemap configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiFake_GoogleSitemap
 * @version   $Id: AmiFake_GoogleSitemap_Rules.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiFake/GoogleSitemap configuration rules.
 *
 * @package    Config_AmiFake_GoogleSitemap
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiFake_GoogleSitemap_Rules extends AMI_ModRules{
    /**
     * Handler for ##modId##:disableSitemaps callback.
     *
     * @param mixed $callbackData   Data for callback
     * @param mixed &$optionsData    Options data
     * @param string $callbackMode  Callback mode
     * @param mixed &$result         Result data
     * @param array &$aData          Data
     * @return bool
     */
    function disableSitemaps($callbackData, &$optionsData, $callbackMode, &$result, array &$aData){
        if($callbackData["value"]){
            @clearstatcache;
            if(@is_file($GLOBALS['MODULE_PICTURES_PATH']."sitemap_index.xml")){
                @unlink($GLOBALS['MODULE_PICTURES_PATH']."sitemap_index.xml");
            }

            $GLOBALS['Core']->DeleteOption("google_sitemap", "gen_sitemap");
            $GLOBALS['Core']->DeleteOption("google_sitemap", "send_sitemap");

            $oQuery = DB_Query::getSnippet('DELETE FROM cms_google_sitemap');
            $oDB = AMI::getSingleton('db');
            $oDB->query($oQuery);

        }

        switch($callbackMode){
            case "getvalue":
                $result = $callbackData["value"];
                break;
            case "getallvalues":
            case "correctvalue":
            case "apply":
                break;
        }

        return true;
    }


/*
    public function GetSearchPagesCB($callbackData, &$optionsData, $callbackMode, &$result, array &$aData){
        $oTpl = AMI::getSingleton('env/template_sys');
        $aLocale = $oTpl->parseLocale('templates/lang/main.lng');
        switch($callbackMode){
            case self::GET_ALL:
                if(!$callbackData["value"]){
                    $callbackData["value"] = $callbackData["default_value"];
                }
                $result = Array();
                $result[] = array(
                    'name' => 'all',
                    'caption' => $aLocale['all_site_search'],
                    'selected' => in_array('all', $callbackData["value"]) ? 1 : 0
                );
                $oFilter = AMI::getResource('search/filter/model/adm');
                $aPages = $oFilter->getSearchPages();
                foreach($aPages as $aPage){
                    $result[] = array(
                        'name' => $aPage['value'],
                        'caption' => $aPage['name'],
                        'selected' => ($aPage['value'] == $callbackData['value']) ? 1 : 0
                    );
                }
                break;
            case self::CORRECT:
                break;
            case self::GET_VAL:
                $res = $callbackData["value"];
                break;
        }
        return true;
    }
*/
}
