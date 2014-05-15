<?php
/**
 * AmiClean/AmiSeopult configuration.
 *
 * @copyright Amiro.CMS. All rights reserved.
 * @category  Module
 * @package   Config_AmiClean_AmiSeopult
 * @version   $Id: AmiClean_AmiSeopult_Meta.php 48617 2014-03-12 07:37:57Z Kolesnikov Artem $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiClean/AmiSeopult configuration metadata.
 *
 * @package    Config_AmiClean_AmiSeopult
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_AmiSeopult_Meta extends AMI_HyperConfig_Meta{
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
    protected $isSingleInstance = TRUE;

    /**
     * Exact instance modId
     *
     * @var string
     */
    protected $instanceId = 'ami_seopult';

    /**
     * Flag specifies possibility of local PHP-code generation
     *
     * @var   bool
     */
    protected $canGenCode = FALSE;

    /**
     * Array having locales as keys and captions as values
     *
     * @var array
     */
    protected $aTitle = array(
        'en' => 'SeoPult',
        'ru' => 'Продвижение с SeoPult'
    );

    /**
     * Array having locales as keys and meta data as values
     *
     * @var array
     */
    protected $aInfo = array(
        'en' => array(
            'description' => 'SeoPult',
            'author'      => '<a href="http://www.amirocms.com" target="_blank">Amiro.CMS</a>'
        ),
        'ru' => array(
            'description' => 'Продвижение с SeoPult',
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
                'caption' => 'SEOPULT',
              ),
              'ru' => array(
                'name' => 'Название (в шапке)',
                'caption' => 'Продвижение с SeoPult',
              ),
            ),
          ),
          'menu' => array(
            'obligatory' => TRUE,
            'type' => self::CAPTION_TYPE_STRING,
            'locales' => array(
              'en' => array(
                'name' => 'Menu caption',
                'caption' => 'SeoPult',
              ),
              'ru' => array(
                'name' => 'Заголовок для меню',
                'caption' => 'Продвижение с SeoPult',
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
                'caption' => 'Плагин SeoPult для продвижения сайта в поисковых системах',
              ),
            ),
          ),
        ),
      );
}
