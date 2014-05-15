<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_CatModule_FilterAdm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.14.4
 */

/**
 * AMI_CatModule module admin filter component action controller.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.14.4
 */
abstract class AMI_CatModule_FilterAdm extends AMI_ModFilter{
    /**
     * Initialization.
     *
     * @return AMI_CatModule_FilterAdm
     */
    public function init(){
    	if(
            AMI::issetAndTrueOption('core', 'multi_page_allowed') &&
            AMI::issetAndTrueOption(AMI::getResourceModel($this->getModId(). '/table')->getSubItemsModId(), 'multi_page')
        ){
            AMI_Event::addHandler('on_filter_init', array($this, 'handleFilterPageIdInit'), $this->getModId());
    	}
        parent::init();
        return $this;
    }

    /**
     * Add page_id filter values.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleFilterPageIdInit($name, array $aEvent, $handlerModId, $srcModId){
        $aModulePages = array(
            array(
                'value' => 0,
                'caption' => 'common_items'
            )
        );

        $aPages = AMI_PageManager::getModPages(AMI::getResourceModel($srcModId. '/table')->getSubItemsModId(), AMI_Registry::get('lang_data'));
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
                'session_field' => true
            )
        );
        return $aEvent;
    }
}

/**
 * AMI_CatModule module item list component filter model.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @since      5.14.4
 */
abstract class AMI_CatModule_FilterModelAdm extends AMI_Filter{
    /**
     * Constructor.
     */
    public function __construct(){
        $this->addViewField(
            array(
                'name'          => 'header',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'header'
            )
        );
        $this->addViewField(
            array(
                'name'          => 'sticky',
                'type'          => 'checkbox',
                'flt_default'   => '0',
                'flt_condition' => '>=',
                'flt_column'    => 'sticky',
                'act_as_int'    => true,
                'disable_empty' => true
            )
        );
    }
}
