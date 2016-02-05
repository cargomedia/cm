<?php

class CM_Log_Handler_Stream_Cli extends CM_Log_Handler_Stream {

    public function isHandling(CM_Log_Record $record) {
        if (!CM_Bootloader::getInstance()->isCli()) {
            return false;
        }
        return parent::isHandling($record);
    }
}
