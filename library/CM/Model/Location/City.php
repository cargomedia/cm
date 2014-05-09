<?php

class CM_Model_Location_City extends CM_Model_Location_Abstract {

    /**
     * @return CM_Model_Location_Country
     */
    public function getCountry() {
        return $this->_get('countryId');
    }

    /**
     * @param CM_Model_Location_Country $country
     */
    public function setCountry($country) {
        $this->_set('countryId', $country);
    }

    /**
     * @return CM_Model_Location_State|null
     */
    public function getState() {
        return $this->_get('stateId');
    }

    /**
     * @param CM_Model_Location_State|null $state
     */
    public function setState($state) {
        $this->_set('stateId', $state);
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
     * @return string|null
     */
    public function getMaxMind() {
        return $this->_get('_maxmind');
    }

    /**
     * @param string|null $maxMind
     */
    public function setMaxmind($maxMind) {
        $this->_set('_maxmind', $maxMind);
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
        return null;
    }

    public function getLevel() {
        return CM_Model_Location::LEVEL_CITY;
    }

    public function getParent($level = null) {
        if (null === $level) {
            if ($state = $this->getState()) {
                return $state;
            }
            $level = CM_Model_Location::LEVEL_COUNTRY;
        }
        $level = (int) $level;
        switch ($level) {
            case CM_Model_Location::LEVEL_COUNTRY:
                return $this->getCountry();
            case CM_Model_Location::LEVEL_STATE:
                return $this->getState();
            case CM_Model_Location::LEVEL_CITY:
                return $this;
            case CM_Model_Location::LEVEL_ZIP:
                throw new CM_Exception_Invalid('Invalid parent location level `' . $level . '` for a city');
        }
        throw new CM_Exception_Invalid('Invalid location level `' . $level . '`');
    }

    public function _getSchema() {
        return new CM_Model_Schema_Definition(array(
            'countryId' => array('type' => 'CM_Model_Location_Country'),
            'stateId'   => array('type' => 'CM_Model_Location_State', 'optional' => true),
            'name'      => array('type' => 'string'),
            'lat'       => array('type' => 'float', 'optional' => true),
            'lon'       => array('type' => 'float', 'optional' => true),
            '_maxmind'  => array('type' => 'string', 'optional' => true),
        ));
    }

    /**
     * @param CM_Model_Location_Country    $country
     * @param CM_Model_Location_State|null $state
     * @param string                       $name
     * @param float|null                   $lat
     * @param float|null                   $lon
     * @param string|null                  $maxMind
     * @return CM_Model_Location_City
     */
    public static function create(CM_Model_Location_Country $country, CM_Model_Location_State $state = null, $name, $lat = null, $lon = null, $maxMind = null) {
        $city = new self();
        $city->_set(array(
            'countryId' => $country,
            'stateId'   => $state,
            'name'      => $name,
            'lat'       => $lat,
            'lon'       => $lon,
            '_maxmind'  => $maxMind,
        ));
        $city->commit();
        return $city;
    }
}
