<?php

if (!CM_Db_Db::existsColumn('cm_streamChannel', 'createStamp')) {
    CM_Db_Db::exec("ALTER TABLE cm_streamChannel ADD createStamp INT UNSIGNED NOT NULL AFTER `key`");
}

if (!CM_Db_Db::existsColumn('cm_streamChannel_media', 'mediaId')) {
    CM_Db_Db::exec("ALTER TABLE cm_streamChannel_media ADD mediaId VARCHAR(255) DEFAULT NULL, ADD UNIQUE KEY (mediaId)");
}

