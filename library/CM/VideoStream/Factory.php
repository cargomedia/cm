<?php

class CM_MediaStream_Factory {

    /**
     * @param string     $class
     * @param array|null $arguments
     * @return CM_VideoStream_Adapter_Abstract
     * @throws CM_Exception_Invalid
     */
    public function createAdapter($class, $arguments = null) {
        $arguments = (array) $arguments;
        $reflectionClass = new ReflectionClass($class);
        if (!$reflectionClass->isSubclassOf('CM_VideoStream_Adapter_Abstract')) {
            throw new CM_Exception_Invalid("Invalid stream video adapter `{$reflectionClass->getName()}`");
        }
        return $reflectionClass->newInstanceArgs($arguments);
    }

    /**
     * @param string     $adapterClass
     * @param array|null $adapterArguments
     * @throws CM_Exception_Invalid
     * @return CM_VideoStream_Service
     */
    public function createService($adapterClass, $adapterArguments = null) {
        $adapter = $this->createAdapter($adapterClass, $adapterArguments);
        return new CM_VideoStream_Service($adapter);
    }
}
