<?php
/**
 * @copyright Amiro.CMS. All rights reserved. Changes are not allowed.
 * @category  AMI
 * @package   Module_##modId##
 * @version   $Id: --modId--_Adm.php 40175 2013-07-31 12:20:28Z Leontiev Anton $
 * @amidev    Temporary
 */

/**
 * ##modId## module admin action controller.
 *
 * @package    Module_##modId##
 * @subpackage Controller
 * @amidev     Temporary
 */
class ##modId##_Adm extends AmiExt_CustomFields_Adm{
}

/**
 * ##modId## module admin view.
 *
 * @package    Module_##modId##
 * @subpackage View
 * @amidev     Temporary
 */
class ##modId##_ViewAdm extends AmiExt_CustomFields_ViewAdm{
}

/**
 * ##modId## module service class.
 *
 * @package    Module_##modId##
 * @amidev     Temporary
 */
class ##modId##_Service extends AmiExt_CustomFields_Service{
    /**
     * Returns ##modId##_Service instance.
     *
     * @return ##modId##_Service
     */
    public static function getInstance(){
        if(self::$oInstance == null){
            self::$oInstance = new self;
        }
        return self::$oInstance;
    }
}
