<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiMultifeeds5_FAQ
 * @version   $Id: AmiMultifeeds5_FaqCat_Adm.php 42166 2013-10-10 12:29:32Z Leontiev Anton $
 * @since     x.x.x
 * @amidev
 */

/**
 * FaqCat module admin action controller.
 *
 * @package    Config_AmiMultifeeds5_FAQ
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_FaqCat_Adm extends Hyper_AmiMultifeeds5_Cat_Adm{
}

/**
 * FaqCat module admin filter component action controller.
 *
 * @package    Config_AmiMultifeeds5_FAQ
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_FaqCat_FilterAdm extends Hyper_AmiMultifeeds5_Cat_FilterAdm{
}

/**
 * FaqCat module item list component filter model.
 *
 * @package    Config_AmiMultifeeds5_FAQ
 * @subpackage Model
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_FaqCat_FilterModelAdm extends Hyper_AmiMultifeeds5_Cat_FilterModelAdm{
}

/**
 * FaqCat module admin filter component view.
 *
 * @package    Config_AmiMultifeeds5_FAQ
 * @subpackage View
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_FaqCat_FilterViewAdm extends Hyper_AmiMultifeeds5_Cat_FilterViewAdm{
}

/**
 * FaqCat module admin form component action controller.
 *
 * @package    Config_AmiMultifeeds5_FAQ
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_FaqCat_FormAdm extends Hyper_AmiMultifeeds5_Cat_FormAdm{
}

/**
 * FaqCat module form component view.
 *
 * @package    Config_AmiMultifeeds5_FAQ
 * @subpackage View
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_FaqCat_FormViewAdm extends Hyper_AmiMultifeeds5_Cat_FormViewAdm{
}

/**
 * FaqCat module admin list component action controller.
 *
 * @package    Config_AmiMultifeeds5_FAQ
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_FaqCat_ListAdm extends Hyper_AmiMultifeeds5_Cat_ListAdm{
    /**
     * Initialization.
     *
     * @return FaqCat_ListAdm
     */
    public function init(){
        parent::init();
        $this->dropActions(self::ACTION_GROUP, array('seo_section'));
        return $this;
    }
}

/**
 * AmiMultifeeds5/Articles configuration category admin list component view.
 *
 * @package    Config_AmiMultifeeds5_FAQ
 * @subpackage View
 * @since      x.x.x
 * @resource   {$modId}_cat/list/view/adm <code>AMI::getResource('{$modId}_cat/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_FaqCat_ListViewAdm extends Hyper_AmiMultifeeds5_Cat_ListViewAdm{
}

/**
 * AmiMultifeeds5/FAQ configuration category admin list actions controller.
 *
 * @category   AMI
 * @package    Config_AmiMultifeeds5_FAQ
 * @subpackage Controller
 * @resource   {$modId}_cat/list_actions/controller/adm <code>AMI::getResource('{$modId}_cat/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_FaqCat_ListActionsAdm extends Hyper_AmiMultifeeds5_Cat_ListActionsAdm{
}

/**
 * AmiMultifeeds5/FAQ configuration category admin list group actions controller.
 *
 * @category   AMI
 * @package    Config_AmiMultifeeds5_FAQ
 * @subpackage Controller
 * @resource   {$modId}_cat/list_group_actions/controller/adm <code>AMI::getResource('{$modId}_cat/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_FaqCat_ListGroupActionsAdm extends Hyper_AmiMultifeeds5_Cat_ListGroupActionsAdm{
}

/**
 * AmiMultifeeds5/FAQ configuration model.
 *
 * @package    Config_AmiMultifeeds5_FAQ
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_FaqCat_State extends Hyper_AmiMultifeeds5_Cat_State{
}
