<?php

class CM_Janus_Server {

    /** @var int */
    protected $_id;

    /** @var string */
    protected $_httpAddress;

    /** @var string */
    protected $_webSocketAddress;

    /** @var string */
    protected $_token;

    /**
     * @param int $serverId
     * @param string $token
     * @param string $httpAddress
     * @param string $webSocketAddress
     */
    public function __construct($serverId, $token, $httpAddress, $webSocketAddress) {
        $this->_id = (int) $serverId;
        $this->_token = (string) $token;
        $this->_httpAddress = (string) $httpAddress;
        $this->_webSocketAddress = (int) $webSocketAddress;
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
    public function getToken() {
        return $this->_token;
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
