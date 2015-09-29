<?php

class CM_Log_Handler_File extends CM_Log_Handler_Abstract {

    private $_file;

    public function __construct($file, $level = CM_Log_Logger::DEBUG, $bubbling = false) {
        $this->_file = $file;
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
