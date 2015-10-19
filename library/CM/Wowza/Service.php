<?php

class CM_Wowza_Service extends CM_MediaStreams_Service {

    /** @var CM_Wowza_Configuration */
    protected $_configuration;

    /** @var CM_Wowza_httpClient */
    protected $_httpClient;

    /**
     * @param CM_Wowza_Configuration $configuration
     * @param CM_Wowza_httpClient $httpClient
     */
    public function __construct(CM_Wowza_Configuration $configuration, CM_Wowza_httpClient $httpClient) {
        $this->_configuration = $configuration;
        $this->_httpClient = $httpClient;
    }

    /**
     * @return CM_Wowza_Configuration
     */
    public function getConfiguration() {
        return $this->_configuration;
    }

    public function synchronize() {
        $streamRepository = $this->getStreamRepository();

        $startStampLimit = time() - 3;
        $status = [];
        foreach ($this->_configuration->getServers() as $server) {
            $singleServerStatus = $this->_httpClient->fetchStatus($server);
            foreach ($singleServerStatus as $streamName => $publish) {
                $publish['server'] = $server;
                $status[$streamName] = $publish;
            }
        }

        foreach ($status as $streamName => $publish) {
            /** @var CM_Wowza_Server $server */
            $server = $publish['server'];
            /** @var CM_Model_StreamChannel_Abstract $streamChannel */
            $streamChannel = CM_Model_StreamChannel_Abstract::findByKeyAndAdapter($streamName, $this->getType());
            if (!$streamChannel || !$streamChannel->getStreamPublishs()->findKey($publish['clientId'])) {
                $this->_httpClient->stopClient($server, $publish['clientId']);
            }

            foreach ($publish['subscribers'] as $clientId => $subscribe) {
                if (!$streamChannel || !$streamChannel->getStreamSubscribes()->findKey($clientId)) {
                    $this->_httpClient->stopClient($server, $clientId);
                }
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
     * @param CM_Model_Stream_Abstract $stream
     */
    protected function _stopStream(CM_Model_Stream_Abstract $stream) {
        /** @var $streamChannel CM_Model_StreamChannel_Media */
        $streamChannel = $stream->getStreamChannel();
        $server = $this->_configuration->getServer($streamChannel->getServerId());
        $this->_httpClient->stopClient($server, $stream->getKey());
    }
}
