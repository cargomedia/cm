<?php

class CM_Model_Location_State extends CM_Model_Abstract {

	public function _getSchema() {
		return new CM_Model_Schema_Definition(array(
				'countryId'    => array('type' => 'CM_Model_Location_Country'),
				'name'         => array('type' => 'string'),
				'abbreviation' => array('type' => 'string'),
		));
	}

	/**
	 * @param string                    $name
	 * @param string                    $abbreviation
	 * @param CM_Model_Location_Country $country
	 * @return CM_Model_Location_State
	 */
	public static function create($name, $abbreviation, CM_Model_Location_Country $country) {
		$state = new self();
		$state->_set(array(
				'name'         => $name,
				'abbreviation' => $abbreviation,
				'countryId'    => $country,
		));
		$state->commit();
		return $state;
	}

	public static function getPersistenceClass() {
		return 'CM_Model_StorageAdapter_Database';
	}
}
