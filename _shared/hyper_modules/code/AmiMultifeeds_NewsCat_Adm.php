<?php
/**
 * AmiMultifeeds/News configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiMultifeeds_News
 * @version   $Id: AmiMultifeeds_NewsCat_Adm.php 45064 2013-12-05 09:27:10Z Leontiev Anton $
 * @since     6.0.2
 */

/**
 * AmiMultifeeds/News configuration category admin action controller.
 *
 * @package    Config_AmiMultifeeds_News
 * @subpackage Controller
 * @resource   {$modId}_cat/module/controller/adm <code>AMI::getResource('{$modId}_cat/module/controller/adm')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_NewsCat_Adm extends Hyper_AmiMultifeeds_Cat_Adm{
}

/**
 * AmiMultifeeds/News configuration category model.
 *
 * @package    Config_AmiMultifeeds_News
 * @subpackage Model
 * @resource   {$modId}_cat/module/model <code>AMI::getResourceModel('{$modId}_cat/module')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_NewsCat_State extends Hyper_AmiMultifeeds_Cat_State{
}

/**
 * AmiMultifeeds/News configuration category admin filter component action controller.
 *
 * @package    Config_AmiMultifeeds_News
 * @subpackage Controller
 * @resource   {$modId}_cat/filter/controller/adm <code>AMI::getResource('{$modId}_cat/filter/controller/adm')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_NewsCat_FilterAdm extends Hyper_AmiMultifeeds_Cat_FilterAdm{
}

/**
 * AmiMultifeeds/News configuration category item list component filter model.
 *
 * @package    Config_AmiMultifeeds_News
 * @subpackage Model
 * @resource   {$modId}_cat/filter/model/adm <code>AMI::getResource('{$modId}_cat/filter/model/adm')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_NewsCat_FilterModelAdm extends Hyper_AmiMultifeeds_Cat_FilterModelAdm{
}

/**
 * AmiMultifeeds/News configuration category admin filter component view.
 *
 * @package    Config_AmiMultifeeds_News
 * @subpackage View
 * @resource   {$modId}_cat/filter/view/adm <code>AMI::getResource('{$modId}_cat/filter/view/adm')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_NewsCat_FilterViewAdm extends Hyper_AmiMultifeeds_Cat_FilterViewAdm{
}

/**
 * AmiMultifeeds/News configuration category admin form component action controller.
 *
 * @package    Config_AmiMultifeeds_News
 * @subpackage Controller
 * @resource   {$modId}_cat/form/controller/adm <code>AMI::getResource('{$modId}_cat/form/controller/adm')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_NewsCat_FormAdm extends Hyper_AmiMultifeeds_Cat_FormAdm{
    /**
     * Initialization.
     *
     * @return AmiMultifeeds_NewsCat_FormAdm
     */
    public function init(){
        AMI::setProperty($this->getModId(), 'picture_cat', 'photoalbum');
        return parent::init();
    }
}

/**
 * AmiMultifeeds/News configuration category form component view.
 *
 * @package    Config_AmiMultifeeds_News
 * @subpackage View
 * @resource   {$modId}_cat/form/view/adm <code>AMI::getResource('{$modId}_cat/form/view/adm')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_NewsCat_FormViewAdm extends Hyper_AmiMultifeeds_Cat_FormViewAdm{
}

/**
 * AmiMultifeeds/News configuration category admin list component action controller.
 *
 * @package    Config_AmiMultifeeds_News
 * @subpackage Controller
 * @resource   {$modId}_cat/list/controller/adm <code>AMI::getResource('{$modId}_cat/list/controller/adm')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_NewsCat_ListAdm extends Hyper_AmiMultifeeds_Cat_ListAdm{
    /**
     * Initialization.
     *
     * @return AmiMultifeeds_NewsCat_ListAdm
     */
    public function init(){
        $this->listActionsResId = $this->getModId() . '/list_actions/controller/adm';
        $this->listGrpActionsResId = $this->getModId() . '/list_group_actions/controller/adm';
        parent::init();
        return $this;
    }
}

/**
 * AmiMultifeeds/News configuration category admin list component view.
 *
 * @package    Config_AmiMultifeeds_News
 * @subpackage View
 * @since      6.0.2
 * @resource   {$modId}_cat/list/view/adm <code>AMI::getResource('{$modId}_cat/list/view/adm')*</code>
 */
class AmiMultifeeds_NewsCat_ListViewAdm extends Hyper_AmiMultifeeds_Cat_ListViewAdm{
}

/**
 * AmiMultifeeds/News configuration category admin list actions controller.
 *
 * @category   AMI
 * @package    Config_AmiMultifeeds_News
 * @subpackage Controller
 * @resource   {$modId}_cat/list_actions/controller/adm <code>AMI::getResource('{$modId}_cat/list_actions/controller/adm')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_NewsCat_ListActionsAdm extends Hyper_AmiMultifeeds_Cat_ListActionsAdm{
}

/**
 * AmiMultifeeds/News configuration category admin list group actions controller.
 *
 * @category   AMI
 * @package    Config_AmiMultifeeds_News
 * @subpackage Controller
 * @resource   {$modId}_cat/list_group_actions/controller/adm <code>AMI::getResource('{$modId}_cat/list_group_actions/controller/adm')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_NewsCat_ListGroupActionsAdm extends Hyper_AmiMultifeeds_Cat_ListGroupActionsAdm{
}
