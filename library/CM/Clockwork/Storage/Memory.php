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
        return !empty($this->_data[$event->getName()]) ? clone $this->_data[$event->getName()] : new CM_Clockwork_Event_Status();
    }

    /**
     * @param CM_Clockwork_Event        $event
     * @param CM_Clockwork_Event_Status $status
     */
    public function setStatus(CM_Clockwork_Event $event, CM_Clockwork_Event_Status $status) {
        $this->_data[$event->getName()] = clone $status;
    }

}
