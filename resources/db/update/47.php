<?php

if (!CM_Db_Db::existsColumn('cm_mail', 'customHeaders')) {
    CM_Db_Db::exec('
        ALTER TABLE `cm_mail` ADD COLUMN `customHeaders` text AFTER `bcc`;');
}
