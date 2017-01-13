<?php

if (!CM_Db_Db::describeColumn('cm_user', 'activityStamp')->getAllowNull()) {
    CM_Db_Db::exec("ALTER TABLE cm_user CHANGE activityStamp activityStamp int(10) unsigned DEFAULT NULL");
}
