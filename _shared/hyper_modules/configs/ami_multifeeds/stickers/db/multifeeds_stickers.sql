CREATE TABLE `cms_##modId##` (
  `id` int(11) NOT NULL auto_increment,
  `id_cat` int(11) NOT NULL default '1',
  `announce` mediumtext NOT NULL,
  `body` mediumtext NOT NULL,
  `header` varchar(255) NOT NULL default '',
  `date_created` datetime NOT NULL default '0000-00-00 00:00:00',
  `public` tinyint(1) NOT NULL default '1',
  `lang` char(3) NOT NULL default 'en',
  `position` int(10) unsigned NOT NULL default '0',
  `sm_data` blob NOT NULL,
  `date_modified` datetime NOT NULL default '0000-00-00 00:00:00',
  `details_noindex` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `i_id_cat` (`id_cat`),
  KEY `i_lang` (`lang`),
  KEY `i_public` (`public`),
  KEY `i_position` (`position`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
