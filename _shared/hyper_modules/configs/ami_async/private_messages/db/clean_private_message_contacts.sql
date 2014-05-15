CREATE TABLE `cms_private_message_contacts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_owner` int(10) unsigned NOT NULL,
  `id_member` int(10) unsigned NOT NULL,
  `nickname` varchar(100) DEFAULT NULL,
  `is_contact` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_owner` (`id_owner`),
  KEY `i_id_member` (`id_member`),
  KEY `i_id_owner_member` (`id_owner`,`id_member`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;