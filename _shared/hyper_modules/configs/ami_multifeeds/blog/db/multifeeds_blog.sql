CREATE TABLE `cms_##modId##` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `id_cat` int(11) NOT NULL DEFAULT '1',
 `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `announce` mediumtext NOT NULL,
 `body` mediumtext NOT NULL,
 `lang` char(3) NOT NULL DEFAULT 'en',
 `header` varchar(255) NOT NULL DEFAULT '',
 `public` tinyint(1) NOT NULL DEFAULT '1',
 `hide_in_list` tinyint(4) NOT NULL DEFAULT '0',
 `sticky` tinyint(3) unsigned NOT NULL DEFAULT '0',
 `date_sticky_till` datetime DEFAULT NULL,
 `archive` tinyint(1) NOT NULL DEFAULT '0',
 `date_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `sublink` varchar(128) NOT NULL DEFAULT '',
 `link_alias` varchar(128) NOT NULL DEFAULT '',
 `sm_data` blob NOT NULL,
 `id_site` int(11) NOT NULL DEFAULT '0',
 `id_page` int(11) NOT NULL DEFAULT '0',
 `id_owner` int(11) NOT NULL DEFAULT '0',
 `sys_rights_r` bigint(20) unsigned NOT NULL DEFAULT '0',
 `sys_rights_w` bigint(20) unsigned NOT NULL DEFAULT '0',
 `sys_rights_d` bigint(20) unsigned NOT NULL DEFAULT '0',
 `adv_place` int(11) NOT NULL DEFAULT '0',
 `shown_details` int(11) NOT NULL DEFAULT '0',
 `shown_items` int(11) NOT NULL DEFAULT '0',
 `position` int(10) unsigned NOT NULL DEFAULT '0',
 `details_noindex` tinyint(3) NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`),
 KEY `i_date_sticky_till` (`date_sticky_till`),
 KEY `i_sticky` (`sticky`,`date_sticky_till`),
 KEY `i_archive` (`archive`),
 KEY `i_date_created` (`date_created`),
 KEY `i_header` (`header`),
 KEY `i_id_cat` (`id_cat`),
 KEY `i_id_owner` (`id_owner`),
 KEY `i_id_page` (`id_page`),
 KEY `i_id_site` (`id_site`),
 KEY `i_lang` (`lang`),
 KEY `i_link_alias` (`link_alias`),
 KEY `i_position` (`position`),
 KEY `i_public` (`public`),
 KEY `i_hide_in_list` (`hide_in_list`),
 KEY `i_sublink` (`sublink`),
 KEY `i_sys_rights_d` (`sys_rights_d`),
 KEY `i_sys_rights_r` (`sys_rights_r`),
 KEY `i_sys_rights_w` (`sys_rights_w`),
 FULLTEXT KEY `i_announce` (`announce`)
) ENGINE=MyISAM AUTO_INCREMENT=147 DEFAULT CHARSET=utf8