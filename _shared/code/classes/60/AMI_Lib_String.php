<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Library
 * @version   $Id: AMI_Lib_String.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.12.0
 */

/**
 * String library.
 *
 * @package Library
 * @static
 * @since   5.12.0
 */
class AMI_Lib_String{
    /**
     * Internationalization drivers
     *
     * @var array
     */
    private static $aLocaleDrivers = array();

    /**
     * @param  string $entity
     * @return void
     */
/*
    public static function recursiveTrim(&$entity){
        if(is_array($entity)){
            foreach(array_keys($entity) as $k){
                self::recursiveTrim($entity[$k]);
            }
        }else{
            $entity = trim($entity);
        }
    }
*/

    // from CMS_Base class {

    /**
     * Converts all applicable characters to HTML entities.
     *
     * @param  string $string  String
     * @return string
     */
    public static function htmlChars($string){
        return str_replace(array('#', '%', '\''), array('&#035;', '&#037;', '&#039;'), htmlspecialchars($string));
    }

    /**
     * Truncates string.
     *
     * @param  string $string      String
     * @param  int    $length      Max length
     * @param  bool   $isSpecial   Process as htmlspecialchars() result
     * @param  bool   $bSaveWords  Save whole words
     * @param  string $tail        Append tail if truncated
     * @return string
     */
    public static function truncate($string, $length, $isSpecial = false, $bSaveWords = false, $tail = '...'){
        // was CMS_Base::stripLine()
        // $string = strip_tags($string);
        if($isSpecial){
            $string = self::unhtmlEntities($string);
        }
        if(mb_strlen($string) > $length){
            if($bSaveWords){
                $string = mb_substr($string, 0, $length - 3);
                if(mb_substr($string, $length - mb_strlen($tail), 1) != ' '){
                    $last = mb_strrpos($string, ' ');
                    if($last !== false){
                        $string = mb_substr($string, 0, $last);
                    }
                }
                $string = rtrim($string) . $tail;
            }else{
                $string = rtrim(mb_substr($string, 0, $length - mb_strlen($tail))) . $tail;
            }
        }
        if($isSpecial){
            $string = self::htmlChars($string);
        }
        return $string;
    }

    // } from CMS_Base class
    // from "lib/function.php" {

    /**
     * Convert all HTML entities to their applicable characters.
     *
     * @param  string $string   String
     * @param  bool   $bSpaces  Convert "&nbsp;" character
     * @return string
     * @todo   Remember to change ami.common.js decodeHTMLSpecialChars when expanding replacements
     * @amidev
     */
    public static function unhtmlEntities($string, $bSpaces = false){
        static $aTransTable, $aTransTablePlus;

        if(empty($aTransTable)){
            $aTransTable =
                get_html_translation_table(HTML_SPECIALCHARS) +
                array("'" => '&#039;', '%' => '&#037;', '#' => '&#035;');
            $aTransTablePlus = $aTransTable + array(' ' => '&nbsp;');
            $aTransTable = array_flip($aTransTable);
            $aTransTablePlus = array_flip($aTransTablePlus);
        }
        return strtr($string, $bSpaces ? $aTransTablePlus : $aTransTable);
    }

    /**
     * Validates e-mail.
     *
     * @param  string $email  E-mail address
     * @return bool
     * @amidev
     */
    public static function validateEmail($email) {
        // "/^(\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+(;|,|$))+$/"
        return (bool)preg_match("/^(\w+[\w.-]*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+(;|,|$))+$/", $email);
    }

    /**
     * Adds variables to URL.
     *
     * @param  string $url  URL
     * @param  array $aVars  Array of variables
     * @return string
     * @amidev
     */
    public static function addVarsToUrl($url, array $aVars){
        $delim = "&";
        if(mb_strpos($url, "?") === false){
            $delim = "?";
        }
        foreach($aVars as $name => $value){
            $url .= $delim.$name."=".$value;
            $delim = "&";
        }
        return $url;
    }
    // } from "lib/function.php"
    // from "lib/utils.php" {

    /**
     * Prepares string to use in JavaScript.
     *
     * @param  string $string  String
     * @return string
     * @amidev
     */
    public static function jParse($string){
        return str_replace(
            array('\\', '/', "'", "\r", "\n"),
            array('\\\\', '\/', "\'", '', '\\n'),
            $string
        );
    }

    // } from "lib/utils.php"

    /**
     * Transliterate string characters.
     *
     * @param  string $string  String for transliteration
     * @param  string $locale  Locale
     * @return string
     * @amidev
     */
    public static function transliterate($string, $locale){
        return self::_getI18nDriver($locale)->transliterate($string);
    }

    /**
     * Returns valid symbols regexp part.
     *
     * @param  string $locale  Locale
     * @return string
     * @see    AMI_ModTableItemMeta::processHTMLMeta()
     * @amidev
     */
    public static function getValidSymbolsRegExp($locale){
        return self::_getI18nDriver($locale)->getValidSymbolsRegExp();
    }

    /**
     * Strips HTML and PHP tags from a string including script/style inner text.
     *
     * @param  string $string  String
     * @return string
     */
    public static function stripTags($string){
        return
            trim(
                strip_tags(
                    preg_replace(
                        array(
                            '/<script(.*?)\/script>/si',
                            '/<style(.*?)\/style>/si'
                        ),
                        '',
                        self::unhtmlEntities($string, true)
                    )
                )
            );
    }

    // from "lib/func_gui.php" {

    /**
     * Returns file size in text mode.
     *
     * @param  int    $size            Size to be translated as text
     * @param  array  $aLocale         Locale array
     * @param  int    $precision       Number of digits after comma
     * @param  int    $forcePower      Force result power (-1 no force power)
     * @param  string $zeroVal         Format of returning zero value
     * @param  string $thousandsDelim  Thouthands delimiter
     * @return string
     * @since  5.14.0
     */
    public static function getBytesAsText($size, array $aLocale, $precision = 0, $forcePower = -1, $zeroVal = '0', $thousandsDelim = ' '){
        $res = '';

        $pow = 0;
        $prec = pow(10, $precision);
        $size = doubleval($size);
        $size *= $prec;

        while((($size >= 1000 * $prec) || ($forcePower > 0)) && ($forcePower != $pow)){
            $size = doubleval($size / 1024);
            $pow++;
        }
        $size = round($size);
        $size /= $prec;

        if($pow == 0){
            $precision = 0;
        }
        $res = number_format($size, $precision, '.', $thousandsDelim) . " ";

        if($res == 0){
            $res = $zeroVal;
        }else{
            $measure = '';
            switch ($pow) {
                case 0:
                    $measure = 'byte';
                    break;
                case 1:
                    $measure = 'kilobyte';
                    break;
                case 2:
                    $measure = 'megabyte';
                    break;
                case 3:
                    $measure = 'gigabyte';
                    break;
            }
            if($measure && isset($aLocale[$measure])){
                $res .= $aLocale[$measure];
            }
        }
        return $res;
    }

    // } from "lib/func_gui.php"

    // from "lib/func_url.php" {

    /**
     * Checks the URL is full.
     *
     * @param  string $url  URL
     * @return bool
     * @since  5.14.4
     */
    public static function isFullLink($url){
        return (mb_strpos($url, "http://") !== false || mb_strpos($url, "https://") !== false || mb_strpos($url, "ftp://") !== false);
    }

    // } from "lib/func_url.php"

    /**
     * Returns internationalization driver.
     *
     * @param  string $locale  Locale
     * @return AMI_iI18n
     */
    private static function _getI18nDriver($locale){
        if(!isset(self::$aLocaleDrivers[$locale])){
            $driver = strtoupper($locale) . '_I18n';
            AMI_Service::setAutoloadWarning(false);
            if(!class_exists($driver)){
                $driver = 'General_I18n';
            }
            AMI_Service::setAutoloadWarning(true);
            self::$aLocaleDrivers[$locale] = new $driver;
        }
        return self::$aLocaleDrivers[$locale];
    }
}
