<?php

class CM_Stream_Cli extends CM_Cli_Runnable_Abstract {

    /**
     * @synchronized
     */
    public function startMessageSynchronization() {
        CM_Stream_Message::getInstance()->startSynchronization();
    }

    /**
     * @param string  $streamName
     * @param CM_File $thumbnailSource
     * @throws CM_Exception_Invalid
     */
    public function wowzaImportThumbnail($streamName, CM_File $thumbnailSource) {
        $streamChannel = CM_Model_StreamChannel_Video::findByKey($streamName);
        if (!$streamChannel) {
            throw new CM_Exception_Invalid('Cannot find streamChannel with key `' . $streamName . '`.');
        }
        $thumbnailCount = $streamChannel->getThumbnailCount();
        $thumbnailDest = $streamChannel->getThumbnail($thumbnailCount + 1);
        if (0 == $thumbnailCount) {
            $thumbnailDest->ensureParentDirectory();
        }
        $thumbnailSource->copyToFile($thumbnailDest);
        $streamChannel->setThumbnailCount($thumbnailCount + 1);
    }

    public static function getPackageName() {
        return 'stream';
    }
}
