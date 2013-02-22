<?php

if (CM_Mysql::exists('cm_smileySet')) {
	CM_Mysql::exec('DROP TABLE `cm_smileySet`;');
}

if (!CM_Mysql::exists('cm_smiley', null, 'code')) {
	CM_Mysql::exec('ALTER TABLE `cm_smiley` ADD UNIQUE KEY (`code`);');
}

if (CM_Mysql::exists('cm_smiley', 'setId')) {
	CM_Mysql::exec('ALTER TABLE `cm_smiley` DROP `setId`');
}

if (CM_Mysql::exists('cm_smiley')) {
	CM_Mysql::exec('RENAME TABLE `cm_smiley` TO `cm_emoticon`');
}
