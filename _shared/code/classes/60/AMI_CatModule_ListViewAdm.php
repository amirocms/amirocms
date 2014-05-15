<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_CatModule_ListViewAdm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.14.4
 */

/**
 * AMI_CatModule module admin list component view.
 *
 * @package    ModuleComponent
 * @subpackage View
 * @since      5.14.4
 */
abstract class AMI_CatModule_ListViewAdm extends AMI_ModListView_JSON{
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
     * List default elemnts template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#list_header',
            '#flags', 'position', 'public', 'flags',
            '#common', 'id_page', 'cat_header', 'common',
            '#columns', 'header', 'announce', 'num_items', 'columns',
            '#actions', 'front_view', 'edit', 'actions',
        'list_header'
    );

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AMI_CatModule_ListViewAdm
     */
    public function init(){
        // Init columns
        $this
            ->addColumnType('id', 'hidden')
            ->addColumn('position')
            ->addColumn('public')
            ->addColumn('header')
            ->addColumn('announce')
            ->addColumn('num_items')
            ->addColumnType('num_public_items', 'hidden')
            ->setColumnTensility('header')
            ->setColumnTensility('announce')
            ->addSortColumns(
                array(
                    'public',
                    'header',
                    'id_owner',
                    'num_items',
                    'position'
                )
            );

        if(
            AMI::issetAndTrueOption('core', 'multi_page_allowed') &&
            AMI::issetAndTrueOption($this->oModel->getSubItemsModId(), 'multi_page')
        ){
            $this->addColumnType('id_page', 'text');
	    $this->formatColumn('id_page', array($this, 'fmtIdPage'));
        }

        // Truncate 'header' column by 25 symbols
        $this->formatColumn(
            'header',
            array($this, 'fmtTruncate'),
            array(
                'length' => 25
            )
        );

        // Truncate 'announce' column by 250 symbols
        $this->formatColumn(
            'announce',
            array($this, 'fmtTruncate'),
            array(
                'length'       => 250,
                'doStripTags'  => true,
                'doHTMLEncode' => false
            )
        );

        $this->formatColumn(
            'num_items',
            array($this, 'fmtNumItems')
        );

        return $this;
    }

    /**
     * Number of items column formatter.
     *
     * @param  string $value  Cell value
     * @param  array  $aArgs  Arguments
     * @return string
     */
    protected function fmtNumItems($value, array $aArgs){
        return $value. '/' . $aArgs['oItem']->num_public_items;
    }

    /**
     * Id page formatter.
     *
     * @param  string $value  Cell value
     * @param  array  $aArgs  Arguments
     * @return string
     */
    protected function fmtIdPage($value, array $aArgs){
        return $value==0?$this->aLocale['common_item']:AMI_PageManager::getModPageName($value, $aArgs['oItem']->getTable()->getSubItemsModId(), AMI_Registry::get('lang_data'));
    }
}
