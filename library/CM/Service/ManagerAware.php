<?php

abstract class CM_Service_ManagerAware extends CM_Class_Abstract implements CM_Service_ManagerAwareInterface {

    /** @var CM_Service_Manager */
    private $_serviceManager;

    public function setServiceManager(CM_Service_Manager $serviceManager) {
        $this->_serviceManager = $serviceManager;
    }

    public function getServiceManager() {
        if (null === $this->_serviceManager) {
            throw new CM_Exception_Invalid('Service manager not set');
        }
        return $this->_serviceManager;
    }
}
