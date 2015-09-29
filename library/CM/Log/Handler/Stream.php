<?php

class CM_Log_Handler_Stream extends CM_Log_Handler_Abstract {

    private $_stream;

    public function __construct($level = CM_Log_Logger::DEBUG, $bubbling = false) {
        $this->_stream = [];
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
