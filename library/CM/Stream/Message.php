<?php

class CM_Stream_Message {

    /** @var bool */
    private $_enabled;

    /** @var CM_Stream_Adapter_Message_Abstract */
    private $_adapter;

    /**
     * @param bool       $enabled
     * @param array|null $adapter
     * @throws CM_Exception_Invalid
     */
    public function __construct($enabled, array $adapter = null) {
        $this->_enabled = (bool) $enabled;

        if (null !== $adapter) {
            $reflectionClass = new ReflectionClass($adapter['class']);
            $this->_adapter = $reflectionClass->newInstanceArgs($adapter['arguments']);
            if (!$this->_adapter instanceof CM_Stream_Adapter_Message_Abstract) {
                throw new CM_Exception_Invalid('Invalid stream message adapter');
            }
        }
    }

    /**
     * @return boolean
     */
    public function getEnabled() {
        return $this->_enabled;
    }

    /**
     * @return CM_Stream_Adapter_Message_Abstract
     */
    public function getAdapter() {
        return $this->_adapter;
    }

    /**
     * @return string
     */
    public function getAdapterClass() {
        $adapter = $this->getAdapter();
        if (!$adapter) {
            return null;
        }
        return get_class($adapter);
    }

    public function startSynchronization() {
        if (!$this->getEnabled()) {
            throw new CM_Exception('Stream is not enabled');
        }
        $this->getAdapter()->startSynchronization();
    }

    public function synchronize() {
        if (!$this->getEnabled()) {
            throw new CM_Exception('Stream is not enabled');
        }
        $this->getAdapter()->synchronize();
    }

    /**
     * @return array
     */
    public function getOptions() {
        return $this->getAdapter()->getOptions();
    }

    /**
     * @param string     $channel
     * @param string     $event
     * @param mixed|null $data
     */
    public function publish($channel, $event, $data = null) {
        if (!$this->getEnabled()) {
            return;
        }
        $this->getAdapter()->publish($channel, $event, CM_Params::encode($data));
    }

    /**
     * @deprecated use CM_Service_Manager::getInstance()->getStreamMessage()
     * @return CM_Stream_Message
     */
    public static function getInstance() {
        return CM_Service_Manager::getInstance()->getStreamMessage();
    }
}
