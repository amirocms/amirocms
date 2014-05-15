<?php
/**
 * AmiClean/Webservice configuration.
 *
 * @copyright Amiro.CMS. All rights reserved.
 * @category  Config
 * @package   Config_AmiClean_Webservice
 */

/**
 * AmiClean/Webservice front action controller.
 *
 * @package    Config_AmiClean_Webservice
 * @subpackage Controller
 */
class AmiClean_Webservice_Frn extends Hyper_AmiClean_Adm{
    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request
     * @param AMI_Response $oResponse  Response
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        parent::__construct($oRequest, $oResponse);

        $this->addComponents(array('filter', 'list'));
    }
}

/**
 * AmiClean/Webservice front filter component action controller.
 *
 * @package    Config_AmiClean_Webservice
 * @subpackage Controller
 */
class AmiClean_Webservice_FilterFrn extends Hyper_AmiClean_FilterAdm{
    /**
     * Initialization.
     *
     * @return AMI_Module_FilterAdm
     */
    public function init(){
    	AMI_Event::addHandler('on_filter_init', array($this, 'handleFilterInit'), $this->getModId());
        parent::init();
        return $this;
    }

    /**
     * Add page_id/sticky filter values.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleFilterInit($name, array $aEvent, $handlerModId, $srcModId){
        $modId = AMI_Registry::get('modId');
        if(
            AMI::issetAndTrueOption('core', 'multi_page_allowed') &&
            (
                AMI::issetAndTrueOption($modId, 'multi_page') &&
                !AMI::issetAndTrueOption($modId, 'use_categories')
            )
        ){
            $aModulePages = array(
                array(
                    'value' => 0,
                    'caption' => 'common_items'
                )
            );
            $aPages = AMI_PageManager::getModPages($modId, AMI_Registry::get('lang_data'));
            foreach($aPages as $aPage){
                $aModulePages[] = array(
                    'name'  => $aPage['name'],
                    'value' => $aPage['id']
                );
            }
            $aEvent['oFilter']->addViewField(
                array(
                    'name'          => 'id_page',
                    'type'          => 'select',
                    'flt_type'      => 'select',
                    'flt_default'   => '-1',
                    'flt_condition' => '=',
                    'flt_column'    => 'id_page',
                    'data'          => $aModulePages,
                    'not_selected'  => array('id' => '-1', 'caption' => 'all_pages'),
                    'act_as_int'    => true,
                    'session_field' => true
                )
            );
        }

        if(AMI::issetAndTrueProperty($modId, 'use_special_list_view')){
            $aEvent['oFilter']->addViewField(
                array(
                    'name'          => 'sticky',
                    'type'          => 'checkbox',
                    'flt_default'   => '0',
                    'flt_condition' => '=',
                    'flt_column'    => 'sticky',
                    'act_as_int'    => TRUE,
                    'disable_empty' => TRUE
                )
            );
            $aEvent['oFilter']->addViewField(
                array(
                    'name'          => 'public_direct_link',
                    'type'          => 'checkbox',
                    'flt_default'   => '0',
                    'flt_condition' => '=',
                    'flt_column'    => 'hide_in_list',
                    'act_as_int'    => TRUE,
                    'disable_empty' => TRUE
                )
            );
        }
        $aEvent['oFilter']->addViewField(
            array(
                'name'          => 'lang',
                'type'          => 'hidden',
                'flt_default'   => AMI_Registry::get('lang_data'),
                'flt_condition' => '='
            )
        );

        return $aEvent;
    }
}

/**
 * AmiClean/Webservice item list component filter model.
 *
 * @package    Config_AmiClean_Webservice
 * @subpackage Model
 */
class AmiClean_Webservice_FilterModelFrn extends Hyper_AmiClean_FilterModelAdm{
    /**
     * Handle id_page filter custom logic.
     *
     * @param  string $field  Field name
     * @param  array  $aData  Filter data
     * @return array
     */
    protected function processFieldData($field, array $aData){
        if($field == 'id_page'){
            if($aData['value'] > 0){
                $aData['exception'] = " OR i.id_page = 0 ";
            }
        }
        return $aData;
    }
}

/**
 * AmiClean/Webservice front filter component view.
 *
 * @package    Config_AmiClean_Webservice
 * @subpackage View
 */
class AmiClean_Webservice_FilterViewFrn extends AMI_ViewEmpty{ // Hyper_AmiClean_FilterViewAdm
}

/**
 * AmiClean/Webservice front list component action controller.
 *
 * @package    Config_AmiClean_Webservice
 * @subpackage Controller
 */
class AmiClean_Webservice_ListFrn extends Hyper_AmiClean_ListAdm{
    /**
     * Initialization.
     *
     * @return AMI_ModListAdm
     */
    public function init(){
        parent::init();

        AMI_Event::addHandler('on_image_link_generation', array($this, 'handleImageLink'), $this->getModId());
        AMI_Event::addHandler('on_list_load', array($this, 'handleListLoad'), $this->getModId());

        return $this;
    }

    /**
     * Initializes model.
     *
     * @return AMI_ModTable|null
     */
    protected function initModel(){
        $modId = AMI_Registry::get('modId');

        AMI::addResource($this->getModId() . '/table/model', AMI::getResourceClass($modId . '/table/model'));
        AMI::addResource($this->getModId() . '/table/model/item', AMI::getResourceClass($modId . '/table/model/item'));
        AMI::addResource($this->getModId() . '/table/model/list', AMI::getResourceClass($modId . '/table/model/list'));

        $aExtOption = AMI::getOption($modId, 'extensions');
        AMI::setOption($this->getModId(), 'extensions', $aExtOption);

        $hasCat = AMI::issetAndTrueOption($modId, 'use_categories');
        AMI::setOption($this->getModId(), 'use_categories', $hasCat);

        if(in_array($modId, array('eshop_item', 'kb_item', 'portfolio_item'))){
             $catModId = mb_substr($modId, 0, -5) . '_cat';
        }else{
             $catModId = $modId . '_cat';
        }

        if($hasCat){
            AMI::addResource($this->getModId() . '_cat/table/model', AMI::getResourceClass($catModId . '/table/model'));
            AMI::addResource($this->getModId() . '_cat/table/model/item', AMI::getResourceClass($catModId . '/table/model/item'));
            AMI::addResource($this->getModId() . '_cat/table/model/list', AMI::getResourceClass($catModId . '/table/model/list'));
        }

        AMI_ModDeclarator::getInstance()->setAttr($this->getModId(), 'data_source', $modId);
        if($hasCat){
            AMI_ModDeclarator::getInstance()->setAttr($this->getModId() . '_cat', 'data_source', $catModId);
        }

        AMI::cleanupModExtensions($this->getModId());
        AMI::initModExtensions($this->getModId(), $modId);

        $oModel =
            $this->useModel
                ? AMI::getResourceModel(
                    $this->getModId() . '/table'
                )
                : NULL;

        if(!is_null($oModel)){
            $oModel->addFieldsRemap(array());
        }

        $oModel->setModId($this->getModId());
        return $oModel;
    }

    /**
     * Updates generated image link modId.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleImageLink($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['modId'] = AMI_Registry::get('modId');
        return $aEvent;
    }

    /**
     * Adds cat header column.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleListLoad($name, array $aEvent, $handlerModId, $srcModId){
        $oModel = $aEvent['oList']->getTable();
        if(in_array('cat', $oModel->getActiveDependenceAliases())){
            $aEvent['oList']->addColumn('header', 'cat');
            if(in_array('id_page', $oModel->getAvailableFields())){
                $aEvent['oList']->addColumn('lang', 'cat');
                $aEvent['oList']->addColumn('id_page', 'cat');
                $aEvent['oList']->addColumn('sublink', 'cat');
            }
        }
        $oRequest = AMI::getSingleton('env/request');

        if($oRequest->get('sortDir', '') == 'rand'){
            $aEvent['oQuery']->setOrder('RAND()', '');
        }
        return $aEvent;
    }
}

/**
 * AmiClean/Webservice front list component view.
 *
 * @package    Config_AmiClean_Webservice
 * @subpackage View
 */
class AmiClean_Webservice_ListViewFrn extends Hyper_AmiClean_ListViewAdm{

   /**
     * Order column
     *
     * @var string
     */
    protected $orderColumn = 'id';

    /**
     * Order column direction
     *
     * @var bool
     */
    protected $orderDirection = 'asc';

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return this
     */
    public function init(){
        $this->addColumnType('id', 'hidden');

        $oRequest = AMI::getSingleton('env/request');
        $fields = $oRequest->get('fields', FALSE);
        if($fields === FALSE){
            $aFields = $this->oModel->getAvailableFields();
        }else{
            $aFields = json_decode($fields, TRUE);
        }
        $aSafeFields = AMI_Registry::get(AMI_Registry::get('modId') . '/webservice/safeFields', array('id'));
        $aFields = array_intersect($aFields, $aSafeFields);
        foreach($aFields as $field){
            $this->addColumnType($field, 'hidden');
            if($field == 'date_created'){
                $this->addColumnType('fdate', 'hidden');
                $this->addColumnType('ftime', 'hidden');
            }
        }

        $hasCat = AMI::issetAndTrueOption($this->getModId(), 'use_categories');
        if($hasCat){
            $this->addColumn('cat_header');
        }
        $this->addColumn('url');
        AMI_Event::addHandler('on_list_body_row', array($this, 'handleListBodyRow'), $this->getModId());

        return parent::init();
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
        AMI::initModExtensions(AMI_Registry::get('modId'));
        $oModel = AMI::getResourceModel(AMI_Registry::get('modId') . '/table');
        AMI_Registry::set('lang', $aEvent['aScope']['lang']);
        $id = $aEvent['aScope']['id'];
        $aEvent['aScope']['url'] = '';
        if(isset($aEvent['aScope']['date_created'])){
            $aEvent['aScope']['fdate'] = AMI_Lib_Date::formatDateTime($aEvent['aScope']['date_created'], AMI_Lib_Date::FMT_DATE);
            $aEvent['aScope']['ftime'] = AMI_Lib_Date::formatDateTime($aEvent['aScope']['date_created'], AMI_Lib_Date::FMT_TIME);

        }
        if(in_array('id_page', $oModel->getAvailableFields()) && in_array('sublink', $oModel->getAvailableFields())){
            $oItem = $oModel->getItem();
            $oItem->suppressModPageError();
            $oItem->setData($aEvent['aScope']);
            $aEvent['aScope']['url'] = AMI_Registry::get('path/www_root', '') . $oItem->getFullURL();
        }
        return $aEvent;
    }
}
