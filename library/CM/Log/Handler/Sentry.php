<?php

class CM_Log_Handler_Sentry extends CM_Log_Handler_Abstract {

    private $_client;

    public function __construct($client, $level = CM_Log_Logger::DEBUG, $bubbling = false) {
        $this->_client = $client;
        parent::__construct($level, $bubbling);
    }

    /**
     * @param CM_Log_Record $record
     * @return bool Whether the record was successfully handled
     */
    public function handleRecord(CM_Log_Record $record) {
        return false;
    }
}
