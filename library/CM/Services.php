<?php

class CM_Services extends CM_Class_Abstract {

    private $_servicesList = array();
    private $_serviceInstances = array();

    private function __construct() {
        $this->_servicesList = self::_getConfig()->list;
    }

    /**
     * @return CM_Service_MongoDB
     */
    public function getMongoDB() {
        return $this->getServiceInstance('MongoDB');
    }

    /**
     * @param string $serviceName
     * @return array
     * @throws Exception
     */
    public function getServiceEntry($serviceName) {
        if (!isset($this->_servicesList[$serviceName])) {
            throw new Exception("Service {$serviceName} is not registered.");
        }

        return $this->_servicesList[$serviceName];
    }

    /**
     * @param string $serviceName
     * @param array  $config
     * @throws Exception
     */
    public function registerService($serviceName, $config) {
        if (empty($config['class'])) {
            throw new Exception("Class name missing for service {$serviceName}.");
        }
        $this->_servicesList[$serviceName] = $config;
    }

    /**
     * @param string $serviceName
     * @return object
     */
    protected function _instantiateService($serviceName) {
        $serviceConfig = $this->getServiceEntry($serviceName);

        $reflector = new ReflectionClass($serviceConfig['class']);
        $args = !empty($serviceConfig['args']) ? $serviceConfig['args'] : array();
        return $reflector->newInstanceArgs($args);
    }

    /**
     * @param string      $serviceName
     * @param string|null $expectClass
     * @return mixed
     * @throws Exception
     */
    public function getServiceInstance($serviceName, $expectClass = null) {
        if (!isset($this->_serviceInstances[$serviceName])) {
            $instance = $this->_instantiateService($serviceName);

            if ($expectClass !== null && get_class($instance)) {
                throw new Exception("Expected to get an instance of {$expectClass}, got " . get_class($instance));
            }
            $this->_serviceInstances[$serviceName] = $instance;
        }

        return $this->_serviceInstances[$serviceName];
    }

    /**
     * returns a instance of CM_Service_Abstract
     *
     * @param string $name
     * @param array  $arguments
     * @return mixed
     * @throws Exception
     */
    public function __call($name, array $arguments) {
        if (preg_match('/get(.+)/', $name, $matches)) {
            $serviceName = $matches[1];
            if (!empty($arguments[0])) {
                $configName = $arguments[0];
            } else {
                $configName = null;
            }
            return $this->getServiceInstance($serviceName, $configName);
        }

        throw new Exception('Method doesn\'t exist.');
    }

    /**
     * @return CM_Services
     */
    public static function getInstance() {
        static $instance;
        if (!$instance) {
            $instance = new self();
        }
        return $instance;
    }
}
