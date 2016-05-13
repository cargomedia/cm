<?php

if (!CM_Db_Db::existsColumn('cm_streamChannelArchive_media', 'key')) {
    CM_Db_Db::exec("ALTER TABLE cm_streamChannelArchive_media ADD `key` VARCHAR(64) DEFAULT NULL, ADD INDEX (`key`)");
}
