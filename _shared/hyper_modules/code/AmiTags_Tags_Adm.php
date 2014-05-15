<?php
/**
 * AmiTags/Tags configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiTags_Tags
 * @version   $Id: AmiTags_Tags_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiTags/Tags configuration admin action controller.
 *
 * @package    Config_AmiTags_Tags
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiTags_Tags_Adm extends Hyper_AmiTags_Adm{
}

/**
 * AmiTags/Tags configuration model.
 *
 * @package    Config_AmiTags_Tags
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiTags_Tags_State extends Hyper_AmiTags_State{
}

/**
 * AmiTags/Tags configuration admin filter component action controller.
 *
 * @package    Config_AmiTags_Tags
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiTags_Tags_FilterAdm extends Hyper_AmiTags_FilterAdm{
}

/**
 * AmiTags/Tags configuration item list component filter model.
 *
 * @package    Config_AmiTags_Tags
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiTags_Tags_FilterModelAdm extends Hyper_AmiTags_FilterModelAdm{
}

/**
 * AmiTags/Tags configuration admin filter component view.
 *
 * @package    Config_AmiTags_Tags
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiTags_Tags_FilterViewAdm extends Hyper_AmiTags_FilterViewAdm{
}

/**
 * AmiTags/Tags configuration admin form component action controller.
 *
 * @package    Config_AmiTags_Tags
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiTags_Tags_FormAdm extends Hyper_AmiTags_FormAdm{
}

/**
 * AmiTags/Tags configuration form component view.
 *
 * @package    Config_AmiTags_Tags
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiTags_Tags_FormViewAdm extends Hyper_AmiTags_FormViewAdm{
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
 * AmiTags/Tags configuration admin list component action controller.
 *
 * @package    Config_AmiTags_Tags
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiTags_Tags_ListAdm extends Hyper_AmiTags_ListAdm{
    /**
     * Initialization.
     *
     * @return AmiTags_Tags_ListAdm
     */
    public function init(){
        parent::init();

        $this->dropActions(
            AMI_ModListAdm::ACTION_GROUP,
            array(
                'public',
                'unpublic',
                'index_details',
                'no_index_details',
            )
        );
        $this->dropActions(
            AMI_ModListAdm::ACTION_COLUMN,
            array(
                'public',
            )
        );

        return $this;
    }
}

/**
 * AmiTags/Tags configuration admin list component view.
 *
 * @package    Config_AmiTags_Tags
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiTags_Tags_ListViewAdm extends Hyper_AmiTags_ListViewAdm{
}

/**
 * AmiTags/Tags configuration module admin list actions controller.
 *
 * @package    Config_AmiTags_Tags
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiTags_Tags_ListActionsAdm extends Hyper_AmiTags_ListActionsAdm{
}

/**
 * AmiTags/Tags configuration module admin list group actions controller.
 *
 * @package    Config_AmiTags_Tags
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiTags_Tags_ListGroupActionsAdm extends Hyper_AmiTags_ListGroupActionsAdm{
}
