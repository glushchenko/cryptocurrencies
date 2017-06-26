DROP TABLE IF EXISTS `currencies`;

CREATE TABLE `currencies` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `symbol` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `prices`;

CREATE TABLE `prices` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `currency_id` int(11) DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `change24` float DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
