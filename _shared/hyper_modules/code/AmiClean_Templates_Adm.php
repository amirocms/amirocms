<?php
/**
 * AmiPageManager/Templates configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiClean_Templates
 * @version   $Id: AmiClean_Templates_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiPageManager/Templates configuration admin action controller.
 *
 * @package    Config_AmiClean_Templates
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_Templates_Adm extends Hyper_AmiClean_Adm{
    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request
     * @param AMI_Response $oResponse  Response
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        parent::__construct($oRequest, $oResponse);
        $this->addComponents(array('filter', 'list', 'form'));
    }
}

/**
 * AmiPageManager/Templates configuration model.
 *
 * @package    Config_AmiClean_Templates
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_Templates_State extends Hyper_AmiClean_State{
}

/**
 * AmiPageManager/Templates configuration admin filter component action controller.
 *
 * @package    Config_AmiClean_Templates
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_Templates_FilterAdm extends Hyper_AmiClean_FilterAdm{
}

/**
 * AmiPageManager/Templates configuration item list component filter model.
 *
 * @package    Config_AmiClean_Templates
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 * @todo       Avoid hack or make separate hypermodule (was 'AmiPageManager')
 */
class AmiClean_Templates_FilterModelAdm extends AMI_Module_FilterModelAdm{
}

/**
 * AmiPageManager/Templates configuration admin filter component view.
 *
 * @package    Config_AmiClean_Templates
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_Templates_FilterViewAdm extends Hyper_AmiClean_FilterViewAdm{
}

/**
 * AmiPageManager/Templates configuration admin form component action controller.
 *
 * @package    Config_AmiClean_Templates
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_Templates_FormAdm extends Hyper_AmiClean_FormAdm{
}

/**
 * AmiPageManager/Templates configuration form component view.
 *
 * @package    Config_AmiClean_Templates
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_Templates_FormViewAdm extends Hyper_AmiClean_FormViewAdm{
    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){

        $this->addField(array('name' => 'id', 'type' => 'hidden'));
        $this->addField(array('name' => 'mod_action', 'value' => 'form_save', 'type' => 'hidden'));

        $this->addField(array('name' => 'date_created', 'type' => 'date', 'validate' => array('date')));
        $this->addField(array('name' => 'header', 'validate' => array('filled')));

        $this->addTabContainer('default_tabset', 'header.after');
        $this->addTab('body_tab', 'default_tabset', self::TAB_STATE_ACTIVE);
        $this->addField(array('name' => 'body', 'type' => 'htmleditor'));

        return parent::init();
    }
}

/**
 * AmiPageManager/Templates configuration admin list component action controller.
 *
 * @package    Config_AmiClean_Templates
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 * @todo       Avoid hack or make separate hypermodule (was 'AmiPageManager')
 */
class AmiClean_Templates_ListAdm extends AMI_Module_ListAdm{
    /**
     * Initialization.
     *
     * @return AmiClean_Templates_ListAdm
     */
    public function init(){
        parent::init();

        $this->dropActions(AMI_ModListAdm::ACTION_COLUMN, array('public'));
        $this->dropActions(self::ACTION_GROUP);

        return $this;
    }
}

/**
 * AmiPageManager/Templates configuration admin list component view.
 *
 * @package    Config_AmiClean_Templates
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 * @todo       Avoid hack or make separate hypermodule (was 'AmiPageManager')
 */
class AmiClean_Templates_ListViewAdm extends AMI_Module_ListViewAdm{
    /**
     * List default elemnts template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#list_header',
            '#columns', 'date_created', 'header', 'columns',
            '#actions', 'edit', 'delete', 'actions',
        'list_header'
    );

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiClean_Templates_ListViewAdm
     */
    public function init(){
        parent::init();

        // Init columns
        $this
            ->removeColumn('announce')
            ->removeColumn('position')
            ->removeColumn('public');

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
 * AmiPageManager/Templates configuration module admin list actions controller.
 *
 * @package    Config_AmiClean_Templates
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_Templates_ListActionsAdm extends Hyper_AmiClean_ListActionsAdm{
}

/**
 * AmiPageManager/Templates configuration module admin list group actions controller.
 *
 * @package    Config_AmiClean_Templates
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_Templates_ListGroupActionsAdm extends Hyper_AmiClean_ListGroupActionsAdm{
}
