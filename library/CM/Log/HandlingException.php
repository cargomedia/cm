<?php

class CM_Log_HandlingException extends CM_Exception {

    /** @var  Exception[] */
    private $_exceptionList;

    /**
     * @param string|null $message
     * @param Exception[] $exceptionList
     */
    public function __construct($message = null, array $exceptionList) {
        $message = (string) $message;
        $this->_exceptionList = $exceptionList;
        parent::__construct((string) $message, CM_Exception::ERROR);
    }

    /**
     * @return Exception[]
     */
    public function getExceptionList() {
        return $this->_exceptionList;
    }
}
