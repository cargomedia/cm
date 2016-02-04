<?php

class CM_Log_Factory implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var boolean|null */
    private $_isCli = null;

    /**
     * @param string[] $handlerList
     * @return CM_Log_Logger
     * @throws CM_Exception_Invalid
     */
    public function createLogger(array $handlerList) {
        $handlers = \Functional\map($handlerList, function ($handlerService) {
            return $this->getServiceManager()->get($handlerService);
        });
        return $this->_createLogger($handlers);
    }

    /**
     * @return CM_Log_Logger
     */
    public function createBackupLogger() {
        if (true === $this->_isCli) {
            $formatter = new CM_Log_Formatter_Text('{levelname}: {message}');
            $stream = new CM_OutputStream_Stream_StandardError();
        } else {
            $formatter = new CM_Log_Formatter_Html();
            $stream = new CM_OutputStream_Stream_Output();
        }
        $handlers = [];
        if ($this->getServiceManager()->has('logger-handler-file-error')) {
            $handlers[] = $this->getServiceManager()->get('logger-handler-file-error', 'CM_Log_Handler_Stream');
        }
        $handlers[] = new CM_Log_Handler_Stream($stream, $formatter, CM_Log_Logger::WARNING);

        return $this->_createLogger($handlers);
    }

    /**
     * @param boolean $isCli
     */
    public function setIsCli($isCli) {
        $this->_isCli = (bool) $isCli;
    }

    /**
     * @param CM_Log_Handler_HandlerInterface[] $handlers
     * @return CM_Log_Logger
     */
    protected function _createLogger($handlers) {
        $computerInfo = new CM_Log_Context_ComputerInfo(CM_Util::getFqdn(), phpversion());
        $globalContext = new CM_Log_Context(null, null, $computerInfo);
        return new CM_Log_Logger($globalContext, $handlers);
    }
}
