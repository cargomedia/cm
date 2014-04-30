<?php

class CM_Services extends CM_Class_Abstract {

    /** @var array */
    private $_serviceList = array();

    /** @var array */
    private $_serviceInstanceList = array();

    /** @var CM_Services */
    protected static $instance;

    private function __construct() {
        $this->_serviceList = self::_getConfig()->list;
    }

    /**
     * @param string $serviceName
     * @return bool
     */
    public function has($serviceName) {
        return array_key_exists($serviceName, $this->_serviceList);
    }

    /**
     * @param string $serviceName
     * @return mixed
     */
    public function get($serviceName) {
        if (!array_key_exists($serviceName, $this->_serviceInstanceList)) {
            $this->_serviceInstanceList[$serviceName] = $this->_instantiateService($serviceName);
        }
        return $this->_serviceInstanceList[$serviceName];
    }

    /**
     * @param string     $serviceName
     * @param string     $className
     * @param array|null $arguments
     */
    public function register($serviceName, $className, array $arguments = null) {
        $arguments = (array) $arguments;
        $this->_serviceList[$serviceName] = array(
            'class'     => $className,
            'arguments' => $arguments,
        );
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

            return $this->get($serviceName);
        }

        throw new CM_Exception_Nonexistent('Method doesn\'t exist.');
    }

    /**
     * @param string|null $serviceName
     * @return CM_Service_MongoDb
     */
    public function getMongoDb($serviceName = null) {
        if (null === $serviceName) {
            $serviceName = 'MongoDB';
        }
        return $this->get($serviceName);
    }

    /**
     * @param string $serviceName
     * @throws CM_Exception_Nonexistent
     * @return mixed
     */
    protected function _instantiateService($serviceName) {
        if (!$this->has($serviceName)) {
            throw new CM_Exception_Nonexistent("Service {$serviceName} is not registered.");
        }
        $config = $this->_serviceList[$serviceName];
        $arguments = array();
        if (array_key_exists('arguments', $config)) {
            $arguments = $config['arguments'];
        }
        $reflection = new ReflectionClass($config['class']);
        return $reflection->newInstanceArgs($arguments);
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
