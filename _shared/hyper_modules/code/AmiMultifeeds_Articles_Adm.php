<?php
/**
 * AmiMultifeeds/Articles configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiMultifeeds_Articles
 * @version   $Id: AmiMultifeeds_Articles_Adm.php 45064 2013-12-05 09:27:10Z Leontiev Anton $
 * @since     6.0.2
 */

/**
 * AmiMultifeeds/Articles configuration admin action controller.
 *
 * @package    Config_AmiMultifeeds_Articles
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Articles_Adm extends Hyper_AmiMultifeeds_Adm{
}

/**
 * AmiMultifeeds/Articles configuration model.
 *
 * @package    Config_AmiMultifeeds_Articles
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Articles_State extends Hyper_AmiMultifeeds_State{
}

/**
 * AmiMultifeeds/Articles configuration admin filter component action controller.
 *
 * @package    Config_AmiMultifeeds_Articles
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Articles_FilterAdm extends Hyper_AmiMultifeeds_FilterAdm{
}

/**
 * AmiMultifeeds/Articles configuration item list component filter model.
 *
 * @package    Config_AmiMultifeeds_Articles
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Articles_FilterModelAdm extends Hyper_AmiMultifeeds_FilterModelAdm{
}

/**
 * AmiMultifeeds/Articles configuration admin filter component view.
 *
 * @package    Config_AmiMultifeeds_Articles
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Articles_FilterViewAdm extends Hyper_AmiMultifeeds_FilterViewAdm{
}

/**
 * AmiMultifeeds/Articles configuration admin form component action controller.
 *
 * @package    Config_AmiMultifeeds_Articles
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Articles_FormAdm extends Hyper_AmiMultifeeds_FormAdm{
}

/**
 * AmiMultifeeds/Articles configuration form component view.
 *
 * @package    Config_AmiMultifeeds_Articles
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Articles_FormViewAdm extends Hyper_AmiMultifeeds_FormViewAdm{
    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        $this->addField(array('name' => 'date_created', 'type' => 'date'));
        $this->addField(array('name' => 'source', 'position' => 'header.after'));
        $this->addField(array('name' => 'author', 'position' => 'header.after'));
        return parent::init();
    }
}

/**
 * AmiMultifeeds/Articles configuration admin list component action controller.
 *
 * @package    Config_AmiMultifeeds_Articles
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Articles_ListAdm extends Hyper_AmiMultifeeds_ListAdm{
}

/**
 * AmiMultifeeds/Articles configuration admin list component view.
 *
 * @package    Config_AmiMultifeeds_Articles
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Articles_ListViewAdm extends Hyper_AmiMultifeeds_ListViewAdm{
    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiMultifeeds_Articles_ListViewAdm
     */
    public function init(){
        parent::init();
        $this
            ->addColumnType('date_created', 'date');
        // Format 'date_created' column in local date format
        $this->formatColumn(
            'date_created',
            array($this, 'fmtDateTime'),
            array(
                'format' => AMI_Lib_Date::FMT_DATE
            )
        );
        // Truncate 'announce' column by 250 symbols
        $this->formatColumn(
            'announce',
            array($this, 'fmtTruncate'),
            array(
                'length'       => 145,
                'doStripTags'  => true,
                'doHTMLEncode' => false
            )
        );
        return $this;
    }
}

/**
 * AmiMultifeeds/Articles configuration module admin list actions controller.
 *
 * @package    Config_AmiMultifeeds_Articles
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Articles_ListActionsAdm extends Hyper_AmiMultifeeds_ListActionsAdm{
}

/**
 * AmiMultifeeds/Articles configuration module admin list group actions controller.
 *
 * @package    Config_AmiMultifeeds_Articles
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      6.0.2
 */
class AmiMultifeeds_Articles_ListGroupActionsAdm extends Hyper_AmiMultifeeds_ListGroupActionsAdm{
}
