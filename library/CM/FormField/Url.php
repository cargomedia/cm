<?php

class CM_FormField_Url extends CM_FormField_Text {

	public function __construct($name) {
		parent::__construct($name);
	}

	public function validate($userInput, CM_Response_Abstract $response) {
		$userInput = parent::validate($userInput, $response);

		if (false === filter_var($userInput, FILTER_VALIDATE_URL)) {
			throw new CM_Exception_FormFieldValidation('Invalid url');
		}

		return $userInput;
	}
}
