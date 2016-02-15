<?php

abstract class CM_ExceptionHandling_Handler_Abstract implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var CM_Log_Factory */
    private $_loggerFactory;

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

    /**
     * @param CM_Log_Factory $loggerFactory
     */
    public function __construct(CM_Log_Factory $loggerFactory) {
        $this->_loggerFactory = $loggerFactory;
        $this->setServiceManager($loggerFactory->getServiceManager());
    }

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
            $this->handleException($exception);
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
     * @param int|null  $logLevel
     */
    public function logException(Exception $exception, $logLevel = null) {
        try {
            $this->getServiceManager()->getLogger()->addException($exception, null, $logLevel);
        } catch (CM_Log_HandlingException $loggerException) {
            $backupLogger = $this->_getBackupLogger();
            $backupLogger
                ->error('Origin exception:')->addException($exception, null, $logLevel)
                ->error('Handlers exception:')
                ->error($loggerException->getMessage());

            foreach ($loggerException->getExceptionList() as $handlerException) {
                $backupLogger->addException($handlerException);
            }
        } catch (Exception $loggerException) {
            $this->_getBackupLogger()
                ->error('Origin exception:')->addException($exception, null, $logLevel)
                ->error('Logger exception:')->addException($loggerException);
        }
    }

    /**
     * @param Exception $exception
     */
    public function handleException(Exception $exception) {
        $printException = true;
        if ($exception instanceof CM_Exception) {
            $printException = $exception->getSeverity() >= $this->_getPrintSeverityMin();
        }

        if ($printException) {
            $this->_printException($exception);
        }

        $this->logException($exception);
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
     * @return CM_Log_Logger
     */
    protected function _getBackupLogger() {
        return $this->_loggerFactory->createBackupLogger();
    }
}
