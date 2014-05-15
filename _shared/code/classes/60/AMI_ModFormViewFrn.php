<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   ModuleComponent
 * @version   $Id: AMI_ModFormViewFrn.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
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
class AMI_ModFormViewFrn extends AMI_ModFormView{
    /**
     * Template file name
     *
     * @var string
     */
    protected $tplFileName = 'templates/modules/_form.tpl';

    /**
     * Template block name
     *
     * @var string
     */
    protected $tplBlockName = 'admin_form';

    /**
     * Locale file name
     *
     * @var string
     */
    protected $localeFileName = 'templates/lang/modules/_form.lng';

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
                'id'        => $this->oItem->getId(),
                'appliedId' => $oRequest->get('applied_id'),
                'htmlCode'  => $html
            );
        }else{
            return $html;
        }
    }
}
