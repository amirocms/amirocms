<?php
/**
 * AmiMultifeeds5/PhotoGallery configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiMultifeeds5_PhotoGallery
 * @version   $Id: AmiMultifeeds5_Photoalbum_Meta.php 43700 2013-11-15 06:30:30Z Kolesnikov Artem $
 * @since     x.x.x
 * @amidev
 */

/**
 * AmiMultifeeds5/PhotoGallery configuration metadata.
 *
 * @package    Config_AmiMultifeeds5_PhotoGallery
 * @subpackage Model
 * @since      x.x.x
 * @amidev
 */
class AmiMultifeeds5_Photoalbum_Meta extends AMI_HyperConfig_Meta{
    /**
     * Version
     *
     * @var string
     */
    protected $version = '1.0';

    /**
     * Only one instance per config allowed
     *
     * @var  bool
     * @todo Remove flag after mod_manager publication
     */
    // protected $isSingleInstance = TRUE;

    /**
     * Flag speficies impossibility of instance deinstallation
     *
     * @var  bool
     * @amidev
     * @todo Remove flag after mod_manager publication
     */
    // protected $permanent = TRUE;

    /**
     * Import instructions
     *
     * @var array
     * @amidev
     */
    protected $aImport = array(
        'sourceModIds' => array('photoalbum'),
        'allowedTypes' => array('data', 'options', 'templates')
    );

    /**
     * Array having locales as keys and captions as values
     *
     * @var array
     */
    protected $aTitle = array(
        'en' => 'Gallery',
        'ru' => 'Фотоальбомы'
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
          'header' => array(
            'obligatory' => TRUE,
            'type' => self::CAPTION_TYPE_STRING,
            'locales' => array(
              'en' => array(
                'name' => 'Top header',
                'caption' => 'PHOTOALBUMS : PHOTOS',
              ),
              'ru' => array(
                'name' => 'Название (в шапке)',
                'caption' => 'ФОТОАЛЬБОМЫ : ФОТОГРАФИИ',
              ),
            ),
          ),
          'menu_group' => array(
            'obligatory' => FALSE,
            'type' => self::CAPTION_TYPE_STRING,
            'locales' => array(
              'en' => array(
                'name' => 'Group menu caption',
                'caption' => 'Gallery',
              ),
              'ru' => array(
                'name' => 'Заголовок группы в меню',
                'caption' => 'Фотоальбомы',
              ),
            ),
          ),
          'menu' => array(
            'obligatory' => TRUE,
            'type' => self::CAPTION_TYPE_STRING,
            'locales' => array(
              'en' => array(
                'name' => 'Menu caption',
                'caption' => 'Photos',
              ),
              'ru' => array(
                'name' => 'Заголовок для меню',
                'caption' => 'Фотографии',
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
                'caption' => 'Модуль «Фотоальбомы» позволяет загружать на сайт графические изображения, давать им наименования и описания, группировать их в альбомы. Посетители сайта могут добавлять комментарии и давать оценку изображениям.',
              ),
            ),
          ),
          'specblock' => array(
            'obligatory' => TRUE,
            'type' => self::CAPTION_TYPE_STRING,
            'locales' => array(
              'en' => array(
                'name' => 'Specblock caption for site manager',
                'caption' => 'Photoalbum announce',
              ),
              'ru' => array(
                'name' => 'Название спецблока для менеджера сайта',
                'caption' => 'Анонс фотоальбома',
              ),
            ),
          ),
          'specblock_desc' => array(
            'obligatory' => FALSE,
            'type' => self::CAPTION_TYPE_STRING,
            'locales' => array(
              'en' => array(
                'name' => 'Specblock description for site manager',
                'caption' => 'Photoalbum announce strip',
              ),
              'ru' => array(
                'name' => 'Описание спецблока для менеджера сайта',
                'caption' => 'Лента анонса фотоальбома',
              ),
            ),
          ),
        ),
        '_cat' => array(
          'header' => array(
            'obligatory' => TRUE,
            'type' => self::CAPTION_TYPE_STRING,
            'locales' => array(
              'en' => array(
                'name' => 'Categories header',
                'caption' => 'PHOTOALBUMS : ALBUMS',
              ),
              'ru' => array(
                'name' => 'Заголовок категорийного подмодуля',
                'caption' => 'ФОТОАЛЬБОМЫ : АЛЬБОМЫ',
              ),
            ),
          ),
          'menu' => array(
            'obligatory' => TRUE,
            'type' => self::CAPTION_TYPE_STRING,
            'locales' => array(
              'en' => array(
                'name' => 'Categories menu caption',
                'caption' => 'Albums',
              ),
              'ru' => array(
                'name' => 'Заголовок категорийного подмодуля для меню',
                'caption' => 'Альбомы',
              ),
            ),
          ),
        ),
        '_data_exchange' => array(
          'header' => array(
            'obligatory' => TRUE,
            'type' => self::CAPTION_TYPE_STRING,
            'locales' => array(
              'en' => array(
                'name' => 'Import submodule header',
                'caption' => 'PHOTOALBUMS : IMPORT PICTURES',
              ),
              'ru' => array(
                'name' => 'Заголовок подподмодуля импорта',
                'caption' => 'ФОТОАЛЬБОМЫ : ИМПОРТ ИЗОБРАЖЕНИЙ',
              ),
            ),
          ),
          'menu' => array(
            'obligatory' => TRUE,
            'type' => self::CAPTION_TYPE_STRING,
            'locales' => array(
              'en' => array(
                'name' => 'Import submodule menu caption',
                'caption' => 'Photo import',
              ),
              'ru' => array(
                'name' => 'Заголовок подподмодуля импорта для меню',
                'caption' => 'Импорт фотографий',
              ),
            ),
          ),
        ),
      );
}
