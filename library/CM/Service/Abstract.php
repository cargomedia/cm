<?php

abstract class CM_Service_Abstract extends CM_Class_Abstract {

    /** @var CM_ServiceManager */
    private $_serviceManager;

    /**
     * @param CM_ServiceManager $serviceManager
     */
    public function setServiceManager(CM_ServiceManager $serviceManager) {
        $this->_serviceManager = $serviceManager;
    }

    /**
     * @return CM_ServiceManager
     * @throws CM_Exception_Invalid
     */
    public function getServiceManager() {
        if (null === $this->_serviceManager) {
            throw new CM_Exception_Invalid('Service manager not set');
        }
        return $this->_serviceManager;
    }
}
