<?php

class CM_Stream_Video_Factory {

    /**
     * @param string     $class
     * @param array|null $arguments
     * @return CM_Stream_Adapter_Video_Abstract
     * @throws CM_Exception_Invalid
     */
    public function createAdapter($class, $arguments = null) {
        $arguments = (array) $arguments;
        $reflectionClass = new ReflectionClass($class);
        if (!$reflectionClass->isSubclassOf('CM_Stream_Adapter_Video_Abstract')) {
            throw new CM_Exception_Invalid("Invalid stream video adapter `{$reflectionClass->getName()}`");
        }
        return $reflectionClass->newInstanceArgs($arguments);
    }

    /**
     * @param string     $adapterClass
     * @param array|null $adapterArguments
     * @throws CM_Exception_Invalid
     * @return CM_Stream_Video
     */
    public function createClient($adapterClass, $adapterArguments = null) {
        $adapter = $this->createAdapter($adapterClass, $adapterArguments);
        return new CM_Stream_Video($adapter);
    }
}
