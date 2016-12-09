<?php

if (!CM_Db_Db::existsTable('cm_site_settings')) {
    CM_Db_Db::exec('CREATE TABLE `cm_site_settings` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `siteId` int(10) unsigned DEFAULT NULL,
      `name` varchar(32) DEFAULT NULL,
      `configuration` varchar(2000) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `siteId`(`siteId`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
}
