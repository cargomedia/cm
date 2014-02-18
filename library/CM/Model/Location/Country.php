<?php

class CM_Model_Location_Country extends CM_Model_Abstract {

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

	public static function getPersistenceClass() {
		return 'CM_Model_StorageAdapter_Database';
	}
}
