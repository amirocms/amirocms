<?php
/**
 * AmiEshopShipping/Fields configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiEshopShipping_Fields
 * @version   $Id: AmiEshopShipping_Fields_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiEshopShipping/Fields configuration admin action controller.
 *
 * @package    Config_AmiEshopShipping_Fields
 * @subpackage Controller
 * @resource   {$modId}/module/controller/adm <code>AMI::getResource('{$modId}/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Fields_Adm extends Hyper_AmiEshopShipping_Adm{
    /**
     * Returns system fields id's.
     *
     * @return array
     */
    public function getSystemFields(){
        $aSystemIDs = array();
        $oQuery = new DB_Query('cms_es_shipping_fields');
        $oQuery->addFields(array('id'));
        $oQuery->addWhereDef('AND is_sys = 1');
        $oShippingFieldsRS = AMI::getSingleton('db')->select($oQuery);
        if($oShippingFieldsRS->count()){
            foreach($oShippingFieldsRS as $aRow){
                $aSystemIDs[] = $aRow['id'];
            }
        }
        return $aSystemIDs;
    }
}

/**
 * AmiEshopShipping/Fields configuration model.
 *
 * @package    Config_AmiEshopShipping_Fields
 * @subpackage Model
 * @resource   {$modId}/module/model <code>AMI::getResourceModel('{$modId}/module')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Fields_State extends Hyper_AmiEshopShipping_State{
}

/**
 * AmiEshopShipping/Fields configuration admin filter component action controller.
 *
 * @package    Config_AmiEshopShipping_Fields
 * @subpackage Controller
 * @resource   {$modId}/filter/controller/adm <code>AMI::getResource('{$modId}/filter/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Fields_FilterAdm extends Hyper_AmiEshopShipping_FilterAdm{
}

/**
 * AmiEshopShipping/Fields configuration item list component filter model.
 *
 * @package    Config_AmiEshopShipping_Fields
 * @subpackage Model
 * @resource   {$modId}/filter/model/adm <code>AMI::getResource('{$modId}/filter/model/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Fields_FilterModelAdm extends Hyper_AmiEshopShipping_FilterModelAdm{
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

        $this->addViewField(
            array(
                'name'          => 'postfix',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'postfix',
            )
        );
    }
}

/**
 * AmiEshopShipping/Fields configuration admin filter component view.
 *
 * @package    Config_AmiEshopShipping_Fields
 * @subpackage View
 * @resource   {$modId}/filter/view/adm <code>AMI::getResource('{$modId}/filter/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Fields_FilterViewAdm extends Hyper_AmiEshopShipping_FilterViewAdm{
}

/**
 * AmiEshopShipping/Fields configuration admin form component action controller.
 *
 * @package    Config_AmiEshopShipping_Fields
 * @subpackage Controller
 * @resource   {$modId}/form/controller/adm <code>AMI::getResource('{$modId}/form/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Fields_FormAdm extends Hyper_AmiEshopShipping_FormAdm{
    /**
     * Initialization.
     *
     * @return Hyper_AmiModulesTemplates_FormAdm
     * @amidev Temporary
     */
    public function init(){
        parent::init();
        AMI_Event::addHandler('on_after_save_model_item', array($this, 'handleAfterSaveModelItem'), $this->getModId());
        return $this;
    }

    /**
     * Succesful save model item handler.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleAfterSaveModelItem($name, array $aEvent, $handlerModId, $srcModId){
        if($aEvent['onCreate'] && $aEvent['success']){
            // create set for new shipping fields
            $idTemplate = (int)AMI::getSingleton('db')->fetchValue(
                DB_Query::getSnippet(
                    'SELECT id FROM cms_modules_templates WHERE name=%s ORDER BY id ASC LIMIT 1'
                )
                ->q('eshop_shipping_fields.tpl')
            );

            if($idTemplate){
                $oModuleTemplatesModelItem = AMI::getResourceModel('modules_templates/table')->find($idTemplate, array('id', 'content', 'parsed'))->load();
                if($oModuleTemplatesModelItem->id){
                    preg_match("/<!--#set\s+var\s*=\s*\"custom\_shipping\_system\_default\"\s+value\s*=\s*\"(.*?)\"\\s*-->/si", $oModuleTemplatesModelItem->content, $aMatches);
                    if(empty($aMatches[1])){
                        $aMatches[1] = '';
                    }

                    $oModuleTemplatesModelItem->content = $oModuleTemplatesModelItem->content . "\r\n" . '<!--#set var="custom_shipping_'.$aEvent['aData']['postfix'].'" value="' . $aMatches[1] . '"-->' . "\r\n";
                    $oModuleTemplatesModelItem->parsed = '';
                    $oModuleTemplatesModelItem->save();
                }
            }
        }
        return $aEvent;
    }
}

/**
 * AmiEshopShipping/Fields configuration form component view.
 *
 * @package    Config_AmiEshopShipping_Fields
 * @subpackage View
 * @resource   {$modId}/form/view/adm <code>AMI::getResource('{$modId}/form/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Fields_FormViewAdm extends Hyper_AmiEshopShipping_FormViewAdm{
    /**
     * Common fields validators
     *
     * @var array
     */
    protected $aCommonFieldsValidators = array();

    /**
     * Reset form default elements template
     *
     * @var array
     */
    protected $aPlaceholders = array('#form', 'form');

    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        $this->addField(array('name' => 'id', 'type' => 'hidden'));
        $this->addField(array('name' => 'mod_id', 'value' => $this->getModId(), 'type' => 'hidden'));
        $this->addField(array('name' => 'mod_action', 'value' => 'form_save', 'type' => 'hidden'));
        $this->addField(array('name' => 'public', 'type' => 'checkbox', 'default_checked' => true));
        $this->addField(array('name' => 'ami_full', 'value' => 1, 'type' => 'hidden'));
        $this->addField(array('name' => 'header'));

        $oRequest = AMI::getSingleton('env/request');
        $itemId = $oRequest->get('id', null);

        $this->addField(array('name' => 'postfix', 'attributes' => $itemId ? array('disabled' => 'disabled') : array(), 'validate' => array('alphanum', 'stop_on_error')));

        $this->addField(array('name' => 'postfix_hint', 'type' => 'hint'));

        $this->addField(array('name' => 'btn_template', 'type' => 'popup_button'));
        $this->addField(array('name' => 'btn_locale', 'type' => 'popup_button'));
        $this->addField(array('name' => 'btn_locale_adm', 'type' => 'popup_button'));

        if(!$itemId){
            $this->addField(array('name' => 'buttons_hint', 'type' => 'hint'));
        }

        $this->addScriptCode($this->getTemplate()->parse($this->tplBlockName . ':javascript'));

        AMI_Event::addHandler('on_form_field_{btn_template}', array($this, 'handleTemplateBtnFormField'), $this->getModId());
        AMI_Event::addHandler('on_form_field_{btn_locale}', array($this, 'handleTemplateLangBtnFormField'), $this->getModId());
        AMI_Event::addHandler('on_form_field_{btn_locale_adm}', array($this, 'handleTemplateLangAdmBtnFormField'), $this->getModId());

        return $this;
    }

    /**
     * Handle 'Edit template' button form field.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleTemplateBtnFormField($name, array $aEvent, $handlerModId, $srcModId){
        $oRequest = AMI::getSingleton('env/request');
        $aEvent['aScope']['btn_type'] = 'tpl';
        $aEvent['aScope']['disabled'] = $oRequest->get('id', null) ? '0' : '1';
        $aEvent['aScope']['template_info'] = 'custom_shipping_'.$aEvent['oItem']->postfix;
        $aEvent['aScope']['popup_title'] = $this->aLocale['popup_title_templates'];
        $aEvent['aScope']['popup_module'] = 'modules_templates';
        $aEvent['aScope']['template_id'] = $this->getTemplateId('cms_modules_templates', 'eshop_shipping_fields.tpl', 'templates/');
        return $aEvent;
    }

    /**
     * Handle 'Edit lang template' button form field.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleTemplateLangBtnFormField($name, array $aEvent, $handlerModId, $srcModId){
        $oRequest = AMI::getSingleton('env/request');
        $aEvent['aScope']['btn_type'] = 'lng';
        $aEvent['aScope']['disabled'] = $oRequest->get('id', null) ? '0' : '1';
        $aEvent['aScope']['popup_title'] = $this->aLocale['popup_title_templates_langs'];
        $aEvent['aScope']['popup_module'] = 'modules_templates_langs';
        $aEvent['aScope']['template_id'] = $this->getTemplateId('cms_modules_templates_langs', 'eshop_shipping_fields.lng', 'templates/lang/');
        return $aEvent;
    }

    /**
     * Handle 'Edit adm lang template' button form field.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     */
    public function handleTemplateLangAdmBtnFormField($name, array $aEvent, $handlerModId, $srcModId){
        $oRequest = AMI::getSingleton('env/request');
        $aEvent['aScope']['btn_type'] = 'admlng';
        $aEvent['aScope']['disabled'] = $oRequest->get('id', null) ? '0' : '1';
        $aEvent['aScope']['popup_title'] = $this->aLocale['popup_title_templates_langs'];
        $aEvent['aScope']['popup_module'] = 'modules_templates_langs';
        $aEvent['aScope']['template_id'] = $this->getTemplateId('cms_modules_templates_langs', 'eshop_order.lng', '_local/_admin/templates/lang/');
        return $aEvent;
    }

    /**
     * Returns template id.
     *
     * @param  string $table  Table name
     * @param  string $tplName  Template name
     * @param  string $tplPath  Template path
     * @return int
     */
    private function getTemplateId($table, $tplName, $tplPath){
        return (int)AMI::getSingleton('db')->fetchValue(
            DB_Query::getSnippet(
                'SELECT id FROM '.$table.' WHERE name=%s AND path=%s ORDER BY id ASC LIMIT 1'
            )
            ->q($tplName)
            ->q($tplPath)
        );
    }
}

/**
 * AmiEshopShipping/Fields configuration admin list component action controller.
 *
 * @package    Config_AmiEshopShipping_Fields
 * @subpackage Controller
 * @resource   {$modId}/list/controller/adm <code>AMI::getResource('{$modId}/list/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Fields_ListAdm extends Hyper_AmiEshopShipping_ListAdm{
    /**
     * List actions controller resource id
     *
     * @var string
     */
    protected $listActionsResId = 'eshop_shipping_fields/list_actions/controller/adm';

    /**
     * List group actions controller resource id
     *
     * @var string
     */
    protected $listGrpActionsResId = 'eshop_shipping_fields/list_group_actions/controller/adm';

    /**
     * Initialization.
     *
     * @return AmiSubscribe_Topic_ListAdm
     */
    public function init(){
        parent::init();

        $this->dropActions(self::ACTION_GROUP, array('gen_sublink', 'gen_html_meta', 'gen_html_meta_force', 'seo_section'));

        return $this;
    }
}

/**
 * AmiEshopShipping/Fields configuration admin list component view.
 *
 * @package    Config_AmiEshopShipping_Fields
 * @subpackage View
 * @resource   {$modId}/list/view/adm <code>AMI::getResource('{$modId}/list/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Fields_ListViewAdm extends Hyper_AmiEshopShipping_ListViewAdm{
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
     * List default elemnts template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#list_header',
            '#columns', 'public', 'is_sys', 'header', 'postfix', 'columns',
            '#actions', 'edit', 'delete', 'actions',
        'list_header'
    );

    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AmiEshopShipping_Fields_ListViewAdm
     */
    public function init(){
        parent::init();

        // Init columns
        $this
            ->removeColumn('announce')
            ->removeColumn('position')
            ->removeColumn('public')
            ->removeColumn('date_created')
            ->addColumn('is_sys')
            ->setColumnWidth('is_sys', 'extra-narrow')
            ->addColumn('postfix')
            ->setColumnWidth('postfix', 'extra-wide')
            ->addSortColumns(array('is_sys', 'header', 'postfix'));

        $this->setColumnLayout('is_sys', array('align' => 'center'));
        $this->formatColumn('is_sys', array($this, 'fmtColIcon'), array('class' => 'checked'));

        $this->addScriptCode($this->getTemplate()->parse($this->tplBlockName . ':javascript'));

        return $this;
    }
}

/**
 * AmiEshopShipping/Fields configuration module admin list actions controller.
 *
 * @package    Config_AmiEshopShipping_Fields
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Fields_ListActionsAdm extends Hyper_AmiEshopShipping_ListActionsAdm{
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
        $aSystemIDs = $aEvent['oController']->getSystemFields();

        $id = $this->getRequestId();
        if(in_array($id, $aSystemIDs)){
            $statusMsg = 'status_del_fail';
            $aEvent['oResponse']->addStatusMessage($statusMsg);
            $this->refreshView();
            return $aEvent;
        }else{
            return parent::dispatchDelete($name, $aEvent, $handlerModId, $srcModId);
        }
    }
}

/**
 * AmiEshopShipping/Fields configuration module admin list group actions controller.
 *
 * @package    Config_AmiEshopShipping_Fields
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiEshopShipping_Fields_ListGroupActionsAdm extends Hyper_AmiEshopShipping_ListGroupActionsAdm{
    /**
     * Event handler.
     *
     * Dispatches group delete action.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */
    public function dispatchGrpDelete($name, array $aEvent, $handlerModId, $srcModId){
        $actionIDs = $aEvent['oRequest']->get('mod_action_id');
        if(!empty($actionIDs)){
            $aActionIDs = explode(',', $actionIDs);
        }else{
            return $aEvent;
        }

        $aSystemIDs = $aEvent['oController']->getSystemFields();
        foreach($aSystemIDs as $systemID){
            $resKey = array_search($systemID, $aActionIDs);
            if($resKey !== false){
                unset($aActionIDs[$resKey]);
            }
        }

        $aEvent['oRequest']->set('mod_action_id', implode(',', $aActionIDs));

        return parent::dispatchGrpDelete($name, $aEvent, $handlerModId, $srcModId);
    }
}
