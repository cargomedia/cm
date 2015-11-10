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
        $streamList = $this->_fetchStatus();

        foreach ($streamList as $stream) {
            $streamChannel = $streamRepository->findStreamChannelByKey($stream->getStreamChannelKey());
            /** @var CM_Janus_Stream $stream */
            $clientKey = $stream->getStreamKey();
            if ($streamChannel) {
                if ($streamChannel->getStreamPublishs()->findKey($clientKey)) {
                    continue;
                }
                if ($streamChannel->getStreamSubscribes()->findKey($clientKey)) {
                    continue;
                }
            }
            $this->_httpApiClient->stopStream($stream->getServer(), $clientKey);
        }

        $startStampLimit = time() - 3;

        $streamKeyMap = [];
        foreach ($streamList as $stream) {
            $streamKeyMap[$stream->getStreamKey()] = true; //need just to define key
        }

        $streamChannels = $streamRepository->getStreamChannels();
        /** @var CM_Model_StreamChannel_Abstract $streamChannel */
        foreach ($streamChannels as $streamChannel) {
            if (!$streamChannel->hasStreams()) {
                $streamChannel->delete();
                continue;
            }

            $streams = array_merge(
                $streamChannel->getStreamPublishs()->getItems(),
                $streamChannel->getStreamSubscribes()->getItems()
            );

            /** @var CM_Model_Stream_Abstract $stream */
            foreach ($streams as $stream) {
                $isJustCreated = $stream->getStart() > $startStampLimit;
                if ($isJustCreated) {
                    continue;
                }
                if (!array_key_exists($stream->getKey(), $streamKeyMap)) {
                    $streamRepository->removeStream($stream);
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
                $status[] = new CM_Janus_Stream($streamInfo['id'], $streamInfo['channelName'], $server);
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
