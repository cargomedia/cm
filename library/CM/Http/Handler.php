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
     * @throws CM_Exception
     * @return CM_Http_Response_Abstract
     */
    public function processRequest(CM_Http_Request_Abstract $request) {
        try {
            $this->getServiceManager()->getLogger()->getContext()->setHttpRequest($request);
            $response = CM_Http_Response_Abstract::factory($request, $this->getServiceManager());
        } catch (CM_Exception $e) {
            $e->setSeverity(CM_Exception::WARN);
            throw $e;
        }
        $response->process();
        return $response;
    }
}
