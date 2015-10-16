<?php

abstract class CM_MediaStream_Adapter_Abstract extends CM_Class_Abstract implements CM_Typed {

    /** @var array */
    protected $_servers;

    abstract public function synchronize();

    /**
     * @param CM_Http_Request_Abstract $request
     * @throws CM_Exception_Invalid
     * return int
     */
    abstract public function getServerId(CM_Http_Request_Abstract $request);

    /**
     * @param CM_Model_Stream_Abstract $stream
     */
    abstract protected function _stopStream(CM_Model_Stream_Abstract $stream);

    /**
     * @param array|null $servers
     */
    public function __construct(array $servers = null) {
        $this->_servers = (array) $servers;
    }

    public function checkStreams() {
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        foreach ($this->_getStreamChannels() as $streamChannel) {
            $streamChannelIsValid = $streamChannel->isValid();
            if ($streamChannel->hasStreamPublish()) {
                /** @var CM_Model_Stream_Publish $streamPublish */
                $streamPublish = $streamChannel->getStreamPublish();
                if (!$streamChannelIsValid) {
                    $this->stopStream($streamPublish);
                } else {
                    if ($streamPublish->getAllowedUntil() < time()) {
                        $streamPublish->setAllowedUntil($streamChannel->canPublish($streamPublish->getUser(), $streamPublish->getAllowedUntil()));
                        if ($streamPublish->getAllowedUntil() < time()) {
                            $this->stopStream($streamPublish);
                        }
                    }
                }
            }
            /** @var CM_Model_Stream_Subscribe $streamSubscribe */
            foreach ($streamChannel->getStreamSubscribes() as $streamSubscribe) {
                if (!$streamChannelIsValid) {
                    $this->stopStream($streamSubscribe);
                } else {
                    if ($streamSubscribe->getAllowedUntil() < time()) {
                        $streamSubscribe->setAllowedUntil($streamChannel->canSubscribe($streamSubscribe->getUser(), $streamSubscribe->getAllowedUntil()));
                        if ($streamSubscribe->getAllowedUntil() < time()) {
                            $this->stopStream($streamSubscribe);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param CM_Model_Stream_Abstract $stream
     * @throws CM_Exception_Invalid
     */
    public function stopStream(CM_Model_Stream_Abstract $stream) {
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = $stream->getStreamChannel();
        if (!$streamChannel instanceof CM_Model_StreamChannel_Media) {
            throw new CM_Exception_Invalid('Cannot stop stream of non-video channel');
        }
        $this->_stopStream($stream);
    }

    /**
     * @param string $streamName
     * @param string $clientKey
     * @param int $start
     * @param int $serverId
     * @param string $data
     * @return int
     * @throws CM_Exception_AuthRequired
     * @throws CM_Exception_Invalid
     * @throws CM_Exception_NotAllowed
     */
    public function publish($streamName, $clientKey, $start, $serverId, $data) {
        $params = CM_Params::factory(CM_Params::jsonDecode($data), true);
        $session = new CM_Session($params->getString('sessionId'));
        $user = $session->getUser(true);
        $streamChannelType = $params->getInt('streamChannelType');
        $streamRepository = $this->_getStreamRepository();

        $streamChannel = $streamRepository->createStreamChannel($streamName, $streamChannelType, $serverId, 0);
        try {
            $streamRepository->createStreamPublish($streamChannel, $user, $clientKey, $start);
        } catch (CM_Exception $ex) {
            $streamChannel->delete();
            throw new CM_Exception_NotAllowed('Cannot publish: ' . $ex->getMessage());
        }
        return $streamChannel->getId();
    }

    /**
     * @param string $streamName
     * @return null
     */
    public function unpublish($streamName) {
        $streamRepository = $this->_getStreamRepository();
        $streamChannel = $streamRepository->findStreamChannelByKey($streamName);

        if (!$streamChannel) {
            return;
        }
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamRepository->removeStream($streamChannel->getStreamPublish());
    }

    /**
     * @param string $streamName
     * @param string $clientKey
     * @param int $start
     * @param string $data
     * @throws CM_Exception_NotAllowed
     */
    public function subscribe($streamName, $clientKey, $start, $data) {
        $params = CM_Params::factory(CM_Params::jsonDecode($data), true);
        $user = null;
        if ($params->has('sessionId')) {
            if ($session = CM_Session::findById($params->getString('sessionId'))) {
                $user = $session->getUser(false);
            }
        }

        $streamRepository = $this->_getStreamRepository();
        $streamChannel = $streamRepository->findStreamChannelByKey($streamName);
        if (!$streamChannel) {
            throw new CM_Exception_NotAllowed();
        }

        try {
            $streamRepository->createStreamSubscribe($streamChannel, $user, $clientKey, $start);
        } catch (CM_Exception $ex) {
            throw new CM_Exception_NotAllowed('Cannot subscribe: ' . $ex->getMessage());
        }
    }

    /**
     * @param string $streamName
     * @param string $clientKey
     */
    public function unsubscribe($streamName, $clientKey) {
        $streamRepository = $this->_getStreamRepository();

        $streamChannel = $streamRepository->findStreamChannelByKey($streamName);
        if (!$streamChannel) {
            return;
        }

        $streamSubscribe = $streamChannel->getStreamSubscribes()->findKey($clientKey);
        if ($streamSubscribe) {
            $streamRepository->removeStream($streamSubscribe);
        }
    }

    /**
     * @param int|null $serverId
     * @throws CM_Exception_Invalid
     * @return array
     */
    public function getServer($serverId = null) {
        $servers = $this->_servers;
        if (null === $serverId) {
            $serverId = array_rand($servers);
        }

        $serverId = (int) $serverId;
        if (!array_key_exists($serverId, $servers)) {
            throw new CM_Exception_Invalid("No video server with id `$serverId` found");
        }
        return $servers[$serverId];
    }

    /**
     * @param int $serverId
     * @return string
     * @throws CM_Exception_Invalid
     */
    public function getPublicHost($serverId) {
        $serverId = (int) $serverId;
        $server = $this->getServer($serverId);
        return $server['publicHost'];
    }

    /**
     * @param int $serverId
     * @return string
     * @throws CM_Exception_Invalid
     */
    public function getPrivateHost($serverId) {
        $serverId = (int) $serverId;
        $server = $this->getServer($serverId);
        return $server['privateIp'];
    }

    /**
     * @return CM_Paging_StreamChannel_AdapterType
     */
    protected function _getStreamChannels() {
        return new CM_Paging_StreamChannel_AdapterType($this->getType());
    }

    /**
     * @return CM_Streaming_MediaStreamRepository
     */
    protected function _getStreamRepository() {
        return new CM_Streaming_MediaStreamRepository($this->getType());
    }
}
