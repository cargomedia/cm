<?php

class CM_MediaStreams_StreamRepository {

    /** @var int */
    protected $_adapterType;

    /**
     * @param int $adapterType
     */
    public function __construct($adapterType) {
        $this->_adapterType = (int) $adapterType;
    }

    /**
     * @return CM_Paging_StreamChannel_AdapterType
     */
    public function getStreamChannels() {
        return new CM_Paging_StreamChannel_AdapterType($this->_adapterType);
    }

    /**
     * @param string $streamName
     * @return CM_Model_StreamChannel_Abstract|null
     */
    public function findStreamChannelByKey($streamName) {
        return CM_Model_StreamChannel_Abstract::findByKeyAndAdapter($streamName, $this->_adapterType);
    }

    /**
     * @param string $streamName
     * @param int $streamChannelType
     * @param int $serverId
     * @param int|null $thumbnailCount
     * @return CM_Model_StreamChannel_Abstract
     */
    public function createStreamChannel($streamName, $streamChannelType, $serverId, $thumbnailCount = null) {
        if (null !== $thumbnailCount) {
            $thumbnailCount = (int) $thumbnailCount;
        }
        return CM_Model_StreamChannel_Abstract::createType($streamChannelType, [
            'key'            => $streamName,
            'adapterType'    => $this->_adapterType,
            'serverId'       => (int) $serverId,
            'thumbnailCount' => $thumbnailCount,
        ]);
    }

    /**
     * @param CM_Model_StreamChannel_Abstract $streamChannel
     * @param CM_Model_User $user
     * @param string $clientKey
     * @param int $start
     * @return CM_Model_Stream_Publish
     */
    public function createStreamPublish(CM_Model_StreamChannel_Abstract $streamChannel, CM_Model_User $user, $clientKey, $start) {
        return CM_Model_Stream_Publish::createStatic([
            'streamChannel' => $streamChannel,
            'user'          => $user,
            'key'           => (string) $clientKey,
            'start'         => (int) $start,
        ]);
    }

    /**
     * @param CM_Model_StreamChannel_Abstract $streamChannel
     * @param CM_Model_User $user
     * @param string $clientKey
     * @param int $start
     * @return CM_Model_Stream_Subscribe
     */
    public function createStreamSubscribe(CM_Model_StreamChannel_Abstract $streamChannel, CM_Model_User $user, $clientKey, $start) {
        return CM_Model_Stream_Subscribe::createStatic([
            'streamChannel' => $streamChannel,
            'user'          => $user,
            'start'         => (int) $start,
            'key'           => (string) $clientKey,
        ]);
    }

    /**
     * @param CM_Model_Stream_Abstract $stream
     */
    public function removeStream(CM_Model_Stream_Abstract $stream) {
        $streamChannel = $stream->getStreamChannel();
        $stream->delete();
        if (!$streamChannel->hasStreams()) {
            $streamChannel->delete();
        }
    }
}
