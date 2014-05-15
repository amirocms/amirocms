<?php
/**
 * Module declaration code template.
 * 
 * @copyright Amiro.CMS. All rights reserved.
 * @category  Module
 * @package   Module_##modId##
 * @version   $Id: declaration.php 40338 2013-08-07 09:29:36Z Leontiev Anton $
 */
// {{}}
$oDeclarator->startConfig('##section##', ##taborder##);
$oDeclarator->register('ami_clean', 'data_import', '##modId##', '', AMI_ModDeclarator::INTERFACE_ADMIN/* | AMI_ModDeclarator::INTERFACE_FRONT */);
$oDeclarator->setAttr('##modId##', 'id_pkg', '##pkgId##');
$oDeclarator->setAttr('##modId##', 'id_install', '##installId##');
