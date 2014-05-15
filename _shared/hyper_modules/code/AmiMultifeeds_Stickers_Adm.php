<?php
/**
 * AmiMultifeeds/Stickers configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiMultifeeds_Stickers
 * @version   $Id: AmiMultifeeds_Stickers_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiMultifeeds/Stickers configuration admin action controller.
 *
 * @package    Config_AmiMultifeeds_Stickers
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Stickers_Adm extends Hyper_AmiMultifeeds_Adm{
}

/**
 * AmiMultifeeds/Stickers configuration model.
 *
 * @package    Config_AmiMultifeeds_Stickers
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Stickers_State extends Hyper_AmiMultifeeds_State{
}

/**
 * AmiMultifeeds/Stickers configuration admin filter component action controller.
 *
 * @package    Config_AmiMultifeeds_Stickers
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Stickers_FilterAdm extends Hyper_AmiMultifeeds_FilterAdm{
}

/**
 * AmiMultifeeds/Stickers configuration item list component filter model.
 *
 * @package    Config_AmiMultifeeds_Stickers
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Stickers_FilterModelAdm extends Hyper_AmiMultifeeds_FilterModelAdm{
    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();

        $this->addViewField(
            array(
                'name'          => 'public',
                'type'          => 'checkbox',
                'flt_default'   => '0',
                'flt_condition' => '>=',
                'act_as_int'    => TRUE,
                'disable_empty' => TRUE
            )
        );
    }
}

/**
 * AmiMultifeeds/Stickers configuration admin filter component view.
 *
 * @package    Config_AmiMultifeeds_Stickers
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Stickers_FilterViewAdm extends Hyper_AmiMultifeeds_FilterViewAdm{
}

/**
 * AmiMultifeeds/Stickers configuration admin form component action controller.
 *
 * @package    Config_AmiMultifeeds_Stickers
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Stickers_FormAdm extends Hyper_AmiMultifeeds_FormAdm{
}

/**
 * AmiMultifeeds/Stickers configuration form component view.
 *
 * @package    Config_AmiMultifeeds_Stickers
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Stickers_FormViewAdm extends Hyper_AmiMultifeeds_FormViewAdm{
    /**
     * Used tabs list
     *
     * @var array
     */
    protected $aUsedTabs = array('announce');

    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        parent::init();
        $this->addField(array('name' => 'date_created', 'type' => 'date'));
        return $this;
    }
}

/**
 * AmiMultifeeds/Stickers configuration admin list component action controller.
 *
 * @package    Config_AmiMultifeeds_Stickers
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Stickers_ListAdm extends Hyper_AmiMultifeeds_ListAdm{
    /**
     * Initialization.
     *
     * @return AMI_Module_ListAdm
     */
    public function init(){
        parent::init();

        $this->dropActions(
            AMI_ModListAdm::ACTION_GROUP,
            array(
                'gen_sublink',
                'gen_html_meta',
                'gen_html_meta_force',
                'index_details',
                'no_index_details',
            )
        );
        return $this;
    }
}

/**
 * AmiMultifeeds/Stickers configuration admin list component view.
 *
 * @package    Config_AmiMultifeeds_Stickers
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Stickers_ListViewAdm extends Hyper_AmiMultifeeds_ListViewAdm{
    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiMultifeeds_Articles_ListViewAdm
     */
    public function init(){
        parent::init();

        $this->addColumnType('date_created', 'date');
        $this->formatColumn(
            'date_created',
            array($this, 'fmtDateTime'),
            array(
                'format' => AMI_Lib_Date::FMT_DATE
            )
        );
        return $this;
    }
}

/**
 * AmiMultifeeds/Stickers configuration module admin list actions controller.
 *
 * @package    Config_AmiMultifeeds_Stickers
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Stickers_ListActionsAdm extends Hyper_AmiMultifeeds_ListActionsAdm{
}

/**
 * AmiMultifeeds/Stickers configuration module admin list group actions controller.
 *
 * @package    Config_AmiMultifeeds_Stickers
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Stickers_ListGroupActionsAdm extends Hyper_AmiMultifeeds_ListGroupActionsAdm{
}
