<?php

if (!CM_Db_Db::existsColumn('cm_ipBlocked', 'expirationStamp')) {
    CM_Db_Db::exec("ALTER TABLE `cm_ipBlocked`
                    ADD `expirationStamp` int(10) unsigned NOT NULL,
                    ADD KEY `expirationStamp` (`expirationStamp`);");
}

$config = CM_Config::get();
$IpsWithoutExpirationStamp = CM_Db_Db::select('cm_ipBlocked', '*', ['expirationStamp' => 0])->fetchAll();

foreach ($IpsWithoutExpirationStamp as $row) {
    if ($row['expirationStamp'] == 0) {
        $expirationStamp = $row['createStamp'] + $config->CM_Paging_Ip_Blocked->maxAge;
        CM_Db_Db::update('cm_ipBlocked', ['expirationStamp' => $expirationStamp], ['ip' => $row['ip']]);
    }
}
