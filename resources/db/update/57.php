<?php

if (!CM_Db_Db::existsColumn('cm_streamChannelArchive_media', 'file')) {
    CM_Db_Db::exec('ALTER TABLE `cm_streamChannelArchive_media` ADD COLUMN `file` VARCHAR(255) DEFAULT NULL AFTER `hash`');
}

$archives = new CM_Paging_StreamChannelArchiveMedia_All();
/** @var CM_Model_StreamChannelArchive_Media $streamChannelArchive */
foreach ($archives as $streamChannelArchive) {
    $filename = $streamChannelArchive->getId() . '-' . $streamChannelArchive->getHash() . '-original.mp4';
    $archiveDestination = new CM_File_UserContent('streamChannels', $filename, $streamChannelArchive->getId());
    $streamChannelArchive->setFile($archiveDestination);
}
