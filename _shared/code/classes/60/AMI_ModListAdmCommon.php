<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModListAdmCommon.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.14.4
 */

/**
 * Common module admin list component action controller.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.14.4
 */
abstract class AMI_ModListAdmCommon extends AMI_ModListAdm{
    /**
     * Initialization.
     *
     * @return AMI_ModListAdmCommon
     */
    public function init(){
        // Default actions
        $this->addActions(array(self::REQUIRE_FULL_ENV . 'edit', self::REQUIRE_FULL_ENV . 'delete'));

        $this->addColActions(array(self::REQUIRE_FULL_ENV . 'public'), true);

        // Default group actions
        $this->addGroupActions(
            array(
                array(self::REQUIRE_FULL_ENV . 'public',                'common_section'),
                array(self::REQUIRE_FULL_ENV . 'unpublic',              'common_section'),
                array(self::REQUIRE_FULL_ENV . 'delete',                'delete_section'),
                array(self::REQUIRE_FULL_ENV . 'gen_sublink',           'meta_section'),
                array(self::REQUIRE_FULL_ENV . 'gen_html_meta',         'meta_section'),
                array(self::REQUIRE_FULL_ENV . 'gen_html_meta_force',   'meta_section'),
                array(self::REQUIRE_FULL_ENV . 'index_details',         'seo_section'),
                array(self::REQUIRE_FULL_ENV . 'no_index_details',      'seo_section'),
             // array(self::REQUIRE_FULL_ENV . 'repair',                'seo_section'),
            )
        );

        $this->addPositionActions(
            array(
                self::REQUIRE_FULL_ENV . 'move_up', self::REQUIRE_FULL_ENV . 'move_down', self::REQUIRE_FULL_ENV . 'move_top',
                self::REQUIRE_FULL_ENV . 'move_bottom', self::REQUIRE_FULL_ENV . 'repair'
            )
        );

        if($this->getGroupActions() && $this->getPositionActions()){
            $this->addGroupActions(
                array(
                    array(self::REQUIRE_FULL_ENV . 'move_position', 'move_section'),
                )
            );
        }

        parent::init();

        return $this;
    }
}
