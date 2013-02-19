<?php

if (!CM_Mysql::exists('cm_stream_publish', null, 'channelId-key') {
	CM_Mysql::exec('ALTER TABLE  `cm_stream_publish` DROP INDEX `key`');
	CM_Mysql::exec('ALTER TABLE  `cm_stream_publish` DROP INDEX `channelId`');
	CM_Mysql::exec('ALTER TABLE  `cm_stream_publish` ADD UNIQUE `channelId-key` (`channelId`, `key`)');
}
