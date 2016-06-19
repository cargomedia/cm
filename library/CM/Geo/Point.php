<?php

class CM_Geo_Point implements CM_Comparable, CM_ArrayConvertible {

    /** @var float */
    private $_latitude;

    /** @var float */
    private $_longitude;

    /**
     * @param float $latitude
     * @param float $longitude
     * @throws CM_Exception_Invalid
     */
    public function __construct($latitude, $longitude) {
        $this->setLatitude($latitude);
        $this->setLongitude($longitude);
    }

    /**
     * @return float
     */
    public function getLatitude() {
        return $this->_latitude;
    }

    /**
     * @param float $latitude
     * @throws CM_Exception_Invalid
     */
    public function setLatitude($latitude) {
        $latitude = (float) $latitude;
        if ($latitude > 90 || $latitude < -90) {
            throw new CM_Exception_Invalid('Latitude `' . $latitude . '` out of range');
        }
        $this->_latitude = $latitude;
    }

    /**
     * @return float
     */
    public function getLongitude() {
        return $this->_longitude;
    }

    /**
     * @param float $longitude
     * @throws CM_Exception_Invalid
     */
    public function setLongitude($longitude) {
        $longitude = (float) $longitude;
        if ($longitude > 180 || $longitude < -180) {
            throw new CM_Exception_Invalid('Longitude `' . $longitude . '` out of range');
        }
        $this->_longitude = $longitude;
    }

    /**
     * @param CM_Comparable $other
     * @return boolean
     */
    public function equals(CM_Comparable $other = null) {
        if (empty($other)) {
            return false;
        }
        /** @var CM_Geo_Point $other */
        return (get_class($this) === get_class($other)
            && $this->getLatitude() === $other->getLatitude()
            && $this->getLongitude() === $other->getLongitude());
    }

    /**
     * @param CM_Geo_Point $pointTo
     * @return float
     */
    public function calculateDistanceTo(CM_Geo_Point $pointTo) {
        $pi180 = M_PI / 180;
        $currentRadianLatitude = $this->getLatitude() * $pi180;
        $currentRadianLongitude = $this->getLongitude() * $pi180;
        $againstRadianLatitude = $pointTo->getLatitude() * $pi180;
        $againstRadianLongitude = $pointTo->getLongitude() * $pi180;

        $arcCosine = acos(
            sin($currentRadianLatitude) * sin($againstRadianLatitude) +
            cos($currentRadianLatitude) * cos($againstRadianLatitude) * cos($currentRadianLongitude - $againstRadianLongitude)
        );

        return CM_Model_Location::EARTH_RADIUS * $arcCosine;
    }

    public function toArray() {
        return [
            'latitude'  => $this->_latitude,
            'longitude' => $this->_longitude,
        ];
    }

    /**
     * @param array $data
     * @return CM_Geo_Point
     */
    public static function fromArray(array $data) {
        return new self($data['latitude'], $data['longitude']);
    }
}
