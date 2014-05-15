<?php
/**
 * AmiClean/DataImport configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiClean_EshopOrder
 * @version   $Id: AmiClean_DataImport_Service.php 40440 2013-08-08 14:24:08Z Kolesnikov Artem $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiClean/DataImport service dispatcher and class helper.
 *
 * @package    Config_AmiClean_DataImport
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_DataImport_Service{
    /**
     * Execution error status
     *
     * @var int
     */
    const RESULT_ERROR = 0x000001;

    /**
     * Execution success
     *
     * @var int
     */
    const RESULT_SUCCESS = 0x000002;

    /**
     * Calculate time for next execution of the import task.
     *
     * @param int    $frequency        Import frequency
     * @param string $timeImportRange  Available import range
     * @param int    $lastImport       Time of the last import
     * @return int
     */
    public function makeNextImportDate($frequency, $timeImportRange = '00:00-23:59', $lastImport = 0){
        if(empty($lastImport)){
            $lastImport = time();
        }

        if(empty($timeImportRange)){
            $timeImportRange = '00:00-23:59';
        }

        // make today's period start end end
        list($tmpMinutesStart, $tmpMinutesEnd) = explode('-', $timeImportRange);
        $nextImportPeriodStart = (int)strtotime('tomorrow ' . $tmpMinutesStart, time());
        $todayImportPeriodEnd = (int)strtotime(date('Y-m-d', time()) . ' ' . $tmpMinutesEnd);

        $nextImport = $lastImport + ($frequency * 60);

        // if next import time more than current period...
        return ($nextImport > $todayImportPeriodEnd
            ? $nextImportPeriodStart
            : $nextImport
        );
    }

    /**
     * Dispatch action executer.
     *
     * @param integer|null $taskId  Task identifier
     * @return void
     */
    public function dispatchAction($taskId = null){
        AMI_Service::hideDebug();
        $oRequest = AMI::getSingleton('env/request');

        // get the tasks list
        $q = "
            SELECT p.handler,
                    p.module,
                    p.last_execution,
                    p.next_execution,
                    p.update_start,
                    p.import_task_id,
                    di.driver_data,
                    di.allow_duplicate
            FROM cms_processes p
            LEFT JOIN cms_data_import di ON p.import_task_id = di.id
            WHERE p.is_sheduled = 1
                AND p.type =1
                AND p.next_execution <= %s
                AND di.public = 1
        ";

        // got the identifier ?
        if(!empty($taskId) && ctype_digit($taskId)){
            $q .= DB_Query::getSnippet("AND p.import_task_id = %s")->plain($taskId)->get();
        }
        $oSnippet = DB_Query::getSnippet($q)->q(date('Y-m-d H:i:s', time()));
        $oResult = AMI::getSingleton('db')->select($oSnippet);

        foreach($oResult as $aTask){
            $oTask = AMI::getResourceModel('data_import/table')->find($aTask['import_task_id'], array('*'));
            $this->executeTask($oTask);
        }
    }

    /**
     * Execute the task.
     *
     * @param object $oTask  Task data
     * @return bool|mixed
     * @todo send email to the administrator with notice if task execution failed
     */
    protected function executeTask($oTask){
        // data settings is empty?
        if(empty($oTask->driver_data)){
            return $this->setTaskComplete($oTask->module, $oTask->import_task_id, self::RESULT_ERROR);
        }

        // check for blocked task
        if(!empty($oTask->blocked_till)
            && (strtotime($oTask->blocked_till, time()) > time())
        ){
            return false;
        }

        // set task blocked
        $oTask->blocked_till = date('Y-m-d H:i:s', (time() + 3600));
        AMI_Registry::set('ami_allow_model_save', true);
        $oTask->save();

        $aDriverData = unserialize($oTask->driver_data);
        $aDriverData['table_fields'] = explode(',', $aDriverData['table_fields']);
        $aDriverData['import_fields'] = explode(',', $aDriverData['import_fields']);

        // get the driver
        $oDriver = false;
        $driverResourceId = 'import_driver/' . $oTask->driver_name;
        if(AMI::isResource($driverResourceId)){
            $oDriver = AMI::getResource($driverResourceId);
        }
        // unable to load driver
        if(!is_object($oDriver)){
            return $this->setTaskComplete($oTask->module, $oTask->import_task_id, self::RESULT_ERROR);
        }
        // set up base data
        $resourceName = $oTask->driver_name == 'ami_csv' ? $aDriverData['file_name'] : $aDriverData['source_url'];
        $oDriver
            ->setModId($aDriverData['mod_id'])
            ->setContentEncoding('utf-8');

        // add import fields and mapping to the destination table
        for($i = 0; $i < count($aDriverData['table_fields']); $i++){
            $aField = array(
                'name'  => $aDriverData['import_fields'][$i],
                'mapTo' => $aDriverData['table_fields'][$i],
            );

            $oDriver->addImportField($aField);
        }

        // add settings
        $aSettings = array();
        if(!empty($aDriverData['driver_settings'])){
            foreach(explode(',', $aDriverData['driver_settings']) as $fieldName){
                if(isset($aDriverData[$fieldName])){
                    $aSettings[$fieldName] = $aDriverData[$fieldName];
                }
            }
        }

        // try to establish the connection and import the data
        try{
            $oDriver
                ->setResourceName($resourceName)
                ->setResourceId($aDriverData['table_name'])
                ->setRequestSettings($aSettings)
                ->initConnection();

            // read the data from the source
            if($oDriver->isImportResourceAvailable()){
                $oDriver
                    ->readData()
                    ->closeConnection();

                // if data succesfully imported save it into db
                if($oDriver->doImport() && $oDriver->isImported()){
                    $oDriver->save($oTask->id, $oTask->allow_duplicate);
                    return $this->setTaskComplete($oTask->module, $oTask->import_task_id, self::RESULT_SUCCESS);
                }else{
                    return $this->setTaskComplete($oTask->module, $oTask->import_task_id, self::RESULT_ERROR);
                }
            }else{
                return $this->setTaskComplete($oTask->module, $oTask->import_task_id, self::RESULT_ERROR);
            }
        }catch(AMI_DataImportException $oImportExeption){
            return $this->setTaskComplete($oTask->module, $oTask->import_task_id, self::RESULT_ERROR);
        }

    }

    /**
     * Change task execution status.
     *
     * @param string $modId    Mod identifier
     * @param integer $taskId  Task identifier
     * @param integer $status  Execution status
     * @return true
     */
    protected function setTaskComplete($modId, $taskId, $status){
        // get the task object
        $oModel = AMI::getResourceModel($modId . '/table');
        $oTask = $oModel->find($taskId);

        $lastImportDate = date('Y-m-d H:i:s', time());
        $nextImportDate = $this->makeNextImportDate($oTask->frequency, $oTask->update_start, time());
        $nextImportDate = date('Y-m-d H:i:s', $nextImportDate);

        // update task data
        if($status == self::RESULT_ERROR){
            $oTask->last_success = 0;
            $oTask->errors_num++;
        }else{
            $oTask->blocked_till = null;
            $oTask->last_success = 1;
        }
        $oTask->executed++;

        $oTask->date_lastimport = $lastImportDate;
        $oTask->date_nextimport = $nextImportDate;
        AMI_Registry::set('ami_allow_model_save', true);
        $oTask->save();

        return true;
    }

    /**
     * Returns records hashes for the task.
     *
     * @param integer $taskId  Task identifier
     * @return array
     */
    public function getHashes($taskId){
        $qString = "SELECT data_hash
                        FROM cms_data_import_history
                        WHERE id_task = %s";
        $qHashes = DB_Query::getSnippet($qString)->plain($taskId)->get();
        $oResult = AMI::getSingleton('db')->select($qHashes);

        $aHashes = array();
        foreach($oResult as $oHash){
            $aHashes[] = $oHash['data_hash'];
        }

        return $aHashes;
    }
}