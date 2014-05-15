<?php
/**
 * AmiEshopShipping/Types configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiEshopShipping_Types
 * @version   $Id: AmiEshopShipping_Types_Table.php 48620 2014-03-12 08:07:41Z Leontiev Anton $
 * @since     6.0.6
 */

/**
 * AmiEshopShipping/Types configuration table model.
 *
 * See {@link AMI_ModTable::getAvailableFields()} for common fields description.
 * Fields description:
 * - <b>is_default</b> - default method flag, use 0 always (int);
 * - <b>hidden</b> - if 1, type will not be displayed in interface (int).
 * - <b>methods</b> - array of associated methods ids (array).
 *
 * @package    Config_AmiEshopShipping_Types
 * @subpackage Model
 * @resource   {$modId}/table/model <code>AMI::getResourceModel('{$modId}/table')*</code>
 * @since      6.0.6
 */
class AmiEshopShipping_Types_Table extends Hyper_AmiEshopShipping_Table{
    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'cms_es_shipping_types';

    /**
     * Initializing table data.
     *
     * @param array $aAttributes  Attributes of table model
     */
    public function __construct(array $aAttributes = array()){
        $this->setDependence('ami_eshop_shipping_types_methods', 'tm', "tm.id_type=i.id", 'LEFT OUTER JOIN');
        $this->setActiveDependence('tm');
        $this->setDependence('ami_eshop_shipping_types_methods', 'tm2', "tm2.id_type=i.id", 'LEFT OUTER JOIN');
        $this->setActiveDependence('tm2');

        parent::__construct($aAttributes);

        $aRemap = array(
            'header'        => 'name',
            'date_modified' => 'modified_date'
        );
        $this->addFieldsRemap($aRemap);
    }

    /**
     * Sets new table name for a model.
     *
     * @param string $tableName  New table name
     * @return void
     * @amidev Temporary
     */
    public function setTableName($tableName){
    }
}

/**
 * AmiEshopShipping/Types configuration table item model.
 *
 * @package    Config_AmiEshopShipping_Types
 * @subpackage Model
 * @resource   {$modId}/table/model/item <code>AMI::getResourceModel('{$modId}/table')->getItem()*</code>
 * @since      6.0.6
 */
class AmiEshopShipping_Types_TableItem extends Hyper_AmiEshopShipping_TableItem{
    /**
     * Types to methods model field name
     *
     * @var string
     */
    protected $typesToMethodsModelField = 'methods';

    /**
     * Types to methods column field name
     *
     * @var string
     */
    protected $typesToMethodsColumnField = 'id_method';

    /**
     * Types to methods column field name
     *
     * @var string
     */
    protected $typesToMethodsConditionField = 'id_type';

    /**
     * Constructor.
     *
     * Initializes table item data.
     *
     * @param AMI_ModTable $oTable  Module table model
     * @param DB_Query     $oQuery  Required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery = null){
        parent::__construct($oTable, $oQuery);

        $this->setFieldCallback(
            $this->typesToMethodsModelField,
            array($this, 'fcbTypesToMethodsField')
        );
    }

    /**
     * Methods field callback.
     *
     * @param  array $aData  Formatter data
     * @return array
     */
    protected function fcbMethodsField(array $aData){
        $action = $aData['action'];

        switch($action){
            case 'get':
                $notFilled = !isset($this->aData['methods']);
                if($this->id){
                    if($notFilled){
                        $oDB = AMI::getSingleton('db');
                        $oQuery =
                            DB_Query::getSnippet(
                                "SELECT `id_method` " .
                                "FROM `%s`" .
                                "WHERE `id_type` = %s"
                            )
                            ->plain($this->typesXMethodsTableName)
                            ->q($this->id);
                        $this->aData['methods'] = iterator_to_array($oDB->fetchCol($oQuery));
                    }
                }elseif($notFilled){
                    $this->aData['methods'] = array();
                }
                $aData['value'] = $this->aData['types'];
                break; // case 'get'
            case 'after_save':
                d::vd(123);###
                if($this->id && isset($this->aData['types'])){
                    $this->aData['types'] = array_unique($this->aData['types']);
                    $oDB = AMI::getSingleton('db');
                    $oQuery =
                        DB_Query::getSnippet(
                            "DELETE FROM `%s`" .
                            "WHERE `id_method` = %s"
                        )
                        ->plain($this->typesXMethodsTableName)
                        ->q($this->id);
                    $oDB->query($oQuery);
                    foreach($this->aData['types'] as $typeId){
                        $oQuery =
                            DB_Query::getInsertQuery(
                                $this->typesXMethodsTableName,
                                array(
                                    'id_type'   => $typeId,
                                    'id_method' => $this->id
                                )
                            );
                        $oDB->query($oQuery);
                    }
                }
                break; // case 'after_save'
        }

        return $aData;
    }
}

/**
 * AmiEshopShipping/Types configuration table list model.
 *
 * @package    Config_AmiEshopShipping_Types
 * @subpackage Model
 * @resource   {$modId}/table/model/list <code>AMI::getResourceModel('{$modId}/table')->getList()*</code>
 * @since      6.0.6
 */
class AmiEshopShipping_Types_TableList extends Hyper_AmiEshopShipping_TableList{
    /**
     * Initializing table list data.
     *
     * @param AMI_ModTable $oTable  Table model
     * @param DB_Query     $oQuery  DB_Query object, required for load or save operations
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery){
        parent::__construct($oTable, $oQuery);

        $this->addExpressionColumn('methods_count', 'COUNT(DISTINCT(`tm`.`id_method`))', 'tm');
    }
}
