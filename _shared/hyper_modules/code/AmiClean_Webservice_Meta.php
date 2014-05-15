<?php
/**
 * AmiClean/Webservice configuration.
 *
 * @copyright Amiro.CMS. All rights reserved.
 * @category  Module
 * @package   Config_AmiClean_Webservice
 */

/**
 * AmiClean/Webservice configuration metadata.
 *
 * @package    Config_AmiClean_Webservice
 * @subpackage Meta
 */
class AmiClean_Webservice_Meta extends AMI_HyperConfig_Meta{
    /**
     * Version
     *
     * @var string
     */
    protected $version = '1.0';

    /**
     * Only one instance per config allowed
     *
     * @var bool
     */
    protected $isSingleInstance = FALSE;

    /**
     * Array having locales as keys and captions as values
     *
     * @var array
     */
    protected $aTitle = array(
        'en' => 'Webservice',
        'ru' => 'Вебсервисы'
    );

    /**
     * Array having locales as keys and meta data as values
     *
     * @var array
     */
    protected $aInfo = array(
        'en' => array(
            'description' => 'REST.API webservice setup module',
            'author'      => '<a href="http://www.amirocms.com" target="_blank">Amiro.CMS</a>'
        ),
        'ru' => array(
            'description' => 'Модуль настройки вебсервисов REST.API',
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
            'header' => array(
                'obligatory' => TRUE,
                'type' => self::CAPTION_TYPE_STRING,
                'locales' => array(
                    'en' => array(
                        'name' => 'Header',
                        'caption' => 'Webservice',
                    ),
                    'ru' => array(
                        'name' => 'Заголовок',
                        'caption' => 'Вебсервисы',
                    ),
                ),
            ),
            'menu' => array(
                'obligatory' => TRUE,
                'type' => self::CAPTION_TYPE_STRING,
                'locales' => array(
                    'en' => array(
                        'name' => 'Menu caption',
                        'caption' => 'Webservice',
                    ),
                    'ru' => array(
                        'name' => 'Заголовок для меню',
                        'caption' => 'Вебсервисы',
                    ),
                ),
            ),
            'description' => array(
                'obligatory' => FALSE,
                'type' => self::CAPTION_TYPE_TEXT,
                'locales' => array(
                    'en' => array(
                        'name' => 'REST.API webservice setup module',
                        'caption' => 'Instance of AmiClean hypermodule / Webservice configuration',
                    ),
                    'ru' => array(
                        'name' => 'Описание модуля для стартовой страницы интерфейса администратора',
                        'caption' => 'Модуль настройки вебсервисов REST.API',
                    ),
                ),
            ),
        ),
    );
}
