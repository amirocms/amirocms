<?php
/**
 * AmiSubscribe/Export configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiSubscribe_Export
 * @version   $Id: AmiSubscribe_Export_Adm.php 45064 2013-12-05 09:27:10Z Leontiev Anton $
 * @amidev    Temporary
 */

/**
 * AmiSubscribe/Export configuration admin action controller.
 *
 * @package    Config_AmiSubscribe_Export
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @amidev     Temporary
 * @since      6.0.2
 */
class AmiSubscribe_Export_Adm extends Hyper_AmiSubscribe_Adm{
    /**
     * Array of default components
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aDefaultComponents = array('form');

    /**
     * Constructor.
     *
     * Our sample module has three common components: list filter, list and form.
     *
     * @param AMI_Request  $oRequest    Request object
     * @param AMI_Response $oResponse  Response object
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        parent::__construct($oRequest, $oResponse);
    }
}

/**
 * AmiSubscribe/Export configuration model.
 *
 * @package    Config_AmiSubscribe_Export
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @amidev     Temporary
 * @since      6.0.2
 */
class AmiSubscribe_Export_State extends Hyper_AmiSubscribe_State{
}

/**
 * AmiSubscribe/Export configuration admin form component action controller.
 *
 * @package    Config_AmiSubscribe_Export
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @amidev     Temporary
 * @since      6.0.2
 */
class AmiSubscribe_Export_FormAdm extends Hyper_AmiSubscribe_FormAdm{
    /**
     * Flag specifying to use model
     *
     * @var   bool
     */
    protected $useModel = FALSE;

    /**
     * HTTP request object
     *
     * @var AMI_HTTPRequest
     */
    protected $oHTTPRequest = null;

    /**
     * UniSender API URL
     *
     * @var string
     */
    protected $apiUrl = 'http://api.unisender.com/';

    /**
     * Export result
     *
     * @var array
     */
    protected $aExportResult = array('total' => 0, 'inserted' => 0, 'updated' => 0, 'new_emails' => 0);

    /**
     * Export fields
     *
     * @var array
     */
    protected $aFieldNames = array('ami_id', 'email', 'ami_username', 'ami_nickname', 'ami_date', 'ami_firstname', 'ami_lastname', 'ami_info', 'ami_address1', 'ami_address2', 'ami_city', 'ami_state', 'ami_zip', 'ami_country', 'ami_phone', 'ami_phone_cell', 'ami_phone_work', 'ami_company');

    /**
     * Export error
     *
     * @var bool
     */
    protected $isExportError = false;

    /**
     * Save action dispatcher.
     *
     * @param  array &$aEvent  Event data
     * @return void
     */
    protected function _save(array &$aEvent){
        $oResponse = $aEvent['oResponse'];
        $oRequest = AMI::getSingleton('env/request');
        $aRequestScope = $oRequest->getScope();
        $action = $aRequestScope['action'];
        $listUniSender = $aRequestScope['unisender_export_list'];

        if($action == 'export'){
            $errorExport = false;
            $aTopics = explode(';', trim($aRequestScope['subs_topics'], ';'));

            if(!sizeof($aTopics) || empty($listUniSender)){
                $errorExport = true;
                $oResponse->addStatusMessage('status_export_error', array(), AMI_Response::STATUS_MESSAGE);
            }else{
                $topicFilter = '';

                $sql = 'SELECT DISTINCT u.* FROM cms_subs_members i INNER JOIN cms_members u ON u.id = i.id_member WHERE i.active = 1 AND (';

                $i = 0;
                foreach($aTopics as $topicId){
                    if($i > 0){
                        $sql .= ' OR';
                    }
                    $sql .= ' i.topics LIKE %s';
                    $i += 1;
                }
                $sql .= ')';

                $snippet = DB_Query::getSnippet($sql);
                foreach($aTopics as $topicId){
                    $snippet->q("%;".$topicId.";%");
                }

                $this->oHTTPRequest = new AMI_HTTPRequest(
                    array(
                        'returnHeaders'  => false,
                        'followLocation' => false
                    )
                );

                $aPostData = array(
                    'api_key' => AMI::getOption($this->getModId(), 'api_key'),
                    'format'  => 'json'
                );

                $resCreateFields = $this->createAmiroFields();
                if(!$resCreateFields){
                    $errorExport = true;
                    $oResponse->addStatusMessage('status_create_field_error', array(), AMI_Response::STATUS_MESSAGE);
                }

                if(!$errorExport){
                    $aPostData['double_optin'] = 1;
                    foreach($this->aFieldNames as $fieldIndex => $fieldName){
                        $aPostData['field_names[' . $fieldIndex . ']'] = $fieldName;
                    }
                    $aPostData['field_names[' . sizeof($this->aFieldNames) . ']'] = 'email_list_ids';
                    $aPostData['field_names[' . (sizeof($this->aFieldNames) + 1) . ']'] = 'email_request_ip';
                    $aPostData['field_names[' . (sizeof($this->aFieldNames) + 2) . ']'] = 'email_status';

                    $cntUsers = 0;
                    $aUsersData = array();

                    $oDB = AMI::getSingleton('db');
                    $oRS = $oDB->select($snippet);

                    foreach($oRS as $aRecord){
                        foreach($this->aFieldNames as $fieldIndex => $fieldName){
                            $aUsersData['data[' . $cntUsers . '][' . $fieldIndex . ']'] = $aRecord[str_replace('ami_', '', $fieldName)];
                        }
                        $aUsersData['data[' . $cntUsers . '][' . sizeof($this->aFieldNames) . ']'] = $listUniSender;
                        $aUsersData['data[' . $cntUsers . '][' . (sizeof($this->aFieldNames) + 1) . ']'] = $aRecord['ip'];
                        $aUsersData['data[' . $cntUsers . '][' . (sizeof($this->aFieldNames) + 2) . ']'] = 'active';
                        $cntUsers += 1;

                        if($cntUsers >= 500){
                            @set_time_limit(29);
                            $this->keepAlive();
                            $this->callAPIMethod('importContacts', $aPostData + $aUsersData);
                            $aUsersData = array();
                            $cntUsers = 0;
                        }
                    }
                    $this->keepAlive();
                    if(sizeof($aUsersData)){
                        $this->callAPIMethod('importContacts', $aPostData + $aUsersData);
                    }

                    if(!$this->isExportError){
                        $oResponse->addStatusMessage('status_export_done', $this->aExportResult, AMI_Response::STATUS_MESSAGE);
                    }else{
                        $oResponse->addStatusMessage('status_export_error', array(), AMI_Response::STATUS_MESSAGE);
                    }
                }
            }
        }elseif($action == 'add'){
            if(isset($GLOBALS['Core'])){
                if(!empty($aRequestScope['unisender_api_key'])){
                    AMI::setOption($this->getModId(), 'api_key', $aRequestScope['unisender_api_key']);
                }
                if(!empty($aRequestScope['unisender_autoexport_users'])){
                    AMI::setOption($this->getModId(), 'autoexport_enabled', $aRequestScope['unisender_autoexport_users']);
                }

                $aTopics = array();
                $aAutoExportTopics = array();
                $oTopicsList = AMI::getResourceModel('subs_topic/table')->getList()->addColumn('*')->addOrder('name')->load();
                foreach($oTopicsList as $oTopic){
                    if(!empty($aRequestScope['unisender_list_'.$oTopic->id])){
                        $aAutoExportTopics[$oTopic->id] = $aRequestScope['unisender_list_'.$oTopic->id];
                    }
                }
                AMI::setOption($this->getModId(), 'autoexport_topics', $aAutoExportTopics);

                $GLOBALS['Core']->SaveOptions($this->getModId(), false);
            }
            $oResponse->addStatusMessage('status_save', array(), AMI_Response::STATUS_MESSAGE);
        }

        $oRequest->set('id', 0);
        $this->oItem = null;

        AMI_Event::fire('dispatch_mod_action_form_edit', $aEvent, $this->getModId());
    }

    /**
     * Keeping export alive.
     *
     * @return void
     */
    private function keepAlive(){
        print ' ';
    }

    /**
     * Call API method.
     *
     * @param string $method     API method
     * @param array  $aPostData  API data
     * @return mixed
     */
    protected function callAPIMethod($method, array $aPostData){
        if($method != 'importContacts'){
            AMI_Service::log("AmiSubscribe_Export_Adm::callAPIMethod. Method: ".$method, $GLOBALS['ROOT_PATH'] . '_admin/_logs/err.log');
            AMI_Service::log("AmiSubscribe_Export_Adm::callAPIMethod. Post data: ".print_r($aPostData, true), $GLOBALS['ROOT_PATH'] . '_admin/_logs/err.log');
        }

        if(is_null($this->oHTTPRequest)){
            $this->oHTTPRequest = new AMI_HTTPRequest(
                array(
                    'returnHeaders'  => false,
                    'followLocation' => false
                )
            );
        }

        $res = $this->oHTTPRequest->send(
            $this->apiUrl . AMI_Registry::get('lang', 'en') . '/api/' . $method,
            $aPostData,
            AMI_HTTPRequest::METHOD_POST
        );

        $requestRes = AMI_Lib_JSON::decode($res);

        if($method != 'importContacts'){
            AMI_Service::log("AmiSubscribe_Export_Adm::callAPIMethod. Result: " . print_r($requestRes, true), $GLOBALS['ROOT_PATH'] . '_admin/_logs/err.log');
        }

        if(isset($requestRes['result'])){
            if($method == 'importContacts'){
                $this->aExportResult['total'] += $requestRes['result']['total'];
                $this->aExportResult['inserted'] += $requestRes['result']['inserted'];
                $this->aExportResult['updated'] += $requestRes['result']['updated'];
                $this->aExportResult['new_emails'] += $requestRes['result']['new_emails'];
                AMI_Service::log("AmiSubscribe_Export_FormAdm::callAPIMethod() Post data: " . print_r($aPostData, true), $GLOBALS['ROOT_PATH'] . '_admin/_logs/err.log');
                AMI_Service::log("AmiSubscribe_Export_FormAdm::callAPIMethod() Export result: " . print_r($requestRes, true), $GLOBALS['ROOT_PATH'] . '_admin/_logs/err.log');
            }
        }else{
            $this->isExportError = true;
            return false;
        }

        return $requestRes;
    }

    /**
     * Create Amiro fields.
     *
     * @return bool
     */
    protected function createAmiroFields(){
        $aPostData = array(
            'api_key' => AMI::getOption($this->getModId(), 'api_key'),
            'format'  => 'json'
        );

        // get UniSender fields
        $requestRes = $this->callAPIMethod('getFields', $aPostData);
        $aCreateFields = array();
        if(isset($requestRes['result'])){
            $aUniSenderFields = array();
            if(is_array($requestRes['result'])){
                foreach($requestRes['result'] as $fieldUniSender){
                    $aUniSenderFields[] = $fieldUniSender['name'];
                }
            }

            foreach($this->aFieldNames as $fieldName){
                if($fieldName != 'email' && $fieldName != 'phone' && !in_array($fieldName, $aUniSenderFields)){
                    $aCreateFields[] = $fieldName;
                }
            }
        }else{
            return false;
        }

        // create UniSender fields
        if(sizeof($aCreateFields)){
            foreach($aCreateFields as $fieldName){
                $fieldType = 'string';
                if($fieldName == 'ami_info' || $fieldName == 'ami_address1' || $fieldName == 'ami_address2'){
                    $fieldType = 'text';
                }
                @set_time_limit(29);
                $requestRes = $this->callAPIMethod('createField', $aPostData + array('name' => $fieldName, 'type' => $fieldType));
                if(!isset($requestRes['result'])){
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Change subscriber event handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleChangeSubscriber($name, array $aEvent, $handlerModId, $srcModId){
        $apiKey = AMI::getOption($this->getModId(), 'api_key');

        if(empty($apiKey) || !AMI::getOption($this->getModId(), 'autoexport_enabled')){
            return $aEvent;
        }

        $aUserPostData = array();

        if(!empty($aEvent['id_member'])){
            $aUserData = AMI::getSingleton('db')->fetchRow("SELECT * FROM `cms_members` WHERE id = " . (int)$aEvent['id_member']);
            if(empty($aEvent['email'])){
                $aEvent['email'] = $aUserData['email'];
            }
            $requestIp = $aUserData['ip'];
            $requestTime = $aUserData['date'];

            if(!empty($aEvent['exclude'])){
                $aPostData = array(
                    'api_key' => $apiKey,
                    'format'  => 'json',
                    'contact_type' => 'email',
                    'contact' => $aEvent['email']
                );
                $this->callAPIMethod('exclude', $aPostData);

                return $aEvent;
            }

            if(!empty($aEvent['subscribe'])){
                $aTopicsData = AMI::getSingleton('db')->fetchRow("SELECT active, topics FROM `cms_subs_members` WHERE id_member = " . (int)$aEvent['id_member']);
                $aEvent['topics'] = $aTopicsData['topics'];
                if(!isset($aEvent['active'])){
                    $aEvent['active'] = $aTopicsData['active'];
                }
            }

            $resCreateFields = $this->createAmiroFields();
            if($resCreateFields){
                foreach($this->aFieldNames as $fieldName){
                    if($fieldName != 'email' && $fieldName != 'phone'){
                        $aUserPostData['fields[' . $fieldName . ']'] = $aUserData[str_replace('ami_', '', $fieldName)];
                    }
                }
            }
        }

        if(!empty($aEvent['active']) && !empty($aEvent['email'])){
            $aTopics = explode(';', trim($aEvent['topics'], ';'));
            $aExportTopics = AMI::getOption($this->getModId(), 'autoexport_topics');
            $listIds = '';
            foreach($aTopics as $topicId){
                if(!empty($aExportTopics[$topicId])){
                    if($listIds){
                        $listIds .= ',';
                    }
                    $listIds .= $aExportTopics[$topicId];
                }
            }

            $aPostData = array(
                'api_key' => $apiKey,
                'format'  => 'json',
                'contact_type' => 'email',
                'contact' => $aEvent['email']
            );
            $this->callAPIMethod('exclude', $aPostData);

            if($listIds){
                $aPostData = array(
                    'api_key' => $apiKey,
                    'format'  => 'json',
                    'list_ids' => $listIds,
                    'fields[email]' => $aEvent['email'],
                    'overwrite' => 2,
                    'double_optin' => 1
                );
                if(!empty($requestIp)){
                    $aPostData['request_ip'] = $requestIp;
                }
                if(!empty($requestTime)){
                    $aPostData['request_time'] = $requestTime;
                }
                $this->callAPIMethod('subscribe', $aPostData + $aUserPostData);
            }
        }
        return $aEvent;
    }
}

/**
 * AmiSubscribe/Export configuration admin form component view.
 *
 * @package    Config_AmiSubscribe_Export
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @amidev     Temporary
 * @since      6.0.2
 */
class AmiSubscribe_Export_FormViewAdm extends AMI_ModFormView{
    /**
     * Reset form default elements template
     *
     * @var array
     */
    protected $aPlaceholders = array('#form', 'form');

    /**
     * UniSender API key
     *
     * @var string
     */
    protected $apiKey = '';

    /**
     * Export new subscribers to UniSender
     *
     * @var string
     */
    protected $isAutoExportUsers = true;

    /**
     * Export new subscribers to UniSender lists
     *
     * @var array
     */
    protected $aAutoExportTopics = array();

    /**
     * UniSender API URL
     *
     * @var string
     */
    protected $apiUrl = 'http://api.unisender.com/';

    /**
     * HTTP request object
     *
     * @var AMI_HTTPRequest
     */
    protected $oHTTPRequest = null;

    /**
     * Constructor.
     */
    public function __construct(){
        $this->oHTTPRequest = new AMI_HTTPRequest(
            array(
                'returnHeaders'  => false,
                'followLocation' => false
            )
        );

        parent::__construct();
    }

    /**
     * Initialize fields.
     *
     * @return AmiSubscribe_Export_FormViewAdm
     */
    public function init(){
        $this->apiKey = AMI::getOption($this->getModId(), 'api_key');
        $this->isAutoExportUsers = AMI::getOption($this->getModId(), 'autoexport_enabled');
        $this->aAutoExportTopics = AMI::getOption($this->getModId(), 'autoexport_topics');

        $this->addField(array('name' => 'id', 'value' => 1, 'type' => 'hidden'));
        $this->addField(array('name' => 'mod_id', 'value' => $this->getModId(), 'type' => 'hidden'));
        $this->addField(array('name' => 'mod_action', 'value' => 'form_save', 'type' => 'hidden'));
        $this->addField(array('name' => 'ami_full', 'value' => 1, 'type' => 'hidden'));

        $this->addField(array('name' => 'export_csv', 'type' => 'export_csv', 'value' => AMI_Registry::get('lang', 'en')));
        $this->addField(array('name' => 'export_unisender', 'type' => 'export_unisender'));
        $this->addField(array('name' => 'unisender_api_key', 'value' => $this->apiKey, 'type' => 'input', 'validate' => array('filled', 'stop_on_error')));

        $apiError = false;
        $noTopics = false;
        $aUniSenderListOptions = array();
        if($this->apiKey){
            $this->addField(array('name' => 'unisender_autoexport_users', 'type' => 'checkbox', 'value' => $this->isAutoExportUsers));

            $aTopics = array();
            $oTopicsList = AMI::getResourceModel('subs_topic/table')->getList()->addColumn('*')->addOrder('name')->load();
            if(!sizeof($oTopicsList)){
                $noTopics = true;
            }else{
                foreach($oTopicsList as $oTopic){
                    $aTopics[] = array('value' => $oTopic->id , 'html_caption' => $oTopic->name);
                }

                $apiCallResult = $this->callAPIMethod('getLists');

                if(!is_array($apiCallResult) || empty($apiCallResult['result'])){
                    $apiError = true;
                    if(is_array($apiCallResult['result']) && !sizeof($apiCallResult['result'])){
                        $this->addField(array('name' => 'unisender_no_list', 'type' => 'message', 'value' => ''));
                    }else{
                        $this->addField(array('name' => 'unisender_api_error', 'type' => 'message', 'value' => 'getLists: ' . $apiCallResult));
                    }
                }else{
                    $aUniSenderListOptions[] = array('name' => '---', 'value' => '');
                    foreach($apiCallResult['result'] as $aUniSenderList){
                        $aUniSenderListOptions[] = array('name' => $aUniSenderList['title'], 'value' =>  $aUniSenderList['id']);
                    }

                    $this->addField(array('name' => 'unisender_list_open', 'type' => 'unisender_list_open'));
                    foreach($aTopics as $topic){
                        $listValue = '';
                        foreach($aUniSenderListOptions as $listKey => $aUniSenderListOption){
                            if(isset($this->aAutoExportTopics[$topic['value']]) && ($aUniSenderListOption['value'] == $this->aAutoExportTopics[$topic['value']])){
                                $listValue = $aUniSenderListOption['value'];
                            }
                        }
                        $this->addField(array('name' => 'unisender_list_'.$topic['value'], 'caption' => $topic['html_caption'], 'type' => 'select', 'data' => $aUniSenderListOptions, 'value' => $listValue));
                    }
                    $this->addField(array('name' => 'unisender_list_close', 'type' => 'unisender_list_close'));
                }
            }
        }else{
            $this->addField(array('name' => 'unisender_api_key_absent', 'type' => 'message'));
        }

        $this->addField(array('name' => 'unisender_save_settings', 'type' => 'unisender_save_settings'));

        if($noTopics){
            $this->addField(array('name' => 'no_topics', 'type' => 'message'));
            return $this;
        }

        if($this->apiKey && !$apiError){
            $this->addField(array('name' => 'export_to_unisender', 'type' => 'export_to_unisender'));
            if(!$this->apiKey){
                // $this->addField(array('name' => 'unisender_api_key_absent', 'type' => 'message'));
            }else{
                $this->addField(array('name' => 'subs_topics', 'type' => 'select', 'data' => $aTopics, 'multiple' => true));
                $this->addField(array('name' => 'unisender_export_list', 'type' => 'select', 'data' => $aUniSenderListOptions));
                $this->addField(array('name' => 'unisender_export', 'type' => 'unisender_export'));
            }
        }

        return $this;
    }

    /**
     * Call API method.
     *
     * @param string $method  API method
     * @return bool
     */
    protected function callAPIMethod($method){
        if(empty($method) || empty($this->apiKey) || is_null($this->oHTTPRequest)){
            return false;
        }

        // API format: http://api.unisender.com/LANG/api/METHOD?format=json&api_key=KEY&arg1=ARG_1&argN=ARG_N
        $res = $this->oHTTPRequest->send(
            $this->apiUrl . AMI_Registry::get('lang', 'en') . '/api/' . $method,
            array(
                'api_key' => $this->apiKey,
                'format'  => 'json'
            ),
            AMI_HTTPRequest::METHOD_POST
        );

        $aResult = AMI_Lib_JSON::decode($res);

        if(!empty($aResult['error'])){
            return $aResult['error'];
        }

        return $aResult;
    }
}
