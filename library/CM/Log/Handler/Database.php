<?php

class CM_Log_Handler_Database extends CM_Log_Handler_Abstract {

    private $_connection;

    public function __construct($connection, $level = CM_Log_Logger::DEBUG, $bubbling = false) {
        $this->_connection = $connection;
        parent::__construct($level, $bubbling);
    }

    /**
     * {@inheritdoc}
     */
    public function writeRecord(CM_Log_Record $record) {
        throw new CM_Exception_NotImplemented(__CLASS__ . ' not implemented yet.');
    }
}
