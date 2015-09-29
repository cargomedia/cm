<?php

class CM_Log_Record {

    /** @var int */
    private $_level;

    /** @var string */
    private $_message;

    /** @var CM_Log_Context */
    private $_context;

    /**
     * @param int            $level
     * @param string         $message
     * @param CM_Log_Context $context
     */
    public function __construct($level, $message, CM_Log_Context $context) {
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
