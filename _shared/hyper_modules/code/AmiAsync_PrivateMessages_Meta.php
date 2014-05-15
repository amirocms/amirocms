<?php
/**
 * AmiAsync/PrivateMessages configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiAsync_PrivateMessages
 * @version   $Id: AmiAsync_PrivateMessages_Meta.php 40563 2013-08-12 14:53:26Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiAsync/PrivateMessages configuration metadata.
 *
 * @package    Config_AmiAsync_PrivateMessages
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAsync_PrivateMessages_Meta extends AMI_HyperConfig_Meta{
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
     * @var  bool
     * @amidev
     * @todo Remove flag after mod_manager publication
     */
    protected $permanent = TRUE;

    /**
     * Import instructions
     *
     * @var array
     * @amidev
     */
    protected $aImport = array(
        'sourceModIds' => array('private_messages'),
        'allowedTypes' => array('data', 'options', 'templates')
    );

    /**
     * Array having locales as keys and captions as values
     *
     * @var array
     */
    protected $aTitle = array(
        'en' => 'Private messages',
        'ru' => 'Личные сообщения'
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
                'caption' => 'PRIVATE MESSAGES',
              ),
              'ru' => array(
                'name' => 'Название (в шапке)',
                'caption' => 'ЛИЧНЫЕ СООБЩЕНИЯ',
              ),
            ),
          ),
          'menu' => array(
            'obligatory' => TRUE,
            'type' => self::CAPTION_TYPE_STRING,
            'locales' => array(
              'en' => array(
                'name' => 'Menu caption',
                'caption' => 'Private messages',
              ),
              'ru' => array(
                'name' => 'Menu caption',
                'caption' => 'ЛИЧНЫЕ СООБЩЕНИЯ',
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
                'caption' => 'Чтение и отправка личных сообщений',
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
                'caption' => 'PRIVATE MESSAGES',
              ),
              'ru' => array(
                'name' => 'Заголовок категорийного подмодуля',
                'caption' => 'ЛИЧНЫЕ СООБЩЕНИЯ',
              ),
            ),
          ),
          'menu' => array(
            'obligatory' => TRUE,
            'type' => self::CAPTION_TYPE_STRING,
            'locales' => array(
              'en' => array(
                'name' => 'Categories menu caption',
                'caption' => 'Private messages',
              ),
              'ru' => array(
                'name' => 'Заголовок категорийного подмодуля для меню',
                'caption' => 'Личные сообщения',
              ),
            ),
          ),
        ),
      );
}
