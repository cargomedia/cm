<?php

if (CM_Db_Db::existsIndex('cm_svm', 'trainingChanges')) {
    CM_Db_Db::exec('DROP INDEX `trainingChanges` on `cm_svm`');
}
if (CM_Db_Db::existsColumn('cm_svm', 'trainingChanges')) {
    CM_Db_Db::exec('
        ALTER TABLE `cm_svm`
          DROP COLUMN `trainingChanges`,
          ADD COLUMN `updateStamp` int(10) unsigned NOT NULL');
    CM_Db_Db::update('cm_svm', array('updateStamp' => time()));
}
