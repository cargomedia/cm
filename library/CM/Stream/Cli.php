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
        $thumbnailDest = $streamChannel->getThumbnail($thumbnailCount + 1);
        if (0 == $thumbnailCount) {
            $thumbnailDest->ensureParentDirectory();
        }
        $thumbnailSource->copyToFile($thumbnailDest);
        $streamChannel->setThumbnailCount($thumbnailCount + 1);
    }

    /**
     * @param int     $streamChannelId
     * @param CM_File $archiveSource
     * @throws CM_Exception_Invalid
     */
    public function importVideoArchive($streamChannelId, CM_File $archiveSource) {
        $streamChannelArchive = new CM_Model_StreamChannelArchive_Video($streamChannelId);
        $archiveDest = $streamChannelArchive->getVideo();
        $archiveDest->ensureParentDirectory();
        $archiveSource->copyToFile($archiveDest);
    }

    public static function getPackageName() {
        return 'stream';
    }
}
