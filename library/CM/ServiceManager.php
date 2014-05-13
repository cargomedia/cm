<?php

class CM_ServiceManager extends CM_Class_Abstract {

    /** @var array */
    private $_serviceList = array();

    /** @var array */
    private $_serviceInstanceList = array();

    /** @var CM_ServiceManager */
    protected static $instance;

    /**
     * @param string $serviceName,...
     * @return bool
     */
    public function has($serviceName) {
        $serviceNameList = func_get_args();
        foreach ($serviceNameList as $serviceName) {
            $serviceName = (string) $serviceName;
            if (array_key_exists($serviceName, $this->_serviceList)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $serviceName,...
     * @throws CM_Exception_Nonexistent
     * @return mixed
     */
    public function get($serviceName) {
        $serviceNameList = func_get_args();
        foreach ($serviceNameList as $serviceName) {
            $serviceName = (string) $serviceName;
            if ($this->has($serviceName)) {
                if (!array_key_exists($serviceName, $this->_serviceInstanceList)) {
                    $this->_serviceInstanceList[$serviceName] = $this->_instantiateService($serviceName);
                }
                return $this->_serviceInstanceList[$serviceName];
            }
        }
        throw new CM_Exception_Nonexistent('Service `' . implode('`, `', $serviceNameList) . '` is not registered.');
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
     * @param string $serviceName,...
     * @return CM_Service_MongoDb
     */
    public function getMongoDb($serviceName = null) {
        $serviceNameList = func_get_args();
        $serviceNameList[] = 'MongoDb';
        return call_user_func_array(array($this, 'get'), $serviceNameList);
    }

    /**
     * @param string $serviceName,...
     * @return CM_Db_Client
     */
    public function getDb($serviceName = null) {
        $serviceNameList = func_get_args();
        $serviceNameList[] = 'Db';
        return call_user_func_array(array($this, 'get'), $serviceNameList);
    }

    /**
     * @param string $serviceName
     * @return CM_Service_Filesystem
     */
    public function getFilesystem($serviceName) {
        return $this->get($serviceName);
    }

    /**
     * @param string $serviceName
     * @return mixed
     */
    protected function _instantiateService($serviceName) {
        $config = $this->_serviceList[$serviceName];
        $arguments = isset($config['arguments']) ? $config['arguments'] : array();
        $reflection = new ReflectionClass($config['class']);
        return $reflection->newInstanceArgs($arguments);
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
