<?php

class CM_Janus_RpcEndpoints {

    /**
     * @param string $serverKey
     * @param string $sessionData
     * @param string $channelKey
     * @param string $channelMediaId
     * @param string $channelData
     * @param string $streamKey
     * @param int    $streamStart
     * @throws CM_Exception_AuthFailed
     * @throws CM_Exception_AuthRequired
     * @throws CM_Exception_Invalid
     * @throws CM_Exception_NotAllowed
     */
    public static function rpc_publish($serverKey, $sessionData, $channelKey, $channelMediaId, $channelData, $streamKey, $streamStart) {
        $janus = CM_Service_Manager::getInstance()->getJanus('janus');
        self::_authenticate($janus, $serverKey);

        $server = $janus->getServerList()->findByKey($serverKey);
        $sessionParams = CM_Params::factory(CM_Params::jsonDecode($sessionData), true);
        $session = $sessionParams->getSession('sessionId');
        $user = $session->getUser(true);

        $channelKey = (string) $channelKey;
        $channelMediaId = (string) $channelMediaId;
        $channelParams = CM_Params::factory(CM_Params::jsonDecode($channelData), true);
        $channelType = $channelParams->getInt('streamChannelType');

        $streamKey = (string) $streamKey;
        $streamStart = (int) $streamStart;

        $streamRepository = $janus->getStreamRepository();
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = $streamRepository->findStreamChannelByKey($channelKey);
        if (null === $streamChannel) {
            $streamChannel = $streamRepository->createStreamChannel($channelKey, $channelType, $server->getId(), $channelMediaId);
        } else {
            $channelServerData = [
                'type'     => $streamChannel->getType(),
                'mediaId'  => $streamChannel->getMediaId(),
                'serverId' => $streamChannel->getServerId(),
            ];
            $channelRequestData = [
                'type'     => $channelType,
                'mediaId'  => $channelMediaId,
                'serverId' => $server->getId(),
            ];
            if ($channelServerData !== $channelRequestData) {
                throw new CM_Exception_Invalid('Channel request data differs from server data', null, [
                    'channelRequestData' => $channelRequestData,
                    'channelServerData'  => $channelServerData,
                ]);
            }
        }
        try {
            $streamRepository->createStreamPublish($streamChannel, $user, $streamKey, $streamStart);
        } catch (CM_Exception_NotAllowed $exception) {
            if (!$streamChannel->hasStreams()) {
                $streamChannel->delete();
            }
            throw new CM_Exception_NotAllowed('Cannot publish: ' . $exception->getMessage(), $exception->getSeverity());
        } catch (CM_Exception_Invalid $exception) {
            if (!$streamChannel->hasStreams()) {
                $streamChannel->delete();
            }
            throw new CM_Exception_Invalid('Cannot publish: ' . $exception->getMessage(), $exception->getSeverity());
        }
    }

    /**
     * @param string $serverKey
     * @param string $sessionData
     * @param string $channelKey
     * @param string $channelMediaId
     * @param string $channelData
     * @param string $streamKey
     * @param int    $streamStart
     * @throws CM_Exception_AuthFailed
     * @throws CM_Exception_AuthRequired
     * @throws CM_Exception_Invalid
     * @throws CM_Exception_NotAllowed
     */
    public static function rpc_subscribe($serverKey, $sessionData, $channelKey, $channelMediaId, $channelData, $streamKey, $streamStart) {
        $janus = CM_Service_Manager::getInstance()->getJanus('janus');
        self::_authenticate($janus, $serverKey);

        $server = $janus->getServerList()->findByKey($serverKey);
        $sessionParams = CM_Params::factory(CM_Params::jsonDecode($sessionData), true);
        $session = $sessionParams->getSession('sessionId');
        $user = $session->getUser(true);

        $channelKey = (string) $channelKey;
        $channelMediaId = (string) $channelMediaId;
        $channelParams = CM_Params::factory(CM_Params::jsonDecode($channelData), true);
        $channelType = $channelParams->getInt('streamChannelType');

        $streamKey = (string) $streamKey;
        $streamStart = (int) $streamStart;

        $streamRepository = $janus->getStreamRepository();
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = $streamRepository->findStreamChannelByKey($channelKey);
        if (null === $streamChannel) {
            $streamChannel = $streamRepository->createStreamChannel($channelKey, $channelType, $server->getId(), $channelMediaId);
        } else {
            $channelServerData = [
                'type'     => $streamChannel->getType(),
                'mediaId'  => $streamChannel->getMediaId(),
                'serverId' => $streamChannel->getServerId(),
            ];
            $channelRequestData = [
                'type'     => $channelType,
                'mediaId'  => $channelMediaId,
                'serverId' => $server->getId(),
            ];
            if ($channelServerData !== $channelRequestData) {
                throw new CM_Exception_Invalid('Channel request data differs from server data', null, [
                    'channelRequestData' => $channelRequestData,
                    'channelServerData'  => $channelServerData,
                ]);
            }
        }
        try {
            $streamRepository->createStreamSubscribe($streamChannel, $user, $streamKey, $streamStart);
        } catch (CM_Exception_NotAllowed $exception) {
            if (!$streamChannel->hasStreams()) {
                $streamChannel->delete();
            }
            throw new CM_Exception_NotAllowed('Cannot subscribe: ' . $exception->getMessage(), $exception->getSeverity());
        } catch (CM_Exception_Invalid $exception) {
            if (!$streamChannel->hasStreams()) {
                $streamChannel->delete();
            }
            throw new CM_Exception_Invalid('Cannot subscribe: ' . $exception->getMessage(), $exception->getSeverity());
        }
    }

    /**
     * @param string $serverKey
     * @param string $channelKey
     * @param string $streamKey
     * @return bool
     * @throws CM_Exception_AuthFailed
     * @throws CM_Exception_Invalid
     */
    public static function rpc_removeStream($serverKey, $channelKey, $streamKey) {
        $janus = CM_Service_Manager::getInstance()->getJanus('janus');
        self::_authenticate($janus, $serverKey);

        $server = $janus->getServerList()->findByKey($serverKey);
        $channelKey = (string) $channelKey;
        $streamKey = (string) $streamKey;

        $streamRepository = $janus->getStreamRepository();
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        $streamChannel = $streamRepository->findStreamChannelByKey($channelKey);
        if (null === $streamChannel) {
            return false;
        }

        if ($streamChannel->getServerId() !== $server->getId()) {
            throw new CM_Exception_Invalid("Request server `{$server->getId()}` does not match existing `{$streamChannel->getServerId()}`");
        }
        $streamSubscribe = $streamChannel->getStreamSubscribes()->findKey($streamKey);
        if ($streamSubscribe) {
            $streamRepository->removeStream($streamSubscribe);
            return true;
        }
        $streamPublish = $streamChannel->getStreamPublishs()->findKey($streamKey);
        if ($streamPublish) {
            $streamRepository->removeStream($streamPublish);
            return true;
        }
        return false;
    }

    /**
     * @param string $serverKey
     * @return bool
     * @throws CM_Exception_AuthFailed
     */
    public static function rpc_removeAllStreams($serverKey) {
        $janus = CM_Service_Manager::getInstance()->getJanus('janus');
        self::_authenticate($janus, $serverKey);

        $server = $janus->getServerList()->findByKey($serverKey);

        $streamRepository = $janus->getStreamRepository();
        /** @var CM_Model_StreamChannel_Media $streamChannel */
        foreach ($streamRepository->getStreamChannels() as $streamChannel) {
            if ($streamChannel->getServerId() === $server->getId()) {
                $streamRepository->removeStreamChannel($streamChannel);
            }
        }
        return true;
    }

    /**
     * @param CM_Janus_Service $janus
     * @param string           $serverKey
     * @throws CM_Exception_AuthFailed
     */
    protected static function _authenticate(CM_Janus_Service $janus, $serverKey) {
        if (!$janus->getServerList()->findByKey($serverKey)) {
            throw new CM_Exception_AuthFailed('Invalid serverKey');
        }
    }
}
