<?php

abstract class CM_Log_Handler_Abstract implements CM_Log_Handler_HandlerInterface {

    /** @var int */
    protected $_level;

    /**
     * @param int $level The minimum logging level at which this handler will be triggered
     */
    public function __construct($level = null) {
        $level = null === $level ? CM_Log_Logger::DEBUG : (int) $level;
        $this->setLevel($level);
    }

    public function getLevel() {
        return $this->_level;
    }

    /**
     * @param int $level
     * @throws CM_Exception_Invalid
     */
    public function setLevel($level) {
        $level = (int) $level;
        if (CM_Log_Logger::hasLevel($level)) {
            $this->_level = $level;
        }
    }

    public function handleRecord(CM_Log_Record $record) {
        if ($this->isHandling($record)) {
            $this->_writeRecord($record);
        }
    }

    public function isHandling(CM_Log_Record $record) {
        return $record->getLevel() >= $this->getLevel();
    }

    /**
     * @param CM_Log_Record $record
     */
    abstract protected function _writeRecord(CM_Log_Record $record);
}
