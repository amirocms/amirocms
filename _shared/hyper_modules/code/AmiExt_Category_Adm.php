<?php
/**
 * AmiExt/Category extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Config_AmiExt_Category
 * @version   $Id: AmiExt_Category_Adm.php 46631 2014-01-15 19:08:53Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/Category extension configuration admin controller.
 *
 * @package    Config_AmiExt_Category
 * @subpackage Controller
 * @resource   ext_category/module/controller/adm <code>AMI::getResource('ext_category/module/controller/adm')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Category_Adm extends AmiExt_Category{
}

/**
 * AmiExt/Category extension configuration admin view.
 *
 * @package    Config_AmiExt_Category
 * @subpackage View
 * @resource   ext_category/view/adm <code>AMI::getResource('ext_category/view/adm')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Category_ViewAdm extends AMI_ExtView{
    /**
     * Template block name
     *
     * @var string
     */
    protected $tplBlockName = 'category_ext';

    /**
     * Flag specifying to show category column in list
     *
     * @var bool
     */
    protected $showColumn = TRUE;

    /**
     * Header column formatter.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::formatColumn()
     */
    public function fmtHeader($value, array $aArgs){
        $oTpl = $this->getTemplate();
        return
            $oTpl->parse(
                $this->tplBlockName . ':cat_header_column',
                array(
                    '_mod_id' => $aArgs['aScope']['_mod_id'],
                    'id'      => $aArgs['aScope']['cat_id'],
                    'header'  => $value
                )
            );
    }

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Adds field to admin form.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModFormView::get()
     */
    public function handleFormFields($name, array $aEvent, $handlerModId, $srcModId){
        $oFormView = $this->oExt->getFormViewInstance($aEvent);
        if(!$oFormView){
            return $aEvent;
        }

        $itemId = $aEvent['oItem']->getValue($this->oExt->getAliasPrefix() . 'id');

        // Add required fields
        $aSelectField = array(
            'name'  => 'id_cat',
            'type'  => 'select',
            'data'  => $this->oExt->getCatList(TRUE),
            'value' => $itemId
        );
        if(!$itemId){
            $aSelectField['not_selected'] = array(
                'id'      => '',
                'caption' => 'select_category'
            );
        }

        if(
            AMI::issetAndTrueOption('core', 'multi_page_allowed') &&
            AMI::issetAndTrueOption($srcModId, 'multi_page')
        ){
            $aModulePages = array();

            $aPages = AMI_PageManager::getModPages($srcModId, AMI_Registry::get('lang_data'));
            foreach($aPages as $aPage){
                $aModulePages[] = array(
                    'name'  => $aPage['name'],
                    'value' => $aPage['id']
                 );
            }

            $aIdPageSelectField = array(
                'name'         => 'catname_id_page',
                'type'         => 'select',
                'data'         => $aModulePages,
                'position'     => 'id_cat.after',
                'not_selected' => array(
                    'id'      => 0,
                    'caption' => 'common_item'
                ),
                'value'        => 0
            );

            $oFormView->addField($aIdPageSelectField);
            $aSelectField['catname_id_page'] = TRUE;
        }

        $oFormView
            ->addTemplate($this->tplFileName)
            ->addField($aSelectField)
            ->addLocale($this->aLocale, FALSE)
            ->dropPlaceholders(array('id_page'));

        return $aEvent;
    }

    /**
     * Adds filter field locales.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModFormView::get()
     */
    public function handleFilterFormFileds($name, array $aEvent, $handlerModId, $srcModId){
        /**
         * @var AMI_ModFormView
         */
        $oFormView = $aEvent['oFormView'];
        $oFormView->addLocale($this->aLocale, FALSE);
        $oFormView->dropPlaceholders(array('id_page'));

        // Add field data
        $oFormView->setFieldData('category', $this->oExt->getCatList());

        return $aEvent;
    }

    /**
     * Adds category column to admin list view, patch order column.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView_JSON::get()
     */
    public function handleListColumns($name, array $aEvent, $handlerModId, $srcModId){
        /**
         * @var AMI_ModListView_JSON
         */
        $oView = $aEvent['oView'];
        $aliasPrefix = $this->oExt->getAliasPrefix();
        if($this->showColumn){
            $oView->addColumn($aliasPrefix . 'header');
            // $oView->setColumnWidth($aliasPrefix . 'header', 'wide');
            $oView->formatColumn($aliasPrefix . 'header', array($this, 'fmtHeader'));
            $oView->addLocale($this->aLocale, FALSE);
        }else{
            $oView->addColumnType($aliasPrefix . 'header', 'none');
        }
        if(!empty($aEvent['aScope']['order_column']) && mb_strpos($aEvent['aScope']['order_column'], $aliasPrefix) === 0){
            $aEvent['aScope']['order_column'] = str_replace('_', '.', $aliasPrefix) . mb_substr($aEvent['aScope']['order_column'], mb_strlen($aliasPrefix));
        }
        return $aEvent;
    }

    /**
     * Adds category column to admin list view.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::getContent()
     */
    public function handleListSortColumns($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['aColumns'][] = $this->oExt->getAliasPrefix() . 'header';
        return $aEvent;
    }

    /**
     * Hides id_page control from list group actions.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListViewAdm::getGroupActions()
     */
    public function handleListGroupActions($name, array $aEvent, $handlerModId, $srcModId){
        $index = array_search('grp_id_page', $aEvent['aGroupActions']);
        if($index !== false){
            $aEvent['aGroupActions'] =
                array_merge(
                    array_slice($aEvent['aGroupActions'], 0, $index),
                    array_slice($aEvent['aGroupActions'], $index + 1)
                );
        }
        return $aEvent;
    }

    /**#@-*/
}
