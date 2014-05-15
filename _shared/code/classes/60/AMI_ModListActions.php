<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModListActions.php 44116 2013-11-20 07:32:33Z Leontiev Anton $
 * @since     5.12.0
 */

/**
 * List action controller.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.12.0
 * @resource   list/actions <code>AMI::getResource('list/actions')</code>
 */
class AMI_ModListActions{
    /**
     * Action event data
     *
     * @var array
     * @see AMI_ModListActions::setActionData()
     */
    protected $aEvent;

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     * @see    AMI_ModListAdm::addActions()
     * @see    AMI_ModListAdm::addColActions()
     */

    /**
     * Stores action event data for posterior usage.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListAdm::addActionCallback()
     */
    public function setActionData($name, array $aEvent, $handlerModId, $srcModId){
        $this->aEvent = array($aEvent, $handlerModId, $srcModId);

        return $aEvent;
    }

    /**
     * Dispatches 'delete' action.
     *
     * Deletes item.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchDelete($name, array $aEvent, $handlerModId, $srcModId){
        $oItem = $this->getItem($this->getRequestId(), array('id', 'public'));
        $oItem->delete();
        if($oItem->getId()){
            $statusMsg = 'status_del_fail';
            $aEvent['failed'] = true;
        }else{
            $statusMsg = 'status_del';
        }
        $aEvent['oResponse']->addStatusMessage($statusMsg);
        $this->refreshView();
        return $aEvent;
    }

    /**
     * Dispatches 'public' action.
     *
     * Publishes item (sets 'public' field to 1).
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchPublic($name, array $aEvent, $handlerModId, $srcModId){
        $this->changeItemFlag($this->getRequestId(), 'public', 1);
        $aEvent['oResponse']->addStatusMessage('status_publish');
        $this->refreshView();
        return $aEvent;
    }

    /**
     * Dispatches 'unpublic' action.
     *
     * Unpublishes item (sets 'public' field to 0).
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchUnPublic($name, array $aEvent, $handlerModId, $srcModId){
        $this->changeItemFlag($this->getRequestId(), 'public', 0);
        $aEvent['oResponse']->addStatusMessage('status_publish');
        $this->refreshView();
        return $aEvent;
    }

    /**#@-*/

    /**
     * Refreshes list view.
     *
     * @return void
     */
    protected function refreshView(){
        $this->checkEventData();
        AMI::getSingleton('response')->setType('JSON');
        /**
         * List view module controller action.
         *
         * @event      dispatch_mod_action_list_view $modId
         * @eventparam string        modId         Module id
         * @eventparam AMI_Mod|null  oController   Module controller object
         * @eventparam string        tableModelId  Table model resource id
         * @eventparam AMI_Request   oRequest      Request object
         * @eventparam AMI_Response  oResponse     Response object
         * @eventparam string        action        Action
         */
        AMI_Event::fire('dispatch_mod_action_list_view', $this->aEvent[0], $this->aEvent[1]); // , $this->aEvent[2]);
    }

    /**
     * Returns 'id' parameter from request.
     *
     * @return string
     */
    protected function getRequestId(){
        $this->checkEventData();
        return AMI::getSingleton('env/request')->get('mod_action_id', null);
    }

    /**
     * Returns corresponding table item model.
     *
     * @param  string $id       Item primary key
     * @param  array  $aFields  Fields to load (since 5.12.8)
     * @return AMI_ModTableItem
     */
    protected function getItem($id, array $aFields = array('*')){
        $this->checkEventData();
        /**
         * @var AMI_ModTable
         */
        $oTable = AMI::getResourceModel($this->aEvent[0]['tableModelId']);
        if($aFields !== array('*')){
            $aTmpFields = array();
            foreach($aFields as $field){
                if($oTable->hasField($field)){
                    $aTmpFields[] = $field;
                }
            }
            $aFields = $aTmpFields;
        }
        return $oTable->find($id, $aFields);
    }

    /**
     * Changes one field of table item & save it.
     *
     * Example:
     * <code>
     * class AMI_ModListActions{
     *     // ...
     *     public function dispatchPublic($name, array $aEvent, $handlerModId, $srcModId){
     *         $this->changeItemFlag($this->getRequestId(), 'public', 1);
     *         $aEvent['oResponse']->addStatusMessage('status_publish');
     *         $this->refreshView();
     *         return $aEvent;
     *     }
     * </code>
     *
     * @param  string $id     Item primary key
     * @param  string $flag   Flag
     * @param  string $value  Value
     * @return AMI_ModTableItem
     */
    protected function changeItemFlag($id, $flag, $value){
        $oItem = $this->getItem($id, array('id', $flag));
        $oItem->setValue($flag, $value);
        return $oItem->save();
    }

    /**
     * Checks saved event data.
     *
     * @return void
     * @see    AMI_ModListActions::setActionData()
     * @amidev
     */
    protected function checkEventData(){
        if(!is_array($this->aEvent)){
            trigger_error('Event dispather called without AMI_ModListActions::setActionData()', E_USER_ERROR);
        }
    }
}

/**
 * List group action controller.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @since      5.12.4
 * @resource   list/actions/group <code>AMI::getResource('list/actions/group')</code>
 */
class AMI_ModListGroupActions extends AMI_ModListActions{
    /**
     * Limit for AMI_ModListGroupActions::handleListRecordset()
     *
     * @var int
     */
    private $limitStart;

    /**
     * Limit for AMI_ModListGroupActions::handleListRecordset()
     *
     * @var int
     */
    private $limit;

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     * @amidev Temporary
     */

    /**
     * Dispatches group 'delete' action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchGrpDelete($name, array $aEvent, $handlerModId, $srcModId){
        $count = 0;
        foreach($this->getRequestIds() as $id){
            $oItem = $this->getItem($id, array('id', 'public'));
            $oItem->delete();
            if(!$oItem->getId()){
                $count += 1;
            }
        }
        $aEvent['oResponse']->addStatusMessage('status_grp_del', array('num_items' => $count));
        $this->refreshView();
        return $aEvent;
    }

    /**
     * Dispatches group 'public' action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchGrpPublic($name, array $aEvent, $handlerModId, $srcModId){
        $aRequestIds = $this->getRequestIds($aEvent);
        foreach($aRequestIds as $id){
            $this->changeItemFlag($id, 'public', 1);
        }
        $aEvent['oResponse']->addStatusMessage('status_grp_publish', array('num_items' => sizeof($aRequestIds)));
        $this->refreshView();
        return $aEvent;
    }

    /**
     * Dispatches group 'unpublic' action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchGrpUnPublic($name, array $aEvent, $handlerModId, $srcModId){
        $aRequestIds = $this->getRequestIds($aEvent);
        foreach($aRequestIds as $id){
            $this->changeItemFlag($id, 'public', 0);
        }
        $aEvent['oResponse']->addStatusMessage('status_grp_unpublish', array('num_items' => sizeof($aRequestIds)));
        $this->refreshView();
        return $aEvent;
    }

    /**
     * Dispatches 'index details' group action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchGrpIndexDetails($name, array $aEvent, $handlerModId, $srcModId){
        $aRequestIds = $this->getRequestIds($aEvent);
        foreach($aRequestIds as $id){
            $this->changeItemFlag($id, 'details_noindex', 0);
        }
        $aEvent['oResponse']->addStatusMessage('status_grp_index_details', array('num_items' => sizeof($aRequestIds)));
        $this->refreshView();
        return $aEvent;
    }

    /**
     * Dispatches 'noindex details' group action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchGrpNoIndexDetails($name, array $aEvent, $handlerModId, $srcModId){
        $aRequestIds = $this->getRequestIds($aEvent);
        foreach($aRequestIds as $id){
            $this->changeItemFlag($id, 'details_noindex', 1);
        }
        $aEvent['oResponse']->addStatusMessage('status_grp_index_details', array('num_items' => sizeof($aRequestIds)));
        $this->refreshView();
        return $aEvent;
    }

    /**
     * Dispatches group 'move_position' action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchGrpMovePosition($name, array $aEvent, $handlerModId, $srcModId){
        $this->checkEventData();
        $this->limitStart = 0;

        $oRequest = AMI::getSingleton('env/request');
        $moveType = $oRequest->get('grp_move_position', null);
        $afterIndex = (int)$oRequest->get('grp_move_position_index', 0);

        $aRequestIds = $this->getRequestIds($aEvent);
        $skipIncrease = false;
        $posAfter = 0;
        $posAfterNext = 0;
        $nMoved = sizeof($aRequestIds);
        if($nMoved > 0){
            /**
             * @var AMI_iDB
             */
            $oDB = AMI::getSingleton('db');
            /**
             * @var AMI_ModTable
             */
            $oTable = AMI::getResourceModel($this->aEvent[0]['tableModelId']);

            if($moveType == 'move_top'){
                $oQuery = new DB_Query($oTable->getTableName());
                $this->localeCondition($oTable, $oQuery);
                $oQuery
                    ->addField('position')
                    ->addOrder('position', 'ASC')
                    ->setLimitParameters(0, 1);
                $pos = $oDB->fetchValue($oQuery);
                if($pos){
                    $posAfterNext = $pos;
                    $oQuery = new DB_Query($oTable->getTableName());
                    $this->localeCondition($oTable, $oQuery);
                    $oQuery
                        ->addField('position')
                        ->addWhereDef("AND `position` < " . $posAfterNext)
                        ->addOrder('position', 'DESC')
                        ->setLimitParameters(0, 1);
                    $posAfter = $oDB->fetchValue($oQuery);
                    if(!$posAfter){
                        $posAfter = $posAfterNext - 1; // min 0
                    }
                }
            }elseif($moveType == 'move_bottom'){
                $skipIncrease = true;
                $oQuery = new DB_Query($oTable->getTableName());
                $this->localeCondition($oTable, $oQuery);
                $oQuery
                    ->addField('position')
                    ->addOrder('position', 'DESC')
                    ->setLimitParameters(0, 1);
                $pos = $oDB->fetchValue($oQuery);
                if($pos){
                    $posAfter = $pos;
                    $posAfterNext = $posAfter + 1;
                }
            }elseif($afterIndex){ // $moveType == 'move_after'
                $grpPosition = max(0, $afterIndex - 1);
                $oQuery = new DB_Query($oTable->getTableName());
                $oQuery->addExpressionField('COUNT(*)');
                $this->localeCondition($oTable, $oQuery);
                $maxCount = $oDB->fetchValue($oQuery);
                if($grpPosition > $maxCount){
                    $aEvent['oResponse']->addStatusMessage('status_grp_position_max_warn', array(), AMI_Response::STATUS_MESSAGE_ERROR);
                    $this->refreshView();
                    return $aEvent;
                }

                $oQuery = new DB_Query($oTable->getTableName());
                $this->localeCondition($oTable, $oQuery);
                $oQuery
                    ->addField('position')
                    ->addOrder('position', 'ASC')
                    ->setLimitParameters($grpPosition, 1);
                $pos = $oDB->fetchValue($oQuery);
                if($pos){
                    $posAfter = $pos;
                }
                $oQuery = new DB_Query($oTable->getTableName());
                $this->localeCondition($oTable, $oQuery);
                $oQuery
                    ->addField('position')
                    ->addWhereDef("AND `position` > " . $posAfter)
                    ->addOrder('position', 'ASC')
                    ->setLimitParameters(0, 1);
                $pos = $oDB->fetchValue($oQuery);
                if($pos !== false){
                    $posAfterNext = $pos;
                }
            }

            // sorting elements ang get positions
            $oQuery =
                DB_Query::getSnippet(
                    "SELECT `id` " .
                    "FROM %s " .
                    "WHERE id IN (%s) " .
                    "ORDER BY `position` ASC"
                )
                    ->plain($oTable->getTableName())
                    ->implode($aRequestIds);
            $aIds = iterator_to_array($oDB->fetchCol($oQuery));

            $numEmptyPlaces = $posAfterNext - $posAfter - 1;
            if($numEmptyPlaces > -1){
                $numIncrease = max(0, $nMoved - $numEmptyPlaces);
                if($numIncrease > 0 && !$skipIncrease){
                    $oSnippet = $this->localeCondition($oTable);
                    $oSnippet =
                        $oSnippet
                        ? DB_Query::getSnippet('WHERE 1 %s AND `position` > ' . $posAfter)->plain($oSnippet)
                        : 'WHERE 1 %s AND `position` > ' . $posAfter;

                    // Enlarge window (move uper items down)
                    $oQuery = DB_Query::getUpdateQuery(
                        $oTable->getTableName(),
                        array('position' => DB_Query::getSnippet('position + ' . $numIncrease)),
                        $oSnippet,
                        true
                    );
                    $oDB->query($oQuery);
                }
            }
            foreach($aIds as $i => $id){
                $oQuery = DB_Query::getUpdateQuery(
                    $oTable->getTableName(),
                    array('position' => $posAfter + $i + 1),
                    'WHERE `id` = ' . $id,
                    true
                );
                $oDB->query($oQuery);
            }
            $aEvent['oResponse']->addStatusMessage('status_grp_position_changed');
        }

        $this->refreshView();
        return $aEvent;
    }

    /**
     * Dispatches group 'id_page' action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchGrpIdPage($name, array $aEvent, $handlerModId, $srcModId){
        $oRequest = AMI::getSingleton('env/request');
        $operation = $oRequest->get('grp_operation');
        $idPage = $oRequest->get('grp_page');
        $pageName = ($idPage) ? AMI_PageManager::getModPageName($idPage, $handlerModId, AMI_Registry::get('lang', 'en')) : '';
        $aRequestIds = $this->getRequestIds($aEvent);
        $oTable = AMI::getResourceModel($this->aEvent[0]['tableModelId']);
        switch($operation){
            case 'copy':
                $numItems = 0;
                foreach($aRequestIds as $id){
                    $oItem = $oTable->find($id);
                    $oItem->resetId();
                    if($oItem->id_page != $idPage){
                        $oItem->id_page = $idPage;
                        $oItem->sublink = '';
                        if(isset($oItem->num_items)){
                            $oItem->num_items = 0;
                        }
                        if(isset($oItem->num_public_items)){
                            $oItem->num_public_items = 0;
                        }
                        $oItem->save();
                        $numItems++;
                    }
                }
                break;
            case 'move':
                $oDB = AMI::getSingleton('db');
                $oQuery =
                    DB_Query::getSnippet("UPDATE %s SET id_page = %s WHERE id IN (%s)")
                        ->plain($oTable->getTableName())
                        ->q($idPage)
                        ->implode($aRequestIds);
                $oDB->query($oQuery);
                $numItems = $oDB->getAffectedRows();
                break;
        }
        $statusMsg = 'status_grp_id_page_' . $operation . (($idPage) ? '' : '_common');
        $aEvent['oResponse']->addStatusMessage($statusMsg, array('page_name' => $pageName, 'num_items' => $numItems));
        $this->refreshView();
        return $aEvent;
    }

    /**
     * Dispatches group 'id_cat' action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchGrpIdCat($name, array $aEvent, $handlerModId, $srcModId){
        $oRequest = AMI::getSingleton('env/request');
        $operation = $oRequest->get('grp_operation');
        $idCat = $oRequest->get('grp_id_cat');
        $catModId = $handlerModId . '_cat';
        $oCatsModel = AMI::getResourceModel($catModId . '/table');
        $oCatItem = $oCatsModel->find($idCat);
        if(!$oCatItem){
            return $aEvent;
        }

        $aRequestIds = $this->getRequestIds($aEvent);
        $numItems = 0;
        foreach($aRequestIds as $id){
            $oItem = $this->getItem($id, array('*'));
            if($oItem && ($oItem->cat_id != $idCat)){
                $oItem->resetId();
                $oItem->id_cat = $idCat;
                $oItem->sublink = '';
                $oItem->save();
                if($oItem->getId()){
                    $numItems++;
                    if($operation == 'move'){
                        unset($oItem);
                        $oItem = $this->getItem($id, array('id', 'public'));
                        $oItem->delete();
                    }
                }
            }
        }

        $statusMsg = 'status_grp_id_cat_' . $operation;
        $aEvent['oResponse']->resetStatusMessages();
        $aEvent['oResponse']->addStatusMessage($statusMsg, array('cat_name' => $oCatItem->header, 'num_items' => $numItems));
        $this->refreshView();
        return $aEvent;
    }

    /**
     * Dispatches group sublink generation.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchGrpGenSublink($name, array $aEvent, $handlerModId, $srcModId){
        $aRequestIds = $this->getRequestIds($aEvent);
        $count = 0;
        if(sizeof($aRequestIds)){
            // suppress model status messages
            AMI_Event::addHandler('on_add_status_message', array($this, 'dispatchAddStatusMessage'), AMI_Event::MOD_ANY);

            /**
             * @var AMI_ModTable
             */
            $oTable = AMI::getResourceModel($this->aEvent[0]['tableModelId']);
            foreach($aRequestIds as $id){
                $oItem = $oTable->find($id, array('*'));
                $origSublink = $oItem->sublink;
                $oItem->sublink = '';
                $oItem->save();
                if($oItem->sublink !== $origSublink){
                    $count++;
                }
            }
            $aEvent['oResponse']->addStatusMessage('status_grp_note_linkauto', array('num_items' => $count));
            AMI_Event::dropHandler('on_add_status_message', array($this));
        }
        $this->refreshView();
        return $aEvent;
    }

    /**
     * Dispatches group HTML-meta fields.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchGrpGenHtmlMeta($name, array $aEvent, $handlerModId, $srcModId){
        $aRequestIds = $this->getRequestIds($aEvent);
        $count = 0;
        if(sizeof($aRequestIds)){
            // suppress model status messages
            AMI_Event::addHandler('on_add_status_message', array($this, 'dispatchAddStatusMessage'), AMI_Event::MOD_ANY);

            $force = !empty($aEvent['_force']);
            $mode = AMI::getOption($aEvent['modId'], 'keywords_generate');
            AMI::setOption($aEvent['modId'], 'keywords_generate', 'force');

            /**
             * @var AMI_ModTable
             */
            $oTable = AMI::getResourceModel($this->aEvent[0]['tableModelId']);
            foreach($aRequestIds as $id){
                $oItem = $oTable->find($id, array($oTable->getItem()->getPrimaryKeyField(), 'header', 'announce', 'body', 'sm_data', 'lang'));
                if($force || !$oItem->html_is_kw_manual){
                    $source = $oItem->html_title . $oItem->html_keywords . $oItem->html_description;
                    $oItem->html_title = '';
                    $oItem->html_keywords = '';
                    $oItem->html_description = '';
                    $oItem->save();
                    if($oItem->html_title . $oItem->html_keywords . $oItem->html_description !== $source){
                        $count++;
                    }
                }
            }
            $aEvent['oResponse']->addStatusMessage('status_grp_note_kwauto', array('num_items' => $count));

            AMI::setOption($aEvent['modId'], 'keywords_generate', $mode);
            AMI_Event::dropHandler('on_add_status_message', array($this));
        }
        $this->refreshView();
        return $aEvent;
    }

    /**
     * Dispatches group HTML-meta fields.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchGrpGenHtmlMetaForce($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['_force'] = true;
        return $this->dispatchGrpGenHtmlMeta($name, $aEvent, $handlerModId, $srcModId);
    }


    /**
     * Handles list recordset.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListRecordset($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['oQuery']->setLimitParameters($this->limitStart, $this->limit);
        return $aEvent;
    }

    /**
     * Handles status messages. Discards item model messages.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchAddStatusMessage($name, array $aEvent, $handlerModId, $srcModId){
        if($aEvent['modId']){
            $aEvent['_break'] = TRUE;
        }
        return $aEvent;
    }

    /**#@-*/

    /**
     * Returns requested ids list as array.
     *
     * @return array
     */
    protected function getRequestIds(){
        $ids = $this->getRequestId();
        if(empty($ids)){
            return array();
        }
        $aAppliedIds = explode(',', $ids);
        AMI::getSingleton('env/request')->set('ami_applied_id', $aAppliedIds[0]);
        return $aAppliedIds;
    }

    /**
     * Returns locacle filter snippet.
     *
     * @param  AMI_ModTable $oTable  Table model
     * @param  DB_Query     $oQuery  DB query to apply DB_Query::addWhereDef() with locale snippet
     * @return DB_Snippet|null
     * @amidev Temporary?
     */
    protected function localeCondition(AMI_ModTable $oTable, DB_Query $oQuery = null){
        if($oTable->hasField('lang')){
            $oSnippet = DB_Query::getSnippet("AND `lang` = %s")->q(AMI_Registry::get('lang_data'));
            if($oQuery){
                $oQuery->addWhereDef($oSnippet);
            }else{
                return $oSnippet;
            }
        }
        return null;
    }
}

/**
 * List position action controller.
 *
 * @package    ModuleComponent
 * @subpackage Controller
 * @amidev
 */
class AMI_ModListPositionActions extends AMI_ModListActions{
    /**
     * Limit for AMI_ModListPositionActions::handleListRecordset()
     *
     * @var int
     */
    private $limit;

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     * @amidev Temporary
     */

    /**
     * Dispatches 'move_up' action.
     *
     * Moves item up in current viewed list (applying filter).
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchMoveUp($name, array $aEvent, $handlerModId, $srcModId){
        $this->moveItem('up', 1);
        $aEvent['oResponse']->addStatusMessage('status_position_changed');
        $this->refreshView();
        return $aEvent;
    }

    /**
     * Dispatches 'move_down' action.
     *
     * Moves item down in current viewed list (applying filter).
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchMoveDown($name, array $aEvent, $handlerModId, $srcModId){
        $this->moveItem('down', 1);
        $aEvent['oResponse']->addStatusMessage('status_position_changed');
        $this->refreshView();
        return $aEvent;
    }

    /**
     * Dispatches 'move_top' action.
     *
     * Moves item in the top of current viewed list (applying filter).
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchMoveTop($name, array $aEvent, $handlerModId, $srcModId){
        $this->moveItem('top');
        $aEvent['oResponse']->addStatusMessage('status_position_changed');
        $this->refreshView();
        return $aEvent;
    }

    /**
     * Dispatches 'move_bottom' action.
     *
     * Moves item in the bottom of current viewed list (applying filter).
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchMoveBottom($name, array $aEvent, $handlerModId, $srcModId){
        $this->moveItem('bottom');
        $aEvent['oResponse']->addStatusMessage('status_position_changed');
        $this->refreshView();
        return $aEvent;
    }

    /**
     * Handles list recordset. Sets query limit parameters.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function handleListRecordset($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['oQuery']->setLimitParameters(0, $this->limit);
        return $aEvent;
    }

    /**#@-*/

    /**
     * Moves item.
     *
     * Moves item to required position (up, down, top or bottom).
     *
     * @param  string $target  Target direction of moving (up, down, top or bottom)
     * @param  int    $count   Items count
     * @return void
     */
    private function moveItem($target, $count = 0){
        $id = $this->getRequestId();
        /**
         * @var AMI_iDB
         */
        $oDB = AMI::getSingleton('db');
        $oItem = $this->getItem($id, array('id', 'position'));
        if($oItem->getId()){
            if($target == 'up' || $target == 'top'){
                $cond = '<';
                $sortDim = 'DESC';
            }else{
                $cond = '>';
                $sortDim = 'ASC';
            }
            $oTable = $oItem->getTable();
            $oList =
                $oTable
                    ->getList()
                    ->addColumns(array('id', 'position'))
                    ->addWhereDef(
                        DB_Query::getSnippet('AND i.position %s %s')
                        ->plain($cond)
                        ->q($oItem->position)
                    )
                    ->addOrder('position', $sortDim);

            // #CMS-11382 {

            $catId = (int)$this->aEvent[0]['oRequest']->get('category');
            if($catId){
                foreach(array('id_cat' => 'cat.id', 'id_parent' => 'i.id_parent') as $checkField => $field){
                    if($oTable->hasField($checkField)){
                        $oList->addWhereDef(
                            DB_Query::getSnippet('AND %s = %s')
                            ->plain($field)
                            ->plain($catId)
                        );
                        break;
                    }
                }
            }

            // } #CMS-11382

            if($count){
                $oList->setLimitParameters(0, $count);
            }
            $pos = $oItem->position;
            $this->limit = (int)$count;
            // Hack to override AMI_ModListPagination::handleListRecordset() limits
            AMI_Event::addHandler('on_list_recordset', array($this, 'handleListRecordset'), $this->aEvent[1], AMI_Event::PRIORITY_LOW);
            $oList->load();
            AMI_Event::dropHandler('on_list_recordset', $this);
            foreach($oList as $oListItem){
                if($oListItem->getId() == $id){
                    break;
                }
                @set_time_limit(29);
                $lastPos = $oListItem->position;
                /*
                $oListItem->position = $pos;
                $oListItem->save();
                */
                $oQuery = DB_Query::getUpdateQuery(
                    $oItem->getTable()->getTableName(),
                    array('position' => $pos),
                    DB_Query::getSnippet('WHERE `id` = %s')->q($oListItem->getId()),
                    true
                );
                $oDB->query($oQuery);
                $pos = $lastPos;
            }
            if($pos != $oItem->position){
                $oQuery = DB_Query::getUpdateQuery(
                    $oItem->getTable()->getTableName(),
                    array('position' => $pos),
                    DB_Query::getSnippet('WHERE `id` = %s')->q($oItem->getId()),
                    true
                );
                $oDB->query($oQuery);
            }
        }
    }
}

## /**
##  * List group action controller
##  *
##  * @package    ModuleComponent
##  * @subpackage Controller
##  * @static
##  */
## class AMI_ModListActions{
##    /**
##     * @var  string
##     * @todo Find out where could we store it
##     */
##    protected static $_varName = 'grp_ids';
##
##    /**#@+
##     * Event handler
##     *
##     * Module list common group actions related handlers
##     *
##     * @param  string $name
##     * @param  array $aEvent
##     * @param  string $handlerModId
##     * @param  string $srcModId
##     * @return array
##     * @see    AMI_Event::addHandler()
##     * @see    AMI_Event::fire()
##     */
##
##    public static function dispatchGrpGenSublink($name, array $aEvent, $handlerModId, $srcModId){
##        $this->callTableModelMethod($aEvent, 'genSublink');
##        return $aEvent;
##    }
##
##    public static function dispatchGrpGenHTMLMeta($name, array $aEvent, $handlerModId, $srcModId){
##        $this->callTableModelMethod($aEvent, 'genHTMLMeta');
##        return $aEvent;
##    }
##
##    public static function dispatchGrpGenHTMLMetaForce($name, array $aEvent, $handlerModId, $srcModId){
##        $this->callTableModelMethod($aEvent, 'genHTMLMeta', array(true));
##        return $aEvent;
##    }
##
##    public static function dispatchRepair($name, array $aEvent, $handlerModId, $srcModId){
##        $oModTableList = AMI::getResourceModel($aEvent['tableModelId'])->getItem();
##        $type = $aEvent['oController']->getRequestParam('type', null);
##        switch($type){
##            case 'search_hash':
##                $oModTableList->repairSearchHash();
##                break;
##            case 'positions':
##                $oModTableList->repairPositions();
##                break;
##        }
##        return $aEvent;
##    }
##
##    /**#@-*/
##
##    /**
##     * Returns array of ids from request
##     *
##     * @param  AMI_Request $oRequest
##     * @return array
##     */
##    protected static function getIds(AMI_Mod $oMod){
##        $aIds = AMI::getSingleton('env/request')->get($this->$_varName, null);
##        $aIds = is_null($aIds) ? array() : explode(',', $aIds);
##        return $aIds;
##    }
##
##    /**
##     * @param  AMI_Request $oRequest
##     * @param  string $flag
##     * @return void
##     * @todo   return void?
##     */
##    protected static function changeFlag(array $aEvent, $flag, $value){
##        $count = 0;
##        foreach($this->getIds($aEvent['oController']) as $id){
##            $oModTable = AMI::getResourceModel($aEvent['tableModelId'], array($id));
##            if($oModTable->getId()){
##                $oModTable->$flag = $value;
##                $oModTable->save();
##                $count++;
##            }
##        }
##        echo $count, ' processed';
##    }
##
##    /**
##     * @param  array $aEvent
##     * @param  string $method
##     * @param  array $aArgs
##     * @return void
##     * @todo   return void?
##     */
##    protected static function callTableModelMethod(array $aEvent, $method, array $aArgs = array()){
##        $count = 0;
##        foreach($this->getIds($aEvent['oController']) as $id){
##            $oModTable = AMI::getResourceModel($aEvent['tableModelId'], array($id));
##            if($oModTable->getId()){
##                if(sizeof($aArgs)){
##                    call_user_func_array(array($oModTable, $method), $aArgs);
##                }else{
##                    call_user_func(array($oModTable, $method));
##                }
##                $oModTable->save();
##                $count++;
##            }
##        }
##        echo $count, ' processed';###
##    }
##
##    /**
##     * @param  array $aEvent
##     * @param  string $method
##     * @param  array $aArgs
##     * @return void
##     * @todo   return void?
##     */
##    protected static function callTableListModelMethod(array $aEvent, $method, array $aArgs = array()){
##        array_unshift($aArgs, $this->getIds($aEvent['oController']));
##        call_user_func_array(array(AMI::getResourceModel($aEvent['tableModelId'])->getItem(), $method), $aArgs);
##    }
##}