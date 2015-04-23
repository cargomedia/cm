<?php

class CM_MessageStream_Service {

    /** @var CM_MessageStream_Adapter_Abstract|null */
    private $_adapter;

    /**
     * @param CM_MessageStream_Adapter_Abstract|null $adapter
     * @throws CM_Exception_Invalid
     */
    public function __construct(CM_MessageStream_Adapter_Abstract $adapter = null) {
        $this->_adapter = $adapter;
    }

    /**
     * @return boolean
     */
    public function getEnabled() {
        return null !== $this->_adapter;
    }

    /**
     * @return CM_MessageStream_Adapter_Abstract|null
     */
    public function getAdapter() {
        return $this->_adapter;
    }

    /**
     * @return string|null
     */
    public function getAdapterClass() {
        if (null === $this->getAdapter()) {
            return null;
        }
        return get_class($this->getAdapter());
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
        if (null === $this->getAdapter()) {
            return [];
        }
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
}
