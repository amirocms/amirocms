<?php
/**
 * AmiEshopShipping hypermodule.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Hyper_AmiEshopShipping
 * @version   $Id: Hyper_AmiEshopShipping_Meta.php 45064 2013-12-05 09:27:10Z Leontiev Anton $
 * @since     6.0.2
 * @amidev    Temporary
 */

/**
 * AmiEshopShipping hypermodule metadata.
 *
 * @package    Hyper_AmiEshopShipping
 * @subpackage Model
 * @since      6.0.2
 * @amidev     Temporary
 */
class Hyper_AmiEshopShipping_Meta extends AMI_Hyper_Meta{
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
     * Flag speficies impossibility of instance deinstallation
     *
     * @var bool
     * @amidev
     */
    protected $permanent = TRUE;

    /**
     * Array having locales as keys and captions as values
     *
     * @var array
     */
    protected $aTitle = array(
        'en' => 'Shipping',
        'ru' => 'Доставка'
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
