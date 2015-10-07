<?php

class CM_Log_Handler_Newrelic extends CM_Log_Handler_Abstract {

    /** @var  CMService_Newrelic */
    private $_newrelic;

    /**
     * @param CMService_Newrelic $newrelic
     * @param int|null           $level
     * @param bool|null          $bubble
     */
    public function __construct(CMService_Newrelic $newrelic, $level = null, $bubble = null) {
        $level = null === $level ? CM_Log_Logger::ERROR : $level;
        $this->_newrelic = $newrelic;
        parent::__construct($level, $bubble);
    }

    public function isHandling(CM_Log_Record $record) {
        return $record instanceof CM_Log_Record_Exception && parent::isHandling($record);
    }

    protected function _writeRecord(CM_Log_Record $record) {
        if (!$record instanceof CM_Log_Record_Exception) {
            throw new CM_Exception_Invalid('`' . get_class($record) . '` is not supported by `' . __CLASS__ . '`.');
        }
        /** @var CM_Log_Record_Exception $record */
        $this->_newrelic->setNoticeError($record->getOriginalException());
    }
}
