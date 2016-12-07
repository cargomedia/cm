<?php

class CM_Service_InstanceWrapperDefinition extends CM_Service_AbstractDefinition {

    /** @var mixed */
    private $_instance;

    /**
     * @param mixed $instance
     */
    public function __construct($instance) {
        $this->_instance = $instance;
    }

    public function createInstance(CM_Service_Manager $serviceManager) {
        return $this->_instance;
    }

}
