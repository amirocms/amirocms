CREATE TABLE `cms_##modId##` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `id_cat` int(11) NOT NULL DEFAULT '1',
 `author` varchar(100) NOT NULL DEFAULT '',
 `email` varchar(60) NOT NULL DEFAULT '',
 `question` mediumtext NOT NULL,
 `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `answer` mediumtext NOT NULL,
 `public` tinyint(1) NOT NULL DEFAULT '1',
 `hide_in_list` tinyint(4) NOT NULL DEFAULT '0',
 `sticky` tinyint(3) unsigned NOT NULL DEFAULT '0',
 `date_sticky_till` datetime DEFAULT NULL,
 `lang` char(3) NOT NULL DEFAULT 'en',
 `sublink` varchar(128) NOT NULL DEFAULT '',
 `link_alias` varchar(128) NOT NULL DEFAULT '',
 `sm_data` blob NOT NULL,
 `id_page` int(11) NOT NULL DEFAULT '0',
 `date_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `position` int(10) unsigned NOT NULL DEFAULT '0',
 `subject` varchar(255) NOT NULL,
 `details_noindex` tinyint(3) NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`),
 KEY `i_date_sticky_till` (`date_sticky_till`),
 KEY `i_sticky_date_sticky_till` (`sticky`,`date_sticky_till`),
 KEY `i_subject` (`subject`),
 KEY `i_id_cat` (`id_cat`),
 KEY `i_id_page` (`id_page`),
 KEY `i_lang` (`lang`),
 KEY `i_link_alias` (`link_alias`),
 KEY `i_position` (`position`),
 KEY `i_public` (`public`),
 KEY `i_hide_in_list` (`hide_in_list`),
 KEY `i_sublink` (`sublink`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8