<?php

class CM_Clockwork_Persistence_None extends CM_Clockwork_Persistence {

    public function __construct() {
    }

    public function getLastRuntime(CM_Clockwork_Event $event) {
        return null;
    }

    public function setRuntime(CM_Clockwork_Event $event, DateTime $runtime) {
    }
}
