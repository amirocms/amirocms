<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Library
 * @version   $Id: AMI_Lib_Array.php 47117 2014-01-28 13:56:01Z Kolesnikov Artem $
 * @since     5.14.4
 */

/**
 * Array library.
 *
 * @package Library
 * @static
 * @since   5.14.4
 */
class AMI_Lib_Array{
    /**
     * Swap two array elements and returns true if swapped.
     *
     * @param  array      &$array     Array
     * @param  int|string $srcIndex   Source index
     * @param  int|string $destIndex  Destination index
     * @return bool
     */
    public static function swap(array &$array, $srcIndex, $destIndex){
        $res = is_array($array) && isset($array[$srcIndex]) && isset($array[$destIndex]);
        if($res){
            $element = $array[$srcIndex];
            $array[$srcIndex] = $array[$destIndex];
            $array[$destIndex] = $element;
        }
        return $res;
    }

    // from lib/func_array.php {

    /**
     * Sort an array by column.
     *
     * @param  array &$aArray       Array
     * @param  int|string $sortCol  Sort column
     * @param  int $sort            Sort type: SORT_STRING|SORT_REGULAR|SORT_NUMERIC
     * @param  int $direction       Sort direction: SORT_ASC|SORT_DESC
     * @return void
     */
    function sortMultiArray(array &$aArray, $sortCol, $sort = SORT_STRING, $direction = SORT_ASC){
        if(sizeof($aArray) > 0){
            $aIndex = array();
            $aRes = array();
            $i = 0;
            foreach($aArray as $vData){
                $aIndex['pos'][$i]  = $i;
                $aIndex['name'][$i] = $vData[$sortCol];
                $i++;
            }
            array_multisort($aIndex['name'], $sort, $direction, $aIndex['pos']);
            $aKeys = array_keys($aArray);
            for($j = 0; $j < $i; $j++){
                $aRes[$j] = $aArray[$aKeys[$aIndex['pos'][$j]]];
            }
            $aArray = $aRes;
        }
    }

    /**
     * Sort an array by column preserving row keys.
     *
     * @param  array &$aArray       Array
     * @param  int|string $sortCol  Sort column
     * @param  int $sort            Sort type: SORT_STRING|SORT_REGULAR|SORT_NUMERIC
     * @param  int $direction       Sort direction: SORT_ASC|SORT_DESC
     * @return void
     */
    public static function sortMultiArrayPreserveKeys(array &$aArray, $sortCol, $sort = SORT_STRING, $direction = SORT_ASC){
        if(sizeof($aArray)){
            $aIndex = array ();
            $i = 0;
            foreach($aArray as $vKey => $vData){
                $aIndex['pos'][$i]  = $vKey;
                $aIndex['name'][$i] = $vData[$sortCol];
                $i++;
            }
            array_multisort($aIndex['name'], $sort, $direction, $aIndex['pos']);
            $aRes = array();
            for($j = 0; $j < $i; $j++){
                $aRes[$aIndex['pos'][$j]] = $aArray[$aIndex['pos'][$j]];
            }
            $aArray = $aRes;
        }
    }

    // } from lib/func_array.php

    /**
     * Prepends prefix for each array key.
     *
     * @param  array  $aSource  Source array
     * @param  string $prefix   Prefix
     * @return array
     * @since  5.12.8
     */
    public static function prependKeyPrefix(array $aSource, $prefix){
        $aResult = array();
        foreach($aSource as $key => $value){
            $aResult[$prefix . $key] = $value;
        }
        return $aResult;
    }

    /**
     * Inserts data into array after/before specified position.
     *
     * @param  array      $aSource  Source array
     * @param  array      $aData    Data to insert
     * @param  int|string $pos      Position
     * @param  bool       $after    Flag specifying to insert before or after position
     * @return array
     */
    public static function insert(array $aSource, array $aData, $pos, $after = TRUE){
        if(is_int($pos)){
            return
                array_merge(
                    array_slice($aSource, 0, $pos + (int)(bool)$after),
                    $aData,
                    array_slice($aSource, $pos + (int)(bool)$after)
                );
        }
        $aResult = array();
        if($after){
            foreach($aSource as $key => $value){
                $aResult[$key] = $value;
                if($key == $pos){
                    $aResult = array_merge($aResult, $aData);
                }
            }
        }else{
            foreach($aSource as $key => $value){
                if($key == $pos){
                    $aResult = array_merge($aResult, $aData);
                }
                $aResult[$key] = $value;
            }
        }
        return $aResult;
    }

    /**
     * Renames key in associative array.
     *
     * @param  array  &$aArray    Array
     * @param  string $from       Key to raname from
     * @param  string $to         Key to raname to
     * @param  bool   $preserve   Preserve key order flag, not implemented yet
     * @param  bool   $recursive  Flag specifying to rename recursively
     * @return void
     * @todo   Preserve key order option
     */
    public static function renameKey(array &$aArray, $from, $to, $preserve = FALSE, $recursive = FALSE){
        if($from === $to){
            return;
        }
        if(isset($aArray[$from])){
            $aArray[$to] = $aArray[$from];
            unset($aArray[$from]);
        }
        if($recursive){
            $aKeys = array_keys($aArray);
            foreach($aKeys as $key){
                if(is_array($aArray[$key])){
                    self::renameKey($aArray[$key], $from, $to, $preserve, TRUE);
                }
            }
        }
    }
}
