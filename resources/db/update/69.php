<?php

if (!CM_Db_Db::existsColumn('cm_user', 'lastSessionSite')) {
    $res = CM_Db_Db::exec('ALTER TABLE `cm_user` ADD COLUMN `lastSessionSite` INT(10) UNSIGNED DEFAULT NULL');
}
