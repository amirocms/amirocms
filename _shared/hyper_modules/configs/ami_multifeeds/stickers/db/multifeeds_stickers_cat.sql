CREATE TABLE `cms_##modId##_cat` (
  `id` int(11) NOT NULL auto_increment,
  `public` tinyint(1) NOT NULL default '1',
  `header` varchar(255) NOT NULL default '',
  `announce` mediumtext NOT NULL,
  `num_items` int(11) NOT NULL default '0',
  `num_public_items` int(11) NOT NULL default '0',
  `position` int(11) unsigned NOT NULL default '0',
  `lang` char(3) NOT NULL default 'en',
  `body` mediumtext NOT NULL,
  `date_modified` datetime NOT NULL default '0000-00-00 00:00:00',
  `sublink` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `i_lang` (`lang`),
  KEY `i_header` (`header`),
  KEY `i_position` (`position`),
  KEY `i_public` (`public`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
