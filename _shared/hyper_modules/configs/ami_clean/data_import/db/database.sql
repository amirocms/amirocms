CREATE TABLE `cms_##modId##` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `driver_name` varchar(32) NOT NULL,
 `header` varchar(128) NOT NULL,
 `frequency` int(11) NOT NULL,
 `update_start` varchar(16) DEFAULT NULL,
 `driver_data` text,
 `date_created` datetime NOT NULL,
 `date_modified` datetime DEFAULT NULL,
 `date_lastimport` datetime DEFAULT NULL,
 `date_nextimport` datetime DEFAULT NULL,
 `errors_num` int(11) NOT NULL DEFAULT '0',
 `last_success` tinyint(1) NOT NULL DEFAULT '0',
 `public` tinyint(1) NOT NULL DEFAULT '0',
 `executed` int(11) NOT NULL DEFAULT '0',
 `blocked_till` datetime DEFAULT NULL,
 `allow_duplicate` tinyint(1) NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

CREATE TABLE `cms_##modId##` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `id_task` int(11) NOT NULL,
 `data_hash` char(32) NOT NULL,
 PRIMARY KEY (`id`),
 KEY `record_hash` (`data_hash`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;