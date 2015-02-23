<?php

class CM_Stream_Video {

    /**
     * @param bool    $enabled
     * @param array[] $servers
     * @param array   $adapter
     * @throws CM_Exception_Invalid
     */
    public function __construct($enabled, array $servers = null, array $adapter = null) {
        $this->_enabled = (bool) $enabled;
        $this->_servers = (array) $servers;

        if (null !== $adapter) {
            $adapterConfig = isset($adapter['config']) ? $adapter['config'] : [];
            $this->_adapter = new $adapter['className']($adapterConfig, $this->getServers());
            if (!$this->_adapter instanceof CM_Stream_Adapter_Video_Abstract) {
                throw new CM_Exception_Invalid('Invalid stream video adapter');
            }
        }
    }

    /**
     * @return bool
     */
    public function getEnabled() {
        return $this->_enabled;
    }

    /**
     * @return CM_Stream_Adapter_Video_Abstract
     */
    public function getAdapter() {
        return $this->_adapter;
    }

    /**
     * @return array[]
     */
    public function getServers() {
        return $this->_servers;
    }

    /**
     * @param string|null $serverId
     * @throws CM_Exception_Invalid
     * @return array
     */
    public function getServer($serverId = null) {
        $servers = $this->getServers();
        if (null === $serverId) {
            $serverId = array_rand($servers);
        }

        $serverId = (int) $serverId;
        if (!array_key_exists($serverId, $servers)) {
            throw new CM_Exception_Invalid("No video server with id `$serverId` found");
        }
        return $servers[$serverId];
    }

    public function checkStreams() {
        $this->getAdapter()->checkStreams();
    }

    public function synchronize() {
        $this->getAdapter()->synchronize();
    }

    /**
     * @param CM_Model_Stream_Abstract $stream
     * @throws CM_Exception_Invalid
     */
    public function stopStream(CM_Model_Stream_Abstract $stream) {
        $this->getAdapter()->stopStream($stream);
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
        $serverId = self::getInstance()->getAdapter()->getServerId($request);

        $channelId = self::getInstance()->getAdapter()->publish($streamName, $clientKey, $start, $width, $height, $serverId, $data);
        return $channelId;
    }

    /**
     * @param string $streamName
     * @return bool
     */
    public static function rpc_unpublish($streamName) {
        $adapter = self::getInstance()->getAdapter();
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
        $adapter = self::getInstance()->getAdapter();
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
        $adapter = self::getInstance()->getAdapter();
        $adapter->getServerId(CM_Http_Request_Abstract::getInstance());
        $adapter->unsubscribe($streamName, $clientKey);
        return true;
    }

    /**
     * @deprecated use CM_Service_Manager::getInstance()->getStreamVideo()
     * @return CM_Stream_Video
     */
    public static function getInstance() {
        return CM_Service_Manager::getInstance()->getStreamVideo();
    }
}
