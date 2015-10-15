<?php

if (CM_Db_Db::existsTable('cm_streamChannel_video')) {
    CM_Db_Db::exec('RENAME TABLE cm_streamChannel_video TO cm_streamChannel_media');
}

if (CM_Db_Db::existsColumn('cm_streamChannel_media', 'width') && CM_Db_Db::existsColumn('cm_streamChannel_media', 'height')) {
    CM_Db_Db::exec('ALTER TABLE cm_streamChannel_media DROP COLUMN `width`, DROP COLUMN `height`');
}

if (!CM_Db_Db::existsColumn('cm_streamChannel_media', 'data')) {
    CM_Db_Db::exec('ALTER TABLE cm_streamChannel_media ADD COLUMN `data` VARCHAR(255) NOT NULL DEFAULT \'\' ');
}

if (CM_Db_Db::existsTable('cm_streamChannelArchive_video')) {
    CM_Db_Db::exec('RENAME TABLE cm_streamChannelArchive_video TO cm_streamChannelArchive_media');
}

if (CM_Db_Db::existsColumn('cm_streamChannelArchive_media', 'width') && CM_Db_Db::existsColumn('cm_streamChannelArchive_media', 'height')) {
    CM_Db_Db::exec('ALTER TABLE cm_streamChannelArchive_media DROP COLUMN `width`, DROP COLUMN `height`');
}

if (!CM_Db_Db::existsColumn('cm_streamChannelArchive_media', 'data')) {
    CM_Db_Db::exec('ALTER TABLE cm_streamChannelArchive_media ADD COLUMN `data` VARCHAR(255) NOT NULL DEFAULT \'\' ');
}
