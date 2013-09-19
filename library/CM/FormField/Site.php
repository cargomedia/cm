<?php

class CM_FormField_Site extends CM_FormField_Set_Select {

	public function __construct() {
		$valuesSet = array();
		foreach (CM_Site_Abstract::getAll() as $site) {
			$valuesSet[$site->getType()] = $site->getName();
		}
		parent::__construct($valuesSet, true);
	}

	public function validate($userInput, CM_Response_Abstract $response) {
		$userInput = parent::validate($userInput, $response);
		return CM_Site_Abstract::factory($userInput);
	}
}
