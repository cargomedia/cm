<?php

$filename = md5('app-maintenance-local');
$file = new CM_File("clockwork/{$filename}.json", $this->getServiceManager()->getFilesystems()->getData());
$file->delete();

$filename = md5('search-maintenance');
$file = new CM_File("clockwork/{$filename}.json", $this->getServiceManager()->getFilesystems()->getData());
$file->delete();

if (CM_Db_Db::existsTable('cm_svm')) {
    CM_Db_Db::exec('DROP TABLE cm_svm');
}

if (CM_Db_Db::existsTable('cm_svmtraining')) {
    CM_Db_Db::exec('DROP TABLE cm_svmtraining');
}
