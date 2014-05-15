<?php
/**
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Library
 * @version   $Id: AMI_I18n.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 * @todo      Describe usage
 */

/**
 * Internationalization driver interface.
 *
 * @package Library
 * @since   x.x.x
 * @amidev  Temporary
 */
interface AMI_iI18n{
    /**
     * Transliterates string.
     *
     * @param  mixed $string  String
     * @return string
     */
    public function transliterate($string);

    /**
     * Returns valid symbols regexp part.
     *
     * @return string
     * @see    AMI_ModTableItemMeta::processHTMLMeta()
     */
    public function getValidSymbolsRegExp();
}

/**
 * General internationalization driver for unknown languages.
 *
 * @package Library
 * @since   x.x.x
 * @amidev  Temporary
 */
class General_I18n implements AMI_iI18n{
    /**
     * Transliterates string.
     *
     * @param  mixed $string  String
     * @return string
     */
    public function transliterate($string){
        return preg_replace('/[^!#\-\)\+\-\.0-9;\=\?\@A-Za-z\[\]\^\_\{\}]/s', '_', $string);
    }

    /**
     * Returns valid symbols regexp part.
     *
     * @return string
     * @see    AMI_ModTableItemMeta::processHTMLMeta()
     */
    public function getValidSymbolsRegExp(){
        return 'a-zA-Z0-9-';
    }
}

/**
 * Internationalization driver for English language.
 *
 * @package Library
 * @since   x.x.x
 * @amidev  Temporary
 */
class EN_I18n implements AMI_iI18n{
    /**
     * Transliterates string.
     *
     * @param  mixed $string  String
     * @return string
     */
    public function transliterate($string){
        return $string;
    }

    /**
     * Returns valid symbols regexp part.
     *
     * @return string
     * @see    AMI_ModTableItemMeta::processHTMLMeta()
     */
    public function getValidSymbolsRegExp(){
        return 'a-zA-Z0-9-';
    }
}

/**
 * Internationalization driver for Russian language.
 *
 * @package Library
 * @since   x.x.x
 * @amidev  Temporary
 */
class RU_I18n implements AMI_iI18n{
    /**
     * Transliteration mapping
     *
     * @var array
     */
    protected $aTranslit = array(
        'in' => array(
            'А', 'а',
            'Б', 'б',
            'В', 'в',
            'Г', 'г',
            'Д', 'д',
            'Е', 'е',
            'Ё', 'ё',
            'Ж', 'ж',
            'З', 'з',
            'И', 'и',
            'Й', 'й',
            'К', 'к',
            'Л', 'л',
            'М', 'м',
            'Н', 'н',
            'О', 'о',
            'П', 'п',
            'Р', 'р',
            'С', 'с',
            'Т', 'т',
            'У', 'у',
            'Ф', 'ф',
            'Х', 'х',
            'Ц', 'ц',
            'Ч', 'ч',
            'Ш', 'ш',
            'Щ', 'щ',
            'Ъ', 'ъ',
            'Ы', 'ы',
            'Ь', 'ь',
            'Э', 'э',
            'Ю', 'ю',
            'Я', 'я'
        ),
        'out' => array(
            'A', 'a',
            'B', 'b',
            'V', 'v',
            'G', 'g',
            'D', 'd',
            'E', 'e',
            'E', 'e',
            'Zh', 'zh',
            'Z', 'z',
            'I', 'i',
            'J', 'j',
            'K', 'k',
            'L', 'l',
            'M', 'm',
            'N', 'n',
            'O', 'o',
            'P', 'p',
            'R', 'r',
            'S', 's',
            'T', 't',
            'U', 'u',
            'F', 'f',
            'H', 'h',
            'C', 'c',
            'Ch', 'ch',
            'Sh', 'sh',
            'Sch', 'sch',
            '\'', '\'',
            'Y', 'y',
            '\\', '\\',
            'E', 'e',
            'Ju', 'ju',
            'Ja', 'ja'
        )
    );

    /**
     * Transliterates string.
     *
     * @param  mixed $string  String
     * @return string
     */
    public function transliterate($string){
        return str_replace($this->aTranslit['in'], $this->aTranslit['out'], $string);
    }

    /**
     * Returns valid symbols regexp part.
     *
     * @return string
     * @see    AMI_ModTableItemMeta::processHTMLMeta()
     */
    public function getValidSymbolsRegExp(){
        return 'a-zA-Zа-яА-Я0-9-';
    }
}
