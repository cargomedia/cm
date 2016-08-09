<?php

class CM_Log_Record {

    /** @var int */
    private $_level;

    /** @var string */
    private $_message;

    /** @var DateTime */
    private $_createdAt;

    /** @var CM_Log_Context */
    private $_context;

    /**
     * @param int            $level
     * @param string         $message
     * @param CM_Log_Context $context
     * @throws CM_Exception_Invalid
     */
    public function __construct($level, $message, CM_Log_Context $context) {
        $level = (int) $level;
        $message = (string) $message;

        if (!CM_Log_Logger::hasLevel($level)) {
            throw new CM_Exception_Invalid('Log level does not exist.', null, ['level' => $level]);
        }
        $this->_level = $level;
        $this->_message = $message;
        $this->_context = $context;
        $this->_createdAt = new DateTime();
        $this->_createdAt->setTimestamp(time());
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

    /**
     * @return DateTime
     */
    public function getCreatedAt() {
        return $this->_createdAt;
    }
}
