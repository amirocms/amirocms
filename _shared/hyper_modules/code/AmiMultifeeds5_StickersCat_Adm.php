<?php
/**
 * AmiMultifeeds5/Stickers configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiMultifeeds5_Stickers
 * @version   $Id: AmiMultifeeds5_StickersCat_Adm.php 42166 2013-10-10 12:29:32Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiMultifeeds5/Stickers configuration admin action controller.
 *
 * @package    Config_AmiMultifeeds5_Stickers
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_StickersCat_Adm extends Hyper_AmiMultifeeds5_Cat_Adm{
}

/**
 * AmiMultifeeds5/Stickers configuration model.
 *
 * @package    Config_AmiMultifeeds5_Stickers
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_StickersCat_State extends Hyper_AmiMultifeeds5_Cat_State{
}

/**
 * AmiMultifeeds5/Stickers configuration admin filter component action controller.
 *
 * @package    Config_AmiMultifeeds5_Stickers
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_StickersCat_FilterAdm extends Hyper_AmiMultifeeds5_Cat_FilterAdm{
}

/**
 * AmiMultifeeds5/Stickers configuration item list component filter model.
 *
 * @package    Config_AmiMultifeeds5_Stickers
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_StickersCat_FilterModelAdm extends Hyper_AmiMultifeeds5_Cat_FilterModelAdm{
    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();

        $this->dropViewFields(array('sticky'));
    }
}

/**
 * AmiMultifeeds5/Stickers configuration admin filter component view.
 *
 * @package    Config_AmiMultifeeds5_Stickers
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_StickersCat_FilterViewAdm extends Hyper_AmiMultifeeds5_Cat_FilterViewAdm{
}

/**
 * AmiMultifeeds5/Stickers configuration admin form component action controller.
 *
 * @package    Config_AmiMultifeeds5_Stickers
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_StickersCat_FormAdm extends Hyper_AmiMultifeeds5_Cat_FormAdm{
}

/**
 * AmiMultifeeds5/Stickers configuration form component view.
 *
 * @package    Config_AmiMultifeeds5_Stickers
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_StickersCat_FormViewAdm extends Hyper_AmiMultifeeds5_Cat_FormViewAdm{
    /**
     * Used tabs list
     *
     * @var array
     */
    protected $aUsedTabs = array('announce');
}

/**
 * AmiMultifeeds5/Stickers configuration admin list component action controller.
 *
 * @package    Config_AmiMultifeeds5_Stickers
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_StickersCat_ListAdm extends Hyper_AmiMultifeeds5_Cat_ListAdm{
    /**
     * Initialization.
     *
     * @return AmiMultifeeds5_ArticlesCat_ListAdm
     */
    public function init(){
        $this->listActionsResId = $this->getModId() . '/list_actions/controller/adm';
        $this->listGrpActionsResId = $this->getModId() . '/list_group_actions/controller/adm';
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
 * AmiMultifeeds5/Stickers configuration admin list component view.
 *
 * @package    Config_AmiMultifeeds5_Stickers
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_StickersCat_ListViewAdm extends Hyper_AmiMultifeeds5_Cat_ListViewAdm{
}

/**
 * AmiMultifeeds5/Stickers configuration module admin list actions controller.
 *
 * @package    Config_AmiMultifeeds5_Stickers
 * @subpackage Controller
 * @resource   {$modId}_cat/list_actions/controller/adm <code>AMI::getResource('{$modId}_cat/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_StickersCat_ListActionsAdm extends Hyper_AmiMultifeeds5_Cat_ListActionsAdm{
}

/**
 * AmiMultifeeds5/Stickers configuration module admin list group actions controller.
 *
 * @package    Config_AmiMultifeeds5_Stickers
 * @subpackage Controller
 * @resource   {$modId}_cat/list_group_actions/controller/adm <code>AMI::getResource('{$modId}_cat/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_StickersCat_ListGroupActionsAdm extends Hyper_AmiMultifeeds5_Cat_ListGroupActionsAdm{
}
