<?php

if (!CM_Db_Db::existsColumn('cm_user_online', 'offlineStamp')) {
    CM_Db_Db::exec("ALTER TABLE `cm_user_online` ADD `offlineStamp` int(10) unsigned DEFAULT NULL, ADD INDEX `offlineStamp` (`offlineStamp`)");
}
