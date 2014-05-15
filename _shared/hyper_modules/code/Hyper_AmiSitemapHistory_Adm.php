<?php
/**
 * AmiSitemapHistory hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiSitemapHistory
 * @version   $Id: Hyper_AmiSitemapHistory_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiSitemapHistory hypermodule admin action controller.
 *
 * @package    Hyper_AmiSitemapHistory
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiSitemapHistory_Adm extends AMI_Module_Adm{
}

/**
 * Module model.
 *
 * @package    Hyper_AmiSitemapHistory
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiSitemapHistory_State extends AMI_ModState{
}

/**
 * AmiSitemapHistory hypermodule admin filter component action controller.
 *
 * @package    Hyper_AmiSitemapHistory
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiSitemapHistory_FilterAdm extends AMI_Module_FilterAdm{
}

/**
 * AmiSitemapHistory hypermodule item list component filter model.
 *
 * @package    Hyper_AmiSitemapHistory
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiSitemapHistory_FilterModelAdm extends AMI_Module_FilterModelAdm{

    /**
     * Common filter fields
     *
     * @var   array
     */
    protected $aCommonFields = array();

    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();

        $this->addViewField(
            array(
                'name'          => 'datefrom',
                'type'          => 'datefrom',
                'flt_type'      => 'date',
                'flt_default'   => AMI_Lib_Date::formatUnixTime(AMI_Lib_Date::UTIME_MIN),
                'flt_condition' => '>=',
                'flt_column'    => 'date',
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
                'flt_column'    => 'date',
                'validate' 		=> array('date','date_limits'),
                'session_field' => true
            )
        );

        $this->addViewField(
            array(
                'name'          => 'action',
                'type'          => 'select',
                'flt_type'      => 'select',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'action',
                'data'			=> array(
                    array( 'caption' => 'act_output_sitemap', 'value' => 'act_output_sitemap' ),
                    array( 'caption' => 'act_auto_send_sitemap', 'value' => 'act_auto_send_sitemap' ),
                    array( 'caption' => 'act_send_sitemap', 'value' => 'act_send_sitemap' ),
                    array( 'caption' => 'act_auto_gen_sitemap', 'value' => 'act_auto_gen_sitemap' ),
                    array( 'caption' => 'act_gen_sitemap', 'value' => 'act_gen_sitemap' ),
                ),
                'not_selected'  => array('value' => null, 'caption' => 'all')
            )
        );

        $this->addViewField(
            array(
                'name'          => 'useragent',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'useragent'
            )
        );

    }
}

/**
 * AmiSitemapHistory hypermodule admin filter component view.
 *
 * @package    Hyper_AmiSitemapHistory
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiSitemapHistory_FilterViewAdm extends AMI_Module_FilterViewAdm{
}

/**
 * AmiSitemapHistory hypermodule admin form component action controller.
 *
 * @package    Hyper_AmiSitemapHistory
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiSitemapHistory_FormAdm extends AMI_Module_FormAdm{
    /**
     * Returns true if component needs to be started always in full environment.
     *
     * @return bool
     */
    public function isFullEnv(){
        return FALSE;
    }
}

/**
 * AmiSitemapHistory hypermodule admin form component view.
 *
 * @package    Hyper_AmiSitemapHistory
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiSitemapHistory_FormViewAdm extends AMI_Module_FormViewAdm{
    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){

        $this->addField(array('name' => 'date', 'type' => 'input'));
        $this->addField(array('name' => 'action', 'type' => 'input'));
        $this->addField(array('name' => 'sitemap_file', 'type' => 'input'));
        $this->addField(array('name' => 'login', 'type' => 'input'));
        $this->addField(array('name' => 'ip', 'type' => 'input'));
        $this->addField(array('name' => 'useragent', 'type' => 'input'));
        $this->addField(array('name' => 'time', 'type' => 'input'));
        $this->addField(array('name' => 'sitemap_size', 'type' => 'input'));
        $this->addField(array('name' => 'num_urls', 'type' => 'input'));
        $this->addField(array('name' => 'status', 'type' => 'input'));

        AMI_Event::addHandler('on_form_field_{sitemap_file}', array($this, 'fmtSitemapFile'), $this->getModId());
        AMI_Event::addHandler('on_form_field_{time}', array($this, 'fmtTime'), $this->getModId());
        AMI_Event::addHandler('on_form_field_{sitemap_size}', array($this, 'fmtSSize'), $this->getModId());
        AMI_Event::addHandler('on_form_field_{date}', array($this, 'fmtDate'), $this->getModId());

        return $this;
    }

    /**
     * Special handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function fmtSitemapFile($name, array $aEvent, $handlerModId, $srcModId){

        $fileUrl = $aEvent['aScope']['value'];

        if($aEvent['oItem']->action == "act_output_sitemap"){
            $fileUrl = AMI_Registry::get('path/www_root').$fileUrl;
        }elseif($aEvent['oItem']->action == "act_gen_sitemap" || $aEvent['oItem']->action == "act_auto_gen_sitemap"){
            $fileUrl = AMI_Registry::get('path/www_root').'_mod_files/'.$fileUrl;
        }

        $aEvent['aScope']['file_url'] = $fileUrl;

        return $aEvent;
    }

    /**
     * Special handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function fmtTime($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['aScope']['value'] = sprintf('%.2f', $aEvent['aScope']['value']);
        return $aEvent;
    }

    /**
     * Special handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function fmtSSize($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['aScope']['value'] = AMI_Lib_String::getBytesAsText($aEvent['aScope']['value'], $this->aLocale, 1);
        return $aEvent;
    }

    /**
     * Special handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function fmtDate($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['aScope']['value'] = AMI_Lib_Date::formatDateTime($aEvent['aScope']['value'], AMI_Lib_Date::FMT_BOTH);
        return $aEvent;
    }
}

/**
 * AmiSitemapHistory hypermodule admin list component action controller.
 *
 * @package    Hyper_AmiSitemapHistory
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiSitemapHistory_ListAdm extends AMI_Module_ListAdm{

    /**
     * Initialization.
     *
     * @return AmiMultifeeds_Classifieds_ListAdm
     */
    public function init(){
        parent::init();

        $this->aColActions = array();

        $this->dropActions(AMI_ModListAdm::ACTION_COMMON, array('edit'));
        $this->dropActions(AMI_ModListAdm::ACTION_COMMON, array('delete'), FALSE);
        $this->dropActions(AMI_ModListAdm::ACTION_GROUP, array('public', 'unpublic', 'gen_sublink', 'gen_html_meta', 'gen_html_meta_force', 'index_details', 'no_index_details'));
        $this->addActions(array('show', self::REQUIRE_FULL_ENV .'delete'));
        return $this;
    }
}

/**
 * AmiSitemapHistory hypermodule admin list component actions controller.
 *
 * @package    Hyper_AmiSitemapHistory
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiSitemapHistory_ListActionsAdm extends AMI_Module_ListActionsAdm{
}

/**
 * AmiSitemapHistory hypermodule admin list component group actions controller.
 *
 * @package    Hyper_AmiSitemapHistory
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiSitemapHistory_ListGroupActionsAdm extends AMI_Module_ListGroupActionsAdm{
}

/**
 * AmiSitemapHistory hypermodule admin list component view.
 *
 * @package    Hyper_AmiSitemapHistory
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiSitemapHistory_ListViewAdm extends AMI_Module_ListViewAdm{
    /**
     * List default elemnts template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#list_header',
            '#flags', 'flags',
            '#common', 'date', 'action', 'time', 'sitemap_size','num_urls', 'useragent' ,'status', 'common',
            '#columns','columns',
            '#actions', 'edit', 'actions',
        'list_header'
    );

    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'date';

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiMultifeeds_Classifieds_ListViewAdm
     */
    public function init(){
        parent::init();

        $this
            ->removeColumn('position')
            ->removeColumn('header')
            ->removeColumn('announce')
            ->removeColumn('public')
            ->removeColumn('date_created')
            ->addColumn('useragent')
            ->addColumn('action')
            ->addColumnType('time', 'float')
            ->addColumnType('sitemap_size', 'int')
            ->addColumnType('num_urls', 'int')
            ->addColumn('status')
            ->setColumnAlign('status', 'center')
            ->addColumnType('date', 'date')
            ->addColumnType('id', 'hidden')
            ->addColumnType('sitemap_file', 'hidden')
            ->setColumnWidth('time', '20px')
            ->setColumnWidth('sitemap_size', '60px')
            ->setColumnWidth('num_urls', '20px')
            ->setColumnTensility('useragent', true)
            ->addSortColumns(array( 'date', 'action', 'time', 'status', 'sitemap_size'));

        $this->formatColumn(
            'date',
            array($this, 'fmtDateTime'),
            array(
                'format' => AMI_Lib_Date::FMT_BOTH
            )
        );

        $this->formatColumn('time', array($this, 'fmtTime'));
        $this->formatColumn('status', array($this, 'fmtLocaleCaption'));
        $this->formatColumn('action', array($this, 'fmtAction'));
        $this->formatColumn('sitemap_size', array($this, 'fmtSize'));

       $this->getTemplate()->setBlockLocale($this->tplBlockName, $this->aLocale, true);
       $this->addScriptCode($this->getTemplate()->parse($this->tplBlockName . ':javascript'));

        return $this;
    }

    /**
     * Time column formatter.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtTime($value, array $aArgs){
        return sprintf('%.2f', $value);
    }

    /**
     * Size column formatter.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtSize($value, array $aArgs){
        return AMI_Lib_String::getBytesAsText($value, $this->aLocale, 1);
    }

    /**
     * Action column formatter.
     *
     * @param  string $value  Value to format
     * @param  array  $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::handleFormatCell()
     */
    protected function fmtAction($value, array $aArgs){
        /**
         * @var AMI_ModTableItem
         */

        $oItem = $aArgs['oItem'];
        $oTpl = $this->getTemplate();

        $file = $fileUrl = $oItem->sitemap_file;

        if($value == "act_output_sitemap"){
            $fileUrl = AMI_Registry::get('path/www_root').$fileUrl;
        }elseif($value == "act_gen_sitemap" || $value == "act_auto_gen_sitemap"){
            $fileUrl = AMI_Registry::get('path/www_root').'_mod_files/'.$fileUrl;
        }

        return
            $oTpl->parse(
                $this->tplBlockName . ':action',
                array(
                    'title'     =>  $this->fmtLocaleCaption($value),
                    'file_url'  => $fileUrl,
                    'file'      => $file,
                )
            );
    }
}
