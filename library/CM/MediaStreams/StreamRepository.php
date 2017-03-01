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
     * @param string      $streamName
     * @param int         $streamChannelType
     * @param int         $serverId
     * @param string|null $mediaId
     * @return CM_Model_StreamChannel_Abstract
     * @throws CM_Exception_Invalid
     */
    public function createStreamChannel($streamName, $streamChannelType, $serverId, $mediaId = null) {
        if (null !== $mediaId) {
            $mediaId = (string) $mediaId;
            if (null !== CM_Model_StreamChannelArchive_Media::findByMediaId($mediaId)) {
                throw new CM_Exception_Invalid('Channel archive with this mediaId already exists', null, ['mediaId' => $mediaId]);
            }
        }

        return CM_Model_StreamChannel_Abstract::createType($streamChannelType, [
            'key'         => $streamName,
            'adapterType' => $this->_adapterType,
            'serverId'    => (int) $serverId,
            'mediaId'     => $mediaId,
        ]);
    }

    /**
     * @param CM_Model_StreamChannel_Abstract $streamChannel
     * @param CM_Model_User                   $user
     * @param string                          $clientKey
     * @param int                             $start
     * @return CM_Model_Stream_Publish
     * @throws CM_Exception_NotAllowed
     * @throws CM_Exception_Invalid
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
     * @param CM_Model_User                   $user
     * @param string                          $clientKey
     * @param int                             $start
     * @return CM_Model_Stream_Subscribe
     * @throws CM_Exception_NotAllowed
     * @throws CM_Exception_Invalid
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

    /**
     * @param CM_Model_StreamChannel_Abstract $streamChannel
     */
    public function removeStreamChannel(CM_Model_StreamChannel_Abstract $streamChannel) {
        /** @var CM_Model_Stream_Subscribe $streamSubscribe */
        foreach ($streamChannel->getStreamSubscribes() as $streamSubscribe) {
            $streamSubscribe->delete();
        }
        /** @var CM_Model_Stream_Publish $streamPublish */
        foreach ($streamChannel->getStreamPublishs() as $streamPublish) {
            $streamPublish->delete();
        }
        $streamChannel->delete();
    }
}
