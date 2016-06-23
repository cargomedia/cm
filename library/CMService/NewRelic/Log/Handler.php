<?php

class CMService_NewRelic_Log_Handler extends CM_Log_Handler_Abstract {

    /** @var CMService_Newrelic */
    protected $_newRelicService;

    public function __construct($minLevel) {
        parent::__construct($minLevel);
        $this->_newRelicService = CM_Service_Manager::getInstance()->getNewrelic();
    }

    public function isHandling(CM_Log_Record $record) {
        if (true !== $this->_newRelicService->getEnabled()) {
            return false;
        }
        if (!$record->getContext()->getException()) {
            return false;
        }
        return parent::isHandling($record);
    }

    protected function _writeRecord(CM_Log_Record $record) {
        $this->_newRelicService->setNoticeError($record->getContext()->getException());
    }
}
