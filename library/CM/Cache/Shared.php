<?php

class CM_Cache_Shared extends CM_Cache_Abstract {

    /**
     * @param string $storageClassName
     * @param int    $defaultLifetime
     * @throws CM_Exception
     */
    public function __construct($storageClassName, $defaultLifetime) {
        $storageClassName = (string) $storageClassName;
        if (!is_subclass_of($storageClassName, 'CM_Cache_Storage_Abstract')) {
            throw new CM_Exception('Invalid cache storage: `' . $storageClassName . '`');
        }
        $this->_storage = new $storageClassName();
        $this->_defaultLifetime = (int) $defaultLifetime;
    }

    /**
     * @return CM_Cache_Shared
     */
    public static function getInstance() {
        return CM_Service_Manager::getInstance()->getCache()->getShared();
    }
}
