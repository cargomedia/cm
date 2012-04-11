<?php

class CM_FormField_Color extends CM_FormField_Abstract {

	public function __construct($name = 'color') {
		parent::__construct($name);
	}

	function validate($userInput, CM_Response_Abstract $response) {
		if (!preg_match('/^#[abcdef\d]{6}$/i', $userInput)) {
			throw new CM_Exception_FormFieldValidation('Invalid color');
		}
		return (string) $userInput;
	}

}
