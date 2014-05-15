<?php
/**
 * AmiClean/DataImport configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiClean_DataImport
 * @version   $Id: AmiClean_DataImport_Meta.php 43508 2013-11-12 13:06:34Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiClean/DataImport configuration metadata.
 *
 * @package    Config_AmiClean_DataImport
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_DataImport_Meta extends AMI_HyperConfig_Meta{
    /**
     * Only one instance per config allowed
     *
     * @var bool
     */
    protected $isSingleInstance = true;

    /**
     * Flag speficies impossibility of instance deinstallation
     *
     * @var bool
     * @amidev
     */
    protected $permanent = true;

    /**
     * Array having locales as keys and titles as values
     *
     * @var array
     */
    protected $aTitle = array(
        'en' => 'Data import schedule',
        'ru' => 'Расписание импорта'
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
                'caption' => 'Data import schedule',
              ),
              'ru' => array(
                'name' => 'Заголовок для меню',
                'caption' => 'Расписание импорта',
              ),
            ),
          ),
          'header' => array(
            'obligatory' => TRUE,
            'type' => self::CAPTION_TYPE_STRING,
            'locales' => array(
              'en' => array(
                'name' => 'Top header',
                'caption' => 'DATA IMPORT SCHEDULE',
              ),
              'ru' => array(
                'name' => 'Название (в шапке)',
                'caption' => 'РАСПИСАНИЕ ИМПОРТА',
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
        )
      );
}
