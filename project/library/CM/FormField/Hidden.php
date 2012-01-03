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

	public function validate($userInput) {
		return $userInput;
	}

}
