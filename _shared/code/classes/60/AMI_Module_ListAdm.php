<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_Module_ListAdm.php 43458 2013-11-12 09:27:53Z Maximov Alexey $
 * @since     5.14.4
 */

/**
 * AMI_Module module admin list component action controller.
 *
 * @category   AMI
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.14.4
 */
abstract class AMI_Module_ListAdm extends AMI_ModListAdmCommon{
    /**
     * Initialization.
     *
     * @return AMI_Module_ListAdm
     */
    public function init(){
        parent::init();
        if(
            AMI::issetAndTrueOption('core', 'multi_page_allowed') &&
            (
                AMI::issetAndTrueOption($this->getModId(), "multi_page") &&
                !AMI::issetAndTrueOption($this->getModId(), "use_categories")
            )
        ){
            $this->addGroupActions(array(array(self::REQUIRE_FULL_ENV . 'id_page', 'id_page_section')));
            $this->addActionCallback('group', 'grp_id_page');
        }elseif(AMI::issetAndTrueOption($this->getModId(), "use_categories")){
            $this->addGroupActions(array(array(self::REQUIRE_FULL_ENV . 'id_cat', 'id_cat_section')));
            $this->addActionCallback('group', 'grp_id_cat');
        }
        return $this;
    }
}

/**
 * AMI_Module module list action controller.
 *
 * @category   AMI
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.14.4
 */
abstract class AMI_Module_ListActionsAdm extends AMI_ModListActions{
}

/**
 * AMI_Module module list group action controller.
 *
 * @category   AMI
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.14.4
 */
abstract class AMI_Module_ListGroupActionsAdm extends AMI_ModListGroupActions{
}

/**
 * AMI_CatModule module admin list component action controller.
 *
 * @category   AMI
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.14.4
 */
abstract class AMI_CatModule_ListAdm extends AMI_Module_ListAdm{
    /**
     * Initialization.
     *
     * @return AMI_CatModule_ListAdm
     */
    public function init(){
        parent::init();
        if(AMI::issetAndTrueOption($this->getModel()->getSubItemsModId(), "multi_page")){
            $this->addGroupActions(array(array(self::REQUIRE_FULL_ENV . 'id_page', 'id_page_section')));
            $this->addActionCallback('group', 'grp_id_page');
        }
        return $this;
    }
}

/**
 * AMI_CatModule module list action controller.
 *
 * @category   AMI
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.14.4
 */
abstract class AMI_CatModule_ListActionsAdm extends AMI_Module_ListActionsAdm{
}

/**
 * AMI_CatModule module list group action controller.
 *
 * @category   AMI
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.14.4
 */
abstract class AMI_CatModule_ListGroupActionsAdm extends AMI_Module_ListGroupActionsAdm{
}