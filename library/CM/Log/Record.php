<?php

class CM_Log_Record {

    /** @var int */
    private $_level;

    /** @var string */
    private $_message;

    /** @var CM_Log_Context */
    private $_context;

    /**
     * @param                $level
     * @param                $message
     * @param CM_Log_Context $context
     * @throws CM_Exception_Invalid
     */
    public function __construct($level, $message, CM_Log_Context $context) {
        $level = (int) $level;
        $message = (string) $message;

        if (!CM_Log_Logger::hasLevel($level)) {
            throw new CM_Exception_Invalid('Log level `' . $level . '` does not exist.');
        }
        $this->_level = $level;
        $this->_message = $message;
        $this->_context = $context;
    }

    /**
     * @return int
     */
    public function getLevel() {
        return $this->_level;
    }

    /**
     * @return string
     */
    public function getMessage() {
        return $this->_message;
    }

    /**
     * @return CM_Log_Context
     */
    public function getContext() {
        return $this->_context;
    }
}
