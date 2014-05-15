<?php
/**
 * AmiFiles/Files configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiFiles_Files
 * @version   $Id: AmiFiles_Files_Meta.php 40563 2013-08-12 14:53:26Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiFiles/Files configuration metadata.
 *
 * @package    Config_AmiFiles_Files
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiFiles_Files_Meta extends AMI_HyperConfig_Meta{
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
    protected $isSingleInstance = TRUE;

    /**
     * Flag speficies impossibility of instance deinstallation
     *
     * @var bool
     * @amidev
     * @todo  Remove flag after mod_manager publication
     */
    protected $permanent = TRUE;

    /**
     * Flag specifying that hypermodule / configs has one common data source
     *
     * @var bool
     */
    protected $hasCommonDataSource = TRUE;

    /**
     * Array having locales as keys and captions as values
     *
     * @var array
     */
    protected $aTitle = array(
        'en' => 'Files',
        'ru' => 'Файлы'
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
                'caption' => 'FILES',
              ),
              'ru' => array(
                'name' => 'Название (в шапке)',
                'caption' => 'ФАЙЛЫ',
              ),
            ),
          ),
          'menu_group' => array(
            'obligatory' => FALSE,
            'type' => self::CAPTION_TYPE_STRING,
            'locales' => array(
              'en' => array(
                'name' => 'Group menu caption',
                'caption' => 'File Cabinet',
              ),
              'ru' => array(
                'name' => 'Заголовок группы в меню',
                'caption' => 'Файловый архив',
              ),
            ),
          ),
          'menu' => array(
            'obligatory' => TRUE,
            'type' => self::CAPTION_TYPE_STRING,
            'locales' => array(
              'en' => array(
                'name' => 'Menu caption',
                'caption' => 'File Cabinet',
              ),
              'ru' => array(
                'name' => 'Заголовок для меню',
                'caption' => 'Файловый архив',
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
                'caption' => 'Модуль «Файловый архив» позволяет администратору закачивать на сайт файлы различных форматов, распределять их по категориям и публиковать на сайте ссылки на них. Для каждого файла есть возможность отображать его текстовое описание, размер, количество загрузок и рейтинг. Ссылка на загруженный файл может быть размещена в любом разделе сайта.',
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
                'caption' => 'FILES : CATEGORIES',
              ),
              'ru' => array(
                'name' => 'Заголовок категорийного подмодуля',
                'caption' => 'ФАЙЛЫ : КАТЕГОРИИ',
              ),
            ),
          ),
          'menu' => array(
            'obligatory' => TRUE,
            'type' => self::CAPTION_TYPE_STRING,
            'locales' => array(
              'en' => array(
                'name' => 'Categories menu caption',
                'caption' => 'Categories',
              ),
              'ru' => array(
                'name' => 'Заголовок категорийного подмодуля для меню',
                'caption' => 'Категории',
              ),
            ),
          ),
        ),
        '_import' => array(
          'header' => array(
            'obligatory' => TRUE,
            'type' => self::CAPTION_TYPE_STRING,
            'locales' => array(
              'en' => array(
                'name' => 'Import submodule header',
                'caption' => 'FILES : IMPORT',
              ),
              'ru' => array(
                'name' => 'Заголовок подмодуля импорта',
                'caption' => 'ФАЙЛЫ : ИМПОРТ',
              ),
            ),
          ),
          'menu' => array(
            'obligatory' => TRUE,
            'type' => self::CAPTION_TYPE_STRING,
            'locales' => array(
              'en' => array(
                'name' => 'Import submodule menu caption',
                'caption' => 'Import files',
              ),
              'ru' => array(
                'name' => 'Заголовок подмодуля импорта для меню',
                'caption' => 'Импорт файлов',
              ),
            ),
          ),
        ),
      );
}
