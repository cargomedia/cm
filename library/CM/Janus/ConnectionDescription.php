<?php

class CM_Janus_ConnectionDescription implements JsonSerializable, CM_ArrayConvertible {

    /** @var CM_StreamChannel_Definition */
    protected $_channelDefinition;

    /** @var  CM_Janus_Server */
    protected $_server;

    /**
     * @param CM_StreamChannel_Definition $channelDefinition
     * @param CM_Janus_Server             $server
     */
    public function __construct(CM_StreamChannel_Definition $channelDefinition, CM_Janus_Server $server) {
        $this->_channelDefinition = $channelDefinition;
        $this->_server = $server;
    }

    /**
     * @return CM_StreamChannel_Definition
     */
    public function getChannelDefinition() {
        return $this->_channelDefinition;
    }

    /**
     * @return CM_Janus_Server
     */
    public function getServer() {
        return $this->_server;
    }

    public function jsonSerialize() {
        return [
            'channel' => $this->getChannelDefinition()->jsonSerialize(),
            'server'  => $this->getServer()->jsonSerialize(),
        ];
    }

    public function toArray() {
        return $this->jsonSerialize();
    }

    public static function fromArray(array $array) {
        throw new CM_Exception_NotImplemented();
    }

}
