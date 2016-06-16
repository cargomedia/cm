<?php

class CMService_NewRelic_Log_Handler extends CM_Log_Handler_Abstract {

    public function isHandling(CM_Log_Record $record) {
        if (!$record->getContext()->getException()) {
            return false;
        }
        return parent::isHandling($record);
    }

    protected function _writeRecord(CM_Log_Record $record) {
        $newRelic = CM_Service_Manager::getInstance()->getNewrelic();
        $newRelic->setNoticeError($record->getContext()->getException());
    }
}
