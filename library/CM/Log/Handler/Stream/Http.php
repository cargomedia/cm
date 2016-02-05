<?php

class CM_Log_Handler_Stream_Http extends CM_Log_Handler_Stream {

    public function isHandling(CM_Log_Record $record) {
        if (CM_Bootloader::getInstance()->isCli()) {
            return false;
        }
        return parent::isHandling($record);
    }

    public function isBubbling() {
        return true; //force
    }

    protected function _writeRecord(CM_Log_Record $record) {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: text/html');
        }
        if (!CM_Bootloader::getInstance()->isDebug()) {
            $this->_stream->writeln('Internal server error');
        } else {
            parent::_writeRecord($record);
        }
    }
}
