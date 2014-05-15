<?php
/**
 * AmiCatalog hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiCatalog
 * @version   $Id: Hyper_AmiCatalog_Meta.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiCatalog hypermodule metadata.
 *
 * @package    Hyper_AmiCatalog
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class Hyper_AmiCatalog_Meta extends AMI_Hyper_Meta{
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
        'en' => 'Catalog',
        'ru' => 'Каталог'
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
