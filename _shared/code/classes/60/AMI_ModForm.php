<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModForm.php 48699 2014-03-14 13:06:38Z Leontiev Anton $
 * @since     5.12.0
 */

/**
 * Module form component action controller.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.12.0
 */
abstract class AMI_ModForm extends AMI_ModComponent{
    /**
     * Flag specified that save is failed.
     *
     * @see   AMI_ModForm::_save()
     * @since 5.14.8
     */
    const SAVE_FAILED = 0;

    /**
     * Flag specified that save is succeed.
     *
     * @see   AMI_ModForm::_save()
     * @since 5.14.8
     */
    const SAVE_SUCCEED = 1;

    /**
     * Table item model
     *
     * Backward compatibility.
     *
     * @var   AMI_ModTableItem|null
     * @since 5.14.8
     */
    protected $oItem;

    /**
     * Table item model.
     *
     * @var   AMI_ModTableItem|null
     * @deprecated 5.14.8
     * @amidev
     */
    protected $oModelItem;

    /**
     * File factory resource id
     *
     * @var   string
     * @since 5.14.8
     */
    protected $fileFactoryResId = 'env/file';

    /**
     * Array of actions processed by form
     *
     * @var   array
     * @since 6.0.6
     */
    protected $aActions = array('display', 'edit', 'save', 'show');

    /**
     * Returns component type.
     *
     * @return string
     */
    public function getType(){
        return 'form';
    }

    /**
     * Returns true if component needs to be started always in full environment.
     *
     * @return bool
     */
    public function isFullEnv(){
        return TRUE;
    }

    /**
     * Initialization.
     *
     * @return AMI_ModForm
     */
    public function init(){
        $this->oModelItem = &$this->oItem;
        foreach($this->aActions as $action){
            AMI_Event::addHandler(
                'dispatch_mod_action_form_' . $action,
                array($this, AMI::actionToHandler($action)),
                $this->getModId()
            );
        }
        return $this;
    }

    /**#@+
     * Event handler.
     *
     * @since 5.14.8
     */

    /**
     * Display action handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function dispatchDisplay($name, array $aEvent, $handlerModId, $srcModId){
        $this->displayView();
        $this->oItem = $this->getModel()->getItem();
        return $aEvent;
    }

    /**
     * Edit action handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function dispatchEdit($name, array $aEvent, $handlerModId, $srcModId){
        $this->displayView();
        $this->_edit($aEvent);
        return $aEvent;
    }

    /**
     * Save action handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function dispatchSave($name, array $aEvent, $handlerModId, $srcModId){
        $this->_save($aEvent);
        return $aEvent;
    }

    /**
     * Show action handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function dispatchShow($name, array $aEvent, $handlerModId, $srcModId){
        $this->displayView();
        $this->_edit($aEvent);
        return $aEvent;
    }

    /**#@-*/

    /**
     * Returns component view.
     *
     * @return AMI_ModFormView
     */
    public function getView(){
        $oView = $this->_getView('/form/view/' . AMI_Registry::get('side'));
        if(!($oView instanceof AMI_ViewEmpty)){
            $oView->setFullEnvMarker($this->getSerialId(), AMI::getSingleton('env/request')->get('ami_full', false));
        }
        return $oView;
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
        if($oView instanceof AMI_iModFormView && $this->useModel){
            // TODO. Think if model should exist if we show a view
            if(is_null($this->oItem)){
                $this->oItem = $this->getModel()->getItem();
            }
            $oView->setModelItem($this->oItem);
        }
        return $oView;
    }

    /**#@+
     * Common admin module form action dispatching handler.
     *
     * @since  5.14.8
     */

    /**
     * Edit action dispatcher.
     *
     * @param  array &$aEvent  Event data
     * @return void
     */
    protected function _edit(array &$aEvent){
        if(empty($this->useModel)){
            return;
        }
        /**
         * @var AMI_RequestHTTP
         */
        $oRequest = AMI::getSingleton('env/request');
        $itemId = $oRequest->get('id', 0);
        if(!$this->oItem){
            $this->oItem = $itemId
                ? $this->getModel()->find($itemId, array('*')) // all fields are required
                : $this->getModel()->getItem();
            $forcePageId = $oRequest->get('ami_force_id_page', false);
            $forceCategoryId = $oRequest->get('ami_force_id_cat', false);
            if($forcePageId !== false){
                // Categories module checking
                $subItemsModId = false;
                if(is_callable(array($this->getModel(), 'getSubItemsModId'))){
                    $subItemsModId = $this->getModel()->getSubItemsModId();
                }
                if(
                    AMI::issetAndTrueOption('core', 'multi_page_allowed') &&
                    (
                        (AMI::issetAndTrueOption($this->getModId(), 'multi_page') && !AMI::issetAndTrueOption($this->getModId(), 'use_categories')) ||
                        ($subItemsModId && AMI::issetAndTrueOption($subItemsModId, 'multi_page'))
                    )
                ){
                    $this->oItem->id_page = (int)$forcePageId;
                }
            }elseif($forceCategoryId !== false){
                $this->oItem->id_cat = (int)$forceCategoryId;
            }
        }
        if($this->oItem->getValidatorException()){
            $this->oItem->setValues(AMI::getSingleton('env/request')->getScope());
        }
    }

    /**
     * Save action dispatcher.
     *
     * @param  array &$aEvent  Event data
     * @return void
     */
    protected function _save(array &$aEvent){
        /**
         * @var AMI_Response
         */
        $oResponse = $aEvent['oResponse'];
        /**
         * @var AMI_Mod
         */
        $oModController = $aEvent['oController'];

        $id = AMI::getSingleton('env/request')->get('id');
        $aStatusMessage = array();
        $aCodes = $this->getUploadedFileCodes();
        /**
         * @var AMI_RequestHTTP
         */
        $oRequest = AMI::getSingleton('env/request');
        $returnType = $oRequest->get('return_type', 'current');
        try{
            /**
             * @var AMI_FileFactory
             */
            $oFileFactory = AMI::getResource($this->fileFactoryResId);
            $aUploadedFiles = $oFileFactory->getUploaded($aCodes);
            foreach($aUploadedFiles as $code => $oFile){
                if(!$oFile->isValid()){
                    $oRequest->set(array_search($code, $aCodes), null);
                }
            }
            $fileStoragePath = $GLOBALS['ROOT_PATH'] . $this->getFileStoragePath();
            if($id){
                // Existing item
                $this->oItem = $this->getModel()->find($id, array('*')); // all fields are required
                if($this->oItem->getId() && $oFileFactory->move($this->oItem, $fileStoragePath, $aUploadedFiles)){
                    $aFileFields = $this->oItem->getVirtualFields('file');
                    $aData = $this->convertRequestDates($oRequest->getScope());
                    $aPreviousFiles = array();
                    foreach($aFileFields as $field => $aField){
                        unset($aData[$field]);
                        $code = isset($aCodes[$field]) ? $aCodes[$field] : '';
                        if($code != 'uploaded'){
                            $oFile = $this->oItem->getValue($field);
                            if(
                                !is_null($oFile) &&
                                (isset($aUploadedFiles[$code])
                                    ? $oFile->getLocalName() != $aUploadedFiles[$code]->getLocalName()
                                    : TRUE
                                )
                            ){
                                $aPreviousFiles[] = $oFile;
                            }
                            if(isset($aUploadedFiles[$code])){
                                if(isset($aField['aParams']['aMapping']) && in_array('type', $aField['aParams']['aMapping'])){
                                    $typeField = array_search('type', $aField['aParams']['aMapping']);
                                    if($oRequest->get($typeField, FALSE) !== FALSE){
                                        $aUploadedFiles[$code]->setParameter('type', $oRequest->get($typeField));
                                    }
                                }
                                $this->oItem->setValue($field, $aUploadedFiles[$code]);
                            }else{
                                $this->oItem->setValue($field, '');
                            }
                        }
                    }
                    $this->oItem->setValues($aData);
                    $this->oItem->save();
                    foreach($aPreviousFiles as $oFile){
                        $oFile->delete();
                    }
                    if($returnType === 'current'){
                        // To load updated by model fields, like date_updated
                        $this->oItem = $this->getModel()->find($id, array('*')); // all fields are required
                    }
                    $oResponse->setMessage('form_item_saved', self::SAVE_SUCCEED);
                    $aStatusMessage = array('status_apply', AMI_Response::STATUS_MESSAGE);
                }else{
                    $oResponse->setMessage('form_item_not_found', self::SAVE_FAILED);
                    $aStatusMessage = array('status_apply_fail', AMI_Response::STATUS_MESSAGE_ERROR);
                }
            }else{
                // New item
                $aData = $this->convertRequestDates($oRequest->getScope());
                $this->oItem = $this->getModel()->getItem();
                if($oFileFactory->move($this->oItem, $fileStoragePath, $aUploadedFiles)){
                    $aFileFields = $this->oItem->getVirtualFields('file');
                    foreach($aFileFields as $field => $aField){
                        unset($aData[$field]);
                        if(!isset($aCodes[$field])){
                            continue;
                        }
                        $code = $aCodes[$field];
                        if(isset($aUploadedFiles[$code])){
                            if(isset($aField['aParams']['aMapping']) && in_array('type', $aField['aParams']['aMapping'])){
                                $typeField = array_search('type', $aField['aParams']['aMapping']);
                                if($oRequest->get($typeField, FALSE) !== FALSE){
                                    $aUploadedFiles[$code]->setParameter('type', $oRequest->get($typeField));
                                }
                            }
                            $this->oItem->setValue($field, $aUploadedFiles[$code]);
                        }else{
                            $this->oItem->setValue($field, '');
                        }
                    }
                    $this->oItem->setValues($aData);
                    $this->oItem->save();
                    $id = $this->oItem->getId();
                    if($id !== $this->oItem->getEmptyId()){
                        $this->oItem = $this->getModel()->find($id, array('*')); // all fields are required
                        $oResponse->setMessage('form_item_created', self::SAVE_SUCCEED);
                        $aStatusMessage = array('status_add', AMI_Response::STATUS_MESSAGE);
                    }else{
                        $oResponse->setMessage('form_item_not_created', self::SAVE_FAILED);
                        $aStatusMessage = array('status_add_fail', AMI_Response::STATUS_MESSAGE_ERROR);
                    }
                }else{
                    $oResponse->setMessage('form_item_not_created', self::SAVE_FAILED);
                    $aStatusMessage = array('status_add_fail', AMI_Response::STATUS_MESSAGE_ERROR);
                }
            }
        }catch(AMI_ModTableItemException $oException){
            d::pr($oException->getData(), 'AMI_ModTableItemException caught on item save');
            if($id){
                foreach($aUploadedFiles as $oFile){
                    $oFile->delete();
                }
                $oResponse->setMessage('form_item_not_saved', self::SAVE_FAILED);
                $aStatusMessage = array('status_apply_fail', AMI_Response::STATUS_MESSAGE_ERROR);
            }else{
                $oResponse->setMessage('form_item_not_created', self::SAVE_FAILED);
                $aStatusMessage = array('status_add_fail', AMI_Response::STATUS_MESSAGE_ERROR);
            }

            // method for additional manipulations with the error data
            $aEvent = $aEvent + array('oException' => $oException);
            $aEvent = $this->onSaveException($aEvent);
        }

        if(!empty($aStatusMessage)){
            $oResponse->addStatusMessage($aStatusMessage[0], array(), $aStatusMessage[1]);
        }

        $oRequest->set('applied_id', $id);
        if($returnType === 'new'){
            $oRequest->set('id', 0);
            $this->oItem = null;
        }elseif($id){
            $oRequest->set('id', $id);
        }
        /**
         * Processing controller actions of the AMI_Mod module.
         *
         * @event      dispatch_mod_action_form_edit $modId
         * @eventparam string modId  Module id  ---
         * @eventparam AMI_Mod|null oController  Module controller object
         * @eventparam string tableModelId  Table model resource id
         * @eventparam AMI_Request oRequest  Request object
         * @eventparam AMI_Response oResponse  Response object
         */
        AMI_Event::fire('dispatch_mod_action_form_edit', $aEvent, $this->getModId());
    }

    /**#@-*/

    /**
     * Converts dates in the request scope into mysql friendly format.
     *
     * @param array $aRequestScope  Request scope
     * @return array
     * @see    AMI_ModForm::_save()
     * @amidev Temporary
     */
    protected function convertRequestDates(array $aRequestScope){
        $bDisplayView = parent::isDisplayView();
        $this->displayView();
        $oView = $this->getView();
        $oModel = $this->getModel();
        $aFieldsData = $oModel->getFieldsByTypes(array('date', 'datetime'));
        $aViewFields = $oView->getFields();
        $aFields = array_intersect_key($aFieldsData, $aViewFields);
        foreach($aFields as $field => $fieldType){
            if(isset($aRequestScope[$field])){
                if($fieldType == 'datetime'){
                    if($aRequestScope[$field]){
                        $hasTime = isset($aRequestScope[$field . '_time']);
                        if($hasTime && trim($aRequestScope[$field . '_time']) == ''){
                            $aRequestScope[$field . '_time'] = date('H:i:s');
                        }
                        $fullTime = $hasTime ? $aRequestScope[$field] . ' ' . $aRequestScope[$field . '_time'] : $aRequestScope[$field];
                        $format = $hasTime ? AMI_Lib_Date::FMT_BOTH : AMI_Lib_Date::FMT_DATE;
                        $aRequestScope[$field] = AMI_Lib_Date::formatDateTime($fullTime, $format, true);
                        $aRequestScope[$field] .= ($format == AMI_Lib_Date::FMT_DATE ) ? ' 00:00:00' : '';
                    }
                }else{
                    $fullTime = $aRequestScope[$field];
                    $aRequestScope[$field] = AMI_Lib_Date::formatDateTime($fullTime, AMI_Lib_Date::FMT_DATE, true);
                }
            }
        }
        $this->displayView($bDisplayView);
        return $aRequestScope;
    }

    /**
     * Returns uploaded file codes.
     *
     * @return array
     */
    protected function getUploadedFileCodes(){
        $bDispayView = $this->isDisplayView();
        $this->displayView(TRUE);
        $oView = $this->getView();
        $this->displayView($bDispayView);
        // Get file fields from form view
        $aFileFields = $oView->getFields('file');
        $aCodes = array();
        /**
         * @var AMI_RequestHTTP
         */
        $oRequest = AMI::getSingleton('env/request');
        foreach($aFileFields as $aField){
            if($oRequest->get($aField['name'], FALSE)){
                $aCodes[$aField['name']] = $oRequest->get($aField['name']);
            }
        }
        return $aCodes;
    }

    /**
     * Returns module file storage path.
     *
     * @return string
     */
    protected function getFileStoragePath(){
        return '_local/plugins/' . $this->getModId() . '/';
    }

    /**
     * Do something when data is not saved and exception is called.
     *
     * @param array $aEvent  Event array with exception, model item and error status
     * @return array
     * @since 6.0.2
     */
    protected function onSaveException(array $aEvent){
        return $aEvent;
    }
}

/**
 * Module admin form component action controller.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.12.0
 */
abstract class AMI_ModFormAdm extends AMI_ModForm{
}
