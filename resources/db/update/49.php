<?php

if (!CM_Db_Db::existsTable('cm_model_currency')) {
    CM_Db_Db::exec("CREATE TABLE `cm_model_currency` (
                      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                      `code` varchar(3) NOT NULL DEFAULT '',
                      `abbreviation` varchar(3) NOT NULL DEFAULT '',
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `code` (`code`),
                      UNIQUE KEY `string` (`abbreviation`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
}

if (!CM_Db_Db::existsTable('cm_model_currency_country')) {
    CM_Db_Db::exec("CREATE TABLE `cm_model_currency_country` (
                      `currencyId` int(10) NOT NULL,
                      `countryId` int(10) NOT NULL,
                      UNIQUE KEY `countryId` (`countryId`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
}

if (!CM_Db_Db::existsColumn('cm_user', 'currencyId')) {
    CM_Db_Db::exec("ALTER TABLE `cm_user` ADD `currencyId` INT(10) UNSIGNED DEFAULT NULL");
}
