<?php

if (!CM_Db_Db::existsTable('cm_process')) {
    CM_Db_Db::exec('
        CREATE TABLE `cm_process` (
            `name` varchar(100) NOT NULL,
            `hostId` int(10) unsigned NOT NULL,
            `processId` int(10) unsigned DEFAULT NULL,
            `timeoutStamp` int(10) unsigned NOT NULL,
            PRIMARY KEY (`name`),
            KEY `hostId` (`hostId`),
            KEY `timeoutStamp` (`timeoutStamp`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8');
}
