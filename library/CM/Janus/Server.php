<?php

class CM_Janus_Server implements CM_ArrayConvertible, JsonSerializable {

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
        $this->_httpAddress = (string) $httpAddress;
        $this->_webSocketAddress = (string) $webSocketAddress;
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
     * @return string
     */
    public function getWebSocketAddressSubscribeOnly() {
        return CM_Util::link($this->getWebSocketAddress(), ['subscribeOnly' => 1]);
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

    public function toArray() {
        return $this->jsonSerialize();
    }

    public static function fromArray(array $array) {
        throw new CM_Exception_NotImplemented();
    }
}
