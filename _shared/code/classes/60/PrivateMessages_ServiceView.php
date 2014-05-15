<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiAsync_PrivateMessages
 * @version   $Id: PrivateMessages_ServiceView.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Private Messages service view.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class PrivateMessages_ServiceView extends AMI_View{
    /**
     * Main block name
     *
     * @var string
     */
    protected $tplFileName = null;

    /**
     * Locale file name
     *
     * @var string
     */
    protected $localeFileName = null;

    /**
     * Stub function.
     *
     * @return null
     */
    public function get(){
        return null;
    }

    /**
     * Returns link to message page to user by user's id.
     *
     * @param  int    $memberId  Member ID
     * @param  string $locale    Locale
     * @return string
     */
    public function getUserSendMessageLink($memberId, $locale = 'en'){
        if($memberId){
    	    $isMultilang = AMI::getOption('core', 'allow_multi_lang');
    	    $string =
                ($isMultilang ? $locale . '/' : '') .
                AMI_PageManager::getModLink('private_messages', $locale) .
                '?recipient=' . $memberId;
        }else{
            $string = '';
        }
        return $string;
    }
}
