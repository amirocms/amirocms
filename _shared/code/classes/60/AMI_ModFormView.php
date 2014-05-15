<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModFormView.php 46631 2014-01-15 19:08:53Z Leontiev Anton $
 * @since     5.12.0
 */

/**
 * Module form component view interface.
 *
 * Use AMI_ModFormView children, interface usage will be described later.
 *
 * @package    ModuleComponent
 * @subpackage View
 * @todo       Describe usage
 * @since      5.12.0
 */
interface AMI_iModFormView{
    /**
     * Adds new field into fields array.
     *
     * @param  array $aField  Field structure
     * @return AMI_iModFormView
     * @see    AMI_ModFormView::addField()
     */
    public function addField(array $aField);

    /**
     * Adds locale.
     *
     * @param  array $aLocale  Locale array
     * @return AMI_iModFormView
     * @see    AMI_View::addLocale()
     */
    public function addLocale(array $aLocale);
}

/**
 * Module form component view abstraction.
 *
 * @package    ModuleComponent
 * @subpackage View
 * @since      5.12.0
 */
abstract class AMI_ModFormView extends AMI_ModPlaceholderView implements AMI_iModFormView{
    /**#@+
     * Tab state
     *
     * @see AMI_ModFormView::addTab()
     */

    const TAB_STATE_COMMON   = 'normal';

    const TAB_STATE_ACTIVE   = 'active';

    const TAB_STATE_DISABLED = 'disabled';

    /**#@-*/

    /**
     * Module table model
     *
     * @var    AMI_ModTable
     * @amidev Temporary
     */
    protected $oModel;

    /**
     * Module table item model
     *
     * @var    AMI_iFormModTableItem
     * @amidev Temporary
     */
    protected $oItem;

    /**
     * Module table item model
     *
     * Backward compatibility,
     *
     * @var        AMI_iFormModTableItem
     * @deprecated 5.14.8
     * @amidev
     */
    protected $oModelItem;

    /**
     * View type
     *
     * @var  string
     * @todo Move this to AMI_View?
     */
    protected $viewType = 'form';

    /**
     * Form default elements template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#form',
            'public', 'id_dataset', 'id_page', 'id_cat', 'ext_custom_fields_addon', 'date_created', 'header', 'ext_adv', 'ext_image', 'ext_tags', 'ext_relations',
            '#ext_custom_fields_top',
            'ext_custom_fields_top',
            '#default_tabset',
                '#announce_tab', 'announce', 'announce_tab',
                '#body_tab', 'body', 'body_tab',
                '#ext_custom_fields_tab', 'ext_custom_fields_tab',
                '#options_tab',
                    'disable_comments',
                    'sublink', 'html_title', 'html_keywords', 'html_description', 'og_image', 'details_noindex',
                    '#ext_rating_values' ,'ext_rating_values',
                'options_tab',
            'default_tabset',
            '#ext_custom_fields_bottom',
            'ext_custom_fields_bottom',
        'form'
    );

    /**
     * Placeholders custom data array.
     *
     * @var array
     */
    protected $aPlaceholdersData = array();

    /**
     * Array of tab containers (groups of tabsheets)
     *
     * @var    array Containers for tabsheets
     * @amidev Temporary
     */
    protected $aTabContainers = array();

    /**
     * Array of tabs
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aTabs = array();

    /**
     * Array of form fields
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aFields = array();

    /**
     * Array of form buttons
     *
     * @var    array
     * @amidev Temporary
     */
    protected $aFormButtons = array('add', 'apply', 'cancel', 'save');

    /**
     * Standard filters
     *
     * @var    array
     * @amidev Temporary
     */
    private $aFilters = array(
        'required', 'filled', 'alphanum', 'int', 'float', 'date', 'email', 'domain',
        'custom', 'stop_on_error', 'sublink', 'url'
    );

    /**
     * Template engine object
     *
     * @var AMI_Template
     * @amidev Temporary
     */
    protected $oTpl = null;

    /**
     * Variable scope
     *
     * @var array
     * @amidev Temporary
     */
    protected $aScope = array();

    /**
     * Field type filter
     *
     * @var string
     * @see AMI_ModFormView::getFields()
     */
    private $fieldFilter;

    /**
     * Constructor.
     */
    public function  __construct(){
        $this->oModelItem = &$this->oItem;

        parent::__construct();
        $this->oTpl = $this->getTemplate();
    }

    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        $this->addField(array('name' => 'mod_id', 'value' => $this->getModId(), 'type' => 'hidden'));
        return parent::init();
    }

    /**
     * Setting up model item object.
     *
     * @param  AMI_iFormModTableItem $oItem  Item model
     * @return AMI_ModFormView
     */
    public function setModelItem(AMI_iFormModTableItem $oItem){
        $this->oItem = $oItem;
        return $this;
    }

    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        $this->aScope = $this->getScope($this->viewType);
        $this->aScope['scripts'] = $this->getScripts($this->aScope);
        $aEvent = array(
            'aScope'     => &$this->aScope,
            'oFormView'  => $this,
            'oModelItem' => $this->oItem, // @deprecated since 5.14.0
            'oItem'      => $this->oItem
        );
        /**
         * Allows modify list of form fields.
         *
         * @event      on_form_fields_{viewType} $modId
         * @eventparam array                 aScope     Scope
         * @eventparam AMI_ModFormView       oFormView  Form view object
         * @eventparam AMI_iFormModTableItem oItem      Table item model
         */
        AMI_Event::fire('on_form_fields_' . $this->viewType, $aEvent, $this->getModId());
        $this->oTpl->setBlockLocale($this->tplBlockName, $this->aLocale);

        $formHTML = '';
        foreach($this->getPlaceholders(true) as $placeholderName => $placeholderContent){
            if(is_array($placeholderContent)){
                $formHTML .= $this->getSectionHTML($placeholderName, $placeholderContent);
            }
        }

        return $formHTML;
    }

    /**
     * Returns section HTML.
     *
     * @param string $sectionName     Section name
     * @param array $aSectionContent  Section inner elements
     * @return string
     * @amidev
     */
    protected function getSectionHTML($sectionName, array $aSectionContent){
        $currentAction = AMI::getSingleton('env/request')->get('mod_action');
        $this->aScope['form_mode'] = '';
        if($currentAction == 'form_show'){
            $this->aScope['form_mode'] = 'show';
        }
        if(!$this->canDisplayByAction($sectionName, $currentAction)){
            return '';
        }

        $sectionHTML = '';
        $aScope = $this->aScope + array('name' => $sectionName);

        // Process custom template file
        if(isset($this->aPlaceholdersData[$sectionName]) && isset($this->aPlaceholdersData[$sectionName]['templates'])){
            $oTemplater = clone $this->oTpl;
            $tplBlockName = $this->tplBlockName;
            $this->tplBlockName = $this->tplBlockName.$sectionName;
            foreach($this->aPlaceholdersData[$sectionName]['templates'] as $path){
                $this->oTpl->mergeBlock($this->tplBlockName, $path);
            }
            if(isset($this->aPlaceholdersData[$sectionName]['locales'])){
                foreach($this->aPlaceholdersData[$sectionName]['locales'] as $aLocale){
                    $this->oTpl->setBlockLocale($this->tplBlockName, $aLocale, true);
                }
            }
        }

        // Process section elements
        foreach($aSectionContent as $placeholderName => $placeholderContent){
            if(is_array($placeholderContent)){
                $innerSectionHTML = $this->getSectionHTML($placeholderName, $placeholderContent);
                $sectionHTML .= $innerSectionHTML;
                $aScope['section_' . $placeholderName] = $innerSectionHTML;
            }else{
                if(isset($this->aFields[$placeholderName])){
                    $fieldHTML = $this->getFieldHTML($placeholderName);
                    $sectionHTML .= $fieldHTML;
                    $aScope['field_' . $placeholderName] = $fieldHTML;
                }
            }
        }
        $aScope['section_html'] = $sectionHTML;
        $prefix = 'section';

        // Tabset
        if(isset($this->aTabContainers[$sectionName])){
            $prefix = 'tabset';
            $aTabs = isset($this->aTabContainers[$sectionName]['tabs']) ? $this->aTabContainers[$sectionName]['tabs'] : array();
            $tabArray = '';
            foreach($aTabs as $tabName => $aTab){
                if($this->canDisplayByAction($tabName, $currentAction)){
                    $tabArray .= $this->oTpl->parse($this->tplBlockName . ':tab_array', array('name' => $tabName, 'title' => $aTab['title'], 'state' => $aTab['state']));
                }
            }
            $aScope['tab_array'] = $tabArray;
        }

        // Tab
        if(isset($this->aTabs[$sectionName])){
            $prefix = 'tab';
        }

        // Form specific vars
        if($sectionName == 'form'){
            $aScope['id'] = AMI::getSingleton('env/request')->get('id');
            if($aScope['form_mode'] == 'show'){
                $aScope['header'] = $this->aLocale['form_caption_show'];
            }else{
                $aScope['header'] = $this->aLocale[$this->isNewItem() ? 'form_caption_add' : 'form_caption_apply'];
            }
            $aScope['form_buttons'] = $this->getFormButtons();
        }

        // Render section with specific or common template (if present)
        if($this->oTpl->issetSet($this->tplBlockName . ':' . $prefix . '_' . $sectionName)){
            $sectionHTML = $this->oTpl->parse($this->tplBlockName . ':' . $prefix . '_' . $sectionName, $aScope);
        }elseif($this->oTpl->issetSet($this->tplBlockName . ':' . $prefix)){
            $sectionHTML = $this->oTpl->parse($this->tplBlockName . ':' . $prefix, $aScope);
        }

        // Restore templater if was saved
        if(isset($oTemplater)){
            $this->oTpl = $oTemplater;
            $this->tplBlockName = $tplBlockName;
        }

        return $sectionHTML;
    }

    /**
     * Returns field HTML.
     *
     * @param string $fieldName  Field name
     * @return string
     * @amidev
     */
    protected function getFieldHTML($fieldName){
        $aValidators = $this->oItem ? $this->oItem->getValidators() : array();
        $aInvalidFields = $this->getInvalidFields();
        $aField = $this->aFields[$fieldName];
        if(isset($aField['multiple']) && $aField['multiple']){
            if(!isset($aField['attributes'])){
                $aField['attributes'] = array();
            }
            $aField['attributes']['multiple'] = true;
        }
        if(isset($aField['attributes']) && isset($aField['attributes']['multiple']) && $aField['attributes']['multiple']){
            $aField['multiple'] = true;
        }
        $currentAction = AMI::getSingleton('env/request')->get('mod_action');

        // Display fields by action filter
        if(isset($aField['display_by_action'])){
            $aField['display_by_action'] = is_array($aField['display_by_action']) ? $aField['display_by_action'] : array($aField['display_by_action']);
            $bDisplay = false;
            foreach($aField['display_by_action'] as $action){
                if($currentAction == 'form_edit' or $currentAction == 'form_save'){
                    if(($action == 'edit' && $this->oItem->id) || ($action == 'new' && empty($this->oItem->id))){
                        $bDisplay = true;
                    }
                }elseif('form_'.$action == $currentAction){
                    $bDisplay = true;
                }
            }
            if(!$bDisplay){
                return '';
            }
        }

        $aScope = $this->aScope;

        if(!isset($aField['type'])){
            $aField['type'] = 'input';
        }
        $aFieldScope = array('is_invalid' => in_array($aField['name'], $aInvalidFields)) + $this->aScope + $aField + array('validators' => '');

        // Event: on_before_form_field_{##field_name##}
        $aEvent = array(
           'aField'     => &$aField,
           'aScope'     => &$aFieldScope,
           'oTpl'       => $this->oTpl,
           'oModelItem' => $this->oItem, // @deprecated since 5.14.0
           'oItem'      => $this->oItem
        );
        /**
         * Allows change field at displaying form.
         *
         * @event      on_before_form_field_{fieldName} $modId
         * @eventparam array                 aField  Form field structure
         * @eventparam array                 aScope  Scope
         * @eventparam AMI_Template          oTpl    Template object
         * @eventparam AMI_iFormModTableItem oItem   Table item object
         */
        AMI_Event::fire('on_before_form_field_{'.($aField['name'] ? $aField['name'] : '').'}', $aEvent, $this->getModId());

        $caption = isset($aField['caption']) ? $aField['caption'] : $aField['name'];
        $aFieldScope['caption'] = $caption;
        $aLocale = $this->oTpl->getBlockLocale($this->tplBlockName);
        $viewType = $this->viewType == 'form_filter' ? 'filter' : $this->viewType;
        if(isset($aLocale[$viewType . '_field_' . $caption])){
            $aFieldScope['element_caption'] = $aLocale[$viewType . '_field_' . $caption];
        }elseif(isset($aLocale['caption_' . $caption])){
            $aFieldScope['element_caption'] = $aLocale['caption_' . $caption];
        }else{
            if(in_array($aFieldScope['type'], array('select', 'checkbox', 'radio', 'input'))){
                $aFieldScope['element_caption'] = '--- ' . $caption . ' ---';
                if('' != $caption){
                    trigger_error('Language variable `' . $viewType . '_field_' . $caption . '` is not exists', E_USER_NOTICE);
                }
            }
        }

        // @todo??? Don't get 'value' from model if 'value' already set
        if(isset($aField['value'])){
            $aFieldScope['value'] = $aField['value'];
        }else{
            $aFieldScope['value'] = is_object($this->oItem)? $this->oItem->getValue($aField['name']): '';
        }

        // @todo: find out right way
        if(isset($aField['flt_default']) && empty($aFieldScope['value'])){
            $aFieldScope['value'] = $aField['flt_default'];
        }

        // CE modes: editor, bb, textarea
        if($aField['type'] === 'htmleditor'){
            $aModes = array('editor', 'bb', 'textarea');
            if(isset($aField['modes']) && is_array($aField['modes']) && count($aField['modes'])){
                $aNewModes = array();
                foreach($aField['modes'] as $mode){
                    if(in_array($mode, $aModes)){
                        $aNewModes[] = $mode;
                    }
                }
                if(count($aNewModes)){
                    $aModes = $aNewModes;
                }
            }
            foreach($aModes as $i => $mode){
                $aModes[$i] = "'" . $mode . "'";
            }
            $aFieldScope['ce_modes'] = "[" . implode(",", $aModes) . "]";
        }

        if($aField['type'] === 'checkbox'){
            $aFieldScope['checked'] = !empty($aFieldScope['value']) || ($this->isNewItem() && !empty($aFieldScope['default_checked'])) ? ' checked="checked"' : '';
        }

        if($aField['type'] == 'datetime' || $aField['type'] == 'date' || $aField['type'] == 'datefrom' || $aField['type'] == 'dateto'){

        	isset($aFieldScope['attributes_date'])?1:$aFieldScope['attributes_date'] = array();
            $aFieldScope['attributes_date']['data-ami-max-date'] =
            	$aFieldScope['max_date'] = AMI_Lib_Date::formatUnixTime(AMI_Lib_Date::UTIME_MAX);
            $aFieldScope['attributes_date']['data-ami-min-date'] =
            	$aFieldScope['min_date'] = AMI_Lib_Date::formatUnixTime(AMI_Lib_Date::UTIME_MIN);

            if(!isset($aField['html']) && isset($aFieldScope['attributes_date'])){
	            $attributes = '';
	            foreach($aFieldScope['attributes_date'] as $name => $value){
	                $attributes .= ' ' . $name . '="' . AMI_Lib_String::htmlChars($value) . '"';
	            }
	            $aFieldScope['attributes_date'] = $attributes;
	        }

            if(!isset($aField['html']) && isset($aFieldScope['attributes_time'])){
	            $attributes = '';
	            foreach($aFieldScope['attributes_time'] as $name => $value){
	                $attributes .= ' ' . $name . '="' . AMI_Lib_String::htmlChars($value) . '"';
	            }
	            $aFieldScope['attributes_time'] = $attributes;
	        }

        }

        if($aField['type'] == 'datetime' || $aField['type'] == 'date'){
            $aDateParts = explode(' ', $aFieldScope['value']);
            $aFieldScope['value'] = $aDateParts[0] ? AMI_Lib_Date::formatDateTime($aDateParts[0], AMI_Lib_Date::FMT_DATE) : '';
            $aFieldScope['value_time'] = isset($aDateParts[1]) ? AMI_Lib_Date::formatDateTime($aDateParts[1], AMI_Lib_Date::FMT_TIME) : '';
            if(!$this->oItem->getId()){
                $lang = AMI_Registry::get('lang', 'en');
                if($aFieldScope['value'] == ''){
                    $aFieldScope['value'] = date(AMI::getDateFormat($lang, AMI_Lib_Date::FMT_DATE));
                }
                if($aFieldScope['value_time'] == ''){
                    $aFieldScope['value_time'] = date(AMI::getDateFormat($lang, AMI_Lib_Date::FMT_TIME));
                }
            }
        }

        if($aField['type'] == 'time_period'){
            if(!empty($aFieldScope['value'])){
                $aPeriods = explode('-', $aFieldScope['value']);
                $aFieldScope['value_first'] = $aPeriods[0];
                $aFieldScope['value_second'] = isset($aPeriods[1]) ? $aPeriods[1] : '';
            }else{
                $aFieldScope['value_first'] = '';
                $aFieldScope['value_second'] = '';
            }
        }

        /**
         * Allows to modify form fields before displaying.
         *
         * @event      on_form_field_{fieldName} $modId
         * @eventparam array                 aField  Form field structure
         * @eventparam array                 aScope  Scope
         * @eventparam AMI_Template          oTpl    Tamplate object
         * @eventparam AMI_iFormModTableItem oItem   Table item model
         */
        AMI_Event::fire('on_form_field_{' . ($aField['name'] ? $aField['name'] : '') . '}', $aEvent, $this->getModId());

        if($aField['type'] == 'select' || $aField['type'] == 'radio'){
            $rows = '';
            if($aField['type'] == 'select' && isset($aField['not_selected'])){
                array_unshift($aField['data'], $aField['not_selected']);
            }
            $isMultiple = false;
            if(isset($aField['multiple']) && $aField['multiple']){
                $isMultiple = true;
                $aValues = explode(';', $aFieldScope['value']);
            }
            foreach($aField['data'] as $aRowScope){
                $idValue = isset($aRowScope['value']) ? $aRowScope['value'] : (isset($aRowScope['id'])? $aRowScope['id'] : null);
                // fix for 'selected' attribute in options in the select
                if(isset($aRowScope['selected']) && (!isset($aFieldScope['value']) || empty($aFieldScope['value']))){
                    $selected = true;
                }else{
                    $selected = ($isMultiple) ? in_array($idValue, $aValues) : ($idValue == $aFieldScope['value']);
                }
                if(!isset($aRowScope['html_caption']) && isset($aRowScope['level'])){ // level-based lists (eshop categories, etc)
                    $aRowScope['html_caption'] = AMI_Lib_String::htmlChars(isset($aRowScope['caption']) ? $this->aLocale[$aRowScope['caption']] : (isset($aRowScope['name']) ? $aRowScope['name'] : ''));
                    for($i = 0; $i < $aRowScope['level']; $i++){
                        $aRowScope['html_caption'] = '&middot;&nbsp;'.$aRowScope['html_caption'];
                    }
                }
                if(isset($aRowScope['caption'])){
                    if(isset($this->aLocale[$aRowScope['caption']])){
                        $caption = $this->aLocale[$aRowScope['caption']];
                    }else{
                        $caption = '--- ' . $aRowScope['caption'] . ' ---';
                        trigger_error(
                            'Language variable `' . $aRowScope['caption'] . '` is not exists',
                            E_USER_NOTICE
                        );
                    }
                }elseif(isset($aRowScope['name'])){
                    $caption = $aRowScope['name'];
                }else{
                    $caption = '---' . $aField['name'] . '---';
                }
                $aLocalScope = array(
                    'name'      => $aField['name'],
                    'value'     => AMI_Lib_String::htmlChars($idValue),
                    'caption'   => isset($aRowScope['html_caption']) ? $aRowScope['html_caption'] : AMI_Lib_String::htmlChars($caption),
                    'selected'  => $selected ? ' selected="selected"' : '',
                    'form_mode' => $aFieldScope['form_mode']
                ) + $aRowScope;
                if($aField['type'] == 'radio'){
                    $aLocalScope['checked'] = !empty($aRowScope['checked']) || $aRowScope['id'] == $aFieldScope['value'] ? 'checked="yes"' : '';
                    $aLocalScope['disabled'] = !empty($aRowScope['disabled']) ? 'disabled="yes"' : '';
                }
                if(isset($aRowScope['attributes']) && is_array($aRowScope['attributes'])){
                    $aLocalScope['attributes'] = '';
                    foreach($aRowScope['attributes'] as $name => $value){
                        $aLocalScope['attributes'] .= ' ' . $name . '="' . AMI_Lib_String::htmlChars($value) . '"';
                    }
                }
                $rows .= $this->oTpl->parse($this->tplBlockName . ':' . $aField['type'] . '_field_row', $aLocalScope);
            }
            $aFieldScope['select'] = $rows;
        }

        if($aField['type'] != 'hidden'){
            // Prepare validators and filters for not hidden fields
            $aFieldScope['filter_class'] = '';

            if(!empty($aValidators[$aField['name']])){
                $aField['validate'] = array_merge($aField['validate'], $aValidators[$aField['name']]);
            }

            if(!empty($aField['validate'])){
                foreach($aField['validate'] as $validator){
                    if(in_array($validator, $this->aFilters)){
                        $aFieldScope['filter_class'] .= ' ' . $validator;
                        $aFieldScope['validators'] .= ' ' . $validator;
                        $aFieldScope['validator_' . $validator] = true;
                        if($validator == 'custom' && !empty($aField['validate_script'])){
                            $aFieldScope['validate_script'] = rawurlencode($aField['validate_script']);
                        }
                    }
                }
            }
        }

        if($aField['type'] == 'file'){
            if(is_object($aFieldScope['value'])){
                $oFile = $aFieldScope['value'];
                $name = $oFile->getName();
                $size = AMI_Lib_String::getBytesAsText($oFile->getSize(), $this->aLocale, 1);
                $aFieldScope['value'] = is_null($name) ? false : ($name . ' (' . $size . ')');
            }else{
                $aFieldScope['value'] = false;
            }
        }

        if($aField['type'] == 'static' && isset($aField['caption_text'])){
            $aFieldScope['value'] = $this->aLocale[$aField['caption_text']];
        }

        if(!empty($aFieldScope['forceHTMLEncoding']) || (empty($aFieldScope['skipHTMLEncoding']) && is_string($aFieldScope['value']) && ($aFieldScope['form_mode'] != 'show'))){
            $aFieldScope['value'] = AMI_Lib_String::htmlChars($aFieldScope['value']);
        }

        // add extended classes to field
        $classes = isset($aFieldScope['attributes']['class']) ? $aFieldScope['attributes']['class'] : '';
        $aFieldScope['classes'] = $classes;

        if(!isset($aField['html']) && isset($aFieldScope['attributes'])){
            // custom attributes
            $attributes = '';
            foreach($aFieldScope['attributes'] as $name => $value){
                $attributes .= ' ' . $name . '="' . AMI_Lib_String::htmlChars($value) . '"';
            }

            if(!isset($aFieldScope['attributes']['maxlength'])){
                $attributes .= ' maxlength="255" ';
            }

            $aFieldScope['attributes'] = $attributes;
            unset($aAttributes);
        }

        if(isset($aField['hint'])){
            $aHintScope = array(
                'tooltip_id'    => 'ami_tooltip_' . uniqid(),
                'value'         => isset($this->aLocale['hint_' . $aField['name']]) ? AMI_Lib_String::jParse($this->aLocale['hint_' . $aField['name']]) : ''
            );
            if(isset($aField['hint_text'])){
                $aHintScope['value'] = AMI_Lib_String::jParse($aField['hint_text']);
            }
            if(isset($aField['hint_label'])){
                $aHintScope['label'] = $aField['hint_label'];
            }
            if(!empty($aHintScope['value'])){
                $aFieldScope['hint'] = $this->oTpl->parse($this->tplBlockName . ':hint', $aHintScope);
            }
        }

        // Don't generate html if 'html' already set
        $formField = isset($aField['html'])
            ? $aField['html']
            : $this->oTpl->parse($this->tplBlockName . ':' . $aField['type'] . '_field', $aFieldScope);
        return $formField;
    }

    /**
     * Checks, whether it's a new or edit mode.
     *
     * @return bool
     * @amidev
     */
    protected function isNewItem(){
        if($this->oItem){
            return !($this->viewType == 'form' && $this->oItem->getId());
        }else{
            return TRUE;
        }
    }

    /**
     * Returns Invalid fields array.
     *
     * @return array
     * @amidev
     */
    protected function getInvalidFields(){
        $aInvalidFields = array();
        if($this->oItem){
            if($this->oItem->getValidatorException()){
                foreach($this->oItem->getValidatorException()->getData() as $aError){
                    $aInvalidFields[] = $aError['field'];
                }
            }
        }
        return $aInvalidFields;
    }

    /**
     * Returns form buttons HTML.
     *
     * @return string
     * @amidev
     */
    protected function getFormButtons(){
        $formButtons = '';
        if($this->viewType == 'form'){
            if($this->oItem && $this->oItem->getId()){
                if(($ind = array_search('add', $this->aFormButtons)) !== false){
                    unset($this->aFormButtons[$ind]);
                }
            }else{
                if(($ind = array_search('save', $this->aFormButtons)) !== false){
                    unset($this->aFormButtons[$ind]);
                }
                if(($ind = array_search('cancel', $this->aFormButtons)) !== false){
                    unset($this->aFormButtons[$ind]);
                }
            }
            $aButtonsData = array();
            foreach($this->aFormButtons as $btnName){
                $aButtonsData[$btnName.'_btn'] = $this->oTpl->parse($this->tplBlockName . ":" . $btnName . '_btn', $this->aScope);
            }
            $formButtons = $this->oTpl->parse($this->tplBlockName . ':' . 'form_buttons', $aButtonsData);
        }
        return $formButtons;
    }

    /**
     * Returns true if the specified section can be displayed for the current action.
     *
     * @param string $sectionName   Section name
     * @param string $currentAction  Current action
     * @return bool
     * @amidev
     */
    protected function canDisplayByAction($sectionName, $currentAction){
        $bDisplay = true;
        $aActions = isset($this->aPlaceholdersData[$sectionName]['display_by_action']) ? $this->aPlaceholdersData[$sectionName]['display_by_action'] : false;
        if($aActions){
            $bDisplay = false;
            $aActions = is_array($aActions) ? $aActions : array($aActions);
            foreach($aActions as $action){
                if($currentAction == 'form_edit' or $currentAction == 'form_save'){
                    if(($action == 'edit' && $this->oItem->id) || ($action == 'new' && empty($this->oItem->id))){
                        $bDisplay = true;
                        break;
                    }
                }elseif('form_'.$action == $currentAction){
                    $bDisplay = true;
                    break;
                }
            }
        }
        return $bDisplay;
    }

    /**
     * Returns section placeholders
     *
     * @param  string $section
     * @return array
     */
     /*
    public function getSectionPlaceholders($section){
        return array_slice($this->aFormPlaceholders, 1, -1);
    }*/

    /**
     * Add new field into fields array.
     *
     * Field structure is array having following data:
     * - name - form field name;
     * - type - field type: input|checkbox|textarea|hidden|select|radio|datetime|date, 'input' by default;
     * - attributes - custom attributes (array, optional);
     * - default_checked - default checkbox state (bool, optional);
     * - rows - textarea tag "rows" attribute value (int, optional);
     * - cols - textarea tag "cols" attribute value (int, optional);
     * - data - select/radio tag options data (array, optional).
     * - display_by_action - string/array to specify corresponding actions for field to display (edit, new, etc...) (since 5.12.8)<br /><br />
     *
     * Example:
     * <code>
     * // AmiSample_FormViewAdm
     * public function init(){
     *     parent::init();
     *
     *     // Common form fields {
     *
     *     // Item id
     *     $this->addField(array('name' => 'id', 'type' => 'hidden'));
     *     // Form action - save item
     *     $this->addField(array('name' => 'mod_action', 'value' => 'form_save', 'type' => 'hidden'));
     *
     *     // } Common form fields
     *
     *     $this->addField(array('name' => 'nickname'), 'attributes' => array('readonly' => 'readonly'));
     *     $this->addField(array('name' => 'birth', 'type' => 'date'));
     *     // only after element create
     *     $this->addField(array('name' => 'creation_date', 'type' => 'date', 'display_by_action' => 'edit'));
     * }
     * </code>
     *
     * Example:
     * <code>
     * // Somewhere in AMI_ModFormViewAdm child
     * public function init(){
     *     parent::init();
     *
     *     // ...
     *
     *     // <input type="text" name="text_field" ... />
     *     $this->addField(array('name' => 'text_field');
     *
     *     // <input type="checkbox" name="checkbox" checked="checked" />
     *     // checked by default
     *     $this->addField(array('name' => 'checkbox_field', 'type' => 'checkbox', 'default_checked' => true));
     *
     *     // <textarea name="textarea_field" ... >...</textarea>
     *     $this->addField(array('name' => 'textarea_field', 'type' => 'textarea', 'rows' => 10, 'cols' => 20));
     *
     *     // <input type="hidden" name="hidden_field" ... />
     *     $this->addField(array('name' => 'hidden_field', 'type' => 'hidden'));
     *
     *     // <select name="select_field"> ... </select>
     *     $this->addField(
     *         array(
     *             'name' => 'select_field',
     *             'type' => 'select',
     *             'data' => array(
     *                 // <option value="1">%%localized_caption%%</option> // %%localized_caption%%from locale resource
     *                 array('id' => '1', 'caption' => 'localized_caption'),
     *                 // <option value="0">direct_option_caption</option> // direct caption
     *                 array('id' => '0', 'name' => 'direct_option_caption')
     *             )
     *         )
     *     );
     *
     *     // <radio name="radio_field"> ... </select>
     *     $this->addField(
     *         array(
     *             'name' => 'radio_field',
     *             'type' => 'select',
     *             'data' => array(
     *                 // <option value="0">%%localized_caption%%</option> // %%localized_caption%%from locale resource
     *                 array('id' => '0', 'caption' => 'localized_caption'),
     *                 // <option value="1">direct_option_caption</option> // direct caption
     *                 array('id' => '1', 'name' => 'direct_option_caption')
     *             )
     *         )
     *     );
     *
     *     // text field for date with calendar popup control, separate text field for time
     *     $this->addField(array('name' => 'datetime_field', 'type' => 'datetime'));
     *
     *     // text field for date with calendar popup control
     *     $this->addField(array('name' => 'date_field', 'type' => 'date'));
     *
     *     // visual editor field
     *     $this->addField(
     *         array(
     *             'name' => 'about',                           // Field name
     *             'type' => 'htmleditor',                      // Field type
     *             'cols' => 80,                                // Number of columns
     *             'rows' => 10,                                // Number of rows
     *             'modes' => array('editor', 'bb', 'textarea') // Supported modes
     *         )
     *     );
     * }
     * </code>
     *
     * @param  array $aField  Field structure
     * @return AMI_ModFormView
     */
    public function addField(array $aField){
        // Add defaults
        $aField += array(
            'position' => '',
            'validate' => array()
        );
        $this->aFields[$aField['name']] = $aField;
        $this->addPlaceholders(array($aField['name'] => $aField['position']));

        return $this;
    }

    /**
     * Set field data to render field.
     *
     * @param  string $name  Field name
     * @param  mixed  $data  Field data
     * @return AMI_ModFormView
     * @since  6.0.4
     */
    public function setFieldData($name, $data){
        if(isset($this->aFields[$name])){
            $this->aFields[$name]['data'] = $data;
        }

        return $this;
    }

    /**
     * Drop field from fields array.
     *
     * @param  string $fieldName  Field name
     * @return AMI_ModFormView
     */
    public function dropField($fieldName){
        unset($this->aFields[$fieldName]);

    	return $this;
    }


    /**
     * Add a tab container on the form.
     *
     * Example:
     * <code>
     * // Somewhere in AMI_ModFormViewAdm child
     * public function init(){
     *     parent::init();
     *
     *     // ...
     *
     *     // Add new tab container
     *     $this->addTabContainer('tabset1');
     *
     *     // Add tabs
     *     $this->addTab('tab1', 'tabset1', 'active');
     *     $this->addTab('tab2', 'tabset1');
     *
     *     // Add a field to the first tab
     *     $this->addField(array('name' => 'about', 'type' => 'htmleditor', 'cols' => 80, 'rows' => 10, 'position' => 'tab1.end'));
     *
     *     // Add a field to the second tab
     *     $this->addField(array('name' => 'birth', 'type' => 'date', 'position' => 'tab2.end'));
     *
     *     // ...
     *
     * }
     * </code>
     *
     * @param string $name  Name of the container
     * @param string $position  Container placeholder position
     * @return AMI_ModFormView
     * @since  5.12.4
     * @see    AMI_ModFormView::addTab()
     */
    public function addTabContainer($name, $position = ''){
        $this->aTabContainers[$name] = array('name' => $name, 'position' => $position, 'tabs' => array());
        $this->putPlaceholder($name, $position, true);
        return $this;
    }

    /**
     * Add a tab to the tab container. See {@link AMI_ModFormView::addTabContainer()} for usage example.
     *
     * @param string $name  Tab name
     * @param string $container  Container name
     * @param string $state  State of the tab: AMI_ModFormView::TAB_STATE_COMMON, AMI_ModFormView::TAB_STATE_ACTIVE or AMI_ModFormView::TAB_STATE_DISABLED
     * @param string $position  Tab placeholder position, before|after locations are supported
     * @param string $displayByAction  String/array to specify corresponding actions for tab to display (edit, new, etc...)
     *
     * @return AMI_ModFormView
     * @since 5.12.4
     */
    public function addTab($name, $container, $state = self::TAB_STATE_COMMON, $position = '', $displayByAction = NULL){
        if(!isset($this->aTabContainers[$container])){
            // Container not found
            trigger_error('Tab container "' . $container . '" not found.', E_USER_ERROR);
            return $this;
        }
        if($position){
/*
            // Check position: only reative to container and its tabs
            $parts = explode('.', $position);
            $placeholder = $parts[0];
            $location = isset($parts[1]) ? $parts[1] : '';
            if(!(($placeholder == $container) && in_array($location, array('before', 'after'))) || !in_array($placeholder, $this->getTabNames($container))){
                trigger_error('Invalid position "' . $position . '" for tab "' . $name . '"', E_USER_ERROR);
                return $this;
            }
*/
        }else{
            // If position is not set, add it to the end of container
            $position = $container . '.end';
        }
        $aTab = array(
            'name' => $name, // remove?
            'title' => isset($this->aLocale['form_tab_' . $name]) ? $this->aLocale['form_tab_' . $name] : $name,
            'state' => $state,
            'position' => $position,
        );

        $this->aTabs[$name] = true;

        $aPlace = explode('.', $position);
        if(isset($aPlace[1]) && isset($this->aTabs[$aPlace[0]]) && in_array($aPlace[1], array('before', 'after'))){
            $this->aTabContainers[$container]['tabs'] =
                AMI_Lib_Array::insert(
                    $this->aTabContainers[$container]['tabs'],
                    array($name => $aTab),
                    $aPlace[0],
                    $aPlace[1] == 'after'
                );
        }else{
            $this->aTabContainers[$container]['tabs'][$name] = $aTab;
        }
        $this->addSection($name, $position, $displayByAction);
        return $this;
    }

    /**
     * Drop the tab from the tab container.
     *
     * @param string $name  Tab name
     * @param string $container  Container name
     *
     * @return AMI_ModFormView
     */
    public function dropTab($name, $container = ''){
        if($this->aTabs){
            foreach($this->aTabs as $key => $aTab){
                if($aTab['name'] == $name){
                    unset($this->aTabs[$key]);
                    break;
                }
            }
            if($container && $this->aTabContainers){
                if(isset($this->aTabContainers[$container]) && isset($this->aTabContainers[$container]['tabs'][$name])){
                    unset($this->aTabContainers[$container]['tabs'][$name]);
                }
            }
        }
        return $this;
    }

    /**
     * Add new field into fields array.
     *
     * @param  string $name  Section name.
     * @param  string $position  Section placeholder position.
     * @param  string|array $displayByAction  String/array to specify corresponding actions for tab to display (edit, new, etc...).
     * @return false|AMI_ModFormView
     */
    public function addSection($name, $position, $displayByAction = NULL){
        $index = $this->putPlaceholder($name, $position, true);
        if($index === false){
        	return false;
        }
		if(!empty($displayByAction)){
        	if(!isset($this->aPlaceholdersData[$name])){
                $this->aPlaceholdersData[$name] = array();
            }
            $this->aPlaceholdersData[$name]['display_by_action'] = is_array($displayByAction)?$displayByAction:array($displayByAction);
		}
        return $this;
    }

    /**
     * Returns fields as an array.
     *
     * @param  string $type  Field type
     * @return array
     * @amidev Temporary
     */
    public function getFields($type = null){
        $this->fieldFilter = $type;
        return
            is_null($type)
                ? $this->aFields
                : array_filter($this->aFields, array($this, 'cbFilterFields'));
    }

    /**
     * Merges specified template with current one. New sets will be added, exisiting sets will be overwritten.
     *
     * @param string $path             Template path
     * @param string $placeholderName  Custom template for specific placeholder ( optional )
     * @param array $aLocale           Section template locale
     * @return AMI_ModFormView
     * @since 5.12.4
     */
     public function addTemplate($path, $placeholderName = null, array $aLocale = null){
        // Specify templates for specified placeholders
        if(!empty($placeholderName)){
            if(!isset($this->aPlaceholdersData[$placeholderName])){
                $this->aPlaceholdersData[$placeholderName] = array();
            }
            if(!isset($this->aPlaceholdersData[$placeholderName]['templates'])){
                $this->aPlaceholdersData[$placeholderName]['templates'] = array();
            }elseif(isset($this->aPlaceholdersData[$placeholderName]) && is_object($this->aPlaceholdersData[$placeholderName])){
                $this->aPlaceholdersData[$placeholderName]['templates']->mergeBlock($this->tplBlockName, $path);
            }
            if(is_array($this->aPlaceholdersData[$placeholderName]['templates'])){
                $this->aPlaceholdersData[$placeholderName]['templates'][] = $path;
            }
            if(!is_null($aLocale) && is_array($aLocale)){
                if(!isset($this->aPlaceholdersData[$placeholderName]['locales'])){
                    $this->aPlaceholdersData[$placeholderName]['locales'] = array();
                }
                $this->aPlaceholdersData[$placeholderName]['locales'][] = $aLocale;
            }
        }else{
            $this->oTpl->mergeBlock($this->tplBlockName, $path);
            if(!is_null($aLocale) && is_array($aLocale)){
                $this->oTpl->setBlockLocale($this->tplBlockName, $aLocale);
            }
        }
        return $this;
     }

     /**
      * Sets full environment marker.
      *
      * @param string $serialId  Component Id
      * @param bool $isFullEnv   Full environment flag
      * @return AMI_ModFormView
      * @amidev Temporary
      */
     public function setFullEnvMarker($serialId, $isFullEnv){
        $this->addScriptCode('if(typeof(fullEnvForms) == "undefined") var fullEnvForms = {};');
        $this->addScriptCode('fullEnvForms["' . $serialId . '"] = ' . (($isFullEnv) ? 'true;' : 'false;'));
        return $this;
     }

     /**
      * Filters fields by type.
      *
      * @param  array $aField  Field data
      * @return bool
      * @see    AMI_ModFormView::getFields()
      */
     private function cbFilterFields(array $aField){
         $type = isset($aField['type']) ? $aField['type'] : 'text';
         return $this->fieldFilter === $type;
     }
}
