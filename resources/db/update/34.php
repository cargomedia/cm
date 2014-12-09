<?php

if (count(CM_Db_Db::exec("SHOW COLUMNS FROM `cm_log` WHERE Field = 'metaInfo' AND Type LIKE 'varchar%'")->fetchAll()) === 1) {
    CM_Db_Db::exec("ALTER TABLE `cm_log` CHANGE `metaInfo` `metaInfo` TEXT");
}
