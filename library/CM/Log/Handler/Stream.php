<?php

class CM_Log_Handler_Stream extends CM_Log_Handler_Abstract {

    private $_stream;

    public function __construct($level = CM_Log_Logger::DEBUG, $bubble = false) {
        $this->_stream = [];
        parent::__construct($level, $bubble);
    }

    public function writeRecord(CM_Log_Record $record) {
        throw new CM_Exception_NotImplemented(__CLASS__ . ' not implemented yet.');
    }
}
