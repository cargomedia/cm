<?php

class CM_FormField_Email extends CM_FormField_Text {

	public function __construct($name = 'email') {
		parent::__construct($name);
	}

	public function validate($userInput) {
		$userInput = parent::validate($userInput);

		if (!preg_match("/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i", $userInput)) {
			throw new CM_Exception_FormFieldValidation('Invalid email address');
		}

		return $userInput;
	}
}
