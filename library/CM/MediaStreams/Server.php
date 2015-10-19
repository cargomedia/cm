<?php

class CM_MediaStreams_Server {

    /** @var int */
    protected $_id;

    /** @var string */
    protected $_privateIp;

    /** @var string */
    protected $_publicIp;

    /** @var string */
    protected $_publicHost;

    /**
     * @param int $serverId
     * @param string $publicHost
     * @param string $publicIp
     * @param string $privateIp
     *
     */
    public function __construct($serverId, $publicHost, $publicIp, $privateIp) {
        $this->_id = $serverId;
        $this->_publicHost = (string) $publicHost;
        $this->_publicIp = (string) $publicIp;
        $this->_privateIp = (string) $privateIp;
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
}
