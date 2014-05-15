<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Page
 * @version   $Id: AMI_PageViewAdm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Admin module page view.
 *
 * @package    Page
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
final class AMI_PageViewAdm extends AMI_View{
    /**
     * Template file name
     *
     * @var string
     */
    protected $tplFileName = 'templates/modules/_mod.tpl';

    /**
     * Template block name
     *
     * @var string
     */
    protected $tplBlockName = 'mod_page';

    /**
     * Locale file name
     *
     * @var string
     */
    protected $localeFileName = 'templates/lang/modules/_page_adm.lng';

    /**
     * AMI_View::get() implementation.
     *
     * @return string
     */
    public function get(){
        $aScope = $this->getScope('mod_page');
        $aScope['head_scripts'] = AMI_Registry::get('oGUI')->getScripts();
        $aScope['active_lang'] = AMI_Registry::get('lang');
        return $this->getTemplate()->parse($this->tplBlockName, $aScope);
    }

    /**
     * Returns module id.
     *
     * @return string
     */
    protected function getModId(){
        return 'page_adm';
    }
}
