<?php

class CM_Janus_Service extends CM_MediaStreams_Service {

    /** @var CM_Janus_Configuration */
    protected $_configuration;

    /** @var CM_Janus_HttpApiClient */
    protected $_httpApiClient;

    /**
     * @param CM_Janus_Configuration                $configuration
     * @param CM_Janus_HttpApiClient                $httpClient
     * @param CM_MediaStreams_StreamRepository|null $streamRepository
     */
    public function __construct(CM_Janus_Configuration $configuration, CM_Janus_HttpApiClient $httpClient, CM_MediaStreams_StreamRepository $streamRepository = null) {
        $this->_configuration = $configuration;
        $this->_httpApiClient = $httpClient;
        parent::__construct($streamRepository);
    }

    public function synchronize() {
        $streamRepository = $this->getStreamRepository();

        $startStampLimit = time() - 3;
        $channelSubscriberList = [];

        $streamList = $this->_fetchStatus();
        foreach ($streamList as $stream) {
            $channelSubscriberList[$stream->getStreamChannelKey()][] = $stream->getStreamKey();
        }

        foreach ($streamList as $stream) {
            $clientKey = $stream->getStreamKey();

            /** @var CM_Model_StreamChannel_Abstract $streamChannel */
            $streamChannel = CM_Model_StreamChannel_Abstract::findByKeyAndAdapter($stream->getStreamChannelKey(), $this->getType());
            if (!$streamChannel
                || (true === $stream['isPublish'] && !$streamChannel->getStreamPublishs()->findKey($clientKey))
                || (false === $stream['isPublish'] && !$streamChannel->getStreamSubscribes()->findKey($clientKey))
            ) {
                $this->_httpApiClient->stopStream($stream->getServer(), $clientKey);
            }
        }

        $streamChannels = $streamRepository->getStreamChannels();
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
                if (!array_key_exists($streamChannel->getKey(), $channelSubscriberList)) {
                    $streamRepository->removeStream($streamPublish);
                }
            }

            /** @var CM_Model_Stream_Subscribe $streamSubscribe */
            foreach ($streamChannel->getStreamSubscribes() as $streamSubscribe) {
                if ($streamSubscribe->getStart() > $startStampLimit) {
                    continue;
                }
                if (!isset($channelSubscriberList[$streamChannel->getKey()][$streamSubscribe->getKey()])) {
                    $streamRepository->removeStream($streamSubscribe);
                }
            }
        }
    }

    /**
     * @return CM_Janus_Configuration
     */
    public function getConfiguration() {
        return $this->_configuration;
    }

    /**
     * @return CM_Janus_Stream[]
     * @throws CM_Exception_Invalid
     */
    protected function _fetchStatus() {
        $status = [];
        foreach ($this->_configuration->getServers() as $server) {
            foreach ($this->_httpApiClient->fetchStatus($server) as $streamInfo) {
                $status[] = new CM_Janus_Stream($streamInfo['streamKey'], $streamInfo['streamChannelKey'], $server);
            }
        }
        return $status;
    }

    /**
     * @param CM_Model_Stream_Abstract $stream
     * @throws CM_Exception_Invalid
     * @throws CM_Janus_StopStreamError
     */
    protected function _stopStream(CM_Model_Stream_Abstract $stream) {
        /** @var $streamChannel CM_Model_StreamChannel_Media */
        $streamChannel = $stream->getStreamChannel();
        $server = $this->_configuration->getServer($streamChannel->getServerId());
        $result = $this->_httpApiClient->stopStream($server, $stream->getKey());
        if (array_key_exists('error', $result)) {
            throw new CM_Janus_StopStreamError($result['error']);
        }
    }
}
