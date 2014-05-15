<?php
/**
 * AmiMultifeeds/FAQ configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiMultifeeds_FAQ
 * @version   $Id: AmiMultifeeds_Faq_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiMultifeeds/FAQ configuration admin action controller.
 *
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Faq_Adm extends Hyper_AmiMultifeeds_Adm{
}

/**
 * AmiMultifeeds/FAQ configuration email notification view.
 *
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage View
 * @resource   {$modId}/mail/view <code>AMI::getResource('{$modId}/mail/view')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Faq_EmailView extends AMI_View{

    /**
     * Template block name
     *
     * @var string
     */
    protected $tplBlockName = 'faq_mail';

    /**
     * Constructor.
     *
     * @param string $modId  Module Id
     */
    public function __construct($modId){
        $this->setModId($modId);
        $this->tplFileName    = AMI_iTemplate::LOCAL_TPL_MOD_PATH . '/' . $this->getModId() . '_mail_' . AMI_Registry::get('lang_data') . '.tpl';
        $this->localeFileName = AMI_iTemplate::LOCAL_LNG_MOD_PATH . '/' . $this->getModId() . '_mail.lng';
        parent::__construct();
        $oTpl = $this->getTemplate();
        $oTpl->setBlockLocale($this->tplBlockName, $oTpl->parseLocale($this->localeFileName), true);
    }

    /**
     * Returns view data.
     *
     * @return null
     */
    public function get(){
        return null;
    }

    /**
     * Returns view data.
     *
     * @param  string $part  Subject/Body ('subject'|'body')
     * @return string
     */
    public function getPart($part){
        $oTpl = $this->getTemplate();
        $aScope = $this->getScope($part);
        return $oTpl->parse($this->tplBlockName . ':' . $part, $aScope);
    }
}

/**
 * AmiMultifeeds/FAQ configuration admin filter component action controller.
 *
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Faq_FilterAdm extends Hyper_AmiMultifeeds_FilterAdm{
}

/**
 * AmiMultifeeds/FAQ configuration item list component filter model.
 *
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Faq_FilterModelAdm extends Hyper_AmiMultifeeds_FilterModelAdm{
    /**
     * Constructor.
     */
    public function __construct(){
        $index = array_search('header', $this->aCommonFields);
        if($index !== FALSE){
            unset($this->aCommonFields[$index]);
        }
        parent::__construct();
        $this->addViewField(
            array(
                'name'          => 'question',
                'type'          => 'input',
                'flt_type'      => 'text',
                'flt_default'   => '',
                'flt_condition' => 'like',
                'flt_column'    => 'question'
            )
        );
    }
}

/**
 * AmiMultifeeds/FAQ configuration admin filter component view.
 *
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Faq_FilterViewAdm extends Hyper_AmiMultifeeds_FilterViewAdm{
    /**
     * Filter default elemnts template (placeholders)
     *
     * @var array
     * @see AMI_ModPlaceholderView::addPlaceholders()
     * @see AMI_ModPlaceholderView::putPlaceholder()
     */
    protected $aPlaceholders = array(
        '#filter',
             'id_page', 'category', 'datefrom', 'dateto', 'question', 'sticky',
        'filter'
    );
}

/**
 * AmiMultifeeds/FAQ configuration admin form component action controller.
 *
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Faq_FormAdm extends Hyper_AmiMultifeeds_FormAdm{
    /**
     * Save action dispatcher.
     *
     * @param  array &$aEvent  Event data
     * @return void
     */
    protected function _save(array &$aEvent){
        /**
         * @var AMI_RequestHTTP
         */
        $oRequest = AMI::getSingleton('env/request');

        parent::_save($aEvent);

        $id = $oRequest->get('applied_id', false) ? $oRequest->get('applied_id') : $oRequest->get('id');
        $oModelItem = AMI::getResourceModel($aEvent['tableModelId'])->find($id, array('id', 'email', 'answer'))->load();
        if(
            $oModelItem &&
            $oRequest->get('send', false) &&
            mb_strlen($oModelItem->email) &&
            mb_strlen($oModelItem->answer)
        ){
            /**
             * @var FAQ_EmailView
             */
            $oView = AMI::getResource($this->getModId() . '/mail/view', array($this->getModId()));

            $aScope = removeSpecial($oModelItem->getData());
            $aScope['cat_name'] = isset($aScope['cat_header']) ? $aScope['cat_header'] : '';

            $aScope += array(
                'company_name' =>  AMI::getOption('core', 'company_name'),
                'company_url'  =>  AMI::getOption('core', 'company_url')
            );

            if($aScope['public']){
                $aScope['faq_full_link'] = $oModelItem->getFullURL();
            }
            $oView->setScope($aScope);

            $oMail = new Mailer();
            $oMail->SenderAddress = AMI::getOption($this->getModId(), 'faq_email');
            $oMail->SenderName = AMI::getOption('core', 'company_name');
            $oMail->RecipientAddress = $oModelItem->email;
            $oMail->RecipientName = $oModelItem->author;
            $oMail->Subject = $oView->getPart('subject');
            $oMail->BodyFormat = 'html';
            $oMail->Body = $oView->getPart('answer');
            $oMail->Prepare();
            if(!$oMail->Send()){
                trigger_error('FAQ [' . $this->getModId() . ']: sending email failed...', E_USER_WARNING);
            }
        }
    }
}

/**
 * AmiMultifeeds/FAQ configuration form component view.
 *
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Faq_FormViewAdm extends Hyper_AmiMultifeeds_FormViewAdm{
    /**
     * Initialize fields.
     *
     * @see    AMI_View::init()
     * @return AMI_View
     */
    public function init(){
        $this->addField(array('name' => 'date_created', 'type' => 'date'));
        $this->addField(array('name' => 'author', 'position' => 'date_created.after', 'validate' => array('filled', 'stop_on_error')));
        $this->addField(array('name' => 'email', 'position' => 'author.after', 'validate' => array('email', 'stop_on_error')));
        $this->addField(array('name' => 'send', 'type' => 'checkbox')); // always checked
        parent::init();
        $this
            ->dropField('header');

        return $this;
    }
}

/**
 * AmiMultifeeds/FAQ configuration admin list component action controller.
 *
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Faq_ListAdm extends Hyper_AmiMultifeeds_ListAdm{
}

/**
 * AmiMultifeeds/FAQ configuration admin list component view.
 *
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Faq_ListViewAdm extends Hyper_AmiMultifeeds_ListViewAdm{
    /**
     * Init columns.
     *
     * @see    AMI_View::init()
     * @return AMI_Module_ListViewAdm
     */
    public function init(){
        parent::init();
        $this
            ->removeColumn('header')
            ->removeColumn('announce')
            ->addColumn('author')
            ->addColumn('question')
            ->addColumnType('email', 'hidden')
            ->addColumnType('is_answered', 'hidden')
            ->setColumnTensility('question')
            ->setColumnTensility('cat_header')
            ->addColumnType('date_created', 'date')
            ->addSortColumns(array('question', 'author'));

        // Format 'date_created' column in local date format
        $this->formatColumn(
            'date_created',
            array($this, 'fmtDateTime'),
            array(
                'format' => AMI_Lib_Date::FMT_DATE
            )
        );
        // Truncate 'question' column by 250 symbols
        $this->formatColumn(
            'question',
            array($this, 'fmtTruncate'),
            array(
                'length'       => 145,
                'doStripTags'  => true,
                'doHTMLEncode' => false
            )
        );
        // Truncate 'author' column by 35 symbols
        $this->formatColumn(
            'author',
            array($this, 'fmtTruncate'),
            array(
                'length'       => 45,
                'doStripTags'  => true,
                'doHTMLEncode' => false
            )
        );
        $this->formatColumn(
            'author',
            array($this, 'fmtAuthor')
        );

        $this->addScriptFile('_admin/' . $GLOBALS['CURRENT_SKIN_PATH'] . '_js/faq_list.js');

        return $this;
    }

    /**
     * Common column formatter.
     *
     * Appends mailto link if author email is present.
     *
     * @param  mixed $value  Value to format
     * @param  array $aArgs  Arguments
     * @return mixed
     * @see    AMI_ModListView::handleFormatCell()
     * @todo   Use templater
     */
    protected function fmtAuthor($value, array $aArgs){
        // hack
        return $aArgs['oItem']->email == '' ? $value : ('<a href="mailto:' . $aArgs['oItem']->email . '">' . $value . ' &raquo;</a>');
    }
}

/**
 * Module model.
 *
 * @package Config_AmiMultifeeds_FAQ
 * @amidev  Temporary
 */
class AmiMultifeeds_Faq_State extends Hyper_AmiMultifeeds_State{
}

/**
 * AmiMultifeeds/FAQ configuration admin list actions controller.
 *
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage Controller
 * @resource   {$modId}/list_actions/controller/adm <code>AMI::getResource('{$modId}/list_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Faq_ListActionsAdm extends Hyper_AmiMultifeeds_ListActionsAdm{
}

/**
 * AmiMultifeeds/FAQ configuration admin list group actions controller.
 *
 * @package    Config_AmiMultifeeds_FAQ
 * @subpackage Controller
 * @resource   {$modId}/list_group_actions/controller/adm <code>AMI::getResource('{$modId}/list_group_actions/controller/adm')*</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiMultifeeds_Faq_ListGroupActionsAdm extends Hyper_AmiMultifeeds_ListGroupActionsAdm{
}
