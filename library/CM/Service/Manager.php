<?php

class CM_Service_Manager extends CM_Class_Abstract {

    /** @var array */
    private $_serviceConfigList;
    
    /** @var CM_Service_AbstractDefinition[] */
    private $_definitions;

    /** @var array */
    private $_serviceInstanceList;
    
    /** @var array */
    private $_loadingServices;
    
    /** @var array */
    private $_subscriptions;

    /** @var CM_Service_Manager */
    protected static $instance;

    public function __construct() {
        $this->_definitions = [];
        $this->_subscriptions = [];
        $this->_serviceConfigList = [];
        $this->_serviceInstanceList = [];
        $this->_loadingServices = [];
    }

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
        if (!$this->_hasInstance($serviceName)) {
            $this->_loadingServices[] = $serviceName;
            $this->_serviceInstanceList[$serviceName] = $this->_instantiateService($serviceName);
            $this->_loadingServices = array_diff($this->_loadingServices, [$serviceName]);
        }
        $service = $this->_serviceInstanceList[$serviceName];
        if (null !== $assertInstanceOf && !is_a($service, $assertInstanceOf, true)) {
            throw new CM_Exception_Invalid('Service has an invalid class.', null, [
                'service'           => $serviceName,
                'actualClassName'   => get_class($service),
                'expectedClassName' => $assertInstanceOf,
            ]);
        }
        if ($subscriptions = $this->_findSubscriptions($serviceName)) {
            foreach ($subscriptions as $subscribedService) {
                if (!in_array($subscribedService, $this->_loadingServices)) {
                    $this->get($subscribedService);
                }
            }
        }
        return $service;
    }

    /**
     * @param string      $serviceName
     * @param string      $className
     * @param array|null  $arguments
     * @param string|null $methodName
     * @param array|null  $methodArguments
     * @param null        $subscribe
     */
    public function register($serviceName, $className, array $arguments = null, $methodName = null, array $methodArguments = null, $subscribe = null) {
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
        if (null !== $subscribe) {
            $config['subscribe'] = $subscribe;
        }
        $this->_registerDefinition($serviceName, new CM_Service_ConfigDefinition($config));
    }

    /**
     * @param string                              $serviceName
     * @param CM_Service_AbstractDefinition|array $definition
     * @throws CM_Exception_Invalid
     */
    public function registerDefinition($serviceName, $definition) {
        if (is_array($definition)) {
            $definition = new CM_Service_ConfigDefinition($definition);
        }
        
        if ($definition instanceof CM_Service_AbstractDefinition) {
            $this->_registerDefinition($serviceName, $definition);
            return;
        }
        
        var_dump($definition);
        die();
        
        throw new CM_Exception_Invalid('Invalid definition');
    }
    
    protected function _registerDefinition($serviceName, CM_Service_AbstractDefinition $definition) {
        $this->_definitions[$serviceName] = $definition;
    }

    /**
     * @param string $serviceName
     * @param mixed  $instance
     * @throws CM_Exception_Invalid
     */
    public function registerInstance($serviceName, $instance) {
        if ($this->has($serviceName)) {
            throw new CM_Exception_Invalid('Service is already registered.', null, ['service' => $serviceName]);
        }
        $serviceName = (string) $serviceName;
        if ($instance instanceof CM_Service_ManagerAwareInterface) {
            $instance->setServiceManager($this);
        }
        $this->_serviceInstanceList[$serviceName] = $instance;
    }

    public function resetServiceInstances() {
        $this->_serviceInstanceList = [];
    }

    /**
     * @param string $serviceName
     * @param mixed  $instance
     */
    public function replaceInstance($serviceName, $instance) {
        if ($this->has($serviceName)) {
            $this->unregister($serviceName);
        }
        $this->registerInstance($serviceName, $instance);
    }

    /**
     * @param string $serviceName
     * @return $this
     */
    public function unregister($serviceName) {
        unset($this->_serviceConfigList[$serviceName]);
        unset($this->_serviceInstanceList[$serviceName]);
        return $this;
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
        throw new CM_Exception_Invalid('Cannot extract service name.', null, ['name' => $name]);
    }

    /**
     * @return CM_Service_Databases
     */
    public function getDatabases() {
        return $this->get('databases', 'CM_Service_Databases');
    }

    /**
     * @return CM_Jobdistribution_DelayedQueue
     */
    public function getDelayedJobQueue() {
        return $this->get('delayedJobQueue', 'CM_Jobdistribution_DelayedQueue');
    }

    /**
     * @param string|null $serviceName
     * @return CM_MongoDb_Client
     */
    public function getMongoDb($serviceName = null) {
        if (null === $serviceName) {
            $serviceName = 'MongoDb';
        }
        return $this->get($serviceName, 'CM_MongoDb_Client');
    }

    /**
     * @return CM_Options
     * @throws CM_Exception_Invalid
     */
    public function getOptions() {
        return $this->get('options', 'CM_Options');
    }

    /**
     * @return CM_Service_Filesystems
     */
    public function getFilesystems() {
        return $this->get('filesystems', 'CM_Service_Filesystems');
    }

    /**
     * @return CM_Debug
     */
    public function getDebug() {
        return $this->get('debug', 'CM_Debug');
    }

    /**
     * @return CM_Service_Trackings
     */
    public function getTrackings() {
        return $this->get('trackings', 'CM_Service_Trackings');
    }

    /**
     * @return CM_Service_UserContent
     */
    public function getUserContent() {
        return $this->get('usercontent', 'CM_Service_UserContent');
    }

    /**
     * @param string $serviceName
     * @return CM_Janus_Service
     * @throws CM_Exception_Invalid
     */
    public function getJanus($serviceName) {
        return $this->get($serviceName, 'CM_Janus_Service');
    }

    /**
     * @return CM_Memcache_Client
     */
    public function getMemcache() {
        return $this->get('memcache', 'CM_Memcache_Client');
    }

    /**
     * @return CM_MessageStream_Service
     */
    public function getStreamMessage() {
        return $this->get('stream-message', 'CM_MessageStream_Service');
    }

    /**
     * @return CM_Redis_Client
     */
    public function getRedis() {
        return $this->get('redis', 'CM_Redis_Client');
    }

    /**
     * @return CM_Elasticsearch_Cluster
     */
    public function getElasticsearch() {
        return $this->get('elasticsearch', 'CM_Elasticsearch_Cluster');
    }

    /**
     * @return CMService_Newrelic
     */
    public function getNewrelic() {
        return $this->get('newrelic', 'CMService_Newrelic');
    }

    /**
     * @return CM_Log_Logger
     */
    public function getLogger() {
        return $this->get('logger', 'CM_Log_Logger');
    }

    /**
     * @return CM_Mail_Mailer
     */
    public function getMailer() {
        return $this->get('mailer', 'CM_Mail_Mailer');
    }

    /**
     * @param string $serviceName
     * @return bool
     */
    protected function _hasInstance($serviceName) {
        return array_key_exists($serviceName, $this->_serviceInstanceList);
    }

    /**
     * @param string $serviceName
     * @throws CM_Exception_Invalid
     * @return mixed
     */
    protected function _instantiateService($serviceName) {
        if (!array_key_exists($serviceName, $this->_definitions)) {
            throw new CM_Exception_Invalid('Service has no definition.' . $serviceName, null, ['serviceName' => $serviceName]);
        }
        $definition = $this->_definitions[$serviceName];
        return $definition->createInstance($this);
    }

    /**
     * @param string $serviceName
     * @param string $target
     */
    protected function _subscribe($serviceName, $target) {
        if (!array_key_exists($target, $this->_subscriptions)) {
            $this->_subscriptions[$target] = [];
        }
        $this->_subscriptions[$target][] = $serviceName;

        if ($this->_hasInstance($target)) {
            $this->get($serviceName);
        }
    }

    /**
     * @param string $serviceName
     * @return array
     */
    protected function _findSubscriptions($serviceName) {
        if (!array_key_exists($serviceName, $this->_subscriptions)) {
            return [];
        }
        return $this->_subscriptions[$serviceName];
    }

    /**
     * @param string           $serviceName
     * @param ReflectionMethod $method
     * @param array            $arguments
     * @throws CM_Exception_Invalid
     * @return array
     */
    protected function _matchNamedArgs($serviceName, ReflectionMethod $method, array $arguments) {
        $namedArgs = new CM_Util_NamedArgs();
        try {
            return $namedArgs->matchNamedArgs($method, $arguments);
        } catch (CM_Exception_Invalid $e) {
            throw new CM_Exception_Invalid('Cannot match arguments for service', null, [
                'serviceName'              => $serviceName,
                'originalExceptionMessage' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @deprecated Instead make your class manager-aware (`CM_Service_ManagerAwareInterface`) and pass the manager.
     *
     * @return CM_Service_Manager
     */
    public static function getInstance() {
        if (null === self::$instance) {
            self::setInstance(new self());
        }
        return self::$instance;
    }

    /**
     * @param CM_Service_Manager $serviceManager
     */
    public static function setInstance(CM_Service_Manager $serviceManager) {
        self::$instance = $serviceManager;
    }

    function __clone() {
        foreach ($this->_serviceInstanceList as &$instance) {
            $instance = clone $instance;
        }
    }

}
