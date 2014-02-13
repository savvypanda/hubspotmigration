CREATE TABLE IF NOT EXISTS `#__hubspotmigration_migrations` (
  `hubspotmigration_migration_id` INT(11) NOT NULL AUTO_INCREMENT,
  `k2_category_id` INT(11) NOT NULL,
  `blog_group_id` VARCHAR(64) NOT NULL,
  PRIMARY KEY (`hubspotmigration_migration_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__hubspotmigration_blogs` (
  `hubspotmigration_blog_id` int(11) NOT NULL AUTO_INCREMENT,
  `blog_group_id` INT(11) NOT NULL,
  `k2_item_id` INT(11),
  `title` VARCHAR(255),
  `k2_link` VARCHAR(255),
  `hb_blog_id` VARCHAR(64),
  `hb_blog_link` VARCHAR(255),
  `link_mapped` TINYINT(1) DEFAULT 0,
  `status` VARCHAR(16),
  `details` VARCHAR(255),
  `api_response` TEXT,
  UNIQUE(`k2_item_id`),
  PRIMARY KEY (`hubspotmigration_blog_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__hubspotmigration_textmappings` (
	`hubspotmigration_textmapping_id` INT(11) NOT NULL AUTO_INCREMENT,
	`regex_from` VARCHAR(255) NOT NULL,
	`regex_to` VARCHAR(255) NOT NULL,
	`is_regex` TINYINT(1) DEFAULT 0,
	`ordering` int(11) DEFAULT 0,
	`enabled` TINYINT(1) DEFAULT 0,
	PRIMARY KEY (`hubspotmigration_textmapping_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__hubspotmigration_authors` (
	`hubspotmigration_author_id` INT(11) NOT NULL AUTO_INCREMENT,
	`juser_id` INT(11) DEFAULT NULL,
	`juser_name` VARCHAR(128) DEFAULT NULL,
	`hb_author_id` VARCHAR(64) DEFAULT NULL,
	`hb_author_name` VARCHAR(128) DEFAULT NULL,
	`auto_created` TINYINT(1) DEFAULT 0,
	PRIMARY KEY (`hubspotmigration_author_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__hubspotmigration_files` (
	`hubspotmigration_file_id` INT(11) NOT NULL AUTO_INCREMENT,
	`jpath` VARCHAR(255) NOT NULL,
	`hb_file_id` VARCHAR(64),
	`hb_file_path` VARCHAR(255),
	UNIQUE(`jpath`),
	PRIMARY KEY (`hubspotmigration_file_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

-- CREATE TABLE IF NOT EXISTS `#__hubspotmigration_folders` (
-- 	`hubspotmigration_folder_id` INT(11) NOT NULL AUTO_INCREMENT,
-- 	`jpath` VARCHAR(255) NOT NULL,
-- 	`hb_folder_id` VARCHAR(64),
-- 	`hb_folder_path` VARCHAR(255),
-- 	`auto_created` TINYINT(1) DEFAULT 0,
--   UNIQUE(`jpath`),
--   PRIMARY KEY (`hubspotmigration_folder_id`)
-- ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
