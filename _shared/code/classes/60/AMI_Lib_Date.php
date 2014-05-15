<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Library
 * @version   $Id: AMI_Lib_Date.php 49516 2014-04-08 10:27:12Z Maximov Alexey $
 * @since     5.12.0
 */

/**
 * Date/time library.
 *
 * @package Library
 * @static
 * @since   5.12.0
 */
class AMI_Lib_Date{
    /**
     * Minimum possible unix time
     */
    const UTIME_MIN = 315511200; // mktime(0, 0, 0, 1, 1, 1980);

    /**
     * Maximum possible unix time
     */
    const UTIME_MAX = 2019664799; // mktime(0, 0, 0, 1, 1, 2034) - 1;

    const FMT_DATE = 'PHP_DATE';
    const FMT_TIME = 'PHP_TIME';
    const FMT_BOTH = 'PHP';
    const FMT_BOTH_ZONE = 'PHP_ZONE';
    const FMT_UNIX = 'UNIX';

    /**
     * Date/time formats
     *
     * @var array
     */
    protected static $aFormats = array(
        'PHP'      => 'Y-m-d H:i:s',
        'PHP_ZONE' => 'Y-m-d H:i:s e',
        'PHP_DATE' => 'Y-m-d',
        'PHP_TIME' => 'H:i:s'
    );

    /**
     * Returns formatted unix time.
     *
     * @param  int    $utime   Unix time
     * @param  string $format  Format: AMI_Lib_Date::FMT_DATE, AMI_Lib_Date::FMT_TIME or AMI_Lib_Date::FMT_BOTH
     * @return string
     */
    public static function formatUnixTime($utime, $format = self::FMT_DATE){
        return date(AMI::getDateFormat(AMI_Registry::get('lang', 'en'), $format), $utime);
    }

   // public static function ajustDbDate
    /**
     * Date/time formatter.
     *
     * Example:
     * <code>
     * $date = '2011-12-21';
     * $formattedDate = AMI_Lib_Date::formatDateTime($date, AMI_Lib_Date::FMT_BOTH);
     * </code>
     *
     * @param  string $value   Date to format
     * @param  string $format  Format: AMI_Lib_Date::FMT_DATE, AMI_Lib_Date::FMT_TIME or AMI_Lib_Date::FMT_BOTH
     * @param  bool $bToSQL    Return MySQL specific formatted date
     * @return string
     * @since  5.12.4
     */
    public static function formatDateTime($value, $format = self::FMT_DATE, $bToSQL = false){
        if($bToSQL){
            $localFormat = DateTools::php2confFormat(AMI::getDateFormat(AMI_Registry::get('lang', 'en'), $format));
            $unixTime = DateTools::isvaliddate($value, $localFormat);
        }else{
            $unixTime = strtotime($value);
        }
        if($unixTime === FALSE){
            return $value;
        }

        if($bToSQL){
            $format = isset(self::$aFormats[$format]) ? self::$aFormats[$format] : 'PHP';

            return date($format, $unixTime);
        }

        return ($format == self::FMT_UNIX) ? $unixTime : self::formatUnixTime($unixTime, $format);
    }

    /**
     * Re
     *
     * @param  int $type  self::FMT_DATE } self:: | self::FMT_TIME | FMT_BOTH
     * @return string
     * @since  6.0.4
     */
    public static function getFormat($type){
        return self::$aFormats[$type];
    }
}
