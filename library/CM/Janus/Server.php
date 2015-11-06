<?php

class CM_Janus_Server {

    /** @var int */
    protected $_id;

    /** @var string */
    protected $_httpAddress;

    /** @var string */
    protected $_webSocketAddress;

    /** @var string */
    protected $_key;

    /**
     * @param int $serverId
     * @param string $key
     * @param string $httpAddress
     * @param string $webSocketAddress
     */
    public function __construct($serverId, $key, $httpAddress, $webSocketAddress) {
        $this->_id = (int) $serverId;
        $this->_key = (string) $key;
        $this->_httpAddress = (string) $httpAddress;
        $this->_webSocketAddress = (string) $webSocketAddress;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * @return string
     */
    public function getKey() {
        return $this->_key;
    }

    /**
     * @return string
     */
    public function getHttpAddress() {
        return $this->_httpAddress;
    }

    /**
     * @return string
     */
    public function getWebSocketAddress() {
        return $this->_webSocketAddress;
    }
}
