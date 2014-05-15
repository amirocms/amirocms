<?php
/**
 * AmiModulesTemplates/Langs configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiModulesTemplates_Langs
 * @version   $Id: AmiModulesTemplates_Langs_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiModulesTemplates/Langs configuration admin action controller.
 *
 * @package    Config_AmiModulesTemplates_Langs
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiModulesTemplates_Langs_Adm extends Hyper_AmiModulesTemplates_Adm{
}

/**
 * AmiModulesTemplates/Langs configuration model.
 *
 * @package    Config_AmiModulesTemplates_Langs
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiModulesTemplates_Langs_State extends Hyper_AmiModulesTemplates_State{
}

/**
 * AmiModulesTemplates/Langs configuration admin filter component action controller.
 *
 * @package    Config_AmiModulesTemplates_Langs
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiModulesTemplates_Langs_FilterAdm extends Hyper_AmiModulesTemplates_FilterAdm{
}

/**
 * AmiModulesTemplates/Langs configuration item list component filter model.
 *
 * @package    Config_AmiModulesTemplates_Langs
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiModulesTemplates_Langs_FilterModelAdm extends Hyper_AmiModulesTemplates_FilterModelAdm{
}

/**
 * AmiModulesTemplates/Langs configuration admin filter component view.
 *
 * @package    Config_AmiModulesTemplates_Langs
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiModulesTemplates_Langs_FilterViewAdm extends Hyper_AmiModulesTemplates_FilterViewAdm{
}

/**
 * AmiModulesTemplates/Langs configuration admin form component action controller.
 *
 * @package    Config_AmiModulesTemplates_Langs
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiModulesTemplates_Langs_FormAdm extends Hyper_AmiModulesTemplates_FormAdm{
}

/**
 * AmiModulesTemplates/Langs configuration form component view.
 *
 * @package    Config_AmiModulesTemplates_Langs
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiModulesTemplates_Langs_FormViewAdm extends Hyper_AmiModulesTemplates_FormViewAdm{
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
 * AmiModulesTemplates/Langs configuration admin list component action controller.
 *
 * @package    Config_AmiModulesTemplates_Langs
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiModulesTemplates_Langs_ListAdm extends Hyper_AmiModulesTemplates_ListAdm{
}

/**
 * AmiModulesTemplates/Langs configuration admin list component view.
 *
 * @package    Config_AmiModulesTemplates_Langs
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiModulesTemplates_Langs_ListViewAdm extends Hyper_AmiModulesTemplates_ListViewAdm{
    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiModulesTemplates_Langs_ListViewAdm
     */
    public function init(){
        parent::init();
        return $this;
    }
}

/**
 * AmiModulesTemplates/Langs configuration module admin list actions controller.
 *
 * @package    Config_AmiModulesTemplates_Langs
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiModulesTemplates_Langs_ListActionsAdm extends Hyper_AmiModulesTemplates_ListActionsAdm{
}

/**
 * AmiModulesTemplates/Langs configuration module admin list group actions controller.
 *
 * @package    Config_AmiModulesTemplates_Langs
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiModulesTemplates_Langs_ListGroupActionsAdm extends Hyper_AmiModulesTemplates_ListGroupActionsAdm{
}
