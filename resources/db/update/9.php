<?php

if(!CM_Db_Db::existsColumn('cm_tmp_location', 'coordinates')) {
	CM_Db_Db::exec('ALTER TABLE `cm_tmp_location` ADD COLUMN `coordinates` point NOT NULL');
	CM_Db_Db::exec('UPDATE `cm_tmp_location` SET `coordinates` = POINT( lat, lon )');
	CM_Db_Db::exec("UPDATE `cm_tmp_location` SET `coordinates` = POINT( 0, 0) WHERE `coordinates` = ''");
	CM_Db_Db::exec('CREATE SPATIAL INDEX `coordinates_spatial` ON cm_tmp_location(`coordinates`)');

	CM_Db_Db::exec('ALTER TABLE `cm_tmp_location` DROP COLUMN `lat`');
	CM_Db_Db::exec('ALTER TABLE `cm_tmp_location` DROP COLUMN `lon`');
}
