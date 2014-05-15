CREATE TABLE `cms_##modId##` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_owner` int(10) unsigned NOT NULL,
  `id_sender` int(10) unsigned NOT NULL,
  `id_recipient` int(10) unsigned NOT NULL,
  `date_created` datetime NOT NULL,
  `header` varchar(255) NOT NULL,
  `id_body` int(10) unsigned NOT NULL,
  `is_read` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_replied` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_broadcast` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `i_incoming` (`id_owner`,`id_recipient`,`is_deleted`,`date_created`),
  KEY `i_incoming_read` (`id_owner`,`id_recipient`,`is_deleted`,`is_read`,`date_created`),
  KEY `i_sent` (`id_owner`,`id_sender`,`is_deleted`,`date_created`),
  KEY `i_deleted` (`id_owner`,`is_deleted`,`date_created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

