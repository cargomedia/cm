<?php

class CM_Log_Factory implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param array $handlerConfigList
     * @return CM_Log_Logger
     * @throws CM_Exception_Invalid
     */
    public function createLogger(array $handlerConfigList) {
        $handlerStructList = \Functional\map($handlerConfigList, function (array $handlerConfig) {
            return [
                'handler'   => $this->getServiceManager()->get($handlerConfig['name']),
                'propagate' => (bool) $handlerConfig['propagate'],
            ];
        });
        return $this->_createLogger($handlerStructList);
    }

    /**
     * @return CM_Log_Logger
     */
    public function createBackupLogger() {
        $handlerStructList = [];
        if ($this->getServiceManager()->has('logger-handler-file-error')) {
            $handlerStructList[] = [
                'handler'   => $this->getServiceManager()->get('logger-handler-file-error', 'CM_Log_Handler_Stream'),
                'propagate' => true,
            ];
        }
        $handlerStructList[] = [
            'handler' => (new CM_Log_Handler_Factory())->createStderrHandler('{levelname}: {message}'),
            'propagate' => false,
        ];
        return $this->_createLogger($handlerStructList);
    }

    /**
     * @param array $handlerStructList
     * @return CM_Log_Logger
     */
    protected function _createLogger($handlerStructList) {
        $computerInfo = new CM_Log_Context_ComputerInfo(CM_Util::getFqdn(), phpversion());
        $globalContext = new CM_Log_Context(null, null, $computerInfo);
        return new CM_Log_Logger($globalContext, $handlerStructList);
    }
}
