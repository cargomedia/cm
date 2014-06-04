<?php

abstract class CM_Model_Location_Abstract extends CM_Model_Abstract {

    /**
     * @return int
     */
    abstract public function getLevel();

    /**
     * @param int|null $level
     * @return CM_Model_Location_Abstract|null
     */
    abstract public function getParent($level = null);

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
