<?php
/**
 * AmiMultifeeds/PhotoGallery configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiMultifeeds_PhotoGallery
 * @version   $Id: AmiMultifeeds_Photoalbum_Frn.php 44504 2013-11-27 10:21:28Z Leontiev Anton $
 * @since     6.0.2
 */

/**
 * AmiMultifeeds/PhotoGallery configuration front action controller.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage Controller
 * @resource   {$modId}/module/controller/frn <code>AMI::getResource('{$modId}/module/controller/frn')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Photoalbum_Frn extends Hyper_AmiMultifeeds_Frn{
    /**
     * Constructor.
     *
     * @param AMI_Request $oRequest    Request object
     * @param AMI_Response $oResponse  Response object
     */
    public function  __construct(AMI_Request $oRequest, AMI_Response $oResponse){
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
 * AmiMultifeeds/PhotoGallery configuration front items component.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage Controller
 * @resource   {$modId}/items/controller/frn <code>AMI::getResource('{$modId}/items/controller/frn')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Photoalbum_ItemsFrn extends Hyper_AmiMultifeeds_ItemsFrn{
}

/**
 * AmiMultifeeds/PhotoGallery configuration front items component view.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage View
 * @resource   {$modId}/items/view/frn <code>AMI::getResource('{$modId}/items/view/frn')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Photoalbum_ItemsViewFrn extends Hyper_AmiMultifeeds_ItemsViewFrn{
    /**
     * Array of simple set fields
     *
     * @var array
     */
    protected $aSimpleSetFields = array('announce', 'header', 'fdate', 'ftime');
}

/**
 * AmiMultifeeds/PhotoGallery configuration front sticky items component.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage Controller
 * @resource   {$modId}/sticky_items/controller/frn <code>AMI::getResource('{$modId}/sticky_items/controller/frn')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Photoalbum_StickyItemsFrn extends Hyper_AmiMultifeeds_StickyItemsFrn{
}

/**
 * AmiMultifeeds/PhotoGallery configuration front sticky items component view.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage View
 * @resource   {$modId}/sticky_items/view/frn <code>AMI::getResource('{$modId}/sticky_items/view/frn')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Photoalbum_StickyItemsViewFrn extends Hyper_AmiMultifeeds_StickyItemsViewFrn{
}

/**
 * AmiMultifeeds/PhotoGallery configuration front details component.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage Controller
 * @resource   {$modId}/details/controller/frn <code>AMI::getResource('{$modId}/details/controller/frn')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Photoalbum_DetailsFrn extends Hyper_AmiMultifeeds_DetailsFrn{
}

/**
 * AmiMultifeeds/PhotoGallery configuration front details component view.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage Controller
 * @resource   {$modId}/details/view/frn <code>AMI::getResource('{$modId}/details/view/frn')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Photoalbum_DetailsViewFrn extends Hyper_AmiMultifeeds_DetailsViewFrn{
}

/**
 * AmiMultifeeds/PhotoGallery configuration front filter component view.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/frn <code>AMI::getResource('{$modId}/filter/controller/frn')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Photoalbum_FilterFrn extends Hyper_AmiMultifeeds_FilterFrn{
}

/**
 * AmiMultifeeds/PhotoGallery configuration front filter component model.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage Controller
 * @resource   {$modId}/filter/model/frn <code>AMI::getResource('{$modId}/filter/model/frn')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Photoalbum_FilterModelFrn extends Hyper_AmiMultifeeds_FilterModelFrn{
}

/**
 * AmiMultifeeds/PhotoGallery configuration front specblock component.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage Controller
 * @resource   {$modId}/specblock/controller/frn <code>AMI::getResource('{$modId}/specblock/controller/frn')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Photoalbum_SpecblockFrn extends Hyper_AmiMultifeeds_SpecblockFrn{
}

/**
 * AmiMultifeeds/PhotoGallery configuration front specblock component view.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage View
 * @resource   {$modId}/specblock/view/frn <code>AMI::getResource('{$modId}/specblock/view/frn')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Photoalbum_SpecblockViewFrn extends Hyper_AmiMultifeeds_SpecblockViewFrn{
}

/**
 * AmiMultifeeds/PhotoGallery configuration front browse items component.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage Controller
 * @resource   {$modId}/browse/controller/frn <code>AMI::getResource('{$modId}/browse/controller/frn')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Photoalbum_BrowseItemsFrn extends Hyper_AmiMultifeeds_BrowseItemsFrn{
}

/**
 * AmiMultifeeds/PhotoGallery configuration front browse items component view.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage View
 * @resource   {$modId}/browse/view/frn <code>AMI::getResource('{$modId}/browse/view/frn')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Photoalbum_BrowseItemsViewFrn extends Hyper_AmiMultifeeds_BrowseItemsViewFrn{
}

/**
 * AmiMultifeeds/PhotoGallery configuration front category details component.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage Controller
 * @resource   {$modId}/details/controller/frn <code>AMI::getResource('{$modId}/details/controller/frn')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Photoalbum_CatDetailsFrn extends Hyper_AmiMultifeeds_CatDetailsFrn{
}

/**
 * AmiMultifeeds/PhotoGallery configuration front category details component view.
 *
 * @package    Config_AmiMultifeeds_PhotoGallery
 * @subpackage Controller
 * @resource   {$modId}/cat_details/view/frn <code>AMI::getResource('{$modId}/cat_details/view/frn')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Photoalbum_CatDetailsViewFrn extends Hyper_AmiMultifeeds_CatDetailsViewFrn{
}
