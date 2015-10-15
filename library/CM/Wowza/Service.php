<?php

class CM_Wowza_Service extends CM_StreamService {

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
     * @param int    $start
     * @param int    $width
     * @param int    $height
     * @param string $data
     * @return int
     */
    public static function rpc_publish($streamName, $clientKey, $start, $width, $height, $data) {
        $request = CM_Http_Request_Abstract::getInstance();
        $serverId = CM_Service_Manager::getInstance()->getStreamVideo()->getClient()->getServerId($request);

        $channelId = CM_Service_Manager::getInstance()->getStreamVideo()->getClient()->publish($streamName, $clientKey, $start, $width, $height, $serverId, $data);
        return $channelId;
    }

    /**
     * @param string $streamName
     * @return bool
     */
    public static function rpc_unpublish($streamName) {
        $adapter = CM_Service_Manager::getInstance()->getStreamVideo()->getClient();
        $adapter->getServerId(CM_Http_Request_Abstract::getInstance());
        $adapter->unpublish($streamName);
        return true;
    }

    /**
     * @param string $streamName
     * @param string $clientKey
     * @param string $start
     * @param string $data
     * @return boolean
     */
    public static function rpc_subscribe($streamName, $clientKey, $start, $data) {
        $adapter = CM_Service_Manager::getInstance()->getStreamVideo()->getClient();
        $adapter->getServerId(CM_Http_Request_Abstract::getInstance());
        $adapter->subscribe($streamName, $clientKey, $start, $data);
        return true;
    }

    /**
     * @param string $streamName
     * @param string $clientKey
     * @return boolean
     */
    public static function rpc_unsubscribe($streamName, $clientKey) {
        $adapter = CM_Service_Manager::getInstance()->getStreamVideo()->getClient();
        $adapter->getServerId(CM_Http_Request_Abstract::getInstance());
        $adapter->unsubscribe($streamName, $clientKey);
        return true;
    }


}
