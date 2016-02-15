<?php

class CMService_NewRelic_Log_Handler extends CM_Log_Handler_Abstract {

    public function isHandling(CM_Log_Record $record) {
        if (!$record instanceof CM_Log_Record_Exception) {
            return false;
        }
        return parent::isHandling($record);
    }

    protected function _writeRecord(CM_Log_Record $record) {
        $newRelic = CM_Service_Manager::getInstance()->getNewrelic();
        /** @var CM_Log_Record_Exception $record */
        $newRelic->setNoticeError($record->getException());
    }

}
