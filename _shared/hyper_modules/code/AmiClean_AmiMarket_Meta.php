<?php
/**
 * AmiClean/AmiMarket configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiClean_AmiMarket
 * @version   $Id: AmiClean_AmiMarket_Meta.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiClean/AmiMarket configuration metadata.
 *
 * @package    Config_AmiClean_AmiMarket
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_AmiMarket_Meta extends AMI_HyperConfig_Meta{
    /**
     * Version
     *
     * @var string
     */
    protected $version = '1.0';

    /**
     * Instance can not be edited
     *
     * @var bool
     */
    protected $editable = FALSE;

    /**
     * Only one instance per config allowed
     *
     * @var bool
     */
    protected $isSingleInstance = TRUE;

    /**
     * Flag specifies possibility of local PHP-code generation
     *
     * @var   bool
     */
    protected $canGenCode = FALSE;

    /**
     * Flag speficies impossibility of instance deinstallation
     *
     * @var bool
     * @amidev
     */
    protected $permanent = TRUE;

    /**
     * Array having locales as keys and titles as values
     *
     * @var array
     */
    protected $aTitle = array(
        'en' => 'Market',
        'ru' => 'Маркет'
    );

    /**
     * Array having locales as keys and meta data as values
     *
     * @var array
     */
    protected $aInfo = array(
        'en' => array(
            // 'description' => '',
            'author'      => '<a href="http://www.amirocms.com" target="_blank">Amiro.CMS</a>'
        ),
        'ru' => array(
            // 'description' => '',
            'author'      => '<a href="http://www.amiro.ru" target="_blank">Amiro.CMS</a>'
        )
    );

    /**
     * Array containing captions struct
     *
     * @var array
     */
    protected $aCaptions = array(
        '' => array(
            'menu' => array(
                'obligatory' => TRUE,
                'type'       => self::CAPTION_TYPE_STRING,
                'locales'    => array(
                    'en' => array(
                        'name'    => 'Menu caption',
                        'caption' => 'Market'
                    ),
                    'ru' => array(
                        'name'    => 'Заголовок для меню',
                        'caption' => 'Маркет'
                    )
                )
            )
        )
    );
}
