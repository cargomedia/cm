<?php

abstract class CM_Log_Handler_Abstract implements CM_Log_Handler_HandlerInterface {

    /** @var int */
    protected $_levelMin;

    /** @var  bool */
    protected $_bubble;

    /**
     * @param int|null  $levelMin
     * @param bool|null $bubble
     */
    public function __construct($levelMin = null, $bubble = null) {
        $levelMin = null === $levelMin ? CM_Log_Logger::DEBUG : (int) $levelMin;
        $bubble = null === $bubble ? true : (bool) $bubble;

        $this->_bubble = $bubble;
        $this->setLevelMin($levelMin);
    }

    /**
     * @return int
     */
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

    public function isBubbling() {
        return $this->_bubble;
    }

    public function isHandling(CM_Log_Record $record) {
        return $record->getLevel() >= $this->getLevelMin();
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
