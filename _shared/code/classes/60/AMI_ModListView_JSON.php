<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModListView_JSON.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.12.0
 */

/**
 * Module list component JSON view abstraction.
 *
 * @package    ModuleComponent
 * @subpackage View
 * @since      5.12.0
 */
abstract class AMI_ModListView_JSON extends AMI_ModListView{
    /**
     * Columns footers
     *
     * @var array
     * @since     5.14.0
     */
    protected $aColumnFooters = array();

    /**
     * Tensile columns
     *
     * @var array
     */
    protected $aTensileColumns = array();

    /**
     * Columns with fixed width
     *
     * @var array
     */
    protected $aFixedWidthColumns = array();

    /**
     * Returns view data.
     *
     * @return array
     * @todo   Pass list columns format, sortable
     * @todo   Detect necessity of passing $aResponse to the "on_list_view" event
     */
    public function get(){
        $aScope = $this->getScope('list');

        $aEvent = array(
            'oView'    => $this,
            'aScope'   => &$aScope,
            'aLocale'  => &$this->aLocale
        );
        /**
         * Allows modify list of columns in list of elements.
         *
         * @event      on_list_columns $modId
         * @eventparam AMI_ModListView_JSON oView    View Model
         * @eventparam array                aScope   Scope
         * @eventparam array                aLocale  Array of locales
         */
        AMI_Event::fire('on_list_columns', $aEvent, $this->getModId());
        unset($aEvent);
        $aColumns = $this->getColumns();

        $appliedId = AMI::getSingleton('env/request')->get('ami_applied_id', false);

        $orderColumn = isset($aScope['order_column']) ? $aScope['order_column'] : $this->orderColumn;
        $orderDirection = mb_strtolower(isset($aScope['order_direction']) ? $aScope['order_direction'] : $this->orderDirection);

        if($appliedId){
            $aEvent = array(
                'oTable'         => $this->oModel,
                'appliedId'      => $appliedId,
                'orderColumn'    => $orderColumn,
                'orderDirection' => $orderDirection,
                // 'groupBy'        =>
                'aColumns'       => $aColumns
            );
            /**
             * Calls before location of item position in list.
             *
             * @event      on_list_locate_pos $modId
             * @eventparam AMI_ModTable oTable    Table item model
             * @eventparam string apliedId        Applied item id
             * @eventparam string orderColumn     Order column name
             * @eventparam string orderDirection  Order direction
             * @eventparam array aColumns         Columns
             */
            AMI_Event::fire('on_list_locate_pos', $aEvent, $this->getModId());
            if(isset($aEvent['offset'])){
                $overridenOffset = $aEvent['offset'];
            }
            unset($aEvent);
        }

        $currentOffset = (int)AMI_Registry::get($this->getModId() . '_paginator_offset', 0);
        $currentPageSize = (int)AMI_Registry::get($this->getModId() . '_paginator_page_size', 10);

        $oModelList = $this->oModel
            ->getList();
        $aEvent = array(
            'oTable'   => $this->oModel,
            'oList'    => $oModelList,
            'aColumns' => $aColumns
        );
        /**
         * Aloows to add columns to a list.
         *
         * @event      on_list_add_columns $modId
         * @eventparam AMI_ModTable     oTable    Table model
         * @eventparam AMI_ModTableList oList     Table l;ist model
         * @eventparam array            aColumns  Array of columns
         */
        AMI_Event::fire('on_list_add_columns', $aEvent, $this->getModId());
        $oModelList
            ->addColumns($aColumns)
            ->addNavColumns()
            ->addCalcFoundRows(!empty($aScope['calc_found_rows']))
            ->setLimitParameters($currentOffset, $currentPageSize)
            ->addOrder($orderColumn, $orderDirection);
        $pkField = $this->oModel->getItem()->getPrimaryKeyField();
        if($orderColumn != $pkField && $oModelList instanceof AMI_ModTableList){
            $oModelList->addOrder($pkField, $orderDirection);
        }
        $oModelList->load();

        // If the returned dataset is null, decrease current offset and try again
        if($oModelList->count() == 0 && $currentOffset > 0){
            $overridenOffset = ($currentOffset - $currentPageSize > 0) ? ($currentOffset - $currentPageSize) : 0;
            $oModelList = $oModelList
                ->addCalcFoundRows(true)
                ->setLimitParameters($overridenOffset, $currentPageSize);
            $oModelList->load();
        }

        $aSortColumns = $this->getSortColumns();
        $aEvent = array(
            'aColumns' => &$aSortColumns,
            'oList'    => $oModelList // since 5.14.0
        );
        /**
         * Allows modify list of sortable columns in list.
         *
         * @event      on_sort_columns $modId
         * @eventparam array            aColumns  Array of columns
         * @eventparam AMI_ModTableList oList     Table list model
         */
        AMI_Event::fire('on_sort_columns', $aEvent, $this->getModId());
        unset($aEvent);

        $aColumnTypes = $this->getColumnTypes();

        $aResponse = array(
            'listData'    => array(
                'dataType'  => 'array',
                'dataCount' => $oModelList->getNumberOfFoundRows(),
                'sort'      => array(
                    'col' => $orderColumn,
                    'dir' => $orderDirection
                )
            ),
            'listColumns'       => array(),
            'list'              => array(),
            'listFooter'        => array()
        );
        if(isset($overridenOffset)){
            $aResponse['listData']['offset'] = $overridenOffset;
        }

/*
        if(!empty($aScope['_action_list']) && AMI::issetOption($this->getModId(), '_source_mod_id')){
            // plugin having custom actions
            $css = '';
            $oTpl = $this->getTemplate();
            $blockName = 'json_list_' . $this->getModId();
            foreach($aScope['_action_list'] as $action){
                $aPath = array(
                    '_local/_admin/images/' . $this->getModId() . '/icon-' . $action . '.gif',
                    '_local/_admin/images/' . $this->getModId() . '/icon-' . $action . '.png'
                );
                foreach($aPath as $path){
                    if(is_file($GLOBALS['ROOT_PATH'] . $path)){
                        if(!$css){
                            $oTpl->addBlock($blockName, AMI_iTemplate::TPL_MOD_PATH . '/_list.tpl');
                        }
                        $css .= $oTpl->parse(
                            $blockName . ':action_icon_css',
                            array(
                                'action' => $action,
                                'url'    => $GLOBALS['ROOT_PATH_WWW'] . $path
                            )
                        );
                        break;
                    }
                }
            }
            if($css){
                $aResponse['listData']['css'] = $css;
            }
            unset($css, $oTpl, $blockName, $aPath, $path, $action);
        }
*/

        if(!empty($aScope['_group_actions'])){
            $aResponse['listGrpActions'] = $aScope['_group_actions'];
        }
        if(!empty($aScope['_full_env_actions'])){
            $aResponse['listFullEnvActions'] = $aScope['_full_env_actions'];
        }

        // $this->setColumnPlaceholders($aColumns);
        $this->convertColumns($aColumns);
        $aTensileColumns = $this->getTensileColumns();
        $aColumnFooters = $this->getColumnFooters();

        // list header (columns description) {

        $aPlaceholders = $this->getPlaceholders();
        foreach($aPlaceholders as $placeholder){
            if(!in_array($placeholder, $aColumns)){
                continue;
            }
            $column = $placeholder;
            if(isset($aColumnTypes[$column]) && $aColumnTypes[$column] === 'none'){
                continue;
            }
            $aColData = array(
                'format'    => isset($aColumnTypes[$column]) ? $aColumnTypes[$column] : 'text',
/*
                'bSortable' => in_array($column, $aSortColumns),
                'bEditable' => isset($aColumnTypes[$column]) && $aColumnTypes[$column] != 'hidden',
                'bTensile'  => isset($aTensileColumns[$column]),
*/
                'caption'   =>
                    isset($this->aLocale['list_col_' . $column])
                        ? $this->aLocale['list_col_' . $column]
                        : (isset($this->aLocale['caption_' . $column]) ? $this->aLocale['caption_' . $column] : '')
            );
            if(in_array($column, $aSortColumns)){
                $aColData['bSortable'] = true;
            }
            if(isset($aColumnTypes[$column]) && !in_array($aColumnTypes[$column], array('hidden', 'array', 'object'))){
                $aColData['bEditable'] = true;
            }
            if(isset($aTensileColumns[$column])){
                $aColData['bTensile'] = true;
            }
            if(isset($aColumnFooters[$column])){
                $aColFooterData = array();
                if(isset($aColumnFooters[$column]['caption']) && isset($this->aLocale[$aColumnFooters[$column]['caption']])){
                    $aColFooterData['caption'] = $this->aLocale[$aColumnFooters[$column]['caption']];
                }
                if(isset($aColumnFooters[$column]['data'])){
                    $aColFooterData['data'] = $aColumnFooters[$column]['data'];
                }
                $aResponse['listFooter'][$column] = $aColFooterData;
                unset($aColFooterData);
            }
            $aResponse['listColumns'][$column] = $aColData;
        }
        unset($aColData);

        $aEvent = array('columns' => &$aResponse['listColumns']);
        /**
         * Allows to modify async list columns.
         *
         * @event      after_list_columns $modId
         * @eventparam array &columns  Reference to array of columns
         */
        AMI_Event::fire('after_list_columns', $aEvent, $this->getModId());
        unset($aEvent);

        // } list header (columns description)
        // list rows {

        $addTplBlock = true;

        foreach($oModelList as $oModelItem){
            $aRow = array();

            $aEvent = array(
                'aScope'     => $aScope + $oModelItem->getData(),
                'oTableItem' => $oModelItem, // @deprecated since 5.14.0
                'oItem'      => $oModelItem
            );
            /**
             * Allows to modify async list row.
             *
             * @event      on_list_body_row $modId
             * @eventparam array            aScope  Row scope
             * @eventparam AMI_ModTableItem oItem   Table item model
             */
            AMI_Event::fire('on_list_body_row', $aEvent, $this->getModId());

            foreach($aPlaceholders as $placeholder){
                if(!in_array($placeholder, $aColumns)){
                    continue;
                }
                $column = $placeholder;

                $aEvent['aScope']['list_col_name'] = $column;
                $aEvent['aScope']['list_col_value'] = isset($aEvent['aScope'][$column]) ? $aEvent['aScope'][$column] : null;
                /**
                 * Allows modify the data line of the list.
                 *
                 * @event      on_list_body_row $modId
                 * @eventparam array            aScope  Scope
                 * @eventparam AMI_ModTableItem oItem   Table item model
                 */
                AMI_Event::fire('on_list_body_{' . $column . '}', $aEvent, $this->getModId());
                if(!empty($aEvent['aScope']['_actions']) && in_array($column, $aEvent['aScope']['_actions'])){
                    $aEvent['aScope']['list_col_value'] = array(
                        'format' => 'action',
                        'value'  => $column,
                        'title'  => $this->aLocale['list_action_' . $column]
                    );
                    if(isset($aEvent['aScope']['_value_dependend_actions'][$column])){
                        $enabled = (int)$oModelItem->$column;
                        $aEvent['aScope']['list_col_value']['enabled'] = $enabled;
                        if(!$enabled){
                            $aEvent['aScope']['list_col_value']['title'] = $this->aLocale['list_action_un' . $column];
                        }
                    }
                }
                if(!empty($aEvent['aScope']['_inner_actions'])){
                    $aInnerActions = $aEvent['aScope']['_inner_actions'];
                    do{
                        $action = array_search($column, $aInnerActions);
                        if($action !== false){
                            $oTpl = $this->getTemplate();
                            $blockName = 'inner_actions_' . $this->getModId();
                            if($addTplBlock){
                                // $oTpl->addBlock($blockName, AMI_iTemplate::TPL_MOD_PATH . '/_list.tpl');
                                // $oTpl->mergeBlock($blockName, $this->tplFileName);
                                $oTpl->addBlock($blockName, $this->tplFileName);
                                $addTplBlock = false;
                            }
                            $aEvent['aScope']['list_col_value'] .=
                                $oTpl->parse(
                                    $blockName . ':list_action_inner',
                                    array(
                                        'column'     => $column,
                                        'action'     => $action,
                                        'parameters' => 'id=' . $oModelItem->id . (empty($aScope['_heavy_actions']) || !in_array($action, $aScope['_heavy_actions']) ? '' : '&ami_full=1')
                                    )
                                );
                            unset($aInnerActions[$action]); // to get next action for current column using array_search($column, $aInnerActions)
                        }else{
                            break;
                        }
                    }while(true);
                }

                $aRow[$column] = $aEvent['aScope']['list_col_value'];
            }
            $aResponse['list'][] = $aRow;
        }

        // } list rows

        $scripts = $this->getScripts($aScope);
        if($scripts){
            $aResponse['listData']['scripts'] = $scripts;
        }

        $aResponse['listData']['columnLayout'] = $this->aColumnLayouts;

        $aEvent = array(
            'aScope' => $aScope + array(
                'header' => &$aResponse['listColumns'],
                'body'   => &$aResponse['list'],
                'footer' => &$aResponse['listFooter']
            ),
            'aResponse'  => &$aResponse
        );
        /**
         * Allows modify the value of header, body and footer of list.
         *
         * @event      on_list_view $modId
         * @eventparam array aScope  Contens keys header, body, footer
         * @eventparam AMI_Response oResponse  Response object
         */
        AMI_Event::fire('on_list_view', $aEvent, $this->getModId());

        return $aResponse;
    }

    /**
     * Adds standard action columns.
     *
     * @return void
     */
    public function addActionColumns(){
        $this->addColumnType('actions', 'object');
        AMI_Event::addHandler('on_list_body_{actions}', array($this, 'handleActionCell'), $this->getModId());
    }

    /**
     * Event handler.
     *
     * Handling action cell.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     * @see    AMI_ModListView::formatColumn()
     * @todo   Use array_map?
     */
    public function handleActionCell($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['aScope']['list_col_value'] = array();
        if(isset($aEvent['aScope']['_action_col'])){
            foreach($aEvent['aScope']['_action_col'] as $action => $aAction){
                $aAction['title'] = $this->aLocale['list_action_' . $aAction['value']];
                $aEvent['aScope']['list_col_value'][$action] = $aAction;
            }
        }
        return $aEvent;
    }

    /**
     * Adds footer for corresponding column.
     *
     * @param  string $column  Column name
     * @param  string $aData   Column footer data
     *
     * @return AMI_ModListView_JSON
     * @since     5.14.0
     */
    public function addColumnFooter($column, $aData = array()){
        $this->aColumnFooters[$column] = $aData;
        return $this;
    }

    /**
     * Returns columns footers.
     *
     * @return array
     * @since     5.14.0
     */
    protected function getColumnFooters(){
        return $this->aColumnFooters;
    }

    /**
     * Returns tensile columns.
     *
     * Can be overridden in child classes to cancel {@link AMI_ModListView_JSON::setColumnTensility()} calls.
     *
     * @return array
     */
    protected function getTensileColumns(){
        return $this->aTensileColumns;
    }

    /**
     * Sets tensile column.
     *
     * @param  string $column   Column name
     * @param  bool $isTensile  Tensility flag
     * @return AMI_ModListView_JSON
     */
    protected function setColumnTensility($column, $isTensile = true){
        if($isTensile){
            $this->aTensileColumns[$column] = true;
            // Tensible column cannot have fixed width
            $this->setColumnWidth($column, null);
        }else{
            unset($this->aTensileColumns[$column]);
        }
        return $this;
    }

    /**
     * Returns column types for JavaScript controller.
     *
     * @param  array $aColumns  List columns, the result in AMI_ModTable::getAvailableFields() format
     * @return array
     * @see    AMI_ModTable::getColumnTypes()
     */
/*
    protected function getColumnTypes(array $aColumns){
        $aTypes = $this->oModel->getColumnTypes($aColumns);
        foreach($aColumns as $column){
            if(in_array($aTypes[$column], array('datetime', 'date', 'time'))){
                $aTypes[$column] = 'date';
            }else{
                $aTypes[$column] = 'text';
            }
        }
        echo '<pre>';print_r($this->oModel->getValidators());die;

        return $aTypes;
    }
*/
}
