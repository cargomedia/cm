<?php

if (!CM_Db_Db::existsColumn('cm_ipBlocked', 'expirationStamp')) {
    CM_Db_Db::exec("ALTER TABLE `cm_ipBlocked`
                    ADD `expirationStamp` int(10) unsigned NOT NULL,
                    ADD KEY `expirationStamp` (`expirationStamp`);");
}

$config = CM_Config::get();
CM_Db_Db::exec("UPDATE `cm_ipBlocked` SET `expirationStamp` = `createStamp` + " . $config->CM_Paging_Ip_Blocked->maxAge .
    " WHERE `expirationStamp` = 0");
