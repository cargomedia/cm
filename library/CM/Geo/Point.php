<?php

class CM_Geo_Point implements CM_Comparable {

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
}
