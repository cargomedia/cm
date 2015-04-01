<?php

if (!CM_Db_Db::existsColumn('cm_splittest', 'optimized')) {
    CM_Db_Db::exec('
        ALTER TABLE `cm_splittest` ADD COLUMN `optimized` int(1) unsigned NOT NULL AFTER `name`');
}
