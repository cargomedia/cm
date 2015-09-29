<?php

class CM_Log_Record_Exception extends CM_Log_Record {

    /** @var CM_ExceptionHandling_SerializableException */
    private $_exception;

    /**
     * @param Exception      $exception
     * @param CM_Log_Context $context
     */
    public function __construct(Exception $exception, CM_Log_Context $context) {
        $this->_exception = new CM_ExceptionHandling_SerializableException($exception);
        parent::__construct($this->_exceptionSeverityToLevel($exception), $exception->getMessage(), $context);
    }

    /**
     * @return CM_ExceptionHandling_SerializableException
     */
    public function getException(){
        return $this->_exception;
    }

    /**
     * @param Exception $exception
     * @return int
     */
    protected function _exceptionSeverityToLevel(Exception $exception) {
        $severity = $exception instanceof CM_Exception ? $exception->getSeverity() : null;
        $map = [
            CM_Exception::WARN  => CM_Log_Logger::WARNING,
            CM_Exception::ERROR => CM_Log_Logger::ERROR,
            CM_Exception::FATAL => CM_Log_Logger::CRITICAL,
        ];
        return isset($map[$severity]) ? $map[$severity] : CM_Log_Logger::NOTSET;
    }
}
