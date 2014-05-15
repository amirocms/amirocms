<?php
/**
 * AmiMultifeeds5/Jobs configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiMultifeeds5_Jobs
 * @version   $Id: AmiMultifeeds5_Jobs_Adm.php 42166 2013-10-10 12:29:32Z Leontiev Anton $
 * @since     x.x.x
 * @amidev
 */

/**
 * AmiMultifeeds5/Jobs configuration admin action controller.
 *
 * @package    Config_AmiMultifeeds5_Jobs
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Jobs_Adm extends Hyper_AmiMultifeeds5_Adm{
    /**
     * Resource mapping
     *
     * @var  array
     * @todo fill empty values
     * @see  AMI_Mod::__construct()
     */
    private $aExtResourceMapping = array(
        'ext_images' => '',
    );
}

/**
 * AmiMultifeeds5/Jobs configuration model.
 *
 * @package    Config_AmiMultifeeds5_Jobs
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Jobs_State extends Hyper_AmiMultifeeds5_State{
}

/**
 * AmiMultifeeds5/Jobs configuration admin filter component action controller.
 *
 * @package    Config_AmiMultifeeds5_Jobs
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Jobs_FilterAdm extends Hyper_AmiMultifeeds5_FilterAdm{
}

/**
 * AmiMultifeeds5/Jobs configuration item list component filter model.
 *
 * @package    Config_AmiMultifeeds5_Jobs
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Jobs_FilterModelAdm extends Hyper_AmiMultifeeds5_FilterModelAdm{
}

/**
 * AmiMultifeeds5/Jobs configuration admin filter component view.
 *
 * @package    Config_AmiMultifeeds5_Jobs
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Jobs_FilterViewAdm extends Hyper_AmiMultifeeds5_FilterViewAdm{
    /**
     * Filter default elemnts template (placeholders)
     *
     * @var array
     */
    protected $aPlaceholders = array(
        '#filter',
            'header', 'id_page', 'category', 'datefrom', 'dateto', 'sticky',
        'filter'
    );
}

/**
 * AmiMultifeeds5/Jobs configuration admin form component action controller.
 *
 * @package    Config_AmiMultifeeds5_Jobs
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Jobs_FormAdm extends Hyper_AmiMultifeeds5_FormAdm{
}

/**
 * AmiMultifeeds5/Jobs configuration form component view.
 *
 * @package    Config_AmiMultifeeds5_Jobs
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Jobs_FormViewAdm extends Hyper_AmiMultifeeds5_FormViewAdm{
}

/**
 * AmiMultifeeds5/Jobs configuration admin list component action controller.
 *
 * @package    Config_AmiMultifeeds5_Jobs
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Jobs_ListAdm extends Hyper_AmiMultifeeds5_ListAdm{
    /**
     * Default list order
     *
     * @var array
     * @amidev
     */
    protected $aDefaultOrder = array(
        'col' => 'date',
        'dir' => 'asc'
    );

    /**
     * List actions controller resource id
     *
     * @var string
     */
    protected $listActionsResId = 'jobs/list_actions/controller/adm';

    /**
     * List group actions controller resource id
     *
     * @var string
     */
    protected $listGrpActionsResId = 'jobs/list_group_actions/controller/adm';
}

/**
 * AmiMultifeeds5/Jobs configuration admin list component view.
 *
 * @package    Config_AmiMultifeeds5_Jobs
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Jobs_ListViewAdm extends Hyper_AmiMultifeeds5_ListViewAdm{

    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'cat.header';

    /**
     * Order column direction
     *
     * @var bool
     */
    protected $orderDirection = 'desc';

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
            '#common', 'common',
            '#columns', 'date', 'date_expire', 'status', 'header', 'cat_header', 'salary', 'columns',
            '#actions', 'front_view', 'edit', 'actions',
        'list_header'
    );

    /**
     * Init columns.
     *
     * @return News_ListViewAdm
     */
    public function init(){
        parent::init();

        $this
            ->removeColumn('date_created')
            ->removeColumn('header')
            ->removeColumn('announce')
            ->addColumnType('date', 'date')
            ->addColumnType('date_expire', 'date')
            ->addColumn('status')
            ->addColumn('header')
            ->addColumnType('salary', 'int')
            ->setColumnTensility('header')
            ->setColumnTensility('cat_header')
            ->addSortColumns(
                array(
                    'date', 'date_expire', 'status', 'header', 'id_cat', 'salary'
                )
            );

        $this->formatColumn(
            'status',
            array($this, 'fmtStatus'),
            array()
        );

        $this->formatColumn(
            'date',
            array($this, 'fmtDateTime'),
            array(
                'format' => AMI_Lib_Date::FMT_DATE
            )
        );

        $this->formatColumn(
            'date_expire',
            array($this, 'fmtDateTime'),
            array(
                'format' => AMI_Lib_Date::FMT_DATE
            )
        );

        $this->setColumnLayout(
            'status',
            array(
                'width' => 'narrow',
                'align' => 'center'
            )
        );

        $this->setColumnWidth('salary', 'narrow');

        return $this;
    }

    /**
     * Formats status value.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     */
    protected function fmtStatus($value, array $aArgs){
        return $this->aLocale['status_' . $value ];
    }
}

/**
 * AmiMultifeeds5/Jobs configuration module admin list actions controller.
 *
 * @package    Config_AmiMultifeeds5_Jobs
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Jobs_ListActionsAdm extends Hyper_AmiMultifeeds5_ListActionsAdm{
}

/**
 * AmiMultifeeds5/Jobs configuration module admin list group actions controller.
 *
 * @package    Config_AmiMultifeeds5_Jobs
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Jobs_ListGroupActionsAdm extends Hyper_AmiMultifeeds5_ListGroupActionsAdm{
}
