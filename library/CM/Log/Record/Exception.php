<?php

class CM_Log_Record_Exception extends CM_Log_Record {

    /** @var Exception */
    private $_exception;

    /** @var CM_ExceptionHandling_SerializableException */
    private $_serializableException;

    /**
     * @param int            $logLevel
     * @param CM_Log_Context $context
     * @param Exception      $exception
     * @throws CM_Exception_Invalid
     */
    public function __construct($logLevel, CM_Log_Context $context, Exception $exception) {
        $this->_exception = $exception;
        $this->_serializableException = new CM_ExceptionHandling_SerializableException($exception);
        $message = $this->_serializableException->getClass() . ': ' . $this->_serializableException->getMessage();

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
    public static function exceptionSeverityToLevel(Exception $exception) {
        $severity = $exception instanceof CM_Exception ? $exception->getSeverity() : null;
        $map = [
            CM_Exception::WARN  => CM_Log_Logger::WARNING,
            CM_Exception::ERROR => CM_Log_Logger::ERROR,
            CM_Exception::FATAL => CM_Log_Logger::CRITICAL,
        ];
        return isset($map[$severity]) ? $map[$severity] : CM_Log_Logger::ERROR;
    }
}
