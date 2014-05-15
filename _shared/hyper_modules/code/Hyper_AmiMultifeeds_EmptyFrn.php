<?php
/**
 * AmiMultifeeds hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiMultifeeds
 * @version   $Id: Hyper_AmiMultifeeds_EmptyFrn.php 45064 2013-12-05 09:27:10Z Leontiev Anton $
 * @since     6.0.2
 */

/**
 * Multifeeds hypermodule front empty body type action controller.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage Controller
 * @since      6.0.2
 */
class Hyper_AmiMultifeeds_EmptyFrn extends AMI_ModEmptyFrn{
}

/**
 * Multifeeds hypermodule front cats body type view.
 *
 * @package    Hyper_AmiMultifeeds
 * @subpackage View
 * @since      6.0.2
 */
class Hyper_AmiMultifeeds_EmptyViewFrn extends AMI_ModEmptyView{
    /**
     * Constructor.
     */
    public function __construct(){
        $modId = $this->getModId();

        $this->tplFileName = AMI_iTemplate::TPL_MOD_PATH . '/' . $modId . '.tpl';;
        $this->tplBlockName = $modId . '_empty';
        $this->localeFileName = AMI_iTemplate::LNG_MOD_PATH . '/' . $modId . '.lng';

        parent::__construct();
    }
}
