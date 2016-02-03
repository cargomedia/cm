<?php

class CM_Log_HandlingException extends CM_Exception {

    /** @var  Exception[] */
    private $_exceptionList;

    /**
     * @param string|null $message
     * @param Exception[] $exceptionList
     */
    public function __construct($message = null, array $exceptionList) {
        $message = null !== $message ? (string) $message : null;
        $this->_exceptionList = $exceptionList;
        parent::__construct($message, CM_Exception::ERROR);
    }

    /**
     * @return Exception[]
     */
    public function getExceptionList() {
        return $this->_exceptionList;
    }
}
