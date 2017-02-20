<?php

abstract class CM_Clockwork_Storage_Abstract {

    /** @var string */
    protected $_context;

    /**
     * @param string $context
     */
    public function __construct($context) {
        $this->_context = (string) $context;
    }

    abstract public function fetchData();

    /**
     * @param CM_Clockwork_Event $event
     * @return CM_Clockwork_Event_Status
     */
    abstract public function getStatus(CM_Clockwork_Event $event);

    /**
     * @param CM_Clockwork_Event        $event
     * @param CM_Clockwork_Event_Status $status
     */
    abstract public function setStatus(CM_Clockwork_Event $event, CM_Clockwork_Event_Status $status);

}
