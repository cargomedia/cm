<?php

class CM_Log_Record_Exception extends CM_Log_Record {

    /** @var Exception */
    private $_exception;

    /** @var CM_ExceptionHandling_SerializableException */
    private $_serializableException;

    /**
     * @param Exception      $exception
     * @param int|null       $logLevel
     * @param CM_Log_Context $context
     */
    public function __construct(Exception $exception, CM_Log_Context $context, $logLevel = null) {
        $this->_exception = $exception;
        $this->_exceptionSerializable = new CM_ExceptionHandling_SerializableException($exception);

        $message = $this->_exceptionSerializable->getClass() . ': ' . $this->_exceptionSerializable->getMessage();
        if (null === $logLevel) {
            $logLevel = $this->_exceptionSeverityToLevel($exception);
        }

        parent::__construct($logLevel, $message, $context);
    }

    /**
     * @return Exception
     */
    public function getException() {
        return $this->_exception;
    }

    /**
     * @return CM_ExceptionHandling_SerializableException
     */
    public function getSerializableException() {
        return $this->_serializableException;
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
        return isset($map[$severity]) ? $map[$severity] : CM_Log_Logger::CRITICAL;
    }
}
