-- Дамп структуры для таблица bazalt_cms.cms_languages
DROP TABLE IF EXISTS `cms_languages`;
CREATE TABLE IF NOT EXISTS `cms_languages` (
  `id` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `ico` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица bazalt_cms.cms_themes
DROP TABLE IF EXISTS `cms_themes`;
CREATE TABLE IF NOT EXISTS `cms_themes` (
  `id` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'default',
  `settings` text COLLATE utf8_unicode_ci,
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_hidden` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Дамп структуры для таблица bazalt_cms.cms_sites
DROP TABLE IF EXISTS `cms_sites`;
CREATE TABLE IF NOT EXISTS `cms_sites` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '/',
  `secret_key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `theme_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `language_id` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `languages` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  `is_subdomain` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_multilingual` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_allow_indexing` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `site_id` int(10) unsigned DEFAULT NULL,
  `is_redirect` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain` (`domain`),
  KEY `FK_cms_sites_cms_sites` (`site_id`),
  KEY `FK_cms_sites_cms_users` (`user_id`),
  KEY `FK_cms_sites_cms_languages` (`language_id`),
  KEY `FK_cms_sites_cms_themes` (`theme_id`),
  CONSTRAINT `FK_cms_sites_cms_languages` FOREIGN KEY (`language_id`) REFERENCES `cms_languages` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `FK_cms_sites_cms_sites` FOREIGN KEY (`site_id`) REFERENCES `cms_sites` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `FK_cms_sites_cms_themes` FOREIGN KEY (`theme_id`) REFERENCES `cms_themes` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `FK_cms_sites_cms_users` FOREIGN KEY (`user_id`) REFERENCES `cms_users` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица bazalt_cms.cms_sites_ref_users
DROP TABLE IF EXISTS `cms_sites_ref_users`;
CREATE TABLE IF NOT EXISTS `cms_sites_ref_users` (
  `site_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `last_activity` datetime DEFAULT NULL,
  `session_id` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`site_id`,`user_id`),
  KEY `FK_cms_sites_ref_users_cms_users` (`user_id`),
  CONSTRAINT `FK_cms_sites_ref_users_cms_sites` FOREIGN KEY (`site_id`) REFERENCES `cms_sites` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_cms_sites_ref_users_cms_users` FOREIGN KEY (`user_id`) REFERENCES `cms_users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица bazalt_cms.cms_users
DROP TABLE IF EXISTS `cms_users`;
CREATE TABLE IF NOT EXISTS `cms_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(60) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstname` varchar(255) DEFAULT NULL COMMENT 'Ім''я',
  `secondname` varchar(255) DEFAULT NULL COMMENT 'Прізвище',
  `patronymic` varchar(255) DEFAULT NULL COMMENT 'По-батькові',
  `gender` enum('unknown','male','female') NOT NULL DEFAULT 'unknown' COMMENT 'Стать',
  `birth_date` date DEFAULT NULL COMMENT 'Дата народження',
  `email` varchar(60) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `last_activity` datetime DEFAULT NULL,
  `session_id` varchar(50) DEFAULT NULL,
  `is_god` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;