<?php
/**
 * AmiFiles/Files configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_##modId##
 * @version   $Id: --modId--_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.14.4
 */

/**
 * AmiFiles/Files configuration admin action controller.
 *
 * @package    Config_##modId##
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      5.14.4
 */
class ##modId##_Adm extends Hyper_AmiMultifeeds_Adm{
}

/**
 * AmiFiles/Files configuration model.
 *
 * @package    Config_##modId##
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      5.14.4
 */
class ##modId##_State extends Hyper_AmiMultifeeds_State{
}

/**
 * AmiFiles/Files configuration admin filter component action controller.
 *
 * @package    Config_##modId##
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      5.14.4
 */
class ##modId##_FilterAdm extends Hyper_AmiMultifeeds_FilterAdm{
}

/**
 * AmiFiles/Files configuration item list component filter model.
 *
 * @package    Config_##modId##
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      5.14.4
 */
class ##modId##_FilterModelAdm extends Hyper_AmiMultifeeds_FilterModelAdm{
}

/**
 * AmiFiles/Files configuration admin filter component view.
 *
 * @package    Config_##modId##
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      5.14.4
 */
class ##modId##_FilterViewAdm extends Hyper_AmiMultifeeds_FilterViewAdm{
}

/**
 * AmiFiles/Files configuration admin form component action controller.
 *
 * @package    Config_##modId##
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      5.14.4
 */
class ##modId##_FormAdm extends Hyper_AmiMultifeeds_FormAdm{
}

/**
 * AmiFiles/Files configuration form component view.
 *
 * @package    Config_##modId##
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      5.14.4
 */
class ##modId##_FormViewAdm extends Hyper_AmiMultifeeds_FormViewAdm{
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
        return parent::init();
    }
}

/**
 * AmiFiles/Files configuration admin list component action controller.
 *
 * @package    Config_##modId##
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      5.14.4
 */
class ##modId##_ListAdm extends Hyper_AmiMultifeeds_ListAdm{
}

/**
 * AmiFiles/Files configuration admin list component view.
 *
 * @package    Config_##modId##
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      5.14.4
 */
class ##modId##_ListViewAdm extends Hyper_AmiMultifeeds_ListViewAdm{
    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return ##modId##_ListViewAdm
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
