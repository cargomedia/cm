<?php

abstract class CM_Model_Location_Abstract extends CM_Model_Abstract {

    /**
     * @param int $level
     * @return CM_Model_Location_Abstract|null
     */
    abstract public function get($level);

    /**
     * @return int
     */
    abstract public function getLevel();

    /**
     * @return string
     */
    public function getName() {
        return $this->_get('name');
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->_set('name', $name);
    }

    public static function getCacheClass() {
        return 'CM_Model_StorageAdapter_CacheLocal';
    }

    public static function getPersistenceClass() {
        return 'CM_Model_StorageAdapter_Database';
    }
}
