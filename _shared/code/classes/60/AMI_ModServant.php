<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Module
 * @version   $Id: AMI_ModServant.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * Module service interface.
 *
 * @package    Module
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
interface AMI_iModServant{
    /**
     * Initialize service.
     *
     * @param  AMI_Mod $oController  Module controller
     * @return AMI_iModServant
     */
    public function init(AMI_Mod $oController);

    /**
     * Calls on module controller constructor starts.
     *
     * @return AMI_iModServant
     */
    public function onModConstructorStart();

    /**
     * Calls on module controller constructor ends.
     *
     * @return AMI_iModServant
     */
    public function onModConstructorEnd();
}

/**
 * Module service abstract class.
 *
 * @package    Module
 * @subpackage Controller
 * @see        AMI_Module_Adm::__construct()
 * @see        EshopItem_Serve_EshopOrder
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class AMI_ModServant implements AMI_iModServant{
    /**
     * Module controller
     *
     * @var AMI_Mod
     */
    protected $oController;

    /**
     * Module resource id
     *
     * @var string
     */
    protected $modId;

    /**
     * Serve for module resource id
     *
     * @var string
     */
    protected $serveModId;

    /**
     * Initialize service.
     *
     * @param  AMI_Mod $oController  Module controller
     * @return AMI_ModServant
     */
    public function init(AMI_Mod $oController){
        $this->oController = $oController;
        $class = get_class($this);
        $this->modId = AMI::getModId($class);
        $aTmp = explode('_', $class);
        $this->serveModId = AMI::getModId(array_pop($aTmp));
        return $this;
    }
}
