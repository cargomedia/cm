<?php

if (!CM_Db_Db::existsTable('cm_site_setting')) {

    CM_Db_Db::exec('CREATE TABLE `cm_site_setting` (
      `siteId` int(10) unsigned NOT NULL,
      `key` varchar(64) NOT NULL,
      `value` varchar(255) NOT NULL,
      PRIMARY KEY (`siteId`,`key`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
}
