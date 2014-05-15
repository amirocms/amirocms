<?php
/**
 * AmiEshopShipping hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiEshopShipping
 * @version   $Id: Hyper_AmiEshopShipping_Table.php 49989 2014-04-17 10:51:25Z Leontiev Anton $
 * @since     6.0.6
 */

/**
 * AmiEshopShipping hypermodule table model.
 *
 * @package    Hyper_AmiEshopShipping
 * @subpackage Model
 * @since      6.0.6
 */
abstract class Hyper_AmiEshopShipping_Table extends AMI_ModTable{
}

/**
 * AmiEshopShipping hypermodule table item model.
 *
 * @package    Hyper_AmiEshopShipping
 * @subpackage Model
 * @since      6.0.6
 */
abstract class Hyper_AmiEshopShipping_TableItem extends AMI_Module_TableItem{
    /**
     * Allow to save model flag.
     *
     * @var bool
     */
    protected $bAllowSave = TRUE;

    /**
     * Common fields validators
     *
     * @var array
     */
    protected $aCommonFieldsValidators =
        array(
            'lang'   => array('filled'),
            'header' => array('filled', 'stop_on_error')
        );

    /**
     * Types to methods relations database table name
     *
     * @var string
     * @see Hyper_AmiEshopShipping_TableItem::fcbTypesToMethodsField()
     */
    protected $typesXMethodsTableName = 'cms_es_shipping_types_methods';

    /**
     * Types to methods model field name
     *
     * @var string
     * @see Hyper_AmiEshopShipping_TableItem::fcbTypesToMethodsField()
     */
    protected $typesToMethodsModelField;

    /**
     * Types to methods column field name
     *
     * @var string
     * @see Hyper_AmiEshopShipping_TableItem::fcbTypesToMethodsField()
     */
    protected $typesToMethodsColumnField;

    /**
     * Types to methods column field name
     *
     * @var string
     * @see Hyper_AmiEshopShipping_TableItem::fcbTypesToMethodsField()
     */
    protected $typesToMethodsConditionField;

    /**
     * Types/Methods field callback.
     *
     * @param  array $aData  Formatter data
     * @return array
     * @see    AmiEshopShipping_Methods_TableItem::__construct()
     * @see    AmiEshopShipping_Types_TableItem::__construct()
     */
    protected function fcbTypesToMethodsField(array $aData){
        $action = $aData['action'];
        $modelField = $this->typesToMethodsModelField;

        switch($action){
            case 'get':
                $notFilled = !isset($this->aData[$modelField]);
                if($this->id){
                    if($notFilled){
                        $oDB = AMI::getSingleton('db');
                        $oQuery =
                            DB_Query::getSnippet(
                                "SELECT `%s` " .
                                "FROM `%s`" .
                                "WHERE `%s` = %s"
                            )
                            ->plain($this->typesToMethodsColumnField)
                            ->plain($this->typesXMethodsTableName)
                            ->plain($this->typesToMethodsConditionField)
                            ->q($this->id);
                        $this->aData[$modelField] = iterator_to_array($oDB->fetchCol($oQuery));
                    }
                }elseif($notFilled){
                    $this->aData[$modelField] = array();
                }
                $aData['value'] = $this->aData[$modelField];
                break; // case 'get'
            case 'after_save':
                if($this->id && isset($this->aData[$modelField])){
                    $this->aData[$modelField] = array_unique($this->aData[$modelField]);
                    $this->deleteRelations($this->id);
                    $oDB = AMI::getSingleton('db');
                    foreach($this->aData[$modelField] as $id){
                        $oQuery =
                            DB_Query::getInsertQuery(
                                $this->typesXMethodsTableName,
                                array(
                                    $this->typesToMethodsColumnField    => $id,
                                    $this->typesToMethodsConditionField => $this->id
                                )
                            );
                        $oDB->query($oQuery);
                    }
                }
                break; // case 'after_save'
            case 'delete':
                $this->deleteRelations($aData['value']);
                break; // case 'delete'
        }

        return $aData;
    }

    /**
     * Deletes related types/methods.
     *
     * @param  int $id  Source item Id
     * @return void
     * @see    Hyper_AmiEshopShipping_TableItem::fcbTypesToMethodsField();
     * @see    Hyper_AmiEshopShipping_TableItem::fcbTypesToMethodsField();
     */
    protected function deleteRelations($id){
        $oDB = AMI::getSingleton('db');
        $oQuery =
            DB_Query::getSnippet(
                "SELECT `%s` " .
                "FROM `%s`" .
                "WHERE `%s` = %s"
            )
            ->plain($this->typesToMethodsColumnField)
            ->plain($this->typesXMethodsTableName)
            ->plain($this->typesToMethodsConditionField)
            ->q($id);
    }
}

/**
 * AmiEshopShipping hypermodule table list model.
 *
 * @package    Hyper_AmiEshopShipping
 * @subpackage Model
 * @since      6.0.6
 */
abstract class Hyper_AmiEshopShipping_TableList extends AMI_ModTableList{
}
