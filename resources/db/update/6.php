<?php

if (CM_Db_Db::existsIndex('cm_streamChannel', 'key-adapterType')) {
	CM_Db_Db::exec('ALTER TABLE `cm_streamChannel` DROP INDEX `key-adapterType`');
	CM_Db_Db::exec('ALTER TABLE `cm_streamChannel` ADD UNIQUE `adapterType-key` (`adapterType`, `key`)');
}
if (CM_Db_Db::existsIndex('cm_streamChannel', 'key')) {
	CM_Db_Db::exec('ALTER TABLE `cm_streamChannel` DROP INDEX `key`');
}
