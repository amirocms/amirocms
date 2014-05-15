<?php
/**
 * AmiAntispam hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiAntispam
 * @version   $Id: Hyper_AmiAntispam_Meta.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiAntispam hypermodule metadata.
 *
 * @package    Hyper_AmiAntispam
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiAntispam_Meta extends AMI_Hyper_Meta{
    /**
     * Version
     *
     * @var string
     */
    protected $version = '1.0';

    /**
     * Array having locales as keys and captions as values
     *
     * @var array
     */
    protected $aTitle = array(
        'en' => 'Twist Prevention',
        'ru' => 'Защита от СПАМа'
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
}