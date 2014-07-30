<?php

if (CM_Db_Db::existsTable('cm_language')) {
    CM_Db_Db::exec('RENAME TABLE cm_language TO cm_model_language');
}

if (CM_Db_Db::existsTable('cm_languageKey')) {
    CM_Db_Db::exec('RENAME TABLE cm_languageKey TO cm_model_languagekey');
