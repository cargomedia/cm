<?php

abstract class CM_Log_Handler_Abstract implements CM_Log_Handler_HandlerInterface {

    /** @var int */
    protected $_level;

    /**
     * @param int  $level  The minimum logging level at which this handler will be triggered
     */
    public function __construct($level = null) {
        if (null === $level) {
            $level = CM_Log_Logger::DEBUG;
        }
        $level = (int) $level;
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
            $this->_level = (int) $level;
        }
    }

    public function handleRecord(CM_Log_Record $record) {
        $handled = false;
        if ($this->isHandling($record)) {
            $handled = $this->writeRecord($record);
        }
        return $handled;
    }

    /**
     * @param CM_Log_Record $record
     * @return bool
     */
    public function isHandling(CM_Log_Record $record) {
        return $record->getLevel() >= $this->getLevel();
    }

    /**
     * @param CM_Log_Record $record
     */
    abstract public function writeRecord(CM_Log_Record $record);
}
