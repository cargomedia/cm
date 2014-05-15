<?php

if (!CM_Db_Db::existsTable('cm_cli_command_manager_process')) {
    CM_Db_Db::exec('
        CREATE TABLE `cm_cli_command_manager_process` (
            `commandName` varchar(100) NOT NULL,
            `hostId` int(10) unsigned NOT NULL,
            `processId` int(10) unsigned DEFAULT NULL,
            `timeoutStamp` int(10) unsigned NOT NULL,
            PRIMARY KEY (`commandName`),
            KEY `hostId` (`hostId`),
            KEY `timeoutStamp` (`timeoutStamp`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8');
}
