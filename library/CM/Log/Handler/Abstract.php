<?php

abstract class CM_Log_Handler_Abstract implements CM_Log_Handler_HandlerInterface {

    /** @var  bool */
    protected $_bubbling;

    /** @var int */
    protected $_level;

    /**
     * @param int  $level    The minimum logging level at which this handler will be triggered
     * @param bool $bubbling Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct($level = null, $bubbling = null) {
        $level = is_null($level) ? CM_Log_Logger::DEBUG : $level;
        $bubbling = is_null($bubbling) ? true : $bubbling;
        $this->setLevel($level);
        $this->setBubble($bubbling);
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
        $this->_bubbling = (bool) $bubbling;
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
            $this->_level = (int) $level;
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
