<?php
/**
 * AmiFiles hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiFiles
 * @version   $Id: Hyper_AmiFiles_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiFiles hypermodule admin action controller.
 *
 * @package    Hyper_AmiFiles
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiFiles_Adm extends Hyper_AmiMultifeeds_Adm{
}

/**
 * Module model.
 *
 * @package    Hyper_AmiFiles
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiFiles_State extends Hyper_AmiMultifeeds_State{
}

/**
 * AmiFiles hypermodule admin filter component action controller.
 *
 * @package    Hyper_AmiFiles
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiFiles_FilterAdm extends Hyper_AmiMultifeeds_FilterAdm{
}

/**
 * AmiFiles hypermodule item list component filter model.
 *
 * @package    Hyper_AmiFiles
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiFiles_FilterModelAdm extends Hyper_AmiMultifeeds_FilterModelAdm{
}

/**
 * AmiFiles hypermodule admin filter component view.
 *
 * @package    Hyper_AmiFiles
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiFiles_FilterViewAdm extends Hyper_AmiMultifeeds_FilterViewAdm{
    /**
     * Filter default elemnts template (placeholders)
     *
     * @var array
     */
    protected $aPlaceholders = array(
        '#filter',
            'id_page', 'category', 'header', 'datefrom', 'dateto', 'sticky',
        'filter'
    );
}

/**
 * AmiFiles hypermodule admin form component action controller.
 *
 * @package    Hyper_AmiFiles
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiFiles_FormAdm extends Hyper_AmiMultifeeds_FormAdm{
    /**
     * Returns true if component needs to be started always in full environment.
     *
     * @return bool
     */
    public function isFullEnv(){
        return FALSE;
    }

    /**
     * Returns module file storage path.
     *
     * @return string
     */
    protected function getFileStoragePath(){
        return '_mod_files/ftpfiles/';
    }

    /**
     * Initialization.
     *
     * @return Files_FormAdm
     */
    public function init(){
        AMI_Event::addHandler('on_file_move', array($this, 'handleFileMove'), $this->getModId());
        return parent::init();
    }

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Handles uploaded file movement.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_FileFactory::move()
     */
    public function handleFileMove($name, array $aEvent, $handlerModId, $srcModId){
        /**
         * @var AMI_ModTableItem
         */
        $oItem = $aEvent['oItem'];
        $appliedId = $oItem->getId();
        if(!$appliedId){
            $appliedId = $oItem->getTable()->getNextPKValue();
        }
        $aEvent['newName'] =
            AMI_Lib_FS::prepareName(
                $oItem->getModId() . '_' . $appliedId . "_" .
                $aEvent['oFile']->getName() .
                AMI::getOption($oItem->getModId(), 'extension')
            );

        return $aEvent;
    }

    /**#@-*/
}

/**
 * AmiFiles hypermodule admin form component view.
 *
 * @package    Hyper_AmiFiles
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiFiles_FormViewAdm extends Hyper_AmiMultifeeds_FormViewAdm{
    /**
     * Common fields validators
     *
     * @var array
     */
    protected $aCommonFieldsValidators = array();

    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        parent::init();

        $this->addScriptFile('_admin/' . $GLOBALS['CURRENT_SKIN_PATH'] . '_js/files.js');

        $this->dropField('date_created');
        /**
         * @var AMI_ModTableList
         */
        $oTypes = AMI::getResourceModel('file_type/table')
            ->getList()
            ->addColumns(array('id', 'name', 'extensions'))
            ->addOrder('id', 'ASC')
            ->load();
        $aData = array();
        $aExtensions = array();
        foreach($oTypes as $oItem){
            $aData[] = array(
                'name'  => $oItem->name,
                'value' => $oItem->id
            );
            $aExt = explode(';', trim($oItem->extensions, ';'));
            foreach($aExt as $ext){
                if(!$ext){
                    $ext = 'OTHER';
                }
                $aExtensions[] = $ext . ": " . $oItem->id;
            }
        }
        $this->addField(array('name' => 'type', 'type' => 'select', 'data' => $aData, 'position' => 'header.after'));
        $this->addField(array('name' => 'js_file_types', 'value' => implode(', ', $aExtensions)));

        $this->addField(array('name' => 'file', 'type' => 'file', 'validate' => array('filled'), 'position' => 'type.after'));

        $this->aLocale['form_field_file'] =
            '<nobr>' . $this->aLocale['form_field_file'] . ' (' .
            AMI_Lib_String::getBytesAsText(AMI::getOption('core', 'max_upload_size'), $this->aLocale, 1) .
            ' ' . $this->aLocale['maximum'] . ')</nobr>';

        $this->dropPlaceholders(array('date_created', 'date_modified'));

        $this->addField(array('name' => 'date_created', 'type' => 'datetime', 'validate' => array('date'), 'position' => 'file.after'));
        $this->addField(array('name' => 'date_modified', 'type' => 'datetime', 'validate' => array('date'), 'position' => 'date_created.after', 'value' => AMI_Lib_Date::formatUnixTime(time(), AMI_Lib_Date::FMT_BOTH)));

        $this->addField(array('name' => 'num_downloaded', 'display_by_action' => 'edit', 'position' => 'date_modified.after'));

        return $this;
    }
}

/**
 * AmiFiles hypermodule admin list component action controller.
 *
 * @package    Hyper_AmiFiles
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiFiles_ListAdm extends Hyper_AmiMultifeeds_ListAdm{
    /**
     * Initialization.
     *
     * @return Files_ListAdm
     */
    public function init(){
        $this->addJoinedColumns(array('name', 'icon'), 'ft');
        return parent::init();
    }
}

/**
 * AmiFiles hypermodule admin list component view.
 *
 * @package    Hyper_AmiFiles
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiFiles_ListViewAdm extends Hyper_AmiMultifeeds_ListViewAdm{
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
            '#columns', 'header', 'announce', 'type', 'num_downloaded', 'date_created', 'columns',
            '#actions', 'front_view', 'edit', 'actions',
        'list_header'
    );


    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AMI_Module_ListViewAdm
     */
    public function init(){
        parent::init();

        $this
            ->addColumn('type')
            ->addColumnType('ft_name', 'hidden')
            ->addColumnType('ft_icon', 'hidden')
            ->addColumnType('name_orig', 'hidden')
            ->addColumnType('name_fs', 'hidden')
            ->addColumnType('size', 'hidden')
            ->addColumnType('date_modified', 'hidden')
            ->addColumnType('num_downloaded', 'int')
            ->addSortColumns(array('type', 'num_downloaded'));

        $this->formatColumn('type', array($this, 'fmtType'));

        $this->formatColumn(
            'date_created',
            array($this, 'fmtDateCreated'),
            array(
                'format' => AMI_Lib_Date::FMT_BOTH
            )
        );

        return $this;
    }

    /**
     * Date created/modified column formatter.
     *
     * @param  string $value  Value to format
     * @param  array  $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::handleFormatCell()
     */
    protected function fmtDateCreated($value, array $aArgs){
        $oTpl = $this->getTemplate();
        return
            $oTpl->parse(
                $this->tplBlockName . ':date_created',
                array(
                    'date_created'  => $value,
                    'date_modified' => AMI_Lib_Date::formatDateTime($aArgs['oItem']->date_modified, $aArgs['format'])
                )
            );
    }

    /**
     * Type column formatter.
     *
     * @param  string $value  Value to format
     * @param  array  $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::handleFormatCell()
     */
    protected function fmtType($value, array $aArgs){
        /**
         * @var AMI_ModTableItem
         */
        $oItem = $aArgs['oItem'];
        $oTpl = $this->getTemplate();
        // td_small_text
        return
            $oTpl->parse(
                $this->tplBlockName . ':type',
                array(
                    'id'            => $oItem->getId(),
                    'icons_path'    => AMI::getOption($this->getModId(), 'icons_path'),
                    'icon'          => $oItem->ft_icon,
                    'size'          => AMI_Lib_String::getBytesAsText($oItem->file->getSize(), $this->aLocale, 1),
                    'root_path_www' => AMI_Registry::get('path/www_root')
                )
            );
    }
}
