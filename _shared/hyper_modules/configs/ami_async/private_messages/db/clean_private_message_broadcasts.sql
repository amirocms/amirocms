CREATE TABLE `cms_private_message_broadcasts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_created` datetime NOT NULL,
  `header` varchar(255) NOT NULL,
  `body` longtext NOT NULL,
  `licence_type` enum('all','free','trial','paid') NOT NULL DEFAULT 'all',
  `id_broadcast` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `i_date_created` (`date_created`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

