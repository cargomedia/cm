<?php

if (!CM_Db_Db::existsTable('cm_site_settings')) {
    CM_Db_Db::exec('CREATE TABLE `cm_site_settings` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `classType` int(10) unsigned NOT NULL,
      `settings` varchar(2000) NOT NULL,
      `name` varchar(32) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
}
