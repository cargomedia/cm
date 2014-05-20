<?php

interface CM_Service_ManagerAwareInterface {

    /**
     * @param CM_Service_Manager $serviceManager
     */
    public function setServiceManager(CM_Service_Manager $serviceManager);

    /**
     * @return CM_Service_Manager
     * @throws CM_Exception_Invalid
     */
    public function getServiceManager();
}
