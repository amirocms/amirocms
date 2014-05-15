<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module_AMI_Module
 * @version   $Id: AMI_Module_ListFrn.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AMI_Module module front list component action controller.
 *
 * @category   AMI
 * @package    Module_AMI_Module
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class AMI_Module_ListFrn extends AMI_ModListFrn{
    /**
     * Initialization.
     *
     * @return AMI_ModListAdmCommon
     */
    public function init(){
        // Default actions
        $this->addActions(array(self::REQUIRE_FULL_ENV . 'edit', self::REQUIRE_FULL_ENV . 'delete'));

        // Default group actions
        $this->addGroupActions(
            array(
                array(self::REQUIRE_FULL_ENV . 'delete', 'delete_section'),
            )
        );

        parent::init();

        return $this;
    }
}

/**
 * AMI_Module module list action controller.
 *
 * @category   AMI
 * @package    Module_AMI_Module
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class AMI_Module_ListActionsFrn extends AMI_ModListActions{
}

/**
 * AMI_Module module list group action controller.
 *
 * @category   AMI
 * @package    Module_AMI_Module
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class AMI_Module_ListGroupActionsFrn extends AMI_ModListGroupActions{
}
