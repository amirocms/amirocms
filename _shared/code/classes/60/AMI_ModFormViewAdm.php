<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModFormViewAdm.php 45362 2013-12-16 09:13:41Z Kolesnikov Artem $
 * @since     5.12.0
 */

/**
 * Module admin form component view abstraction.
 *
 * @package    ModuleComponent
 * @subpackage View
 * @see        AmiSample_FormViewAdm
 * @since      5.12.0
 */
class AMI_ModFormViewAdm extends AMI_ModFormView{
    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        $html = parent::get();
        /**
         * AMI_Response
         */
        $oResponse = AMI::getSingleton('response');
        if($oResponse->getType() == 'JSON'){
            /**
             * @var AMI_RequestHTTP
             */
            $oRequest = AMI::getSingleton('env/request');
            return array(
                'id'        => is_object($this->oItem) ? $this->oItem->getId() : '',
                'appliedId' => $oRequest->get('applied_id'),
                'htmlCode'  => $html
            );
        }else{
            return $html;
        }
    }

    /**
     * Adds Options and SEO tab on the form.
     *
     * @param string $subItemsModId  Subitems module id
     * @return AMI_ModFormViewAdm
     * @since 6.0.4
     */
    protected function addOptionsTab($subItemsModId = false){
        $this->addTab('options_tab', 'default_tabset');
        $this->addField(array('name' => 'sublink'));
        $this->addField(array('name' => 'html_title', 'skipHTMLEncoding' => true));
        $this->addField(array('name' => 'html_keywords', 'type' => 'textarea', 'cols' => 40, 'rows' => 4));
        $this->addField(array('name' => 'is_kw_manual', 'type' => 'hidden'));
        $this->addField(array('name' => 'html_description', 'type' => 'textarea', 'cols' => 40, 'rows' => 4));
        if(strpos($this->getModId(), 'inst_') === 0){
            $this->addField(array('name' => 'og_image'), 'html_description.after');
        }
        $this->addField(array('name' => 'details_noindex', 'type' => 'checkbox'));

        // sticky, hide_in_list
        if(AMI::issetAndTrueProperty($this->getModId(), "use_special_list_view") || ($subItemsModId && AMI::issetAndTrueProperty($subItemsModId, 'use_special_list_view'))){
            $this->putPlaceholder('sticky_fields', 'options_tab.begin');
            $this->addField(array('name' => 'hide_in_list', 'type' => 'checkbox', 'default_checked' => false, 'position' => 'sticky_fields.begin'));
            $this->addField(array('name' => 'sticky', 'type' => 'checkbox', 'default_checked' => false, 'position' => 'hide_in_list.after'));
            $this->addField(array('name' => 'date_sticky_till', 'type' => 'date', 'position' => 'sticky.after', 'validate' => array('date')));
        }
        return $this;
    }
}
