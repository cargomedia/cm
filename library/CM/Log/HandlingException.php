<?php

class CM_Log_HandlingException extends CM_Exception {

    /** @var Exception */
    private $_originalException;

    /**
     * @param Exception $originalException
     */
    public function __construct(Exception $originalException) {
        $this->_originalException = $originalException;
        parent::__construct($originalException->getMessage(), CM_Exception::ERROR);
    }

    /**
     * @return Exception
     */
    public function getOriginalException() {
        return $this->_originalException;
    }
}
