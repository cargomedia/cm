<?php

class CM_Services extends CM_Class_Abstract {

    private $_servicesList = array();
    private $_serviceInstances = array();
    /** @var CM_Services $instance */
    protected static $instance;

    private function __construct() {
        $this->_servicesList = self::_getConfig()->list;
    }

    /**
     * @return CMService_MongoDb
     */
    public function getMongoDb() {
        return $this->getServiceInstance('MongoDb');
    }

    /**
     * @param $serviceName
     * @return string
     * @throws CM_Exception_Nonexistent
     */
    public function getServiceClass($serviceName) {
        if (!isset($this->_servicesList[$serviceName])) {
            throw new CM_Exception_Nonexistent("Service {$serviceName} is not registered.");
        }

        return $this->_servicesList[$serviceName];
    }

    /**
     * @param string $serviceName
     * @param string $className
     */
    public function registerService($serviceName, $className) {
        $this->_servicesList[$serviceName] = $className;
    }

    /**
     * @param string $serviceName
     * @return object
     */
    protected function _instantiateService($serviceName) {
        $serviceClass = $this->getServiceClass($serviceName);
        $instance = new $serviceClass;

        return $instance;
    }

    /**
     * @param string $serviceName
     * @return mixed
     */
    public function getServiceInstance($serviceName) {
        if (!isset($this->_serviceInstances[$serviceName])) {
            $instance = $this->_instantiateService($serviceName);
            $this->_serviceInstances[$serviceName] = $instance;
        }

        return $this->_serviceInstances[$serviceName];
    }

    /**
     * Methods in format get[serviceName] returns a instance of a service with given name.
     *
     * @param string $name
     * @param mixed  $parameters
     * @return mixed
     * @throws CM_Exception_Nonexistent
     */
    public function __call($name, $parameters) {
        if (preg_match('/get(.+)/', $name, $matches)) {
            $serviceName = $matches[1];

            return $this->getServiceInstance($serviceName);
        }

        throw new CM_Exception_Nonexistent('Method doesn\'t exist.');
    }

    /**
     * @return CM_Services
     */
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
