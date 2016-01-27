<?php

class CM_Wowza_RpcEndpoints {

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
        $wowza = CM_Service_Manager::getInstance()->getWowza('wowza');
        $request = CM_Http_Request_Abstract::getInstance();
        self::_authenticate($wowza, $request);

        $server = $wowza->getConfiguration()->findServerByIp(long2ip($request->getIp()));
        $params = CM_Params::factory(CM_Params::jsonDecode($data), true);
        $session = new CM_Session($params->getString('sessionId'));
        $streamChannelType = $params->getInt('streamChannelType');
        $user = $session->getUser(true);

        $streamRepository = $wowza->getStreamRepository();
        $streamChannel = $streamRepository->createStreamChannel($streamName, $streamChannelType, $server->getId());
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
        $wowza = CM_Service_Manager::getInstance()->getWowza('wowza');
        self::_authenticate($wowza, CM_Http_Request_Abstract::getInstance());

        $streamRepository = $wowza->getStreamRepository();
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
        $wowza = CM_Service_Manager::getInstance()->getWowza('wowza');
        self::_authenticate($wowza, CM_Http_Request_Abstract::getInstance());

        $params = CM_Params::factory(CM_Params::jsonDecode($data), true);
        $user = null;
        if ($params->has('sessionId')) {
            if ($session = CM_Session::findById($params->getString('sessionId'))) {
                $user = $session->getUser(false);
            }
        }

        $streamRepository = $wowza->getStreamRepository();
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
        $wowza = CM_Service_Manager::getInstance()->getWowza('wowza');
        self::_authenticate($wowza, CM_Http_Request_Abstract::getInstance());

        $streamRepository = $wowza->getStreamRepository();
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
     * @param CM_Wowza_Service $wowza
     * @param CM_Http_Request_Abstract $request
     * @throws CM_Exception_AuthFailed
     */
    protected static function _authenticate(CM_Wowza_Service $wowza, CM_Http_Request_Abstract $request) {
        $ipAddress = long2ip($request->getIp());
        $server = $wowza->getConfiguration()->findServerByIp($ipAddress);
        if (null === $server) {
            throw new CM_Exception_AuthFailed('No video server with ipAddress `' . $ipAddress . '` found');
        }
    }
}
