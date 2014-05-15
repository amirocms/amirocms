<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module_Catalog
 * @version   $Id: EshopItem_ListAdm_Serve_EshopOrder.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * EshopItem module admin list component action controller.
 *
 * @package    Module_Catalog
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 * @todo       R/O for positions
 * @todo       R/O for public column
 */
class EshopItem_ListAdm_Serve_EshopOrder extends AmiCatalog_Items_ListAdm{
    /**
     * Initialization.
     *
     * @return AMI_Module_ListAdm
     */
    public function init(){
        parent::init();

        $this->dropActions(self::ACTION_ALL);
#        $this->addColActions(array('public'), FALSE);

        $this->addActions(array('add_to_order'));

        return $this;
    }

    /**
     * Add item list supported position actions.
     *
     * Overload to forbid position actions.
     *
     * @param  array $aActions  Array of actions
     * @return EshopItem_ListAdm_Serve_EshopOrder
     * @amidev Temporary
     */
    protected function addPositionActions(array $aActions){
        return $this;
    }
}

/**
 * EshopItem module admin list component view.
 *
 * @package    Module_Catalog
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class EshopItem_ListViewAdm_Serve_EshopOrder extends AmiCatalog_Items_ListViewAdm{
    /**
     * Flag specifying that class is servant
     *
     * @var bool
     */
    protected $isServant = TRUE;

    /**
     * Template file name
     *
     * @var string
     */
    protected $tplFileName = '_local/_admin/templates/modules/eshop_item_list_serve_eshop_order.tpl';

    /**
     * Eshop object
     *
     * @var AMI_Eshop
     */
    protected $oEshop;

    /**
     * Order currency passed by request
     *
     * @var string
     */
    protected $orderCurrency;

    /**
     * Array of product ids having properties
     *
     * @var array
     */
    protected $aPropProductIds;

    /**
     * Init columns.
     *
     * @return EshopItem_ListViewAdm_Serve_EshopOrder
     */
    public function init(){
        $this->oEshop = AMI::getSingleton('eshop');
        $this->orderCurrency = AMI::getSingleton('env/request')->get('order_currency', $this->oEshop->getBaseCurrency());
        if(!$this->oEshop->issetCurrency($this->orderCurrency)){
            $this->orderCurrency = $this->oEshop->getBaseCurrency();
        }

        $this->addLocale($this->getTemplate()->parseLocale('templates/lang/hyper/eshop_item_list_serve_eshop_order.lng'));
        $this->addScriptFile('_admin/' . $GLOBALS['CURRENT_SKIN_PATH'] . '_js/eshop_item_list_serve_eshop_order.js');

        parent::init();

        $this->addColumnType('position', 'hidden');
        $this->addColumnType('public', 'date');
        // $this->addColumn('pos', 'public.before');
        $this->addSortColumns(array('pos'));

        foreach(array(
            'tax',
            'tax_type',
            'charge_tax_type',
            'shipping',
            'shipping_type',
            'discount',
            'discount_type',
            'sku',
        ) as $column){
            $this->addColumnType($column, 'none');
        }
        $this->addColumnType('data', 'hidden');
        $this->addColumnType('prices', 'hidden');
        $this->addColumnType('price', 'none');
        $this->addColumnType('other_prices', 'none');

        $this->addColumn('price_selector', 'other_prices.after');

        // Format 'position' column
        // $this->formatColumn('pos', array($this, 'fmtPosition'));
        // Format 'public' column
        $this->formatColumn('public', array($this, 'fmtPublic'));
        // Initialize 'data' column
        $this->formatColumn('data', array($this, 'fmtData'));
        // Format 'prices' column
        $this->formatColumn('prices', array($this, 'fmtPrices'));
        // Format 'price_selector' column
        $this->formatColumn('price_selector', array($this, 'fmtPriceSelector'));

        AMI_Event::addHandler('on_sort_columns', array($this, 'handleSortColumns'), $this->getModId());

        return $this;
    }

    /**#@+
     * Event handler.
     *
     * @see AMI_Event::addHandler()
     * @see AMI_Event::fire()
     */

    /**
     * Handles sort columns event to mark products having properties and disable adding from list.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @todo   Avoid table name hardcode
     */
    public function handleSortColumns($name, array $aEvent, $handlerModId, $srcModId){
        $this->aPropProductIds = array();

        /**
         * @var AMI_ModTableList
         */
        $oList = $aEvent['oList'];
        $aIds = array();
        foreach($oList as $oItem){
            $aIds[] = $oItem->getId();
        }
        if(sizeof($aIds)){
            // Hardcode: table name
            $oQuery = new DB_Query('cms_es_props');
            $oQuery
                ->addField('id_item')
                ->addWhereDef(DB_Query::getSnippet('AND `id_item` IN (%s)')->implode($aIds, TRUE));
            $this->aPropProductIds = iterator_to_array(AMI::getSingleton('db')->fetchCol($oQuery));
        }
        return $aEvent;
    }

    /**#@-*/

    /**#@+
     * Column formatter.
     *
     * @see AMI_ModListView::handleFormatCell()
     */

    /**
     * Formats 'public' column value.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     */
/*
    protected function fmtPosition($value, array $aArgs){
        return $this->orderColumn === 'position' ? $aArgs['oItem']->position : '';
    }
*/
    /**
     * Formats 'public' column value.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     */
    protected function fmtPublic($value, array $aArgs){
        $public = $aArgs['oItem']->public;
        return
            '<img src="icons/icon-' . ($public ? 'published' : 'notpublished') . '.gif" style="width: 18px; height: 16px;" title="' .
            $this->aLocale[$public ? 'list_action_public' : 'list_action_unpublic'] . '" />';
    }

    /**
     * Formats 'price' column value.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     */
    protected function fmtPrice($value, array $aArgs){
        $aArgs['oItem']->prices = array(
            0 => is_null($value)
                ? null
                : $this->oEshop->formatNumber($this->oEshop->convertCurrency($value, $this->oEshop->getBaseCurrency(), $this->orderCurrency), TRUE, TRUE)
        );
        return parent::fmtPrice($value, $aArgs);
    }

    /**
     * Formats 'prices' column value.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     */
    protected function fmtPrices($value, array $aArgs){
        return  $aArgs['oItem']->prices;
    }

    /**
     * Fills 'data' column.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     */
    protected function fmtData($value, array $aArgs){
        /**
         * @var AMI_ModTableItem
         */
        $oItem = $aArgs['oItem'];
        $aData = $oItem->data;
        $aData['tax'] = array(
            'type'   => $oItem->tax_type,
            'value'  => $oItem->tax_type == 'abs' ? $this->oEshop->convertCurrency($oItem->tax, $this->oEshop->getBaseCurrency(), $this->orderCurrency) : $oItem->tax,
            'charge' => $oItem->charge_tax_type
        );
        $aData['discount'] = array(
            'type'   => $oItem->discount_type,
            'value'  => $oItem->discount_type == 'abs' ? $this->oEshop->convertCurrency($oItem->discount, $this->oEshop->getBaseCurrency(), $this->orderCurrency) : $oItem->discount,
        );
        $aData['shipping'] = array(
            'type'   => $oItem->shipping_type,
            'value'  => $oItem->shipping_type == 'abs' ? $this->oEshop->convertCurrency($oItem->shipping, $this->oEshop->getBaseCurrency(), $this->orderCurrency) : $oItem->shipping,
        );
        if(in_array($oItem->getId(), $this->aPropProductIds)){
            $aData['hasProps'] = TRUE;
        }


        $oItem->data = $aData;
        return $aData;
    }

    /**
     * Fills 'price_selector' column.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     */
    protected function fmtPriceSelector($value, array $aArgs){
        /**
         * @var AMI_ModTableItem
         */
        $oItem = $aArgs['oItem'];
        /**
         * @var AMI_Eshop
         */
        $oEshop = AMI::getSingleton('eshop');
        $oTpl = $this->getTemplate();
        $html = '';
        foreach($oItem->prices as $num => $price){
            $html .= $oTpl->parse(
                $this->tplBlockName . ':price_selector_row',
                array(
                    'value'   => $num,
                    'caption' => is_null($price) ? '---' : $oEshop->formatMoney($price, $this->orderCurrency)
                )
            );
        }

        $aScope = $this->getScope('list');
        $aScope['options'] = $html;
        $aScope['id'] = $oItem->getId();
        $html = $oTpl->parse($this->tplBlockName . ':price_selector', $aScope);
        return $html;
    }

    /**#@-*/

    /**
     * Other price callback to improve functionality.
     *
     * @param  AMI_ModTableItem $oItem     Product
     * @param  int              $num       Price number
     * @param  float            $price     Price value
     * @param  string           $currency  Currency code
     * @return void
     */
    protected function onExtraPrice(AMI_ModTableItem $oItem, $num, $price, $currency){
        $aData = $oItem->prices;
        $aData[$num] = $this->oEshop->formatNumber($this->oEshop->convertCurrency($price, $currency, $this->orderCurrency), TRUE, TRUE);
        $oItem->prices = $aData;
    }
}
