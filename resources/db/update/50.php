<?php

if (!CM_Db_Db::existsColumn('cm_user', 'currencyId')) {
    CM_Db_Db::exec("ALTER TABLE `cm_user` ADD `currencyId` INT(10) UNSIGNED DEFAULT NULL");
}
