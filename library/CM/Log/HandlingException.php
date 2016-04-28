<?php

class CM_Log_HandlingException extends CM_Exception {

    /** @var Exception */
    private $_originalException;

    /**
     * @param string $message
     * @param Exception   $originalException
     */
    public function __construct($message, Exception $originalException) {
        $this->_originalException = $originalException;
        parent::__construct($message, CM_Exception::ERROR);
    }

    /**
     * @return Exception
     */
    public function getOriginalException() {
        return $this->_originalException;
    }
}
