<?php

class CM_Stream_Cli extends CM_Cli_Runnable_Abstract {

    /**
     * @synchronized
     */
    public function startMessageSynchronization() {
        CM_Stream_Message::getInstance()->startSynchronization();
    }

    /**
     * @param int     $streamChannelId
     * @param CM_File $thumbnailSource
     * @throws CM_Exception_Invalid
     */
    public function importVideoThumbnail($streamChannelId, CM_File $thumbnailSource) {
        $streamChannel = CM_Model_StreamChannel_Video::factory($streamChannelId);
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
     * @throws CM_Exception_Invalid
     */
    public function importVideoArchive($streamChannelId, CM_File $archiveSource) {
        $streamChannelArchive = new CM_Model_StreamChannelArchive_Video($streamChannelId);
        $archiveDestination = $streamChannelArchive->getVideo();
        $archiveDestination->ensureParentDirectory();
        $archiveSource->copyToFile($archiveDestination);
    }

    public static function getPackageName() {
        return 'stream';
    }
}
