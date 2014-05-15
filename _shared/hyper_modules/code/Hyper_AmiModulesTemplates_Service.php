<?php
/**
 * AmiModulesTemplates hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiModulesTemplates
 * @version   $Id: Hyper_AmiModulesTemplates_Service.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiModulesTemplates hypermodule service class.
 *
 * @package    Hyper_AmiModulesTemplates
 * @subpackage Service
 * @since      x.x.x
 * @amidev     Temporary
 */
abstract class Hyper_AmiModulesTemplates_Service{

    /**
     * Disk templates data
     *
     * @var array
     */
    protected static $aDiskTemplates = null;

    /**
     * Sets templates permissions.
     *
     * @return void
     */
    public static function setTemplatesPermissions(){
        if(isset($GLOBALS['Core']) && ($GLOBALS['Core'] instanceof CMS_Core)){
            $oCore = $GLOBALS['Core'];
        }else{
            trigger_error("Full entry point required!", E_USER_ERROR);
        }

        $modId = self::getModId();
        $oModel = AMI::getResourceModel($modId . '/table');
        $oDB = AMI::getSingleton('db');
        $tableName = $oModel->getTableName();
        // Set all permissions to 0
        $oDB->query(
            DB_Query::getSnippet("UPDATE `%s` SET allowed=%s, content_type=%s, parsed=%s")
            ->plain($tableName)
            ->plain(0)
            ->plain(0)
            ->q('')
        );
        // Allow common templates
        $oDB->query(
            DB_Query::getSnippet("UPDATE `%s` SET allowed=%s WHERE module_owner=%s and module=%s")
            ->plain($tableName)
            ->plain(1)
            ->q('')
            ->q('')
        );

        // Get installed owners
        $allowedOwners = array();
        $vOwners = $oCore->GetOwnersList();
        foreach($vOwners as $name => $vOwner){
            if($oCore->IsOwnerInstalled($name)){
                $allowedOwners[] = $name;
            }
        }
        if(!in_array('pmanager', $allowedOwners)){
            $allowedOwners[] = 'pmanager';
        }
        // For each owner get installed modules
        if(sizeof($allowedOwners) > 0){
            $oDB->query(
                DB_Query::getSnippet("UPDATE `%s` SET allowed=%s WHERE module like %s and module_owner in (%s)")
                ->plain($tableName)
                ->plain(1)
                ->q('!%')
                ->implode($allowedOwners)
            );

            $allowedModules = array();
            $oList =
                $oModel
                ->getList()
                ->addColumns(array('module_owner'))
                ->addExpressionColumn('module', 'distinct module')
                ->addWhereDef(
                    DB_Query::getSnippet(' AND module_owner IN (%s)')
                    ->implode($allowedOwners)
                )
                ->load();
            foreach($oList as $oItem){
                if(!empty($oItem->module) && ($oItem->module_owner == 'pmanager' || $oCore->IsInstalled($oItem->module))){
                    $allowedModules[] = $oItem->module;
                }
            }
        }
        // Allow to use templates for installed modules
        if(sizeof($allowedModules) > 0){
            $oDB->query(
                DB_Query::getSnippet("UPDATE `%s` SET allowed=%s WHERE module in (%s) and module_owner in (%s)")
                ->plain($tableName)
                ->plain(1)
                ->implode($allowedModules)
                ->implode($allowedOwners)
            );
        }
    }

    /**
     * Drops all parsed data.
     *
     * @return void
     */
    public static function dropParsedData(){
        $modId = self::getModId();
        $oModel = AMI::getResourceModel($modId . '/table');
        $bIsLangs = ($modId == 'modules_templates_langs');
        AMI::getSingleton('db')
            ->query(
                DB_Query::getSnippet("UPDATE `%s` SET content_type=%s, parsed=%s")
                ->plain($oModel->getTableName())
                ->plain(0)
                ->q('')
            );
        if($bIsLangs){
            AMI::getSingleton('db')
            ->query(
                DB_Query::getSnippet("UPDATE `cms_modules_templates` SET content_type=%s, parsed=%s")
                ->plain(0)
                ->q('')
            );
        }
    }

    /**
     * Get list of modules as localized "owner : module".
     *
     * @param bool $bNoCommon  Do not add all and common modules
     * @return array
     */
    public static function getModulesList($bNoCommon = false){
        $aData = array();
        $thisGetModId = self::getModId();
        $aModuleNames = AMI::getSingleton('env/template_sys')->parseLocale('templates/lang/_menu_all.lng');
        $aOwnersNames = AMI::getSingleton('env/template_sys')->parseLocale('templates/lang/_menu_owners.lng');
        $aLocale = AMI::getSingleton('env/template_sys')->parseLocale(AMI_iTemplate::LOCAL_LNG_MOD_PATH . '/' . $thisGetModId . '_filter.lng');
        $oList =
            AMI::getResourceModel($thisGetModId . '/table')
                ->getList()
                ->addColumns(array('module_owner', 'module'))
                ->addWhereDef(
                    DB_Query::getSnippet('AND allowed=1 AND module_owner != %s')
                    ->q('')
                )
                ->addGrouping('module_owner, module')
                ->addOrder('module_owner, module')
                ->load();

        $aAddedOwners = array();
        $aAdded = array();
        $aFirstElements = array();
        if(!$bNoCommon){
            $aFirstElements += array(
                '._all'     => '_all',
            );
        }
        $aFirstElements += array(
            '._common'  => '_common',
        );
        $oDeclarator = AMI_ModDeclarator::getInstance();
        foreach($oList as $oItem){
            // skip items that contains unparsed data in module or owner name
            if((strpos($oItem->module_owner, '##') !== false) || (strpos($oItem->module, '##') !== false)){
                continue;
            }
            $owner = (isset($aOwnersNames[$oItem->module_owner])) ? $aOwnersNames[$oItem->module_owner] : $oItem->module_owner;
            if(!$bNoCommon && !isset($aAddedOwners[$oItem->module_owner])){
                $aAddedOwners[$oItem->module_owner] = true;
                $aAdded[$oItem->module_owner . '._all'] = $owner . ' : ' . '_all';
            }
            if($oItem->module && (mb_strpos($oItem->module, '!') === false)){
                if(strpos($oItem->module, '-') !== false){
                    continue;
                }
                $module = isset($aModuleNames[$oItem->module]) ? $aModuleNames[$oItem->module] : $oItem->module;
                $parent = $oDeclarator->getParent($oItem->module);
                if($parent && isset($aModuleNames[$parent])){
                    $parent = $aModuleNames[$parent];
                }
                $aModNames = AMI_Service_Adm::getModulesCaptions(array($oItem->module), TRUE, array(), TRUE);
                if(strlen($aModNames[$oItem->module]) == 0){
                    continue;
                }
                $aAdded[$oItem->module_owner . '.' . $oItem->module] =
                    $owner . ' : ' . $aModNames[$oItem->module];
            }elseif(mb_strpos($oItem->module, '!') === 0){
                $aAdded[$oItem->module_owner . '.' .  $oItem->module] = $owner . ' : ' . '_common';
            }
        }

        asort($aAdded);
        $aAdded = array_merge($aFirstElements, $aAdded);
        foreach($aAdded as $modId => $modName){
            if(!$bNoCommon && (mb_strpos($modName, '_all') !== false) || (mb_strpos($modName, '_common') !== false)){
                $modName = str_replace('_all', $aLocale['_all'], $modName);
                $modName = str_replace('_common', $aLocale['_common'], $modName);
            }
            $aData[] = array(
                'name'    => $modName,
                'value'   => $modId
            );
        }
        return $aData;
    }

    /**
     * Get template paths.
     *
     * @return array
     */
    public static function getDirectoriesList(){
        $aData = array();
        $modId = self::getModId();
        $oList =
            AMI::getResourceModel($modId . '/table')
                ->getList()
                ->addExpressionColumn('dpath', 'distinct path')
                ->addWhereDef(
                    DB_Query::getSnippet("AND allowed=1 and side <> %s")
                    ->q('admin')
                )
                ->addOrder('path')
                ->load();
        foreach($oList as $oItem){
            $aData[] = array(
                'name' => $oItem->dpath,
                'value' => $oItem->dpath
            );
        }
        return $aData;
    }

    /**
     * Gets list of modified templates into aDiskTemplates property.
     *
     * @param bool $bForce  Force modification date reread
     * @return array
     */
    public static function getModifiedTemplates($bForce = false){
        if(is_null(self::$aDiskTemplates) || $bForce){
            self::$aDiskTemplates = array();
            $modId = self::getModId();
            $dbTemplates = array();
            $oList =
                AMI::getResourceModel($modId . '/table')
                    ->getList()
                    ->addColumns(
                        array('id', 'path', 'header')
                    )
                    ->addExpressionColumn('modified_tm', 'unix_timestamp(synchronized)')
                    ->addWhereDef(DB_Query::getSnippet("AND side != %s")->q('admin'))
                    ->load();
            foreach($oList as $oItem){
                $dbTemplates[$oItem->path . $oItem->header] = array($oItem->id, $oItem->modified_tm);
            }

            // 2. Get templates from disk and compare datetime
            clearstatcache();
            self::_rGetModifiedTemplates($dbTemplates, "templates/");
            self::_rGetModifiedTemplates($dbTemplates, "_local/_admin/templates/");
        }
        return self::$aDiskTemplates;
    }

    /**
     * Returns template file extension.
     *
     * @return string
     */
    public static function getFileExtension(){
        return (self::getModId() == 'modules_templates') ? 'tpl' : 'lng';
    }

    /**
     * Get current module id where it was not set in the class properties.
     *
     * @return string
     */
    public static function getModId(){
        return AMI_Registry::get('modId', false);
    }

    /**
     * Reads modified templates.
     *
     * @param array &$dbTemplates  List of templates from DB
     * @param string $filesPath    Current file path
     * @return void
     */
    protected static function _rGetModifiedTemplates(array &$dbTemplates, $filesPath){
        $extension = self::getFileExtension();
        if(is_dir($GLOBALS["ROOT_PATH"] . $filesPath)){
            if($handle = opendir($GLOBALS["ROOT_PATH"].$filesPath)){
                while(($file = readdir($handle)) !== false){
                    if($file == "." || $file == ".." || $file == ".svn")
                        continue;
                    else if(is_dir($GLOBALS["ROOT_PATH"] . $filesPath . $file)){
                        self::_rGetModifiedTemplates($dbTemplates, $filesPath . $file . "/");
                    }else{
                        if(preg_match('/\.'.$extension.'$/si', $file, $matches)){
                            if(file_exists($GLOBALS["ROOT_PATH"] . $filesPath . $file)){
                                $fileTime = filemtime($GLOBALS["ROOT_PATH"] . $filesPath . $file);
                                if(isset($dbTemplates[$filesPath . $file]) && $dbTemplates[$filesPath . $file][1] < $fileTime){
                                    self::$aDiskTemplates["modified"][$filesPath . $file] = $fileTime;
                                    self::$aDiskTemplates["modified_ids"][] = $dbTemplates[$filesPath . $file][0];
                                }else if(isset($dbTemplates[$filesPath.$file]) && $dbTemplates[$filesPath . $file][1] > $fileTime){
                                    self::$aDiskTemplates["old"][$filesPath . $file] = $fileTime;
                                }else if(!isset($dbTemplates[$filesPath . $file])){
                                    self::$aDiskTemplates["new"][$filesPath . $file] = $fileTime;
                                }
                            }
                        }
                    }
                }
                closedir($handle);
            }
        }
    }
}
