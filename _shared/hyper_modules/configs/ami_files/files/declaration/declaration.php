<?php
/**
 * @version $Id: declaration.php 40338 2013-08-07 09:29:36Z Leontiev Anton $
 */
// {{}}
$oDeclarator->startConfig('##section##', ##taborder##);
$oDeclarator->register('##hypermod##', '##config##', '##modId##', '', AMI_ModDeclarator::INTERFACE_ADMIN | AMI_ModDeclarator::INTERFACE_FRONT | AMI_ModDeclarator::HAS_ASSOC_MODELS | AMI_ModDeclarator::IS_SYS);
$oDeclarator->setAttr('##modId##', 'assoc_db_tables', array('cms_ftypes'));
$oDeclarator->register('##hypermod##', '##config##', '##modId##_cat', '##modId##', AMI_ModDeclarator::INTERFACE_ADMIN | AMI_ModDeclarator::IS_SYS);
$oDeclarator->setAttr('##modId##_cat', 'marker', 'cat');
$oDeclarator->register('ami_data_exchange', '##config##', '##modId##_import', '##modId##', AMI_ModDeclarator::INTERFACE_ADMIN | AMI_ModDeclarator::IS_SYS);
$oDeclarator->setAttr('##modId##_import', 'marker', 'data_exchange');
$oDeclarator->setAttr('##modId##', 'id_pkg', '##pkgId##');
$oDeclarator->setAttr('##modId##', 'id_install', '##installId##');
$dataSource = AMI_Registry::get('AMI/HyperConfig/DataSource/##hypermod##/##config##', FALSE);
if($dataSource){
    $oDeclarator->setAttr('##modId##', 'dataSource', $dataSource);
    $oDeclarator->setAttr('##modId##_cat', 'dataSource', $dataSource . '_cat');
    $oDeclarator->setAttr('##modId##_import', 'dataSource', $dataSource);
}
