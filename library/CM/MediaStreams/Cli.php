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
     * @param string  $mediaId
     * @param CM_File $archiveSource
     * @throws CM_Exception_Invalid
     */
    public function importArchive($mediaId, CM_File $archiveSource) {
        $mediaId = (string) $mediaId;
        $streamChannelArchive = CM_Model_StreamChannelArchive_Media::findByMediaId($mediaId);
        if (!$streamChannelArchive) {
            throw new CM_Exception_Invalid('Archive not found', null, ['mediaId' => $mediaId]);
        }
        $filename = $streamChannelArchive->getId() . '-' . $streamChannelArchive->getHash() . '-original' . $archiveSource->getExtension();
        $archiveDestination = new CM_File_UserContent('streamChannels', $filename, $streamChannelArchive->getId());
        $archiveDestination->ensureParentDirectory();
        $archiveSource->copyToFile($archiveDestination);
        $streamChannelArchive->setFile($archiveDestination);
    }

    public static function getPackageName() {
        return 'media-streams';
    }
}
