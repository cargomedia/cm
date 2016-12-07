<?php

class CM_Service_ConfigDefinition extends CM_Service_AbstractDefinition {

    /** @var array */
    private $_config;

    /**
     * @param array $config
     */
    public function __construct(array $config) {
        $class = (string) $config['class'];
        $arguments = [];
        if (isset($config['arguments'])) {
            $arguments = (array) $config['arguments'];
        }
        $method = null;
        if (isset($config['method'])) {
            $methodName = (string) $config['method']['name'];
            $methodArguments = [];
            if (isset($config['method']['arguments'])) {
                $methodArguments = (array) $config['method']['arguments'];
            }
            $method = ['name' => $methodName, 'arguments' => $methodArguments];
        }

        $this->_config = [
            'class'     => $class,
            'arguments' => $arguments,
            'method'    => $method,
        ];
    }

    public function createInstance(CM_Service_Manager $serviceManager) {
        $config = $this->_config;
        $reflection = new ReflectionClass($config['class']);

        $arguments = $config['arguments'];
        if ($constructor = $reflection->getConstructor()) {
            $arguments = $this->_matchNamedArgs($constructor, $arguments);
        }
        $instance = $reflection->newInstanceArgs($arguments);

        if ($instance instanceof CM_Service_ManagerAwareInterface) {
            $instance->setServiceManager($serviceManager);
        }

        if (null !== $config['method']) {
            $method = $reflection->getMethod($config['method']['name']);
            $methodArguments = $this->_matchNamedArgs($method, $config['method']['arguments']);
            $instance = $method->invokeArgs($instance, $methodArguments);
        }

        return $instance;
    }

    /**
     * @param ReflectionMethod $method
     * @param array            $arguments
     * @throws CM_Exception_Invalid
     * @return array
     */
    protected function _matchNamedArgs(ReflectionMethod $method, array $arguments) {
        $namedArgs = new CM_Util_NamedArgs();
        try {
            return $namedArgs->matchNamedArgs($method, $arguments);
        } catch (CM_Exception_Invalid $e) {
            throw new CM_Exception_Invalid('Cannot match arguments for service', null, [
                'originalExceptionMessage' => $e->getMessage(),
            ]);
        }
    }
}
