<?php

class CM_Http_Handler implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param CM_Service_Manager $serviceManager
     */
    public function __construct(CM_Service_Manager $serviceManager) {
        $this->setServiceManager($serviceManager);
    }

    /**
     * @param CM_Http_Request_Abstract $request
     * @return CM_Response_Abstract
     */
    public function processRequest(CM_Http_Request_Abstract $request) {
        $response = CM_Response_Abstract::factory($request);
        $response->process();
        return $response;
    }
}
