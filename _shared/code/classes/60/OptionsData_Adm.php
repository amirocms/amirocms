<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Service
 * @version   $Id: OptionsData_Adm.php 49905 2014-04-16 09:45:37Z Maximov Alexey $
 * @amidev
 */

/**
 * Options Data.
 *
 * @package Service
 * @amidev
 */
final class OptionsData_Adm extends Hyper_AmiClean_Adm{
    /**
     * Path to status message locales
     *
     * @var string
     */
    protected $statusMessagePath = NULL;

    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request
     * @param AMI_Response $oResponse  Response
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
    	if(!AMI::getSingleton('core')->isSysUser()){
            return;
    	}
        $this->statusMessagePath = AMI_iTemplate::LNG_MOD_PATH . '/options_data_messages.lng';
        parent::__construct($oRequest, $oResponse);
        $this->addComponents(array('filter', 'list', 'form'));
    }

    /**
     * Returns client locale path.
     *
     * @return string
     */
    public function getClientLocalePath(){
        return AMI_iTemplate::LNG_MOD_PATH . '/options_data_client.lng';
    }
}

/**
 * Options Data.
 *
 * @package Service
 * @amidev
 */
class OptionsData_FilterModelAdm extends AMI_Module_FilterModelAdm{
    /**
     * Constructor.
     */
    public function __construct(){
    	$aTmp = array();
    	$oOptions = new CMS_ModulesOptions();
        $oOptions->ReadOption($aTmp, 'core', 'data_opti'.'ons_total');
        $oDB = AMI::getSingleton('db');
        $oRS = $oDB->select('select count(id) as ci from cms_options_data');
        $aRS = $oRS->current();
        /*
        if((int)$aRS['ci'] != (int)$aTmp['value']){
            echo '<div style="color:red; font-size : 30px;">Number of records in DB and in option are different!</div>';
        }
        */

        $this->addViewField(
            array(
                'name'          => 'datefrom',
                'type'          => 'datefrom',
                'flt_type'      => 'date',
                'flt_default'   => AMI_Lib_Date::formatUnixTime(AMI_Lib_Date::UTIME_MIN),
                'flt_condition' => '>=',
                'flt_column'    => 'data_d'
            )
        );
        $this->addViewField(
            array(
                'name'          => 'dateto',
                'type'          => 'dateto',
                'flt_type'      => 'date',
                'flt_default'   => AMI_Lib_Date::formatUnixTime(AMI_Lib_Date::UTIME_MAX),
                'flt_condition' => '<',
                'flt_column'    => 'data_d'
            )
        );
        $this->addViewField(
            array(
                'name'          => 'login',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => '=',
                'flt_column'    => 'data_l'
            )
        );
        $this->addViewField(
            array(
                'name'          => 'ip',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'data_p'
            )
        );
        $this->addViewField(
            array(
                'name'          => 'module',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'data_m'
            )
        );
        $this->addViewField(
            array(
                'name'          => 'option',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'data_o'
            )
        );
        $this->addViewField(
            array(
                'name'          => 'option_value',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'data_a'
            )
        );
    }

    /**
     * For custom data manipulation.
     *
     * @param  string $field  Field name
     * @param  array  $aData  Filter data
     * @return array
     * @amidev Temporary
     */
    protected function processFieldData($field, array $aData){
    	parent::processFieldData($field, $aData);
    	switch ($field){
            case 'module':
            case 'option':
                $aData['value'] = str_rot13($aData['value']);
                break;
            case 'ip':
                $aData['value'] = str_rot13($aData['value']);
            case 'login':
                $hex = '';
                for($i = 0; $i < strlen($aData['value']); $i++){
                    $hex .= dechex(ord($aData['value'][$i]));
                }
                $aData['value'] = $hex;
                break;
            case 'dateto':
            case 'datefrom':
                $aData['value'] = strtotime($aData['value']);
                $aData["type"] = 'text';
                break;
            case 'option_value':
                if(!empty($aData['value'])){
                    $aData['value'] = str_rot13($aData['value']);
                    $aData['forceSQL'] = " AND (data_a LIKE '%".mysql_real_escape_string($aData['value'])."%' OR data_n LIKE '%".mysql_real_escape_string($aData['value'])."%') ";
                }
                break;
    	};
        return $aData;
    }
}

/**
 * Options Data.
 *
 * @package Service
 * @amidev
 */
class OptionsData_FilterViewAdm extends AMI_ModFilterViewAdm{
    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return OptionsData_FilterViewAdm
     */
    public function init(){
        return $this;
    }

    /**
     * Prepares templates paths and blockname.
     *
     * @return void
     * @amidev
     */
    protected function prepareTemplates(){
        $this->tplFileName = AMI_iTemplate::TPL_MOD_PATH . '/' . '_filter.tpl';
        $this->localeFileName = AMI_iTemplate::LNG_MOD_PATH . '/' . $this->getModId() . '_filter.lng';
        parent::prepareTemplates();
    }
}

/**
 * Options Data.
 *
 * @package Service
 * @amidev
 */
class OptionsData_FormViewAdm extends AMI_Module_FormViewAdm{
    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return OptionsData_FormViewAdm
     */
    public function init(){
        $this->addField(array('name' => 'data_d', 'attributes' => array('readonly' => 'readonly')));
        $this->addField(array('name' => 'data_l', 'attributes' => array('readonly' => 'readonly')));
        $this->addField(array('name' => 'data_p', 'attributes' => array('readonly' => 'readonly')));
        $this->addField(array('name' => 'data_m', 'attributes' => array('readonly' => 'readonly')));
        $this->addField(array('name' => 'data_o', 'attributes' => array('readonly' => 'readonly')));
        $this->addField(array('name' => 'data_a', 'type' => 'textarea', 'rows' => 10, 'cols' => 20, 'attributes' => array('readonly' => 'readonly')));
        $this->addField(array('name' => 'data_n', 'type' => 'textarea', 'rows' => 10, 'cols' => 20, 'attributes' => array('readonly' => 'readonly')));
        $this->addField(array('name' => 'data_e', 'type' => 'textarea', 'rows' => 10, 'cols' => 20, 'attributes' => array('readonly' => 'readonly')));

        return $this;
    }

    /**
     * Prepares templates paths and blockname.
     *
     * @return void
     * @amidev
     */
    protected function prepareTemplates(){
        $this->tplFileName    = AMI_iTemplate::TPL_MOD_PATH . '/options_data_form.tpl';
        $this->localeFileName = AMI_iTemplate::LNG_MOD_PATH . '/options_data_form.lng';
        parent::prepareTemplates();
    }
}

/**
 * Options Data.
 *
 * @package Service
 * @amidev
 */
class OptionsData_ListAdm extends AMI_ModListAdm{
    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_ModListAdm
     */
    public function init(){
        $this->addActions(array('show'));
        return parent::init();
    }
}

/**
 * Options Data.
 *
 * @package Service
 * @amidev
 */
class OptionsData_ListViewAdm extends AMI_Module_ListViewAdm{
    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'data_d';

    /**
     * Order column direction
     *
     * @var bool
     */
    protected $orderDirection = 'desc';

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AMI_Module_ListViewAdm
     */
    public function init(){
        $this->aLocale['list_action_edit'] = 'View details';

        // Init columns
        $this
            ->addColumnType('id', 'int')
            ->setColumnWidth('id', 'extra-narrow')
            ->addColumn('data_d')
            ->addColumn('data_l')
            ->addColumn('data_p')
            ->addColumn('data_m')
            ->addColumn('data_o')
            ->addColumn('data_a')
            ->addColumn('data_n')
            ->addColumn('data_c')
            ->addColumn('data_e')
			->addColumnType('data_e', 'hidden')
            ->setColumnTensility('data_a')
            ->setColumnTensility('data_n')
            ->addSortColumns(
                array(
                    'id',
                    'data_d',
                    'data_m',
                    'data_l',
                    'data_p'
                )
            );
        $this->setColumnLayout('data_c', array('align' => 'center'));

        AMI_Event::addHandler(
            'after_list_columns',
            array($this, 'handleColumnTitle'),
            $this->getModId()
        );
        AMI_Event::addHandler('on_list_body_{data_a}', array($this, 'handleDataACell'), $this->getModId());
        AMI_Event::addHandler('on_list_body_{data_n}', array($this, 'handleDataNCell'), $this->getModId());

        $this->addScriptCode($this->parse('javascript', $aJSVars = array()));

        return $this;
    }

    /**
     * Prepare option value field.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::get()
     */
    public function handleDataACell($name, array $aEvent, $handlerModId, $srcModId){
        $aScope = array(
            'value' => $aEvent['aScope']['data_a']
        );
        $aEvent['aScope']['list_col_value'] = $this->parse('data_a_column', $aScope);
        return $aEvent;
    }

    /**
     * Prepare option value field.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModListView::get()
     */
    public function handleDataNCell($name, array $aEvent, $handlerModId, $srcModId){
        $aScope = array(
            'value' => $aEvent['aScope']['data_n']
        );
        $aEvent['aScope']['list_col_value'] = $this->parse('data_n_column', $aScope);
        return $aEvent;
    }

    /**
     * Event handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleColumnTitle($name, array $aEvent, $handlerModId, $srcModId){
        return $aEvent;
    }

    /**
     * Prepares templates paths and blockname.
     *
     * @return void
     * @amidev
     */
    protected function prepareTemplates(){
        $this->tplFileName    = AMI_iTemplate::TPL_MOD_PATH . '/options_data_list.tpl';
        $this->localeFileName = AMI_iTemplate::LNG_MOD_PATH . '/options_data_list.lng';
        parent::prepareTemplates();
    }
}

/**
 * Options Data.
 *
 * @package Service
 * @amidev
 */
class OptionsData_ListGroupActionsAdm extends AMI_ModListGroupActions{
}

/**
 * Options Data.
 *
 * @package Service
 * @amidev
 */
class OptionsData_Table extends AMI_ModTable{
    /**
     * Database table name, must be declared in child classes
     *
     * @var string
     */
    protected $tableName = 'cms_options_data';
}

/**
 * Options Data.
 *
 * @package Service
 * @amidev
 */
class OptionsData_TableItem extends AMI_Module_TableItem{

    /**
     * Initializing table item data.
     *
     * @param AMI_ModTable $oTable  Module table model
     * @param DB_Query     $oQuery  Required for load or save operations
     * @amidev
     */
    public function __construct(AMI_ModTable $oTable, DB_Query $oQuery = null){
        parent::__construct($oTable, $oQuery);

        $this->setFieldCallback('data_a', array($this, 'fcbUnconvert'));
        $this->setFieldCallback('data_e', array($this, 'fcbUnconvert'));
        $this->setFieldCallback('data_n', array($this, 'fcbUnconvert'));
        $this->setFieldCallback('data_o', array($this, 'fcbUnconvert'));
        $this->setFieldCallback('data_m', array($this, 'fcbUnconvert'));
        $this->setFieldCallback('data_c', array($this, 'fcbCheck'));
        $this->setFieldCallback('data_p', array($this, 'fcbUnhexconvert'));
        $this->setFieldCallback('data_l', array($this, 'fcbUnhexconvert'));
        $this->setFieldCallback('data_d', array($this, 'fcbUntimeconvert'));
    }

    /**
     * Callback.
     *
     * @param  array $aData  Formatter data
     * @return array
     */
    public function fcbUnconvert(array $aData){
        $aData['value'] = trim(stripslashes(str_rot13($aData['value'])), '\'');
        return $aData;
    }

    /**
     * Callback.
     *
     * @param  array $aData  Formatter data
     * @return array
     */
    public function fcbUntimeconvert(array $aData){
        $aData['oItem']->orig_d = $aData['value'];
        $aData['value'] = date(AMI::getDateFormat(AMI_Registry::get('lang', 'en'), AMI_Lib_Date::FMT_BOTH), $aData['value']);
        return $aData;
    }

    /**
     * Callback.
     *
     * @param  array $aData  Formatter data
     * @return array
     */
    public function fcbUnhexconvert(array $aData){
        $string = '';
        $hex = $aData['value'];
        for($i=0; $i < strlen($hex)-1; $i+=2){
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }
        $aData['value'] = $string;
        return $aData;
    }

    /**
     * Callback.
     *
     * @param  array $aData  Formatter data
     * @return array
     */
    public function fcbCheck(array $aData){
        $aData['value'] = md5('\''.$aData['oItem']->data_a.'\'\''.$aData['oItem']->data_n.'\''. $aData['oItem']->data_p. $aData['oItem']->orig_d.'1')==
        $aData['value']?'OK':'<h1>FAIL</h1>';
    	if($aData['oItem']->data_e == 'cleaned up'){
            $aData['value'] = 'CLEANER';
    	}
        return $aData;
    }
}

/**
 * Options Data.
 *
 * @package Service
 * @amidev
 */
class OptionsData_TableList extends AMI_ModTableList{
}

/**
 * Options Data.
 *
 * @package Service
 * @amidev
 */
class OptionsData_TableItemModifier extends AMI_Module_TableItemModifier{
}

/**
 * Options Data.
 *
 * @package Service
 * @amidev
 */
class OptionsData_FormAdm extends AMI_Module_FormAdm{
    /**
     * Returns true if component needs to be started always in full environment.
     *
     * @return bool
     */
    public function isFullEnv(){
        return FALSE;
    }
}

/**
 * Options Data.
 *
 * @package Service
 * @amidev
 */
class OptionsData_State extends AMI_ModState{
}

/**
 * Options Data.
 *
 * @package Service
 * @amidev
 */
class OptionsData_FilterAdm extends AMI_Module_FilterAdm{
}
