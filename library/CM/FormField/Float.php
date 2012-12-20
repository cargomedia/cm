<?php

class CM_FormField_Float extends CM_FormField_Text {

	public function validate($userInput, CM_Response_Abstract $response) {
		$userInput = parent::validate($userInput, $response);
		if (!is_numeric($userInput)) {
			throw new CM_Exception_FormFieldValidation('Not numeric');
		}
		return (float) $userInput;
	}
}
