<?php

if (CM_Db_Db::existsTable('cm_model_location_city_ip') && !CM_Db_Db::existsTable('cm_model_location_ip')) {
    CM_Db_Db::exec('RENAME TABLE `cm_model_location_city_ip` TO `cm_model_location_ip`');
}

if (CM_Db_Db::existsTable('cm_model_location_ip')) {
    if (CM_Db_Db::existsIndex('cm_model_location_ip', 'cityId')) {
        CM_Db_Db::exec('DROP INDEX `cityId` ON `cm_model_location_ip`');
    }
    if (CM_Db_Db::existsColumn('cm_model_location_ip', 'cityId') && !CM_Db_Db::existsColumn('cm_model_location_ip', 'id')) {
        CM_Db_Db::exec('ALTER TABLE `cm_model_location_ip` CHANGE COLUMN `cityId` `id` int(10) unsigned NOT NULL ');
    }
    if (!CM_Db_Db::existsColumn('cm_model_location_ip', 'level')) {
        CM_Db_Db::exec('ALTER TABLE `cm_model_location_ip` ADD COLUMN `level` int(10) unsigned NOT NULL AFTER `id`');
    }
    if (CM_Db_Db::existsColumn('cm_model_location_ip', 'level')) {
        CM_Db_Db::update('cm_model_location_ip', array('level' => CM_Model_Location::LEVEL_CITY), array('level' => 0));
    }
    if (CM_Db_Db::existsTable('cm_model_location_country_ip')) {
        $result = CM_Db_Db::select('cm_model_location_country_ip', array('countryId', 'ipStart', 'ipEnd'));
        foreach ($result->fetchAll() as $row) {
            CM_Db_Db::insert('cm_model_location_ip', array(
                'id'      => $row['countryId'],
                'level'   => CM_Model_Location::LEVEL_COUNTRY,
                'ipStart' => $row['ipStart'],
                'ipEnd'   => $row['ipEnd'],
            ));
        }
        CM_Db_Db::exec('DROP TABLE `cm_model_location_country_ip`');
    }
}
