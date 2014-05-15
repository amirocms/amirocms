<?php
/**
 * AmiMultifeeds5/Articles configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiMultifeeds5_Articles
 * @version   $Id: AmiMultifeeds5_Articles_Rules.php 42166 2013-10-10 12:29:32Z Leontiev Anton $
 * @since     x.x.x
 * @amidev
 */

/**
 * AmiMultifeeds5/Articles configuration rules.
 *
 * @package    Config_AmiMultifeeds5_Articles
 * @subpackage Controller
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Articles_Rules extends Hyper_AmiMultifeeds5_Rules{
    /*
    public static function ruleTest($callbackData, $optionsData, $callbackMode,
$result, array $aData){
        switch($callbackMode){
            case 'getvalue':
                $result = $callbackData['value'];
                break;
            case 'getallvalues':
                if(!is_array($callbackData['value'])){
                    $callbackData['value'] = array();
                }
                $result = array(
                    array(
                        'name'      => 'r1',
                        'caption'   => 'Value 1',
                        'selected'  => in_array('r1', $callbackData['value'])
                    ),
                    array(
                        'name'      => 'r2',
                        'caption'   => 'Value 2',
                        'selected'  => in_array('r2', $callbackData['value'])
                    ),
                    array(
                        'name'      => 'r3',
                        'caption'   => 'Value 3',
                        'selected'  => in_array('r3', $callbackData['value'])
                    )
                );
                break;
        }
        return true;
    }
    */
}
