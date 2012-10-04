<?php

class CM_FormField_Email extends CM_FormField_Text {

	public function __construct($name = 'email') {
		parent::__construct($name);
	}

	public function validate($userInput, CM_Response_Abstract $response) {
		$userInput = parent::validate($userInput, $response);

		if (false === filter_var($userInput, FILTER_VALIDATE_EMAIL)) {
			throw new CM_Exception_FormFieldValidation('Invalid email address');
		}

		return $userInput;
	}
}
