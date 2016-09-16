<?php

class CM_MessageStream_Factory implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param string     $adapterClass
     * @param array|null $adapterArguments
     * @return CM_MessageStream_Service
     * @throws CM_Exception_Invalid
     */
    public function createService($adapterClass, array $adapterArguments = null) {
        $adapter = $this->createAdapter($adapterClass, $adapterArguments);
        return new CM_MessageStream_Service($adapter);
    }

    /**
     * @param string     $adapterClass
     * @param array|null $adapterArguments
     * @return CM_MessageStream_Adapter_Abstract
     * @throws CM_Exception_Invalid
     */
    public function createAdapter($adapterClass, array $adapterArguments = null) {
        $adapterArguments = (array) $adapterArguments;
        $reflectionClass = new ReflectionClass($adapterClass);
        if (!$reflectionClass->isSubclassOf('CM_MessageStream_Adapter_Abstract')) {
            throw new CM_Exception_Invalid('Adapter class is not valid stream message adapter', null, ['adapterClass' => $adapterClass]);
        }
        /** @var CM_MessageStream_Adapter_Abstract $adapter */
        $adapter = $reflectionClass->newInstanceArgs($adapterArguments);
        if ($adapter instanceof CM_Service_ManagerAwareInterface) {
            $adapter->setServiceManager($this->getServiceManager());
        }
        return $adapter;
    }
}
