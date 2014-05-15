<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_Module_FilterAdm.php 44589 2013-11-27 16:05:53Z Leontiev Anton $
 * @since     5.14.4
 */

/**
 * Common module admin filter component action controller.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.14.4
 */
abstract class AMI_Module_FilterAdm extends AMI_ModFilter{
    /**
     * Initialization.
     *
     * @return AMI_Module_FilterAdm
     */
    public function init(){
    	AMI_Event::addHandler('on_filter_init', array($this, 'handleFilterInit'), $this->getModId());
        parent::init();
        return $this;
    }

    /**
     * Add page_id/sticky filter values.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleFilterInit($name, array $aEvent, $handlerModId, $srcModId){
        if(
            AMI::issetAndTrueOption('core', 'multi_page_allowed') &&
            (
                AMI::issetAndTrueOption($this->getModId(), 'multi_page') &&
                !AMI::issetAndTrueOption($this->getModId(), 'use_categories')
            )
        ){
            $aModulePages = array(
                array(
                    'value' => 0,
                    'caption' => 'common_items'
                )
            );
            $aPages = AMI_PageManager::getModPages($this->getModId(), AMI_Registry::get('lang_data'));
            foreach($aPages as $aPage){
                $aModulePages[] = array(
                    'name'  => $aPage['name'],
                    'value' => $aPage['id']
                );
            }
            $aEvent['oFilter']->addViewField(
                array(
                    'name'          => 'id_page',
                    'type'          => 'select',
                    'flt_type'      => 'select',
                    'flt_default'   => '-1',
                    'flt_condition' => '=',
                    'flt_column'    => 'id_page',
                    'data'          => $aModulePages,
                    'not_selected'  => array('id' => '-1', 'caption' => 'all_pages'),
                    'act_as_int'    => true,
                    'session_field' => true
                )
            );
        }

        if(AMI::issetAndTrueProperty($this->getModId(), 'use_special_list_view')){
            $aEvent['oFilter']->addViewField(
                array(
                    'name'          => 'sticky',
                    'type'          => 'checkbox',
                    'flt_default'   => '0',
                    'flt_condition' => '=',
                    'flt_column'    => 'sticky',
                    'act_as_int'    => TRUE,
                    'disable_empty' => TRUE
                )
            );
            $aEvent['oFilter']->addViewField(
                array(
                    'name'          => 'public_direct_link',
                    'type'          => 'checkbox',
                    'flt_default'   => '0',
                    'flt_condition' => '=',
                    'flt_column'    => 'hide_in_list',
                    'act_as_int'    => TRUE,
                    'disable_empty' => TRUE
                )
            );
        }

        return $aEvent;
	}
}
