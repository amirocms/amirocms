<?php
/**
 * AmiMultifeeds5/Blog configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiMultifeeds5_Blog
 * @version   $Id: AmiMultifeeds5_BlogCat_Adm.php 42166 2013-10-10 12:29:32Z Leontiev Anton $
 * @since     x.x.x
 * @amidev
 */

/**
 * AmiMultifeeds5/Blog configuration category admin action controller.
 *
 * @package    Config_AmiMultifeeds5_Blog
 * @subpackage Controller
 * @resource   {$modId}_cat/module/controller/adm <code>AMI::getResource('{$modId}_cat/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_BlogCat_Adm extends Hyper_AmiMultifeeds5_Cat_Adm{
}

/**
 * AmiMultifeeds5/Blog configuration category model.
 *
 * @package    Config_AmiMultifeeds5_Blog
 * @subpackage Model
 * @resource   {$modId}_cat/module/model <code>AMI::getResourceModel('{$modId}_cat/module')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_BlogCat_State extends Hyper_AmiMultifeeds5_Cat_State{
}

/**
 * AmiMultifeeds5/Blog configuration category admin filter component action controller.
 *
 * @package    Config_AmiMultifeeds5_Blog
 * @subpackage Controller
 * @resource   {$modId}_cat/filter/controller/adm <code>AMI::getResource('{$modId}_cat/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_BlogCat_FilterAdm extends Hyper_AmiMultifeeds5_Cat_FilterAdm{
}

/**
 * AmiMultifeeds5/Blog configuration category item list component filter model.
 *
 * @package    Config_AmiMultifeeds5_Blog
 * @subpackage Model
 * @resource   {$modId}_cat/filter/model/adm <code>AMI::getResource('{$modId}_cat/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_BlogCat_FilterModelAdm extends Hyper_AmiMultifeeds5_Cat_FilterModelAdm{
}

/**
 * AmiMultifeeds5/Blog configuration category admin filter component view.
 *
 * @package    Config_AmiMultifeeds5_Blog
 * @subpackage View
 * @resource   {$modId}_cat/filter/view/adm <code>AMI::getResource('{$modId}_cat/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_BlogCat_FilterViewAdm extends Hyper_AmiMultifeeds5_Cat_FilterViewAdm{
}

/**
 * AmiMultifeeds5/Blog configuration category admin form component action controller.
 *
 * @package    Config_AmiMultifeeds5_News
 * @subpackage Controller
 * @resource   {$modId}_cat/form/controller/adm <code>AMI::getResource('{$modId}_cat/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_BlogCat_FormAdm extends Hyper_AmiMultifeeds5_Cat_FormAdm{
    /**
     * Initialization.
     *
     * @return AmiMultifeeds5_BlogCat_FormAdm
     */
    public function init(){
        AMI::setProperty($this->getModId(), 'picture_cat', 'photoalbum');
        return parent::init();
    }
}

/**
 * AmiMultifeeds5/Blog configuration category form component view.
 *
 * @package    Config_AmiMultifeeds5_Blog
 * @subpackage View
 * @resource   {$modId}_cat/form/view/adm <code>AMI::getResource('{$modId}_cat/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_BlogCat_FormViewAdm extends Hyper_AmiMultifeeds5_Cat_FormViewAdm{
}

/**
 * AmiMultifeeds5/Blog configuration category admin list component action controller.
 *
 * @package    Config_AmiMultifeeds5_Blog
 * @subpackage Controller
 * @resource   {$modId}_cat/list/controller/adm <code>AMI::getResource('{$modId}_cat/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_BlogCat_ListAdm extends Hyper_AmiMultifeeds5_Cat_ListAdm{
    /**
     * Initialization.
     *
     * @return AmiMultifeeds5_NewsCat_ListAdm
     */
    public function init(){
        $this->listActionsResId = $this->getModId() . '/list_actions/controller/adm';
        $this->listGrpActionsResId = $this->getModId() . '/list_group_actions/controller/adm';
        parent::init();
        return $this;
    }
}

/**
 * AmiMultifeeds5/Blog configuration category admin list component view.
 *
 * @package    Config_AmiMultifeeds5_Blog
 * @subpackage View
 * @resource   {$modId}_cat/list/view/adm <code>AMI::getResource('{$modId}_cat/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_BlogCat_ListViewAdm extends Hyper_AmiMultifeeds5_Cat_ListViewAdm{
}

/**
 * AmiMultifeeds5/Blog configuration category admin list actions controller.
 *
 * @category   AMI
 * @package    Config_AmiMultifeeds5_Blog
 * @subpackage Controller
 * @resource   {$modId}_cat/list_actions/controller/adm <code>AMI::getResource('{$modId}_cat/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_BlogCat_ListActionsAdm extends Hyper_AmiMultifeeds5_Cat_ListActionsAdm{
}

/**
 * AmiMultifeeds5/Blog configuration category admin list group actions controller.
 *
 * @category   AMI
 * @package    Config_AmiMultifeeds5_Blog
 * @subpackage Controller
 * @resource   {$modId}_cat/list_group_actions/controller/adm <code>AMI::getResource('{$modId}_cat/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_BlogCat_ListGroupActionsAdm extends Hyper_AmiMultifeeds5_Cat_ListGroupActionsAdm{
}
