<?php
/**
 * AmiModulesTemplates/Templates configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiModulesTemplates_Templates
 * @version   $Id: AmiModulesTemplates_Templates_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiModulesTemplates/Templates configuration admin action controller.
 *
 * @package    Config_AmiModulesTemplates_Templates
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiModulesTemplates_Templates_Adm extends Hyper_AmiModulesTemplates_Adm{
}

/**
 * AmiModulesTemplates/Templates configuration model.
 *
 * @package    Config_AmiModulesTemplates_Templates
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiModulesTemplates_Templates_State extends Hyper_AmiModulesTemplates_State{
}

/**
 * AmiModulesTemplates/Templates configuration admin filter component action controller.
 *
 * @package    Config_AmiModulesTemplates_Templates
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiModulesTemplates_Templates_FilterAdm extends Hyper_AmiModulesTemplates_FilterAdm{
}

/**
 * AmiModulesTemplates/Templates configuration item list component filter model.
 *
 * @package    Config_AmiModulesTemplates_Templates
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiModulesTemplates_Templates_FilterModelAdm extends Hyper_AmiModulesTemplates_FilterModelAdm{
}

/**
 * AmiModulesTemplates/Templates configuration admin filter component view.
 *
 * @package    Config_AmiModulesTemplates_Templates
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiModulesTemplates_Templates_FilterViewAdm extends Hyper_AmiModulesTemplates_FilterViewAdm{
}

/**
 * AmiModulesTemplates/Templates configuration admin form component action controller.
 *
 * @package    Config_AmiModulesTemplates_Templates
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiModulesTemplates_Templates_FormAdm extends Hyper_AmiModulesTemplates_FormAdm{
}

/**
 * AmiModulesTemplates/Templates configuration form component view.
 *
 * @package    Config_AmiModulesTemplates_Templates
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiModulesTemplates_Templates_FormViewAdm extends Hyper_AmiModulesTemplates_FormViewAdm{
    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        return parent::init();
    }
}

/**
 * AmiModulesTemplates/Templates configuration admin list component action controller.
 *
 * @package    Config_AmiModulesTemplates_Templates
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiModulesTemplates_Templates_ListAdm extends Hyper_AmiModulesTemplates_ListAdm{
}

/**
 * AmiModulesTemplates/Templates configuration admin list component view.
 *
 * @package    Config_AmiModulesTemplates_Templates
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiModulesTemplates_Templates_ListViewAdm extends Hyper_AmiModulesTemplates_ListViewAdm{
    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiModulesTemplates_Templates_ListViewAdm
     */
    public function init(){
        parent::init();
        return $this;
    }
}

/**
 * AmiModulesTemplates/Templates configuration module admin list actions controller.
 *
 * @package    Config_AmiModulesTemplates_Templates
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiModulesTemplates_Templates_ListActionsAdm extends Hyper_AmiModulesTemplates_ListActionsAdm{
}

/**
 * AmiModulesTemplates/Templates configuration module admin list group actions controller.
 *
 * @package    Config_AmiModulesTemplates_Templates
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiModulesTemplates_Templates_ListGroupActionsAdm extends Hyper_AmiModulesTemplates_ListGroupActionsAdm{
}
