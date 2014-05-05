<?php

class CM_Model_Location_State extends CM_Model_Location_Abstract {

    /**
     * @return string|null
     */
    public function getAbbreviation() {
        return $this->_get('abbreviation');
    }

    /**
     * @param string|null $abbreviation
     */
    public function setAbbreviation($abbreviation) {
        $this->_set('abbreviation', $abbreviation);
    }

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

    public function getLevel() {
        return CM_Model_Location::LEVEL_STATE;
    }

    public function getParent($level = null) {
        if (null === $level) {
            $level = CM_Model_Location::LEVEL_COUNTRY;
        }
        $level = (int) $level;
        switch ($level) {
            case CM_Model_Location::LEVEL_COUNTRY:
                return $this->getCountry();
            case CM_Model_Location::LEVEL_STATE:
                return $this;
            case CM_Model_Location::LEVEL_CITY:
            case CM_Model_Location::LEVEL_ZIP:
                throw new CM_Exception_Invalid('Invalid parent location level for a state');
        }
        throw new CM_Exception_Invalid('Invalid location level `' . $level . '`');
    }

    public function _getSchema() {
        return new CM_Model_Schema_Definition(array(
            'countryId'    => array('type' => 'CM_Model_Location_Country'),
            'name'         => array('type' => 'string'),
            'abbreviation' => array('type' => 'string', 'optional' => true),
            '_maxmind'     => array('type' => 'string', 'optional' => true),
        ));
    }

    /**
     * @param CM_Model_Location_Country $country
     * @param string                    $name
     * @param string|null               $abbreviation
     * @param string|null               $maxMind
     * @return CM_Model_Location_State
     */
    public static function create(CM_Model_Location_Country $country, $name, $abbreviation = null, $maxMind = null) {
        $state = new self();
        $state->_set(array(
            'name'         => $name,
            'abbreviation' => $abbreviation,
            'countryId'    => $country,
            '_maxmind'     => $maxMind,
        ));
        $state->commit();
        return $state;
    }
}
