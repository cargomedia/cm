<?php

if (!CM_Db_Db::existsTable('cm_site_settings')) {
    CM_Db_Db::exec('CREATE TABLE `cm_site_settings` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `siteType` int(10) unsigned DEFAULT NULL,
      `name` varchar(32) NOT NULL,
      `configuration` varchar(2000) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `siteType`(`siteType`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
}
