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

    /** @var string[] */
    protected $_pluginList;

    /** @var  array */
    protected $_iceServerList;

    /**
     * @param int        $serverId
     * @param string     $key
     * @param string     $httpAddress
     * @param string     $webSocketAddress
     * @param string[]   $pluginList
     * @param array|null $iceServerList
     */
    public function __construct($serverId, $key, $httpAddress, $webSocketAddress, array $pluginList, array $iceServerList = null) {
        if (null === $iceServerList) {
            $iceServerList = [];
        }
        $this->_id = (int) $serverId;
        $this->_key = (string) $key;
        $this->_httpAddress = (string) $httpAddress;
        $this->_webSocketAddress = (string) $webSocketAddress;
        $this->_pluginList = array_map(function ($el) {
            return (string) $el;
        }, $pluginList);
        $this->_iceServerList = $iceServerList;
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

    /**
     * @return string[]
     */
    public function getPluginList() {
        return $this->_pluginList;
    }

    /**
     * @return array
     */
    public function getIceServerList() {
        return $this->_iceServerList;
    }
}
