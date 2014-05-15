<?php
/**
 * AmiFiles/Files configuration.
 *
 * Script executing before installation start.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiFiles_Files
 * @version   $Id: install_before.php 40881 2013-08-19 14:09:34Z Leontiev Anton $
 * @since     5.14.4
 */

if(!class_exists('AmiFiles_Files_OnInstall', FALSE)){
    /**
     * AmiFiles/Files on install controller.
     *
     * @package    Config_AmiFiles_Files
     * @subpackage Controller
     * @since      5.14.4
     */
    class AmiFiles_Files_OnInstall{
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
                get_class($aEvent['oTx_Cmd']) === 'AMI_Tx_Cmd_DB_CreateTable' &&
                is_array($aEvent['caller']) && is_object($aEvent['caller'][0]) &&
                get_class($aEvent['caller'][0]) === 'AMI_Tx_ModInstall' &&
                $aEvent['caller'][1] === 'installDB' &&
                !($aEvent['oArgs']->mode & (AMI_iTx_Cmd::MODE_APPEND | AMI_iTx_Cmd::MODE_OVERWRITE))
            ){
                /**
                 * @var AMI_Tx_Cmd_Args
                 */
                $aEvent['oArgs']->overwrite('mode', AMI_iTx_Cmd::MODE_APPEND);
            }
            return $aEvent;
        }
    }

    new AmiFiles_Files_OnInstall;
}
