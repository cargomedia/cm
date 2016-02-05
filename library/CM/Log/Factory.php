<?php

class CM_Log_Factory implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

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
        $handlers = [];
        if ($this->getServiceManager()->has('logger-handler-file-error')) {
            $handlers[] = $this->getServiceManager()->get('logger-handler-file-error', 'CM_Log_Handler_Stream');
        }
        $handlers[] = (new CM_Log_Handler_Factory())->createStderrHandler('{levelname}: {message}');

        return $this->_createLogger($handlers);
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
