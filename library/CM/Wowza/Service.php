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

    /**
     * @return CM_Wowza_Configuration
     */
    public function getConfiguration() {
        return $this->_configuration;
    }

    public function synchronize() {
        $streamRepository = $this->getStreamRepository();

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
}
