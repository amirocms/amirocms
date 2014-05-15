<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Extension
 * @version   $Id: AMI_ExtView.php 43147 2013-11-06 11:52:25Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Extension view interface.
 *
 * @package    Extension
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
interface AMI_iExtView{
    /**
     * Sets extension object.
     *
     * @param  AMI_Ext $oExt  Extension module controller object
     * @return void
     */
    public function setExt(AMI_Ext $oExt);
}

/**
 * Module extension base view.
 *
 * @package    Extension
 * @subpackage View
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class AMI_ExtView extends AMI_View implements AMI_iExtView{
    /**
     * Extension module controller object
     *
     * @var AMI_Ext
     */
    protected $oExt = NULL;

    /**
     * Sets extension object.
     *
     * @param  AMI_Ext $oExt  Extension module controller object
     * @return void
     */
    public function setExt(AMI_Ext $oExt){
        $this->oExt = $oExt;
        $extId = $oExt->getExtId();
        $this->setDefaultExtTemplates($extId);
        /*
        // Commented by #CMS-11470
        if($extId === 'ext_image'){
            $extId = 'ext_images';
        }
        $var1 = 'EXTENSION_' . mb_strtoupper(mb_substr($extId, 4));
        $var2 = mb_strtoupper($extId);
        $this->getTemplate()->addGlobalVars(
            array(
                $var1 => '1',
                $var2 => '1'
            )
        );
        */
    }

    /**
     * Returns view data.
     *
     * @return string
     */
    public function get(){
        return '';
    }

    /**
     * Set template names according to ext id.
     *
     * @param  string $name  Extension id or template name
     * @return void
     */
    protected function setDefaultExtTemplates($name){
        $tplPath = AMI_Registry::get('side') == 'frn' ? AMI_iTemplate::TPL_MOD_PATH : AMI_iTemplate::LOCAL_TPL_MOD_PATH;
        $lngPath = AMI_Registry::get('side') == 'frn' ? AMI_iTemplate::LNG_MOD_PATH : AMI_iTemplate::LOCAL_LNG_MOD_PATH;
        if($this->tplFileName === ''){
            $this->tplFileName = $tplPath . '/' . $name . '.tpl';
        }
        if($this->tplBlockName === ''){
            $this->tplBlockName = $name;
        }
        if($this->localeFileName === ''){
            $this->localeFileName = $lngPath . '/' . $name . '.lng';
        }
    }
}
