<?php

class CM_Log_Factory implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param string $handlerName
     * @return CM_Log_Logger
     * @throws CM_Exception_Invalid
     */
    public function createLogger($handlerName) {
        $handler = $this->getServiceManager()->get((string) $handlerName, 'CM_Log_Handler_HandlerInterface');
        return $this->_createLogger($handler);
    }

    /**
     * @param string[] $handlersLayerConfigList
     * @return CM_Log_Logger
     */
    public function createLayeredLogger(array $handlersLayerConfigList) {
        $handlerFactory = new CM_Log_Handler_Factory();
        $handlerFactory->setServiceManager($this->getServiceManager());
        $handler = $handlerFactory->createLayeredHandler($handlersLayerConfigList);
        return $this->_createLogger($handler);
    }

    /**
     * @param CM_Log_Handler_HandlerInterface $handler
     * @return CM_Log_Logger
     */
    protected function _createLogger(CM_Log_Handler_HandlerInterface $handler) {
        $computerInfo = new CM_Log_Context_ComputerInfo(CM_Util::getFqdn(), phpversion());
        $globalContext = new CM_Log_Context(null, $computerInfo);
        return new CM_Log_Logger($globalContext, $handler);
    }
}
