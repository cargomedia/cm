<?php

class CM_FormField_GeoPoint extends CM_FormField_Abstract {

	public function __construct($name) {
		parent::__construct($name);
	}

	/**
	 * @param array                $userInput
	 * @param CM_Response_Abstract $response
	 * @return CM_Geo_Point
	 * @throws CM_Exception_FormFieldValidation
	 */
	public function validate($userInput, CM_Response_Abstract $response) {
		if (!isset($userInput['latitude']) || !is_numeric($userInput['latitude'])) {
			throw new CM_Exception_FormFieldValidation('Latitude needs to be numeric');
		}
		if (!isset($userInput['longitude']) || !is_numeric($userInput['longitude'])) {
			throw new CM_Exception_FormFieldValidation('Longitude needs to be numeric');
		}

		try {
			$point = new CM_Geo_Point($userInput['latitude'], $userInput['longitude']);
		} catch (CM_Exception_Invalid $e) {
			throw new CM_Exception_FormFieldValidation('Invalid latitude or longitude value');
		}

		return $point;
	}

	public function prepare(array $params) {
		/** @var CM_Geo_Point $value */
		$value = $this->getValue();

		$this->setTplParam('latitude', $value->getLatitude());
		$this->setTplParam('longitude', $value->getLongitude());
	}

	public function isEmpty($userInput) {
		return empty($userInput['latitude']) && empty($userInput['longitude']);
	}
}
