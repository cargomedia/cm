<?php

if ('INT' !== CM_Db_Db::describeColumn('cm_user', 'site')->getType()) {
    CM_Db_Db::exec("ALTER TABLE `cm_user` CHANGE `site` `site` int(10) unsigned DEFAULT NULL;");
}
