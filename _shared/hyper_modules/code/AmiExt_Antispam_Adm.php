<?php
/**
 * AmiExt/Antispam extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiExt_Antispam
 * @version   $Id: AmiExt_Antispam_Adm.php 40881 2013-08-19 14:09:34Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/Antispam extension configuration front controller.
 *
 * @package    Config_AmiExt_Antispam
 * @subpackage Controller
 * @resource   ext_twist_prevention/module/controller/adm <code>AMI::getResource('ext_twist_prevention/module/controller/adm')</code>
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_Antispam_Adm extends Hyper_AmiExt{
    /**
     * Callback called after module is uninstalled.
     *
     * Cleans up uninstalled module data.
     *
     * @param  string         $modId  Unnstalled module id
     * @param  AMI_Tx_Cmd_Args $oArgs  Transaction command arguments
     * @return void
     */
    public function onModPostUninstall($modId, AMI_Tx_Cmd_Args $oArgs){
        $oDB = AMI::getSingleton('db');
        $oQuery =
            DB_Query::getSnippet(
                "DELETE FROM `cms_twist_prevention` " .
                "WHERE `ext_module` = %s"
            )->q($modId);
        $oDB->query($oQuery);
    }

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
        return $aEvent;
    }
}
