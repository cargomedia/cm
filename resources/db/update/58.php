<?php

if (!CM_Db_Db::existsTable('cm_tmp_classType')) {
    CM_Db_Db::exec("
        CREATE TABLE `cm_tmp_classType` (
          `type` int(10) unsigned NOT NULL,
          `className` varchar(255) NOT NULL,
          PRIMARY KEY (`type`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    ");
}
