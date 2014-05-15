<?php
/**
 * AmiExt/EshopCustomFields extension configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiExt_EshopCustomFields
 * @version   $Id: AmiExt_EshopCustomFields_Meta.php 40563 2013-08-12 14:53:26Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiExt/EshopCustomFields extension configuration metadata.
 *
 * @package    Config_AmiExt_EshopCustomFields
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiExt_EshopCustomFields_Meta extends AMI_HyperConfig_Meta{
    /**
     * Array having locales as keys and titles as values
     *
     * @var array
     */
    protected $aTitle = array(
        'en' => 'Eshop custrom fields',
        'ru' => 'Дополнительные поля каталога'
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
            'type' => self::CAPTION_TYPE_STRING,
            'locales' => array(
              'en' => array(
                'name' => 'Menu caption',
                'caption' => 'EshopCustomFields',
              ),
              'ru' => array(
                'name' => 'Заголовок для меню',
                'caption' => 'EshopCustomFields',
              ),
            ),
          ),
          'header' => array(
            'obligatory' => TRUE,
            'type' => self::CAPTION_TYPE_STRING,
            'locales' => array(
              'en' => array(
                'name' => 'Top header',
                'caption' => 'EshopCustomFields',
              ),
              'ru' => array(
                'name' => 'Название (в шапке)',
                'caption' => 'Eshop Custom Fields',
              ),
            ),
          ),
          'description' => array(
            'obligatory' => FALSE,
            'type' => self::CAPTION_TYPE_TEXT,
            'locales' => array(
              'en' => array(
                'name' => 'Admin interface start page module description',
                'caption' => '',
              ),
              'ru' => array(
                'name' => 'Описание модуля для стартовой страницы интерфейса администратора',
                'caption' => '',
              ),
            ),
          ),
        ),
      );
}
