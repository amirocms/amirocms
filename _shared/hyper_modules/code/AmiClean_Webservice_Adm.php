<?php
/**
 * AmiClean/Webservice configuration.
 *
 * @copyright Amiro.CMS. All rights reserved.
 * @category  Config
 * @package   Config_AmiClean_Webservice
 */

/**
 * AmiClean/Webservice admin action controller.
 *
 * @package    Config_AmiClean_Webservice
 * @subpackage Controller
 */
class AmiClean_Webservice_Adm extends Hyper_AmiClean_Adm{
    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request
     * @param AMI_Response $oResponse  Response
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        parent::__construct($oRequest, $oResponse);
        $this->addComponents(
            array('filter', 'list', 'form', ) // 'filter', 'list', 'form' (order matters)
        );
    }
}

/**
 * AmiClean/Webservice model.
 *
 * @package    Config_AmiClean_Webservice
 * @subpackage Model
 */
class AmiClean_Webservice_State extends Hyper_AmiClean_State{
}

/**
 * AmiClean/Webservice admin filter component action controller.
 *
 * @package    Config_AmiClean_Webservice
 * @subpackage Controller
 */
class AmiClean_Webservice_FilterAdm extends Hyper_AmiClean_FilterAdm{
}

/**
 * AmiClean/Webservice item list component filter model.
 *
 * @package    Config_AmiClean_Webservice
 * @subpackage Model
 */
class AmiClean_Webservice_FilterModelAdm extends Hyper_AmiClean_FilterModelAdm{
    /**
     * Constructor.
     */
    public function __construct(){
        $this->addViewField(
            array(
                'name'          => 'header',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'header'
            )
        );
        $this->addViewField(
            array(
                'name'          => 'api_key',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'api_key'
            )
        );
    }
}

/**
 * AmiClean/Webservice admin filter component view.
 *
 * @package    Config_AmiClean_Webservice
 * @subpackage View
 */
class AmiClean_Webservice_FilterViewAdm extends Hyper_AmiClean_FilterViewAdm{
}

/**
 * AmiClean/Webservice admin form component action controller.
 *
 * @package    Config_AmiClean_Webservice
 * @subpackage Controller
 */
class AmiClean_Webservice_FormAdm extends Hyper_AmiClean_FormAdm{
}

/**
 * AmiClean/Webservice form component view.
 *
 * @package    Config_AmiClean_Webservice
 * @subpackage View
 */
class AmiClean_Webservice_FormViewAdm extends Hyper_AmiClean_FormViewAdm{
    /**
     * Fields init.
     *
     */
    public function init(){
        $this->aPlaceholders = array(
            '#form',
                'id', 'mod_action',
                'active', 'header', 'api_key', 'id_user', 'modules', 'announce',
            'form'
        );
        $this
            ->addField(array('name' => 'id', 'type' => 'hidden'))
            ->addField(array('name' => 'mod_action', 'value' => 'form_save', 'type' => 'hidden'))
            ->addField(array('name' => 'active', 'type' => 'checkbox', 'default_checked' => true))
            ->addField(array('name' => 'id_user', 'validate' => array('filled', 'required')))
            ->addField(array('name' => 'header', 'validate' => array('filled', 'required')))
            ->addField(array('name' => 'announce'));
        // Adds prefilled API Key field for new form mode
        $aModules = $this->getRegisteredModules();
        $aCaptions = AMI_Service_Adm::getModulesCaptions($aModules, true, array('modules', 'eshop', 'kb', 'portfolio', 'services', 'plugins'), true);
        $aData = array();
        foreach($aModules as $module){
            $aData[] = array(
                'name' => isset($aCaptions[$module]) ? $aCaptions[$module] : '[' . $module . ']',
                'value' => $module
            );
        }
        $this->addField(array('name' => 'modules', 'type' => 'select', 'multiple' => true, 'data' => $aData));
        return parent::init();
    }

    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        if(!$this->oItem->getId()){
            $apiKey = strtoupper(substr(md5(time()), 0, 31));
            $apiKey[10] = '-';
            $apiKey[21] = '-';
            $this->addField(array('name' => 'api_key', 'value' => $apiKey));
        }else{
            if($this->oItem->is_sys){
                $this->dropField('id_user')
                    ->dropField('header')
                    ->dropField('announce')
                    ->addField(array('name' => 'header', 'type' => 'static'))
                    ->addField(array('name' => 'announce', 'type' => 'static'));                
            }else{
                $oUser = AMI::getResourceModel('users/table')->find($this->oItem->id_user);
                $this->addField(array('name' => 'api_key', 'validate' => array('filled', 'required'), 'position' => 'id_user.after'));
                $this->addScriptCode("AMI.$('#audit_owner_name').val('" . $oUser->login . "');");
            }
        }
        return parent::get();
    }

    /**
     * Returns list of modules.
     *
     * @return array
     */
    private function getRegisteredModules(){
        $aExcludeModules = array('ami_webservice', 'templates', 'data_import', 'ami_seopult');
        $oDeclartor = AMI_ModDeclarator::getInstance();
        $aRegistered = $oDeclartor->getRegistered();
        foreach ($aRegistered as $index => $modId){
            list($hyper,) = $oDeclartor->getHyperData($modId);
            if(in_array($hyper, array('ami_clean', 'ami_multifeeds', 'ami_multifeeds5', 'ami_users', 'ami_catalog'))){
                if(
                    !in_array($modId, $aExcludeModules) &&
                    ((strpos($modId, 'eshop_') !== 0) || ($modId == 'eshop_item') || ($modId == 'eshop_order')) &&
                    ((strpos($modId, 'kb_') !== 0) || ($modId == 'kb_item')) &&
                    ((strpos($modId, 'portfolio_') !== 0) || ($modId == 'portfolio_item'))
                ){
                    try{
                        // For those who has service class
                        $oService = AMI::getResource($modId . '/service');
                        if(method_exists($oService, 'addWebserviceHandlers')){
                            $aResult[] = $modId;
                        }
                        continue;
                    }catch(Exception $e){}
                    if(in_array($hyper, array('ami_multifeeds', 'ami_multifeeds5'))){
                        try{
                            // For those who has model
                            AMI::getResourceModel($modId . '/table');
                            $aResult[] = $modId;
                        }catch(Exception $e){}
                    }
                }
            }
        }
        return $aResult;
    }
}

/**
 * AmiClean/Webservice admin list component action controller.
 *
 * @package    Config_AmiClean_Webservice
 * @subpackage Controller
 */
class AmiClean_Webservice_ListAdm extends Hyper_AmiClean_ListAdm{
    /**
     * Initialization.
     *
     * @return AmiClean_Webservice_ListAdm
     */
    public function init(){
        $this
            ->addActions(
                array(
                    self::REQUIRE_FULL_ENV . 'edit',
                    self::REQUIRE_FULL_ENV . 'delete'
                )
            )
            ->addColActions(array(self::REQUIRE_FULL_ENV . 'active'), TRUE);

        $this->listActionsResId = $this->getModId() . '/list_actions/controller/adm';

        return parent::init();
    }

    /**
     * Returns true if component needs to be started always in full environment.
     *
     * @return bool
     */
    public function isFullEnv(){
        return TRUE;
    } 
}

/**
 * AmiClean/Webservice admin list component view.
 *
 * @package    Config_AmiClean_Webservice
 * @subpackage View
 */
class AmiClean_Webservice_ListViewAdm extends Hyper_AmiClean_ListViewAdm{

    /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'header';

    /**
     * Order column direction
     *
     * @var bool
     */
    protected $orderDirection = 'asc';

    /**
     * Constructor.
     */
    public function __construct(){

        $oTable = AMI::getResourceModel($this->getModId() . '/table');
        $oItem = 
            $oTable
                ->getItem()
                ->addFields(array('id'))
                ->addSearchCondition(
                    array(
                        'is_sys'  => 1,
                        'id_user' => 0,
                        'api_key' => ''
                    )
                )
                ->load();
        if(!$oItem->getId()){
            $oItem = $oTable->add(
                array(
                    'active'   => true,
                    'id_user'  => 0,
                    'api_key'  => '',
                    'header'   => 'App. Public Token',
                    'announce' => 'Use "' . AmiClean_Webservice_Service::PUBLIC_TOKEN . '" application token for webservice public access',
                    'is_sys'   => 1,
                    'modules'  => ''
                )
            );
            $oItem->save();
        }

        parent::__construct();

        // Init columns
        $this
            ->addColumnType('id', 'hidden')
            ->addColumnType('is_sys', 'hidden')
            ->addColumn('active')
            ->setColumnWidth('active', 'extra-narrow')
            ->setColumnAlign('active', 'center')
            ->addColumn('header')
            ->setColumnWidth('header', 'extra-wide')
            ->addColumnType('announce', 'hidden')
            ->addColumnType('id_user', 'mediumtext')
            ->formatColumn('id_user', array($this, 'fmtUser'))
            ->addColumn('api_key')
            ->setColumnWidth('api_key', 'extra-wide')
            ->addColumnType('modules', 'int')
            ->formatColumn('modules', array($this, 'fmtModules'))
            ->setColumnTensility('header')
            ->addSortColumns(array('header'));

        AMI_Event::addHandler('on_list_body_row', array($this, 'handleListBodyRow'), $this->getModId());
        AMI_Event::addHandler('on_list_load', array($this, 'handleListLoad'), $this->getModId());
    }

    /**
     * Handles list load.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListLoad($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['oList']->getQuery()->prependOrder('is_sys', 'desc');
        return $aEvent;
    }

    /**
     * Handles row cells.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListBodyRow($name, array $aEvent, $handlerModId, $srcModId){

        $aTplData = array(
            'header' => $aEvent['aScope']['header'],
            'announce' => $aEvent['aScope']['announce']
        );
        $aEvent['aScope']['header'] = $this->parse('header_column', $aTplData);
        $isSys = $aEvent['aScope']['is_sys'];
        if($isSys){
            unset($aEvent['aScope']["_action_col"]['delete']);
            unset($aEvent['aScope']['_actions'][array_search('delete', $aEvent['aScope']['_actions'])]);
        }
        return $aEvent;
    }

    /**
     * User formatter.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtUser($value, array $aArgs){
        $oUserModel = AMI::getResourceModel('users/table');
        $oUser = $oUserModel->find($value, array('id', 'login'));
        if($oUser->getId()){
            $value = $oUser->login;
        }else{
            if($value){
                $value = $this->parse('deleted_user', array('id' => $value));
            }else{
                $value = $this->parse('system');
            }
        }
        return $value;
    }

    /**
     * Modules column formatter.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtModules($value, array $aArgs){
        if(!is_null($value) && strlen($value)){
            $aVal = explode(';', $value);
            if(is_array($aVal)){
                return count($aVal) - 1;
            }
        }
        return 0;
    }
}

/**
 * AmiClean/Webservice admin list actions controller.
 *
 * @package    Config_AmiClean_Webservice
 * @subpackage Controller
 */
class AmiClean_Webservice_ListActionsAdm extends Hyper_AmiClean_ListActionsAdm{
    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     * @see    AMI_ModListAdm::addActions()
     * @see    AMI_ModListAdm::addColActions()
     */

    /**
     * Dispatches 'active' action.
     *
     * Activates user.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchActive($name, array $aEvent, $handlerModId, $srcModId){
        $this->changeItemFlag($this->getRequestId(), 'public', 1);
        $aEvent['oResponse']->addStatusMessage('status_activate');
        $this->refreshView();

        return $aEvent;
    }

    /**
     * Dispatches 'unactive' action.
     *
     * Disactivates user.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function dispatchUnactive($name, array $aEvent, $handlerModId, $srcModId){
        $this->changeItemFlag($this->getRequestId(), 'public', 1);
        $aEvent['oResponse']->addStatusMessage('status_activate');
        $this->refreshView();

        return $aEvent;
    }

    /**#@-*/
}

/**
 * AmiClean/Webservice admin list group actions controller.
 *
 * @package    Config_AmiClean_Webservice
 * @subpackage Controller
 */
class AmiClean_Webservice_ListGroupActionsAdm extends Hyper_AmiClean_ListGroupActionsAdm{
}
