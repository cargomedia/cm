<?php

abstract class CM_Service_AbstractDefinition {

    /** @var string[] */
    private $_subscriptions;

    /**
     * @param string $serviceName
     */
    public function subscribeTo($serviceName) {
        $this->_subscriptions[] = $serviceName;
    }

    /**
     * @param CM_Service_Manager $serviceManager
     * @return mixed
     */
    abstract public function createInstance(CM_Service_Manager $serviceManager);
    
}
