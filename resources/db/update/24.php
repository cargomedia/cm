<?php

foreach (CM_Db_Db::exec("SHOW TABLES LIKE  'wurfl%'")->fetchAllColumn() as $tableName) {
    CM_Db_Db::exec('DROP TABLE IF EXISTS ' . $tableName);
}
CM_Db_Db::exec('DROP PROCEDURE IF EXISTS `wurfl_FallBackDevices`');
CM_Db_Db::exec('DROP PROCEDURE IF EXISTS `wurfl_RIS`');
