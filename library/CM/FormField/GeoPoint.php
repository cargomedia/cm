<?php

class CM_FormField_GeoPoint extends CM_FormField_Abstract {

	public function __construct($name) {
		parent::__construct($name);
	}

	public function validate($userInput, CM_Response_Abstract $response) {
		if (!isset($userInput['latitude']) || !is_numeric($userInput['latitude'])) {
			throw new CM_Exception_FormFieldValidation('Latitude needs to be numeric');
		}
		if (!isset($userInput['longitude']) || !is_numeric($userInput['longitude'])) {
			throw new CM_Exception_FormFieldValidation('Longitude needs to be numeric');
		}
		$latitude = (float) $userInput['latitude'];
		$longitude = (float) $userInput['longitude'];
		if ($latitude > 90 || $latitude < -90) {
			throw new CM_Exception_FormFieldValidation('Latitude out of range');
		}
		if ($longitude > 180 || $longitude < -180) {
			throw new CM_Exception_FormFieldValidation('Longitude out of range');
		}

		return array('latitude' => $latitude, 'longitude' => $longitude);
	}

	public function prepare(array $params) {
		$value = $this->getValue();

		$this->setTplParam('latitude', $value['latitude']);
		$this->setTplParam('longitude', $value['longitude']);
	}

	public function isEmpty($userInput) {
		return empty($userInput['latitude']) && empty($userInput['longitude']);
	}
}
