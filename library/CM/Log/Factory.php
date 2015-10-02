<?php

class CM_Log_Factory {

    /**
     * @param array $handlerList
     * @return CM_Log_Logger
     * @throws CM_Exception_Invalid
     */
    public function createLogger(array $handlerList) {
        $serviceManager = CM_Service_Manager::getInstance();

        $computerInfo = new CM_Log_Context_ComputerInfo(CM_Util::getFqdn(), phpversion());
        $globalContext = new CM_Log_Context(null, null, $computerInfo);

        $handlers = [];
        foreach ($handlerList as $handlerService) {
            $handlers[] = $serviceManager->get($handlerService);
        }
        return new CM_Log_Logger($globalContext, $handlers);
    }
}
