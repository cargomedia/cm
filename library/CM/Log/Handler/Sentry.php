<?php

class CM_Log_Handler_Sentry extends CM_Log_Handler_Abstract {

    private $_client;

    public function __construct($client, $level = CM_Log_Logger::DEBUG, $bubble = false) {
        $this->_client = $client;
        parent::__construct($level, $bubble);
    }

    public function writeRecord(CM_Log_Record $record) {
        throw new CM_Exception_NotImplemented(__CLASS__ . ' not implemented yet.');
    }
}
