<?php

abstract class CM_MediaStreams_Service extends CM_Class_Abstract implements CM_Typed {

    /**
     * @param CM_Model_Stream_Abstract $stream
     */
    abstract protected function _stopStream(CM_Model_Stream_Abstract $stream);

    public function checkStreams() {
        $streamRepository = $this->getStreamRepository();

        /** @var CM_Model_StreamChannel_Media $streamChannel */
        foreach ($streamRepository->getStreamChannels() as $streamChannel) {
            $streamChannelIsValid = $streamChannel->isValid();
            if ($streamChannel->hasStreamPublish()) {
                /** @var CM_Model_Stream_Publish $streamPublish */
                $streamPublish = $streamChannel->getStreamPublish();
                if (!$streamChannelIsValid) {
                    $this->_stopStream($streamPublish);
                } else {
                    if ($streamPublish->getAllowedUntil() < time()) {
                        $streamPublish->setAllowedUntil($streamChannel->canPublish($streamPublish->getUser(), $streamPublish->getAllowedUntil()));
                        if ($streamPublish->getAllowedUntil() < time()) {
                            $this->_stopStream($streamPublish);
                        }
                    }
                }
            }
            /** @var CM_Model_Stream_Subscribe $streamSubscribe */
            foreach ($streamChannel->getStreamSubscribes() as $streamSubscribe) {
                if (!$streamChannelIsValid) {
                    $this->_stopStream($streamSubscribe);
                } else {
                    if ($streamSubscribe->getAllowedUntil() < time()) {
                        $streamSubscribe->setAllowedUntil($streamChannel->canSubscribe($streamSubscribe->getUser(), $streamSubscribe->getAllowedUntil()));
                        if ($streamSubscribe->getAllowedUntil() < time()) {
                            $this->_stopStream($streamSubscribe);
                        }
                    }
                }
            }
        }
    }

    /**
     * @return CM_MediaStreams_StreamRepository
     */
    public function getStreamRepository() {
        return new CM_MediaStreams_StreamRepository($this->getType());
    }
}
