<?php

if (CM_Mysql::exists('cm_stream_subscribe')) {
	CM_Mysql::exec('ALTER TABLE  `cm_stream_subscribe` DROP INDEX `key`');
	CM_Mysql::exec('ALTER TABLE  `cm_stream_subscribe` DROP INDEX `channelId`');
	CM_Mysql::exec('ALTER TABLE  `cm_stream_subscribe` ADD UNIQUE `channelId-key` (`channelId`, `key`)');
}
