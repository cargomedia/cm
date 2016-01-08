<?php

if (!CM_Db_Db::existsTable('cm_jobdistribution_delayedqueue')) {
    CM_Db_Db::exec('
        CREATE TABLE `cm_jobdistribution_delayedqueue` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `className` varchar(255) NOT NULL,
          `params` text NOT NULL,
          `executeAt` int(10) unsigned NOT NULL,
          PRIMARY KEY (`id`),
          KEY `executeAt` (`executeAt`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    ');
}
