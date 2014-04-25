<?php

class CM_Model_Location_Zip extends CM_Model_Abstract {

	public function _getSchema() {
		return new CM_Model_Schema_Definition(array(
				'cityId' => array('type' => 'CM_Model_Location_City'),
				'name'   => array('type' => 'string'),
				'lat'    => array('type' => 'float'),
				'lon'    => array('type' => 'float'),
		));
	}

	/**
	 * @param string                 $name
	 * @param float                  $lat
	 * @param float                  $lon
	 * @param CM_Model_Location_City $city
	 * @return CM_Model_Location_Zip
	 */
	public static function create($name, $lat, $lon, CM_Model_Location_City $city) {
		$zip = new self();
		$zip->_set(array(
				'name'   => $name,
				'lat'    => $lat,
				'lon'    => $lon,
				'cityId' => $city,
		));
		$zip->commit();
		return $zip;
	}

	public static function getPersistenceClass() {
		return 'CM_Model_StorageAdapter_Database';
	}
}
