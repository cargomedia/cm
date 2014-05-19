<?php

class CM_ServiceManager extends CM_Class_Abstract {

    /** @var array */
    private $_serviceList = array();

    /** @var array */
    private $_serviceInstanceList = array();

    /** @var CM_ServiceManager */
    protected static $instance;

    /**
     * @param string $serviceName
     * @return bool
     */
    public function has($serviceName) {
        return array_key_exists($serviceName, $this->_serviceList);
    }

    /**
     * @param string      $serviceName
     * @param string|null $assertInstanceOf
     * @throws CM_Exception_Invalid
     * @return mixed
     */
    public function get($serviceName, $assertInstanceOf = null) {
        if (!array_key_exists($serviceName, $this->_serviceInstanceList)) {
            $this->_serviceInstanceList[$serviceName] = $this->_instantiateService($serviceName);
        }
        $service = $this->_serviceInstanceList[$serviceName];
        if (null !== $assertInstanceOf && !is_a($service, $assertInstanceOf, true)) {
            throw new CM_Exception_Invalid('Service `' . $serviceName . '` is a `' . get_class($service) . '`, but not `' . $assertInstanceOf . '`.');
        }
        return $service;
    }

    /**
     * @param string      $serviceName
     * @param string      $className
     * @param array|null  $arguments
     * @param string|null $method
     * @param array|null  $methodArguments
     * @throws CM_Exception_Invalid
     */
    public function register($serviceName, $className, array $arguments = null, $method = null, array $methodArguments = null) {
        $this->registerWithArray($serviceName, array(
            'class'           => $className,
            'arguments'       => $arguments,
            'method'          => $method,
            'methodArguments' => $methodArguments,
        ));
    }

    /**
     * @param string $serviceName
     * @param array  $config
     * @throws CM_Exception_Invalid
     */
    public function registerWithArray($serviceName, array $config) {
        if ($this->has($serviceName)) {
            throw new CM_Exception_Invalid('Service `' . $serviceName . '` already registered.');
        }
        $class = (string) $config['class'];
        $arguments = isset($config['arguments']) ? (array) $config['arguments'] : array();
        $method = isset($config['method']) ? (string) $config['method'] : null;
        $methodArguments = isset($config['methodArguments']) ? (array) $config['methodArguments'] : array();

        $this->_serviceList[$serviceName] = array(
            'class'           => $class,
            'arguments'       => $arguments,
            'method'          => $method,
            'methodArguments' => $methodArguments,
        );
    }

    /**
     * Methods in format get[serviceName] returns a instance of a service with given name.
     *
     * @param string $name
     * @param mixed  $parameters
     * @return mixed
     * @throws CM_Exception_Invalid
     */
    public function __call($name, $parameters) {
        if (preg_match('/get(.+)/', $name, $matches)) {
            $serviceName = $matches[1];
            return $this->get($serviceName);
        }
        throw new CM_Exception_Invalid('Cannot extract service name from `' . $name . '`.');
    }

    /**
     * @param string|null $serviceName
     * @return CM_Service_MongoDb
     */
    public function getMongoDb($serviceName = null) {
        if (null === $serviceName) {
            $serviceName = 'MongoDb';
        }
        return $this->get($serviceName, 'CM_Service_MongoDb');
    }

    /**
     * @return CM_Service_Filesystems
     */
    public function getFilesystems() {
        return $this->get('filesystems', 'CM_Service_Filesystems');
    }

    /**
     * @param string $serviceName
     * @throws CM_Exception_Invalid
     * @return mixed
     */
    protected function _instantiateService($serviceName) {
        if (!$this->has($serviceName)) {
            throw new CM_Exception_Invalid("Service {$serviceName} is not registered.");
        }
        $config = $this->_serviceList[$serviceName];
        $reflection = new ReflectionClass($config['class']);
        $instance = $reflection->newInstanceArgs($config['arguments']);
        if (null !== $config['method']) {
            $instance = call_user_func_array(array($instance, $config['method']), $config['methodArguments']);
        }
        if ($instance instanceof CM_Service_Abstract) {
            $instance->setServiceManager($this);
        }
        return $instance;
    }

    /**
     * @return CM_ServiceManager
     */
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
