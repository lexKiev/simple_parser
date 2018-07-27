CREATE TABLE `articles` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `article_url` varchar(255) DEFAULT NULL,
  `h1` varchar(255) DEFAULT NULL,
  `content` text,
  `dt_parsed` timestamp NULL DEFAULT NULL,
  `tmp_uniq` char(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
  UNIQUE KEY `UNIQUE` (`article_url`)
) ENGINE=InnoDB AUTO_INCREMENT=266 DEFAULT CHARSET=utf8

CREATE TABLE `images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `article_url` varchar(255) DEFAULT NULL,
  `img_origin_name` varchar(255) DEFAULT NULL,
  `img_new_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE` (`article_url`)
) ENGINE=InnoDB AUTO_INCREMENT=324 DEFAULT CHARSET=utf8