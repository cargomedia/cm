<?php

class CM_Clockwork_Storage_Memory extends CM_Clockwork_Storage_Abstract {

    private $_data = [];

    /**
     * @param string|null $context
     */
    public function __construct($context = null) {
        $context = (null !== $context) ? $context : 'memory';
        parent::__construct($context);
    }

    public function fetchData() {
    }

    /**
     * @param CM_Clockwork_Event $event
     * @return CM_Clockwork_Event_Status
     */
    public function getStatus(CM_Clockwork_Event $event) {
        return !empty($this->_data[$event->getName()]) ? $this->_cloneStatus($this->_data[$event->getName()]) : new CM_Clockwork_Event_Status();
    }

    /**
     * @param CM_Clockwork_Event        $event
     * @param CM_Clockwork_Event_Status $status
     */
    public function setStatus(CM_Clockwork_Event $event, CM_Clockwork_Event_Status $status) {
        $this->_data[$event->getName()] = $this->_cloneStatus($status);
    }

    /**
     * @param CM_Clockwork_Event_Status $status
     * @return CM_Clockwork_Event_Status
     */
    protected function _cloneStatus(CM_Clockwork_Event_Status $status) {
        $clone = new CM_Clockwork_Event_Status();
        $clone->setRunning($status->isRunning());
        if (null !== $status->getLastRuntime()) {
            $clone->setLastRuntime(clone $status->getLastRuntime());
        }
        if (null !== $status->getLastStartTime()) {
            $clone->setLastStartTime(clone $status->getLastStartTime());
        }
        return $clone;
    }

}
