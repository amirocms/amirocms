<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Module
 * @version   $Id: AMI_ModSettings.php 50137 2014-04-21 12:17:43Z Leontiev Anton $
 * @since     6.0.2
 */

/**
 * Module setting manipulation class.
 *
 * @package Module
 * @static
 * @since   6.0.2
 */
final class AMI_ModSettings{
    /**
     * Module options, keys are module Ids, values are options
     *
     * @var array
     */
    private static $aOptions =
        array(
            'standalone' => array(),
            'modules'    => array()
        );

    /**
     * Loads and returns module options/option.
     *
     * @param  string $modId       Module Id
     * @param  string $name        Option name, if empty string passed, all options
     *                             will be returned
     * @param  bool   $standalone  Flag specifying to load stanalone option
     * @param  bool   $autofree    Flag specifying to free options cache before returning value
     * @param  bool   $forceLoad   Flag specifying to force load value from DB (since 6.0.6)
     * @return mixed  If module or option not found, null will be returned
     */
    public static function getOptions($modId, $name = '', $standalone = FALSE, $autofree = FALSE, $forceLoad = FALSE){
        $name = mb_strtolower($name);
        $result = null;
        $source = $standalone ? 'standalone' : 'modules';

        if($standalone){
            if(array_key_exists($modId . '|' . $name, self::$aOptions[$source])){
                $result = self::$aOptions[$source][$modId . '|' . $name];
            }else{
                $oDB = AMI::getSingleton('db');
                $oQuery =
                    DB_Query::getSnippet(
                        "SELECT `value`, `big_value` " .
                        "FROM `cms_options` " .
                        "WHERE `module_name` = %s AND `name` = %s"
                    )
                    ->q($modId)
                    ->q($name);
                $aRow = $oDB->fetchRow($oQuery);
                if($aRow){
                    $result =
                        $aRow['big_value']
                        ? unserialize($aRow['big_value'])
                        : $aRow['value'];
                }
                self::$aOptions[$source][$modId . '|' . $name] = $result;
                $modId .= ('|' . $name);
            }
        }else{ // if($standalone){
            global $Core;

            $useCore = isset($Core) && ($Core instanceof CMS_Core);
            if(!$forceLoad && $useCore){
                $result = $Core->GetModOption($modId, $name);
            }else{
                if(!array_key_exists($name, self::$aOptions[$source])){
                    $oDB = AMI::getSingleton('db');
                    $oQuery =
                        DB_Query::getSnippet(
                            "SELECT `big_value` " .
                            "FROM `cms_options` " .
                            "WHERE `module_name` = %s AND `name` = %s"
                        )
                        ->q($modId)
                        ->q('options_dump');
                    $aRow = $oDB->fetchRow($oQuery);
                    if($aRow){
                        $aOptions = unserialize($aRow['big_value']);
                        self::$aOptions[$source][$modId] = $aOptions['Options'];
                    }else{
                        trigger_error("Undeclared module '" . $modId . "'", E_USER_WARNING);
                    }
                }
                if(is_array(self::$aOptions[$source][$modId])){
                    $result =
                        $name !== ''
                            ? self::$aOptions[$source][$modId][$name]
                            : self::$aOptions[$source][$modId];
                }
            }
        }

        if($autofree){
            self::freeOptions($modId, $source);
        }

        return $result;
    }

    /**
     * Free options cache to optimize memory usage.
     *
     * @param  string $modId   Module Id
     * @param  string $source  'standalone' | 'modules'
     * @return void
     */
    public static function freeOptions($modId, $source = 'modules'){
        unset(self::$aOptions[$source][$modId]);
    }

    /**
     * Saves standalone option.
     *
     * @param  string $modId            Module Id
     * @param  string $name             Option name
     * @param  mixed  $value            Option value, NULL to delete option
     * @param  int    $forceShortValue  Will be described later
     * @return void
     * @since  6.0.6
     * @todo   Describe $forceShortValue parameter
     */
    public static function saveStandaloneOption($modId, $name, $value, $forceShortValue = NULL){
        $oDB = AMI::getSingleton('db');

        if(is_null($value)){
            $oQuery =
                DB_Query::getSnippet(
                    "DELETE FROM `cms_options` " .
                    "WHERE " .
                        "`module_name` = %s AND " .
                        "`name` = %s "
                )
                ->q($modId)
                ->q($name);
            $oDB->query($oQuery);

            return;
        }

        $aRecord = array(
            'module_name'   => DB_Query::getSnippet('%s')->q($modId),
            'name'          => DB_Query::getSnippet('%s')->q($name),
            'date_modified' => 'NOW()'
        );
        if(
            (is_string($value) || is_numeric($value)) &&
            mb_strlen($value, 'ASCII') <= 255
        ){
            $aRecord += array(
                'value' => DB_Query::getSnippet('%s')->q($value)
            );
        }else{
            $value = serialize($value);
            $aRecord += array(
                'value'     => is_null($forceShortValue) ? mb_strlen($value, 'ASCII') : (int)$forceShortValue,
                'big_value' => DB_Query::getSnippet('%s')->q($value)
            );
        }

        $oQuery =
            DB_Query::getSnippet(
                "SELECT `id` " .
                "FROM `cms_options` " .
                "WHERE " .
                    "`module_name` = %s AND " .
                    "`name` = %s " .
                "LIMIT 1"
            )
            ->q($modId)
            ->q($name);
        $id = $oDB->fetchValue($oQuery);

        if($id){
            $oQuery = DB_Query::getUpdateQuery(
                'cms_options',
                $aRecord,
                DB_Query::getSnippet('WHERE `id` = %s')->plain($id)
            );
        }else{
            $oQuery = DB_Query::getInsertQuery('cms_options', $aRecord);
        }
        $oDB->query($oQuery);
    }
}
