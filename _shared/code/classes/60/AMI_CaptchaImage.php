<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Service
 * @version   $Id: AMI_CaptchaImage.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     5.12.0
 */

/**
 * Captcha image generator interface.
 *
 * @package Service
 * @since   5.12.0
 */
interface AMI_iCaptchaImage{
	 /**
      * Constructor.
      *
      * Defines the default parameters and constants.
      *
      * @param int    $numSymbols  How many symbols
      * @param string $charset     Available constants: AMI_iCaptcha::CHARSET_DIGITS, AMI_iCaptcha::CHARSET_LETTERS, AMI_iCaptcha::CHARSET_LETTERS|AMI_iCaptcha::CHARSET_DIGITS
      */
    public function __construct($numSymbols = 4, $charset = AMI_iCaptcha::CHARSET_DIGITS);

    /**
     * Setting custom option.
     *
     * @param  string $name   Name of option
     * @param  string $value  Option's value
     * @return void
     */
    public function setGenerateOption($name, $value);

    /**
     * Getting custom option value.
     *
     * @param  string $name  Option name
     * @return mixed
     */
    public function getGenerateOption($name);

    /**
     * Setting string of symbols to generate image.
     *
     * @param  string $string  String
     * @return void
     */
    public function setImageString($string);

    /**
     * Returns image string.
     *
     * @return string
     */
    public function getImageString();

    /**
     * Setting number of symbols in image.
     *
     * @param  int $numSymbols  How many symbols
     * @return void
     */
    public function setNumSymbols($numSymbols = 4);

    /**
     * Returns is GD lib installed or not.
     *
     * @return bool
     */
    public function isGDLibInstalled();


    /**
     * Main class function. Generation and return the image.
     *
     * @param  string $imageType    Optional, type of image. This version supported types - 'png', 'gif', 'jpg', 'wbmp'
     * @param  string $imageString  Optional, the string, which will be drawn. If parameter is not given, then will be drawn random number.
     * @return void
     */
    public function createImage($imageType = '', $imageString = '');

    /**
     * Output current image to output stream.
     *
     * @return bool  True - if successful, or false, when errors occupied.
     */
    public function outToStream();

    /**
     * Generates random string for image.
     *
     * @param  string $charset  Available constants: AMI_iCaptcha::CHARSET_DIGITS, AMI_iCaptcha::CHARSET_LETTERS, AMI_iCaptcha::CHARSET_LETTERS|AMI_iCaptcha::CHARSET_DIGITS
     * @return string
     */
    public function generateRandomImageString($charset = AMI_iCaptcha::CHARSET_DIGITS);
}

/**
 * Captcha image generator class.
 *
 * Example:
 * <code>
 * // Customization: Adding zoom.
 * // Add into '_local/common_functions.php':
 *
 * class NewCaptchaImage extends AMI_CaptchaImage{
 *         public function createImage($imageType = '', $imageString = ''){
 *             $oImage = parent::createImage($imageType, $imageString);
 *             $this->currentWidth = imagesx($oImage);
 *             $this->currentHeight = imagesy($oImage);
 *             $zoom = 1.5;
 *             $oImage2 = imagecreatetruecolor($this->currentWidth / $zoom, $this->currentHeight / $zoom);
 *             imagecopyresampled(
 *                 $oImage2, $oImage, 0, 0, 0, 0,
 *                 $this->currentWidth / $zoom, $this->currentHeight / $zoom,
 *                 $this->currentWidth, $this->currentHeight
 *             );
 *             $this->oImage = $oImage2;
 *             return $this->image;
 *         }
 * }
 * AMI::addResource('captcha/image', 'NewCaptchaImage');
 * </code>
 *
 * @package  Service
 * @since    5.12.0
 * @resource captcha/image <code>AMI::getResource('captcha/image')</code>
 */
class AMI_CaptchaImage implements AMI_iCaptchaImage{
    /**
     * Number of symbols in image
     *
     * @var int
     */
    protected $numSymbols;

    /**
     * Default image type
     *
     * @var string
     */
    protected $defaultImageType = 'png';

    /**
     * Array containig supported image types
     *
     * @var array
     */
    protected $aSupportedImageTypes = array('png', 'gif', 'jpg', 'wbmp');

    /**
     * Final image width
     *
     * @var int
     */
    protected $currentWidth;

    /**
     * Final image height
     *
     * @var int
     */
    protected $currentHeight;

    /**
     * Current image type ('png','jpg',...)
     *
     * @var string
     */
    protected $imageType;

    /**
     * Current GD image object
     *
     * @var	object
     */
    protected $oImage;

    /**
     * GDLib installed flag
     *
     * @var bool
     */
    protected $bGDLibIsInstalled;

    /**
     * Image string to display
     *
     * @var string
     */
    protected $imageString = '0';

    /**
     * Current symbols set: AMI_iCaptcha::CHARSET_DIGITS, AMI_iCaptcha::CHARSET_LETTERS, AMI_iCaptcha::CHARSET_LETTERS|AMI_iCaptcha::CHARSET_DIGITS
     *
     * @var int
     */
    protected $charset;

    /**
     * Custiom generator options
     *
     * @var array
     */
    protected $aOptions = array();

    /**
     * Constructor.
     *
     * Defines the default parameters and constants.
     *
     * @param int    $numSymbols  How many symbols
     * @param string $charset     Available constatns: AMI_iCaptcha::CHARSET_DIGITS, AMI_iCaptcha::CHARSET_LETTERS, AMI_iCaptcha::CHARSET_LETTERS|AMI_iCaptcha::CHARSET_DIGITS
     */
    public function __construct($numSymbols = 4, $charset = AMI_iCaptcha::CHARSET_DIGITS){
        // Default values
        $this->numSymbols = $numSymbols;
        $this->imageString = 0;
        if(!$charset){
            $charset = AMI_iCaptcha::CHARSET_DIGITS;
        }
        $this->charset = $charset;
        $this->setGenerateOption('digitJitterAmplitude', 2);
        $this->isGDLibInstalled();
    }

    /**
     * Setting number of symbols in image.
     *
     * @param  int $numSymbols  How many symbols
     * @return void
     */
    public function setNumSymbols($numSymbols = 4){
        $this->numSymbols = (int)$numSymbols;
    }

    /**
     * Returns is GD lib installed or not.
     *
     * @return bool
     */
    public function isGDLibInstalled(){
        if(is_null($this->bGDLibIsInstalled)){
            $this->bGDLibIsInstalled = function_exists('imagetypes');
        }
        return $this->bGDLibIsInstalled;
    }

    /**
     * Setting string of symbols to generate image.
     *
     * @param  string $string  Available constatns: AMI_iCaptcha::CHARSET_DIGITS, AMI_iCaptcha::CHARSET_LETTERS, AMI_iCaptcha::CHARSET_LETTERS|AMI_iCaptcha::CHARSET_DIGITS
     * @return void
     */
    public function setImageString($string){
        $this->imageString = $string;
    }

     /**
      * Setting custom option.
      *
      * @param  string $name   Option number
      * @param  string $value  Variable value
      * @return void
      */
    public function setGenerateOption($name, $value){
        $this->aOptions[$name] = $value;
    }

    /**
     * Getting custom option.
     *
     * @param  string $name  Option name
     * @return mixed
     */
    public function getGenerateOption($name){
        return isset($this->aOptions[$name])?$this->aOptions[$name]:null;
    }

    /**
     * Modify current image by wave deformation.
     *
     * @param  object $img  Image GD object
     * @return object       Image GD object
     */
    protected function addWave($img = null){
        $width = $img ? imagesx($img) : $this->currentWidth;
        $height = $img ? imagesy($img) : $this->currentHeight;
        if(!$img){
            $img = $this->oImage;
        }

        $img2 = imagecreatetruecolor($width, $height);

        $rand1 = mt_rand(700000, 1000000) / 15000000 * 0.1; // x
        $rand2 = mt_rand(700000, 1000000) / 15000000 * 0.1; // y
        $rand3 = mt_rand(700000, 1000000) / 15000000; // x
        $rand4 = mt_rand(700000, 1000000) / 15000000; // y

        $rand5 = mt_rand(0, 3141592) / 1000000; // x
        $rand6 = mt_rand(0, 3141592) / 1000000; // x
        $rand7 = mt_rand(0, 3141592) / 1000000; // y
        $rand8 = mt_rand(0, 3141592) / 1000000; // y

        $rand9 = mt_rand(400, 1200) / 100 * 0.7 * $this->getGenerateOption('waveAmplitude'); // x 600
        $rand10 = mt_rand(400, 1200) / 100 * 0.7 * $this->getGenerateOption('waveAmplitude'); // y

        for($x = 0; $x < $width; $x ++){
            for($y = 0; $y < $height; $y ++){
                $sx1 = sin(round($x * $rand1 + $rand5, 1));
                $sx2 = sin(round($y * $rand3 + $rand6, 1));
                $sy1 = sin(round($x * $rand2 + $rand7, 1));
                $sy2 = sin(round($y * $rand4 + $rand8, 1));

                $sx = $x + ($sx1 + $sx2) * $rand9;
                $sy = $y + ($sy1 + $sy2) * $rand10;

                if($sx < 0 || $sy < 0 || $sx >= $width - 1 || $sy >= $height - 1){
                    $color = 0xFFFFFF;
                    $colorX = 0xFFFFFF;
                    $colorY = 0xFFFFFF;
                    $colorXY = 0xFFFFFF;
                }else{
                    $color = (imagecolorat($img, $sx, $sy) >> 16) & 0xFF;
                    $colorX = (imagecolorat($img, $sx + 1, $sy) >> 16) & 0xFF;
                    $colorY = (imagecolorat($img, $sx, $sy + 1) >> 16) & 0xFF;
                    $colorXY = (imagecolorat($img, $sx + 1, $sy + 1) >> 16) & 0xFF;
                }

                if($color == $colorX && $color == $colorY && $color == $colorXY){
                    $newcolor = $color;
                }else{
                    $frsx = $sx - floor($sx);
                    $frsy = $sy - floor($sy);
                    $frsx1 = 1 - $frsx;
                    $frsy1 = 1 - $frsy;

                    $newcolor = floor($color * $frsx1 * $frsy1 + $colorX * $frsx * $frsy1 + $colorY * $frsx1 * $frsy + $colorXY * $frsx * $frsy);
                }

                imagesetpixel($img2, $x, $y, imagecolorallocate($img2, $newcolor, $newcolor, $newcolor));
            }
        }

        return $img2;
    }

    /**
     * Main class function.
     *
     * Generation and return the image.
     *
     * @param  string $imageType    Optional, type of image, this version supported types: 'png', 'gif', 'jpg', 'wbmp'
     * @param  string $imageString  Optional, the string, which will be drawn, if parameter is not given, then will be drawn random number
     * @return object Image object
     */
    public function createImage($imageType = '', $imageString = ''){
    	$res = false;

    	if($this->checkSupportedTypes($imageType)){

    		// Reading font file
            if($this->charset == AMI_iCaptcha::CHARSET_DIGITS){
                $alphabet = '0123456789';
                $font = imagecreatefrompng(
                    $GLOBALS['ROOT_PATH'] . 'templates/fonts/ami_40_trans_numbers.png'
                );
            }else{
                $alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $font = imagecreatefrompng(
                    $GLOBALS['ROOT_PATH'] . 'templates/fonts/ami_40_trans_numbers_n_letters.png'
                );
            }

            $alphabetLength = strlen($alphabet);
            imagealphablending($font, true);
            $fontfileWidth = imagesx($font);
            $fontfileHeight = imagesy($font) - 1;
            $fontMetrics = array();
            $symbol = 0;
            $readingSymbol = false;
            $fontTransparent = imagecolorat($font, 0, $fontfileHeight);
            $maxFontSizeX = 0;
            $avrFontSizeX = 0;

            for($i = 0; $i < $fontfileWidth && $symbol < $alphabetLength; $i++){
                $rgb = imagecolorat($font, $i, 0);
                $transparent = ($rgb == $fontTransparent);
                if(!$readingSymbol && !$transparent){
                    $fontMetrics[$alphabet{$symbol}] = array('start' => $i);
                    $readingSymbol = true;
                    continue;
                }
                if($readingSymbol && $transparent){
                    $fontMetrics[$alphabet{$symbol}]['end'] = $i;
                    $symbolWidth = $fontMetrics[$alphabet{$symbol}]['end'] - $fontMetrics[$alphabet{$symbol}]['start'];
                    $fontMetrics[$alphabet{$symbol}]['width'] = $symbolWidth;
                    if($maxFontSizeX < $symbolWidth){
                        $maxFontSizeX = $symbolWidth;
                    }
                    if(!$avrFontSizeX){
                        $avrFontSizeX = $maxFontSizeX;
                    }
                    $avrFontSizeX = ceil(($avrFontSizeX + $symbolWidth) / 2);
                    $readingSymbol = false;
                    $symbol ++;
                    continue;
                }
            }

            $fontMetrics[$alphabet{$symbol}]['end'] = $fontfileWidth;

            // Calculation symbols x-jitter and width of image

            $maxFontSizeY = $fontfileHeight;
            $maxFontJitterY = $maxFontSizeY / 10 * $this->getGenerateOption('digitJitterAmplitude');
            $maxFontJitterX = $maxFontSizeX / 2 * $this->getGenerateOption('digitJitterAmplitude');

            if(empty($imageString)){
                if($this->imageString == 0){
                    $this->generateRandomImageString($this->charset);
                }
            }else{
                $this->imageString = $imageString;
            }

            $this->imageString = $this->imageString . '';

            $stringWidth = 0;
            $aStringArray = array();

            for($i = 0; $i < $this->numSymbols; $i++){
                $aStringArray[$i] = $fontMetrics[$this->imageString[$i]];
                $aStringArray[$i]['xshift'] = - ($maxFontJitterX - ($maxFontJitterX * (mt_rand(0, 10) / 10))) / 3;
                $stringWidth += $aStringArray[$i]['width'] + $aStringArray[$i]['xshift'];
            }

            // Creating image

            $imgHeight = $maxFontSizeY;
            $imgWidth = $avrFontSizeX * ($this->numSymbols) * 1.1;

            $img = imagecreatetruecolor($imgWidth, $imgHeight + $maxFontJitterY * 2);

            // Copying alphabet symbols

            if($img){
                $bgColor = imagecolorallocate($img, 255, 255, 255); // white
                imagefill($img, 0, 0, $bgColor);

                $x = ($imgWidth - $stringWidth) / 3; // left margin
                for($i = 0; $i < $this->numSymbols; $i ++){

                    $m = $aStringArray[$i];
                    // $y = mt_rand(- $maxFontJitterY, $maxFontJitterY) + ($imgHeight - $maxFontSizeY) / 2.5 + $maxFontJitterX / 2;
                    $y = mt_rand(-$maxFontJitterY, $maxFontJitterY) + ($imgHeight/2 - $maxFontSizeY/4);

                    $shift = 0;
                    if($i > 0){
                        $shift = 10000;
                        for($sy = 7; $sy < $maxFontSizeY - 20; $sy += 1){
                            for($sx = $m['start'] - 1; $sx < $m['end']; $sx += 1){
                                $rgb = imagecolorat($font, $sx, $sy);
                                $opacity = $rgb >> 24;
                                if($opacity < 127){
                                    $left = $sx - $m['start'] + $x;
                                    $py = $sy + $y;
                                    if($py > $height) break;
                                    for($px = min($left, $imgWidth - 1); $px > $left - 12 && $px >= 0; $px -= 1){
                                        $color = imagecolorat($img, $px, $py) & 0xFF;
                                        if($color + $opacity < 190){
                                            if($shift > $left - $px){
                                                $shift = $left - $px;
                                            }
                                            break;
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                        if($shift == 10000){
                            $shift = $m['xshift'];
                        }
                    }

                    imagecopy($img, $font, $x + $shift, $y, $m['start'], 1, $m['end'] - $m['start'], $fontfileHeight - 1);
                    $x += $m['end'] - $m['start'] + $shift;
                }

                imagedestroy($font);

                // Adding random white lines

                for($li = 0; $li < 5; $li ++){
                    $x1 = mt_rand(0, $imgWidth / 2);
                    $y1 = mt_rand(0, $imgHeight);
                    $x2 = mt_rand($imgWidth / 2, $imgWidth);
                    $y2 = mt_rand(0, $imgHeight);
                    $bold = mt_rand(2, 4);
                    for($ldi = 0; $ldi < $bold; $ldi ++){
                        imageline($img, $x1, $y1 + $ldi, $x2, $y2 + $ldi, $bgColor);
                    }
                }

                // Adding noice
                if($this->getGenerateOption('noiseLevel') && $this->getGenerateOption('noiseLevel') > 70){
                    for($li = 0; $li < mt_rand(0, $this->getGenerateOption('noiseLevel')-70); $li ++){
                        $x1 = mt_rand(0, $imgWidth);
                        $y1 = mt_rand(0, $imgHeight);
                        $x2 = mt_rand($x1-$imgWidth/7, $x1+$imgWidth/7);
                        $y2 = mt_rand($y1-$imgWidth/7, $y1+$imgWidth/7);
                        $bold = mt_rand(2, 5);
                        for($ldi = 0; $ldi < $bold; $ldi ++){
                            $aNoiseColor = $this->hex2rgb($this->getGenerateOption('noiseColor'));
                            imageline($img, $x1, $y1 + $ldi, $x2, $y2 + $ldi, imagecolorallocate($img, $aNoiseColor['r'], $aNoiseColor['g'], $aNoiseColor['b']));
                        }
                    }
            	}

                if($this->getGenerateOption('waveAmplitude')){
                    $img = $this->addWave($img);
                }

                $this->oImage = $img;

                // Adding random lines
                for($li = 0; $li < 4; $li ++){
                    $x1 = mt_rand(0, $this->currentWidth / 2 / 2);
                    $y1 = mt_rand(0, $this->currentHeight / 2);
                    $x2 = mt_rand($this->currentWidth / 2 / 2, ($this->currentWidth / 2) * 0.9);
                    $y2 = mt_rand(0, $this->currentHeight / 2);
                    $bold = mt_rand(1, 2);
                    for($ldi = 0; $ldi < $bold; $ldi ++){
                        imageline($this->oImage, $x1, $y1 + $ldi, $x2, $y2 + $ldi, $bgColor);
                    }
                }

            }

            $res = $this->oImage;
    	}
        return $res;
    }

    /**
     * Converting hex string into RGB values.
     *
     * @param string $hexVal  Hex string value
     * @return array ("r" => ..., "g" => ..., "b" => ...)
     */
    function hex2rgb($hexVal = ''){
        $hexVal = preg_replace('/[^a-fA-F0-9]/', '', $hexVal);
        if(strlen($hexVal) != 6){
            return array('r' => 0, 'g' => 0, 'b' => 0);
        }
        $aTmp = explode(' ', chunk_split($hexVal, 2, ' '));
        $aTmp = array_map('hexdec', $aTmp);
        $aRet = array('r' => $aTmp[0], 'g' => $aTmp[1], 'b' => $aTmp[2]);
        return $aRet;
    }

    /**
     * Output current image to output stream.
     *
     * @return bool  True - if successful, or false, when errors occupied.
     */
    public function outToStream(){
        $res = false;
        if($this->oImage && $this->bGDLibIsInstalled){
            switch($this->imageType){
                case 'png':
                    header('Content-type: image/png');
                    @imagepng($this->oImage);
                    $res = true;
                    break;
                case 'jpg':
                    header('Content-type: image/jpeg');
                    @imagejpeg($this->oImage);
                    $res = true;
                    break;
                case 'gif':
                    header('Content-type: image/gif');
                    @imagegif($this->oImage);
                    $res = true;
                    break;
                case 'wbmp':
                    header('Content-type: image/vnd.wap.wbmp');
                    @imagewbmp($this->oImage);
                    $res = true;
                    break;
            }
            imagedestroy($this->oImage);
        }
        return $res;
    }

    /**
     * Generates random string for image.
     *
     * @param  string $charset  Available constatns: AMI_iCaptcha::CHARSET_DIGITS, AMI_iCaptcha::CHARSET_LETTERS, AMI_iCaptcha::CHARSET_LETTERS|AMI_iCaptcha::CHARSET_DIGITS
     * @return string  New image string
     */
    public function generateRandomImageString($charset = 0){
        if(mb_strlen($charset) == 0 || $charset == 0){
            $charset = $this->charset;
        }
        $this->imageString = 0;
        if($this->numSymbols > 0){
            switch($charset){
                case AMI_iCaptcha::CHARSET_DIGITS:
                    $this->imageString = rand(pow(10, $this->numSymbols - 1), (pow(10, $this->numSymbols) - 1));
                    break;
                default:
                    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    if($charset & AMI_iCaptcha::CHARSET_DIGITS){
                        $alphabet .= '0123456789';
                    }
                    $alphabetLength = mb_strlen($alphabet);
                    $this->imageString = '';
                    for($i = 0; $i < $this->numSymbols; $i ++){
                        $this->imageString .= $alphabet[rand(0, $alphabetLength - 1)];
                    }
                    break;
            }
        }

        return $this->imageString;
    }

    /**
     * Returns image string.
     *
     * @return string
     */
    public function getImageString(){
        return $this->imageString;
    }

    /**
     * Returns image object.
     *
     * @return object.
     */
    public function getImage(){
    	return $this->oImage;
    }

    /**
     * Checking on supported images types in current version of php graphics library.
     *
     * @param  string $type  Type of image, This version suppors types - 'png', 'gif', 'jpg', 'wbmp'.
     * @return bool
     */
    protected function checkSupportedTypes($type){
        $res = false;
        if($this->bGDLibIsInstalled){
            $this->imageType = null;
            $this->oImage = '';
            if(empty($type)){
                $this->imageType = $this->defaultImageType;
            }elseif(in_array($type, $this->aSupportedImageTypes)){
                $this->imageType = $type;
            }
            switch($this->imageType){
                case 'png':
                    $res = imagetypes() & IMG_PNG;
                    break;
                case 'jpg':
                    $res = imagetypes() & IMG_JPG;
                    break;
                case 'gif':
                    $res = imagetypes() & IMG_GIF;
                    break;
                case 'wbmp':
                    $res = imagetypes() & IMG_WBMP;
                    break;
            }
        }
        return $res;
    }
}
