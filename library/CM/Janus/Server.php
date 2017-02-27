<?php

use CM\Url\Url;
use CM\Url\WsUrl;

class CM_Janus_Server implements JsonSerializable {

    /** @var int */
    protected $_id;

    /** @var Url */
    protected $_httpAddress;

    /** @var WsUrL */
    protected $_webSocketAddress;

    /** @var string */
    protected $_key;

    /** @var string[] */
    protected $_pluginList;

    /** @var CM_Geo_Point */
    protected $_location;

    /** @var  array */
    protected $_iceServerList;

    /**
     * @param int          $serverId
     * @param string       $key
     * @param string       $httpAddress
     * @param string       $webSocketAddress
     * @param string[]     $pluginList
     * @param CM_Geo_Point $location
     * @param array|null   $iceServerList
     */
    public function __construct($serverId, $key, $httpAddress, $webSocketAddress, array $pluginList, CM_Geo_Point $location, array $iceServerList = null) {
        if (null === $iceServerList) {
            $iceServerList = [];
        }
        $this->_id = (int) $serverId;
        $this->_key = (string) $key;
        $this->_httpAddress = Url::create((string) $httpAddress);
        $this->_webSocketAddress = WsUrl::create((string) $webSocketAddress);
        $this->_pluginList = array_map(function ($plugin) {
            return (string) $plugin;
        }, $pluginList);
        $this->_location = $location;
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
     * @return Url
     */
    public function getHttpAddress() {
        return $this->_httpAddress;
    }

    /**
     * @return WsUrl
     */
    public function getWebSocketAddress() {
        return $this->_webSocketAddress;
    }

    /**
     * @return string
     */
    public function getWebSocketAddressSubscribeOnly() {
        return (string) $this->_webSocketAddress->withQuery('subscribeOnly=1');
    }

    /**
     * @return string[]
     */
    public function getPluginList() {
        return $this->_pluginList;
    }

    /**
     * @return CM_Geo_Point
     */
    public function getLocation() {
        return $this->_location;
    }

    /**
     * @return array
     */
    public function getIceServerList() {
        return $this->_iceServerList;
    }

    public function jsonSerialize() {
        return [
            'id'                            => $this->_id,
            'webSocketAddress'              => $this->getWebSocketAddress(),
            'webSocketAddressSubscribeOnly' => $this->getWebSocketAddressSubscribeOnly(),
            'iceServerList'                 => $this->getIceServerList(),
        ];
    }
}
