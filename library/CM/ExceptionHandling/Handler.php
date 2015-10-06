<?php

class CM_ExceptionHandling_Handler implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var CM_Log_Logger */
    private $_loggerBasic;

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
     * @param CM_Log_Logger $loggerBasic
     */
    public function __construct(CM_Log_Logger $loggerBasic) {
        $this->_loggerBasic = $loggerBasic;
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
     */
    public function handleException(Exception $exception) {
        try {
            $this->getServiceManager()->getLogger()->addException($exception);
        } catch (CM_Log_HandlingException $loggerException) {
            $this->_loggerBasic->error('Origin exception:');
            $this->_loggerBasic->addException($exception);
            $this->_loggerBasic->error('Handlers exception:');
            $this->_loggerBasic->error($loggerException->getMessage());
            foreach ($loggerException->getExceptionList() as $handlerException) {
                $this->_loggerBasic->addException($handlerException);
            }
        } catch (Exception $loggerException) {
            $this->_loggerBasic->error('Origin exception:');
            $this->_loggerBasic->addException($exception);
            $this->_loggerBasic->error('Logger exception:');
            $this->_loggerBasic->addException($loggerException);
        }
    }
}
