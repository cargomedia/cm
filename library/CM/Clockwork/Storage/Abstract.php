<?php

abstract class CM_Clockwork_Storage_Abstract {

    /** @var string */
    protected $_context;

    /** @var DateTime[] */
    private $_data = null;

    /**
     * @param string $context
     */
    public function __construct($context) {
        $this->_context = (string) $context;
    }

    /**
     * @return array
     */
    abstract protected function _load();

    /**
     * @param array $data
     */
    abstract protected function _save(array $data);

    /**
     * @param CM_Clockwork_Event $event
     * @return DateTime|null
     */
    public function getLastRuntime(CM_Clockwork_Event $event) {
        if (null === $this->_data) {
            $this->_data = $this->_load();
        }
        if (empty($this->_data[$event->getName()])) {
            return null;
        }
        return clone $this->_data[$event->getName()];
    }

    /**
     * @param CM_Clockwork_Event $event
     * @param DateTime           $runtime
     */
    public function setRuntime(CM_Clockwork_Event $event, DateTime $runtime) {
        if (null === $this->_data) {
            $this->_data = $this->_load();
        }
        $this->_data[$event->getName()] = clone $runtime;
        $this->_save($this->_data);
    }

}
