<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModListView.php 47360 2014-02-03 05:47:25Z Leontiev Anton $
 * @since     5.10.0
 */

/**
 * Module list component view abstraction.
 *
 * @package    ModuleComponent
 * @subpackage View
 * @since     5.10.0
 */
abstract class AMI_ModListView extends AMI_ModPlaceholderView{
    /**#@+
     * List set name
     */

    /**
     * Header column set name
     *
     * @var string
     */
    protected $headerItemSet = 'header_item';

    /**
     * Header row set name
     *
     * @var string
     */
    protected $headerRowSet  = 'header_row';

    /**
     * Header set name
     *
     * @var string
     */
    protected $headerSet     = 'header';

    /**
     * Body column set name
     *
     * @var string
     */
    protected $bodyItemSet   = 'body_item';

    /**
     * Body row set name
     *
     * @var string
     */
    protected $bodyRowSet    = 'body_row';

    /**
     * Body set name
     *
     * @var string
     */
    protected $bodySet       = 'body';

    /**
     * Footer set name
     *
     * @var string
     */
    protected $footerSet     = 'footer';

    /**
     * List set name
     *
     * @var string
     */
    protected $listSet       = 'list';

    /**#@-*/

    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'id';

    /**
     * Order column direction
     *
     * @var string
     */
    protected $orderDirection = 'asc';

    /**
     * Model typification
     *
     * @var AMI_ModTable
     */
    protected $oModel;

    /**
     * Columns array
     *
     * @var array
     */
    protected $aColumns = array();

    /**
     * Column layouts
     *
     * @var array
     */
    protected $aColumnLayouts = array();

    /**
     * List default elements template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#list_header',
            '#flags', 'position', 'public', 'flags',
            '#common', 'date_created', 'cat_header', 'common',
            '#columns', 'columns',
            '#actions', 'front_view', 'edit', 'actions',
        'list_header'
    );

    /**
     * Column rules object
     *
     * @var array
     * @see AMI_ModListView::addColumnRule()
     */
    protected $aColumnFormatters = array();

    /**
     * Sort columns list
     *
     * @var array
     */
    protected $aSortColumns = array();

    /**
     * Columns types
     *
     * @var array
     */
    protected $aColumnTypes = array();

    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        $oTpl = $this->getTemplate();
        $aScope = $this->getScope('list');

        $aColumnTypes = $this->getColumnTypes();
        $aColumns = $this->getColumns();

        /**
         * @var AMI_ModTableList
         */
        $oModelList = $this->oModel->getList();
        $aEvent = array(
            'oTable'         => $this->oModel,
            'oList'          => $oModelList,
            'aColumns'       => $aColumns
        );
        /**
         * Event, called to add columns to a list.
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
            ->addOrder(
            	isset($aScope['order_column'])    ? $aScope['order_column']    : $this->orderColumn,
            	isset($aScope['order_direction']) ? $aScope['order_direction'] : $this->orderDirection
            )
            ->load();

        $aEvent = array('columns' => $aColumns);
        /**
         * Allows modify list of columns in list of elements.
         *
         * @event      on_list_columns $modId
         * @eventparam array aColumns  Array of columns
         */
        AMI_Event::fire('on_list_columns', $aEvent, $this->getModId());
        $aColumns = $aEvent['columns'];

#        $aEvent = array('aScope' => &$aScope);

#        $this->setColumnPlaceholders($aColumns);
        $this->convertColumns($aColumns);
        $aPlaceholders = $this->getPlaceholders();

        // $aPlaceholders = $this->getPlaceholders();
        $header = '';
        foreach($aPlaceholders as $placeholder){
            if(!in_array($placeholder, $aColumns)){
                continue;
            }
            $column = $placeholder;
            if(isset($aColumnTypes[$column]) && $aColumnTypes[$column] === 'none'){
                continue;
            }
            $aScope['list_col_name']    = $column;
            $aScope['list_col_caption'] =
                isset($this->aLocale['list_col_' . $column])
                    ? $this->aLocale['list_col_' . $column]
                    : $this->aLocale['caption_' . $column];
#            AMI_Event::fire('on_list_header_{' . $column . '}', $aEvent);
            $header .= $oTpl->parse($this->tplBlockName . ':' . $this->headerItemSet, $aScope);
        }
/*
        foreach($aColumns as $column){
            $aScope['list_col_name']    = $column;
            $aScope['list_col_caption'] = isset($this->aLocale['list_col_' . $column]) ? $this->aLocale['list_col_' . $column] : 'list_col_' . $column;####
#            AMI_Event::fire('on_list_header_{' . $column . '}', $aEvent);
            $header .= $oTpl->parse($this->tplBlockName . ':' . $this->headerItemSet, $aScope);
        }
*/
        $aEvent = array('aScope' => $aScope + array('header_row' => $header));
#        AMI_Event::fire('on_list_header_row', $aEvent);
        $header = $oTpl->parse($this->tplBlockName . ':' . $this->headerRowSet, $aEvent['aScope']);
        $aEvent = array('aScope' => $aScope + array('header' => $header));
        /**
         * Allows modify list's header.
         *
         * @event      on_list_header $modId
         * @eventparam array aScope  Scope
         */
        AMI_Event::fire('on_list_header', $aEvent, $this->getModId());
        $header = $oTpl->parse($this->tplBlockName . ':' . $this->headerSet, $aEvent['aScope']);
        $body = '';
        foreach($oModelList as $oModelItem){
            $aRecord = $oModelItem->getData();
            $trow = '';

            $aEvent = array(
                'aScope'     => $aScope + $aRecord,
                'oTableItem' => $oModelItem, // @deprecated since 5.14.0
                'oItem'      => $oModelItem,
            );
            /**
             * Allows modify the data line of the list.
             *
             * @event      on_list_body_row $modId
             * @eventparam array            aScope  Scope
             * @eventparam AMI_ModTableItem oItem   Table item model
             */
            AMI_Event::fire('on_list_body_row', $aEvent, $this->getModId());

            foreach($aPlaceholders as $placeholder){
                if(!in_array($placeholder, $aColumns)){
                    continue;
                }
                $column = $placeholder;
                if(isset($aColumnTypes[$column]) && $aColumnTypes[$column] === 'none'){
                    continue;
                }
                $aEvent['aScope']['list_col_name'] = $column;
                $aEvent['aScope']['list_col_value'] = $oModelItem->$column;
                /**
                 * Allows modify the value of a cell in the list.
                 *
                 * @event      on_list_body_{item} $modId
                 * @eventparam array            aScope  Scope
                 * @eventparam AMI_ModTableItem oItem   Table item model
                 */
                AMI_Event::fire('on_list_body_{' . $column . '}', $aEvent, $this->getModId());
                $trow .= $oTpl->parse($this->tplBlockName . ':' . $this->bodyItemSet, $aEvent['aScope']);
            }
            $body .= $oTpl->parse($this->tplBlockName . ':' . $this->bodyRowSet, $aEvent['aScope'] + array('body_row' => $trow));
        }
        $aEvent = array('aScope' => $aScope + array('body' => $body));
#        AMI_Event::fire('on_list_body', $aEvent);
        $body = $oTpl->parse($this->tplBlockName . ':' . $this->bodySet, $aEvent['aScope']);
        $aEvent = array('aScope' => $aScope);
#        AMI_Event::fire('on_list_footer', $aEvent);
        $footer = $oTpl->parse($this->tplBlockName . ':' . $this->footerSet, $aEvent['aScope']);
        $aEvent = array(
            'aScope' => $aScope + array(
                'header' => $header,
                'body'   => $body,
                'footer' => $footer
            )
        );
        /**
         * Allows modify the value of header, body and footer of list.
         *
         * @event      on_list_view $modId
         * @eventparam array aScope  Contents header, body, footer
         */
        AMI_Event::fire('on_list_view', $aEvent, $this->getModId());
        return $oTpl->parse($this->tplBlockName . ':' . $this->listSet, $aEvent['aScope']);
    }

    /**
     * Sets fixed column layout.
     *
     * @param string $column  Column name
     * @param array $aLayout  Column layout as array(width => width, align: align, classname: classname)
     * @return AMI_ModListView
     * @since 5.14.4
     */
    public function setColumnLayout($column, array $aLayout){
        // Column width
        if(isset($aLayout['width'])){
            $this->setColumnWidth($column, $aLayout['width']);
        }
        // Alignment
        if(isset($aLayout['align'])){
            $this->setColumnAlign($column, $aLayout['align']);
        }
        // Class name
        if(isset($aLayout['class'])){
            $this->setColumnClass($column, $aLayout['class']);
        }
        return $this;
    }

    /**
     * Sets fixed column width.
     *
     * @param string $column  Column name
     * @param string $width   Column width (allowed values: 'extra-narrow', 'narrow', 'normal', 'wide', 'extra-wide')
     * @return AMI_ModListView
     * @since 5.14.4
     */
    public function setColumnWidth($column, $width){
        if(!isset($this->aColumnLayouts[$column])){
            $this->aColumnLayouts[$column] = array();
        }
        if(!is_null($width)){
            $this->aColumnLayouts[$column]['width'] = $width;
        }elseif(is_null($width) && isset($this->aColumnLayouts[$column]['width'])){
            unset($this->aColumnLayouts[$column]['width']);
        }
        return $this;
    }

    /**
     * Sets fixed column alignment.
     *
     * @param string $column  Column name
     * @param string $align   Column alignment (allowed values: 'left', 'center', 'right', 'justify')
     * @return AMI_ModListView
     * @since 5.14.4
     */
    public function setColumnAlign($column, $align){
        if(!isset($this->aColumnLayouts[$column])){
            $this->aColumnLayouts[$column] = array();
        }
        if(!is_null($align)){
            $this->aColumnLayouts[$column]['align'] = $align;
        }elseif(is_null($align) && isset($this->aColumnLayouts[$column]['align'])){
            unset($this->aColumnLayouts[$column]['align']);
        }
        return $this;
    }

    /**
     * Sets fixed column class name.
     *
     * @param string $column  Column name
     * @param string $class   Column class name (null or empty string to reset)
     * @return AMI_ModListView
     * @since 5.14.4
     */
    public function setColumnClass($column, $class){
        if(!isset($this->aColumnLayouts[$column])){
            $this->aColumnLayouts[$column] = array();
        }
        if(!is_null($class)){
            $this->aColumnLayouts[$column]['class'] = $class;
        }elseif(is_null($class) && isset($this->aColumnLayouts[$column]['class'])){
            unset($this->aColumnLayouts[$column]['class']);
        }
        return $this;
    }

    /**
     * Event handler.
     *
     * Applies formatters to the column cell.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     * @see    AMI_ModListView::formatColumn()
     * @amidev Temporary?
     */
    public function handleFormatCell($name, array $aEvent, $handlerModId, $srcModId){
        if(isset($this->aColumnFormatters[$aEvent['aScope']['list_col_name']])){
            foreach($this->aColumnFormatters[$aEvent['aScope']['list_col_name']] as $aRule){
                $aEvent['aScope']['list_col_value'] = call_user_func(
                    $aRule[0],
                    $aEvent['aScope']['list_col_value'],
                    $aEvent + $aRule[1]
                );
            }
        }
        return $aEvent;
    }

    /**
     * Adds column formatter.
     *
     * Passed $aArgs always contain 'aScope' (array, list view scope) and oItem (AMI_ModTableItem, current list item model).
     *
     * Example:
     * <code>
     * class MyModule_ListViewAdm extends AMI_ModListView{
     *
     *     // ...
     *
     *     public function __construct(){
     *         $this->formatColumn(
     *             'announce',
     *             array($this, 'fmtTruncate'),
     *             array(
     *                 'doSaveWords' => false,
     *                 'length'      => 200
     *             )
     *         );
     *         $this->formatColumn(
     *             'announce',
     *             array($this, 'fmtMyAnnounce'),
     *             array(
     *                 'key1' => 'value1'
     *             )
     *         );
     *     }
     *
     *     protected function fmtMyAnnounce($value, array $aArgs){
     *         // $aArgs contains third AMI_ModListView::formatColumn() argument
     *
     *         // ...
     *
     *         return $value;
     *     }
     *
     *     // ...
     *
     * }
     * </code>
     *
     * @param  string   $column    Column name
     * @param  callback $callback  Callback
     * @param  array    $aArgs     Formatter arguments
     * @return AMI_ModListView
     * @see    AMI_ModListView::fmtTruncate()
     * @since  5.12.0
     * @todo   Decide about public method access (i.e. to call from extensions).
     */
    public function formatColumn($column, $callback, array $aArgs = array()){
        if(!isset($this->aColumnFormatters[$column])){
            $this->aColumnFormatters[$column] = array();
            AMI_Event::addHandler(
                'on_list_body_{' . $column . '}',
                array($this, 'handleFormatCell'),
                $this->getModId()
            );
        }else{
            // Remove old formater with the same callback if exists
            foreach($this->aColumnFormatters[$column] as $index => $aFormatter){
                if($aFormatter[0] === $callback){
                    unset($this->aColumnFormatters[$column][$index]);
                }
            }
        }
        $this->aColumnFormatters[$column][] = array($callback, $aArgs);
        return $this;
    }

    /**#@+
     * Common column formatter.
     *
     * @see AMI_ModListView::formatColumn()
     * @see AMI_ModListView::handleFormatCell()
     */

    /**
     * Truncates column value.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments:
     *                        - <b>length</b> - maximum string length (int, 50 by default);
     *                        - <b>doSaveWords</b> - save whole words (bool, true by default);
     *                        - <b>doStripTags</b> - strip tags before truncation (bool, false by default);
     *                        - <b>doHTMLEncode</b> - HTML encode after truncation (bool, true by default);
     *                        - <b>tail</b> - tail after truncation (string, '...' by default).
     * @return mixed
     * @since  5.12.0
     */
    protected function fmtTruncate($value, array $aArgs){
        // <b>is_special</b> process as htmlspecialchars() result (bool, false by default);
        $aArgs += array(
            'length'       => 50,
            // 'is_special' => false,
            'doSaveWords'  => true,
            'doStripTags'  => false,
            'doHTMLEncode' => true,
            'tail'         => '...'
        );
        if($aArgs['doStripTags']){
            $value = preg_replace('/<span class=\"gadget\".*\/span class=\"gadget\">/', ' ', $value); // Clear gadgets
            $value = strip_tags($value);
        }
        $value = AMI_Lib_String::truncate(
            $value,
            $aArgs['length'],
            false, // $aArgs['is_special'],
            $aArgs['doSaveWords'],
            $aArgs['tail']
        );
        if($aArgs['doHTMLEncode']){
            $value = AMI_Lib_String::htmlChars($value);
        }
        return $value;
    }

    /**
     * Encodes column value.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     * @since  5.12.0
     */
    protected function fmtHTMLEncode($value, array $aArgs){
        return AMI_Lib_String::htmlChars($value);
    }

    /**
     * Strips tags from column value.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     * @since  5.12.0
     */
    protected function fmtStripTags($value, array $aArgs){
        return strip_tags($value);
    }

    /**
     * Replaces column value by its localized caption.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     * @since  5.12.4
     */
    protected function fmtLocaleCaption($value, array $aArgs = array()){
        return $this->aLocale[$value];
    }

    /**
     * Date/time formatter.
     *
     * @param  string $value  Value to format
     * @param  array $aArgs   Arguments:
     *                        - <b>format</b> AMI_Lib_Date::FMT_DATE, AMI_Lib_Date::FMT_TIME or AMI_Lib_Date::FMT_DATETIME;
     * @return mixed
     * @since  5.12.4
     */
    protected function fmtDateTime($value, array $aArgs){
        $aArgs += array('format' => AMI_Lib_Date::FMT_BOTH);
        return AMI_Lib_Date::formatDateTime($value, $aArgs['format']);
    }

    /**
     * Extended Date/time formatter. For FMT_DATEs Replaces yesterday date by localized word "yesterday" (today, tommorow).
     *
     * @param  string $value  Value to format
     * @param  array $aArgs   Arguments:
     *                        - <b>format</b> AMI_Lib_Date::FMT_DATE, AMI_Lib_Date::FMT_TIME or AMI_Lib_Date::FMT_DATETIME;
     * @return mixed
     * @since  5.14.4
     */
    protected function fmtHumanDateTime($value, array $aArgs){
        $aArgs += array('format' => AMI_Lib_Date::FMT_BOTH);
        $hasTimePart = $aArgs['format'] == AMI_Lib_Date::FMT_BOTH;
        if(($aArgs['format'] == AMI_Lib_Date::FMT_DATE) || $hasTimePart){
            $strDate = AMI_Lib_Date::formatDateTime($value, AMI_Lib_Date::FMT_DATE);
            $utime = time();
            $timePart = $hasTimePart ? ' ' . AMI_Lib_Date::formatDateTime($value, AMI_Lib_Date::FMT_TIME) : '';
            if($strDate == AMI_Lib_Date::formatUnixTime($utime, AMI_Lib_Date::FMT_DATE)){
                return $this->fmtLocaleCaption('list_today') . $timePart;
            }elseif($strDate == AMI_Lib_Date::formatUnixTime($utime + 60 * 60 * 24, AMI_Lib_Date::FMT_DATE)){
                return $this->fmtLocaleCaption('list_tomorrow') . $timePart;
            }elseif($strDate == AMI_Lib_Date::formatUnixTime($utime - 60 * 60 * 24, AMI_Lib_Date::FMT_DATE)){
                return $this->fmtLocaleCaption('list_yesterday') . $timePart;
            }
        }
        return AMI_Lib_Date::formatDateTime($value, $aArgs['format']);
    }

    /**
     * Displays image in separate column if (bool)value is TRUE.
     *
     * Example:
     * <code>
     * class MyModule_ListViewAdm extends AMI_ModListViewAdm{
     *     // ...
     *     public function init(){
     *         // ...
     *         // Following code will output flag columns containing green checkbox icon
     *         foreach(array('flag1', 'flag2', ...) as $column){
     *             $this->setColumnLayout($column, array('align' => 'center'));
     *             $this->formatColumn($column, array($this, 'fmtColIcon'), array('class' => 'checked'));
     *         }
     *         // ...
     *     }
     * }
     * </code>
     *
     * @param  mixed $value  Not false to display icon
     * @param  array $aArgs  Arguments:
     *                        - <b>name</b>              - image class name part ("col-icon-{$name}");
     *                        - <b>caption</b>           - forced icon caption,
     *                        - <b>has_inactive</b>      - flag specifying to display inactive icon,
     *                        - <b>caption_inactive</b>  - forced inactive icon caption.
     * @return string
     * @since  5.14.4
     */
    protected function fmtColIcon($value, array $aArgs){
        $name  = isset($aArgs['name']) ? $aArgs['name'] : $aArgs['aScope']['list_col_name'];
        $class = isset($aArgs['class']) ? $aArgs['class'] : $name;
        if($value || !empty($aArgs['has_inactive'])){
            /**
             * @var AMI_ModTableItem
             */
            $oItem   = $aArgs['oItem'];
            $oTpl    = $this->getTemplate();
            $caption = $value ? 'caption' : 'caption_inactive';
            if(isset($aArgs[$caption])){
                $caption = $aArgs[$caption];
            }else{
                $caption = $this->aLocale['list_col_icon_' . $name . ($value ? '' : '_inactive')];
            }
            $setPostix = empty($aArgs['noAction']) ? '' : '_no_action';
            $value =
                $oTpl->parse(
                    $this->tplBlockName . ':list_col_icon' . $setPostix,
                    array(
                        'value'   => $value,
                        'class'   => 'col-icon-' . $class . ($value ? '' : '_inactive'),
                        'caption' => $caption
                    )
                );
        }else{
            $value = '';
        }
        return $value;
    }

    /**#@-*/

    /**
     * Model typification.
     *
     * @param  object $oModel  Model
     * @return AMI_ModListView
     */
    protected function _setModel($oModel){
        return parent::_setModel($oModel);
    }

    /**
     * Return columns for list model.
     *
     * Return columns added by {@link AMI_ModListView::addColumn()}.<br />
     * Can be overridden in child classes to cancel previously added columns.<br /><br />
     *
     * Example:
     * <code>
     * protected function getColumns(){
     *     return array('id', 'name');
     * }
     * </code>
     *
     * @return array
     * @since 5.14.0
     */
    public function getColumns(){
        return $this->aColumns;
    }

    /**
     * Add column into array.
     *
     * Example:
     * <code>
     * // AmiSample_ListViewAdm::__construct()
     * $this
     *     ->addColumn('id')
     *     ->addColumn('nickname')
     *     ->addColumn('birth', 'nickname.before');
     * </code>
     *
     * @param  string $column          Column name
     * @param  string $placeholderPos  Placeholder position (since 5.12.4)
     * @return AMI_ModListView
     */
    public function addColumn($column, $placeholderPos = ''){
		$this->aColumns[] = $column;
        if(!is_null($placeholderPos)){
            $this->putPlaceholder($column, $placeholderPos === '' ? 'columns.end' : $placeholderPos);
        }
        return $this;
    }

    /**
     * Remove column.
     *
     * @param  string $column  Column name
     * @return AMI_ModListView
     * @since  5.12.8
     */
    public function removeColumn($column){
        $index = array_search($column, $this->aColumns);
        if($index !== FALSE){
            array_splice($this->aColumns, $index, 1);
        }
        return $this;
    }

    /**
     * Returns sortable columns.
     *
     * @return array
     * @amidev
     */
    public function getSortColumns(){
        $aColumns = $this->aSortColumns;
        $aEvent = array(
            'oView'    => $this,
            'aColumns' => &$aColumns
        );
        /**
         * Event, called to sort columns in a list.
         *
         * @event      on_list_sort_columns $modId
         * @eventparam AMI_ModListView oView  View Model
         * @eventparam array aColumns  Array of Columns
         */
        AMI_Event::fire('on_list_sort_columns', $aEvent, $this->getModId());
        return $aColumns;
    }

    /**
     * Adding order.
     *
     * @param  string $column   Column name
     * @param  bool $direction  Direction: true - asc, false - desc
     * @return AMI_ModListView
     */
    public function addOrder($column, $direction = true){
        $this->orderColumn    = $column;
        $this->orderDirection = $direction ? 'asc' : 'desc';
        return $this;
    }

    /**
     * Getting sorting order.
     *
     * @return string
     */
    public function getOrder(){
        return $this->orderColumn;
    }

    /**
     * Getting sorting direction.
     *
     * @return string
     */
    public function getOrderDirection(){
        return $this->orderDirection;
    }

    /**
     * Adds sort columns.
     *
     * Example:
     * <code>
     * // AmiSample_ListViewAdm
     * public function __construct(){
     *     // ...
     *     // Init columns
     *     $this
     *         // ...
     *         ->addSortColumns(
     *             array(
     *                 'public',
     *                 'nickname',
     *                 'birth'
     *             )
     *         );
     *     // ...
     * }
     * </code>
     *
     * @param  array $aColumns  Columns list
     * @return AMI_ModListView
     * @since  5.12.4
     */
    protected function addSortColumns(array $aColumns){
        $this->aSortColumns = array_merge($this->aSortColumns, $aColumns);
        return $this;
    }

    /**
     * Append columns having not specified placeholders to the default "columns" or other model placeholders.
     *
     * @param  array  $aColumns  Columns
     * @param  string $placeholder  Placeholder
     * @return void
     * @todo   Subdependent models?
     * @amidev Temporary?
     */
/*
    protected function setColumnPlaceholders(array $aColumns, $placeholder = 'columns.end'){
        $aPlaceholders = $this->getPlaceholders();
        foreach($aColumns as $model => $column){
            if(is_array($column)){
                array_walk($column, array($this, 'cbPrependModel'), $model);
                $this->addPlaceholders(
                    array(
                        '#' . $model => 'common.after',
                        $model       => '#' . $model . '.after'
                    )
                );
                $this->setColumnPlaceholders($column, $model . '.end');
            }else{
                if(!in_array($column, $aPlaceholders)){
                    $this->putPlaceholder($column, $placeholder);
                }
            }
        }
    }
*/

    /**
     * Converts AMI_ModTable::getAvailableFields() result to one-dimensional array using dependent model aliases as prefix.
     *
     * @param  array &$aColumns  Columns
     * @param  string $prefix  Prefix
     * @return void
     * @amidev Temporary?
     */
    protected function convertColumns(array &$aColumns, $prefix = ''){
        $keys = array_keys($aColumns);
        foreach($keys as $index => $key){
            if(is_array($aColumns[$key])){
                $this->convertColumns($aColumns[$key], $key . '_');
                array_splice($aColumns, $index, 1, $aColumns[$key]);
            }elseif($prefix !== ''){
                $aColumns[$key] = $prefix . $aColumns[$key];
            }
        }
    }

    /**
     * Returns column types for JavaScript list controller basing on form view fields format.
     *
     * @return array
     * @amidev
     */
    protected function getColumnTypes(){
        $aColumnTypes = array();
        $resId = $this->getModId() . '/form/view/' . AMI_Registry::get('side');
        if(AMI::isResource($resId)){
            /**
             * @var AMI_ModFormView
             */
            $oForm = AMI::getResource($resId);
            $aColumnTypes = $oForm->getFields();
            array_walk($aColumnTypes, array($this, 'cbFromFieldTransformer'));
        }
        return $this->aColumnTypes + $aColumnTypes;
    }

    /**
     * Adds column (if not added yet) and its type for corresponding column.
     *
     * List loads column types from fields added by {@link AMI_ModFormView::addField()}.<br />
     * Other fields has 'text' type by default, call this method to change default 'text' type.<br />
     * Also virtual fields should be added calling this method to be visible in the list.<br />
     * See {@link AmiSample_ListViewAdm} for usage example.<br /><br />
     *
     * Example:
     * <code>
     * // AmiSample_ListViewAdm
     * public function __construct(){
     *     parent::__construct();
     *
     *     // Init columns
     *     $this
     *         ->addColumn('id')
     *         ->addColumn('nickname')
     *         ->addColumn('birth')
     *         ->addColumnType('age', 'int')
     *         ->setColumnTensility('nickname');
     *     // ...
     * }
     * </code>
     *
     * @param  string $column  Column name
     * @param  string $type    Column type, none|hidden|int|float|text|date|datetime
     *                         - none - no column data will be passed to client (since 5.14.0);
     *                         - hidden - hidden column;
     *                         - int|float - column containing number;
     *                         - text - column containing text;
     *                         - mediumtext - wide column containing text;
     *                         - longtext - column containing long text data
     *                         - date|datetime - column containing date/time.
     * @return AMI_ModListView_JSON
     */
    public function addColumnType($column, $type){
        if(!in_array($column, $this->getColumns())){
            $this->addColumn($column);
        }
        if($type == 'longtext'){
            $this->setColumnTensility($column);
        }
        $this->aColumnTypes[$column] = $type;
        return $this;
    }

    /**
     * Callback transforming form field to column data.
     *
     * @param  array &$aValue  Field structure
     * @return void
     * @see    AMI_ModListView::getColumnTypes()
     */
    private function cbFromFieldTransformer(array &$aValue){
        $aValue = isset($aValue['type']) ? $aValue['type'] : 'text';
    }

    /**
     * Callback for array_walk().
     *
     * @param  string &$value  Value
     * @param  string $key     Key
     * @param  string $prefix  Prefix
     * @return void
     * @see    AMI_ModListView::setColumnPlaceholders()
     */
/*
    private function cbPrependModel(&$value, $key, $prefix){
        $value = $prefix . '_' . $value;
    }

*/
}
