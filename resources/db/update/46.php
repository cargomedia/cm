<?php

if (!CM_Db_Db::existsColumn('cm_splittest', 'optimized')) {
    CM_Db_Db::exec('
        ALTER TABLE `cm_splittest` ADD COLUMN `optimized` int(1) unsigned NOT NULL AFTER `name`');
}
if (!CM_Db_Db::existsIndex('cm_splittestVariation_fixture', 'splittestVariationCreateStamp')) {
    CM_Db_Db::exec('
        ALTER TABLE `cm_splittestVariation_fixture`
          ADD INDEX `splittestVariationCreateStamp` (`splittestId`,`variationId`,`createStamp`),
          ADD INDEX `splittestVariationConversionStamp` (`splittestId`,`variationId`,`conversionStamp`),
          DROP INDEX `splittestId`,
          DROP INDEX `createStamp`,
          DROP INDEX `conversionStamp`');
}
