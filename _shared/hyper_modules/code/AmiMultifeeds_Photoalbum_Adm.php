<?php
/**
 * AmiMultifeeds/PhotoGallery configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiMultifeeds_PhotoGallery
 * @version   $Id: AmiMultifeeds_Photoalbum_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiMultifeeds/PhotoGallery configuration module admin action controller.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Photoalbum_Adm extends Hyper_AmiMultifeeds_Adm{
    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request
     * @param AMI_Response $oResponse  Response
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        $aExtensions = $this->getModState()->getOption('extensions');
        if(!is_array($aExtensions)){
            $aExtensions = array();
        }
        if(!in_array('ext_images', $aExtensions)){
            $aExtensions[] = 'ext_images';
            AMI::setOption($this->getModId(), 'extensions', $aExtensions);
        }

        parent::__construct($oRequest, $oResponse);
    }
}

/**
 * AmiMultifeeds/PhotoGallery configuration module model.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage Model
 * @amidev     Temporary
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 */
class AmiMultifeeds_Photoalbum_State extends Hyper_AmiMultifeeds_State{
}

/**
 * AmiMultifeeds/PhotoGallery configuration module admin filter component action controller.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage Controller
 * @amidev     Temporary
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 */
class AmiMultifeeds_Photoalbum_FilterAdm extends Hyper_AmiMultifeeds_FilterAdm{
}

/**
 * AmiMultifeeds/PhotoGallery configuration module item list component filter model.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage Model
 * @amidev     Temporary
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 */
class AmiMultifeeds_Photoalbum_FilterModelAdm extends Hyper_AmiMultifeeds_FilterModelAdm{
}

/**
 * AmiMultifeeds/PhotoGallery configuration admin filter component view.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Photoalbum_FilterViewAdm extends Hyper_AmiMultifeeds_FilterViewAdm{
}

/**
 * AmiMultifeeds/PhotoGallery configuration admin form component action controller.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Photoalbum_FormAdm extends Hyper_AmiMultifeeds_FormAdm{
}

/**
 * AmiMultifeeds/PhotoGallery configuration module form component view.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Photoalbum_FormViewAdm extends Hyper_AmiMultifeeds_FormViewAdm{
    /**
     * Common fields validators
     *
     * @var array
     */
    protected $aCommonFieldsValidators = array();

    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        $this->addField(array('name' => 'date_created', 'type' => 'date'));
        $this->addField(array('name' => 'source', 'position' => 'date_created.after'));
        $this->addField(array('name' => 'author', 'position' => 'date_created.after'));
        $this->addScriptFile('_admin/' . $GLOBALS['CURRENT_SKIN_PATH'] . '_js/photoalbum.js');

        return parent::init();
    }
}

/**
 * AmiMultifeeds/PhotoGallery configuration module admin list component action controller.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Photoalbum_ListAdm extends Hyper_AmiMultifeeds_ListAdm{
    /**
     * Initialization.
     *
     * @return AMI_Module_ListAdm
     */
    public function init(){
        $this->listActionsResId = $this->getModId() . '/list_actions/controller/adm';
        $this->listGrpActionsResId = $this->getModId() . '/list_group_actions/controller/adm';
        return parent::init();
    }
}


/**
 * AmiMultifeeds/PhotoGallery configuration module admin list component view.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Photoalbum_ListViewAdm extends Hyper_AmiMultifeeds_ListViewAdm{
    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return Photoalbum_ListViewAdm
     */
    public function init(){
        parent::init();
        $this->putPlaceholder('picture', 'common.end');
        $this->addColumnType('date_created', 'date');

        // Format 'date_created' column in local date format
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
 * AmiMultifeeds/PhotoGallery configuration module admin list actions controller.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Photoalbum_ListActionsAdm extends Hyper_AmiMultifeeds_ListActionsAdm{
}

/**
 * AmiMultifeeds/PhotoGallery configuration module admin list group actions controller.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Photoalbum_ListGroupActionsAdm extends Hyper_AmiMultifeeds_ListGroupActionsAdm{
}
