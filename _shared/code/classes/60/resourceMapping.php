<?php
/**
 * API resource mapping.
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Environment
 * @version   $Id: resourceMapping.php 48491 2014-03-06 14:19:04Z Kolesnikov Artem $
 * @since     x.x.x
 * @amidev    Temporary?
 */

// Common mapping
$amiRes = array(
    // Core {
    'core'        => 'AMI_Core',
    // } Core

    // DB {
    'db'  => 'AMI_DB',
    // } DB

    // Cache {
    'cache'  => 'AMI_Cache',
    // } Cache

    // Environment {
    'env/request'      => 'AMI_RequestHTTP',
    'env/template'     => 'AMI_Template',
    'env/template_sys' => 'AMI_TemplateSystem',
    'env/session'      => 'AMI_Session',
    'env/cookie'       => 'AMI_ServerCookie',
    'env/async50'      => 'AMI_PartialAsync',
    'env/file_cache'   => 'AMI_FileCache',
    // 'env/page_mgr' => 'AMI_PageManager',
    // } Environment

    // Response {
    'response' => 'AMI_Response',
    // } Response

    // File {
    'env/file'                          => 'AMI_FileFactory',
    'env/file/local'                    => 'AMI_File_Local',
    'env/file/local/validator/presence' => 'AMI_File_LocalValidatePresence',
    // } File

    // Module table item model {
    'table/item/model/meta' => 'AMI_ModTableItemMeta',
    // } Module table item model

    // E-shop {
    'eshop'         => 'AMI_Eshop',
    'eshop_order'   => 'AMI_EshopOrder',
    'eshop_order/service' => 'EshopOrder_Service',
    'eshop_cart/service' => 'EshopCart_Service',
    'eshop/cart'         => 'AMI_EshopCart',
    'eshop/cart_item'    => 'AMI_CartItem',
    // } E-shop
    
    // E-shop {

    // eshop item props model
    'eshop_item_props/table/model' => 'EshopItemProps_Table',
    'eshop_item_props/table/model/list' => 'EshopItemProps_TableList',
    'eshop_item_props/table/model/item' => 'EshopItemProps_TableItem',
    // kb item props model
    'kb_item_props/table/model' => 'KbItemProps_Table',
    'kb_item_props/table/model/list' => 'KbItemProps_TableList',
    'kb_item_props/table/model/item' => 'KbItemProps_TableItem',
    // portfolio item props model
    'portfolio_item_props/table/model' => 'PortfolioItemProps_Table',
    'portfolio_item_props/table/model/list' => 'PortfolioItemProps_TableList',
    'portfolio_item_props/table/model/item' => 'PortfolioItemProps_TableItem',

    // } E-shop    

    // AmiAsync/PrivateMessages configuration {
    'private_messages/mail/view'    => 'PrivateMessages_EmailView',
    'private_messages/user/handler' => 'PrivateMessages_UserHandler',
    // } Private Messages module

    // SearchHistory module {
    'search_history/table/model'      => 'SearchHistory_Table',
    'search_history/table/model/list' => 'SearchHistory_TableList',
    'search_history/table/model/item' => 'SearchHistory_TableItem',
    // } SearchHistory module

    // Members module {
    'users/table/model'      => 'AmiUsers_Users_Table',
    'users/table/model/list' => 'AmiUsers_Users_TableList',
    'users/table/model/item' => 'AmiUsers_Users_TableItem',
    // CMS-11638
    'users/visitors/table/model'      => 'Visitors_Table',
    'users/visitors/table/model/list' => 'Visitors_TableList',
    'users/visitors/table/model/item' => 'Visitors_TableItem',
    // } Members module

    // Captcha module {
    'captcha'            => 'AMI_Captcha',
    'captcha/image'      => 'AMI_CaptchaImage',
    // } Captcha module

    // Pages (Site manager) module {
    'pages/table/model'      => 'Pages_Table',
    'pages/table/model/list' => 'Pages_TableList',
    'pages/table/model/item' => 'Pages_TableItem',
    // } Pages (Site manager) module

    // User Source Applications
    'user_source_app' => 'AMI_UserSourceApp',
    'user_source_app/drivers/ami_twitter' => 'Twitter_UserSourceAppDriver',
    'user_source_app/drivers/ami_vkontakte' => 'VKontakte_UserSourceAppDriver',
    'user_source_app/drivers/ami_facebook' => 'Facebook_UserSourceAppDriver',
    'user_source_app/drivers/ami_loginza' => 'Loginza_UserSourceAppDriver',
    // } User Source Applications

    // External Auth Drivers {
    'extauth/drivers/vbulletin' => 'VBulletin_ExternalAuthDriver',
    // } External Auth Drivers

    // { Private Messages
    'private_message_notifier/service' => 'PrivateMessagesNotifier_Service',
	'private_messages/service/view'    => 'PrivateMessages_ServiceView',
    // } Private Messages

    // { User Rating
    'users/rating'                   => 'AMI_UserRating',
    'users/rating/service'           => 'UserRating_Service',
    'users/rating/table/model'       => 'UserRatingHistory_Table',
    'users/rating/table/model/list'  => 'UserRatingHistory_TableList',
    'users/rating/table/model/item'  => 'UserRatingHistory_TableItem',
    // } User Rating

    // { Session
    'env/session/table/model'       => 'Session_Table',
    'env/session/table/model/list'  => 'Session_TableList',
    'env/session/table/model/item'  => 'Session_TableItem',
    // } Session

    // { Payment drivers
    'payment_drivers/table/model'       => 'PaymentDrivers_Table',
    'payment_drivers/table/model/list'  => 'PaymentDrivers_TableList',
    'payment_drivers/table/model/item'  => 'PaymentDrivers_TableItem',
    // } Payment drivers

    // FAQ module {
    'faq/table/item/model/meta' => 'Faq_ModTableItemMeta',
    // } FAQ module

    // List pagination {
    'list/pagination'      => 'AMI_ModListPagination',
    'list/pagination/view' => 'AMI_ModListPaginationView',
    // } List pagination

    // { Data import drivers
    'import_driver/ami_rss'     => 'AmiClean_DataImport_AmiRssImport',
    'import_driver/ami_csv'     => 'AmiClean_DataImport_AmiCsvImport',
    // } Data import drivers

    'ext_eshop_category/module/controller/frn' => 'AmiExt_EshopCategory',

    'storage/fs'  => 'AMI_Storage_FS',
    'storage/tpl' => 'AMI_Storage_Template'
);

if(AMI_Registry::get('side') == 'frn'){
    // Front mapping
    $amiRes += array(
        'module/common/view/frn' => 'AMI_ModCommonViewFrn',

        'ext_image'                          => 'AmiExt_Image_Frn',
        'ext_image/module/controller/frn'    => 'AmiExt_Image_Frn',
        'ext_category'                       => 'AmiExt_Category_Adm',
        'ext_category/module/controller/adm' => 'AmiExt_Category_Adm',

        'specblock_array_iterator/table/model'      => 'AMI_ModSpecblock_ArrayIterator',
        'specblock_array_iterator/table/model/item' => 'AMI_ModSpecblock_ArrayIteratorItem',
        'specblock_array_iterator/table/model/list' => 'AMI_ModSpecblock_ArrayIteratorList'
    );
}

return $amiRes;
