<?php

class CM_Wowza_Service extends CM_MediaStreams_Service {

    /** @var CM_Wowza_Configuration */
    protected $_configuration;

    /**
     * @param CM_Wowza_Configuration $configuration
     */
    public function __construct(CM_Wowza_Configuration $configuration) {
        $this->_configuration = $configuration;
    }

    public function synchronize() {
        $streamRepository = $this->_getStreamRepository();

        $startStampLimit = time() - 3;
        $status = array();
        foreach ($this->_configuration->getServers() as $server) {
            $singleStatus = CM_Params::decode($this->_fetchStatus($server), true);
            foreach ($singleStatus as $streamName => $publish) {
                $publish['server'] = $server;
                $status[$streamName] = $publish;
            }
        }

        $streamChannels = $streamRepository->getStreamChannels();
        foreach ($status as $streamName => $publish) {
            /** @var CM_MediaStreams_Server $server */
            $server = $publish['server'];
            /** @var CM_Model_StreamChannel_Abstract $streamChannel */
            $streamChannel = CM_Model_StreamChannel_Abstract::findByKeyAndAdapter($streamName, $this->getType());
            if (!$streamChannel || !$streamChannel->getStreamPublishs()->findKey($publish['clientId'])) {
                $this->_stopClient($server, $publish['clientId']);
            }

            foreach ($publish['subscribers'] as $clientId => $subscribe) {
                if (!$streamChannel || !$streamChannel->getStreamSubscribes()->findKey($clientId)) {
                    $this->_stopClient($server, $clientId);
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
     * @param CM_Model_Stream_Abstract $stream
     */
    protected function _stopStream(CM_Model_Stream_Abstract $stream) {
        /** @var $streamChannel CM_Model_StreamChannel_Media */
        $streamChannel = $stream->getStreamChannel();
        $server = $this->_configuration->getServer($streamChannel->getServerId());
        $this->_stopClient($server, $stream->getKey());
    }

    /**
     * @param CM_MediaStreams_Server $server
     * @param string $clientKey
     * @return string
     * @throws CM_Exception_Invalid
     */
    protected function _stopClient(CM_MediaStreams_Server $server, $clientKey) {
        return CM_Util::getContents('http://' . $server->getPrivateHost() . '/stop', ['clientId' => (string) $clientKey], true);
    }

    /**
     * @param CM_MediaStreams_Server $server
     * @return string
     * @throws CM_Exception_Invalid
     */
    protected function _fetchStatus(CM_MediaStreams_Server $server) {
        return CM_Util::getContents('http://' . $server->getPrivateHost() . '/status');
    }

    /**
     * @param CM_Http_Request_Abstract $request
     * @return int
     * @throws CM_Exception_Invalid
     */
    protected function _extractServerIdFromRequest(CM_Http_Request_Abstract $request) {
        $ipAddress = long2ip($request->getIp());
        $server = $this->_configuration->findServerByIp($ipAddress);
        if (null === $server) {
            throw new CM_Exception_Invalid('No video server with ipAddress `' . $ipAddress . '` found');
        }
        return $server->getId();
    }
}
