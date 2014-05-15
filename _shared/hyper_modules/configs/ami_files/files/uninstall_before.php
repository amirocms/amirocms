<?php
/**
 * AmiFiles/Files configuration.
 *
 * Script executing before deinstallation start.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiFiles_Files
 * @version   $Id: uninstall_before.php 40881 2013-08-19 14:09:34Z Leontiev Anton $
 * @since     5.14.4
 */

if(!class_exists('AmiFiles_Files_OnUninstall', FALSE)){
    /**
     * AmiFiles/Files on uninstall controller.
     *
     * @package    Config_AmiFiles_Files
     * @subpackage Controller
     * @since      5.14.4
     */
    class AmiFiles_Files_OnUninstall{
        /**
         * Constructor.
         */
        public function __construct(){
            AMI_Event::addHandler('on_tx_command', array($this, 'onTxCommand'), AMI_Event::MOD_ANY);
        }

        /**
         * Transaction command handler.
         *
         * Discards deleting db tables on module uninstall.
         *
         * @param  string $name          Event name
         * @param  array  $aEvent        Event data
         * @param  string $handlerModId  Handler module id
         * @param  string $srcModId      Source module id
         * @return array
         * @see    AMI_Tx::run()
         */
        public function onTxCommand($name, array $aEvent, $handlerModId, $srcModId){
            if(
                get_class($aEvent['oTx_Cmd']) === 'AMI_Tx_Cmd_DB_DropTable' &&
                is_array($aEvent['caller']) && is_object($aEvent['caller'][0]) &&
                get_class($aEvent['caller'][0]) === 'AMI_Tx_ModUninstall' &&
                $aEvent['caller'][1] === 'uninstallDB'
            ){
                $oDeclarator = AMI_ModDeclarator::getInstance();
                list($hyper, $config) = $oDeclarator->getHyperData($aEvent['oArgs']->modId);
                if((sizeof($oDeclarator->getRegistered($hyper, $config)) >> 1) > 1){
                    $aEvent['_discard'] = TRUE;
                }
            }
            return $aEvent;
        }
    }

    new AmiFiles_Files_OnUninstall;
}
