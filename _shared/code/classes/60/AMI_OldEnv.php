<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Service
 * @version   $Id: AMI.php 41609 2013-09-18 07:20:11Z Leontiev Anton $
 * @amidev
 */

/**
 * Old environment wrapper.
 *
 * @package Service
 * @static
 * @amidev
 */
final class AMI_OldEnv{
    /**
     * Returns admin form HTML.
     *
     * @param  string $modId  Module Id
     * @return string|NULL
     */
    public static function getAdmForm($modId){
        global $cms;

        $aDebug = $cms->Gui->debug;
        $cms->Gui->delDebug('all');
        $oEngine = self::initEngine($modId);
        $oEngine->options['admin_views'] = array('form');
        $aData = array();
        $action = isset($cms->Vars['action']) ? $cms->Vars['action'] : 'none';
        $id = isset($cms->Vars['id']) ? (int)$cms->Vars['id'] : '0';
        $oEngine->ProcessAction($action, $id);
        $aHTML = $oEngine->GetHtml($aData);
        $cms->Gui->debug = $aDebug;

        return trim($aHTML['form']);
    }

    /**
     * Processes action in old environment.
     *
     * @param  string $modId  Module Id
     * @return bool
     */
    public static function processAction($modId){
        global $cms;

        $aDebug = $cms->Gui->debug;
        $cms->Gui->delDebug('all');
        $oEngine = self::initEngine($modId);
        $action = isset($cms->Vars['action']) ? $cms->Vars['action'] : 'none';
        $id = isset($cms->Vars['id']) ? (int)$cms->Vars['id'] : '0';
        $oEngine->ProcessAction($action, $id);
        $cms->Gui->debug = $aDebug;

        return true;
    }

    /**
     * Initializes module engine.
     *
     * @param  string $modId  Module Id
     * @return CMS_ActModule
     * @amidev
     */
    protected static function initEngine($modId){
        global $cms, $db;

        if(AMI_Registry::get('side') !== 'adm'){
            trigger_error('AMI_OldEnv::initEngine() must be called from admin side only', E_USER_WARNING);
            return;
        }

        if(empty($cms) || !is_object($cms) || !($cms instanceof CMS_Base)){
            trigger_error('AMI_OldEnv::initEngine() must be called using full environment', E_USER_WARNING);
            return;
        }        

        $aClasses = AMI::getProperty($modId, 'engine_classes');
        if(!is_array($aClasses)){
            $aClasses = array($aClasses);
        }
        $class = array_pop($aClasses);
        if(empty($class)){
            trigger_error("'engine_classes' property not found for module '" . $modId . "'", E_USER_WARNING);
            return;
        }

        $aEvent = array(
            'modId'    => $modId,
            'aRequest' => &$cms->Vars,
            'aGet'     => &$cms->VarsGet,
            'aPost'    => &$cms->VarsPost,
            'aCookie'  => &$cms->VarsCookie
        );
        AMI_Event::fire('v5_on_adm_form_requested', $aEvent, AMI_Event::MOD_ANY);

        $oModule = $cms->Core->getModule($modId);
        $oEngine = new $class($cms, $db, $oModule);
        $oEngine->Init(
            array(
                $modId . '_subform' => 'templates/' . $modId . '_form.tpl',
                $modId . '_form'    => 'templates/form.tpl'
            ),
            NULL, // messages locales
            'templates/lang/' . $modId . '.lng'
        );
        return $oEngine;
    }
}
