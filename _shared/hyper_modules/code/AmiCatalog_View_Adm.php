<?php
/**
 * AmiCatalog/View configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiCatalog_View
 * @version   $Id: AmiCatalog_View_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiCatalog/View configuration admin action controller.
 *
 * @package    Config_AmiCatalog_View
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @amidev     Temporary
 * @since      x.x.x
 */
class AmiCatalog_View_Adm extends Hyper_AmiCatalog_Adm{
}

/**
 * AmiCatalog/View configuration model.
 *
 * @package    Config_AmiCatalog_View
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_View_State extends Hyper_AmiCatalog_State{
}

/**
 * AmiCatalog/View configuration admin filter component action controller.
 *
 * @package    Config_AmiCatalog_View
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_View_FilterAdm extends Hyper_AmiCatalog_FilterAdm{
}

/**
 * AmiCatalog/View configuration item list component filter model.
 *
 * @package    Config_AmiCatalog_View
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_View_FilterModelAdm extends Hyper_AmiCatalog_FilterModelAdm{
    /**
     * Common filter fields
     *
     * @var   array
     */
    protected $aCommonFields = array('header');
}

/**
 * AmiCatalog/View configuration admin filter component view.
 *
 * @package    Config_AmiCatalog_View
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_View_FilterViewAdm extends Hyper_AmiCatalog_FilterViewAdm{
}

/**
 * AmiCatalog/View configuration admin form component action controller.
 *
 * @package    Config_AmiCatalog_View
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_View_FormAdm extends Hyper_AmiCatalog_FormAdm{
}

/**
 * AmiCatalog/View configuration form component view.
 *
 * @package    Config_AmiCatalog_View
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_View_FormViewAdm extends Hyper_AmiCatalog_FormViewAdm{
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
 * AmiCatalog/View configuration admin list component action controller.
 *
 * @package    Config_AmiCatalog_View
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_View_ListAdm extends Hyper_AmiCatalog_ListAdm{
    /**
     * Initialization.
     *
     * @return PhotoalbumCat_ListAdm
     */
    public function init(){
        AMI_Event::addHandler('on_query_add_table', array($this, 'handleAddTable'), $this->getModId());
        parent::init();
        return $this;
    }

    /**
     * Adds condition.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTable::getQueryBase()
     */
    public function handleAddTable($name, array $aEvent, $handlerModId, $srcModId){
        if(AMI::issetProperty($this->getModId(), 'item_module')){
            $aEvent['oQuery']->addWhereDef(DB_Query::getSnippet("AND i.mod_name = %s")->q(AMI::getProperty($this->getModId(), 'item_module')));
        }
        return $aEvent;
    }
}

/**
 * AmiCatalog/View configuration admin list component view.
 *
 * @package    Config_AmiCatalog_View
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_View_ListViewAdm extends Hyper_AmiCatalog_ListViewAdm{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'header';

    /**
     * Order column direction
     *
     * @var bool
     */
    protected $orderDirection = 'asc';

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiJobs_History_ListViewAdm
     */
    public function init(){
        parent::init();
        $this
            ->removeColumn('position')
            ->removeColumn('date_created')
            ->setColumnWidth('header', 'normal')
            ->setColumnTensility('announce')
            ->setColumnTensility('header', false);

        $this->formatColumn(
            'header',
            array($this, 'fmtTruncate'),
            array(
                'length'       => 25,
                'doStripTags'  => true,
                'doHTMLEncode' => false
            )
        );

        $this->formatColumn(
            'announce',
            array($this, 'fmtTruncate'),
            array(
                'length'       => 250,
                'doStripTags'  => true,
                'doHTMLEncode' => false
            )
        );

        return $this;
    }
}

/**
 * AmiCatalog/View configuration module admin list actions controller.
 *
 * @package    Config_AmiCatalog_View
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_View_ListActionsAdm extends Hyper_AmiCatalog_ListActionsAdm{
}

/**
 * AmiCatalog/View configuration module admin list group actions controller.
 *
 * @package    Config_AmiCatalog_View
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiCatalog_View_ListGroupActionsAdm extends Hyper_AmiCatalog_ListGroupActionsAdm{
}
