<?php

class CM_FormField_Hidden extends CM_FormField_Abstract {
	/**
	 * Constructor.
	 *
	 * @param string $name
	 */
	public function __construct($name) {
		parent::__construct($name);
	}

	function validate($userInput, CM_Response_Abstract $response) {
		return $userInput;
	}

}
