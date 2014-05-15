<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module_AMI_Module
 * @version   $Id: AMI_Module_AsyncViewFrn.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Module front async component view.
 *
 * @package    Module_AMI_Module
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class AMI_Module_AsyncViewFrn extends AMI_View{
    /**
     * Template file name
     *
     * @var string
     */
    protected $tplFileName = '_shared/code/templates/modules/_mod.tpl';

    /**
     * Template block name
     *
     * @var string
     */
    protected $tplBlockName = 'async';

    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        $aScope = $this->getScope('mod_page');
        $aScope['head_scripts'] = AMI_Registry::get('oGUI')->getScripts();
        return $this->getTemplate()->parse($this->tplBlockName, $aScope);
    }
}
