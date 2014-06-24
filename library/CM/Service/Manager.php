<?php

class CM_Service_Manager extends CM_Class_Abstract {

    /** @var array */
    private $_serviceConfigList = array();

    /** @var array */
    private $_serviceInstanceList = array();

    /** @var CM_Service_Manager */
    protected static $instance;

    /**
     * @param string $serviceName
     * @return bool
     */
    public function has($serviceName) {
        $hasConfig = array_key_exists($serviceName, $this->_serviceConfigList);
        $hasInstance = array_key_exists($serviceName, $this->_serviceInstanceList);
        return $hasConfig || $hasInstance;
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
     * @param string|null $methodName
     * @param array|null  $methodArguments
     * @throws CM_Exception_Invalid
     */
    public function register($serviceName, $className, array $arguments = null, $methodName = null, array $methodArguments = null) {
        $config = array(
            'class'     => $className,
            'arguments' => $arguments,
        );
        if (null !== $methodName) {
            $config['method'] = array(
                'name'      => $methodName,
                'arguments' => $methodArguments
            );
        }
        $this->registerWithArray($serviceName, $config);
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
        $arguments = array();
        if (isset($config['arguments'])) {
            $arguments = (array) $config['arguments'];
        }
        $method = null;
        if (isset($config['method'])) {
            $methodName = (string) $config['method']['name'];
            $methodArguments = array();
            if (isset($config['method']['arguments'])) {
                $methodArguments = (array) $config['method']['arguments'];
            }
            $method = array('name' => $methodName, 'arguments' => $methodArguments);
        }

        $this->_serviceConfigList[$serviceName] = array(
            'class'     => $class,
            'arguments' => $arguments,
            'method'    => $method,
        );
    }

    /**
     * @param string $serviceName
     * @param mixed  $instance
     * @throws CM_Exception_Invalid
     */
    public function registerInstance($serviceName, $instance) {
        if ($this->has($serviceName)) {
            throw new CM_Exception_Invalid('Service `' . $serviceName . '` already registered.');
        }
        $serviceName = (string) $serviceName;
        if ($instance instanceof CM_Service_ManagerAwareInterface) {
            $instance->setServiceManager($this);
        }
        $this->_serviceInstanceList[$serviceName] = $instance;
    }

    /**
     * @param string $serviceName
     */
    public function unregister($serviceName) {
        unset($this->_serviceConfigList[$serviceName]);
        unset($this->_serviceInstanceList[$serviceName]);
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
     * @return CM_Service_Databases
     */
    public function getDatabases() {
        return $this->get('databases', 'CM_Service_Databases');
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
     * @return CM_Service_Trackings
     */
    public function getTrackings() {
        return CM_Service_Manager::getInstance()->get('trackings');
    }

    /**
     * @return CM_Service_UserContent
     */
    public function getUserContent() {
        return $this->get('usercontent', 'CM_Service_UserContent');
    }

    /**
     * @param string $serviceName
     * @throws CM_Exception_Invalid
     * @return mixed
     */
    protected function _instantiateService($serviceName) {
        if (!array_key_exists($serviceName, $this->_serviceConfigList)) {
            throw new CM_Exception_Invalid("Service {$serviceName} has no config.");
        }
        $config = $this->_serviceConfigList[$serviceName];
        $reflection = new ReflectionClass($config['class']);
        $instance = $reflection->newInstanceArgs($config['arguments']);
        if (null !== $config['method']) {
            $instance = call_user_func_array(array($instance, $config['method']['name']), $config['method']['arguments']);
        }
        if ($instance instanceof CM_Service_ManagerAwareInterface) {
            $instance->setServiceManager($this);
        }
        return $instance;
    }

    /**
     * @return CM_Service_Manager
     */
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
