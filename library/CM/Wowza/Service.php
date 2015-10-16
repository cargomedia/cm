<?php

class CM_Wowza_Service extends CM_StreamServiceAdapter {

    /** @var CM_Wowza_Client */
    private $_client;

    /**
     * @param array|null $servers
     * @param array|null $config
     */
    public function __construct(array $servers = null, array $config = null) {
        $this->_client = new CM_Wowza_Client($this->getType(), $servers, $config);
    }

    /**
     * @return CM_Wowza_Client
     */
    public function getClient() {
        return $this->_client;
    }

    public function checkStreams() {
        $this->getClient()->checkStreams();
    }

    public function synchronize() {
        $this->getClient()->synchronize();
    }

    /**
     * @param CM_Model_Stream_Abstract $stream
     * @throws CM_Exception_Invalid
     */
    public function stopStream(CM_Model_Stream_Abstract $stream) {
        $this->getClient()->stopStream($stream);
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

    protected function _getStreamRepository() {
        return new CM_Streaming_MediaStreamRepository($this->getType());
    }
}
