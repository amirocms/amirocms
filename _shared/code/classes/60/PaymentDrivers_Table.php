<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module_Catalog
 * @version   $Id: EshopItemProps_Table.php 44504 2013-11-27 10:21:28Z Leontiev Anton $
 * @since     6.0.6
 */

/**
 * Eshop payment drivers table model.
 *
 * @package    Module_Catalog
 * @subpackage Model
 * @resource   payment_drivers/table/model <code>AMI::getResourceModel('payment_drivers/table')*</code>
 * @since      6.0.6
 */
class PaymentDrivers_Table extends AMI_ModTable{
    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model 
     */
    public function __construct(array $aAttributes = array()){
        parent::__construct($aAttributes);

        $this->tableName = 'cms_pay_drivers';

        $aRemap = array(
            'id'            => 'id',
            'public'        => 'is_installed',
            'header'        => 'name',
            'date_modified' => 'modified_date',
            'is_online'     => '',
            'code'          => ''
        );

        $this->addFieldsRemap($aRemap);
    }
}

/**
 * Eshop payment drivers table item model.
 *
 * @package    Module_Catalog
 * @subpackage Model
 * @resource   payment_drivers/table/model/item <code>AMI::getResourceModel('payment_drivers/table')->getItem()*</code>
 * @since      6.0.6
 */
class PaymentDrivers_TableItem extends AMI_Module_TableItem{
    /**
     * Allow to save model flag.
     *
     * @var    bool
     * @amidev Temporary
     */
    protected $bAllowSave = false;
    /**
     * Initializing table item data.
     *
     * @param AMI_ModTable $oTable  Module table model
     * @param DB_Query     $oQuery  Required for load or save operations
     * @amidev
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery = null){
        parent::__construct($oTable, $oQuery);
        $this->setFieldCallback('header', array($this, 'fcbDriverTitle'));
        $this->setFieldCallback('is_online', array($this, 'fcbOnline'));
        $this->setFieldCallback('code', array($this, 'fcbCode'));
    }

    /**
     * Driver header field callback.
     *
     * @param  array $aData  Formatter data
     * @return array
     * @amidev
     */
    protected function fcbDriverTitle(array $aData){
        $action = $aData['action'];

        switch($action){
            case 'get':
                $value = $aData['value'];
                $lngFile = AMI_Registry::get('path/root') . '_local/eshop/pay_drivers/' . $value . '/driver.lng';
                if(file_exists($lngFile) && AMI_Lib_FS::validatePath($lngFile)){
                    $oTpl = AMI::getResource('env/template');
                    $oTpl->setLocale(AMI_Registry::get('lang_data', 'en'));
                    $aLang = $oTpl->parseLocale($lngFile);
                    if(isset($aLang['driver_title'])){
                        $value = $aLang['driver_title'];
                    }
                }
                $aData['value'] = $value;
                break;
        }

        return $aData;
    }

    /**
     * Driver is_online field callback.
     *
     * @param  array $aData  Formatter data
     * @return array
     * @amidev
     */
    protected function fcbOnline(array $aData){
        $action = $aData['action'];

        switch($action){
            case 'get':
                $aData['value'] = true;
                break;
        }

        return $aData;
    }

    /**
     * Driver code field callback.
     *
     * @param  array $aData  Formatter data
     * @return array
     * @amidev
     */
    protected function fcbCode(array $aData){
        $action = $aData['action'];
        switch($action){
            case 'get':
                $aData['value'] = $this->aData['header'];
                break;
        }

        return $aData;
    }
}

/**
 * Eshop payment drivers table list model.
 *
 * @package    Module_Catalog
 * @subpackage Model
 * @resource   payment_drivers/table/model/list <code>AMI::getResourceModel('payment_drivers/table')->getList()*</code>
 * @since      6.0.6
 */
class PaymentDrivers_TableList extends AMI_ModTableList{
}
