<?php

class CM_Log_Handler_Database extends CM_Log_Handler_Abstract {

    private $_connection;

    public function __construct($connection, $level = CM_Log_Logger::DEBUG, $bubbling = false) {
        $this->_connection = $connection;
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
