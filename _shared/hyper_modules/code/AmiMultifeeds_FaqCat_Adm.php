<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiMultifeeds_FAQ
 * @version   $Id: AmiMultifeeds_FaqCat_Adm.php 45064 2013-12-05 09:27:10Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * FaqCat module admin action controller.
 *
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_FaqCat_Adm extends Hyper_AmiMultifeeds_Cat_Adm{
}

/**
 * FaqCat module admin filter component action controller.
 *
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_FaqCat_FilterAdm extends Hyper_AmiMultifeeds_Cat_FilterAdm{
}

/**
 * FaqCat module item list component filter model.
 *
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_FaqCat_FilterModelAdm extends Hyper_AmiMultifeeds_Cat_FilterModelAdm{
}

/**
 * FaqCat module admin filter component view.
 *
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_FaqCat_FilterViewAdm extends Hyper_AmiMultifeeds_Cat_FilterViewAdm{
}

/**
 * FaqCat module admin form component action controller.
 *
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_FaqCat_FormAdm extends Hyper_AmiMultifeeds_Cat_FormAdm{
}

/**
 * FaqCat module form component view.
 *
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_FaqCat_FormViewAdm extends Hyper_AmiMultifeeds_Cat_FormViewAdm{
}

/**
 * FaqCat module admin list component action controller.
 *
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_FaqCat_ListAdm extends Hyper_AmiMultifeeds_Cat_ListAdm{
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
 * AmiMultifeeds/Articles configuration category admin list component view.
 *
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage View
 * @since      6.0.2
 * @resource   {$modId}_cat/list/view/adm <code>AMI::getResource('{$modId}_cat/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_FaqCat_ListViewAdm extends Hyper_AmiMultifeeds_Cat_ListViewAdm{
}

/**
 * AmiMultifeeds/FAQ configuration category admin list actions controller.
 *
 * @category   AMI
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage Controller
 * @resource   {$modId}_cat/list_actions/controller/adm <code>AMI::getResource('{$modId}_cat/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_FaqCat_ListActionsAdm extends Hyper_AmiMultifeeds_Cat_ListActionsAdm{
}

/**
 * AmiMultifeeds/FAQ configuration category admin list group actions controller.
 *
 * @category   AMI
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage Controller
 * @resource   {$modId}_cat/list_group_actions/controller/adm <code>AMI::getResource('{$modId}_cat/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_FaqCat_ListGroupActionsAdm extends Hyper_AmiMultifeeds_Cat_ListGroupActionsAdm{
}

/**
 * AmiMultifeeds/FAQ configuration model.
 *
 * @package    Config_AmiMultifeeds_FAQ
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_FaqCat_State extends Hyper_AmiMultifeeds_Cat_State{
}
