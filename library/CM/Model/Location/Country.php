<?php

class CM_Model_Location_Country extends CM_Model_Location_Abstract {

    /**
     * @return string
     */
    public function getAbbreviation() {
        return $this->_get('abbreviation');
    }

    /**
     * @param string $abbreviation
     */
    public function setAbbreviation($abbreviation) {
        $this->_set('abbreviation', $abbreviation);
    }

    public function getLevel() {
        return CM_Model_Location::LEVEL_COUNTRY;
    }

    public function getParent($level = null) {
        if (null === $level) {
            return null;
        }
        $level = (int) $level;
        switch ($level) {
            case CM_Model_Location::LEVEL_COUNTRY:
                return $this;
            case CM_Model_Location::LEVEL_STATE:
            case CM_Model_Location::LEVEL_CITY:
            case CM_Model_Location::LEVEL_ZIP:
                throw new CM_Exception_Invalid('Invalid parent location level for a country', null, ['level' => $level]);
        }
        throw new CM_Exception_Invalid('Invalid location level', null . ['level' => $level]);
    }

    public function _getSchema() {
        return new CM_Model_Schema_Definition(array(
            'abbreviation' => array('type' => 'string'),
            'name'         => array('type' => 'string'),
            'lat'          => array('type' => 'float', 'optional' => true),
            'lon'          => array('type' => 'float', 'optional' => true),
        ));
    }

    /**
     * @param string     $name
     * @param string     $abbreviation
     * @param float|null $latitude
     * @param float|null $longitude
     * @return CM_Model_Location_Country
     */
    public static function create($name, $abbreviation, $latitude = null, $longitude = null) {
        $country = new self();
        $country->_set(array(
            'abbreviation' => $abbreviation,
            'name'         => $name,
            'lat'          => $latitude,
            'lon'          => $longitude,
        ));
        $country->commit();
        return $country;
    }
}
