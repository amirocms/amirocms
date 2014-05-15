<?php
/**
 * Module declaration code template.
 *
 * @copyright  Amiro.CMS. All rights reserved.
 * @category   Module
 * @package    Config_AmiClean_AmiSeopult
 * @version    $Id: declaration.php 40338 2013-08-07 09:29:36Z Leontiev Anton $
 * @since      5.14.4
 * @filesource
 */
// {{}}
$oDeclarator->startConfig('##section##', ##taborder##);
$oDeclarator->register('##hypermod##', '##config##', '##modId##', '', AMI_ModDeclarator::INTERFACE_ADMIN | AMI_ModDeclarator::IS_SYS);
$oDeclarator->setAttr('##modId##', 'id_pkg', '##pkgId##');
$oDeclarator->setAttr('##modId##', 'id_install', '##installId##');
