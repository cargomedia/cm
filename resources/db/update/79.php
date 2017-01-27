<?php

if (CM_Db_Db::existsTable('cm_svm')) {
    CM_Db_Db::exec('DROP TABLE cm_svm');
}

if (CM_Db_Db::existsTable('cm_svmtraining')) {
    CM_Db_Db::exec('DROP TABLE cm_svmtraining');
}
