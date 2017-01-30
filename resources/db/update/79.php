<?php

//pt-online-schema-change doesn't work here

if (!CM_Db_Db::existsColumn('cm_action', 'id')) {
    CM_Db_Db::exec("ALTER TABLE `cm_action` ADD COLUMN `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;");
}
