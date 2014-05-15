<?php
/**
 * AmiClean/EshopOrder configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiClean_EshopOrder
 * @version   $Id: AmiClean_EshopOrder_Adm.php 47800 2014-02-11 06:45:29Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiClean/EshopOrder module admin action controller.
 *
 * @package    Config_AmiClean_EshopOrder
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_EshopOrder_Adm extends Hyper_AmiClean_Adm{
    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request
     * @param AMI_Response $oResponse  Response
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        parent::__construct($oRequest, $oResponse);

        $aComponents =
            'custom_component_view' !== $oRequest->get('mod_action', FALSE)
                ? array('filter', 'list', 'form')
                : array('custom_component');

        $this->addComponents($aComponents);
    }
}

/**
 * AmiClean/EshopOrder module model.
 *
 * @package    Config_AmiClean_EshopOrder
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_EshopOrder_State extends Hyper_AmiClean_State{
}

/**
 * AmiClean/EshopOrder module admin filter component action controller.
 *
 * @package    Config_AmiClean_EshopOrder
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_EshopOrder_FilterAdm extends AMI_Module_FilterAdm{
}

/**
 * AmiClean/EshopOrder  module item list component filter model.
 *
 * @package    Config_AmiClean_EshopOrder
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_EshopOrder_FilterModelAdm extends AMI_Module_FilterModelAdm{
    /**
     * Common filter fields
     *
     * @var   array
     */
    protected $aCommonFields = array('datefrom', 'dateto');

    /**
     * Constructor.
     */
    public function __construct(){

        parent::__construct();

        $this->addViewField(
            array(
                'name'          => 'order_id',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'id'
            )
        );

        $this->addViewField(
            array(
                'name'          => 'u_fullname',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'fullname',
                'flt_alias'     => 'u'
            )
        );

        $this->addViewField(
            array(
                'name'          => 'u_login',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_alias'     => 'u',
                'flt_column'    => 'username'
            )
        );

        $aStatuses = array('draft', 'printed', 'pending', 'checkout', 'waiting', 'shipped', 'delivered', 'rejected', 'accepted', 'cancelled', 'confirmed', 'confirmed_conditionally', 'confirmed_done');
        if(AMI::getOption(AMI::getModId(get_class($this)), 'use_requirements_order')){
            $aStatuses[] = 'requirements_draft';
            $aStatuses[] = 'requirements_accepted';
            $aStatuses[] = 'requirements_cancelled';
        }
        $aData = array();
        foreach($aStatuses as $status){
            $aData[] = array(
                'name'      => $status,
                'value'     => $status,
                'caption'   => $status
            );
        }
        $this->addViewField(
            array(
                'name'          => 'status',
                'type'          => 'select',
                'flt_condition' => '=',
                'data'          => $aData,
                'multiple'      => true,
                'disable_empty' => true,
                'session_field' => true,
                'flt_default'   => ''
            )
        );
        $this->addViewField(
            array(
                'name'          => 'name',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like'
            )
        );
        $this->addViewField(
            array(
                'name'          => 'adm_comments',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like'
            )
        );
    }
}

/**
 * AmiClean/EshopOrder  module admin filter component view.
 *
 * @package    Config_AmiClean_EshopOrder
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_EshopOrder_FilterViewAdm extends AMI_Module_FilterViewAdm{

    /**
     * Init view.
     *
     * @see    AMI_View::init()
     * @return AmiClean_EshopOrder_FilterViewAdm
     */
    public function init(){
        parent::init();

        $this->addScriptFile('_admin/' . $GLOBALS['CURRENT_SKIN_PATH'] . '_js/' . $this->getModId() . '_filter.js');

        return $this;
    }
}

/**
 * AmiClean/EshopOrder module admin form component action controller.
 *
 * @package    Config_AmiClean_EshopOrder
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_EshopOrder_FormAdm extends Hyper_AmiClean_FormAdm{
    /**
     * Order changed = false
     *
     * @var bool
     */
    private $orderChanged = false;

    /**
     * Order changes
     *
     * @var array
     */
    private $aOrderChanges = array(
        'prev'  => array(),
        'new'   => array()
    );

    /**
     * Customer information changes
     *
     * @var array
     */
    private $aCustomInfoChanges = array(
        'prev'  => array(),
        'new'   => array()
    );

    /**
     * Items changes
     *
     * @var array
     */
    private $aItemsChanges = array();

    /**
     * Returns true if component needs to be started always in full environment.
     *
     * @return bool
     */
    public function isFullEnv(){
        return TRUE;
    }

    /**
     * Returns view.
     *
     * @param  string $resIdTail  Resource id tail
     * @return AMI_View
     * @todo   Think if model should exist if we show a view
     * @amidev Temporary
     */
    protected function _getView($resIdTail){
        $oView = parent::_getView($resIdTail);
        if(method_exists($oView, 'addFields')){
            $oView->addFields();
        }

        return $oView;
    }

    /**
     * Save action dispatcher.
     *
     * @param  array &$aEvent  Event data
     * @return void
     */
    protected function _save(array &$aEvent){
        AMI_Event::addHandler('on_before_set_values_model_item', array($this, 'handleBeforeSetValuesModelItem'), $this->getModId());
        AMI_Event::addHandler('on_before_save_model_item', array($this, 'handleBeforeSaveModelItem'), $this->getModId());
        AMI_Event::addHandler('on_after_save_model_item', array($this, 'handleAfterSaveModelItem'), $this->getModId());

        parent::_save($aEvent);
    }

    /**
     * Collect changes.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function handleBeforeSetValuesModelItem($name, array $aEvent, $handlerModId, $srcModId){
        $aData      = &$aEvent['aData'];
        $oOrder     = $aEvent['oItem'];

        $aData['shipping']  = $aData['order_shipping'];
        $aData['tax']       = $aData['tax_total'];
        $aData['total']     = $aData['order_subtotal'];

        // Order changes
        $aCollectFields = array('status', 'adm_comments', 'login', 'email', 'firstname', 'lastname', 'company', 'comments', 'tax', 'shipping', 'total');
        foreach($aCollectFields as $field){
            $this->_collectChanges($aData[$field], $oOrder->{$field}, $field, 'order');
        }

        // Customer info changes
        $aDataKeys = array_keys($aData);
        foreach($aDataKeys as $key){
            if(strpos($key, 'ci_') === 0){
                $field = substr($key, 3);
                $this->_collectChanges($aData['ci_' . $field], $oOrder->custom_info[$field], $field, 'custom_info');
            }
        }

        // Currencies

        // Order items
        $oEshopOrder = AMI::getSingleton('eshop_order');
        $aItems = $oEshopOrder->getItems(array($oOrder));
        $aOrderItems = array();
        foreach($aItems[$oOrder->id] as $oItem){
            $aOrderItems[$oItem->id] = $oItem;
        }

        foreach($aDataKeys as $key){
            if(strpos($key, 'items_') === 0){
                $field = substr($key, 6);
                $aSkipFields = array('order_item_id', 'charge_tax_type', 'props_caption');
                if(!in_array($field, $aSkipFields)){
                    $aItemsData = $aData[$key];
                    $i = 0;
                    foreach($aItemsData as $aItemData){
                        $itemId = $aData['items_order_item_id'][$i];
                        $itemName = $aData['items_name'][$i];
                        $newValue = $aItemData;
                        $oldValue = null;
                        if($itemId){
                            $oItem = $aOrderItems[$itemId];
                            switch($field){
                                case 'name':
                                    $oldValue = $oItem->data['name'];
                                    break;
                                case 'sku':
                                    $oldValue = $oItem->data['item_info']['sku'];
                                    break;
                                case 'qty':
                                    $oldValue = $oItem->qty;
                                    break;
                                case 'item_id':
                                    $oldValue = $oItem->data['item_info']['id'];
                                    break;
                                case 'abs_discount':
                                    $oldValue = $oItem->data['item_info']['absolute_discount'];
                                    break;
                                case 'prc_discount':
                                    $oldValue = $oItem->data['item_info']['percentage_discount'];
                                    if(is_null($oldValue)){
                                        $oldValue = 0;
                                    }
                                    break;
                                case 'price':
                                    $oldValue = $oItem->data['item_info']['price'];
                                    break;
                                case 'original_price':
                                    $oldValue = $oItem->data['item_info']['original_price'];
                                    break;
                                case 'current_price':
                                    $oldValue = $oItem->data['item_info']['cur_price'];
                                    break;
                                case 'tax_price':
                                    $oldValue = $oItem->data['item_info']['cur_price_tax'];
                                    break;
                                case 'order_price':
                                    $oldValue = $oItem->data['item_info']['order_price'];
                                    break;
                                case 'price_number':
                                    $oldValue = $oItem->data['item_info']['price_number'];
                                    break;
                                case 'tax_total':
                                    $oldValue = $oItem->data['item_info']['tax'];
                                    break;
                                case 'tax_type':
                                    $oldValue = $oItem->data['item_info']['tax_type'];
                                    break;
                                case 'tax_item':
                                    $oldValue = $oItem->data['item_info']['tax_item'];
                                    break;
                                case 'tax':
                                    $oldValue = $oItem->data['item_info']['tax_item_value'];
                                    break;
                            }
                        }
                        $key = $itemName;

                        foreach(array('items_props_caption', 'items_sku', 'items_item_id') as $itemPropName){
                            if(!empty($aData[$itemPropName][$i])){
                                $key .= ' ('.$aData[$itemPropName][$i].')';
                            }
                        }

                        $this->_collectChanges($newValue, $oldValue, $field, 'item', $key);
                        if(count($this->aItemsChanges[$key]['new'])){
                            $this->aItemsChanges[$key]['item_id'] = $itemId;
                        }
                        $i++;
                    }
                }
            }
        }

        return $aEvent;
    }

    /**
     * Update order history.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function handleBeforeSaveModelItem($name, array $aEvent, $handlerModId, $srcModId){
        $oOrder = $aEvent['oItem'];
        $aData  = &$aEvent['aData'];
        $commentsChanged = isset($this->aOrderChanges['new']['adm_comments']);
        $statusChanged = isset($this->aOrderChanges['new']['status']);

        // Customer info changes
        $aDataKeys = array_keys($aData);
        if(count($aDataKeys) && count($this->aCustomInfoChanges)){
            $aCustomInfo = $oOrder->custom_info;
            foreach($aDataKeys as $key){
                if(strpos($key, 'ci_') === 0){
                    $field = substr($key, 3);
                    $aCustomInfo[$field] = $aData[$key];
                }
            }
            $oOrder->custom_info = $aCustomInfo;
            $aData['custom_info'] = $aCustomInfo;
        }

        // Save status change history for 50 compatibility
        if($commentsChanged || $statusChanged){
            $aHistory = $oOrder->status_history;
            $aChanges = array(
                'type'  => 'admin',
                'ip'    => getenv("REMOTE_ADDR")
            );
            if($commentsChanged){
                $aChanges['comments'] = $this->aOrderChanges['new']['adm_comments'];
            }
            if($statusChanged){
                $aChanges['status'] = $this->aOrderChanges['new']['status'];
            }
            $aHistory[time()] = $aChanges;
            $oOrder->status_history = $aHistory;
        }
        return $aEvent;
    }

    /**
     * Update order items.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function handleAfterSaveModelItem($name, array $aEvent, $handlerModId, $srcModId){
        $oOrder = $aEvent['oItem'];
        if($aEvent['success']){
            if(isset($this->aOrderChanges['new']['status'])){
                $oCMS = $GLOBALS['cms'];
                $oEshop = new EshopAdmin($oCMS, $this->aLocale, $this->getModId());
                $oEshop->initByOwnerName('eshop');
                $oCMS->Eshop = $oEshop;

                $oEshopOrder = new EshopOrder();
                $oEshopOrder->aCurrency = &$oEshop->aCurrency;

                // Process send_notification radio button
                $aActions = AMI::getOption($this->getModId(), 'statuses_actions');
                $aStatActions = &$aActions[$this->aOrderChanges['new']['status']];
                $aExtParams = array();
                if(is_array($aStatActions)){
                    $pos = array_search('status_changed_user', $aStatActions);
                    switch(AMI::getSingleton('env/request')->get('send_notification')){
                        case 'force':
                            if($pos === false){
                                $aStatActions[] = 'status_changed_user';
                            }
                            break;
                        case 'dont':
                            if($pos !== false){
                                unset($aStatActions[$pos]);
                            }
                            $aExtParams['dontNotifyUser'] = TRUE;
                            break;
                    }
                }
                // Update new option's value
                AMI::setOption($this->getModId(), 'statuses_actions', $aActions);
                $oEshopOrder->updateStatus($oCMS, $oOrder->id, 'admin', $this->aOrderChanges['new']['status'], $aExtParams, $this->aOrderChanges['prev']['status']);
            }
            $oEshopOrder = AMI::getSingleton('eshop_order');
            $aItems = $oEshopOrder->getItems(array($oOrder));
            $aOrderItems = array();
            $aOrderData = $aEvent['aData']; ### MUST BE AMI::getSingleton('env/request')->getScope();
            $maxAttempts = AMI::getOption('eshop_digitals', "max_download_attempts");
            foreach($aItems[$oOrder->id] as $oItem){
                $aOrderItems[$oItem->id] = $oItem;

                // Eshop digitals extension farsh
                $itemDataChanged = false;
                if(isset($aOrderData['file_id' . $oItem->id])){
                    $aFileIds = $aOrderData['file_id' . $oItem->id];
                    foreach($aFileIds as $fileId){
                        if(isset($aOrderData['number_attempts_old' . $fileId])){
                            $numAttemptsOld = intval($aOrderData['number_attempts_old' . $fileId]);
                            $numAttempts = intval($aOrderData['number_attempts' . $fileId]);
                            if($numAttempts > $maxAttempts){
                                $numAttempts = $maxAttempts;
                            }
                            if($numAttempts != $numAttemptsOld){
                                $aData = $oItem->data;
                                $aData["eshop_digitals"]["download_attempts"][$fileId]["rest"] = $numAttempts;
                                if(!isset($aData["eshop_digitals"]["download_attempts"][$fileId]["initial"])){
                                    $aData["eshop_digitals"]["download_attempts"][$fileId]["initial"] = $maxAttempts;
                                }
                                $oItem->data = $aData;
                                $itemDataChanged = true;
                            }
                        }
                    }
                }
                if($itemDataChanged){
                    $oItem->save();
                }
            }

            // Save items if needed
            if(count($this->aItemsChanges)){
                // Skip new items with qty = 0
                foreach($this->aItemsChanges as $name => $aItem){
                    if(!$aItem['item_id'] && (isset($aItem['new']['qty']) && !$aItem['new']['qty'])){
                        unset($this->aItemsChanges[$name]);
                    }
                }

                foreach($this->aItemsChanges as $name => $aItem){
                    if(isset($aOrderItems[$aItem['item_id']])){
                        // Update item
                        $oItem = $aOrderItems[$aItem['item_id']];
                        $aData = $oItem->data;
                        if(isset($aItem['new']['qty'])){
                            if(!$aItem['new']['qty']){
                                $oItem->delete();
                                continue;
                            }else{
                                $oItem->qty = $aItem['new']['qty'];
                            }
                        }
                        $oItem->data = $this->_fillItemData($aItem['new'], $aData);
                        $oItem->save();
                    }else{
                        // New item
                        $aCommonData = array();
                        foreach($aOrderItems as $itemId => $oItem){
                            $aCommonData = $oItem->data;
                            break;
                        }

                        $aItemData = $aItem['new'];
                        $oProduct = AMI::getResourceModel('eshop_item/table')->find($aItemData['item_id'], array('*'));

                        $aItemInfo = array(
                            'id'                    => $aItemData['item_id'],
                            'name'                  => $oProduct->header,
                            'sku'                   => $oProduct->sku,
                            'rest'                  => $oProduct->rest,
                            'item_type'             => $oProduct->item_type,
                            'variable_price'        => 0, // @todo: !!!
                            'cat_id'                => $oProduct->id_cat,
                            'id_prop'               => 0, // @todo: !!!
                            'id_external'           => $oProduct->id_external,
                            'cat_name'              => '', // @todo: from cat
                            'prop_info'             => array(), // @todo: !!!
                            'sublink'               => $oProduct->sublink,
                            'descr_empty'           => 0, // ?
                            'shipping_method_name'  => $oOrder->custom_info['get_type_name'],
                            'shipping_type'         => 'abs', // ?
                            'shipping'              => 0, // ?
                            'shipping_item'         => 0, // ?
                            'price_null'            => false, // ?
                            'currency'              => $aCommonData['currency']['code'],
                            'owner_name'            => $oProduct->id_owner,
                            'weight'                => $oProduct->weight,
                            'size'                  => $oProduct->size,
                            'picture'               => $oProduct->ext_picture,
                            'popup_picture'         => $oProduct->popup_picture,
                            'small_picture'         => $oProduct->small_picture
                        );
                        $aData = array(
                            'price_currency'        => $aCommonData['price_currency'],
                            'currency'              => $aCommonData['currency'],
                            'name'                  => $oProduct->header,
                            'sku'                   => $oProduct->sku,
                            'item_info'             => $aItemInfo,
                        );

                        $oItem = AMI::getResourceModel('eshop_order_item/table')->getItem();

                        $oItem->qty               = $aItemData['qty'];
                        $oItem->id_order          = $oOrder->id;
                        $oItem->id_product        = $aItemData['item_id'];
                        $oItem->price             = $aItemData['order_price'];
                        $oItem->price_number      = $aItemData['price_number'];
                        $oItem->data              = $this->_fillItemData($aItemData, $aData);
                        $oItem->save();
                    }
                }
            }

            // Save changes to audit table
            if($this->orderChanged){
                $aChanges = array(
                    'order'     => $this->aOrderChanges,
                    'cust_info' => $this->aCustomInfoChanges,
                    'items'     => $this->aItemsChanges
                );

                $oOrder = $aEvent['oItem'];
                $oAudit = AMI::getResourceModel('eshop_order_audit/table')->getItem();
                $oAudit->id_item = $oOrder->id;
                $oAudit->id_actor = AMI::getSingleton('env/session')->getUserData()->getId();
                $oAudit->id_owner = 0;
                $oAudit->item_name = $oOrder->name;
                $oAudit->status_orig = 'changed';
                $oAudit->status_audit = 'changed';
                $oAudit->data = $aChanges;
                $oAudit->lang = AMI_Registry::get('lang_data');
                $oAudit->save();

                // Because it is the end...
                $this->orderChanged = false;
            }
        }
        return $aEvent;
    }

    /**
     * Transfers changes to item data.
     *
     * @param array $aItem  Changes
     * @param array $aData  Item data
     * @return array
     */
    private function _fillItemData(array $aItem,array $aData){
        foreach($aItem as $field => $value){
            switch($field){
                case 'abs_discount':
                    $aData['item_info']['absolute_discount'] = $value;
                    $aData['absolute_discount'] = $value;
                    break;
                case 'prc_discount':
                    $aData['item_info']['percentage_discount'] = $value;
                    $aData['percentage_discount'] = $value;
                    break;
                case 'price':
                    $aData['item_info']['price'] = $value;
                    break;
                case 'original_price':
                    $aData['item_info']['original_price'] = $value;
                    break;
                case 'current_price':
                    $aData['item_info']['cur_price'] = $value;
                    break;
                case 'tax_price':
                    $aData['item_info']['cur_price_tax'] = $value;
                    break;
                case 'order_price':
                    $aData['item_info']['order_price'] = $value;
                    break;
                case 'price_number':
                    $aData['item_info']['price_number'] = $value;
                    break;
                case 'tax_total':
                    $aData['item_info']['tax'] = $value;
                    break;
                case 'tax_type':
                    $aData['item_info']['tax_type'] = $value;
                    break;
                case 'tax_item':
                    $aData['item_info']['tax_item'] = $value;
                    break;
                case 'tax':
                    $aData['item_info']['tax_item_value'] = $value;
                    break;
            }
        }
        return $aData;
    }

    /**
     * Save parameter change, returns true if changed.
     *
     * @param string $value         New value
     * @param mixed $originalValue  Original item property value
     * @param string $property      Property name to use in history
     * @param string $mode          Subject of changes (order/cust_info/item)
     * @param string $key           Item name for item mode
     * @return bool
     */
    private function _collectChanges($value, $originalValue, $property, $mode, $key = null){
        if($originalValue != $value){
            $this->orderChanged = true;
            if($mode == 'order'){
                $this->aOrderChanges['prev'][$property] = $originalValue;
                $this->aOrderChanges['new'][$property] = $value;
            }
            if($mode == 'custom_info'){
                $this->aCustomInfoChanges['prev'][$property] = $originalValue;
                $this->aCustomInfoChanges['new'][$property] = $value;
            }
            if($key && ($mode == 'item')){
                if(!isset($this->aItemsChanges[$key])){
                    $this->aItemsChanges[$key] = array(
                        'prev' => array(),
                        'new' => array()
                    );
                }
                $this->aItemsChanges[$key]['prev'][$property] = $originalValue;
                $this->aItemsChanges[$key]['new'][$property] = $value;
            }
            return true;
        }
        return false;
    }
}

/**
 * AmiClean/EshopOrder module form component view.
 *
 * @package    Config_AmiClean_EshopOrder
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_EshopOrder_FormViewAdm extends Hyper_AmiClean_FormViewAdm{
    /**
     * Reset form default elements template
     *
     * @var array
     */
    protected $aPlaceholders = array('#form', 'form');

    /**
     * Status IDs
     *
     * @var array
     */
    protected $aStatusIds = array('draft', 'printed', 'pending', 'checkout', 'waiting', 'shipped', 'delivered', 'rejected', 'accepted', 'cancelled', 'confirmed', 'confirmed_conditionally', 'confirmed_done');

    /**
     * Requirements status IDs
     *
     * @var array
     */
    protected $aRequirementsStatusIds = array('requirements_draft', 'requirements_accepted', 'requirements_cancelled');

    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        AMI_Event::addHandler('on_before_view_form', array($this, 'handleViewForm'), $this->getModId());

        /*
        AMI_Event::addHandler(
            'handle_order_table_row_before_shown',
            'handleOrderTableRowBeforeShown',
            $this->getModId()
        );
        */

#        parent::init()
        return $this;
    }

    /**
     * Handles getting form scope.
     *
     * @param  string $name    Name
     * @param  array  $aEvent  Event data
     * @return array
     */
    public function handleViewForm($name, array $aEvent){
        $aEvent['aScope']['ami_order_id'] = $this->oItem->id;
        return $aEvent;
    }

    /**
     * Add fields on the form.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     * @amidev Temporary
     */
    public function addFields(){
        $oOrder = $this->oItem;

        // a little hack for ext_images extension
        $aTmpExt = AMI::getOption('eshop_item', 'extensions');
        $tmpSide = AMI_Registry::get('side');
        AMI::setOption('eshop_item', 'extensions', array('ext_images'));
        AMI_Registry::set('side', 'frn');
        AMI::initModExtensions('eshop_item');
        AMI::setOption('eshop_item', 'extensions', $aTmpExt);
        AMI_Registry::set('side', $tmpSide);

        if($oOrder->id){

            $this->addScriptFile('_admin/' . $GLOBALS['CURRENT_SKIN_PATH'] . '_js/' . $this->getModId() . '_form.js');

            /**
             * @var AMI_Template
             */
            $oTpl = $this->getTemplate();

            $bShowImages = AMI::getOption($this->getModId(), "show_image_in_order_details");
            $oTpl->addGlobalVars(array('show_image_in_order_details' => $bShowImages));

            $aLocalOrder = $oTpl->parseLocale("_local/_admin/templates/lang/eshop_order.lng", AMI_Registry::get('lang_data'));
            $this->aLocale = array_merge($this->aLocale, $aLocalOrder);

            $this->addField(array('name' => 'mod_id', 'value' => $this->getModId(), 'type' => 'hidden'));
            $this->addField(array('name' => 'id', 'type' => 'hidden'));
            $this->addField(array('name' => 'mod_action', 'type' => 'hidden', 'value' => 'form_save'));
            $this->addField(array('name' => 'id_static', 'type' => 'static', 'value' => $oOrder->id));
            $this->addField(array('name' => 'name_static', 'type' => 'static', 'position' => 'id_static.begin', 'value' => $oOrder->name));

            $this->addTabContainer('edit_modes');

            /**
             * STATUS TAB
             */
            $this->addTab('statustab', 'edit_modes');

            // Get username from members
            $oUser = AMI::getResourceModel('users/table')->find($oOrder->id_user);
            $login = ($oUser->id) ? $oUser->login : $oOrder->login;
            $this->addField(array('name' => 'login_static', 'type' => 'static', 'position' => 'statustab.begin', 'value' => $login));

            // Format date and time from Mysql to local format
            $date = AMI_Lib_Date::formatDateTime($oOrder->date_created, AMI_Lib_Date::FMT_BOTH);
            $this->addField(array('name' => 'date_created', 'type' => 'static', 'position' => 'login_static.after', 'value' =>$date));

            // Order status dropdown selection box
            $aStatuses = array();
            $aStatusIds = $this->aStatusIds;
            if(AMI::getOption($this->getModId(), "use_requirements_order")){
                $aStatusIds = array_merge($aStatusIds, $this->aRequirementsStatusIds);
            }
            foreach($aStatusIds as $statusId){
                $aStatuses[] = array(
                    'name'  => $this->aLocale[$statusId],
                    'value' => $statusId
                );
            }
            $this->addField(array('name' => 'status', 'type' => 'select', 'position' => 'date_created.after', 'data' =>$aStatuses));
            $this->addField(array('name' => 'adm_comments', 'type' => 'textarea', 'position' => 'status.after', 'rows' => 3));

            $oEshop = new EshopAdmin($GLOBALS['cms'], $this->aLocale, $this->getModId());
            $oEshop->initByOwnerName('eshop');

            /**
             * @var AMI_EshopOrder
             */
            $oEshopOrder = AMI::getSingleton('eshop_order');
            $oEshopOrder->setDefaultCurrency($oOrder->data['base_currency']['code']);
            $oEshopOrder->clearCache($oOrder->id);

            /**
             * @var AMI_DB
             */
            $oDB = AMI::getSingleton('db');

            // DB table prefix
            $dbTablePrefix = substr($oOrder->getTable()->getTableName(), 0, -7);

            // Items total
            $total = $oEshopOrder->getTotal($oOrder, 'items_total', AMI_EshopOrder::FMT_MONEY);
            $this->addField(array('name' => 'goods_value', 'type' => 'static', 'position' => 'adm_comments.after', 'value' => $total));

            // Taxes
            $itemsTax = $oEshopOrder->getTotal($oOrder, 'items_tax');
            $shippingTax = $oEshopOrder->getTotal($oOrder, 'shipping_tax');
            $exciseTax = $oEshopOrder->getTotal($oOrder, 'excise_tax');
            $tax = $oEshopOrder->format($itemsTax + $shippingTax + $exciseTax, AMI_EshopOrder::FMT_MONEY);
            $this->addField(array('name' => 'tax', 'type' => 'static', 'position' => 'goods_value.after', 'value' => $tax));

            // Shipping
            $shipping = $oEshopOrder->getTotal($oOrder, 'shipping', AMI_EshopOrder::FMT_MONEY);
            $this->addField(array('name' => 'shipping', 'type' => 'static', 'position' => 'tax.after', 'value' => $shipping));

            // Amount
            $amount = $oEshopOrder->getTotal($oOrder, 'amount', AMI_EshopOrder::FMT_MONEY);
            $this->addField(array('name' => 'amount', 'type' => 'static', 'position' => 'shipping.after', 'value' => $amount));

            // Applied discount
            $appliedDiscount = $shipping = $oEshopOrder->getTotal($oOrder, 'applied_discount');
            if($appliedDiscount){
                $discount = $shipping = $oEshopOrder->getTotal($oOrder, 'applied_discount', AMI_EshopOrder::FMT_MONEY);
                $amountNum = $oEshopOrder->getTotal($oOrder, 'items_total');
                $taxNum = $itemsTax + $shippingTax + $exciseTax;
                $percent = ($amountNum + $taxNum + $appliedDiscount) > 0
                    ? 100 * (1 - ($amountNum + $taxNum) / ($amountNum + $taxNum + $appliedDiscount))
                    : 0;
                $discountPercent = $oEshop->formatPercent($percent, FALSE);
                $discount .= ' (' . $discountPercent . ' %)';
                $this->addField(array('name' => 'applied_discount', 'type' => 'static', 'position' => 'tax.after', 'value' => $discount));
            }

            // Payment tax
            if(is_array($oOrder->system_info) && isset($oOrder->system_info['fee_percent'])){
                $paymentTax = $oEshopOrder->getTotal($oOrder, 'payment_tax', AMI_EshopOrder::FMT_MONEY);
                $this->addField(array('name' => 'payment_tax_static', 'type' => 'static', 'position' => 'amount.after', 'value' => $paymentTax));
            }

            // Customer info
            $custInfo = '';
            if(is_array($oOrder->custom_info)){
                if(array_key_exists('country', $oOrder->custom_info)){
                    $aCountries = $oTpl->parseLocale("_local/_admin/templates/lang/country.lng", AMI_Registry::get('lang_data'));
                }
                // $customerINN = $oOrder->custom_info["inn"];
                // $customerKPP = $oOrder->custom_info["kpp"];
                if($oOrder->system_info["person_type"] == "natural"){
                    $customerName = $oOrder->firstname . " " . $oOrder->lastname;
                    $customerAddress = $oEshopOrder->getCustomerAddress($oOrder->custom_info);
                }else{
                    $customerName = $oOrder->company;
                    $customerAddress = $oOrder->custom_info['address'];
                }
                $custInfo .= $this->aLocale['ordered'] . ': ' . $customerName . "<br>";
                $custInfo .= $this->aLocale['email'] . ': ' . $oOrder->email . "<br>";

                // Coupons
                foreach($oOrder->custom_info as $name => $val){
                    if(($name == 'id_coupon') && AMI::isModInstalled($oOrder->custom_info['coupon_owner'] . '_coupons')){
                        // @todo: remove hardcode, create models for coupons
                        // $this->oEshop->initByOwnerName($aCustInfo['coupon_owner']);
                        $snippet = "SELECT co.coupon, coc.name coupon_category FROM `%s` co INNER JOIN `%s` coc ON co.id_cat = coc.id WHERE co.id = %s";
                        $aResult = $oDB->fetchRow(
                            DB_Query::getSnippet($snippet)
                            ->plain($dbTablePrefix . '_coupons')
                            ->plain($dbTablePrefix . '_coupons_cats')
                            ->plain(intval($val))
                        );
                        if(count($aResult) == 2){ // coupon and coupon_category
                            $coupon = $aResult['coupon'] . ' (' . $aResult['coupon_category'] . ')';
                            $this->addField(array('name' => 'coupon', 'type' => 'static', 'value' => $coupon, 'position' => 'shipping.before'));
                        }
                    }
                    if(!empty($this->aLocale[$name])){
                        if($name == 'country' && isset($aCountries[$val])){
                            $val = $aCountries[$val];
                        }
                        $custInfo .= $this->aLocale[$name] . ': ' . nl2br($val) . "<br>";
                    }
                }
                if(isset($oOrder->custom_info["shipping_conflicts"])){
                    $shippingConflicts = $oOrder->custom_info["shipping_conflicts"];
                }
            }
            $this->addField(array('name' => 'customer_info', 'type' => 'static', 'position' => 'statustab.end', 'value' => $custInfo, 'skipHTMLEncoding' => true));

            // Comments
            $this->addField(array('name' => 'comments_static', 'type' => 'static', 'position' => 'customer_info.after', 'value' => $oOrder->comments));

            // System info
            require_once $GLOBALS['ROOT_PATH'] . '_shared/code/const/billing_drivers.php';
            $sysInfo = '';
            foreach($oOrder->system_info as $name => $val){
                if($name != "payment_type" && isset($this->aLocale[$name])){
                    if(!empty($this->aLocale[$val])){
                        $sysInfo .= $this->aLocale[$name] . ': ' . $this->aLocale[$val] . "<br>";
                    }elseif($name == 'driver' && isset($GLOBALS['BILLING_DRIVERS_ALIASES'][$val]) && !empty($this->aLocale[$GLOBALS['BILLING_DRIVERS_ALIASES'][$val]])){
                        $sysInfo .= $this->aLocale[$name] . ': ' . $this->aLocale[$GLOBALS['BILLING_DRIVERS_ALIASES'][$val]] . "<br>";
                    }else{
                        $sysInfo .= $this->aLocale[$name] . ': ' . nl2br($val) . "<br>";
                    }
                }
            }
            $this->addField(array('name' => 'system_info', 'type' => 'static', 'position' => 'comments_static.after', 'value' => $sysInfo, 'skipHTMLEncoding' => true));

            // Order details
            $enableWeightCol = false;
            $enableSizeCol = false;
            $aOrderItems = $oEshopOrder->getItems(array($oOrder));
            $aOrderIds = array();
            if(isset($aOrderItems[$oOrder->id])){
                foreach($aOrderItems[$oOrder->id] as $oItem){
                    $aOrderItemsIds[] = $oItem->id_product;
                    if(!empty($oItem->data["item_info"]["weight"])){
                        $enableWeightCol = true;
                    }
                    if(!empty($oItem->data["item_info"]["size"])){
                        $enableSizeCol = true;
                    }
                    if($enableWeightCol && $enableSizeCol){
                        break;
                    }
                }
            }

            // Digitals
            $eshopDigitalsOn = false;
            $eshopDigitalsHeader = '';
            $aItemTypes = AMI::getOption('eshop_item', 'available_item_types');
            foreach($aItemTypes as $index => $typeModId){
                if(AMI::isModInstalled($typeModId)){
                    if($typeModId == 'eshop_digitals'){
                        if(isset($aOrderItems[$oOrder->id])){
                            foreach($aOrderItems[$oOrder->id] as $oItem){
                                $aItemData = $oItem->data['item_info'];
                                if($aItemData['item_type'] == $typeModId){
                                    $eshopDigitalsOn = true;
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            if($eshopDigitalsOn){
                if(isset($aData["eshop_digitals"]["expire"])){
                    // expire time text field
                    $eshopDigitalsExpire = AMI_Lib_Date::formatUnixTime($aData["eshop_digitals"]["expire"], AMI_Lib_Date::FMT_BOTH);
                    $eshopDigitalsExpire = $oTpl->parse($this->tplBlockName . ':eshop_digitals_expire', array('eshop_digitals_expire' => $eshopDigitalsExpire));
                }

                $aIdProducts = array();
                foreach($aOrderItems[$oOrder->id] as $oItem){
                    $aIdProducts[] = $oItem->id_product;
                }

                // get eshop files names
                $snippet = "SELECT id_product, id, original_fname FROM " . $dbTablePrefix . "_files WHERE id_product IN (%s) AND active=1";
                $aFiles = $oDB->select(DB_Query::getSnippet($snippet)->implode($aIdProducts));
                $aEshopFiles = array();
                foreach($aFiles as $aFile){
                    $aEshopFiles[$aFile["id_product"]][$aFile["id"]] = array("name" => $aFile["original_fname"]);
                }

                $eshopDigitalsHeader = $oTpl->parse($this->tplBlockName . ':eshop_digitals_header');
                // $eshopDigitalsLegend = '';
                // No such sets in admin templates
                // $aItem["eshop_digitals_legend"] = $this->cms->Gui->getAbs($this->cms->ActiveModule."_list:leg_darkgreen_eshop","");
                // $aItem["eshop_digitals_legend"] .= $this->cms->Gui->getAbs($this->cms->ActiveModule."_list:leg_darkred_eshop","");
            }

            // get installed clone owners
            $ownerNameHeader = '';
            $aCloneOwners = Array();
            $aOwnersProperty = AMI::getProperty('core', "eshop_clone_owners");
            $Core = $GLOBALS['Core'];
            foreach($aOwnersProperty as $ownerName){
                if($Core->IsOwnerInstalled($ownerName)){
                    $aCloneOwners[$ownerName] = 1;
                }
            }
            $numOwners = sizeof($aCloneOwners);
            if($numOwners > 1){
                $ownerNameHeader = $oTpl->parse($this->tplBlockName . ':item_row_ownername_header');
            }

            // Collect product data and get as a table
            $productList = '';
            $productData = array();

            $addSql = Array("select"=>"", "from"=>"", "join"=>"", "where"=>"", "group"=>"", "order"=>"");

            // Digitals addon (from extension)
            if($eshopDigitalsOn){
                $addSql["select"] = ", i.num_files, i.files_size";
            }

            // Todo: check if custom fields extension enabled
            $addSql["join"] .= "LEFT JOIN cms_es_props AS po ON po.id = oi.id_prop";
            $addSql["select"] .= ", po.price AS order_prop_price0, NOT ISNULL(po.id) AS order_is_prop, po.id AS order_propId, oi.id_prop";

            $i = 0;
            $oldOwner = '';
            if(isset($aOrderItems[$oOrder->id])){

                // get rows via eshop_item/model
                $oRealItemsList = AMI::getResourceModel('eshop_item/table')->getList();
                $oRealItemsList
                    ->addColumns(array('id', 'ext_img', 'ext_img_small', 'ext_img_popup'))
                    ->addWhereDef(DB_Query::getSnippet('AND i.`id` IN (%s)')->implode($aOrderItemsIds, FALSE))
                    ->load();

                $aRealItems = array();
                foreach($oRealItemsList as $oRealItem){
                    $aRealItems[$oRealItem->id] = array(
                        'ext_popup_picture'     => $oRealItem->ext_img_popup,
                        'ext_small_picture'     => $oRealItem->ext_img_small,
                        'ext_picture'           => $oRealItem->ext_img,
                    );
                    $rootPath = AMI_Registry::get('path/www_root');
                    foreach($aRealItems[$oRealItem->id] as $picType => $picSource){
                        if(substr($picSource, 0, strlen($rootPath)) != $rootPath){
                            $aRealItems[$oRealItem->id][$picType] = $rootPath.$picSource;
                        }
                    }
                }

                foreach($aOrderItems[$oOrder->id] as $oItem){
                    $style = ($i++ % 2) ? "row2" : "row1";
                    if($oldOwner != $oItem->owner_name){
                        $oEshop->initByOwnerName($oItem->owner_name);
                        $oldOwner = $oItem->owner_name;
                    }
                    $aData = $oItem->data;
                    $aItem = $aData['item_info'];
                    $snippet = "SELECT i.id, i.name, i.price AS price0" . $oEshop->getPriceFields("i.price%d, SUBSTRING(c.price%d, INSTR(c.price%d, %s)+1, 3) AS db_currency%d") .
                        ", i.tax,i.tax_type,i.charge_tax_type,i.tax_class_type,i.id_tax_class,i.discount,i.discount_type, i.item_type, i.sku, " .
                        "c.id_discount " . $addSql["select"] . " " .
                        "FROM cms_es_order_items oi LEFT JOIN " . $dbTablePrefix . "_items i ON (oi.id_product = i.id) " .
                        "LEFT JOIN " . $dbTablePrefix . "_cats c ON (c.id = i.id_category) " . $addSql["join"] . " WHERE oi.id_order = %s AND i.id = %s";
                    $oQuery = DB_Query::getSnippet($snippet);
                    if($oEshop->otherPricesEnabled){
                        for($i=0; $i < $oEshop->numPrices; $i++){
                            $oQuery->q(':');
                        }
                    }
                    $aProduct = $oDB->fetchRow($oQuery->plain($oOrder->id)->plain($oItem->id_product));
                    $orderName = $aData['name'];
                    $newName = '';
                    $propertyCaption = '';
                    $currPrice = '';
                    if(is_array($aProduct)){
                        // real name
                        if($aProduct["name"] != $orderName){
                            $newName = $aProduct["name"];
                        }

                        if(isset($aData['property_caption'])){
                            $propertyCaption = $aData['property_caption'];
                        }

                        $priceNumber = $oItem->price_number;
                        if($aProduct["price".$priceNumber] != ""){
                            $oEshop->pushCurrencyData($aData["currency"], $oOrder->data["base_currency"]["code"]);
                            $aPrice = $oEshop->calcPrice(
                                $aProduct["price" . $priceNumber],
                                $priceNumber,
                                $aProduct["tax"],
                                $aProduct["tax_type"],
                                $aProduct["charge_tax_type"],
                                $aProduct["discount"],
                                $aProduct["discount_type"],
                                $oEshopOrder->getDefaultCurrency(),
                                $aProduct["db_currency" . $priceNumber],
                                true,
                                array(
                                    "qty"         => $oItem->qty,
                                    "id_discount" => $aProduct["id_discount"]
                                ),
                                array(
                                    "tax_class_type" => $aProduct["tax_class_type"],
                                    "id_tax_class"   => $aProduct["id_tax_class"]
                                )
                            );
                            $currPrice = $aPrice["price_value"];
                        }
                    }else{
                        $newName = $this->aLocale['unknown'];
                    }

                    if(isset($aItem['shipping_method_name'])){
                        $shippingMethodName = $aItem['shipping_method_name'];
                    }

                    $price = $oItem->price;
                    $priceTotal = $oEshop->convertCurrency($price, $oOrder->data["buy_currency"]["code"], $oEshopOrder->getDefaultCurrency());
                    $priceTotal = $oEshop->formatNumber($priceTotal, true, true) * $oItem->qty;

                    $priceCaption = "";
                    if($oEshop->otherPricesEnabled){
                        if($oItem->price_number > 0){
                            $priceCaptions = $aData["price_captions"];
                            $caption = $priceCaptions[$id][$priceNumber];
                        }else{
                            $caption = $this->aLocale["base_price_caption"];
                        }
                        if($caption){
                            $priceCaption = $oTpl->parse($this->tplBlockName . ':price_caption', array("price_caption" => $caption));
                        }
                    }

                    $price = $oEshop->convertCurrency($price, $oOrder->data["buy_currency"]["code"], $oEshopOrder->getDefaultCurrency());

                    if($newName){
                        $newName = $oTpl->parse(
                            $this->tplBlockName . ':item_row_newname',
                            array(
                                'name'          => $newName,
                                'name_short'    => AMI_Lib_String::truncate($newName, 27)
                            )
                        );
                    }

                    $weight = 0;
                    $totalWeight = 0;
                    if(!empty($aItem["weight"])){
                        $weight = $aItem["weight"];
                        $totalWeight = $weight * $oItem->qty;
                    }

                    $size = '';
                    if(!empty($aItem["size"])){
                        $size = $aItem["size"];
                    }

                    if($numOwners > 1){
                        $ownerData = $GLOBALS['Core']->OwnerGetData($oItem->owner_name);
                        $ownerName = $ownerData["admin_menu_caption"];
                        $ownerName = $oTpl->parse($this->tplBlockName . ':item_row_ownername', array('owner_name' => $ownerName, 'style' => $style));
                    }

                    // Digitals
                    $eshopDigitalsCol = '';
                    if($eshopDigitalsOn){
                        $aItemFiles = (isset($aEshopFiles[$oItem->id_product])) ? $aEshopFiles[$oItem->id_product] : false;
                        if(is_array($aItemFiles)){
                            $aStoredInfo = $aData["ext_info"]["eshop_digitals"];
                            if(sizeof($aStoredInfo) > 0 && sizeof($aItemFiles)){
                                $aTmpItems = $aStoredInfo + $aItemFiles;
                            }elseif(sizeof($aStoredInfo) > 0){
                                $aTmpItems = &$aStoredInfo;
                            }elseif(sizeof($aItemFiles) > 0){
                                $aTmpItems = &$aItemFiles;
                            }else{
                                $aTmpItems = array();
                            }
                            if(sizeof($aTmpItems) > 0){
                                $eshopDigitalsRows = "";
                                foreach($aTmpItems as $fileId=>$fileInfo){
                                    $fileData = Array();
                                    $fileData["atempts_disabled"] = "";
                                    if(isset($aStoredInfo[$fileId])){
                                        if(isset($aItemFiles[$fileId])){
                                            // this file ok
                                            $fileData["name_color"] = "#000000";
                                            unset($aItemFiles[$fileId]);
                                        }else{
                                            // this file was deleted from system
                                            $fileData["name_color"] = "#D00000";
                                            $fileData["atempts_disabled"] = "disabled";
                                        }
                                    }else{
                                        // this file was added later, then order created
                                        $fileData["name_color"] = "#00A000";
                                    }
                                    $fileData["id"] = $oItem->id;
                                    $fileData["file_id"] = $fileId;
                                    $fileData["filename"] = $fileInfo["name"];
                                    $fileData["short_filename"] = AMI_Lib_String::truncate($fileInfo["name"], 32);
                                    if(isset($aData["eshop_digitals"]["download_attempts"][$fileId]["rest"])){
                                        $fileData["number_attempts"] = $aData["eshop_digitals"]["download_attempts"][$fileId]["rest"];
                                        $fileData["number_initial_attempts"] = $aData["eshop_digitals"]["download_attempts"][$fileId]["initial"];
                                    }else{
                                        $fileData["number_initial_attempts"] = $fileData["number_attempts"] = AMI::getOption('eshop_digitals', "max_download_attempts");
                                    }

                                    $eshopDigitalsRows .= $oTpl->parse($this->tplBlockName . ':eshop_digitals_row', $fileData);
                                }
                                $eshopDigitalsCol = $oTpl->parse(
                                    $this->tplBlockName . ':eshop_digitals_col',
                                    array(
                                        'style'                 => $style,
                                        'eshop_digitals_rows'   => $eshopDigitalsRows,
                                        'index'                 => $i,
                                        'id'                    => $oItem->id
                                    )
                                );
                            }
                        }else{
                            $eshopDigitalsCol = $oTpl->parse($this->tplBlockName . ':eshop_digitals_col_na', array('style' => $style));
                        }
                    }

                    // Table row
                    $aScope = array(
                        'style'                         => $style,
                        'ENABLE_WEIGHT_COL'             => $enableWeightCol,
                        'ENABLE_SIZE_COL'               => $enableSizeCol,
                        'sku'                           => $aProduct['sku'],
                        'purchase_sku'                  => (($aItem['sku'] == $aProduct['sku']) ? "" : $aItem['sku']),
                        'order_name'                    => $orderName,
                        'order_name_short'              => AMI_Lib_String::truncate($orderName, 27),
                        'new_name'                      => $newName,
                        'order_property_caption'        => $propertyCaption,
                        'order_property_caption_short'  => AMI_Lib_String::truncate($propertyCaption, 27),
                        'curr_price'                    => $oEshopOrder->format($currPrice, AMI_EshopOrder::FMT_MONEY),
                        'shipping_conflicts'            => $shippingConflicts,
                        'shipping_method_name'          => $shippingMethodName,
                        'purchase_price'                => $oEshopOrder->format($price, AMI_EshopOrder::FMT_MONEY),
                        'total'                         => $oEshopOrder->format($priceTotal, AMI_EshopOrder::FMT_MONEY),
                        'qty'                           => $oItem->qty,
                        'weight'                        => $weight,
                        'total_weight'                  => $totalWeight,
                        'size'                          => $size,
                        'owner_name'                    => $ownerName,
                        'price_caption'                 => $priceCaption,
                        'eshop_digitals_col'            => $eshopDigitalsCol,
                        'ext_popup_picture'             => isset($aRealItems[$aProduct['id']]['ext_popup_picture']) ? $aRealItems[$aProduct['id']]['ext_popup_picture'] : null,
                        'ext_small_picture'             => isset($aRealItems[$aProduct['id']]['ext_small_picture']) ? $aRealItems[$aProduct['id']]['ext_small_picture'] : null,
                    );

                    $aEvent = array(
                        'oRequest'  => AMI::getResource('env/request'),
                        'oResponse' => AMI::getSingleton('response'),
                        'aItemRow'  => &$aScope
                    );

                    AMI_Event::fire('handle_order_table_row_before_shown', $aEvent, $this->getModId());

                    $productList .= $orderDetails = $oTpl->parse($this->tplBlockName . ':item_row', $aScope);
                }
            }

            // Get product list table header
            $aScope = array(
                'ENABLE_WEIGHT_COL'     => $enableWeightCol,
                'ENABLE_SIZE_COL'       => $enableSizeCol,
                'owner_name_header'     => $ownerNameHeader,
                'eshop_digitals_header' => $eshopDigitalsHeader,
                'eshop_digitals_legend' => '', // $eshopDigitalsLegend,
                'shipping_conflicts'    => $shippingConflicts,
                'product_list'          => $productList
            );
            $orderDetails = $oTpl->parse($this->tplBlockName . ':order_details', $aScope);
            $this->addField(array('name' => 'order_details', 'type' => 'static', 'position' => 'system_info.after', 'html' => $orderDetails, 'skipHTMLEncoding' => true));

            // Status history
            $aStatHistory = $oOrder->status_history;
            $aHistory = array();
            if(is_array($aStatHistory)){
                $i = 0;
                $aHistory["status_list"] = '';
                foreach($aStatHistory as $date => $aDetails){
                    $aDetails['date'] = AMI_Lib_Date::formatUnixTime($date, AMI_Lib_Date::FMT_BOTH);
                    $aDetails['status'] = $this->aLocale[$aDetails['status']];
                    $aDetails['comments'] = nl2br($aDetails['comments']);
                    $aDetails['changed_by'] = $this->aLocale[$aDetails['type']];

                    $style = "row2";

                    if($i%2){
                        $style="row1";
                    }

                    $aDetails["style"] = $style;

                    $aHistory["status_list"] .= $oTpl->parse($this->tplBlockName . ':status_row', $aDetails);
                }
                $history = $oTpl->parse($this->tplBlockName . ':history', $aHistory);
            }
            $this->addField(array('name' => 'order_history', 'type' => 'static', 'position' => 'order_details.after', 'html' => $history, 'skipHTMLEncoding' => true));

            /**
             * ITEMS TAB
             */
            $this->addTab('itemstab', 'edit_modes');

            $this->addField(array('name' => 'login', 'position' => 'itemstab.begin'));
            $this->addField(array('name' => 'email'));
            $this->addField(array('name' => 'firstname'));
            $this->addField(array('name' => 'lastname'));
            $this->addField(array('name' => 'company'));

            $this->addSection('buyer_info', 'company.after');
            // eshop == hardcode!
            $oEshop->_initShippingModule('eshop');
            $aShippingData = $oEshop->shippingData;
            $aShippingMethods = (isset($aShippingData['methodsNames']) && is_array($aShippingData['methodsNames'])) ? $aShippingData['methodsNames'] : array();
            $aShippingSelect = array();
            $shippingMethod = is_array($oOrder->custom_info) && isset($oOrder->custom_info['get_type_name']) ? $oOrder->custom_info['get_type_name'] : '';
            $isShippingMethodFound = FALSE;
            foreach($aShippingMethods as $id => $method){
                if($method){
                    $aShippingSelect[] = array(
                        'name'  => $method,
                        'value' => $method
                    );
                    $isShippingMethodFound = $isShippingMethodFound || ($method === $shippingMethod);
                }
            }
            if(!$isShippingMethodFound){
                $aShippingSelect[] = array(
                    'name'  => $shippingMethod,
                    'value' => $shippingMethod
                );
            }
            if(is_array($oOrder->custom_info)){
                foreach($oOrder->custom_info as $name => $val){
                    if($name == 'country' && isset($aCountries[$val])){
                        $val = $aCountries[$val];
                    }
                    // Hack to avoid creating spare lang vars
                    if(isset($this->aLocale[$name]) && !isset($this->aLocale['caption_ci_' . $name])){
                        $this->aLocale['caption_ci_' . $name] = $this->aLocale[$name];
                    }
                    if($name == 'get_type_name'){
                        if(($oOrder->custom_info['shipping_conflicts'] == 'show_shipping_for_each_type') || !count($aShippingSelect)){
                            $this->addField(array('name' => 'ci_' . $name, 'type' => 'static', 'position' => 'buyer_info.end', 'value' => $val));
                        }else{
                            $this->addField(array('name' => 'ci_' . $name, 'type' => 'select', 'position' => 'buyer_info.end', 'data' => $aShippingSelect, 'value' => $val));
                        }
                    }else{
                        if($name != 'shipping_conflicts'){
                            $this->addField(array('name' => 'ci_' . $name, 'position' => 'buyer_info.end', 'value' => $val));
                        }
                    }
                }
            }

            $this->addField(array('name' => 'comments', 'type' => 'textarea', 'position' => 'buyer_info.after'));
            $this->addField(array('name' => 'tracking_number', 'position' => 'buyer_info.after'));
            // $this->addField(array('name' => 'adm_comments_static', 'type' => 'static', 'position' => 'comments.after', 'value' => $oOrder->adm_comments));

            $aBuyCurrencies = array();
            foreach($oOrder->data['currency'] as $code => $aCurrencyData){
                if($code != ''){
                    $aBuyCurrencies[] = array(
                        'name' => $code,
                        'value' => $aCurrencyData['exchange']
                    );
                }
            }
            $this->addSection('refresh_currency', 'comments.after'); // adm_comments_static.after

            $this->addField(array('name' => 'base_currency', 'type' => 'static', 'position' => 'refresh_currency.end', 'value' => $oOrder->data['base_currency']['code']));

            /*
            // Payment tax
            if(is_array($oOrder->system_info) && isset($oOrder->system_info['fee_percent'])){
                $paymentTax = $oEshopOrder->getTotal($oOrder, 'payment_tax', AMI_EshopOrder::FMT_NONE);
                $this->addField(array('name' => 'payment_tax', 'position' => 'adm_comments_static.after', 'value' => $paymentTax));
            }
            */
            /*
            Not needed.
            $this->addField(array('name' => 'buy_currency', 'type' => 'select', 'data' => $aBuyCurrencies, 'position' => 'refresh_currency.end', 'value' => $oOrder->data['buy_currency']['code']));
            $this->addField(array('name' => 'buy_exchange', 'position' => 'refresh_currency.end', 'value' => $oOrder->data['currency'][$oOrder->data['buy_currency']['code']]['exchange'], 'attributes' => array('style' => 'width:100px')));
             */

            $productsList = '';
            $index = 0;
            foreach($aOrderItems[$oOrder->id] as $oItem){
                $origPrice   = $oItem->data['item_info']['original_price'];
                $taxPrice    = $oItem->data['item_info']['cur_price_tax'];
                $chargeType  = ($taxPrice > $origPrice) ? "charge" : "detach"; // Only way I've found

                $absDiscount = $oItem->data['item_info']['absolute_discount'];
                $prcDiscount = $oItem->data['item_info']['percentage_discount'];

                if(is_null($absDiscount)){
                    $absDiscount = round(($origPrice * $oItem->qty * $prcDiscount) / 100, 2);
                }

                if(is_null($prcDiscount)){
                    $prcDiscount = ($origPrice * $oItem->qty) ? round((($absDiscount / ($origPrice * $oItem->qty)) * 100), 2) : 0;
                }

                $itemPropsCaption = '';
                if(isset($oItem->data['item_info']['prop_info'])){
                    foreach($oItem->data['item_info']['prop_info'] as $prop){
                        if(strlen($itemPropsCaption) > 0){
                            $itemPropsCaption .= ' ';
                        }
                        $itemPropsCaption .= $prop['name'].':'.$prop['value'];
                    }
                }

                $tmpItemId = $oItem->data['item_info']['id'];
                $aRow = array(
                    'order_item_id'         => $oItem->id,
                    'item_index'            => $index,
                    'item_id'               => $oItem->data['item_info']['id'],
                    'item_name'             => $oItem->data['name'],
                    'item_props_caption'    => $itemPropsCaption,
                    'item_sku'              => $oItem->data['item_info']['sku'],
                    'item_qty'              => $oItem->qty,
                    'item_original_price'   => $origPrice,
                    'item_price'            => $oItem->data['item_info']['price'],
                    'item_cur_price'        => $oItem->data['item_info']['cur_price'],
                    'item_cur_price_tax'    => $taxPrice,
                    'item_order_price'      => $oItem->data['item_info']['order_price'],
                    'item_price_number'     => $oItem->data['item_info']['price_number'],
                    'item_abs_discount'     => $absDiscount,
                    'item_prc_discount'     => $prcDiscount,
                    'item_tax'              => $oItem->data['item_info']['tax'],
                    'item_tax_type'         => $oItem->data['item_info']['tax_type'],
                    'item_tax_item'         => $oItem->data['item_info']['tax_item'],
                    'item_tax_item_value'   => $oItem->data['item_info']['tax_item_value'],
                    'item_charge_tax_type'  => $chargeType,
                    'item_currency'         => $oOrder->data['currency'][$oOrder->data['base_currency']['code']]['name'],
                    'ext_popup_picture'     => isset($aRealItems[$tmpItemId]['ext_popup_picture']) ? $aRealItems[$tmpItemId]['ext_popup_picture'] : null,
                    'ext_small_picture'     => isset($aRealItems[$tmpItemId]['ext_small_picture']) ? $aRealItems[$tmpItemId]['ext_small_picture'] : null,
                );
                $productsList .= $oTpl->parse($this->tplBlockName . ':products_edit_table_row', $aRow);
                $index++;
            }


            $aScope = array(
                'products_edit_list'    => $productsList,
                'shipping'              => $oOrder->shipping,
                'item_currency'         => $oOrder->data['currency'][$oOrder->data['base_currency']['code']]['name']
            );
            $productsEdit = $oTpl->parse($this->tplBlockName . ':products_edit_table', $aScope);
            $this->addField(array('name' => 'products_edit_table', 'type' => 'static', 'position' => 'refresh_currency.after', 'html' => $productsEdit, 'skipHTMLEncoding' => true));

            // Order changes
            $hasChanges = false;
            $oAuditList = AMI::getResourceModel('eshop_order_audit/table')->getList();
            $oAuditList->addColumns(array('date_created', 'ip', 'id_actor', 'data'))->addWhereDef(DB_Query::getSnippet("AND id_item = %s")->plain($oOrder->id))->addOrder('date', 'desc')->load();
            $changeList = '';
            foreach($oAuditList as $oAuditItem){
                $author = 'undefined';
                if($oAuditItem->id_actor){
                    $oUser = AMI::getResourceModel('users/table')->find($oAuditItem->id_actor, array('id', 'login'));
                    if($oUser->id){
                        $author = $oUser->login;
                    }
                }

                $original = '';
                $changes = '';
                // Order data
                if(count($oAuditItem->data['order']['new'])){
                    $aChangesName = array(
                        'action'        => $this->aLocale['order_changes_title'],
                        'name'          => false
                    );
                    $changes .= $this->_getChangesList($oAuditItem->data['order']['prev'], $oAuditItem->data['order']['new'], $aChangesName, $oEshopOrder);
                }
                // Customer info
                if(count($oAuditItem->data['cust_info']['new'])){
                    $aChangesName = array(
                        'action'        => $this->aLocale['ci_changes_title'],
                        'name'          => false
                    );
                    $changes .= $this->_getChangesList($oAuditItem->data['cust_info']['prev'], $oAuditItem->data['cust_info']['new'], $aChangesName, $oEshopOrder);
                }
                // Items
                if(count($oAuditItem->data['items'])){
                    foreach($oAuditItem->data['items'] as $name => $aItem){
                        $action = $this->aLocale['changes_title'];
                        if($aItem['new']['item_id'] && !$aItem['prev']['item_id']){
                            $action = $this->aLocale['new_title'];
                        }
                        if(isset($aItem['new']['qty']) && !$aItem['new']['qty']){
                            $action = $this->aLocale['deleted_title'];
                        }
                        $aChangesName = array(
                            'action'    => $action,
                            'name'      => $name
                        );
                        $changes .= $this->_getChangesList($aItem['prev'], $aItem['new'], $aChangesName, $oEshopOrder, true);
                    }
                }
                if(strlen($changes)){
                    $aItemRow = array(
                        'date'      => AMI_Lib_Date::formatDateTime($oAuditItem->date_created, AMI_Lib_Date::FMT_BOTH),
                        'ip'        => $oAuditItem->ip,
                        'author'    => $author,
                        'changes'   => $changes
                    );
                    $changeList .= $oTpl->parse($this->tplBlockName . ':order_change_row', $aItemRow);
                    $hasChanges = true;
                }
            }
            $aScope = array(
                'order_change_list' => $changeList,
                'changed' => $hasChanges,
            );
            $orderChanges = $oTpl->parse($this->tplBlockName . ':order_change_table', $aScope);
            $this->addField(array('name' => 'order_changes_table', 'type' => 'static', 'position' => 'products_edit_table.after', 'html' => $orderChanges, 'skipHTMLEncoding' => true));


            $this->addSection('notify', 'itemstab.after');
        }
        return $this;
    }

    /**
     * Generates list of changes.
     *
     * @param array $aOldValues            Old values
     * @param array $aNewValues            New values
     * @param array $aTitle                Table caption
     * @param AMI_EshopOrder $oEshopOrder  AMI_EshopOrder object
     * @param bool $isItem                 Is item changes (false by default)
     *
     * @return string
     */
    private function _getChangesList(array $aOldValues, array $aNewValues, array $aTitle, AMI_EshopOrder $oEshopOrder, $isItem = false){
        $oTpl = $this->getTemplate();
        $rows = '';
        $aMoneyFields = array('total', 'tax_total', 'abs_discount', 'shipping', 'price', 'current_price', 'tax_item', 'order_price', 'tax_price', 'original_price');
        if(!$isItem){
            $aMoneyFields[] = 'tax';
        }
        foreach($aNewValues as $field => $newValue){
            $oldValue = (isset($aOldValues[$field])) ? $aOldValues[$field] : '-';
            $caption = $field;
            if(isset($this->aLocale['caption_' . $field . '_static'])){
                $caption = $this->aLocale['caption_' . $field . '_static'];
            }
            if(isset($this->aLocale['caption_' . $field])){
                $caption = $this->aLocale['caption_' . $field];
            }
            if(isset($this->aLocale[$field])){
                $caption = $this->aLocale[$field];
            }

            if(in_array($field, $aMoneyFields)){
                if(strlen($oldValue) && ($oldValue != '-')){
                    $oldValue = $oEshopOrder->format($oldValue, AMI_EshopOrder::FMT_MONEY);
                }
                $newValue = $oEshopOrder->format($newValue, AMI_EshopOrder::FMT_MONEY);
            }elseif($field == 'tax_type'){
                if($oldValue != '-'){
                    $oldValue = $this->aLocale[$oldValue];
                }
                if($newValue != '-'){
                    $newValue = $this->aLocale[$newValue];
                }
            }

            $rows .= $oTpl->parse($this->tplBlockName . ':order_changes_row', array('name' => $caption, 'old_value' => $oldValue, 'new_value' => $newValue));
        }
        $title = $oTpl->parse($this->tplBlockName . ':changes_title', $aTitle);
        return $oTpl->parse($this->tplBlockName . ':order_changes', array('title' => $title, 'rows' => $rows));
    }

}

/**
 * AmiClean/EshopOrder module admin list component action controller.
 *
 * @package    Config_AmiClean_EshopOrder
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_EshopOrder_ListAdm extends AMI_Module_ListAdm{
    /**
     * Initialization.
     *
     * @return EshopOrder_ListAdm
     */
    public function init(){
        $this->addJoinedColumns(array('login', 'firstname', 'lastname', 'fullname'), 'u');
        $this->addActions(array(self::REQUIRE_FULL_ENV . 'edit', self::REQUIRE_FULL_ENV . 'delete'));
        $this->addActions(array('print'));

        parent::init();

        // Delete default group actions
        $this->dropActions(self::ACTION_GROUP, array('seo_section', 'common_section', 'meta_section'));

        $this->addGroupActions(array(array(self::REQUIRE_FULL_ENV . 'change_status', 'change_status_section')));
        $this->addActionCallback('group', 'grp_change_status_section');

        return $this;
    }
}

/**
 * AmiClean/EshopOrder module admin list component view.
 *
 * @package    Config_AmiClean_EshopOrder
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_EshopOrder_ListViewAdm extends AMI_Module_ListViewAdm{
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
        '#columns', 'id', 'u_fullname', 'u_login', 'name', 'date_created', 'status', 'amount', 'columns',
        '#actions', 'actions',
        'list_header'
    );

    /**
     * Default currency code
     *
     * @var string
     */
    protected $defaultCurrency;

    /**
     * Array of EshopOrderItem_TableItem
     *
     * @var array
     */
    protected $aOrderItems;

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return EshopOrder_ListViewAdm
     */
    public function init(){
        parent::init();

        $modId = $this->getModId();
        $oEshop = AMI::getSingleton('eshop');

        $this->dropPlaceholders(array('position', 'public', 'header', 'announce'));
        $this
            ->addColumn('id')->addColumnType('id', 'int')
            ->addColumn('u_fullname')
            ->addColumn('u_login')
            ->addColumn('name')
            ->addColumnType('status', 'float')
            ->addColumnType('amount', 'float')
            ->addColumnType('sum', 'hidden')
            ->addColumnType('firstname', 'none')
            ->addColumnType('lastname', 'none')
            ->addColumnType('login', 'none')
            ->addColumnType('data', 'none')
            ->addColumnType('shipping', 'none')
            ->addColumnType('shipping_tax', 'none')
            ->addColumnType('excise_tax', 'none')
            ->addColumnType('custom_info', 'none')
            ->addColumnType('system_info', 'none')
            ->addColumnType('total', 'none')
            ->addColumnType('tax', 'none')
            ->addSortColumns(array('id', 'u_fullname', 'u_login', 'name', 'status', 'amount'))
            ->addColumnFooter('status', array('caption' => 'total'))
            ->addColumnFooter(
                'amount',
                array(
                    'data' => array(
                        'numberDecimals' => $oEshop->getNumberDecimals(),
                        'decimalPoint' => $oEshop->getDecimalPoint(),
                        'thousandsSeparator' => $oEshop->getThousandsSeparator()
                    )
                )
            )
            ->setColumnTensility('u_fullname')
            ->addSortColumns(array('id', 'u_fullname', 'u_login', 'name', 'status', 'amount'));

        $this->formatColumn('u_fullname', array($this, 'fmtFullname'));
        $this->formatColumn('status', array($this, 'fmtLocaleCaption'));
        $this->formatColumn('amount', array($this, 'fmtAmount'));
        $this->formatColumn('sum', array($this, 'fmtSum'));

        // Truncate 'header' column by 50 symbols
        $this->formatColumn('name', array($this, 'fmtTruncate'), array('length' => 50));

        $this->defaultCurrency = AMI::getOption($modId, 'default_currency');
        $this->defaultCurrency = $this->defaultCurrency[AMI_Registry::get('lang_data')];

        AMI_Event::addHandler('on_sort_columns', array($this, 'handleSortColumns'), $modId);
        AMI_Event::addHandler('on_list_init', array($this, 'handleListInit'), $modId);

        $this->addScriptFile('_admin/' . $GLOBALS['CURRENT_SKIN_PATH'] . '_js/' . $modId . '_list.js');

        return $this;
    }

    /**#@+
     * Event handler.
     *
     * @see AMI_Event::addHandler()
     * @see AMI_Event::fire()
     */

    /**
     * Adds late data binding for comments counter.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListInit($name, array $aEvent, $handlerModId, $srcModId){
        if($handlerModId === $this->getModId()){
            $aEvent['oList']->addWhereDef('AND `show_in_admin` = 1');
        }
        return $aEvent;
    }

    /**
     * Handles sort columns event to load order items to count totals.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleSortColumns($name, array $aEvent, $handlerModId, $srcModId){
        if(is_null($this->aOrderItems)){
            /**
             * @var AMI_EshopOrder
             */
            $oEshopOrder = AMI::getSingleton('eshop_order');
            $this->aOrderItems = $oEshopOrder->getItems($aEvent['oList']);
        }
        return $aEvent;
    }

    /**#@-*/

    /**
     * Returns prepared view scope.
     *
     * @param  string $type    View type
     * @param  array  $aScope  Scope
     * @return array
     */
    protected function getScope($type, array $aScope = array()){
        $aPrintForms = AMI::getOption($this->getModId(), 'printed_forms');
        if(is_array($aPrintForms) && sizeof($aPrintForms) == 1){
            $aScope['default_print_form'] = AMI::getOption($this->getModId(), 'default_printed_form');
        }
        return parent::getScope($type, $aScope);
    }

    /**
     * Fullname column formatter.
     *
     * @param  string $value  Value to format
     * @param  array  $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::handleFormatCell()
     */
    protected function fmtFullname($value, array $aArgs){
        if(is_null($value)){
            /**
             * @var AMI_ModTableItem
             */
            $oItem = $aArgs['oItem'];
            $orderUserName = '';
            if($oItem->firstname != ''){
                $orderUserName = $oItem->firstname . ' ';
            }
            $orderUserName .= $oItem->lastname;
            if($orderUserName == ''){
                $orderUserName = $oItem->login;
            }
            $oTpl = $this->getTemplate();
            $value = $oTpl->parse($this->tplBlockName . ':order_user_name', array('order_user_name' => $orderUserName));
        }
        return $value;
    }

    /**
     * Amount (total) column formatter.
     *
     * @param  string $value  Value to format
     * @param  array  $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::handleFormatCell()
     */
    protected function fmtAmount($value, array $aArgs){
        /**
         * @var EshopOrder_TableItem
         */
        $oOrder = $aArgs['oItem'];
        /**
         * @var AMI_EshopOrder
         */
        $oEshopOrder = AMI::getSingleton('eshop_order');
        $oOrder->sum = $oEshopOrder->getTotal($oOrder, 'amount', AMI_EshopOrder::FMT_NONE);
        return $oEshopOrder->getTotal($oOrder, 'amount', AMI_EshopOrder::FMT_MONEY);
    }

    /**
     * Return unformatted amount (total) column.
     *
     * @param  string $value  Value to format
     * @param  array  $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::handleFormatCell()
     */
    protected function fmtSum($value, array $aArgs){
        return $aArgs['oItem']->sum;
    }
}

/**
 * AmiClean/EshopOrder custom component controller.
 *
 * @package    Config_AmiClean_EshopOrder
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_EshopOrder_CustomComponentAdm extends Hyper_AmiClean_ComponentAdm{
    /**
     * Returns component type.
     *
     * @return string
     */
    public function getType(){
        return 'custom_component';
    }

    /**
     * Returns true if component needs to be started always in full environment.
     *
     * @return bool
     */
    public function isFullEnv(){
        return TRUE;
    }
}

/**
 * AmiClean/EshopOrder custom component view.
 *
 * @package    Config_AmiClean_EshopOrder
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_EshopOrder_CustomComponentViewAdm extends Hyper_AmiClean_ComponentViewAdm{
    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        $oResponse = AMI::getSingleton('response');
        $oResponse->setBuffering(FALSE, TRUE);
        $oResponse->setBenchType('LONG');

        $oTpl = $this->getTemplate();
        $oTpl->addBlock('progress', 'templates/_data_exchange_form.tpl');
        $oGUI = AMI_Registry::get('oGUI');
        $oGUI->addScript($GLOBALS['CURRENT_SKIN_PATH'] . '_js/ami.jquery.js');

        $aScope = array(
            'metas' => $oGUI->getMetas(),
            'scripts' => $oGUI->getScripts()
        );
        $oResponse->write(
            $oTpl->parse('progress:progress_popup_header', $aScope)
        );
        $oResponse->write(
            '<br /><br /><br /><br />'
        );

        $oRequest = AMI::getSingleton('env/request');
        $status = $oRequest->get('status', FALSE);
        $ids = trim(trim($oRequest->get('mod_action_id', ''), ','));
        $aIds = $ids ? explode(',', $ids) : array();
        $count = 0;
        if($status && sizeof($aIds)){
            $oCMS = $GLOBALS['cms'];
            $oEshop = new EshopAdmin($oCMS, $this->aLocale, $this->getModId());
            $oEshop->initByOwnerName('eshop');
            $oCMS->Eshop = $oEshop;

            $oEshopOrder = new EshopOrder();
            $oEshopOrder->aCurrency = &$oEshop->aCurrency;

            foreach($aIds as $orderId){
                @set_time_limit(29);
                $orderId = (int)$orderId;
                if(!$orderId){
                    continue; // foreach($aIds as $orderId)
                }
                $oItem =
                    AMI::getResourceModel($this->getModId() . '/table')
                    ->find($orderId);
                if($oItem->getId() && $status !== $oItem->status){
                    $res =
                        $oEshopOrder->updateStatus(
                            $oCMS,
                            $orderId,
                            'admin',
                            $status,
                            array(),
                            $oItem->status
                        );
                    if($res){
                        $oResponse->write(
                            $this->parse('status_of_order_changed', array('orderId' => $orderId)) .
                            str_repeat(' ', 4096)
                        );
                        ++$count;
                        if($count >= 99){
                            break;
                        }
                    }
                }
            }
        }else{ // if($status && sizeof($aIds))
        }

        if($count){
            $aScope = array(
                'count' => $count,
                'total' => sizeof($aIds)
            );
            $oResponse->write(
                $this->parse('orders_changed', $aScope)
            );
        }else{
            $oResponse->write(
                $this->parse('no_order_changed')
            );
        }
        $oResponse->write(
            $oTpl->parse('progress:progress_popup_footer', $aScope)
        );

        return '';
    }
}
