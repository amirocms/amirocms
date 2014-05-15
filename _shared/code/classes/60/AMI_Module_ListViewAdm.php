<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_Module_ListViewAdm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.14.4
 */

/**
 * Common module admin list component view.
 *
 * @package    ModuleComponent
 * @subpackage View
 * @since      5.14.4
 */
abstract class AMI_Module_ListViewAdm extends AMI_ModListView_JSON{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'date_created';

    /**
     * Order column direction
     *
     * @var bool
     */
    protected $orderDirection = 'desc';

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AMI_Module_ListViewAdm
     */
    public function init(){
        // Init columns
        $this
            ->addColumnType('id', 'hidden')
            ->addColumn('public')
            ->addColumn('position')
            ->addColumnType('date_created', 'datetime')
            ->addColumnType('header', 'mediumtext')
            ->addColumnType('announce', 'longtext');

        // page_id
        if(
            AMI::issetAndTrueOption('core', 'multi_page_allowed') &&
            (
                AMI::issetAndTrueOption($this->getModId(), "multi_page") &&
                !AMI::issetAndTrueOption($this->getModId(), "use_categories")
            )
        ){
            $this->addColumnType('id_page', 'text');
            AMI_Event::addHandler('on_list_body_{id_page}', array($this, 'handleListBodyIdPage'), $this->getModId());
        }

        $this
            ->setColumnTensility('header')
            ->addSortColumns(
                array(
                    'public',
                    'header',
                    'announce',
                    'date_created',
                    'date_modified',
                    'id_owner',
                    'position'
                )
            );

        // Truncate 'header' column by 30 symbols
        $this->formatColumn(
            'header',
            array($this, 'fmtTruncate'),
            array(
                'length'       => 45
            )
        );

        // Truncate 'announce' column by 250 symbols
        $this->formatColumn(
            'announce',
            array($this, 'fmtTruncate'),
            array(
                'length'       => 4096,
                'doStripTags'  => TRUE,
                'doHTMLEncode' => FALSE
            )
        );

        // Format 'date_created' column in local date format
        $this->formatColumn(
            'date_created',
            array($this, 'fmtDateTime'),
            array(
                'format' => AMI_Lib_Date::FMT_BOTH
            )
        );

        // Format 'date_modified' column in local date/time format
        $this->formatColumn(
            'date_modified',
            array($this, 'fmtDateTime'),
            array(
                'format' => AMI_Lib_Date::FMT_BOTH
            )
        );

        return $this;
    }

    /**
     * Inserts admin username if sender_id = 0.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListBodyIdPage($name, array $aEvent, $handlerModId, $srcModId){
        if($aEvent['aScope']['list_col_value']){
            $caption = AMI_PageManager::getModPageName($aEvent['aScope']['list_col_value'], $this->getModId(), AMI_Registry::get('lang_data'));
            if(!$caption){
                $caption = $this->aLocale['notspecified_page'];
            }
        }else{
            $caption = $this->aLocale['list_common_id_page'];
        }
        $aEvent['aScope']['list_col_value'] = $caption;
        return $aEvent;
    }
}
