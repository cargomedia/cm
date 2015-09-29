<?php

abstract class CM_Log_Handler_Abstract implements CM_Log_Handler_HandlerInterface {

    /** @var  bool */
    protected $_bubbling = true;

    /** @var int */
    protected $_level = CM_Log_Logger::DEBUG;

    /**
     * @param integer $level  The minimum logging level at which this handler will be triggered
     * @param Boolean $bubble Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct($level = CM_Log_Logger::DEBUG, $bubble = true) {
        $this->setLevel($level);
        $this->bubble = $bubble;
    }

    /**
     * {@inheritdoc}
     */
    public function getBubble() {
        return $this->_bubbling;
    }

    /**
     * @param bool $bubbling
     */
    public function setBubble($bubbling) {
        $this->_bubbling = $bubbling;
    }

    /**
     * {@inheritdoc}
     */
    public function getLevel() {
        return $this->_level;
    }

    /**
     * @param $level
     * @throws CM_Exception_Invalid
     */
    public function setLevel($level) {
        if (CM_Log_Logger::getLevelName($level)) {
            $this->_level = $level;
        }
    }

    /**
     * {@inheritdoc}
     */
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
