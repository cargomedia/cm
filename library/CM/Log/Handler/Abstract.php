<?php

abstract class CM_Log_Handler_Abstract implements CM_Log_Handler_HandlerInterface {

    /** @var int */
    protected $_level;

    /** @var bool */
    protected $_bubble;

    /**
     * @param int|null  $level
     * @param bool|null $stopPropagation
     */
    public function __construct($level = null, $stopPropagation = null) {
        $level = null === $level ? CM_Log_Logger::DEBUG : (int) $level;
        $this->_bubble = null === $stopPropagation ? true : !(bool) $stopPropagation;

        $this->setLevel($level);
    }

    /**
     * @return int
     */
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

    public function isBubbling() {
        return $this->_bubble;
    }

    public function isHandling(CM_Log_Record $record) {
        return $record->getLevel() >= $this->getLevel();
    }

    public function handleRecord(CM_Log_Record $record) {
        if ($this->isHandling($record)) {
            $this->_writeRecord($record);
        }
    }

    /**
     * @param CM_Log_Record $record
     */
    abstract protected function _writeRecord(CM_Log_Record $record);
}
