<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Service
 * @version   $Id: AMI_Captcha.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.12.0
 */

/**
 * Capctha class interface.
 *
 * @package   Service
 * @since     5.12.0
 */
interface AMI_iCaptcha{
    /**
     * Digits charset
     * @var string
     */
    const CHARSET_DIGITS = 0x01;

    /**
     * Letters charset
     * @var string
     */
    const CHARSET_LETTERS = 0x02;

    /**
     * Constructor.
     *
     * @param string $module   Module name manipulating captha
     * @param string $sid      Session Id
     * @param int    $digits   Symbols number
     * @param string $charset  Available constants: self::CHARSET_DIGITS, self::CHARSET_LETTERS, self::CHARSET_LETTERS|self::CHARSET_DIGITS
     */
    public function __construct($module, $sid, $digits = 4, $charset = self::CHARSET_DIGITS);

    /**
     * Returns Image Generator object.
     *
     * @return AMI_iCaptchaImage
     */
    public function getImageGenerator();

    /**
     * Loads image string from db.
     *
     * @return mixed  string or null in case of failure
     */
    public function loadImageString();

    /**
     * Saves image string in db.
     *
     * @return void
     */
    public function saveImageString();

    /**
     * Removes record from db.
     *
     * @return void
     */
    public function removeRecord();
}

/**
 * Capctha class provides capthca manipulation functionality. At this moment not recomended to use. Use only AMI_CaptchaImage.
 *
 * @package  Service
 * @since    5.12.0
 * @resource captcha <code>AMI::getResource('captcha')</code>
 */
class AMI_Captcha implements AMI_iCaptcha{
    /**
     * Captcha data stored in db time period.
     *
     * @var string
     */
    protected $period = '-1 hour';

    /**
     * Image generator object.
     *
     * @var AMI_iCaptchaImage
     */
    protected $oImageGenerator;

    /**
     * Contains module name manipulating captha.
     *
     * @var string
     */
    protected $modId;

    /**
     * Session id.
     *
     * @var string
     */
    protected $sessionId;

    /**
     * Number of digits
     *
     * @var int
     */
    protected $digits;

    /**
     * DB object
     *
     * @var AMI_DB
     */
    private $oDB;

    /**
     * Return coresponding constat (CHARSET_DIGITS, CHARSET_LETTERS, CHARSET_LETTERS|CHARSET_DIGITS ) value to incoming $charset.
     *
     * @param string $charset  Old charset string
     * @return self::CHARSET_DIGITS|self::CHARSET_LETTERS|(self::CHARSET_LETTERS|self::CHARSET_DIGITS)
     */
    public static function getConstantCharset($charset){
        switch($charset){
            case 'digits':
                return self::CHARSET_DIGITS;
            case 'letters':
                return self::CHARSET_LETTERS;
            case 'letters_and_digits':
                return self::CHARSET_LETTERS | self::CHARSET_DIGITS;
            default:
                trigger_error("Invalid charset '" . $charset . "'", E_USER_WARNING);
                return self::CHARSET_DIGITS;
        }
    }

    /**
     * Constructor.
     *
     * @param string $modId    Module name manipulating captha
     * @param string $sid      Session Id
     * @param int    $digits   Number of digits
     * @param string $charset  Available constants: self::CHARSET_DIGITS, self::CHARSET_LETTERS, self::CHARSET_LETTERS|self::CHARSET_DIGITS
     */
    public function __construct($modId, $sid, $digits = 4, $charset = self::CHARSET_DIGITS){
    	$this->oDB = AMI::getSingleton('db');
        $this->modId = $modId;
        $this->sessionId = $sid;
        $this->digits = (int)$digits;
        if(!$charset){
            $charset = self::CHARSET_DIGITS;
        }
        $this->oImageGenerator = AMI::getResource('captcha/image', array($digits, $charset));

        // Delete expired data
        $oQuery =
            DB_Query::getSnippet(
                'DELETE FROM `cms_tmp` ' .
                'WHERE `module` = %s AND `date` < %s'
            )
            ->q($this->modId)
            ->q(DateTools::toMysqlDate(strtotime($this->period)));
        $this->oDB->query($oQuery);
    }

    /**
     * Returns ImageGenerator object.
     *
     * @return AMI_iCaptchaImage
     */
    public function getImageGenerator(){
        return $this->oImageGenerator;
    }

    /**
     * Loads image string from db.
     *
     * @return mixed  String or null in case of failure
     */
    public function loadImageString(){
        $ip = AMI::getSingleton('env/request')->getEnv('REMOTE_ADDR');
        $oQuery =
            DB_Query::getSnippet(
                'SELECT `sdata1` FROM `cms_tmp` ' .
                'WHERE `module` = %s AND `sdata2` = %s AND `ip` = %s'
            )
            ->q($this->modId)
            ->q($this->sessionId)
            ->q($ip);
        $sdata = $this->oDB->fetchValue($oQuery);

        if($sdata){
            // Image found
            $this->oImageGenerator->setImageString($sdata);
            $this->oImageGenerator->setNumSymbols($this->digits);
        }else{
            // Image not found
            $this->oImageGenerator->setImageString(null);
            $this->oImageGenerator->setNumSymbols(0);
        }
        return $this->oImageGenerator->getImageString();
    }

    /**
     * Saves image string to DB.
     *
     * @return void
     */
    public function saveImageString(){
        $this->removeRecord();
        $aRecord = array(
            'module' => $this->modId,
            'date'   => DB_Query::getSnippet('NOW()'),
            'ip'     => AMI::getSingleton('env/request')->getEnv('REMOTE_ADDR'),
            'sdata1' => $this->oImageGenerator->getImageString(),
            'sdata2' => $this->sessionId
        );
        $oQuery = DB_Query::getInsertQuery('cms_tmp', $aRecord);
        $this->oDB->query($oQuery);
    }

    /**
     * Removes record from DB.
     *
     * @return void
     */
    public function removeRecord(){
        $oQuery = DB_Query::getSnippet(
            'DELETE FROM `cms_tmp` ' .
            'WHERE `module` = %s AND `sdata2` = %s AND `ip` = %s'
        )
        ->q($this->modId)
        ->q($this->sessionId)
        ->q(AMI::getSingleton('env/request')->getEnv('REMOTE_ADDR'));
        $this->oDB->query($oQuery);
    }
}