<?php

if (!CM_Db_Db::existsTable('cm_site_setting')) {
    CM_Db_Db::exec('CREATE TABLE `cm_site_setting` (
      `siteId` int(10) unsigned NOT NULL,
      `classId` int(10) unsigned NOT NULL,
      `hash` varchar(2000) NOT NULL,
      `name` varchar(32) NOT NULL,
      PRIMARY KEY (`siteId`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
}
