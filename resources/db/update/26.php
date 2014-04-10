<?php

if (!CM_Db_Db::existsColumn('cm_languageKey', 'variables')) {
    CM_Db_Db::exec("ALTER TABLE `cm_languageKey` ADD `variables` text CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL AFTER name");
}
