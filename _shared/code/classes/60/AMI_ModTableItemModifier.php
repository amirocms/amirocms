<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModTableItemModifier.php 44504 2013-11-27 10:21:28Z Leontiev Anton $
 * @since     5.12.4
 */

/**
 * Module table item model modifier.
 *
 * Class containing model modification functionality.
 *
 * Example:
 * <code>
 * class AmiSample_TableItemModifier extends AMI_ModTableItemModifier{
 * }
 * </code>
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @since      5.12.4
 */
abstract class AMI_ModTableItemModifier{
    /**
     * Module table model
     *
     * @var AMI_ModTable
     */
    protected $oTable;

    /**
     * Module table item model
     *
     * @var AMI_ModTableItem
     */
    protected $oTableItem;

    /**
     * Validator exception on item saving
     *
     * @var AMI_ModTableItemException|null
     */
    protected $oValidatorException;

    /**
     * Model meta data processor.
     *
     * Contains resource id created on AMI_Mod::save() to process meta fields
     * i.e. 'sublink', 'sm_data' and etc.<br />
     * Resource class must implement AMI_iModTableItemMeta interface.
     *
     * @var    string
     * @see    AMI_ModTableItemModifier::save()
     * @amidev Temporary
     */
    protected $metaResId = 'table/item/model/meta';

    /**
     * Field lengths for system validator
     *
     * @var array
     * @see AMI_ModTableItem::validate()
     */
    private $aFieldLengths = array(
        'tinytext'   => 255,
        'text'       => 65535,
        'mediumtext' => 16777215,
        // 'longtext'   => 4294967295,
        'tinyblob'   => 255,
        'blob'       => 65535,
        'mediumblob' => 16777215,
        // 'longblob'   => 4294967295
    );

    /**
     * Meta object usage flag
     *
     * @var bool
     * @see AMI_ModTableItemModifier::save()
     */
    private $useMetaObj;

    /**
     * Sets if meta functionality is needed
     *
     * @var AMI_ModTableItemMeta
     * @see AMI_ModTableItemModifier::save()
     */
    private $oMeta;

    /**
     * Constructor.
     *
     * @param AMI_ModTable     $oTable      Module table model
     * @param AMI_ModTableItem $oTableItem  Module table item model
     */
    public function __construct(AMI_ModTable $oTable, AMI_ModTableItem $oTableItem){
        $this->oTable     = $oTable;
        $this->oTableItem = $oTableItem;
    }

    /**
     * Saves current item data.
     *
     * @return AMI_ModTableItemModifier
     * @throws AMI_ModTableItemException  If validation failed.
     * @see    AMI_ModTableItem::save()
     */
    public function save(){
        if(is_null($this->useMetaObj)){
            $this->useMetaObj = false;
            $aLoadedFields = $this->oTableItem->getLoadedFields();
            foreach(array('sublink', 'sm_data') as $systemField){
                if(
                    $this->oTable->hasField($systemField) && (
                        !$this->oTableItem->getId() ||
                        in_array('*', $aLoadedFields) ||
                        in_array($systemField, $aLoadedFields)
                    )
                ){
                    $this->useMetaObj = true;
                    break;
                }
            }
            unset($aLoadedFields);
            if($this->useMetaObj){
                $this->oMeta = AMI::getResource($this->metaResId);
                AMI_Event::addHandler(
                    'on_before_save_model_item',
                    array($this->oMeta, 'handleSaveModelItem'),
                    $this->oTableItem->getModId()
                );
            }
        }

        $onCreate = $this->oTableItem->getId() === $this->oTableItem->getEmptyId();
        $aDefaults = $this->getDefaultsOnSave($onCreate);
        $aData = array_merge($this->oTableItem->getRawData() + $aDefaults['append'], $aDefaults['overwrite']);
        unset($aDefaults);

        try{
            $this->oValidatorException = null;
            $this->validate($aData, $onCreate);
        }catch(AMI_ModTableItemException $oException){
            $this->oValidatorException = $oException;
            $this->rollback($aData, $onCreate);
            throw $oException;
        }

        $aEvent = array(
            'onCreate'     => $onCreate,
            'oTable'       => $this->oTable,
            'oTableItem'   => $this->oTableItem, // @deprecated since 5.14.0
            'oItem'        => $this->oTableItem,
            'aData'        => &$aData
        ) + $this->oTableItem->getMetaData();
        /**
         * Allows change data model before create / update item.
         *
         * @event      on_before_save_model_item $modId
         * @eventparam bool             onCreate    TRUE on new item creation, FALSE on existing item saving
         * @eventparam AMI_ModTable     oTable      Table model
         * @eventparam AMI_ModTableItem oItem       Table item model
         * @eventparam array            aData       Table item raw data
         */
        AMI_Event::fire('on_before_save_model_item', $aEvent, $this->oTableItem->getModId());

        $aVirtualFields = $this->oTableItem->getVirtualFields('file');
        foreach($aVirtualFields as $name => $aField){
            if(isset($aData[$name]) && is_object($aData[$name])){
                foreach($aField['aParams']['aMapping'] as $field => $param){
                    $aData[$field] = $aData[$name]->getParameter($param);
                }
                unset($aData[$name]);
            }
        }

        foreach($this->oTableItem->getSerializedFields() as $field){
            if(isset($aData[$field]) && !is_string($aData[$field])){
                $aData[$field] = @serialize($aData[$field]);
            }
        }

        $aData = $this->oTableItem->remapFields($aData);

        $aTableFields = $this->oTable->getTableFieldsData();
        $aSqlData = array();
        if(!AMI_Service::isDebugSkipped()){
            $skippedFields = '';
        }
        foreach($aData as $key => $value){
            if($key == $this->oTableItem->getPrimaryKeyField()){
                continue;
            }
            if(isset($aTableFields[$key])){
                $aSqlData[$key] = $value;
            }elseif(isset($skippedFields)){
                $skippedFields .= $key . ', ';
            }
        }
        if(!sizeof($aSqlData)){
            trigger_error("Empty data inserting to '" . $this->oTableItem->getQuery()->getMainTableName() . "'", E_USER_ERROR);
        }

        if(!empty($skippedFields)){
            d::dump(
                'Invalid (skipped) item model fields: ' . mb_substr($skippedFields, 0, -2) . ".<br />\n" .
                'Reason: not declared in class ' . get_class($this->oTable) . ' (table `' . $this->oTable->getTableName() . "`).",
                '',
                array('method' => 'printf', 'no_pre' => true)
            );
        }

        foreach(array('urgent_date', 'date_sticky_till') as $stickyField){
            if(isset($aSqlData[$stickyField]) && $aSqlData[$stickyField] == ''){
                $aSqlData[$stickyField] = DB_Query::getSnippet('NULL');
            }
        }
        array_walk($aSqlData, array($this, 'cbPrepareSQL'));

        if($onCreate){
            $sql = DB_Query::getInsertQuery(
                $this->oTableItem->getQuery()->getMainTableName(),
                $aSqlData
            );
        }else{
            $sql = DB_Query::getUpdateQuery(
                $this->oTableItem->getQuery()->getMainTableName(),
                $aSqlData,
                'WHERE ' . $this->oTableItem->getPrimaryKeyField() . ' = ' . $this->oTableItem->getValue($this->oTableItem->getPrimaryKeyField())
            );
        }

        /**
         * @var AMI_iDB
         */
        $oDB = AMI::getSingleton('db');
        $success = false;
        if($oDB->query($sql)){
            $success = true;
            if($onCreate){
                $this->oTableItem->setData(array($this->oTableItem->getPrimaryKeyField() => $oDB->getInsertId()), true, true);
            }
        }

        $aEvent = array(
            'onCreate'     => $onCreate,
            'oTable'       => $this->oTable,
            'oItem'        => $this->oTableItem,
            'oTableItem'   => $this->oTableItem, // @deprecated since 5.14.0
            'aData'        => &$aData,
            'success'      => $success
        ) + $this->oTableItem->getMetaData();
        /**
         * Allows perform actions after creating / updating item
         *
         * @event      on_after_save_model_item $modId
         * @eventparam bool             nCreate  TRUE on new item creation, FALSE on existing item saving
         * @eventparam AMI_ModTable     oTable   Table model
         * @eventparam AMI_ModTableItem oItem    Table item model
         * @eventparam array            aData    Table item raw data
         * @eventparam bool             success  TRUE on success
         */
        AMI_Event::fire('on_after_save_model_item', $aEvent, $this->oTableItem->getModId());

        return $this;
    }

    /**
     * Returns default fields and its values on save.
     *
     * Result is an array having two keys: 'append' and 'overwrite'.<br />
     * Let your model have datetime field 'ts' and you need to set it to the current date/time on every INSERT/UPDATE query.<br /><br />
     *
     * Example:
     * <code>
     * class AmiSample_TableItemModifier extends AMI_ModTableItemModifier{
     *     // ...
     *     public function getDefaultsOnSave($onCreate){
     *         $aDefaults = parent::getDefaultsOnSave($onCreate);
     *         $aDefaults['append']['ts'] = DB_Query::getSnippet('%s')->plain('NOW()');
     *         $aDefaults['overwrite']['ts'] = DB_Query::getSnippet('%s')->plain('NOW()');
     *         return $aDefaults;
     *     }
     *     // ...
     * }
     * </code>
     *
     * @param  bool $onCreate  True on item create, false on update
     * @return array           Array having keys as field names and values as field values
     * @see    AMI_ModTableItem::save()
     * @todo   Expect default item locale (`lang`)
     */
    public function getDefaultsOnSave($onCreate){
        if($onCreate){
            $date = date('Y-m-d H:i:s');
            $aAppend =
                array(
                    'public'        => 0,
                    'date_created'  => $date,
                    'date_modified' => $date,
                    'lang'          => AMI_Registry::get('lang_data', 'en')
                );
            if($this->oTable->hasField('position')){
                $position =
                    AMI::getSingleton('db')
                    ->fetchValue(
                        "SELECT `position` FROM " . $this->oTable->getTableName() .
                        " ORDER BY `position` DESC LIMIT 1",
                        AMI_DB::QUERY_SYSTEM
                    );
                $aAppend['position'] = $position !== false ? $position + 1 : 0;
            }
            return array(
                'append'    => $aAppend,
                'overwrite' => array()
            );
        }else{
            return array(
                'append'    => array(),
                'overwrite' => array(
                	'date_modified' => date('Y-m-d H:i:s'),
            	)
            );
        }
    }

    /**
     * Validates item data.
     *
     * @param  array $aData     Item data
     * @param  bool  $onCreate  True on item create, false on update
     * @return AMI_ModTableItemModifier
     * @throws AMI_ModTableItemException  If validation failed.
     * @todo   Decide reaction for snippets
     */
    public function validate(array $aData, $onCreate){
        $aErrors = array();
        foreach($this->oTableItem->getValidators() as $field => $aValidators){
            if(
                ($onCreate ? false : !isset($aData[$field])) ||
                $field === $this->oTableItem->getPrimaryKeyField()
            ){
                continue;
            }
            if(isset($aData[$field])){
                if(is_object($aData[$field])){
                    continue;
                }
                $value = $aData[$field];
            }else{
                $value = null;
            }
            // $value = isset($aData[$field]) ? $aData[$field] : null;
/*
            if(mb_strpos($value, '=||') === 0){
                continue;
            }
*/
            foreach($aValidators as $validator){
                $isError = false;
                $message = '';
                if($validator == 'required'){
                    $isError = is_null($value);
                    $message = 'Missing field';
                }elseif($validator == 'filled'){
                    $isError = is_null($value) || $value === '';
                    $message = 'Empty field';
                }elseif(!$onCreate || !is_null($value)){
                    $message = 'Invalid data format';
                    // Skip validation for empty values except validator 'required'
                    $isFilled = !is_null($value) && $value !== '';
                    switch($validator){
                        case 'int':
                            $isError = $isFilled && !preg_match('/^-?[0-9]+$/', (string)$value);
                            break; // case 'int'

                        case 'float':
                        case 'double':
                            $isError = $isFilled && (float)($value) != $value;
                            break; // case 'float', case 'double'
/*
                        case 'datetime':

                            $format = AMI::getDateFormat(AMI_Registry::get('lang', 'en'), 'PHP');
                            $format = 'Y-m-d H:i:s';

                        case 'date':
                            if(empty($format)){
                                $format = AMI::getDateFormat(AMI_Registry::get('lang', 'en'), 'PHP_DATE');
                            }
                            $isError = $value !== date($format, strtotime($value));
                            break;
*/
                        case 'datetime':
                            $regexp = '/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/';
                        case 'date':
                            if($value){
                                if(empty($regexp)){
                                    $regexp = '/^(\d{4})-(\d{2})-(\d{2})$/';
                                }
                                if(preg_match($regexp, $value, $aMatches)){
                                    if(
                                        checkdate($aMatches[2], $aMatches[3], $aMatches[1]) ||
                                        ($aMatches[1] === '0000' && $aMatches[2] === '00' && $aMatches[3] === '00')
                                    ){
                                        if(sizeof($aMatches) > 4){
                                            $isError =
                                                ($aMatches[4] > 23) ||
                                                ($aMatches[5] > 59) ||
                                                ($aMatches[6] > 59);
                                        }
                                    }else{
                                        $isError = true;
                                    }
                                }else{
                                    $isError = true;
                                }
                            }
                            break; // case 'datetime', case 'date'

                        case 'time':
                            if($isFilled && preg_match('/^(\d{2}):(\d{2}):(\d{2})$/', $value, $aMatches)){
                                $isError =
                                    ($aMatches[0] > 23) ||
                                    ($aMatches[1] > 59) ||
                                    ($aMatches[2] > 59);
                            }else{
                                $isError = true;
                            }
                            break; // case 'time'

                        case 'tinytext':
                        case 'text':
                        case 'mediumtext':
                        // case 'longtext':
                        case 'tinyblob':
                        case 'blob':
                        case 'mediumblob':
                        // case 'longblob':
                            $message = 'Maximum data length (' . $this->aFieldLengths[$validator] . ') exceed';
                            $isError = $isFilled && mb_strlen(is_string($value) ? $value : serialize($value)) > $this->aFieldLengths[$validator];
                            break; // ...

                        case 'char':
                        case 'varchar':
                            if(isset($aFieldsData[$field]['length'])){
                                $aFieldsData = $this->oTable->getTableFieldsData();
                                $message = 'Maximum data length (' . $aFieldsData[$field]['length'] . ') exceed';
                                $isError = mb_strlen($value) > $aFieldsData[$field]['length'];
                                unset($aFieldsData);
                            }
                            break; // case 'char', case 'varchar'

                        case 'email':
                            $isError = $isFilled && $value !== '' && !preg_match('/^(\w+[\w.-]*\@[A-Za-z0-9а-яёА-ЯЁ]+((\.|-+)[A-Za-z0-9а-яёА-ЯЁ]+)*\.[\-A-Za-zа-яёА-ЯЁ0-9]+(;|,|$))+$/', $value);
                            break; // case 'email'

                        case 'file':
                            $isError = $isFilled && is_object($value) ? !$value->isValid() : FALSE;
                            break; // case 'file'

                        default:
                            $aEvent = array(
                                'field'              => $field,
                                'value'              => $value,
                                'oItem'              => $this->oTableItem,
                                'oTable'             => $this->oTable,
                                'oTableItemModifier' => $this
                            );
                            /**
                             * Allows add custom data validator.
                             *
                             * @event      on_save_validate_{validator} $modId
                             * @eventparam string                   field               Field name
                             * @eventparam mixed                    value               Value of field
                             * @eventparam AMI_ModTableItem         oItem               Table item object
                             * @eventparam AMI_ModTable             oTable              Table object
                             * @eventparam AMI_ModTableItemModifier oTableItemModifier  Table item modifier object
                             */
                            AMI_Event::fire('on_save_validate_{' . $validator . '}', $aEvent, $this->oTableItem->getModId());
                            if(!empty($aEvent['message'])){
                                $isError = true;
                                $message = $aEvent['message'];
                            }
                            break; // default
                    }
                }
                if($isError){
                    $aErrors[] = array(
                        'validator' => $validator,
                        'message'   => $message,
                        'field'     => $field,
                        'value'     => $value
                    );
                }
            }
        }
        if(sizeof($aErrors)){
            throw new AMI_ModTableItemException(
                'Validation failed: ' . var_export($aErrors, TRUE),
                AMI_ModTableItemException::VALIDATION_FAILED,
                $aErrors
            );
        }
        return $this;
    }

    /**
     * Returns validator exception object validator after save or null.
     *
     * @return AMI_ModTableItemException|null
     */
    public function getValidatorException(){
        return $this->oValidatorException;
    }

    /**
     * Deletes item from table.
     *
     * @param  mixed $id  Primary key value of item
     * @return bool       True if any record were deleted
     * @see    AMI_ModTableItem::delete()
     */
    public function delete($id = null){
        $id = is_null($id) ? $this->oTableItem->getId() : $id;

        $aFileFields = $this->oTableItem->getVirtualFields('file');
        if(sizeof($aFileFields)){
            $aFileFields = array_keys($aFileFields);
            $oItem = $this->oTable->find($id, array('*')); // all fields are required
        }

    	/**
         * @var AMI_iDB
         */
        $oDB = AMI::getSingleton('db');
        $oQuery =
            DB_Query::getSnippet('DELETE FROM %s WHERE %s = %s')
            ->plain($this->oTableItem->getQuery()->getMainTableName())
            ->plain($this->oTableItem->getPrimaryKeyField())
            ->q($id);
        $oDB->query($oQuery);
        $result = $oDB->getAffectedRows() > 0;
        if($result && isset($oItem)){
            foreach($aFileFields as $field){
                $oItem->getValue($field)->delete();
            }
        }

        $aData = array('id' => $id);
        $aEvent = array(
            'oTable'       => $this->oTable,
            'oTableItem'   => $this->oTableItem, // @deprecated since 5.14.0
            'oItem'        => $this->oTableItem,
            'aData'        => &$aData
        );
        /**
         * Called after model item deletion.
         *
         * @event      on_after_delete_model_item $modId
         * @eventparam AMI_ModTable     oTable  Table model
         * @eventparam AMI_ModTableItem oItem   Table item model
         * @eventparam array            aData   Table item raw data
         */
        AMI_Event::fire('on_after_delete_model_item', $aEvent, $this->oTableItem->getModId());

        return $result;
    }

    /**
     * Calls when validation failed.
     *
     * Example:
     * <code>
     * // PrivateMessages_TableItemModifier::rollback() deletes dependent body model created before validation
     * protected function rollback(array $aData, $onCreate){
     *     parent::rollback($aData, $onCreate);
     *     if($onCreate && $aData['id_body']){
     *         AMI::getResourceModel('private_message_bodies/table')->getItem()->delete($aData['id_body']);
     *     }
     * }
     * </code>
     *
     * @param  array $aData     Item data
     * @param  bool  $onCreate  True on item create, false on update
     * @return void
     * @since  5.12.8
     */
    protected function rollback(array $aData, $onCreate){
        $aEvent = array(
            'onCreate'     => $onCreate,
            'oTable'       => $this->oTable,
            'oTableItem'   => $this->oTableItem, // @deprecated since 5.14.0
            'oItem'        => $this->oTableItem,
            'aData'        => $aData
        ) + $this->oTableItem->getMetaData();
        /**
         * Allows rollback saved model item.
         *
         * @event      on_rollback_save_model_item $modId
         * @eventparam bool             onCreate  TRUE on new item creation, FALSE on existing item saving
         * @eventparam AMI_ModTable     oTable    Table model
         * @eventparam AMI_ModTableItem oItem     Table item model
         * @eventparam array            aData     Table item raw data
         * @eventparam bool             success   TRUE on success
         */
        AMI_Event::fire('on_rollback_save_model_item', $aEvent, $this->oTableItem->getModId());
    }

    /**
     * INSERT/UPDATE query data preparation.
     *
     * @param  DB_Snippet|string|null &$value  Field value
     * @return void
     * @see    AMI_ModTableItemModifier::save()
     */
    private function cbPrepareSQL(&$value){
        if(!is_object($value) && !is_null($value)){
            $value = DB_Query::getSnippet('%s')->q($value);
        }
    }
}

/**
 * AMI_Module module table item model modifier.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @since      5.14.4
 */
abstract class AMI_Module_TableItemModifier extends AMI_ModTableItemModifier{
}

/**
 * AMI_CatModule module table item model modifier.
 *
 * @package    ModuleComponent
 * @subpackage Model
 * @since      5.14.4
 */
class AMI_CatModule_TableItemModifier extends AMI_Module_TableItemModifier{
    /**
     * Filed name storing category id in items table
     *
     * @var   string
     * @since 6.0.2
     */
    protected $catIdField = 'id_cat';

    /**
     * Saves current item data.
     *
     * @return AMI_ModTableItemModifier
     * @throws AMI_ModTableItemException  If validation failed.
     * @see    AMI_ModTableItem::save()
     */
    public function save(){
        parent::save();

        // Public/Unpublic handle
    /*
        $aOrigData = $this->oTableItem->getOriginalData();
        if($aOrigData['public'] != $this->oTableItem->public){

            $oList = AMI::getResourceModel($this->oTable->getSubItemsTableResource())
                ->getList()
                ->addColumn('id')
                ->addWhereDef(DB_Query::getSnippet("AND id_cat = %s")->q($this->oTableItem->id))
                ->load();

            if($oList->count() > 0){
                if((bool)$aOrigData['public'] == true){
                    foreach($oList as $oItem){
                        $oItem->public = 0;
                        $oItem->save();
                    }
                }else{
                    foreach($oList as $oItem){
                        $oItem->public = 1;
                        $oItem->save();
                    }
                }
            }
        }
        */
        return $this;
    }

    /**
     * Deletes item from table.
     *
     * @param  mixed $id  Primary key value of item
     * @return bool       True if any record were deleted
     * @see    AMI_ModTableItem::delete()
     */
    public function delete($id = null){
        parent::delete();

        $oList = AMI::getResourceModel($this->oTable->getSubItemsTableResource())
            ->getList()
            ->addColumn('id')
            ->addWhereDef(
                DB_Query::getSnippet("AND `%s` = %s")
                ->plain($this->catIdField)
                ->q($this->oTableItem->id)
            )
            ->load();

        if($oList->count() > 0){
            foreach($oList as $oItem){
                $oItem->delete();
            }
        }

        return $this;
    }
}
