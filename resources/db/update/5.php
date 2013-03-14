<?php

if (!CM_Db_Db::existsIndex('cm_stream_subscribe', 'channelId-key')) {
	CM_Mysql::exec('ALTER TABLE  `cm_stream_subscribe` DROP INDEX `key`');
	CM_Mysql::exec('ALTER TABLE  `cm_stream_subscribe` DROP INDEX `channelId`');
	CM_Mysql::exec('ALTER TABLE  `cm_stream_subscribe` ADD UNIQUE `channelId-key` (`channelId`, `key`)');
}

if (!CM_Db_Db::existsIndex('cm_stream_publish', 'channelId-key')) {
	CM_Mysql::exec('ALTER TABLE  `cm_stream_publish` DROP INDEX `key`');
	CM_Mysql::exec('ALTER TABLE  `cm_stream_publish` DROP INDEX `channelId`');
	CM_Mysql::exec('ALTER TABLE  `cm_stream_publish` ADD UNIQUE `channelId-key` (`channelId`, `key`)');
}

if (!CM_Mysql::describe('cm_stream_publish', 'allowedUntil')->allowNull()) {
	CM_Mysql::exec('ALTER TABLE  `cm_stream_publish` CHANGE  `allowedUntil`  `allowedUntil` INT( 10 ) UNSIGNED NULL');
}

if (!CM_Mysql::describe('cm_stream_subscribe', 'allowedUntil')->allowNull()) {
	CM_Mysql::exec('ALTER TABLE  `cm_stream_subscribe` CHANGE  `allowedUntil`  `allowedUntil` INT( 10 ) UNSIGNED NULL');
}
