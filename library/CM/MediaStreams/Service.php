<?php

abstract class CM_MediaStreams_Service extends CM_Class_Abstract implements CM_Typed {

    /** @var CM_MediaStreams_StreamRepository|null */
    protected $_streamRepository;

    /**
     * @param CM_Model_Stream_Abstract $stream
     */
    abstract protected function _stopStream(CM_Model_Stream_Abstract $stream);

    /**
     * @param CM_MediaStreams_StreamRepository|null $streamRepository
     */
    public function __construct(CM_MediaStreams_StreamRepository $streamRepository = null) {
        if (null == $streamRepository) {
            $streamRepository = new CM_MediaStreams_StreamRepository($this->getType());
        }
        $this->_streamRepository = $streamRepository;
    }

    public function checkStreams() {
        $streamRepository = $this->getStreamRepository();

        /** @var CM_Model_StreamChannel_Media $streamChannel */
        foreach ($streamRepository->getStreamChannels() as $streamChannel) {
            $streamChannelIsValid = $streamChannel->isValid();
            if ($streamChannel->hasStreamPublish()) {
                /** @var CM_Model_Stream_Publish $streamPublish */
                $streamPublish = $streamChannel->getStreamPublish();
                if (!$streamChannelIsValid || !$this->_isPublishAllowed($streamPublish)) {
                    $this->_stopStream($streamPublish);
                }
            }
            /** @var CM_Model_Stream_Subscribe $streamSubscribe */
            foreach ($streamChannel->getStreamSubscribes() as $streamSubscribe) {
                if (!$streamChannelIsValid || !$this->_isSubscribeAllowed($streamSubscribe)) {
                    $this->_stopStream($streamSubscribe);
                }
            }
        }
    }

    /**
     * @return CM_MediaStreams_StreamRepository
     * @throws CM_Exception_Invalid
     */
    public function getStreamRepository() {
        if (null === $this->_streamRepository) {
            throw new CM_Exception_Invalid('Stream repository not set');
        }
        return $this->_streamRepository;
    }

    /**
     * @param CM_Model_Stream_Publish $streamPublish
     * @return bool
     */
    protected function _isPublishAllowed(CM_Model_Stream_Publish $streamPublish) {
        if ($streamPublish->getAllowedUntil() < time()) {
            $streamChannel = $streamPublish->getStreamChannel();
            $canPublishUntil = $streamChannel->canPublish($streamPublish, $streamPublish->getUser(), $streamPublish->getAllowedUntil());
            $streamPublish->setAllowedUntil($canPublishUntil);
            if ($streamPublish->getAllowedUntil() < time()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param CM_Model_Stream_Subscribe $streamSubscribe
     * @return bool
     */
    protected function _isSubscribeAllowed(CM_Model_Stream_Subscribe $streamSubscribe) {
        if ($streamSubscribe->getAllowedUntil() < time()) {
            $streamChannel = $streamSubscribe->getStreamChannel();
            $canSubscribeUntil = $streamChannel->canSubscribe($streamSubscribe->getUser(), $streamSubscribe->getAllowedUntil());
            $streamSubscribe->setAllowedUntil($canSubscribeUntil);
            if ($streamSubscribe->getAllowedUntil() < time()) {
                return false;
            }
        }
        return true;
    }
}
