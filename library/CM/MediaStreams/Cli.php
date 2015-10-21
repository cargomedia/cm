<?php

class CM_MediaStreams_Cli extends CM_Cli_Runnable_Abstract {

    /**
     * @param int     $streamChannelId
     * @param CM_File $thumbnailSource
     */
    public function importVideoThumbnail($streamChannelId, CM_File $thumbnailSource) {
        $streamChannel = CM_Model_StreamChannel_Media::factory($streamChannelId);
        $thumbnailCount = $streamChannel->getThumbnailCount();
        $thumbnailDestination = $streamChannel->getThumbnail($thumbnailCount + 1);
        if (0 == $thumbnailCount) {
            $thumbnailDestination->ensureParentDirectory();
        }
        $thumbnailSource->copyToFile($thumbnailDestination);
        $streamChannel->setThumbnailCount($thumbnailCount + 1);
    }

    /**
     * @param int     $streamChannelId
     * @param CM_File $archiveSource
     */
    public function importArchive($streamChannelId, CM_File $archiveSource) {
        $streamChannelArchive = new CM_Model_StreamChannelArchive_Media($streamChannelId);
        $archiveDestination = $streamChannelArchive->getFile();
        $archiveDestination->ensureParentDirectory();
        $archiveSource->copyToFile($archiveDestination);
    }

    public static function getPackageName() {
        return 'media-streams';
    }
}
