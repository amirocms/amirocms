<?php
/**
 * AmiEshopShipping/Types configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiEshopShipping_Types
 * @version   $Id: AmiEshopShipping_Types_Adm.php 48619 2014-03-12 08:05:46Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiEshopShipping/Types configuration admin action controller.
 *
 * @package    Config_AmiEshopShipping_Types
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Types_Adm extends Hyper_AmiEshopShipping_Adm{
    /**
     * Constructor.
     *
     * @param AMI_Request  $oRequest   Request object
     * @param AMI_Response $oResponse  Response object
     */
    public function __construct(AMI_Request $oRequest, AMI_Response $oResponse){
        $selectPopup = AMI::getSingleton('env/request')->get('item_id', null);
        if(isset($selectPopup)){
            $this->aDefaultComponents = array('filter', 'list');
        }

        parent::__construct($oRequest, $oResponse);
    }
}

/**
 * AmiEshopShipping/Types configuration model.
 *
 * @package    Config_AmiEshopShipping_Types
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Types_State extends Hyper_AmiEshopShipping_State{
}

/**
 * AmiEshopShipping/Types configuration admin filter component action controller.
 *
 * @package    Config_AmiEshopShipping_Types
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Types_FilterAdm extends Hyper_AmiEshopShipping_FilterAdm{
}

/**
 * AmiEshopShipping/Types configuration item list component filter model.
 *
 * @package    Config_AmiEshopShipping_Types
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Types_FilterModelAdm extends Hyper_AmiEshopShipping_FilterModelAdm{
    /**
     * Common filter fields
     *
     * @var   array
     */
    protected $aCommonFields = array('header');

    /**
     * Constructor.
     */
    public function __construct(){
        parent::__construct();

        $oShippingMethodsList = AMI::getResourceModel('eshop_shipping_methods/table')->getList();
        $oShippingMethodsList
            ->addColumns(array('id', 'header'))
            ->addWhereDef('AND id_parent = 0 AND hidden = 0')
            ->addWhereDef(DB_Query::getSnippet('AND lang = %s')->q(AMI_Registry::get('lang')))
            ->addOrder('header', ' asc')
            ->load();
        $aData = array();
        foreach($oShippingMethodsList as $oShippingMethodModelItem){
            $aData[] = array(
                'name'    => $oShippingMethodModelItem->header,
                'value'   => $oShippingMethodModelItem->id
            );
        }
        $this->addViewField(
            array(
                'name'          => 'id_method',
                'type'          => 'select',
                'flt_condition' => '=',
                'not_selected'  => array('id' => '', 'caption' => 'flt_all'),
                'data'          => $aData
            )
        );
    }

    /**
     * Adds current user id as id_owner.
     *
     * @param string $field  Field name
     * @param array $aData  Filter data
     * @return array
     * @amidev Temporary
     */
    protected function processFieldData($field, array $aData){
        if($field == 'id_method'){
            if($aData['value'] == ''){
                $aData['skip'] = true;
            }else{
                $aData['forceSQL'] = " AND tm2.id_method = ".intval($aData['value']);
            }
        }
        return $aData;
    }
}

/**
 * AmiEshopShipping/Types configuration admin filter component view.
 *
 * @package    Config_AmiEshopShipping_Types
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Types_FilterViewAdm extends Hyper_AmiEshopShipping_FilterViewAdm{
}

/**
 * AmiEshopShipping/Types configuration admin form component action controller.
 *
 * @package    Config_AmiEshopShipping_Types
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Types_FormAdm extends Hyper_AmiEshopShipping_FormAdm{
}

/**
 * AmiEshopShipping/Types configuration form component view.
 *
 * @package    Config_AmiEshopShipping_Types
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Types_FormViewAdm extends Hyper_AmiEshopShipping_FormViewAdm{
    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        return parent::init();
    }
}

/**
 * AmiEshopShipping/Types configuration admin list component action controller.
 *
 * @package    Config_AmiEshopShipping_Types
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Types_ListAdm extends Hyper_AmiEshopShipping_ListAdm{
    /**
     * List actions controller resource id
     *
     * @var string
     */
    protected $listActionsResId = 'eshop_shipping_types/list_actions/controller/adm';

    /**
     * Initialization.
     *
     * @return AmiSubscribe_Topic_ListAdm
     */
    public function init(){
        $selectPopup = AMI::getSingleton('env/request')->get('item_id', null);

        $this->addJoinedColumns(array('methods_count'), 'tm');

        parent::init();
        AMI_Event::addHandler('on_query_add_table', array($this, 'handleAddTable'), $this->getModId());

        $this->dropActions(self::ACTION_GROUP);
        $this->dropActions(AMI_ModListAdm::ACTION_COLUMN, array('public'));

        if(isset($selectPopup)){
            $this->dropActions(AMI_ModListAdm::ACTION_COMMON);
            $this->addActions(array(self::REQUIRE_FULL_ENV . 'active'));
        }

        return $this;
    }

    /**
     * Excluding hidden shipping types.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModTable::getQueryBase()
     */
    public function handleAddTable($name, array $aEvent, $handlerModId, $srcModId){
        $aEvent['oQuery']->addWhereDef(DB_Query::getSnippet('AND i.`hidden` = 0'));
        $aEvent['oQuery']->addGrouping('i.id');
        return $aEvent;
    }
}

/**
 * AmiEshopShipping/Types configuration admin list component view.
 *
 * @package    Config_AmiEshopShipping_Types
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Types_ListViewAdm extends Hyper_AmiEshopShipping_ListViewAdm{
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
     * List default elemnts template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#list_header',
            '#columns', 'header', 'tm_methods_count', 'categories_count', 'columns',
            '#actions', 'edit', 'delete', 'actions',
        'list_header'
    );

    /**
     * Array of categories counts
     *
     * @var array
     */
    protected $aCatCounts = array();

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiEshopShipping_Types_ListViewAdm
     */
    public function init(){
        parent::init();

        $oDB = AMI::getSingleton('db');
        $sql = 'SELECT t.id, COUNT(c.id_shipping_type) categories_count FROM `cms_es_shipping_types` t LEFT OUTER JOIN `cms_es_cats` c ON c.id_shipping_type = t.id WHERE t.lang = %s AND t.hidden = 0 GROUP BY t.id';
        $oRS = $oDB->select(DB_Query::getSnippet($sql)->q(AMI_Registry::get('lang')));
        foreach($oRS as $aRecord){
            $this->aCatCounts[$aRecord['id']] = $aRecord['categories_count'];
        }

        // Init columns
        $this
            ->removeColumn('public')
            ->removeColumn('date_created')
            ->removeColumn('announce')
            ->removeColumn('position')
            ->addColumnType('tm_methods_count', 'int')
            ->addColumnType('categories_count', 'int')
            ->formatColumn('categories_count', array($this, 'fmtCatCounts'))
            ->addSortColumns(array('tm_methods_count'));

        $this->addScriptCode($this->getTemplate()->parse($this->tplBlockName . ':javascript'));

        return $this;
    }

    /**
     * Categories counts column formatter.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return string
     * @see    AMI_ModListView::formatColumn()
     */
    protected function fmtCatCounts($value, array $aArgs){
        return isset($this->aCatCounts[$aArgs['aScope']['id']]) ? $this->aCatCounts[$aArgs['aScope']['id']] : 0;
    }
}

/**
 * AmiEshopShipping/Types configuration module admin list actions controller.
 *
 * @package    Config_AmiEshopShipping_Types
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Types_ListActionsAdm extends Hyper_AmiEshopShipping_ListActionsAdm{
}

/**
 * AmiEshopShipping/Types configuration module admin list group actions controller.
 *
 * @package    Config_AmiEshopShipping_Types
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Types_ListGroupActionsAdm extends Hyper_AmiEshopShipping_ListGroupActionsAdm{
}
