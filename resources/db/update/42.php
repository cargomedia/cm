<?php

// To be run in prod beforehand:
// pt-online-schema-change --execute --charset=utf8 --user=root --ask-pass --database=skadate t=cm_user --alter='CHANGE `site` `site` int(10) unsigned DEFAULT NULL'

if ('INT' !== CM_Db_Db::describeColumn('cm_user', 'site')->getType()) {
    CM_Db_Db::exec("ALTER TABLE `cm_user` CHANGE `site` `site` int(10) unsigned DEFAULT NULL;");
}
