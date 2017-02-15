<?php

if (!CM_Db_Db::existsTable('cm_migration')) {
    CM_Db_Db::exec("CREATE TABLE `cm_migration` (
                      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                      `name` varchar(255) NOT NULL,
                      `executedAt` int(10) unsigned DEFAULT NULL,
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `name` (`name`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
}
