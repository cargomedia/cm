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
     * @return array|null
     */
    public function getCoordinates() {
        $lat = $this->getLat();
        $lon = $this->getLon();
        if (null !== $lat && null !== $lon) {
            return ['lat' => $lat, 'lon' => $lon];
        }
        return null;
    }

    /**
     * @return float|null
     */
    public function getLat() {
        return $this->_get('lat');
    }

    /**
     * @param float|null $lat
     */
    public function setLat($lat) {
        $this->_set('lat', $lat);
    }

    /**
     * @return float|null
     */
    public function getLon() {
        return $this->_get('lon');
    }

    /**
     * @param float|null $lon
     */
    public function setLon($lon) {
        $this->_set('lon', $lon);
    }

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
