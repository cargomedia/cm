<?php

if (!CM_Db_Db::existsColumn('cm_splittest', 'userIdMin')) {
    CM_Db_Db::exec('ALTER TABLE `cm_splittest` ADD COLUMN `userIdMin` int(10) unsigned NOT NULL AFTER `optimized`');
}

if (!CM_Db_Db::existsColumn('cm_splittest', 'requestClientIdMin')) {
    CM_Db_Db::exec('ALTER TABLE `cm_splittest` ADD COLUMN `requestClientIdMin` int(10) unsigned NOT NULL AFTER `userIdMin`');
}
