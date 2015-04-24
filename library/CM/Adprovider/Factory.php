<?php

class CM_Adprovider_Factory {

    /**
     * @param string $class
     * @param array  $arguments
     * @return CM_Adprovider_Adapter_Abstract
     * @throws CM_Exception_Invalid
     */
    public function createAdapter($class, $arguments) {
        $reflectionClass = new ReflectionClass($class);
        if (!$reflectionClass->isSubclassOf('CM_Adprovider_Adapter_Abstract')) {
            throw new CM_Exception_Invalid("Invalid ad provider adapter: `{$reflectionClass->getName()}`");
        }
        return $reflectionClass->newInstanceArgs($arguments);
    }

    /**
     * @param bool  $enabled
     * @param array $zones
     * @param array $adapters
     * @return CM_Adprovider_Client
     */
    public function createClient($enabled, $zones, $adapters) {
        $client = new CM_Adprovider_Client($enabled, $zones);
        foreach ($adapters as $adapterClassName => $adapterArguments) {
            $adapter = $this->createAdapter($adapterClassName, $adapterArguments);
            $client->addAdapter($adapter);
        }
        return $client;
    }
}
