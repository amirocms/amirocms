<?php
/**
 * AmiClean/ModManager configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiClean_ModManager
 * @version   $Id: AmiFake_ModManager_Meta.php 43038 2013-11-05 04:22:36Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiClean/ModManager configuration metadata.
 *
 * @package    Config_AmiClean_ModManager
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiFake_ModManager_Meta extends AMI_HyperConfig_Meta{
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
        'en' => 'Module manager',
        'ru' => 'Менеджер модулей'
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
                        'caption' => 'Module manager'
                    ),
                    'ru' => array(
                        'name'    => 'Заголовок для меню',
                        'caption' => 'Менеджер модулей'
                    )
                )
            )
        )
    );
}
