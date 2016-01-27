<?php

if (!CM_Db_Db::existsTable('cm_streamchannel_thumbnail')) {
    CM_Db_Db::exec("CREATE TABLE `cm_streamchannel_thumbnail` (
          `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
          `channelId` INT(10) UNSIGNED NOT NULL,
          `createStamp` INT(10) UNSIGNED NOT NULL,
          PRIMARY KEY (`id`),
          KEY `channelId-createStamp` (`channelId`, `createStamp`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
}

if (CM_Db_Db::existsColumn('cm_streamChannel_media', 'thumbnailCount')) {
    CM_Db_Db::exec("ALTER TABLE cm_streamChannel_media DROP thumbnailCount, DROP `data`");
}

if (CM_Db_Db::existsColumn('cm_streamChannelArchive_media', 'thumbnailCount')) {
    CM_Db_Db::exec("ALTER TABLE cm_streamChannelArchive_media DROP thumbnailCount, DROP `data`");
}


