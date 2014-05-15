<?php
/**
 * AmiMultifeeds5/PhotoGallery configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiMultifeeds5_PhotoGallery
 * @version   $Id: AmiMultifeeds5_PhotoalbumCat_Adm.php 42166 2013-10-10 12:29:32Z Leontiev Anton $
 * @since     x.x.x
 * @amidev
 */

/**
 * AmiMultifeeds5/PhotoGallery configuration admin action controller.
 *
 * @package    Config_AmiMultifeeds5_PhotoGallery
 * @subpackage Controller
 * @resource   {$modId}_cat/module/controller/adm <code>AMI::getResource('{$modId}_cat/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_PhotoalbumCat_Adm extends Hyper_AmiMultifeeds5_Cat_Adm{
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
 * AmiMultifeeds5/PhotoGallery configuration category module model.
 *
 * @package    Config_AmiMultifeeds5_PhotoGallery
 * @resource   {$modId}_cat/module/model <code>AMI::getResourceModel('{$modId}_cat/module')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_PhotoalbumCat_State extends Hyper_AmiMultifeeds5_Cat_State{
}

/**
 * AmiMultifeeds5/PhotoGallery configuration admin filter component action controller.
 *
 * @package    Config_AmiMultifeeds5_PhotoGallery
 * @subpackage Controller
 * @resource   {$modId}_cat/filter/controller/adm <code>AMI::getResource('{$modId}_cat/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_PhotoalbumCat_FilterAdm extends Hyper_AmiMultifeeds5_Cat_FilterAdm{
}

/**
 * AmiMultifeeds5/PhotoGallery configuration item list component filter model.
 *
 * @package    Config_AmiMultifeeds5_PhotoGallery
 * @subpackage Model
 * @resource   {$modId}_cat/filter/model/adm <code>AMI::getResource('{$modId}_cat/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_PhotoalbumCat_FilterModelAdm extends Hyper_AmiMultifeeds5_Cat_FilterModelAdm{
}

/**
 * AmiMultifeeds5/PhotoGallery configuration admin filter component view.
 *
 * @package    Config_AmiMultifeeds5_PhotoGallery
 * @subpackage View
 * @resource   {$modId}_cat/filter/view/adm <code>AMI::getResource('{$modId}_cat/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_PhotoalbumCat_FilterViewAdm extends Hyper_AmiMultifeeds5_Cat_FilterViewAdm{
}

/**
 * AmiMultifeeds5/PhotoGallery configuration admin form component action controller.
 *
 * @package    Config_AmiMultifeeds5_PhotoGallery
 * @subpackage Controller
 * @resource   {$modId}_cat/form/controller/adm <code>AMI::getResource('{$modId}_cat/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_PhotoalbumCat_FormAdm extends Hyper_AmiMultifeeds5_Cat_FormAdm{
    /**
     * Initialization.
     *
     * @return PhotoalbumCat_FormAdm
     */
    public function init(){
        AMI::setProperty($this->getModId(), 'picture_cat', 'photoalbum');
        return parent::init();
    }
}

/**
 * AmiMultifeeds5/PhotoGallery configuration form component view.
 *
 * @package    Config_AmiMultifeeds5_PhotoGallery
 * @subpackage View
 * @resource   {$modId}_cat/form/view/adm <code>AMI::getResource('{$modId}_cat/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_PhotoalbumCat_FormViewAdm extends Hyper_AmiMultifeeds5_Cat_FormViewAdm{
}

/**
 * AmiMultifeeds5/PhotoGallery configuration admin list component action controller.
 *
 * @package    Config_AmiMultifeeds5_PhotoGallery
 * @subpackage Controller
 * @resource   {$modId}_cat/list/controller/adm <code>AMI::getResource('{$modId}_cat/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_PhotoalbumCat_ListAdm extends Hyper_AmiMultifeeds5_Cat_ListAdm{
    /**
     * Initialization.
     *
     * @return PhotoalbumCat_ListAdm
     */
    public function init(){
        parent::init();
        $this->dropActions(self::ACTION_GROUP, array('seo_section'));
        return $this;
    }
}

/**
 * AmiMultifeeds5/PhotoGallery configuration admin list component view.
 *
 * @package    Config_AmiMultifeeds5_PhotoGallery
 * @subpackage View
 * @resource   {$modId}_cat/list/view/adm <code>AMI::getResource('{$modId}_cat/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_PhotoalbumCat_ListViewAdm extends Hyper_AmiMultifeeds5_Cat_ListViewAdm{
}

/**
 * Photo Gallery module admin list actions controller.
 *
 * @package    Config_AmiMultifeeds5_PhotoGallery
 * @subpackage Controller
 * @resource   {$modId}_cat/list_actions/controller/adm <code>AMI::getResource('{$modId}_cat/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_PhotoalbumCat_ListActionsAdm extends Hyper_AmiMultifeeds5_Cat_ListActionsAdm{
}

/**
 * Photo Gallery module admin list group actions controller.
 *
 * @package    Config_AmiMultifeeds5_PhotoGallery
 * @subpackage Controller
 * @resource   {$modId}_cat/list_group_actions/controller/adm <code>AMI::getResource('{$modId}_cat/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_PhotoalbumCat_ListGroupActionsAdm extends Hyper_AmiMultifeeds5_Cat_ListGroupActionsAdm{
}
