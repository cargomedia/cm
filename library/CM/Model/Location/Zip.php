<?php

class CM_Model_Location_Zip extends CM_Model_Location_Abstract {

    /**
     * @return CM_Model_Location_City
     */
    public function getCity() {
        return new CM_Model_Location_City($this->_get('cityId'));
    }

    /**
     * @param CM_Model_Location_City $city
     */
    public function setCity($city) {
        $this->_set('cityId', $city->getId());
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
     * @return array|null
     */
    public function getCoordinates() {
        $lat = $this->getLat();
        $lon = $this->getLon();
        if (null !== $lat && null !== $lon) {
            return array('lat' => $lat, 'lon' => $lon);
        }
        return $this->getCity()->getCoordinates();
    }

    public function getLevel() {
        return CM_Model_Location::LEVEL_ZIP;
    }

    public function getParent($level = null) {
        if (null === $level) {
            $level = CM_Model_Location::LEVEL_CITY;
        }
        $level = (int) $level;
        switch ($level) {
            case CM_Model_Location::LEVEL_COUNTRY:
                return $this->getCity()->getCountry();
            case CM_Model_Location::LEVEL_STATE:
                return $this->getCity()->getState();
            case CM_Model_Location::LEVEL_CITY:
                return $this->getCity();
            case CM_Model_Location::LEVEL_ZIP:
                return $this;
        }
        throw new CM_Exception_Invalid('Invalid location level `' . $level . '`');
    }

    public function _getSchema() {
        return new CM_Model_Schema_Definition(array(
            'cityId' => array('type' => 'int'),
            'name'   => array('type' => 'string'),
            'lat'    => array('type' => 'float', 'optional' => true),
            'lon'    => array('type' => 'float', 'optional' => true),
        ));
    }

    /**
     * @param CM_Model_Location_City $city
     * @param string                 $name
     * @param float|null             $lat
     * @param float|null             $lon
     * @return CM_Model_Location_Zip
     */
    public static function create(CM_Model_Location_City $city, $name, $lat = null, $lon = null) {
        $zip = new self();
        $zip->_set(array(
            'cityId' => $city->getId(),
            'name'   => $name,
            'lat'    => $lat,
            'lon'    => $lon,
        ));
        $zip->commit();
        return $zip;
    }
}
