<?php
/**
 * AmiMultifeeds5/Classifieds configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiMultifeeds5_ClassifiedsCat
 * @version   $Id: AmiMultifeeds5_ClassifiedsCat_Adm.php 42166 2013-10-10 12:29:32Z Leontiev Anton $
 * @since     x.x.x
 * @amidev
 */

/**
 * AmiMultifeeds5/Classifieds configuration admin action controller.
 *
 * @package    Config_AmiMultifeeds5_ClassifiedsCat
 * @subpackage Controller
 * @resource   {$modId}_cat/module/controller/adm <code>AMI::getResource('{$modId}_cat/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_ClassifiedsCat_Adm extends Hyper_AmiMultifeeds5_Cat_Adm{
}

/**
 * AmiMultifeeds5/Classifieds configuration model.
 *
 * @package    Config_AmiMultifeeds5_ClassifiedsCat
 * @subpackage Model
 * @resource   {$modId}_cat/module/model <code>AMI::getResourceModel('{$modId}_cat/module')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_ClassifiedsCat_State extends Hyper_AmiMultifeeds5_Cat_State{
}

/**
 * AmiMultifeeds5/Classifieds configuration admin filter component action controller.
 *
 * @package    Config_AmiMultifeeds5_ClassifiedsCat
 * @subpackage Controller
 * @resource   {$modId}_cat/filter/controller/adm <code>AMI::getResource('{$modId}_cat/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_ClassifiedsCat_FilterAdm extends Hyper_AmiMultifeeds5_Cat_FilterAdm{
}

/**
 * AmiMultifeeds5/Classifieds configuration item list component filter model.
 *
 * @package    Config_AmiMultifeeds5_ClassifiedsCat
 * @subpackage Model
 * @resource   {$modId}_cat/filter/model/adm <code>AMI::getResource('{$modId}_cat/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_ClassifiedsCat_FilterModelAdm extends Hyper_AmiMultifeeds5_Cat_FilterModelAdm{
}

/**
 * AmiMultifeeds5/Classifieds configuration admin filter component view.
 *
 * @package    Config_AmiMultifeeds5_ClassifiedsCat
 * @subpackage View
 * @resource   {$modId}_cat/filter/view/adm <code>AMI::getResource('{$modId}_cat/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_ClassifiedsCat_FilterViewAdm extends Hyper_AmiMultifeeds5_Cat_FilterViewAdm{
}

/**
 * AmiMultifeeds5/Classifieds configuration admin form component action controller.
 *
 * @package    Config_AmiMultifeeds5_ClassifiedsCat
 * @subpackage Controller
 * @resource   {$modId}_cat/form/controller/adm <code>AMI::getResource('{$modId}_cat/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_ClassifiedsCat_FormAdm extends Hyper_AmiMultifeeds5_Cat_FormAdm{
}

/**
 * AmiMultifeeds5/Classifieds configuration form component view.
 *
 * @package    Config_AmiMultifeeds5_ClassifiedsCat
 * @subpackage View
 * @resource   {$modId}_cat/form/view/adm <code>AMI::getResource('{$modId}_cat/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_ClassifiedsCat_FormViewAdm extends Hyper_AmiMultifeeds5_Cat_FormViewAdm{
    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        return parent::init();
    }
}

/**
 * AmiMultifeeds5/Classifieds configuration admin list component action controller.
 *
 * @package    Config_AmiMultifeeds5_ClassifiedsCat
 * @subpackage Controller
 * @resource   {$modId}_cat/list/controller/adm <code>AMI::getResource('{$modId}_cat/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_ClassifiedsCat_ListAdm extends Hyper_AmiMultifeeds5_Cat_ListAdm{
    /**
     * Initialization.
     *
     * @return AmiMultifeeds5_ClassifiedsCat_ListAdm
     */
    public function init(){
        parent::init();

        $this->dropActions(AMI_ModListAdm::ACTION_ALL, array('index_details', 'no_index_details'));

        return $this;
    }
}

/**
 * AmiMultifeeds5/Classifieds configuration admin list component view.
 *
 * @package    Config_AmiMultifeeds5_ClassifiedsCat
 * @subpackage View
 * @resource   {$modId}_cat/list/view/adm <code>AMI::getResource('{$modId}_cat/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_ClassifiedsCat_ListViewAdm extends Hyper_AmiMultifeeds5_Cat_ListViewAdm{

    /**
     * List default elemnts template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#list_header',
            '#flags', 'position', 'public', 'flags',
            '#common', 'cat_header', 'common',
            '#columns', 'header', 'announce', 'num_items', 'id_page', 'columns',
            '#actions', 'front_view', 'edit', 'actions',
        'list_header'
    );

    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'header';

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiMultifeeds5_ClassifiedsCat_ListViewAdm
     */
    public function init(){
        parent::init();

        $this->setColumnTensility('header', false);

        return $this;
    }
}

/**
 * AmiMultifeeds5/Classifieds configuration module admin list actions controller.
 *
 * @package    Config_AmiMultifeeds5_ClassifiedsCat
 * @subpackage Controller
 * @resource   {$modId}_cat/list_actions/controller/adm <code>AMI::getResource('{$modId}_cat/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_ClassifiedsCat_ListActionsAdm extends Hyper_AmiMultifeeds5_Cat_ListActionsAdm{
}

/**
 * AmiMultifeeds5/Classifieds configuration module admin list group actions controller.
 *
 * @package    Config_AmiMultifeeds5_ClassifiedsCat
 * @subpackage Controller
 * @resource   {$modId}_cat/list_group_actions/controller/adm <code>AMI::getResource('{$modId}_cat/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_ClassifiedsCat_ListGroupActionsAdm extends Hyper_AmiMultifeeds5_Cat_ListGroupActionsAdm{
}
