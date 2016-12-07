<?php

abstract class CM_Service_AbstractDefinition {

    use CM_EventHandler_EventHandlerTrait;

    /** @var mixed|null */
    private $_instance;

    /**
     * @param CM_Service_Manager $serviceManager
     * @return mixed
     */
    abstract public function createInstance(CM_Service_Manager $serviceManager);

    /**
     * @param callable $callback
     */
    public function onCreate(callable $callback) {
        $this->bind('create', $callback);
        if ($this->hasInstance()) {
            $callback($this->_instance);
        }
    }

    /**
     * @param CM_Service_Manager $serviceManager
     * @return mixed
     */
    public function get(CM_Service_Manager $serviceManager) {
        if (null === $this->_instance) {
            $this->_instance = $this->createInstance($serviceManager);
            if ($this->_instance instanceof CM_Service_ManagerAwareInterface) {
                $this->_instance->setServiceManager($serviceManager);
            }
            $this->trigger('create', $this->_instance);
        }
        return $this->_instance;
    }

    /**
     * @return bool
     */
    public function hasInstance() {
        return null !== $this->_instance;
    }

    public function resetInstance() {
        $this->_instance = null;
    }

    /**
     * @param CM_Service_Manager $serviceManager
     */
    public function register(CM_Service_Manager $serviceManager) {
    }
}
