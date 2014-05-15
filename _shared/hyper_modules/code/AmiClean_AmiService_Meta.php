<?php
/**
 * AmiClean/AmiService configuration metadata.
 *
 * @package    Config_AmiClean_AmiService
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiClean_AmiService_Meta extends AMI_HyperConfig_Meta{
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
        'en' => 'Service module',
        'ru' => 'Сервисный модуль'
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
                        'caption' => 'Service module'
                    ),
                    'ru' => array(
                        'name'    => 'Заголовок для меню',
                        'caption' => 'Сервисный модуль'
                    )
                )
            )
        )
    );
}
