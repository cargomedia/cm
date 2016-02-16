<?php

class CM_Log_Factory implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param array $handlersLayerConfigList
     * @return CM_Log_Logger
     * @throws CM_Exception_Invalid
     */
    public function createLogger(array $handlersLayerConfigList) {
        $handlersLayerList = [];
        foreach ($handlersLayerConfigList as $handlersLayerConfig) {
            $currentLayer = [];
            foreach ($handlersLayerConfig as $handlerName) {
                $currentLayer[] = $this->getServiceManager()->get($handlerName);
            }
            $handlersLayerList[] = $currentLayer;
        }
        $handlersLayerList[sizeof($handlersLayerList) - 1][] = (new CM_Log_Handler_Factory())->createStderrHandler('{levelname}: {message}');
        //append stderr to the end of last layer

        return $this->_createLogger($handlersLayerList);
    }

    /**
     * @param array $handlersLayerList
     * @return CM_Log_Logger
     */
    protected function _createLogger($handlersLayerList) {
        $computerInfo = new CM_Log_Context_ComputerInfo(CM_Util::getFqdn(), phpversion());
        $request = $this->_getGlobalRequest();
        $globalContext = new CM_Log_Context(null, $request, $computerInfo);
        return new CM_Log_Logger($globalContext, $handlersLayerList);
    }

    /**
     * @return CM_Http_Request_Abstract|null
     */
    protected function _getGlobalRequest() {
        if (CM_Http_Request_Abstract::hasInstance()) {
            return CM_Http_Request_Abstract::getInstance();
        }
        return null;
    }
}
