<?php

class CM_Janus_Stream {

    /** @var string */
    protected $_streamKey;

    /** @var string */
    protected $_streamChannelKey;

    /** @var CM_Janus_Server */
    protected $_server;

    /**
     * @param string $streamKey
     * @param string $streamChannelKey
     * @param CM_Janus_Server $server
     */
    public function __construct($streamKey, $streamChannelKey, CM_Janus_Server $server) {
        $this->_streamKey = $streamKey;
        $this->_streamChannelKey = $streamChannelKey;
        $this->_server = $server;
    }

    /**
     * @return string
     */
    public function getStreamKey() {
        return $this->_streamKey;
    }

    /**
     * @return string
     */
    public function getStreamChannelKey() {
        return $this->_streamChannelKey;
    }

    /**
     * @return CM_Janus_Server
     */
    public function getServer() {
        return $this->_server;
    }
}
