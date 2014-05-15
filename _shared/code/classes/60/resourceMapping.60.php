<?php
/**
 * Extra resource mapping (6.0 only).
 *
 * @copyright Amiro.CMS. All rights reserved. The changes are undesirable and dangerous.
 * @category  AMI
 * @package   Environment
 * @version   $Id: resourceMapping.60.php 48491 2014-03-06 14:19:04Z Kolesnikov Artem $
 * @since     x.x.x
 * @amidev    Temporary?
 */

return array(
    // List actions {

    'list/actions'            => 'AMI_ModListActions',
    'list/actions/group'      => 'AMI_ModListGroupActions',
    'list/actions/position'   => 'AMI_ModListPositionActions',

    // } List actions

    // E-shop {

    'eshop_item/serve/eshop_order' => 'EshopItem_Serve_EshopOrder',

    // } E-shop

/*
    // Modules custom fields module {

    'modules_custom_fields/table/model'      => 'ModulesCustomFields_Table',
    'modules_custom_fields/table/model/list' => 'ModulesCustomFields_TableList',
    'modules_custom_fields/table/model/item' => 'ModulesCustomFields_TableItem',

    // } Modules custom fields module
    // Modules datasets module {

    'modules_datasets/table/model'      => 'ModulesDatasets_Table',
    'modules_datasets/table/model/list' => 'ModulesDatasets_TableList',
    'modules_datasets/table/model/item' => 'ModulesDatasets_TableItem',

    // } Modules datasets module

    // Relations module {

    'relations/table/model'      => 'Relations_Table',
    'relations/table/model/list' => 'Relations_TableList',
    'relations/table/model/item' => 'Relations_TableItem',

    // } Relations module
    // Rating extension {

    'ext_rating/ext/view/frn' => 'ExtRating_ViewFrn',

    // } Rating extension
    // Relations extension {

    'ext_relations/ext/view/frn' => 'ExtRelations_ViewFrn',

    // } Relations extension
    // RSS extension {

    'ext_rss'      => 'AmiExtRss',
    'ext_rss/ext/view' => 'ExtRss_View',

    // } RSS extension
    // Twist prevention (SPAM filter) extension {

    'ext_twist_prevention'      => 'AmiExtTwistPrevention',
    'ext_twist_prevention/ext/view' => 'ExtTwistPrevention_View',

    // } Twist prevention (SPAM filter) extension
*/
    // Advertising groups module {

    'adv_groups/table/model'      => 'AdvGroups_Table',
    'adv_groups/table/model/list' => 'AdvGroups_TableList',
    'adv_groups/table/model/item' => 'AdvGroups_TableItem',

    // } Advertising groups module
    // Advertising places module {

    'adv_places/table/model'      => 'AdvPlaces_Table',
    'adv_places/table/model/list' => 'AdvPlaces_TableList',
    'adv_places/table/model/item' => 'AdvPlaces_TableItem',

    // } Advertising places module
    // Access rights {

    'ami_sys_users/table/model'      => 'AmiSysUsers_Table',
    'ami_sys_users/table/model/list' => 'AmiSysUsers_TableList',
    'ami_sys_users/table/model/item' => 'AmiSysUsers_TableItem',

    'ami_sys_users_joined_groups/table/model'      => 'AmiSysUsersJoinedGroups_Table',
    'ami_sys_users_joined_groups/table/model/list' => 'AmiSysUsersJoinedGroups_TableList',
    'ami_sys_users_joined_groups/table/model/item' => 'AmiSysUsersJoinedGroups_TableItem',

    // } Access rights
    // Discussion {

    'ext_disucssion/service' => 'AmiDiscussion_Discussion_Service',

    // } Discussion
);
