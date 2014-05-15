<?php
/**
 * AmiFake/GoogleSitemap configuration.
 *
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Config_AmiAccessRights_UsersPopup
 * @version   $Id: AmiAccessRights_UsersPopup_Meta.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @since     x.x.x
 * @amidev    Temporary
 */

/**
 * AmiFake/GoogleSitemap configuration metadata.
 *
 * @package    Config_AmiAccessRights_UsersPopup
 * @subpackage Model
 * @since      x.x.x
 * @amidev     Temporary
 */
class AmiAccessRights_UsersPopup_Meta extends AMI_HyperConfig_Meta{
    /**
     * Version
     *
     * @var string
     */
    protected $version = '1.0';

    /**
     * Flag specifying that hypermodule configs can have only one instance per config
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
        'en' => 'Google Sitemap',
        'ru' => 'Google Sitemap'
    );
}
