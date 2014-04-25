<?php

class CM_Model_Location_City extends CM_Model_Abstract {

	public function _getSchema() {
		return new CM_Model_Schema_Definition(array(
				'stateId'   => array('type' => 'CM_Model_Location_State', 'optional' => true),
				'countryId' => array('type' => 'CM_Model_Location_Country'),
				'name'      => array('type' => 'string'),
				'lat'       => array('type' => 'float'),
				'lon'       => array('type' => 'float'),
		));
	}

	/**
	 * @param string                       $name
	 * @param float                        $lat
	 * @param float                        $lon
	 * @param CM_Model_Location_Country    $country
	 * @param CM_Model_Location_State|null $state
	 * @return CM_Model_Location_City
	 */
	public static function create($name, $lat, $lon, CM_Model_Location_Country $country, CM_Model_Location_State $state = null) {
		$city = new self();
		$city->_set(array(
				'name'     => $name,
				'lat'      => $lat,
				'lon'      => $lon,
				'coutryId' => $country,
				'stateId'  => $state,
		));
		$city->commit();
		return $city;
	}

	public static function getPersistenceClass() {
		return 'CM_Model_StorageAdapter_Database';
	}
}
