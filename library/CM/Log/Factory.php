<?php

class CM_Log_Factory implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param array        $handlersLayerConfigList
     * @param boolean|null $addStderrHandler
     * @return CM_Log_Logger
     * @throws CM_Exception_Invalid
     */
    public function createLogger(array $handlersLayerConfigList, $addStderrHandler = null) {
        $handlersLayerList = [];
        foreach ($handlersLayerConfigList as $handlersLayerConfig) {
            $currentLayer = [];
            foreach ($handlersLayerConfig as $handlerName) {
                $currentLayer[] = $this->getServiceManager()->get($handlerName);
            }
            $handlersLayerList[] = $currentLayer;
        }
        if (true === $addStderrHandler) {
            $handlersLayerList[sizeof($handlersLayerList) - 1][] = (new CM_Log_Handler_Factory())->createStderrHandler('{levelname}: {message}');
            //append stderr to the end of last layer
        }

        return $this->_createLogger($handlersLayerList);
    }

    /**
     * @param array $handlersLayerList
     * @return CM_Log_Logger
     */
    protected function _createLogger($handlersLayerList) {
        $computerInfo = new CM_Log_Context_ComputerInfo(CM_Util::getFqdn(), phpversion());
        $globalContext = new CM_Log_Context(null, $computerInfo);
        return new CM_Log_Logger($globalContext, $handlersLayerList);
    }
}
