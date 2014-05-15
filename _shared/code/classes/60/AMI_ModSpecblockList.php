<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModSpecblockList.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.14.8
 */

/**
 * Module specblock list component controller.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.14.8
 */
abstract class AMI_ModSpecblockList extends AMI_ModList{
    /**
     * Initialization.
     *
     * @return AMI_iModComponent
     * @see    AMI_Mod::init()
     */
    public function init(){
        $this->displayView();
        return $this;
    }

    /**
     * Returns component type.
     *
     * @return string
     */
    public function getType(){
        return 'specblock';
    }
}

/**
 * Module specblock list component view abstraction.
 *
 * @package    ModuleComponent
 * @subpackage View
 * @since      5.14.8
 */
abstract class AMI_ModSpecblockListView extends AMI_ModListView{
    /**
     * Constructor.
     */
    public function __construct(){
        // Specblock template, blockname and locale filename
        $this->tplBlockName   = $this->getModId() . '_specblock';
        $this->tplFileName    = AMI_iTemplate::TPL_MOD_PATH . '/' . $this->getModId() . '_specblock.tpl';
        $this->localeFileName = AMI_iTemplate::LNG_MOD_PATH . '/' . $this->getModId() . '_specblock.lng';

        parent::__construct();
    }
}
