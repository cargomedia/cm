<?php

class CM_Wowza_Server {

    /** @var int */
    protected $_id;

    /** @var string */
    protected $_privateIp;

    /** @var string */
    protected $_publicIp;

    /** @var string */
    protected $_publicHost;

    /** @var int */
    protected $_wowzaPort;

    /** @var int */
    protected $_httpPort;

    /**
     * @param int $serverId
     * @param string $publicHost
     * @param string $publicIp
     * @param string $privateIp
     * @param int $httpPort
     * @param int $wowzaPort
     */
    public function __construct($serverId, $publicHost, $publicIp, $privateIp, $httpPort, $wowzaPort) {
        $this->_id = $serverId;
        $this->_publicHost = (string) $publicHost;
        $this->_publicIp = (string) $publicIp;
        $this->_privateIp = (string) $privateIp;
        $this->_httpPort = (int) $httpPort;
        $this->_wowzaPort = (int) $wowzaPort;
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
    public function getPublicHost() {
        return $this->_publicHost;
    }

    /**
     * @return string
     */
    public function getPublicIp() {
        return $this->_publicIp;
    }

    /**
     * @return string
     */
    public function getPrivateIp() {
        return $this->_privateIp;
    }

    /**
     * @return string
     */
    public function getPrivateHost() {
        return $this->_privateIp . ':' . $this->_httpPort;
    }
}
