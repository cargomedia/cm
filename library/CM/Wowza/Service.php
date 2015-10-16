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
     * @param int    $start
     * @param string $data
     * @return int
     */
    public static function rpc_publish($streamName, $clientKey, $start, $data) {
        $wowza = CM_Service_Manager::getInstance()->getStreamVideo();
        $serverId = $wowza->_extractServerIdFromRequest(CM_Http_Request_Abstract::getInstance());

        $channelId = $wowza->getClient()->publish($streamName, $clientKey, $start, $serverId, $data);
        return $channelId;
    }

    /**
     * @param string $streamName
     * @return bool
     */
    public static function rpc_unpublish($streamName) {
        $wowza = CM_Service_Manager::getInstance()->getStreamVideo();
        $wowza->_extractServerIdFromRequest(CM_Http_Request_Abstract::getInstance());
        $wowza->getClient()->unpublish($streamName);
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
        $wowza = CM_Service_Manager::getInstance()->getStreamVideo();
        $wowza->_extractServerIdFromRequest(CM_Http_Request_Abstract::getInstance());
        $wowza->getClient()->subscribe($streamName, $clientKey, $start, $data);
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
        $wowza->getClient()->unsubscribe($streamName, $clientKey);
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
}
