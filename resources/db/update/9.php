<?php

if (!CM_Db_Db::existsTable('`cm_tmp_location_coordinates`')) {
	CM_Db_Db::exec('CREATE TABLE IF NOT EXISTS `cm_tmp_location_coordinates` (
			`level` tinyint(4) NOT NULL,
			`id` int(10) unsigned NOT NULL,
			`coordinates` point NOT NULL,
			PRIMARY KEY (`level`,`id`),
			SPATIAL KEY `coordinates_spatial` (`coordinates`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;');

	CM_Db_Db::exec('INSERT INTO `cm_tmp_location_coordinates` (`level`,`id`,`coordinates`)
		SELECT `level`, `id`, POINT(lat, lon) FROM `cm_tmp_location` WHERE `lat` IS NOT NULL AND `lon` IS NOT NULL');
}
