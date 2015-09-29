<?php

class CM_Log_Handler_File extends CM_Log_Handler_Abstract {

    private $_file;

    public function __construct($file, $level = CM_Log_Logger::DEBUG, $bubble = false) {
        $this->_file = $file;
        parent::__construct($level, $bubble);
    }

    public function writeRecord(CM_Log_Record $record) {
        throw new CM_Exception_NotImplemented(__CLASS__ . ' not implemented yet.');
    }
}
