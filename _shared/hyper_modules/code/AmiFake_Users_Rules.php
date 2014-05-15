<?php
/**
 * AmiFake/Users configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiFake_Users
 * @version   $Id: AmiFake_Users_Rules.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiFake/Users configuration rules.
 *
 * @package    Config_AmiFake_Users
 * @subpackage Controller
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiFake_Users_Rules extends AMI_ModRules{
    /**
     * Handler for ##modId##:ruleExtensionsCB callback.
     *
     * @param mixed $callbackData   Data for callback
     * @param mixed &$optionsData    Options data
     * @param string $callbackMode  Callback mode
     * @param mixed &$result         Result data
     * @param array &$aData          Data
     * @return bool
     */
    public function ruleDisplayModulesCB($callbackData, &$optionsData, $callbackMode, &$result, array &$aData){
    	// ruleDisplayModulesCB($cData, $cOptionsData, &$res, &$aData, $cMode = 'getallvalues'){
        static $aModules = null;

        switch($callbackMode){
            case 'getallvalues':
                $oTpl = AMI::getSingleton('env/template_sys');
                $aLocale =
                    $oTpl->parseLocale('templates/lang/_menu_all.lng') +
                    $oTpl->parseLocale(AMI_iTemplate::LNG_MOD_PATH . '/_common.lng');
                if(is_null($aModules)){
                    $aModules = array();
                    // Load possible modules captions
                    $oDeclarator = AMI_ModDeclarator::getInstance();
                    foreach(AMI::getProperty('members', 'possible_modules_list') as $modId){
                        if(isset($aLocale[$modId])){
                            $caption = $aLocale[$modId];
                            $parentModId = $oDeclarator->getParent($modId);
                            if(!is_null($parentModId)){
                                $parentCaption = isset($aLocale[$parentModId]) ? $aLocale[$parentModId] : $aLocale['undefined'];
                                $caption = $parentCaption . ' : ' . $caption;
                            }
                        }else{
                            $caption = $aLocale['undefined'];
                        }
                        $aModules[$modId] = $caption;
                    }
                }
                $result = array(array(
                        'name'     => 'all',
                        'caption'  => $aLocale['all'],
                        'selected' => (int)(in_array('all', $callbackData['value']))
                ));
                foreach($aModules as $modId => $caption){
                    $result[] = array(
                        'name'     => $modId,
                        'caption'  => $caption,
                        'selected' => (int)(in_array($modId, $callbackData['value']))
                    );
                }
                break;
            case 'correctvalue':
                break;
            case 'getvalue':
                $result = $callbackData['value'];
                break;
        }
        return TRUE;
    }
}
