<?php

class CM_MediaStreams_Cli extends CM_Cli_Runnable_Abstract {

    /**
     * @param string  $streamChannelMediaId
     * @param CM_File $thumbnailSource
     * @param int     $createStamp
     * @throws CM_Exception
     * @throws CM_Exception_Invalid
     */
    public function importVideoThumbnail($streamChannelMediaId, CM_File $thumbnailSource, $createStamp) {
        $streamChannelMediaId = (string) $streamChannelMediaId;
        $createStamp = (int) $createStamp;
        if ($streamChannel = CM_Model_StreamChannel_Media::findByMediaId($streamChannelMediaId)) {
            $channelId = $streamChannel->getId();
        } elseif ($streamChannelArchive = CM_Model_StreamChannelArchive_Media::findByMediaId($streamChannelMediaId)) {
            $channelId = $streamChannelArchive->getId();
        } else {
            throw new CM_Exception_Invalid('No streamchannel or archive found', null, ['streamChannelMediaId' => $streamChannelMediaId]);
        }
        $thumbnail = CM_StreamChannel_Thumbnail::create($channelId, $createStamp);
        $thumbnailDestination = $thumbnail->getFile();
        try {
            $thumbnailDestination->ensureParentDirectory();
            $thumbnailSource->copyToFile($thumbnailDestination);
        } catch (CM_Exception $ex) {
            $thumbnail->delete();
            throw $ex;
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
            $streamChannel = CM_Model_StreamChannel_Media::findByMediaId($streamChannelMediaId);
            if ($streamChannel) {
                throw new CM_Exception_Invalid('Archive not created, please try again later', null, ['streamChannelMediaId' => $streamChannelMediaId]);
            }
            $exception = new CM_Exception_Invalid('Archive not found, stream channel not found, skipping', CM_Exception::WARN, ['streamChannelMediaId' => $streamChannelMediaId]);
            $context = new CM_Log_Context();
            $context->setException($exception);
            $this->getServiceManager()->getLogger()->warning('Archive creating error', $context);
            return;
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
