<?php

if (!CM_Db_Db::existsTable('cm_site_settings')) {
    CM_Db_Db::exec('CREATE TABLE `cm_site_settings` (
      `id` int(10) unsigned NOT NULL,
      `siteId` int(10) unsigned NOT NULL,
      `configuration` varchar(2000) NOT NULL,
      `name` varchar(32) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `siteId`(`siteId`),
      UNIQUE KEY `name` (`name`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
}
