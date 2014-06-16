<?php

class CM_Clockwork_Persistence_Noop extends CM_Clockwork_Persistence {

    public function __construct() {
    }

    public function getLastRunTime(CM_Clockwork_Event $event) {
        return null;
    }

    public function setRuntime(CM_Clockwork_Event $event, DateTime $runtime) {
    }

    protected function _load() {
    }
}
