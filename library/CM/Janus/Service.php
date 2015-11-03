<?php

class CM_Janus_Service extends CM_MediaStreams_Service {

    /** @var CM_Janus_Configuration */
    protected $_configuration;

    /** @var CM_Janus_HttpApiClient */
    protected $_httpApiClient;

    /**
     * @param CM_Janus_Configuration $configuration
     * @param CM_Janus_HttpApiClient $httpClient
     * @param CM_MediaStreams_StreamRepository|null $streamRepository
     */
    public function __construct(CM_Janus_Configuration $configuration, CM_Janus_HttpApiClient $httpClient, CM_MediaStreams_StreamRepository $streamRepository = null) {
        $this->_configuration = $configuration;
        $this->_httpApiClient = $httpClient;
        parent::__construct($streamRepository);
    }

    public function synchronize() {
        throw new CM_Exception_NotImplemented();
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
     */
    protected function _stopStream(CM_Model_Stream_Abstract $stream) {
        /** @var $streamChannel CM_Model_StreamChannel_Media */
        $streamChannel = $stream->getStreamChannel();
        $server = $this->_configuration->getServer($streamChannel->getServerId());
        $this->_httpApiClient->stopStream($server, $stream->getKey());
    }
}
