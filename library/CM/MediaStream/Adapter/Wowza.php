<?php

class CM_MediaStream_Adapter_Wowza extends CM_Class_Abstract implements CM_Typed {

    /** @var array */
    protected $_servers;

    /** @var array */
    protected $_config;

    /**
     * @param array|null $servers
     * @param array|null $config
     */
    public function __construct(array $servers = null, array $config = null) {
        $this->_config = (array) $config;
    }

    public function checkStreams() {
        /** @var CM_Model_StreamChannel_Video $streamChannel */
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

    public function synchronize() {
        $startStampLimit = time() - 3;
        $status = array();
        foreach ($this->_servers as $serverId => $wowzaServer) {
            $singleStatus = CM_Params::decode($this->_fetchStatus($wowzaServer['privateIp']), true);
            foreach ($singleStatus as $streamName => $publish) {
                $publish['serverId'] = $serverId;
                $publish['serverHost'] = $wowzaServer['privateIp'];
                $status[$streamName] = $publish;
            }
        }

        $streamChannels = $this->_getStreamChannels();
        foreach ($status as $streamName => $publish) {
            /** @var CM_Model_StreamChannel_Abstract $streamChannel */
            $streamChannel = CM_Model_StreamChannel_Abstract::findByKeyAndAdapter($streamName, $this->getType());
            if (!$streamChannel || !$streamChannel->getStreamPublishs()->findKey($publish['clientId'])) {
                $this->_stopClient($publish['clientId'], $publish['serverHost']);
            }

            foreach ($publish['subscribers'] as $clientId => $subscribe) {
                if (!$streamChannel || !$streamChannel->getStreamSubscribes()->findKey($clientId)) {
                    $this->_stopClient($clientId, $publish['serverHost']);
                }
            }
        }

        /** @var CM_Model_StreamChannel_Abstract $streamChannel */
        foreach ($streamChannels as $streamChannel) {
            if (!$streamChannel->hasStreams()) {
                $streamChannel->delete();
                continue;
            }

            /** @var CM_Model_Stream_Publish $streamPublish */
            $streamPublish = $streamChannel->getStreamPublishs()->getItem(0);
            if ($streamPublish) {
                if ($streamPublish->getStart() > $startStampLimit) {
                    continue;
                }
                if (!isset($status[$streamChannel->getKey()])) {
                    $this->unpublish($streamChannel->getKey());
                }
            }
            /** @var CM_Model_Stream_Subscribe $streamSubscribe */
            foreach ($streamChannel->getStreamSubscribes() as $streamSubscribe) {
                if ($streamSubscribe->getStart() > $startStampLimit) {
                    continue;
                }
                if (!isset($status[$streamChannel->getKey()]['subscribers'][$streamSubscribe->getKey()])) {
                    $this->unsubscribe($streamChannel->getKey(), $streamSubscribe->getKey());
                }
            }
        }
    }

    public function getServerId(CM_Http_Request_Abstract $request) {
        $ipAddress = long2ip($request->getIp());

        $servers = $this->_servers;
        foreach ($servers as $serverId => $server) {
            if ($server['publicIp'] == $ipAddress || $server['privateIp'] == $ipAddress) {
                return (int) $serverId;
            }
        }
        throw new CM_Exception_Invalid('No video server with ipAddress `' . $ipAddress . '` found');
    }

    /**
     * @param CM_Model_Stream_Abstract $stream
     * @throws CM_Exception_Invalid
     */
    public function stopStream(CM_Model_Stream_Abstract $stream) {
        /** @var CM_Model_StreamChannel_Video $streamChannel */
        $streamChannel = $stream->getStreamChannel();
        if (!$streamChannel instanceof CM_Model_StreamChannel_Video) {
            throw new CM_Exception_Invalid('Cannot stop stream of non-video channel');
        }
        $this->_stopStream($stream);
    }

    /**
     * @param string $streamName
     * @param string $clientKey
     * @param int    $start
     * @param int    $width
     * @param int    $height
     * @param int    $serverId
     * @param string $data
     * @throws CM_Exception
     * @throws CM_Exception_NotAllowed
     * @return int
     */
    public function publish($streamName, $clientKey, $start, $width, $height, $serverId, $data) {
        $streamName = (string) $streamName;
        $clientKey = (string) $clientKey;
        $start = (int) $start;
        $width = (int) $width;
        $height = (int) $height;
        $serverId = (int) $serverId;
        $data = (string) $data;
        $params = CM_Params::factory(CM_Params::jsonDecode($data), true);
        $streamChannelType = $params->getInt('streamChannelType');
        $session = new CM_Session($params->getString('sessionId'));
        $user = $session->getUser(true);
        /** @var CM_Model_StreamChannel_Abstract $streamChannel */
        $streamChannel = CM_Model_StreamChannel_Abstract::createType($streamChannelType, array(
            'key'            => $streamName,
            'adapterType'    => $this->getType(),
            'params'         => $params,
            'width'          => $width,
            'height'         => $height,
            'serverId'       => $serverId,
            'thumbnailCount' => 0,
        ));
        try {
            CM_Model_Stream_Publish::createStatic(array(
                'streamChannel' => $streamChannel,
                'user'          => $user,
                'start'         => $start,
                'key'           => $clientKey,
            ));
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
        $streamName = (string) $streamName;
        /** @var CM_Model_StreamChannel_Abstract $streamChannel */
        $streamChannel = CM_Model_StreamChannel_Abstract::findByKeyAndAdapter($streamName, $this->getType());
        if (!$streamChannel) {
            return;
        }

        $streamChannel->getStreamPublish()->delete();
        if (!$streamChannel->hasStreams()) {
            $streamChannel->delete();
        }
    }

    /**
     * @param string $streamName
     * @param string $clientKey
     * @param int    $start
     * @param string $data
     * @throws CM_Exception_NotAllowed
     */
    public function subscribe($streamName, $clientKey, $start, $data) {
        $streamName = (string) $streamName;
        $clientKey = (string) $clientKey;
        $start = (int) $start;
        $data = (string) $data;
        $user = null;
        $params = CM_Params::factory(CM_Params::jsonDecode($data), true);
        if ($params->has('sessionId')) {
            if ($session = CM_Session::findById($params->getString('sessionId'))) {
                $user = $session->getUser(false);
            }
        }
        /** @var CM_Model_StreamChannel_Abstract $streamChannel */
        $streamChannel = CM_Model_StreamChannel_Abstract::findByKeyAndAdapter($streamName, $this->getType());
        if (!$streamChannel) {
            throw new CM_Exception_NotAllowed();
        }

        try {
            CM_Model_Stream_Subscribe::createStatic(array(
                'streamChannel' => $streamChannel,
                'user'          => $user,
                'start'         => $start,
                'key'           => $clientKey,
            ));
        } catch (CM_Exception $ex) {
            throw new CM_Exception_NotAllowed('Cannot subscribe: ' . $ex->getMessage());
        }
    }

    /**
     * @param string $streamName
     * @param string $clientKey
     */
    public function unsubscribe($streamName, $clientKey) {
        $streamName = (string) $streamName;
        $clientKey = (string) $clientKey;
        /** @var CM_Model_StreamChannel_Abstract $streamChannel */
        $streamChannel = CM_Model_StreamChannel_Abstract::findByKeyAndAdapter($streamName, $this->getType());
        if (!$streamChannel) {
            return;
        }
        $streamSubscribe = $streamChannel->getStreamSubscribes()->findKey($clientKey);
        if ($streamSubscribe) {
            $streamSubscribe->delete();
        }
        if (!$streamChannel->hasStreams()) {
            $streamChannel->delete();
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
     * @param string $wowzaHost
     * @return string
     */
    protected function _fetchStatus($wowzaHost) {
        return CM_Util::getContents('http://' . $wowzaHost . ':' . $this->_config['httpPort'] . '/status');
    }

    /**
     * @param CM_Model_Stream_Abstract $stream
     */
    protected function _stopStream(CM_Model_Stream_Abstract $stream) {
        /** @var $streamChannel CM_Model_StreamChannel_Video */
        $streamChannel = $stream->getStreamChannel();
        $privateHost = $this->getPrivateHost($streamChannel->getServerId());
        $this->_stopClient($stream->getKey(), $privateHost);
    }

    protected function _stopClient($clientId, $serverHost) {
        CM_Util::getContents('http://' . $serverHost . ':' . $this->_config['httpPort'] . '/stop', array('clientId' => (string) $clientId), true);
    }
}
