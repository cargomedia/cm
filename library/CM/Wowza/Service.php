<?php

class CM_Wowza_Service extends CM_StreamService {

    /** @var array */
    protected $_servers;

    /** @var array */
    protected $_config;

    /**
     * @param array|null $servers
     * @param array|null $config
     */
    public function __construct(array $servers = null, array $config = null) {
        $this->_servers = (array) $servers;
        $this->_config = (array) $config;
    }

    public function synchronize() {
        $streamRepository = $this->_getStreamRepository();

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

        $streamChannels = $streamRepository->getStreamChannels();
        foreach ($status as $streamName => $publish) {
            /** @var CM_Model_StreamChannel_Abstract $streamChannel */
            $streamChannel = CM_Model_StreamChannel_Abstract::findByKeyAndAdapter($streamName, $this->getType());
            if (!$streamChannel || !$streamChannel->getStreamPublishs()->findKey($publish['clientId'])) {
                $this->_stopClient($publish['serverHost'], $publish['clientId']);
            }

            foreach ($publish['subscribers'] as $clientId => $subscribe) {
                if (!$streamChannel || !$streamChannel->getStreamSubscribes()->findKey($clientId)) {
                    $this->_stopClient($publish['serverHost'], $clientId);
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
                    $streamRepository->removeStream($streamPublish);
                }
            }
            /** @var CM_Model_Stream_Subscribe $streamSubscribe */
            foreach ($streamChannel->getStreamSubscribes() as $streamSubscribe) {
                if ($streamSubscribe->getStart() > $startStampLimit) {
                    continue;
                }
                if (!isset($status[$streamChannel->getKey()]['subscribers'][$streamSubscribe->getKey()])) {
                    $streamRepository->removeStream($streamSubscribe);
                }
            }
        }
    }

    /**
     * @param string $streamName
     * @param string $clientKey
     * @param int $start
     * @param string $data
     * @return int
     * @throws CM_Exception_AuthRequired
     * @throws CM_Exception_Invalid
     * @throws CM_Exception_NotAllowed
     */
    public static function rpc_publish($streamName, $clientKey, $start, $data) {
        $wowza = CM_Service_Manager::getInstance()->getStreamVideo();
        $serverId = $wowza->_extractServerIdFromRequest(CM_Http_Request_Abstract::getInstance());

        $params = CM_Params::factory(CM_Params::jsonDecode($data), true);
        $session = new CM_Session($params->getString('sessionId'));
        $streamChannelType = $params->getInt('streamChannelType');
        $user = $session->getUser(true);

        $streamRepository = $wowza->_getStreamRepository();
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
     * @return bool
     */
    public static function rpc_unpublish($streamName) {
        $wowza = CM_Service_Manager::getInstance()->getStreamVideo();
        $wowza->_extractServerIdFromRequest(CM_Http_Request_Abstract::getInstance());

        $streamRepository = $wowza->_getStreamRepository();
        $streamChannel = $streamRepository->findStreamChannelByKey($streamName);

        if ($streamChannel) {
            /** @var CM_Model_StreamChannel_Media $streamChannel */
            $streamRepository->removeStream($streamChannel->getStreamPublish());
        }
        return true;
    }

    /**
     * @param string $streamName
     * @param string $clientKey
     * @param string $start
     * @param string $data
     * @return bool
     * @throws CM_Exception_AuthRequired
     * @throws CM_Exception_Invalid
     * @throws CM_Exception_NotAllowed
     */
    public static function rpc_subscribe($streamName, $clientKey, $start, $data) {
        $wowza = CM_Service_Manager::getInstance()->getStreamVideo();
        $wowza->_extractServerIdFromRequest(CM_Http_Request_Abstract::getInstance());

        $params = CM_Params::factory(CM_Params::jsonDecode($data), true);
        $user = null;
        if ($params->has('sessionId')) {
            if ($session = CM_Session::findById($params->getString('sessionId'))) {
                $user = $session->getUser(false);
            }
        }

        $streamRepository = $wowza->_getStreamRepository();
        $streamChannel = $streamRepository->findStreamChannelByKey($streamName);
        if (!$streamChannel) {
            throw new CM_Exception_NotAllowed();
        }

        try {
            $streamRepository->createStreamSubscribe($streamChannel, $user, $clientKey, $start);
        } catch (CM_Exception $ex) {
            throw new CM_Exception_NotAllowed('Cannot subscribe: ' . $ex->getMessage());
        }
        return true;
    }

    /**
     * @param string $streamName
     * @param string $clientKey
     * @return boolean
     */
    public static function rpc_unsubscribe($streamName, $clientKey) {
        $wowza = CM_Service_Manager::getInstance()->getStreamVideo();
        $wowza->_extractServerIdFromRequest(CM_Http_Request_Abstract::getInstance());

        $streamRepository = $wowza->_getStreamRepository();
        $streamChannel = $streamRepository->findStreamChannelByKey($streamName);

        if ($streamChannel) {
            $streamSubscribe = $streamChannel->getStreamSubscribes()->findKey($clientKey);
            if ($streamSubscribe) {
                $streamRepository->removeStream($streamSubscribe);
            }
        }
        return true;
    }

    /**
     * @param CM_Http_Request_Abstract $request
     * @return int
     * @throws CM_Exception_Invalid
     */
    protected function _extractServerIdFromRequest(CM_Http_Request_Abstract $request) {
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
     */
    public function _stopStream(CM_Model_Stream_Abstract $stream) {
        /** @var $streamChannel CM_Model_StreamChannel_Media */
        $streamChannel = $stream->getStreamChannel();
        $privateHost = $this->_getPrivateHost($streamChannel->getServerId());
        $this->_stopClient($privateHost, $stream->getKey());
    }

    /**
     * @param string $serverHost
     * @param string $clientKey
     * @return string
     * @throws CM_Exception_Invalid
     */
    protected function _stopClient($serverHost, $clientKey) {
        return CM_Util::getContents('http://' . $serverHost . ':' . $this->_config['httpPort'] .
            '/stop', array('clientId' => (string) $clientKey), true);
    }

    /**
     * @param int|null $serverId
     * @throws CM_Exception_Invalid
     * @return array
     */
    protected function _getServer($serverId = null) {
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
    protected function _getPublicHost($serverId) {
        $serverId = (int) $serverId;
        $server = $this->_getServer($serverId);
        return $server['publicHost'];
    }

    /**
     * @param int $serverId
     * @return string
     * @throws CM_Exception_Invalid
     */
    protected function _getPrivateHost($serverId) {
        $serverId = (int) $serverId;
        $server = $this->_getServer($serverId);
        return $server['privateIp'];
    }

    /**
     * @param string $wowzaHost
     * @return string
     */
    protected function _fetchStatus($wowzaHost) {
        return CM_Util::getContents('http://' . $wowzaHost . ':' . $this->_config['httpPort'] . '/status');
    }
}
