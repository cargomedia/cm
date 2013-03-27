<?php

if (CM_Db_Db::existsTable('cm_smileySet')) {
	CM_Db_Db::exec('DROP TABLE `cm_smileySet`;');
}

if (!CM_Db_Db::existsIndex('cm_smiley', 'code')) {
	CM_Db_Db::exec('ALTER TABLE `cm_smiley` ADD UNIQUE KEY (`code`);');
}

if (CM_Db_Db::existsColumn('cm_smiley', 'setId')) {
	CM_Db_Db::exec('ALTER TABLE `cm_smiley` DROP `setId`');
}

if (CM_Db_Db::existsTable('cm_smiley')) {
	CM_Db_Db::exec('RENAME TABLE `cm_smiley` TO `cm_emoticon`');
}

if (!CM_Db_Db::existsColumn('cm_emoticon', 'codeAdditional')) {
	CM_Db_Db::exec('ALTER TABLE `cm_emoticon` ADD `codeAdditional` varchar(50) AFTER `code`');
}
