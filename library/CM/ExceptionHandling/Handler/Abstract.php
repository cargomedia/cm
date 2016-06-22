<?php

abstract class CM_ExceptionHandling_Handler_Abstract implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var  int|null */
    private $_printSeverityMin;

    private $_errorCodes = array(
        E_ERROR             => 'E_ERROR',
        E_WARNING           => 'E_WARNING',
        E_PARSE             => 'E_PARSE',
        E_NOTICE            => 'E_NOTICE',
        E_CORE_ERROR        => 'E_CORE_ERROR',
        E_CORE_WARNING      => 'E_CORE_WARNING',
        E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
        E_USER_ERROR        => 'E_USER_ERROR',
        E_USER_WARNING      => 'E_USER_WARNING',
        E_USER_NOTICE       => 'E_USER_NOTICE',
        E_STRICT            => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED        => 'E_DEPRECATED',
        E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
        E_ALL               => 'E_ALL',
    );

    public function handleErrorFatal() {
        if ($error = error_get_last()) {
            $code = isset($error['type']) ? $error['type'] : E_CORE_ERROR;
            if (0 === ($code & error_reporting())) {
                return;
            }
            $message = isset($error['message']) ? $error['message'] : '';
            $file = isset($error['file']) ? $error['file'] : 'unknown file';
            $line = isset($error['line']) ? $error['line'] : 0;
            if (isset($this->_errorCodes[$code])) {
                $message = $this->_errorCodes[$code] . ': ' . $message;
            }
            $exception = new ErrorException($message, 0, $code, $file, $line);
            $this->handleExceptionWithSeverity($exception, CM_Exception::FATAL);
        }
    }

    /**
     * @param int    $code
     * @param string $message
     * @param string $file
     * @param int    $line
     * @return bool
     * @throws ErrorException
     */
    public function handleErrorRaw($code, $message, $file, $line) {
        if (0 === ($code & error_reporting())) {
            return;
        }
        if (isset($this->_errorCodes[$code])) {
            $message = $this->_errorCodes[$code] . ': ' . $message;
        }
        throw new ErrorException($message, 0, $code, $file, $line);
    }

    /**
     * @param Exception $exception
     */
    public function handleException(Exception $exception) {
        $severity = $exception instanceof CM_Exception ? $exception->getSeverity() : null;
        $this->handleExceptionWithSeverity($exception, $severity);
    }

    /**
     * @param Exception $exception
     * @param           $severity
     */
    public function handleExceptionWithSeverity(Exception $exception, $severity) {
        $printException = true;
        if ($exception instanceof CM_Exception) {
            $printException = $severity >= $this->_getPrintSeverityMin();
        }
        if ($printException) {
            $this->_printException($exception);
        }
        $this->_logException($exception, $severity);
    }

    /**
     * @param int $severity
     */
    public function setPrintSeverityMin($severity) {
        $this->_printSeverityMin = (int) $severity;
    }

    /**
     * @param Exception $exception
     */
    abstract protected function _printException(Exception $exception);

    /**
     * @return int|null
     */
    private function _getPrintSeverityMin() {
        return $this->_printSeverityMin;
    }

    /**
     * @param Exception $exception
     * @param int|null  $severity
     * @throws CM_Exception_Invalid
     */
    protected function _logException(Exception $exception, $severity = null) {
        if (null === $severity) {
            $logLevel = CM_Log_Logger::exceptionToLevel($exception);
        } else {
            $logLevel = CM_Log_Logger::severityToLevel((int) $severity);
        }
        $context = new CM_Log_Context();
        $context->setException($exception);
        $this->getServiceManager()->getLogger()->addMessage('Application error', $logLevel, $context);
    }
}
