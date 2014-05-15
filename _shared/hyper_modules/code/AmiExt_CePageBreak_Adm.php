<?php
/**
 * AmiExt/CePageBreak extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiExt_CePageBreak
 * @version   $Id: AmiExt_CePageBreak_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/CePageBreak extension configuration admin controller.
 *
 * @package    Config_AmiExt_CePageBreak
 * @subpackage Controller
 * @resource   ce_page+break/module/controller/adm <code>AMI::getResource('ce_page+break/module/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_CePageBreak_Adm extends Hyper_AmiExt{
    /**#@+
     * Event handler.
     *
     * @see    AMI_Event::addHandler()
     * @see    AMI_Event::fire()
     */

    /**
     * Extension initialization.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_Ext::__construct()
     * @see    AMI_Mod::init()
     */
    public function handlePreInit($name, array $aEvent, $handlerModId, $srcModId){
        $modId = $aEvent['modId'];
        $oView = $this->getView(AMI_Registry::get('side'));
        $oView->setExt($this);
        AMI_Event::addHandler('on_form_fields_form', array($oView, 'handleFormFields'), $modId);
        return $aEvent;
    }

    /**#@-*/
}

/**
 * AmiExt/CePageBreak extension configuration admin view.
 *
 * @package    Config_AmiExt_CePageBreak
 * @subpackage View
 * @resource   ce_page_break/view/adm <code>AMI::getResource('ce_page_break/view/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_CePageBreak_ViewAdm extends AMI_ExtView{
    /**
     * Template block name
     *
     * @var string
     */
    protected $tplBlockName = 'ce_page_break';

    /**
     * Tag using to break a page
     *
     * @var string
     */
    protected $pageBreakTag;

    /**
     * Tags in which we are unable to set the page break
     *
     * @var string
     */
    protected $pageBreakForbiddenTags;

    /**
     * Default constructor.
     */
    public function __construct(){
        $this->pageBreakTag = '##body_page_break##';
        $this->pageBreakForbiddenTags = 'a;abbr;acronym;address;blockquote;cite;code;del;dfn;h1;h2;h3;h4;h5;h6;ins;kbd;label;marquee;script;style;sub;sup;textarea;map;object;embed;button;fieldset;form;frameset;table;ul;ol;dl;select';
        parent::__construct();
    }

    /**
     * Adds js section to admin form.
     *
     * @param  string $name          Event name
     * @param  array  $aEvent        Event data
     * @param  string $handlerModId  Handler module id
     * @param  string $srcModId      Source module id
     * @return array
     * @see    AMI_ModFormView::get()
     */
    public function handleFormFields($name, array $aEvent, $handlerModId, $srcModId){
        $oFormView = $this->oExt->getFormViewInstance($aEvent);
        if(!$oFormView){
            return $aEvent;
        }
        $oTpl = $this->getTemplate();
        $oFormView->addField(
            array(
                'name' => 'page_break_js',
                'html' => $oTpl->parse($this->tplBlockName . ':page_break_js', array('forbidden_tags' => $this->pageBreakForbiddenTags, 'break_tag' => $this->pageBreakTag))
            )
        );
        return $aEvent;
    }

    /**#@-*/
}
