<?php

class CM_Stream_Video {

    /**
     * @param bool  $enabled
     * @param array $adapter
     * @throws CM_Exception_Invalid
     */
    public function __construct($enabled, array $adapter = null) {
        $this->_enabled = (bool) $enabled;

        if (null !== $adapter) {
            $reflectionClass = new ReflectionClass($adapter['class']);
            if (!$reflectionClass->isSubclassOf('CM_Stream_Adapter_Video_Abstract')) {
                throw new CM_Exception_Invalid('Invalid stream video adapter');
            }
            $this->_adapter = $reflectionClass->newInstanceArgs($adapter['arguments']);
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
        $serverId = CM_Service_Manager::getInstance()->getStreamVideo()->getAdapter()->getServerId($request);

        $channelId = CM_Service_Manager::getInstance()->getStreamVideo()->getAdapter()->publish($streamName, $clientKey, $start, $width, $height, $serverId, $data);
        return $channelId;
    }

    /**
     * @param string $streamName
     * @return bool
     */
    public static function rpc_unpublish($streamName) {
        $adapter = CM_Service_Manager::getInstance()->getStreamVideo()->getAdapter();
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
        $adapter = CM_Service_Manager::getInstance()->getStreamVideo()->getAdapter();
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
        $adapter = CM_Service_Manager::getInstance()->getStreamVideo()->getAdapter();
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
