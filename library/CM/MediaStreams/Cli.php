<?php

class CM_MediaStreams_Cli extends CM_Cli_Runnable_Abstract {

    /**
     * @param string  $streamChannelMediaId
     * @param CM_File $thumbnailSource
     * @param int     $createStamp
     * @throws CM_Exception_Invalid
     */
    public function importVideoThumbnail($streamChannelMediaId, CM_File $thumbnailSource, $createStamp) {
        $streamChannelMediaId = (string) $streamChannelMediaId;
        if ($streamChannel = CM_Model_StreamChannel_Media::findByMediaId($streamChannelMediaId)) {
            $thumbnailCount = $streamChannel->getThumbnailCount();
            $thumbnailDestination = $streamChannel->getThumbnail($thumbnailCount + 1);
        } elseif ($streamChannelArchive = CM_Model_StreamChannelArchive_Media::findByMediaId($streamChannelMediaId)) {
            $thumbnailCount = $streamChannelArchive->getThumbnailCount();
            $thumbnailDestination = $streamChannelArchive->getThumbnail($thumbnailCount + 1);
        } else {
            throw new CM_Exception_Invalid('No streamchannel or archive found', null, ['streamChannelMediaId' => $streamChannelMediaId]);
        }
        if (0 == $thumbnailCount) {
            $thumbnailDestination->ensureParentDirectory();
        }
        $thumbnailSource->copyToFile($thumbnailDestination);
        if ($streamChannel) {
            $streamChannel->setThumbnailCount($thumbnailCount + 1);
        } elseif (!empty($streamChannelArchive)) {
            $streamChannelArchive->setThumbnailCount($thumbnailCount + 1);
        }
    }

    /**
     * @param string  $streamChannelMediaId
     * @param CM_File $archiveSource
     * @throws CM_Exception_Invalid
     */
    public function importArchive($streamChannelMediaId, CM_File $archiveSource) {
        $streamChannelMediaId = (string) $streamChannelMediaId;
        $streamChannelArchive = CM_Model_StreamChannelArchive_Media::findByMediaId($streamChannelMediaId);
        if (!$streamChannelArchive) {
            throw new CM_Exception_Invalid('Archive not found', null, ['streamChannelMediaId' => $streamChannelMediaId]);
        }
        $filename = $streamChannelArchive->getId() . '-' . $streamChannelArchive->getHash() . '-original.' . $archiveSource->getExtension();
        $archiveDestination = new CM_File_UserContent('streamChannels', $filename, $streamChannelArchive->getId());
        $archiveDestination->ensureParentDirectory();
        $archiveSource->copyToFile($archiveDestination);
        $streamChannelArchive->setFile($archiveDestination);
    }

    public static function getPackageName() {
        return 'media-streams';
    }
}
