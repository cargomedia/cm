<?php

class CM_Janus_RpcEndpoints {

    /**
     * @param string $serverKey
     * @param string $streamChannelKey
     * @param string $streamKey
     * @param int    $start
     * @param string $sessionData
     * @param string $channelData
     * @param string $mediaId
     * @throws CM_Exception_AuthFailed
     * @throws CM_Exception_AuthRequired
     * @throws CM_Exception_Invalid
     * @throws CM_Exception_NotAllowed
     */
    public static function rpc_publish($serverKey, $streamChannelKey, $streamKey, $start, $sessionData, $channelData, $mediaId) {
        $janus = CM_Service_Manager::getInstance()->getJanus('janus');
        self::_authenticate($janus, $serverKey);

        $paramsSession = CM_Params::factory(CM_Params::jsonDecode($sessionData), true);
        $session = new CM_Session($paramsSession->getString('sessionId'));
        $user = $session->getUser(true);

        $paramsChannel = CM_Params::factory(CM_Params::jsonDecode($channelData), true);
        $streamChannelType = $paramsChannel->getInt('streamChannelType');

        $mediaId = (string) $mediaId;

        $streamRepository = $janus->getStreamRepository();
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = $streamRepository->findStreamChannelByKey($streamChannelKey);
        if (null === $streamChannel) {
            $server = $janus->getConfiguration()->findServerByKey($serverKey);
            $streamChannel = $streamRepository->createStreamChannel($streamChannelKey, $streamChannelType, $server->getId(), 0, $mediaId);
        } elseif ($streamChannel->getType() !== $streamChannelType) {
            throw new CM_Exception_Invalid('Passed stream channel type does not match existing');
        } elseif ($streamChannel->getMediaId() !== $mediaId) {
            throw new CM_Exception_Invalid('Passed stream channel mediaId does not match existing');
        }
        try {
            $streamRepository->createStreamPublish($streamChannel, $user, $streamKey, $start);
        } catch (CM_Exception_NotAllowed $exception) {
            $streamChannel->delete();
            throw new CM_Exception_NotAllowed('Cannot publish: ' . $exception->getMessage());
        }
    }

    /**
     * @param string $serverKey
     * @param string $streamChannelKey
     * @param string $streamKey
     * @param string $start
     * @param string $sessionData
     * @param string $channelData
     * @param string $mediaId
     * @throws CM_Exception_AuthFailed
     * @throws CM_Exception_AuthRequired
     * @throws CM_Exception_Invalid
     * @throws CM_Exception_Nonexistent
     * @throws CM_Exception_NotAllowed
     */
    public static function rpc_subscribe($serverKey, $streamChannelKey, $streamKey, $start, $sessionData, $channelData, $mediaId) {
        $janus = CM_Service_Manager::getInstance()->getJanus('janus');
        self::_authenticate($janus, $serverKey);

        $params = CM_Params::factory(CM_Params::jsonDecode($sessionData), true);
        $session = new CM_Session($params->getString('sessionId'));
        $user = $session->getUser(true);

        $paramsChannel = CM_Params::factory(CM_Params::jsonDecode($channelData), true);
        $streamChannelType = $paramsChannel->getInt('streamChannelType');

        $mediaId = (string) $mediaId;

        $streamRepository = $janus->getStreamRepository();
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = $streamRepository->findStreamChannelByKey($streamChannelKey);
        if (null === $streamChannel) {
            $server = $janus->getConfiguration()->findServerByKey($serverKey);
            $streamChannel = $streamRepository->createStreamChannel($streamChannelKey, $streamChannelType, $server->getId(), 0, $mediaId);
        } elseif ($streamChannel->getType() !== $streamChannelType) {
            throw new CM_Exception_Invalid('Passed stream channel type does not match existing');
        } elseif ($streamChannel->getMediaId() !== $mediaId) {
            throw new CM_Exception_Invalid('Passed stream channel mediaId does not match existing');
        }
        try {
            $streamRepository->createStreamSubscribe($streamChannel, $user, $streamKey, $start);
        } catch (CM_Exception_NotAllowed $exception) {
            throw new CM_Exception_NotAllowed('Cannot subscribe: ' . $exception->getMessage());
        }
    }

    /**
     * @param string $serverKey
     * @param string $streamChannelKey
     * @param string $streamKey
     * @return bool
     * @throws CM_Exception_AuthFailed
     * @throws CM_Exception_Invalid
     */
    public static function rpc_removeStream($serverKey, $streamChannelKey, $streamKey) {
        $janus = CM_Service_Manager::getInstance()->getJanus('janus');
        self::_authenticate($janus, $serverKey);

        $streamRepository = $janus->getStreamRepository();
        $streamChannel = $streamRepository->findStreamChannelByKey($streamChannelKey);
        if (null === $streamChannel) {
            return;
        }
        $streamSubscribe = $streamChannel->getStreamSubscribes()->findKey($streamKey);
        if ($streamSubscribe) {
            $streamRepository->removeStream($streamSubscribe);
        }
        $streamPublish = $streamChannel->getStreamPublishs()->findKey($streamKey);
        if ($streamPublish) {
            $streamRepository->removeStream($streamPublish);
        }
    }

    /**
     * @param string $serverKey
     * @param string $sessionData
     * @return bool
     * @throws CM_Exception_AuthFailed
     * @throws CM_Exception_Invalid
     */
    public static function rpc_isValidUser($serverKey, $sessionData) {
        $janus = CM_Service_Manager::getInstance()->getJanus('janus');
        self::_authenticate($janus, $serverKey);

        $params = CM_Params::factory(CM_Params::jsonDecode($sessionData), true);
        $session = CM_Session::findById($params->getString('sessionId'));
        return $session && null !== $session->getUser(false);
    }

    /**
     * @param CM_Janus_Service $janus
     * @param string           $serverKey
     * @throws CM_Exception_AuthFailed
     */
    protected static function _authenticate(CM_Janus_Service $janus, $serverKey) {
        if (!$janus->getConfiguration()->findServerByKey($serverKey)) {
            throw new CM_Exception_AuthFailed('Invalid serverKey');
        }
    }
}
