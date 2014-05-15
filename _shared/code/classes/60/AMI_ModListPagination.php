<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModListPagination.php 47117 2014-01-28 13:56:01Z Kolesnikov Artem $
 * @since     5.12.0 (not in API Reference)
 * @amidev
 */

/**
 * Module list pagination.
 *
 * @category   AMI
 * @package    ModuleComponent
 * @subpackage Controller
 * @amidev
 */
class AMI_ModListPagination extends AMI_ModComponent{

    /**
     * Array of pagination data
     *
     * @var array
     */
    protected $aPaginationData = array(
        'offset'            => 0,
        'page_size'         => 10,
        'page_size_options' => array(1, 5, 10, 15, 25, 50, 75, 100, 200),
        'max_pages_count'   => 19,
        'max_count'         => 0,
        'start_offset'      => 0
    );

    /**
     * Pager request variables prefix
     *
     * @var array
     */
    protected $prefix = '';

    /**
     * AMI_iModComponent::getType() implementation.
     *
     * @return string
     */
    public function getType(){
        return 'pagination';
    }

    /**
     * Initialization.
     *
     * @return AMI_ModListPagination
     */
    public function init(){
        $this->prefix = AMI::getSingleton('env/request')->get('componentId', '');
        if($this->prefix){
            $this->prefix = $this->prefix . '_';
        }

        // default action
        AMI_Event::addHandler('dispatch_mod_action_list_view', array($this, AMI::actionToHandler('view')), $this->getModId());
        AMI_Event::addHandler('on_list_locate_pos', array($this, 'handleListLocatePosition'), $this->getModId());
        return $this;
    }

    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Handles view.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchView($name, array $aEvent, $handlerModId, $srcModId){
        $this->displayView();

        // Pager model initialization
        $aRequestScope = AMI::getSingleton('env/request')->getScope();

        $this->aPaginationData['mod_id'] = $this->getModId();

        if(isset($aRequestScope['offset'])){
            $this->aPaginationData['offset'] = max(0, (int)$aRequestScope['offset']);
        }

        $limit = isset($aRequestScope['limit']) ?
            $aRequestScope['limit'] :
            AMI::getSingleton('env/cookie')->get($this->prefix . 'limit', false);
        if($limit && ((AMI_Registry::get('side') == 'frn') || in_array($limit, $this->aPaginationData['page_size_options']))){
            $this->aPaginationData['page_size'] = $limit;
            AMI::getSingleton('env/cookie')->set($this->prefix . 'limit', $limit, time() + AMI_ServerCookie::LIFETIME_HOUR);
        }
        AMI_Registry::set($handlerModId . '_paginator_offset', $this->aPaginationData['offset']);
        AMI_Registry::set($handlerModId . '_paginator_page_size', $this->aPaginationData['page_size']);

        return $aEvent;
    }

    /**
     * Handles list locate position for applied element.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView_JSON::get()
     */
    public function handleListLocatePosition($name, array $aEvent, $handlerModId, $srcModId){
        if(!$this->aPaginationData['page_size']){
            return $aEvent;
        }

        /*
         * $aEvent = array(
         *     'oTable'         => AMI_ModTable AMI_ModListView_JSON::$oModel,
         *     'appliedId'      => (int) $appliedId,
         *     'orderColumn'    => (string) $orderColumn,
         *     'orderDirection' => (string) $orderDirection,
         *     'aColumns'       => (array) $aColumns
         * );
         */
        extract($aEvent);

        $index = array_search('actions', $aColumns);
        if($index !== FALSE){
            unset($aColumns[$index]);
        }
        $pkField = $oTable->getItem()->getPrimaryKeyField();
        $pkFieldPrefix = $oTable->getList()->getMainTableAlias(true);

        if(mb_substr($orderColumn, 0, 2) === 'i.'){
            $orderColumn = mb_substr($orderColumn, 2);
        }
        if(strpos($orderColumn, '.') === false){
            $oModel = $oTable;
        }else{
            list($dependentModel, $orderColumn) = explode('.', $orderColumn, 2);
            $depResId = $oTable->getDependenceResId($dependentModel);
            if(empty($depResId)){
                trigger_error("No dependent model found '" . $dependentModel . "'", E_USER_ERROR);
            }
            $oModel = AMI::getResourceModel($depResId . '/table');
        }

        if(!($oModel instanceof AMI_ArrayIterator)){ // Hack to avoid fatal (CMS-11119)
            if(!$oModel->hasField($orderColumn, true) && !$oModel->getList()->hasExpressionColumn($orderColumn)){
                trigger_error("Unknown order field '" . $orderColumn . "'", E_USER_ERROR);
            }
        }

        $prefix = isset($dependentModel) ? $dependentModel . '.' : $pkFieldPrefix;
        $realOrderColumn = $oModel->getFieldName($orderColumn, $prefix);

        $orderColumn =
            (isset($dependentModel) ? $dependentModel . '_' : '') .
            $orderColumn;

        // Detect sort col value by id
        if(!in_array($orderColumn, $aColumns)){
            $aColumns[] = $orderColumn;
        }
        $oList = $oTable
            ->getList()
            ->addColumns($aColumns)
            ->addWhereDef(
                DB_Query::getSnippet('AND %s = %s')
                ->plain($oTable->getFieldName($pkField, $pkFieldPrefix))
                ->q($appliedId)
            )
            ->load();
        foreach($oList as $oItem){
            break;
        }
        if(
            // Applied item not under filter
            !isset($oItem) ||
            // We cannot detect applied item sort field value properly
            is_null($oItem->$orderColumn)
        ){
            return $aEvent;
        }

        /**
         * @var AMI_ModTableList
         */
        $oList = $oTable
            ->getList()
            // ->addExpressionColumn('_pcount', 'COUNT(*)')
            ->addWhereDef(
                DB_Query::getSnippet('AND %s %s %s')
                ->plain($realOrderColumn)
                ->plain($orderDirection == 'asc' ? '<' : '>')
                ->q($oItem->$orderColumn)
            );

        /*
        // doesn't work
        if(is_null($oItem->$orderColumn)){
            $oList
                ->addWhereDef(
                    DB_Query::getSnippet('AND %s IS NULL')
            //        DB_Query::getSnippet('AND %s IS NOT NULL')
                    ->plain($realOrderColumn)
                );
        }else{
            $oList
                ->addWhereDef(
                    DB_Query::getSnippet('AND %s %s %s')
                    ->plain($realOrderColumn)
                    ->plain($orderDirection == 'asc' ? '<' : '>')
                    ->q($oItem->$orderColumn)
                );
        }
        */

        $oQuery = $oList->getQuery();
        if(is_object($oQuery)){
            // Hack to filter list using AMI_ModFilter::dispatchApply()
            $aTmpEvent = array(
                'modId'  => $this->getModId(),
                'oQuery' => $oQuery
            );
            /**
             * Allows to manipulate with elements list's request (object of class DB_Query).
             *
             * @event      on_list_recordset $modId
             * @eventparam string modId    Module Id
             * @eventparam DB_Query oList  Table list model
             */
            AMI_Event::fire('on_list_recordset', $aTmpEvent, $this->getModId());
            $oQuery
                ->resetFields()
                ->addExpressionField('COUNT(*) `_pcount`');
            unset($aTmpEvent);

            $position = 0;
            if(!$oList->getQuery()->isGroupingUsed()){
                /**
                 * @var DB_iRecordset
                 */
                $oRS = AMI::getSingleton('db')->select($oQuery, MYSQL_NUM);
                foreach($oRS as $aRecord){
                    $position = $aRecord[0];
                    break;
                }
            }else{
                $position = $oList->load()->count();
            }
        }else{
            $position = 0;
        }

        $oList = $oTable
            ->getList()
            ->addColumns($aColumns)
            ->addWhereDef(
                DB_Query::getSnippet('AND %s = %s')
                ->plain($realOrderColumn)
                ->q($oItem->$orderColumn)
            )
            ->addOrder($realOrderColumn, $orderDirection);

        if(is_object($oQuery) && $orderColumn != $pkField){
            $oList->addOrder($pkField, $orderDirection);
        }else{
            $oList->setLimitParameters($this->aPaginationData['offset'], $this->aPaginationData['page_size']);
        }

        $position = $oList->getPosition($pkField, $appliedId, $position);
        $offset = (int)(floor($position / $this->aPaginationData['page_size']) * $this->aPaginationData['page_size']);
        $oldOffset = $this->aPaginationData['offset'];
        $this->aPaginationData['offset'] = $offset;
        AMI_Registry::set($handlerModId . '_paginator_offset', $offset);

        if($offset != $oldOffset){
            // override offset
            $aEvent['offset'] = $offset;
        }

        return $aEvent;
    }

    /**#@-*/

    /**
     * AMI_iModComponent::getView() implementation.
     *
     * @return AMI_ModListView
     * @see    AMI_ModListAdm::init()
     */
    public function getView(){
        return $this->_getView('list/pagination/view', false);
    }

    /**
     * Initializes model.
     *
     * @return AMI_ModTable
     */
    protected function initModel(){
        return $this->aPaginationData;
    }

    /**
     * Returns module id
     *
     * @return string
     */
     /*
    protected function getModId(){
        var_dump($this->modId);die;
        if(empty($this->modId)){
            echo "<pre>";
            debug_print_backtrace();die;
            trigger_error("Missing module id for list pagination", E_USER_ERROR);
        }
        return $this->modId;
    }*/
}
