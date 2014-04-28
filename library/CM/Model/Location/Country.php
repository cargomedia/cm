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
        return $this->_set('abbreviation', $abbreviation);
    }

    public function getLevel() {
        return CM_Model_Location::LEVEL_COUNTRY;
    }

    public function getParent($level = null) {
        if (null === $level) {
            return null;
        }
        $level = (int) $level;
        if ($level === CM_Model_Location::LEVEL_COUNTRY) {
            return $this;
        }
        return null;
    }

    public function _getSchema() {
        return new CM_Model_Schema_Definition(array(
            'name'         => array('type' => 'string'),
            'abbreviation' => array('type' => 'string'),
        ));
    }

    /**
     * @param string $name
     * @param string $abbreviation
     * @return CM_Model_Location_Country
     */
    public static function create($name, $abbreviation) {
        $country = new self();
        $country->_set(array(
            'name'         => $name,
            'abbreviation' => $abbreviation
        ));
        $country->commit();
        return $country;
    }
}
