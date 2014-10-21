<?php

class CM_Clockwork_Persistence_None extends CM_Clockwork_Persistence {

    public function __construct() {
    }

    public function getLastRuntime(CM_Clockwork_Event $event) {
        if (empty($this->_data[$event->getName()])) {
            return null;
        }
        return clone $this->_data[$event->getName()];
    }

    public function setRuntime(CM_Clockwork_Event $event, DateTime $runtime) {
        $this->_data[$event->getName()] = clone $runtime;
    }
}
