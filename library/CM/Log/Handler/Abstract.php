<?php

abstract class CM_Log_Handler_Abstract implements CM_Log_Handler_HandlerInterface {

    /** @var int */
    protected $_levelMin;

    /**
     * @param int $levelMin The minimum logging level at which this handler will be triggered
     */
    public function __construct($levelMin = null) {
        $levelMin = null === $levelMin ? CM_Log_Logger::DEBUG : (int) $levelMin;
        $this->setLevelMin($levelMin);
    }

    public function getLevelMin() {
        return $this->_levelMin;
    }

    /**
     * @param int $level
     * @throws CM_Exception_Invalid
     */
    public function setLevelMin($level) {
        $level = (int) $level;
        if (CM_Log_Logger::hasLevel($level)) {
            $this->_levelMin = $level;
        }
    }

    public function handleRecord(CM_Log_Record $record) {
        if ($this->isHandling($record)) {
            $this->_writeRecord($record);
        }
    }

    public function isHandling(CM_Log_Record $record) {
        return $record->getLevel() >= $this->getLevelMin();
    }

    /**
     * @param CM_Log_Record $record
     */
    abstract protected function _writeRecord(CM_Log_Record $record);
}
