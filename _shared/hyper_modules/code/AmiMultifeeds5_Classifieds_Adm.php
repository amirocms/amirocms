<?php
/**
 * AmiMultifeeds5/Classifieds configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiMultifeeds5_Classifieds
 * @version   $Id: AmiMultifeeds5_Classifieds_Adm.php 42166 2013-10-10 12:29:32Z Leontiev Anton $
 * @since     x.x.x
 * @amidev
 */

/**
 * AmiMultifeeds5/Classifieds configuration admin action controller.
 *
 * @package    Config_AmiMultifeeds5_Classifieds
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Classifieds_Adm extends Hyper_AmiMultifeeds5_Adm{
}

/**
 * AmiMultifeeds5/Classifieds configuration model.
 *
 * @package    Config_AmiMultifeeds5_Classifieds
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Classifieds_State extends Hyper_AmiMultifeeds5_State{
}

/**
 * AmiMultifeeds5/Classifieds configuration admin filter component action controller.
 *
 * @package    Config_AmiMultifeeds5_Classifieds
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Classifieds_FilterAdm extends Hyper_AmiMultifeeds5_FilterAdm{
}

/**
 * AmiMultifeeds5/Classifieds configuration item list component filter model.
 *
 * @package    Config_AmiMultifeeds5_Classifieds
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Classifieds_FilterModelAdm extends Hyper_AmiMultifeeds5_FilterModelAdm{

    /**
     * Common filter fields
     *
     * @var   array
     */
    protected $aCommonFields = array('header');

    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();

        // $this->dropViewFields(array('datefrom','dateto'));

        $this->addViewField(
            array(
                'name'          => 'datefrom',
                'type'          => 'datefrom',
                'flt_type'      => 'date',
                'flt_default'   => AMI_Lib_Date::formatUnixTime(AMI_Lib_Date::UTIME_MIN),
                'flt_condition' => '>=',
                'flt_column'    => 'date_start',
                'validate' 		=> array('date','date_limits'),
                'session_field' => true
            )
        );

        $this->addViewField(
            array(
                'name'          => 'dateto',
                'type'          => 'dateto',
                'flt_type'      => 'date',
                'flt_default'   => AMI_Lib_Date::formatUnixTime(AMI_Lib_Date::UTIME_MAX),
                'flt_condition' => '<=',
                'flt_column'    => 'date_end',
                'validate' 		=> array('date','date_limits'),
                'session_field' => true
            )
        );

        $this->addViewField(
            array(
                'name'          => 'ip',
                'type'          => 'input',
                'flt_type'      => 'ip',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'ip'
            )
        );
    }

}

/**
 * AmiMultifeeds5/Classifieds configuration admin filter component view.
 *
 * @package    Config_AmiMultifeeds5_Classifieds
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Classifieds_FilterViewAdm extends Hyper_AmiMultifeeds5_FilterViewAdm{
    /**
     * Filter default elemnts template (placeholders)
     *
     * @var array
     */
    protected $aPlaceholders = array(
        '#filter',
            'id_page', 'category', 'datefrom', 'dateto', 'header', 'ip', 'sticky',
        'filter'
    );
}

/**
 * AmiMultifeeds5/Classifieds configuration admin form component action controller.
 *
 * @package    Config_AmiMultifeeds5_Classifieds
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Classifieds_FormAdm extends Hyper_AmiMultifeeds5_FormAdm{
}

/**
 * AmiMultifeeds5/Classifieds configuration form component view.
 *
 * @package    Config_AmiMultifeeds5_Classifieds
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Classifieds_FormViewAdm extends Hyper_AmiMultifeeds5_FormViewAdm{
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
 * @package    Config_AmiMultifeeds5_Classifieds
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Classifieds_ListAdm extends Hyper_AmiMultifeeds5_ListAdm{
    /**
     * Initialization.
     *
     * @return AmiMultifeeds5_Classifieds_ListAdm
     */
    public function init(){
        parent::init();

        $this->dropActions(AMI_ModListAdm::ACTION_COMMON, array('edit', 'delete'));
        $this->addActions(array(self::REQUIRE_FULL_ENV .'edit', 'attach', self::REQUIRE_FULL_ENV .'delete'));

        return $this;
    }
}

/**
 * AmiMultifeeds5/Classifieds configuration admin list component view.
 *
 * @package    Config_AmiMultifeeds5_Classifieds
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Classifieds_ListViewAdm extends Hyper_AmiMultifeeds5_ListViewAdm{

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
            '#common', 'date_start', 'date_end', 'cat_header', 'common',
            '#columns', 'columns',
            '#actions', 'front_view', 'edit', 'actions',
        'list_header'
    );

    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'date_start';

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiMultifeeds5_Classifieds_ListViewAdm
     */
    public function init(){
        parent::init();

        $this
            ->addColumnType('date_start', 'date')
            ->addColumnType('date_end', 'date')
            ->addColumnType('id_file', 'hidden')
            ->removeColumn('date_created')
            ->removeColumn('date')
            ->addSortColumns(
                array(
                    'date_start',
                    'date_end',
                )
            );


        $this->formatColumn(
            'date_start',
            array($this, 'fmtHumanDateTime'),
            array(
                'format' => AMI_Lib_Date::FMT_DATE
            )
        );

        $this->formatColumn(
            'date_end',
            array($this, 'fmtHumanDateTime'),
            array(
                'format' => AMI_Lib_Date::FMT_DATE
            )
        );

       $this->getTemplate()->setBlockLocale($this->tplBlockName, $this->aLocale, true);
       $this->addScriptCode($this->getTemplate()->parse($this->tplBlockName . ':javascript'));

        return $this;
    }

}

/**
 * AmiMultifeeds5/Classifieds configuration module admin list actions controller.
 *
 * @package    Config_AmiMultifeeds5_Classifieds
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Classifieds_ListActionsAdm extends Hyper_AmiMultifeeds5_ListActionsAdm{
}

/**
 * AmiMultifeeds5/Classifieds configuration module admin list group actions controller.
 *
 * @package    Config_AmiMultifeeds5_Classifieds
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Classifieds_ListGroupActionsAdm extends Hyper_AmiMultifeeds5_ListGroupActionsAdm{
}
