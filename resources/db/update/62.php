<?php

if (!CM_Db_Db::describeColumn('cm_stream_subscribe', 'allowedUntil')->getAllowNull()) {
    CM_Db_Db::exec("ALTER TABLE cm_stream_subscribe CHANGE allowedUntil allowedUntil INT(10) UNSIGNED DEFAULT NULL");
}

if (!CM_Db_Db::describeColumn('cm_stream_publish', 'allowedUntil')->getAllowNull()) {
    CM_Db_Db::exec("ALTER TABLE cm_stream_publish CHANGE allowedUntil allowedUntil INT(10) UNSIGNED DEFAULT NULL");
}
