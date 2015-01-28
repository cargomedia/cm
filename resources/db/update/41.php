<?php

if (!CM_Db_Db::existsColumn('cm_splittestVariation_fixture', 'id')) {
    CM_Db_Db::exec("
      ALTER TABLE `cm_splittestVariation_fixture`
      ADD `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
      ADD PRIMARY KEY (`id`)
    ");
}
